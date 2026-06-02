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
            'pageTitle' => 'Gastbenutzer',
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
            Session::flash('success', 'Gastbenutzer deaktiviert.');
        } catch (\Throwable $e) {
            Session::flash('error', 'Fehler: ' . $e->getMessage());
        }
        Redirect::to('/guestusers');
    }

    public function remove(string $id): void
    {
        // Hard DELETE of a user — admin only, consistent with other deletions.
        LocalAuth::requireAdmin();
        try {
            app_service(GuestUsersService::class)->removeGuest($id);
            Session::flash('success', 'Gastbenutzer gelöscht.');
        } catch (\Throwable $e) {
            Session::flash('error', 'Löschen fehlgeschlagen: ' . $e->getMessage());
        }
        Redirect::to('/guestusers');
    }

    public function export(): void
    {
        LocalAuth::require();
        $guests = app_service(GuestUsersService::class)->getAll();
        CsvExporter::download('gastbenutzer_' . date('Ymd') . '.csv',
            ['Name', 'E-Mail', 'Status', 'Einladungsstatus', 'Erstellt', 'Letzter Login'],
            array_map(fn($g) => [
                $g['displayName'] ?? '',
                $g['mail'] ?? $g['userPrincipalName'] ?? '',
                ($g['accountEnabled'] ?? true) ? 'Aktiv' : 'Deaktiviert',
                $g['externalUserState'] ?? '',
                CsvExporter::formatDate($g['createdDateTime'] ?? ''),
                CsvExporter::formatDate($g['signInActivity']['lastSignInDateTime'] ?? ''),
            ], $guests)
        );
    }
}
