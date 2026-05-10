<?php

namespace App\Modules\RiskySignIns;

use App\Auth\LocalAuth;
use App\Core\Redirect;
use App\Core\Session;
use App\Core\View;

class RiskySignInsController
{
    public function index(): void
    {
        LocalAuth::require();

        $service    = app_service(RiskySignInsService::class);
        $riskyUsers = $service->getRiskyUsers();
        $detections = $service->getRiskyDetections(50);
        $signIns    = $service->getRecentRiskySignIns(168);
        $stats      = $service->getStats($detections);

        // Count high-risk users
        $highRiskCount   = count(array_filter($riskyUsers, fn($u) => strtolower($u['riskLevel'] ?? '') === 'high'));
        $mediumRiskCount = count(array_filter($riskyUsers, fn($u) => strtolower($u['riskLevel'] ?? '') === 'medium'));

        View::render('riskysignins/index', [
            'pageTitle'       => 'Risiko-Anmeldungen',
            'riskyUsers'      => $riskyUsers,
            'detections'      => $detections,
            'signIns'         => $signIns,
            'stats'           => $stats,
            'highRiskCount'   => $highRiskCount,
            'mediumRiskCount' => $mediumRiskCount,
            'service'         => $service,
            'flash'           => Session::getFlash('success'),
            'error'           => Session::getFlash('error'),
        ]);
    }

    public function confirmCompromised(string $userId): void
    {
        LocalAuth::requireAdmin();
        try {
            app_service(RiskySignInsService::class)->confirmCompromised($userId);
            Session::flash('success', 'Benutzer als kompromittiert bestätigt.');
        } catch (\Throwable $e) {
            Session::flash('error', 'Fehler: ' . $e->getMessage());
        }
        Redirect::to('/riskysignins');
    }

    public function dismissRisk(string $userId): void
    {
        LocalAuth::requireAdmin();
        try {
            app_service(RiskySignInsService::class)->dismissRisk($userId);
            Session::flash('success', 'Risiko für Benutzer zurückgesetzt.');
        } catch (\Throwable $e) {
            Session::flash('error', 'Fehler: ' . $e->getMessage());
        }
        Redirect::to('/riskysignins');
    }
}
