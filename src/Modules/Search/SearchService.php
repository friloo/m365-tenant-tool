<?php

namespace App\Modules\Search;

use App\Graph\GraphClient;

class SearchService
{
    public function __construct(private GraphClient $graph) {}

    public function search(string $query): array
    {
        if (mb_strlen($query) < 2) {
            return [];
        }

        // Escape query for OData string literals
        $escaped = addslashes(str_replace("'", "''", $query));

        $results = [];

        // ── Users ────────────────────────────────────────────────────
        $users = $this->graph->paginate('/users', [
            '$select' => 'id,displayName,userPrincipalName,accountEnabled',
            '$top'    => '999',
        ], 50, 'users_all', 900);

        if (!empty($users)) {
            // Filter from cache client-side
            $ql = mb_strtolower($query);
            $matched = array_filter($users, function ($u) use ($ql) {
                return stripos($u['displayName'] ?? '', $ql) !== false
                    || stripos($u['userPrincipalName'] ?? '', $ql) !== false;
            });
            $matched = array_values($matched);
        } else {
            // Fall back to API query
            $matched = $this->graph->paginate('/users', [
                '$filter' => "startswith(displayName,'{$escaped}') or startswith(userPrincipalName,'{$escaped}')",
                '$select' => 'id,displayName,userPrincipalName,accountEnabled',
                '$top'    => '8',
            ], 1);
        }

        $count = 0;
        foreach ($matched as $u) {
            if ($count >= 8) break;
            $enabled = $u['accountEnabled'] ?? true;
            $results[] = [
                'type'     => 'user',
                'icon'     => 'person',
                'label'    => $u['displayName'] ?? $u['userPrincipalName'] ?? '',
                'subtitle' => $u['userPrincipalName'] ?? '',
                'url'      => '/users/' . ($u['id'] ?? ''),
                'enabled'  => $enabled ? true : false,
            ];
            $count++;
        }

        // ── Groups ───────────────────────────────────────────────────
        $cachedGroups = $this->graph->getCache()->get('teams_group_list');
        if (!empty($cachedGroups)) {
            // teams_group_list is stored as a raw API response: ['value' => [...]]
            $groupList = $cachedGroups['value'] ?? (isset($cachedGroups[0]) ? $cachedGroups : []);
            $ql = mb_strtolower($query);
            $groupMatched = array_filter($groupList, function ($g) use ($ql) {
                return stripos($g['displayName'] ?? '', $ql) !== false;
            });
            $groupMatched = array_values($groupMatched);
        } else {
            $groupMatched = $this->graph->paginate('/groups', [
                '$filter' => "startswith(displayName,'{$escaped}')",
                '$select' => 'id,displayName',
                '$top'    => '5',
            ], 1);
        }

        $count = 0;
        foreach ($groupMatched as $g) {
            if ($count >= 5) break;
            $results[] = [
                'type'     => 'group',
                'icon'     => 'people',
                'label'    => $g['displayName'] ?? '',
                'subtitle' => t('Gruppe'),
                'url'      => '/groups/' . ($g['id'] ?? ''),
                'enabled'  => null,
            ];
            $count++;
        }

        // ── Devices ──────────────────────────────────────────────────
        $devices = $this->graph->paginate('/deviceManagement/managedDevices', [
            '$filter' => "contains(deviceName,'{$escaped}')",
            '$select' => 'id,deviceName,operatingSystem',
            '$top'    => '5',
        ], 1);

        $count = 0;
        foreach ($devices as $d) {
            if ($count >= 5) break;
            $results[] = [
                'type'     => 'device',
                'icon'     => 'laptop',
                'label'    => $d['deviceName'] ?? '',
                'subtitle' => $d['operatingSystem'] ?? t('Gerät'),
                'url'      => '/devices/' . ($d['id'] ?? ''),
                'enabled'  => null,
            ];
            $count++;
        }

        return array_slice($results, 0, 15);
    }
}
