<?php

/**
 * English translations for review-round feature additions (secret-expiry
 * self-check, config-drift detection, action center).
 *
 * @return array<string,string>
 */
return [
    // App secret-expiry self-check (cron)
    'App-Secret-Ablauf prüfen' => 'Check app secret expiry',
    'Warnt, bevor das Client-Secret/Zertifikat der eigenen App-Registrierung abläuft (sonst verliert das Tool den Zugriff).'
        => "Warns before the tool's own app-registration client secret/certificate expires (otherwise the tool loses access).",
    'Kein Ablaufdatum ermittelbar.' => 'No expiry date could be determined.',
    'OK — läuft in :n Tagen ab.'    => 'OK — expires in :n days.',
    'Client-Secret'                 => 'Client secret',
    ':type der App-Registrierung ist ABGELAUFEN'
        => "The app registration's :type has EXPIRED",
    ':type der App-Registrierung läuft in :n Tagen ab'
        => "The app registration's :type expires in :n days",
    'Erneuere das :type im Microsoft Entra Admin Center, sonst verliert das Tool den Graph-Zugriff.'
        => 'Renew the :type in the Microsoft Entra admin center, otherwise the tool loses Graph access.',
    'Warnung gesendet — :type läuft in :n Tagen ab.'
        => 'Warning sent — :type expires in :n days.',

    // Config-drift detection
    'Snapshot #:id als Baseline für die Drift-Erkennung gesetzt.'
        => 'Snapshot #:id set as the baseline for drift detection.',
    'Snapshot nicht gefunden.' => 'Snapshot not found.',
    'Konfigurations-Drift prüfen' => 'Check configuration drift',
    'Vergleicht den neuesten Tenant-Snapshot mit der gesetzten Baseline und warnt bei Abweichungen sicherheitsrelevanter Einstellungen.'
        => 'Compares the latest tenant snapshot against the pinned baseline and warns about deviations in security-relevant settings.',
    'Keine Baseline gesetzt oder kein neuerer Snapshot.' => 'No baseline set or no newer snapshot.',
    'Keine Abweichung von der Baseline.'                 => 'No deviation from the baseline.',
    ':n Konfigurations-Abweichung(en) von der Baseline'  => ':n configuration deviation(s) from the baseline',
    'Sicherheitsrelevante Tenant-Einstellungen haben sich gegenüber der Baseline (Snapshot #:id) geändert. Details unter Audit-Diff.'
        => 'Security-relevant tenant settings have changed compared to the baseline (snapshot #:id). Details under Audit Diff.',
    ':n Abweichung(en) erkannt — Warnung gesendet.' => ':n deviation(s) detected — warning sent.',
    'Drift-Baseline' => 'Drift baseline',
    'Noch keine Baseline gesetzt. Lege einen bekannten, sicheren Stand als Baseline fest — der Cron-Job warnt dann bei jeder Abweichung.'
        => 'No baseline set yet. Pin a known-good state as the baseline — the cron job then warns on every deviation.',
    'Baseline:' => 'Baseline:',
    ':n Abweichung(en) seit Baseline' => ':n deviation(s) since baseline',
    'Drift anzeigen' => 'Show drift',
    'Keine Abweichung' => 'No deviation',
    'Snapshot als Baseline setzen' => 'Set snapshot as baseline',
    'Als Baseline festlegen' => 'Set as baseline',

    // Action Center / Configuration Score
    'Konfigurations-Center' => 'Configuration center',
    'Microsoft-365-Verbindung konfiguriert' => 'Microsoft 365 connection configured',
    'Tenant-ID, Client-ID und Client-Secret hinterlegen.' => 'Provide tenant ID, client ID and client secret.',
    'Einrichtungs-Assistent abgeschlossen' => 'Setup wizard completed',
    'Geführte Erst-Einrichtung durchlaufen.' => 'Run the guided initial setup.',
    'Compliance-Profil ausgewählt' => 'Compliance profile selected',
    'Branchen-Härtungs-Defaults mit einem Klick anwenden.' => 'Apply industry hardening defaults with one click.',
    'Alarm-E-Mail hinterlegt' => 'Alert email configured',
    'Empfänger für Sicherheits-Warnungen festlegen.' => 'Set a recipient for security alerts.',
    'Backup-Status dokumentiert' => 'Backup status documented',
    'Backup-Lösung für M365-Daten hinterlegen.' => 'Record a backup solution for M365 data.',
    'Drift-Baseline gesetzt' => 'Drift baseline set',
    'Bekannten, sicheren Stand als Baseline festlegen.' => 'Pin a known-good state as the baseline.',
    '2FA für Admin-Login aktiv' => '2FA active for admin login',
    'Admin-Konto mit TOTP absichern.' => 'Secure the admin account with TOTP.',
    'Dein Startpunkt zur Tenant-Konfiguration.' => 'Your starting point for tenant configuration.',
    'Diese Seite bündelt den Sicherheits-Score, den Einrichtungsfortschritt und die wichtigsten nächsten Schritte — jeweils mit Direktlink zur Behebung.'
        => 'This page bundles the security score, setup progress and the most important next steps — each with a direct link to fix it.',
    'Sicherheits-Score' => 'Security score',
    'Score nicht verfügbar — Microsoft-365-Verbindung prüfen.' => 'Score unavailable — check the Microsoft 365 connection.',
    'bestanden' => 'passed',
    'offen' => 'open',
    'Alle Prüfungen ansehen' => 'View all checks',
    'Einrichtungsfortschritt' => 'Setup progress',
    'Nächste empfohlene Schritte' => 'Next recommended steps',
    'Empfehlungen nicht verfügbar — bitte zuerst die Microsoft-365-Verbindung in den Einstellungen konfigurieren.'
        => 'Recommendations unavailable — please configure the Microsoft 365 connection in settings first.',
    'Keine offenen Empfehlungen — gut gemacht!' => 'No open recommendations — well done!',
    'Noch nicht berechnet — wird im Hintergrund erstellt.' => 'Not yet computed — generated in the background.',
    'Jetzt berechnen' => 'Compute now',
    'Die Sicherheitsanalyse wird im Hintergrund berechnet (Cache-Warm-Job) und erscheint in Kürze. Du kannst sie auch sofort berechnen:'
        => 'The security analysis is computed in the background (cache-warm job) and will appear shortly. You can also compute it immediately:',
];
