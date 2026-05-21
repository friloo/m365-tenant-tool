<?php

namespace App\Modules\IdentityProviders;

use App\Graph\GraphClient;

/**
 * External Identity Providers — Google, Facebook, SAML/WS-Fed Trust
 * (z. B. ADFS, Okta, Ping). Plus die Federation-Konfiguration der
 * eigenen Domains.
 */
class IdentityProvidersService
{
    public function __construct(private GraphClient $graph) {}

    public function listIdentityProviders(): array
    {
        try {
            $data = $this->graph->get('/identity/identityProviders', [], 'idp_providers', 3600);
            return $data['value'] ?? [];
        } catch (\Throwable $e) {
            error_log('IdentityProviders list: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Federation-Settings pro Domain — wenn eine Domain federiert ist
     * (ADFS/Okta/Ping), zeigt die Domain einen passenden Wert.
     */
    public function listFederatedDomains(): array
    {
        try {
            $domains = $this->graph->paginate(
                '/domains',
                ['$select' => 'id,authenticationType,isVerified,isDefault'],
                5,
                'idp_domains',
                3600
            );
        } catch (\Throwable) { return []; }

        $result = [];
        foreach ($domains as $d) {
            if (($d['authenticationType'] ?? '') !== 'Federated') continue;
            $result[] = [
                'name'      => $d['id'] ?? '',
                'isVerified'=> $d['isVerified'] ?? false,
                'isDefault' => $d['isDefault']  ?? false,
            ];
        }
        return $result;
    }
}
