<?php

/**
 * English translations for the BACKEND (controller/service) layer of the
 * Threats & Devices modules:
 *   - Devices, AccessReview, RiskySignIns, InsiderThreat, SignInAnomaly,
 *     DefenderAlerts, AuditReport, SignInLog, Security
 *
 * Keys are the exact German source strings passed to t(). Only display
 * labels (risk-event labels, finding titles/descriptions, reason text),
 * page titles, CSV column headers/values and Session::flash messages are
 * translated here. API enums, status codes and comparison values stay in
 * the source code untouched.
 *
 * Dynamic values are injected via :param placeholders by I18n::t().
 *
 * @return array<string,string>
 */

return [

    // ── Devices: page titles & CSV export ───────────────────────────────────
    'Geräte'                        => 'Devices',
    'Gerät'                         => 'Device',
    'OS'                            => 'OS',
    'Version'                       => 'Version',
    'Compliance'                    => 'Compliance',
    'Verschlüsselt'                 => 'Encrypted',
    'Letzter Sync'                  => 'Last sync',
    'Registriert'                   => 'Enrolled',

    // ── Devices: flash messages ─────────────────────────────────────────────
    'Synchronisation angefordert. Das Gerät wird sich beim nächsten Check-In aktualisieren.'
        => 'Synchronization requested. The device will update at its next check-in.',
    'Synchronisation fehlgeschlagen: Fehlende Berechtigung <strong>DeviceManagementManagedDevices.PrivilegedOperations.All</strong>. Bitte in Azure AD → App-Registrierungen → deine App → API-Berechtigungen hinzufügen und Admin-Consent erteilen. <a href=":url" target="_blank" rel="noopener noreferrer">Azure AD öffnen →</a>'
        => 'Synchronization failed: missing permission <strong>DeviceManagementManagedDevices.PrivilegedOperations.All</strong>. Add it in Azure AD → App registrations → your app → API permissions and grant admin consent. <a href=":url" target="_blank" rel="noopener noreferrer">Open Azure AD →</a>',
    'Synchronisation fehlgeschlagen: :msg'
        => 'Synchronization failed: :msg',
    'Gerät wurde zurückgesetzt (Retire). Unternehmensdaten wurden entfernt.'
        => 'Device has been reset (retire). Corporate data has been removed.',
    'Retire fehlgeschlagen: :msg'
        => 'Retire failed: :msg',
    'Gerät wird auf Werkseinstellungen zurückgesetzt (Wipe). Dieser Vorgang kann nicht rückgängig gemacht werden.'
        => 'Device is being reset to factory settings (wipe). This action cannot be undone.',
    'Wipe fehlgeschlagen: :msg'
        => 'Wipe failed: :msg',

    // ── Access Review: page title, titles & flash messages ──────────────────
    'Zugriffsprüfungen'             => 'Access reviews',
    'Gastbenutzer-Review :date'     => 'Guest user review :date',
    'Prüfung ":title" wurde erstellt.'
        => 'Review ":title" was created.',
    'Fehler beim Erstellen der Prüfung: :msg'
        => 'Error creating the review: :msg',
    '404 — Prüfung nicht gefunden'  => '404 — Review not found',
    'Ungültige Entscheidung.'       => 'Invalid decision.',
    'genehmigt'                     => 'approved',
    'widerrufen'                    => 'revoked',
    'Alle ausstehenden Einträge wurden :label.'
        => 'All pending entries were :label.',
    'Prüfung abgeschlossen.'        => 'Review completed.',
    ':count Konto(s) deaktiviert.'  => ':count account(s) disabled.',
    ':count Fehler aufgetreten.'    => ':count error(s) occurred.',
    'Fehler beim Anwenden: :msg'    => 'Error applying changes: :msg',

    // ── Risky Sign-ins: risk-event-type labels ──────────────────────────────
    'Anonymisierte IP-Adresse'      => 'Anonymized IP address',
    'Ungewöhnliche Reiseaktivität'  => 'Atypical travel activity',
    'Unmögliche Reise'              => 'Impossible travel',
    'Unbekannte Anmeldemerkmale'    => 'Unfamiliar sign-in properties',
    'Schädliche IP-Adresse'         => 'Malicious IP address',
    'Verdächtige IP-Adresse'        => 'Suspicious IP address',
    'Kompromittierte Anmeldedaten'  => 'Leaked credentials',
    'Threat Intelligence'           => 'Threat intelligence',
    'Passwort-Spray-Angriff'        => 'Password spray attack',
    'Anmeldung aus neuem Land'      => 'Sign-in from a new country',
    'Admin bestätigt Kompromittierung'
        => 'Admin-confirmed compromise',
    'Verdächtige Postfachregeln'    => 'Suspicious inbox rules',
    'Verdächtige Weiterleitung'     => 'Suspicious forwarding',

    // ── Risky Sign-ins: diagnostics & flash messages ────────────────────────
    'Microsoft Graph hat die Anfrage abgelehnt (403). Bitte sicherstellen, dass die App-Registrierung die Berechtigung IdentityRiskyUser.Read.All und IdentityRiskEvent.Read.All hat. Details: :details'
        => 'Microsoft Graph rejected the request (403). Make sure the app registration has the IdentityRiskyUser.Read.All and IdentityRiskEvent.Read.All permissions. Details: :details',
    'Es wurden keine Risiko-Daten gefunden. Hinweis: Microsoft Entra ID Protection erfordert eine Azure AD Premium P2-Lizenz — ohne P2 sind die Endpunkte zwar erreichbar, liefern aber leere Listen.'
        => 'No risk data was found. Note: Microsoft Entra ID Protection requires an Azure AD Premium P2 license — without P2 the endpoints are reachable but return empty lists.',
    'Benutzer als kompromittiert bestätigt.'
        => 'User confirmed as compromised.',
    'Risiko für Benutzer zurückgesetzt.'
        => 'Risk for the user has been dismissed.',

    // ── Insider Threat: risk signal labels ──────────────────────────────────
    ':pct % Anmeldungen außerhalb Bürozeiten'
        => ':pct % of sign-ins outside business hours',
    'Erhöhter Off-Hours-Anteil (:pct %)'
        => 'Elevated off-hours share (:pct %)',
    'Anmeldungen aus :count verschiedenen Ländern'
        => 'Sign-ins from :count different countries',
    ':count x Massen-Download (≥ 50 Files in 1h)'
        => ':count x mass download (≥ 50 files in 1h)',
    ':count x Mass-Mail-Send (≥ 100 Mails in 1h)'
        => ':count x mass mail send (≥ 100 mails in 1h)',
    ':count Lösch-Events'           => ':count delete events',
    ':count Sharing-Events'         => ':count sharing events',

    // ── Sign-in Anomaly: notes ──────────────────────────────────────────────
    'Sign-in-Log nicht abrufbar: :msg'
        => 'Sign-in log not retrievable: :msg',
    'Keine Sign-ins im Zeitraum.'   => 'No sign-ins in the period.',
    'Aggregierte Zähler, keine Benutzer-, IP- oder Länder-Details.'
        => 'Aggregated counters, no user, IP or country details.',

    // ── Defender Alerts: page title & flash messages ────────────────────────
    'Defender Sicherheitswarnungen' => 'Defender security alerts',
    'Warnung wurde als gelöst markiert.'
        => 'Alert was marked as resolved.',
    'Fehler beim Aktualisieren der Warnung: :msg'
        => 'Error updating the alert: :msg',

    // ── Audit Report: page title & article mapping labels ───────────────────
    'DSGVO / NIS-2 Audit-Report'    => 'GDPR / NIS-2 audit report',
    'Vertraulichkeit, Integrität, Verfügbarkeit'
        => 'Confidentiality, integrity, availability',
    'Technische Maßnahmen gegen unautorisierten Zugriff auf personenbezogene Daten.'
        => 'Technical measures against unauthorized access to personal data.',
    'Regelmäßige Überprüfung'       => 'Regular review',
    'Verfahren zur regelmäßigen Bewertung der Wirksamkeit der Sicherheitsmaßnahmen.'
        => 'Procedures for regularly assessing the effectiveness of security measures.',
    'Datenschutz durch Technikgestaltung'
        => 'Data protection by design',
    'Privacy by Design — Standards, die Daten von Anfang an schützen.'
        => 'Privacy by design — standards that protect data from the outset.',
    'Zugriffskontrolle & MFA'       => 'Access control & MFA',
    'Pflicht zur Multifaktor-Authentifizierung und sicheren Anmeldeverfahren.'
        => 'Requirement for multi-factor authentication and secure sign-in procedures.',
    'Lieferkettensicherheit'        => 'Supply chain security',
    'OAuth-Apps und Gast-Berechtigungen müssen kontrolliert werden.'
        => 'OAuth apps and guest permissions must be controlled.',
    'Krypto & Mail-Sicherheit'      => 'Cryptography & mail security',
    'Sichere Übertragung und Phishing-Abwehr per Defender for Office 365.'
        => 'Secure transmission and phishing defense via Defender for Office 365.',
    'Regelung des Passwortgebrauchs'
        => 'Password usage policy',
    'Sichere Authentifizierungsmethoden, keine Legacy-Protokolle.'
        => 'Secure authentication methods, no legacy protocols.',

    // ── Sign-in Log: page title & CSV export ────────────────────────────────
    'Anmeldeprotokoll'              => 'Sign-in log',
    'Datum/Uhrzeit'                 => 'Date/time',
    'App'                           => 'App',
    'IP-Adresse'                    => 'IP address',
    'Land'                          => 'Country',
    'Ergebnis'                      => 'Result',
    'Fehlercode'                    => 'Error code',
    'Risiko'                        => 'Risk',
    'CA-Status'                     => 'CA status',
    'Erfolgreich'                   => 'Successful',
    'Fehlgeschlagen'                => 'Failed',

    // ── Security: flash messages ────────────────────────────────────────────
    'Ungültiger Status.'            => 'Invalid status.',
    'Richtlinie aktualisiert.'      => 'Policy updated.',

    // ── Shared backend messages ─────────────────────────────────────────────
    'Fehler: :msg'                  => 'Error: :msg',
];
