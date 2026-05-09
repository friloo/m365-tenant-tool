<?php

namespace App\Modules\Dashboard;

use App\Graph\GraphClient;

class DashboardService
{
    public function __construct(private GraphClient $graph) {}

    public function getMetrics(): array
    {
        $metrics = [];

        // Total users
        try {
            $users = $this->graph->get('/users', ['$count' => 'true', '$top' => '1', '$select' => 'id'], 'dash_user_count', 300);
            $metrics['total_users'] = (int)($users['@odata.count'] ?? count($users['value'] ?? []));
        } catch (\Throwable) { $metrics['total_users'] = null; }

        // Enabled users
        try {
            $enabled = $this->graph->get('/users', ['$count' => 'true', '$top' => '1', '$select' => 'id', '$filter' => 'accountEnabled eq true'], 'dash_enabled_users', 300);
            $metrics['enabled_users'] = (int)($enabled['@odata.count'] ?? 0);
        } catch (\Throwable) { $metrics['enabled_users'] = null; }

        // License SKUs
        try {
            $skus = $this->graph->get('/subscribedSkus', [], 'dash_skus', 600);
            $metrics['license_products'] = count($skus['value'] ?? []);
        } catch (\Throwable) { $metrics['license_products'] = null; }

        // Risky users
        try {
            $risky = $this->graph->get('/identityProtection/riskyUsers', ['$count' => 'true', '$top' => '1', '$filter' => "riskState eq 'atRisk'"], 'dash_risky', 300);
            $metrics['risky_users'] = (int)($risky['@odata.count'] ?? count($risky['value'] ?? []));
        } catch (\Throwable) { $metrics['risky_users'] = 0; }

        // Devices
        try {
            $devices = $this->graph->get('/deviceManagement/managedDevices', ['$count' => 'true', '$top' => '1', '$select' => 'id'], 'dash_devices', 600);
            $metrics['total_devices'] = (int)($devices['@odata.count'] ?? count($devices['value'] ?? []));
        } catch (\Throwable) { $metrics['total_devices'] = null; }

        // Groups
        try {
            $groups = $this->graph->get('/groups', ['$count' => 'true', '$top' => '1', '$select' => 'id'], 'dash_groups', 600);
            $metrics['total_groups'] = (int)($groups['@odata.count'] ?? count($groups['value'] ?? []));
        } catch (\Throwable) { $metrics['total_groups'] = null; }

        return $metrics;
    }

    public function getLicenseSummary(): array
    {
        try {
            $skus = $this->graph->get('/subscribedSkus', [], 'dash_sku_detail', 600);
            $result = [];
            foreach ($skus['value'] ?? [] as $sku) {
                $consumed = (int)($sku['consumedUnits'] ?? 0);
                $enabled  = (int)($sku['prepaidUnits']['enabled'] ?? 0);
                if ($enabled === 0) continue;
                $result[] = [
                    'name'     => $this->friendlySkuName($sku['skuPartNumber'] ?? ''),
                    'consumed' => $consumed,
                    'total'    => $enabled,
                    'pct'      => $enabled > 0 ? round(($consumed / $enabled) * 100) : 0,
                ];
            }
            usort($result, fn($a, $b) => $b['consumed'] <=> $a['consumed']);
            return array_slice($result, 0, 8);
        } catch (\Throwable) { return []; }
    }

    private function friendlySkuName(string $partNumber): string
    {
        $map = [
            'SPE_E3'           => 'Microsoft 365 E3',
            'SPE_E5'           => 'Microsoft 365 E5',
            'ENTERPRISEPREMIUM'=> 'Office 365 E3',
            'ENTERPRISEPACK'   => 'Office 365 E3',
            'STANDARDPACK'     => 'Office 365 E1',
            'DESKLESSPACK'     => 'Office 365 F3',
            'TEAMS_EXPLORATORY'=> 'Teams Exploratory',
            'FLOW_FREE'        => 'Power Automate Free',
            'POWER_BI_STANDARD'=> 'Power BI (free)',
            'AAD_PREMIUM'      => 'Azure AD Premium P1',
            'AAD_PREMIUM_P2'   => 'Azure AD Premium P2',
            'INTUNE_A'         => 'Intune',
            'EMS'              => 'EMS E3',
            'EMSPREMIUM'       => 'EMS E5',
        ];
        return $map[$partNumber] ?? str_replace('_', ' ', $partNumber);
    }
}
