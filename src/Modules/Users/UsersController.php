<?php

namespace App\Modules\Users;

use App\Auth\LocalAuth;
use App\Core\View;

class UsersController
{
    public function index(): void
    {
        LocalAuth::require();
        $service = app_service(UsersService::class);
        if (isset($_GET['refresh'])) app_graph()->getCache()->forget('users_all');

        $users  = $service->getAll();
        $mfaMap = $service->getMfaStatus();

        View::render('users/index', [
            'pageTitle' => 'Benutzer',
            'users'     => $users,
            'mfaMap'    => $mfaMap,
        ]);
    }

    public function show(string $id): void
    {
        LocalAuth::require();
        $service = app_service(UsersService::class);

        $user   = $service->getOne($id);
        $groups = $service->getMemberOf($id);

        View::render('users/detail', [
            'pageTitle' => $user['displayName'] ?? 'Benutzer',
            'user'      => $user,
            'groups'    => $groups,
        ]);
    }
}
