<?php

/**
 * Alert runner — execute via cron:
 *   0 8 * * * php /var/www/m365tool/run-alerts.php >> /var/log/m365tool-alerts.log 2>&1
 *
 * Note: CronRunner (run-cron.php) ships its own alert_* jobs (Defender alerts,
 * service incidents, new risky users). This AlertRunner covers a different set
 * (risky-user threshold, MFA rate, anonymous shares). If you run both, expect
 * overlapping notifications — review which alert source you want before enabling.
 */

define('BASE_PATH', __DIR__);

if (!file_exists(__DIR__ . '/storage/installed.lock')) {
    echo "Not installed yet.\n"; exit(1);
}

require_once __DIR__ . '/vendor/autoload.php';

use App\Core\CliBootstrap;
use App\Helpers\AlertRunner;

['graph' => $graph, 'config' => $config] = CliBootstrap::boot(__DIR__);

$runner  = new AlertRunner($graph, $config);
$results = $runner->run();

$ts = date('Y-m-d H:i:s');
if (empty($results)) {
    echo "[{$ts}] No alerts triggered.\n";
} else {
    foreach ($results as $msg) {
        echo "[{$ts}] {$msg}\n";
    }
}
