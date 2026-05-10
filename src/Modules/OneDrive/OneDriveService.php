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

    /**
     * Returns a map of lowercase UPN → drive info for all provisioned OneDrives,
     * using the tenant-wide usage report (single API call, cached).
     */
    public function getPersonalDrivesReport(): array
    {
        $rows = $this->graph->getReport(
            "/reports/getOneDriveUsageAccountDetail(period='D30')",
            [],
            'od_personal_report',
            1800
        );
        $map = [];
        foreach ($rows as $row) {
            $upn = strtolower($row['ownerPrincipalName'] ?? '');
            if (!$upn || ($row['isDeleted'] ?? false)) continue;
            $map[$upn] = [
                'storageUsed'      => (int)($row['storageUsedInBytes']      ?? 0),
                'storageAllocated' => (int)($row['storageAllocatedInBytes'] ?? 0),
                'fileCount'        => (int)($row['fileCount']               ?? 0),
                'lastActivity'     => $row['lastActivityDate'] ?? null,
                'siteUrl'          => $row['siteUrl']          ?? null,
            ];
        }
        return $map;
    }

    /**
     * Provision a personal OneDrive for a user by accessing their drive endpoint.
     * Returns true if the drive exists/was just created.
     */
    public function provisionDrive(string $userId): bool
    {
        $drive = $this->graph->get(
            "/users/{$userId}/drive",
            ['$select' => 'id,webUrl'],
            null,
            0
        );
        $ok = !empty($drive['id']);
        if ($ok) {
            $this->graph->getCache()->forget('od_personal_report');
            $this->graph->getCache()->forget("drive_{$userId}");
        }
        return $ok;
    }

    /**
     * Deprovision (delete) a user's personal OneDrive site.
     * Requires Sites.FullControl.All on the Azure App Registration.
     * The site is moved to the SharePoint recycle bin (not immediately purged).
     */
    public function deprovisionDrive(string $userId): void
    {
        // Resolve the site ID from the drive's sharePointIds
        $drive = $this->graph->get(
            "/users/{$userId}/drive",
            ['$select' => 'id,sharePointIds,webUrl'],
            null,
            0
        );

        if (empty($drive['id'])) {
            throw new \RuntimeException('Kein OneDrive für diesen Benutzer gefunden.');
        }

        $siteId = $drive['sharePointIds']['siteId'] ?? null;
        if (!$siteId) {
            throw new \RuntimeException('SharePoint Site-ID konnte nicht ermittelt werden.');
        }

        $this->graph->delete("/sites/{$siteId}");
        $this->graph->getCache()->forget('od_personal_report');
        $this->graph->getCache()->forget("drive_{$userId}");
    }
}
