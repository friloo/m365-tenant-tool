# M365 Tenant Tool

Ein webbasiertes Admin-Dashboard für einen einzelnen Microsoft 365 Tenant. Greift über die Microsoft Graph API per **Client Credentials Flow** zu – kein Microsoft-Login für Endnutzer erforderlich. Das Web-Interface ist durch ein lokales Benutzerkonto geschützt.

![PHP](https://img.shields.io/badge/PHP-8.1+-777BB4?logo=php&logoColor=white)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-7952B3?logo=bootstrap&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.x-4479A1?logo=mysql&logoColor=white)
![License](https://img.shields.io/badge/Lizenz-MIT-green)

---

## Features

| Modul | Beschreibung | Schreiben |
|---|---|---|
| **Dashboard** | Übersicht: Benutzer, Lizenzen, Freigaben, Geräte, Risikowarnungen | — |
| **Benutzer** | Liste aller Benutzer, MFA-Status, Anmeldestatus, Inaktivitätsfilter | ✓ Aktivieren/Deaktivieren, MFA reset, Lizenzen |
| **Gastbenutzer** | B2B-Gäste, Statistiken, nie angemeldet, inaktiv >90d | ✓ Deaktivieren, Entfernen |
| **Gruppen & Teams** | Alle Gruppen und Microsoft Teams, Mitgliederlisten | ✓ Mitglieder hinzufügen/entfernen |
| **Lizenzen** | Verbrauch je SKU, Nutzer ohne Lizenz, Empfehlungen | — |
| **OneDrive** | Speichernutzung aller Nutzer | — |
| **SharePoint** | Site Collections, Drives, Speichernutzung | — |
| **Freigaben** | Externe & anonyme Freigaben aller Nutzer | ✓ Widerrufen |
| **Freigaben-Monitor** | Automatisches Monitoring, E-Mail-Review, Auto-Widerruf | ✓ Manuell widerrufen/erinnern |
| **Freigaberichtlinien** | Globale SharePoint/Teams-Freigabeeinstellungen, pro Site | ✓ Ändern (mit SharePoint.ReadWrite.All) |
| **Sicherheit** | Conditional Access Policies, Risikobenutzer | — |
| **Geräte** | Intune-verwaltete Geräte, Compliance-Status | — |
| **Audit-Log** | Verzeichnis-Audits und Anmeldeprotokolle | — |
| **Einstellungen** | App-Konfiguration, SMTP, Branding, Freigaben-Monitor | ✓ Admin only |

### Weitere Funktionen

- **CSV-Export** auf jedem Modul
- **Rollen-System**: `admin` (voll) und `operator` (schreibend, ohne Einstellungen)
- **E-Mail-Alerts**: Risikobenutzer, MFA-Quote unter Schwellwert, anonyme Freigaben
- **Freigaben-Governance**: Freigabe-Besitzer per E-Mail befragen, Bestätigung per Token-Link ohne Login, automatischer Widerruf bei Nicht-Reaktion
- **Branding**: Primärfarbe, Logo und Fußzeile der öffentlichen Bestätigungsseite konfigurierbar
- **Graph-Cache**: Antworten der Graph API werden in MySQL gecacht (konfigurierbare TTL)
- **AES-256-GCM-Verschlüsselung** aller Credentials in der Datenbank

---

## Voraussetzungen

| Komponente | Version |
|---|---|
| PHP | 8.1 oder höher |
| Apache | 2.4 mit `mod_rewrite` |
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

    # storage/ vor Webzugriff schützen
    <Directory /var/www/m365-tenant-tool/storage>
        Require all denied
    </Directory>

    SSLEngine on
    SSLCertificateFile    /etc/ssl/certs/firma.crt
    SSLCertificateKeyFile /etc/ssl/private/firma.key
</VirtualHost>
```

> Die `.htaccess` im Projektroot aktiviert URL-Rewriting automatisch. `storage/` muss zwingend vor Webzugriff geschützt sein — dort liegt der Verschlüsselungsschlüssel `app.key`.

### 3. Web-Installer aufrufen

Öffne `https://m365.firma.de/install/` im Browser und folge den 5 Schritten:

| Schritt | Inhalt |
|---|---|
| 1 — Datenbank | MySQL-Zugangsdaten eingeben, Schema wird automatisch angelegt |
| 2 — Admin-Konto | Benutzername und Passwort für den lokalen Admin |
| 3 — Azure AD | Tenant ID, Client ID, Client Secret eingeben und Verbindung testen |
| 4 — Einstellungen | App-Name, öffentliche URL, Cache-TTL, Zeitzone |
| 5 — Fertig | Zusammenfassung und Abschluss |

Nach dem Abschluss wird `storage/installed.lock` angelegt und der Installer ist dauerhaft gesperrt.

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

> Ein **Global Administrator** muss nach dem Hinzufügen aller Berechtigungen die Administratorzustimmung erteilen — ohne diese schlagen alle API-Calls fehl.

#### Kern — Verzeichnis & Benutzer

| Berechtigung | Zweck | Typ |
|---|---|---|
| `User.Read.All` | Benutzer, MFA-Status, Anmeldungen lesen | **Erforderlich** |
| `User.ReadWrite.All` | Benutzer aktivieren/deaktivieren, Lizenzen zuweisen | **Erforderlich** |
| `UserAuthenticationMethod.ReadWrite.All` | MFA-Methoden zurücksetzen | **Erforderlich** |
| `Directory.Read.All` | Verzeichnisdaten, Gastbenutzer lesen | **Erforderlich** |
| `AuditLog.Read.All` | Anmeldeprotokolle und Audit-Log lesen | **Erforderlich** |

#### Gruppen & Teams

| Berechtigung | Zweck | Typ |
|---|---|---|
| `Group.Read.All` | Gruppen und Teams lesen | **Erforderlich** |
| `GroupMember.ReadWrite.All` | Mitglieder hinzufügen und entfernen | **Erforderlich** |
| `TeamMember.Read.All` | Teams-Mitgliederlisten lesen | Empfohlen |

#### SharePoint, OneDrive & Freigaben

| Berechtigung | Zweck | Typ |
|---|---|---|
| `Sites.Read.All` | SharePoint Sites lesen | **Erforderlich** |
| `Files.ReadWrite.All` | Freigaben lesen und widerrufen | **Erforderlich** |
| `SharePoint.ReadWrite.All` | Globale Freigaberichtlinien ändern | Empfohlen |

#### Lizenzen & Berichte

| Berechtigung | Zweck | Typ |
|---|---|---|
| `Reports.Read.All` | Nutzungsberichte (OneDrive, SharePoint) | **Erforderlich** |

#### Geräte & Sicherheit

| Berechtigung | Zweck | Typ |
|---|---|---|
| `DeviceManagementManagedDevices.Read.All` | Intune-Geräteverwaltung | Empfohlen |
| `IdentityRiskyUser.Read.All` | Risikobenutzer-Erkennung | Empfohlen |
| `Policy.Read.All` | Conditional Access Policies lesen | Empfohlen |
| `Policy.ReadWrite.CrossTenantAccess` | Mandantenübergreifende Richtlinien | Optional |

---

## Cron Jobs

Beide Skripte bootstrappen die App selbst und benötigen keinen Webserver.

### E-Mail-Alerts

Prüft täglich Risikobenutzer, MFA-Quote und anonyme Freigaben und sendet E-Mails wenn konfigurierte Schwellwerte überschritten werden.

```bash
0 7 * * * php /var/www/m365-tenant-tool/run-alerts.php >> /var/log/m365-alerts.log 2>&1
```

### Freigaben-Monitor

Scannt alle SharePoint/OneDrive-Freigaben, sendet fällige Review-E-Mails an Freigabe-Besitzer und widerruft automatisch Freigaben, auf die nicht rechtzeitig reagiert wurde.

```bash
0 8 * * * php /var/www/m365-tenant-tool/run-share-monitor.php >> /var/log/m365-share-monitor.log 2>&1
```

---

## Freigaben-Governance (Workflow)

```
Cron: scanAndSync()
    └── Neue externe Freigabe erkannt → in share_reviews gespeichert

Cron: sendDueReviewEmails()
    └── next_review_at <= NOW()
        └── Token generiert → E-Mail an Besitzer
            └── Link: https://m365.firma.de/review/{token}  (kein Login!)

Besitzer bestätigt:
    └── Begründung eingeben → confirmed, next_review_at += interval_days

Besitzer reagiert nicht:
    └── Cron: autoRevokeOverdue()
        └── auto_revoke_at <= NOW() → Graph DELETE → status = revoked
            └── Widerrufs-E-Mail an Besitzer
```

Intervalle werden in den Einstellungen konfiguriert: **Prüfintervall** (wie oft eine Bestätigung angefordert wird) und **Toleranzzeit** (Zeit bis zum automatischen Widerruf nach Erinnerung).

---

## Sicherheit

### Verschlüsselung

Alle sensitiven Werte werden mit **AES-256-GCM** + zufälligem IV verschlüsselt in der MySQL-Tabelle `app_config` gespeichert:

| Wert | Gespeichert als |
|---|---|
| `tenant_id`, `client_id`, `client_secret` | AES-256-GCM verschlüsselt |
| `db_password` | AES-256-GCM verschlüsselt |
| `admin_password` | bcrypt-Hash, zusätzlich verschlüsselt |
| `smtp_password` | AES-256-GCM verschlüsselt |

Der Verschlüsselungsschlüssel liegt ausschließlich in `storage/app.key` (256 Bit, base64). **Diese Datei muss gesichert werden** — ohne sie sind alle Credentials unlesbar.

```bash
chmod 600 /var/www/m365-tenant-tool/storage/app.key
chown www-data:www-data /var/www/m365-tenant-tool/storage/
```

### Empfehlungen

- `storage/` per Apache-Config vor Webzugriff sperren
- HTTPS erzwingen, HTTP-Redirect einrichten
- `app.key` in Backup aufnehmen und sicher verwahren
- MySQL-Benutzer: nur `SELECT`, `INSERT`, `UPDATE`, `DELETE` auf die App-Datenbank
- Azure AD App Secret mit kurzer Laufzeit (6–12 Monate) erstellen und Rotation einplanen

---

## Verzeichnisstruktur

```
m365-tenant-tool/
├── .htaccess                        # URL-Rewriting
├── composer.json
├── index.php                        # Front Controller / Router
├── run-alerts.php                   # Cron: E-Mail-Alerts
├── run-share-monitor.php            # Cron: Freigaben-Monitor
├── install/                         # Web-Installer (nach Setup gesperrt)
│   ├── InstallerController.php
│   ├── index.php
│   ├── steps/                       # Wizard-Schritte 1–5
│   └── views/layout.php
├── src/
│   ├── Auth/
│   │   ├── LocalAuth.php            # Session-Login (Admin / Operator)
│   │   └── GraphTokenManager.php   # OAuth2 Client Credentials Token
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
│   │   └── GraphClient.php          # HTTP-Client für Graph API
│   ├── Helpers/
│   │   ├── AlertRunner.php          # Alert-Logik
│   │   ├── CsvExporter.php          # CSV-Download
│   │   └── Mailer.php               # PHP mail() + SMTP
│   └── Modules/                     # Je Modul: Service + Controller
│       ├── AuditLog/
│       ├── Dashboard/
│       ├── Devices/
│       ├── Groups/
│       ├── GuestUsers/
│       ├── Licenses/
│       ├── OneDrive/
│       ├── Security/
│       ├── Settings/
│       ├── SharePoint/
│       ├── ShareReview/             # Freigaben-Governance
│       ├── Sharing/
│       ├── SharingPolicies/         # Globale Freigaberichtlinien
│       └── Users/
├── views/                           # PHP-Templates
│   ├── layout/
│   │   ├── base.php                 # HTML-Shell mit Sidebar
│   │   └── sidebar.php
│   └── sharereview/
│       ├── _brand.php               # Branding-Helper (öffentliche Seiten)
│       ├── review.php               # Öffentliche Bestätigungsseite (kein Login)
│       ├── confirmed.php
│       └── expired.php
├── public/
│   ├── css/app.css
│   └── js/app.js
└── storage/                         # Nicht web-öffentlich!
    ├── app.key                      # Verschlüsselungsschlüssel
    ├── db_bootstrap.ini             # DB-Verbindung für Bootstrap
    └── installed.lock               # Markiert abgeschlossene Installation
```

---

## Datenbankschema

Das Schema wird beim Installer-Schritt 1 automatisch eingespielt.

| Tabelle | Inhalt |
|---|---|
| `app_config` | App-Konfiguration (Credentials verschlüsselt) |
| `graph_tokens` | OAuth2-Tokens (gecacht) |
| `cache` | Graph-API-Response-Cache |
| `audit_log` | Interne App-Aktionen |
| `share_reviews` | Überwachte externe Freigaben |
| `share_review_tokens` | Einmal-Token für Bestätigungslinks |

---

## Lizenz

MIT
