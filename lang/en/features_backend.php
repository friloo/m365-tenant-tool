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
];
