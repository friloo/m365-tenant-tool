<?php

namespace App\Modules\Users;

use App\Auth\LocalAuth;
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

        $user   = $service->getOne($id);
        $groups = $service->getMemberOf($id);
        $skus   = app_service(LicensesService::class)->getSkus();

        View::render('users/detail', [
            'pageTitle' => $user['displayName'] ?? 'Benutzer',
            'user'      => $user,
            'groups'    => $groups,
            'skus'      => $skus,
            'flash'     => Session::getFlash('success'),
            'error'     => Session::getFlash('error'),
        ]);
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
            Session::flash('success', 'Lizenz entfernt.');
        } catch (\Throwable $e) {
            Session::flash('error', 'Lizenz-Entfernung fehlgeschlagen: ' . $e->getMessage());
        }
        Redirect::to('/users/' . $id);
    }

    public function bulkAction(): void
    {
        LocalAuth::require();

        $action  = $_POST['action'] ?? '';
        $userIds = array_values(array_filter(array_map('trim', (array)($_POST['user_ids'] ?? []))));

        if (empty($userIds) || !in_array($action, ['disable', 'enable', 'reset_mfa'], true)) {
            Session::flash('error', 'Ungültige Bulk-Aktion oder keine Benutzer ausgewählt.');
            Redirect::to('/users');
        }

        // Fetch display names for payload (best-effort, uses cache)
        $upnMap = [];
        try {
            foreach (app_service(UsersService::class)->getAll() as $u) {
                $upnMap[$u['id']] = $u['userPrincipalName'] ?? '';
            }
        } catch (\Throwable) {}

        // Map action → queue job type
        $jobType = match($action) {
            'disable'   => 'user_toggle',
            'enable'    => 'user_toggle',
            'reset_mfa' => 'mfa_reset',
        };

        $payloads = array_map(function (string $uid) use ($action, $jobType, $upnMap): array {
            $base = ['user_id' => $uid, 'user_upn' => $upnMap[$uid] ?? ''];
            if ($jobType === 'user_toggle') {
                $base['enabled'] = $action === 'enable';
            }
            return $base;
        }, $userIds);

        $count = QueueDispatcher::dispatchBatch($jobType, $payloads);

        $label = match($action) {
            'disable'   => 'Deaktivieren',
            'enable'    => 'Aktivieren',
            'reset_mfa' => 'MFA zurücksetzen',
        };

        Session::flash('success', "{$count} Benutzer zum {$label} in die Warteschlange aufgenommen — Verarbeitung durch den Cron-Job.");
        Redirect::to('/users');
    }
}
