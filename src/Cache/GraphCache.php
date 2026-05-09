<?php

namespace App\Cache;

use App\Database\DB;

class GraphCache
{
    private int $defaultTtl;

    public function __construct(int $defaultTtlMinutes = 15)
    {
        $this->defaultTtl = $defaultTtlMinutes * 60;
    }

    public function get(string $key): mixed
    {
        $row = DB::fetchOne(
            'SELECT data FROM cache WHERE cache_key = ? AND expires_at > NOW()',
            [$key]
        );
        return $row ? json_decode($row['data'], true) : null;
    }

    public function set(string $key, mixed $data, ?int $ttlSeconds = null): void
    {
        $ttl = $ttlSeconds ?? $this->defaultTtl;
        $expires = date('Y-m-d H:i:s', time() + $ttl);
        $json = json_encode($data, JSON_UNESCAPED_UNICODE);
        DB::execute(
            'INSERT INTO cache (cache_key, data, expires_at) VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE data = VALUES(data), expires_at = VALUES(expires_at)',
            [$key, $json, $expires]
        );
    }

    public function remember(string $key, callable $callback, ?int $ttlSeconds = null): mixed
    {
        $cached = $this->get($key);
        if ($cached !== null) {
            return $cached;
        }
        $data = $callback();
        $this->set($key, $data, $ttlSeconds);
        return $data;
    }

    public function forget(string $key): void
    {
        DB::execute('DELETE FROM cache WHERE cache_key = ?', [$key]);
    }

    public function flush(): void
    {
        DB::execute('DELETE FROM cache');
    }

    public function cleanup(): void
    {
        DB::execute('DELETE FROM cache WHERE expires_at <= NOW()');
    }
}
