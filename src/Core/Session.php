<?php

namespace App\Core;

class Session
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            // Treat the request as HTTPS unless HTTPS is explicitly 'off'. Also
            // honour a TLS-terminating proxy via X-Forwarded-Proto. Avoids both
            // (a) marking the cookie secure on plain HTTP (HTTPS='off') and
            // (b) failing to mark it secure behind a reverse proxy.
            $https = $_SERVER['HTTPS'] ?? '';
            $fwd   = strtolower($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '');
            $secure = ($https !== '' && strtolower($https) !== 'off') || $fwd === 'https';

            ini_set('session.use_strict_mode', '1');
            session_set_cookie_params([
                'lifetime' => 0,
                'path'     => '/',
                'secure'   => $secure,
                'httponly' => true,
                // 'Lax' (not 'Strict') is required so the session cookie is sent on
                // the top-level redirect back from login.microsoftonline.com — with
                // 'Strict' the OAuth callback loses the session, the state check
                // fails, and Microsoft sign-in loops back to /login. CSRF stays
                // covered by the app's per-request CSRF tokens (+ Lax blocks
                // cross-site POST cookies anyway).
                'samesite' => 'Lax',
            ]);
            session_start();
        }
    }

    public static function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public static function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    public static function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public static function destroy(): void
    {
        $_SESSION = [];
        session_destroy();
    }

    public static function flash(string $key, mixed $value): void
    {
        $_SESSION['_flash'][$key] = $value;
    }

    public static function getFlash(string $key, mixed $default = null): mixed
    {
        $val = $_SESSION['_flash'][$key] ?? $default;
        unset($_SESSION['_flash'][$key]);
        return $val;
    }

    public static function regenerate(): void
    {
        session_regenerate_id(true);
    }
}
