<?php

namespace App\Modules\MfaMethods;

use App\Auth\LocalAuth;
use App\Core\View;

class MfaMethodsController
{
    public function index(): void
    {
        LocalAuth::require();

        $service = app_service(MfaMethodsService::class);
        $users   = $service->getAll();
        $summary = $service->getSummary($users);

        View::render('mfamethods/index', [
            'pageTitle' => 'MFA-Methoden',
            'users'     => $users,
            'summary'   => $summary,
            'labels'    => MfaMethodsService::methodLabels(),
        ]);
    }
}
