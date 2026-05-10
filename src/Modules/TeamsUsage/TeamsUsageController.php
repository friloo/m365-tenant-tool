<?php

namespace App\Modules\TeamsUsage;

use App\Auth\LocalAuth;
use App\Core\View;

class TeamsUsageController
{
    public function index(): void
    {
        LocalAuth::require();

        $service = app_service(TeamsUsageService::class);
        $rows    = $service->getUsageReport();
        $stats   = $service->getStats($rows);

        View::render('teamsusage/index', [
            'pageTitle' => 'Teams-Nutzung',
            'rows'      => $rows,
            'stats'     => $stats,
        ]);
    }
}
