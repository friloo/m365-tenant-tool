<?php

namespace App\Modules\ServiceHealth;

use App\Auth\LocalAuth;
use App\Core\View;
use App\Core\Session;

class ServiceHealthController
{
    public function index(): void
    {
        LocalAuth::require();

        $service  = app_service(ServiceHealthService::class);
        ['data' => $overview, 'diag' => $diag] = \App\Graph\GraphErrorTranslator::guard(
            fn() => $service->getOverview(),
            'ServiceHealth.Read.All'
        );
        $overview ??= [];
        $issues   = $service->getActiveIssues();
        $messages = $service->getRecentMessages(10);

        // Determine overall health banner
        $allHealthy = empty($issues) && !in_array(
            true,
            array_map(
                fn($s) => !in_array($s['status'], ['serviceOperational', 'serviceDegradationMitigated']),
                $overview
            )
        );

        View::render('servicehealth/index', [
            'pageTitle'  => 'Service-Status',
            'overview'   => $overview,
            'issues'     => $issues,
            'messages'   => $messages,
            'allHealthy' => $allHealthy,
            'diag'       => $diag,
        ]);
    }
}
