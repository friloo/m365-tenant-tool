<?php

namespace App\Modules\Settings;

use App\Database\DB;

/**
 * GDPR-oriented retention/erasure for the tool's OWN locally stored data.
 *
 * The tool caches and derives data from the tenant (sign-in/audit history,
 * share-review records with recipient emails, config snapshots, notifications,
 * IP-based throttle logs). This service provides:
 *   - purge(days):  delete rows older than N days from the time-stamped tables.
 *   - purgeAll():    erase all locally derived / PII data (a "forget tenant"
 *                    button) — without touching configuration, the tool's user
 *                    accounts or API keys.
 *
 * Each operation is independently wrapped so a missing table/column never
 * aborts the whole run.
 */
class DataRetentionService
{
    /** table => timestamp column, for age-based purging. */
    private const AGE_TABLES = [
        'app_audit_log'        => 'created_at',
        'app_notifications'    => 'created_at',
        'app_tenant_snapshots' => 'created_at',
        'app_metric_history'   => 'created_at',
        'stale_account_log'    => 'created_at',
        'login_attempts'       => 'attempted_at',
        'api_auth_failures'    => 'attempted_at',
        'share_reviews'        => 'first_detected',
    ];

    /** Tables wiped completely by purgeAll() (derived/PII/cache only). */
    private const ERASE_TABLES = [
        'cache',
        'app_audit_log',
        'app_notifications',
        'app_notification_seen',
        'app_tenant_snapshots',
        'app_metric_history',
        'stale_account_log',
        'login_attempts',
        'api_auth_failures',
        'share_review_tokens',
        'share_reviews',
    ];

    /**
     * Delete rows older than $days from every time-stamped table.
     *
     * @return array{deleted:int, per_table:array<string,int>}
     */
    public static function purge(int $days): array
    {
        $days = max(1, $days);
        $total = 0;
        $perTable = [];

        foreach (self::AGE_TABLES as $table => $col) {
            try {
                $n = DB::execute(
                    "DELETE FROM `{$table}` WHERE `{$col}` < DATE_SUB(NOW(), INTERVAL ? DAY)",
                    [$days]
                );
                $perTable[$table] = $n;
                $total += $n;
            } catch (\Throwable) {
                // table/column may not exist on older installs — skip silently
            }
        }

        // Housekeeping: drop already-expired Graph cache regardless of age.
        try { DB::execute("DELETE FROM cache WHERE expires_at < NOW()"); } catch (\Throwable) {}

        return ['deleted' => $total, 'per_table' => $perTable];
    }

    /**
     * Erase all locally derived / cached / PII data. Configuration, the tool's
     * own user accounts (m365_users) and API keys are intentionally preserved.
     *
     * @return array{deleted:int, per_table:array<string,int>}
     */
    public static function purgeAll(): array
    {
        $total = 0;
        $perTable = [];
        foreach (self::ERASE_TABLES as $table) {
            try {
                $n = DB::execute("DELETE FROM `{$table}`");
                $perTable[$table] = $n;
                $total += $n;
            } catch (\Throwable) {}
        }
        return ['deleted' => $total, 'per_table' => $perTable];
    }
}
