<?php

namespace App\Modules\SecurityPosture;

use App\Auth\LocalAuth;
use App\Core\View;

class SecurityPostureController
{
    public function index(): void
    {
        LocalAuth::require();

        $service = app_service(SecurityPostureService::class);
        $checks  = $service->runChecks();
        $score   = $service->getScore($checks);

        // Group checks by category
        $byCategory = [];
        foreach ($checks as $check) {
            $byCategory[$check['category']][] = $check;
        }

        View::render('securityposture/index', [
            'pageTitle'   => 'Security Posture',
            'checks'      => $checks,
            'score'       => $score,
            'byCategory'  => $byCategory,
        ]);
    }
}
