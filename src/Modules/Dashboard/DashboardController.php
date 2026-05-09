<?php

namespace App\Modules\Dashboard;

use App\Auth\LocalAuth;
use App\Core\View;

class DashboardController
{
    public function index(): void
    {
        LocalAuth::require();

        $service = app_service(DashboardService::class);

        if (isset($_GET['refresh'])) {
            app_graph()->getCache()->flush();
        }

        $metrics  = $service->getMetrics();
        $licenses = $service->getLicenseSummary();

        View::render('dashboard/index', [
            'pageTitle' => 'Dashboard',
            'metrics'   => $metrics,
            'licenses'  => $licenses,
        ]);
    }
}
