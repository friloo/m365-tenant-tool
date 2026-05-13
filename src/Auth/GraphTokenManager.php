<?php

namespace App\Auth;

use App\Core\Config;
use App\Database\DB;
use App\Encryption\Encryptor;

class GraphTokenManager
{
    private Encryptor $encryptor;
    private Config $config;

    public function __construct(Encryptor $encryptor)
    {
        $this->encryptor = $encryptor;
        $this->config    = Config::getInstance();
    }

    public function getToken(): string
    {
        // Check for valid cached token (5-minute buffer)
        $row = DB::fetchOne(
            'SELECT access_token, expires_at FROM graph_tokens ORDER BY id DESC LIMIT 1'
        );

        if ($row && strtotime($row['expires_at']) > time() + 300) {
            return $this->encryptor->decrypt($row['access_token']);
        }

        // Fetch new token via client credentials
        $token = $this->fetchNewToken();
        $this->storeToken($token);
        return $token['access_token'];
    }

    private function fetchNewToken(): array
    {
        $tenantId     = $this->config->get('tenant_id');
        $clientId     = $this->config->get('client_id');
        $clientSecret = $this->config->get('client_secret');

        $url = "https://login.microsoftonline.com/{$tenantId}/oauth2/v2.0/token";

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POSTFIELDS => http_build_query([
                'client_id'     => $clientId,
                'client_secret' => $clientSecret,
                'scope'         => 'https://graph.microsoft.com/.default',
                'grant_type'    => 'client_credentials',
            ]),
            CURLOPT_TIMEOUT => 15,
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        // curl_close removed: no-op since PHP 8.0, deprecated since 8.5

        if ($httpCode !== 200) {
            $err = json_decode($response, true);
            throw new \RuntimeException('Token request failed: ' . ($err['error_description'] ?? "HTTP {$httpCode}"));
        }

        return json_decode($response, true);
    }

    private function storeToken(array $token): void
    {
        $expiresAt = date('Y-m-d H:i:s', time() + (int)($token['expires_in'] ?? 3600));
        $encrypted = $this->encryptor->encrypt($token['access_token']);

        DB::execute('DELETE FROM graph_tokens');
        DB::execute(
            'INSERT INTO graph_tokens (access_token, expires_at) VALUES (?, ?)',
            [$encrypted, $expiresAt]
        );
    }
}
