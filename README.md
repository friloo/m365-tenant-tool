<div align="center">

# 🛡️ M365 Tenant Tool

**Das selbst-gehostete Admin-Cockpit für einen Microsoft 365 Tenant.**

Über 70 Module für Identität, Sicherheit, Compliance, Exchange, Teams, SharePoint und Reporting –
in einer schnellen, mobil-tauglichen Web-Oberfläche. Zugriff erfolgt server-seitig über die
Microsoft Graph API per **Client-Credentials-Flow** – Endnutzer brauchen **kein** Microsoft-Login.

![PHP](https://img.shields.io/badge/PHP-8.1+-777BB4?logo=php&logoColor=white)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-7952B3?logo=bootstrap&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.x-4479A1?logo=mysql&logoColor=white)
![Graph API](https://img.shields.io/badge/Microsoft%20Graph-v1.0-0078D4?logo=microsoft&logoColor=white)
![REST API](https://img.shields.io/badge/REST%20API-OpenAPI%203.0-85EA2D?logo=swagger&logoColor=black)
![License](https://img.shields.io/badge/Lizenz-MIT-green)

</div>

---

## Inhaltsverzeichnis

- [Warum dieses Tool?](#warum-dieses-tool)
- [Highlights](#highlights)
- [Funktionsumfang](#funktionsumfang)
- [Architektur](#architektur)
- [Schnellstart](#schnellstart)
- [Azure AD App-Registrierung](#azure-ad-app-registrierung)
- [Cron &amp; Job-Queue](#cron--job-queue)
- [Freigaben-Governance](#freigaben-governance)
- [REST-API](#rest-api)
- [Sicherheit](#sicherheit)
- [Projektstruktur](#projektstruktur)
- [Datenbankschema](#datenbankschema)
- [Troubleshooting](#troubleshooting)
- [Lizenz](#lizenz)

---

## Warum dieses Tool?

Das Microsoft 365 Admin Center ist über viele Portale verteilt (Entra, Intune, Purview, Defender,
Exchange, SharePoint …). Dieses Tool bündelt die **täglichen Admin-Aufgaben eines Tenants** an
einem Ort – mit Fokus auf:

- **Sicherheit & Compliance** – Härtung, Posture-Checks, DSGVO/NIS-2/BSI, Audit-Trails.
- **Automatisierung** – Freigaben-Governance, Lizenz-Recycling, Alerts, Workflows.
- **Übersicht** – ein Dashboard, eine durchsuchbare Modul-Übersicht, eine REST-API für BI-Tools.

Es läuft auf **eigener Infrastruktur** (klassischer LAMP-Stack), speichert alle Credentials
**AES-256-GCM-verschlüsselt** und benötigt keinerlei Cloud-Abhängigkeit außer der Graph API.

---

## Highlights

| | |
|---|---|
| 🔐 **Security Center** | Status **und** One-Click-Härtung aller zentralen Sicherheits-Einstellungen auf einer Seite, inkl. Härtungs-Score |
| 🧭 **Modul-Übersicht** | Durchsuchbare Karten-Übersicht aller Module für schnellen Einstieg |
| 🤖 **KI-Sicherheitsberater** | Gesamtbewertung nach BSI / NIS-2 / DSGVO inkl. Anomalie-Erkennung und konkreten Empfehlungen |
| 📊 **REST-API + Swagger UI** | Vollständige OpenAPI-3.0-Schnittstelle für Power BI, Grafana, n8n & Co. |
| ♻️ **Lizenz-Recycling** | Inaktive Konten erkennen, vorwarnen und Lizenzen automatisch freigeben |
| 📨 **Freigaben-Governance** | Externe Freigaben automatisch prüfen, Besitzer per E-Mail befragen, bei Nicht-Reaktion widerrufen |
| ⚙️ **Ein Cron, alles geregelt** | Ein einziger Cron-Eintrag orchestriert alle Hintergrund-Jobs über konfigurierbare Intervalle |
| 🔑 **2FA & Rollen** | TOTP-2FA für den Admin-Login, Rollen `admin` / `operator`, Brute-Force-Schutz |
| 📱 **Mobil-tauglich** | Responsive Layout mit Off-Canvas-Sidebar und Touch-optimierten Aktionen |

---

## Funktionsumfang

> Jedes Listenmodul bietet **CSV-Export**. Schreib-Aktionen werden im internen **App-Audit-Log**
> protokolliert. Fehlende Graph-Berechtigungen werden pro Modul verständlich erklärt statt zu crashen.

<details open>
<summary><b>👤 Benutzer &amp; Verzeichnis</b></summary>

| Modul | Was es zeigt | Aktionen |
|---|---|---|
| **Dashboard** | Aggregierte KPIs: Benutzer, Lizenzen, Freigaben, Geräte, Secure Score, offene Alerts – mit 7-Tage-Sparklines | — |
| **Benutzer** | Alle User mit MFA-Status, Anmeldestatus, Lizenzanzahl, letztem Login; Filter (aktiv / inaktiv / kein MFA / keine Lizenz) | Aktivieren/Deaktivieren, MFA-Reset, Lizenz zuweisen/entziehen, **Bulk-Aktionen** |
| **Onboarding-Wizard** | 4-Schritt-Assistent für neue Konten inkl. Lizenz + Gruppen | Anlegen, Lizenz zuweisen, Gruppen-Mitgliedschaft |
| **Offboarding** | Geführter Austritts-Prozess | Konto deaktivieren, Sessions widerrufen, Lizenzen entziehen, aus Gruppen entfernen |
| **Gastbenutzer** | B2B-Gäste, Einladungsstatus, „nie angemeldet" | Deaktivieren, Entfernen |
| **Gruppen &amp; Teams** | M365-Gruppen und Teams mit Mitgliederzahl, Typ, Sichtbarkeit | Mitglieder/Owner verwalten, anlegen, löschen |
| **Lizenzen** &amp; **Lizenz-Berater** | Verbrauch je SKU, freie Slots, Über-/Unterlizenzierung nach Funktionskriterien | — |
| **Inaktive Konten** | User ohne Anmeldung seit X Tagen + Lizenzkosten-Warnung; Aktionslog | Lizenzen entziehen, optional **Auto-Release per Cron** |
| **Passwort-Ablauf** · **MFA-Methoden** | Ablaufende Passwörter; registrierte MFA-Methoden + Adoption-Quote | — |

</details>

<details>
<summary><b>🔐 Sicherheit &amp; Identität</b></summary>

| Modul | Was es zeigt | Aktionen |
|---|---|---|
| **Security Center** (`/hardening`) | Härtungs-Score + Status **und** One-Click-Toggles aller zentralen Einstellungen, Deep-Links in die Admin-Center | Aktivieren/Deaktivieren direkt im Tool |
| **Security Posture** | 35+ Härtungs-Checks (Identität, CA, Geräte, Apps, Defender, **DSGVO/NIS-2/BSI**) | — |
| **DSGVO-Status** | Compliance-Checks: Tenant-Region, SP-Sharing, Sensitivity Labels, Audit-Log, Retention, DLP … | — |
| **Compliance-Profile** | 5 Branchen-Presets (Standard/DSGVO, Healthcare/KRITIS, Finance/BaFin/DORA, Public/BSI, Bildung) | One-Click anwenden |
| **PIM (JIT-Admin)** | Aktive privilegierte Rollen (JIT vs. dauerhaft), Eligible-Zuweisungen, 30-Tage-Aktivierungs-Audit | — |
| **Break-Glass-Accounts** | Health-Check der Notfall-Admins: Existiert? Global Admin? MFA? CA-ausgenommen? | Konfigurieren |
| **Auto-Forward-Audit** | Inbox-Regeln, die nach **extern** weiterleiten – häufigster Exfiltrationsvektor | — |
| **OAuth-App-Audit** | Enterprise Apps mit Risk-Score (High-Privilege × Inaktivität, Microsoft vs. 3rd-Party) | Deep-Link zu Entra |
| **Conditional Access** · **Named Locations** | CA-Policies mit Lücken-Analyse & Vorlagen; vertrauenswürdige IP-/Länder-Standorte | Toggle, Anlegen, Löschen |
| **Admin-Rollen** | Rollenzuweisungen gruppiert, Privileged-Markierung | Zuweisen / Entfernen |
| **Secure Score** · **Defender Alerts** · **Risiko-Anmeldungen** | Score-Verlauf; aktive Alerts; At-Risk-Benutzer & Risk-Detections | Resolve · Als kompromittiert markieren / Risiko zurücksetzen |
| **Auth-Strength** · **MFA-Fatigue** · **Insider-Threat** · **Cross-Tenant-Access** · **Token-Lifetime** · **Phishing-Simulationen** · **Identity Provider Trust** | Spezialisierte Detektions- und Konfigurations-Ansichten | je nach Modul |
| **App-Registrierungen** | Apps & ablaufende Secrets | Secret hinzufügen / löschen |

</details>

<details>
<summary><b>📋 Compliance &amp; Audit</b></summary>

| Modul | Was es zeigt | Aktionen |
|---|---|---|
| **Audit-Log** · **Sign-in-Log** | Verzeichnis-Audits & Anmeldungen mit Filtern | CSV-Export |
| **Audit-Diff** | Diff zwischen zwei täglichen Tenant-Snapshots – Änderungen sichtbar | Snapshot erstellen, A/B-Vergleich |
| **DSGVO/NIS-2 Audit-Report** | Vollständiger Compliance-Bericht mit Artikel-Zuordnung | Im Browser als PDF speichern |
| **Access Reviews** | Periodische Zugriffsüberprüfungen für Gäste/Apps | Erstellen, Bulk-Entscheidungen, Apply |
| **Papierkorb** | Soft-deleted Users + Groups | Wiederherstellen, permanent löschen |
| **Vertraulichkeitslabels** · **eDiscovery-Fälle** · **Sensitivity Labels** | Information-Protection-Übersicht | Deep-Link zu Purview |

</details>

<details>
<summary><b>✉️ Exchange, Teams &amp; Geräte</b></summary>

| Modul | Was es zeigt | Aktionen |
|---|---|---|
| **Postfächer** | Mailbox-Nutzung (Größe, Items, Aktivität) + Detail (Forwarding, Auto-Reply) | Forwarding setzen, Auto-Reply, CSV-Export |
| **Freigegebene Postfächer** · **Externe Weiterleitungen** | Shared Mailboxes; Mailboxen mit externer Weiterleitung | Anlegen · Entfernen |
| **Mail Flow** | Exchange-Service-Status + Defender-for-Office-Alerts | — |
| **Teams-Übersicht** · **Teams-Nutzung** · **Teams Governance** | Policies; Usage-Bericht; inaktive Teams / ohne Owner / ohne Members | — |
| **Geräte (Intune)** | Verwaltete Geräte, Compliance, OS, BitLocker | Sync, Retire, Wipe |
| **EXO Migration** · **Message Center** · **Dienststatus** | Migrations-Readiness; Roadmap-Nachrichten; Live-Service-Status | Auto-Refresh |

</details>

<details>
<summary><b>💾 Speicher, Freigaben &amp; Berichte</b></summary>

| Modul | Was es zeigt | Aktionen |
|---|---|---|
| **OneDrive** · **SharePoint** | Speichernutzung pro Nutzer/Site | Drive provisionieren/de-provisionieren |
| **Freigaben** | Alle externen und anonymen Freigaben | Widerrufen |
| **Freigaben-Monitor** | Vollautomatisches Monitoring externer Freigaben mit E-Mail-Review + Auto-Widerruf | Widerrufen, erinnern, Scan auslösen |
| **Freigaberichtlinien** | Globale & Pro-Site-Sharing-Einstellungen | Ändern |
| **Nutzungsberichte** · **Adoption-Report** | Aggregierte M365-Aktivität; Service-Adoption | — |
| **Executive-Report** | Monatliche KPI-Mail an die Geschäftsführung | Aktivieren, Vorschau, Test-Versand |
| **Domain Health** | DNS / SPF / DKIM / DMARC pro Domain | — |
| **Backup-Status** | Tracking eines 3rd-Party-Backups mit Health-Score | Konfigurieren |

</details>

<details>
<summary><b>⚙️ Administration &amp; Plattform</b></summary>

| Modul | Was es zeigt | Aktionen |
|---|---|---|
| **Modul-Übersicht** (`/overview`) | Durchsuchbare Karten-Übersicht aller Bereiche | — |
| **Einrichtungs-Assistent** (`/setup`) | 5-Schritt-Onboarding für neue Admins | Durchlaufen, neu starten |
| **Workflows** | Leichtgewichtige Trigger+Action-Automatisierung (Mini-Power-Automate) | Anlegen, aktivieren, „Jetzt ausführen", Run-Log |
| **Cron &amp; Automatisierung** | Geplante Aufgaben mit Status/Logs, Job-Queue-Statistik | Job sofort ausführen, Intervalle setzen |
| **Einstellungen** | App, SMTP/Alerts, Freigaben-Monitor, Inaktive Konten, Branding, KI … | nur Admin |
| **Benutzer-Zugang** · **Berechtigungen** | Tool-Zugriff (Admin/Operator); Graph-Permission-Audit | Verwalten · — |
| **REST-API** + **Swagger UI** · **API-Schlüssel** | OpenAPI-3.0 unter `/api/docs`; Key-Verwaltung | — · Erstellen/Widerrufen |
| **App-Audit-Log** · **Updates** | Internes Aktions-Audit; OTA-Update per Git-Pull | — · Check/Install |

</details>

### Querschnittsfunktionen

- **Rollen-System** `admin` (voll) · `operator` (schreibend, keine Einstellungen)
- **2FA (TOTP, RFC 6238)** kompatibel mit Microsoft/Google Authenticator & Aegis, inkl. Wiederherstellungscodes
- **Job-Queue** für Schreib-Operationen – asynchron, mit Retry + Exponential Backoff
- **Graph-Cache** in MySQL (konfigurierbare TTL, Standard 15 Min.)
- **Anomalie-Erkennung** auf Audit- & Sign-in-Logs (Credential-Stuffing, Impossible Travel, neue Länder)
- **Konkrete Fehler-Diagnose** – jede Graph-Fehlermeldung wird in eine deutsche Erklärung mit Lösungsweg übersetzt
- **Sicherheits-Header** – CSRF-Schutz, CSP, HSTS, `SameSite=Strict`-Cookies, Brute-Force-Schutz auf dem Login
- **Inline-Hilfe** – `?`-Bubbles mit deutschsprachigen Erklärungen zu Fachbegriffen

---

## Architektur

**Klassischer LAMP-Stack, modulares MVC, keine Cloud-Abhängigkeiten außer Microsoft Graph.**

```
Browser ──HTTPS──> Apache + PHP-FPM ──> index.php (Front Controller / Router)
                                          │
                                          ├── Module (Controller → Service → View)
                                          ├── GraphClient ──OAuth2 Client Credentials──> Microsoft Graph
                                          ├── MySQL (Config verschlüsselt, Cache, Queue, Audit)
                                          └── run-cron.php (alle Hintergrund-Jobs, 1×/Minute)
```

| Komponente | Aufgabe |
|---|---|
| `GraphClient` | HTTP-Client mit Cache, Pagination, CSV-Report-Handling, 429-Retry, Fehler-Übersetzung |
| `GraphTokenManager` | OAuth2-Client-Credentials-Token (gecacht) |
| `Config` + `Encryptor` | Konfiguration aus der DB, sensible Werte AES-256-GCM-verschlüsselt |
| `Router` | Pfad-basiertes Routing mit Platzhaltern, zentraler CSRF-Check |
| `Navigation` | Zentrale Menü-Definition (Sidebar **und** Modul-Übersicht) |
| `CronRunner` + `QueueWorker` | Orchestrierung aller geplanten Jobs und asynchroner Schreib-Operationen |

---

## Schnellstart

### Voraussetzungen

| Komponente | Version |
|---|---|
| PHP | 8.1+ (`pdo_mysql`, `openssl`, `curl`, `mbstring`) |
| Apache | 2.4 mit `mod_rewrite` + PHP-FPM |
| MySQL / MariaDB | 8.x / 10.x |
| Composer | 2.x |

### 1 · Repository klonen &amp; Abhängigkeiten installieren

```bash
git clone https://github.com/friloo/m365-tenant-tool.git
cd m365-tenant-tool
composer install --no-dev --optimize-autoloader
```

### 2 · Server einrichten

Entweder per Komfort-Script …

```bash
sudo bash setup-server.sh   # prüft Abhängigkeiten, setzt Rechte, schreibt VirtualHost, startet Apache neu
```

… oder manuell per Apache-VirtualHost:

```apache
<VirtualHost *:443>
    ServerName m365.firma.de
    DocumentRoot /var/www/m365-tenant-tool

    <Directory /var/www/m365-tenant-tool>
        AllowOverride All
        Require all granted
    </Directory>

    # storage/ enthält den Verschlüsselungsschlüssel – niemals web-öffentlich!
    <Directory /var/www/m365-tenant-tool/storage>
        Require all denied
    </Directory>

    <FilesMatch "\.php$">
        SetHandler "proxy:unix:/run/php/php-fpm.sock|fcgi://localhost"
    </FilesMatch>

    SSLEngine on
    SSLCertificateFile    /etc/letsencrypt/live/m365.firma.de/fullchain.pem
    SSLCertificateKeyFile /etc/letsencrypt/live/m365.firma.de/privkey.pem
</VirtualHost>
```

### 3 · Web-Installer durchlaufen

Öffne `https://m365.firma.de/install/` und folge den fünf Schritten:

| Schritt | Inhalt |
|---|---|
| 1 — Datenbank | MySQL-Zugangsdaten, Schema wird automatisch eingespielt |
| 2 — Admin-Konto | Benutzername + Passwort für den lokalen Admin |
| 3 — Azure AD | Tenant ID, Client ID, Client Secret + Verbindungstest |
| 4 — Einstellungen | App-Name, öffentliche URL, Cache-TTL, Zeitzone |
| 5 — Fertig | Zusammenfassung → Login |

Nach Abschluss wird `storage/installed.lock` angelegt und der Installer dauerhaft gesperrt.

### 4 · Cron einrichten

```bash
crontab -u www-data -e
```

```cron
* * * * * php /var/www/m365-tenant-tool/run-cron.php >> /var/log/m365-cron.log 2>&1
```

> **Genau ein** Cron-Eintrag genügt. `run-cron.php` entscheidet intern anhand der konfigurierten
> Intervalle, welche Jobs fällig sind. Alles Weitere konfigurierst du im Web-UI unter
> **Cron &amp; Automatisierung**.

---

## Azure AD App-Registrierung

1. [Entra Admin Center](https://entra.microsoft.com) → **Anwendungen → App-Registrierungen → Neue Registrierung**
2. Name: `M365 Tenant Tool` · Kontotyp: *Nur dieser Verzeichnisinstanz*
3. **Zertifikate &amp; Geheimnisse → Neuer geheimer Clientschlüssel** → sofort kopieren
4. **API-Berechtigungen → hinzufügen → Microsoft Graph → Anwendungsberechtigungen**
5. Berechtigungen aus den Tabellen unten hinzufügen
6. **Administratorzustimmung erteilen** klicken (erfordert Global Administrator)

> 💡 Unter `/settings/permissions` zeigt das Tool live an, welche Berechtigungen erteilt sind und
> welche fehlen – inklusive der davon betroffenen Module.

<details>
<summary><b>Benutzer &amp; Verzeichnis</b></summary>

| Berechtigung | Zweck | Typ |
|---|---|---|
| `User.Read.All` | Benutzer, MFA-Status, Anmeldungen lesen | **Erforderlich** |
| `User.ReadWrite.All` | Benutzer anlegen/bearbeiten/löschen | für Schreib-Aktionen |
| `User.EnableDisableAccount.All` | Aktivieren/Deaktivieren | Empfohlen |
| `User.ManageIdentities.All` | Authentifizierungsmethoden verwalten | Empfohlen |
| `UserAuthenticationMethod.ReadWrite.All` | MFA-Methoden lesen/zurücksetzen | **Erforderlich** |
| `Directory.Read.All` | Verzeichnis, Gäste, Domains | **Erforderlich** |
| `Domain.Read.All` | Tenant-Domains (Onboarding-Picker, Domain Health) | Empfohlen |
| `LicenseAssignment.ReadWrite.All` | Lizenzen zuweisen / entziehen | für Lizenz-Aktionen |
| `Organization.Read.All` | Tenant-Region, Subscription-Info | Empfohlen |
| `AuditLog.Read.All` | Sign-in-/Audit-Log, MFA-Reports | **Erforderlich** |
| `Reports.Read.All` | Nutzungsberichte (OneDrive, SharePoint, Teams, Mailbox) | **Erforderlich** |

</details>

<details>
<summary><b>Gruppen &amp; Teams</b></summary>

| Berechtigung | Zweck | Typ |
|---|---|---|
| `Group.Read.All` | Gruppen und Teams lesen | **Erforderlich** |
| `Group.ReadWrite.All` | Gruppen/Owner/Mitglieder verwalten | für Schreib-Aktionen |
| `Team.ReadBasic.All` | Teams-Basisinfos | Empfohlen |
| `Teamwork.Read.All` | Tenant-weite Teams-Settings (Governance) | Empfohlen |
| `AppCatalog.Read.All` | Teams-App-Katalog | Optional |

</details>

<details>
<summary><b>SharePoint, OneDrive &amp; Freigaben</b></summary>

| Berechtigung | Zweck | Typ |
|---|---|---|
| `Sites.Read.All` | SharePoint Sites + OneDrive lesen | **Erforderlich** |
| `Files.ReadWrite.All` | Freigaben lesen und widerrufen | **Erforderlich** |
| `SharePointTenantSettings.ReadWrite.All` | Tenant-Sharing-Einstellungen ändern | für Schreib-Aktionen |

</details>

<details>
<summary><b>Sicherheit &amp; Identität</b></summary>

| Berechtigung | Zweck | Typ |
|---|---|---|
| `Policy.Read.All` | CA-Policies, Named Locations, Auth-Strength, Tenant-Policies | **Erforderlich** |
| `Policy.ReadWrite.ConditionalAccess` | CA / Named Locations / Auth-Strength schreiben | für Schreib-Aktionen |
| `Policy.ReadWrite.Authorization` | Gast-Einladungs-Regeln, Gast-Rolle, User-Standardrechte, App-Consent | für Schreib-Aktionen |
| `Policy.ReadWrite.SecurityDefaults` | Security Defaults ein-/ausschalten (Security Center) | für Schreib-Aktionen |
| `RoleManagement.Read.Directory` | Admin-Rollen & PIM lesen | **Erforderlich** |
| `RoleManagement.ReadWrite.Directory` | Admin-Rollen zuweisen/entfernen | Empfohlen |
| `Application.Read.All` | App-Registrierungen, Enterprise Apps | **Erforderlich** |
| `Application.ReadWrite.All` | App-Secrets verwalten | Empfohlen |
| `IdentityRiskyUser.Read.All` | Risiko-Benutzer (read-only) | **Erforderlich** |
| `IdentityRiskyUser.ReadWrite.All` | Risiko bestätigen / verwerfen | für Schreib-Aktionen |
| `IdentityRiskEvent.Read.All` | Risk Detections | Empfohlen (Entra ID P2) |
| `SecurityAlert.Read.All` / `SecurityAlert.ReadWrite.All` | Defender Alerts lesen + auflösen | Empfohlen |
| `SecurityEvents.Read.All` | Secure Score & Sicherheitsereignisse | Empfohlen |
| `AttackSimulation.Read.All` | Phishing-Simulationen | Optional |
| `LifecycleWorkflows.Read.All` | Lifecycle Workflows (Entra ID Governance) | Optional |
| `IdentityProvider.Read.All` | Externe Identity Provider | Optional |
| `BitLockerKey.Read.All` | BitLocker-Recovery-Keys | Optional |

</details>

<details>
<summary><b>E-Mail, Compliance, Geräte &amp; Dienststatus</b></summary>

| Berechtigung | Zweck | Typ |
|---|---|---|
| `Mail.ReadBasic.All` | Postfach-Basisinfos | Empfohlen |
| `Mail.Read` | Inbox-Regeln (Auto-Forward-Audit) | Empfohlen |
| `MailboxSettings.ReadWrite` | Auto-Reply, Forwarding | Empfohlen |
| `InformationProtectionPolicy.Read.All` | Sensitivity Labels | Empfohlen |
| `eDiscovery.Read.All` | eDiscovery-Fälle | Optional |
| `DeviceManagementManagedDevices.Read.All` | Intune-Geräte lesen | Empfohlen |
| `DeviceManagementManagedDevices.ReadWrite.All` | Sync / Retire / Wipe | für Schreib-Aktionen |
| `DeviceManagementManagedDevices.PrivilegedOperations.All` | Privilegierte Geräte-Aktionen | Empfohlen |
| `ServiceHealth.Read.All` | Dienststatus & Incidents | Empfohlen |
| `ServiceMessage.Read.All` | Message Center / Roadmap | Optional |
| `Mail.Send` | E-Mails über Graph senden (alternativ zu SMTP) | Optional |

</details>

---

## Cron &amp; Job-Queue

Ein einziger Cron-Eintrag (`run-cron.php`, 1×/Minute) orchestriert alle Hintergrund-Jobs.
Intervalle und Aktiv-Status sind im Web-UI unter **Cron &amp; Automatisierung** konfigurierbar –
inkl. „Jetzt ausführen"-Button pro Job.

| Job | Standard-Intervall | Beschreibung |
|---|---|---|
| Job-Queue verarbeiten | jede Minute | Asynchrone Graph-Writes (Lizenzen, Bulk-Aktionen) |
| Freigaben scannen | stündlich | SharePoint-/OneDrive-Freigaben aus Graph synchronisieren |
| Review-E-Mails senden | stündlich | Fällige Besitzer-Anfragen versenden |
| Auto-Widerruf | stündlich | Freigaben ohne Reaktion widerrufen |
| Inaktive Konten bereinigen | täglich | Auto-Lizenzfreigabe (wenn aktiviert) |
| Audit-Snapshot | täglich | Tenant-Snapshot für Audit-Diff |
| Benachrichtigungen aufräumen | täglich | Alte In-App-Benachrichtigungen entfernen |
| Workflow-Runner | alle 15 Min. | Geplante Workflow-Automatisierungen |
| Queue aufräumen | täglich | Alte abgeschlossene Jobs löschen |

> ℹ️ Die früheren Einzelskripte (`run-stale-cleanup.php`, `run-share-monitor.php`, `run-alerts.php`)
> sind durch `run-cron.php` abgelöst und **deprecated** – nicht parallel einplanen, sonst laufen
> Jobs doppelt.

**Job-Queue:** Schreib-Operationen werden nicht synchron ausgeführt, sondern in die
`job_queue`-Tabelle geschrieben. Der Cron verarbeitet pro Minute bis zu 20 Items mit
automatischem Retry und Exponential Backoff (max. 3 Versuche).

---

## Freigaben-Governance

Vollautomatischer Lebenszyklus für externe Freigaben – ohne Login für die Besitzer:

```
Scan (stündlich)        →  neue externe Freigabe erkannt → in share_reviews gespeichert
Review-Mail (stündlich) →  Einmal-Token erzeugt → Mail an Besitzer
                              Link:  https://…/review/{token}   (kein Login nötig)
Besitzer bestätigt      →  Begründung → status=confirmed, nächster Review-Termin gesetzt
Keine Reaktion          →  Auto-Widerruf → Graph DELETE → status=revoked
```

Konfigurierbar: **Prüfintervall** (wie oft bestätigt werden muss) und **Toleranzzeit** (Frist bis
zum automatischen Widerruf). Interne Freigaben werden anhand der verifizierten Tenant-Domains
erkannt und **nicht** widerrufen.

---

## REST-API

Vollständige **OpenAPI-3.0**-Schnittstelle für BI- und Automatisierungs-Tools (Power BI, Grafana, n8n …).

- **Swagger UI:** `/api/docs` · **Spec:** `/api/openapi.json`
- **Auth:** Header `X-Api-Key` (Keys verwaltet unter `/settings/api-keys`)
- **Scopes:** `read` (GET) · `write` (POST) · `admin` (Key-Verwaltung **und** tenant-verändernde Aktionen wie Härtung/Compliance-Anwendung)

```bash
curl -H "X-Api-Key: m365_xxxxxxxx" https://m365.firma.de/api/v1/dashboard/metrics
```

> 🔒 Der API-Key wird **ausschließlich** über den Header akzeptiert (nie als Query-Parameter),
> damit er nicht in Server-Logs landet.

---

## Sicherheit

### Verschlüsselung

Alle sensiblen Werte werden mit **AES-256-GCM** (zufälliger IV) in `app_config` gespeichert:

| Wert | Speicherung |
|---|---|
| `tenant_id`, `client_id`, `client_secret` | AES-256-GCM |
| `db_password`, `smtp_password` | AES-256-GCM |
| `admin_password` | bcrypt-Hash, zusätzlich verschlüsselt |

Der Schlüssel liegt in `storage/app.key` (256 Bit, base64). **Diese Datei sichern** – ohne sie
sind alle Credentials unlesbar.

```bash
chmod 600 /var/www/m365-tenant-tool/storage/app.key
chown -R www-data:www-data /var/www/m365-tenant-tool/storage/
```

### Empfehlungen

- `storage/` per Apache-Config vor Webzugriff sperren (im VirtualHost oben enthalten)
- HTTPS erzwingen (HTTP → HTTPS Redirect), `app.key` separat ins Backup
- MySQL-Benutzer nur mit `SELECT, INSERT, UPDATE, DELETE` auf die App-Datenbank
- Azure-AD-Secret mit kurzer Laufzeit (6–12 Monate) und Rotationsplan
- 2FA für den Admin-Login aktivieren (`/settings/2fa`)

---

## Projektstruktur

```
m365-tenant-tool/
├── index.php                  # Front Controller / Router
├── run-cron.php               # Einziger Cron-Einstiegspunkt
├── setup-server.sh            # Server-Ersteinrichtung
├── install/                   # Web-Installer (nach Setup gesperrt)
├── src/
│   ├── Auth/                  # LocalAuth, GraphTokenManager, Microsoft-OAuth, TOTP
│   ├── Cache/                 # GraphCache (MySQL)
│   ├── Core/                  # Router, Config, Session, View, Csrf, Navigation, CliBootstrap …
│   ├── Database/              # DB (PDO), Schema.sql
│   ├── Encryption/            # Encryptor (AES-256-GCM)
│   ├── Graph/                 # GraphClient, GraphErrorTranslator
│   ├── Helpers/               # CsvExporter, Mailer, AlertRunner, SkuCatalog
│   ├── Modules/               # 70+ Feature-Module (Controller → Service)
│   └── Queue/                 # QueueDispatcher, QueueWorker
├── views/                     # PHP-Templates (Layout, Module, öffentliche Review-Seiten)
├── public/                    # css/app.css, js/app.js
└── storage/                   # NICHT web-öffentlich: app.key, db_bootstrap.ini, *.lock
```

Jedes Feature-Modul folgt demselben Muster: `XxxController` (HTTP, Auth, Rendering) →
`XxxService` (Graph-/DB-Zugriff) → View unter `views/xxx/`.

---

## Datenbankschema

Kern-Tabellen (das vollständige Schema liegt in `src/Database/Schema.sql`):

| Tabelle | Inhalt |
|---|---|
| `app_config` | App-Konfiguration (Credentials AES-verschlüsselt) |
| `graph_tokens` | OAuth2-Tokens (gecacht) |
| `cache` | Graph-API-Response-Cache |
| `app_audit_log` | Interne Aktionen im Tool |
| `m365_users` | Tool-Benutzer mit Rollen (admin/operator) |
| `share_reviews` · `share_review_tokens` | Überwachte Freigaben + Einmal-Token |
| `stale_account_log` | Protokoll automatischer Lizenzentnahmen |
| `cron_jobs` · `job_queue` | Cron-Status/Intervalle + asynchrone Schreib-Queue |
| `app_workflows` · `app_workflow_runs` | Workflow-Definitionen + Lauf-Historie |
| `app_api_keys` | REST-API-Schlüssel (gehasht) |
| `app_notifications` · `app_tenant_snapshots` · `app_metric_history` | In-App-Benachrichtigungen, Audit-Snapshots, KPI-Verlauf |

---

## Troubleshooting

| Symptom | Vorgehen |
|---|---|
| Weiße Seite / Interner Fehler | `/health` aufrufen – zeigt PHP-Version, geladene Extensions und ob `storage/`-Dateien existieren (ohne DB/Autoload) |
| Detaillierte Fehlermeldungen sehen | Als Admin eingeloggt werden Stacktraces ohnehin angezeigt; serverseitig per Env-Var `M365_DEBUG=1` global aktivierbar (nicht über Cookie/URL — das wäre ein Info-Leak) |
| „Verbindung nicht konfiguriert" | Tenant-Daten unter `/settings` ergänzen |
| Modul zeigt „Berechtigung fehlt" | unter `/settings/permissions` prüfen, welche Graph-Permission fehlt, und Admin-Consent erteilen |
| Cron läuft nicht | Logfile aus dem crontab-Eintrag prüfen; `run-cron.php` manuell als `www-data` ausführen |
| Reports/Nutzungszahlen leer | Graph-Reports brauchen `Reports.Read.All` und hinken ~1–2 Tage nach |

---

## Lizenz

[MIT](LICENSE) — frei nutzbar, ohne Gewähr.

<div align="center">
<sub>Gebaut für Microsoft-365-Administratoren, die ihren Tenant an einem Ort im Griff haben wollen.</sub>
</div>
