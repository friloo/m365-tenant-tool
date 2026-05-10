<?php

namespace App\Modules\Update;

use App\Auth\LocalAuth;
use App\Core\Redirect;
use App\Core\Session;
use App\Core\View;
use App\Database\DB;
use App\Update\UpdateManager;
use App\Update\UpdateManagerFactory;

class UpdateController
{
    public function index(): void
    {
        LocalAuth::requireAdmin();

        $um             = UpdateManagerFactory::create();
        $currentVersion = $um->getCurrentVersion();
        $currentVersionShort = $currentVersion ? substr($currentVersion, 0, 7) : 'unbekannt';

        $runtimeFile = BASE_PATH . '/config/runtime.php';
        $rt      = file_exists($runtimeFile) ? (include $runtimeFile) : [];
        $channel = $rt['update']['channel'] ?? 'stable';

        View::render('update/index', [
            'pageTitle'       => 'Updates',
            'currentVersion'  => $currentVersion,
            'currentVersionShort' => $currentVersionShort,
            'channel'         => $channel,
            'channels'        => array_keys(UpdateManager::CHANNELS),
            'migrationStatus' => $this->getMigrationStatus(),
            'flash'           => Session::getFlash('success'),
            'error'           => Session::getFlash('error'),
        ]);
    }

    /** POST /settings/update/check */
    public function check(): void
    {
        LocalAuth::requireAdmin();
        header('Content-Type: application/json');
        try {
            $um     = UpdateManagerFactory::create();
            $result = $um->checkForUpdates();
            echo json_encode($result);
        } catch (\Throwable $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    /** POST /settings/update/install */
    public function install(): void
    {
        LocalAuth::requireAdmin();
        header('Content-Type: application/json');
        try {
            $um     = UpdateManagerFactory::create();
            $result = $um->installUpdate(LocalAuth::username());
            echo json_encode($result);
        } catch (\Throwable $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /** GET /settings/update/progress */
    public function progress(): void
    {
        LocalAuth::requireAdmin();
        header('Content-Type: application/json');
        $um = UpdateManagerFactory::create();
        $p  = $um->getProgress();
        echo json_encode($p ?? ['pct' => 0, 'step' => 'idle', 'text' => 'Bereit']);
    }

    /** POST /settings/update/channel */
    public function setChannel(): void
    {
        LocalAuth::requireAdmin();

        $channel = $_POST['channel'] ?? '';
        if (!array_key_exists($channel, UpdateManager::CHANNELS)) {
            Session::flash('error', 'Ungültiger Channel: ' . $channel);
            Redirect::to('/settings/update');
        }

        $runtimeFile = BASE_PATH . '/config/runtime.php';
        $configDir   = BASE_PATH . '/config';

        if (!is_dir($configDir)) {
            mkdir($configDir, 0755, true);
        }

        $rt = file_exists($runtimeFile) ? (include $runtimeFile) : [];
        $rt['update']['channel'] = $channel;
        file_put_contents($runtimeFile, '<?php return ' . var_export($rt, true) . ';');

        Session::flash('success', 'Channel geändert zu ' . $channel);
        Redirect::to('/settings/update');
    }

    /** POST /settings/update/migrations */
    public function runMigrations(): void
    {
        LocalAuth::requireAdmin();
        try {
            $um    = UpdateManagerFactory::create();
            $count = $um->runPendingMigrations();
            Session::flash('success', "{$count} Migration(en) ausgeführt.");
        } catch (\Throwable $e) {
            Session::flash('error', 'Fehler beim Ausführen der Migrationen: ' . $e->getMessage());
        }
        Redirect::to('/settings/update');
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    private function getMigrationStatus(): array
    {
        // Ensure _migrations table exists
        try {
            DB::execute("CREATE TABLE IF NOT EXISTS `_migrations` (
                `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `filename` VARCHAR(255) NOT NULL UNIQUE,
                `applied_at` DATETIME DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        } catch (\Throwable) {
            // DB might not be available in all contexts
        }

        $migrationsDir = BASE_PATH . '/database/migrations';
        $sqlFiles      = is_dir($migrationsDir) ? (glob($migrationsDir . '/*.sql') ?: []) : [];
        sort($sqlFiles);

        $appliedRows = [];
        try {
            $appliedRows = DB::fetchAll('SELECT filename, applied_at FROM _migrations');
        } catch (\Throwable) {
            // ignore
        }

        $appliedMap = [];
        foreach ($appliedRows as $row) {
            $appliedMap[$row['filename']] = $row['applied_at'];
        }

        $files        = [];
        $pendingCount = 0;

        foreach ($sqlFiles as $path) {
            $name    = basename($path);
            $applied = array_key_exists($name, $appliedMap);
            if (!$applied) {
                $pendingCount++;
            }
            $files[] = [
                'name'       => $name,
                'applied'    => $applied,
                'applied_at' => $appliedMap[$name] ?? null,
            ];
        }

        return [
            'files'         => $files,
            'pending_count' => $pendingCount,
            'total'         => count($files),
        ];
    }
}
