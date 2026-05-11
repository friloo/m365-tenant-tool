<?php

namespace App\Modules\ExchangeMigration;

use App\Auth\LocalAuth;
use App\Core\View;

class ExchangeMigrationController
{
    public function index(): void
    {
        LocalAuth::require();

        /** @var ExchangeMigrationService $service */
        $service = app_service(ExchangeMigrationService::class);

        $report = $service->getFullReport();

        View::render('exchangemigration/index', [
            'pageTitle' => 'Exchange Online Migration Readiness',
            'report'    => $report,
        ]);
    }
}
