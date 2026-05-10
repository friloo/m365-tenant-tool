<?php

namespace App\Modules\Licenses;

use App\Graph\GraphClient;

class LicensesService
{
    private array $skuNames = [
        'SPE_E3'                => 'Microsoft 365 E3',
        'SPE_E5'                => 'Microsoft 365 E5',
        'SPE_F1'                => 'Microsoft 365 F3',
        'ENTERPRISEPREMIUM'     => 'Microsoft 365 E5',
        'ENTERPRISEPACK'        => 'Microsoft 365 E3',
        'STANDARDPACK'          => 'Office 365 E1',
        'DESKLESSPACK'          => 'Office 365 F3',
        'BUSINESS_PREMIUM'      => 'Microsoft 365 Business Premium',
        'O365_BUSINESS_ESSENTIALS' => 'Microsoft 365 Business Basic',
        'O365_BUSINESS_PREMIUM' => 'Microsoft 365 Business Standard',
        'TEAMS_EXPLORATORY'     => 'Teams Exploratory',
        'AAD_PREMIUM'           => 'Azure AD Premium P1',
        'AAD_PREMIUM_P2'        => 'Azure AD Premium P2',
        'INTUNE_A'              => 'Intune',
        'EMS'                   => 'EMS E3',
        'EMSPREMIUM'            => 'EMS E5',
        'POWER_BI_STANDARD'     => 'Power BI (Free)',
        'PBI_PREMIUM_P1_ADDON'  => 'Power BI Premium P1',
        'FLOW_FREE'             => 'Power Automate Free',
        'PROJECTPREMIUM'        => 'Project Plan 5',
        'VISIOCLIENT'           => 'Visio Plan 2',
        'EXCHANGESTANDARD'      => 'Exchange Online Plan 1',
        'EXCHANGEENTERPRISE'    => 'Exchange Online Plan 2',
        'MCOSTANDARD'           => 'Skype for Business Online Plan 2',
        'MCOPSTN1'              => 'Microsoft 365 Domestic Calling Plan',
        'DEFENDER_ENDPOINT_P1'  => 'Microsoft Defender for Endpoint P1',
    ];

    public function __construct(private GraphClient $graph) {}

    public function getSkus(): array
    {
        $data = $this->graph->get('/subscribedSkus', [], 'licenses_skus', 1800);
        $result = [];
        foreach ($data['value'] ?? [] as $sku) {
            $consumed = (int)($sku['consumedUnits'] ?? 0);
            $enabled  = (int)($sku['prepaidUnits']['enabled'] ?? 0);
            $result[] = [
                'skuId'      => $sku['skuId'],
                'partNumber' => $sku['skuPartNumber'],
                'name'       => $this->friendlyName($sku['skuPartNumber']),
                'consumed'   => $consumed,
                'total'      => $enabled,
                'available'  => max(0, $enabled - $consumed),
                'suspended'  => (int)($sku['prepaidUnits']['suspended'] ?? 0),
                'pct'        => $enabled > 0 ? round(($consumed / $enabled) * 100) : 0,
            ];
        }
        usort($result, fn($a, $b) => $b['consumed'] <=> $a['consumed']);
        return $result;
    }

    public function getUserAssignments(): array
    {
        $users = $this->graph->paginate(
            '/users',
            ['$select' => 'id,displayName,userPrincipalName,assignedLicenses', '$top' => '999', '$filter' => 'assignedLicenses/$count ne 0'],
            30,
            'licenses_users',
            1800
        );
        return $users;
    }

    public function friendlyName(string $partNumber): string
    {
        return $this->skuNames[$partNumber] ?? str_replace('_', ' ', $partNumber);
    }

    public function getSubscriptionExpiry(): array
    {
        $cache = $this->graph->getCache();
        return $cache->remember('license_expiry', function () {
            $today = new \DateTimeImmutable('today');

            // Try beta endpoint first
            try {
                $data = $this->graph->get(
                    '/directory/subscriptions',
                    ['$select' => 'id,skuId,skuPartNumber,status,nextLifecycleDateTime,totalLicenseCount,consumedLicenseCount'],
                    null,
                    0
                );
                $items = $data['value'] ?? [];
                if (!empty($items)) {
                    $result = [];
                    foreach ($items as $sub) {
                        $nextRaw = $sub['nextLifecycleDateTime'] ?? null;
                        $next    = null;
                        $daysLeft = null;
                        if ($nextRaw !== null) {
                            $next     = new \DateTimeImmutable($nextRaw);
                            $diff     = $today->diff($next);
                            $daysLeft = $next >= $today ? (int)$diff->days : -(int)$diff->days;
                        }
                        $total    = (int)($sub['totalLicenseCount'] ?? 0);
                        $consumed = (int)($sub['consumedLicenseCount'] ?? 0);
                        $status   = $sub['status'] ?? 'Unknown';
                        $result[] = [
                            'id'               => $sub['id'] ?? '',
                            'sku_name'         => $this->friendlyName($sub['skuPartNumber'] ?? ''),
                            'status'           => $status,
                            'next_lifecycle'   => $next,
                            'days_until_expiry' => $daysLeft,
                            'total_licenses'   => $total,
                            'consumed_licenses' => $consumed,
                            'is_expiring_soon' => $daysLeft !== null && $daysLeft <= 60,
                            'is_expired'       => $daysLeft !== null && $daysLeft <= 0,
                        ];
                    }
                    return $result;
                }
            } catch (\Throwable) {
                // Fall through to v1.0 fallback
            }

            // Fallback: v1.0 subscribedSkus
            $data  = $this->graph->get(
                '/subscribedSkus',
                ['$select' => 'id,skuId,skuPartNumber,prepaidUnits,consumedUnits,capabilityStatus'],
                null,
                0
            );
            $result = [];
            foreach ($data['value'] ?? [] as $sku) {
                $total    = (int)($sku['prepaidUnits']['enabled'] ?? 0);
                $consumed = (int)($sku['consumedUnits'] ?? 0);
                $status   = $sku['capabilityStatus'] ?? 'Enabled';
                $result[] = [
                    'id'               => $sku['id'] ?? '',
                    'sku_name'         => $this->friendlyName($sku['skuPartNumber'] ?? ''),
                    'status'           => $status,
                    'next_lifecycle'   => null,
                    'days_until_expiry' => null,
                    'total_licenses'   => $total,
                    'consumed_licenses' => $consumed,
                    'is_expiring_soon' => false,
                    'is_expired'       => false,
                ];
            }
            return $result;
        }, 3600);
    }

    public function getExpiringSoon(int $days = 60): array
    {
        $all = $this->getSubscriptionExpiry();
        $soon = array_filter($all, fn($s) => $s['is_expiring_soon'] === true);
        usort($soon, fn($a, $b) => ($a['days_until_expiry'] ?? PHP_INT_MAX) <=> ($b['days_until_expiry'] ?? PHP_INT_MAX));
        return array_values($soon);
    }
}
