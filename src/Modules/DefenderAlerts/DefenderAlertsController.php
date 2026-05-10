<?php

namespace App\Modules\DefenderAlerts;

use App\Auth\LocalAuth;
use App\Core\Redirect;
use App\Core\Session;
use App\Core\View;

class DefenderAlertsController
{
    public function index(): void
    {
        LocalAuth::require();

        $service = app_service(DefenderAlertsService::class);
        $alerts  = $service->getAlerts();
        $stats   = $service->getStats($alerts);

        View::render('defenderalerts/index', [
            'pageTitle' => 'Defender Sicherheitswarnungen',
            'alerts'    => $alerts,
            'stats'     => $stats,
            'service'   => $service,
            'flash'     => Session::getFlash('success'),
            'error'     => Session::getFlash('error'),
        ]);
    }

    public function resolve(string $alertId): void
    {
        LocalAuth::requireAdmin();

        try {
            app_service(DefenderAlertsService::class)->updateAlertStatus($alertId, 'resolved');
            Session::flash('success', 'Warnung wurde als gelöst markiert.');
        } catch (\Throwable $e) {
            Session::flash('error', 'Fehler beim Aktualisieren der Warnung: ' . $e->getMessage());
        }

        Redirect::to('/defenderalerts');
    }
}
