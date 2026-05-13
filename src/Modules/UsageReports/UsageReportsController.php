<?php

namespace App\Modules\UsageReports;

use App\Auth\LocalAuth;
use App\Core\View;

class UsageReportsController
{
    public function index(): void
    {
        LocalAuth::require();

        $allowed = [7, 30, 90];
        $period  = (int)($_GET['period'] ?? 30);
        if (!in_array($period, $allowed, true)) {
            $period = 30;
        }

        $service = app_service(UsageReportsService::class);

        try {
            $summary = $service->getSummary($period);
        } catch (\Throwable) {
            $summary = [
                'period'         => $period,
                'exchange'       => 0,
                'oneDrive'       => 0,
                'sharePoint'     => 0,
                'teams'          => 0,
                'emailsSent'     => 0,
                'emailsReceived' => 0,
                'teamsMessages'  => 0,
                'teamsMeetings'  => 0,
                'teamsCalls'     => 0,
            ];
        }

        View::render('usagereports/index', [
            'pageTitle' => 'Nutzungsberichte',
            'summary'   => $summary,
            'period'    => $period,
        ]);
    }
}
