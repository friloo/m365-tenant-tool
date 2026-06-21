<?php

/**
 * English translations for view-specific strings (notifications, favorites,
 * auth pages, error pages, partials and the user manual).
 *
 * Keys are the exact German source strings used in the views. The central
 * lang/en.php map wins on collisions for shared glossary terms.
 *
 * @return array<string,string>
 */
return [

    // ── notifications/index.php ─────────────────────────────────────────────
    'Alle Ereignisse aus diesem Tenant (jüngste zuerst). Werden 90 Tage aufbewahrt und automatisch durch den Cron getrimmt.'
        => 'All events from this tenant (most recent first). Retained for 90 days and trimmed automatically by the cron job.',
    'Keine Benachrichtigungen' => 'No notifications',

    // ── favorites/index.php ─────────────────────────────────────────────────
    'Hier liegen deine angepinnten Seiten. Öffne ein beliebiges Modul und tippe oben rechts auf den'
        => 'Your pinned pages live here. Open any module and tap the',
    '-Stern, um es zu den Favoriten hinzuzufügen. Die Favoriten werden lokal in diesem Browser gespeichert.'
        => ' star at the top right to add it to your favorites. Favorites are stored locally in this browser.',
    'Noch keine Favoriten. Tippe auf einer Modulseite oben rechts auf den Stern.'
        => 'No favorites yet. Tap the star at the top right on any module page.',

    // ── auth/2fa.php ────────────────────────────────────────────────────────
    'Zwei-Faktor-Authentifizierung' => 'Two-Factor Authentication',
    'Gib den Code aus deiner Authenticator-App ein.' => 'Enter the code from your authenticator app.',
    '6-stelliger Code' => '6-digit code',
    'Verifizieren' => 'Verify',
    'Wiederherstellungscode verwenden' => 'Use a recovery code',
    'Verwenden' => 'Use',
    'Zurück zur Anmeldung' => 'Back to sign-in',

    // ── auth/no_access.php ──────────────────────────────────────────────────
    'Kein Zugriff' => 'No access',
    'Ihr Microsoft-Konto' => 'Your Microsoft account',
    'hat keinen Zugriff auf dieses Tool.' => 'does not have access to this tool.',
    'Bitte wenden Sie sich an den Administrator, um Zugriff zu erhalten.'
        => 'Please contact your administrator to obtain access.',

    // ── errors/404.php ──────────────────────────────────────────────────────
    'Seite nicht gefunden' => 'Page not found',
    'Zum Dashboard' => 'To dashboard',

    // ── partials/graph_diagnostic.php ───────────────────────────────────────
    'Zur Lösung' => 'View solution',

    // ── manual: table of contents ───────────────────────────────────────────
    'Einstieg' => 'Getting started',
    'Einführung' => 'Introduction',
    'Navigation' => 'Navigation',
    'Verzeichnis' => 'Directory',
    'Aktionen' => 'Actions',
    'Lizenz-Berater' => 'License advisor',
    'Speicher & Freigaben' => 'Storage & Sharing',
    'Freigaben' => 'Sharing',
    'Freigaben-Monitor' => 'Sharing monitor',
    'Freigaberichtlinien' => 'Sharing policies',
    'Exchange & Komm.' => 'Exchange & Comm.',
    'Postfächer' => 'Mailboxes',
    'Teams-Nutzung' => 'Teams usage',
    'Adoptions-Report' => 'Adoption report',
    'Mail Flow & Schutz' => 'Mail flow & protection',
    'Dienststatus' => 'Service health',
    'Sicherheit' => 'Security',
    'Sicherheit (CA)' => 'Security (CA)',
    'DSGVO-Status' => 'GDPR status',
    'Tenant-Härtung' => 'Tenant hardening',
    'App-Registrierungen' => 'App registrations',
    'Erweitertes Hardening' => 'Advanced hardening',
    'Auto-Forward-Audit' => 'Auto-forward audit',
    'OAuth-App-Audit' => 'OAuth app audit',
    'DLP-Vorfälle' => 'DLP incidents',
    'Backup-Status' => 'Backup status',
    'Executive-Report' => 'Executive report',
    'MFA-Fatigue-Erkennung' => 'MFA fatigue detection',
    'Insider-Threat-Detection' => 'Insider threat detection',
    'Lifecycle Workflows' => 'Lifecycle Workflows',
    'Phishing-Simulationen' => 'Phishing simulations',
    '→ Anleitung Phishing-Sim' => '→ Phishing sim guide',
    'KI & Reports' => 'AI & Reports',
    'KI-Sicherheitsberater' => 'AI security advisor',
    'Geräte' => 'Devices',
    'Audit-Diff' => 'Audit diff',
    'DSGVO/NIS-2 Report' => 'GDPR/NIS-2 report',
    'Compliance-Profile' => 'Compliance profiles',
    'Compliance & Audit (erweitert)' => 'Compliance & Audit (advanced)',
    'Erweiterungen' => 'Extensions',
    'Einrichtungs-Assistent' => 'Setup wizard',
    'Workflow-Automatisierung' => 'Workflow automation',
    'In-App-Benachrichtigungen' => 'In-app notifications',
    'Online-Hilfe' => 'Online help',
    'REST-API & Swagger' => 'REST API & Swagger',
    'Cron & Automatisierung' => 'Cron & Automation',
    'Benutzer-Zugang' => 'User access',
    'Berechtigungen' => 'Permissions',

    // ── manual: Introduction ────────────────────────────────────────────────
    'Das <strong>M365 Tenant Tool</strong> ist ein webbasiertes Administrator-Dashboard für Microsoft 365. Es ermöglicht die zentrale Verwaltung und Überwachung eines M365-Tenants direkt über den Browser — ohne Microsoft Entra Admin Center oder PowerShell.'
        => 'The <strong>M365 Tenant Tool</strong> is a web-based admin dashboard for Microsoft 365. It enables central management and monitoring of an M365 tenant directly from the browser — without the Microsoft Entra admin center or PowerShell.',
    'Die Verbindung zu Microsoft Graph erfolgt über den <strong>OAuth 2.0 Client-Credentials-Flow</strong> (Anwendungs-Berechtigungen). Das Werkzeug benötigt keinen Benutzer-Login bei Microsoft — ein einmalig eingerichtetes App-Konto in Azure AD (Entra ID) reicht aus.'
        => 'The connection to Microsoft Graph uses the <strong>OAuth 2.0 client credentials flow</strong> (application permissions). The tool requires no user login with Microsoft — a one-time app account configured in Azure AD (Entra ID) is sufficient.',
    'Voraussetzungen' => 'Prerequisites',
    'Eine App-Registrierung in Microsoft Entra ID mit den erforderlichen Anwendungsberechtigungen'
        => 'An app registration in Microsoft Entra ID with the required application permissions',
    'Administrator-Zustimmung (Admin Consent) für alle API-Berechtigungen'
        => 'Administrator approval (admin consent) for all API permissions',
    'Die App muss über einen Browser erreichbar sein (lokales Netzwerk oder Internet)'
        => 'The app must be reachable via a browser (local network or internet)',
    'Rollen im Tool' => 'Roles in the tool',
    'vollständiger Zugriff inkl. Einstellungen, Updates, Offboarding, Wipe'
        => 'full access incl. settings, updates, offboarding, wipe',
    'alle Monitoring-Module lesen, Scans starten, Erinnerungen senden, Freigaben widerrufen; kein Zugriff auf Einstellungen/Updates'
        => 'read all monitoring modules, start scans, send reminders, revoke shares; no access to settings/updates',

    // ── manual: Navigation ──────────────────────────────────────────────────
    'Navigation & Bedienung' => 'Navigation & Operation',
    'Die linke Seitenleiste ist in thematische Bereiche unterteilt. Per Klick auf den'
        => 'The left sidebar is divided into thematic sections. Click the',
    '-Button oben links kann sie ein- und ausgeklappt werden. Operator-Accounts sehen den Administrationsbereich nicht.'
        => ' button at the top left to expand or collapse it. Operator accounts do not see the administration section.',
    'Schnellsuche (Strg+K)' => 'Quick search (Ctrl+K)',
    'Mit <kbd>Strg</kbd>+<kbd>K</kbd> (oder dem Lupensymbol in der Topbar) öffnet sich die Kommandopalette. Dort können alle Seiten des Tools per Tastatur gesucht und direkt angesprungen werden.'
        => 'Press <kbd>Ctrl</kbd>+<kbd>K</kbd> (or the magnifier icon in the top bar) to open the command palette. There you can search all pages of the tool by keyboard and jump straight to them.',
    'Daten aktualisieren' => 'Refreshing data',
    'Alle Daten werden aus der Microsoft Graph API geladen und serverseitig gecacht (Standard: 15 Minuten). Mit dem'
        => 'All data is loaded from the Microsoft Graph API and cached server-side (default: 15 minutes). Use the',
    '-Button in der Topbar (hängt <code>?refresh=1</code> an die URL) wird der Cache für die aktuelle Seite geleert und ein neuer Abruf gestartet.'
        => ' button in the top bar (which appends <code>?refresh=1</code> to the URL) to clear the cache for the current page and trigger a fresh fetch.',
    'Suche in Tabellen' => 'Searching in tables',
    'Auf den meisten Listenansichten gibt es ein Suchfeld über der Tabelle. Die Suche filtert live alle sichtbaren Zeilen nach dem eingegebenen Begriff.'
        => 'Most list views have a search box above the table. The search filters all visible rows live by the entered term.',
    'Viele Module bieten eine Export-Schaltfläche, die die aktuell geladenen Daten als CSV-Datei herunterlädt.'
        => 'Many modules provide an export button that downloads the currently loaded data as a CSV file.',

    // ── manual: Dashboard ───────────────────────────────────────────────────
    'Das Dashboard gibt auf einen Blick einen Überblick über den M365-Tenant:'
        => 'The dashboard provides an at-a-glance overview of the M365 tenant:',
    'Anzahl Benutzer, aktive/inaktive Konten, Gastbenutzer'
        => 'User count, active/inactive accounts, guest users',
    'Lizenzübersicht: verbrauchte vs. verfügbare Plätze'
        => 'License overview: consumed vs. available seats',
    'Sicherheitsampel: MFA-Abdeckung, Risiko-Anmeldungen, Defender-Alerts'
        => 'Security indicators: MFA coverage, risky sign-ins, Defender alerts',
    'Dienststatus: aktuelle Vorfälle in Microsoft 365-Diensten'
        => 'Service health: current incidents in Microsoft 365 services',
    'Schnellzugriff zu den wichtigsten Modulen'
        => 'Quick access to the most important modules',
    'Beim ersten Aufruf werden alle Daten frisch aus der API geholt und gecacht. Je nach Tenant-Größe kann dies einige Sekunden dauern.'
        => 'On first load, all data is fetched fresh from the API and cached. Depending on tenant size, this may take a few seconds.',

    // ── manual: Users ───────────────────────────────────────────────────────
    'Zeigt alle Benutzer des Tenants in einer durchsuchbaren und sortierbaren Tabelle. Spalten: Anzeigename, E-Mail, Status (aktiv/deaktiviert), Abteilung, Stelle, Lizenzen.'
        => 'Shows all users of the tenant in a searchable and sortable table. Columns: display name, email, status (active/disabled), department, job title, licenses.',
    'Aktionen auf der Detailseite' => 'Actions on the detail page',
    'Per Klick auf einen Benutzernamen öffnet sich die Detailseite mit:'
        => 'Clicking a user name opens the detail page with:',
    'Konto aktivieren / deaktivieren' => 'Enable / disable account',
    'Schaltet <code>accountEnabled</code> in Azure AD um. Deaktivierte Benutzer können sich nicht mehr anmelden, ihr Postfach und ihre Daten bleiben erhalten.'
        => 'Toggles <code>accountEnabled</code> in Azure AD. Disabled users can no longer sign in; their mailbox and data are retained.',
    'MFA zurücksetzen' => 'Reset MFA',
    'Löscht alle registrierten Authentifizierungsmethoden des Benutzers (Authenticator-App, Telefonnummern, FIDO2-Schlüssel) — außer dem Passwort. Der Benutzer muss beim nächsten Login eine neue Methode registrieren.'
        => 'Deletes all registered authentication methods of the user (authenticator app, phone numbers, FIDO2 keys) — except the password. The user must register a new method at the next login.',
    'Achtung:' => 'Caution:',
    'Diese Aktion kann nicht rückgängig gemacht werden. Der Benutzer muss anschließend den MFA-Registrierungsprozess erneut durchlaufen.'
        => 'This action cannot be undone. The user must then go through the MFA registration process again.',
    'Sign-in-Sessions widerrufen' => 'Revoke sign-in sessions',
    'Invalidiert alle aktiven Anmeldesitzungen des Benutzers (Refresh-Token-Revocation). Alle Geräte werden beim nächsten Aufruf zur Neuanmeldung aufgefordert.'
        => 'Invalidates all active sign-in sessions of the user (refresh token revocation). All devices will be prompted to sign in again on next use.',
    'Lizenz zuweisen / entfernen' => 'Assign / remove license',
    'Weist dem Benutzer eine SKU direkt zu oder entfernt sie. Die Lizenz muss im Tenant verfügbar sein (freie Plätze vorhanden).'
        => 'Assigns or removes a SKU directly for the user. The license must be available in the tenant (free seats present).',
    'Benutzer bearbeiten' => 'Edit user',
    'Ändert Profilfelder wie Anzeigename, Stellenbezeichnung, Abteilung, Telefon und Nutzungsstandort direkt über die Graph API.'
        => 'Changes profile fields such as display name, job title, department, phone and usage location directly via the Graph API.',
    'Anmeldeverlauf' => 'Sign-in history',
    'Zeigt die letzten 25 Anmeldeereignisse des Benutzers mit App, IP-Adresse, Standort, Status und Risikoinformationen.'
        => 'Shows the last 25 sign-in events of the user with app, IP address, location, status and risk information.',
    'Für den Anmeldeverlauf wird' => 'The sign-in history requires',
    'benötigt.' => '.',
    'Gruppenmitgliedschaften' => 'Group memberships',
    'Listet alle Gruppen, in denen der Benutzer Mitglied ist.'
        => 'Lists all groups the user is a member of.',
    'Offboarding-Assistent' => 'Offboarding wizard',
    'Der Offboarding-Assistent führt in einem Schritt mehrere Aktionen gleichzeitig durch:'
        => 'The offboarding wizard performs several actions at once in a single step:',
    'Konto deaktivieren' => 'Disable account',
    'MFA-Methoden zurücksetzen' => 'Reset MFA methods',
    'Alle Lizenzen entfernen' => 'Remove all licenses',
    'Aus allen Gruppen entfernen (außer On-Premises-synchronisierte Gruppen)'
        => 'Remove from all groups (except on-premises synchronized groups)',
    'Beim Offboarding werden die Gruppen-Mitgliedschaften dauerhaft entfernt. Diese müssen bei Bedarf manuell wiederhergestellt werden.'
        => 'During offboarding, group memberships are removed permanently. They must be restored manually if needed.',

    // ── manual: Guest users ─────────────────────────────────────────────────
    'Listet alle Gastbenutzer (<code>userType = Guest</code>) im Tenant. Gastbenutzer werden typischerweise für externe Kollaborationen (SharePoint, Teams) eingeladen.'
        => 'Lists all guest users (<code>userType = Guest</code>) in the tenant. Guest users are typically invited for external collaboration (SharePoint, Teams).',
    'In der Tabelle sind sichtbar: E-Mail, Einladungsstatus, letzte Anmeldung, Lizenzen, Erstelldatum.'
        => 'The table shows: email, invitation status, last sign-in, licenses, creation date.',
    'Deaktivieren' => 'Disable',
    'setzt <code>accountEnabled = false</code>, der Gast kann sich nicht mehr anmelden'
        => 'sets <code>accountEnabled = false</code>; the guest can no longer sign in',
    'Entfernen' => 'Remove',
    'löscht das Gastkonto vollständig aus dem Tenant'
        => 'deletes the guest account completely from the tenant',
    'Regelmäßige Bereinigung alter Gastkonten ist eine wichtige Sicherheitsmaßnahme. Das Modul „Inaktive Konten" hilft dabei, Gäste zu identifizieren, die sich lange nicht mehr angemeldet haben.'
        => 'Regular cleanup of old guest accounts is an important security measure. The "Inactive accounts" module helps identify guests who have not signed in for a long time.',

    // ── manual: Groups ──────────────────────────────────────────────────────
    'Zeigt alle Gruppen im Tenant: Microsoft 365-Gruppen, Sicherheitsgruppen und E-Mail-aktivierte Gruppen. Teams-aktivierte Gruppen sind entsprechend markiert.'
        => 'Shows all groups in the tenant: Microsoft 365 groups, security groups and mail-enabled groups. Teams-enabled groups are marked accordingly.',
    'Gruppendetails' => 'Group details',
    'Per Klick auf eine Gruppe öffnet sich die Detailansicht mit Mitgliedern, Eigentümern und Gruppentyp.'
        => 'Clicking a group opens the detail view with members, owners and group type.',
    'Mitglieder & Eigentümer' => 'Members & owners',
    'Mitglieder und Eigentümer können direkt hinzugefügt oder entfernt werden. Bei On-Premises-synchronisierten Gruppen ist dies nicht möglich (Änderungen müssen im lokalen Active Directory erfolgen).'
        => 'Members and owners can be added or removed directly. This is not possible for on-premises synchronized groups (changes must be made in the local Active Directory).',
    'Gruppe erstellen' => 'Create group',
    'Erstellt eine neue Microsoft 365-Gruppe oder Sicherheitsgruppe direkt im Tenant.'
        => 'Creates a new Microsoft 365 group or security group directly in the tenant.',
    'Gruppe löschen' => 'Delete group',
    'Löscht die Gruppe dauerhaft. Bei Microsoft 365-Gruppen werden auch das zugehörige Team, das Postfach und die SharePoint-Site gelöscht (Soft-Delete, 30 Tage wiederherstellbar im Entra Portal).'
        => 'Deletes the group permanently. For Microsoft 365 groups, the associated team, mailbox and SharePoint site are deleted as well (soft delete, recoverable for 30 days in the Entra portal).',
    'Inaktive Gruppen' => 'Inactive groups',
    'Unter <strong>Gruppen → Inaktive Gruppen</strong> werden Microsoft 365-Gruppen angezeigt, die seit mehr als 90 Tagen keine Aktivität hatten. Grundlage ist die letzte Aktivität der zugehörigen SharePoint-Site.'
        => 'Under <strong>Groups → Inactive groups</strong>, Microsoft 365 groups with no activity for more than 90 days are shown. The basis is the last activity of the associated SharePoint site.',

    // ── manual: Licenses ────────────────────────────────────────────────────
    'Zeigt alle abonnierten Lizenz-SKUs im Tenant mit verbrauchten und verfügbaren Plätzen sowie den enthaltenen Service-Plänen.'
        => 'Shows all subscribed license SKUs in the tenant with consumed and available seats as well as the included service plans.',
    'Ablaufende Lizenzen' => 'Expiring licenses',
    'Unter <strong>Lizenzen → Ablauf</strong> werden alle Abonnements angezeigt, die in den nächsten 90 Tagen ablaufen. Dies ermöglicht eine frühzeitige Verlängerungsplanung.'
        => 'Under <strong>Licenses → Expiry</strong>, all subscriptions expiring within the next 90 days are shown. This enables early renewal planning.',
    'Der CSV-Export enthält alle Lizenzen mit Ablaufdatum, Verbrauch und Kosten-relevanten Infos — nützlich für Budgetplanung.'
        => 'The CSV export contains all licenses with expiry date, consumption and cost-relevant info — useful for budget planning.',

    // ── manual: License advisor ─────────────────────────────────────────────
    'Analysiert die Lizenznutzung und identifiziert Benutzer, die lizenzierte Dienste nicht aktiv nutzen (potenzielle Einsparungen).'
        => 'Analyzes license usage and identifies users who do not actively use licensed services (potential savings).',
    'In den Einstellungen können Kriterien konfiguriert werden, welche Dienste für eine „sinnvolle" Lizenz als nötig gelten (Exchange Online, Teams, SharePoint, OneDrive, Office Desktop, Intune).'
        => 'In the settings you can configure criteria for which services are considered necessary for a "meaningful" license (Exchange Online, Teams, SharePoint, OneDrive, Office Desktop, Intune).',
    'Das Modul zeigt dann Benutzer, die lizenziert sind, aber mindestens einen der konfigurierten Dienste nicht nutzen — mit der Möglichkeit zum CSV-Export für die Entscheidungsfindung.'
        => 'The module then shows users who are licensed but do not use at least one of the configured services — with the option of a CSV export for decision-making.',

    // ── manual: MFA methods ─────────────────────────────────────────────────
    'Zeigt für jeden Benutzer, welche Authentifizierungsmethoden registriert sind: Microsoft Authenticator, SMS/Anruf, FIDO2-Schlüssel, softwarebasierter TOTP-Token u.a.'
        => 'Shows for each user which authentication methods are registered: Microsoft Authenticator, SMS/call, FIDO2 keys, software-based TOTP token and others.',
    'Benutzer ohne MFA-Registrierung sind deutlich markiert und können gefiltert angezeigt werden. Der CSV-Export eignet sich für Compliance-Berichte.'
        => 'Users without MFA registration are clearly marked and can be shown filtered. The CSV export is suitable for compliance reports.',
    'Verwende den Filter „Kein MFA", um schnell alle Konten ohne zweiten Faktor zu identifizieren, und leite dann entsprechende Maßnahmen ein.'
        => 'Use the "No MFA" filter to quickly identify all accounts without a second factor, then take appropriate action.',

    // ── manual: Password expiry ─────────────────────────────────────────────
    'Zeigt Benutzer, deren Passwort abgelaufen ist oder bald abläuft — basierend auf der konfigurierten Passwort-Ablauf-Richtlinie (Standard: 90 Tage).'
        => 'Shows users whose password has expired or expires soon — based on the configured password expiry policy (default: 90 days).',
    'Die Ansicht ist in Kategorien unterteilt: Abgelaufen, Kritisch (weniger als 7 Tage), Warnung (weniger als 30 Tage) und Alle.'
        => 'The view is divided into categories: Expired, Critical (less than 7 days), Warning (less than 30 days) and All.',
    'Benutzer mit aktiviertem „Passwort läuft nie ab" werden entsprechend markiert.'
        => 'Users with "Password never expires" enabled are marked accordingly.',

    // ── manual: OneDrive ────────────────────────────────────────────────────
    'Zeigt die OneDrive-Nutzung aller Benutzer: verwendeter und verfügbarer Speicher, Anzahl der Dateien, letzte Aktivität.'
        => 'Shows OneDrive usage of all users: used and available storage, number of files, last activity.',
    'Der Bericht basiert auf dem Microsoft 365 Nutzungsbericht (<code>/reports/oneDriveUsageAccountDetail</code>) und wird täglich von Microsoft aktualisiert — die Daten können daher bis zu 48 Stunden alt sein.'
        => 'The report is based on the Microsoft 365 usage report (<code>/reports/oneDriveUsageAccountDetail</code>) and is updated daily by Microsoft — the data may therefore be up to 48 hours old.',

    // ── manual: SharePoint ──────────────────────────────────────────────────
    'Listet alle SharePoint-Sites im Tenant mit URL, Typ (Kommunikationssite / Teamsite), Speichernutzung und letzter Aktivität.'
        => 'Lists all SharePoint sites in the tenant with URL, type (communication site / team site), storage usage and last activity.',
    'Per Klick auf eine Site öffnet sich die Detailansicht mit den zugehörigen Dokumentbibliotheken.'
        => 'Clicking a site opens the detail view with the associated document libraries.',

    // ── manual: Sharing ─────────────────────────────────────────────────────
    'Zeigt alle externen Freigaben (Sharing-Links) im Tenant — Dateien und Ordner, die per Link nach außen geteilt wurden.'
        => 'Shows all external shares (sharing links) in the tenant — files and folders that have been shared externally via link.',
    'Für jede Freigabe sind sichtbar: Dateiname, SharePoint-Site, Freigabe-Typ (Anonym, Org, Spezifisch), Ersteller, Ablaufdatum.'
        => 'For each share you can see: file name, SharePoint site, share type (Anonymous, Org, Specific), creator, expiry date.',
    'Freigabe widerrufen' => 'Revoke share',
    'Einzelne Freigabe-Links können direkt aus der Tabelle heraus widerrufen werden. Dies entfernt den Link, die Datei bleibt erhalten.'
        => 'Individual sharing links can be revoked directly from the table. This removes the link; the file is retained.',
    'Das Widerrufen einer anonymen Freigabe deaktiviert den Link sofort — alle Personen, die über diesen Link zugegriffen haben, verlieren den Zugriff.'
        => 'Revoking an anonymous share disables the link immediately — everyone who accessed it via this link loses access.',

    // ── manual: Sharing monitor ─────────────────────────────────────────────
    'Der Freigaben-Monitor ermöglicht es, Benutzer regelmäßig per E-Mail zu ihren externen Freigaben zu befragen — sie können direkt aus der E-Mail heraus Freigaben bestätigen oder widerrufen.'
        => 'The sharing monitor lets you regularly survey users by email about their external shares — they can confirm or revoke shares directly from the email.',
    'Wie es funktioniert' => 'How it works',
    'Der Cron-Job scannt täglich alle externen Freigaben'
        => 'The cron job scans all external shares daily',
    'Benutzer erhalten eine E-Mail mit einer Liste ihrer aktiven Freigaben'
        => 'Users receive an email with a list of their active shares',
    'Sie können jede Freigabe per Klick bestätigen oder widerrufen'
        => 'They can confirm or revoke each share with a click',
    'Nicht reagierte Freigaben werden nach dem konfigurierten Zeitraum automatisch widerrufen'
        => 'Shares with no response are revoked automatically after the configured period',
    'Konfiguration' => 'Configuration',
    'Im Einstellungsbereich (<strong>Freigaben-Monitor</strong>): Review-Intervall (Standard: 30 Tage), Kulanzfrist (Standard: 7 Tage), nur anonyme Freigaben prüfen.'
        => 'In the settings area (<strong>Sharing monitor</strong>): review interval (default: 30 days), grace period (default: 7 days), check only anonymous shares.',
    'Admin-Ansicht' => 'Admin view',
    'Die Admin-Ansicht zeigt alle aktiven Review-Anfragen mit Status. Von hier aus können Freigaben auch manuell widerrufen oder Erinnerungs-E-Mails versendet werden.'
        => 'The admin view shows all active review requests with status. From here, shares can also be revoked manually or reminder emails sent.',

    // ── manual: Sharing policies ────────────────────────────────────────────
    'Verwaltet die tenant-weiten Freigabeeinstellungen für SharePoint und OneDrive:'
        => 'Manages the tenant-wide sharing settings for SharePoint and OneDrive:',
    'Tenant-Ebene' => 'Tenant level',
    'Maximale erlaubte Freigabe-Stufe (Anonym, Org, Org+Anonym deaktiviert, Nur eingeladene Benutzer)'
        => 'Maximum allowed sharing level (Anonymous, Org, Org+Anonymous disabled, Invited users only)',
    'Site-Ebene' => 'Site level',
    'Freigabe-Einstellung für eine einzelne SharePoint-Site (kann restriktiver sein als die Tenant-Einstellung, aber nicht freizügiger)'
        => 'Sharing setting for a single SharePoint site (can be more restrictive than the tenant setting, but not more permissive)',
    'Als Best Practice empfiehlt sich die Tenant-Einstellung auf „Nur authentifizierte Benutzer" und für einzelne Sites bei Bedarf spezifisch zu lockern.'
        => 'As a best practice, set the tenant setting to "Authenticated users only" and loosen it specifically for individual sites where needed.',

    // ── manual: Mailboxes ───────────────────────────────────────────────────
    'Zeigt alle Exchange Online-Postfächer (Benutzer, Shared, Room, Equipment) mit Größe, Quota, letzter Aktivität und Weiterleitungseinstellungen.'
        => 'Shows all Exchange Online mailboxes (user, shared, room, equipment) with size, quota, last activity and forwarding settings.',
    'Postfach-Detailseite' => 'Mailbox detail page',
    'Weiterleitung einrichten' => 'Set up forwarding',
    'Konfiguriert <code>ForwardingSmtpAddress</code> — E-Mails werden an eine externe oder interne Adresse weitergeleitet (optional: Kopie im Postfach behalten)'
        => 'Configures <code>ForwardingSmtpAddress</code> — emails are forwarded to an external or internal address (optionally: keep a copy in the mailbox)',
    'Abwesenheitsnachricht' => 'Out-of-office message',
    'Setzt die AutoReply-Konfiguration für intern und extern'
        => 'Sets the auto-reply configuration for internal and external',
    'Externe Weiterleitungen' => 'External forwarding',
    'Unter <strong>Postfächer → Externe Weiterleitungen</strong> werden alle Postfächer angezeigt, die E-Mails an externe Adressen weiterleiten. Dies ist ein wichtiger Sicherheitscheck gegen Mail-Exfiltration.'
        => 'Under <strong>Mailboxes → External forwarding</strong>, all mailboxes that forward emails to external addresses are shown. This is an important security check against mail exfiltration.',
    'Von dieser Ansicht aus können Weiterleitungen direkt deaktiviert werden.'
        => 'From this view, forwarding can be disabled directly.',
    'Listet alle freigegebenen Postfächer mit Mitglieder-Übersicht. Neue Shared Mailboxes können direkt erstellt werden.'
        => 'Lists all shared mailboxes with a member overview. New shared mailboxes can be created directly.',
    'Shared Mailboxes bis 50 GB benötigen in der Regel keine separate Lizenz — sie werden automatisch mit Exchange Online-Postfach-Merkmalen versehen.'
        => 'Shared mailboxes up to 50 GB usually do not require a separate license — they are automatically provisioned with Exchange Online mailbox characteristics.',

    // ── manual: Teams usage ─────────────────────────────────────────────────
    'Zeigt die Teams-Aktivität aller Benutzer: Nachrichten gesendet, Anrufe, Meetings, Reaktionen — basierend auf den Microsoft 365-Nutzungsberichten der letzten 30 Tage.'
        => 'Shows the Teams activity of all users: messages sent, calls, meetings, reactions — based on the Microsoft 365 usage reports of the last 30 days.',
    'Nützlich um zu erkennen, welche Benutzer Teams kaum nutzen (und ob eine Teams-Lizenz gerechtfertigt ist).'
        => 'Useful for identifying which users barely use Teams (and whether a Teams license is justified).',

    // ── manual: Adoption report ─────────────────────────────────────────────
    'Gibt einen aggregierten Überblick über die Nutzung der M365-Dienste im Tenant: aktive Benutzer je Dienst (Exchange, SharePoint, OneDrive, Teams, Yammer) über verschiedene Zeiträume (7, 30, 90, 180 Tage).'
        => 'Provides an aggregated overview of the usage of M365 services in the tenant: active users per service (Exchange, SharePoint, OneDrive, Teams, Yammer) across various periods (7, 30, 90, 180 days).',
    'Enthält Diagramme zur zeitlichen Entwicklung der Nutzungszahlen.'
        => 'Includes charts of the usage figures over time.',

    // ── manual: Message Center ──────────────────────────────────────────────
    'Zeigt die aktuellen Nachrichten aus dem Microsoft 365 Message Center — Ankündigungen zu geplanten Änderungen, neuen Features und Wartungsarbeiten.'
        => 'Shows the current messages from the Microsoft 365 Message Center — announcements about planned changes, new features and maintenance.',
    'Nachrichten sind nach Kategorie und Schweregrad gefiltert darstellbar. Wichtige Änderungen, die Administrative Maßnahmen erfordern, sind hervorgehoben.'
        => 'Messages can be displayed filtered by category and severity. Important changes requiring administrative action are highlighted.',

    // ── manual: Mail flow & protection ──────────────────────────────────────
    'Fasst die wichtigsten Exchange Online Sicherheits- und Mail-Flow-Konfigurationen zusammen:'
        => 'Summarizes the most important Exchange Online security and mail flow configurations:',
    'Anti-Spam-Richtlinien' => 'Anti-spam policies',
    'Konfiguration der Spam-Filter-Einstellungen' => 'Configuration of the spam filter settings',
    'Anti-Malware-Richtlinien' => 'Anti-malware policies',
    'Malware-Erkennungseinstellungen' => 'Malware detection settings',
    'Anti-Phishing-Richtlinien' => 'Anti-phishing policies',
    'Schutz vor Phishing und Spoofing' => 'Protection against phishing and spoofing',
    'Defender for Office 365-Richtlinien (wenn lizenziert)' => 'Defender for Office 365 policies (if licensed)',
    'Eingehende und ausgehende Mail-Flow-Konnektoren' => 'Inbound and outbound mail flow connectors',
    'Transport-Regeln' => 'Transport rules',
    'Übersicht über aktive Mail-Flow-Regeln' => 'Overview of active mail flow rules',

    // ── manual: Service health ──────────────────────────────────────────────
    'Zeigt den aktuellen Status aller Microsoft 365-Dienste (Exchange, SharePoint, Teams, Entra ID, Intune usw.).'
        => 'Shows the current status of all Microsoft 365 services (Exchange, SharePoint, Teams, Entra ID, Intune etc.).',
    'Aktive Vorfälle und Wartungsfenster werden mit Details zum Fortschritt und der voraussichtlichen Behebungszeit angezeigt. Vergangene Vorfälle der letzten 7 Tage sind in einer separaten Liste sichtbar.'
        => 'Active incidents and maintenance windows are shown with details on progress and the estimated time to resolution. Past incidents from the last 7 days are visible in a separate list.',

    // ── manual: Security (CA) ───────────────────────────────────────────────
    'Sicherheit (Conditional Access)' => 'Security (Conditional Access)',
    'Listet alle Richtlinien für bedingten Zugriff (Conditional Access Policies) im Tenant mit Status (aktiviert, deaktiviert, Berichtsmodus), Bedingungen und Zugriffsteuerungen.'
        => 'Lists all conditional access policies in the tenant with status (enabled, disabled, report-only), conditions and access controls.',
    'Richtlinien ein-/ausschalten' => 'Enable/disable policies',
    'CA-Richtlinien können direkt im Tool aktiviert oder deaktiviert werden (Berichtsmodus ist ebenfalls möglich). Dies erfordert erhöhte Berechtigungen.'
        => 'CA policies can be enabled or disabled directly in the tool (report-only mode is also possible). This requires elevated permissions.',
    'Vorsicht:' => 'Caution:',
    'Das Deaktivieren einer aktiven CA-Richtlinie kann die Sicherheit des Tenants erheblich reduzieren. Änderungen sollten sorgfältig geplant werden.'
        => 'Disabling an active CA policy can significantly reduce the security of the tenant. Changes should be planned carefully.',

    // ── manual: Security Posture ────────────────────────────────────────────
    'Gibt eine aggregierte Sicherheitsübersicht des Tenants: MFA-Abdeckung, Risiko-Benutzer, Geräte-Compliance, aktive CA-Richtlinien, externe Freigaben und weitere Sicherheitsindikatoren.'
        => 'Provides an aggregated security overview of the tenant: MFA coverage, risky users, device compliance, active CA policies, external shares and further security indicators.',
    'Jeder Indikator ist mit einer Ampel (grün/gelb/rot) bewertet und mit dem zugehörigen Modul verlinkt, um direkt handeln zu können.'
        => 'Each indicator is rated with a traffic light (green/yellow/red) and linked to the relevant module so you can act directly.',

    // ── manual: Secure Score ────────────────────────────────────────────────
    'Zeigt den Microsoft Secure Score des Tenants — eine Bewertung der Sicherheitskonfiguration auf einer Skala von 0–100 — sowie den Verlauf über die letzten 30 Tage.'
        => 'Shows the tenant\'s Microsoft Secure Score — a rating of the security configuration on a scale of 0–100 — as well as the trend over the last 30 days.',
    'Die einzelnen Verbesserungsmaßnahmen (Control Scores) werden mit ihrer Punktzahl und dem Implementierungsstatus aufgelistet. Direkt-Links ins Microsoft 365 Defender Portal ermöglichen die schnelle Umsetzung.'
        => 'The individual improvement actions (control scores) are listed with their points and implementation status. Direct links to the Microsoft 365 Defender portal enable quick implementation.',

    // ── manual: Defender Alerts ─────────────────────────────────────────────
    'Zeigt offene Sicherheitswarnungen aus Microsoft Defender for Endpoint, Defender for Office 365 und Microsoft Sentinel.'
        => 'Shows open security alerts from Microsoft Defender for Endpoint, Defender for Office 365 and Microsoft Sentinel.',
    'Für jeden Alert sind sichtbar: Titel, Schweregrad (Hoch/Mittel/Niedrig/Informativ), betroffene Entität, Status und Erstellzeit.'
        => 'For each alert you can see: title, severity (High/Medium/Low/Informational), affected entity, status and creation time.',
    'Alert auflösen' => 'Resolve alert',
    'Alerts können direkt im Tool als „Gelöst" markiert werden. Dies schließt den Alert in Microsoft Defender.'
        => 'Alerts can be marked as "Resolved" directly in the tool. This closes the alert in Microsoft Defender.',

    // ── manual: Risky sign-ins ──────────────────────────────────────────────
    'Zeigt Benutzer, denen Microsoft Entra ID Protection ein erhöhtes Anmelderisiko zugewiesen hat — z.B. durch ungewöhnliche Anmeldeorte, kompromittierte Zugangsdaten (Credential-Leak-Erkennung) oder anomales Verhalten.'
        => 'Shows users to whom Microsoft Entra ID Protection has assigned an elevated sign-in risk — e.g. due to unusual sign-in locations, compromised credentials (credential leak detection) or anomalous behavior.',
    'Als kompromittiert bestätigen' => 'Confirm as compromised',
    'Markiert das Benutzerkonto als kompromittiert, erzwingt Passwort-Reset und blockiert laufende Sessions'
        => 'Marks the user account as compromised, forces a password reset and blocks ongoing sessions',
    'Risiko verwerfen' => 'Dismiss risk',
    'Verwirft den Risikohinweis (wenn es sich um ein False Positive handelt)'
        => 'Dismisses the risk indication (if it is a false positive)',
    'Dieses Modul erfordert Microsoft Entra ID P2 (oder Microsoft 365 E5) im Tenant.'
        => 'This module requires Microsoft Entra ID P2 (or Microsoft 365 E5) in the tenant.',

    // ── manual: App registrations ───────────────────────────────────────────
    'App-Registrierungen & Enterprise Apps' => 'App registrations & enterprise apps',
    'Zeigt alle App-Registrierungen und Enterprise-Anwendungen im Tenant mit ihren API-Berechtigungen, Client-Secrets und Zertifikaten.'
        => 'Shows all app registrations and enterprise applications in the tenant with their API permissions, client secrets and certificates.',
    'Client-Secrets verwalten' => 'Manage client secrets',
    'Auf der Detailseite einer App-Registrierung können neue Client-Secrets erstellt und vorhandene gelöscht werden. Ablaufende Secrets sind farblich markiert.'
        => 'On the detail page of an app registration, new client secrets can be created and existing ones deleted. Expiring secrets are color-coded.',
    'Secrets, die in weniger als 30 Tagen ablaufen, werden orange markiert. Abgelaufene Secrets werden rot hervorgehoben. Prüfe regelmäßig, ob Produktivsysteme davon betroffen sind.'
        => 'Secrets expiring in less than 30 days are marked orange. Expired secrets are highlighted in red. Check regularly whether production systems are affected.',

    // ── manual: Admin roles ─────────────────────────────────────────────────
    'Zeigt alle Microsoft Entra-Administratorrollen und die jeweils zugewiesenen Benutzer. Privilegierte Rollen (Globaler Administrator, Privilegierter Rollenverwaltungsadministrator usw.) sind besonders hervorgehoben.'
        => 'Shows all Microsoft Entra administrator roles and the users assigned to each. Privileged roles (Global Administrator, Privileged Role Administrator etc.) are specially highlighted.',
    'Rollen zuweisen / entfernen' => 'Assign / remove roles',
    'Benutzer können direkt einer Rolle zugewiesen oder aus einer Rolle entfernt werden. Die Zuweisung erfolgt als permanente direkte Zuweisung (kein PIM).'
        => 'Users can be assigned to a role or removed from a role directly. The assignment is a permanent direct assignment (no PIM).',
    'Administratorrollen-Änderungen sind sicherheitskritisch. Die Vergabe der Rolle „Globaler Administrator" sollte auf das absolute Minimum beschränkt werden.'
        => 'Administrator role changes are security-critical. Assignment of the "Global Administrator" role should be limited to the absolute minimum.',

    // ── manual: GDPR status ─────────────────────────────────────────────────
    'Eigene Kategorie innerhalb der Security Posture mit acht spezifischen DSGVO-Checks. Pro Check stehen das geprüfte Tenant-Setting und die relevanten DSGVO-Artikel.'
        => 'A dedicated category within the Security Posture with eight specific GDPR checks. Each check shows the tenant setting examined and the relevant GDPR articles.',
    'Was geprüft wird' => 'What is checked',
    'Tenant-Region in EU/EWR' => 'Tenant region in EU/EEA',
    'der Datacenter-Standort entscheidet, ob die Verarbeitung primär unter Art. 6 (Rechtmäßigkeit) oder Art. 44–49 (Drittlandtransfer) fällt.'
        => 'the data center location determines whether processing falls primarily under Art. 6 (lawfulness) or Art. 44–49 (third-country transfer).',
    'SharePoint External Sharing restriktiv' => 'SharePoint external sharing restrictive',
    'die Tenant-Sharing-Capability darf für DSGVO-konforme Defaults nicht „Anyone-Links" als Default-Wert haben (Art. 25 Privacy by Default).'
        => 'for GDPR-compliant defaults, the tenant sharing capability must not have "Anyone links" as the default value (Art. 25 privacy by default).',
    'Anonyme Freigabe-Links laufen ab' => 'Anonymous sharing links expire',
    'Speicherbegrenzung Art. 5 Abs. 1e: ohne Ablaufdatum bleibt der Link unbegrenzt nutzbar.'
        => 'storage limitation Art. 5(1)(e): without an expiry date the link remains usable indefinitely.',
    'Standard-Freigabetyp ist intern' => 'Default sharing type is internal',
    'Default-Linktyp sollte nicht Anyone sein.' => 'the default link type should not be Anyone.',
    'Sensitivity Labels veröffentlicht' => 'Sensitivity labels published',
    'Voraussetzung für Information Protection (Art. 32 Maßnahmen zur Datenintegrität).'
        => 'prerequisite for Information Protection (Art. 32 measures for data integrity).',
    'Aufbewahrungs-/eDiscovery-Fälle aktiv' => 'Retention/eDiscovery cases active',
    'Voraussetzung für Speicherbegrenzung und Lösch­pflichten (Art. 17).'
        => 'prerequisite for storage limitation and erasure obligations (Art. 17).',
    'Audit-Log aktiv & abrufbar' => 'Audit log active & retrievable',
    'Rechenschafts­pflicht Art. 5 Abs. 2 und Art. 32.' => 'accountability Art. 5(2) and Art. 32.',
    'DLP-/Label-Schutz für personenbezogene Daten' => 'DLP/label protection for personal data',
    'mindestens eine aktive Information-Protection-Maßnahme.' => 'at least one active information protection measure.',
    'Direkt-Link:' => 'Direct link:',

    // ── manual: Tenant hardening ────────────────────────────────────────────
    'Tenant-Härtung (Quick-Actions)' => 'Tenant hardening (quick actions)',
    'Eine kuratierte Seite mit den wichtigsten Sicherheits-Einstellungen, die mit einem Klick aktiviert werden können — entweder direkt über die Graph API oder per Deep-Link in das richtige Admin-Center, wenn Microsoft den Endpunkt nicht öffentlich gemacht hat.'
        => 'A curated page with the most important security settings that can be enabled with one click — either directly via the Graph API or via deep link into the right admin center where Microsoft has not exposed the endpoint.',
    'Direkt schaltbar (via Graph API)' => 'Directly switchable (via Graph API)',
    'ein/aus (PATCH <code>/policies/identitySecurityDefaultsEnforcementPolicy</code>)'
        => 'on/off (PATCH <code>/policies/identitySecurityDefaultsEnforcementPolicy</code>)',
    'Anyone-Links global blocken oder einschränken' => 'block or restrict Anyone links globally',
    'Anonyme Link-Ablauffrist' => 'Anonymous link expiry period',
    'z. B. auf 30 Tage setzen' => 'e.g. set to 30 days',
    'auf „intern" zwingen' => 'force to "internal"',
    'mit einem Klick anlegen' => 'create with one click',
    'Template, das nach Bestätigung im Report-Only-Modus angelegt wird'
        => 'template that is created in report-only mode after confirmation',
    'Authorization-Policy / Out­bound-Spam' => 'authorization policy / outbound spam',
    'App-Consent einschränken' => 'Restrict app consent',
    'User-Consent auf „nur für verifizierte Publisher mit Low-Risk-Permissions"'
        => 'user consent to "only for verified publishers with low-risk permissions"',
    'nur Admins dürfen einladen' => 'only admins may invite',
    'Per Deep-Link ins Admin-Center' => 'Via deep link to the admin center',
    'Wo Graph keinen Schreib-Endpunkt anbietet (z. B. Audit-Log-Aktivierung, Defender-for-Office-Policies, Microsoft-Purview-DLP-Erstellung), öffnet der Button direkt die entsprechende Microsoft-Konsole.'
        => 'Where Graph does not offer a write endpoint (e.g. audit log activation, Defender for Office policies, Microsoft Purview DLP creation), the button opens the corresponding Microsoft console directly.',
    'Jede Aktion zeigt vor dem Ausführen den aktuellen Zustand, eine Erklärung des Effekts und eine BSI/NIS-2/DSGVO-Begründung.'
        => 'Before execution, each action shows the current state, an explanation of the effect and a BSI/NIS-2/GDPR justification.',

    // ── manual: PIM ─────────────────────────────────────────────────────────
    'PIM — Just-in-Time-Admin' => 'PIM — Just-in-Time admin',
    'Übersicht über das Microsoft Entra Privileged Identity Management. Statt dauerhafter Admin-Zuweisungen sollen Administratoren als „eligible" konfiguriert sein und ihre Rolle nur bei Bedarf für eine begrenzte Zeit aktivieren — mit MFA und Begründung. Das ist die Empfehlung aus BSI IT-Grundschutz ORP.4.A23 und NIS-2 Art. 21(j).'
        => 'Overview of Microsoft Entra Privileged Identity Management. Instead of permanent admin assignments, administrators should be configured as "eligible" and activate their role only when needed for a limited time — with MFA and justification. This is the recommendation from BSI IT-Grundschutz ORP.4.A23 and NIS-2 Art. 21(j).',
    'Was die Seite zeigt' => 'What the page shows',
    'Aktiv erhöht' => 'Currently elevated',
    'wer gerade eine Privileged-Role hat (entweder JIT-aktiviert oder dauerhaft zugewiesen).'
        => 'who currently holds a privileged role (either JIT-activated or permanently assigned).',
    'wer eine Rolle aktivieren kann, sie aber gerade nicht nutzt.'
        => 'who can activate a role but is not currently using it.',
    'Dauerhafte Admins' => 'Permanent admins',
    'als Zahl mit Schwellwert ≤ 2 (rot, wenn überschritten — solche Konten sollten zu Eligible umgestellt werden).'
        => 'as a number with a threshold of ≤ 2 (red if exceeded — such accounts should be switched to eligible).',
    'Aktivierungen der letzten 30 Tage' => 'Activations in the last 30 days',
    'Audit-Trail: wer hat wann welche Rolle aktiviert, mit Erfolg/Misserfolg.'
        => 'audit trail: who activated which role and when, with success/failure.',
    'Keine dauerhaften Global-Administrator-Zuweisungen.' => 'No permanent Global Administrator assignments.',
    'Maximale Aktivierungs­dauer 8 Stunden, mit MFA-Pflicht.' => 'Maximum activation duration of 8 hours, with mandatory MFA.',
    'Approval-Workflow für besonders kritische Rollen (z. B. „Privileged Role Administrator").'
        => 'Approval workflow for especially critical roles (e.g. "Privileged Role Administrator").',
    'Audit-Trail mindestens 90 Tage aufbewahren.' => 'Retain the audit trail for at least 90 days.',

    // ── manual: Break-glass accounts ────────────────────────────────────────
    'Notfall-Administratorkonten sind die letzte Eskalationsstufe, wenn alle anderen Admin-Wege versagen — etwa wenn eine fehlerhafte Conditional-Access-Policy alle anderen Admins aussperrt, oder bei einem MFA-Ausfall. Microsoft empfiehlt mindestens <strong>zwei</strong> solcher Konten.'
        => 'Emergency administrator accounts are the last escalation level when all other admin paths fail — for example when a faulty conditional access policy locks out all other admins, or during an MFA outage. Microsoft recommends at least <strong>two</strong> such accounts.',
    'Im Tool werden die UPNs der Break-Glass-Konten als Liste hinterlegt (kommagetrennt oder ein UPN pro Zeile). Für jeden Eintrag prüft das Tool automatisch:'
        => 'In the tool, the UPNs of the break-glass accounts are stored as a list (comma-separated or one UPN per line). For each entry, the tool automatically checks:',
    'Existiert das Konto im Tenant?' => 'Does the account exist in the tenant?',
    'Wenn nicht → kritisches Issue.' => 'If not → critical issue.',
    'Ist es aktiviert?' => 'Is it enabled?',
    'Deaktivierte Notfall­konten sind unbrauchbar.' => 'Disabled emergency accounts are useless.',
    'Ist es dauerhaft als Global Administrator zugewiesen?' => 'Is it permanently assigned as Global Administrator?',
    'PIM-Eligible reicht nicht — eine Aktivierung verlangt MFA, das im Notfall vielleicht nicht funktioniert.'
        => 'PIM-eligible is not enough — activation requires MFA, which may not work in an emergency.',
    'Hat das Konto eine MFA-Methode registriert?' => 'Has the account registered an MFA method?',
    'Empfohlen ist ein FIDO2-Hardware-Key, der im Tresor liegt.' => 'A FIDO2 hardware key kept in the vault is recommended.',
    'Aus welchen CA-Policies ist es ausgeschlossen?' => 'Which CA policies is it excluded from?',
    'Wenn aus keiner — Sperre droht. Wenn aus allen — Risiko bei kompromittiertem Passwort.'
        => 'If from none — risk of lockout. If from all — risk if the password is compromised.',
    'Wann war der letzte Login?' => 'When was the last login?',
    'Break-Glass-Konten sollten mindestens halbjährlich getestet werden, sonst weiß niemand, ob sie im Notfall funktionieren.'
        => 'Break-glass accounts should be tested at least every six months, otherwise no one knows whether they work in an emergency.',
    'Microsoft empfiehlt für Break-Glass-Konten <strong>reine Cloud-Identitäten</strong> (nicht aus AD synchronisiert), <strong>komplexe Passwörter</strong> (mindestens 16 Zeichen, im Tresor verwahrt), und eine <strong>physische Ablage</strong> der Recovery-Methode (FIDO2-Key in zwei Standorten).'
        => 'For break-glass accounts, Microsoft recommends <strong>cloud-only identities</strong> (not synced from AD), <strong>complex passwords</strong> (at least 16 characters, kept in the vault), and <strong>physical storage</strong> of the recovery method (FIDO2 key in two locations).',

    // ── manual: Auto-forward audit ──────────────────────────────────────────
    'Scannt alle aktiven Mailboxen im Tenant nach Inbox-Regeln, die eingehende E-Mails automatisch an eine externe Adresse weiterleiten. <strong>Auto-Forward an externe Domains ist statistisch der häufigste Exfiltrations­vektor</strong> bei kompromittierten Konten: der Angreifer richtet eine versteckte Inbox-Regel ein, die alle eingehenden Mails an seine Adresse weiterleitet, oft Tage bevor der Account-Inhaber es bemerkt.'
        => 'Scans all active mailboxes in the tenant for inbox rules that automatically forward incoming emails to an external address. <strong>Auto-forwarding to external domains is statistically the most common exfiltration vector</strong> for compromised accounts: the attacker sets up a hidden inbox rule that forwards all incoming mail to their address, often days before the account owner notices.',
    'Externe Auto-Forwards' => 'External auto-forwards',
    'rot markiert. Pro Treffer: Benutzer, Regel-Name, Ziel-Adresse, Active/Inactive.'
        => 'marked red. Per hit: user, rule name, destination address, Active/Inactive.',
    'Interne Auto-Forwards' => 'Internal auto-forwards',
    'informativ (innerhalb der eigenen Domains).' => 'informational (within your own domains).',
    'Lösch-Regeln' => 'Delete rules',
    'verdächtig in Kombination mit Phishing-Hijacks: ein Angreifer löscht automatisch alle Antworten und Sicherheits-Benachrichtigungen, damit der echte User nichts merkt.'
        => 'suspicious in combination with phishing hijacks: an attacker automatically deletes all replies and security notifications so the real user notices nothing.',
    'Wie reagieren' => 'How to respond',
    'Bei verdächtiger externer Weiterleitung: User-Konto sperren, Sessions revoken, Passwort-Reset erzwingen, Defender-Investigation öffnen.'
        => 'On suspicious external forwarding: block the user account, revoke sessions, force a password reset, open a Defender investigation.',
    'Tenant-weit blockieren: Mail-Flow-Regel oder Exchange-Anti-Spam-Outbound-Policy mit <code>AutoForwardingMode = Off</code>.'
        => 'Block tenant-wide: mail flow rule or Exchange anti-spam outbound policy with <code>AutoForwardingMode = Off</code>.',
    'Performance-Hinweis: der Scan dauert je nach Tenant-Größe 30 Sekunden bis 5 Minuten. Ergebnisse werden 30 Min. gecached; per <code>?refresh=1</code> erzwingbar.'
        => 'Performance note: depending on tenant size, the scan takes 30 seconds to 5 minutes. Results are cached for 30 min; can be forced via <code>?refresh=1</code>.',

    // ── manual: OAuth app audit ─────────────────────────────────────────────
    'Inventur aller Enterprise Apps (Service Principals) im Tenant mit Risiko-Bewertung. OAuth-Apps mit hohen Berechtigungen sind seit 2023 einer der Top-Vektoren für Tenant-Übernahme — typischerweise nach Migrationen, gekündigten 3rd-Party-Tools oder Phishing-Angriffen mit Illicit-Consent-Grant.'
        => 'Inventory of all enterprise apps (service principals) in the tenant with risk assessment. Since 2023, OAuth apps with high permissions have been one of the top vectors for tenant takeover — typically after migrations, decommissioned third-party tools or phishing attacks with illicit consent grant.',
    'Risiko-Bewertung' => 'Risk assessment',
    'Pro App wird ein Score 0–100 berechnet:' => 'A score of 0–100 is calculated per app:',
    '+20 pro High-Privilege-Permission' => '+20 per high-privilege permission',
    'z. B. <code>Mail.ReadWrite.All</code>, <code>Files.ReadWrite.All</code>, <code>Sites.FullControl.All</code>, <code>User.ReadWrite.All</code>, <code>Directory.ReadWrite.All</code>, <code>full_access_as_app</code>.'
        => 'e.g. <code>Mail.ReadWrite.All</code>, <code>Files.ReadWrite.All</code>, <code>Sites.FullControl.All</code>, <code>User.ReadWrite.All</code>, <code>Directory.ReadWrite.All</code>, <code>full_access_as_app</code>.',
    '+25 wenn nie angemeldet' => '+25 if never signed in',
    'die App hat Permissions, nutzt sie aber nicht — typisch nach Migration.'
        => 'the app has permissions but does not use them — typical after a migration.',
    '+30 wenn letzte Anmeldung > 365 Tage' => '+30 if last sign-in > 365 days',
    ', +15 wenn > 180, +5 wenn > 90.' => ', +15 if > 180, +5 if > 90.',
    'Microsoft-First-Party-Apps werden mit Score 0 markiert.' => 'Microsoft first-party apps are marked with a score of 0.',
    'Standardmäßig werden nur 3rd-Party-Apps gezeigt. Filter „Alle (inkl. Microsoft)" zeigt auch die etwa 100 Microsoft-eigenen Service Principals, die in jedem Tenant existieren.'
        => 'By default, only third-party apps are shown. The "All (incl. Microsoft)" filter also shows the roughly 100 Microsoft-owned service principals that exist in every tenant.',
    'Was tun bei hohem Risiko' => 'What to do on high risk',
    'Klick auf das Pfeil-Symbol öffnet die App direkt in Entra → Enterprise Applications. Dort: Berechtigungen prüfen, App ggf. deaktivieren oder löschen, alle bestehenden Token revoken.'
        => 'Clicking the arrow icon opens the app directly in Entra → Enterprise Applications. There: review permissions, disable or delete the app if necessary, revoke all existing tokens.',

    // ── manual: DLP incidents ───────────────────────────────────────────────
    'Während das DLP-Richtlinien-Modul anzeigt, <em>ob</em> DLP-Policies aktiv sind, zeigt diese Seite die <strong>tatsächlichen Treffer</strong> — also wer hat versucht, eine als „Vertraulich" gelabelte Datei nach außen zu teilen, wer hat versucht eine Kreditkarten-Nummer per Mail zu versenden, etc. Das ist der eigentliche Compliance-Audit-Wert (DSGVO Art. 5 + 32).'
        => 'While the DLP policy module shows <em>whether</em> DLP policies are active, this page shows the <strong>actual hits</strong> — i.e. who tried to share a file labeled "Confidential" externally, who tried to send a credit card number by email, etc. This is the real compliance audit value (GDPR Art. 5 + 32).',
    'Datenquelle' => 'Data source',
    'Audit-Log Filter auf <code>category eq \'DataLossPrevention\'</code> oder <code>activityDisplayName</code> mit DLP-/Sensitivity-Label-Prefix. Für detailliertere Daten (Inhalt der Auslöser, betroffene Felder) braucht es Microsoft Purview Premium.'
        => 'Audit log filter on <code>category eq \'DataLossPrevention\'</code> or <code>activityDisplayName</code> with a DLP/sensitivity-label prefix. More detailed data (content of triggers, affected fields) requires Microsoft Purview Premium.',
    'Top User mit Treffern' => 'Top users with hits',
    'wer wird wiederholt von DLP geblockt? Schulung nötig oder absichtlich?'
        => 'who is repeatedly blocked by DLP? Training needed, or intentional?',
    'Top Aktivitäten' => 'Top activities',
    'welche Regel-Typen lösen am häufigsten aus?' => 'which rule types trigger most often?',
    'Tages-Trend' => 'Daily trend',
    'Mini-Bar-Chart über den Zeitraum (7/30/90 Tage wählbar).' => 'mini bar chart over the period (7/30/90 days selectable).',

    // ── manual: Authentication strength ─────────────────────────────────────
    'Microsoft empfiehlt seit 2024 ausschließlich <strong>phishing-resistente MFA-Methoden</strong>: FIDO2-Security-Keys, Windows Hello for Business, Certificate-Based Authentication oder Hardware-OATH-Token. Microsoft Authenticator mit Number-Matching ist <strong>nicht</strong> phishing-resistent — Adversary-in-the-Middle-Angriffe (Evilginx, EvilProxy) können den Push-Code abfangen. SMS-OTP und Voice-Call sind erst recht unsicher.'
        => 'Since 2024, Microsoft has recommended exclusively <strong>phishing-resistant MFA methods</strong>: FIDO2 security keys, Windows Hello for Business, certificate-based authentication or hardware OATH tokens. Microsoft Authenticator with number matching is <strong>not</strong> phishing-resistant — adversary-in-the-middle attacks (Evilginx, EvilProxy) can intercept the push code. SMS OTP and voice call are even less secure.',
    'Klassifizierung der User' => 'User classification',
    'Phishing-resistent' => 'Phishing-resistant',
    'mindestens eine starke Methode registriert.' => 'at least one strong method registered.',
    'Nur Software-MFA' => 'Software MFA only',
    'Authenticator-App oder TOTP, aber keine FIDO2.' => 'authenticator app or TOTP, but no FIDO2.',
    'Nur schwache MFA' => 'Weak MFA only',
    'nur SMS / Voice / E-Mail-OTP.' => 'only SMS / voice / email OTP.',
    'Keine MFA' => 'No MFA',
    'nur Passwort.' => 'password only.',
    'Methoden-Verteilung' => 'Method distribution',
    'Pro Methode (FIDO2, Windows Hello, Authenticator, TOTP, SMS, E-Mail) wird die Adoption als horizontales Bar-Chart angezeigt. Starke Methoden grün, schwache rot.'
        => 'For each method (FIDO2, Windows Hello, Authenticator, TOTP, SMS, email), adoption is shown as a horizontal bar chart. Strong methods green, weak ones red.',
    'Tenant-Strength-Policies' => 'Tenant strength policies',
    'Listet die im Tenant konfigurierten Authentication-Strength-Policies (Built-in + Custom). Die Built-ins „Phishing-resistant MFA" und „Passwordless MFA" können in Conditional Access als Zugriffs­bedingung für kritische Apps verwendet werden.'
        => 'Lists the authentication strength policies configured in the tenant (built-in + custom). The built-ins "Phishing-resistant MFA" and "Passwordless MFA" can be used in Conditional Access as an access condition for critical apps.',

    // ── manual: Backup status ───────────────────────────────────────────────
    'Microsoft sichert deine M365-Daten NICHT.' => 'Microsoft does NOT back up your M365 data.',
    'Die Recycle-Bin-Frist von 30–93 Tagen ist kein Backup — nach Ransomware, versehentlichem Löschen, kompromittierten Admin-Konten oder Tenant-Kündigung sind die Daten weg. Für DSGVO Art. 32 (Verfügbarkeit), ISO 27001 A.12.3 und NIS-2 Art. 21(d) ist ein 3rd-Party-Backup-Tool Pflicht.'
        => 'The recycle bin period of 30–93 days is not a backup — after ransomware, accidental deletion, compromised admin accounts or tenant termination, the data is gone. For GDPR Art. 32 (availability), ISO 27001 A.12.3 and NIS-2 Art. 21(d), a third-party backup tool is mandatory.',
    'Manuelles Tracking' => 'Manual tracking',
    'Da jedes 3rd-Party-Tool (Veeam, Druva, Spanning, AvePoint, Acronis, …) eigene APIs hat und keine einheitliche Microsoft-Backup-API existiert, lässt sich der Backup-Status nicht automatisch abfragen. Stattdessen pflegen Admins folgende Felder manuell:'
        => 'Since every third-party tool (Veeam, Druva, Spanning, AvePoint, Acronis, …) has its own APIs and no unified Microsoft backup API exists, the backup status cannot be queried automatically. Instead, admins maintain the following fields manually:',
    'Anbieter + URL' => 'Provider + URL',
    'Datum des letzten erfolgreichen Backup-Laufs + Status' => 'Date of the last successful backup run + status',
    'Retention (in Tagen)' => 'Retention (in days)',
    'Coverage: welche Workloads sind gesichert (Mail, OneDrive, SharePoint, Teams)'
        => 'Coverage: which workloads are protected (Mail, OneDrive, SharePoint, Teams)',
    'Datum des letzten erfolgreichen Restore-Tests' => 'Date of the last successful restore test',
    '0–100, berechnet aus den oben genannten Feldern. Critical: kein Backup-Anbieter. High: Coverage unvollständig, letzter Lauf > 7 Tage alt, Restore-Test nie durchgeführt.'
        => '0–100, calculated from the fields above. Critical: no backup provider. High: incomplete coverage, last run > 7 days old, restore test never performed.',

    // ── manual: AI security advisor ─────────────────────────────────────────
    'Eine Gesamt-Übersicht des Tenants, die auf den anonymisierten Metriken aller Module aufbaut und durch ein optionales LLM zu einer Geschäftsführungs-tauglichen Zusammenfassung verdichtet wird.'
        => 'An overall overview of the tenant, built on the anonymized metrics of all modules and condensed by an optional LLM into a management-ready summary.',
    'Was die KI sieht' => 'What the AI sees',
    'Ausschließlich aggregierte Counts und Prozentwerte' => 'Exclusively aggregated counts and percentages',
    'Niemals UPNs, niemals Domain-Namen, niemals Geräte-Namen, niemals Tenant-IDs, niemals SKU-Bezeichnungen, niemals einzelne IP-Adressen oder Zeitstempel. Beispiel:'
        => 'Never UPNs, never domain names, never device names, never tenant IDs, never SKU designations, never individual IP addresses or timestamps. Example:',
    'Empfehlungen' => 'Recommendations',
    'Die konkreten Empfehlungen (mit Step-by-Step-Anleitung, BSI-/NIS-2-/DSGVO-Artikel-Zitaten und Microsoft-Doku-Links) kommen aus einer hartcodierten <code>RecommendationLibrary</code> — nicht aus der KI. Dadurch sind die Empfehlungen reproduzierbar und nicht-halluzinierend. Die KI liefert nur den 2–3-sätzigen Executive-Summary-Text und einen Score 0–100.'
        => 'The concrete recommendations (with step-by-step instructions, BSI/NIS-2/GDPR article citations and Microsoft documentation links) come from a hard-coded <code>RecommendationLibrary</code> — not from the AI. This makes the recommendations reproducible and non-hallucinating. The AI provides only the 2–3 sentence executive summary text and a score of 0–100.',
    'Anomalie-Erkennung' => 'Anomaly detection',
    'Zwei deterministische Anomaly-Services laufen im Hintergrund und fließen in den Kontext ein:'
        => 'Two deterministic anomaly services run in the background and feed into the context:',
    'Audit-Log-Anomalien' => 'Audit log anomalies',
    '7-Tage-Rollup vs. 23-Tage-Baseline mit Poisson-Schwelle (avg + 2·√avg). Findet Aktivitäts-Spikes pro Kategorie.'
        => '7-day rollup vs. 23-day baseline with a Poisson threshold (avg + 2·√avg). Finds activity spikes per category.',
    'Sign-in-Anomalien' => 'Sign-in anomalies',
    'Credential-Stuffing-Signaturen (≥ 5 Failures + Success in 30 min), Impossible-Travel (Successful-Pair < 4 h, unterschiedliche Länder), Logins aus neuen Ländern, Off-Hours-Logins.'
        => 'credential stuffing signatures (≥ 5 failures + success in 30 min), impossible travel (successful pair < 4 h, different countries), logins from new countries, off-hours logins.',
    'Protokoll' => 'Log',
    'Unter <em>Einstellungen → KI-Sicherheitsberater → Protokoll anzeigen</em> kann der Administrator nachsehen, welche exakten Daten beim letzten Aufruf an die KI gesendet wurden — als Audit-Trail für DSGVO-Compliance.'
        => 'Under <em>Settings → AI security advisor → Show log</em>, the administrator can review exactly which data was sent to the AI on the last call — as an audit trail for GDPR compliance.',
    'Provider-Konfiguration' => 'Provider configuration',
    'gpt-4o-mini empfohlen, schnell und günstig' => 'gpt-4o-mini recommended, fast and inexpensive',
    'günstige Alternative' => 'inexpensive alternative',
    'komplett on-prem, keine Daten verlassen das Netz; llama3.2 funktioniert gut'
        => 'fully on-prem, no data leaves the network; llama3.2 works well',

    // ── manual: Executive report ────────────────────────────────────────────
    'Monatliche HTML-Mail an die Geschäftsführung mit den wichtigsten Tenant-KPIs. Läuft automatisch am 1. jedes Monats via Cron.'
        => 'Monthly HTML email to management with the most important tenant KPIs. Runs automatically on the 1st of every month via cron.',
    'Inhalt' => 'Content',
    'aus den Posture-Checks (grün/orange/rot je nach Wert).' => 'from the posture checks (green/orange/red depending on value).',
    '4 KPI-Tiles' => '4 KPI tiles',
    'Benutzer, Geräte (mit non-compliant), MFA-Quote, Conditional-Access-Policies.'
        => 'users, devices (with non-compliant), MFA rate, conditional access policies.',
    '4 Risk-Tiles' => '4 risk tiles',
    'Risikobenutzer, offene Defender Alerts, Gastbenutzer, Lizenz-SKUs.'
        => 'risky users, open Defender alerts, guest users, license SKUs.',
    'bis zu 5 fehlgeschlagene Posture-Checks.' => 'up to 5 failed posture checks.',
    'mit Link auf den KI-Berater für vollständige Empfehlungen.' => 'with a link to the AI advisor for complete recommendations.',
    'Empfänger ist standardmäßig die Alert-E-Mail-Adresse, kann aber pro Report-Typ überschrieben werden (mehrere durch Komma getrennt).'
        => 'The recipient is the alert email address by default, but can be overridden per report type (multiple separated by commas).',
    'Buttons <em>„Vorschau im Browser"</em> und <em>„Jetzt versenden"</em> erlauben Tests, ohne auf den 1. des Monats zu warten.'
        => 'The <em>"Preview in browser"</em> and <em>"Send now"</em> buttons allow testing without waiting for the 1st of the month.',

    // ── manual: MFA fatigue detection ───────────────────────────────────────
    'MFA-Fatigue ist die Strategie, mit der ein Angreifer ein gestohlenes Passwort doch noch nutzbar macht: er triggert wiederholt MFA-Push-Notifications auf dem Handy des Opfers, bis es genervt „Approve" tippt. Bekanntester Fall: Uber-Hack 2022.'
        => 'MFA fatigue is the strategy by which an attacker makes a stolen password usable after all: they repeatedly trigger MFA push notifications on the victim\'s phone until, annoyed, they tap "Approve". Best-known case: the 2022 Uber hack.',
    'MFA-Denials gesamt' => 'Total MFA denials',
    'im gewählten Zeitraum (24h / 7 Tage / 30 Tage).' => 'in the selected period (24h / 7 days / 30 days).',
    'Verdächtige Cluster' => 'Suspicious clusters',
    'pro User gruppiert in 30-Minuten-Fenster; ab 5 Denials gilt es als verdächtig.'
        => 'grouped per user into 30-minute windows; 5 or more denials is considered suspicious.',
    'Erfolgreich (Approve!)' => 'Successful (Approve!)',
    'Cluster, in denen direkt nach den Denials eine erfolgreiche Anmeldung stand. Sofort-Maßnahmen einleiten!'
        => 'clusters where a successful sign-in followed directly after the denials. Take immediate action!',
    'Sofortmaßnahmen bei einem erfolgreichen Angriff' => 'Immediate actions on a successful attack',
    'Konto sperren (<code>/users</code> → Benutzer → Deaktivieren).' => 'Block the account (<code>/users</code> → User → Disable).',
    'Alle aktiven Sitzungen widerrufen (<code>revokeSignInSessions</code>).' => 'Revoke all active sessions (<code>revokeSignInSessions</code>).',
    'Passwort-Reset erzwingen.' => 'Force a password reset.',
    'Inbox-Regeln im' => 'Check inbox rules in the',
    'prüfen — typischerweise legt ein Angreifer als Erstes eine Weiterleitungs-Regel an.'
        => '— typically an attacker first creates a forwarding rule.',
    'Im' => 'In the',
    'nachsehen, ob die App neuen Consents gegeben wurden.' => ', check whether new consents have been granted to the app.',
    'Prävention' => 'Prevention',
    'Auf <strong>Number-Matching</strong> umstellen (Microsoft hat das 2023 standardmäßig aktiviert).'
        => 'Switch to <strong>number matching</strong> (Microsoft enabled this by default in 2023).',
    'Für privilegierte Konten: FIDO2 oder Windows Hello erzwingen (siehe'
        => 'For privileged accounts: enforce FIDO2 or Windows Hello (see',
    ').' => ').',
    'Sign-in-Frequency in CA-Policies erhöhen, damit ein gekaperter Token nicht 90 Tage gültig bleibt (siehe'
        => 'Increase sign-in frequency in CA policies so a hijacked token does not stay valid for 90 days (see',

    // ── manual: Insider threat detection ────────────────────────────────────
    'Insider-Threat-Detection (Light)' => 'Insider threat detection (light)',
    'Statistische Anomalie-Erkennung pro User, basierend auf Sign-in- und Audit-Log-Daten. Das volle Microsoft Purview Insider Risk Management ist mächtiger, aber lizenz-pflichtig (E5 / Compliance-Add-on); dieses Modul liefert die wichtigsten Signale ohne zusätzliche Lizenz.'
        => 'Statistical anomaly detection per user, based on sign-in and audit log data. The full Microsoft Purview Insider Risk Management is more powerful but requires a license (E5 / compliance add-on); this module provides the most important signals without an additional license.',
    'Erfasste Signale' => 'Captured signals',
    'Off-Hours-Anmeldungen' => 'Off-hours sign-ins',
    'wieviel Prozent der Logins fanden zwischen 22:00 und 06:00 statt? &gt; 50 % = Score +25, &gt; 25 % = +10.'
        => 'what percentage of logins occurred between 22:00 and 06:00? &gt; 50 % = score +25, &gt; 25 % = +10.',
    'Geo-Diversität' => 'Geo diversity',
    'Anmeldungen aus &gt; 3 verschiedenen Ländern in 30 Tagen = +15.' => 'sign-ins from &gt; 3 different countries in 30 days = +15.',
    'Massendownloads' => 'Mass downloads',
    '≥ 50 OneDrive-File-Reads in einer Stunde = +15 pro Burst.' => '≥ 50 OneDrive file reads in one hour = +15 per burst.',
    '≥ 100 Mails in einer Stunde = +20 pro Burst.' => '≥ 100 mails in one hour = +20 per burst.',
    'Lösch-Aktivität' => 'Delete activity',
    '≥ 100 Lösch-Events = +25, ≥ 30 = +10.' => '≥ 100 delete events = +25, ≥ 30 = +10.',
    'Sharing-Aktivität' => 'Sharing activity',
    '≥ 50 Sharing-Events = +20.' => '≥ 50 sharing events = +20.',
    'Der Gesamt-Score wird auf 100 gecappt. User mit Score ≥ 50 sind High-Risk und sollten geprüft werden — entweder ein legitimer „Power User" (Marketing, Außendienst) oder ein Insider-Threat-Verdachtsfall.'
        => 'The total score is capped at 100. Users with a score ≥ 50 are high-risk and should be reviewed — either a legitimate "power user" (marketing, field sales) or a suspected insider threat.',

    // ── manual: Cross-tenant access ─────────────────────────────────────────
    'Cross-Tenant-Access (B2B/Federation)' => 'Cross-Tenant Access (B2B/Federation)',
    'Regelt, welche externen Tenants Zugriff auf Ihre Ressourcen haben und in welche externen Tenants Ihre User dürfen. Drei Ebenen:'
        => 'Controls which external tenants have access to your resources and which external tenants your users may access. Three levels:',
    'Gilt für alle externen Tenants ohne expliziten Eintrag. Microsoft-Default: B2B-Kollaboration erlaubt, B2B-Direct-Connect (Teams-Federation) blockiert, kein Trust für MFA/Compliant-Device.'
        => 'Applies to all external tenants without an explicit entry. Microsoft default: B2B collaboration allowed, B2B Direct Connect (Teams federation) blocked, no trust for MFA/compliant device.',
    'Partner-spezifisch' => 'Partner-specific',
    'Pro bekanntem Partner können Overrides definiert werden — z. B. eine engere Beziehung mit konkreten Tochterunternehmen, in denen MFA-Trust gegenseitig akzeptiert wird (dann muss der Gast nicht ein zweites Mal MFA durchlaufen).'
        => 'Overrides can be defined per known partner — e.g. a closer relationship with specific subsidiaries where MFA trust is accepted mutually (so the guest does not have to go through MFA a second time).',
    'Markiert einen Tenant als „Managed Service Provider" — gibt diesem erweiterte Verwaltungs-Berechtigungen für unseren Tenant. Sicherheits-kritisch.'
        => 'Marks a tenant as a "Managed Service Provider" — granting it extended management permissions for our tenant. Security-critical.',
    'Schreib-Operationen über Entra-Portal.' => 'Write operations via the Entra portal.',

    // ── manual: Token lifetime ──────────────────────────────────────────────
    'Token-Lifetime &amp; Sign-in-Frequency' => 'Token Lifetime &amp; Sign-in Frequency',
    'Microsoft hat 2021 die globalen Token-Lifetime-Policies deprecated. Heute steuert man die effektive Anmelde-Frequenz über das <code>signInFrequency</code>-Setting in Conditional-Access-Policies.'
        => 'In 2021, Microsoft deprecated the global token lifetime policies. Today, the effective sign-in frequency is controlled via the <code>signInFrequency</code> setting in conditional access policies.',
    'Empfohlene Werte' => 'Recommended values',
    'App-Klasse' => 'App class',
    '4 Stunden' => '4 hours',
    '12 Stunden' => '12 hours',
    '7 Tage' => '7 days',
    'Privater Browser (Persistent-Browser)' => 'Private browser (persistent browser)',
    'Niemals persistent' => 'Never persistent',
    'Konfiguration:' => 'Configuration:',
    'Sitzung → Sign-in frequency.' => 'Session → Sign-in frequency.',

    // ── manual: Lifecycle Workflows ─────────────────────────────────────────
    'Microsoft Entra ID Governance bietet automatisierte Workflows für die drei Lebens­phasen eines Mitarbeiter­kontos:'
        => 'Microsoft Entra ID Governance offers automated workflows for the three lifecycle phases of an employee account:',
    'beim Eintritt: zu Standard-Gruppen hinzufügen, Welcome-Mail senden, Manager benachrichtigen, Lizenzen zuweisen.'
        => 'on joining: add to default groups, send welcome mail, notify manager, assign licenses.',
    'bei Abteilungs-Wechsel: alte Gruppen entfernen, neue zuweisen, Mailbox-Permissions anpassen.'
        => 'on department change: remove old groups, assign new ones, adjust mailbox permissions.',
    'beim Austritt: Konto deaktivieren, Lizenzen entziehen, Manager benachrichtigen, nach X Tagen löschen.'
        => 'on leaving: disable account, remove licenses, notify manager, delete after X days.',
    'Voraussetzung:' => 'Prerequisite:',
    '(separate Lizenz oder im E5-Bundle).' => '(separate license or in the E5 bundle).',
    'Konfiguration im Entra-Portal — das Tool zeigt nur die definierten Workflows mit ihrem Status. Schreib-Operationen sind über die Graph-API möglich, sind aber nicht im Tool integriert (komplexe Task-Definitionen würden ihre eigene UI brauchen).'
        => 'Configuration is done in the Entra portal — the tool only shows the defined workflows with their status. Write operations are possible via the Graph API but are not integrated into the tool (complex task definitions would need their own UI).',

    // ── manual: Phishing simulations (module) ───────────────────────────────
    'Übersicht der durchgeführten Phishing-Simulationen aus Microsoft Defender Attack Simulation Training. Pro Simulation werden Empfänger-Anzahl, Klick-Rate, „Compromised"-Rate (User hat Credentials eingegeben oder Datei geöffnet) und Reporting-Rate (User hat die Phishing-Mail korrekt gemeldet) angezeigt.'
        => 'Overview of the phishing simulations performed via Microsoft Defender Attack Simulation Training. Each simulation shows recipient count, click rate, "compromised" rate (user entered credentials or opened a file) and reporting rate (user correctly reported the phishing mail).',
    'Wichtige Kennzahlen' => 'Key metrics',
    'Compromised-Rate &lt; 5 %' => 'Compromised rate &lt; 5 %',
    'ist ein gutes Ziel. &gt; 20 % bedeutet dringender Schulungs­bedarf.' => 'is a good target. &gt; 20 % means urgent training is needed.',
    'Reporting-Rate &gt; 50 %' => 'Reporting rate &gt; 50 %',
    'zeigt, dass die User das „Report Phishing"-Plugin in Outlook aktiv nutzen.'
        => 'shows that users actively use the "Report Phishing" plugin in Outlook.',
    'Training-Quote' => 'Training rate',
    'wieviele der erwischten User haben das zugewiesene Training auch abgeschlossen.'
        => 'how many of the caught users actually completed the assigned training.',

    // ── manual: Phishing guide ──────────────────────────────────────────────
    'Anleitung: Phishing-Simulationen mit Microsoft aufsetzen' => 'Guide: Setting up phishing simulations with Microsoft',
    'Schritt-für-Schritt-Anleitung, wie Sie mit Microsoft Defender Attack Simulation Training eine kontrollierte Phishing-Kampagne in Ihrem Tenant durchführen — von der Vorbereitung über die Durchführung bis zur Nachbereitung.'
        => 'Step-by-step guide on how to run a controlled phishing campaign in your tenant with Microsoft Defender Attack Simulation Training — from preparation through execution to follow-up.',
    '1. Voraussetzungen' => '1. Prerequisites',
    'Lizenz:' => 'License:',
    'Microsoft Defender for Office 365 <em>Plan 2</em> (in <code>Microsoft 365 E5</code> und <code>Microsoft 365 A5</code> enthalten) oder als Add-on buchbar.'
        => 'Microsoft Defender for Office 365 <em>Plan 2</em> (included in <code>Microsoft 365 E5</code> and <code>Microsoft 365 A5</code>) or bookable as an add-on.',
    'Rolle:' => 'Role:',
    'Sie benötigen eine der folgenden Microsoft-Entra-Rollen:' => 'You need one of the following Microsoft Entra roles:',
    'Globaler Administrator' => 'Global Administrator',
    'Sicherheits-Administrator' => 'Security Administrator',
    'Attack-Simulation-Administrator' => 'Attack Simulation Administrator',
    '(empfohlene Mindest­rolle)' => '(recommended minimum role)',
    'Postfach-Verzeichnis:' => 'Mailbox directory:',
    'Defender Attack Simulator nutzt die normale Tenant-Verzeichnis-Liste, also sind alle aktiven Mailboxen automatisch verfügbar.'
        => 'Defender Attack Simulator uses the normal tenant directory list, so all active mailboxes are automatically available.',
    'Vorgespräche:' => 'Prior consultation:',
    'Betriebs­rat und Daten­schutz­beauftragten <strong>vor</strong> der ersten Simulation einbinden — in Deutschland ist eine Phishing-Simulation eine Mitarbeiter-Schulungs­maßnahme, die u. U. mitbestimmungs­pflichtig ist (§ 87 Abs. 1 Nr. 6 BetrVG).'
        => 'Involve the works council and data protection officer <strong>before</strong> the first simulation — in Germany, a phishing simulation is an employee training measure that may be subject to co-determination (§ 87(1) no. 6 BetrVG).',
    '2. Vorbereitung &amp; Kommunikation' => '2. Preparation &amp; Communication',
    'Pilotphase planen.' => 'Plan a pilot phase.',
    'Niemals direkt den ganzen Tenant ins kalte Wasser werfen — beginnen Sie mit einer Pilotgruppe von 10–30 Personen aus IT, Marketing oder Verwaltung.'
        => 'Never throw the whole tenant in at the deep end — start with a pilot group of 10–30 people from IT, marketing or administration.',
    'Vorab-Kommunikation:' => 'Advance communication:',
    'ankündigen, dass „in den nächsten Wochen Phishing-Simulationen stattfinden werden, ohne konkreten Termin". Das ist nicht der Verrat — Mitarbeiter sollen wissen, dass es passieren <em>kann</em>, aber nicht <em>wann</em>.'
        => 'announce that "phishing simulations will take place over the coming weeks, with no specific date". This is not giving the game away — employees should know it <em>can</em> happen, but not <em>when</em>.',
    'Reporting-Plugin in Outlook aktivieren:' => 'Enable the reporting plugin in Outlook:',
    'stellen Sie sicher, dass der Button „Report Phishing" oder „Report Message" in Outlook für alle User sichtbar ist (Defender-Portal → Email &amp; collaboration → Policies → User reported settings).'
        => 'make sure the "Report Phishing" or "Report Message" button is visible in Outlook for all users (Defender portal → Email &amp; collaboration → Policies → User reported settings).',
    'Training-Module vorbereiten:' => 'Prepare training modules:',
    'Microsoft bringt einen Standard-Pool von ca. 70 Trainings­videos mit. Schauen Sie sie sich vorher an und wählen Sie 3–5 aus, die zu Ihrer Kampagne passen.'
        => 'Microsoft includes a standard pool of about 70 training videos. Watch them beforehand and select 3–5 that fit your campaign.',
    '3. Erste Simulation anlegen' => '3. Creating the first simulation',
    'Defender-Portal öffnen:' => 'Open the Defender portal:',
    'Reiter <em>Simulations</em> → <em>+ Launch a simulation</em>.' => 'tab <em>Simulations</em> → <em>+ Launch a simulation</em>.',
    'Technik wählen.' => 'Choose a technique.',
    'Microsoft bietet sechs Standard-Techniken — beginnen Sie mit <em>Credential Harvest</em> (gefälschte Login-Seite), das ist statistisch der häufigste reale Angriffstyp.'
        => 'Microsoft offers six standard techniques — start with <em>Credential Harvest</em> (fake login page), which is statistically the most common real attack type.',
    'gefälschte Anmelde-Seite' => 'fake sign-in page',
    'schädlicher Anhang' => 'malicious attachment',
    'Link im Dokument' => 'link inside a document',
    'Direkt-Link zu Malware' => 'direct link to malware',
    'bösartige Webseite' => 'malicious website',
    'gefälschte App-Berechtigungs-Anfrage (besonders aktuell)' => 'fake app permission request (particularly current)',
    'Payload wählen.' => 'Choose a payload.',
    'Microsoft liefert Hunderte fertige Payloads in vielen Sprachen — wählen Sie eine deutschsprachige Variante, am besten mit einem Bezug zu Ihrem Branchen­alltag (Paket­benachrichtigung, Bewerbung, Microsoft-Sicherheits­warnung, …). Mit Klick auf eine Payload sehen Sie eine Vorschau.'
        => 'Microsoft provides hundreds of ready-made payloads in many languages — choose a localized variant, ideally with a connection to your day-to-day business (parcel notification, job application, Microsoft security alert, …). Click a payload to see a preview.',
    'Empfänger auswählen.' => 'Select recipients.',
    'Für die erste Kampagne 10–30 Pilot-User. Spätere Kampagnen können auf Gruppen abzielen oder alle User auf einmal.'
        => 'For the first campaign, 10–30 pilot users. Later campaigns can target groups or all users at once.',
    'Training konfigurieren.' => 'Configure training.',
    'User, die auf den Link klicken / Daten eingeben / die Mail nicht melden, bekommen automatisch ein Training zugewiesen (Microsoft empfiehlt das „NIST"-Trainingspfad).'
        => 'Users who click the link / enter data / fail to report the mail are automatically assigned a training (Microsoft recommends the "NIST" training path).',
    'Phishing-Landing-Page wählen.' => 'Choose a phishing landing page.',
    'Bei <em>Credential Harvest</em> sieht der User nach Eingabe seiner Daten eine kurze Erklärungs-Seite („Dies war eine Simulation — bitte verwenden Sie nie Ihr echtes Passwort auf solchen Seiten").'
        => 'With <em>Credential Harvest</em>, after entering their data the user sees a short explanation page ("This was a simulation — never use your real password on such pages").',
    'Zeitfenster setzen.' => 'Set a time window.',
    'Empfohlen: 2-Wochen-Fenster, in denen die Mail zufällig verteilt wird. Microsoft hat ein „Region-Aware-Delivery"-Feature, das die Mail in den lokalen Bürozeiten ausliefert.'
        => 'Recommended: a 2-week window in which the mail is distributed randomly. Microsoft has a "region-aware delivery" feature that delivers the mail during local office hours.',
    'Starten.' => 'Launch.',
    'Vor dem finalen Klick auf <em>Submit</em> erhalten Sie eine Zusammenfassung — prüfen Sie sie sorgfältig.'
        => 'Before the final click on <em>Submit</em>, you receive a summary — review it carefully.',
    '4. Während der Kampagne' => '4. During the campaign',
    'Im Defender-Portal können Sie den Live-Status sehen — wieviele User die Mail bekommen haben, wieviele geklickt haben, wieviele „kompromittiert" sind.'
        => 'In the Defender portal you can see the live status — how many users received the mail, how many clicked, how many are "compromised".',
    'Im Tool wird die Simulation unter' => 'In the tool, the simulation is mirrored at',
    'mit denselben Daten gespiegelt.' => 'with the same data.',
    'Helpdesk-Tickets von Usern, die fragen „ist diese Mail echt?" sind erwünschte Reaktionen — kein Anlass zur Sorge.'
        => 'Helpdesk tickets from users asking "is this mail real?" are desired reactions — no cause for concern.',
    '5. Nachbereitung' => '5. Follow-up',
    'Reporting-Quote analysieren.' => 'Analyze the reporting rate.',
    'Wenn weniger als 30 % der User die Phishing-Mail gemeldet haben, ist das ein klares Schulungs­signal — das Reporting-Plugin ist entweder unbekannt oder nicht installiert.'
        => 'If fewer than 30 % of users reported the phishing mail, that is a clear training signal — the reporting plugin is either unknown or not installed.',
    'Compromised-User zuweisen.' => 'Assign compromised users.',
    'User, die geklickt + Daten eingegeben haben, bekommen automatisch Trainings — überprüfen Sie nach 14 Tagen die Abschluss­quote. Wer das Training nicht abschließt, bekommt eine Eskalation an den Vorgesetzten.'
        => 'Users who clicked and entered data are automatically assigned trainings — check the completion rate after 14 days. Whoever does not complete the training is escalated to their manager.',
    'Transparenter Bericht an die Belegschaft.' => 'Transparent report to the workforce.',
    'Senden Sie eine anonymisierte Zusammenfassung („28 % der Mitarbeiter haben geklickt, 12 % haben Credentials eingegeben, 65 % haben die Mail gemeldet — wir machen die nächste Runde in 3 Monaten"). Das fördert Awareness ohne Beschämung.'
        => 'Send an anonymized summary ("28 % of employees clicked, 12 % entered credentials, 65 % reported the mail — we will run the next round in 3 months"). This builds awareness without shaming.',
    'Datenschutz-konform speichern.' => 'Store in a privacy-compliant way.',
    'Defender speichert die Daten 90 Tage automatisch; für längere Aufbewahrung müssen Sie sie exportieren — was bei DSGVO problematisch ist, weil Mitarbeiter dann namentlich auftauchen.'
        => 'Defender retains the data automatically for 90 days; for longer retention you must export it — which is problematic under GDPR because employees then appear by name.',
    '6. Kadenz' => '6. Cadence',
    'Empfehlung: <strong>alle 2–3 Monate</strong> eine Kampagne, je mit anderer Technik und anderem Payload. Studien zeigen, dass die Klick-Quote bei einer konstanten Kampagne nach ca. 18 Monaten von typisch 25 % auf unter 5 % sinkt.'
        => 'Recommendation: a campaign <strong>every 2–3 months</strong>, each with a different technique and payload. Studies show that with a consistent campaign, the click rate drops from a typical 25 % to under 5 % after about 18 months.',
    '7. Häufige Fallstricke' => '7. Common pitfalls',
    'Personalrat/Betriebsrat nicht eingebunden.' => 'Staff/works council not involved.',
    'Kann zu Beschwerden und im schlimmsten Fall zu Untersagung führen. Vorher klären.'
        => 'Can lead to complaints and, in the worst case, a ban. Clarify in advance.',
    'Beschämungs-Kommunikation.' => 'Shaming communication.',
    'Wer dem Marketing einen Brief schickt „Sie sind unser bester Klicker", verliert die Belegschaft. Stattdessen anonyme Aggregate.'
        => 'Whoever sends marketing a letter saying "you are our best clicker" loses the workforce. Use anonymous aggregates instead.',
    'Zu seltene Wiederholung.' => 'Repeating too rarely.',
    'Eine Phishing-Simulation pro Jahr bringt fast nichts — Skills verblassen schnell.'
        => 'One phishing simulation per year achieves almost nothing — skills fade quickly.',
    'Standard-Payloads ohne Anpassung.' => 'Standard payloads without customization.',
    'Microsoft-Standard-Templates sind oft zu generisch. Erstellen Sie für die zweite/dritte Kampagne <em>Custom Payloads</em>, die Ihren Branchen-Kontext aufnehmen.'
        => 'Microsoft standard templates are often too generic. For the second/third campaign, create <em>custom payloads</em> that reflect your industry context.',
    'Verteilung über Mail-Allow-Listen umgehen.' => 'Bypassing distribution via mail allow lists.',
    'Defender Simulator ist standardmäßig auf der Allow-Liste — wenn Ihre Anti-Spam-Regeln zu aggressiv sind, kann die Simulations-Mail trotzdem gefiltert werden. Prüfen Sie im Vorhinein mit einer Test-Simulation an die IT-Abteilung.'
        => 'Defender Simulator is on the allow list by default — but if your anti-spam rules are too aggressive, the simulation mail can still be filtered. Test in advance with a test simulation to the IT department.',
    'Pro-Tipp:' => 'Pro tip:',
    'Nach 2–3 erfolgreichen Kampagnen lassen sich die Simulationen mit der <em>Automation</em>-Funktion im Defender-Portal auch selbst-fahrend einrichten — Microsoft wählt dann pro Quartal eine neue Technik + Payload aus und versendet die Mail an User-Gruppen, die schon eine Weile keine Simulation mehr bekommen haben.'
        => 'After 2–3 successful campaigns, you can set the simulations to run on autopilot using the <em>Automation</em> feature in the Defender portal — Microsoft then picks a new technique + payload each quarter and sends the mail to user groups that have not received a simulation for a while.',
    '8. Weiterführende Links' => '8. Further links',
    'Payload-Bibliothek' => 'Payload library',
    'BSI — Phishing-Methoden' => 'BSI — Phishing methods',

    // ── manual: External Identity Provider Trust ────────────────────────────
    'Listet alle konfigurierten externen Identity Providers im Tenant (Google, Facebook, Apple für B2C-Szenarien) sowie federierte Domains (ADFS, Okta, Ping Identity, …). Jeder zusätzliche IdP ist eine Erweiterung der Angriffsfläche — sollte periodisch auditiert werden.'
        => 'Lists all configured external identity providers in the tenant (Google, Facebook, Apple for B2C scenarios) as well as federated domains (ADFS, Okta, Ping Identity, …). Every additional IdP is an extension of the attack surface — and should be audited periodically.',

    // ── manual: Customer Lockbox ────────────────────────────────────────────
    'Ohne Customer Lockbox darf Microsoft Support im Notfall direkt auf Ihre Daten zugreifen — Sie erfahren es nicht. Mit aktiviertem Lockbox muss jeder Microsoft-Support-Zugriff aktiv von einem Tenant-Admin approvt werden, sonst gibt es <strong>keinen</strong> Zugriff.'
        => 'Without Customer Lockbox, Microsoft Support may access your data directly in an emergency — and you will not be told. With Lockbox enabled, every Microsoft Support access must be actively approved by a tenant admin, otherwise there is <strong>no</strong> access.',
    'Microsoft 365 E5 oder als Add-on.' => 'Microsoft 365 E5 or as an add-on.',
    'Microsoft Graph stellt für diese Einstellung keinen Schreib-Endpunkt zur Verfügung — Konfiguration daher im M365 Admin Center → Security &amp; Privacy. Das Tool tracked nur den manuell eingetragenen Aktivierungs-Status, die Approver-Liste, die SLA-Reaktionszeit und das Datum der letzten Review (halbjährlich empfohlen).'
        => 'Microsoft Graph provides no write endpoint for this setting — so it is configured in the M365 admin center → Security &amp; Privacy. The tool only tracks the manually entered activation status, the approver list, the SLA response time and the date of the last review (recommended semi-annually).',

    // ── manual: Devices (Intune) ────────────────────────────────────────────
    'Geräte (Intune)' => 'Devices (Intune)',
    'Zeigt alle in Microsoft Intune verwalteten Geräte mit Betriebssystem, Version, Compliance-Status, Verschlüsselungsstatus und letztem Sync.'
        => 'Shows all devices managed in Microsoft Intune with operating system, version, compliance status, encryption status and last sync.',
    'Synchronisieren' => 'Synchronize',
    'Sendet eine Sync-Anfrage ans Gerät — beim nächsten Check-In werden Richtlinien und Status aktualisiert'
        => 'Sends a sync request to the device — policies and status are updated at the next check-in',
    '(nur Admin)' => '(admin only)',
    'Entfernt Unternehmens-Apps und -Daten, das Gerät bleibt persönlich nutzbar (für BYOD geeignet)'
        => 'Removes company apps and data; the device remains usable personally (suitable for BYOD)',
    'Setzt das Gerät auf Werkseinstellungen zurück — <strong>alle Daten werden unwiderruflich gelöscht</strong>'
        => 'Resets the device to factory settings — <strong>all data is deleted irreversibly</strong>',
    'BitLocker-Schlüssel' => 'BitLocker keys',
    'Auf der Gerätdetailseite werden BitLocker-Recovery-Schlüssel angezeigt (sofern in Intune hinterlegt und die Berechtigung vorhanden ist).'
        => 'On the device detail page, BitLocker recovery keys are shown (if stored in Intune and the permission is present).',
    'Ein Wipe kann nicht rückgängig gemacht werden. Vergewissere dich, dass das Gerät tatsächlich verloren, gestohlen oder auszumustern ist.'
        => 'A wipe cannot be undone. Make sure the device is actually lost, stolen or due for decommissioning.',

    // ── manual: Inactive accounts ───────────────────────────────────────────
    'Listet Benutzerkonten, die sich länger als die konfigurierte Anzahl Tage nicht mehr angemeldet haben (Standard: 90 Tage).'
        => 'Lists user accounts that have not signed in for longer than the configured number of days (default: 90 days).',
    'In den Einstellungen kann konfiguriert werden:' => 'In the settings you can configure:',
    'Inaktivitätsschwelle' => 'Inactivity threshold',
    'Ab wann gilt ein Konto als inaktiv (Standard: 90 Tage)' => 'When an account is considered inactive (default: 90 days)',
    'Automatisches Lizenz-Entfernen' => 'Automatic license removal',
    'Optionale Automatisierung — Lizenzen werden nach X Tagen Inaktivität automatisch entzogen'
        => 'Optional automation — licenses are removed automatically after X days of inactivity',
    'Vorwarnzeit' => 'Advance warning period',
    'Benutzer erhalten X Tage vor der automatischen Aktion eine Warn-E-Mail'
        => 'Users receive a warning email X days before the automatic action',
    'Lizenz manuell entfernen' => 'Remove license manually',
    'Für einzelne inaktive Benutzer können Lizenzen direkt aus der Tabelle heraus entzogen werden.'
        => 'For individual inactive users, licenses can be removed directly from the table.',

    // ── manual: Audit log ───────────────────────────────────────────────────
    'Zeigt Aktivitäten aus dem Microsoft Entra-Audit-Log: Benutzeränderungen, Gruppen-Änderungen, App-Zuweisungen, Rollen-Änderungen und weitere Verzeichnisoperationen.'
        => 'Shows activities from the Microsoft Entra audit log: user changes, group changes, app assignments, role changes and other directory operations.',
    'Die letzten 200 Ereignisse werden angezeigt, filterbar nach Kategorie und Datum. Der CSV-Export enthält alle sichtbaren Einträge.'
        => 'The last 200 events are shown, filterable by category and date. The CSV export contains all visible entries.',
    'Das Entra-Audit-Log speichert Daten 30 Tage (Azure AD Free) bzw. 90 Tage (Azure AD P1/P2). Ältere Daten sind nur über Azure Monitor / Log Analytics zugänglich.'
        => 'The Entra audit log retains data for 30 days (Azure AD Free) or 90 days (Azure AD P1/P2). Older data is only accessible via Azure Monitor / Log Analytics.',

    // ── manual: Sign-in log ─────────────────────────────────────────────────
    'Zeigt die Anmeldeprotokolle des Tenants: Wer hat sich wann, von welchem Gerät und welcher IP-Adresse angemeldet, mit welcher App, mit welchem Ergebnis und ob Conditional Access angewendet wurde.'
        => 'Shows the tenant\'s sign-in logs: who signed in when, from which device and IP address, with which app, with what result and whether Conditional Access was applied.',
    'Filter: Zeitraum, Status (Erfolg/Fehler/Unterbrochen), Benutzer, App.'
        => 'Filters: time period, status (Success/Failure/Interrupted), user, app.',
    'Der CSV-Export eignet sich für Compliance-Audits und Forensik-Untersuchungen.'
        => 'The CSV export is suitable for compliance audits and forensic investigations.',

    // ── manual: Cron & Automation ───────────────────────────────────────────
    'Verwaltet wiederkehrende Hintergrundaufgaben des Tools. Jeder Job hat einen konfigurierbaren Zeitplan (Cron-Ausdruck) und kann auch manuell ausgelöst werden.'
        => 'Manages the tool\'s recurring background tasks. Each job has a configurable schedule (cron expression) and can also be triggered manually.',
    'Verfügbare Jobs' => 'Available jobs',
    'Prüft Sicherheitsmetriken und sendet E-Mail-Benachrichtigungen (MFA-Abdeckung unter Schwellwert, neue Risiko-Benutzer, neue anonyme Freigaben)'
        => 'Checks security metrics and sends email notifications (MFA coverage below threshold, new risky users, new anonymous shares)',
    'Scannt externe Freigaben und versendet Review-E-Mails' => 'Scans external shares and sends review emails',
    'Inaktive Konten — Warn-E-Mail' => 'Inactive accounts — warning email',
    'Versendet Warnungen vor automatischem Lizenz-Entzug' => 'Sends warnings before automatic license removal',
    'Inaktive Konten — Auto-Release' => 'Inactive accounts — auto-release',
    'Entzieht Lizenzen bei Inaktivität (wenn aktiviert)' => 'Removes licenses on inactivity (if enabled)',
    'Wöchentlicher Report' => 'Weekly report',
    'Sendet einen wöchentlichen Zusammenfassungs-Report per E-Mail' => 'Sends a weekly summary report by email',
    'Einrichten des System-Crons' => 'Setting up the system cron',
    'Das Tool selbst führt keine Hintergrundprozesse aus. Es muss ein System-Cron eingerichtet werden, der regelmäßig den Tool-internen Cron-Runner aufruft:'
        => 'The tool itself does not run background processes. A system cron must be set up that regularly calls the tool\'s internal cron runner:',
    'Oder via HTTP-Aufruf (z.B. mit curl in einem Shell-Script).' => 'Or via HTTP call (e.g. with curl in a shell script).',

    // ── manual: Settings ────────────────────────────────────────────────────
    'Zentraler Konfigurationsbereich für alle allgemeinen Tool-Einstellungen. Nur für Administratoren sichtbar.'
        => 'Central configuration area for all general tool settings. Visible to administrators only.',
    'Allgemein' => 'General',
    'Wird im Browser-Tab und in der Sidebar angezeigt' => 'Shown in the browser tab and in the sidebar',
    'Cache-Dauer' => 'Cache duration',
    'Wie lange API-Antworten gecacht werden (5–60 Min.)' => 'How long API responses are cached (5–60 min)',
    'Zeitzone' => 'Time zone',
    'Für Zeitstempel-Anzeigen im Tool' => 'For timestamp displays in the tool',
    'Konfiguriert den ausgehenden Mailserver für Alert-E-Mails, Freigaben-Monitor und Berichte. Pflichtfelder: SMTP-Host, Port, Absender-Adresse, Empfänger-Adresse. Optional: SMTP-Authentifizierung.'
        => 'Configures the outgoing mail server for alert emails, the sharing monitor and reports. Required fields: SMTP host, port, sender address, recipient address. Optional: SMTP authentication.',
    'Admin-Passwort' => 'Admin password',
    'Ändert das Passwort des lokalen Administrator-Kontos (das ursprünglich beim Setup eingerichtete Konto).'
        => 'Changes the password of the local administrator account (the account originally created during setup).',
    'Berechtigungen prüfen' => 'Check permissions',
    'Zeigt, welche Microsoft Graph Berechtigungen dem konfigurierten App-Konto erteilt wurden und welche Module dadurch eingeschränkt sind. Nach Änderungen in Azure AD kann das Token über den Button „Token erneuern & neu prüfen" sofort aktualisiert werden.'
        => 'Shows which Microsoft Graph permissions have been granted to the configured app account and which modules are restricted as a result. After changes in Azure AD, the token can be refreshed immediately via the "Renew token & re-check" button.',
    'Cache leeren' => 'Clear cache',
    'Löscht alle zwischengespeicherten API-Antworten sofort. Hilfreich nach manuellen Änderungen direkt in Azure AD.'
        => 'Clears all cached API responses immediately. Helpful after manual changes directly in Azure AD.',

    // ── manual: User access ─────────────────────────────────────────────────
    'Neben dem lokalen Admin-Konto können beliebig viele <strong>Microsoft 365-Benutzer</strong> des Tenants berechtigt werden, sich mit ihrem Microsoft-Konto anzumelden — z.B. IT-Mitarbeiter als Operator.'
        => 'In addition to the local admin account, any number of <strong>Microsoft 365 users</strong> of the tenant can be authorized to sign in with their Microsoft account — e.g. IT staff as operators.',
    'Voraussetzungen in Azure' => 'Prerequisites in Azure',
    'Die bestehende App-Registrierung muss um folgende Konfiguration ergänzt werden:'
        => 'The existing app registration must be extended with the following configuration:',
    'In der App-Registrierung unter <strong>Authentifizierung → Redirect-URIs</strong> die angezeigte URI eintragen (wird auf der Seite <em>Einstellungen → Benutzer-Zugang</em> direkt angezeigt)'
        => 'In the app registration under <strong>Authentication → Redirect URIs</strong>, enter the displayed URI (shown directly on the <em>Settings → User access</em> page)',
    'Unter <strong>API-Berechtigungen</strong> die <strong>delegierte</strong> Berechtigung'
        => 'Under <strong>API permissions</strong>, add the <strong>delegated</strong> permission',
    'hinzufügen (nicht die Anwendungsberechtigung — die delegierte Version reicht für das Login)'
        => '(not the application permission — the delegated version is sufficient for the login)',
    'Kein zusätzlicher Admin-Consent nötig — <code>User.Read</code> delegiert wird von jedem Benutzer selbst beim ersten Login genehmigt'
        => 'No additional admin consent needed — delegated <code>User.Read</code> is approved by each user themselves at first login',
    'Die Redirect-URI ist im Format <code>https://ihre-domain/auth/microsoft/callback</code>. Wenn <em>App-Basis-URL</em> in den Einstellungen konfiguriert ist, wird diese verwendet.'
        => 'The redirect URI is in the format <code>https://your-domain/auth/microsoft/callback</code>. If <em>App base URL</em> is configured in the settings, that is used.',
    'Benutzer hinzufügen' => 'Add user',
    'Über <strong>Einstellungen → Benutzer-Zugang → Benutzer hinzufügen</strong> können vorhandene Tenant-Benutzer per Suchfunktion ausgewählt werden:'
        => 'Via <strong>Settings → User access → Add user</strong>, existing tenant users can be selected using the search function:',
    'Im Suchfeld Name oder E-Mail-Adresse eingeben (mindestens 2 Zeichen)'
        => 'Enter a name or email address in the search box (at least 2 characters)',
    'Benutzer aus den Vorschlägen auswählen (durchsucht Anzeigenamen und UPN)'
        => 'Select a user from the suggestions (searches display name and UPN)',
    'Rolle festlegen: <strong>Operator</strong> (Standard) oder <strong>Administrator</strong>'
        => 'Set the role: <strong>Operator</strong> (default) or <strong>Administrator</strong>',
    '„Hinzufügen" klicken' => 'Click "Add"',
    'Falls die Graph-Suche nicht verfügbar ist (z.B. fehlende Berechtigung), kann der UPN auch manuell eingegeben werden (Link „UPN manuell eingeben" im Dialog).'
        => 'If the Graph search is unavailable (e.g. missing permission), the UPN can also be entered manually (the "Enter UPN manually" link in the dialog).',
    'Anmeldevorgang für Benutzer' => 'Sign-in process for users',
    'Benutzer öffnet die Login-Seite und klickt <strong>„Mit Microsoft anmelden"</strong>'
        => 'The user opens the login page and clicks <strong>"Sign in with Microsoft"</strong>',
    'Weiterleitung zur Microsoft-Anmeldeseite (login.microsoftonline.com)'
        => 'Redirect to the Microsoft sign-in page (login.microsoftonline.com)',
    'Nach erfolgreicher Authentifizierung prüft das Tool, ob der Benutzer in der Zugriffsliste steht'
        => 'After successful authentication, the tool checks whether the user is on the access list',
    'Ist er eingetragen und aktiv: automatische Anmeldung mit der zugewiesenen Rolle'
        => 'If listed and active: automatic sign-in with the assigned role',
    'Ist er nicht eingetragen: Anzeige der Seite „Kein Zugriff" — kein Zugriff auf das Tool'
        => 'If not listed: the "No access" page is shown — no access to the tool',
    'Rollen &amp; Berechtigungen' => 'Roles &amp; permissions',
    'Funktion' => 'Feature',
    'Alle Monitoring-Module lesen' => 'Read all monitoring modules',
    'Scans starten, Erinnerungen senden' => 'Start scans, send reminders',
    'Freigaben manuell widerrufen' => 'Revoke shares manually',
    'Einstellungen bearbeiten' => 'Edit settings',
    'Benutzer-Zugang verwalten' => 'Manage user access',
    'Updates einspielen' => 'Install updates',
    'Benutzer deaktivieren / entfernen' => 'Disable / remove user',
    'Über den Bearbeiten-Button kann ein Benutzer <strong>deaktiviert</strong> werden (Zugriff gesperrt, aber Eintrag bleibt erhalten) oder über den Löschen-Button vollständig entfernt werden. Eine aktive Session wird beim nächsten Seitenaufruf automatisch beendet.'
        => 'Via the edit button, a user can be <strong>disabled</strong> (access blocked, but the entry is retained) or removed completely via the delete button. An active session is ended automatically on the next page load.',
    'Der letzte Administrator-Benutzer sollte nicht entfernt werden. Das lokale Admin-Konto (aus dem Setup) ist davon unabhängig und bleibt immer erhalten.'
        => 'The last administrator user should not be removed. The local admin account (from setup) is independent of this and always remains.',

    // ── manual: Updates ─────────────────────────────────────────────────────
    'Ermöglicht das automatische Aktualisieren des Tools auf die neueste Version aus dem konfigurierten Update-Channel.'
        => 'Enables automatic updating of the tool to the latest version from the configured update channel.',
    'Produktionsreife Releases — empfohlen für den Produktivbetrieb' => 'Production-ready releases — recommended for production use',
    'Aktuelle Entwicklungsversion mit neuen Features — nur für Testumgebungen'
        => 'Current development version with new features — for test environments only',
    'Update-Prozess' => 'Update process',
    '„Auf Updates prüfen" klicken — vergleicht die installierte SHA mit der neuesten im Channel'
        => 'Click "Check for updates" — compares the installed SHA with the latest in the channel',
    'Falls ein Update verfügbar ist, erscheint „Update installieren"' => 'If an update is available, "Install update" appears',
    'Während des Updates wird ein Fortschrittsbalken angezeigt' => 'A progress bar is shown during the update',
    'Nach dem Update lädt die Seite automatisch neu' => 'After the update, the page reloads automatically',
    'Geschützte Verzeichnisse (<code>config/</code>, <code>storage/</code>, <code>vendor/</code>, <code>composer.lock</code>) werden beim Update nie überschrieben — Konfiguration und Daten bleiben erhalten.'
        => 'Protected directories (<code>config/</code>, <code>storage/</code>, <code>vendor/</code>, <code>composer.lock</code>) are never overwritten during an update — configuration and data are retained.',
    'Datenbank-Migrationen' => 'Database migrations',
    'Nach einem Update können ausstehende SQL-Migrationen manuell oder automatisch ausgeführt werden. Das Tool zeigt an, welche Migrationen bereits angewendet wurden.'
        => 'After an update, pending SQL migrations can be run manually or automatically. The tool shows which migrations have already been applied.',

    // ── manual: Required Graph permissions ──────────────────────────────────
    'Erforderliche Graph-Berechtigungen' => 'Required Graph permissions',
    'Alle Berechtigungen sind <strong>Anwendungsberechtigungen</strong> (Application Permissions), keine delegierten Berechtigungen. Sie werden in der Azure App-Registrierung unter <em>API-Berechtigungen → Microsoft Graph → Anwendungsberechtigungen</em> erteilt und erfordern <strong>Administrator-Zustimmung</strong>.'
        => 'All permissions are <strong>application permissions</strong>, not delegated permissions. They are granted in the Azure app registration under <em>API permissions → Microsoft Graph → Application permissions</em> and require <strong>administrator consent</strong>.',
    'Mindest-Berechtigungen (Lesen)' => 'Minimum permissions (read)',
    'Benutzer, MFA, Offboarding, Inaktive Konten' => 'Users, MFA, offboarding, inactive accounts',
    'Tenant-Infos, Lizenzen' => 'Tenant info, licenses',
    'Audit-Log, Sign-in-Log, Anmeldeverlauf' => 'Audit log, sign-in log, sign-in history',
    'OneDrive, Teams-Nutzung, Adoptions-Report' => 'OneDrive, Teams usage, adoption report',
    'SharePoint, Freigaben' => 'SharePoint, sharing',
    'Sicherheitsrichtlinien, CA-Richtlinien' => 'Security policies, CA policies',
    'Postfächer, Mail-Flow' => 'Mailboxes, mail flow',
    'Intune-Geräte' => 'Intune devices',
    'Dienststatus, Message Center' => 'Service health, Message Center',
    'Secure Score, Defender' => 'Secure Score, Defender',
    'Defender Alerts auflösen' => 'Resolve Defender alerts',
    'Für M365-Benutzer-Login (delegiert)' => 'For M365 user login (delegated)',
    'Damit IT-Mitarbeiter sich mit ihrem Microsoft-Konto anmelden können, wird zusätzlich benötigt:'
        => 'So that IT staff can sign in with their Microsoft account, the following is additionally required:',
    '<strong>delegierte</strong> Berechtigung (nicht Application), ermöglicht das Auslesen von Name und UPN des angemeldeten Benutzers nach dem Login'
        => '<strong>delegated</strong> permission (not application), allows reading the name and UPN of the signed-in user after login',
    'Diese Berechtigung wird unter <em>API-Berechtigungen → Delegierte Berechtigungen → Microsoft Graph → User.Read</em> hinzugefügt. Kein Admin-Consent nötig.'
        => 'This permission is added under <em>API permissions → Delegated permissions → Microsoft Graph → User.Read</em>. No admin consent needed.',
    'Zusätzliche Schreib-Berechtigungen' => 'Additional write permissions',
    'Benutzer bearbeiten, deaktivieren, Offboarding' => 'Edit, disable users, offboarding',
    'Gruppen verwalten' => 'Manage groups',
    'App-Secrets verwalten' => 'Manage app secrets',
    'Freigaben widerrufen' => 'Revoke shares',
    'Postfach-Einstellungen (Weiterleitung, AutoReply)' => 'Mailbox settings (forwarding, auto-reply)',
    'CA-Richtlinien umschalten' => 'Toggle CA policies',
    'Admin-Rollen zuweisen' => 'Assign admin roles',
    'Risikobenutzer bestätigen/verwerfen' => 'Confirm/dismiss risky users',
    'Intune Wipe/Retire' => 'Intune wipe/retire',
    'Freigaberichtlinien ändern' => 'Change sharing policies',
    'Unter <strong>Einstellungen → Berechtigungen prüfen</strong> siehst du immer, welche Berechtigungen aktuell erteilt sind und welche Module dadurch eingeschränkt sind.'
        => 'Under <strong>Settings → Check permissions</strong> you can always see which permissions are currently granted and which modules are restricted as a result.',

    // ── manual: Setup wizard ────────────────────────────────────────────────
    'Beim ersten Login eines Admins erscheint automatisch der fünfstufige Einrichtungs-Assistent. Er prüft die Tenant-Verbindung, die App-Permissions, fragt Benachrichtigungs-Empfänger und Branding ab, und schlägt am Ende ein passendes Compliance-Profil vor. Der Assistent kann jederzeit erneut über <strong>Administration → Einrichtungs-Assistent</strong> aufgerufen werden.'
        => 'On an admin\'s first login, the five-step setup wizard appears automatically. It checks the tenant connection and the app permissions, asks for notification recipients and branding, and finally suggests a suitable compliance profile. The wizard can be reopened at any time via <strong>Administration → Setup wizard</strong>.',

    // ── manual: Compliance profiles ─────────────────────────────────────────
    'Compliance-Profile bündeln branchen-typische Härtungs-Voreinstellungen zu Ein-Klick-Presets. Verfügbare Profile: <strong>Standard / DSGVO-Basis</strong>, <strong>Gesundheitswesen (KRITIS)</strong>, <strong>Finanzwesen (BaFin/DORA)</strong>, <strong>Öffentlicher Sektor / BSI</strong>, <strong>Bildung</strong>. Jedes Profil ruft beim Anwenden eine Sequenz von <code>HardeningService</code>-Aktionen auf — komplett im Audit-Log nachvollziehbar und über das Härtungs-Modul einzeln revidierbar.'
        => 'Compliance profiles bundle industry-typical hardening presets into one-click presets. Available profiles: <strong>Standard / GDPR base</strong>, <strong>Healthcare (KRITIS)</strong>, <strong>Finance (BaFin/DORA)</strong>, <strong>Public sector / BSI</strong>, <strong>Education</strong>. When applied, each profile invokes a sequence of <code>HardeningService</code> actions — fully traceable in the audit log and individually reversible via the hardening module.',
    'Profile sind <strong>nicht exklusiv</strong>. Du kannst z. B. mit dem Standard-Profil starten und einzelne Härtungs-Items im <code>/hardening</code>-Modul nachjustieren. Das aktuell aktive Profil wird in den Settings vermerkt.'
        => 'Profiles are <strong>not exclusive</strong>. You can, for example, start with the standard profile and fine-tune individual hardening items in the <code>/hardening</code> module. The currently active profile is noted in the settings.',

    // ── manual: In-app notifications ────────────────────────────────────────
    'Die Glocke oben rechts in der Topbar zeigt alle Tenant-Ereignisse seit deinem letzten Besuch. Module wie Defender-Alerts, Cross-Tenant-Access, MFA-Fatigue oder das Compliance-Profil drücken Events in das gemeinsame Feed — eine Klick-Adresse pro Eintrag führt direkt zur Detail-Seite. Benachrichtigungen werden 90 Tage aufbewahrt und automatisch vom Cron-Job <code>notification_trim</code> gepflegt.'
        => 'The bell at the top right of the top bar shows all tenant events since your last visit. Modules such as Defender alerts, cross-tenant access, MFA fatigue or the compliance profile push events into the shared feed — one click target per entry leads directly to the detail page. Notifications are retained for 90 days and maintained automatically by the cron job <code>notification_trim</code>.',

    // ── manual: Audit diff ──────────────────────────────────────────────────
    'Täglich (Cron-Job <code>audit_diff_snapshot</code>) wird ein Snapshot aller sicherheitsrelevanten Tenant-Einstellungen erstellt — Authorization Policy, Security Defaults, Auth Methods, SharePoint, Conditional Access, Admin-Rollen, Gast-Konfiguration. In <strong>Compliance &amp; Audit → Audit-Diff</strong> kannst du beliebige zwei Snapshots auswählen und alle Veränderungen mit Rot-/Grün-/Gelb-Markierung darstellen.'
        => 'Daily (cron job <code>audit_diff_snapshot</code>), a snapshot of all security-relevant tenant settings is created — authorization policy, security defaults, auth methods, SharePoint, Conditional Access, admin roles, guest configuration. In <strong>Compliance &amp; Audit → Audit diff</strong> you can select any two snapshots and display all changes with red/green/yellow markings.',
    'Ideal für Übergaben (was hat der Vorgänger letzte Woche verändert?), für Audits (was hat sich seit der letzten Prüfung getan?) und für die Untersuchung von Vorfällen (wer hat wann diese Einstellung umgestellt? — Audit-Log liefert das &quot;wer&quot;, Audit-Diff das &quot;was&quot;).'
        => 'Ideal for handovers (what did my predecessor change last week?), for audits (what has changed since the last review?) and for investigating incidents (who changed this setting and when? — the audit log provides the &quot;who&quot;, the audit diff the &quot;what&quot;).',

    // ── manual: GDPR/NIS-2 audit report ─────────────────────────────────────
    'DSGVO/NIS-2 Audit-Report' => 'GDPR/NIS-2 audit report',
    'Unter <strong>Compliance &amp; Audit → DSGVO/NIS-2 Report</strong> erzeugt das Tool einen kompletten Audit-Bericht. Die Struktur:'
        => 'Under <strong>Compliance &amp; Audit → GDPR/NIS-2 report</strong>, the tool generates a complete audit report. The structure:',
    'Deckblatt' => 'Cover page',
    'mit Tenant-Stammdaten und aktivem Compliance-Profil' => 'with tenant master data and the active compliance profile',
    'wieviele erteilt, wieviele fehlen' => 'how many granted, how many missing',
    'aller 21 Items, gruppiert nach Kategorie' => 'of all 21 items, grouped by category',
    'Regulatorische Zuordnung' => 'Regulatory mapping',
    'DSGVO Art. 25/32, NIS-2 Art. 21, BSI ORP.4 mit den jeweils zugeordneten Hardening-Items'
        => 'GDPR Art. 25/32, NIS-2 Art. 21, BSI ORP.4 with the respectively assigned hardening items',
    'Mit dem &quot;Als PDF speichern&quot;-Button generiert dein Browser daraus eine PDF-Datei — perfekt für Auditoren, IT-Leitung oder Lieferanten-Auskünfte.'
        => 'With the &quot;Save as PDF&quot; button, your browser generates a PDF file from it — perfect for auditors, IT management or supplier disclosures.',

    // ── manual: REST API & Swagger ──────────────────────────────────────────
    'Das Tool stellt unter <code>/api/v1/...</code> eine umfangreiche REST-API für externe Werkzeuge bereit: PowerBI, Grafana, n8n, eigene Skripte. Endpunkte u. a.:'
        => 'Under <code>/api/v1/...</code>, the tool provides an extensive REST API for external tools: PowerBI, Grafana, n8n, your own scripts. Endpoints include:',
    'alle KPIs in einem JSON' => 'all KPIs in a single JSON',
    'Top-Lizenz-Nutzung' => 'top license usage',
    'Historie für Charts' => 'history for charts',
    'Liste der Härtungs-Items mit Status' => 'list of hardening items with status',
    'Authentifizierung' => 'Authentication',
    'Per API-Key im Header: <code>X-Api-Key: m365_xxxxxxxx</code>. Keys erzeugst du unter <strong>Administration → API-Schlüssel</strong>; der Klartextwert wird genau einmal angezeigt und nur als SHA-256-Hash gespeichert. Scopes: <code>read</code> (Lesen), <code>write</code> (Notifications pushen), <code>admin</code> (reserviert).'
        => 'Via API key in the header: <code>X-Api-Key: m365_xxxxxxxx</code>. You create keys under <strong>Administration → API keys</strong>; the plaintext value is shown exactly once and stored only as a SHA-256 hash. Scopes: <code>read</code> (read), <code>write</code> (push notifications), <code>admin</code> (reserved).',
    'Dokumentation' => 'Documentation',
    'Die vollständige interaktive OpenAPI-3.0-Dokumentation findest du unter <code>/api/docs</code> — Swagger UI mit &quot;Try it out&quot;-Funktion. Die Roh-Spec gibt es unter <code>/api/openapi.json</code> für Import in z. B. Postman.'
        => 'The complete interactive OpenAPI 3.0 documentation is available at <code>/api/docs</code> — Swagger UI with a &quot;Try it out&quot; function. The raw spec is available at <code>/api/openapi.json</code> for import into, e.g., Postman.',

    // ── manual: Workflow automation ─────────────────────────────────────────
    'Unter <strong>Administration → Workflows</strong> kannst du leichtgewichtige Trigger-Aktion-Sequenzen anlegen — als Mini-Power-Automate für M365-Standardabläufe. Beispiele:'
        => 'Under <strong>Administration → Workflows</strong> you can create lightweight trigger-action sequences — as a mini Power Automate for standard M365 workflows. Examples:',
    '&quot;Neuer Gast-Benutzer&quot; → &quot;In Gruppe X aufnehmen&quot; + &quot;Mail an IT-Leitung&quot; + &quot;In-App-Benachrichtigung erzeugen&quot;'
        => '&quot;New guest user&quot; → &quot;Add to group X&quot; + &quot;Mail to IT management&quot; + &quot;Create in-app notification&quot;',
    '&quot;Alle 60 Minuten&quot; → &quot;Notification erzeugen, wenn Risk-Score hoch&quot;'
        => '&quot;Every 60 minutes&quot; → &quot;Create notification if risk score is high&quot;',
    '&quot;Neuer Benutzer in Gruppe XY&quot; → &quot;Lizenz E3 zuweisen&quot; + &quot;Begrüßungsmail senden&quot;'
        => '&quot;New user in group XY&quot; → &quot;Assign E3 license&quot; + &quot;Send welcome mail&quot;',
    'Trigger werden alle 15 Minuten vom Cron-Job <code>workflow_runner</code> ausgewertet. Jede Aktion landet im Run-Log (Schwester-Tabelle <code>app_workflow_runs</code>) mit Status, Ziel und Detail. Template-Variablen für Mail-/Notification-Felder: <code>{{user.userPrincipalName}}</code>, <code>{{user.displayName}}</code>, <code>{{user.id}}</code>, <code>{{trigger}}</code>.'
        => 'Triggers are evaluated every 15 minutes by the cron job <code>workflow_runner</code>. Each action is recorded in the run log (sister table <code>app_workflow_runs</code>) with status, target and detail. Template variables for mail/notification fields: <code>{{user.userPrincipalName}}</code>, <code>{{user.displayName}}</code>, <code>{{user.id}}</code>, <code>{{trigger}}</code>.',

    // ── manual: KPI sparklines ──────────────────────────────────────────────
    'Neben den wichtigsten Dashboard-Kennzahlen siehst du ein 7-Tage-Mini-Diagramm und einen Prozent-Pfeil (<code>↑ 3,2%</code>) — der Trend gegenüber der letzten Woche. Das funktioniert, sobald das Dashboard ein paar Tage in Folge aufgerufen wurde (Werte werden in <code>app_metric_history</code> persistiert). API-Endpunkt für externe Charts: <code>/api/v1/metrics/{name}/history</code>.'
        => 'Next to the most important dashboard figures, you see a 7-day mini chart and a percentage arrow (<code>↑ 3.2%</code>) — the trend versus the previous week. This works once the dashboard has been opened a few days in a row (values are persisted in <code>app_metric_history</code>). API endpoint for external charts: <code>/api/v1/metrics/{name}/history</code>.',

    // ── manual: Online help ─────────────────────────────────────────────────
    'Online-Hilfe (?-Bubbles)' => 'Online help (?-bubbles)',
    'An vielen Stellen findest du kleine <code>?</code>-Symbole neben Labels und Überschriften. Beim Hovern erscheint eine deutschsprachige Erklärung — der gesamte Katalog (~35 Begriffe) wird zentral in <code>src/Core/Help.php</code> gepflegt und kann mit <code>\App\Core\Help::tip(\'key\')</code> in jeder View aufgerufen werden.'
        => 'In many places you will find small <code>?</code> icons next to labels and headings. On hover, an explanation appears — the entire catalog (~35 terms) is maintained centrally in <code>src/Core/Help.php</code> and can be invoked with <code>\App\Core\Help::tip(\'key\')</code> in any view.',

    // ── manual: TOC + section titles (config & governance) ───────────────────
    'Konfiguration & Governance' => 'Configuration & Governance',
    'Aktionsfreigaben (Vier-Augen)' => 'Action Approvals (Four-Eyes)',
    'Aktionsfreigaben (Vier-Augen-Prinzip)' => 'Action Approvals (Four-Eyes Principle)',
    'Alert-Webhook (Teams/SIEM)' => 'Alert webhook (Teams/SIEM)',
    'Datenschutz & Daten-Retention' => 'Data Protection & Data Retention',
    'Erweiterte Module („Mehr")' => 'Advanced modules (“More”)',
    'Konfiguration sichern & übertragen' => 'Back up & transfer configuration',
    'Konfigurations-Drift' => 'Configuration drift',

    // ── manual: Konfigurations-Center ───────────────────────────────────────
    'Das Konfigurations-Center (oben in der Seitenleiste) ist der Startpunkt für die Tenant-Konfiguration und bündelt drei Dinge auf einer Seite: einen Sicherheits-Score, eine Einrichtungs-Checkliste und die wichtigsten nächsten Schritte.'
        => 'The Configuration Center (top of the sidebar) is the starting point for tenant configuration and bundles three things on one page: a security score, a setup checklist and the most important next steps.',
    'Einrichtungs-Checkliste: Microsoft-365-Verbindung, Einrichtungs-Assistent, Compliance-Profil, Alarm-E-Mail, Backup-Status, Drift-Baseline und Admin-2FA.'
        => 'Setup checklist: Microsoft 365 connection, setup wizard, compliance profile, alert e-mail, backup status, drift baseline and admin 2FA.',
    'Empfohlene Schritte: die offenen Findings der Security Posture nach Priorität sortiert, jeweils mit Direktlink zur Behebung.'
        => 'Recommended steps: the open Security Posture findings sorted by priority, each with a direct link to remediation.',
    'Der Score wird im Hintergrund vom <code>cache_warm</code>-Cron berechnet und 30 Minuten zwischengespeichert — die Seite lädt daher sofort. Ist noch kein Wert vorhanden, lässt er sich mit „Jetzt berechnen" einmalig erzeugen.'
        => 'The score is computed in the background by the <code>cache_warm</code> cron and cached for 30 minutes — so the page loads instantly. If no value exists yet, it can be generated once via “Calculate now”.',

    // ── manual: Aktionsfreigaben (Vier-Augen) ───────────────────────────────
    'Optional aktivierbar unter Einstellungen → Datenschutz. Ist das Vier-Augen-Prinzip aktiv, müssen besonders kritische Aktionen von einem zweiten Administrator freigegeben werden: Gerät zurücksetzen (Retire) oder löschen (Wipe), Konto deaktivieren und MFA-Methoden zurücksetzen.'
        => 'Optionally enabled under Settings → Data protection. When the four-eyes principle is active, particularly critical actions must be approved by a second administrator: retire or wipe a device, disable an account and reset MFA methods.',
    'Ablauf: Administrator A löst die Aktion aus — sie wird blockiert und als Anfrage eingereicht. Administrator B gibt sie unter „Aktionsfreigaben" frei (die eigene Anfrage kann man nicht selbst freigeben). Danach löst A dieselbe Aktion erneut aus, und sie wird ausgeführt.'
        => 'Workflow: Administrator A triggers the action — it is blocked and submitted as a request. Administrator B approves it under “Action Approvals” (you cannot approve your own request). Administrator A then triggers the same action again and it is executed.',
    'Freigaben gelten 24 Stunden; jede Anfrage, Freigabe und Ausführung wird im Audit-Log protokolliert. Bei deaktiviertem Vier-Augen-Prinzip laufen alle Aktionen wie gewohnt sofort.'
        => 'Approvals are valid for 24 hours; every request, approval and execution is recorded in the audit log. With the four-eyes principle disabled, all actions run immediately as usual.',

    // ── manual: Konfiguration sichern & übertragen ──────────────────────────
    'Unter Einstellungen → Allgemein. Exportiert die operativen Einstellungen als JSON-Datei — als Backup oder um einen weiteren Tenant identisch aufzusetzen.'
        => 'Under Settings → General. Exports the operational settings as a JSON file — as a backup or to set up another tenant identically.',
    'Aus Sicherheitsgründen werden <strong>niemals Secrets exportiert</strong> (Passwörter, Client-Secret, API-Keys, SMTP-Passwort, Tenant-/App-IDs). Beim Import werden ausschließlich bekannte, nicht-sensible Einstellungen übernommen; alles andere wird ignoriert.'
        => 'For security reasons <strong>secrets are never exported</strong> (passwords, client secret, API keys, SMTP password, tenant/app IDs). On import, only known, non-sensitive settings are applied; everything else is ignored.',

    // ── manual: Konfigurations-Drift ────────────────────────────────────────
    'Auf der Seite <strong>Audit-Diff</strong> lässt sich ein Snapshot als <em>Baseline</em> festlegen. Der tägliche Cron-Job <code>config_drift_check</code> vergleicht den neuesten Snapshot mit dieser Baseline und warnt bei Abweichungen sicherheitsrelevanter Einstellungen — als In-App-Benachrichtigung und (falls konfiguriert) über den Alert-Webhook, mit Direktlink zum Diff.'
        => 'On the <strong>Audit Diff</strong> page a snapshot can be set as a <em>baseline</em>. The daily cron job <code>config_drift_check</code> compares the latest snapshot with this baseline and warns about deviations in security-relevant settings — as an in-app notification and (if configured) via the alert webhook, with a direct link to the diff.',

    // ── manual: Alert-Webhook ───────────────────────────────────────────────
    'Unter Einstellungen → Benachrichtigungen. Sicherheits-Warnungen ab der gewählten Stufe (Warnung oder Kritisch) werden zusätzlich an einen externen Endpunkt gesendet: einen Microsoft-Teams-Webhook (als MessageCard) oder ein generisches JSON-Ziel (SIEM/Sentinel/Slack-kompatibel). Ein Test-Button prüft die Verbindung.'
        => 'Under Settings → Notifications. Security alerts at or above the selected level (warning or critical) are additionally sent to an external endpoint: a Microsoft Teams webhook (as a MessageCard) or a generic JSON target (SIEM/Sentinel/Slack-compatible). A test button checks the connection.',

    // ── manual: Datenschutz & Daten-Retention ───────────────────────────────
    'Unter Einstellungen → Datenschutz. Lokal gespeicherte Verlaufs- und PII-Daten (Audit-Log, Sign-ins, Freigaben, Snapshots, Drosselungs-Logs) älter als die konfigurierte Aufbewahrungsfrist werden täglich vom Cron-Job <code>local_data_retention</code> gelöscht (0 = unbegrenzt).'
        => 'Under Settings → Data protection. Locally stored history and PII data (audit log, sign-ins, approvals, snapshots, throttling logs) older than the configured retention period are deleted daily by the cron job <code>local_data_retention</code> (0 = unlimited).',
    'Zusätzlich gibt es eine Sofort-Bereinigung sowie ein unwiderrufliches <strong>„Alle lokalen Tenant-Daten löschen"</strong> (mit getippter Bestätigung). Konfiguration, Tool-Benutzerzugänge und API-Schlüssel bleiben dabei immer erhalten.'
        => 'In addition there is an instant cleanup as well as an irreversible <strong>“Delete all local tenant data”</strong> (with typed confirmation). Configuration, tool user accounts and API keys are always preserved.',
    'Der tägliche Selbstcheck <code>app_secret_expiry</code> warnt außerdem rechtzeitig, bevor das Client-Secret oder Zertifikat der eigenen App-Registrierung abläuft — andernfalls verliert das Tool den Graph-Zugriff.'
        => 'The daily self-check <code>app_secret_expiry</code> also warns in good time before the client secret or certificate of the tool’s own app registration expires — otherwise the tool would lose Graph access.',

    // ── manual: Erweiterte Module („Mehr") ──────────────────────────────────
    'Nischige und Beta-Module (z. B. Token-Lifetime, Cross-Tenant-Access, Identity Provider Trust, MFA-Fatigue, Insider-Threat, Phishing-Simulationen, eDiscovery, Customer Lockbox) liegen in der jeweiligen Hub-Tableiste standardmäßig im „Mehr"-Dropdown. So bleibt die Hauptleiste auf die Kern-Module fokussiert, alles bleibt aber jederzeit erreichbar.'
        => 'Niche and beta modules (e.g. Token Lifetime, Cross-Tenant Access, Identity Provider Trust, MFA Fatigue, Insider Threat, Phishing Simulations, eDiscovery, Customer Lockbox) are placed in the respective hub tab bar’s “More” dropdown by default. This keeps the main bar focused on the core modules while everything remains accessible at all times.',
];
