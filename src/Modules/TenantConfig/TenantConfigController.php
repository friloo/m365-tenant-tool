<?php

namespace App\Modules\TenantConfig;

use App\Auth\LocalAuth;
use App\Core\AppAudit;
use App\Core\Redirect;
use App\Core\Session;

class TenantConfigController
{
    /** Download the current operational settings as a JSON file. */
    public function export(): void
    {
        LocalAuth::requireAdmin();

        $payload = TenantConfigService::export();
        AppAudit::log('config_export', 'tenantconfig',
            count($payload['settings']) . ' settings exported');

        $json     = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $filename = 'm365-config-' . date('Y-m-d') . '.json';

        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Cache-Control: no-cache, no-store, must-revalidate');
            header('Pragma: no-cache');
        }
        echo $json;
        exit;
    }

    /** Apply an uploaded/pasted config JSON. */
    public function import(): void
    {
        LocalAuth::requireAdmin();

        // Accept either a file upload or pasted JSON.
        $raw = '';
        if (!empty($_FILES['config_file']['tmp_name']) && is_uploaded_file($_FILES['config_file']['tmp_name'])) {
            if (($_FILES['config_file']['size'] ?? 0) > 256 * 1024) {
                Session::flash('error', t('Die Konfigurationsdatei ist zu groß (max. 256 KB).'));
                Redirect::to('/settings#config');
            }
            $raw = (string)file_get_contents($_FILES['config_file']['tmp_name']);
        } elseif (!empty($_POST['config_json'])) {
            $raw = (string)$_POST['config_json'];
        }

        if (trim($raw) === '') {
            Session::flash('error', t('Keine Konfiguration zum Importieren angegeben.'));
            Redirect::to('/settings#config');
        }

        $payload = json_decode($raw, true);
        if (!is_array($payload)) {
            Session::flash('error', t('Ungültiges JSON-Format.'));
            Redirect::to('/settings#config');
        }

        $result = TenantConfigService::import($payload);

        if ($result['error'] === 'unsupported_version') {
            Session::flash('error', t('Diese Konfigurationsdatei stammt aus einer neueren Version und kann nicht importiert werden.'));
            Redirect::to('/settings#config');
        }
        if ($result['error'] !== null) {
            Session::flash('error', t('Die Konfigurationsdatei hat ein ungültiges Format.'));
            Redirect::to('/settings#config');
        }

        AppAudit::log('config_import', 'tenantconfig',
            $result['applied'] . ' settings applied, ' . count($result['skipped']) . ' skipped');

        Session::flash('success', t(':n Einstellung(en) importiert. :skipped übersprungen (unbekannt oder gesperrt).', [
            'n'       => $result['applied'],
            'skipped' => count($result['skipped']),
        ]));
        Redirect::to('/settings#config');
    }
}
