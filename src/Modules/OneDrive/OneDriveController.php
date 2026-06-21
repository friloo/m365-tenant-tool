<?php

namespace App\Modules\OneDrive;

use App\Auth\LocalAuth;
use App\Core\Redirect;
use App\Core\Session;
use App\Core\View;
use App\Modules\Users\UsersService;

class OneDriveController
{
    public function index(): void
    {
        LocalAuth::require();
        $service = app_service(OneDriveService::class);

        $users = []; $drives = []; $loadErr = null;
        try { $users = app_service(UsersService::class)->getAll(); }
        catch (\Throwable $e) { $loadErr = t('Benutzer nicht ladbar: ') . $e->getMessage(); error_log('OneDrive index users: ' . $e->getMessage()); }

        // getStorageOverview covers ALL provisioned OneDrives: tenant usage report
        // first, with an automatic per-user fallback (real names) when the report
        // is missing or anonymised.
        try { $drives = $service->getStorageOverview($users); }
        catch (\Throwable $e) { $loadErr = ($loadErr ? $loadErr . ' | ' : '') . 'Drives: ' . $e->getMessage(); error_log('OneDrive index drives: ' . $e->getMessage()); }

        View::render('onedrive/index', [
            'pageTitle' => 'OneDrive',
            'drives'    => $drives,
            'error'     => Session::getFlash('error') ?: $loadErr,
        ]);
    }

    public function personal(): void
    {
        LocalAuth::require();
        $service   = app_service(OneDriveService::class);

        if (isset($_GET['refresh'])) {
            app_graph()->getCache()->forget('od_personal_report');
            app_graph()->getCache()->forget('od_personal_drives');
            app_graph()->getCache()->forget('users_all');
        }

        $allUsers = []; $driveMap = []; $loadErr = null;
        try { $allUsers = app_service(UsersService::class)->getAll(); }
        catch (\Throwable $e) { $loadErr = 'Benutzer: ' . $e->getMessage(); error_log('OneDrive personal users: ' . $e->getMessage()); }
        // Report first, with automatic per-user fallback when the report is empty
        // OR anonymised (concealed user names) — so names are always resolvable.
        try { $driveMap = $service->getProvisionedDriveMap($allUsers); }
        catch (\Throwable $e) { $driveMap = []; error_log('OneDrive personal drive map: ' . $e->getMessage()); }
        $reportMode = !empty($driveMap);

        $list = [];
        foreach ($allUsers as $user) {
            $upn       = strtolower($user['userPrincipalName'] ?? '');
            $driveInfo = $driveMap[$upn] ?? null;
            $list[] = [
                'id'               => $user['id'],
                'displayName'      => $user['displayName'] ?? $upn,
                'upn'              => $upn,
                'accountEnabled'   => $user['accountEnabled'] ?? true,
                'hasOneDrive'      => $driveInfo !== null,
                'storageUsed'      => $driveInfo['storageUsed']      ?? 0,
                'storageAllocated' => $driveInfo['storageAllocated'] ?? 0,
                'fileCount'        => $driveInfo['fileCount']        ?? 0,
                'lastActivity'     => $driveInfo['lastActivity']     ?? null,
                'siteUrl'          => $driveInfo['siteUrl']          ?? null,
            ];
        }

        $provisioned    = count(array_filter($list, fn($u) => $u['hasOneDrive']));
        $notProvisioned = count($list) - $provisioned;

        View::render('onedrive/personal', [
            'pageTitle'      => t('OneDrive – Persönliche Laufwerke'),
            'list'           => $list,
            'provisioned'    => $provisioned,
            'notProvisioned' => $notProvisioned,
            'reportMode'     => $reportMode,
            'flash'          => Session::getFlash('success'),
            'error'          => Session::getFlash('error') ?: $loadErr,
        ]);
    }

    public function provision(string $id): void
    {
        LocalAuth::require();
        $service = app_service(OneDriveService::class);
        try {
            $ok = $service->provisionDrive($id);
            Session::flash(
                $ok ? 'success' : 'error',
                $ok ? t('OneDrive wurde erfolgreich provisioniert.') : t('Provisionierung fehlgeschlagen — prüfen Sie Lizenzzuweisung und Berechtigungen.')
            );
        } catch (\Throwable $e) {
            Session::flash('error', t('Fehler: ') . $e->getMessage());
        }
        Redirect::to('/onedrive/personal');
    }

    public function deprovision(string $id): void
    {
        LocalAuth::requireAdmin();
        $service = app_service(OneDriveService::class);
        try {
            $service->deprovisionDrive($id);
            Session::flash('success', t('OneDrive wurde gelöscht (Papierkorb). Endgültige Löschung nach 93 Tagen.'));
        } catch (\Throwable $e) {
            Session::flash('error', t('Fehler beim Löschen: ') . $e->getMessage());
        }
        Redirect::to('/onedrive/personal');
    }
}
