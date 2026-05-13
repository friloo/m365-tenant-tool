<?php

namespace App\Modules\Users;

use App\Auth\LocalAuth;
use App\Core\AppAudit;
use App\Core\Config;
use App\Core\Redirect;
use App\Core\Session;
use App\Core\View;
use App\Helpers\CsvExporter;
use App\Modules\Licenses\LicensesService;
use App\Queue\QueueDispatcher;

class UsersController
{
    public function index(): void
    {
        LocalAuth::require();
        $service = app_service(UsersService::class);
        if (isset($_GET['refresh'])) app_graph()->getCache()->forget('users_all');

        $users  = $service->getAll();
        $mfaMap = $service->getMfaStatus();
        $skus   = app_service(LicensesService::class)->getSkus();

        View::render('users/index', [
            'pageTitle' => 'Benutzer',
            'users'     => $users,
            'mfaMap'    => $mfaMap,
            'skus'      => $skus,
            'flash'     => Session::getFlash('success'),
            'error'     => Session::getFlash('error'),
        ]);
    }

    public function show(string $id): void
    {
        LocalAuth::require();
        $service = app_service(UsersService::class);

        $user     = $service->getOne($id);
        $groups   = $service->getMemberOf($id);
        $skus     = app_service(LicensesService::class)->getSkus();
        $signIns  = $service->getSignInHistory($id);
        $notes    = (new UserNotesService())->getForUser($id);

        View::render('users/detail', [
            'pageTitle' => $user['displayName'] ?? 'Benutzer',
            'user'      => $user,
            'groups'    => $groups,
            'skus'      => $skus,
            'signIns'   => $signIns,
            'notes'     => $notes,
            'flash'     => Session::getFlash('success'),
            'error'     => Session::getFlash('error'),
        ]);
    }

    public function editForm(string $id): void
    {
        LocalAuth::require();
        $service = app_service(UsersService::class);
        $user    = $service->getOne($id);

        View::render('users/edit', [
            'pageTitle' => 'Benutzer bearbeiten',
            'user'      => $user,
            'flash'     => Session::getFlash('success'),
            'error'     => Session::getFlash('error'),
        ]);
    }

    public function updateUser(string $id): void
    {
        LocalAuth::require();
        $allowed = ['displayName', 'jobTitle', 'department', 'mobilePhone', 'officeLocation'];
        $data    = [];
        foreach ($allowed as $field) {
            if (isset($_POST[$field])) {
                $data[$field] = trim($_POST[$field]);
            }
        }
        try {
            app_service(UsersService::class)->updateUser($id, $data);
            AppAudit::log('user_updated', 'users', "User ID: {$id}");
            Session::flash('success', 'Benutzer erfolgreich aktualisiert.');
        } catch (\Throwable $e) {
            Session::flash('error', 'Fehler beim Speichern: ' . $e->getMessage());
        }
        Redirect::to('/users/' . $id);
    }

    public function offboarding(string $id): void
    {
        LocalAuth::requireAdmin();
        $service  = app_service(UsersService::class);
        $completed = [];

        if (!empty($_POST['revoke_sessions'])) {
            try {
                $service->revokeSignInSessions($id);
                $completed[] = 'Sitzungen beendet';
            } catch (\Throwable $e) {
                Session::flash('error', 'Sitzungen beenden fehlgeschlagen: ' . $e->getMessage());
            }
        }

        if (!empty($_POST['remove_licenses'])) {
            try {
                $service->removeAllLicenses($id);
                $completed[] = 'Lizenzen entzogen';
            } catch (\Throwable $e) {
                Session::flash('error', 'Lizenzen entziehen fehlgeschlagen: ' . $e->getMessage());
            }
        }

        if (!empty($_POST['set_forwarding'])) {
            $forwardTo = trim($_POST['forward_to'] ?? '');
            if ($forwardTo !== '') {
                try {
                    app_graph()->patch("/users/{$id}/mailboxSettings", [
                        'forwardingSmtpAddress' => $forwardTo,
                    ]);
                    $completed[] = 'E-Mail-Weiterleitung gesetzt';
                } catch (\Throwable) {
                    // silently skip — may require MailboxSettings.ReadWrite permission
                }
            }
        }

        if (!empty($_POST['set_ooo'])) {
            $oooMessage = trim($_POST['ooo_message'] ?? '');
            if ($oooMessage !== '') {
                try {
                    app_graph()->patch("/users/{$id}/mailboxSettings", [
                        'automaticRepliesSetting' => [
                            'status'               => 'alwaysEnabled',
                            'internalReplyMessage' => $oooMessage,
                            'externalReplyMessage' => $oooMessage,
                        ],
                    ]);
                    $completed[] = 'Abwesenheitsnotiz aktiviert';
                } catch (\Throwable $e) {
                    Session::flash('error', 'Abwesenheitsnotiz fehlgeschlagen: ' . $e->getMessage());
                }
            }
        }

        if (!empty($completed)) {
            AppAudit::log('offboarding', 'users', "User ID: {$id}, actions: " . implode(',', $completed));
            Session::flash('success', 'Cloud-Cleanup abgeschlossen: ' . implode(', ', $completed) . '.');
        }
        Redirect::to('/users/' . $id);
    }

    public function export(): void
    {
        LocalAuth::require();
        $users  = app_service(UsersService::class)->getAll();
        $mfaMap = app_service(UsersService::class)->getMfaStatus();

        CsvExporter::download('benutzer_' . date('Ymd') . '.csv',
            ['Name', 'UPN', 'Status', 'MFA', 'Abteilung', 'Titel', 'Lizenzen', 'Erstellt', 'Letzter Login'],
            array_map(fn($u) => [
                $u['displayName'] ?? '',
                $u['userPrincipalName'] ?? '',
                ($u['accountEnabled'] ?? true) ? 'Aktiv' : 'Deaktiviert',
                ($mfaMap[$u['userPrincipalName'] ?? '']['mfaRegistered'] ?? false) ? 'Ja' : 'Nein',
                $u['department'] ?? '',
                $u['jobTitle'] ?? '',
                count($u['assignedLicenses'] ?? []),
                CsvExporter::formatDate($u['createdDateTime'] ?? ''),
                CsvExporter::formatDate($u['signInActivity']['lastSignInDateTime'] ?? ''),
            ], $users)
        );
    }

    public function toggleEnabled(string $id): void
    {
        LocalAuth::require();
        $service = app_service(UsersService::class);
        $user    = $service->getOne($id);
        $current = $user['accountEnabled'] ?? true;
        try {
            $service->setAccountEnabled($id, !$current);
            AppAudit::log('user_' . ($current ? 'disabled' : 'enabled'), 'users', "User ID: {$id}");
            Session::flash('success', ($current ? 'Deaktiviert: ' : 'Aktiviert: ') . ($user['displayName'] ?? $id));
        } catch (\Throwable $e) {
            Session::flash('error', 'Fehler: ' . $e->getMessage());
        }
        Redirect::to('/users/' . $id);
    }

    public function resetMfa(string $id): void
    {
        LocalAuth::require();
        try {
            app_service(UsersService::class)->resetMfa($id);
            AppAudit::log('mfa_reset', 'users', "User ID: {$id}");
            Session::flash('success', 'MFA-Methoden wurden zurückgesetzt.');
        } catch (\Throwable $e) {
            Session::flash('error', 'MFA-Reset fehlgeschlagen: ' . $e->getMessage());
        }
        Redirect::to('/users/' . $id);
    }

    public function assignLicense(string $id): void
    {
        LocalAuth::require();
        $skuId = trim($_POST['sku_id'] ?? '');
        if (!$skuId) { Redirect::to('/users/' . $id); }
        try {
            app_service(UsersService::class)->assignLicense($id, $skuId);
            AppAudit::log('license_assign', 'users', "User: {$id}");
            Session::flash('success', 'Lizenz zugewiesen.');
        } catch (\Throwable $e) {
            Session::flash('error', 'Lizenz-Zuweisung fehlgeschlagen: ' . $e->getMessage());
        }
        Redirect::to('/users/' . $id);
    }

    public function removeLicense(string $id): void
    {
        LocalAuth::require();
        $skuId = trim($_POST['sku_id'] ?? '');
        if (!$skuId) { Redirect::to('/users/' . $id); }
        try {
            app_service(UsersService::class)->removeLicense($id, $skuId);
            AppAudit::log('license_remove', 'users', "User: {$id}");
            Session::flash('success', 'Lizenz entfernt.');
        } catch (\Throwable $e) {
            Session::flash('error', 'Lizenz-Entfernung fehlgeschlagen: ' . $e->getMessage());
        }
        Redirect::to('/users/' . $id);
    }

    public function addNote(string $id): void
    {
        LocalAuth::requireAdmin();
        $note = trim($_POST['note'] ?? '');
        if ($note !== '') {
            (new UserNotesService())->add($id, $note, LocalAuth::username());
            Session::flash('success', 'Notiz gespeichert.');
        }
        Redirect::to("/users/{$id}");
    }

    public function deleteNote(string $id, string $noteId): void
    {
        LocalAuth::requireAdmin();
        (new UserNotesService())->delete((int)$noteId, $id);
        Session::flash('success', 'Notiz gelöscht.');
        Redirect::to("/users/{$id}");
    }

    public function bulkAction(): void
    {
        LocalAuth::require();

        $action  = $_POST['action'] ?? '';
        $userIds = array_values(array_filter(array_map('trim', (array)($_POST['user_ids'] ?? []))));

        $validActions = ['disable', 'enable', 'reset_mfa', 'assign_license', 'remove_license'];
        if (empty($userIds) || !in_array($action, $validActions, true)) {
            Session::flash('error', 'Ungültige Bulk-Aktion oder keine Benutzer ausgewählt.');
            Redirect::to('/users');
        }

        if ($action === 'assign_license') {
            $skuId = trim($_POST['sku_id'] ?? '');
            if (!$skuId) {
                Session::flash('error', 'Keine Lizenz ausgewählt.');
                Redirect::to('/users');
            }
            $graph  = app_graph();
            $ok = 0; $errors = 0;
            foreach ($userIds as $uid) {
                try {
                    $graph->post("/users/{$uid}/assignLicense", [
                        'addLicenses'    => [['skuId' => $skuId]],
                        'removeLicenses' => [],
                    ]);
                    $ok++;
                } catch (\Throwable) { $errors++; }
            }
            $graph->getCache()->forget('users_all');
            AppAudit::log('bulk_assign_license', 'users', count($userIds) . " users");
            Session::flash($errors ? 'error' : 'success',
                "{$ok} Lizenzen zugewiesen" . ($errors ? ", {$errors} Fehler." : '.'));
            Redirect::to('/users');
        }

        if ($action === 'remove_license') {
            $graph  = app_graph();
            $ok = 0; $errors = 0;
            foreach ($userIds as $uid) {
                try {
                    $user = $graph->get("/users/{$uid}", ['$select' => 'assignedLicenses'], null, 0);
                    $skus = array_column($user['assignedLicenses'] ?? [], 'skuId');
                    if ($skus) {
                        $graph->post("/users/{$uid}/assignLicense", [
                            'addLicenses'    => [],
                            'removeLicenses' => $skus,
                        ]);
                    }
                    $ok++;
                } catch (\Throwable) { $errors++; }
            }
            $graph->getCache()->forget('users_all');
            AppAudit::log('bulk_remove_license', 'users', count($userIds) . " users");
            Session::flash($errors ? 'error' : 'success',
                "{$ok} Benutzer Lizenzen entfernt" . ($errors ? ", {$errors} Fehler." : '.'));
            Redirect::to('/users');
        }

        // Queue-based actions
        $jobType = match($action) {
            'disable'   => 'user_toggle',
            'enable'    => 'user_toggle',
            'reset_mfa' => 'mfa_reset',
        };

        $payloads = array_map(function (string $uid) use ($action, $jobType): array {
            $base = ['user_id' => $uid];
            if ($jobType === 'user_toggle') {
                $base['enabled'] = $action === 'enable';
            }
            return $base;
        }, $userIds);

        $count = QueueDispatcher::dispatchBatch($jobType, $payloads);

        AppAudit::log('bulk_' . $action, 'users', count($userIds) . " users");

        $label = match($action) {
            'disable'   => 'Deaktivieren',
            'enable'    => 'Aktivieren',
            'reset_mfa' => 'MFA zurücksetzen',
        };

        Session::flash('success', "{$count} Benutzer zum {$label} in die Warteschlange aufgenommen — Verarbeitung durch den Cron-Job.");
        Redirect::to('/users');
    }
}
