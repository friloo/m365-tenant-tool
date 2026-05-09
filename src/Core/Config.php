<?php

namespace App\Core;

use App\Database\DB;
use App\Encryption\Encryptor;

class Config
{
    private static ?Config $instance = null;
    private array $cache = [];
    private ?Encryptor $encryptor = null;

    private function __construct() {}

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function setEncryptor(Encryptor $enc): void
    {
        $this->encryptor = $enc;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        if (isset($this->cache[$key])) {
            return $this->cache[$key];
        }
        try {
            $row = DB::fetchOne('SELECT value, is_encrypted FROM app_config WHERE `key` = ?', [$key]);
            if (!$row) {
                return $default;
            }
            $value = $row['value'];
            if ($row['is_encrypted'] && $this->encryptor && $value !== null) {
                $value = $this->encryptor->decrypt($value);
            }
            $this->cache[$key] = $value;
            return $value;
        } catch (\Throwable) {
            return $default;
        }
    }

    public function set(string $key, string $value, bool $encrypt = false): void
    {
        if ($encrypt && $this->encryptor) {
            $stored = $this->encryptor->encrypt($value);
        } else {
            $stored = $value;
        }
        DB::execute(
            'INSERT INTO app_config (`key`, value, is_encrypted) VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE value = VALUES(value), is_encrypted = VALUES(is_encrypted)',
            [$key, $stored, $encrypt ? 1 : 0]
        );
        unset($this->cache[$key]);
    }

    public function clearCache(): void
    {
        $this->cache = [];
    }
}
