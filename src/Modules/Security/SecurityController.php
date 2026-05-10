<?php

namespace App\Modules\Security;

use App\Auth\LocalAuth;
use App\Core\Redirect;
use App\Core\Session;
use App\Core\View;

class SecurityController
{
    public function index(): void
    {
        LocalAuth::require();
        $service    = app_service(SecurityService::class);
        $policies   = $service->getConditionalAccessPolicies();
        $riskyUsers = $service->getRiskyUsers();
        $mfa        = $service->getMfaSummary();
        $signIns    = $service->getRecentSignIns(30);

        View::render('security/index', [
            'pageTitle'  => 'Sicherheit',
            'policies'   => $policies,
            'riskyUsers' => $riskyUsers,
            'mfa'        => $mfa,
            'signIns'    => $signIns,
        ]);
    }

    public function toggleCaPolicy(string $policyId): void
    {
        LocalAuth::requireAdmin();

        $allowedStates = ['enabled', 'disabled', 'enabledForReportingButNotEnforced'];
        $newState = trim($_POST['state'] ?? '');

        if (!in_array($newState, $allowedStates, true)) {
            Session::flash('error', 'Ungültiger Status.');
            Redirect::to('/security');
            return;
        }

        try {
            $service = app_service(SecurityService::class);
            $service->toggleCaPolicy($policyId, $newState);
            Session::flash('success', 'Richtlinie aktualisiert.');
        } catch (\Throwable $e) {
            Session::flash('error', 'Fehler: ' . $e->getMessage());
        }

        Redirect::to('/security');
    }
}
