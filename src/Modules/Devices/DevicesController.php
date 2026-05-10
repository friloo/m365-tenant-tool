<?php

namespace App\Modules\Devices;

use App\Auth\LocalAuth;
use App\Core\Redirect;
use App\Core\Session;
use App\Core\View;
use App\Helpers\CsvExporter;

class DevicesController
{
    public function index(): void
    {
        LocalAuth::require();
        $service = app_service(DevicesService::class);
        $error   = Session::getFlash('error');
        $devices = [];
        try {
            $devices = $service->getAll();
        } catch (\Throwable $e) {
            $error = 'Geräte konnten nicht geladen werden: ' . $e->getMessage();
            error_log('DevicesController::index error: ' . $e->getMessage());
        }
        $stats = $service->getStats($devices);

        View::render('devices/index', [
            'pageTitle' => 'Geräte',
            'devices'   => $devices,
            'stats'     => $stats,
            'flash'     => Session::getFlash('success'),
            'error'     => $error,
        ]);
    }

    public function show(string $id): void
    {
        LocalAuth::require();
        $service = app_service(DevicesService::class);

        $detail       = $service->getDeviceDetail($id);
        $bitlockerKeys = $service->getBitLockerKeys($detail['azureADDeviceId'] ?? '');

        View::render('devices/detail', [
            'pageTitle'     => $detail['deviceName'] ?? 'Gerät',
            'detail'        => $detail,
            'bitlockerKeys' => $bitlockerKeys,
            'flash'         => Session::getFlash('success'),
            'error'         => Session::getFlash('error'),
        ]);
    }

    public function sync(string $id): void
    {
        LocalAuth::require();
        try {
            app_service(DevicesService::class)->syncDevice($id);
            Session::flash('success', 'Synchronisation angefordert. Das Gerät wird sich beim nächsten Check-In aktualisieren.');
        } catch (\Throwable $e) {
            Session::flash('error', 'Synchronisation fehlgeschlagen: ' . $e->getMessage());
        }
        Redirect::to('/devices/' . $id);
    }

    public function retire(string $id): void
    {
        LocalAuth::requireAdmin();
        try {
            app_service(DevicesService::class)->retireDevice($id);
            Session::flash('success', 'Gerät wurde zurückgesetzt (Retire). Unternehmensdaten wurden entfernt.');
        } catch (\Throwable $e) {
            Session::flash('error', 'Retire fehlgeschlagen: ' . $e->getMessage());
        }
        Redirect::to('/devices');
    }

    public function wipe(string $id): void
    {
        LocalAuth::requireAdmin();
        try {
            app_service(DevicesService::class)->wipeDevice($id);
            Session::flash('success', 'Gerät wird auf Werkseinstellungen zurückgesetzt (Wipe). Dieser Vorgang kann nicht rückgängig gemacht werden.');
        } catch (\Throwable $e) {
            Session::flash('error', 'Wipe fehlgeschlagen: ' . $e->getMessage());
        }
        Redirect::to('/devices');
    }

    public function export(): void
    {
        LocalAuth::require();
        $devices = app_service(DevicesService::class)->getAll();
        CsvExporter::download('geraete_' . date('Ymd') . '.csv',
            ['Gerät', 'OS', 'Version', 'Benutzer', 'Compliance', 'Verschlüsselt', 'Letzter Sync', 'Registriert'],
            array_map(fn($d) => [
                $d['deviceName'] ?? '',
                $d['operatingSystem'] ?? '',
                $d['osVersion'] ?? '',
                $d['userPrincipalName'] ?? '',
                $d['complianceState'] ?? '',
                ($d['isEncrypted'] ?? false) ? 'Ja' : 'Nein',
                CsvExporter::formatDate($d['lastSyncDateTime'] ?? ''),
                CsvExporter::formatDate($d['enrolledDateTime'] ?? ''),
            ], $devices)
        );
    }
}
