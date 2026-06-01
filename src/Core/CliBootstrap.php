<?php

namespace App\Core;

use App\Auth\GraphTokenManager;
use App\Cache\GraphCache;
use App\Database\DB;
use App\Encryption\Encryptor;
use App\Graph\GraphClient;

/**
 * Shared bootstrap for the standalone CLI/cron entry points. The encryptor +
 * db_bootstrap.ini + DB::connect + Config + GraphClient sequence used to be
 * copy-pasted into every run-*.php script; keep it here so it stays in sync.
 */
class CliBootstrap
{
    /**
     * @return array{encryptor: Encryptor, config: Config, graph: GraphClient}
     * @throws \RuntimeException when storage files are missing/invalid
     */
    public static function boot(string $basePath): array
    {
        $keyPath = $basePath . '/storage/app.key';
        $encryptor = new Encryptor($keyPath);

        $bootstrapFile = $basePath . '/storage/db_bootstrap.ini';
        if (!file_exists($bootstrapFile)) {
            throw new \RuntimeException('db_bootstrap.ini not found.');
        }
        $ini = parse_ini_file($bootstrapFile);
        if (!is_array($ini) || empty($ini['db_password_enc'])) {
            throw new \RuntimeException('db_bootstrap.ini invalid.');
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
        try {
            DB::get()->exec("SET time_zone = '" . (new \DateTime())->format('P') . "'");
        } catch (\Throwable) {
            // Shared MySQL servers without tz tables reject this — cosmetic only.
        }

        $graph = new GraphClient(
            new GraphTokenManager($encryptor),
            new GraphCache((int)$config->get('cache_ttl', 15))
        );

        return ['encryptor' => $encryptor, 'config' => $config, 'graph' => $graph];
    }
}
