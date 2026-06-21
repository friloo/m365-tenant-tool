<?php

namespace App\Modules\GuestUsers;

use App\Auth\LocalAuth;
use App\Core\Redirect;
use App\Core\Session;
use App\Core\View;
use App\Helpers\CsvExporter;

class GuestUsersController
{
    public function index(): void
    {
        LocalAuth::require();
        $service = app_service(GuestUsersService::class);
        $guests  = $service->getAll();
        $stats   = $service->getStats($guests);

        View::render('guestusers/index', [
            'pageTitle' => t('Gastbenutzer'),
            'guests'    => $guests,
            'stats'     => $stats,
            'flash'     => Session::getFlash('success'),
            'error'     => Session::getFlash('error'),
        ]);
    }

    public function disable(string $id): void
    {
        LocalAuth::require();
        try {
            app_service(GuestUsersService::class)->disableGuest($id);
            Session::flash('success', t('Gastbenutzer deaktiviert.'));
        } catch (\Throwable $e) {
            Session::flash('error', t('Fehler: ') . $e->getMessage());
        }
        Redirect::to('/guestusers');
    }

    public function remove(string $id): void
    {
        // Hard DELETE of a user — admin only, consistent with other deletions.
        LocalAuth::requireAdmin();
        try {
            app_service(GuestUsersService::class)->removeGuest($id);
            Session::flash('success', t('Gastbenutzer gelöscht.'));
        } catch (\Throwable $e) {
            Session::flash('error', t('Löschen fehlgeschlagen: ') . $e->getMessage());
        }
        Redirect::to('/guestusers');
    }

    public function export(): void
    {
        LocalAuth::require();
        $guests = app_service(GuestUsersService::class)->getAll();
        CsvExporter::download('gastbenutzer_' . date('Ymd') . '.csv',
            [t('Name'), t('E-Mail'), t('Status'), t('Einladungsstatus'), t('Erstellt'), t('Letzter Login')],
            array_map(fn($g) => [
                $g['displayName'] ?? '',
                $g['mail'] ?? $g['userPrincipalName'] ?? '',
                ($g['accountEnabled'] ?? true) ? t('Aktiv') : t('Deaktiviert'),
                $g['externalUserState'] ?? '',
                CsvExporter::formatDate($g['createdDateTime'] ?? ''),
                CsvExporter::formatDate($g['signInActivity']['lastSignInDateTime'] ?? ''),
            ], $guests)
        );
    }
}
