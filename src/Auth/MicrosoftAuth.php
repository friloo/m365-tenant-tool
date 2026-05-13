<?php

namespace App\Auth;

use App\Core\Config;
use App\Core\Session;
use App\Database\DB;

class MicrosoftAuth
{
    public static function getAuthUrl(): string
    {
        $config     = Config::getInstance();
        $tenantId   = $config->get('tenant_id');
        $clientId   = $config->get('client_id');

        $state = bin2hex(random_bytes(16));
        Session::set('oauth_state', $state);

        $params = http_build_query([
            'client_id'     => $clientId,
            'response_type' => 'code',
            'redirect_uri'  => self::redirectUri(),
            'response_mode' => 'query',
            'scope'         => 'openid profile email User.Read',
            'state'         => $state,
        ]);

        return "https://login.microsoftonline.com/{$tenantId}/oauth2/v2.0/authorize?{$params}";
    }

    /**
     * Exchange authorization code for user info.
     * Returns ['object_id', 'upn', 'display_name'] or null on failure.
     */
    public static function handleCallback(string $code, string $state): ?array
    {
        if (!$state || $state !== Session::get('oauth_state')) {
            return null;
        }
        Session::remove('oauth_state');

        $config       = Config::getInstance();
        $tenantId     = $config->get('tenant_id');
        $clientId     = $config->get('client_id');
        $clientSecret = $config->get('client_secret');

        $tokenUrl = "https://login.microsoftonline.com/{$tenantId}/oauth2/v2.0/token";

        $ch = curl_init($tokenUrl);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => http_build_query([
                'client_id'     => $clientId,
                'client_secret' => $clientSecret,
                'code'          => $code,
                'redirect_uri'  => self::redirectUri(),
                'grant_type'    => 'authorization_code',
            ]),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/x-www-form-urlencoded'],
        ]);
        $response = curl_exec($ch);
        $curlErr  = curl_errno($ch);
        // curl_close removed: no-op since PHP 8.0, deprecated since 8.5

        if ($curlErr || !$response) {
            return null;
        }

        $token = json_decode($response, true);
        if (empty($token['access_token'])) {
            return null;
        }

        return self::fetchMe($token['access_token']);
    }

    private static function fetchMe(string $accessToken): ?array
    {
        $ch = curl_init('https://graph.microsoft.com/v1.0/me?$select=id,userPrincipalName,displayName');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_HTTPHEADER     => ["Authorization: Bearer {$accessToken}"],
        ]);
        $response = curl_exec($ch);
        // curl_close removed: no-op since PHP 8.0, deprecated since 8.5

        $data = json_decode($response, true);
        if (empty($data['id'])) {
            return null;
        }

        return [
            'object_id'    => $data['id'],
            'upn'          => strtolower($data['userPrincipalName'] ?? ''),
            'display_name' => $data['displayName'] ?? '',
        ];
    }

    /**
     * Look up a DB user by Azure object ID, falling back to UPN for first-time logins.
     * Updates object_id + display_name on first UPN match.
     */
    public static function findDbUser(string $objectId, string $upn): ?array
    {
        $row = DB::fetchOne(
            'SELECT * FROM m365_users WHERE azure_object_id = ? AND is_active = 1 LIMIT 1',
            [$objectId]
        );

        if (!$row) {
            $row = DB::fetchOne(
                'SELECT * FROM m365_users WHERE upn = ? AND is_active = 1 LIMIT 1',
                [strtolower($upn)]
            );
            if ($row) {
                DB::execute(
                    'UPDATE m365_users SET azure_object_id = ? WHERE id = ?',
                    [$objectId, $row['id']]
                );
                $row['azure_object_id'] = $objectId;
            }
        }

        return $row ?: null;
    }

    public static function loginUser(array $dbUser, string $displayName, string $upn): void
    {
        Session::regenerate();
        Session::set('authenticated', true);
        Session::set('auth_type', 'microsoft');
        Session::set('username', $displayName ?: $upn);
        Session::set('role', $dbUser['role']);
        Session::set('login_time', time());
        Session::set('m365_upn', strtolower($upn));
        Session::set('m365_object_id', $dbUser['azure_object_id']);

        DB::execute(
            'UPDATE m365_users SET last_login = NOW(), display_name = ? WHERE id = ?',
            [$displayName, $dbUser['id']]
        );
    }

    public static function redirectUri(): string
    {
        $base = Config::getInstance()->get('app_base_url', '');
        if (!$base) {
            $proto = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $host  = $_SERVER['HTTP_HOST'] ?? 'localhost';
            $base  = "{$proto}://{$host}";
        }
        return rtrim($base, '/') . '/auth/microsoft/callback';
    }

    public static function isConfigured(): bool
    {
        $cfg = Config::getInstance();
        return !empty($cfg->get('tenant_id')) && !empty($cfg->get('client_id'));
    }
}
