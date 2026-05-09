<?php

namespace App\Modules\SharePoint;

use App\Auth\LocalAuth;
use App\Core\View;

class SharePointController
{
    public function index(): void
    {
        LocalAuth::require();
        $service = app_service(SharePointService::class);
        $sites   = $service->getAllSites();

        View::render('sharepoint/index', [
            'pageTitle' => 'SharePoint',
            'sites'     => $sites,
        ]);
    }

    public function site(string $siteId): void
    {
        LocalAuth::require();
        $service = app_service(SharePointService::class);
        $site    = $service->getSite($siteId);
        $drives  = $service->getSiteDrives($siteId);

        View::render('sharepoint/site', [
            'pageTitle' => $site['displayName'] ?? 'Site',
            'site'      => $site,
            'drives'    => $drives,
        ]);
    }
}
