<?php

namespace App\Modules\Sharing;

use App\Auth\LocalAuth;
use App\Core\View;

class SharingController
{
    public function index(): void
    {
        LocalAuth::require();
        $service = app_service(SharingService::class);
        $data    = $service->getSharingSummary();

        View::render('sharing/index', [
            'pageTitle' => 'Externe Freigaben',
            'summary'   => $data,
        ]);
    }
}
