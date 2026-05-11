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
use App\Auth\GraphTokenManager;
use App\Cache\GraphCache;
use App\Core\Config;
use App\Database\DB;
use App\Encryption\Encryptor;
use App\Graph\GraphClient;
use App\Modules\Cron\CronRunner;

$encryptor = new Encryptor(__DIR__ . '/storage/app.key');

$ini = parse_ini_file(__DIR__ . '/storage/db_bootstrap.ini');
if (!$ini) {
    fwrite(STDERR, "[cron] db_bootstrap.ini not found or invalid.\n");
    flock($lock, LOCK_UN);
    exit(1);
}

DB::connect([
    'host'     => $ini['db_host'],
    'port'     => $ini['db_port'] ?? 3306,
    'name'     => $ini['db_name'],
    'user'     => $ini['db_user'],
    'password' => $encryptor->decrypt($ini['db_password_enc']),
]);

$config = Config::getInstance();
$config->setEncryptor($encryptor);
date_default_timezone_set($config->get('timezone', 'Europe/Berlin'));
DB::get()->exec("SET time_zone = '" . (new \DateTime())->format('P') . "'");

$graphClient = new GraphClient(
    new GraphTokenManager($encryptor),
    new GraphCache((int)$config->get('cache_ttl', 15))
);

// ── Run ─────────────────────────────────────────────────────
try {
    (new CronRunner($graphClient))->run();
} catch (\Throwable $e) {
    fwrite(STDERR, "[cron][" . date('Y-m-d H:i:s') . "] Fatal: " . $e->getMessage() . "\n");
}

flock($lock, LOCK_UN);
fclose($lock);
