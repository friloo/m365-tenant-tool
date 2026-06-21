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
        <h6><?= te('Einstieg') ?></h6>
        <a href="#intro"><?= te('Einführung') ?></a>
        <a href="#navigation"><?= te('Navigation') ?></a>
        <a href="#dashboard">Dashboard</a>

        <h6><?= te('Verzeichnis') ?></h6>
        <a href="#users"><?= te('Benutzer') ?></a>
        <a class="sub" href="#users-actions"><?= te('Aktionen') ?></a>
        <a class="sub" href="#users-offboarding">Offboarding</a>
        <a href="#guestusers"><?= te('Gastbenutzer') ?></a>
        <a href="#groups"><?= te('Gruppen & Teams') ?></a>
        <a href="#licenses"><?= te('Lizenzen') ?></a>
        <a href="#licenseadvisor"><?= te('Lizenz-Berater') ?></a>
        <a href="#mfa"><?= te('MFA-Methoden') ?></a>
        <a href="#passwordexpiry"><?= te('Passwort-Ablauf') ?></a>

        <h6><?= te('Speicher & Freigaben') ?></h6>
        <a href="#onedrive">OneDrive</a>
        <a href="#sharepoint">SharePoint</a>
        <a href="#sharing"><?= te('Freigaben') ?></a>
        <a href="#sharing-monitor"><?= te('Freigaben-Monitor') ?></a>
        <a href="#sharing-policies"><?= te('Freigaberichtlinien') ?></a>

        <h6><?= te('Exchange & Komm.') ?></h6>
        <a href="#mailboxes"><?= te('Postfächer') ?></a>
        <a href="#teamsusage"><?= te('Teams-Nutzung') ?></a>
        <a href="#adoption"><?= te('Adoptions-Report') ?></a>
        <a href="#msgcenter">Message Center</a>
        <a href="#mailflow"><?= te('Mail Flow & Schutz') ?></a>
        <a href="#servicehealth"><?= te('Dienststatus') ?></a>

        <h6><?= te('Sicherheit') ?></h6>
        <a href="#security"><?= te('Sicherheit (CA)') ?></a>
        <a href="#securityposture">Security Posture</a>
        <a href="#dsgvo"><?= te('DSGVO-Status') ?></a>
        <a href="#hardening"><?= te('Tenant-Härtung') ?></a>
        <a href="#securescore">Secure Score</a>
        <a href="#defender">Defender Alerts</a>
        <a href="#riskysignins"><?= te('Risiko-Anmeldungen') ?></a>
        <a href="#appregistrations"><?= te('App-Registrierungen') ?></a>
        <a href="#adminroles"><?= te('Admin-Rollen') ?></a>

        <h6><?= te('Erweitertes Hardening') ?></h6>
        <a href="#pim"><?= te('PIM (JIT-Admin)') ?></a>
        <a href="#breakglass"><?= te('Break-Glass-Accounts') ?></a>
        <a href="#mailboxrules"><?= te('Auto-Forward-Audit') ?></a>
        <a href="#oauthaudit"><?= te('OAuth-App-Audit') ?></a>
        <a href="#dlpincidents"><?= te('DLP-Vorfälle') ?></a>
        <a href="#authstrength"><?= te('Auth-Strength') ?></a>
        <a href="#backup"><?= te('Backup-Status') ?></a>
        <a href="#executivereport"><?= te('Executive-Report') ?></a>
        <a href="#mfafatigue"><?= te('MFA-Fatigue-Erkennung') ?></a>
        <a href="#insiderthreat"><?= te('Insider-Threat-Detection') ?></a>
        <a href="#crosstenantaccess"><?= te('Cross-Tenant-Access') ?></a>
        <a href="#tokenlifetime"><?= te('Token-Lifetime') ?></a>
        <a href="#lifecycle"><?= te('Lifecycle Workflows') ?></a>
        <a href="#phishingsim"><?= te('Phishing-Simulationen') ?></a>
        <a class="sub" href="#phishing-anleitung"><?= te('→ Anleitung Phishing-Sim') ?></a>
        <a href="#identityproviders"><?= te('Identity Provider Trust') ?></a>
        <a href="#customerlockbox">Customer Lockbox</a>

        <h6><?= te('KI & Reports') ?></h6>
        <a href="#ai"><?= te('KI-Sicherheitsberater') ?></a>

        <h6><?= te('Compliance & Audit') ?></h6>
        <a href="#devices"><?= te('Geräte') ?></a>
        <a href="#staleaccounts"><?= te('Inaktive Konten') ?></a>
        <a href="#auditlog"><?= te('Audit-Log') ?></a>
        <a href="#signinlog"><?= te('Sign-in-Log') ?></a>

        <h6><?= te('Compliance & Audit (erweitert)') ?></h6>
        <a href="#auditdiff"><?= te('Audit-Diff') ?></a>
        <a href="#auditreport"><?= te('DSGVO/NIS-2 Report') ?></a>
        <a href="#complianceprofiles"><?= te('Compliance-Profile') ?></a>

        <h6><?= te('Erweiterungen') ?></h6>
        <a href="#setupwizard"><?= te('Einrichtungs-Assistent') ?></a>
        <a href="#workflows"><?= te('Workflow-Automatisierung') ?></a>
        <a href="#notifications"><?= te('In-App-Benachrichtigungen') ?></a>
        <a href="#kpisparklines"><?= te('KPI-Sparklines') ?></a>
        <a href="#onlinehelp"><?= te('Online-Hilfe') ?></a>
        <a href="#restapi"><?= te('REST-API & Swagger') ?></a>

        <h6><?= te('Administration') ?></h6>
        <a href="#cron"><?= te('Cron & Automatisierung') ?></a>
        <a href="#settings"><?= te('Einstellungen') ?></a>
        <a href="#useraccess"><?= te('Benutzer-Zugang') ?></a>
        <a href="#updates">Updates</a>
        <a href="#permissions"><?= te('Berechtigungen') ?></a>
    </div>
</aside>

<!-- ── Manual Body ───────────────────────────────────────── -->
<div class="manual-body">

<!-- Einführung ───────────────────────────────────────────── -->
<div class="man-section" id="intro">
    <h2><i class="bi bi-book text-primary"></i> <?= te('Einführung') ?></h2>
    <p><?= te('Das <strong>M365 Tenant Tool</strong> ist ein webbasiertes Administrator-Dashboard für Microsoft 365. Es ermöglicht die zentrale Verwaltung und Überwachung eines M365-Tenants direkt über den Browser — ohne Microsoft Entra Admin Center oder PowerShell.') ?></p>
    <p><?= te('Die Verbindung zu Microsoft Graph erfolgt über den <strong>OAuth 2.0 Client-Credentials-Flow</strong> (Anwendungs-Berechtigungen). Das Werkzeug benötigt keinen Benutzer-Login bei Microsoft — ein einmalig eingerichtetes App-Konto in Azure AD (Entra ID) reicht aus.') ?></p>

    <h3><?= te('Voraussetzungen') ?></h3>
    <ul>
        <li>PHP 8.2+, Apache/Nginx, MySQL/MariaDB</li>
        <li><?= te('Eine App-Registrierung in Microsoft Entra ID mit den erforderlichen Anwendungsberechtigungen') ?></li>
        <li><?= te('Administrator-Zustimmung (Admin Consent) für alle API-Berechtigungen') ?></li>
        <li><?= te('Die App muss über einen Browser erreichbar sein (lokales Netzwerk oder Internet)') ?></li>
    </ul>

    <h3><?= te('Rollen im Tool') ?></h3>
    <ul>
        <li><strong>Administrator</strong> — <?= te('vollständiger Zugriff inkl. Einstellungen, Updates, Offboarding, Wipe') ?></li>
        <li><strong>Operator</strong> — <?= te('alle Monitoring-Module lesen, Scans starten, Erinnerungen senden, Freigaben widerrufen; kein Zugriff auf Einstellungen/Updates') ?></li>
    </ul>
</div>

<!-- Navigation ───────────────────────────────────────────── -->
<div class="man-section" id="navigation">
    <h2><i class="bi bi-layout-sidebar text-primary"></i> <?= te('Navigation & Bedienung') ?></h2>

    <h3>Sidebar</h3>
    <p><?= te('Die linke Seitenleiste ist in thematische Bereiche unterteilt. Per Klick auf den') ?> <i class="bi bi-list"></i><?= te('-Button oben links kann sie ein- und ausgeklappt werden. Operator-Accounts sehen den Administrationsbereich nicht.') ?></p>

    <h3><?= te('Schnellsuche (Strg+K)') ?></h3>
    <p><?= te('Mit <kbd>Strg</kbd>+<kbd>K</kbd> (oder dem Lupensymbol in der Topbar) öffnet sich die Kommandopalette. Dort können alle Seiten des Tools per Tastatur gesucht und direkt angesprungen werden.') ?></p>

    <h3><?= te('Daten aktualisieren') ?></h3>
    <p><?= te('Alle Daten werden aus der Microsoft Graph API geladen und serverseitig gecacht (Standard: 15 Minuten). Mit dem') ?> <i class="bi bi-arrow-clockwise"></i><?= te('-Button in der Topbar (hängt <code>?refresh=1</code> an die URL) wird der Cache für die aktuelle Seite geleert und ein neuer Abruf gestartet.') ?></p>

    <h3><?= te('Suche in Tabellen') ?></h3>
    <p><?= te('Auf den meisten Listenansichten gibt es ein Suchfeld über der Tabelle. Die Suche filtert live alle sichtbaren Zeilen nach dem eingegebenen Begriff.') ?></p>

    <h3>CSV-Export</h3>
    <p><?= te('Viele Module bieten eine Export-Schaltfläche, die die aktuell geladenen Daten als CSV-Datei herunterlädt.') ?></p>
</div>

<!-- Dashboard ────────────────────────────────────────────── -->
<div class="man-section" id="dashboard">
    <h2><i class="bi bi-speedometer2 text-primary"></i> Dashboard</h2>
    <p><?= te('Das Dashboard gibt auf einen Blick einen Überblick über den M365-Tenant:') ?></p>
    <ul>
        <li><?= te('Anzahl Benutzer, aktive/inaktive Konten, Gastbenutzer') ?></li>
        <li><?= te('Lizenzübersicht: verbrauchte vs. verfügbare Plätze') ?></li>
        <li><?= te('Sicherheitsampel: MFA-Abdeckung, Risiko-Anmeldungen, Defender-Alerts') ?></li>
        <li><?= te('Dienststatus: aktuelle Vorfälle in Microsoft 365-Diensten') ?></li>
        <li><?= te('Schnellzugriff zu den wichtigsten Modulen') ?></li>
    </ul>
    <div class="tip-box"><i class="bi bi-lightbulb"></i><?= te('Beim ersten Aufruf werden alle Daten frisch aus der API geholt und gecacht. Je nach Tenant-Größe kann dies einige Sekunden dauern.') ?></div>
    <p><span class="perm-tag">User.Read.All</span> <span class="perm-tag">Organization.Read.All</span> <span class="perm-tag">Reports.Read.All</span></p>
</div>

<!-- Benutzer ─────────────────────────────────────────────── -->
<div class="man-section" id="users">
    <h2><i class="bi bi-people text-primary"></i> <?= te('Benutzer') ?></h2>
    <p><?= te('Zeigt alle Benutzer des Tenants in einer durchsuchbaren und sortierbaren Tabelle. Spalten: Anzeigename, E-Mail, Status (aktiv/deaktiviert), Abteilung, Stelle, Lizenzen.') ?></p>

    <h3 id="users-actions"><?= te('Aktionen auf der Detailseite') ?></h3>
    <p><?= te('Per Klick auf einen Benutzernamen öffnet sich die Detailseite mit:') ?></p>

    <h4><?= te('Konto aktivieren / deaktivieren') ?></h4>
    <p><?= te('Schaltet <code>accountEnabled</code> in Azure AD um. Deaktivierte Benutzer können sich nicht mehr anmelden, ihr Postfach und ihre Daten bleiben erhalten.') ?></p>

    <h4><?= te('MFA zurücksetzen') ?></h4>
    <p><?= te('Löscht alle registrierten Authentifizierungsmethoden des Benutzers (Authenticator-App, Telefonnummern, FIDO2-Schlüssel) — außer dem Passwort. Der Benutzer muss beim nächsten Login eine neue Methode registrieren.') ?></p>
    <div class="warn-box"><i class="bi bi-exclamation-triangle"></i><strong><?= te('Achtung:') ?></strong> <?= te('Diese Aktion kann nicht rückgängig gemacht werden. Der Benutzer muss anschließend den MFA-Registrierungsprozess erneut durchlaufen.') ?></div>

    <h4><?= te('Sign-in-Sessions widerrufen') ?></h4>
    <p><?= te('Invalidiert alle aktiven Anmeldesitzungen des Benutzers (Refresh-Token-Revocation). Alle Geräte werden beim nächsten Aufruf zur Neuanmeldung aufgefordert.') ?></p>

    <h4><?= te('Lizenz zuweisen / entfernen') ?></h4>
    <p><?= te('Weist dem Benutzer eine SKU direkt zu oder entfernt sie. Die Lizenz muss im Tenant verfügbar sein (freie Plätze vorhanden).') ?></p>

    <h4><?= te('Benutzer bearbeiten') ?></h4>
    <p><?= te('Ändert Profilfelder wie Anzeigename, Stellenbezeichnung, Abteilung, Telefon und Nutzungsstandort direkt über die Graph API.') ?></p>

    <h4><?= te('Anmeldeverlauf') ?></h4>
    <p><?= te('Zeigt die letzten 25 Anmeldeereignisse des Benutzers mit App, IP-Adresse, Standort, Status und Risikoinformationen.') ?></p>
    <div class="info-box"><i class="bi bi-info-circle"></i><?= te('Für den Anmeldeverlauf wird') ?> <span class="perm-tag">AuditLog.Read.All</span> <?= te('benötigt.') ?></div>

    <h4><?= te('Gruppenmitgliedschaften') ?></h4>
    <p><?= te('Listet alle Gruppen, in denen der Benutzer Mitglied ist.') ?></p>

    <h3 id="users-offboarding"><?= te('Offboarding-Assistent') ?></h3>
    <p><?= te('Der Offboarding-Assistent führt in einem Schritt mehrere Aktionen gleichzeitig durch:') ?></p>
    <ul>
        <li><?= te('Konto deaktivieren') ?></li>
        <li><?= te('Sign-in-Sessions widerrufen') ?></li>
        <li><?= te('MFA-Methoden zurücksetzen') ?></li>
        <li><?= te('Alle Lizenzen entfernen') ?></li>
        <li><?= te('Aus allen Gruppen entfernen (außer On-Premises-synchronisierte Gruppen)') ?></li>
    </ul>
    <div class="warn-box"><i class="bi bi-exclamation-triangle"></i><?= te('Beim Offboarding werden die Gruppen-Mitgliedschaften dauerhaft entfernt. Diese müssen bei Bedarf manuell wiederhergestellt werden.') ?></div>
    <p><span class="perm-tag">User.Read.All</span> <span class="perm-tag">User.ReadWrite.All</span> <span class="perm-tag">UserAuthenticationMethod.ReadWrite.All</span> <span class="perm-tag">AuditLog.Read.All</span></p>
</div>

<!-- Gastbenutzer ─────────────────────────────────────────── -->
<div class="man-section" id="guestusers">
    <h2><i class="bi bi-person-badge text-primary"></i> <?= te('Gastbenutzer') ?></h2>
    <p><?= te('Listet alle Gastbenutzer (<code>userType = Guest</code>) im Tenant. Gastbenutzer werden typischerweise für externe Kollaborationen (SharePoint, Teams) eingeladen.') ?></p>
    <p><?= te('In der Tabelle sind sichtbar: E-Mail, Einladungsstatus, letzte Anmeldung, Lizenzen, Erstelldatum.') ?></p>

    <h3><?= te('Aktionen') ?></h3>
    <ul>
        <li><strong><?= te('Deaktivieren') ?></strong> — <?= te('setzt <code>accountEnabled = false</code>, der Gast kann sich nicht mehr anmelden') ?></li>
        <li><strong><?= te('Entfernen') ?></strong> — <?= te('löscht das Gastkonto vollständig aus dem Tenant') ?></li>
    </ul>
    <div class="tip-box"><i class="bi bi-lightbulb"></i><?= te('Regelmäßige Bereinigung alter Gastkonten ist eine wichtige Sicherheitsmaßnahme. Das Modul „Inaktive Konten" hilft dabei, Gäste zu identifizieren, die sich lange nicht mehr angemeldet haben.') ?></div>
    <p><span class="perm-tag">User.Read.All</span> <span class="perm-tag">User.ReadWrite.All</span> <span class="perm-tag">AuditLog.Read.All</span></p>
</div>

<!-- Gruppen ──────────────────────────────────────────────── -->
<div class="man-section" id="groups">
    <h2><i class="bi bi-diagram-3 text-primary"></i> <?= te('Gruppen & Teams') ?></h2>
    <p><?= te('Zeigt alle Gruppen im Tenant: Microsoft 365-Gruppen, Sicherheitsgruppen und E-Mail-aktivierte Gruppen. Teams-aktivierte Gruppen sind entsprechend markiert.') ?></p>

    <h3><?= te('Gruppendetails') ?></h3>
    <p><?= te('Per Klick auf eine Gruppe öffnet sich die Detailansicht mit Mitgliedern, Eigentümern und Gruppentyp.') ?></p>

    <h3><?= te('Mitglieder & Eigentümer') ?></h3>
    <p><?= te('Mitglieder und Eigentümer können direkt hinzugefügt oder entfernt werden. Bei On-Premises-synchronisierten Gruppen ist dies nicht möglich (Änderungen müssen im lokalen Active Directory erfolgen).') ?></p>

    <h3><?= te('Gruppe erstellen') ?></h3>
    <p><?= te('Erstellt eine neue Microsoft 365-Gruppe oder Sicherheitsgruppe direkt im Tenant.') ?></p>

    <h3><?= te('Gruppe löschen') ?></h3>
    <p><?= te('Löscht die Gruppe dauerhaft. Bei Microsoft 365-Gruppen werden auch das zugehörige Team, das Postfach und die SharePoint-Site gelöscht (Soft-Delete, 30 Tage wiederherstellbar im Entra Portal).') ?></p>

    <h3><?= te('Inaktive Gruppen') ?></h3>
    <p><?= te('Unter <strong>Gruppen → Inaktive Gruppen</strong> werden Microsoft 365-Gruppen angezeigt, die seit mehr als 90 Tagen keine Aktivität hatten. Grundlage ist die letzte Aktivität der zugehörigen SharePoint-Site.') ?></p>
    <p><span class="perm-tag">Group.Read.All</span> <span class="perm-tag">Group.ReadWrite.All</span> <span class="perm-tag">GroupMember.ReadWrite.All</span></p>
</div>

<!-- Lizenzen ─────────────────────────────────────────────── -->
<div class="man-section" id="licenses">
    <h2><i class="bi bi-award text-primary"></i> <?= te('Lizenzen') ?></h2>
    <p><?= te('Zeigt alle abonnierten Lizenz-SKUs im Tenant mit verbrauchten und verfügbaren Plätzen sowie den enthaltenen Service-Plänen.') ?></p>

    <h3><?= te('Ablaufende Lizenzen') ?></h3>
    <p><?= te('Unter <strong>Lizenzen → Ablauf</strong> werden alle Abonnements angezeigt, die in den nächsten 90 Tagen ablaufen. Dies ermöglicht eine frühzeitige Verlängerungsplanung.') ?></p>

    <div class="tip-box"><i class="bi bi-lightbulb"></i><?= te('Der CSV-Export enthält alle Lizenzen mit Ablaufdatum, Verbrauch und Kosten-relevanten Infos — nützlich für Budgetplanung.') ?></div>
    <p><span class="perm-tag">Organization.Read.All</span></p>
</div>

<!-- Lizenz-Berater ───────────────────────────────────────── -->
<div class="man-section" id="licenseadvisor">
    <h2><i class="bi bi-lightbulb text-primary"></i> <?= te('Lizenz-Berater') ?></h2>
    <p><?= te('Analysiert die Lizenznutzung und identifiziert Benutzer, die lizenzierte Dienste nicht aktiv nutzen (potenzielle Einsparungen).') ?></p>
    <p><?= te('In den Einstellungen können Kriterien konfiguriert werden, welche Dienste für eine „sinnvolle" Lizenz als nötig gelten (Exchange Online, Teams, SharePoint, OneDrive, Office Desktop, Intune).') ?></p>
    <p><?= te('Das Modul zeigt dann Benutzer, die lizenziert sind, aber mindestens einen der konfigurierten Dienste nicht nutzen — mit der Möglichkeit zum CSV-Export für die Entscheidungsfindung.') ?></p>
    <p><span class="perm-tag">User.Read.All</span> <span class="perm-tag">Reports.Read.All</span> <span class="perm-tag">AuditLog.Read.All</span></p>
</div>

<!-- MFA-Methoden ─────────────────────────────────────────── -->
<div class="man-section" id="mfa">
    <h2><i class="bi bi-shield-lock text-primary"></i> <?= te('MFA-Methoden') ?></h2>
    <p><?= te('Zeigt für jeden Benutzer, welche Authentifizierungsmethoden registriert sind: Microsoft Authenticator, SMS/Anruf, FIDO2-Schlüssel, softwarebasierter TOTP-Token u.a.') ?></p>
    <p><?= te('Benutzer ohne MFA-Registrierung sind deutlich markiert und können gefiltert angezeigt werden. Der CSV-Export eignet sich für Compliance-Berichte.') ?></p>
    <div class="tip-box"><i class="bi bi-lightbulb"></i><?= te('Verwende den Filter „Kein MFA", um schnell alle Konten ohne zweiten Faktor zu identifizieren, und leite dann entsprechende Maßnahmen ein.') ?></div>
    <p><span class="perm-tag">UserAuthenticationMethod.Read.All</span></p>
</div>

<!-- Passwort-Ablauf ──────────────────────────────────────── -->
<div class="man-section" id="passwordexpiry">
    <h2><i class="bi bi-key text-primary"></i> <?= te('Passwort-Ablauf') ?></h2>
    <p><?= te('Zeigt Benutzer, deren Passwort abgelaufen ist oder bald abläuft — basierend auf der konfigurierten Passwort-Ablauf-Richtlinie (Standard: 90 Tage).') ?></p>
    <p><?= te('Die Ansicht ist in Kategorien unterteilt: Abgelaufen, Kritisch (weniger als 7 Tage), Warnung (weniger als 30 Tage) und Alle.') ?></p>
    <p><?= te('Benutzer mit aktiviertem „Passwort läuft nie ab" werden entsprechend markiert.') ?></p>
    <p><span class="perm-tag">User.Read.All</span></p>
</div>

<!-- OneDrive ─────────────────────────────────────────────── -->
<div class="man-section" id="onedrive">
    <h2><i class="bi bi-cloud text-primary"></i> OneDrive</h2>
    <p><?= te('Zeigt die OneDrive-Nutzung aller Benutzer: verwendeter und verfügbarer Speicher, Anzahl der Dateien, letzte Aktivität.') ?></p>
    <p><?= te('Der Bericht basiert auf dem Microsoft 365 Nutzungsbericht (<code>/reports/oneDriveUsageAccountDetail</code>) und wird täglich von Microsoft aktualisiert — die Daten können daher bis zu 48 Stunden alt sein.') ?></p>
    <p><span class="perm-tag">Reports.Read.All</span></p>
</div>

<!-- SharePoint ───────────────────────────────────────────── -->
<div class="man-section" id="sharepoint">
    <h2><i class="bi bi-share text-primary"></i> SharePoint</h2>
    <p><?= te('Listet alle SharePoint-Sites im Tenant mit URL, Typ (Kommunikationssite / Teamsite), Speichernutzung und letzter Aktivität.') ?></p>
    <p><?= te('Per Klick auf eine Site öffnet sich die Detailansicht mit den zugehörigen Dokumentbibliotheken.') ?></p>
    <p><span class="perm-tag">Sites.Read.All</span></p>
</div>

<!-- Freigaben ────────────────────────────────────────────── -->
<div class="man-section" id="sharing">
    <h2><i class="bi bi-link-45deg text-primary"></i> <?= te('Freigaben') ?></h2>
    <p><?= te('Zeigt alle externen Freigaben (Sharing-Links) im Tenant — Dateien und Ordner, die per Link nach außen geteilt wurden.') ?></p>
    <p><?= te('Für jede Freigabe sind sichtbar: Dateiname, SharePoint-Site, Freigabe-Typ (Anonym, Org, Spezifisch), Ersteller, Ablaufdatum.') ?></p>

    <h3><?= te('Freigabe widerrufen') ?></h3>
    <p><?= te('Einzelne Freigabe-Links können direkt aus der Tabelle heraus widerrufen werden. Dies entfernt den Link, die Datei bleibt erhalten.') ?></p>
    <div class="warn-box"><i class="bi bi-exclamation-triangle"></i><?= te('Das Widerrufen einer anonymen Freigabe deaktiviert den Link sofort — alle Personen, die über diesen Link zugegriffen haben, verlieren den Zugriff.') ?></div>
    <p><span class="perm-tag">Sites.Read.All</span> <span class="perm-tag">Files.ReadWrite.All</span></p>
</div>

<!-- Freigaben-Monitor ────────────────────────────────────── -->
<div class="man-section" id="sharing-monitor">
    <h2><i class="bi bi-eye-slash text-primary"></i> <?= te('Freigaben-Monitor') ?></h2>
    <p><?= te('Der Freigaben-Monitor ermöglicht es, Benutzer regelmäßig per E-Mail zu ihren externen Freigaben zu befragen — sie können direkt aus der E-Mail heraus Freigaben bestätigen oder widerrufen.') ?></p>

    <h3><?= te('Wie es funktioniert') ?></h3>
    <ol>
        <li><?= te('Der Cron-Job scannt täglich alle externen Freigaben') ?></li>
        <li><?= te('Benutzer erhalten eine E-Mail mit einer Liste ihrer aktiven Freigaben') ?></li>
        <li><?= te('Sie können jede Freigabe per Klick bestätigen oder widerrufen') ?></li>
        <li><?= te('Nicht reagierte Freigaben werden nach dem konfigurierten Zeitraum automatisch widerrufen') ?></li>
    </ol>

    <h3><?= te('Konfiguration') ?></h3>
    <p><?= te('Im Einstellungsbereich (<strong>Freigaben-Monitor</strong>): Review-Intervall (Standard: 30 Tage), Kulanzfrist (Standard: 7 Tage), nur anonyme Freigaben prüfen.') ?></p>

    <h3><?= te('Admin-Ansicht') ?></h3>
    <p><?= te('Die Admin-Ansicht zeigt alle aktiven Review-Anfragen mit Status. Von hier aus können Freigaben auch manuell widerrufen oder Erinnerungs-E-Mails versendet werden.') ?></p>
    <p><span class="perm-tag">Sites.Read.All</span> <span class="perm-tag">Files.ReadWrite.All</span></p>
</div>

<!-- Freigaberichtlinien ──────────────────────────────────── -->
<div class="man-section" id="sharing-policies">
    <h2><i class="bi bi-sliders text-primary"></i> <?= te('Freigaberichtlinien') ?></h2>
    <p><?= te('Verwaltet die tenant-weiten Freigabeeinstellungen für SharePoint und OneDrive:') ?></p>
    <ul>
        <li><strong><?= te('Tenant-Ebene') ?></strong>: <?= te('Maximale erlaubte Freigabe-Stufe (Anonym, Org, Org+Anonym deaktiviert, Nur eingeladene Benutzer)') ?></li>
        <li><strong><?= te('Site-Ebene') ?></strong>: <?= te('Freigabe-Einstellung für eine einzelne SharePoint-Site (kann restriktiver sein als die Tenant-Einstellung, aber nicht freizügiger)') ?></li>
    </ul>
    <div class="tip-box"><i class="bi bi-lightbulb"></i><?= te('Als Best Practice empfiehlt sich die Tenant-Einstellung auf „Nur authentifizierte Benutzer" und für einzelne Sites bei Bedarf spezifisch zu lockern.') ?></div>
    <p><span class="perm-tag">Sites.ReadWrite.All</span></p>
</div>

<!-- Postfächer ───────────────────────────────────────────── -->
<div class="man-section" id="mailboxes">
    <h2><i class="bi bi-envelope text-primary"></i> <?= te('Postfächer') ?></h2>
    <p><?= te('Zeigt alle Exchange Online-Postfächer (Benutzer, Shared, Room, Equipment) mit Größe, Quota, letzter Aktivität und Weiterleitungseinstellungen.') ?></p>

    <h3><?= te('Postfach-Detailseite') ?></h3>
    <ul>
        <li><strong><?= te('Weiterleitung einrichten') ?></strong>: <?= te('Konfiguriert <code>ForwardingSmtpAddress</code> — E-Mails werden an eine externe oder interne Adresse weitergeleitet (optional: Kopie im Postfach behalten)') ?></li>
        <li><strong><?= te('Abwesenheitsnachricht') ?></strong>: <?= te('Setzt die AutoReply-Konfiguration für intern und extern') ?></li>
    </ul>

    <h3><?= te('Externe Weiterleitungen') ?></h3>
    <p><?= te('Unter <strong>Postfächer → Externe Weiterleitungen</strong> werden alle Postfächer angezeigt, die E-Mails an externe Adressen weiterleiten. Dies ist ein wichtiger Sicherheitscheck gegen Mail-Exfiltration.') ?></p>
    <p><?= te('Von dieser Ansicht aus können Weiterleitungen direkt deaktiviert werden.') ?></p>

    <h3>Shared Mailboxes</h3>
    <p><?= te('Listet alle freigegebenen Postfächer mit Mitglieder-Übersicht. Neue Shared Mailboxes können direkt erstellt werden.') ?></p>
    <div class="tip-box"><i class="bi bi-lightbulb"></i><?= te('Shared Mailboxes bis 50 GB benötigen in der Regel keine separate Lizenz — sie werden automatisch mit Exchange Online-Postfach-Merkmalen versehen.') ?></div>
    <p><span class="perm-tag">Mail.ReadBasic.All</span> <span class="perm-tag">MailboxSettings.ReadWrite</span></p>
</div>

<!-- Teams-Nutzung ────────────────────────────────────────── -->
<div class="man-section" id="teamsusage">
    <h2><i class="bi bi-camera-video text-primary"></i> <?= te('Teams-Nutzung') ?></h2>
    <p><?= te('Zeigt die Teams-Aktivität aller Benutzer: Nachrichten gesendet, Anrufe, Meetings, Reaktionen — basierend auf den Microsoft 365-Nutzungsberichten der letzten 30 Tage.') ?></p>
    <p><?= te('Nützlich um zu erkennen, welche Benutzer Teams kaum nutzen (und ob eine Teams-Lizenz gerechtfertigt ist).') ?></p>
    <p><span class="perm-tag">Reports.Read.All</span></p>
</div>

<!-- Adoptions-Report ─────────────────────────────────────── -->
<div class="man-section" id="adoption">
    <h2><i class="bi bi-graph-up-arrow text-primary"></i> <?= te('Adoptions-Report') ?></h2>
    <p><?= te('Gibt einen aggregierten Überblick über die Nutzung der M365-Dienste im Tenant: aktive Benutzer je Dienst (Exchange, SharePoint, OneDrive, Teams, Yammer) über verschiedene Zeiträume (7, 30, 90, 180 Tage).') ?></p>
    <p><?= te('Enthält Diagramme zur zeitlichen Entwicklung der Nutzungszahlen.') ?></p>
    <p><span class="perm-tag">Reports.Read.All</span></p>
</div>

<!-- Message Center ───────────────────────────────────────── -->
<div class="man-section" id="msgcenter">
    <h2><i class="bi bi-megaphone text-primary"></i> Message Center</h2>
    <p><?= te('Zeigt die aktuellen Nachrichten aus dem Microsoft 365 Message Center — Ankündigungen zu geplanten Änderungen, neuen Features und Wartungsarbeiten.') ?></p>
    <p><?= te('Nachrichten sind nach Kategorie und Schweregrad gefiltert darstellbar. Wichtige Änderungen, die Administrative Maßnahmen erfordern, sind hervorgehoben.') ?></p>
    <p><span class="perm-tag">ServiceMessage.Read.All</span></p>
</div>

<!-- Mail Flow & Schutz ───────────────────────────────────── -->
<div class="man-section" id="mailflow">
    <h2><i class="bi bi-arrow-left-right text-primary"></i> <?= te('Mail Flow & Schutz') ?></h2>
    <p><?= te('Fasst die wichtigsten Exchange Online Sicherheits- und Mail-Flow-Konfigurationen zusammen:') ?></p>
    <ul>
        <li><strong><?= te('Anti-Spam-Richtlinien') ?></strong>: <?= te('Konfiguration der Spam-Filter-Einstellungen') ?></li>
        <li><strong><?= te('Anti-Malware-Richtlinien') ?></strong>: <?= te('Malware-Erkennungseinstellungen') ?></li>
        <li><strong><?= te('Anti-Phishing-Richtlinien') ?></strong>: <?= te('Schutz vor Phishing und Spoofing') ?></li>
        <li><strong>Safe Attachments / Safe Links</strong>: <?= te('Defender for Office 365-Richtlinien (wenn lizenziert)') ?></li>
        <li><strong>Connectors</strong>: <?= te('Eingehende und ausgehende Mail-Flow-Konnektoren') ?></li>
        <li><strong><?= te('Transport-Regeln') ?></strong>: <?= te('Übersicht über aktive Mail-Flow-Regeln') ?></li>
    </ul>
    <p><span class="perm-tag">Mail.ReadBasic.All</span></p>
</div>

<!-- Dienststatus ─────────────────────────────────────────── -->
<div class="man-section" id="servicehealth">
    <h2><i class="bi bi-heart-pulse text-primary"></i> <?= te('Dienststatus') ?></h2>
    <p><?= te('Zeigt den aktuellen Status aller Microsoft 365-Dienste (Exchange, SharePoint, Teams, Entra ID, Intune usw.).') ?></p>
    <p><?= te('Aktive Vorfälle und Wartungsfenster werden mit Details zum Fortschritt und der voraussichtlichen Behebungszeit angezeigt. Vergangene Vorfälle der letzten 7 Tage sind in einer separaten Liste sichtbar.') ?></p>
    <p><span class="perm-tag">ServiceMessage.Read.All</span></p>
</div>

<!-- Sicherheit (CA) ──────────────────────────────────────── -->
<div class="man-section" id="security">
    <h2><i class="bi bi-shield-check text-primary"></i> <?= te('Sicherheit (Conditional Access)') ?></h2>
    <p><?= te('Listet alle Richtlinien für bedingten Zugriff (Conditional Access Policies) im Tenant mit Status (aktiviert, deaktiviert, Berichtsmodus), Bedingungen und Zugriffsteuerungen.') ?></p>

    <h3><?= te('Richtlinien ein-/ausschalten') ?></h3>
    <p><?= te('CA-Richtlinien können direkt im Tool aktiviert oder deaktiviert werden (Berichtsmodus ist ebenfalls möglich). Dies erfordert erhöhte Berechtigungen.') ?></p>
    <div class="warn-box"><i class="bi bi-exclamation-triangle"></i><strong><?= te('Vorsicht:') ?></strong> <?= te('Das Deaktivieren einer aktiven CA-Richtlinie kann die Sicherheit des Tenants erheblich reduzieren. Änderungen sollten sorgfältig geplant werden.') ?></div>
    <p><span class="perm-tag">Policy.Read.All</span> <span class="perm-tag">Policy.ReadWrite.ConditionalAccess</span></p>
</div>

<!-- Security Posture ─────────────────────────────────────── -->
<div class="man-section" id="securityposture">
    <h2><i class="bi bi-shield-fill-check text-primary"></i> Security Posture</h2>
    <p><?= te('Gibt eine aggregierte Sicherheitsübersicht des Tenants: MFA-Abdeckung, Risiko-Benutzer, Geräte-Compliance, aktive CA-Richtlinien, externe Freigaben und weitere Sicherheitsindikatoren.') ?></p>
    <p><?= te('Jeder Indikator ist mit einer Ampel (grün/gelb/rot) bewertet und mit dem zugehörigen Modul verlinkt, um direkt handeln zu können.') ?></p>
    <p><span class="perm-tag">User.Read.All</span> <span class="perm-tag">Policy.Read.All</span> <span class="perm-tag">IdentityRiskyUser.Read.All</span></p>
</div>

<!-- Secure Score ─────────────────────────────────────────── -->
<div class="man-section" id="securescore">
    <h2><i class="bi bi-bar-chart-line text-primary"></i> Secure Score</h2>
    <p><?= te('Zeigt den Microsoft Secure Score des Tenants — eine Bewertung der Sicherheitskonfiguration auf einer Skala von 0–100 — sowie den Verlauf über die letzten 30 Tage.') ?></p>
    <p><?= te('Die einzelnen Verbesserungsmaßnahmen (Control Scores) werden mit ihrer Punktzahl und dem Implementierungsstatus aufgelistet. Direkt-Links ins Microsoft 365 Defender Portal ermöglichen die schnelle Umsetzung.') ?></p>
    <p><span class="perm-tag">SecurityEvents.Read.All</span></p>
</div>

<!-- Defender Alerts ──────────────────────────────────────── -->
<div class="man-section" id="defender">
    <h2><i class="bi bi-bell text-primary"></i> Defender Alerts</h2>
    <p><?= te('Zeigt offene Sicherheitswarnungen aus Microsoft Defender for Endpoint, Defender for Office 365 und Microsoft Sentinel.') ?></p>
    <p><?= te('Für jeden Alert sind sichtbar: Titel, Schweregrad (Hoch/Mittel/Niedrig/Informativ), betroffene Entität, Status und Erstellzeit.') ?></p>

    <h3><?= te('Alert auflösen') ?></h3>
    <p><?= te('Alerts können direkt im Tool als „Gelöst" markiert werden. Dies schließt den Alert in Microsoft Defender.') ?></p>
    <p><span class="perm-tag">SecurityAlert.ReadWrite.All</span></p>
</div>

<!-- Risiko-Anmeldungen ───────────────────────────────────── -->
<div class="man-section" id="riskysignins">
    <h2><i class="bi bi-exclamation-triangle text-primary"></i> <?= te('Risiko-Anmeldungen') ?></h2>
    <p><?= te('Zeigt Benutzer, denen Microsoft Entra ID Protection ein erhöhtes Anmelderisiko zugewiesen hat — z.B. durch ungewöhnliche Anmeldeorte, kompromittierte Zugangsdaten (Credential-Leak-Erkennung) oder anomales Verhalten.') ?></p>

    <h3><?= te('Aktionen') ?></h3>
    <ul>
        <li><strong><?= te('Als kompromittiert bestätigen') ?></strong>: <?= te('Markiert das Benutzerkonto als kompromittiert, erzwingt Passwort-Reset und blockiert laufende Sessions') ?></li>
        <li><strong><?= te('Risiko verwerfen') ?></strong>: <?= te('Verwirft den Risikohinweis (wenn es sich um ein False Positive handelt)') ?></li>
    </ul>
    <div class="info-box"><i class="bi bi-info-circle"></i><?= te('Dieses Modul erfordert Microsoft Entra ID P2 (oder Microsoft 365 E5) im Tenant.') ?></div>
    <p><span class="perm-tag">IdentityRiskyUser.Read.All</span> <span class="perm-tag">IdentityRiskyUser.ReadWrite.All</span></p>
</div>

<!-- App-Registrierungen ──────────────────────────────────── -->
<div class="man-section" id="appregistrations">
    <h2><i class="bi bi-grid-3x3-gap text-primary"></i> <?= te('App-Registrierungen & Enterprise Apps') ?></h2>
    <p><?= te('Zeigt alle App-Registrierungen und Enterprise-Anwendungen im Tenant mit ihren API-Berechtigungen, Client-Secrets und Zertifikaten.') ?></p>

    <h3><?= te('Client-Secrets verwalten') ?></h3>
    <p><?= te('Auf der Detailseite einer App-Registrierung können neue Client-Secrets erstellt und vorhandene gelöscht werden. Ablaufende Secrets sind farblich markiert.') ?></p>
    <div class="tip-box"><i class="bi bi-lightbulb"></i><?= te('Secrets, die in weniger als 30 Tagen ablaufen, werden orange markiert. Abgelaufene Secrets werden rot hervorgehoben. Prüfe regelmäßig, ob Produktivsysteme davon betroffen sind.') ?></div>
    <p><span class="perm-tag">Application.Read.All</span> <span class="perm-tag">Application.ReadWrite.All</span></p>
</div>

<!-- Admin-Rollen ─────────────────────────────────────────── -->
<div class="man-section" id="adminroles">
    <h2><i class="bi bi-person-lock text-primary"></i> <?= te('Admin-Rollen') ?></h2>
    <p><?= te('Zeigt alle Microsoft Entra-Administratorrollen und die jeweils zugewiesenen Benutzer. Privilegierte Rollen (Globaler Administrator, Privilegierter Rollenverwaltungsadministrator usw.) sind besonders hervorgehoben.') ?></p>

    <h3><?= te('Rollen zuweisen / entfernen') ?></h3>
    <p><?= te('Benutzer können direkt einer Rolle zugewiesen oder aus einer Rolle entfernt werden. Die Zuweisung erfolgt als permanente direkte Zuweisung (kein PIM).') ?></p>
    <div class="warn-box"><i class="bi bi-exclamation-triangle"></i><?= te('Administratorrollen-Änderungen sind sicherheitskritisch. Die Vergabe der Rolle „Globaler Administrator" sollte auf das absolute Minimum beschränkt werden.') ?></div>
    <p><span class="perm-tag">RoleManagement.Read.All</span> <span class="perm-tag">RoleManagement.ReadWrite.Directory</span></p>
</div>

<!-- ═══════════════════════════════════════════════════════════
     ERWEITERTES HARDENING
     8 Module für tiefe Sicherheits- und Compliance-Analysen
     ═══════════════════════════════════════════════════════════ -->

<!-- DSGVO-Status ─────────────────────────────────────────── -->
<div class="man-section" id="dsgvo">
    <h2><i class="bi bi-file-earmark-lock text-primary"></i> <?= te('DSGVO-Status') ?></h2>
    <p><?= te('Eigene Kategorie innerhalb der Security Posture mit acht spezifischen DSGVO-Checks. Pro Check stehen das geprüfte Tenant-Setting und die relevanten DSGVO-Artikel.') ?></p>
    <h3><?= te('Was geprüft wird') ?></h3>
    <ul>
        <li><strong><?= te('Tenant-Region in EU/EWR') ?></strong> — <?= te('der Datacenter-Standort entscheidet, ob die Verarbeitung primär unter Art. 6 (Rechtmäßigkeit) oder Art. 44–49 (Drittlandtransfer) fällt.') ?></li>
        <li><strong><?= te('SharePoint External Sharing restriktiv') ?></strong> — <?= te('die Tenant-Sharing-Capability darf für DSGVO-konforme Defaults nicht „Anyone-Links" als Default-Wert haben (Art. 25 Privacy by Default).') ?></li>
        <li><strong><?= te('Anonyme Freigabe-Links laufen ab') ?></strong> — <?= te('Speicherbegrenzung Art. 5 Abs. 1e: ohne Ablaufdatum bleibt der Link unbegrenzt nutzbar.') ?></li>
        <li><strong><?= te('Standard-Freigabetyp ist intern') ?></strong> — <?= te('Default-Linktyp sollte nicht Anyone sein.') ?></li>
        <li><strong><?= te('Sensitivity Labels veröffentlicht') ?></strong> — <?= te('Voraussetzung für Information Protection (Art. 32 Maßnahmen zur Datenintegrität).') ?></li>
        <li><strong><?= te('Aufbewahrungs-/eDiscovery-Fälle aktiv') ?></strong> — <?= te('Voraussetzung für Speicherbegrenzung und Lösch­pflichten (Art. 17).') ?></li>
        <li><strong><?= te('Audit-Log aktiv & abrufbar') ?></strong> — <?= te('Rechenschafts­pflicht Art. 5 Abs. 2 und Art. 32.') ?></li>
        <li><strong><?= te('DLP-/Label-Schutz für personenbezogene Daten') ?></strong> — <?= te('mindestens eine aktive Information-Protection-Maßnahme.') ?></li>
    </ul>
    <div class="tip-box"><i class="bi bi-info-circle"></i><?= te('Direkt-Link:') ?> <a href="/securityposture#cat-dsgvo-datenschutz">/securityposture#cat-dsgvo-datenschutz</a></div>
    <p><span class="perm-tag">Policy.Read.All</span> <span class="perm-tag">Domain.Read.All</span> <span class="perm-tag">InformationProtectionPolicy.Read.All</span> <span class="perm-tag">eDiscovery.Read.All</span> <span class="perm-tag">AuditLog.Read.All</span></p>
</div>

<!-- Tenant-Härtung ───────────────────────────────────────── -->
<div class="man-section" id="hardening">
    <h2><i class="bi bi-shield-fill-check text-primary"></i> <?= te('Tenant-Härtung (Quick-Actions)') ?></h2>
    <p><?= te('Eine kuratierte Seite mit den wichtigsten Sicherheits-Einstellungen, die mit einem Klick aktiviert werden können — entweder direkt über die Graph API oder per Deep-Link in das richtige Admin-Center, wenn Microsoft den Endpunkt nicht öffentlich gemacht hat.') ?></p>
    <h3><?= te('Direkt schaltbar (via Graph API)') ?></h3>
    <ul>
        <li><strong>Security Defaults</strong> — <?= te('ein/aus (PATCH <code>/policies/identitySecurityDefaultsEnforcementPolicy</code>)') ?></li>
        <li><strong>SharePoint Tenant-Sharing</strong> — <?= te('Anyone-Links global blocken oder einschränken') ?></li>
        <li><strong><?= te('Anonyme Link-Ablauffrist') ?></strong> — <?= te('z. B. auf 30 Tage setzen') ?></li>
        <li><strong>Default-Sharing-Linktyp</strong> — <?= te('auf „intern" zwingen') ?></li>
        <li><strong>Block-Legacy-Authentication CA-Policy</strong> — <?= te('mit einem Klick anlegen') ?></li>
        <li><strong>MFA-für-Alle CA-Policy</strong> — <?= te('Template, das nach Bestätigung im Report-Only-Modus angelegt wird') ?></li>
        <li><strong>Block-Auto-Forwarding zu externen Empfängern</strong> — <?= te('Authorization-Policy / Out­bound-Spam') ?></li>
        <li><strong><?= te('App-Consent einschränken') ?></strong> — <?= te('User-Consent auf „nur für verifizierte Publisher mit Low-Risk-Permissions"') ?></li>
        <li><strong>Guest-Invite-Restrictions</strong> — <?= te('nur Admins dürfen einladen') ?></li>
    </ul>
    <h3><?= te('Per Deep-Link ins Admin-Center') ?></h3>
    <p><?= te('Wo Graph keinen Schreib-Endpunkt anbietet (z. B. Audit-Log-Aktivierung, Defender-for-Office-Policies, Microsoft-Purview-DLP-Erstellung), öffnet der Button direkt die entsprechende Microsoft-Konsole.') ?></p>
    <p><?= te('Jede Aktion zeigt vor dem Ausführen den aktuellen Zustand, eine Erklärung des Effekts und eine BSI/NIS-2/DSGVO-Begründung.') ?></p>
    <p><span class="perm-tag">Policy.ReadWrite.ConditionalAccess</span> <span class="perm-tag">SharePointTenantSettings.ReadWrite.All</span> <span class="perm-tag">Policy.ReadWrite.Authorization</span></p>
</div>

<!-- PIM (JIT-Admin) ──────────────────────────────────────── -->
<div class="man-section" id="pim">
    <h2><i class="bi bi-lightning-charge text-primary"></i> <?= te('PIM — Just-in-Time-Admin') ?></h2>
    <p><?= te('Übersicht über das Microsoft Entra Privileged Identity Management. Statt dauerhafter Admin-Zuweisungen sollen Administratoren als „eligible" konfiguriert sein und ihre Rolle nur bei Bedarf für eine begrenzte Zeit aktivieren — mit MFA und Begründung. Das ist die Empfehlung aus BSI IT-Grundschutz ORP.4.A23 und NIS-2 Art. 21(j).') ?></p>
    <h3><?= te('Was die Seite zeigt') ?></h3>
    <ul>
        <li><strong><?= te('Aktiv erhöht') ?></strong> — <?= te('wer gerade eine Privileged-Role hat (entweder JIT-aktiviert oder dauerhaft zugewiesen).') ?></li>
        <li><strong>Eligible</strong> — <?= te('wer eine Rolle aktivieren kann, sie aber gerade nicht nutzt.') ?></li>
        <li><strong><?= te('Dauerhafte Admins') ?></strong> — <?= te('als Zahl mit Schwellwert ≤ 2 (rot, wenn überschritten — solche Konten sollten zu Eligible umgestellt werden).') ?></li>
        <li><strong><?= te('Aktivierungen der letzten 30 Tage') ?></strong> — <?= te('Audit-Trail: wer hat wann welche Rolle aktiviert, mit Erfolg/Misserfolg.') ?></li>
    </ul>
    <h3>Best Practice</h3>
    <ul>
        <li><?= te('Keine dauerhaften Global-Administrator-Zuweisungen.') ?></li>
        <li><?= te('Maximale Aktivierungs­dauer 8 Stunden, mit MFA-Pflicht.') ?></li>
        <li><?= te('Approval-Workflow für besonders kritische Rollen (z. B. „Privileged Role Administrator").') ?></li>
        <li><?= te('Audit-Trail mindestens 90 Tage aufbewahren.') ?></li>
    </ul>
    <p><span class="perm-tag">RoleManagement.Read.Directory</span> <span class="perm-tag">AuditLog.Read.All</span></p>
</div>

<!-- Break-Glass-Accounts ────────────────────────────────── -->
<div class="man-section" id="breakglass">
    <h2><i class="bi bi-key-fill text-primary"></i> <?= te('Break-Glass-Accounts') ?></h2>
    <p><?= te('Notfall-Administratorkonten sind die letzte Eskalationsstufe, wenn alle anderen Admin-Wege versagen — etwa wenn eine fehlerhafte Conditional-Access-Policy alle anderen Admins aussperrt, oder bei einem MFA-Ausfall. Microsoft empfiehlt mindestens <strong>zwei</strong> solcher Konten.') ?></p>
    <h3><?= te('Konfiguration') ?></h3>
    <p><?= te('Im Tool werden die UPNs der Break-Glass-Konten als Liste hinterlegt (kommagetrennt oder ein UPN pro Zeile). Für jeden Eintrag prüft das Tool automatisch:') ?></p>
    <ul>
        <li><strong><?= te('Existiert das Konto im Tenant?') ?></strong> <?= te('Wenn nicht → kritisches Issue.') ?></li>
        <li><strong><?= te('Ist es aktiviert?') ?></strong> <?= te('Deaktivierte Notfall­konten sind unbrauchbar.') ?></li>
        <li><strong><?= te('Ist es dauerhaft als Global Administrator zugewiesen?') ?></strong> <?= te('PIM-Eligible reicht nicht — eine Aktivierung verlangt MFA, das im Notfall vielleicht nicht funktioniert.') ?></li>
        <li><strong><?= te('Hat das Konto eine MFA-Methode registriert?') ?></strong> <?= te('Empfohlen ist ein FIDO2-Hardware-Key, der im Tresor liegt.') ?></li>
        <li><strong><?= te('Aus welchen CA-Policies ist es ausgeschlossen?') ?></strong> <?= te('Wenn aus keiner — Sperre droht. Wenn aus allen — Risiko bei kompromittiertem Passwort.') ?></li>
        <li><strong><?= te('Wann war der letzte Login?') ?></strong> <?= te('Break-Glass-Konten sollten mindestens halbjährlich getestet werden, sonst weiß niemand, ob sie im Notfall funktionieren.') ?></li>
    </ul>
    <div class="warn-box"><i class="bi bi-exclamation-triangle"></i><?= te('Microsoft empfiehlt für Break-Glass-Konten <strong>reine Cloud-Identitäten</strong> (nicht aus AD synchronisiert), <strong>komplexe Passwörter</strong> (mindestens 16 Zeichen, im Tresor verwahrt), und eine <strong>physische Ablage</strong> der Recovery-Methode (FIDO2-Key in zwei Standorten).') ?></div>
    <p><span class="perm-tag">User.Read.All</span> <span class="perm-tag">Policy.Read.All</span> <span class="perm-tag">RoleManagement.Read.Directory</span></p>
</div>

<!-- Auto-Forward-Audit ──────────────────────────────────── -->
<div class="man-section" id="mailboxrules">
    <h2><i class="bi bi-arrow-right-square text-primary"></i> <?= te('Auto-Forward-Audit') ?></h2>
    <p><?= te('Scannt alle aktiven Mailboxen im Tenant nach Inbox-Regeln, die eingehende E-Mails automatisch an eine externe Adresse weiterleiten. <strong>Auto-Forward an externe Domains ist statistisch der häufigste Exfiltrations­vektor</strong> bei kompromittierten Konten: der Angreifer richtet eine versteckte Inbox-Regel ein, die alle eingehenden Mails an seine Adresse weiterleitet, oft Tage bevor der Account-Inhaber es bemerkt.') ?></p>
    <h3><?= te('Was die Seite zeigt') ?></h3>
    <ul>
        <li><strong><?= te('Externe Auto-Forwards') ?></strong> — <?= te('rot markiert. Pro Treffer: Benutzer, Regel-Name, Ziel-Adresse, Active/Inactive.') ?></li>
        <li><strong><?= te('Interne Auto-Forwards') ?></strong> — <?= te('informativ (innerhalb der eigenen Domains).') ?></li>
        <li><strong><?= te('Lösch-Regeln') ?></strong> — <?= te('verdächtig in Kombination mit Phishing-Hijacks: ein Angreifer löscht automatisch alle Antworten und Sicherheits-Benachrichtigungen, damit der echte User nichts merkt.') ?></li>
    </ul>
    <h3><?= te('Wie reagieren') ?></h3>
    <ul>
        <li><?= te('Bei verdächtiger externer Weiterleitung: User-Konto sperren, Sessions revoken, Passwort-Reset erzwingen, Defender-Investigation öffnen.') ?></li>
        <li><?= te('Tenant-weit blockieren: Mail-Flow-Regel oder Exchange-Anti-Spam-Outbound-Policy mit <code>AutoForwardingMode = Off</code>.') ?></li>
    </ul>
    <div class="tip-box"><i class="bi bi-info-circle"></i><?= te('Performance-Hinweis: der Scan dauert je nach Tenant-Größe 30 Sekunden bis 5 Minuten. Ergebnisse werden 30 Min. gecached; per <code>?refresh=1</code> erzwingbar.') ?></div>
    <p><span class="perm-tag">User.Read.All</span> <span class="perm-tag">Mail.Read</span> <span class="perm-tag">Domain.Read.All</span></p>
</div>

<!-- OAuth-App-Audit ─────────────────────────────────────── -->
<div class="man-section" id="oauthaudit">
    <h2><i class="bi bi-app-indicator text-primary"></i> <?= te('OAuth-App-Audit') ?></h2>
    <p><?= te('Inventur aller Enterprise Apps (Service Principals) im Tenant mit Risiko-Bewertung. OAuth-Apps mit hohen Berechtigungen sind seit 2023 einer der Top-Vektoren für Tenant-Übernahme — typischerweise nach Migrationen, gekündigten 3rd-Party-Tools oder Phishing-Angriffen mit Illicit-Consent-Grant.') ?></p>
    <h3><?= te('Risiko-Bewertung') ?></h3>
    <p><?= te('Pro App wird ein Score 0–100 berechnet:') ?></p>
    <ul>
        <li><strong><?= te('+20 pro High-Privilege-Permission') ?></strong> — <?= te('z. B. <code>Mail.ReadWrite.All</code>, <code>Files.ReadWrite.All</code>, <code>Sites.FullControl.All</code>, <code>User.ReadWrite.All</code>, <code>Directory.ReadWrite.All</code>, <code>full_access_as_app</code>.') ?></li>
        <li><strong><?= te('+25 wenn nie angemeldet') ?></strong> — <?= te('die App hat Permissions, nutzt sie aber nicht — typisch nach Migration.') ?></li>
        <li><strong><?= te('+30 wenn letzte Anmeldung > 365 Tage') ?></strong><?= te(', +15 wenn > 180, +5 wenn > 90.') ?></li>
        <li><?= te('Microsoft-First-Party-Apps werden mit Score 0 markiert.') ?></li>
    </ul>
    <h3>Filter</h3>
    <p><?= te('Standardmäßig werden nur 3rd-Party-Apps gezeigt. Filter „Alle (inkl. Microsoft)" zeigt auch die etwa 100 Microsoft-eigenen Service Principals, die in jedem Tenant existieren.') ?></p>
    <h3><?= te('Was tun bei hohem Risiko') ?></h3>
    <p><?= te('Klick auf das Pfeil-Symbol öffnet die App direkt in Entra → Enterprise Applications. Dort: Berechtigungen prüfen, App ggf. deaktivieren oder löschen, alle bestehenden Token revoken.') ?></p>
    <p><span class="perm-tag">Application.Read.All</span> <span class="perm-tag">AuditLog.Read.All</span></p>
</div>

<!-- DLP-Vorfälle ────────────────────────────────────────── -->
<div class="man-section" id="dlpincidents">
    <h2><i class="bi bi-shield-shaded text-primary"></i> <?= te('DLP-Vorfälle') ?></h2>
    <p><?= te('Während das DLP-Richtlinien-Modul anzeigt, <em>ob</em> DLP-Policies aktiv sind, zeigt diese Seite die <strong>tatsächlichen Treffer</strong> — also wer hat versucht, eine als „Vertraulich" gelabelte Datei nach außen zu teilen, wer hat versucht eine Kreditkarten-Nummer per Mail zu versenden, etc. Das ist der eigentliche Compliance-Audit-Wert (DSGVO Art. 5 + 32).') ?></p>
    <h3><?= te('Datenquelle') ?></h3>
    <p><?= te('Audit-Log Filter auf <code>category eq \'DataLossPrevention\'</code> oder <code>activityDisplayName</code> mit DLP-/Sensitivity-Label-Prefix. Für detailliertere Daten (Inhalt der Auslöser, betroffene Felder) braucht es Microsoft Purview Premium.') ?></p>
    <h3>Aggregate</h3>
    <ul>
        <li><strong><?= te('Top User mit Treffern') ?></strong> — <?= te('wer wird wiederholt von DLP geblockt? Schulung nötig oder absichtlich?') ?></li>
        <li><strong><?= te('Top Aktivitäten') ?></strong> — <?= te('welche Regel-Typen lösen am häufigsten aus?') ?></li>
        <li><strong><?= te('Tages-Trend') ?></strong> — <?= te('Mini-Bar-Chart über den Zeitraum (7/30/90 Tage wählbar).') ?></li>
    </ul>
    <p><span class="perm-tag">AuditLog.Read.All</span></p>
</div>

<!-- Authentication-Strength ─────────────────────────────── -->
<div class="man-section" id="authstrength">
    <h2><i class="bi bi-fingerprint text-primary"></i> Authentication-Strength</h2>
    <p><?= te('Microsoft empfiehlt seit 2024 ausschließlich <strong>phishing-resistente MFA-Methoden</strong>: FIDO2-Security-Keys, Windows Hello for Business, Certificate-Based Authentication oder Hardware-OATH-Token. Microsoft Authenticator mit Number-Matching ist <strong>nicht</strong> phishing-resistent — Adversary-in-the-Middle-Angriffe (Evilginx, EvilProxy) können den Push-Code abfangen. SMS-OTP und Voice-Call sind erst recht unsicher.') ?></p>
    <h3><?= te('Klassifizierung der User') ?></h3>
    <ul>
        <li><strong><?= te('Phishing-resistent') ?></strong> — <?= te('mindestens eine starke Methode registriert.') ?></li>
        <li><strong><?= te('Nur Software-MFA') ?></strong> — <?= te('Authenticator-App oder TOTP, aber keine FIDO2.') ?></li>
        <li><strong><?= te('Nur schwache MFA') ?></strong> — <?= te('nur SMS / Voice / E-Mail-OTP.') ?></li>
        <li><strong><?= te('Keine MFA') ?></strong> — <?= te('nur Passwort.') ?></li>
    </ul>
    <h3><?= te('Methoden-Verteilung') ?></h3>
    <p><?= te('Pro Methode (FIDO2, Windows Hello, Authenticator, TOTP, SMS, E-Mail) wird die Adoption als horizontales Bar-Chart angezeigt. Starke Methoden grün, schwache rot.') ?></p>
    <h3><?= te('Tenant-Strength-Policies') ?></h3>
    <p><?= te('Listet die im Tenant konfigurierten Authentication-Strength-Policies (Built-in + Custom). Die Built-ins „Phishing-resistant MFA" und „Passwordless MFA" können in Conditional Access als Zugriffs­bedingung für kritische Apps verwendet werden.') ?></p>
    <p><span class="perm-tag">AuditLog.Read.All</span> <span class="perm-tag">Policy.Read.All</span></p>
</div>

<!-- Backup-Status ───────────────────────────────────────── -->
<div class="man-section" id="backup">
    <h2><i class="bi bi-database-fill-check text-primary"></i> <?= te('Backup-Status') ?></h2>
    <div class="warn-box"><i class="bi bi-exclamation-triangle"></i><strong><?= te('Microsoft sichert deine M365-Daten NICHT.') ?></strong> <?= te('Die Recycle-Bin-Frist von 30–93 Tagen ist kein Backup — nach Ransomware, versehentlichem Löschen, kompromittierten Admin-Konten oder Tenant-Kündigung sind die Daten weg. Für DSGVO Art. 32 (Verfügbarkeit), ISO 27001 A.12.3 und NIS-2 Art. 21(d) ist ein 3rd-Party-Backup-Tool Pflicht.') ?></div>
    <h3><?= te('Manuelles Tracking') ?></h3>
    <p><?= te('Da jedes 3rd-Party-Tool (Veeam, Druva, Spanning, AvePoint, Acronis, …) eigene APIs hat und keine einheitliche Microsoft-Backup-API existiert, lässt sich der Backup-Status nicht automatisch abfragen. Stattdessen pflegen Admins folgende Felder manuell:') ?></p>
    <ul>
        <li><?= te('Anbieter + URL') ?></li>
        <li><?= te('Datum des letzten erfolgreichen Backup-Laufs + Status') ?></li>
        <li><?= te('Retention (in Tagen)') ?></li>
        <li><?= te('Coverage: welche Workloads sind gesichert (Mail, OneDrive, SharePoint, Teams)') ?></li>
        <li><?= te('Datum des letzten erfolgreichen Restore-Tests') ?></li>
    </ul>
    <h3>Health-Score</h3>
    <p><?= te('0–100, berechnet aus den oben genannten Feldern. Critical: kein Backup-Anbieter. High: Coverage unvollständig, letzter Lauf > 7 Tage alt, Restore-Test nie durchgeführt.') ?></p>
</div>

<!-- KI-Sicherheitsberater ──────────────────────────────── -->
<div class="man-section" id="ai">
    <h2><i class="bi bi-robot text-primary"></i> <?= te('KI-Sicherheitsberater') ?></h2>
    <p><?= te('Eine Gesamt-Übersicht des Tenants, die auf den anonymisierten Metriken aller Module aufbaut und durch ein optionales LLM zu einer Geschäftsführungs-tauglichen Zusammenfassung verdichtet wird.') ?></p>
    <h3><?= te('Was die KI sieht') ?></h3>
    <p><strong><?= te('Ausschließlich aggregierte Counts und Prozentwerte') ?></strong>. <?= te('Niemals UPNs, niemals Domain-Namen, niemals Geräte-Namen, niemals Tenant-IDs, niemals SKU-Bezeichnungen, niemals einzelne IP-Adressen oder Zeitstempel. Beispiel:') ?></p>
    <pre style="font-size:12px;background:#f9fafb;padding:10px;border-radius:6px;">{
  "users":   {"total": 50, "mfa_pct": 60, "stale_90d": 5},
  "devices": {"total": 80, "compliant_pct": 87},
  "sharing": {"external": 50, "anonymous": 10},
  "risky":   {"users_at_risk": 0},
  "secure_score": {"current": 130, "max": 200}
}</pre>
    <h3><?= te('Empfehlungen') ?></h3>
    <p><?= te('Die konkreten Empfehlungen (mit Step-by-Step-Anleitung, BSI-/NIS-2-/DSGVO-Artikel-Zitaten und Microsoft-Doku-Links) kommen aus einer hartcodierten <code>RecommendationLibrary</code> — nicht aus der KI. Dadurch sind die Empfehlungen reproduzierbar und nicht-halluzinierend. Die KI liefert nur den 2–3-sätzigen Executive-Summary-Text und einen Score 0–100.') ?></p>
    <h3><?= te('Anomalie-Erkennung') ?></h3>
    <p><?= te('Zwei deterministische Anomaly-Services laufen im Hintergrund und fließen in den Kontext ein:') ?></p>
    <ul>
        <li><strong><?= te('Audit-Log-Anomalien') ?></strong> — <?= te('7-Tage-Rollup vs. 23-Tage-Baseline mit Poisson-Schwelle (avg + 2·√avg). Findet Aktivitäts-Spikes pro Kategorie.') ?></li>
        <li><strong><?= te('Sign-in-Anomalien') ?></strong> — <?= te('Credential-Stuffing-Signaturen (≥ 5 Failures + Success in 30 min), Impossible-Travel (Successful-Pair < 4 h, unterschiedliche Länder), Logins aus neuen Ländern, Off-Hours-Logins.') ?></li>
    </ul>
    <h3><?= te('Protokoll') ?></h3>
    <p><?= te('Unter <em>Einstellungen → KI-Sicherheitsberater → Protokoll anzeigen</em> kann der Administrator nachsehen, welche exakten Daten beim letzten Aufruf an die KI gesendet wurden — als Audit-Trail für DSGVO-Compliance.') ?></p>
    <h3><?= te('Provider-Konfiguration') ?></h3>
    <ul>
        <li><strong>OpenAI</strong> — <?= te('gpt-4o-mini empfohlen, schnell und günstig') ?></li>
        <li><strong>DeepSeek</strong> — <?= te('günstige Alternative') ?></li>
        <li><strong>Ollama (lokal)</strong> — <?= te('komplett on-prem, keine Daten verlassen das Netz; llama3.2 funktioniert gut') ?></li>
    </ul>
</div>

<!-- Executive-Report ───────────────────────────────────── -->
<div class="man-section" id="executivereport">
    <h2><i class="bi bi-envelope-paper text-primary"></i> <?= te('Executive-Report') ?></h2>
    <p><?= te('Monatliche HTML-Mail an die Geschäftsführung mit den wichtigsten Tenant-KPIs. Läuft automatisch am 1. jedes Monats via Cron.') ?></p>
    <h3><?= te('Inhalt') ?></h3>
    <ul>
        <li><strong>Security-Score</strong> <?= te('aus den Posture-Checks (grün/orange/rot je nach Wert).') ?></li>
        <li><strong><?= te('4 KPI-Tiles') ?></strong>: <?= te('Benutzer, Geräte (mit non-compliant), MFA-Quote, Conditional-Access-Policies.') ?></li>
        <li><strong><?= te('4 Risk-Tiles') ?></strong>: <?= te('Risikobenutzer, offene Defender Alerts, Gastbenutzer, Lizenz-SKUs.') ?></li>
        <li><strong>Top-Findings</strong> — <?= te('bis zu 5 fehlgeschlagene Posture-Checks.') ?></li>
        <li><strong>Footer</strong> <?= te('mit Link auf den KI-Berater für vollständige Empfehlungen.') ?></li>
    </ul>
    <p><?= te('Empfänger ist standardmäßig die Alert-E-Mail-Adresse, kann aber pro Report-Typ überschrieben werden (mehrere durch Komma getrennt).') ?></p>
    <p><?= te('Buttons <em>„Vorschau im Browser"</em> und <em>„Jetzt versenden"</em> erlauben Tests, ohne auf den 1. des Monats zu warten.') ?></p>
</div>

<!-- MFA-Fatigue ─────────────────────────────────────────── -->
<div class="man-section" id="mfafatigue">
    <h2><i class="bi bi-shield-slash text-primary"></i> <?= te('MFA-Fatigue-Erkennung') ?></h2>
    <p><?= te('MFA-Fatigue ist die Strategie, mit der ein Angreifer ein gestohlenes Passwort doch noch nutzbar macht: er triggert wiederholt MFA-Push-Notifications auf dem Handy des Opfers, bis es genervt „Approve" tippt. Bekanntester Fall: Uber-Hack 2022.') ?></p>
    <h3><?= te('Was die Seite zeigt') ?></h3>
    <ul>
        <li><strong><?= te('MFA-Denials gesamt') ?></strong> <?= te('im gewählten Zeitraum (24h / 7 Tage / 30 Tage).') ?></li>
        <li><strong><?= te('Verdächtige Cluster') ?></strong> — <?= te('pro User gruppiert in 30-Minuten-Fenster; ab 5 Denials gilt es als verdächtig.') ?></li>
        <li><strong><?= te('Erfolgreich (Approve!)') ?></strong> — <?= te('Cluster, in denen direkt nach den Denials eine erfolgreiche Anmeldung stand. Sofort-Maßnahmen einleiten!') ?></li>
    </ul>
    <h3><?= te('Sofortmaßnahmen bei einem erfolgreichen Angriff') ?></h3>
    <ol>
        <li><?= te('Konto sperren (<code>/users</code> → Benutzer → Deaktivieren).') ?></li>
        <li><?= te('Alle aktiven Sitzungen widerrufen (<code>revokeSignInSessions</code>).') ?></li>
        <li><?= te('Passwort-Reset erzwingen.') ?></li>
        <li><?= te('Inbox-Regeln im') ?> <a href="/mailboxrules"><?= te('Auto-Forward-Audit') ?></a> <?= te('prüfen — typischerweise legt ein Angreifer als Erstes eine Weiterleitungs-Regel an.') ?></li>
        <li><?= te('Im') ?> <a href="/oauthaudit"><?= te('OAuth-Audit') ?></a> <?= te('nachsehen, ob die App neuen Consents gegeben wurden.') ?></li>
    </ol>
    <h3><?= te('Prävention') ?></h3>
    <ul>
        <li><?= te('Auf <strong>Number-Matching</strong> umstellen (Microsoft hat das 2023 standardmäßig aktiviert).') ?></li>
        <li><?= te('Für privilegierte Konten: FIDO2 oder Windows Hello erzwingen (siehe') ?> <a href="/authstrength"><?= te('Auth-Strength') ?></a><?= te(').') ?></li>
        <li><?= te('Sign-in-Frequency in CA-Policies erhöhen, damit ein gekaperter Token nicht 90 Tage gültig bleibt (siehe') ?> <a href="/tokenlifetime"><?= te('Token-Lifetime') ?></a><?= te(').') ?></li>
    </ul>
    <p><span class="perm-tag">AuditLog.Read.All</span></p>
</div>

<!-- Insider-Threat ──────────────────────────────────────── -->
<div class="man-section" id="insiderthreat">
    <h2><i class="bi bi-eye-fill text-primary"></i> <?= te('Insider-Threat-Detection (Light)') ?></h2>
    <p><?= te('Statistische Anomalie-Erkennung pro User, basierend auf Sign-in- und Audit-Log-Daten. Das volle Microsoft Purview Insider Risk Management ist mächtiger, aber lizenz-pflichtig (E5 / Compliance-Add-on); dieses Modul liefert die wichtigsten Signale ohne zusätzliche Lizenz.') ?></p>
    <h3><?= te('Erfasste Signale') ?></h3>
    <ul>
        <li><strong><?= te('Off-Hours-Anmeldungen') ?></strong> — <?= te('wieviel Prozent der Logins fanden zwischen 22:00 und 06:00 statt? &gt; 50 % = Score +25, &gt; 25 % = +10.') ?></li>
        <li><strong><?= te('Geo-Diversität') ?></strong> — <?= te('Anmeldungen aus &gt; 3 verschiedenen Ländern in 30 Tagen = +15.') ?></li>
        <li><strong><?= te('Massendownloads') ?></strong> — <?= te('≥ 50 OneDrive-File-Reads in einer Stunde = +15 pro Burst.') ?></li>
        <li><strong>Mass-Mail-Send</strong> — <?= te('≥ 100 Mails in einer Stunde = +20 pro Burst.') ?></li>
        <li><strong><?= te('Lösch-Aktivität') ?></strong> — <?= te('≥ 100 Lösch-Events = +25, ≥ 30 = +10.') ?></li>
        <li><strong><?= te('Sharing-Aktivität') ?></strong> — <?= te('≥ 50 Sharing-Events = +20.') ?></li>
    </ul>
    <p><?= te('Der Gesamt-Score wird auf 100 gecappt. User mit Score ≥ 50 sind High-Risk und sollten geprüft werden — entweder ein legitimer „Power User" (Marketing, Außendienst) oder ein Insider-Threat-Verdachtsfall.') ?></p>
    <p><span class="perm-tag">AuditLog.Read.All</span></p>
</div>

<!-- Cross-Tenant-Access ────────────────────────────────── -->
<div class="man-section" id="crosstenantaccess">
    <h2><i class="bi bi-arrow-left-right text-primary"></i> <?= te('Cross-Tenant-Access (B2B/Federation)') ?></h2>
    <p><?= te('Regelt, welche externen Tenants Zugriff auf Ihre Ressourcen haben und in welche externen Tenants Ihre User dürfen. Drei Ebenen:') ?></p>
    <h3>Default-Policy</h3>
    <p><?= te('Gilt für alle externen Tenants ohne expliziten Eintrag. Microsoft-Default: B2B-Kollaboration erlaubt, B2B-Direct-Connect (Teams-Federation) blockiert, kein Trust für MFA/Compliant-Device.') ?></p>
    <h3><?= te('Partner-spezifisch') ?></h3>
    <p><?= te('Pro bekanntem Partner können Overrides definiert werden — z. B. eine engere Beziehung mit konkreten Tochterunternehmen, in denen MFA-Trust gegenseitig akzeptiert wird (dann muss der Gast nicht ein zweites Mal MFA durchlaufen).') ?></p>
    <h3>Service-Provider (MSP)</h3>
    <p><?= te('Markiert einen Tenant als „Managed Service Provider" — gibt diesem erweiterte Verwaltungs-Berechtigungen für unseren Tenant. Sicherheits-kritisch.') ?></p>
    <p><span class="perm-tag">Policy.Read.All</span> · <?= te('Schreib-Operationen über Entra-Portal.') ?></p>
</div>

<!-- Token-Lifetime ──────────────────────────────────────── -->
<div class="man-section" id="tokenlifetime">
    <h2><i class="bi bi-clock-history text-primary"></i> <?= te('Token-Lifetime &amp; Sign-in-Frequency') ?></h2>
    <p><?= te('Microsoft hat 2021 die globalen Token-Lifetime-Policies deprecated. Heute steuert man die effektive Anmelde-Frequenz über das <code>signInFrequency</code>-Setting in Conditional-Access-Policies.') ?></p>
    <h3><?= te('Empfohlene Werte') ?></h3>
    <table style="width:100%;border-collapse:collapse;font-size:13px;">
        <thead><tr style="background:#f3f4f6;"><th style="padding:6px;text-align:left;border:1px solid #e5e7eb;"><?= te('App-Klasse') ?></th><th style="padding:6px;text-align:left;border:1px solid #e5e7eb;">Sign-in-Frequency</th></tr></thead>
        <tbody>
            <tr><td style="padding:6px;border:1px solid #e5e7eb;">Privileged Roles (Admin)</td><td style="padding:6px;border:1px solid #e5e7eb;"><?= te('4 Stunden') ?></td></tr>
            <tr><td style="padding:6px;border:1px solid #e5e7eb;">Sensitive Apps (Finance, HR)</td><td style="padding:6px;border:1px solid #e5e7eb;"><?= te('12 Stunden') ?></td></tr>
            <tr><td style="padding:6px;border:1px solid #e5e7eb;">Standard Office-Apps</td><td style="padding:6px;border:1px solid #e5e7eb;"><?= te('7 Tage') ?></td></tr>
            <tr><td style="padding:6px;border:1px solid #e5e7eb;"><?= te('Privater Browser (Persistent-Browser)') ?></td><td style="padding:6px;border:1px solid #e5e7eb;"><?= te('Niemals persistent') ?></td></tr>
        </tbody>
    </table>
    <p><?= te('Konfiguration:') ?> <a href="https://entra.microsoft.com/#view/Microsoft_AAD_ConditionalAccess/ConditionalAccessBlade/~/Policies" target="_blank" rel="noopener">Entra → Conditional Access → Policy</a> → <?= te('Sitzung → Sign-in frequency.') ?></p>
    <p><span class="perm-tag">Policy.Read.All</span></p>
</div>

<!-- Lifecycle Workflows ─────────────────────────────────── -->
<div class="man-section" id="lifecycle">
    <h2><i class="bi bi-diagram-2 text-primary"></i> Lifecycle Workflows</h2>
    <p><?= te('Microsoft Entra ID Governance bietet automatisierte Workflows für die drei Lebens­phasen eines Mitarbeiter­kontos:') ?></p>
    <ul>
        <li><strong>Joiner</strong> — <?= te('beim Eintritt: zu Standard-Gruppen hinzufügen, Welcome-Mail senden, Manager benachrichtigen, Lizenzen zuweisen.') ?></li>
        <li><strong>Mover</strong> — <?= te('bei Abteilungs-Wechsel: alte Gruppen entfernen, neue zuweisen, Mailbox-Permissions anpassen.') ?></li>
        <li><strong>Leaver</strong> — <?= te('beim Austritt: Konto deaktivieren, Lizenzen entziehen, Manager benachrichtigen, nach X Tagen löschen.') ?></li>
    </ul>
    <p><?= te('Voraussetzung:') ?> <strong>Microsoft Entra ID Governance</strong> <?= te('(separate Lizenz oder im E5-Bundle).') ?></p>
    <p><?= te('Konfiguration im Entra-Portal — das Tool zeigt nur die definierten Workflows mit ihrem Status. Schreib-Operationen sind über die Graph-API möglich, sind aber nicht im Tool integriert (komplexe Task-Definitionen würden ihre eigene UI brauchen).') ?></p>
</div>

<!-- Phishing-Simulationen Modul + ausführliche Anleitung ─── -->
<div class="man-section" id="phishingsim">
    <h2><i class="bi bi-bullseye text-primary"></i> <?= te('Phishing-Simulationen') ?></h2>
    <p><?= te('Übersicht der durchgeführten Phishing-Simulationen aus Microsoft Defender Attack Simulation Training. Pro Simulation werden Empfänger-Anzahl, Klick-Rate, „Compromised"-Rate (User hat Credentials eingegeben oder Datei geöffnet) und Reporting-Rate (User hat die Phishing-Mail korrekt gemeldet) angezeigt.') ?></p>
    <h3><?= te('Wichtige Kennzahlen') ?></h3>
    <ul>
        <li><strong><?= te('Compromised-Rate &lt; 5 %') ?></strong> <?= te('ist ein gutes Ziel. &gt; 20 % bedeutet dringender Schulungs­bedarf.') ?></li>
        <li><strong><?= te('Reporting-Rate &gt; 50 %') ?></strong> <?= te('zeigt, dass die User das „Report Phishing"-Plugin in Outlook aktiv nutzen.') ?></li>
        <li><strong><?= te('Training-Quote') ?></strong> — <?= te('wieviele der erwischten User haben das zugewiesene Training auch abgeschlossen.') ?></li>
    </ul>
</div>

<!-- ═══════════════════════════════════════════════════════════
     AUSFÜHRLICHE PHISHING-SIMULATIONS-ANLEITUNG
     ═══════════════════════════════════════════════════════════ -->
<div class="man-section" id="phishing-anleitung">
    <h2><i class="bi bi-book-half text-primary"></i> Anleitung: Phishing-Simulationen mit Microsoft aufsetzen</h2>
    <p>Schritt-für-Schritt-Anleitung, wie Sie mit Microsoft Defender Attack Simulation Training eine kontrollierte Phishing-Kampagne in Ihrem Tenant durchführen — von der Vorbereitung über die Durchführung bis zur Nachbereitung.</p>

    <h3>1. Voraussetzungen</h3>
    <ul>
        <li><strong>Lizenz:</strong> Microsoft Defender for Office 365 <em>Plan 2</em> (in <code>Microsoft 365 E5</code> und <code>Microsoft 365 A5</code> enthalten) oder als Add-on buchbar.</li>
        <li><strong>Rolle:</strong> Sie benötigen eine der folgenden Microsoft-Entra-Rollen:
            <ul>
                <li><em>Globaler Administrator</em></li>
                <li><em>Sicherheits-Administrator</em></li>
                <li><em>Attack-Simulation-Administrator</em> (empfohlene Mindest­rolle)</li>
            </ul>
        </li>
        <li><strong>Postfach-Verzeichnis:</strong> Defender Attack Simulator nutzt die normale Tenant-Verzeichnis-Liste, also sind alle aktiven Mailboxen automatisch verfügbar.</li>
        <li><strong>Vorgespräche:</strong> Betriebs­rat und Daten­schutz­beauftragten <strong>vor</strong> der ersten Simulation einbinden — in Deutschland ist eine Phishing-Simulation eine Mitarbeiter-Schulungs­maßnahme, die u. U. mitbestimmungs­pflichtig ist (§ 87 Abs. 1 Nr. 6 BetrVG).</li>
    </ul>

    <h3>2. Vorbereitung &amp; Kommunikation</h3>
    <ol>
        <li>
            <strong>Pilotphase planen.</strong> Niemals direkt den ganzen Tenant ins kalte Wasser werfen — beginnen Sie mit einer Pilotgruppe von 10–30 Personen aus IT, Marketing oder Verwaltung.
        </li>
        <li>
            <strong>Vorab-Kommunikation:</strong> ankündigen, dass „in den nächsten Wochen Phishing-Simulationen stattfinden werden, ohne konkreten Termin". Das ist nicht der Verrat — Mitarbeiter sollen wissen, dass es passieren <em>kann</em>, aber nicht <em>wann</em>.
        </li>
        <li>
            <strong>Reporting-Plugin in Outlook aktivieren:</strong> stellen Sie sicher, dass der Button „Report Phishing" oder „Report Message" in Outlook für alle User sichtbar ist (Defender-Portal → Email &amp; collaboration → Policies → User reported settings).
        </li>
        <li>
            <strong>Training-Module vorbereiten:</strong> Microsoft bringt einen Standard-Pool von ca. 70 Trainings­videos mit. Schauen Sie sie sich vorher an und wählen Sie 3–5 aus, die zu Ihrer Kampagne passen.
        </li>
    </ol>

    <h3>3. Erste Simulation anlegen</h3>
    <ol>
        <li>
            <strong>Defender-Portal öffnen:</strong>
            <a href="https://security.microsoft.com/attacksimulator" target="_blank" rel="noopener">security.microsoft.com/attacksimulator</a> → Reiter <em>Simulations</em> → <em>+ Launch a simulation</em>.
        </li>
        <li>
            <strong>Technik wählen.</strong> Microsoft bietet sechs Standard-Techniken — beginnen Sie mit <em>Credential Harvest</em> (gefälschte Login-Seite), das ist statistisch der häufigste reale Angriffstyp.
            <ul style="margin-top:6px;">
                <li><em>Credential Harvest</em> — gefälschte Anmelde-Seite</li>
                <li><em>Malware Attachment</em> — schädlicher Anhang</li>
                <li><em>Link in Attachment</em> — Link im Dokument</li>
                <li><em>Link to Malware</em> — Direkt-Link zu Malware</li>
                <li><em>Drive-by URL</em> — bösartige Webseite</li>
                <li><em>OAuth Consent Grant</em> — gefälschte App-Berechtigungs-Anfrage (besonders aktuell)</li>
            </ul>
        </li>
        <li>
            <strong>Payload wählen.</strong> Microsoft liefert Hunderte fertige Payloads in vielen Sprachen — wählen Sie eine deutschsprachige Variante, am besten mit einem Bezug zu Ihrem Branchen­alltag (Paket­benachrichtigung, Bewerbung, Microsoft-Sicherheits­warnung, …). Mit Klick auf eine Payload sehen Sie eine Vorschau.
        </li>
        <li>
            <strong>Empfänger auswählen.</strong> Für die erste Kampagne 10–30 Pilot-User. Spätere Kampagnen können auf Gruppen abzielen oder alle User auf einmal.
        </li>
        <li>
            <strong>Training konfigurieren.</strong> User, die auf den Link klicken / Daten eingeben / die Mail nicht melden, bekommen automatisch ein Training zugewiesen (Microsoft empfiehlt das „NIST"-Trainingspfad).
        </li>
        <li>
            <strong>Phishing-Landing-Page wählen.</strong> Bei <em>Credential Harvest</em> sieht der User nach Eingabe seiner Daten eine kurze Erklärungs-Seite („Dies war eine Simulation — bitte verwenden Sie nie Ihr echtes Passwort auf solchen Seiten").
        </li>
        <li>
            <strong>Zeitfenster setzen.</strong> Empfohlen: 2-Wochen-Fenster, in denen die Mail zufällig verteilt wird. Microsoft hat ein „Region-Aware-Delivery"-Feature, das die Mail in den lokalen Bürozeiten ausliefert.
        </li>
        <li>
            <strong>Starten.</strong> Vor dem finalen Klick auf <em>Submit</em> erhalten Sie eine Zusammenfassung — prüfen Sie sie sorgfältig.
        </li>
    </ol>

    <h3>4. Während der Kampagne</h3>
    <ul>
        <li>Im Defender-Portal können Sie den Live-Status sehen — wieviele User die Mail bekommen haben, wieviele geklickt haben, wieviele „kompromittiert" sind.</li>
        <li>Im Tool wird die Simulation unter <a href="/phishingsim">/phishingsim</a> mit denselben Daten gespiegelt.</li>
        <li>Helpdesk-Tickets von Usern, die fragen „ist diese Mail echt?" sind erwünschte Reaktionen — kein Anlass zur Sorge.</li>
    </ul>

    <h3>5. Nachbereitung</h3>
    <ol>
        <li>
            <strong>Reporting-Quote analysieren.</strong> Wenn weniger als 30 % der User die Phishing-Mail gemeldet haben, ist das ein klares Schulungs­signal — das Reporting-Plugin ist entweder unbekannt oder nicht installiert.
        </li>
        <li>
            <strong>Compromised-User zuweisen.</strong> User, die geklickt + Daten eingegeben haben, bekommen automatisch Trainings — überprüfen Sie nach 14 Tagen die Abschluss­quote. Wer das Training nicht abschließt, bekommt eine Eskalation an den Vorgesetzten.
        </li>
        <li>
            <strong>Transparenter Bericht an die Belegschaft.</strong> Senden Sie eine anonymisierte Zusammenfassung („28 % der Mitarbeiter haben geklickt, 12 % haben Credentials eingegeben, 65 % haben die Mail gemeldet — wir machen die nächste Runde in 3 Monaten"). Das fördert Awareness ohne Beschämung.
        </li>
        <li>
            <strong>Datenschutz-konform speichern.</strong> Defender speichert die Daten 90 Tage automatisch; für längere Aufbewahrung müssen Sie sie exportieren — was bei DSGVO problematisch ist, weil Mitarbeiter dann namentlich auftauchen.
        </li>
    </ol>

    <h3>6. Kadenz</h3>
    <p>Empfehlung: <strong>alle 2–3 Monate</strong> eine Kampagne, je mit anderer Technik und anderem Payload. Studien zeigen, dass die Klick-Quote bei einer konstanten Kampagne nach ca. 18 Monaten von typisch 25 % auf unter 5 % sinkt.</p>

    <h3>7. Häufige Fallstricke</h3>
    <ul>
        <li><strong>Personalrat/Betriebsrat nicht eingebunden.</strong> Kann zu Beschwerden und im schlimmsten Fall zu Untersagung führen. Vorher klären.</li>
        <li><strong>Beschämungs-Kommunikation.</strong> Wer dem Marketing einen Brief schickt „Sie sind unser bester Klicker", verliert die Belegschaft. Stattdessen anonyme Aggregate.</li>
        <li><strong>Zu seltene Wiederholung.</strong> Eine Phishing-Simulation pro Jahr bringt fast nichts — Skills verblassen schnell.</li>
        <li><strong>Standard-Payloads ohne Anpassung.</strong> Microsoft-Standard-Templates sind oft zu generisch. Erstellen Sie für die zweite/dritte Kampagne <em>Custom Payloads</em>, die Ihren Branchen-Kontext aufnehmen.</li>
        <li><strong>Verteilung über Mail-Allow-Listen umgehen.</strong> Defender Simulator ist standardmäßig auf der Allow-Liste — wenn Ihre Anti-Spam-Regeln zu aggressiv sind, kann die Simulations-Mail trotzdem gefiltert werden. Prüfen Sie im Vorhinein mit einer Test-Simulation an die IT-Abteilung.</li>
    </ul>

    <div class="tip-box">
        <i class="bi bi-lightbulb"></i>
        <strong>Pro-Tipp:</strong> Nach 2–3 erfolgreichen Kampagnen lassen sich die Simulationen mit der <em>Automation</em>-Funktion im Defender-Portal auch selbst-fahrend einrichten — Microsoft wählt dann pro Quartal eine neue Technik + Payload aus und versendet die Mail an User-Gruppen, die schon eine Weile keine Simulation mehr bekommen haben.
    </div>

    <h3>8. Weiterführende Links</h3>
    <ul>
        <li><a href="https://learn.microsoft.com/de-de/defender-office-365/attack-simulation-training-get-started" target="_blank" rel="noopener">Microsoft Learn — Attack Simulation Training</a></li>
        <li><a href="https://learn.microsoft.com/de-de/defender-office-365/attack-simulation-training-payloads" target="_blank" rel="noopener">Payload-Bibliothek</a></li>
        <li><a href="https://learn.microsoft.com/de-de/defender-office-365/attack-simulation-training-simulation-automations" target="_blank" rel="noopener">Simulation Automations</a></li>
        <li><a href="https://www.bsi.bund.de/DE/Themen/Verbraucherinnen-und-Verbraucher/Cyber-Sicherheitslage/Methoden-der-Cyber-Kriminalitaet/Spam-Phishing-Co/Phishing/phishing_node.html" target="_blank" rel="noopener">BSI — Phishing-Methoden</a></li>
    </ul>
</div>

<!-- Identity Providers ──────────────────────────────────── -->
<div class="man-section" id="identityproviders">
    <h2><i class="bi bi-person-bounding-box text-primary"></i> External Identity Provider Trust</h2>
    <p>Listet alle konfigurierten externen Identity Providers im Tenant (Google, Facebook, Apple für B2C-Szenarien) sowie federierte Domains (ADFS, Okta, Ping Identity, …). Jeder zusätzliche IdP ist eine Erweiterung der Angriffsfläche — sollte periodisch auditiert werden.</p>
    <p><span class="perm-tag">IdentityProvider.Read.All</span> <span class="perm-tag">Domain.Read.All</span></p>
</div>

<!-- Customer Lockbox ───────────────────────────────────── -->
<div class="man-section" id="customerlockbox">
    <h2><i class="bi bi-lock-fill text-primary"></i> Customer Lockbox</h2>
    <p>Ohne Customer Lockbox darf Microsoft Support im Notfall direkt auf Ihre Daten zugreifen — Sie erfahren es nicht. Mit aktiviertem Lockbox muss jeder Microsoft-Support-Zugriff aktiv von einem Tenant-Admin approvt werden, sonst gibt es <strong>keinen</strong> Zugriff.</p>
    <p><strong>Voraussetzung:</strong> Microsoft 365 E5 oder als Add-on.</p>
    <p>Microsoft Graph stellt für diese Einstellung keinen Schreib-Endpunkt zur Verfügung — Konfiguration daher im M365 Admin Center → Security &amp; Privacy. Das Tool tracked nur den manuell eingetragenen Aktivierungs-Status, die Approver-Liste, die SLA-Reaktionszeit und das Datum der letzten Review (halbjährlich empfohlen).</p>
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

    <h3>Admin-Passwort</h3>
    <p>Ändert das Passwort des lokalen Administrator-Kontos (das ursprünglich beim Setup eingerichtete Konto).</p>

    <h3>Berechtigungen prüfen</h3>
    <p>Zeigt, welche Microsoft Graph Berechtigungen dem konfigurierten App-Konto erteilt wurden und welche Module dadurch eingeschränkt sind. Nach Änderungen in Azure AD kann das Token über den Button „Token erneuern & neu prüfen" sofort aktualisiert werden.</p>

    <h3>Cache leeren</h3>
    <p>Löscht alle zwischengespeicherten API-Antworten sofort. Hilfreich nach manuellen Änderungen direkt in Azure AD.</p>
</div>

<!-- Benutzer-Zugang ──────────────────────────────────────── -->
<div class="man-section" id="useraccess">
    <h2><i class="bi bi-people-fill text-primary"></i> Benutzer-Zugang</h2>
    <p>Neben dem lokalen Admin-Konto können beliebig viele <strong>Microsoft 365-Benutzer</strong> des Tenants berechtigt werden, sich mit ihrem Microsoft-Konto anzumelden — z.B. IT-Mitarbeiter als Operator.</p>

    <h3>Voraussetzungen in Azure</h3>
    <p>Die bestehende App-Registrierung muss um folgende Konfiguration ergänzt werden:</p>
    <ol>
        <li>In der App-Registrierung unter <strong>Authentifizierung → Redirect-URIs</strong> die angezeigte URI eintragen (wird auf der Seite <em>Einstellungen → Benutzer-Zugang</em> direkt angezeigt)</li>
        <li>Unter <strong>API-Berechtigungen</strong> die <strong>delegierte</strong> Berechtigung <span class="perm-tag">User.Read</span> hinzufügen (nicht die Anwendungsberechtigung — die delegierte Version reicht für das Login)</li>
        <li>Kein zusätzlicher Admin-Consent nötig — <code>User.Read</code> delegiert wird von jedem Benutzer selbst beim ersten Login genehmigt</li>
    </ol>
    <div class="tip-box"><i class="bi bi-lightbulb"></i>Die Redirect-URI ist im Format <code>https://ihre-domain/auth/microsoft/callback</code>. Wenn <em>App-Basis-URL</em> in den Einstellungen konfiguriert ist, wird diese verwendet.</div>

    <h3>Benutzer hinzufügen</h3>
    <p>Über <strong>Einstellungen → Benutzer-Zugang → Benutzer hinzufügen</strong> können vorhandene Tenant-Benutzer per Suchfunktion ausgewählt werden:</p>
    <ol>
        <li>Im Suchfeld Name oder E-Mail-Adresse eingeben (mindestens 2 Zeichen)</li>
        <li>Benutzer aus den Vorschlägen auswählen (durchsucht Anzeigenamen und UPN)</li>
        <li>Rolle festlegen: <strong>Operator</strong> (Standard) oder <strong>Administrator</strong></li>
        <li>„Hinzufügen" klicken</li>
    </ol>
    <p>Falls die Graph-Suche nicht verfügbar ist (z.B. fehlende Berechtigung), kann der UPN auch manuell eingegeben werden (Link „UPN manuell eingeben" im Dialog).</p>

    <h3>Anmeldevorgang für Benutzer</h3>
    <ol>
        <li>Benutzer öffnet die Login-Seite und klickt <strong>„Mit Microsoft anmelden"</strong></li>
        <li>Weiterleitung zur Microsoft-Anmeldeseite (login.microsoftonline.com)</li>
        <li>Nach erfolgreicher Authentifizierung prüft das Tool, ob der Benutzer in der Zugriffsliste steht</li>
        <li>Ist er eingetragen und aktiv: automatische Anmeldung mit der zugewiesenen Rolle</li>
        <li>Ist er nicht eingetragen: Anzeige der Seite „Kein Zugriff" — kein Zugriff auf das Tool</li>
    </ol>

    <h3>Rollen &amp; Berechtigungen</h3>
    <table class="table table-sm table-bordered small">
        <thead class="table-light"><tr><th>Funktion</th><th>Operator</th><th>Administrator</th></tr></thead>
        <tbody>
            <tr><td>Alle Monitoring-Module lesen</td><td><i class="bi bi-check text-success"></i></td><td><i class="bi bi-check text-success"></i></td></tr>
            <tr><td>Scans starten, Erinnerungen senden</td><td><i class="bi bi-check text-success"></i></td><td><i class="bi bi-check text-success"></i></td></tr>
            <tr><td>Freigaben manuell widerrufen</td><td><i class="bi bi-check text-success"></i></td><td><i class="bi bi-check text-success"></i></td></tr>
            <tr><td>Einstellungen bearbeiten</td><td><i class="bi bi-x text-danger"></i></td><td><i class="bi bi-check text-success"></i></td></tr>
            <tr><td>Benutzer-Zugang verwalten</td><td><i class="bi bi-x text-danger"></i></td><td><i class="bi bi-check text-success"></i></td></tr>
            <tr><td>Updates einspielen</td><td><i class="bi bi-x text-danger"></i></td><td><i class="bi bi-check text-success"></i></td></tr>
        </tbody>
    </table>

    <h3>Benutzer deaktivieren / entfernen</h3>
    <p>Über den Bearbeiten-Button kann ein Benutzer <strong>deaktiviert</strong> werden (Zugriff gesperrt, aber Eintrag bleibt erhalten) oder über den Löschen-Button vollständig entfernt werden. Eine aktive Session wird beim nächsten Seitenaufruf automatisch beendet.</p>
    <div class="warn-box"><i class="bi bi-exclamation-triangle"></i>Der letzte Administrator-Benutzer sollte nicht entfernt werden. Das lokale Admin-Konto (aus dem Setup) ist davon unabhängig und bleibt immer erhalten.</div>
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

    <h3>Für M365-Benutzer-Login (delegiert)</h3>
    <p>Damit IT-Mitarbeiter sich mit ihrem Microsoft-Konto anmelden können, wird zusätzlich benötigt:</p>
    <ul>
        <li><span class="perm-tag">User.Read</span> — <strong>delegierte</strong> Berechtigung (nicht Application), ermöglicht das Auslesen von Name und UPN des angemeldeten Benutzers nach dem Login</li>
    </ul>
    <p class="text-muted small">Diese Berechtigung wird unter <em>API-Berechtigungen → Delegierte Berechtigungen → Microsoft Graph → User.Read</em> hinzugefügt. Kein Admin-Consent nötig.</p>

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

<div class="man-section" id="setupwizard">
    <h2><i class="bi bi-magic"></i> Einrichtungs-Assistent</h2>
    <p>Beim ersten Login eines Admins erscheint automatisch der fünfstufige Einrichtungs-Assistent. Er prüft die Tenant-Verbindung, die App-Permissions, fragt Benachrichtigungs-Empfänger und Branding ab, und schlägt am Ende ein passendes Compliance-Profil vor. Der Assistent kann jederzeit erneut über <strong>Administration → Einrichtungs-Assistent</strong> aufgerufen werden.</p>
</div>

<div class="man-section" id="complianceprofiles">
    <h2><i class="bi bi-patch-check"></i> Compliance-Profile</h2>
    <p>Compliance-Profile bündeln branchen-typische Härtungs-Voreinstellungen zu Ein-Klick-Presets. Verfügbare Profile: <strong>Standard / DSGVO-Basis</strong>, <strong>Gesundheitswesen (KRITIS)</strong>, <strong>Finanzwesen (BaFin/DORA)</strong>, <strong>Öffentlicher Sektor / BSI</strong>, <strong>Bildung</strong>. Jedes Profil ruft beim Anwenden eine Sequenz von <code>HardeningService</code>-Aktionen auf — komplett im Audit-Log nachvollziehbar und über das Härtungs-Modul einzeln revidierbar.</p>
    <div class="tip-box"><i class="bi bi-lightbulb"></i>Profile sind <strong>nicht exklusiv</strong>. Du kannst z. B. mit dem Standard-Profil starten und einzelne Härtungs-Items im <code>/hardening</code>-Modul nachjustieren. Das aktuell aktive Profil wird in den Settings vermerkt.</div>
</div>

<div class="man-section" id="notifications">
    <h2><i class="bi bi-bell"></i> In-App-Benachrichtigungen</h2>
    <p>Die Glocke oben rechts in der Topbar zeigt alle Tenant-Ereignisse seit deinem letzten Besuch. Module wie Defender-Alerts, Cross-Tenant-Access, MFA-Fatigue oder das Compliance-Profil drücken Events in das gemeinsame Feed — eine Klick-Adresse pro Eintrag führt direkt zur Detail-Seite. Benachrichtigungen werden 90 Tage aufbewahrt und automatisch vom Cron-Job <code>notification_trim</code> gepflegt.</p>
</div>

<div class="man-section" id="auditdiff">
    <h2><i class="bi bi-arrow-left-right"></i> Audit-Diff</h2>
    <p>Täglich (Cron-Job <code>audit_diff_snapshot</code>) wird ein Snapshot aller sicherheitsrelevanten Tenant-Einstellungen erstellt — Authorization Policy, Security Defaults, Auth Methods, SharePoint, Conditional Access, Admin-Rollen, Gast-Konfiguration. In <strong>Compliance &amp; Audit → Audit-Diff</strong> kannst du beliebige zwei Snapshots auswählen und alle Veränderungen mit Rot-/Grün-/Gelb-Markierung darstellen.</p>
    <p>Ideal für Übergaben (was hat der Vorgänger letzte Woche verändert?), für Audits (was hat sich seit der letzten Prüfung getan?) und für die Untersuchung von Vorfällen (wer hat wann diese Einstellung umgestellt? — Audit-Log liefert das &quot;wer&quot;, Audit-Diff das &quot;was&quot;).</p>
</div>

<div class="man-section" id="auditreport">
    <h2><i class="bi bi-file-earmark-pdf"></i> DSGVO/NIS-2 Audit-Report</h2>
    <p>Unter <strong>Compliance &amp; Audit → DSGVO/NIS-2 Report</strong> erzeugt das Tool einen kompletten Audit-Bericht. Die Struktur:</p>
    <ol>
        <li><strong>Deckblatt</strong> mit Tenant-Stammdaten und aktivem Compliance-Profil</li>
        <li><strong>Graph-API-Berechtigungen</strong> — wieviele erteilt, wieviele fehlen</li>
        <li><strong>Hardening-Übersicht</strong> aller 21 Items, gruppiert nach Kategorie</li>
        <li><strong>Regulatorische Zuordnung</strong> — DSGVO Art. 25/32, NIS-2 Art. 21, BSI ORP.4 mit den jeweils zugeordneten Hardening-Items</li>
    </ol>
    <p>Mit dem &quot;Als PDF speichern&quot;-Button generiert dein Browser daraus eine PDF-Datei — perfekt für Auditoren, IT-Leitung oder Lieferanten-Auskünfte.</p>
</div>

<div class="man-section" id="restapi">
    <h2><i class="bi bi-code-slash"></i> REST-API &amp; Swagger</h2>
    <p>Das Tool stellt unter <code>/api/v1/...</code> eine umfangreiche REST-API für externe Werkzeuge bereit: PowerBI, Grafana, n8n, eigene Skripte. Endpunkte u. a.:</p>
    <ul>
        <li><code>GET /api/v1/dashboard/metrics</code> — alle KPIs in einem JSON</li>
        <li><code>GET /api/v1/dashboard/security</code> — MFA/CA/Risk-Status</li>
        <li><code>GET /api/v1/dashboard/licenses</code> — Top-Lizenz-Nutzung</li>
        <li><code>GET /api/v1/metrics/{name}/history?days=30</code> — Historie für Charts</li>
        <li><code>GET /api/v1/hardening</code> — Liste der Härtungs-Items mit Status</li>
        <li><code>GET /api/v1/snapshots</code> &amp; <code>/api/v1/snapshots/diff?from=&amp;to=</code></li>
        <li><code>GET /api/v1/notifications</code> &amp; <code>POST /api/v1/notifications/push</code> (Webhook-Stil)</li>
        <li><code>GET /api/v1/audit-log</code></li>
    </ul>
    <h3>Authentifizierung</h3>
    <p>Per API-Key im Header: <code>X-Api-Key: m365_xxxxxxxx</code>. Keys erzeugst du unter <strong>Administration → API-Schlüssel</strong>; der Klartextwert wird genau einmal angezeigt und nur als SHA-256-Hash gespeichert. Scopes: <code>read</code> (Lesen), <code>write</code> (Notifications pushen), <code>admin</code> (reserviert).</p>
    <h3>Dokumentation</h3>
    <p>Die vollständige interaktive OpenAPI-3.0-Dokumentation findest du unter <code>/api/docs</code> — Swagger UI mit &quot;Try it out&quot;-Funktion. Die Roh-Spec gibt es unter <code>/api/openapi.json</code> für Import in z. B. Postman.</p>
</div>

<div class="man-section" id="workflows">
    <h2><i class="bi bi-diagram-2"></i> Workflow-Automatisierung</h2>
    <p>Unter <strong>Administration → Workflows</strong> kannst du leichtgewichtige Trigger-Aktion-Sequenzen anlegen — als Mini-Power-Automate für M365-Standardabläufe. Beispiele:</p>
    <ul>
        <li>&quot;Neuer Gast-Benutzer&quot; → &quot;In Gruppe X aufnehmen&quot; + &quot;Mail an IT-Leitung&quot; + &quot;In-App-Benachrichtigung erzeugen&quot;</li>
        <li>&quot;Alle 60 Minuten&quot; → &quot;Notification erzeugen, wenn Risk-Score hoch&quot;</li>
        <li>&quot;Neuer Benutzer in Gruppe XY&quot; → &quot;Lizenz E3 zuweisen&quot; + &quot;Begrüßungsmail senden&quot;</li>
    </ul>
    <p>Trigger werden alle 15 Minuten vom Cron-Job <code>workflow_runner</code> ausgewertet. Jede Aktion landet im Run-Log (Schwester-Tabelle <code>app_workflow_runs</code>) mit Status, Ziel und Detail. Template-Variablen für Mail-/Notification-Felder: <code>{{user.userPrincipalName}}</code>, <code>{{user.displayName}}</code>, <code>{{user.id}}</code>, <code>{{trigger}}</code>.</p>
</div>

<div class="man-section" id="kpisparklines">
    <h2><i class="bi bi-graph-up"></i> KPI-Sparklines</h2>
    <p>Neben den wichtigsten Dashboard-Kennzahlen siehst du ein 7-Tage-Mini-Diagramm und einen Prozent-Pfeil (<code>↑ 3,2%</code>) — der Trend gegenüber der letzten Woche. Das funktioniert, sobald das Dashboard ein paar Tage in Folge aufgerufen wurde (Werte werden in <code>app_metric_history</code> persistiert). API-Endpunkt für externe Charts: <code>/api/v1/metrics/{name}/history</code>.</p>
</div>

<div class="man-section" id="onlinehelp">
    <h2><i class="bi bi-question-circle"></i> Online-Hilfe (?-Bubbles)</h2>
    <p>An vielen Stellen findest du kleine <code>?</code>-Symbole neben Labels und Überschriften. Beim Hovern erscheint eine deutschsprachige Erklärung — der gesamte Katalog (~35 Begriffe) wird zentral in <code>src/Core/Help.php</code> gepflegt und kann mit <code>\App\Core\Help::tip('key')</code> in jeder View aufgerufen werden.</p>
</div>

</div><!-- /manual-body -->
</div><!-- /manual-wrap -->
