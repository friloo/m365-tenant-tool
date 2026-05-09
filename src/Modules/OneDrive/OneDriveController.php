<?php

namespace App\Modules\OneDrive;

use App\Auth\LocalAuth;
use App\Core\View;
use App\Modules\Users\UsersService;

class OneDriveController
{
    public function index(): void
    {
        LocalAuth::require();
        $service = app_service(OneDriveService::class);
        $users   = app_service(UsersService::class)->getAll();

        $drives  = $service->getUserDrives($users);

        View::render('onedrive/index', [
            'pageTitle' => 'OneDrive',
            'drives'    => $drives,
        ]);
    }
}
