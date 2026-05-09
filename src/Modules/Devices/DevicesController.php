<?php

namespace App\Modules\Devices;

use App\Auth\LocalAuth;
use App\Core\View;

class DevicesController
{
    public function index(): void
    {
        LocalAuth::require();
        $service = app_service(DevicesService::class);
        $devices = $service->getAll();
        $stats   = $service->getStats($devices);

        View::render('devices/index', [
            'pageTitle' => 'Geräte',
            'devices'   => $devices,
            'stats'     => $stats,
        ]);
    }
}
