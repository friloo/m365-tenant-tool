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
        ['data' => $rows, 'diag' => $diag] = \App\Graph\GraphErrorTranslator::guard(
            fn() => $service->getUsageReport(),
            'Reports.Read.All'
        );
        $rows ??= [];
        $stats = $service->getStats($rows);

        View::render('teamsusage/index', [
            'pageTitle' => 'Teams-Nutzung',
            'rows'      => $rows,
            'stats'     => $stats,
            'diag'      => $diag,
        ]);
    }
}
