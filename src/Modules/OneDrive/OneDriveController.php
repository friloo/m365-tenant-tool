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
        $users   = app_service(UsersService::class)->getAll();

        $drives  = $service->getUserDrives($users);

        View::render('onedrive/index', [
            'pageTitle' => 'OneDrive',
            'drives'    => $drives,
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

        $allUsers = app_service(UsersService::class)->getAll();
        $driveMap = $service->getPersonalDrivesReport();
        $reportMode = !empty($driveMap); // false = fell back to per-user check

        if (!$reportMode) {
            // Report API returned empty (permission issue, anonymisation, or $format problem).
            // Fall back to per-user drive checks for the first 150 users.
            $driveMap = $service->getPersonalDrivesPerUser($allUsers, 150);
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
            'error'          => Session::getFlash('error'),
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
