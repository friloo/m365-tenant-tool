<?php

namespace App\Modules\Api;

use App\Database\DB;

/**
 * Stateless authentication for the REST API. Clients send their key as
 * an `X-Api-Key` request header. The key is sha256-hashed before lookup
 * so the DB row never contains the plaintext value.
 *
 * The key is intentionally NOT accepted as a query string: URLs end up in
 * web-server/proxy access logs and Referer headers, which would leak the key.
 *
 * Scopes (comma-separated in app_api_keys.scopes):
 *   read   — GET endpoints (default)
 *   write  — POST/PATCH/DELETE endpoints (not used yet)
 *   admin  — manage other API keys
 */
class ApiAuth
{
    /** Reject after this many failed key attempts from one IP within the window. */
    private const MAX_FAILURES = 30;
    private const WINDOW_MINUTES = 5;

    /** Hash a plaintext key for storage / lookup. */
    public static function hash(string $plain): string
    {
        return hash('sha256', $plain);
    }

    /**
     * Authenticate the current request. Returns the matched key row, or
     * sends a 401/403/429 JSON response and exits.
     */
    public static function require(string $scope = 'read'): array
    {
        $ip = self::clientIp();
        self::enforceRateLimit($ip);

        $raw = self::extractKey();
        if ($raw === '') {
            self::recordFailure($ip);
            self::reject(401, 'missing_api_key', 'Header X-Api-Key fehlt.');
        }

        $row = null;
        try {
            $row = DB::fetchOne(
                "SELECT id, name, scopes, revoked_at FROM app_api_keys WHERE key_hash = ?",
                [self::hash($raw)]
            );
        } catch (\Throwable $e) {
            self::reject(500, 'db_error', $e->getMessage());
        }
        if (!$row) {
            self::recordFailure($ip);
            self::reject(401, 'invalid_api_key', 'API-Key unbekannt.');
        }
        if ($row['revoked_at']) {
            self::recordFailure($ip);
            self::reject(403, 'revoked', 'API-Key wurde widerrufen.');
        }

        $scopes = array_map('trim', explode(',', (string)$row['scopes']));
        if (!in_array($scope, $scopes, true)) {
            self::reject(403, 'insufficient_scope', 'Erforderlicher Scope: ' . $scope);
        }

        try {
            DB::execute("UPDATE app_api_keys SET last_used = NOW() WHERE id = ?", [(int)$row['id']]);
        } catch (\Throwable) {}

        return $row;
    }

    /** Send a JSON error and terminate. */
    public static function reject(int $code, string $error, string $message): never
    {
        http_response_code($code);
        if (!headers_sent()) header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['error' => $error, 'message' => $message], JSON_UNESCAPED_UNICODE);
        exit;
    }

    private static function extractKey(): string
    {
        // Header only — never the query string (would leak the key into logs).
        return trim((string)($_SERVER['HTTP_X_API_KEY'] ?? ''));
    }

    private static function clientIp(): string
    {
        // REMOTE_ADDR only — X-Forwarded-* is client-spoofable and would let an
        // attacker dodge the throttle by rotating a fake header.
        return (string)($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
    }

    /** 429 if this IP has exceeded the failed-attempt budget in the window. */
    private static function enforceRateLimit(string $ip): void
    {
        try {
            $row = DB::fetchOne(
                "SELECT COUNT(*) AS c FROM api_auth_failures
                 WHERE ip_address = ? AND attempted_at > DATE_SUB(NOW(), INTERVAL ? MINUTE)",
                [$ip, self::WINDOW_MINUTES]
            );
        } catch (\Throwable) {
            return; // table missing / DB issue → fail open, don't break valid clients
        }
        if ((int)($row['c'] ?? 0) >= self::MAX_FAILURES) {
            self::reject(429, 'rate_limited',
                'Zu viele fehlgeschlagene API-Authentifizierungen. Bitte später erneut versuchen.');
        }
    }

    /** Record a failed key attempt; opportunistically prune old rows. */
    private static function recordFailure(string $ip): void
    {
        try {
            DB::execute("INSERT INTO api_auth_failures (ip_address) VALUES (?)", [$ip]);
            if (random_int(1, 100) === 1) {
                DB::execute("DELETE FROM api_auth_failures WHERE attempted_at < DATE_SUB(NOW(), INTERVAL 1 DAY)");
            }
        } catch (\Throwable) {}
    }

    /** Generate a fresh plaintext key (40 chars hex). */
    public static function generate(): string
    {
        return 'm365_' . bin2hex(random_bytes(20));
    }
}
