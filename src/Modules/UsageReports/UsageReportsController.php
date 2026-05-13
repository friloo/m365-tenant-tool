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
        $diag    = null;
        $empty   = [
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

        try {
            $summary = $service->getSummary($period);
            // Wenn alle Werte 0 sind, war wahrscheinlich ein Graph-Fehler im Spiel
            // (siehe GraphClient::getLastError) — diagnostizieren statt schweigen.
            $hasAny = array_sum(array_filter($summary, 'is_int')) > 0;
            if (!$hasAny) {
                $diag = \App\Graph\GraphErrorTranslator::translate(app_graph()->getLastError(), 'Reports.Read.All');
            }
        } catch (\Throwable $e) {
            $summary = $empty;
            $diag    = \App\Graph\GraphErrorTranslator::fromThrowable($e, 'Reports.Read.All');
        }

        View::render('usagereports/index', [
            'pageTitle' => 'Nutzungsberichte',
            'summary'   => $summary,
            'period'    => $period,
            'diag'      => $diag,
        ]);
    }
}
