<?php

namespace App\Modules\MailFlow;

use App\Auth\LocalAuth;
use App\Core\View;

class MailFlowController
{
    public function index(): void
    {
        LocalAuth::require();

        // Bust caches when ?refresh=1 is passed
        if (isset($_GET['refresh'])) {
            $cache = app_graph()->getCache();
            $cache->forget('mailflow_health');
            $cache->forget('mailflow_issues');
            $cache->forget('mailflow_defender_alerts');
        }

        $service = app_service(MailFlowService::class);

        $healthOverview = $service->getMailServiceHealth();
        $activeIssues   = $service->getActiveMailIssues();
        $defenderAlerts = $service->getMailDefenderAlerts();
        $adminLinks     = $service->getAntiSpamLinks();

        View::render('mailflow/index', [
            'pageTitle'      => 'Mail Flow & Schutz',
            'healthOverview' => $healthOverview,
            'activeIssues'   => $activeIssues,
            'defenderAlerts' => $defenderAlerts,
            'adminLinks'     => $adminLinks,
        ]);
    }
}
