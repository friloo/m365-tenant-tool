<?php

namespace App\Modules\Sharing;

use App\Graph\GraphClient;

class SharingService
{
    public function __construct(private GraphClient $graph) {}

    public function getExternalShares(): array
    {
        $shares = [];

        // SharePoint external sharing report
        try {
            $sites = $this->graph->paginate(
                '/sites',
                ['search' => '*', '$select' => 'id,displayName,webUrl'],
                20,
                'sharing_sites',
                1800
            );

            foreach (array_slice($sites, 0, 20) as $site) {
                try {
                    $drives = $this->graph->paginate(
                        "/sites/{$site['id']}/drives",
                        ['$select' => 'id,name'],
                        5,
                        "sharing_drives_{$site['id']}",
                        1800
                    );

                    foreach (array_slice($drives, 0, 3) as $drive) {
                        try {
                            $sharedItems = $this->graph->paginate(
                                "/drives/{$drive['id']}/root/search(q='')",
                                ['$select' => 'id,name,webUrl,shared,createdBy,lastModifiedDateTime', '$filter' => "shared ne null", '$top' => '50'],
                                3,
                                "sharing_items_{$drive['id']}",
                                1800
                            );

                            foreach ($sharedItems as $item) {
                                if (empty($item['shared'])) continue;
                                $scope = $item['shared']['scope'] ?? 'unknown';
                                $shares[] = [
                                    'type'     => 'SharePoint',
                                    'site'     => $site['displayName'] ?? '',
                                    'name'     => $item['name'] ?? '',
                                    'url'      => $item['webUrl'] ?? '',
                                    'scope'    => $scope,
                                    'owner'    => $item['createdBy']['user']['displayName'] ?? '',
                                    'modified' => $item['lastModifiedDateTime'] ?? '',
                                ];
                            }
                        } catch (\Throwable) { continue; }
                    }
                } catch (\Throwable) { continue; }
            }
        } catch (\Throwable) {}

        return $shares;
    }

    public function getSharingSummary(): array
    {
        $shares = $this->getExternalShares();
        $byType = ['organization' => 0, 'users' => 0, 'anonymous' => 0, 'unknown' => 0];
        foreach ($shares as $s) {
            $scope = $s['scope'];
            $byType[$scope] = ($byType[$scope] ?? 0) + 1;
        }
        return [
            'total'  => count($shares),
            'byType' => $byType,
            'items'  => $shares,
        ];
    }
}
