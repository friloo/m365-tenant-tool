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

    public function create(): void
    {
        LocalAuth::requireAdmin();
        $displayName  = trim($_POST['displayName'] ?? '');
        $description  = trim($_POST['description'] ?? '');
        $type         = trim($_POST['type'] ?? 'm365');
        $mailNickname = trim($_POST['mailNickname'] ?? '');

        if ($displayName === '') {
            Session::flash('error', 'Anzeigename darf nicht leer sein.');
            Redirect::to('/groups');
        }

        try {
            $group = app_service(GroupsService::class)->createGroup(
                $displayName,
                $description,
                $type,
                false,
                $mailNickname
            );
            Session::flash('success', 'Gruppe „' . $group['displayName'] . '" wurde erstellt.');
        } catch (\Throwable $e) {
            Session::flash('error', 'Fehler beim Erstellen: ' . $e->getMessage());
        }
        Redirect::to('/groups');
    }

    public function delete(string $id): void
    {
        LocalAuth::requireAdmin();
        try {
            app_service(GroupsService::class)->deleteGroup($id);
            Session::flash('success', 'Gruppe wurde gelöscht.');
        } catch (\Throwable $e) {
            Session::flash('error', 'Fehler beim Löschen: ' . $e->getMessage());
        }
        Redirect::to('/groups');
    }

    public function addOwner(string $id): void
    {
        LocalAuth::requireAdmin();
        $userId = trim($_POST['user_id'] ?? '');
        if (!$userId) { Redirect::to('/groups/' . $id); }
        try {
            app_service(GroupsService::class)->addOwner($id, $userId);
            Session::flash('success', 'Besitzer hinzugefügt.');
        } catch (\Throwable $e) {
            Session::flash('error', 'Fehler: ' . $e->getMessage());
        }
        Redirect::to('/groups/' . $id);
    }

    public function removeOwner(string $id, string $userId): void
    {
        LocalAuth::requireAdmin();
        try {
            app_service(GroupsService::class)->removeOwner($id, $userId);
            Session::flash('success', 'Besitzer entfernt.');
        } catch (\Throwable $e) {
            Session::flash('error', 'Fehler: ' . $e->getMessage());
        }
        Redirect::to('/groups/' . $id);
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
