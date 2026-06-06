<?php

namespace App\Modules\AuthMethods;

use App\Graph\GraphClient;

/**
 * Reads and writes the tenant authentication methods policy
 * (/policies/authenticationMethodsPolicy). Each method can be enabled or
 * disabled. Recommendations lean on CIS M365 / Microsoft guidance:
 * prefer phishing-resistant methods, avoid SMS/Voice/Email as MFA.
 */
class AuthMethodsService
{
    private const POLICY    = '/policies/authenticationMethodsPolicy';
    private const CACHE_KEY  = 'auth_methods_policy';

    /** Display metadata + recommended target state per method id. */
    private const CATALOG = [
        'Fido2'                  => ['label' => 'FIDO2 Security Keys',         'recommend' => 'enabled',  'note' => 'Phishing-resistent — empfohlen.'],
        'MicrosoftAuthenticator' => ['label' => 'Microsoft Authenticator',     'recommend' => 'enabled',  'note' => 'Push/Passwordless — empfohlen (mit Number-Matching).'],
        'TemporaryAccessPass'    => ['label' => 'Temporary Access Pass (TAP)',  'recommend' => 'enabled',  'note' => 'Onboarding/Recovery — situativ aktivieren.'],
        'SoftwareOath'           => ['label' => 'Software-OATH (TOTP-App)',     'recommend' => 'enabled',  'note' => 'Akzeptabel als zusätzliche Methode.'],
        'X509Certificate'        => ['label' => 'Zertifikatsbasiert (CBA)',     'recommend' => 'neutral',  'note' => 'Phishing-resistent, falls PKI vorhanden.'],
        'HardwareOath'           => ['label' => 'Hardware-OATH-Token',          'recommend' => 'neutral',  'note' => 'OK, falls Hardware-Token im Einsatz.'],
        'Sms'                    => ['label' => 'SMS',                          'recommend' => 'disabled', 'note' => 'Schwach (SIM-Swapping) — möglichst deaktivieren.'],
        'Voice'                  => ['label' => 'Sprachanruf',                  'recommend' => 'disabled', 'note' => 'Schwach — möglichst deaktivieren.'],
        'Email'                  => ['label' => 'E-Mail-OTP',                   'recommend' => 'disabled', 'note' => 'Nur für Gäste/SSPR — als MFA schwach.'],
    ];

    public function __construct(private GraphClient $graph) {}

    /** Return each configured authentication method with its state + metadata. */
    public function getMethods(): array
    {
        $data    = $this->graph->get(self::POLICY, [], self::CACHE_KEY, 600);
        $configs = $data['authenticationMethodConfigurations'] ?? [];

        $out = [];
        foreach ($configs as $c) {
            $id = (string)($c['id'] ?? '');
            if ($id === '') continue;
            $meta = self::CATALOG[$id] ?? ['label' => $id, 'recommend' => 'neutral', 'note' => ''];
            $out[] = [
                'id'        => $id,
                'state'     => $c['state'] ?? 'default',
                'odataType' => $c['@odata.type'] ?? '',
                'label'     => $meta['label'],
                'recommend' => $meta['recommend'],
                'note'      => $meta['note'],
            ];
        }
        return $out;
    }

    /** Returns the last Graph error, or null. */
    public function getLastError(): ?array
    {
        return $this->graph->getLastError();
    }

    /**
     * Enable or disable a single authentication method.
     * Reuses the method's live @odata.type so the PATCH body always matches.
     */
    public function setState(string $methodId, string $state): void
    {
        $state = strtolower(trim($state));
        if (!in_array($state, ['enabled', 'disabled'], true)) {
            throw new \InvalidArgumentException('Ungültiger Status (erlaubt: enabled, disabled).');
        }

        // Read uncached to get the exact @odata.type of the target method.
        $data = $this->graph->get(self::POLICY, [], null, 0);
        $type = '';
        foreach ($data['authenticationMethodConfigurations'] ?? [] as $c) {
            if ((string)($c['id'] ?? '') === $methodId) {
                $type = (string)($c['@odata.type'] ?? '');
                break;
            }
        }
        if ($type === '') {
            throw new \RuntimeException('Authentifizierungsmethode nicht gefunden: ' . $methodId);
        }

        $this->graph->patch(
            self::POLICY . '/authenticationMethodConfigurations/' . rawurlencode($methodId),
            ['@odata.type' => $type, 'state' => $state]
        );
        $this->graph->getCache()->forget(self::CACHE_KEY);
    }
}
