<?php

namespace App\Modules\Groups;

use App\Auth\LocalAuth;
use App\Core\Redirect;
use App\Core\Session;
use App\Core\View;
use App\Helpers\CsvExporter;

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
            'flash'     => Session::getFlash('success'),
            'error'     => Session::getFlash('error'),
        ]);
    }

    public function addMember(string $id): void
    {
        LocalAuth::require();
        $userId = trim($_POST['user_id'] ?? '');
        if (!$userId) { Redirect::to('/groups/' . $id); }
        try {
            app_service(GroupsService::class)->addMember($id, $userId);
            Session::flash('success', 'Mitglied hinzugefügt.');
        } catch (\Throwable $e) {
            Session::flash('error', 'Fehler: ' . $e->getMessage());
        }
        Redirect::to('/groups/' . $id);
    }

    public function removeMember(string $groupId, string $userId): void
    {
        LocalAuth::require();
        try {
            app_service(GroupsService::class)->removeMember($groupId, $userId);
            Session::flash('success', 'Mitglied entfernt.');
        } catch (\Throwable $e) {
            Session::flash('error', 'Fehler: ' . $e->getMessage());
        }
        Redirect::to('/groups/' . $groupId);
    }

    public function export(): void
    {
        LocalAuth::require();
        $groups = app_service(GroupsService::class)->getAll();
        CsvExporter::download('gruppen_' . date('Ymd') . '.csv',
            ['Name', 'Typ', 'E-Mail', 'Erstellt'],
            array_map(fn($g) => [
                $g['displayName'] ?? '',
                GroupsService::getType($g),
                $g['mail'] ?? '',
                CsvExporter::formatDate($g['createdDateTime'] ?? ''),
            ], $groups)
        );
    }
}
