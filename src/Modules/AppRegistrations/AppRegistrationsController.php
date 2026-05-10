<?php

namespace App\Modules\AppRegistrations;

use App\Auth\LocalAuth;
use App\Core\View;

class AppRegistrationsController
{
    public function index(): void
    {
        LocalAuth::require();

        $service          = app_service(AppRegistrationsService::class);
        $apps             = $service->getApplications();
        $servicePrincipals = $service->getServicePrincipals();
        $highRiskApps     = $service->getHighRiskApps($apps);

        // Build high-risk app ID set for quick lookup in view
        $highRiskAppIds = [];
        foreach ($highRiskApps as $entry) {
            $highRiskAppIds[$entry['app']['id']] = $entry['riskReasons'];
        }

        // Apps created in last 30 days
        $cutoff = strtotime('-30 days');
        $recentApps = array_filter($apps, function ($a) use ($cutoff) {
            $created = $a['createdDateTime'] ?? null;
            return $created && strtotime($created) >= $cutoff;
        });

        View::render('appregistrations/index', [
            'pageTitle'          => 'App-Registrierungen',
            'apps'               => $apps,
            'servicePrincipals'  => $servicePrincipals,
            'highRiskApps'       => $highRiskApps,
            'highRiskAppIds'     => $highRiskAppIds,
            'recentAppsCount'    => count($recentApps),
            'service'            => $service,
        ]);
    }
}
