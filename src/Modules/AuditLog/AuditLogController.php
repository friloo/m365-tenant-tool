<?php

namespace App\Modules\AuditLog;

use App\Auth\LocalAuth;
use App\Core\View;
use App\Helpers\CsvExporter;

class AuditLogController
{
    public function index(): void
    {
        LocalAuth::require();
        $service = app_service(AuditLogService::class);

        $from = $_GET['from'] ?? date('Y-m-d', strtotime('-7 days'));
        $to   = $_GET['to']   ?? date('Y-m-d');
        $tab  = $_GET['tab']  ?? 'directory';

        $directoryAudits = [];
        $signIns         = [];

        if ($tab === 'directory') {
            $directoryAudits = $service->getDirectoryAudits($from, $to);
        } else {
            $signIns = $service->getSignIns($from, $to);
        }

        View::render('auditlog/index', [
            'pageTitle'       => 'Audit-Log',
            'from'            => $from,
            'to'              => $to,
            'tab'             => $tab,
            'directoryAudits' => $directoryAudits,
            'signIns'         => $signIns,
        ]);
    }

    public function export(): void
    {
        LocalAuth::require();
        $service = app_service(AuditLogService::class);
        $from    = $_GET['from'] ?? date('Y-m-d', strtotime('-7 days'));
        $to      = $_GET['to']   ?? date('Y-m-d');
        $tab     = $_GET['tab']  ?? 'directory';

        if ($tab === 'signins') {
            $rows = $service->getSignIns($from, $to, 500);
            CsvExporter::download("anmeldungen_{$from}_{$to}.csv",
                ['Zeitpunkt', 'Benutzer', 'App', 'IP', 'Status', 'Risiko', 'CA-Status', 'Client'],
                array_map(fn($r) => [
                    CsvExporter::formatDate($r['createdDateTime'] ?? ''),
                    $r['userPrincipalName'] ?? '',
                    $r['appDisplayName'] ?? '',
                    $r['ipAddress'] ?? '',
                    ($r['status']['errorCode'] ?? 1) === 0 ? 'Erfolg' : 'Fehler',
                    $r['riskLevelDuringSignIn'] ?? '',
                    $r['conditionalAccessStatus'] ?? '',
                    $r['clientAppUsed'] ?? '',
                ], $rows)
            );
        } else {
            $rows = $service->getDirectoryAudits($from, $to);
            CsvExporter::download("auditlog_{$from}_{$to}.csv",
                ['Zeitpunkt', 'Aktion', 'Kategorie', 'Ergebnis', 'Initiiert von', 'Ziel'],
                array_map(fn($r) => [
                    CsvExporter::formatDate($r['activityDateTime'] ?? ''),
                    $r['activityDisplayName'] ?? '',
                    $r['category'] ?? '',
                    $r['result'] ?? '',
                    $r['initiatedBy']['user']['userPrincipalName'] ?? $r['initiatedBy']['app']['displayName'] ?? '',
                    implode(', ', array_column($r['targetResources'] ?? [], 'displayName')),
                ], $rows)
            );
        }
    }
}
