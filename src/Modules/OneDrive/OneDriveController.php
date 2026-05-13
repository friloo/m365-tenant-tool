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
        catch (\Throwable $e) { $loadErr = 'Benutzer nicht ladbar: ' . $e->getMessage(); error_log('OneDrive index users: ' . $e->getMessage()); }
        try { $drives = $service->getUserDrives($users); }
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
        try { $driveMap = $service->getPersonalDrivesReport(); }
        catch (\Throwable $e) { error_log('OneDrive personal report: ' . $e->getMessage()); }
        $reportMode = !empty($driveMap);

        if (!$reportMode) {
            // Report API returned empty (permission issue, anonymisation, or $format problem).
            // Fall back to per-user drive checks for the first 150 users.
            try { $driveMap = $service->getPersonalDrivesPerUser($allUsers, 150); }
            catch (\Throwable $e) { $driveMap = []; error_log('OneDrive personal perUser: ' . $e->getMessage()); }
        }

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
            'pageTitle'      => 'OneDrive – Persönliche Laufwerke',
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
                $ok ? 'OneDrive wurde erfolgreich provisioniert.' : 'Provisionierung fehlgeschlagen — prüfen Sie Lizenzzuweisung und Berechtigungen.'
            );
        } catch (\Throwable $e) {
            Session::flash('error', 'Fehler: ' . $e->getMessage());
        }
        Redirect::to('/onedrive/personal');
    }

    public function deprovision(string $id): void
    {
        LocalAuth::requireAdmin();
        $service = app_service(OneDriveService::class);
        try {
            $service->deprovisionDrive($id);
            Session::flash('success', 'OneDrive wurde gelöscht (Papierkorb). Endgültige Löschung nach 93 Tagen.');
        } catch (\Throwable $e) {
            Session::flash('error', 'Fehler beim Löschen: ' . $e->getMessage());
        }
        Redirect::to('/onedrive/personal');
    }
}
