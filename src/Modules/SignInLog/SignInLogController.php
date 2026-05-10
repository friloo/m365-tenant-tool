<?php

namespace App\Modules\SignInLog;

use App\Auth\LocalAuth;
use App\Core\Session;
use App\Core\View;
use App\Helpers\CsvExporter;

class SignInLogController
{
    public function index(): void
    {
        LocalAuth::require();

        $service = app_service(SignInLogService::class);

        $rawFilters = [
            'user'    => trim($_GET['user']    ?? ''),
            'status'  => trim($_GET['status']  ?? ''),
            'app'     => trim($_GET['app']     ?? ''),
            'country' => trim($_GET['country'] ?? ''),
            'risk'    => trim($_GET['risk']    ?? ''),
            'days'    => trim($_GET['days']    ?? '7'),
        ];

        // Only pass non-empty filter values to getLogs
        $filters = array_filter($rawFilters, fn($v) => $v !== '');

        // Always include days (default 7)
        if (!isset($filters['days'])) {
            $filters['days'] = '7';
        }

        $logs      = $service->getLogs($filters);
        $stats     = $service->getStats($logs);
        $apps      = $service->getDistinctApps($logs);
        $countries = $service->getDistinctCountries($logs);

        View::render('signinlog/index', [
            'pageTitle' => 'Anmeldeprotokoll',
            'logs'      => $logs,
            'stats'     => $stats,
            'apps'      => $apps,
            'countries' => $countries,
            'filters'   => $rawFilters,
            'flash'     => Session::getFlash('success'),
            'error'     => Session::getFlash('error'),
        ]);
    }

    public function export(): void
    {
        LocalAuth::require();

        $service = app_service(SignInLogService::class);

        $rawFilters = [
            'user'    => trim($_GET['user']    ?? ''),
            'status'  => trim($_GET['status']  ?? ''),
            'app'     => trim($_GET['app']     ?? ''),
            'country' => trim($_GET['country'] ?? ''),
            'risk'    => trim($_GET['risk']    ?? ''),
            'days'    => trim($_GET['days']    ?? '7'),
        ];

        $filters = array_filter($rawFilters, fn($v) => $v !== '');
        if (!isset($filters['days'])) {
            $filters['days'] = '7';
        }

        $logs = $service->getLogs($filters);

        CsvExporter::download(
            'anmeldelog_' . date('Ymd_Hi') . '.csv',
            [
                'Datum/Uhrzeit',
                'Benutzer',
                'UPN',
                'App',
                'IP-Adresse',
                'Land',
                'Gerät',
                'Ergebnis',
                'Fehlercode',
                'Risiko',
                'CA-Status',
            ],
            array_map(function (array $log): array {
                $success    = ($log['status']['errorCode'] ?? 1) === 0;
                $errorCode  = $log['status']['errorCode'] ?? '';
                $country    = $log['location']['countryOrRegion'] ?? '';
                $os         = $log['deviceDetail']['operatingSystem'] ?? '';
                $risk       = $log['riskLevelDuringSignIn'] ?? '';
                $caStatus   = $log['conditionalAccessStatus'] ?? '';

                return [
                    CsvExporter::formatDate($log['createdDateTime'] ?? ''),
                    $log['userDisplayName'] ?? '',
                    $log['userPrincipalName'] ?? '',
                    $log['appDisplayName'] ?? '',
                    $log['ipAddress'] ?? '',
                    $country,
                    $os,
                    $success ? 'Erfolgreich' : 'Fehlgeschlagen',
                    (string)$errorCode,
                    $risk,
                    $caStatus,
                ];
            }, $logs)
        );
    }
}
