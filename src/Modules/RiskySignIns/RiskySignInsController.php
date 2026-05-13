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
        $usersErr   = $service->getLastError();
        $detections = $service->getRiskyDetections(50);
        $detectionsErr = $service->getLastError();
        $signIns    = $service->getRecentRiskySignIns(168);
        $stats      = $service->getStats($detections);

        // Count high-risk users
        $highRiskCount   = count(array_filter($riskyUsers, fn($u) => strtolower($u['riskLevel'] ?? '') === 'high'));
        $mediumRiskCount = count(array_filter($riskyUsers, fn($u) => strtolower($u['riskLevel'] ?? '') === 'medium'));

        // Surface why the list might be empty so the operator doesn't think
        // the tenant is safe when really a permission or licence is missing.
        $diagnostic = null;
        $err = $usersErr ?: $detectionsErr;
        if ($err && ($err['status'] ?? 0) === 403) {
            $diagnostic = 'Microsoft Graph hat die Anfrage abgelehnt (403). '
                . 'Bitte sicherstellen, dass die App-Registrierung die Berechtigung '
                . 'IdentityRiskyUser.Read.All und IdentityRiskEvent.Read.All hat. '
                . 'Details: ' . ($err['message'] ?? '');
        } elseif (empty($riskyUsers) && empty($detections) && empty($signIns)) {
            $diagnostic = 'Es wurden keine Risiko-Daten gefunden. '
                . 'Hinweis: Microsoft Entra ID Protection erfordert eine Azure AD Premium P2-Lizenz — '
                . 'ohne P2 sind die Endpunkte zwar erreichbar, liefern aber leere Listen.';
        }

        View::render('riskysignins/index', [
            'pageTitle'       => 'Risiko-Anmeldungen',
            'riskyUsers'      => $riskyUsers,
            'detections'      => $detections,
            'signIns'         => $signIns,
            'stats'           => $stats,
            'highRiskCount'   => $highRiskCount,
            'mediumRiskCount' => $mediumRiskCount,
            'service'         => $service,
            'diagnostic'      => $diagnostic,
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
