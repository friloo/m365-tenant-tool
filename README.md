# M365 Tenant Tool

Ein webbasiertes Admin-Dashboard für einen einzelnen Microsoft 365 Tenant. Greift über die Microsoft Graph API per **Client Credentials Flow** zu — kein Microsoft-Login für Endnutzer erforderlich. Das Web-Interface ist durch ein lokales Benutzerkonto geschützt.

![PHP](https://img.shields.io/badge/PHP-8.1+-777BB4?logo=php&logoColor=white)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-7952B3?logo=bootstrap&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.x-4479A1?logo=mysql&logoColor=white)
![License](https://img.shields.io/badge/Lizenz-MIT-green)

---

## Was funktioniert

### Benutzer & Verzeichnis

| Modul | Was es zeigt | Aktionen |
|---|---|---|
| **Dashboard** | Aggregierte Übersicht: Benutzer, Lizenzen, Freigaben, Geräte, Score, offene Alerts | — |
| **Benutzer** | Alle User mit MFA-Status, Anmeldestatus, Lizenzanzahl, letzter Login; Filter nach aktiv/inaktiv/kein MFA/keine Lizenz | Aktivieren/Deaktivieren, MFA reset, Lizenz zuweisen/entziehen, **Bulk-Aktionen** (mehrere gleichzeitig) |
| **Onboarding-Wizard** | 4-Schritt-Assistent zum Anlegen neuer User mit Lizenz + Gruppen | Anlegen (Graph POST /users), Lizenz zuweisen, Gruppen-Membership |
| **Offboarding** | Vollautomatischer Offboarding-Assistent | Konto deaktivieren, Sessions revoken, Lizenzen entziehen, Gruppen-Memberships entfernen |
| **Gastbenutzer** | B2B-Gäste, Einladungsstatus, zuletzt aktiv, nie angemeldet | Deaktivieren, Entfernen |
| **Gruppen & Teams** | Alle M365-Gruppen und Microsoft Teams mit Mitgliederzahl, Typ, Sichtbarkeit | Mitglieder hinzufügen/entfernen, Owner setzen, Gruppen anlegen/löschen |
| **Lizenzen** | Verbrauchsübersicht je SKU, freie Slots, Nutzer ohne Lizenz | — |
| **Lizenz-Berater** | Welche Nutzer haben zu viele/zu wenige Lizenzen — basierend auf konfigurierbaren Funktionskriterien | — |
| **Inaktive Konten** | Benutzer ohne Anmeldung seit X Tagen (konfigurierbar), Lizenz-Kosten-Warnung; Aktionslog | Lizenzen entziehen; optionaler **Auto-Release per Cron** |
| **Passwort-Ablauf** | Konten mit ablaufendem Passwort | — |
| **MFA-Methoden** | Welche MFA-Methode je User registriert ist, Adoption-Quote | — |

### Sicherheit & Identity

| Modul | Was es zeigt | Aktionen |
|---|---|---|
| **Security Posture** | 35+ Hardening-Checks (Identität, CA, Geräte, Apps, Defender, **DSGVO/NIS-2/BSI**) | — |
| **DSGVO-Status** | 8 Compliance-Checks: Tenant-Region, SP-Sharing, Sensitivity Labels, Audit-Log, Retention, DLP-Schutz, …  | — |
| **Tenant-Härtung** (`/hardening`) | One-Click-Toggles für die wichtigsten Sicherheits-Einstellungen via Graph + Deep-Links zu Admin-Centers | Aktivieren/Deaktivieren direkt im Tool |
| **PIM (JIT-Admin)** | Aktive Privileged-Rollen (JIT vs. dauerhaft), Eligible-Zuweisungen, 30-Tage-Aktivierungs-Audit | — |
| **Break-Glass-Accounts** | Health-Check der Notfall-Admin-Accounts: Existiert? Global Admin? MFA? CA-ausgenommen? Letzter Test? | Konfigurieren |
| **Auto-Forward-Audit** | Scannt alle Mailboxen auf Inbox-Regeln, die nach **extern** weiterleiten — häufigster Exfiltrationsvektor | — |
| **OAuth-App-Audit** | Enterprise Apps mit Risk-Score: High-Privilege × Inaktivität, Microsoft- vs. 3rd-Party | Deep-Link zu Entra |
| **DLP-Vorfälle** | Echte DLP-Treffer aus Audit-Logs (nicht nur Policies), Top-User, Tages-Trend | — |
| **Authentication-Strength** | Phishing-resistente vs. schwache MFA-Methoden, User-Breakdown, Tenant-Strength-Policies | — |
| **Secure Score** | Aktueller Score, 30-Tage-Verlauf (Chart), Maßnahmen nach Kategorie | — |
| **Risiko-Anmeldungen** | At-Risk-Benutzer, Risk-Detections, risikoreiche Sign-ins | Als kompromittiert markieren, Risiko zurücksetzen |
| **Defender Alerts** | Aktive Defender-Sicherheits­warnungen | Resolve |
| **Conditional Access** | Alle CA-Policies mit State, Lücken-Analyse, Vorlagen-Wizards | Toggle, Anlegen, Löschen |
| **Named Locations** | Vertrauenswürdige IP- und Länder-Standorte | Anlegen, Löschen |
| **Admin-Rollen** | Alle Rollenzuweisungen gruppiert nach Rolle, Privileged-Markierungen | Zuweisen / Entfernen |
| **App-Registrierungen** | App-Registrierungen, ablaufende Secrets, Berechtigungs-Typ | Secret hinzufügen / löschen |

### Compliance & Audit

| Modul | Was es zeigt | Aktionen |
|---|---|---|
| **Audit-Log** | Verzeichnis-Audits aus Graph | CSV-Export |
| **Sign-in-Log** | Anmelde-Logs mit Filter (User, Status, App, Land, Risiko, Zeitraum) | CSV-Export |
| **Inaktive Konten** | siehe oben | siehe oben |
| **Access Reviews** | Periodische Zugriffsüberprüfungen für Gäste/Apps | Erstellen, Bulk-Entscheidungen, Apply |
| **Papierkorb** | Soft-deleted Users + Groups | Restore, permanent löschen |
| **DLP-Richtlinien** | Sensitivity Labels Übersicht | Deep-Link zu Purview |
| **Aufbewahrungsrichtlinien** | eDiscovery-Fälle | Deep-Link zu Purview |
| **Sensitivity Labels** | Information-Protection-Labels | Deep-Link zu Purview |

### Geräte

| Modul | Was es zeigt | Aktionen |
|---|---|---|
| **Geräte** | Intune-verwaltete Geräte, Compliance-Status, OS, BitLocker | Sync, Retire, Wipe |

### Exchange & Kommunikation

| Modul | Was es zeigt | Aktionen |
|---|---|---|
| **Postfächer** | Mailbox-Nutzung aller User (Größe, Item-Anzahl, letzte Aktivität) + Detail-Ansicht (Forwarding, Auto-Reply) | Forwarding setzen, Auto-Reply, CSV-Export |
| **Freigegebene Postfächer** | Liste der Shared Mailboxes | Anlegen |
| **Externe Weiterleitungen** | Mailboxen mit externer Weiterleitung im Tenant-Setting | Entfernen |
| **Mail Flow** | Exchange-Service-Status + Defender-for-Office-Alerts | — |
| **Teams-Nutzung** | Teams-Usage-Bericht | — |
| **Adoption-Report** | Service-Adoption Exchange/OneDrive/SharePoint/Teams | — |
| **Message Center** | M365 Roadmap-Nachrichten, Wartungs-Updates | — |
| **Dienststatus** | Live-Status aller M365-Dienste, aktive Incidents | Auto-Refresh |

### Speicher & Freigaben

| Modul | Was es zeigt | Aktionen |
|---|---|---|
| **OneDrive** | Speichernutzung aller Nutzer + persönliche Drives | Drive provisionieren/de-provisionieren |
| **SharePoint** | Site Collections, Drives, Speichernutzung pro Site | — |
| **Freigaben** | Alle externen und anonymen Freigaben im Tenant | Widerrufen |
| **Freigaben-Monitor** | Vollautomatisches Monitoring externer Freigaben mit E-Mail-Review + Auto-Widerruf | Manuell widerrufen, Erinnerung senden, Scan auslösen |
| **Freigaberichtlinien** | Globale SharePoint/OneDrive-Sharing-Einstellungen, Pro-Site-Konfiguration | Ändern |

### Berichte & KI

| Modul | Was es zeigt | Aktionen |
|---|---|---|
| **KI-Sicherheitsberater** (`/ai`) | KI-gestützte Gesamt-Übersicht: BSI + NIS-2 + DSGVO + Anomalien, konkrete Empfehlungen mit Artikel-Zitaten | Analyse starten, Protokoll einsehen |
| **Executive-Report** | Monatliche HTML-Mail an Geschäftsführung mit Tenant-KPIs | Aktivieren, Vorschau, Test-Versand |
| **Domain Health** | DNS / DKIM / DMARC / SPF pro Domain | — |
| **Teams Governance** | Inaktive Teams, ohne Owner, ohne Members | — |
| **Sign-in-Log** | Auditierte Anmeldungen | CSV-Export |
| **Backup-Status** | Manuelles Tracking 3rd-Party-Backup mit Health-Score | Konfigurieren |
| **Nutzungsberichte** | Aggregierte M365-Aktivität | — |

### Administration

| Modul | Was es zeigt | Aktionen |
|---|---|---|
| **Einrichtungs-Assistent** (`/setup`) | 5-Schritt-Onboarding für neue Admins: Verbindung, Permissions, Empfänger, Branding, Compliance-Profil | Vollständig durchlaufen, neu starten |
| **Compliance-Profile** (`/complianceprofile`) | 5 Branchen-Presets (Standard/DSGVO, Healthcare/KRITIS, Finance/BaFin/DORA, Public/BSI, Bildung) | One-Click anwenden |
| **Workflows** (`/workflows`) | Leichtgewichtige Trigger+Action-Automatisierung (Mini-Power-Automate) | Anlegen, Aktivieren, Jetzt ausführen, Run-Log |
| **In-App-Benachrichtigungen** (Topbar-Glocke) | Tenant-Events seit letztem Besuch | Lesen markieren, alle anzeigen |
| **Audit-Diff** (`/auditdiff`) | Diff zwischen zwei täglichen Tenant-Snapshots — Änderungen sichtbar | Manueller Snapshot, A/B-Vergleich |
| **DSGVO/NIS-2 Audit-Report** (`/auditreport`) | Vollständiger Compliance-Bericht mit Zuordnung zu Artikeln | Im Browser als PDF speichern |
| **REST-API** (`/api/v1/...`) + Swagger UI (`/api/docs`) | OpenAPI-3.0-Schnittstelle für PowerBI/Grafana/n8n | API-Keys verwalten unter `/settings/api-keys` |
| **Cron & Automatisierung** | Alle geplanten Aufgaben mit Status, Logs; Job-Queue-Statistiken | Job sofort ausführen, Intervall konfigurieren |
| **Einstellungen** | App, SMTP/Alerts, Freigaben-Monitor, Inaktive Konten, Branding, KI, …  — **mit Tab-Navigation** | Admin only |
| **Benutzer-Zugang** (`/settings/users`) | M365-Benutzer mit Tool-Zugriff verwalten (Admin/Operator-Rollen) | Hinzufügen, Bearbeiten, Entfernen |
| **Berechtigungen** (`/settings/permissions`) | Graph-API-Berechtigungs-Audit: welche Permissions hat die App, welche fehlen | — |
| **App-Audit-Log** | Internes Audit aller Aktionen im Tool selbst | — |
| **Updates** | OTA-Update-Mechanismus (Git-pull aus diesem Repo) | Check, Install |

### Querschnittsfunktionen

- **CSV-Export** auf jedem Listenmodul
- **Rollen-System**: `admin` (voll) · `operator` (schreibend, keine Einstellungen) — verwaltet unter `/settings/users`
- **2FA für Admin-Login** (TOTP-RFC-6238 — kompatibel mit Microsoft Authenticator, Google Authenticator, Aegis) inkl. Wiederherstellungs­codes
- **Bulk-Aktionen** auf der Benutzerliste — asynchron über Job-Queue
- **E-Mail-Alerts**: Risikobenutzer, MFA-Quote unter Schwellwert, anonyme Freigaben, Auto-Lizenzfreigabe, Defender-Alerts
- **Freigaben-Governance**: Besitzer per E-Mail befragen, Bestätigung per einmaligem Token-Link (kein Login), automatischer Widerruf bei Nicht-Reaktion
- **Job-Queue**: Schreib-Operationen (Lizenzen, Bulk) asynchron über DB-Queue mit Retry + Exponential Backoff
- **Cron-Orchestrator**: Ein einziger Cron-Job (`* * * * *`) steuert alle Aufgaben über konfigurierbare Intervalle
- **Graph-Cache**: API-Antworten in MySQL gecacht (konfigurierbare TTL, Standard 15 Min.)
- **AES-256-GCM-Verschlüsselung** aller Credentials in der Datenbank
- **CSRF-Schutz**, **CSP-Header**, **HSTS**, **SameSite=Strict Cookies**, **Brute-Force-Protection** auf Login
- **Konkrete Fehler-Diagnose**: jede Graph-API-Fehlermeldung wird in eine deutsche Erklärung mit Lösungsweg übersetzt (`GraphErrorTranslator`)
- **Mobile-responsive Layout**: Off-Canvas-Sidebar auf Phone, Touch-optimierte Buttons
- **Anomalie-Erkennung**: Audit-Log + Sign-in-Log werden auf Anomalien geprüft (Credential-Stuffing, Impossible Travel, neue Länder) und fließen in den KI-Berater ein
- **KPI-Sparklines**: 7-Tage-Mini-Diagramme neben den wichtigsten Kennzahlen auf dem Dashboard mit Prozent-Trend (z. B. `↑ 3,2%`)
- **Inline-Online-Hilfe**: `?`-Bubbles an Labels und Überschriften mit deutschsprachigen Erklärungen (~35 Begriffe, zentraler Katalog in `src/Core/Help.php`)
- **REST-API & Swagger UI**: vollständige OpenAPI-3.0-Spec unter `/api/docs`, X-Api-Key-Auth mit Scopes (read/write/admin)

---

## Voraussetzungen

| Komponente | Version |
|---|---|
| PHP | 8.1 oder höher |
| Apache | 2.4 mit `mod_rewrite` + PHP-FPM |
| MySQL / MariaDB | 8.x / 10.x |
| Composer | 2.x |
| PHP-Extensions | `pdo_mysql`, `openssl`, `curl`, `mbstring` |

---

## Installation

### 1. Repository klonen & Abhängigkeiten installieren

```bash
git clone https://github.com/friloo/m365-tenant-tool.git
cd m365-tenant-tool
composer install --no-dev --optimize-autoloader
```

### 2. Apache konfigurieren

```apache
<VirtualHost *:443>
    ServerName m365.firma.de
    DocumentRoot /var/www/m365-tenant-tool

    <Directory /var/www/m365-tenant-tool>
        AllowOverride All
        Require all granted
    </Directory>

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

> `storage/` muss zwingend vor Webzugriff geschützt sein — dort liegt `app.key` (Verschlüsselungsschlüssel).

### 3. Schnell-Setup per Script

```bash
sudo bash setup-server.sh
```

Das Script prüft Abhängigkeiten, setzt Dateisystemrechte, schreibt den VirtualHost und startet Apache neu.

### 4. Web-Installer aufrufen

Öffne `https://m365.firma.de/install/` und folge den 5 Schritten:

| Schritt | Inhalt |
|---|---|
| 1 — Datenbank | MySQL-Zugangsdaten, Schema wird automatisch eingespielt |
| 2 — Admin-Konto | Benutzername und Passwort für lokalen Admin |
| 3 — Azure AD | Tenant ID, Client ID, Client Secret + Verbindungstest |
| 4 — Einstellungen | App-Name, öffentliche URL, Cache-TTL, Zeitzone |
| 5 — Fertig | Zusammenfassung, Abschluss, Redirect auf Login |

Nach dem Abschluss wird `storage/installed.lock` angelegt und der Installer dauerhaft gesperrt.

### 5. Cron einrichten

```bash
crontab -u www-data -e
```

Eintrag hinzufügen:

```
* * * * * php /var/www/m365-tenant-tool/run-cron.php >> /var/log/m365-cron.log 2>&1
```

Alle Intervalle werden danach im Web-UI unter **Cron & Automatisierung** konfiguriert.

---

## Azure AD App-Registrierung

### App anlegen

1. [Entra Admin Center](https://entra.microsoft.com) → **Anwendungen → App-Registrierungen → Neue Registrierung**
2. Name: `M365 Tenant Tool` · Kontotyp: *Nur dieser Verzeichnisinstanz*
3. **Zertifikate & Geheimnisse → Neuer geheimer Clientschlüssel** → sofort kopieren
4. **API-Berechtigungen → Berechtigung hinzufügen → Microsoft Graph → Anwendungsberechtigungen**
5. Alle Berechtigungen aus der Tabelle unten hinzufügen
6. **Administratorzustimmung erteilen für [Tenant-Name]** klicken

### Berechtigungen

> Ein **Global Administrator** muss nach dem Hinzufügen aller Berechtigungen die Administratorzustimmung erteilen.
> Im Tool unter `/settings/permissions` siehst du, welche Berechtigungen erteilt sind und welche fehlen.

#### Benutzer & Verzeichnis

| Berechtigung | Zweck | Typ |
|---|---|---|
| `User.Read.All` | Benutzer, MFA-Status, Anmeldungen lesen | **Erforderlich** |
| `User.ReadWrite.All` | Benutzer anlegen (Onboarding), bearbeiten, löschen | **Erforderlich** für Schreib-Operationen |
| `User.EnableDisableAccount.All` | Aktivieren/Deaktivieren | Empfohlen |
| `User.ManageIdentities.All` | Authentifizierungsmethoden verwalten | Empfohlen |
| `UserAuthenticationMethod.ReadWrite.All` | MFA-Methoden zurücksetzen, Detail-Lesen | **Erforderlich** |
| `Directory.Read.All` | Verzeichnisdaten, Gastbenutzer, Domains | **Erforderlich** |
| `Domain.Read.All` | Tenant-Domains lesen (für Onboarding-Picker + Domain Health) | Empfohlen |
| `LicenseAssignment.ReadWrite.All` | Lizenzen zuweisen / entziehen | **Erforderlich** für Lizenz-Aktionen |
| `Organization.Read.All` | Tenant-Region, Subscription-Info | Empfohlen |
| `AuditLog.Read.All` | Sign-in-Logs, Audit-Log, MFA-Reports | **Erforderlich** |
| `Reports.Read.All` | Nutzungsberichte (OneDrive, SharePoint, Teams, Mailbox) | **Erforderlich** |

#### Gruppen & Teams

| Berechtigung | Zweck | Typ |
|---|---|---|
| `Group.Read.All` | Gruppen und Teams lesen | **Erforderlich** |
| `Group.ReadWrite.All` | Gruppen anlegen/löschen, Owner setzen, Mitglieder | **Erforderlich** für Schreib-Operationen |
| `Team.ReadBasic.All` | Teams-Basisinfos | Empfohlen |
| `Teamwork.Read.All` | Tenant-weite Teams-Settings (Governance) | Empfohlen |
| `AppCatalog.Read.All` | Teams-App-Katalog | Optional |

#### SharePoint, OneDrive & Freigaben

| Berechtigung | Zweck | Typ |
|---|---|---|
| `Sites.Read.All` | SharePoint Sites + OneDrive lesen | **Erforderlich** |
| `Files.ReadWrite.All` | Freigaben lesen und widerrufen | **Erforderlich** |
| `SharePointTenantSettings.ReadWrite.All` | Tenant-Sharing-Einstellungen ändern (Tenant-Härtung) | Empfohlen für Schreib |

#### Sicherheit & Identity

| Berechtigung | Zweck | Typ |
|---|---|---|
| `Policy.Read.All` | Conditional Access Policies, Named Locations, Auth-Strength, Tenant-Policies | **Erforderlich** |
| `Policy.ReadWrite.ConditionalAccess` | CA-Policies + Named Locations + Auth-Strength anlegen/ändern/löschen | **Erforderlich** für Schreib |
| `RoleManagement.Read.Directory` | Admin-Rollen-Zuweisungen, PIM lesen | **Erforderlich** |
| `RoleManagement.ReadWrite.Directory` | Admin-Rollen zuweisen/entfernen | Empfohlen |
| `Application.Read.All` | App-Registrierungen, Enterprise-Apps, Service-Principals | **Erforderlich** |
| `Application.ReadWrite.All` | App-Secrets verwalten, OAuth-Audit | Empfohlen |
| `IdentityRiskyUser.Read.All` | Risiko-Benutzer (read-only) | **Erforderlich** |
| `IdentityRiskyUser.ReadWrite.All` | Risiko bestätigen / verwerfen | Empfohlen für Schreib |
| `IdentityRiskEvent.Read.All` | Risk Detections (Identity Protection) | Empfohlen (braucht Entra ID P2) |
| `SecurityAlert.Read.All` / `SecurityAlert.ReadWrite.All` | Defender Alerts lesen + auflösen | Empfohlen |
| `SecurityEvents.Read.All` | Secure Score und Sicherheitsereignisse | Empfohlen |
| `Policy.ReadWrite.Authorization` | Gast-Einladungs-Regeln & App-Consent-Defaults (Tenant-Härtung) | Empfohlen für Schreib |
| `AttackSimulation.Read.All` | Phishing-Simulationen (Defender Attack Simulation) | Optional |
| `LifecycleWorkflows.Read.All` | Lifecycle Workflows (Entra ID Governance) | Optional |
| `IdentityProvider.Read.All` | Externe Identity Provider (Google/Facebook/SAML/WS-Fed) | Optional |
| `BitLockerKey.Read.All` | BitLocker-Recovery-Keys auf Geräten | Optional |

#### E-Mail & Compliance

| Berechtigung | Zweck | Typ |
|---|---|---|
| `Mail.ReadBasic.All` | Postfach-Basisinfos | Empfohlen |
| `Mail.Read` | Mailbox-Regeln (Auto-Forward-Audit) | Empfohlen |
| `MailboxSettings.ReadWrite` | Postfach-Settings, Auto-Reply, Forwarding | Empfohlen |
| `InformationProtectionPolicy.Read.All` | Sensitivity Labels (DLP-Modul) | Empfohlen |
| `eDiscovery.Read.All` | eDiscovery-Cases (Aufbewahrungsrichtlinien) | Optional |

#### Geräte (Intune)

| Berechtigung | Zweck | Typ |
|---|---|---|
| `DeviceManagementManagedDevices.Read.All` | Intune-Geräte lesen | Empfohlen |
| `DeviceManagementManagedDevices.ReadWrite.All` | Sync, Retire, Wipe | Empfohlen für Schreib |
| `DeviceManagementManagedDevices.PrivilegedOperations.All` | Privilegierte Aktionen (Sync/Retire/Wipe) | Empfohlen |

#### Dienststatus & Roadmap

| Berechtigung | Zweck | Typ |
|---|---|---|
| `ServiceHealth.Read.All` | M365-Dienststatus und Incidents | Empfohlen |
| `ServiceMessage.Read.All` | Message Center / Roadmap-Nachrichten | Optional |

#### Sonstiges

| Berechtigung | Zweck | Typ |
|---|---|---|
| `Mail.Send` | E-Mails über Graph senden (alternativ zu SMTP) | Optional |
| `Policy.ReadWrite.CrossTenantAccess` | Mandantenübergreifende Richtlinien | Optional |


---

## Cron & Job-Queue

### Einziger Cron-Eintrag

```
* * * * * php /var/www/m365-tenant-tool/run-cron.php >> /var/log/m365-cron.log 2>&1
```

Der Cron läuft jede Minute und entscheidet intern anhand konfigurierter Intervalle, welche Aufgaben fällig sind.

### Enthaltene Jobs

| Job | Standard-Intervall | Beschreibung |
|---|---|---|
| **Job-Queue verarbeiten** | Jede Minute | Async Graph-API-Writes (Lizenzen, Bulk-Aktionen) |
| **Freigaben scannen** | Stündlich | Synct SharePoint-Freigaben aus Graph |
| **Review-E-Mails senden** | Stündlich | Sendet fällige Besitzer-Anfragen |
| **Auto-Widerruf** | Stündlich | Widerruft Freigaben ohne Reaktion |
| **Inaktive Konten bereinigen** | Täglich | Auto-Lizenzfreigabe (wenn aktiviert) |
| **Queue aufräumen** | Täglich | Löscht alte abgeschlossene Jobs |

Alle Intervalle und der Aktiviert-Status lassen sich im Web-UI unter **Cron & Automatisierung** ändern. Dort ist auch ein „Jetzt ausführen"-Button pro Job.

### Job-Queue

Schreib-Operationen auf die Graph API (Lizenzen zuweisen/entziehen, Benutzer aktivieren, MFA reset bei Bulk-Aktionen) werden nicht synchron ausgeführt, sondern in die `job_queue`-Tabelle geschrieben. Der Cron verarbeitet pro Minute bis zu 20 Items mit automatischem Retry und Exponential Backoff (max. 3 Versuche).

---

## Freigaben-Governance (Workflow)

```
Cron: Freigaben scannen (stündlich)
    └── Neue externe Freigabe erkannt → in share_reviews gespeichert

Cron: Review-E-Mails (stündlich)
    └── next_review_at <= NOW()
        └── Einmal-Token generiert → E-Mail an Freigabe-Besitzer
            └── Link: https://m365.firma.de/review/{token}  (kein Login nötig)

Besitzer bestätigt:
    └── Begründung eingeben → confirmed, next_review_at += interval_days

Besitzer reagiert nicht:
    └── Cron: Auto-Widerruf
        └── auto_revoke_at <= NOW() → Graph DELETE → status = revoked
```

Konfigurierbar unter Einstellungen: **Prüfintervall** (wie oft eine Bestätigung angefordert wird) und **Toleranzzeit** (Zeitfenster bis zum automatischen Widerruf).

---

## Sicherheit

### Verschlüsselung

Alle sensitiven Werte werden mit **AES-256-GCM** + zufälligem IV in `app_config` gespeichert:

| Wert | Gespeichert als |
|---|---|
| `tenant_id`, `client_id`, `client_secret` | AES-256-GCM verschlüsselt |
| `db_password` | AES-256-GCM verschlüsselt |
| `admin_password` | bcrypt-Hash, zusätzlich verschlüsselt |
| `smtp_password` | AES-256-GCM verschlüsselt |

Der Verschlüsselungsschlüssel liegt in `storage/app.key` (256 Bit, base64). **Diese Datei muss gesichert werden** — ohne sie sind alle Credentials unlesbar.

```bash
chmod 600 /var/www/m365-tenant-tool/storage/app.key
chown www-data:www-data /var/www/m365-tenant-tool/storage/
```

### Empfehlungen

- `storage/` per Apache-Config vor Webzugriff sperren (bereits in VirtualHost enthalten)
- HTTPS erzwingen, HTTP → HTTPS Redirect
- `app.key` in Backup aufnehmen und separat sichern
- MySQL-Benutzer: nur `SELECT`, `INSERT`, `UPDATE`, `DELETE` auf die App-Datenbank
- Azure AD App Secret mit kurzer Laufzeit (6–12 Monate) und Rotationsplan

---

## Verzeichnisstruktur

```
m365-tenant-tool/
├── .htaccess                        # URL-Rewriting, Sicherheits-Header
├── composer.json
├── index.php                        # Front Controller / Router
├── run-cron.php                     # Einziger Cron-Einstiegspunkt
├── setup-server.sh                  # Server-Ersteinrichtung (Apache, Rechte)
├── install/                         # Web-Installer (nach Setup gesperrt)
│   ├── InstallerController.php
│   ├── index.php
│   └── steps/                       # Wizard-Schritte 1–5
├── src/
│   ├── Auth/
│   │   ├── LocalAuth.php            # Session-Login (Admin / Operator)
│   │   └── GraphTokenManager.php   # OAuth2 Client Credentials Token
│   ├── Cache/
│   │   └── GraphCache.php           # MySQL-basierter Graph-Response-Cache
│   ├── Core/
│   │   ├── Config.php               # Konfiguration aus DB mit Verschlüsselung
│   │   ├── Router.php
│   │   ├── Session.php
│   │   └── View.php
│   ├── Database/
│   │   ├── DB.php                   # PDO-Wrapper
│   │   └── Schema.sql               # Vollständiges Datenbankschema
│   ├── Encryption/
│   │   └── Encryptor.php            # AES-256-GCM
│   ├── Graph/
│   │   └── GraphClient.php          # HTTP-Client mit Cache + Pagination
│   ├── Helpers/
│   │   ├── CsvExporter.php
│   │   └── Mailer.php               # PHP mail() + SMTP
│   ├── Modules/
│   │   ├── AppRegistrations/        # App-Registrierungen & Enterprise Apps
│   │   ├── AuditLog/
│   │   ├── Auth/
│   │   ├── Cron/                    # Cron-Orchestrator + Controller
│   │   ├── Dashboard/
│   │   ├── Devices/                 # Intune-Geräte
│   │   ├── Groups/                  # Gruppen & Teams
│   │   ├── GuestUsers/              # B2B-Gastbenutzer
│   │   ├── Licenses/
│   │   ├── Mailboxes/               # Exchange Mailbox-Nutzung
│   │   ├── OneDrive/
│   │   ├── RiskySignIns/            # Identity Protection
│   │   ├── SecureScore/
│   │   ├── Security/                # Conditional Access
│   │   ├── ServiceHealth/           # M365-Dienststatus
│   │   ├── Settings/
│   │   ├── SharePoint/
│   │   ├── ShareReview/             # Freigaben-Governance (öffentliche Seiten)
│   │   ├── Sharing/                 # Externe Freigaben
│   │   ├── SharingPolicies/         # Globale Freigaberichtlinien
│   │   ├── StaleAccounts/           # Inaktive Konten + Auto-Release
│   │   └── Users/                   # Benutzer mit Bulk-Aktionen
│   └── Queue/
│       ├── QueueDispatcher.php      # Jobs in DB-Queue schreiben
│       └── QueueWorker.php          # Jobs verarbeiten (Graph-Writes)
├── views/
│   ├── layout/
│   │   ├── base.php
│   │   └── sidebar.php
│   └── sharereview/
│       ├── _brand.php               # Branding-Helper für öffentliche Seiten
│       ├── review.php               # Öffentliche Bestätigungsseite (kein Login)
│       ├── confirmed.php
│       └── expired.php
├── public/
│   ├── css/app.css
│   └── js/app.js
└── storage/                         # Nicht web-öffentlich!
    ├── app.key                      # AES-256-GCM Schlüssel
    ├── db_bootstrap.ini             # DB-Verbindung für Bootstrap
    ├── cron.lock                    # Lock-Datei gegen parallele Cron-Läufe
    └── installed.lock               # Markiert abgeschlossene Installation
```

---

## Datenbankschema

| Tabelle | Inhalt |
|---|---|
| `app_config` | App-Konfiguration (Credentials AES-verschlüsselt) |
| `graph_tokens` | OAuth2-Tokens (gecacht) |
| `cache` | Graph-API-Response-Cache |
| `audit_log` | Interne App-Aktionen |
| `share_reviews` | Überwachte externe Freigaben |
| `share_review_tokens` | Einmal-Token für Bestätigungslinks |
| `stale_account_log` | Protokoll automatischer Lizenzentnahmen |
| `cron_jobs` | Cron-Job-Status und konfigurierte Intervalle |
| `job_queue` | Async-Queue für Graph-API-Schreiboperationen |

---

## Lizenz

MIT
