<?php

namespace App\Modules\Licenses;

use App\Graph\GraphClient;

class LicensesService
{
    private array $skuNames = [
        'SPE_E3'            => 'Microsoft 365 E3',
        'SPE_E5'            => 'Microsoft 365 E5',
        'SPE_F1'            => 'Microsoft 365 F3',
        'ENTERPRISEPREMIUM' => 'Office 365 E3',
        'ENTERPRISEPACK'    => 'Office 365 E3',
        'STANDARDPACK'      => 'Office 365 E1',
        'DESKLESSPACK'      => 'Office 365 F3',
        'TEAMS_EXPLORATORY' => 'Teams Exploratory',
        'AAD_PREMIUM'       => 'Azure AD Premium P1',
        'AAD_PREMIUM_P2'    => 'Azure AD Premium P2',
        'INTUNE_A'          => 'Intune',
        'EMS'               => 'EMS E3',
        'EMSPREMIUM'        => 'EMS E5',
        'POWER_BI_STANDARD' => 'Power BI (Free)',
        'PBI_PREMIUM_P1_ADDON' => 'Power BI Premium P1',
        'FLOW_FREE'         => 'Power Automate Free',
        'PROJECTPREMIUM'    => 'Project Plan 5',
        'VISIOCLIENT'       => 'Visio Plan 2',
        'EXCHANGESTANDARD'  => 'Exchange Online Plan 1',
        'EXCHANGEENTERPRISE'=> 'Exchange Online Plan 2',
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
}
