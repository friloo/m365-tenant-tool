<?php

namespace App\Core;

class Redirect
{
    public static function to(string $url): never
    {
        // Strip CR/LF/NUL to prevent HTTP response splitting / header injection.
        $url = str_replace(["\r", "\n", "\0"], '', $url);
        header('Location: ' . $url);
        exit;
    }

    public static function back(): never
    {
        // HTTP_REFERER is attacker-controlled — only follow it if it resolves to
        // a same-origin target, otherwise fall back to the app root (open-redirect guard).
        self::to(self::sameOriginOrHome($_SERVER['HTTP_REFERER'] ?? ''));
    }

    /** Return $url only if it is a safe same-origin target, else '/'. */
    private static function sameOriginOrHome(string $url): string
    {
        if ($url === '') return '/';
        $parts = parse_url($url);
        if ($parts === false) return '/';

        if (!isset($parts['host'])) {
            // Relative URL: must be a single-slash absolute path, never "//host" (protocol-relative).
            return (isset($url[0]) && $url[0] === '/' && !str_starts_with($url, '//')) ? $url : '/';
        }

        // Absolute URL: host must match the current request host.
        $host = $_SERVER['HTTP_HOST'] ?? '';
        return ($host !== '' && $parts['host'] === $host) ? $url : '/';
    }

    public static function withFlash(string $url, string $key, string $message): never
    {
        Session::start();
        Session::flash($key, $message);
        self::to($url);
    }
}
