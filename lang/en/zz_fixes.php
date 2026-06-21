<?php

/**
 * Fixes from the module-by-module review: full-sentence keys for info texts
 * that were previously split around inline <a>/<em> markup with German
 * verb-final word order (which does not translate word-by-word). These use a
 * :link / :section placeholder so the markup is injected after translation
 * (rendered via raw t() at the view site — no user data involved).
 *
 * @return array<string,string>
 */
return [
    'Erweiterte Teams-Einstellungen (Gäste, externe Channels) werden über das :link verwaltet.'
        => 'Advanced Teams settings (guests, external channels) are managed via the :link.',
    'Vollzugriff (Full Access) und &bdquo;Senden als&ldquo;-Berechtigungen werden über das :link verwaltet.'
        => 'Full Access and &bdquo;Send As&ldquo; permissions are managed via the :link.',
    'Diese Einstellung wird im :link unter :section verwaltet.'
        => 'This setting is managed in the :link under :section.',
    'Einstellungen → OneDrive' => 'Settings → OneDrive',
];
