<?php

namespace App\Encryption;

class Encryptor
{
    private string $key;
    private const CIPHER = 'aes-256-gcm';

    public function __construct(string $keyPath)
    {
        if (!file_exists($keyPath)) {
            throw new \RuntimeException('Encryption key file not found: ' . $keyPath);
        }
        $raw = trim(file_get_contents($keyPath));
        $this->key = base64_decode($raw, true) ?: '';
        if (strlen($this->key) !== 32) {
            throw new \RuntimeException('Invalid encryption key length');
        }
    }

    public static function generateKey(string $keyPath): void
    {
        $dir = dirname($keyPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0700, true);
        }
        $key = random_bytes(32);
        file_put_contents($keyPath, base64_encode($key));
        chmod($keyPath, 0600);
    }

    public function encrypt(string $plaintext): string
    {
        $iv = random_bytes(12);
        $tag = '';
        $ciphertext = openssl_encrypt($plaintext, self::CIPHER, $this->key, OPENSSL_RAW_DATA, $iv, $tag, '', 16);
        return base64_encode($iv . $tag . $ciphertext);
    }

    public function decrypt(string $encoded): string
    {
        $data = base64_decode($encoded, true);
        // 12-byte IV + 16-byte tag = 28-byte minimum before any ciphertext.
        if ($data === false || strlen($data) < 28) {
            throw new \RuntimeException('Decryption failed: malformed ciphertext');
        }
        $iv = substr($data, 0, 12);
        $tag = substr($data, 12, 16);
        $ciphertext = substr($data, 28);
        $plain = openssl_decrypt($ciphertext, self::CIPHER, $this->key, OPENSSL_RAW_DATA, $iv, $tag);
        if ($plain === false) {
            throw new \RuntimeException('Decryption failed');
        }
        return $plain;
    }
}
