<?php

namespace App\Modules\AuthStrength;

use App\Graph\GraphClient;

/**
 * Authentication-Strength — wer im Tenant benutzt phishing-resistente
 * Methoden (FIDO2, Windows Hello, Certificate-Based), wer nur SMS-OTP.
 * Außerdem die im Tenant konfigurierten Authentication-Strength-Policies.
 */
class AuthStrengthService
{
    /**
     * MFA-Methoden, die Microsoft als phishing-resistent klassifiziert.
     * Microsoft Authenticator mit Number-Matching ist NICHT auf der Liste,
     * weil die Number-Matching-Eingabe phishbar ist (Adversary-in-the-Middle).
     */
    private const PHISHING_RESISTANT = [
        '#microsoft.graph.fido2AuthenticationMethod'                        => 'FIDO2 Security Key',
        '#microsoft.graph.windowsHelloForBusinessAuthenticationMethod'      => 'Windows Hello for Business',
        '#microsoft.graph.x509CertificateAuthenticationMethod'              => 'Certificate-Based Auth',
        '#microsoft.graph.platformCredentialAuthenticationMethod'           => 'Platform Credential (iOS/Mac)',
        '#microsoft.graph.hardwareOathAuthenticationMethod'                 => 'Hardware OATH Token',
    ];

    private const WEAK_METHODS = [
        '#microsoft.graph.phoneAuthenticationMethod'                        => 'Telefon (SMS/Call)',
        '#microsoft.graph.emailAuthenticationMethod'                        => 'E-Mail-OTP',
    ];

    private const SOFTWARE_MFA = [
        '#microsoft.graph.microsoftAuthenticatorAuthenticationMethod'       => 'Microsoft Authenticator App',
        '#microsoft.graph.softwareOathAuthenticationMethod'                 => 'Software OATH (TOTP)',
        '#microsoft.graph.temporaryAccessPassAuthenticationMethod'          => 'Temporary Access Pass',
    ];

    public function __construct(private GraphClient $graph) {}

    /**
     * @return array{
     *   total:int,
     *   phishing_resistant:int,
     *   phishing_resistant_pct:int,
     *   software_mfa:int,
     *   weak_only:int,
     *   no_mfa:int,
     *   method_breakdown: array<string,int>,
     *   weak_users: array<int,array{upn:string,methods:array<int,string>}>
     * }
     */
    public function getRegistrationReport(): array
    {
        $rows = [];
        try {
            $rows = $this->graph->paginate(
                '/reports/authenticationMethods/userRegistrationDetails',
                [
                    '$select' => 'userPrincipalName,methodsRegistered,isMfaRegistered,isPasswordlessCapable',
                    '$top'    => '999',
                ],
                20,
                'auth_strength_reg',
                3600
            );
        } catch (\Throwable $e) {
            error_log('AuthStrength registration: ' . $e->getMessage());
            return $this->emptyResult();
        }
        if (empty($rows)) return $this->emptyResult();

        $total = $resistant = $software = $weakOnly = $noMfa = 0;
        $breakdown = [
            'FIDO2 Security Key' => 0, 'Windows Hello for Business' => 0,
            'Certificate-Based Auth' => 0, 'Microsoft Authenticator App' => 0,
            'Software OATH (TOTP)' => 0, 'Telefon (SMS/Call)' => 0,
            'E-Mail-OTP' => 0,
        ];
        $weakUsers = [];

        foreach ($rows as $r) {
            $total++;
            $methods = $r['methodsRegistered'] ?? [];
            $hasResistant = false;
            $hasSoftware  = false;
            $hasWeak      = false;
            $upn = $r['userPrincipalName'] ?? '';

            foreach ($methods as $m) {
                // Der Report-Endpoint nutzt vereinfachte Method-Namen, nicht @odata.type.
                $label = $this->mapReportMethodName($m);
                if (isset($breakdown[$label])) $breakdown[$label]++;
                if ($this->isResistantLabel($label)) $hasResistant = true;
                elseif ($this->isSoftwareLabel($label)) $hasSoftware = true;
                elseif ($this->isWeakLabel($label))    $hasWeak     = true;
            }

            if ($hasResistant) {
                $resistant++;
            } elseif ($hasSoftware) {
                $software++;
            } elseif ($hasWeak) {
                $weakOnly++;
                $weakUsers[] = ['upn' => $upn, 'methods' => array_map([$this, 'mapReportMethodName'], $methods)];
            } else {
                $noMfa++;
            }
        }

        return [
            'total'                  => $total,
            'phishing_resistant'     => $resistant,
            'phishing_resistant_pct' => $total > 0 ? (int)round($resistant / $total * 100) : 0,
            'software_mfa'           => $software,
            'weak_only'              => $weakOnly,
            'no_mfa'                 => $noMfa,
            'method_breakdown'       => array_filter($breakdown, fn($n) => $n > 0),
            'weak_users'             => array_slice($weakUsers, 0, 100),
        ];
    }

    /**
     * Authentication-Strength-Policies (Tenant-Settings) + Built-Ins.
     */
    public function getPolicies(): array
    {
        try {
            $data = $this->graph->get(
                '/identity/conditionalAccess/authenticationStrengths/policies',
                [],
                'auth_strength_policies',
                3600
            );
            return $data['value'] ?? [];
        } catch (\Throwable $e) {
            error_log('AuthStrength policies: ' . $e->getMessage());
            return [];
        }
    }

    // ── Private helpers ────────────────────────────────────────────────────

    private function mapReportMethodName(string $m): string
    {
        // Der report-endpoint liefert teils kurze Namen, teils @odata.type
        $m = strtolower($m);
        return match (true) {
            str_contains($m, 'fido')                    => 'FIDO2 Security Key',
            str_contains($m, 'windowsHello')
                || str_contains($m, 'windowshello')     => 'Windows Hello for Business',
            str_contains($m, 'x509')
                || str_contains($m, 'certificate')      => 'Certificate-Based Auth',
            str_contains($m, 'authenticator')
                || str_contains($m, 'microsoftauth')    => 'Microsoft Authenticator App',
            str_contains($m, 'softwareoath')
                || str_contains($m, 'softoath')
                || $m === 'softwareonetimepasscode'     => 'Software OATH (TOTP)',
            str_contains($m, 'hardwareoath')            => 'Hardware OATH Token',
            str_contains($m, 'mobilephone')
                || str_contains($m, 'sms')
                || str_contains($m, 'voice')
                || str_contains($m, 'phone')            => 'Telefon (SMS/Call)',
            str_contains($m, 'email')                   => 'E-Mail-OTP',
            str_contains($m, 'temporaryaccess')         => 'Temporary Access Pass',
            default                                     => $m,
        };
    }

    private function isResistantLabel(string $label): bool
    {
        return in_array($label, [
            'FIDO2 Security Key', 'Windows Hello for Business',
            'Certificate-Based Auth', 'Hardware OATH Token',
        ], true);
    }

    private function isSoftwareLabel(string $label): bool
    {
        return in_array($label, [
            'Microsoft Authenticator App', 'Software OATH (TOTP)',
        ], true);
    }

    private function isWeakLabel(string $label): bool
    {
        return in_array($label, ['Telefon (SMS/Call)', 'E-Mail-OTP'], true);
    }

    private function emptyResult(): array
    {
        return [
            'total' => 0, 'phishing_resistant' => 0, 'phishing_resistant_pct' => 0,
            'software_mfa' => 0, 'weak_only' => 0, 'no_mfa' => 0,
            'method_breakdown' => [], 'weak_users' => [],
        ];
    }
}
