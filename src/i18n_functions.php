<?php

/**
 * Global translation helpers. Required once from index.php (and CLI bootstrap)
 * so they are available inside every view without an explicit import.
 *
 *   t('Benutzer')         → raw translation (use where output is already escaped
 *                           or inside attributes built with htmlspecialchars)
 *   te('Benutzer')        → translation + HTML-escaped (safe for direct echo)
 */

use App\Core\I18n;
use App\Core\View;

if (!function_exists('t')) {
    /**
     * @param array<string,string|int> $params
     */
    function t(string $key, array $params = []): string
    {
        return I18n::t($key, $params);
    }
}

if (!function_exists('te')) {
    /**
     * Translate and HTML-escape — the safe default for echoing into markup.
     *
     * @param array<string,string|int> $params
     */
    function te(string $key, array $params = []): string
    {
        return View::escape(I18n::t($key, $params));
    }
}
