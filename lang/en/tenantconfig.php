<?php

/**
 * English translations for the Config-as-Code (export/import) feature.
 *
 * @return array<string,string>
 */
return [
    'Konfiguration sichern &amp; übertragen' => 'Back up &amp; transfer configuration',
    'Exportiere die operativen Einstellungen als JSON-Datei — als Backup oder um einen weiteren Tenant identisch aufzusetzen. Aus Sicherheitsgründen werden niemals Secrets exportiert (Passwörter, Client-Secret, API-Keys, SMTP-Passwort, Tenant-/App-IDs).'
        => 'Export the operational settings as a JSON file — as a backup, or to set up another tenant identically. For security reasons secrets are never exported (passwords, client secret, API keys, SMTP password, tenant/app IDs).',
    'Konfiguration exportieren'        => 'Export configuration',
    'Lädt eine JSON-Datei herunter.'   => 'Downloads a JSON file.',
    'Konfiguration importieren'        => 'Import configuration',
    '… oder JSON direkt einfügen:'     => '… or paste JSON directly:',
    'Importierte Werte überschreiben die aktuellen Einstellungen. Fortfahren?'
        => 'Imported values will overwrite the current settings. Continue?',
    'Nur bekannte, nicht-sensible Einstellungen werden übernommen; alles andere wird ignoriert.'
        => 'Only known, non-sensitive settings are applied; everything else is ignored.',

    // Flash messages
    'Die Konfigurationsdatei ist zu groß (max. 256 KB).' => 'The configuration file is too large (max. 256 KB).',
    'Keine Konfiguration zum Importieren angegeben.'     => 'No configuration provided to import.',
    'Ungültiges JSON-Format.'                            => 'Invalid JSON format.',
    'Diese Konfigurationsdatei stammt aus einer neueren Version und kann nicht importiert werden.'
        => 'This configuration file is from a newer version and cannot be imported.',
    'Die Konfigurationsdatei hat ein ungültiges Format.' => 'The configuration file has an invalid format.',
    ':n Einstellung(en) importiert. :skipped übersprungen (unbekannt oder gesperrt).'
        => ':n setting(s) imported. :skipped skipped (unknown or locked).',
];
