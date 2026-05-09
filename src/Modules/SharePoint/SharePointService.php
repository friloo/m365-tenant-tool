<?php

namespace App\Modules\SharePoint;

use App\Graph\GraphClient;

class SharePointService
{
    public function __construct(private GraphClient $graph) {}

    public function getAllSites(): array
    {
        return $this->graph->paginate(
            '/sites',
            ['search' => '*', '$select' => 'id,displayName,webUrl,description,createdDateTime'],
            30,
            'sp_sites',
            1800
        );
    }

    public function getSite(string $siteId): array
    {
        return $this->graph->get(
            "/sites/{$siteId}",
            [],
            "sp_site_{$siteId}",
            900
        );
    }

    public function getSiteDrives(string $siteId): array
    {
        try {
            return $this->graph->paginate(
                "/sites/{$siteId}/drives",
                ['$select' => 'id,name,description,quota,driveType'],
                10,
                "sp_drives_{$siteId}",
                900
            );
        } catch (\Throwable) { return []; }
    }

    public function getUsageReport(): array
    {
        try {
            return $this->graph->get(
                '/reports/getSharePointSiteUsageDetail(period=\'D30\')',
                [],
                'sp_usage',
                3600
            );
        } catch (\Throwable) { return []; }
    }
}
