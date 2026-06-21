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
        $diag    = null;
        $devices = [];
        try {
            $devices = $service->getAll();
        } catch (\Throwable $e) {
            $diag = \App\Graph\GraphErrorTranslator::fromThrowable($e, 'DeviceManagementManagedDevices.Read.All');
            error_log('DevicesController::index error: ' . $e->getMessage());
        }
        $stats = $service->getStats($devices);

        View::render('devices/index', [
            'pageTitle' => t('Geräte'),
            'devices'   => $devices,
            'stats'     => $stats,
            'flash'     => Session::getFlash('success'),
            'error'     => $error,
            'diag'      => $diag,
        ]);
    }

    public function show(string $id): void
    {
        LocalAuth::require();
        $service = app_service(DevicesService::class);

        $detail       = $service->getDeviceDetail($id);
        $bitlockerKeys = $service->getBitLockerKeys($detail['azureADDeviceId'] ?? '');

        View::render('devices/detail', [
            'pageTitle'     => $detail['deviceName'] ?? t('Gerät'),
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
            Session::flash('success', t('Synchronisation angefordert. Das Gerät wird sich beim nächsten Check-In aktualisieren.'));
        } catch (\Throwable $e) {
            $msg = $e->getMessage();
            if (str_contains($msg, 'PrivilegedOperations') || str_contains($msg, 'not authorized')) {
                Session::flash('error',
                    t('Synchronisation fehlgeschlagen: Fehlende Berechtigung <strong>DeviceManagementManagedDevices.PrivilegedOperations.All</strong>. Bitte in Azure AD → App-Registrierungen → deine App → API-Berechtigungen hinzufügen und Admin-Consent erteilen. <a href=":url" target="_blank" rel="noopener noreferrer">Azure AD öffnen →</a>', ['url' => 'https://entra.microsoft.com/#view/Microsoft_AAD_RegisteredApps/ApplicationMenuBlade/~/CallAnAPI'])
                );
            } else {
                Session::flash('error', t('Synchronisation fehlgeschlagen: :msg', ['msg' => $msg]));
            }
        }
        Redirect::to('/devices/' . $id);
    }

    public function retire(string $id): void
    {
        LocalAuth::requireAdmin();
        try {
            app_service(DevicesService::class)->retireDevice($id);
            Session::flash('success', t('Gerät wurde zurückgesetzt (Retire). Unternehmensdaten wurden entfernt.'));
        } catch (\Throwable $e) {
            Session::flash('error', t('Retire fehlgeschlagen: :msg', ['msg' => $e->getMessage()]));
        }
        Redirect::to('/devices');
    }

    public function wipe(string $id): void
    {
        LocalAuth::requireAdmin();
        try {
            app_service(DevicesService::class)->wipeDevice($id);
            Session::flash('success', t('Gerät wird auf Werkseinstellungen zurückgesetzt (Wipe). Dieser Vorgang kann nicht rückgängig gemacht werden.'));
        } catch (\Throwable $e) {
            Session::flash('error', t('Wipe fehlgeschlagen: :msg', ['msg' => $e->getMessage()]));
        }
        Redirect::to('/devices');
    }

    public function export(): void
    {
        LocalAuth::require();
        $devices = app_service(DevicesService::class)->getAll();
        CsvExporter::download('geraete_' . date('Ymd') . '.csv',
            [t('Gerät'), t('OS'), t('Version'), t('Benutzer'), t('Compliance'), t('Verschlüsselt'), t('Letzter Sync'), t('Registriert')],
            array_map(fn($d) => [
                $d['deviceName'] ?? '',
                $d['operatingSystem'] ?? '',
                $d['osVersion'] ?? '',
                $d['userPrincipalName'] ?? '',
                $d['complianceState'] ?? '',
                ($d['isEncrypted'] ?? false) ? t('Ja') : t('Nein'),
                CsvExporter::formatDate($d['lastSyncDateTime'] ?? ''),
                CsvExporter::formatDate($d['enrolledDateTime'] ?? ''),
            ], $devices)
        );
    }
}
