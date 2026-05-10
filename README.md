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
| **Gastbenutzer** | B2B-Gäste, Einladungsstatus, zuletzt aktiv, nie angemeldet | Deaktivieren, Entfernen |
| **Gruppen & Teams** | Alle M365-Gruppen und Microsoft Teams mit Mitgliederzahl, Typ, Sichtbarkeit | Mitglieder hinzufügen/entfernen |
| **Lizenzen** | Verbrauchsübersicht je SKU, freie Slots, Nutzer ohne Lizenz | — |
| **Inaktive Konten** | Benutzer ohne Anmeldung seit X Tagen (konfigurierbar), Lizenz-Kosten-Warnung; Aktionslog | Lizenzen entziehen; optionaler **Auto-Release per Cron** |

### Exchange & Kommunikation

| Modul | Was es zeigt | Aktionen |
|---|---|---|
| **Postfächer** | Mailbox-Nutzung aller User (Größe, Item-Anzahl, letzte Aktivität), Statistiken | CSV-Export |
| **Dienststatus** | Live-Status aller M365-Dienste, aktive Incidents & Advisories, letzte Service-Meldungen | Auto-Refresh alle 5 Min. |

### Speicher & Freigaben

| Modul | Was es zeigt | Aktionen |
|---|---|---|
| **OneDrive** | Speichernutzung aller Nutzer | — |
| **SharePoint** | Site Collections, Drives, Speichernutzung pro Site | — |
| **Freigaben** | Alle externen und anonymen Freigaben im Tenant | Widerrufen |
| **Freigaben-Monitor** | Vollautomatisches Monitoring externer Freigaben: Erkennung, E-Mail-Review, Token-Link, Auto-Widerruf | Manuell widerrufen, Erinnerung senden, Scan auslösen |
| **Freigaberichtlinien** | Globale SharePoint/OneDrive-Sharing-Einstellungen, Pro-Site-Konfiguration, Teams-Extern-Zugriff | Ändern (erfordert `SharePoint.ReadWrite.All`) |

### Sicherheit

| Modul | Was es zeigt | Aktionen |
|---|---|---|
| **Sicherheit** | Conditional Access Policies, Risikobenutzer-Übersicht | — |
| **Secure Score** | Aktueller Score, 30-Tage-Verlauf (Chart), Maßnahmen nach Kategorie mit Fortschrittsbalken | — |
| **Risiko-Anmeldungen** | At-Risk-Benutzer, Risk-Detections, risikoreiche Sign-ins; Typ-Labels auf Deutsch | Als kompromittiert markieren, Risiko zurücksetzen (Admin) |
| **App-Registrierungen** | Alle App-Registrierungen und Enterprise Apps, ablaufende Secrets, Berechtigungs-Typ | — |
| **Geräte** | Intune-verwaltete Geräte, Compliance-Status, OS-Versionen | CSV-Export |
| **Audit-Log** | Verzeichnis-Audits und Sign-in-Logs aus Graph | CSV-Export |

### Administration

| Modul | Was es zeigt | Aktionen |
|---|---|---|
| **Cron & Automatisierung** | Alle geplanten Aufgaben mit Status, letztem Lauf, nächstem Lauf, Logs; Job-Queue-Statistiken und -Items | Job sofort ausführen, Intervall konfigurieren, fehlgeschlagene Jobs wiederholen |
| **Einstellungen** | App, SMTP/Alerts, Operator-Konto, Freigaben-Monitor, Inaktive Konten (Auto-Release), Branding | Admin only |

### Querschnittsfunktionen

- **CSV-Export** auf jedem Listenmodul
- **Rollen-System**: `admin` (voll) · `operator` (schreibend, keine Einstellungen)
- **Bulk-Aktionen** auf der Benutzerliste (Checkbox-Auswahl → Deaktivieren / Aktivieren / MFA reset) — asynchron über Job-Queue
- **E-Mail-Alerts**: Risikobenutzer, MFA-Quote unter Schwellwert, anonyme Freigaben, Auto-Lizenzfreigabe
- **Freigaben-Governance**: Besitzer per E-Mail befragen, Bestätigung per einmaligem Token-Link (kein Login), automatischer Widerruf bei Nicht-Reaktion, konfigurierbares Branding der öffentlichen Seite
- **Job-Queue**: Schreib-Operationen auf die Graph API (Lizenzen, Bulk) werden asynchron über die DB-Queue verarbeitet — kein Timeout, automatisches Retry mit Exponential Backoff
- **Cron-Orchestrator**: Ein einziger Cron-Job (`* * * * *`) steuert alle Aufgaben über konfigurierbare Intervalle
- **Graph-Cache**: API-Antworten in MySQL gecacht (konfigurierbare TTL, Standard 15 Min.)
- **AES-256-GCM-Verschlüsselung** aller Credentials in der Datenbank

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

#### Benutzer & Verzeichnis

| Berechtigung | Zweck | Typ |
|---|---|---|
| `User.Read.All` | Benutzer, MFA-Status, Anmeldungen lesen | **Erforderlich** |
| `User.ReadWrite.All` | Aktivieren/Deaktivieren, Lizenzen zuweisen | **Erforderlich** |
| `UserAuthenticationMethod.ReadWrite.All` | MFA-Methoden zurücksetzen | **Erforderlich** |
| `Directory.Read.All` | Verzeichnisdaten, Gastbenutzer | **Erforderlich** |
| `AuditLog.Read.All` | Sign-in-Logs und Audit-Log | **Erforderlich** |

#### Gruppen & Teams

| Berechtigung | Zweck | Typ |
|---|---|---|
| `Group.Read.All` | Gruppen und Teams lesen | **Erforderlich** |
| `GroupMember.ReadWrite.All` | Mitglieder hinzufügen/entfernen | **Erforderlich** |
| `TeamMember.Read.All` | Teams-Mitgliederlisten | Empfohlen |

#### SharePoint, OneDrive & Freigaben

| Berechtigung | Zweck | Typ |
|---|---|---|
| `Sites.Read.All` | SharePoint Sites lesen | **Erforderlich** |
| `Files.ReadWrite.All` | Freigaben lesen und widerrufen | **Erforderlich** |
| `SharePoint.ReadWrite.All` | Globale Freigaberichtlinien ändern | Empfohlen |

#### Lizenzen, Berichte & Mail

| Berechtigung | Zweck | Typ |
|---|---|---|
| `Reports.Read.All` | Nutzungsberichte (OneDrive, SharePoint, Mailboxes) | **Erforderlich** |
| `Mail.Send` | E-Mails über Graph senden (alternativ zu SMTP) | Optional |

#### Geräte & Sicherheit

| Berechtigung | Zweck | Typ |
|---|---|---|
| `DeviceManagementManagedDevices.Read.All` | Intune-Geräteverwaltung | Empfohlen |
| `IdentityRiskyUser.ReadWrite.All` | Risikobenutzer lesen und Risiko zurücksetzen | Empfohlen |
| `IdentityRiskEvent.Read.All` | Risk Detections lesen | Empfohlen |
| `Policy.Read.All` | Conditional Access Policies | Empfohlen |
| `SecurityEvents.Read.All` | Microsoft Defender Alerts | Optional |
| `Policy.ReadWrite.CrossTenantAccess` | Mandantenübergreifende Richtlinien | Optional |

#### Dienststatus & Score

| Berechtigung | Zweck | Typ |
|---|---|---|
| `ServiceHealth.Read.All` | M365-Dienststatus und Incidents | Empfohlen |
| `SecurityEvents.Read.All` | Secure Score | Empfohlen |

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
