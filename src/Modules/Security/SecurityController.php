<?php

namespace App\Modules\Security;

use App\Auth\LocalAuth;
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
}
