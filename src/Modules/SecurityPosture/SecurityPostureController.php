<?php

namespace App\Modules\SecurityPosture;

use App\Auth\LocalAuth;
use App\Core\View;

class SecurityPostureController
{
    public function index(): void
    {
        LocalAuth::require();

        if (isset($_GET['refresh'])) {
            app_graph()->getCache()->flush();
        }

        $service         = app_service(SecurityPostureService::class);
        $checks          = $service->runChecksCached();
        $score           = $service->getScore($checks);
        $recommendations = $service->getRecommendations($checks);

        $byCategory = [];
        foreach ($checks as $check) {
            $byCategory[$check['category']][] = $check;
        }

        View::render('securityposture/index', [
            'pageTitle'       => 'Security Posture',
            'checks'          => $checks,
            'score'           => $score,
            'byCategory'      => $byCategory,
            'recommendations' => $recommendations,
        ]);
    }
}
