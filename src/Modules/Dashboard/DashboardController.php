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
        $security = $service->getSecurityStatus();
        $extended = $service->getExtendedStats();

        // Persist today's KPI readings for the sparkline trend history.
        // Idempotent — calling multiple times per day overwrites the same row.
        MetricHistoryService::recordMany(array_merge($metrics, [
            'guests'             => $extended['guests']             ?? null,
            'teams_count'        => $extended['teams_count']        ?? null,
            'admin_assignments'  => $extended['admin_assignments']  ?? null,
            'secure_score'       => $extended['secure_score']       ?? null,
            'service_incidents'  => $extended['service_incidents']  ?? null,
            'msg_center_count'   => $extended['msg_center_count']   ?? null,
        ]));

        View::render('dashboard/index', [
            'pageTitle' => 'Dashboard',
            'metrics'   => $metrics,
            'licenses'  => $licenses,
            'security'  => $security,
            'extended'  => $extended,
        ]);
    }
}
