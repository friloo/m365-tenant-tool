<?php

/**
 * English translations for the core backend (PHP) layer — strings produced in
 * services/controllers and surfaced to the user via flash messages, cron job
 * names/status texts, Graph API error diagnostics, update progress/status
 * messages and framework-level error pages.
 *
 * Covered classes:
 *   - App\Modules\Cron\CronRunner / CronController
 *   - App\Graph\GraphErrorTranslator
 *   - App\Update\UpdateManager
 *   - App\Modules\Update\UpdateController
 *   - App\Core\Router
 *   - App\Auth\LocalAuth
 *
 * Keys are the exact German source strings; dynamic values use :param markers.
 *
 * @return array<string,string>
 */
return [

    // ── Cron: job names (labels) ───────────────────────────────────────────
    'Job-Queue verarbeiten'                      => 'Process job queue',
    'Cache vorwärmen (alle Module)'              => 'Warm cache (all modules)',
    'Freigaben scannen'                          => 'Scan shares',
    'Review-E-Mails senden'                      => 'Send review emails',
    'Freigaben automatisch widerrufen'           => 'Auto-revoke shares',
    'Inaktive Konten bereinigen'                 => 'Clean up inactive accounts',
    'Queue aufräumen'                            => 'Prune queue',
    'Wöchentlicher E-Mail-Report'                => 'Weekly email report',
    'Monatlicher Executive-Report'               => 'Monthly executive report',
    'Alert: Neue Defender-Warnungen'             => 'Alert: New Defender alerts',
    'Alert: Dienststörungen'                     => 'Alert: Service incidents',
    'Alert: Neue Risiko-Benutzer'                => 'Alert: New risky users',
    'Audit-Diff-Snapshot'                        => 'Audit diff snapshot',
    'Benachrichtigungen aufräumen'               => 'Clean up notifications',
    'Workflow-Runner'                            => 'Workflow runner',

    // ── Cron: job descriptions ─────────────────────────────────────────────
    'Verarbeitet ausstehende Aufgaben aus der Warteschlange (Lizenzänderungen, Bulk-Aktionen). Läuft jede Minute.'
        => 'Processes pending tasks from the queue (license changes, bulk actions). Runs every minute.',
    'Ruft alle Graph-API-Endpunkte im Hintergrund ab und füllt den DB-Cache. Seiten laden danach sofort aus der DB ohne API-Wartezeit.'
        => 'Calls all Graph API endpoints in the background and populates the database cache. Pages then load instantly from the database with no API wait time.',
    'Synchronisiert externe SharePoint-Freigaben aus der Graph API und legt neue Einträge in der Datenbank an.'
        => 'Synchronizes external SharePoint shares from the Graph API and creates new entries in the database.',
    'Sendet Bestätigungs-E-Mails an Freigabe-Besitzer, deren Prüfintervall abgelaufen ist.'
        => 'Sends confirmation emails to share owners whose review interval has expired.',
    'Widerruft Freigaben, für die kein Besitzer innerhalb der Toleranzzeit reagiert hat.'
        => 'Revokes shares for which no owner responded within the grace period.',
    'Prüft inaktive Benutzer und entfernt Lizenzen bei konfigurierten Schwellwerten (nur wenn Auto-Release aktiviert).'
        => 'Checks inactive users and removes licenses at the configured thresholds (only when auto-release is enabled).',
    'Löscht abgeschlossene Jobs aus der Warteschlange, die älter als 24 Stunden sind.'
        => 'Deletes completed jobs from the queue that are older than 24 hours.',
    'Sendet einen wöchentlichen Zusammenfassungsbericht per E-Mail (konfigurierbar: Wochentag, Empfänger). Läuft täglich und prüft selbst, ob heute der richtige Tag ist.'
        => 'Sends a weekly summary report by email (configurable: weekday, recipients). Runs daily and checks for itself whether today is the right day.',
    'Versendet am ersten Tag jedes Monats den Executive-Report an die Geschäftsführung. Läuft täglich und prüft selbst, ob heute der 1. ist.'
        => 'Sends the executive report to management on the first day of each month. Runs daily and checks for itself whether today is the 1st.',
    'Sendet E-Mail wenn neue ungelöste Defender Alerts seit dem letzten Check aufgetreten sind.'
        => 'Sends an email when new unresolved Defender alerts have occurred since the last check.',
    'Sendet E-Mail wenn Microsoft-Dienste mit Störungen gemeldet werden.'
        => 'Sends an email when Microsoft services are reported with incidents.',
    'Sendet E-Mail wenn neue Benutzer einen aktiven Risikostatus erhalten haben.'
        => 'Sends an email when new users have been assigned an active risk status.',
    'Tenant-Snapshot für Audit-Diff erstellen'   => 'Create tenant snapshot for audit diff',
    'Alte In-App-Benachrichtigungen aufräumen'   => 'Clean up old in-app notifications',
    'Geplante Workflow-Automatisierungen ausführen' => 'Run scheduled workflow automations',

    // ── Cron: cache-warm sub-job labels ────────────────────────────────────
    'Dashboard — Metriken'         => 'Dashboard — Metrics',
    'Dashboard — Lizenzübersicht'  => 'Dashboard — License overview',
    'Dashboard — Sicherheit'       => 'Dashboard — Security',
    'Dashboard — Erweitert'        => 'Dashboard — Extended',
    'Benutzer — Gesamtliste'       => 'Users — Full list',
    'Benutzer — MFA-Status'        => 'Users — MFA status',
    'MFA-Methoden'                 => 'MFA methods',
    'Geräte'                       => 'Devices',
    'Gruppen'                      => 'Groups',
    'Lizenzen'                     => 'Licenses',
    'Dienststatus'                 => 'Service health',

    // ── Cron: job status / result texts ────────────────────────────────────
    ':n Job(s) verarbeitet'                      => ':n job(s) processed',
    'Keine ausstehenden Jobs'                    => 'No pending jobs',
    'Gefunden: :found, Neu: :new'                => 'Found: :found, New: :new',
    ':n E-Mail(s) gesendet'                      => ':n email(s) sent',
    ':n Freigabe(n) widerrufen'                  => ':n share(s) revoked',
    'Auto-Release deaktiviert — übersprungen'    => 'Auto-release disabled — skipped',
    'Lizenzen entzogen: :released, Warnungen: :warned'
        => 'Licenses removed: :released, Warnings: :warned',
    ':n abgeschlossene Job(s) gelöscht'          => ':n completed job(s) deleted',
    'Wöchentlicher Report deaktiviert — übersprungen' => 'Weekly report disabled — skipped',
    'Heute kein Report-Tag — übersprungen'       => 'Not a report day today — skipped',
    'Executive-Report deaktiviert — übersprungen' => 'Executive report disabled — skipped',
    'Heute nicht der 1. des Monats — übersprungen' => 'Not the 1st of the month today — skipped',
    'Kein Empfänger konfiguriert'                => 'No recipient configured',
    'Defender-API nicht verfügbar (403 — fehlende Lizenz oder Berechtigung)'
        => 'Defender API unavailable (403 — missing license or permission)',
    'Keine neuen Alerts'                         => 'No new alerts',
    ':count neue Alerts — E-Mail gesendet'       => ':count new alerts — email sent',
    'Keine neuen Dienststörungen'                => 'No new service incidents',
    ':count neue Incidents — E-Mail gesendet'    => ':count new incidents — email sent',
    'Keine neuen Risiko-Benutzer'                => 'No new risky users',
    ':count neue Risiko-Benutzer — E-Mail gesendet' => ':count new risky users — email sent',
    'Snapshot #:id erstellt'                     => 'Snapshot #:id created',
    ':deleted alte Benachrichtigungen entfernt'  => ':deleted old notifications removed',
    'Ausgeführt: :ran Workflows · :actions Aktionen' => 'Executed: :ran workflows · :actions actions',
    'Unbekannter Job: :jobKey'                   => 'Unknown job: :jobKey',

    // ── Cron: email subjects / bodies & table headers ──────────────────────
    'Lizenz automatisch entzogen: :name'         => 'License automatically removed: :name',
    'Automatische Lizenzfreigabe'                => 'Automatic license release',
    '<p>Dem Benutzer <strong>:name</strong> (<code>:upn</code>) wurden automatisch alle Lizenzen entzogen (inaktiv seit <strong>:days Tagen</strong>).</p><p><a href=":url">→ Inaktive Konten verwalten</a></p>'
        => '<p>All licenses were automatically removed from user <strong>:name</strong> (<code>:upn</code>) (inactive for <strong>:days days</strong>).</p><p><a href=":url">→ Manage inactive accounts</a></p>',
    'Vorwarnung: Lizenzfreigabe in :remaining Tagen — :name'
        => 'Advance warning: license release in :remaining days — :name',
    'Bevorstehende Lizenzfreigabe'               => 'Upcoming license release',
    '<p>Dem Benutzer <strong>:name</strong> werden in <strong>:remaining Tagen</strong> automatisch alle Lizenzen entzogen (inaktiv seit :days Tagen).</p><p><a href=":url">→ Inaktive Konten verwalten</a></p>'
        => '<p>All licenses will be automatically removed from user <strong>:name</strong> in <strong>:remaining days</strong> (inactive for :days days).</p><p><a href=":url">→ Manage inactive accounts</a></p>',
    'Link'                                        => 'Link',
    'Titel'                                       => 'Title',
    'Schweregrad'                                 => 'Severity',
    'Erstellt'                                    => 'Created',
    'Details'                                     => 'Details',
    'Defender Alert: :count neue Warnung(en)'     => 'Defender alert: :count new alert(s)',
    'Neue Defender-Warnungen (:count)'            => 'New Defender alerts (:count)',
    '<p>Es wurden <strong>:count</strong> neue Defender-Alert(s) gefunden:</p>:html'
        => '<p><strong>:count</strong> new Defender alert(s) were found:</p>:html',
    ':count neue Defender-Warnungen'              => ':count new Defender alerts',
    'Bitte unter /defenderalerts prüfen und bewerten.'
        => 'Please review and assess under /defenderalerts.',
    'Dienst'                                      => 'Service',
    'Status'                                      => 'Status',
    'Beginn'                                      => 'Start',
    'Dienststörung: :count neue Incident(s)'      => 'Service incident: :count new incident(s)',
    'Neue Dienststörungen (:count)'               => 'New service incidents (:count)',
    '<p>Es wurden <strong>:count</strong> neue Dienststörung(en) erkannt:</p>:html'
        => '<p><strong>:count</strong> new service incident(s) were detected:</p>:html',
    'Risikostufe'                                 => 'Risk level',
    'Aktualisiert'                                => 'Updated',
    'Risiko-Benutzer: :count neue(r) Benutzer'    => 'Risky users: :count new user(s)',
    'Neue Risiko-Benutzer (:count)'               => 'New risky users (:count)',
    '<p>Es wurden <strong>:count</strong> neue Risiko-Benutzer erkannt:</p>:html'
        => '<p><strong>:count</strong> new risky users were detected:</p>:html',

    // ── CronController: page title & flash messages ────────────────────────
    'Cron & Automatisierung'                     => 'Cron & Automation',
    'Job ":jobKey" aktualisiert.'                => 'Job ":jobKey" updated.',
    'Job ausgeführt (:secondss): :log'           => 'Job executed (:secondss): :log',
    'Job fehlgeschlagen: :log'                   => 'Job failed: :log',
    'Fehlgeschlagene Jobs zurückgesetzt.'        => 'Failed jobs reset.',
    'Abgeschlossene Jobs aus der Warteschlange entfernt.'
        => 'Completed jobs removed from the queue.',

    // ── GraphErrorTranslator: error diagnostics ────────────────────────────
    'SharePoint nicht lizenziert'                => 'SharePoint not licensed',
    'Der Tenant hat keine SharePoint-Online-Lizenz. Diese Funktion ist daher nicht zutreffend.'
        => 'The tenant has no SharePoint Online license. This feature therefore does not apply.',
    'Im Tenant nicht aktiviert'                  => 'Not enabled in the tenant',
    'Microsoft Graph meldet "Request not applicable to target tenant". Häufige Gründe: passende Lizenz fehlt (z.B. Intune/EM&S für /deviceManagement, Office-365-Subscription für Reports), Dienst ist im Tenant nicht aktiviert, oder der Tenant-Typ (B2C, Government, Sovereign Cloud) unterstützt diesen Endpunkt nicht.'
        => 'Microsoft Graph reports "Request not applicable to target tenant". Common reasons: a matching license is missing (e.g. Intune/EM&S for /deviceManagement, an Office 365 subscription for reports), the service is not enabled in the tenant, or the tenant type (B2C, Government, Sovereign Cloud) does not support this endpoint.',
    'Lizenz fehlt im Tenant'                     => 'License missing in the tenant',
    'Microsoft Graph meldet: :msg'               => 'Microsoft Graph reports: :msg',
    'Datenschutzmodus für Berichte aktiv'        => 'Privacy mode for reports active',
    'Im Microsoft-365-Admin-Center ist der Datenschutz für Berichtsdaten aktiviert — die Endpunkte liefern dann anonymisierte oder leere Daten. Lösungsweg: Admin-Center → Einstellungen → Org-Einstellungen → Berichte → "Verborgene Benutzer-/Gruppen-/Site-Namen anzeigen" einschalten.'
        => 'Privacy for report data is enabled in the Microsoft 365 admin center — the endpoints then return anonymized or empty data. Resolution: Admin center → Settings → Org settings → Reports → enable "Display concealed user, group, and site names".',
    'Token ungültig oder abgelaufen'             => 'Token invalid or expired',
    'Microsoft Graph hat den Zugriff abgelehnt (401). Bitte Token-Aktualisierung versuchen.'
        => 'Microsoft Graph denied access (401). Please try refreshing the token.',
    ' Konkret benötigt: <code>:perm</code>.'     => ' Specifically required: <code>:perm</code>.',
    'Berechtigung fehlt oder kein Admin Consent' => 'Permission missing or no admin consent',
    'Microsoft Graph hat die Anfrage abgelehnt (403).'
        => 'Microsoft Graph denied the request (403).',
    ' Mögliche Ursachen: Die App-Berechtigung ist in Azure noch nicht eingetragen, oder ein Global Admin hat noch keinen Admin Consent erteilt. Unter Einstellungen → Berechtigungen prüfen welche fehlt.'
        => ' Possible causes: the app permission is not yet registered in Azure, or a Global Admin has not yet granted admin consent. Check under Settings → Permissions to see which one is missing.',
    'Reports-API liefert keine Daten (404)'      => 'Reports API returns no data (404)',
    'Der Endpunkt :url antwortet mit 404. Microsoft gibt diesen Statuscode aus drei Gründen — bitte einen davon prüfen: (1) Tenant hat keinen Office-365-Plan, der Aktivitätsberichte unterstützt (E1/E3/E5 oder Business). (2) Datenschutz für Berichte ist aktiviert — Admin Center → Einstellungen → Org-Einstellungen → Berichte → "Verborgene Namen anzeigen". (3) Reports.Read.All-Permission fehlt im App-Token (zwar oft 403, aber bei manchen Lizenz-Kombinationen wird 404 gemeldet). Original-Antwort: :msg'
        => 'The endpoint :url responds with 404. Microsoft returns this status code for three reasons — please check one of them: (1) The tenant has no Office 365 plan that supports activity reports (E1/E3/E5 or Business). (2) Privacy for reports is enabled — Admin center → Settings → Org settings → Reports → "Display concealed names". (3) The Reports.Read.All permission is missing from the app token (often 403, but with some license combinations a 404 is reported). Original response: :msg',
    'Endpunkt oder Ressource nicht gefunden (404)' => 'Endpoint or resource not found (404)',
    'Microsoft Graph antwortet mit 404 für :url. Mögliche Gründe: API-Pfad in neuerer Graph-Version umbenannt, Ressource im Tenant nicht angelegt, oder Tenant-Typ unterstützt diesen Endpunkt nicht. Original-Antwort: :msg'
        => 'Microsoft Graph responds with 404 for :url. Possible reasons: the API path was renamed in a newer Graph version, the resource has not been created in the tenant, or the tenant type does not support this endpoint. Original response: :msg',
    'Rate-Limit erreicht'                        => 'Rate limit reached',
    'Microsoft Graph drosselt die Anfragen (429). Bitte später erneut versuchen.'
        => 'Microsoft Graph is throttling the requests (429). Please try again later.',
    'Microsoft Graph antwortet mit Server-Fehler' => 'Microsoft Graph responds with a server error',
    'Microsoft Graph hat den Status :status geliefert. Das ist meist temporär — bitte später erneut versuchen.'
        => 'Microsoft Graph returned status :status. This is usually temporary — please try again later.',
    'Microsoft Graph: Fehler'                    => 'Microsoft Graph: Error',
    'unbekannt'                                  => 'unknown',
    'Fehler'                                     => 'Error',

    // ── UpdateManager: progress / status / error messages ──────────────────
    'Update wird gestartet…'                     => 'Starting update…',
    'Aktuelle Version wird abgerufen…'           => 'Retrieving current version…',
    'Keine SHA vom Proxy erhalten'               => 'No SHA received from the proxy',
    'Update-Paket wird heruntergeladen…'         => 'Downloading update package…',
    'Paket wird entpackt…'                       => 'Extracting package…',
    'Dateien werden übernommen…'                 => 'Applying files…',
    'Datenbank-Migrationen werden ausgeführt…'   => 'Running database migrations…',
    'Cache wird geleert…'                        => 'Clearing cache…',
    'Update auf :sha installiert (:count Migrationen)'
        => 'Update to :sha installed (:count migrations)',
    'Update erfolgreich abgeschlossen.'          => 'Update completed successfully.',
    'Update auf :sha installiert. :count Migration(en) ausgeführt.'
        => 'Update to :sha installed. :count migration(s) executed.',
    'Fehler: :msg'                               => 'Error: :msg',
    'Update fehlgeschlagen: :msg'                => 'Update failed: :msg',
    'cURL-Fehler: :err'                          => 'cURL error: :err',
    'Proxy lieferte HTTP :code für :path'        => 'Proxy returned HTTP :code for :path',
    'Ungültige JSON-Antwort vom Proxy'           => 'Invalid JSON response from the proxy',
    'Download-Fehler: :err'                      => 'Download error: :err',
    'Proxy lieferte HTTP :code für ZIP-Download' => 'Proxy returned HTTP :code for ZIP download',
    'Leere ZIP-Antwort vom Proxy erhalten'       => 'Received empty ZIP response from the proxy',
    'Heruntergeladene Datei ist kein gültiges ZIP-Archiv'
        => 'Downloaded file is not a valid ZIP archive',
    'ZipArchive PHP-Extension fehlt'             => 'ZipArchive PHP extension is missing',
    'ZIP konnte nicht geöffnet werden (Code: :code)' => 'Could not open ZIP (code: :code)',
    'Migration :filename fehlgeschlagen: :msg'   => 'Migration :filename failed: :msg',

    // ── UpdateController: page title & flash / status messages ─────────────
    'Updates'                                    => 'Updates',
    'Bereit'                                     => 'Ready',
    'Ungültiger Channel: :channel'               => 'Invalid channel: :channel',
    'Channel geändert zu :channel'               => 'Channel changed to :channel',
    ':count Migration(en) ausgeführt.'           => ':count migration(s) executed.',
    'Fehler beim Ausführen der Migrationen: :msg' => 'Error running the migrations: :msg',

    // ── Router: framework-level error pages ────────────────────────────────
    'Sicherheitsfehler'                          => 'Security error',
    'CSRF-Schutz'                                => 'CSRF protection',
    'Ungültiges oder abgelaufenes Sicherheits-Token.'
        => 'Invalid or expired security token.',
    'Bitte :link und versuche es erneut.'        => 'Please :link and try again.',
    'gehe zurück'                                => 'go back',
    '404 — Nicht gefunden'                       => '404 — Not Found',

    // ── LocalAuth: auth error messages ─────────────────────────────────────
    '403 — Nur für Administratoren'              => '403 — Administrators only',
];
