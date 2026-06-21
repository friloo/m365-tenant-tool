<?php

namespace App\Modules\TenantConfig;

use App\Core\Config;
use App\Database\DB;

/**
 * Config-as-Code: export/import of the tool's operational settings.
 *
 * Design goals:
 *  - Make it trivial to back up a working configuration, or replicate it to a
 *    second tenant ("set up once, apply everywhere").
 *  - NEVER leak secrets. Only an explicit allowlist of non-sensitive operational
 *    settings is ever exported, and the export additionally refuses any row that
 *    is stored encrypted (defence in depth). Credentials, secrets, tenant
 *    identity (tenant_id/client_id/client_secret) and per-instance runtime state
 *    (last-run timestamps, setup flags) are intentionally excluded.
 */
class TenantConfigService
{
    /** Schema version of the export format. Bump on breaking changes. */
    public const FORMAT_VERSION = 1;

    /**
     * The ONLY keys that may be exported or imported. Everything else is
     * ignored. Keep this list free of any secret / credential / identity key.
     *
     * @var string[]
     */
    public const EXPORTABLE_KEYS = [
        // General
        'app_name', 'default_language', 'timezone', 'cache_ttl', 'app_base_url',
        // Alerting thresholds & toggles
        'alert_email_to', 'alert_email_from',
        'alert_mfa_threshold', 'alert_license_threshold', 'alert_external_shares_max',
        'alert_noncompliant_devices_max', 'alert_risky_users_max', 'alert_stale_accounts_max',
        'alert_risky_users', 'alert_anon_shares', 'notification_recipients',
        // Share review
        'share_review_interval_days', 'share_review_grace_days', 'share_review_only_anonymous',
        // Branding
        'brand_primary_color', 'brand_logo_url', 'brand_logo_text',
        'brand_review_support_email', 'brand_review_footer',
        // Stale accounts
        'stale_account_days', 'stale_auto_release_enabled', 'stale_auto_release_days', 'stale_warn_days_before',
        // Passwords / reports
        'password_expiry_days',
        'weekly_report_enabled', 'weekly_report_day',
        'executive_report_enabled', 'executive_report_to',
        // License criteria
        'lic_need_exchange_online', 'lic_need_office_desktop', 'lic_need_teams',
        'lic_need_sharepoint', 'lic_need_onedrive', 'lic_need_intune',
        // AI advisor (NOT the api key)
        'ai_enabled', 'ai_provider', 'ai_model', 'ai_base_url', 'ai_cache_hours',
        // SMTP (NOT the password)
        'smtp_host', 'smtp_port', 'smtp_user',
        // Compliance / backup / lockbox metadata
        'compliance_profile',
        'backup_provider', 'backup_provider_url', 'backup_retention_days',
        'backup_covers_mail', 'backup_covers_onedrive', 'backup_covers_sharepoint', 'backup_covers_teams',
        'backup_notes', 'backup_restore_tested',
        'lockbox_enabled', 'lockbox_approvers', 'lockbox_sla_hours',
        'break_glass_upns',
    ];

    /**
     * Build the export payload: only allowlisted, non-encrypted settings.
     *
     * @return array{format_version:int,exported_at:string,app_name:string,settings:array<string,string>}
     */
    public static function export(): array
    {
        $config   = Config::getInstance();
        $settings = [];

        // Pull the encryption flag straight from the DB so a secret can never
        // slip into the export even if it were mistakenly allowlisted.
        $placeholders = implode(',', array_fill(0, count(self::EXPORTABLE_KEYS), '?'));
        $rows = [];
        try {
            $rows = DB::fetchAll(
                "SELECT `key`, `value`, is_encrypted FROM app_config WHERE `key` IN ($placeholders)",
                self::EXPORTABLE_KEYS
            );
        } catch (\Throwable) {
            $rows = [];
        }
        foreach ($rows as $row) {
            if ((int)($row['is_encrypted'] ?? 0) === 1) {
                continue; // never export encrypted values
            }
            $settings[$row['key']] = (string)$row['value'];
        }

        return [
            'format_version' => self::FORMAT_VERSION,
            'exported_at'    => date('c'),
            'app_name'       => (string)$config->get('app_name', 'M365 Tenant Tool'),
            'settings'       => $settings,
        ];
    }

    /**
     * Apply an imported payload. Only allowlisted keys are written; unknown keys
     * are ignored and reported as skipped.
     *
     * @param array $payload Decoded JSON (as from export()).
     * @return array{applied:int,skipped:string[],error:?string}
     */
    public static function import(array $payload): array
    {
        if (!isset($payload['settings']) || !is_array($payload['settings'])) {
            return ['applied' => 0, 'skipped' => [], 'error' => 'invalid_payload'];
        }
        if ((int)($payload['format_version'] ?? 0) > self::FORMAT_VERSION) {
            return ['applied' => 0, 'skipped' => [], 'error' => 'unsupported_version'];
        }

        $allow   = array_flip(self::EXPORTABLE_KEYS);
        $config  = Config::getInstance();
        $applied = 0;
        $skipped = [];

        foreach ($payload['settings'] as $key => $value) {
            if (!is_string($key) || !isset($allow[$key])) {
                $skipped[] = (string)$key;
                continue;
            }
            if (is_array($value) || is_object($value)) {
                $skipped[] = $key;
                continue;
            }
            // Stored unencrypted — these are non-sensitive operational settings.
            $config->set($key, (string)$value, false);
            $applied++;
        }

        return ['applied' => $applied, 'skipped' => $skipped, 'error' => null];
    }
}
