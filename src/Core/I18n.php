<?php

namespace App\Core;

/**
 * Lightweight internationalisation layer.
 *
 * The application's source language is German (de) — i.e. the German strings
 * live directly in the code and views. Translations are stored as flat
 * "German source string" => "translation" maps in /lang/<locale>.php. When the
 * active locale is the source language, t() simply returns the key unchanged,
 * so no German translation file is required.
 *
 * Locale resolution order (first match wins):
 *   1. ?lang=xx query parameter (also persisted to session + cookie)
 *   2. session value (set during this session)
 *   3. lang cookie (persists across sessions / before login)
 *   4. configured tenant default (app_config: default_language)
 *   5. SOURCE ('de')
 */
class I18n
{
    /** The language the source strings are written in. */
    public const SOURCE = 'de';

    /** locale code => native display name. */
    public const SUPPORTED = [
        'de' => 'Deutsch',
        'en' => 'English',
    ];

    private static string $locale = self::SOURCE;
    private static array $messages = [];
    private static bool $loaded = false;

    /**
     * Resolve and lock in the active locale for this request.
     *
     * @param string|null $configDefault Tenant-wide default (app_config).
     */
    public static function init(?string $configDefault = null): void
    {
        $locale = null;

        // 1. Explicit switch via ?lang= — persist for the rest of the session.
        $param = $_GET['lang'] ?? null;
        if (is_string($param) && self::isSupported($param)) {
            $locale = $param;
            Session::set('locale', $locale);
            self::setCookie($locale);
        }

        // 2. Session.
        if ($locale === null) {
            $s = Session::get('locale');
            if (is_string($s) && self::isSupported($s)) {
                $locale = $s;
            }
        }

        // 3. Cookie.
        if ($locale === null) {
            $c = $_COOKIE['lang'] ?? null;
            if (is_string($c) && self::isSupported($c)) {
                $locale = $c;
            }
        }

        // 4. Configured tenant default.
        if ($locale === null && is_string($configDefault) && self::isSupported($configDefault)) {
            $locale = $configDefault;
        }

        self::setLocale($locale ?? self::SOURCE);
    }

    public static function setLocale(string $locale): void
    {
        if (!self::isSupported($locale)) {
            $locale = self::SOURCE;
        }
        if ($locale !== self::$locale) {
            self::$locale   = $locale;
            self::$messages = [];
            self::$loaded   = false;
        }
    }

    public static function locale(): string
    {
        return self::$locale;
    }

    public static function isSupported(string $locale): bool
    {
        return isset(self::SUPPORTED[$locale]);
    }

    /** @return array<string,string> locale code => native name */
    public static function supported(): array
    {
        return self::SUPPORTED;
    }

    /**
     * Translate a German source string into the active locale.
     *
     * Placeholders of the form :name are replaced from $params, e.g.
     *   t('Hallo :name', ['name' => 'Friedrich'])
     *
     * @param array<string,string|int> $params
     */
    public static function t(string $key, array $params = []): string
    {
        $msg = $key;

        if (self::$locale !== self::SOURCE) {
            self::load();
            if (isset(self::$messages[$key]) && self::$messages[$key] !== '') {
                $msg = self::$messages[$key];
            }
        }

        if ($params) {
            $repl = [];
            foreach ($params as $k => $v) {
                $repl[':' . $k] = (string)$v;
            }
            $msg = strtr($msg, $repl);
        }

        return $msg;
    }

    /**
     * Build a URL that switches to the given locale while preserving the
     * current path and any other query parameters.
     */
    public static function switchUrl(string $locale): string
    {
        $uri  = $_SERVER['REQUEST_URI'] ?? '/';
        $path = strtok($uri, '?') ?: '/';
        parse_str((string)parse_url($uri, PHP_URL_QUERY), $q);
        unset($q['refresh']); // don't force a Graph refetch just for a language change
        $q['lang'] = $locale;
        return $path . '?' . http_build_query($q);
    }

    private static function load(): void
    {
        if (self::$loaded) {
            return;
        }
        self::$loaded = true;
        $file = BASE_PATH . '/lang/' . self::$locale . '.php';
        if (is_file($file)) {
            $data = require $file;
            if (is_array($data)) {
                self::$messages = $data;
            }
        }
    }

    private static function setCookie(string $locale): void
    {
        if (headers_sent()) {
            return;
        }
        $https  = $_SERVER['HTTPS'] ?? '';
        $fwd    = strtolower($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '');
        $secure = ($https !== '' && strtolower($https) !== 'off') || $fwd === 'https';
        setcookie('lang', $locale, [
            'expires'  => time() + 31536000, // 1 year
            'path'     => '/',
            'secure'   => $secure,
            'httponly' => false,
            'samesite' => 'Lax',
        ]);
        $_COOKIE['lang'] = $locale;
    }
}
