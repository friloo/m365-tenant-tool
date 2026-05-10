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
            $cache->forget('adoption_active_users');
            $cache->forget('adoption_email_counts');
            $cache->forget('adoption_teams_counts');
            $cache->forget('adoption_onedrive_counts');
            $cache->forget('adoption_skus');
        }

        try {
            $activeUsers = $service->getActiveUserSummary();
        } catch (\Throwable) {
            $activeUsers = [];
        }

        try {
            $emailCounts = $service->getEmailActivityCounts();
        } catch (\Throwable) {
            $emailCounts = [];
        }

        try {
            $teamsCounts = $service->getTeamsActivityCounts();
        } catch (\Throwable) {
            $teamsCounts = [];
        }

        try {
            $onedriveCounts = $service->getOneDriveActivityCounts();
        } catch (\Throwable) {
            $onedriveCounts = [];
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
        ]);
    }
}
