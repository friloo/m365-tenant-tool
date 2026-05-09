<?php

namespace App\Modules\Licenses;

use App\Auth\LocalAuth;
use App\Core\View;

class LicensesController
{
    public function index(): void
    {
        LocalAuth::require();
        $service = app_service(LicensesService::class);
        $skus    = $service->getSkus();

        View::render('licenses/index', [
            'pageTitle' => 'Lizenzen',
            'skus'      => $skus,
        ]);
    }
}
