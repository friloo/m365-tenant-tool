<?php

namespace App\Modules\TeamsGovernance;

use App\Auth\LocalAuth;
use App\Core\View;

class TeamsGovernanceController
{
    public function index(): void
    {
        LocalAuth::require();

        $days    = max(1, (int)($_GET['days'] ?? 90));
        $service = app_service(TeamsGovernanceService::class);
        $teams   = $service->getAll($days);
        $summary = $service->getSummary($teams);

        View::render('teamsgovernance/index', [
            'pageTitle' => 'Teams Governance',
            'teams'     => $teams,
            'summary'   => $summary,
            'days'      => $days,
        ]);
    }
}
