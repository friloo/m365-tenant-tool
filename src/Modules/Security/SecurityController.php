<?php

namespace App\Modules\Security;

use App\Auth\LocalAuth;
use App\Core\Redirect;
use App\Core\Session;
use App\Core\View;

class SecurityController
{
    /**
     * The standalone "Sicherheit" dashboard only aggregated what the dedicated
     * modules already show (Conditional Access, Risiko-Anmeldungen, MFA-Methoden,
     * Sign-in-Log) and duplicated the CA toggle. It was folded away to leave a
     * clear pair: Security Posture (status) + Security Center (hardening).
     */
    public function index(): void
    {
        LocalAuth::require();
        Redirect::to('/hardening');
    }

    public function toggleCaPolicy(string $policyId): void
    {
        LocalAuth::requireAdmin();

        $allowedStates = ['enabled', 'disabled', 'enabledForReportingButNotEnforced'];
        $newState = trim($_POST['state'] ?? '');

        if (!in_array($newState, $allowedStates, true)) {
            Session::flash('error', t('Ungültiger Status.'));
            Redirect::to('/hardening');
            return;
        }

        try {
            $service = app_service(SecurityService::class);
            $service->toggleCaPolicy($policyId, $newState);
            Session::flash('success', t('Richtlinie aktualisiert.'));
        } catch (\Throwable $e) {
            Session::flash('error', t('Fehler: :msg', ['msg' => $e->getMessage()]));
        }

        Redirect::to('/hardening');
    }
}
