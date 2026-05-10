<?php use App\Core\View; $e = fn($v) => View::escape($v); ?>
<style>
.manual-wrap      { display:flex; gap:32px; align-items:flex-start; }
.manual-toc       { flex-shrink:0; width:220px; position:sticky; top:20px; }
.manual-toc-inner { background:#f9fafb; border:1px solid #e5e7eb; border-radius:10px; padding:16px; font-size:13px; max-height:calc(100vh - 80px); overflow-y:auto; }
.manual-toc h6    { font-size:11px; text-transform:uppercase; letter-spacing:.6px; color:#9ca3af; font-weight:700; margin:12px 0 4px; }
.manual-toc h6:first-child { margin-top:0; }
.manual-toc a     { display:block; color:#374151; text-decoration:none; padding:3px 6px; border-radius:4px; line-height:1.5; }
.manual-toc a:hover { background:#e5e7eb; color:#111827; }
.manual-toc a.sub { padding-left:14px; font-size:12px; color:#6b7280; }
.manual-body      { flex:1; min-width:0; }
.man-section      { background:#fff; border:1px solid #e5e7eb; border-radius:10px; padding:24px 28px; margin-bottom:20px; scroll-margin-top:16px; }
.man-section h2   { font-size:18px; font-weight:700; color:#111827; margin:0 0 16px; display:flex; align-items:center; gap:10px; padding-bottom:12px; border-bottom:2px solid #f0f0f0; }
.man-section h3   { font-size:14px; font-weight:700; color:#1d4ed8; margin:20px 0 8px; }
.man-section h4   { font-size:13px; font-weight:600; color:#374151; margin:12px 0 4px; }
.man-section p    { font-size:13px; color:#374151; line-height:1.7; margin-bottom:8px; }
.man-section ul, .man-section ol { font-size:13px; color:#374151; line-height:1.7; padding-left:20px; margin-bottom:8px; }
.man-section li   { margin-bottom:3px; }
.perm-tag         { font-family:monospace; font-size:11px; background:#eff6ff; border:1px solid #bfdbfe; color:#1d4ed8; border-radius:4px; padding:1px 6px; white-space:nowrap; }
.tip-box          { background:#fffbeb; border:1px solid #fde68a; border-radius:6px; padding:10px 14px; font-size:13px; color:#78350f; margin:10px 0; }
.tip-box i        { margin-right:6px; }
.warn-box         { background:#fef2f2; border:1px solid #fecaca; border-radius:6px; padding:10px 14px; font-size:13px; color:#991b1b; margin:10px 0; }
.info-box         { background:#eff6ff; border:1px solid #bfdbfe; border-radius:6px; padding:10px 14px; font-size:13px; color:#1e40af; margin:10px 0; }
.badge-perm       { display:inline-block; font-size:10px; background:#f3f4f6; border:1px solid #d1d5db; color:#6b7280; border-radius:3px; padding:0 5px; margin:1px; font-family:monospace; }
</style>

<div class="manual-wrap">

<!-- ── Table of Contents ─────────────────────────────────── -->
<aside class="manual-toc">
    <div class="manual-toc-inner">
        <h6>Einstieg</h6>
        <a href="#intro">Einführung</a>
        <a href="#navigation">Navigation</a>
        <a href="#dashboard">Dashboard</a>

        <h6>Verzeichnis</h6>
        <a href="#users">Benutzer</a>
        <a class="sub" href="#users-actions">Aktionen</a>
        <a class="sub" href="#users-offboarding">Offboarding</a>
        <a href="#guestusers">Gastbenutzer</a>
        <a href="#groups">Gruppen & Teams</a>
        <a href="#licenses">Lizenzen</a>
        <a href="#licenseadvisor">Lizenz-Berater</a>
        <a href="#mfa">MFA-Methoden</a>
        <a href="#passwordexpiry">Passwort-Ablauf</a>

        <h6>Speicher & Freigaben</h6>
        <a href="#onedrive">OneDrive</a>
        <a href="#sharepoint">SharePoint</a>
        <a href="#sharing">Freigaben</a>
        <a href="#sharing-monitor">Freigaben-Monitor</a>
        <a href="#sharing-policies">Freigaberichtlinien</a>

        <h6>Exchange & Komm.</h6>
        <a href="#mailboxes">Postfächer</a>
        <a href="#teamsusage">Teams-Nutzung</a>
        <a href="#adoption">Adoptions-Report</a>
        <a href="#msgcenter">Message Center</a>
        <a href="#mailflow">Mail Flow & Schutz</a>
        <a href="#servicehealth">Dienststatus</a>

        <h6>Sicherheit</h6>
        <a href="#security">Sicherheit (CA)</a>
        <a href="#securityposture">Security Posture</a>
        <a href="#securescore">Secure Score</a>
        <a href="#defender">Defender Alerts</a>
        <a href="#riskysignins">Risiko-Anmeldungen</a>
        <a href="#appregistrations">App-Registrierungen</a>
        <a href="#adminroles">Admin-Rollen</a>

        <h6>Compliance & Audit</h6>
        <a href="#devices">Geräte</a>
        <a href="#staleaccounts">Inaktive Konten</a>
        <a href="#auditlog">Audit-Log</a>
        <a href="#signinlog">Sign-in-Log</a>

        <h6>Administration</h6>
        <a href="#cron">Cron & Automatisierung</a>
        <a href="#settings">Einstellungen</a>
        <a href="#updates">Updates</a>
        <a href="#permissions">Berechtigungen</a>
    </div>
</aside>

<!-- ── Manual Body ───────────────────────────────────────── -->
<div class="manual-body">

<!-- Einführung ───────────────────────────────────────────── -->
<div class="man-section" id="intro">
    <h2><i class="bi bi-book text-primary"></i> Einführung</h2>
    <p>Das <strong>M365 Tenant Tool</strong> ist ein webbasiertes Administrator-Dashboard für Microsoft 365. Es ermöglicht die zentrale Verwaltung und Überwachung eines M365-Tenants direkt über den Browser — ohne Microsoft Entra Admin Center oder PowerShell.</p>
    <p>Die Verbindung zu Microsoft Graph erfolgt über den <strong>OAuth 2.0 Client-Credentials-Flow</strong> (Anwendungs-Berechtigungen). Das Werkzeug benötigt keinen Benutzer-Login bei Microsoft — ein einmalig eingerichtetes App-Konto in Azure AD (Entra ID) reicht aus.</p>

    <h3>Voraussetzungen</h3>
    <ul>
        <li>PHP 8.2+, Apache/Nginx, MySQL/MariaDB</li>
        <li>Eine App-Registrierung in Microsoft Entra ID mit den erforderlichen Anwendungsberechtigungen</li>
        <li>Administrator-Zustimmung (Admin Consent) für alle API-Berechtigungen</li>
        <li>Die App muss über einen Browser erreichbar sein (lokales Netzwerk oder Internet)</li>
    </ul>

    <h3>Rollen im Tool</h3>
    <ul>
        <li><strong>Administrator</strong> — vollständiger Zugriff inkl. Einstellungen, Updates, Offboarding, Wipe</li>
        <li><strong>Operator</strong> — Leserechte und begrenzte Aktionen (kein Zugriff auf Einstellungen/Updates)</li>
    </ul>
</div>

<!-- Navigation ───────────────────────────────────────────── -->
<div class="man-section" id="navigation">
    <h2><i class="bi bi-layout-sidebar text-primary"></i> Navigation & Bedienung</h2>

    <h3>Sidebar</h3>
    <p>Die linke Seitenleiste ist in thematische Bereiche unterteilt. Per Klick auf den <i class="bi bi-list"></i>-Button oben links kann sie ein- und ausgeklappt werden. Operator-Accounts sehen den Administrationsbereich nicht.</p>

    <h3>Schnellsuche (Strg+K)</h3>
    <p>Mit <kbd>Strg</kbd>+<kbd>K</kbd> (oder dem Lupensymbol in der Topbar) öffnet sich die Kommandopalette. Dort können alle Seiten des Tools per Tastatur gesucht und direkt angesprungen werden.</p>

    <h3>Daten aktualisieren</h3>
    <p>Alle Daten werden aus der Microsoft Graph API geladen und serverseitig gecacht (Standard: 15 Minuten). Mit dem <i class="bi bi-arrow-clockwise"></i>-Button in der Topbar (hängt <code>?refresh=1</code> an die URL) wird der Cache für die aktuelle Seite geleert und ein neuer Abruf gestartet.</p>

    <h3>Suche in Tabellen</h3>
    <p>Auf den meisten Listenansichten gibt es ein Suchfeld über der Tabelle. Die Suche filtert live alle sichtbaren Zeilen nach dem eingegebenen Begriff.</p>

    <h3>CSV-Export</h3>
    <p>Viele Module bieten eine Export-Schaltfläche, die die aktuell geladenen Daten als CSV-Datei herunterlädt.</p>
</div>

<!-- Dashboard ────────────────────────────────────────────── -->
<div class="man-section" id="dashboard">
    <h2><i class="bi bi-speedometer2 text-primary"></i> Dashboard</h2>
    <p>Das Dashboard gibt auf einen Blick einen Überblick über den M365-Tenant:</p>
    <ul>
        <li>Anzahl Benutzer, aktive/inaktive Konten, Gastbenutzer</li>
        <li>Lizenzübersicht: verbrauchte vs. verfügbare Plätze</li>
        <li>Sicherheitsampel: MFA-Abdeckung, Risiko-Anmeldungen, Defender-Alerts</li>
        <li>Dienststatus: aktuelle Vorfälle in Microsoft 365-Diensten</li>
        <li>Schnellzugriff zu den wichtigsten Modulen</li>
    </ul>
    <div class="tip-box"><i class="bi bi-lightbulb"></i>Beim ersten Aufruf werden alle Daten frisch aus der API geholt und gecacht. Je nach Tenant-Größe kann dies einige Sekunden dauern.</div>
    <p><span class="perm-tag">User.Read.All</span> <span class="perm-tag">Organization.Read.All</span> <span class="perm-tag">Reports.Read.All</span></p>
</div>

<!-- Benutzer ─────────────────────────────────────────────── -->
<div class="man-section" id="users">
    <h2><i class="bi bi-people text-primary"></i> Benutzer</h2>
    <p>Zeigt alle Benutzer des Tenants in einer durchsuchbaren und sortierbaren Tabelle. Spalten: Anzeigename, E-Mail, Status (aktiv/deaktiviert), Abteilung, Stelle, Lizenzen.</p>

    <h3 id="users-actions">Aktionen auf der Detailseite</h3>
    <p>Per Klick auf einen Benutzernamen öffnet sich die Detailseite mit:</p>

    <h4>Konto aktivieren / deaktivieren</h4>
    <p>Schaltet <code>accountEnabled</code> in Azure AD um. Deaktivierte Benutzer können sich nicht mehr anmelden, ihr Postfach und ihre Daten bleiben erhalten.</p>

    <h4>MFA zurücksetzen</h4>
    <p>Löscht alle registrierten Authentifizierungsmethoden des Benutzers (Authenticator-App, Telefonnummern, FIDO2-Schlüssel) — außer dem Passwort. Der Benutzer muss beim nächsten Login eine neue Methode registrieren.</p>
    <div class="warn-box"><i class="bi bi-exclamation-triangle"></i><strong>Achtung:</strong> Diese Aktion kann nicht rückgängig gemacht werden. Der Benutzer muss anschließend den MFA-Registrierungsprozess erneut durchlaufen.</div>

    <h4>Sign-in-Sessions widerrufen</h4>
    <p>Invalidiert alle aktiven Anmeldesitzungen des Benutzers (Refresh-Token-Revocation). Alle Geräte werden beim nächsten Aufruf zur Neuanmeldung aufgefordert.</p>

    <h4>Lizenz zuweisen / entfernen</h4>
    <p>Weist dem Benutzer eine SKU direkt zu oder entfernt sie. Die Lizenz muss im Tenant verfügbar sein (freie Plätze vorhanden).</p>

    <h4>Benutzer bearbeiten</h4>
    <p>Ändert Profilfelder wie Anzeigename, Stellenbezeichnung, Abteilung, Telefon und Nutzungsstandort direkt über die Graph API.</p>

    <h4>Anmeldeverlauf</h4>
    <p>Zeigt die letzten 25 Anmeldeereignisse des Benutzers mit App, IP-Adresse, Standort, Status und Risikoinformationen.</p>
    <div class="info-box"><i class="bi bi-info-circle"></i>Für den Anmeldeverlauf wird <span class="perm-tag">AuditLog.Read.All</span> benötigt.</div>

    <h4>Gruppenmitgliedschaften</h4>
    <p>Listet alle Gruppen, in denen der Benutzer Mitglied ist.</p>

    <h3 id="users-offboarding">Offboarding-Assistent</h3>
    <p>Der Offboarding-Assistent führt in einem Schritt mehrere Aktionen gleichzeitig durch:</p>
    <ul>
        <li>Konto deaktivieren</li>
        <li>Sign-in-Sessions widerrufen</li>
        <li>MFA-Methoden zurücksetzen</li>
        <li>Alle Lizenzen entfernen</li>
        <li>Aus allen Gruppen entfernen (außer On-Premises-synchronisierte Gruppen)</li>
    </ul>
    <div class="warn-box"><i class="bi bi-exclamation-triangle"></i>Beim Offboarding werden die Gruppen-Mitgliedschaften dauerhaft entfernt. Diese müssen bei Bedarf manuell wiederhergestellt werden.</div>
    <p><span class="perm-tag">User.Read.All</span> <span class="perm-tag">User.ReadWrite.All</span> <span class="perm-tag">UserAuthenticationMethod.ReadWrite.All</span> <span class="perm-tag">AuditLog.Read.All</span></p>
</div>

<!-- Gastbenutzer ─────────────────────────────────────────── -->
<div class="man-section" id="guestusers">
    <h2><i class="bi bi-person-badge text-primary"></i> Gastbenutzer</h2>
    <p>Listet alle Gastbenutzer (<code>userType = Guest</code>) im Tenant. Gastbenutzer werden typischerweise für externe Kollaborationen (SharePoint, Teams) eingeladen.</p>
    <p>In der Tabelle sind sichtbar: E-Mail, Einladungsstatus, letzte Anmeldung, Lizenzen, Erstelldatum.</p>

    <h3>Aktionen</h3>
    <ul>
        <li><strong>Deaktivieren</strong> — setzt <code>accountEnabled = false</code>, der Gast kann sich nicht mehr anmelden</li>
        <li><strong>Entfernen</strong> — löscht das Gastkonto vollständig aus dem Tenant</li>
    </ul>
    <div class="tip-box"><i class="bi bi-lightbulb"></i>Regelmäßige Bereinigung alter Gastkonten ist eine wichtige Sicherheitsmaßnahme. Das Modul „Inaktive Konten" hilft dabei, Gäste zu identifizieren, die sich lange nicht mehr angemeldet haben.</div>
    <p><span class="perm-tag">User.Read.All</span> <span class="perm-tag">User.ReadWrite.All</span> <span class="perm-tag">AuditLog.Read.All</span></p>
</div>

<!-- Gruppen ──────────────────────────────────────────────── -->
<div class="man-section" id="groups">
    <h2><i class="bi bi-diagram-3 text-primary"></i> Gruppen & Teams</h2>
    <p>Zeigt alle Gruppen im Tenant: Microsoft 365-Gruppen, Sicherheitsgruppen und E-Mail-aktivierte Gruppen. Teams-aktivierte Gruppen sind entsprechend markiert.</p>

    <h3>Gruppendetails</h3>
    <p>Per Klick auf eine Gruppe öffnet sich die Detailansicht mit Mitgliedern, Eigentümern und Gruppentyp.</p>

    <h3>Mitglieder & Eigentümer</h3>
    <p>Mitglieder und Eigentümer können direkt hinzugefügt oder entfernt werden. Bei On-Premises-synchronisierten Gruppen ist dies nicht möglich (Änderungen müssen im lokalen Active Directory erfolgen).</p>

    <h3>Gruppe erstellen</h3>
    <p>Erstellt eine neue Microsoft 365-Gruppe oder Sicherheitsgruppe direkt im Tenant.</p>

    <h3>Gruppe löschen</h3>
    <p>Löscht die Gruppe dauerhaft. Bei Microsoft 365-Gruppen werden auch das zugehörige Team, das Postfach und die SharePoint-Site gelöscht (Soft-Delete, 30 Tage wiederherstellbar im Entra Portal).</p>

    <h3>Inaktive Gruppen</h3>
    <p>Unter <strong>Gruppen → Inaktive Gruppen</strong> werden Microsoft 365-Gruppen angezeigt, die seit mehr als 90 Tagen keine Aktivität hatten. Grundlage ist die letzte Aktivität der zugehörigen SharePoint-Site.</p>
    <p><span class="perm-tag">Group.Read.All</span> <span class="perm-tag">Group.ReadWrite.All</span> <span class="perm-tag">GroupMember.ReadWrite.All</span></p>
</div>

<!-- Lizenzen ─────────────────────────────────────────────── -->
<div class="man-section" id="licenses">
    <h2><i class="bi bi-award text-primary"></i> Lizenzen</h2>
    <p>Zeigt alle abonnierten Lizenz-SKUs im Tenant mit verbrauchten und verfügbaren Plätzen sowie den enthaltenen Service-Plänen.</p>

    <h3>Ablaufende Lizenzen</h3>
    <p>Unter <strong>Lizenzen → Ablauf</strong> werden alle Abonnements angezeigt, die in den nächsten 90 Tagen ablaufen. Dies ermöglicht eine frühzeitige Verlängerungsplanung.</p>

    <div class="tip-box"><i class="bi bi-lightbulb"></i>Der CSV-Export enthält alle Lizenzen mit Ablaufdatum, Verbrauch und Kosten-relevanten Infos — nützlich für Budgetplanung.</div>
    <p><span class="perm-tag">Organization.Read.All</span></p>
</div>

<!-- Lizenz-Berater ───────────────────────────────────────── -->
<div class="man-section" id="licenseadvisor">
    <h2><i class="bi bi-lightbulb text-primary"></i> Lizenz-Berater</h2>
    <p>Analysiert die Lizenznutzung und identifiziert Benutzer, die lizenzierte Dienste nicht aktiv nutzen (potenzielle Einsparungen).</p>
    <p>In den Einstellungen können Kriterien konfiguriert werden, welche Dienste für eine „sinnvolle" Lizenz als nötig gelten (Exchange Online, Teams, SharePoint, OneDrive, Office Desktop, Intune).</p>
    <p>Das Modul zeigt dann Benutzer, die lizenziert sind, aber mindestens einen der konfigurierten Dienste nicht nutzen — mit der Möglichkeit zum CSV-Export für die Entscheidungsfindung.</p>
    <p><span class="perm-tag">User.Read.All</span> <span class="perm-tag">Reports.Read.All</span> <span class="perm-tag">AuditLog.Read.All</span></p>
</div>

<!-- MFA-Methoden ─────────────────────────────────────────── -->
<div class="man-section" id="mfa">
    <h2><i class="bi bi-shield-lock text-primary"></i> MFA-Methoden</h2>
    <p>Zeigt für jeden Benutzer, welche Authentifizierungsmethoden registriert sind: Microsoft Authenticator, SMS/Anruf, FIDO2-Schlüssel, softwarebasierter TOTP-Token u.a.</p>
    <p>Benutzer ohne MFA-Registrierung sind deutlich markiert und können gefiltert angezeigt werden. Der CSV-Export eignet sich für Compliance-Berichte.</p>
    <div class="tip-box"><i class="bi bi-lightbulb"></i>Verwende den Filter „Kein MFA", um schnell alle Konten ohne zweiten Faktor zu identifizieren, und leite dann entsprechende Maßnahmen ein.</div>
    <p><span class="perm-tag">UserAuthenticationMethod.Read.All</span></p>
</div>

<!-- Passwort-Ablauf ──────────────────────────────────────── -->
<div class="man-section" id="passwordexpiry">
    <h2><i class="bi bi-key text-primary"></i> Passwort-Ablauf</h2>
    <p>Zeigt Benutzer, deren Passwort abgelaufen ist oder bald abläuft — basierend auf der konfigurierten Passwort-Ablauf-Richtlinie (Standard: 90 Tage).</p>
    <p>Die Ansicht ist in Kategorien unterteilt: Abgelaufen, Kritisch (weniger als 7 Tage), Warnung (weniger als 30 Tage) und Alle.</p>
    <p>Benutzer mit aktiviertem „Passwort läuft nie ab" werden entsprechend markiert.</p>
    <p><span class="perm-tag">User.Read.All</span></p>
</div>

<!-- OneDrive ─────────────────────────────────────────────── -->
<div class="man-section" id="onedrive">
    <h2><i class="bi bi-cloud text-primary"></i> OneDrive</h2>
    <p>Zeigt die OneDrive-Nutzung aller Benutzer: verwendeter und verfügbarer Speicher, Anzahl der Dateien, letzte Aktivität.</p>
    <p>Der Bericht basiert auf dem Microsoft 365 Nutzungsbericht (<code>/reports/oneDriveUsageAccountDetail</code>) und wird täglich von Microsoft aktualisiert — die Daten können daher bis zu 48 Stunden alt sein.</p>
    <p><span class="perm-tag">Reports.Read.All</span></p>
</div>

<!-- SharePoint ───────────────────────────────────────────── -->
<div class="man-section" id="sharepoint">
    <h2><i class="bi bi-share text-primary"></i> SharePoint</h2>
    <p>Listet alle SharePoint-Sites im Tenant mit URL, Typ (Kommunikationssite / Teamsite), Speichernutzung und letzter Aktivität.</p>
    <p>Per Klick auf eine Site öffnet sich die Detailansicht mit den zugehörigen Dokumentbibliotheken.</p>
    <p><span class="perm-tag">Sites.Read.All</span></p>
</div>

<!-- Freigaben ────────────────────────────────────────────── -->
<div class="man-section" id="sharing">
    <h2><i class="bi bi-link-45deg text-primary"></i> Freigaben</h2>
    <p>Zeigt alle externen Freigaben (Sharing-Links) im Tenant — Dateien und Ordner, die per Link nach außen geteilt wurden.</p>
    <p>Für jede Freigabe sind sichtbar: Dateiname, SharePoint-Site, Freigabe-Typ (Anonym, Org, Spezifisch), Ersteller, Ablaufdatum.</p>

    <h3>Freigabe widerrufen</h3>
    <p>Einzelne Freigabe-Links können direkt aus der Tabelle heraus widerrufen werden. Dies entfernt den Link, die Datei bleibt erhalten.</p>
    <div class="warn-box"><i class="bi bi-exclamation-triangle"></i>Das Widerrufen einer anonymen Freigabe deaktiviert den Link sofort — alle Personen, die über diesen Link zugegriffen haben, verlieren den Zugriff.</div>
    <p><span class="perm-tag">Sites.Read.All</span> <span class="perm-tag">Files.ReadWrite.All</span></p>
</div>

<!-- Freigaben-Monitor ────────────────────────────────────── -->
<div class="man-section" id="sharing-monitor">
    <h2><i class="bi bi-eye-slash text-primary"></i> Freigaben-Monitor</h2>
    <p>Der Freigaben-Monitor ermöglicht es, Benutzer regelmäßig per E-Mail zu ihren externen Freigaben zu befragen — sie können direkt aus der E-Mail heraus Freigaben bestätigen oder widerrufen.</p>

    <h3>Wie es funktioniert</h3>
    <ol>
        <li>Der Cron-Job scannt täglich alle externen Freigaben</li>
        <li>Benutzer erhalten eine E-Mail mit einer Liste ihrer aktiven Freigaben</li>
        <li>Sie können jede Freigabe per Klick bestätigen oder widerrufen</li>
        <li>Nicht reagierte Freigaben werden nach dem konfigurierten Zeitraum automatisch widerrufen</li>
    </ol>

    <h3>Konfiguration</h3>
    <p>Im Einstellungsbereich (<strong>Freigaben-Monitor</strong>): Review-Intervall (Standard: 30 Tage), Kulanzfrist (Standard: 7 Tage), nur anonyme Freigaben prüfen.</p>

    <h3>Admin-Ansicht</h3>
    <p>Die Admin-Ansicht zeigt alle aktiven Review-Anfragen mit Status. Von hier aus können Freigaben auch manuell widerrufen oder Erinnerungs-E-Mails versendet werden.</p>
    <p><span class="perm-tag">Sites.Read.All</span> <span class="perm-tag">Files.ReadWrite.All</span></p>
</div>

<!-- Freigaberichtlinien ──────────────────────────────────── -->
<div class="man-section" id="sharing-policies">
    <h2><i class="bi bi-sliders text-primary"></i> Freigaberichtlinien</h2>
    <p>Verwaltet die tenant-weiten Freigabeeinstellungen für SharePoint und OneDrive:</p>
    <ul>
        <li><strong>Tenant-Ebene</strong>: Maximale erlaubte Freigabe-Stufe (Anonym, Org, Org+Anonym deaktiviert, Nur eingeladene Benutzer)</li>
        <li><strong>Site-Ebene</strong>: Freigabe-Einstellung für eine einzelne SharePoint-Site (kann restriktiver sein als die Tenant-Einstellung, aber nicht freizügiger)</li>
    </ul>
    <div class="tip-box"><i class="bi bi-lightbulb"></i>Als Best Practice empfiehlt sich die Tenant-Einstellung auf „Nur authentifizierte Benutzer" und für einzelne Sites bei Bedarf spezifisch zu lockern.</div>
    <p><span class="perm-tag">Sites.ReadWrite.All</span></p>
</div>

<!-- Postfächer ───────────────────────────────────────────── -->
<div class="man-section" id="mailboxes">
    <h2><i class="bi bi-envelope text-primary"></i> Postfächer</h2>
    <p>Zeigt alle Exchange Online-Postfächer (Benutzer, Shared, Room, Equipment) mit Größe, Quota, letzter Aktivität und Weiterleitungseinstellungen.</p>

    <h3>Postfach-Detailseite</h3>
    <ul>
        <li><strong>Weiterleitung einrichten</strong>: Konfiguriert <code>ForwardingSmtpAddress</code> — E-Mails werden an eine externe oder interne Adresse weitergeleitet (optional: Kopie im Postfach behalten)</li>
        <li><strong>Abwesenheitsnachricht</strong>: Setzt die AutoReply-Konfiguration für intern und extern</li>
    </ul>

    <h3>Externe Weiterleitungen</h3>
    <p>Unter <strong>Postfächer → Externe Weiterleitungen</strong> werden alle Postfächer angezeigt, die E-Mails an externe Adressen weiterleiten. Dies ist ein wichtiger Sicherheitscheck gegen Mail-Exfiltration.</p>
    <p>Von dieser Ansicht aus können Weiterleitungen direkt deaktiviert werden.</p>

    <h3>Shared Mailboxes</h3>
    <p>Listet alle freigegebenen Postfächer mit Mitglieder-Übersicht. Neue Shared Mailboxes können direkt erstellt werden.</p>
    <div class="tip-box"><i class="bi bi-lightbulb"></i>Shared Mailboxes bis 50 GB benötigen in der Regel keine separate Lizenz — sie werden automatisch mit Exchange Online-Postfach-Merkmalen versehen.</div>
    <p><span class="perm-tag">Mail.ReadBasic.All</span> <span class="perm-tag">MailboxSettings.ReadWrite</span></p>
</div>

<!-- Teams-Nutzung ────────────────────────────────────────── -->
<div class="man-section" id="teamsusage">
    <h2><i class="bi bi-camera-video text-primary"></i> Teams-Nutzung</h2>
    <p>Zeigt die Teams-Aktivität aller Benutzer: Nachrichten gesendet, Anrufe, Meetings, Reaktionen — basierend auf den Microsoft 365-Nutzungsberichten der letzten 30 Tage.</p>
    <p>Nützlich um zu erkennen, welche Benutzer Teams kaum nutzen (und ob eine Teams-Lizenz gerechtfertigt ist).</p>
    <p><span class="perm-tag">Reports.Read.All</span></p>
</div>

<!-- Adoptions-Report ─────────────────────────────────────── -->
<div class="man-section" id="adoption">
    <h2><i class="bi bi-graph-up-arrow text-primary"></i> Adoptions-Report</h2>
    <p>Gibt einen aggregierten Überblick über die Nutzung der M365-Dienste im Tenant: aktive Benutzer je Dienst (Exchange, SharePoint, OneDrive, Teams, Yammer) über verschiedene Zeiträume (7, 30, 90, 180 Tage).</p>
    <p>Enthält Diagramme zur zeitlichen Entwicklung der Nutzungszahlen.</p>
    <p><span class="perm-tag">Reports.Read.All</span></p>
</div>

<!-- Message Center ───────────────────────────────────────── -->
<div class="man-section" id="msgcenter">
    <h2><i class="bi bi-megaphone text-primary"></i> Message Center</h2>
    <p>Zeigt die aktuellen Nachrichten aus dem Microsoft 365 Message Center — Ankündigungen zu geplanten Änderungen, neuen Features und Wartungsarbeiten.</p>
    <p>Nachrichten sind nach Kategorie und Schweregrad gefiltert darstellbar. Wichtige Änderungen, die Administrative Maßnahmen erfordern, sind hervorgehoben.</p>
    <p><span class="perm-tag">ServiceMessage.Read.All</span></p>
</div>

<!-- Mail Flow & Schutz ───────────────────────────────────── -->
<div class="man-section" id="mailflow">
    <h2><i class="bi bi-arrow-left-right text-primary"></i> Mail Flow & Schutz</h2>
    <p>Fasst die wichtigsten Exchange Online Sicherheits- und Mail-Flow-Konfigurationen zusammen:</p>
    <ul>
        <li><strong>Anti-Spam-Richtlinien</strong>: Konfiguration der Spam-Filter-Einstellungen</li>
        <li><strong>Anti-Malware-Richtlinien</strong>: Malware-Erkennungseinstellungen</li>
        <li><strong>Anti-Phishing-Richtlinien</strong>: Schutz vor Phishing und Spoofing</li>
        <li><strong>Safe Attachments / Safe Links</strong>: Defender for Office 365-Richtlinien (wenn lizenziert)</li>
        <li><strong>Connectors</strong>: Eingehende und ausgehende Mail-Flow-Konnektoren</li>
        <li><strong>Transport-Regeln</strong>: Übersicht über aktive Mail-Flow-Regeln</li>
    </ul>
    <p><span class="perm-tag">Mail.ReadBasic.All</span></p>
</div>

<!-- Dienststatus ─────────────────────────────────────────── -->
<div class="man-section" id="servicehealth">
    <h2><i class="bi bi-heart-pulse text-primary"></i> Dienststatus</h2>
    <p>Zeigt den aktuellen Status aller Microsoft 365-Dienste (Exchange, SharePoint, Teams, Entra ID, Intune usw.).</p>
    <p>Aktive Vorfälle und Wartungsfenster werden mit Details zum Fortschritt und der voraussichtlichen Behebungszeit angezeigt. Vergangene Vorfälle der letzten 7 Tage sind in einer separaten Liste sichtbar.</p>
    <p><span class="perm-tag">ServiceMessage.Read.All</span></p>
</div>

<!-- Sicherheit (CA) ──────────────────────────────────────── -->
<div class="man-section" id="security">
    <h2><i class="bi bi-shield-check text-primary"></i> Sicherheit (Conditional Access)</h2>
    <p>Listet alle Richtlinien für bedingten Zugriff (Conditional Access Policies) im Tenant mit Status (aktiviert, deaktiviert, Berichtsmodus), Bedingungen und Zugriffsteuerungen.</p>

    <h3>Richtlinien ein-/ausschalten</h3>
    <p>CA-Richtlinien können direkt im Tool aktiviert oder deaktiviert werden (Berichtsmodus ist ebenfalls möglich). Dies erfordert erhöhte Berechtigungen.</p>
    <div class="warn-box"><i class="bi bi-exclamation-triangle"></i><strong>Vorsicht:</strong> Das Deaktivieren einer aktiven CA-Richtlinie kann die Sicherheit des Tenants erheblich reduzieren. Änderungen sollten sorgfältig geplant werden.</div>
    <p><span class="perm-tag">Policy.Read.All</span> <span class="perm-tag">Policy.ReadWrite.ConditionalAccess</span></p>
</div>

<!-- Security Posture ─────────────────────────────────────── -->
<div class="man-section" id="securityposture">
    <h2><i class="bi bi-shield-fill-check text-primary"></i> Security Posture</h2>
    <p>Gibt eine aggregierte Sicherheitsübersicht des Tenants: MFA-Abdeckung, Risiko-Benutzer, Geräte-Compliance, aktive CA-Richtlinien, externe Freigaben und weitere Sicherheitsindikatoren.</p>
    <p>Jeder Indikator ist mit einer Ampel (grün/gelb/rot) bewertet und mit dem zugehörigen Modul verlinkt, um direkt handeln zu können.</p>
    <p><span class="perm-tag">User.Read.All</span> <span class="perm-tag">Policy.Read.All</span> <span class="perm-tag">IdentityRiskyUser.Read.All</span></p>
</div>

<!-- Secure Score ─────────────────────────────────────────── -->
<div class="man-section" id="securescore">
    <h2><i class="bi bi-bar-chart-line text-primary"></i> Secure Score</h2>
    <p>Zeigt den Microsoft Secure Score des Tenants — eine Bewertung der Sicherheitskonfiguration auf einer Skala von 0–100 — sowie den Verlauf über die letzten 30 Tage.</p>
    <p>Die einzelnen Verbesserungsmaßnahmen (Control Scores) werden mit ihrer Punktzahl und dem Implementierungsstatus aufgelistet. Direkt-Links ins Microsoft 365 Defender Portal ermöglichen die schnelle Umsetzung.</p>
    <p><span class="perm-tag">SecurityEvents.Read.All</span></p>
</div>

<!-- Defender Alerts ──────────────────────────────────────── -->
<div class="man-section" id="defender">
    <h2><i class="bi bi-bell text-primary"></i> Defender Alerts</h2>
    <p>Zeigt offene Sicherheitswarnungen aus Microsoft Defender for Endpoint, Defender for Office 365 und Microsoft Sentinel.</p>
    <p>Für jeden Alert sind sichtbar: Titel, Schweregrad (Hoch/Mittel/Niedrig/Informativ), betroffene Entität, Status und Erstellzeit.</p>

    <h3>Alert auflösen</h3>
    <p>Alerts können direkt im Tool als „Gelöst" markiert werden. Dies schließt den Alert in Microsoft Defender.</p>
    <p><span class="perm-tag">SecurityAlert.ReadWrite.All</span></p>
</div>

<!-- Risiko-Anmeldungen ───────────────────────────────────── -->
<div class="man-section" id="riskysignins">
    <h2><i class="bi bi-exclamation-triangle text-primary"></i> Risiko-Anmeldungen</h2>
    <p>Zeigt Benutzer, denen Microsoft Entra ID Protection ein erhöhtes Anmelderisiko zugewiesen hat — z.B. durch ungewöhnliche Anmeldeorte, kompromittierte Zugangsdaten (Credential-Leak-Erkennung) oder anomales Verhalten.</p>

    <h3>Aktionen</h3>
    <ul>
        <li><strong>Als kompromittiert bestätigen</strong>: Markiert das Benutzerkonto als kompromittiert, erzwingt Passwort-Reset und blockiert laufende Sessions</li>
        <li><strong>Risiko verwerfen</strong>: Verwirft den Risikohinweis (wenn es sich um ein False Positive handelt)</li>
    </ul>
    <div class="info-box"><i class="bi bi-info-circle"></i>Dieses Modul erfordert Microsoft Entra ID P2 (oder Microsoft 365 E5) im Tenant.</div>
    <p><span class="perm-tag">IdentityRiskyUser.Read.All</span> <span class="perm-tag">IdentityRiskyUser.ReadWrite.All</span></p>
</div>

<!-- App-Registrierungen ──────────────────────────────────── -->
<div class="man-section" id="appregistrations">
    <h2><i class="bi bi-grid-3x3-gap text-primary"></i> App-Registrierungen & Enterprise Apps</h2>
    <p>Zeigt alle App-Registrierungen und Enterprise-Anwendungen im Tenant mit ihren API-Berechtigungen, Client-Secrets und Zertifikaten.</p>

    <h3>Client-Secrets verwalten</h3>
    <p>Auf der Detailseite einer App-Registrierung können neue Client-Secrets erstellt und vorhandene gelöscht werden. Ablaufende Secrets sind farblich markiert.</p>
    <div class="tip-box"><i class="bi bi-lightbulb"></i>Secrets, die in weniger als 30 Tagen ablaufen, werden orange markiert. Abgelaufene Secrets werden rot hervorgehoben. Prüfe regelmäßig, ob Produktivsysteme davon betroffen sind.</div>
    <p><span class="perm-tag">Application.Read.All</span> <span class="perm-tag">Application.ReadWrite.All</span></p>
</div>

<!-- Admin-Rollen ─────────────────────────────────────────── -->
<div class="man-section" id="adminroles">
    <h2><i class="bi bi-person-lock text-primary"></i> Admin-Rollen</h2>
    <p>Zeigt alle Microsoft Entra-Administratorrollen und die jeweils zugewiesenen Benutzer. Privilegierte Rollen (Globaler Administrator, Privilegierter Rollenverwaltungsadministrator usw.) sind besonders hervorgehoben.</p>

    <h3>Rollen zuweisen / entfernen</h3>
    <p>Benutzer können direkt einer Rolle zugewiesen oder aus einer Rolle entfernt werden. Die Zuweisung erfolgt als permanente direkte Zuweisung (kein PIM).</p>
    <div class="warn-box"><i class="bi bi-exclamation-triangle"></i>Administratorrollen-Änderungen sind sicherheitskritisch. Die Vergabe der Rolle „Globaler Administrator" sollte auf das absolute Minimum beschränkt werden.</div>
    <p><span class="perm-tag">RoleManagement.Read.All</span> <span class="perm-tag">RoleManagement.ReadWrite.Directory</span></p>
</div>

<!-- Geräte ───────────────────────────────────────────────── -->
<div class="man-section" id="devices">
    <h2><i class="bi bi-phone text-primary"></i> Geräte (Intune)</h2>
    <p>Zeigt alle in Microsoft Intune verwalteten Geräte mit Betriebssystem, Version, Compliance-Status, Verschlüsselungsstatus und letztem Sync.</p>

    <h3>Aktionen</h3>
    <ul>
        <li><strong>Synchronisieren</strong>: Sendet eine Sync-Anfrage ans Gerät — beim nächsten Check-In werden Richtlinien und Status aktualisiert</li>
        <li><strong>Retire</strong> (nur Admin): Entfernt Unternehmens-Apps und -Daten, das Gerät bleibt persönlich nutzbar (für BYOD geeignet)</li>
        <li><strong>Wipe</strong> (nur Admin): Setzt das Gerät auf Werkseinstellungen zurück — <strong>alle Daten werden unwiderruflich gelöscht</strong></li>
    </ul>

    <h3>BitLocker-Schlüssel</h3>
    <p>Auf der Gerätdetailseite werden BitLocker-Recovery-Schlüssel angezeigt (sofern in Intune hinterlegt und die Berechtigung vorhanden ist).</p>
    <div class="warn-box"><i class="bi bi-exclamation-triangle"></i>Ein Wipe kann nicht rückgängig gemacht werden. Vergewissere dich, dass das Gerät tatsächlich verloren, gestohlen oder auszumustern ist.</div>
    <p><span class="perm-tag">DeviceManagementManagedDevices.Read.All</span> <span class="perm-tag">DeviceManagementManagedDevices.PrivilegedOperations.All</span></p>
</div>

<!-- Inaktive Konten ──────────────────────────────────────── -->
<div class="man-section" id="staleaccounts">
    <h2><i class="bi bi-person-x text-primary"></i> Inaktive Konten</h2>
    <p>Listet Benutzerkonten, die sich länger als die konfigurierte Anzahl Tage nicht mehr angemeldet haben (Standard: 90 Tage).</p>

    <h3>Konfiguration</h3>
    <p>In den Einstellungen kann konfiguriert werden:</p>
    <ul>
        <li><strong>Inaktivitätsschwelle</strong>: Ab wann gilt ein Konto als inaktiv (Standard: 90 Tage)</li>
        <li><strong>Automatisches Lizenz-Entfernen</strong>: Optionale Automatisierung — Lizenzen werden nach X Tagen Inaktivität automatisch entzogen</li>
        <li><strong>Vorwarnzeit</strong>: Benutzer erhalten X Tage vor der automatischen Aktion eine Warn-E-Mail</li>
    </ul>

    <h3>Lizenz manuell entfernen</h3>
    <p>Für einzelne inaktive Benutzer können Lizenzen direkt aus der Tabelle heraus entzogen werden.</p>
    <p><span class="perm-tag">User.Read.All</span> <span class="perm-tag">AuditLog.Read.All</span></p>
</div>

<!-- Audit-Log ────────────────────────────────────────────── -->
<div class="man-section" id="auditlog">
    <h2><i class="bi bi-clock-history text-primary"></i> Audit-Log</h2>
    <p>Zeigt Aktivitäten aus dem Microsoft Entra-Audit-Log: Benutzeränderungen, Gruppen-Änderungen, App-Zuweisungen, Rollen-Änderungen und weitere Verzeichnisoperationen.</p>
    <p>Die letzten 200 Ereignisse werden angezeigt, filterbar nach Kategorie und Datum. Der CSV-Export enthält alle sichtbaren Einträge.</p>
    <div class="info-box"><i class="bi bi-info-circle"></i>Das Entra-Audit-Log speichert Daten 30 Tage (Azure AD Free) bzw. 90 Tage (Azure AD P1/P2). Ältere Daten sind nur über Azure Monitor / Log Analytics zugänglich.</div>
    <p><span class="perm-tag">AuditLog.Read.All</span></p>
</div>

<!-- Sign-in-Log ──────────────────────────────────────────── -->
<div class="man-section" id="signinlog">
    <h2><i class="bi bi-journal-text text-primary"></i> Sign-in-Log</h2>
    <p>Zeigt die Anmeldeprotokolle des Tenants: Wer hat sich wann, von welchem Gerät und welcher IP-Adresse angemeldet, mit welcher App, mit welchem Ergebnis und ob Conditional Access angewendet wurde.</p>
    <p>Filter: Zeitraum, Status (Erfolg/Fehler/Unterbrochen), Benutzer, App.</p>
    <p>Der CSV-Export eignet sich für Compliance-Audits und Forensik-Untersuchungen.</p>
    <p><span class="perm-tag">AuditLog.Read.All</span></p>
</div>

<!-- Cron ─────────────────────────────────────────────────── -->
<div class="man-section" id="cron">
    <h2><i class="bi bi-clock text-primary"></i> Cron & Automatisierung</h2>
    <p>Verwaltet wiederkehrende Hintergrundaufgaben des Tools. Jeder Job hat einen konfigurierbaren Zeitplan (Cron-Ausdruck) und kann auch manuell ausgelöst werden.</p>

    <h3>Verfügbare Jobs</h3>
    <ul>
        <li><strong>Alert-Check</strong>: Prüft Sicherheitsmetriken und sendet E-Mail-Benachrichtigungen (MFA-Abdeckung unter Schwellwert, neue Risiko-Benutzer, neue anonyme Freigaben)</li>
        <li><strong>Freigaben-Monitor-Scan</strong>: Scannt externe Freigaben und versendet Review-E-Mails</li>
        <li><strong>Inaktive Konten — Warn-E-Mail</strong>: Versendet Warnungen vor automatischem Lizenz-Entzug</li>
        <li><strong>Inaktive Konten — Auto-Release</strong>: Entzieht Lizenzen bei Inaktivität (wenn aktiviert)</li>
        <li><strong>Wöchentlicher Report</strong>: Sendet einen wöchentlichen Zusammenfassungs-Report per E-Mail</li>
    </ul>

    <h3>Einrichten des System-Crons</h3>
    <p>Das Tool selbst führt keine Hintergrundprozesse aus. Es muss ein System-Cron eingerichtet werden, der regelmäßig den Tool-internen Cron-Runner aufruft:</p>
    <pre style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:6px;padding:10px;font-size:12px;overflow-x:auto;">* * * * * www-data php /var/www/m365.example.com/artisan cron:run</pre>
    <p>Oder via HTTP-Aufruf (z.B. mit curl in einem Shell-Script).</p>
    <p><span class="perm-tag">Mail.ReadBasic.All</span> <span class="perm-tag">Sites.Read.All</span> <span class="perm-tag">User.Read.All</span></p>
</div>

<!-- Einstellungen ────────────────────────────────────────── -->
<div class="man-section" id="settings">
    <h2><i class="bi bi-gear text-primary"></i> Einstellungen</h2>
    <p>Zentraler Konfigurationsbereich für alle allgemeinen Tool-Einstellungen. Nur für Administratoren sichtbar.</p>

    <h3>Allgemein</h3>
    <ul>
        <li><strong>App-Name</strong>: Wird im Browser-Tab und in der Sidebar angezeigt</li>
        <li><strong>Cache-Dauer</strong>: Wie lange API-Antworten gecacht werden (5–60 Min.)</li>
        <li><strong>Zeitzone</strong>: Für Zeitstempel-Anzeigen im Tool</li>
    </ul>

    <h3>E-Mail & SMTP</h3>
    <p>Konfiguriert den ausgehenden Mailserver für Alert-E-Mails, Freigaben-Monitor und Berichte. Pflichtfelder: SMTP-Host, Port, Absender-Adresse, Empfänger-Adresse. Optional: SMTP-Authentifizierung.</p>

    <h3>Admin-Passwort / Operator-Konto</h3>
    <p>Ändert das Passwort des Administrator-Kontos. Das Operator-Konto ist ein zweites Login mit eingeschränkten Rechten (nur Lesen + begrenzte Aktionen, kein Zugriff auf Administration).</p>

    <h3>Berechtigungen prüfen</h3>
    <p>Zeigt, welche Microsoft Graph Berechtigungen dem konfigurierten App-Konto erteilt wurden und welche Module dadurch eingeschränkt sind. Nach Änderungen in Azure AD kann das Token über den Button „Token erneuern & neu prüfen" sofort aktualisiert werden.</p>

    <h3>Cache leeren</h3>
    <p>Löscht alle zwischengespeicherten API-Antworten sofort. Hilfreich nach manuellen Änderungen direkt in Azure AD.</p>
</div>

<!-- Updates ──────────────────────────────────────────────── -->
<div class="man-section" id="updates">
    <h2><i class="bi bi-cloud-arrow-down text-primary"></i> Updates</h2>
    <p>Ermöglicht das automatische Aktualisieren des Tools auf die neueste Version aus dem konfigurierten Update-Channel.</p>

    <h3>Update-Channels</h3>
    <ul>
        <li><strong>stable</strong>: Produktionsreife Releases — empfohlen für den Produktivbetrieb</li>
        <li><strong>development</strong>: Aktuelle Entwicklungsversion mit neuen Features — nur für Testumgebungen</li>
    </ul>

    <h3>Update-Prozess</h3>
    <ol>
        <li>„Auf Updates prüfen" klicken — vergleicht die installierte SHA mit der neuesten im Channel</li>
        <li>Falls ein Update verfügbar ist, erscheint „Update installieren"</li>
        <li>Während des Updates wird ein Fortschrittsbalken angezeigt</li>
        <li>Nach dem Update lädt die Seite automatisch neu</li>
    </ol>
    <div class="tip-box"><i class="bi bi-lightbulb"></i>Geschützte Verzeichnisse (<code>config/</code>, <code>storage/</code>, <code>vendor/</code>, <code>composer.lock</code>) werden beim Update nie überschrieben — Konfiguration und Daten bleiben erhalten.</div>

    <h3>Datenbank-Migrationen</h3>
    <p>Nach einem Update können ausstehende SQL-Migrationen manuell oder automatisch ausgeführt werden. Das Tool zeigt an, welche Migrationen bereits angewendet wurden.</p>
</div>

<!-- Berechtigungen ───────────────────────────────────────── -->
<div class="man-section" id="permissions">
    <h2><i class="bi bi-shield-check text-primary"></i> Erforderliche Graph-Berechtigungen</h2>
    <p>Alle Berechtigungen sind <strong>Anwendungsberechtigungen</strong> (Application Permissions), keine delegierten Berechtigungen. Sie werden in der Azure App-Registrierung unter <em>API-Berechtigungen → Microsoft Graph → Anwendungsberechtigungen</em> erteilt und erfordern <strong>Administrator-Zustimmung</strong>.</p>

    <h3>Mindest-Berechtigungen (Lesen)</h3>
    <ul>
        <li><span class="perm-tag">User.Read.All</span> — Benutzer, MFA, Offboarding, Inaktive Konten</li>
        <li><span class="perm-tag">Group.Read.All</span> — Gruppen & Teams</li>
        <li><span class="perm-tag">Organization.Read.All</span> — Tenant-Infos, Lizenzen</li>
        <li><span class="perm-tag">AuditLog.Read.All</span> — Audit-Log, Sign-in-Log, Anmeldeverlauf</li>
        <li><span class="perm-tag">Reports.Read.All</span> — OneDrive, Teams-Nutzung, Adoptions-Report</li>
        <li><span class="perm-tag">Sites.Read.All</span> — SharePoint, Freigaben</li>
        <li><span class="perm-tag">Policy.Read.All</span> — Sicherheitsrichtlinien, CA-Richtlinien</li>
        <li><span class="perm-tag">Application.Read.All</span> — App-Registrierungen</li>
        <li><span class="perm-tag">Mail.ReadBasic.All</span> — Postfächer, Mail-Flow</li>
        <li><span class="perm-tag">DeviceManagementManagedDevices.Read.All</span> — Intune-Geräte</li>
        <li><span class="perm-tag">ServiceMessage.Read.All</span> — Dienststatus, Message Center</li>
        <li><span class="perm-tag">UserAuthenticationMethod.Read.All</span> — MFA-Methoden</li>
        <li><span class="perm-tag">RoleManagement.Read.All</span> — Admin-Rollen</li>
        <li><span class="perm-tag">IdentityRiskyUser.Read.All</span> — Risiko-Anmeldungen</li>
        <li><span class="perm-tag">SecurityEvents.Read.All</span> — Secure Score, Defender</li>
        <li><span class="perm-tag">SecurityAlert.ReadWrite.All</span> — Defender Alerts auflösen</li>
    </ul>

    <h3>Zusätzliche Schreib-Berechtigungen</h3>
    <ul>
        <li><span class="perm-tag">User.ReadWrite.All</span> — Benutzer bearbeiten, deaktivieren, Offboarding</li>
        <li><span class="perm-tag">UserAuthenticationMethod.ReadWrite.All</span> — MFA zurücksetzen</li>
        <li><span class="perm-tag">Group.ReadWrite.All</span> + <span class="perm-tag">GroupMember.ReadWrite.All</span> — Gruppen verwalten</li>
        <li><span class="perm-tag">Application.ReadWrite.All</span> — App-Secrets verwalten</li>
        <li><span class="perm-tag">Files.ReadWrite.All</span> — Freigaben widerrufen</li>
        <li><span class="perm-tag">MailboxSettings.ReadWrite</span> — Postfach-Einstellungen (Weiterleitung, AutoReply)</li>
        <li><span class="perm-tag">Policy.ReadWrite.ConditionalAccess</span> — CA-Richtlinien umschalten</li>
        <li><span class="perm-tag">RoleManagement.ReadWrite.Directory</span> — Admin-Rollen zuweisen</li>
        <li><span class="perm-tag">IdentityRiskyUser.ReadWrite.All</span> — Risikobenutzer bestätigen/verwerfen</li>
        <li><span class="perm-tag">DeviceManagementManagedDevices.PrivilegedOperations.All</span> — Intune Wipe/Retire</li>
        <li><span class="perm-tag">Sites.ReadWrite.All</span> — Freigaberichtlinien ändern</li>
    </ul>

    <div class="tip-box"><i class="bi bi-lightbulb"></i>Unter <strong>Einstellungen → Berechtigungen prüfen</strong> siehst du immer, welche Berechtigungen aktuell erteilt sind und welche Module dadurch eingeschränkt sind.</div>
</div>

</div><!-- /manual-body -->
</div><!-- /manual-wrap -->
