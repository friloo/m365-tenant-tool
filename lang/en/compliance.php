<?php

/**
 * English translations for the Compliance & Audit / Hardening view modules.
 *
 * Keys are the exact German source strings used in the views. The central
 * map (/lang/en.php) wins on collisions, so shared glossary terms repeated
 * here are harmless.
 */

return [
    // ── Hardening (views/hardening/index.php) ─────────────────────────────
    'Härtungs-Score' => 'Hardening score',
    'Security Center — Status &amp; Einstellungen an einem Ort' => 'Security Center — status &amp; settings in one place',
    'zu härten' => 'to harden',
    'prüfen' => 'to check',
    'manuell' => 'manual',
    'unbekannt' => 'unknown',
    'Vollständige Posture' => 'Full posture',
    'DSGVO-Status' => 'GDPR status',
    'Compliance-Profile' => 'Compliance profiles',
    'Grundlegende Sicherheits-Einstellungen für alle Module.' => 'Core security settings for all modules.',
    'Hier siehst du den aktuellen Zustand jeder zentralen Einstellung und kannst sie mit einem Klick
        aktivieren oder per Deep-Link ins passende Admin-Center springen. Jede Aktion schreibt das
        Audit-Log mit, damit nachvollziehbar bleibt, was, wann, von wem geändert wurde.'
        => 'Here you can see the current state of every central setting and either enable it with a single
        click or jump to the relevant admin center via deep link. Every action is written to the
        audit log so it stays traceable what was changed, when, and by whom.',
    'Härten' => 'Harden',
    'Prüfen' => 'Check',
    'Manuell' => 'Manual',
    'Unbekannt' => 'Unknown',
    'Item(s)' => 'item(s)',
    'Aktueller Status:' => 'Current status:',
    'Im Admin-Center öffnen' => 'Open in admin center',
    'Diese Aktion wirkt sofort tenant-weit. Fortfahren?' => 'This action takes effect tenant-wide immediately. Continue?',
    'Wichtig:' => 'Important:',
    'Tenant-weite Änderungen wirken sofort. Insbesondere die Conditional-Access-
        Policy „Block Legacy Auth" wird im <em>Report-Only</em>-Modus angelegt — bitte einige Tage Reports
        prüfen, bevor du sie auf <em>Enabled</em> stellst, um keine produktiven Services zu blockieren.
        Microsoft Graph schreibt jede Änderung in das Tenant-Audit-Log; das Tool zusätzlich in <em>App
        Audit-Log</em>.'
        => 'Tenant-wide changes take effect immediately. In particular, the Conditional Access
        policy "Block Legacy Auth" is created in <em>Report-Only</em> mode — please review the reports
        for a few days before setting it to <em>Enabled</em>, to avoid blocking productive services.
        Microsoft Graph writes every change to the tenant audit log; the tool additionally to the <em>App
        audit log</em>.',

    // ── Security posture (views/securityposture/index.php) ────────────────
    'Gut' => 'Good',
    'Verbesserungsbedarf' => 'Needs improvement',
    'Kritisch' => 'Critical',
    'Hoch' => 'High',
    'Mittel' => 'Medium',
    'Niedrig' => 'Low',
    'Security Posture Assessment' => 'Security posture assessment',
    'Bestanden' => 'Passed',
    'Warnung' => 'Warning',
    'Fehlgeschlagen' => 'Failed',
    'Prüfungen bewertet · gewichteter Score (Kritisch = 3×, Mittel = 2×, Niedrig = 1×) ·'
        => 'checks evaluated · weighted score (Critical = 3×, Medium = 2×, Low = 1×) ·',
    'Neu laden' => 'Reload',
    'Empfehlungen' => 'Recommendations',
    'Maßnahme(n) · sortiert nach Priorität' => 'action(s) · sorted by priority',
    'Prüfungen' => 'checks',
    'fehlgeschlagen' => 'failed',
    'Hinweis' => 'Note',
    'Hinweis:' => 'Note:',
    'Prüfungen basieren auf Microsoft Graph API-Daten und Best Practices (CIS M365, Microsoft Security Baseline).
    Fehlende Berechtigungen werden als <strong>Unbekannt</strong> angezeigt.
    Einige Prüfungen nutzen gecachte Daten (5–30 Min). Für aktuelle Ergebnisse:'
        => 'Checks are based on Microsoft Graph API data and best practices (CIS M365, Microsoft Security Baseline).
    Missing permissions are shown as <strong>Unknown</strong>.
    Some checks use cached data (5–30 min). For current results:',
    'Aktualisieren' => 'Refresh',
    'Risikobasierte CA-Richtlinien (Anmelderisiko, Benutzerrisiko) erfordern <strong>Entra ID P2</strong>.'
        => 'Risk-based CA policies (sign-in risk, user risk) require <strong>Entra ID P2</strong>.',

    // ── Best practice guide (views/bestpractice/index.php) ────────────────
    'Tenant-Härtungs-Leitfaden' => 'Tenant hardening guide',
    'Best-Practice-Schritt-für-Schritt für einen sicheren Microsoft-365-Tenant. Du kannst Schritte abhaken, überspringen, jederzeit zurückkehren. Der Fortschritt wird im Tool gespeichert.'
        => 'A best-practice step-by-step path to a secure Microsoft 365 tenant. You can check off steps, skip them, and return at any time. Your progress is saved in the tool.',
    'Drucken / PDF' => 'Print / PDF',
    'Allen Fortschritt zurücksetzen?' => 'Reset all progress?',
    'Zurücksetzen' => 'Reset',
    'Gesamtfortschritt' => 'Overall progress',
    'erledigt' => 'done',
    'übersprungen' => 'skipped',
    'offen' => 'open',
    'insgesamt' => 'total',
    'Schritte automatisch als erledigt erkannt (z. B. Setup-Wizard, Compliance-Profil, Backup-Konfiguration).'
        => 'steps automatically detected as done (e.g. setup wizard, compliance profile, backup configuration).',
    'auto-erkannt' => 'auto-detected',
    'Vom Tool automatisch erkannt' => 'Automatically detected by the tool',
    'Geschätzter Zeitaufwand' => 'Estimated time required',
    'Warum:' => 'Why:',
    'So gehst du vor:' => 'How to proceed:',
    'Erledigt' => 'Done',
    'Überspringen' => 'Skip',
    'wieder öffnen' => 'reopen',
    'Kurzfristige Varianten' => 'Quick variants',
    'Einrichtungs-Assistent' => 'Setup wizard',
    'Compliance-Profil anwenden' => 'Apply compliance profile',
    'Break-Glass-Account' => 'Break-glass account',
    'Phase 1 + 2 + 3 — deckt bereits ~80 % des realistischen Angriffsvektors (Identität) ab.'
        => 'Phase 1 + 2 + 3 — already covers ~80% of the realistic attack vector (identity).',
    'Bei Phase 1 starten' => 'Start with phase 1',

    // ── Compliance profile (views/complianceprofile/index.php) ────────────
    'Compliance-Profile' => 'Compliance profiles',
    'Wähle ein Branchen-Profil und wende mit einem Klick die dazu passenden Hardening-Defaults an. Aktionen laufen einzeln im Browser mit Fortschritts-Anzeige; alle Schritte sind im Audit-Log nachvollziehbar und können im <a href="/hardening">Tenant-Härtungs-Modul</a> einzeln umgekehrt werden.'
        => 'Choose an industry profile and apply the matching hardening defaults with one click. Actions run individually in the browser with a progress indicator; every step is traceable in the audit log and can be reversed individually in the <a href="/hardening">tenant hardening module</a>.',
    'Aktuell aktives Profil:' => 'Currently active profile:',
    'Du kannst es jederzeit überschreiben oder einzelne Items in <a href="/hardening">/hardening</a> umkehren.'
        => 'You can overwrite it at any time or reverse individual items in <a href="/hardening">/hardening</a>.',
    'Aktionen anzeigen' => 'Show actions',
    'Profil anwenden' => 'Apply profile',
    'Profil anwenden:' => 'Apply profile:',
    'Fortschritt' => 'Progress',
    'Fertig!' => 'Done!',
    'Seite neu laden' => 'Reload page',
    'Aktionen erfolgreich angewendet.' => 'actions applied successfully.',
    'fehlgeschlagen. Details siehe Protokoll oben.' => 'failed. See the log above for details.',
    'Aktionen fehlgeschlagen. Bitte Berechtigungen prüfen.' => 'actions failed. Please check permissions.',
    'Profil "' => 'Apply profile "',
    '" jetzt anwenden? Es werden ' => '" now? This runs ',
    ' Hardening-Aktionen ausgeführt — bestehende Werte werden überschrieben.' => ' hardening actions — existing values will be overwritten.',
    'Abgebrochen:' => 'Aborted:',

    // ── Customer Lockbox (views/customerlockbox/index.php) ─────────────────
    'ohne Customer Lockbox darf Microsoft Support im Notfall
        auf Ihre Daten zugreifen, ohne dass Sie es erfahren oder zustimmen können. Mit aktiviertem
        Lockbox muss ein Tenant-Admin jeden Microsoft-Support-Zugriff aktiv approven; ohne
        Approval gibt es <em>keinen</em> Zugriff.'
        => 'without Customer Lockbox, Microsoft Support may access your data in an emergency
        without you being notified or able to consent. With Lockbox enabled, a tenant admin must
        actively approve every Microsoft support access; without
        approval there is <em>no</em> access.',
    'Voraussetzung:' => 'Prerequisite:',
    'Microsoft 365 E5 oder als Add-on, plus Bedingung für viele
        DSGVO-Verträge mit Mandanten in regulierten Branchen.'
        => 'Microsoft 365 E5 or as an add-on, plus a condition for many
        GDPR contracts with customers in regulated industries.',
    'Status (manuell gepflegt)' => 'Status (manually maintained)',
    'Customer Lockbox ist im Tenant aktiviert' => 'Customer Lockbox is enabled in the tenant',
    'Approver-Liste' => 'Approver list',
    '(UPNs, kommagetrennt)' => '(UPNs, comma-separated)',
    'Reaktions-SLA (Stunden)' => 'Response SLA (hours)',
    'Letzte Review' => 'Last review',
    'Halbjährlich prüfen, ob noch alles aktuell ist.' => 'Review every six months to ensure everything is still up to date.',
    'Konfiguration im Admin-Center' => 'Configuration in the admin center',
    'Customer Lockbox wird im Microsoft 365 Admin Center konfiguriert. Microsoft Graph stellt
            für diese Einstellung keine Schreib-Endpunkt zur Verfügung — daher der manuelle Eintrag
            oben und die direkten Links unten.'
        => 'Customer Lockbox is configured in the Microsoft 365 Admin Center. Microsoft Graph does not
            provide a write endpoint for this setting — hence the manual entry
            above and the direct links below.',
    'Microsoft-Doku' => 'Microsoft docs',

    // ── Backup (views/backup/index.php) ───────────────────────────────────
    'Microsoft 365 sichert deine Daten NICHT.' => 'Microsoft 365 does NOT back up your data.',
    'Die Recycle-Bin-Frist von 30-93 Tagen ist kein Backup —
        nach Ransomware, versehentlichem Löschen, kompromittierten Admin-Konten oder Kündigungen
        sind die Daten weg. Für DSGVO Art. 32 (Verfügbarkeit), ISO 27001 A.12.3 und NIS-2 Art. 21(d)
        ist ein 3rd-Party-Backup Pflicht.'
        => 'The recycle bin retention of 30-93 days is not a backup —
        after ransomware, accidental deletion, compromised admin accounts, or terminations,
        the data is gone. For GDPR Art. 32 (availability), ISO 27001 A.12.3, and NIS-2 Art. 21(d),
        a third-party backup is mandatory.',
    'Backup-Health-Score' => 'Backup health score',
    'Backup-Setup ist gut konfiguriert.' => 'Backup setup is well configured.',
    'Backup-Setup hat Lücken.' => 'Backup setup has gaps.',
    'Backup-Setup hat kritische Lücken oder fehlt komplett.' => 'Backup setup has critical gaps or is missing entirely.',
    'Anbieter' => 'Provider',
    'Findings' => 'Findings',
    'Backup-Konfiguration eintragen' => 'Enter backup configuration',
    'Anbieter-URL' => 'Provider URL',
    '(optional)' => '(optional)',
    'Letzter Backup-Lauf' => 'Last backup run',
    '— wählen —' => '— select —',
    'Erfolgreich' => 'Successful',
    'Teilweise erfolgreich' => 'Partially successful',
    'Aufbewahrung (Tage)' => 'Retention (days)',
    'Geschützte Workloads' => 'Protected workloads',
    'Letzter Restore-Test' => 'Last restore test',
    'Mindestens einmal jährlich.' => 'At least once a year.',
    'Notizen' => 'Notes',

    // ── Devices (views/devices/index.php + detail.php) ────────────────────
    'Zur Lösung' => 'To the solution',
    'Geräte gesamt' => 'Devices total',
    'Konform' => 'Compliant',
    'Nicht konform' => 'Non-compliant',
    'Verschlüsselt' => 'Encrypted',
    'Betriebssysteme' => 'Operating systems',
    'Gerät suchen…' => 'Search device…',
    'Alle Status' => 'All statuses',
    'Gerätename' => 'Device name',
    'Version' => 'Version',
    'Letzter Sync' => 'Last sync',
    'Synchronisieren' => 'Synchronize',
    'Gerät löschen (Wipe)' => 'Delete device (wipe)',
    'ACHTUNG: Alle Daten auf dem Gerät werden unwiderruflich gelöscht. Wirklich fortfahren?'
        => 'WARNING: All data on the device will be permanently deleted. Really continue?',
    'Keine Geräte gefunden (Intune-Berechtigungen prüfen)' => 'No devices found (check Intune permissions)',
    'Zurück zu Geräte' => 'Back to devices',
    'Geräteinformationen' => 'Device information',
    'Hersteller' => 'Manufacturer',
    'Modell' => 'Model',
    'Seriennummer' => 'Serial number',
    'Registriert' => 'Enrolled',
    'Verwaltung' => 'Management',
    'Verschlüsselung' => 'Encryption',
    'Aktiv' => 'Active',
    'Inaktiv' => 'Inactive',
    'Freier Speicher' => 'Free storage',
    'von' => 'of',
    'Aktionen' => 'Actions',
    'Fordert das Gerät auf, sich sofort mit Intune zu synchronisieren.' => 'Asks the device to synchronize with Intune immediately.',
    'Sync anfordern' => 'Request sync',
    'Entfernt Unternehmensdaten, lässt persönliche Daten intakt. Geeignet für persönliche Geräte (BYOD).'
        => 'Removes company data, leaves personal data intact. Suitable for personal devices (BYOD).',
    'Gerät wirklich zurücksetzen (Retire)? Unternehmensdaten werden entfernt, persönliche Daten bleiben erhalten.'
        => 'Really retire the device? Company data will be removed, personal data will be retained.',
    'Retire ausführen' => 'Run retire',
    'Wipe (Werksreset)' => 'Wipe (factory reset)',
    'Löscht alle Daten auf dem Gerät. Nicht rückgängig machbar. Nur für Unternehmensgeräte.'
        => 'Deletes all data on the device. Cannot be undone. For company devices only.',
    'Wipe ausführen' => 'Run wipe',
    'BitLocker-Schlüssel' => 'BitLocker keys',
    'Keine BitLocker-Schlüssel gefunden. Entweder ist das Gerät nicht verschlüsselt, oder die Berechtigung <code>InformationProtection.Read.All</code> fehlt.'
        => 'No BitLocker keys found. Either the device is not encrypted, or the <code>InformationProtection.Read.All</code> permission is missing.',
    'Schlüssel werden nur protokolliert angezeigt. Stelle sicher, dass der Zugriff nur autorisierten Personen möglich ist.'
        => 'Keys are displayed with logging only. Make sure access is restricted to authorized people.',
    'Erstellt am' => 'Created on',
    'Schlüssel-ID' => 'Key ID',
    'Recovery-Schlüssel' => 'Recovery key',
    'Klicken zum Anzeigen' => 'Click to reveal',
    'Schlüssel kopieren' => 'Copy key',
    'Schlüsselwert nicht verfügbar' => 'Key value not available',
    'Gerade eben' => 'Just now',
    'Vor :n Minuten' => ':n minutes ago',
    'Vor :n Stunden' => ':n hours ago',
    'Vor 1 Tag' => '1 day ago',
    'Vor :n Tagen' => ':n days ago',

    // ── Access review (views/accessreview/index.php + show.php) ───────────
    'Offene Prüfungen' => 'Open reviews',
    'Prüfungen gesamt' => 'Reviews total',
    'Gäste (letzte Prüfung)' => 'Guests (last review)',
    'Zugriffsprüfungen' => 'Access reviews',
    'Neue Prüfung starten' => 'Start new review',
    'Titel' => 'Title',
    'Typ' => 'Type',
    'Erstellt von' => 'Created by',
    'Ausstehend' => 'Pending',
    'Offen' => 'Open',
    'Abgeschlossen' => 'Completed',
    'Öffnen' => 'Open',
    'Noch keine Prüfungen vorhanden' => 'No reviews yet',
    'Titel der Prüfung' => 'Review title',
    'Gastbenutzer-Review' => 'Guest user review',
    'Alle aktuellen Gastbenutzer werden als Prüfungseinträge geladen.' => 'All current guest users will be loaded as review entries.',
    'Dieser Vorgang fragt alle Gastbenutzer live aus Microsoft 365 ab und kann einige Sekunden dauern.'
        => 'This operation queries all guest users live from Microsoft 365 and may take a few seconds.',
    'Prüfung starten' => 'Start review',
    'Erstellt:' => 'Created:',
    'Abgeschlossen:' => 'Completed:',
    'Einträge gesamt' => 'Entries total',
    'Genehmigt' => 'Approved',
    'Widerrufen' => 'Revoked',
    'Widerrufen deaktiviert das Konto in Microsoft 365.
    Diese Aktion kann durch einen Administrator wieder rückgängig gemacht werden.'
        => 'Revoking disables the account in Microsoft 365.
    This action can be reverted again by an administrator.',
    'Diese Prüfung wurde abgeschlossen. Entscheidungen können nicht mehr geändert werden.'
        => 'This review has been completed. Decisions can no longer be changed.',
    'Alle ausstehenden genehmigen' => 'Approve all pending',
    'Alle ausstehenden Einträge widerrufen?' => 'Revoke all pending entries?',
    'Alle ausstehenden widerrufen' => 'Revoke all pending',
    'Entscheidungen anwenden und Prüfung abschließen?\n\nAlle als „Widerrufen" markierten Konten werden deaktiviert. Diese Aktion kann nicht rückgängig gemacht werden.'
        => 'Apply decisions and complete the review?\n\nAll accounts marked as "Revoked" will be disabled. This action cannot be undone.',
    'Entscheidungen anwenden &amp; abschließen' => 'Apply decisions &amp; complete',
    'Alle' => 'All',
    'Letzter Login' => 'Last login',
    'Entscheidung' => 'Decision',
    'Entschieden von' => 'Decided by',
    'Aktion' => 'Action',
    'Nie' => 'Never',
    'Genehmigen' => 'Approve',
    'Zurücksetzen' => 'Reset',
    'Keine Einträge vorhanden' => 'No entries available',

    // ── Audit log (views/auditlog/index.php) ──────────────────────────────
    'Von' => 'From',
    'Bis' => 'To',
    'Laden' => 'Load',
    'Verzeichnis-Audit' => 'Directory audit',
    'Anmeldungen' => 'Sign-ins',
    'Suchen…' => 'Search…',
    'Einträge' => 'entries',
    'Zeitpunkt' => 'Time',
    'Kategorie' => 'Category',
    'Ergebnis' => 'Result',
    'Initiiert von' => 'Initiated by',
    'Ziel' => 'Target',
    'Fehler' => 'Error',
    'Keine Einträge im gewählten Zeitraum' => 'No entries in the selected period',
    'Risiko' => 'Risk',

    // ── Audit diff (views/auditdiff/index.php) ────────────────────────────
    'Vergleiche zwei Snapshots der Tenant-Einstellungen. Snapshots werden täglich automatisch erstellt (Cron-Job: <code>audit_diff_snapshot</code>) und können hier manuell ergänzt werden.'
        => 'Compare two snapshots of the tenant settings. Snapshots are created automatically every day (cron job: <code>audit_diff_snapshot</code>) and can be added manually here.',
    'Snapshot A (älter / „vorher")' => 'Snapshot A (older / "before")',
    'Snapshot B (neuer / „nachher")' => 'Snapshot B (newer / "after")',
    'Vergleichen' => 'Compare',
    'Jetzt manuellen Snapshot erstellen' => 'Create manual snapshot now',
    'Aktuell' => 'Currently',
    'Snapshots gespeichert (Aufbewahrung 365 Tage).' => 'snapshots stored (retention 365 days).',
    'Noch keine Snapshots vorhanden. Klick oben auf' => 'No snapshots yet. Click',
    'oder warte auf den nächsten Cron-Lauf.' => 'above, or wait for the next cron run.',
    'Bitte zwei Snapshots auswählen und auf "Vergleichen" klicken.' => 'Please select two snapshots and click "Compare".',
    'Vergleich' => 'Comparison',
    'Keine Änderungen zwischen den beiden Snapshots.' => 'No changes between the two snapshots.',
    'geändert' => 'changed',
    'neu' => 'new',
    'entfernt' => 'removed',
    'Geänderte Werte' => 'Changed values',
    'Hinzugefügte Werte' => 'Added values',
    'Entfernte Werte' => 'Removed values',

    // ── Audit report (views/auditreport/index.php) ────────────────────────
    'erfüllt' => 'met',
    'Achtung' => 'Attention',
    'nicht erfüllt' => 'not met',
    'DSGVO / NIS-2 Audit-Report' => 'GDPR / NIS-2 audit report',
    'Auditfähige Übersicht der Tenant-Konfiguration entlang der wichtigsten Compliance-Anforderungen. Drucken oder als PDF speichern über den Drucker-Knopf oben rechts.'
        => 'Audit-ready overview of the tenant configuration along the most important compliance requirements. Print or save as PDF using the printer button in the top right.',
    'Als PDF speichern' => 'Save as PDF',
    'Tenant-Sicherheitsbericht' => 'Tenant security report',
    'Generiert am' => 'Generated on',
    'Tenant-Name' => 'Tenant name',
    'Land' => 'Country',
    'Verifizierte Domains' => 'Verified domains',
    'Compliance-Profil' => 'Compliance profile',
    'nicht gesetzt' => 'not set',
    'Graph-API-Berechtigungen' => 'Graph API permissions',
    'Gesamt' => 'Total',
    'Erteilt' => 'Granted',
    'Fehlend' => 'Missing',
    'Schreib-Permissions)' => 'write permissions)',
    'Eingeschränkte Funktionen:' => 'Restricted features:',
    'Tenant-Härtung (alle 21 Items)' => 'Tenant hardening (all 21 items)',
    'Begründung (BSI / NIS-2 / DSGVO)' => 'Rationale (BSI / NIS-2 / GDPR)',
    'Zuordnung zu Compliance-Artikeln' => 'Mapping to compliance articles',
    'Wie die oben dokumentierten Hardening-Items konkret die rechtlichen Anforderungen abdecken.'
        => 'How the hardening items documented above concretely cover the legal requirements.',
    'Keine konkreten Hardening-Items zugeordnet.' => 'No specific hardening items assigned.',
    'Ende des Berichts' => 'End of report',
    'Dieser Bericht wurde am' => 'This report was generated on',
    'automatisiert aus der Microsoft Graph API erzeugt und ersetzt keine externe Auditierung.'
        => 'automatically from the Microsoft Graph API and does not replace an external audit.',
];
