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

        $groups = []; $loadErr = null;
        try { $groups = $service->getAll(); }
        catch (\Throwable $e) { $loadErr = 'Gruppen nicht ladbar: ' . $e->getMessage(); error_log('Groups index: ' . $e->getMessage()); }

        View::render('groups/index', [
            'pageTitle' => 'Gruppen & Teams',
            'groups'    => $groups,
            'error'     => Session::getFlash('error') ?: $loadErr,
        ]);
    }

    public function show(string $id): void
    {
        LocalAuth::require();
        $service = app_service(GroupsService::class);

        $group = null; $members = []; $owners = []; $loadErr = null;
        try { $group = $service->getOne($id); }
        catch (\Throwable $e) { $loadErr = 'Gruppe nicht ladbar: ' . $e->getMessage(); error_log('Groups show: ' . $e->getMessage()); }
        try { $members = $service->getMembers($id); }
        catch (\Throwable $e) { error_log('Groups show members: ' . $e->getMessage()); }
        try { $owners = $service->getOwners($id); }
        catch (\Throwable $e) { error_log('Groups show owners: ' . $e->getMessage()); }

        View::render('groups/detail', [
            'pageTitle' => $group['displayName'] ?? 'Gruppe',
            'group'     => $group,
            'members'   => $members,
            'owners'    => $owners,
            'flash'     => Session::getFlash('success'),
            'error'     => Session::getFlash('error') ?: $loadErr,
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

    public function inactive(): void
    {
        LocalAuth::require();
        $service = app_service(GroupsService::class);

        if (($_GET['refresh'] ?? '') === '1') {
            app_graph()->getCache()->forget('groups_inactive_report');
        }

        $days   = max(7, min(180, (int)($_GET['days'] ?? 30)));
        $groups = $service->getInactiveGroups($days);

        View::render('groups/inactive', [
            'pageTitle' => 'Inaktive Gruppen',
            'groups'    => $groups,
            'days'      => $days,
        ]);
    }

    public function exportInactive(): void
    {
        LocalAuth::require();
        $days   = max(7, min(180, (int)($_GET['days'] ?? 30)));
        $groups = app_service(GroupsService::class)->getInactiveGroups($days);

        CsvExporter::download('inaktive-gruppen.csv',
            ['Gruppe', 'Besitzer', 'Letzte Aktivität', 'Tage inaktiv', 'Mitglieder', 'Externe', 'Exchange E-Mails', 'SharePoint Dateien'],
            array_map(fn($row) => [
                $row['group_name'],
                $row['owner'],
                $row['last_activity'] !== null ? $row['last_activity']->format('Y-m-d') : '',
                $row['days_inactive'] >= 9999 ? '' : $row['days_inactive'],
                $row['member_count'],
                $row['external_count'],
                $row['exchange_emails'],
                $row['sharepoint_files'],
            ], $groups)
        );
    }
}
