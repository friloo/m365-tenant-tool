<?php

namespace App\Auth;

class TotpService
{
    private const ALPHA = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    public static function generateSecret(): string
    {
        return self::base32Encode(random_bytes(20));
    }

    public static function base32Encode(string $bytes): string
    {
        $out = '';
        $v = $n = 0;
        foreach (str_split($bytes) as $c) {
            $v = ($v << 8) + ord($c);
            $n += 8;
            while ($n >= 5) {
                $n -= 5;
                $out .= self::ALPHA[($v >> $n) & 31];
            }
        }
        if ($n > 0) {
            $out .= self::ALPHA[($v << (5 - $n)) & 31];
        }
        return $out;
    }

    public static function base32Decode(string $input): string
    {
        $input = strtoupper(preg_replace('/[\s=]/', '', $input));
        $out   = '';
        $v = $n = 0;
        foreach (str_split($input) as $c) {
            $pos = strpos(self::ALPHA, $c);
            if ($pos === false) continue;
            $v = ($v << 5) + $pos;
            $n += 5;
            if ($n >= 8) {
                $n -= 8;
                $out .= chr(($v >> $n) & 0xFF);
            }
        }
        return $out;
    }

    public static function getCode(string $secret, ?int $timeStep = null): string
    {
        $timeStep = $timeStep ?? (int)floor(time() / 30);
        $key      = self::base32Decode($secret);
        $time     = pack('N*', 0) . pack('N*', $timeStep);
        $hmac     = hash_hmac('sha1', $time, $key, true);
        $offset   = ord($hmac[19]) & 0x0F;
        $code     = ((ord($hmac[$offset])     & 0x7F) << 24)
                  | ((ord($hmac[$offset + 1]) & 0xFF) << 16)
                  | ((ord($hmac[$offset + 2]) & 0xFF) << 8)
                  |  (ord($hmac[$offset + 3]) & 0xFF);
        return str_pad((string)($code % 1_000_000), 6, '0', STR_PAD_LEFT);
    }

    public static function verify(string $secret, string $code, int $window = 1): bool
    {
        $code     = preg_replace('/\s/', '', $code);
        $timeStep = (int)floor(time() / 30);
        for ($i = -$window; $i <= $window; $i++) {
            if (hash_equals(self::getCode($secret, $timeStep + $i), $code)) {
                return true;
            }
        }
        return false;
    }

    public static function getUri(string $secret, string $issuer = 'M365 Tenant Tool'): string
    {
        return 'otpauth://totp/' . rawurlencode($issuer)
             . '?secret=' . $secret
             . '&issuer=' . rawurlencode($issuer)
             . '&algorithm=SHA1&digits=6&period=30';
    }

    public static function generateRecoveryCodes(int $count = 8): array
    {
        $codes = [];
        for ($i = 0; $i < $count; $i++) {
            $raw     = bin2hex(random_bytes(6));
            $codes[] = strtoupper(substr($raw, 0, 4) . '-' . substr($raw, 4, 4) . '-' . substr($raw, 8, 4));
        }
        return $codes;
    }

    public static function hashCode(string $code): string
    {
        return hash('sha256', strtoupper(str_replace('-', '', $code)));
    }

    public static function verifyRecoveryCode(string $input, array $storedHashes): int|false
    {
        $hashed = self::hashCode($input);
        foreach ($storedHashes as $i => $stored) {
            if (hash_equals((string)$stored, $hashed)) {
                return $i;
            }
        }
        return false;
    }
}
