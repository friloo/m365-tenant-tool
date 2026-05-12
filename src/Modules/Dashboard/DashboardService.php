<?php

namespace App\Modules\Dashboard;

use App\Graph\GraphClient;

class DashboardService
{
    public function __construct(private GraphClient $graph) {}

    public function getSecurityStatus(): array
    {
        $status = [
            'mfa_registered'   => null,
            'mfa_total'        => null,
            'mfa_pct'          => null,
            'ca_enabled'       => null,
            'ca_report_only'   => null,
            'non_compliant'    => null,
            'unresolved_alerts'=> null,
        ];

        // MFA: reuse cache already populated by MfaMethods module
        try {
            $mfaData = $this->graph->paginate(
                '/reports/authenticationMethods/userRegistrationDetails',
                ['$select' => 'isMfaRegistered', '$top' => '999'],
                50,
                'mfa_methods_detail',
                1800
            );
            if (!empty($mfaData)) {
                $status['mfa_total']      = count($mfaData);
                $status['mfa_registered'] = count(array_filter($mfaData, fn($r) => $r['isMfaRegistered'] ?? false));
                $status['mfa_pct']        = $status['mfa_total'] > 0
                    ? round(($status['mfa_registered'] / $status['mfa_total']) * 100)
                    : 0;
            }
        } catch (\Throwable) {}

        // CA policies: reuse cache populated by CA module
        try {
            $caPolicies = $this->graph->get(
                '/identity/conditionalAccessPolicies',
                ['$top' => '200'],
                'ca_policies',
                900
            );
            $policies = $caPolicies['value'] ?? [];
            $status['ca_enabled']     = count(array_filter($policies, fn($p) => ($p['state'] ?? '') === 'enabled'));
            $status['ca_report_only'] = count(array_filter($policies, fn($p) => ($p['state'] ?? '') === 'enabledForReportingButNotEnforced'));
        } catch (\Throwable) {}

        // Non-compliant Intune devices
        try {
            $nonComp = $this->graph->getEventual(
                '/deviceManagement/managedDevices',
                ['$count' => 'true', '$top' => '1', '$select' => 'id', '$filter' => "complianceState eq 'noncompliant'"],
                'dash_noncompliant',
                600
            );
            $status['non_compliant'] = (int)($nonComp['@odata.count'] ?? count($nonComp['value'] ?? []));
        } catch (\Throwable) {}

        // Unresolved Defender alerts
        try {
            $alerts = $this->graph->get(
                '/security/alerts_v2',
                ['$filter' => "status eq 'new' or status eq 'inProgress'", '$top' => '1', '$count' => 'true'],
                'dash_alerts',
                300
            );
            $status['unresolved_alerts'] = (int)($alerts['@odata.count'] ?? count($alerts['value'] ?? []));
        } catch (\Throwable) {}

        return $status;
    }

    public function getMetrics(): array
    {
        $metrics = [];

        // Total users — $count requires ConsistencyLevel: eventual to be returned
        try {
            $users = $this->graph->getEventual('/users', ['$count' => 'true', '$top' => '1', '$select' => 'id'], 'dash_user_count', 300);
            $metrics['total_users'] = (int)($users['@odata.count'] ?? count($users['value'] ?? []));
        } catch (\Throwable) { $metrics['total_users'] = null; }

        // Enabled users — same requirement
        try {
            $enabled = $this->graph->getEventual('/users', ['$count' => 'true', '$top' => '1', '$select' => 'id', '$filter' => 'accountEnabled eq true'], 'dash_enabled_users', 300);
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
            $devices = $this->graph->getEventual('/deviceManagement/managedDevices', ['$count' => 'true', '$top' => '1', '$select' => 'id'], 'dash_devices', 600);
            $metrics['total_devices'] = (int)($devices['@odata.count'] ?? count($devices['value'] ?? []));
        } catch (\Throwable) { $metrics['total_devices'] = null; }

        // Groups
        try {
            $groups = $this->graph->getEventual('/groups', ['$count' => 'true', '$top' => '1', '$select' => 'id'], 'dash_groups', 600);
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

    public function getLicenseRecommendations(array $skus): array
    {
        $recs = [];
        foreach ($skus as $sku) {
            if ($sku['pct'] >= 90) {
                $recs[] = ['type' => 'warning', 'msg' => "⚠️ <strong>{$sku['name']}</strong>: nur noch {$sku['available']} Lizenzen verfügbar ({$sku['pct']}% belegt)"];
            }
            if ($sku['pct'] <= 20 && $sku['consumed'] > 0 && $sku['total'] >= 10) {
                $recs[] = ['type' => 'info', 'msg' => "💡 <strong>{$sku['name']}</strong>: {$sku['available']} ungenutzte Lizenzen ({$sku['pct']}% belegt) — Kontingent reduzierbar"];
            }
            if ($sku['suspended'] > 0) {
                $recs[] = ['type' => 'danger', 'msg' => "🚫 <strong>{$sku['name']}</strong>: {$sku['suspended']} Lizenzen gesperrt"];
            }
        }
        return $recs;
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
