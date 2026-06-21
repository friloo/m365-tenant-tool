<?php

namespace App\Modules\Backup;

use App\Auth\LocalAuth;
use App\Core\Config;
use App\Core\Redirect;
use App\Core\Session;
use App\Core\View;

/**
 * Manuelles Tracking des 3rd-Party-Backup-Status. Microsoft selbst sichert
 * M365-Daten nur über die Recycle-Bin-Mechanismen (30-93 Tage), eine echte
 * Wiederherstellung nach Ransomware oder versehentlichem Löschen ist nur
 * mit einem dedizierten 3rd-Party-Backup-Tool (Veeam, Druva, Spanning,
 * AvePoint, Acronis, …) möglich.
 *
 * Da diese Tools jeweils eigene APIs haben und keine einheitliche
 * Schnittstelle existiert, lassen wir den Admin den Status manuell
 * dokumentieren — als Hinweis und Audit-Trail für ISO-/NIS-2-Audits.
 */
class BackupController
{
    public function index(): void
    {
        LocalAuth::require();
        $config = Config::getInstance();

        $data = [
            'provider'        => $config->get('backup_provider', ''),
            'provider_url'    => $config->get('backup_provider_url', ''),
            'last_run'        => $config->get('backup_last_run', ''),
            'last_run_status' => $config->get('backup_last_run_status', ''),
            'retention_days'  => (int)$config->get('backup_retention_days', 0),
            'covers_mail'     => (int)$config->get('backup_covers_mail', 0),
            'covers_onedrive' => (int)$config->get('backup_covers_onedrive', 0),
            'covers_sp'       => (int)$config->get('backup_covers_sharepoint', 0),
            'covers_teams'    => (int)$config->get('backup_covers_teams', 0),
            'restore_tested'  => $config->get('backup_restore_tested', ''),
            'notes'           => $config->get('backup_notes', ''),
        ];

        $health = $this->assessHealth($data);

        View::render('backup/index', [
            'pageTitle' => t('Backup-Status (3rd-Party)'),
            'data'      => $data,
            'health'    => $health,
            'flash'     => Session::getFlash('success'),
            'error'     => Session::getFlash('error'),
        ]);
    }

    public function save(): void
    {
        LocalAuth::requireAdmin();
        $config = Config::getInstance();

        $config->set('backup_provider',           trim($_POST['provider']           ?? ''));
        $config->set('backup_provider_url',       trim($_POST['provider_url']       ?? ''));
        $config->set('backup_last_run',           trim($_POST['last_run']           ?? ''));
        $config->set('backup_last_run_status',    trim($_POST['last_run_status']    ?? ''));
        $config->set('backup_retention_days',     (string)(int)($_POST['retention_days'] ?? 0));
        $config->set('backup_covers_mail',        isset($_POST['covers_mail'])      ? '1' : '0');
        $config->set('backup_covers_onedrive',    isset($_POST['covers_onedrive'])  ? '1' : '0');
        $config->set('backup_covers_sharepoint',  isset($_POST['covers_sp'])        ? '1' : '0');
        $config->set('backup_covers_teams',       isset($_POST['covers_teams'])     ? '1' : '0');
        $config->set('backup_restore_tested',     trim($_POST['restore_tested']     ?? ''));
        $config->set('backup_notes',              trim($_POST['notes']              ?? ''));

        Session::flash('success', t('Backup-Konfiguration gespeichert.'));
        Redirect::to('/backup');
    }

    /**
     * Bewertet den manuell eingetragenen Status — gibt eine Liste von
     * Findings und einen Health-Score zurück.
     */
    private function assessHealth(array $data): array
    {
        $issues = [];
        if (empty($data['provider'])) {
            $issues[] = ['severity' => 'critical', 'msg' => t('Kein Backup-Anbieter eingetragen — M365-Daten haben keine echte Wiederherstellung über die Microsoft-Recycle-Bin-Frist hinaus (30-93 Tage).')];
        } else {
            // Coverage
            $coverage = $data['covers_mail'] + $data['covers_onedrive'] + $data['covers_sp'] + $data['covers_teams'];
            if ($coverage === 0) $issues[] = ['severity' => 'high',  'msg' => t('Backup-Anbieter eingetragen, aber keine Workloads markiert — Coverage unbekannt.')];
            elseif ($coverage < 3) $issues[] = ['severity' => 'medium', 'msg' => t('Nicht alle M365-Workloads (Mail, OneDrive, SharePoint, Teams) gesichert.')];

            // Last run
            if (empty($data['last_run'])) {
                $issues[] = ['severity' => 'high', 'msg' => t('Kein Datum für letzten Backup-Lauf eingetragen.')];
            } else {
                $age = (time() - strtotime($data['last_run'])) / 86400;
                if ($age > 7)       $issues[] = ['severity' => 'high',   'msg' => t('Letzter Backup-Lauf vor :days Tagen — sollte täglich laufen.', ['days' => (int)$age])];
                elseif ($age > 2)   $issues[] = ['severity' => 'medium', 'msg' => t('Letzter Backup-Lauf vor :days Tagen.', ['days' => (int)$age])];
            }
            if ($data['last_run_status'] !== '' && strtolower($data['last_run_status']) !== 'success') {
                $issues[] = ['severity' => 'critical', 'msg' => t('Letzter Backup-Lauf war nicht erfolgreich: :status', ['status' => $data['last_run_status']])];
            }
            if ($data['retention_days'] === 0) {
                $issues[] = ['severity' => 'medium', 'msg' => t('Aufbewahrungsfrist nicht dokumentiert.')];
            } elseif ($data['retention_days'] < 90) {
                $issues[] = ['severity' => 'medium', 'msg' => t('Aufbewahrung :days Tage — empfohlen mindestens 90, bei DSGVO-Pflicht oft 7 Jahre.', ['days' => $data['retention_days']])];
            }
            if (empty($data['restore_tested'])) {
                $issues[] = ['severity' => 'high', 'msg' => t('Restore-Test nicht dokumentiert — ein nie getesteter Backup ist ein unbekannter Backup.')];
            } else {
                $tested = (time() - strtotime($data['restore_tested'])) / 86400;
                if ($tested > 365) $issues[] = ['severity' => 'medium', 'msg' => t('Letzter Restore-Test vor mehr als einem Jahr.')];
            }
        }

        $score = 100;
        foreach ($issues as $i) {
            $score -= match ($i['severity']) {
                'critical' => 35,
                'high'     => 20,
                'medium'   => 10,
                default    => 5,
            };
        }
        return ['score' => max(0, $score), 'issues' => $issues];
    }
}
