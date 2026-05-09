<?php

namespace App\Modules\OneDrive;

use App\Graph\GraphClient;

class OneDriveService
{
    public function __construct(private GraphClient $graph) {}

    public function getUsageReport(): array
    {
        try {
            // Get usage report via CSV (period D30)
            $data = $this->graph->get(
                '/reports/getOneDriveUsageAccountDetail(period=\'D30\')',
                [],
                'onedrive_usage',
                3600
            );
            return $data['value'] ?? $data;
        } catch (\Throwable) { return []; }
    }

    public function getStorageSummary(): array
    {
        try {
            return $this->graph->get(
                '/reports/getOneDriveUsageSummary(period=\'D30\')',
                [],
                'onedrive_summary',
                3600
            );
        } catch (\Throwable) { return []; }
    }

    public function getUserDrives(array $users): array
    {
        $result = [];
        $sample = array_slice($users, 0, 50); // limit API calls
        foreach ($sample as $user) {
            try {
                $drive = $this->graph->get(
                    "/users/{$user['id']}/drive",
                    ['$select' => 'id,name,quota,owner'],
                    "drive_{$user['id']}",
                    1800
                );
                if (isset($drive['quota'])) {
                    $result[] = [
                        'user'       => $user['displayName'] ?? $user['userPrincipalName'] ?? '',
                        'upn'        => $user['userPrincipalName'] ?? '',
                        'used'       => $drive['quota']['used'] ?? 0,
                        'total'      => $drive['quota']['total'] ?? 0,
                        'remaining'  => $drive['quota']['remaining'] ?? 0,
                        'state'      => $drive['quota']['state'] ?? 'normal',
                    ];
                }
            } catch (\Throwable) { continue; }
        }
        usort($result, fn($a, $b) => $b['used'] <=> $a['used']);
        return $result;
    }
}
