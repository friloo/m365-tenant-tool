<?php

/**
 * English translations for the second feature batch:
 * alert webhook (Teams/SIEM), four-eyes approvals, local data retention.
 *
 * @return array<string,string>
 */
return [
    // ── Alert webhook ───────────────────────────────────────────────────────
    'Im Tool öffnen'                  => 'Open in the tool',
    'Keine Webhook-URL konfiguriert.' => 'No webhook URL configured.',
    'Test-Benachrichtigung'           => 'Test notification',
    'Diese Nachricht bestätigt, dass der Alert-Webhook korrekt konfiguriert ist.'
        => 'This message confirms that the alert webhook is configured correctly.',
    'Test-Benachrichtigung an den Webhook gesendet.' => 'Test notification sent to the webhook.',
    'Webhook-Test fehlgeschlagen. URL und Format prüfen.' => 'Webhook test failed. Check the URL and format.',
    'Alert-Webhook (Teams / SIEM)' => 'Alert webhook (Teams / SIEM)',
    'Sende Sicherheits-Warnungen zusätzlich an einen externen Endpunkt — einen Microsoft-Teams-Webhook oder ein generisches JSON-Ziel (SIEM/Sentinel/Slack-kompatibel).'
        => 'Also send security alerts to an external endpoint — a Microsoft Teams webhook or a generic JSON target (SIEM/Sentinel/Slack-compatible).',
    'Webhook-URL (https)'             => 'Webhook URL (https)',
    'Format'                          => 'Format',
    'Microsoft Teams'                 => 'Microsoft Teams',
    'Generisch (JSON)'                => 'Generic (JSON)',
    'Ab Stufe'                        => 'From level',
    'Test-Benachrichtigung senden'    => 'Send test notification',
    'Speichere zuerst die URL, dann teste.' => 'Save the URL first, then test.',

    // ── Local data retention (GDPR) ─────────────────────────────────────────
    'Datenschutz' => 'Privacy',
    'Datenschutz &amp; Aufbewahrung' => 'Privacy &amp; retention',
    'Lokale Aufbewahrung (Tage)' => 'Local retention (days)',
    'Lokal gespeicherte Verlaufs-/PII-Daten (Audit, Sign-ins, Freigaben, Snapshots) älter als diese Frist werden täglich gelöscht. 0 = unbegrenzt aufbewahren.'
        => 'Locally stored history/PII data (audit, sign-ins, shares, snapshots) older than this period is deleted daily. 0 = keep indefinitely.',
    'Lokale Daten löschen' => 'Delete local data',
    'Diese Aktionen betreffen nur die lokal im Tool gespeicherten Daten — Konfiguration, Benutzerzugänge und API-Schlüssel bleiben erhalten.'
        => 'These actions only affect data stored locally in the tool — configuration, user access and API keys are preserved.',
    'Lokale Datensätze älter als die Aufbewahrungsfrist jetzt löschen?' => 'Delete local records older than the retention period now?',
    'Alte Daten jetzt bereinigen' => 'Clean up old data now',
    'Wendet die oben gesetzte Aufbewahrungsfrist sofort an.' => 'Applies the retention period set above immediately.',
    'Unwiderruflich: löscht alle lokal abgeleiteten Daten (Audit, Sign-ins, Freigaben, Snapshots, Cache, Benachrichtigungen).'
        => 'Irreversible: deletes all locally derived data (audit, sign-ins, shares, snapshots, cache, notifications).',
    'Wirklich ALLE lokalen Tenant-Daten unwiderruflich löschen?' => 'Really delete ALL local tenant data irreversibly?',
    'LÖSCHEN eintippen' => 'type DELETE',
    'Alle lokalen Tenant-Daten löschen' => 'Delete all local tenant data',
    'Bestätigung fehlt — bitte LÖSCHEN eintippen.' => 'Confirmation missing — please type DELETE.',
    'Alle lokalen Tenant-Daten gelöscht (:n Datensätze). Konfiguration und Benutzerzugänge bleiben erhalten.'
        => 'All local tenant data deleted (:n records). Configuration and user access are preserved.',
    'Keine Aufbewahrungsfrist gesetzt (0 = unbegrenzt). Bitte zuerst eine Frist konfigurieren.'
        => 'No retention period set (0 = unlimited). Please configure a period first.',
    ':n lokale Datensätze älter als :days Tage gelöscht.' => ':n local records older than :days days deleted.',
    // cron
    'Lokale Daten-Aufbewahrung' => 'Local data retention',
    'Löscht lokal gespeicherte PII/Verlaufsdaten (Audit, Sign-ins, Freigaben, Snapshots) älter als die konfigurierte Aufbewahrungsfrist. Deaktiviert, solange die Frist 0 ist.'
        => 'Deletes locally stored PII/history data (audit, sign-ins, shares, snapshots) older than the configured retention period. Disabled while the period is 0.',
    'Deaktiviert (Aufbewahrungsfrist = 0).' => 'Disabled (retention period = 0).',
    ':n Datensätze älter als :days Tage gelöscht.' => ':n records older than :days days deleted.',
    // four-eyes setting label (in privacy tab)
    'Vier-Augen-Prinzip für kritische Aktionen' => 'Four-eyes principle for critical actions',
    'Wenn aktiv, müssen besonders kritische Aktionen (Konto deaktivieren, Gerät zurücksetzen/löschen, MFA zurücksetzen) von einem zweiten Administrator freigegeben werden, bevor sie ausgeführt werden.'
        => 'When enabled, especially critical actions (disable account, reset/wipe device, reset MFA) must be approved by a second administrator before they run.',
];
