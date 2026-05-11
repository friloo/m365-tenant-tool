<?php

namespace App\Modules\TeamsPolicies;

use App\Auth\LocalAuth;
use App\Core\View;

class TeamsPoliciesController
{
    public function index(): void
    {
        LocalAuth::require();

        if (isset($_GET['refresh'])) {
            $cache = app_graph()->getCache();
            foreach (['teams_app_settings','teams_teamwork','teams_org_apps','teams_group_list'] as $k) {
                $cache->forget($k);
            }
        }

        /** @var TeamsPoliciesService $service */
        $service   = app_service(TeamsPoliciesService::class);
        $appSettings = $service->getAppPermissionPolicies();
        $teamwork    = $service->getTeamworkSettings();
        $orgApps     = $service->getOrgInstalledApps();
        $teamStats   = $service->getTeamStats();

        View::render('teamspolicies/index', [
            'pageTitle'   => 'Teams-Übersicht & Richtlinien',
            'appSettings' => $appSettings,
            'teamwork'    => $teamwork,
            'orgApps'     => $orgApps,
            'teamStats'   => $teamStats,
            'lastError'   => $service->getLastError(),
        ]);
    }
}
