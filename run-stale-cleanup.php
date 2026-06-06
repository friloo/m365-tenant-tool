<?php
/**
 * Stale Accounts Auto-Cleanup Cron Script
 *
 * ⚠️ DEPRECATED — this logic is also implemented as the `stale_cleanup` job inside
 * CronRunner (run-cron.php). Do NOT schedule this script alongside run-cron.php,
 * or licenses get removed and warning emails sent TWICE. Prefer the single unified
 * cron (run-cron.php); keep this standalone script only for legacy setups.
 *
 * Cron example (daily at 03:00):
 *   0 3 * * * php /var/www/m365-tenant-tool/run-stale-cleanup.php >> /var/log/m365-stale-cleanup.log 2>&1
 *
 * What it does (when stale_auto_release_enabled = 1):
 *   1. Fetches all enabled users with sign-in activity
 *   2. Sends a warning email X days before the auto-release threshold
 *   3. Removes licenses from users that exceeded stale_auto_release_days
 */

define('BASE_PATH', __DIR__);
define('CRON_MODE', true);

if (!file_exists(__DIR__ . '/storage/installed.lock')) {
    echo "[SKIP] App not installed.\n";
    exit(0);
}

require_once __DIR__ . '/vendor/autoload.php';

use App\Core\CliBootstrap;
use App\Database\DB;
use App\Helpers\Mailer;
use App\Modules\StaleAccounts\StaleAccountsService;

try {
    ['config' => $config, 'graph' => $graphClient] = CliBootstrap::boot(__DIR__);
} catch (\Throwable $e) {
    echo "[ERROR] " . $e->getMessage() . "\n";
    exit(1);
}

// Check if auto-release is enabled
if ($config->get('stale_auto_release_enabled', '0') !== '1') {
    echo "[" . date('Y-m-d H:i:s') . "] Auto-release disabled — nothing to do.\n";
    exit(0);
}

$autoReleaseDays = (int)$config->get('stale_auto_release_days', '180');
$warnDaysBefore  = (int)$config->get('stale_warn_days_before', '14');
$alertEmail      = $config->get('alert_email_to', '');
$appName         = $config->get('app_name', 'M365 Tenant Tool');

echo "[" . date('Y-m-d H:i:s') . "] Starting stale accounts cleanup (threshold: {$autoReleaseDays} days)\n";

$service      = new StaleAccountsService($graphClient);

// Fetch stale users at the auto-release threshold
$staleUsers = $service->getStaleUsers($autoReleaseDays);

$nowTs        = time();
$warnTs       = $nowTs + ($warnDaysBefore * 86400);
$released     = 0;
$warned       = 0;
$skipped      = 0;

foreach ($staleUsers as $user) {
    $userId = $user['id'] ?? '';
    $upn    = $user['userPrincipalName'] ?? '';
    $name   = $user['displayName'] ?? $upn;
    $days   = $user['daysInactive'] ?? null;

    if (!$userId || empty($user['assignedLicenses'])) {
        $skipped++;
        continue;
    }

    $skuIds = array_column($user['assignedLicenses'], 'skuId');

    // Users at exactly the auto-release threshold: remove licenses
    if ($days !== null && $days >= $autoReleaseDays) {
        try {
            $service->removeLicenses($userId, $skuIds);
            $service->logAction($userId, $upn, 'license_removed', [
                'skuIds'      => $skuIds,
                'daysInactive' => $days,
                'triggeredBy'  => 'cron_auto_release',
            ]);
            echo "[RELEASED] {$upn} — {$days} days inactive, removed " . count($skuIds) . " license(s)\n";
            $released++;

            if ($alertEmail) {
                Mailer::send(
                    $alertEmail,
                    "Lizenz automatisch entzogen: {$name}",
                    Mailer::alertTemplate(
                        "Automatische Lizenzfreigabe",
                        "<p>Dem Benutzer <strong>" . htmlspecialchars($name) . "</strong> (<code>" . htmlspecialchars($upn) . "</code>) "
                        . "wurden automatisch alle Lizenzen entzogen, da keine Anmeldung seit <strong>{$days} Tagen</strong> erfolgte.</p>"
                        . "<p>Entzogene Lizenzen: " . implode(', ', $skuIds) . "</p>",
                        $appName
                    )
                );
            }
        } catch (\Throwable $e) {
            echo "[ERROR] Failed to remove licenses for {$upn}: {$e->getMessage()}\n";
        }
        continue;
    }

    // Warning: within X days of the auto-release threshold
    if ($warnDaysBefore > 0 && $days !== null) {
        $daysUntilRelease = $autoReleaseDays - $days;
        if ($daysUntilRelease > 0 && $daysUntilRelease <= $warnDaysBefore) {
            // Check if we already warned recently (avoid duplicate emails)
            $alreadyWarned = DB::fetchOne(
                "SELECT id FROM stale_account_log WHERE user_id = ? AND action = 'warn_sent'
                 AND created_at > DATE_SUB(NOW(), INTERVAL ? DAY)",
                [$userId, $warnDaysBefore]
            );

            if (!$alreadyWarned && $alertEmail) {
                Mailer::send(
                    $alertEmail,
                    "Vorwarnung: Lizenzfreigabe in {$daysUntilRelease} Tagen — {$name}",
                    Mailer::alertTemplate(
                        "Bevorstehende Lizenzfreigabe",
                        "<p>Dem Benutzer <strong>" . htmlspecialchars($name) . "</strong> (<code>" . htmlspecialchars($upn) . "</code>) "
                        . "werden in <strong>{$daysUntilRelease} Tagen</strong> automatisch alle Lizenzen entzogen "
                        . "(inaktiv seit {$days} Tagen, Schwelle: {$autoReleaseDays} Tage).</p>"
                        . "<p>Um die Freigabe zu verhindern, aktiviere das Konto oder melde dich als betroffener Benutzer an.</p>"
                        . "<p><a href=\"" . $config->get('app_base_url') . "/staleaccounts\">→ Inaktive Konten verwalten</a></p>",
                        $appName
                    )
                );

                $service->logAction($userId, $upn, 'warn_sent', [
                    'daysInactive'     => $days,
                    'daysUntilRelease' => $daysUntilRelease,
                ]);
                echo "[WARNED] {$upn} — {$daysUntilRelease} days until auto-release\n";
                $warned++;
            } else {
                $skipped++;
            }
        } else {
            $skipped++;
        }
    } else {
        $skipped++;
    }
}

echo "[" . date('Y-m-d H:i:s') . "] Done — released: {$released}, warned: {$warned}, skipped: {$skipped}\n";
