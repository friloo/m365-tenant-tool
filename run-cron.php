<?php
/**
 * M365 Tenant Tool — Unified Cron Entry Point
 *
 * Add to crontab (as www-data):
 *   * * * * * php /var/www/m365-tenant-tool/run-cron.php >> /var/log/m365-cron.log 2>&1
 *
 * This script runs every minute. All scheduling logic lives in CronRunner,
 * which reads intervals from the cron_jobs DB table (configurable via web UI).
 */

define('BASE_PATH', __DIR__);
define('CRON_MODE', true);

if (!file_exists(__DIR__ . '/storage/installed.lock')) {
    exit(0); // Not installed yet — silent exit
}

if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
    fwrite(STDERR, "[cron] vendor/autoload.php not found. Run composer install.\n");
    exit(1);
}

require_once __DIR__ . '/vendor/autoload.php';

// ── File lock — prevent overlapping cron runs ──────────────
$lockFile = __DIR__ . '/storage/cron.lock';
$lock = fopen($lockFile, 'w');
if (!flock($lock, LOCK_EX | LOCK_NB)) {
    exit(0); // Another instance is running — silent skip
}

// ── Bootstrap ───────────────────────────────────────────────
use App\Core\CliBootstrap;
use App\Modules\Cron\CronRunner;

try {
    ['graph' => $graphClient] = CliBootstrap::boot(__DIR__);
} catch (\Throwable $e) {
    fwrite(STDERR, "[cron] Bootstrap failed: " . $e->getMessage() . "\n");
    flock($lock, LOCK_UN);
    exit(1);
}

// ── Run ─────────────────────────────────────────────────────
try {
    (new CronRunner($graphClient))->run();
} catch (\Throwable $e) {
    fwrite(STDERR, "[cron][" . date('Y-m-d H:i:s') . "] Fatal: " . $e->getMessage() . "\n");
}

flock($lock, LOCK_UN);
fclose($lock);
