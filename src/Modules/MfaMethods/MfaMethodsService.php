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
     * Uses the userRegistrationDetails report endpoint.
     *
     * @return array<int, array>
     */
    public function getAll(): array
    {
        try {
            return $this->graph->paginate(
                '/reports/authenticationMethods/userRegistrationDetails',
                [
                    '$select' => 'id,userPrincipalName,userDisplayName,isMfaRegistered,isMfaCapable,methodsRegistered,defaultMfaMethod',
                    '$top'    => '999',
                ],
                50,
                'mfa_methods_detail',
                1800
            );
        } catch (\Throwable) {
            return [];
        }
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
