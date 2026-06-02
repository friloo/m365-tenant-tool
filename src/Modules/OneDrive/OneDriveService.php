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
     * UPN → drive-info map for all provisioned OneDrives. Prefers the tenant
     * usage report (one Graph call). If the report is empty OR anonymised (M365
     * "concealed user names" replaces UPNs with an irreversible token that can't
     * be joined to real users), falls back to per-user /drive lookups, which
     * always return real UPNs. Shared by the overview and the personal-drives tab.
     */
    public function getProvisionedDriveMap(array $users): array
    {
        $realUpns = [];
        foreach ($users as $u) {
            $upn = strtolower($u['userPrincipalName'] ?? '');
            if ($upn !== '') { $realUpns[$upn] = true; }
        }

        $map = [];
        try { $map = $this->getPersonalDrivesReport(); } catch (\Throwable) { $map = []; }

        $anonymised = false;
        if (!empty($map)) {
            $matches = 0;
            foreach (array_keys($map) as $k) {
                if (isset($realUpns[$k])) { $matches++; }
            }
            $anonymised = ($matches === 0);
        }

        if (empty($map) || $anonymised) {
            $map = $this->getPersonalDrivesPerUser($users, 500);
        }
        return $map;
    }

    /**
     * Storage overview for ALL provisioned OneDrives (via getProvisionedDriveMap),
     * mapped to the index table shape and joined with real display names.
     * Returns [] only if no data is obtainable at all.
     */
    public function getStorageOverview(array $users): array
    {
        $nameByUpn = [];
        foreach ($users as $u) {
            $upn = strtolower($u['userPrincipalName'] ?? '');
            if ($upn === '') continue;
            $nameByUpn[$upn] = $u['displayName'] ?? $upn;
        }

        $map = $this->getProvisionedDriveMap($users);
        if (empty($map)) return [];

        $result = [];
        foreach ($map as $upn => $d) {
            $used  = (int)($d['storageUsed'] ?? 0);
            $total = (int)($d['storageAllocated'] ?? 0);
            $result[] = [
                'user'      => $nameByUpn[$upn] ?? $upn,
                'upn'       => $upn,
                'used'      => $used,
                'total'     => $total,
                'remaining' => max(0, $total - $used),
                'state'     => ($total > 0 && $used / $total >= 0.9) ? 'warning' : 'normal',
            ];
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
     * Per-user fallback for getPersonalDrivesReport() when the report API returns empty.
     * Checks up to $limit users individually via /users/{id}/drive.
     * Results are cached for 30 minutes (cache key: od_personal_drives).
     *
     * Returns the same UPN-keyed map as getPersonalDrivesReport().
     *
     * @param  array $allUsers  Full user list from UsersService::getAll()
     * @param  int   $limit     Max users to check (default 150 to keep response time < 30s)
     * @return array<string, array>
     */
    public function getPersonalDrivesPerUser(array $allUsers, int $limit = 150): array
    {
        $cacheKey = 'od_personal_drives';
        $cached   = $this->graph->getCache()->get($cacheKey);
        if (!empty($cached)) {
            return $cached;
        }

        $map    = [];
        $sample = array_slice($allUsers, 0, $limit);

        foreach ($sample as $user) {
            $upn = strtolower($user['userPrincipalName'] ?? '');
            $id  = $user['id'] ?? '';
            if ($upn === '' || $id === '') {
                continue;
            }
            try {
                $drive = $this->graph->get(
                    "/users/{$id}/drive",
                    ['$select' => 'id,webUrl,quota'],
                    null,
                    0
                );
                if (!empty($drive['id'])) {
                    $quota = $drive['quota'] ?? [];
                    $map[$upn] = [
                        'storageUsed'      => (int)($quota['used']      ?? 0),
                        'storageAllocated' => (int)($quota['total']     ?? 0),
                        'fileCount'        => 0,
                        'lastActivity'     => null,
                        'siteUrl'          => $drive['webUrl'] ?? null,
                    ];
                }
            } catch (\Throwable) {
                // Drive not provisioned or inaccessible — skip
            }
        }

        if (!empty($map)) {
            $this->graph->getCache()->set($cacheKey, $map, 1800);
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
