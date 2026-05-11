<?php

namespace App\Modules\MfaMethods;

use App\Graph\GraphClient;

class MfaMethodsService
{
    public function __construct(private GraphClient $graph) {}

    /**
     * Friendly display labels for method keys returned by Graph.
     */
    public static function methodLabels(): array
    {
        return [
            'microsoftAuthenticatorPush' => 'Microsoft Authenticator (Push)',
            'softwareOneTimePasscode'    => 'Authenticator App (TOTP)',
            'phoneAuthentication'        => 'SMS / Anruf',
            'hardwareOneTimePasscode'    => 'Hardware-Token (TOTP)',
            'fido2SecurityKey'           => 'FIDO2-Sicherheitsschlüssel',
            'windowsHelloForBusiness'    => 'Windows Hello',
            'email'                      => 'E-Mail OTP',
            'temporaryAccessPass'        => 'Temporärer Zugangscode',
        ];
    }

    /**
     * Fetch all users with their MFA registration details.
     * Tries the modern endpoint first, falls back to the legacy one.
     *
     * @return array<int, array>
     */
    public function getAll(): array
    {
        // Try the legacy endpoint first — works with AuditLog.Read.All + Reports.Read.All + Entra P1.
        // Does NOT require the "Authentication Methods Activity" feature to be enabled in the portal.
        try {
            $rows = $this->graph->paginate(
                '/reports/credentialUserRegistrationDetails',
                [],
                50,
                'mfa_methods_legacy',
                1800
            );
            if (!empty($rows)) {
                return array_map(fn($r) => [
                    'id'                => $r['id'] ?? '',
                    'userPrincipalName' => $r['userPrincipalName'] ?? '',
                    'userDisplayName'   => $r['userDisplayName'] ?? '',
                    'isMfaRegistered'   => $r['isMfaRegistered'] ?? false,
                    'isMfaCapable'      => $r['isCapable']       ?? ($r['isMfaRegistered'] ?? false),
                    'methodsRegistered' => $r['authMethods']     ?? [],
                    'defaultMfaMethod'  => '',
                ], $rows);
            }
        } catch (\Throwable $e) {
            error_log('MFA methods (legacy endpoint) failed: ' . $e->getMessage());
        }

        // Fall back to the modern endpoint (requires tenant-side enablement in Entra portal)
        try {
            $users = $this->graph->paginate(
                '/reports/authenticationMethods/userRegistrationDetails',
                [
                    '$select' => 'id,userPrincipalName,userDisplayName,isMfaRegistered,isMfaCapable,methodsRegistered,defaultMfaMethod',
                    '$top'    => '999',
                ],
                50,
                'mfa_methods_detail',
                1800
            );
            if (!empty($users)) {
                return $users;
            }
        } catch (\Throwable $e) {
            error_log('MFA methods (modern endpoint) failed: ' . $e->getMessage());
        }

        return [];
    }

    /** Returns the last Graph error (e.g. 403 missing permission), or null. */
    public function getLastError(): ?array
    {
        return $this->graph->getLastError();
    }

    /**
     * Compute summary statistics from the fetched user list.
     *
     * @param  array $users  Array returned by getAll()
     * @return array{
     *   total: int,
     *   mfa_registered: int,
     *   mfa_capable: int,
     *   no_mfa: int,
     *   by_method: array<string, int>,
     *   by_default: array<string, int>
     * }
     */
    public function getSummary(array $users): array
    {
        $byMethod  = [];
        $byDefault = [];

        foreach ($users as $u) {
            // methodsRegistered is already an array from Graph
            $methods = $u['methodsRegistered'] ?? [];
            if (is_string($methods)) {
                $methods = array_filter(array_map('trim', explode(',', $methods)));
            }
            foreach ($methods as $method) {
                $method = trim((string)$method);
                if ($method === '') {
                    continue;
                }
                $byMethod[$method] = ($byMethod[$method] ?? 0) + 1;
            }

            $default = trim((string)($u['defaultMfaMethod'] ?? ''));
            if ($default !== '') {
                $byDefault[$default] = ($byDefault[$default] ?? 0) + 1;
            }
        }

        // Sort both maps descending by count
        arsort($byMethod);
        arsort($byDefault);

        return [
            'total'          => count($users),
            'mfa_registered' => count(array_filter($users, fn($u) => $u['isMfaRegistered'] ?? false)),
            'mfa_capable'    => count(array_filter($users, fn($u) => $u['isMfaCapable'] ?? false)),
            'no_mfa'         => count(array_filter($users, fn($u) => !($u['isMfaRegistered'] ?? false))),
            'by_method'      => $byMethod,
            'by_default'     => $byDefault,
        ];
    }
}
