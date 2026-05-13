<?php

namespace App\Modules\Adoption;

use App\Auth\LocalAuth;
use App\Core\View;

class AdoptionController
{
    public function index(): void
    {
        LocalAuth::require();

        /** @var AdoptionService $service */
        $service = app_service(AdoptionService::class);

        // Bust caches when ?refresh=1 is passed
        if (isset($_GET['refresh'])) {
            $cache = app_graph()->getCache();
            $cache->forget('adoption_active_user_counts');
            $cache->forget('adoption_active_users');
            $cache->forget('adoption_email_counts');
            $cache->forget('adoption_teams_counts');
            $cache->forget('adoption_onedrive_counts');
            $cache->forget('adoption_skus');
        }

        $graph = app_graph();
        $diagnoseFor = function () use ($graph): ?array {
            return \App\Graph\GraphErrorTranslator::translate($graph->getLastError(), 'Reports.Read.All');
        };

        // Wir rufen jeden Endpoint und merken uns den konkreten Fehlergrund pro
        // Datenbereich — keine "Berechtigung fehlt möglicherweise"-Texte mehr.
        $activeDiag = $emailDiag = $teamsDiag = $oneDriveDiag = null;

        try {
            $activeUsers = $service->getActiveUserSummary();
            if (empty($activeUsers) || array_sum($activeUsers) === 0) $activeDiag = $diagnoseFor();
        } catch (\Throwable $e) {
            $activeUsers = [];
            $activeDiag  = \App\Graph\GraphErrorTranslator::fromThrowable($e, 'Reports.Read.All');
        }

        try {
            $emailCounts = $service->getEmailActivityCounts();
            if (empty($emailCounts)) $emailDiag = $diagnoseFor();
        } catch (\Throwable $e) {
            $emailCounts = [];
            $emailDiag   = \App\Graph\GraphErrorTranslator::fromThrowable($e, 'Reports.Read.All');
        }

        try {
            $teamsCounts = $service->getTeamsActivityCounts();
            if (empty($teamsCounts)) $teamsDiag = $diagnoseFor();
        } catch (\Throwable $e) {
            $teamsCounts = [];
            $teamsDiag   = \App\Graph\GraphErrorTranslator::fromThrowable($e, 'Reports.Read.All');
        }

        try {
            $onedriveCounts = $service->getOneDriveActivityCounts();
            if (empty($onedriveCounts)) $oneDriveDiag = $diagnoseFor();
        } catch (\Throwable $e) {
            $onedriveCounts = [];
            $oneDriveDiag   = \App\Graph\GraphErrorTranslator::fromThrowable($e, 'Reports.Read.All');
        }

        try {
            $skuTotals = $service->getSubscribedSkuTotals();
        } catch (\Throwable) {
            $skuTotals = ['consumed' => 0, 'total' => 0];
        }

        View::render('adoption/index', [
            'pageTitle'      => 'Adoption Dashboard',
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
