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

        // MFA percentage — dedicated cache key to avoid overwriting full MfaMethods dataset
        try {
            $mfaData = $this->graph->paginate(
                '/reports/authenticationMethods/userRegistrationDetails',
                ['$select' => 'isMfaRegistered', '$top' => '999'],
                50,
                'dash_mfa_pct',
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

        // CA policies: reuse the shared provider (single cache key ca_policies)
        try {
            $policies = \App\Modules\ConditionalAccess\ConditionalAccessService::fetchAllPolicies($this->graph);
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

    public function getExtendedStats(): array
    {
        $s = [
            'guests'             => null,
            'teams_count'        => null,
            'admin_assignments'  => null,
            'service_incidents'  => null,
            'incident_services'  => [],
            'msg_center_count'   => null,
            'secure_score'       => null,
            'secure_score_max'   => null,
            'adoption_exchange'  => null,
            'adoption_teams'     => null,
            'adoption_onedrive'  => null,
            'adoption_sharepoint'=> null,
        ];

        // Guest users — fast $count query, dedicated cache key
        try {
            $r = $this->graph->getEventual('/users', [
                '$count' => 'true', '$top' => '1', '$select' => 'id',
                '$filter' => "userType eq 'Guest'",
            ], 'dash_guests_count', 1800);
            $s['guests'] = (int)($r['@odata.count'] ?? count($r['value'] ?? []));
        } catch (\Throwable $e) { error_log('Dashboard guests: ' . $e->getMessage()); }

        // Teams count — fast $count query, dedicated cache key
        try {
            $r = $this->graph->getEventual('/groups', [
                '$count' => 'true', '$top' => '1', '$select' => 'id',
                '$filter' => "resourceProvisioningOptions/Any(x:x eq 'Team')",
            ], 'dash_teams_count', 1800);
            $s['teams_count'] = (int)($r['@odata.count'] ?? count($r['value'] ?? []));
        } catch (\Throwable $e) { error_log('Dashboard teams: ' . $e->getMessage()); }

        // Admin role assignments — fast $count query, dedicated cache key
        try {
            $r = $this->graph->getEventual('/roleManagement/directory/roleAssignments', [
                '$count' => 'true', '$top' => '1', '$select' => 'id',
            ], 'dash_admin_count', 1800);
            $s['admin_assignments'] = (int)($r['@odata.count'] ?? count($r['value'] ?? []));
        } catch (\Throwable $e) { error_log('Dashboard adminroles: ' . $e->getMessage()); }

        // Service health — reuse ServiceHealth module cache (same key + format)
        try {
            $r = $this->graph->get(
                '/admin/serviceAnnouncement/healthOverviews',
                ['$select' => 'service,status'],
                'servicehealth_overview', 300
            );
            $all = is_array($r) && isset($r['value']) ? $r['value'] : (array)$r;
            $incidents = array_filter($all, fn($i) => ($i['status'] ?? '') !== 'serviceOperational');
            $s['service_incidents'] = count($incidents);
            $s['incident_services'] = array_slice(array_column(array_values($incidents), 'service'), 0, 3);
        } catch (\Throwable $e) { error_log('Dashboard servicehealth: ' . $e->getMessage()); }

        // Message Center count — reuse MessageCenter module cache (same key + format)
        try {
            $r = $this->graph->get(
                '/admin/serviceAnnouncement/messages',
                ['$select' => 'id', '$top' => '100'],
                'msgcenter_messages', 900
            );
            $msgs = is_array($r) && isset($r['value']) ? $r['value'] : [];
            $s['msg_center_count'] = count($msgs);
        } catch (\Throwable $e) { error_log('Dashboard msgcenter: ' . $e->getMessage()); }

        // Secure Score — reuse SecureScore module cache (same key + format)
        try {
            $r = $this->graph->get(
                '/security/secureScores',
                ['$top' => '1', '$select' => 'currentScore,maxScore'],
                'securescore_latest', 3600
            );
            $items = is_array($r) && isset($r['value']) ? $r['value'] : [];
            if (!empty($items)) {
                $s['secure_score']     = round((float)($items[0]['currentScore'] ?? 0));
                $s['secure_score_max'] = round((float)($items[0]['maxScore']     ?? 0));
            }
        } catch (\Throwable $e) { error_log('Dashboard securescore: ' . $e->getMessage()); }

        // Adoption — reuse AdoptionService cache (getReport returns flat array)
        try {
            $rows = $this->graph->getReport(
                "/reports/getOffice365ActiveUserCounts(period='D30')",
                [], 'adoption_active_user_counts', 3600
            );
            if (!empty($rows)) {
                usort($rows, fn($a, $b) => strcmp(
                    $b['reportDate'] ?? $b['Report Date'] ?? '',
                    $a['reportDate'] ?? $a['Report Date'] ?? ''
                ));
                $latest = $rows[0];
                $s['adoption_exchange']   = (int)($latest['exchange']   ?? 0);
                $s['adoption_teams']      = (int)($latest['teams']      ?? 0);
                $s['adoption_onedrive']   = (int)($latest['oneDrive']   ?? $latest['onedrive']   ?? 0);
                $s['adoption_sharepoint'] = (int)($latest['sharePoint'] ?? $latest['sharepoint'] ?? 0);
            }
        } catch (\Throwable $e) { error_log('Dashboard adoption: ' . $e->getMessage()); }

        return $s;
    }

    private function friendlySkuName(string $partNumber): string
    {
        return \App\Helpers\SkuCatalog::name($partNumber);
    }
}
