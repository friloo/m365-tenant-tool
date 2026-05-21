<?php

namespace App\Modules\CrossTenantAccess;

use App\Graph\GraphClient;

/**
 * Cross-Tenant-Access — welche Partner-Tenants haben Zugriff auf
 * unsere Ressourcen (Inbound) und in welche Tenants dürfen unsere
 * User (Outbound). Plus die Tenant-Default-Einstellungen.
 *
 * Endpoint: /policies/crossTenantAccessPolicy
 *   /default            — Default-Policy für unbekannte Tenants
 *   /partners           — Partner-spezifische Overrides
 */
class CrossTenantAccessService
{
    public function __construct(private GraphClient $graph) {}

    public function getDefault(): array
    {
        try {
            return $this->graph->get('/policies/crossTenantAccessPolicy/default', [], 'xta_default', 1800) ?: [];
        } catch (\Throwable $e) {
            error_log('CrossTenant default: ' . $e->getMessage());
            return [];
        }
    }

    public function getPartners(): array
    {
        try {
            $data = $this->graph->paginate(
                '/policies/crossTenantAccessPolicy/partners',
                [],
                5,
                'xta_partners',
                1800
            );
            $result = [];
            foreach ($data as $p) {
                $result[] = [
                    'tenantId'              => $p['tenantId']   ?? '',
                    'isServiceProvider'     => $p['isServiceProvider'] ?? false,
                    'inbound_b2bCollab'     => $this->summarizeAccess($p['b2bCollaborationInbound'] ?? null),
                    'outbound_b2bCollab'    => $this->summarizeAccess($p['b2bCollaborationOutbound'] ?? null),
                    'inbound_b2bDirect'     => $this->summarizeAccess($p['b2bDirectConnectInbound'] ?? null),
                    'outbound_b2bDirect'    => $this->summarizeAccess($p['b2bDirectConnectOutbound'] ?? null),
                    'inbound_trust'         => $this->summarizeTrust($p['inboundTrust'] ?? null),
                    'tenantRestrictions'    => $p['tenantRestrictions'] ?? null,
                    'automaticUserConsentSettings' => $p['automaticUserConsentSettings'] ?? null,
                ];
            }
            return $result;
        } catch (\Throwable $e) {
            error_log('CrossTenant partners: ' . $e->getMessage());
            return [];
        }
    }

    private function summarizeAccess(?array $cfg): string
    {
        if (!$cfg) return 'Default';
        $apps = $cfg['applications']  ?? [];
        $users = $cfg['usersAndGroups'] ?? [];
        $appMode  = $apps['accessType']  ?? '';
        $userMode = $users['accessType'] ?? '';
        if ($appMode === 'blocked' || $userMode === 'blocked') return 'Blockiert';
        if ($appMode === 'allowed' || $userMode === 'allowed') return 'Erlaubt';
        return ucfirst($appMode . ' / ' . $userMode);
    }

    private function summarizeTrust(?array $trust): string
    {
        if (!$trust) return '–';
        $parts = [];
        if ($trust['isMfaAccepted']             ?? false) $parts[] = 'MFA';
        if ($trust['isCompliantDeviceAccepted'] ?? false) $parts[] = 'Compliant Device';
        if ($trust['isHybridAzureADJoinedDeviceAccepted'] ?? false) $parts[] = 'Hybrid Join';
        return empty($parts) ? 'Keine' : implode(' + ', $parts);
    }
}
