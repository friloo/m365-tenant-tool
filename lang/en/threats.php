<?php

/**
 * English translations for the security / threat dashboard views:
 *   dashboard, securescore, defenderalerts, riskysignins, mfafatigue,
 *   insiderthreat, phishingsim, signinlog.
 *
 * Keys are the exact German source strings. Common shared terms (e.g.
 * "Benutzer", "Geräte", "Status", "Hoch", "Mittel", "Niedrig", "Kritisch",
 * "Standort", "Schweregrad", "Kategorie", "Zeitraum", "Alle", "Filter",
 * "Zurücksetzen", "Filtern", "Gesamt", "Aktionen", "Lizenzen") already live in
 * the central /lang/en.php map, which wins on collisions; they are intentionally
 * not duplicated here.
 */

return [
    // ── Dashboard: widget config ────────────────────────────────
    'Verzeichnis & Identität'              => 'Directory & Identity',
    'Sicherheit & Geräte'                  => 'Security & Devices',
    'Charts & Sicherheitsstatus'           => 'Charts & Security Status',
    'Info-Panels'                          => 'Info Panels',
    'Schnellzugriff'                       => 'Quick Access',
    'Widgets'                              => 'Widgets',
    'Dashboard-Widgets'                    => 'Dashboard Widgets',
    'Einstellungen werden im Browser gespeichert.' => 'Settings are stored in your browser.',

    // ── Dashboard: metric cards ─────────────────────────────────
    'Benutzer gesamt'                      => 'Total users',
    'aktiv'                                => 'active',
    'Lizenz-Produkte'                      => 'License products',
    'Abonnierte SKUs'                      => 'Subscribed SKUs',
    'MFA-Abdeckung'                        => 'MFA coverage',
    'Keine Daten'                          => 'No data',
    'aktive Richtlinien'                   => 'active policies',
    'nicht konform'                        => 'non-compliant',
    'Alle konform'                         => 'All compliant',
    'Intune verwaltet'                     => 'Intune managed',
    'Risikobenutzer'                       => 'Risky users',
    'Aktive Risiken'                       => 'Active risks',
    'Defender Alerts'                      => 'Defender alerts',
    'Offen / In Bearbeitung'               => 'Open / In progress',
    'Gruppen & Teams'                      => 'Groups & Teams',
    'Im Verzeichnis'                       => 'In directory',

    // ── Dashboard: charts + security status ─────────────────────
    'Lizenz-Nutzung'                       => 'License usage',
    'Keine Lizenzdaten verfügbar'          => 'No license data available',
    'Sicherheitsstatus'                    => 'Security status',
    'Gut'                                  => 'Good',
    'Ausbaufähig'                          => 'Needs improvement',
    'Keine Richtlinien!'                   => 'No policies!',
    'Wenige'                               => 'Few',
    'Konfiguriert'                         => 'Configured',
    'Keine Risiken'                        => 'No risks',
    'Prüfen'                               => 'Review',
    'Nicht konforme Geräte'                => 'Non-compliant devices',
    'Offene Defender Alerts'               => 'Open Defender alerts',
    'Keine offen'                          => 'None open',
    'Security Posture öffnen'              => 'Open Security Posture',

    // ── Dashboard: info panels ──────────────────────────────────
    'Verzeichnis & Identitäten'            => 'Directory & Identities',
    'Gastbenutzer'                         => 'Guest users',
    'Admin-Zuweisungen'                    => 'Admin assignments',
    'Teams im Tenant'                      => 'Teams in tenant',
    'Gruppen gesamt'                       => 'Total groups',
    'Inaktive Konten'                      => 'Inactive accounts',
    'öffnen'                               => 'open',
    'Gäste'                                => 'Guests',
    'Admin-Rollen'                         => 'Admin roles',
    'Dienste & Kommunikation'              => 'Services & Communication',
    'Dienst-Vorfälle'                      => 'Service incidents',
    'Message Center'                       => 'Message Center',
    'Aktive Nachrichten'                   => 'Active messages',
    'Postfächer'                           => 'Mailboxes',
    'Modul öffnen'                         => 'Open module',
    'EXO Migration'                        => 'EXO Migration',
    'Readiness prüfen'                     => 'Check readiness',
    'Dienststatus'                         => 'Service health',
    'Mail Flow'                            => 'Mail Flow',
    'Nutzungsaktivität'                    => 'Usage activity',
    '30 Tage'                              => '30 days',
    'Adoptions-Report'                     => 'Adoption report',
    'Teams-Nutzung'                        => 'Teams usage',

    // ── Dashboard: quick access ─────────────────────────────────
    'Lizenz-Berater'                       => 'License advisor',
    'Sign-in-Log'                          => 'Sign-in log',
    'Freigaben'                            => 'Sharing',

    // ── Secure Score ────────────────────────────────────────────
    'Keine Secure-Score-Daten verfügbar'   => 'No Secure Score data available',
    'Microsoft hat noch keinen Secure-Score-Snapshot für diesen Tenant erzeugt.'
        => 'Microsoft has not yet generated a Secure Score snapshot for this tenant.',
    'Microsoft Secure Score'               => 'Microsoft Secure Score',
    'von :n Punkten'                       => 'of :n points',
    'Stand:'                               => 'As of:',
    'Score-Verlauf (30 Tage)'              => 'Score history (30 days)',
    'Keine Verlaufsdaten vorhanden'        => 'No history data available',
    'Kontrollpunkte nach Kategorie'        => 'Controls by category',
    'Kontrollpunkt'                        => 'Control',
    'Punkte'                               => 'Points',
    'Max'                                  => 'Max',
    'Fortschritt'                          => 'Progress',
    'Keine Kontrollpunkte verfügbar. Berechtigung'
        => 'No controls available. Check permission',
    'oder erweiterte Security-Rollen prüfen.'
        => 'or extended security roles.',
    'Aktueller Score'                      => 'Current score',
    'Max. Score'                           => 'Max. score',

    // ── Defender Alerts ─────────────────────────────────────────
    'Gesamt offen'                         => 'Total open',
    'Aktive Warnungen'                     => 'Active alerts',
    'Hohe Schwere'                         => 'High severity',
    'Mittlere Schwere'                     => 'Medium severity',
    'Niedrige Schwere'                     => 'Low severity',
    'Keine Defender-Daten verfügbar'       => 'No Defender data available',
    'Aktuell keine Defender-Warnungen — alles ruhig.'
        => 'No Defender alerts at the moment — all quiet.',
    ':n kritische Sicherheitswarnungen erfordern sofortige Aufmerksamkeit'
        => ':n critical security alerts require immediate attention',
    'Aktive Sicherheitswarnungen'          => 'Active security alerts',
    'Warnungen suchen…'                    => 'Search alerts…',
    'Titel'                                => 'Title',
    'Erstellt am'                          => 'Created on',
    'Letztes Update'                       => 'Last update',
    'Neu'                                  => 'New',
    'In Bearbeitung'                       => 'In progress',
    'Gelöst'                               => 'Resolved',
    'Warnung als gelöst markieren?'        => 'Mark alert as resolved?',
    'Lösen'                                => 'Resolve',

    // ── Risky Sign-ins ──────────────────────────────────────────
    'Warum sehe ich nichts?'               => 'Why do I see nothing?',
    ':n Benutzer mit hohem Risiko!'        => ':n users at high risk!',
    'Diese Konten sollten sofort überprüft und gesichert werden.'
        => 'These accounts should be reviewed and secured immediately.',
    'Aktuell gefährdet'                    => 'Currently at risk',
    'Hohes Risiko'                         => 'High risk',
    'Kritische Benutzer'                   => 'Critical users',
    'Mittleres Risiko'                     => 'Medium risk',
    'Überwachungsbedarf'                   => 'Needs monitoring',
    'Erkennungen (24h)'                    => 'Detections (24h)',
    'Neue Risikoereignisse'                => 'New risk events',
    'Erkennungen'                          => 'Detections',
    'Risiko-Anmeldungen'                   => 'Risky sign-ins',
    'Benutzer suchen…'                     => 'Search users…',
    'Risikostufe'                          => 'Risk level',
    'Risikodetail'                         => 'Risk detail',
    'Zuletzt aktualisiert'                 => 'Last updated',
    'Kompromittiert'                       => 'Compromised',
    'Risiko für diesen Benutzer zurücksetzen?'
        => 'Reset risk for this user?',
    'Benutzer als kompromittiert markieren?'
        => 'Mark user as compromised?',
    'Keine Risikobenutzer gefunden — Berechtigungen prüfen (IdentityRiskyUser.Read.All)'
        => 'No risky users found — check permissions (IdentityRiskyUser.Read.All)',
    'Erkennung suchen…'                    => 'Search detection…',
    'Zeitpunkt'                            => 'Time',
    'Ereignistyp'                          => 'Event type',
    'IP-Adresse'                           => 'IP address',
    'Versteckt'                            => 'Hidden',
    'Keine Risikoerkennungen (IdentityRiskEvent.Read.All prüfen)'
        => 'No risk detections (check IdentityRiskEvent.Read.All)',
    'Keine risikobehafteten Anmeldungen gefunden'
        => 'No risky sign-ins found',
    'AuditLog.Read.All und IdentityRiskEvent.Read.All Berechtigungen prüfen'
        => 'Check AuditLog.Read.All and IdentityRiskEvent.Read.All permissions',
    'Anmeldung suchen…'                    => 'Search sign-in…',
    'Risikozustand'                        => 'Risk state',
    'Gefährdet'                            => 'At risk',

    // ── MFA Fatigue ─────────────────────────────────────────────
    'MFA-Fatigue:'                         => 'MFA fatigue:',
    'ein Angreifer hat das Passwort und triggert wiederholt MFA-Pushs, bis der genervte User „Approve" tippt. Wir gruppieren MFA-Denials in 30-Minuten-Cluster — ≥ 5 Denials sind verdächtig, mit nachfolgendem Success ist es ein wahrscheinlich erfolgreicher Angriff.'
        => 'an attacker has the password and repeatedly triggers MFA pushes until the annoyed user taps "Approve". We group MFA denials into 30-minute clusters — ≥ 5 denials are suspicious, and with a following success it is a likely successful attack.',
    'Letzte :n Tage'                       => 'Last :n days',
    'Letzte :n Std.'                       => 'Last :n hrs',
    'Neu scannen'                          => 'Rescan',
    'MFA-Denials gesamt'                   => 'Total MFA denials',
    'im Zeitraum'                          => 'in the period',
    'Verdächtige Cluster'                  => 'Suspicious clusters',
    '≥ 5 Denials in 30 min'                => '≥ 5 denials in 30 min',
    'Erfolgreich (Approve!)'               => 'Successful (Approve!)',
    'Sofort-Reaktion nötig'                => 'Immediate response needed',
    'Keine MFA-Fatigue-Cluster im Zeitraum.'
        => 'No MFA fatigue clusters in the period.',
    'Erstes Denial'                        => 'First denial',
    'Letztes Denial'                       => 'Last denial',
    'Approve am :date'                     => 'Approve on :date',
    'Verdächtig'                           => 'Suspicious',
    'Reaktion auf einen erfolgreichen Fatigue-Angriff:'
        => 'Response to a successful fatigue attack:',
    'Konto sperren, alle aktiven Sitzungen revoken (im Benutzer-Detail unter'
        => 'Block the account, revoke all active sessions (in the user detail under',
    '), Passwort-Reset erzwingen, Inbox-Regeln prüfen ('
        => '), force a password reset, review inbox rules (',
    'Auto-Forward-Audit'                   => 'Auto-Forward Audit',
    '), zuletzt erteilte App-Consents prüfen ('
        => '), review recently granted app consents (',
    'OAuth-App-Audit'                      => 'OAuth App Audit',
    '). Langfristig: zu Number-Matching umstellen oder FIDO2 erzwingen.'
        => '). Long term: switch to number matching or enforce FIDO2.',

    // ── Insider Threat ──────────────────────────────────────────
    'Light-Insider-Threat-Detection.'      => 'Light insider threat detection.',
    'Statistische Anomalien pro User aus Sign-in- und Audit-Logs. Signale: Off-Hours-Anmeldungen, viele Länder, Mass-Downloads (≥ 50 Files/h), Mass-Mail-Send (≥ 100/h), viele Lösch-Events, viele Sharing-Events. Echtes Insider-Risk-Management (Microsoft Purview) ist umfangreicher und lizenz-pflichtig, aber diese Light-Variante deckt die häufigsten Signale ab.'
        => 'Statistical anomalies per user from sign-in and audit logs. Signals: off-hours sign-ins, many countries, mass downloads (≥ 50 files/h), mass mail send (≥ 100/h), many delete events, many sharing events. True insider risk management (Microsoft Purview) is more comprehensive and requires a license, but this light variant covers the most common signals.',
    'User analysiert'                      => 'Users analyzed',
    'High-Risk (Score ≥ 50)'               => 'High risk (score ≥ 50)',
    'Top-50 User nach Risk-Score'          => 'Top 50 users by risk score',
    'Keine User-Aktivität im Zeitraum.'    => 'No user activity in the period.',
    'Länder'                               => 'Countries',
    'Signale'                              => 'Signals',
    'unauffällig'                          => 'inconspicuous',

    // ── Phishing Simulation ─────────────────────────────────────
    'Phishing-Simulationen aus Microsoft Defender Attack Simulation Training.'
        => 'Phishing simulations from Microsoft Defender Attack Simulation Training.',
    'Voraussetzung:'                       => 'Requirement:',
    '(in E5 / M365 E5 enthalten). Im'      => '(included in E5 / M365 E5). The',
    'Handbuch'                             => 'manual',
    'findest du eine ausführliche Anleitung zum Aufsetzen einer Simulation.'
        => 'contains a detailed guide on setting up a simulation.',
    'Durchgeführte Simulationen'           => 'Conducted simulations',
    'Keine Phishing-Simulationen gefunden. Im'
        => 'No phishing simulations found. In the',
    'Defender-Portal'                      => 'Defender portal',
    'eine Simulation anlegen — Schritt-für-Schritt-Anleitung im Handbuch unter'
        => 'create a simulation — step-by-step guide in the manual under',
    'Phishing-Simulationen mit Microsoft'  => 'Phishing simulations with Microsoft',
    'Empfänger'                            => 'Recipients',
    'Klicks'                               => 'Clicks',
    'Gemeldet'                             => 'Reported',
    'Training'                             => 'Training',
    'Gestartet'                            => 'Started',
    'Defender Attack Simulator öffnen'     => 'Open Defender Attack Simulator',
    'Anleitung im Handbuch'                => 'Guide in the manual',

    // ── Sign-in Log ─────────────────────────────────────────────
    'Anmeldungen'                          => 'Sign-ins',
    'Anmeldungen OK'                       => 'Sign-ins OK',
    'Fehler aufgetreten'                   => 'Errors occurred',
    'Keine Fehler'                         => 'No errors',
    'Eindeutige Benutzer'                  => 'Unique users',
    'verschiedene Konten'                  => 'distinct accounts',
    'Eindeutige IPs'                       => 'Unique IPs',
    'verschiedene Adressen'                => 'distinct addresses',
    'Name oder UPN'                        => 'Name or UPN',
    '1 Tag'                                => '1 day',
    '7 Tage'                               => '7 days',
    '14 Tage'                              => '14 days',
    'Ergebnis'                             => 'Result',
    'Nur Erfolg'                           => 'Success only',
    'Nur Fehler'                           => 'Failure only',
    'Land'                                 => 'Country',
    'Keine'                                => 'None',
    'CSV-Export'                           => 'CSV export',
    'Top Apps'                             => 'Top apps',
    'Top Länder'                           => 'Top countries',
    'Tabelle durchsuchen…'                 => 'Search table…',
    ':n Einträge'                          => ':n entries',
    'Limit: 200'                           => 'Limit: 200',
    'Keine Ergebnisse für diese Filter'    => 'No results for these filters',
    'Filter zurücksetzen'                  => 'Reset filters',
    'Zur Lösung'                           => 'Go to solution',
    'Keine Anmeldedaten im gewählten Zeitraum'
        => 'No sign-in data in the selected period',
    'Datum/Uhrzeit'                        => 'Date/Time',
    'IP / Standort'                        => 'IP / Location',
    'Gerät (OS)'                           => 'Device (OS)',
    'CA-Fehler'                            => 'CA error',
    'Es werden maximal 200 Einträge angezeigt. Verwenden Sie engere Filter oder den CSV-Export für vollständige Daten.'
        => 'A maximum of 200 entries are shown. Use narrower filters or the CSV export for complete data.',
];
