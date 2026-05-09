<?php
/**
 * Share Governance Cron Script
 * Run periodically (e.g. daily at 08:00):
 *   0 8 * * * php /path/to/m365-tenant-tool/run-share-monitor.php >> /var/log/share-monitor.log 2>&1
 */

define('BASE_PATH', __DIR__);

if (!file_exists(__DIR__ . '/storage/installed.lock')) {
    echo "[ERROR] Application not installed.\n";
    exit(1);
}

require_once __DIR__ . '/vendor/autoload.php';

use App\Auth\GraphTokenManager;
use App\Cache\GraphCache;
use App\Core\Config;
use App\Core\Session;
use App\Database\DB;
use App\Encryption\Encryptor;
use App\Graph\GraphClient;
use App\Modules\ShareReview\ShareReviewService;

// ── Bootstrap (same as index.php, minus HTTP) ──────────────

$keyPath   = __DIR__ . '/storage/app.key';
$encryptor = new Encryptor($keyPath);

$bootstrapFile = __DIR__ . '/storage/db_bootstrap.ini';
if (!file_exists($bootstrapFile)) {
    echo "[ERROR] db_bootstrap.ini not found.\n";
    exit(1);
}

$ini        = parse_ini_file($bootstrapFile);
$dbPassword = $encryptor->decrypt($ini['db_password_enc']);
DB::connect([
    'host'     => $ini['db_host'],
    'port'     => $ini['db_port'] ?? 3306,
    'name'     => $ini['db_name'],
    'user'     => $ini['db_user'],
    'password' => $dbPassword,
]);

$config = Config::getInstance();
$config->setEncryptor($encryptor);

$tz = $config->get('timezone', 'Europe/Berlin');
date_default_timezone_set($tz);

$graphCache   = new GraphCache((int)$config->get('cache_ttl', 15));
$tokenManager = new GraphTokenManager($encryptor);
$graphClient  = new GraphClient($tokenManager, $graphCache);

// ── Run share monitor tasks ─────────────────────────────────

$service = new ShareReviewService($graphClient);

$timestamp = date('Y-m-d H:i:s');
echo "[{$timestamp}] Starting share monitor run...\n";

// 1. Scan and sync current shares from Graph API
echo "[{$timestamp}] Phase 1: Scanning SharePoint/OneDrive for external shares...\n";
$scanLog = $service->scanAndSync();
foreach ($scanLog as $line) {
    echo "  → {$line}\n";
}
echo "  Done. " . count($scanLog) . " entries.\n";

// 2. Send review emails for shares due for review
echo "[{$timestamp}] Phase 2: Sending review emails...\n";
$emailLog = $service->sendDueReviewEmails();
foreach ($emailLog as $line) {
    echo "  → {$line}\n";
}
echo "  Done. " . count($emailLog) . " entries.\n";

// 3. Auto-revoke shares that haven't been responded to
echo "[{$timestamp}] Phase 3: Auto-revoking overdue shares...\n";
$revokeLog = $service->autoRevokeOverdue();
foreach ($revokeLog as $line) {
    echo "  → {$line}\n";
}
echo "  Done. " . count($revokeLog) . " entries.\n";

echo "[" . date('Y-m-d H:i:s') . "] Share monitor run completed.\n";
