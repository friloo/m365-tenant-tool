<?php

namespace App\Modules\Settings;

use App\Auth\LocalAuth;
use App\Core\Config;
use App\Core\Redirect;
use App\Core\Session;
use App\Core\View;
use App\Encryption\Encryptor;

class SettingsController
{
    public function index(): void
    {
        LocalAuth::require();
        $config = Config::getInstance();

        $settings = [
            'app_name'           => $config->get('app_name', 'M365 Tenant Tool'),
            'cache_ttl'          => $config->get('cache_ttl', '15'),
            'timezone'           => $config->get('timezone', 'Europe/Berlin'),
            'alert_email_to'     => $config->get('alert_email_to', ''),
            'alert_email_from'   => $config->get('alert_email_from', ''),
            'smtp_host'          => $config->get('smtp_host', ''),
            'smtp_port'          => $config->get('smtp_port', '587'),
            'smtp_user'          => $config->get('smtp_user', ''),
            'alert_mfa_threshold'=> $config->get('alert_mfa_threshold', '80'),
            'operator_username'              => $config->get('operator_username', ''),
            'alert_risky_users'              => $config->get('alert_risky_users', '1'),
            'alert_anon_shares'              => $config->get('alert_anon_shares', '1'),
            'app_base_url'                   => $config->get('app_base_url', ''),
            'share_review_interval_days'     => $config->get('share_review_interval_days', '30'),
            'share_review_grace_days'        => $config->get('share_review_grace_days', '7'),
            'share_review_only_anonymous'    => $config->get('share_review_only_anonymous', '0'),
            'brand_primary_color'            => $config->get('brand_primary_color', '#0078d4'),
            'brand_logo_url'                 => $config->get('brand_logo_url', ''),
            'brand_logo_text'                => $config->get('brand_logo_text', ''),
            'brand_review_support_email'     => $config->get('brand_review_support_email', ''),
            'brand_review_footer'            => $config->get('brand_review_footer', ''),
            'stale_account_days'             => $config->get('stale_account_days', '90'),
            'stale_auto_release_enabled'     => $config->get('stale_auto_release_enabled', '0'),
            'stale_auto_release_days'        => $config->get('stale_auto_release_days', '180'),
            'stale_warn_days_before'         => $config->get('stale_warn_days_before', '14'),
            'password_expiry_days'           => $config->get('password_expiry_days', '90'),
            'weekly_report_enabled'          => $config->get('weekly_report_enabled', '0'),
            'weekly_report_day'              => $config->get('weekly_report_day', '1'),
            'lic_need_exchange_online'       => $config->get('lic_need_exchange_online', '0'),
            'lic_need_office_desktop'        => $config->get('lic_need_office_desktop', '0'),
            'lic_need_teams'                 => $config->get('lic_need_teams', '0'),
            'lic_need_sharepoint'            => $config->get('lic_need_sharepoint', '0'),
            'lic_need_onedrive'              => $config->get('lic_need_onedrive', '0'),
            'lic_need_intune'                => $config->get('lic_need_intune', '0'),
        ];

        $flash = Session::getFlash('success');
        $error = Session::getFlash('error');

        View::render('settings/index', [
            'pageTitle' => 'Einstellungen',
            'settings'  => $settings,
            'flash'     => $flash,
            'error'     => $error,
        ]);
    }

    public function save(): void
    {
        LocalAuth::require();
        $config = Config::getInstance();

        try {
            $config->set('app_name',            trim($_POST['app_name'] ?? 'M365 Tenant Tool'));
            $config->set('cache_ttl',            (string)(int)($_POST['cache_ttl'] ?? 15));
            $config->set('timezone',             trim($_POST['timezone'] ?? 'Europe/Berlin'));
            $config->set('alert_email_to',       trim($_POST['alert_email_to'] ?? ''));
            $config->set('alert_email_from',     trim($_POST['alert_email_from'] ?? ''));
            $config->set('smtp_host',            trim($_POST['smtp_host'] ?? ''));
            $config->set('smtp_port',            (string)(int)($_POST['smtp_port'] ?? 587));
            $config->set('smtp_user',            trim($_POST['smtp_user'] ?? ''));
            $config->set('alert_mfa_threshold',  (string)(int)($_POST['alert_mfa_threshold'] ?? 80));
            $config->set('alert_risky_users',             isset($_POST['alert_risky_users']) ? '1' : '0');
            $config->set('alert_anon_shares',             isset($_POST['alert_anon_shares']) ? '1' : '0');
            $config->set('app_base_url',                  rtrim(trim($_POST['app_base_url'] ?? ''), '/'));
            $config->set('share_review_interval_days',    (string)max(1, (int)($_POST['share_review_interval_days'] ?? 30)));
            $config->set('share_review_grace_days',       (string)max(1, (int)($_POST['share_review_grace_days'] ?? 7)));
            $config->set('share_review_only_anonymous',   isset($_POST['share_review_only_anonymous']) ? '1' : '0');
            $config->set('brand_primary_color',           trim($_POST['brand_primary_color'] ?? '#0078d4') ?: '#0078d4');
            $config->set('brand_logo_url',                trim($_POST['brand_logo_url'] ?? ''));
            $config->set('brand_logo_text',               trim($_POST['brand_logo_text'] ?? ''));
            $config->set('brand_review_support_email',    trim($_POST['brand_review_support_email'] ?? ''));
            $config->set('brand_review_footer',           trim($_POST['brand_review_footer'] ?? ''));
            $config->set('stale_account_days',            (string)max(1, (int)($_POST['stale_account_days'] ?? 90)));
            $config->set('stale_auto_release_enabled',    isset($_POST['stale_auto_release_enabled']) ? '1' : '0');
            $config->set('stale_auto_release_days',       (string)max(1, (int)($_POST['stale_auto_release_days'] ?? 180)));
            $config->set('stale_warn_days_before',        (string)max(0, (int)($_POST['stale_warn_days_before'] ?? 14)));
            $config->set('password_expiry_days',          (string)max(1, (int)($_POST['password_expiry_days'] ?? 90)));
            $config->set('weekly_report_enabled',         isset($_POST['weekly_report_enabled']) ? '1' : '0');
            $config->set('weekly_report_day',             (string)max(1, min(7, (int)($_POST['weekly_report_day'] ?? 1))));
            $config->set('lic_need_exchange_online',      isset($_POST['lic_need_exchange_online']) ? '1' : '0');
            $config->set('lic_need_office_desktop',       isset($_POST['lic_need_office_desktop']) ? '1' : '0');
            $config->set('lic_need_teams',                isset($_POST['lic_need_teams']) ? '1' : '0');
            $config->set('lic_need_sharepoint',           isset($_POST['lic_need_sharepoint']) ? '1' : '0');
            $config->set('lic_need_onedrive',             isset($_POST['lic_need_onedrive']) ? '1' : '0');
            $config->set('lic_need_intune',               isset($_POST['lic_need_intune']) ? '1' : '0');

            if (!empty($_POST['smtp_password'])) {
                $config->set('smtp_password', trim($_POST['smtp_password']), true);
            }

            // Update admin password
            if (!empty($_POST['admin_password'])) {
                if ($_POST['admin_password'] !== ($_POST['admin_password_confirm'] ?? '')) {
                    Session::flash('error', 'Passwörter stimmen nicht überein.');
                    Redirect::to('/settings');
                }
                $hash = password_hash($_POST['admin_password'], PASSWORD_BCRYPT);
                $config->set('admin_password', $hash, true);
            }

            // Update operator credentials
            if (!empty($_POST['operator_username'])) {
                $config->set('operator_username', trim($_POST['operator_username']));
            }
            if (!empty($_POST['operator_password'])) {
                $hash = password_hash($_POST['operator_password'], PASSWORD_BCRYPT);
                $config->set('operator_password', $hash, true);
            }

            $config->clearCache();
            date_default_timezone_set($config->get('timezone', 'Europe/Berlin'));

            Session::flash('success', 'Einstellungen gespeichert.');
        } catch (\Throwable $e) {
            Session::flash('error', 'Fehler: ' . $e->getMessage());
        }

        Redirect::to('/settings');
    }

    public function clearCache(): void
    {
        LocalAuth::require();
        app_graph()->getCache()->flush();
        Session::flash('success', 'Cache erfolgreich geleert.');
        Redirect::to('/settings');
    }

    public function testMail(): void
    {
        LocalAuth::require();
        $config = Config::getInstance();
        $to = $config->get('alert_email_to');
        if (!$to) {
            Session::flash('error', 'Keine Alert-E-Mail-Adresse konfiguriert.');
            Redirect::to('/settings');
        }

        $body = \App\Helpers\Mailer::alertTemplate(
            'Test-E-Mail',
            '<p>Diese E-Mail bestätigt, dass der E-Mail-Versand korrekt konfiguriert ist.</p>',
            $config->get('app_name', 'M365 Tenant Tool')
        );

        $ok = \App\Helpers\Mailer::send($to, 'Test-E-Mail — M365 Tenant Tool', $body);
        if ($ok) {
            Session::flash('success', 'Test-E-Mail gesendet an ' . $to);
        } else {
            Session::flash('error', 'E-Mail-Versand fehlgeschlagen. SMTP-Einstellungen prüfen.');
        }
        Redirect::to('/settings');
    }

    public function manual(): void
    {
        LocalAuth::require();
        View::render('manual/index', ['pageTitle' => 'Handbuch']);
    }

    public function refreshToken(): void
    {
        LocalAuth::require();
        \App\Database\DB::execute('DELETE FROM graph_tokens');
        \App\Core\Session::setFlash('success', 'Token gelöscht — ein neues wird beim nächsten API-Aufruf geholt.');
        \App\Core\Redirect::to('/settings/permissions');
    }

    public function permissions(): void
    {
        LocalAuth::require();

        // Force new token if requested (needed after permission changes in Azure)
        if (isset($_GET['refresh'])) {
            \App\Database\DB::execute('DELETE FROM graph_tokens');
        }

        /** @var \App\Modules\Settings\PermissionCheckerService $svc */
        $svc     = app_service(PermissionCheckerService::class);
        $checked = $svc->checkPermissions();
        $summary = $svc->getSummary($checked);
        $info    = $svc->getTokenInfo();
        $tenant  = $svc->getTenantName();

        // Group by section
        $bySectionGranted = [];
        $bySectionMissing = [];
        foreach ($checked as $perm => $data) {
            $sec = $data['section'];
            if ($data['granted']) {
                $bySectionGranted[$sec][] = $data;
            } else {
                $bySectionMissing[$sec][] = $data;
            }
        }
        ksort($bySectionGranted);
        ksort($bySectionMissing);

        View::render('settings/permissions', [
            'pageTitle'        => 'Graph API Berechtigungen',
            'checked'          => $checked,
            'summary'          => $summary,
            'tokenInfo'        => $info,
            'tenantName'       => $tenant,
            'bySectionGranted' => $bySectionGranted,
            'bySectionMissing' => $bySectionMissing,
        ]);
    }
}
