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
        ['data' => $alerts, 'diag' => $diag] = \App\Graph\GraphErrorTranslator::guard(
            fn() => $service->getAlerts(),
            'SecurityAlert.Read.All'
        );
        $alerts ??= [];
        $stats   = $service->getStats($alerts);

        View::render('defenderalerts/index', [
            'pageTitle' => t('Defender Sicherheitswarnungen'),
            'alerts'    => $alerts,
            'stats'     => $stats,
            'service'   => $service,
            'diag'      => $diag,
            'flash'     => Session::getFlash('success'),
            'error'     => Session::getFlash('error'),
        ]);
    }

    public function resolve(string $alertId): void
    {
        LocalAuth::requireAdmin();

        try {
            app_service(DefenderAlertsService::class)->updateAlertStatus($alertId, 'resolved');
            Session::flash('success', t('Warnung wurde als gelöst markiert.'));
        } catch (\Throwable $e) {
            Session::flash('error', t('Fehler beim Aktualisieren der Warnung: :msg', ['msg' => $e->getMessage()]));
        }

        Redirect::to('/defenderalerts');
    }
}
