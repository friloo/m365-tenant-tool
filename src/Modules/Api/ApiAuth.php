<?php

namespace App\Modules\Api;

use App\Database\DB;

/**
 * Stateless authentication for the REST API. Clients send their key as
 * an `X-Api-Key` request header (or as `?api_key=` query string for
 * one-off tests). The key is sha256-hashed before lookup so the DB
 * row never contains the plaintext value.
 *
 * Scopes (comma-separated in app_api_keys.scopes):
 *   read   — GET endpoints (default)
 *   write  — POST/PATCH/DELETE endpoints (not used yet)
 *   admin  — manage other API keys
 */
class ApiAuth
{
    /** Hash a plaintext key for storage / lookup. */
    public static function hash(string $plain): string
    {
        return hash('sha256', $plain);
    }

    /**
     * Authenticate the current request. Returns the matched key row, or
     * sends a 401/403 JSON response and exits.
     */
    public static function require(string $scope = 'read'): array
    {
        $raw = self::extractKey();
        if ($raw === '') self::reject(401, 'missing_api_key', 'Header X-Api-Key oder ?api_key= fehlt.');

        $row = null;
        try {
            $row = DB::fetchOne(
                "SELECT id, name, scopes, revoked_at FROM app_api_keys WHERE key_hash = ?",
                [self::hash($raw)]
            );
        } catch (\Throwable $e) {
            self::reject(500, 'db_error', $e->getMessage());
        }
        if (!$row) self::reject(401, 'invalid_api_key', 'API-Key unbekannt.');
        if ($row['revoked_at']) self::reject(403, 'revoked', 'API-Key wurde widerrufen.');

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
        $k = $_SERVER['HTTP_X_API_KEY'] ?? '';
        if ($k === '' && !empty($_GET['api_key'])) $k = (string)$_GET['api_key'];
        return trim((string)$k);
    }

    /** Generate a fresh plaintext key (40 chars hex). */
    public static function generate(): string
    {
        return 'm365_' . bin2hex(random_bytes(20));
    }
}
