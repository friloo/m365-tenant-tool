<?php

namespace App\Modules\Groups;

use App\Auth\LocalAuth;
use App\Core\View;

class GroupsController
{
    public function index(): void
    {
        LocalAuth::require();
        $service = app_service(GroupsService::class);
        $groups  = $service->getAll();

        View::render('groups/index', [
            'pageTitle' => 'Gruppen & Teams',
            'groups'    => $groups,
        ]);
    }

    public function show(string $id): void
    {
        LocalAuth::require();
        $service = app_service(GroupsService::class);
        $group   = $service->getOne($id);
        $members = $service->getMembers($id);
        $owners  = $service->getOwners($id);

        View::render('groups/detail', [
            'pageTitle' => $group['displayName'] ?? 'Gruppe',
            'group'     => $group,
            'members'   => $members,
            'owners'    => $owners,
        ]);
    }
}
