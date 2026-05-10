<?php

namespace App\Modules\SignInLog;

use App\Graph\GraphClient;

class SignInLogService
{
    public function __construct(private GraphClient $graph) {}

    public function getLogs(array $filters = []): array
    {
        try {
            $conditions = [];

            // User filter: displayName or UPN starts with
            if (!empty($filters['user'])) {
                $safe = str_replace("'", "''", $filters['user']);
                $conditions[] = sprintf(
                    "startsWith(userDisplayName,'%s') or startsWith(userPrincipalName,'%s')",
                    $safe,
                    $safe
                );
            }

            // Status filter
            if (!empty($filters['status'])) {
                if ($filters['status'] === 'success') {
                    $conditions[] = 'status/errorCode eq 0';
                } elseif ($filters['status'] === 'failure') {
                    $conditions[] = 'status/errorCode ne 0';
                }
            }

            // App filter
            if (!empty($filters['app'])) {
                $safe = str_replace("'", "''", $filters['app']);
                $conditions[] = sprintf("appDisplayName eq '%s'", $safe);
            }

            // Country filter
            if (!empty($filters['country'])) {
                $safe = str_replace("'", "''", $filters['country']);
                $conditions[] = sprintf("location/countryOrRegion eq '%s'", $safe);
            }

            // Risk filter
            if (!empty($filters['risk'])) {
                $safe = str_replace("'", "''", $filters['risk']);
                $conditions[] = sprintf("riskLevelDuringSignIn eq '%s'", $safe);
            }

            // Days filter (default 7)
            $days = isset($filters['days']) && is_numeric($filters['days']) && (int)$filters['days'] > 0
                ? (int)$filters['days']
                : 7;
            $since = date('Y-m-d\TH:i:s\Z', strtotime("-{$days} days"));
            $conditions[] = "createdDateTime ge {$since}";

            $params = [
                '$select'  => 'id,createdDateTime,userDisplayName,userPrincipalName,appDisplayName,ipAddress,location,status,deviceDetail,riskLevelDuringSignIn,conditionalAccessStatus,clientAppUsed,resourceDisplayName',
                '$orderby' => 'createdDateTime desc',
                '$top'     => '200',
            ];

            if (!empty($conditions)) {
                $params['$filter'] = implode(' and ', $conditions);
            }

            $data = $this->graph->get('/auditLogs/signIns', $params, null);
            return $data['value'] ?? [];
        } catch (\Throwable) {
            return [];
        }
    }

    public function getStats(array $logs): array
    {
        return [
            'total'         => count($logs),
            'success'       => count(array_filter($logs, fn($l) => ($l['status']['errorCode'] ?? 1) === 0)),
            'failure'       => count(array_filter($logs, fn($l) => ($l['status']['errorCode'] ?? 1) !== 0)),
            'unique_users'  => count(array_unique(array_column($logs, 'userPrincipalName'))),
            'unique_ips'    => count(array_unique(array_column($logs, 'ipAddress'))),
            'top_apps'      => array_slice(
                array_count_values(array_filter(array_column($logs, 'appDisplayName'))),
                0, 5, true
            ),
            'top_countries' => array_slice(
                array_count_values(array_filter(array_map(
                    fn($l) => $l['location']['countryOrRegion'] ?? null,
                    $logs
                ))),
                0, 5, true
            ),
        ];
    }

    public function getDistinctApps(array $logs): array
    {
        $apps = array_filter(array_unique(array_column($logs, 'appDisplayName')));
        sort($apps);
        return $apps;
    }

    public function getDistinctCountries(array $logs): array
    {
        $countries = array_filter(array_unique(array_map(
            fn($l) => $l['location']['countryOrRegion'] ?? null,
            $logs
        )));
        sort($countries);
        return array_values($countries);
    }
}
