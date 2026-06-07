<?php

namespace App\Modules\UsageReports;

use App\Auth\LocalAuth;
use App\Core\View;
use App\Modules\Adoption\AdoptionService;

/**
 * Unified "Nutzung & Adoption" page. Usage reports and the adoption dashboard
 * drew on overlapping Graph activity reports and lived as two separate modules;
 * they are now two tabs on one page (the existing views are reused as panes).
 */
class UsageReportsController
{
    public function index(): void
    {
        LocalAuth::require();

        if (isset($_GET['refresh'])) {
            $cache = app_graph()->getCache();
            foreach ([
                'adoption_active_user_counts', 'adoption_active_users', 'adoption_email_counts',
                'adoption_teams_counts', 'adoption_onedrive_counts', 'adoption_skus',
            ] as $key) {
                $cache->forget($key);
            }
        }

        // ── Usage reports tab ────────────────────────────────────────────
        $allowed = [7, 30, 90];
        $period  = (int)($_GET['period'] ?? 30);
        if (!in_array($period, $allowed, true)) $period = 30;

        $service = app_service(UsageReportsService::class);
        $diag    = null;
        $empty   = [
            'period' => $period, 'exchange' => 0, 'oneDrive' => 0, 'sharePoint' => 0, 'teams' => 0,
            'emailsSent' => 0, 'emailsReceived' => 0, 'teamsMessages' => 0, 'teamsMeetings' => 0, 'teamsCalls' => 0,
        ];
        try {
            $summary = $service->getSummary($period);
            $hasAny  = array_sum(array_filter($summary, 'is_int')) > 0;
            if (!$hasAny) {
                $diag = \App\Graph\GraphErrorTranslator::translate(app_graph()->getLastError(), 'Reports.Read.All');
            }
        } catch (\Throwable $e) {
            $summary = $empty;
            $diag    = \App\Graph\GraphErrorTranslator::fromThrowable($e, 'Reports.Read.All');
        }

        // ── Adoption tab ─────────────────────────────────────────────────
        $adoption = app_service(AdoptionService::class);
        $graph    = app_graph();
        $diagnoseFor = fn() => \App\Graph\GraphErrorTranslator::translate($graph->getLastError(), 'Reports.Read.All');
        $activeDiag = $emailDiag = $teamsDiag = $oneDriveDiag = null;

        try {
            $activeUsers = $adoption->getActiveUserSummary();
            if (empty($activeUsers) || array_sum($activeUsers) === 0) $activeDiag = $diagnoseFor();
        } catch (\Throwable $e) { $activeUsers = []; $activeDiag = \App\Graph\GraphErrorTranslator::fromThrowable($e, 'Reports.Read.All'); }

        try {
            $emailCounts = $adoption->getEmailActivityCounts();
            if (empty($emailCounts)) $emailDiag = $diagnoseFor();
        } catch (\Throwable $e) { $emailCounts = []; $emailDiag = \App\Graph\GraphErrorTranslator::fromThrowable($e, 'Reports.Read.All'); }

        try {
            $teamsCounts = $adoption->getTeamsActivityCounts();
            if (empty($teamsCounts)) $teamsDiag = $diagnoseFor();
        } catch (\Throwable $e) { $teamsCounts = []; $teamsDiag = \App\Graph\GraphErrorTranslator::fromThrowable($e, 'Reports.Read.All'); }

        try {
            $onedriveCounts = $adoption->getOneDriveActivityCounts();
            if (empty($onedriveCounts)) $oneDriveDiag = $diagnoseFor();
        } catch (\Throwable $e) { $onedriveCounts = []; $oneDriveDiag = \App\Graph\GraphErrorTranslator::fromThrowable($e, 'Reports.Read.All'); }

        try {
            $skuTotals = $adoption->getSubscribedSkuTotals();
        } catch (\Throwable) { $skuTotals = ['consumed' => 0, 'total' => 0]; }

        View::render('usagereports/combined', [
            'pageTitle'      => 'Nutzung & Adoption',
            // usage tab
            'summary'        => $summary,
            'period'         => $period,
            'diag'           => $diag,
            // adoption tab
            'activeUsers'    => $activeUsers,
            'emailCounts'    => $emailCounts,
            'teamsCounts'    => $teamsCounts,
            'onedriveCounts' => $onedriveCounts,
            'skuTotals'      => $skuTotals,
            'activeDiag'     => $activeDiag,
            'emailDiag'      => $emailDiag,
            'teamsDiag'      => $teamsDiag,
            'oneDriveDiag'   => $oneDriveDiag,
        ]);
    }
}
