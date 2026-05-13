<?php

namespace App\Modules\SharePoint;

use App\Auth\LocalAuth;
use App\Core\Session;
use App\Core\View;

class SharePointController
{
    public function index(): void
    {
        LocalAuth::require();
        $service = app_service(SharePointService::class);

        $sites = []; $loadErr = null;
        try { $sites = $service->getAllSites(); }
        catch (\Throwable $e) { $loadErr = 'Sites nicht ladbar: ' . $e->getMessage(); error_log('SharePoint index: ' . $e->getMessage()); }

        View::render('sharepoint/index', [
            'pageTitle' => 'SharePoint',
            'sites'     => $sites,
            'error'     => Session::getFlash('error') ?: $loadErr,
        ]);
    }

    public function site(string $siteId): void
    {
        LocalAuth::require();
        $service = app_service(SharePointService::class);

        $site = null; $drives = []; $loadErr = null;
        try { $site = $service->getSite($siteId); }
        catch (\Throwable $e) { $loadErr = 'Site nicht ladbar: ' . $e->getMessage(); error_log('SharePoint site: ' . $e->getMessage()); }
        try { $drives = $service->getSiteDrives($siteId); }
        catch (\Throwable $e) { error_log('SharePoint site drives: ' . $e->getMessage()); }

        View::render('sharepoint/site', [
            'pageTitle' => $site['displayName'] ?? 'Site',
            'site'      => $site,
            'drives'    => $drives,
            'error'     => Session::getFlash('error') ?: $loadErr,
        ]);
    }
}
