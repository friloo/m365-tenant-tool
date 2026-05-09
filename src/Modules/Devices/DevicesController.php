<?php

namespace App\Modules\Devices;

use App\Auth\LocalAuth;
use App\Core\View;
use App\Helpers\CsvExporter;

class DevicesController
{
    public function index(): void
    {
        LocalAuth::require();
        $service = app_service(DevicesService::class);
        $devices = $service->getAll();
        $stats   = $service->getStats($devices);

        View::render('devices/index', [
            'pageTitle' => 'Geräte',
            'devices'   => $devices,
            'stats'     => $stats,
        ]);
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
