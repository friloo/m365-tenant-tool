<?php

namespace App\Update;

class UpdateManager
{
    public const CHANNELS = [
        'stable'      => 'https://update.loheide.eu/m365-tool',
        'development' => 'https://update.loheide.eu/m365-tool-development',
    ];

    private const PROTECTED_PATHS = [
        'config/', 'storage/', '.env', '.env.example',
        '.git/', '.gitignore', 'public/uploads/',
        'vendor/', 'composer.lock',
    ];

    private const VERSION_FILE     = 'storage/.version';
    private const MAINTENANCE_FILE = 'storage/.maintenance';
    private const PROGRESS_FILE    = 'storage/.update-progress';
    private const STAGING_DIR      = 'storage/.update-staging';
    private const MIGRATIONS_DIR   = 'database/migrations';

    public function __construct(private string $channel = 'stable')
    {
    }

    // -------------------------------------------------------------------------
    // Public API
    // -------------------------------------------------------------------------

    public function getCurrentVersion(): ?string
    {
        $file = BASE_PATH . '/' . self::VERSION_FILE;
        if (!file_exists($file)) {
            return null;
        }
        $content = trim((string) file_get_contents($file));
        return $content !== '' ? $content : null;
    }

    public function saveCurrentVersion(string $sha): void
    {
        file_put_contents(BASE_PATH . '/' . self::VERSION_FILE, $sha);
    }

    public function checkForUpdates(): array
    {
        $currentSha = $this->getCurrentVersion() ?? 'none';
        return $this->proxyGet('/check', ['current_sha' => $currentSha]);
    }

    public function installUpdate(string $username): array
    {
        $this->maintenanceOn();
        try {
            $this->setProgress(0, 'start', 'Update wird gestartet…');

            // 1. Get latest SHA
            $this->setProgress(5, 'version', 'Aktuelle Version wird abgerufen…');
            $versionData = $this->proxyGet('/version');
            $latestSha = $versionData['sha'] ?? throw new \RuntimeException('Keine SHA vom Proxy erhalten');

            // 2. Download ZIP
            $this->setProgress(20, 'download', 'Update-Paket wird heruntergeladen…');
            $zipPath = $this->downloadZip($latestSha);

            // 3. Extract to staging
            $this->setProgress(50, 'extract', 'Paket wird entpackt…');
            $stagingDir = BASE_PATH . '/' . self::STAGING_DIR;
            $this->cleanDir($stagingDir);
            $this->extractZip($zipPath, $stagingDir);
            @unlink($zipPath);

            // 4. Apply staging to production
            $this->setProgress(65, 'apply', 'Dateien werden übernommen…');
            $this->applyStagingToProduction($stagingDir);
            $this->cleanDir($stagingDir);

            // 5. Run migrations
            $this->setProgress(80, 'migrations', 'Datenbank-Migrationen werden ausgeführt…');
            $migCount = $this->runPendingMigrations();

            // 6. Clear caches
            $this->setProgress(90, 'cache', 'Cache wird geleert…');
            $this->clearCaches();

            // 7. Save version
            $this->saveCurrentVersion($latestSha);

            // 8. Audit log
            $this->auditLog("Update auf {$latestSha} installiert ({$migCount} Migrationen)", $username);

            $this->setProgress(100, 'done', 'Update erfolgreich abgeschlossen.');

            return ['success' => true, 'message' => "Update auf " . substr($latestSha, 0, 7) . " installiert. {$migCount} Migration(en) ausgeführt."];

        } catch (\Throwable $e) {
            $this->setProgress(-1, 'error', 'Fehler: ' . $e->getMessage());
            $this->auditLog('Update fehlgeschlagen: ' . $e->getMessage(), $username);
            return ['success' => false, 'message' => $e->getMessage()];
        } finally {
            $this->maintenanceOff();
        }
    }

    public function getProgress(): ?array
    {
        $file = BASE_PATH . '/' . self::PROGRESS_FILE;
        if (!file_exists($file)) {
            return null;
        }
        $json = file_get_contents($file);
        if ($json === false) {
            return null;
        }
        $data = json_decode($json, true);
        return is_array($data) ? $data : null;
    }

    public function maintenanceOn(): void
    {
        $data = json_encode(['since' => time(), 'until' => 'estimated']);
        file_put_contents(BASE_PATH . '/' . self::MAINTENANCE_FILE, $data);
    }

    public function maintenanceOff(): void
    {
        $file = BASE_PATH . '/' . self::MAINTENANCE_FILE;
        if (file_exists($file)) {
            @unlink($file);
        }
    }

    public function runPendingMigrations(): int
    {
        // Ensure migrations table exists
        \App\Database\DB::execute("CREATE TABLE IF NOT EXISTS `_migrations` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `filename` VARCHAR(255) NOT NULL UNIQUE,
            `applied_at` DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $migrationsDir = BASE_PATH . '/' . self::MIGRATIONS_DIR;
        if (!is_dir($migrationsDir)) return 0;

        $files = glob($migrationsDir . '/*.sql');
        if (!$files) return 0;
        sort($files); // alphabetical = chronological

        $applied = \App\Database\DB::fetchAll('SELECT filename FROM _migrations');
        $appliedSet = array_column($applied, 'filename');

        $count = 0;
        foreach ($files as $file) {
            $filename = basename($file);
            if (in_array($filename, $appliedSet, true)) continue;

            $sql = file_get_contents($file);
            $statements = $this->splitSql($sql);

            foreach ($statements as $stmt) {
                $stmt = trim($stmt);
                if ($stmt === '') continue;
                try {
                    \App\Database\DB::execute($stmt);
                } catch (\PDOException $e) {
                    // Ignore ignorable errors (idempotent migrations)
                    $code = (int)($e->errorInfo[1] ?? 0);
                    $ignorable = [1060, 1061, 1062, 1050, 1091, 1054];
                    if (!in_array($code, $ignorable, true)) {
                        throw new \RuntimeException("Migration {$filename} fehlgeschlagen: " . $e->getMessage());
                    }
                }
            }

            \App\Database\DB::execute('INSERT INTO _migrations (filename) VALUES (?)', [$filename]);
            $count++;
        }
        return $count;
    }

    public function rerunAllMigrations(): int
    {
        \App\Database\DB::execute('DELETE FROM _migrations');
        return $this->runPendingMigrations();
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    private function proxyGet(string $path, array $query = []): array
    {
        $base = self::CHANNELS[$this->channel] ?? self::CHANNELS['stable'];
        $url  = $base . $path;
        if (!empty($query)) {
            $url .= '?' . http_build_query($query);
        }

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS      => 5,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_USERAGENT      => 'm365-tool-Updater/1.0',
            CURLOPT_HTTPHEADER     => ['Accept: application/json'],
        ]);

        $body     = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr  = curl_error($ch);
        // curl_close removed: no-op since PHP 8.0, deprecated since 8.5

        if ($curlErr !== '') {
            throw new \RuntimeException('cURL-Fehler: ' . $curlErr);
        }
        if ($httpCode >= 400) {
            throw new \RuntimeException("Proxy returned HTTP {$httpCode} for {$path}");
        }

        $data = json_decode((string) $body, true);
        if (!is_array($data)) {
            throw new \RuntimeException('Ungültige JSON-Antwort vom Proxy');
        }
        if (isset($data['error'])) {
            throw new \RuntimeException((string) $data['error']);
        }

        return $data;
    }

    private function downloadZip(string $sha): string
    {
        $base    = self::CHANNELS[$this->channel] ?? self::CHANNELS['stable'];
        $url     = $base . '/zip?' . http_build_query(['ref' => $sha]);
        $tmpPath = sys_get_temp_dir() . '/m365-update-' . $sha . '.zip';

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS      => 5,
            CURLOPT_TIMEOUT        => 120,
            CURLOPT_USERAGENT      => 'm365-tool-Updater/1.0',
        ]);

        $body    = curl_exec($ch);
        $curlErr = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        // curl_close removed: no-op since PHP 8.0, deprecated since 8.5

        if ($curlErr !== '') {
            throw new \RuntimeException('Download-Fehler: ' . $curlErr);
        }
        if ($httpCode >= 400) {
            throw new \RuntimeException("Proxy returned HTTP {$httpCode} for ZIP download");
        }
        if (empty($body)) {
            throw new \RuntimeException('Leere ZIP-Antwort vom Proxy erhalten');
        }
        if (substr((string) $body, 0, 2) !== 'PK') {
            throw new \RuntimeException('Heruntergeladene Datei ist kein gültiges ZIP-Archiv');
        }

        file_put_contents($tmpPath, $body);
        return $tmpPath;
    }

    private function extractZip(string $zipPath, string $targetDir): void
    {
        if (!class_exists('ZipArchive')) {
            throw new \RuntimeException('ZipArchive PHP-Extension fehlt');
        }

        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        $zip = new \ZipArchive();
        $result = $zip->open($zipPath);
        if ($result !== true) {
            throw new \RuntimeException('ZIP konnte nicht geöffnet werden (Code: ' . $result . ')');
        }

        // Detect common top-level prefix (GitHub-style zip: "repo-sha123/")
        $prefix = null;
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            if ($name === false) continue;
            $slashPos = strpos($name, '/');
            if ($slashPos === false) {
                // File at root level — no common prefix possible
                $prefix = null;
                break;
            }
            $candidate = substr($name, 0, $slashPos + 1);
            if ($prefix === null) {
                $prefix = $candidate;
            } elseif ($prefix !== $candidate) {
                $prefix = null;
                break;
            }
        }

        $prefixLen = ($prefix !== null) ? strlen($prefix) : 0;

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            if ($name === false) continue;

            // Strip common prefix
            $relative = ($prefixLen > 0 && str_starts_with($name, $prefix))
                ? substr($name, $prefixLen)
                : $name;

            if ($relative === '' || $relative === false) continue; // top-level dir entry itself

            // Zip Slip guard: reject absolute paths, Windows drive letters and any
            // ".." traversal so a crafted entry like "../../index.php" cannot escape
            // $targetDir and overwrite arbitrary files (→ RCE on update).
            $relative = str_replace('\\', '/', $relative);
            if ($relative[0] === '/'
                || preg_match('#^[A-Za-z]:#', $relative)
                || preg_match('#(^|/)\.\.(/|$)#', $relative)) {
                continue; // skip unsafe entry
            }

            $destPath = $targetDir . '/' . $relative;

            // Defence in depth: the resolved parent directory must stay inside $targetDir.
            $baseReal = realpath($targetDir);
            $parentReal = realpath(dirname($destPath));
            if ($baseReal !== false && $parentReal !== false
                && !str_starts_with($parentReal . '/', $baseReal . '/')) {
                continue;
            }

            if (str_ends_with($name, '/')) {
                // Directory entry
                if (!is_dir($destPath)) {
                    mkdir($destPath, 0755, true);
                }
                continue;
            }

            $destDir = dirname($destPath);
            if (!is_dir($destDir)) {
                mkdir($destDir, 0755, true);
            }

            $content = $zip->getFromIndex($i);
            if ($content === false) continue;
            file_put_contents($destPath, $content);
        }

        $zip->close();
    }

    private function applyStagingToProduction(string $stagingDir): void
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($stagingDir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            /** @var \SplFileInfo $item */
            $stagingPath = $item->getPathname();
            $relativePath = ltrim(substr($stagingPath, strlen($stagingDir)), '/\\');

            if ($this->isProtected($relativePath)) {
                continue;
            }

            if ($item->isDir()) {
                $destDir = BASE_PATH . '/' . $relativePath;
                if (!is_dir($destDir)) {
                    mkdir($destDir, 0755, true);
                }
            } else {
                $destFile = BASE_PATH . '/' . $relativePath;
                $destDir  = dirname($destFile);
                if (!is_dir($destDir)) {
                    mkdir($destDir, 0755, true);
                }
                copy($stagingPath, $destFile);
            }
        }
    }

    private function isProtected(string $relativePath): bool
    {
        foreach (self::PROTECTED_PATHS as $protected) {
            if (str_starts_with($relativePath, $protected)) {
                return true;
            }
            // Also check exact match for non-directory entries (e.g. ".env")
            if ($relativePath === rtrim($protected, '/')) {
                return true;
            }
        }
        return false;
    }

    private function clearCaches(): void
    {
        // Clear application graph/cache table
        try {
            \App\Database\DB::execute('DELETE FROM cache');
        } catch (\Throwable) {
            // Table may not exist — ignore
        }

        // Reset OPcache
        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        // Invalidate individual PHP files (skip vendor/)
        if (function_exists('opcache_invalidate')) {
            $srcDir  = BASE_PATH . '/src';
            $viewDir = BASE_PATH . '/views';
            foreach ([$srcDir, $viewDir] as $dir) {
                if (!is_dir($dir)) continue;
                $it = new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS)
                );
                foreach ($it as $file) {
                    /** @var \SplFileInfo $file */
                    if ($file->getExtension() === 'php') {
                        opcache_invalidate($file->getPathname(), true);
                    }
                }
            }
        }
    }

    private function setProgress(int $pct, string $step, string $text): void
    {
        $data = json_encode([
            'pct'  => $pct,
            'step' => $step,
            'text' => $text,
            'ts'   => time(),
        ]);
        file_put_contents(BASE_PATH . '/' . self::PROGRESS_FILE, $data);
    }

    private function cleanDir(string $dir): void
    {
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
            return;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $item) {
            /** @var \SplFileInfo $item */
            if ($item->isDir()) {
                rmdir($item->getPathname());
            } else {
                unlink($item->getPathname());
            }
        }
    }

    private function auditLog(string $action, string $username): void
    {
        try {
            \App\Database\DB::execute(
                'INSERT INTO audit_log (action, module, ip_address, details) VALUES (?, ?, ?, ?)',
                [$action, 'update', $_SERVER['REMOTE_ADDR'] ?? '', $username]
            );
        } catch (\Throwable) {
            // Don't crash if audit_log table is missing
        }
    }

    private function splitSql(string $sql): array
    {
        $statements = [];
        $current = '';
        $len = strlen($sql);
        $i = 0;

        while ($i < $len) {
            $c = $sql[$i];

            // Single-line comment: -- or #
            if (($c === '-' && isset($sql[$i+1]) && $sql[$i+1] === '-') ||
                $c === '#') {
                while ($i < $len && $sql[$i] !== "\n") {
                    $current .= $sql[$i++];
                }
                continue;
            }

            // Block comment: /* ... */
            if ($c === '/' && isset($sql[$i+1]) && $sql[$i+1] === '*') {
                $current .= $sql[$i++]; // /
                $current .= $sql[$i++]; // *
                while ($i < $len) {
                    if ($sql[$i] === '*' && isset($sql[$i+1]) && $sql[$i+1] === '/') {
                        $current .= $sql[$i++]; // *
                        $current .= $sql[$i++]; // /
                        break;
                    }
                    $current .= $sql[$i++];
                }
                continue;
            }

            // String literal: ' or "
            if ($c === '\'' || $c === '"') {
                $quote = $c;
                $current .= $sql[$i++];
                while ($i < $len) {
                    $sc = $sql[$i];
                    if ($sc === '\\') {
                        $current .= $sql[$i++]; // backslash
                        if ($i < $len) $current .= $sql[$i++]; // escaped char
                        continue;
                    }
                    $current .= $sql[$i++];
                    if ($sc === $quote) break;
                }
                continue;
            }

            // Backtick identifier
            if ($c === '`') {
                $current .= $sql[$i++];
                while ($i < $len && $sql[$i] !== '`') {
                    $current .= $sql[$i++];
                }
                if ($i < $len) $current .= $sql[$i++]; // closing `
                continue;
            }

            // Statement delimiter
            if ($c === ';') {
                $statements[] = $current;
                $current = '';
                $i++;
                continue;
            }

            $current .= $sql[$i++];
        }

        if (trim($current) !== '') {
            $statements[] = $current;
        }

        return $statements;
    }
}
