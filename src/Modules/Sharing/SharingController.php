<?php

namespace App\Modules\Sharing;

use App\Auth\LocalAuth;
use App\Core\Redirect;
use App\Core\Session;
use App\Core\View;
use App\Helpers\CsvExporter;

class SharingController
{
    public function index(): void
    {
        LocalAuth::require();
        $service = app_service(SharingService::class);
        $data    = $service->getSharingSummary();

        View::render('sharing/index', [
            'pageTitle' => 'Externe Freigaben',
            'summary'   => $data,
            'flash'     => Session::getFlash('success'),
            'error'     => Session::getFlash('error'),
        ]);
    }

    public function revoke(): void
    {
        LocalAuth::require();
        $driveId      = trim($_POST['drive_id'] ?? '');
        $itemId       = trim($_POST['item_id'] ?? '');
        $permissionId = trim($_POST['permission_id'] ?? '');

        if (!$driveId || !$itemId || !$permissionId) {
            Session::flash('error', 'Ungültige Parameter.');
            Redirect::to('/sharing');
        }

        try {
            app_service(SharingService::class)->revokePermission($driveId, $itemId, $permissionId);
            Session::flash('success', 'Freigabe wurde widerrufen.');
        } catch (\Throwable $e) {
            Session::flash('error', 'Widerrufen fehlgeschlagen: ' . $e->getMessage());
        }
        Redirect::to('/sharing');
    }

    public function export(): void
    {
        LocalAuth::require();
        $data  = app_service(SharingService::class)->getSharingSummary();
        $items = $data['items'] ?? [];

        CsvExporter::download('freigaben_' . date('Ymd') . '.csv',
            ['Typ', 'Name', 'Quelle', 'Freigabe-Typ', 'Besitzer', 'Geändert'],
            array_map(fn($i) => [
                $i['type'] ?? '',
                $i['name'] ?? '',
                $i['site'] ?? '',
                $i['scope'] ?? '',
                $i['owner'] ?? '',
                CsvExporter::formatDate($i['modified'] ?? ''),
            ], $items)
        );
    }
}
