# Benutzerhandbuch — M365 Tenant Tool

Praxisorientiertes Handbuch für den täglichen Betrieb. Es beschreibt Anmeldung, Navigation und
alle Modulgruppen mit ihren wichtigsten Aktionen. Für Installation/Härtung siehe
[`INSTALL-UBUNTU.md`](INSTALL-UBUNTU.md) und [`DEPLOYMENT-HARDENING.md`](DEPLOYMENT-HARDENING.md),
für den Funktionsumfang gegenüber CIS/Secure Score die [`CIS-COVERAGE.md`](CIS-COVERAGE.md).

---

## 1 · Grundkonzept

- **Server-seitiger Zugriff über Microsoft Graph** (Client-Credentials-Flow). Endnutzer
  brauchen kein Microsoft-Login — sie melden sich nur am Tool an.
- **Zwei Rollen:** `admin` (Vollzugriff inkl. Einstellungen und tenant-verändernde Aktionen)
  und `operator` (Anzeige + ausgewählte schreibende Aktionen, **keine** Einstellungen).
- **Lese- vs. Schreibaktionen:** Listen/Reports sind für beide Rollen sichtbar; tenant-weite
  Sicherheitsänderungen (Security Center, Compliance-Profile, Authentifizierungsmethoden,
  Massen-/Lösch-Aktionen) erfordern `admin`.
- **Jede schreibende Aktion** landet im **App-Audit-Log** (Administration → App Audit-Log).

## 2 · Anmeldung & 2FA

1. `https://<deine-domain>/login` öffnen, mit dem lokalen Admin/Operator-Konto anmelden.
2. **2FA aktivieren** (Einstellungen → 2FA): QR-Code mit einer TOTP-App scannen
   (Microsoft/Google Authenticator, Aegis …), Code bestätigen, **Wiederherstellungscodes
   offline sichern**.
3. Nach 30 Min. Inaktivität wirst du automatisch abgemeldet. 5 Fehlversuche → 15 Min. Sperre
   (pro IP), für Passwort- und 2FA-Schritt getrennt geschützt.

## 3 · Navigation

Linke Sidebar (auf Mobil als Off-Canvas). Gruppen u. a.: *Identität & Zugriff, Lizenzen,
E-Mail & Exchange, Teams & Zusammenarbeit, Sicherheit & Härtung, Identität & Bedrohungen,
Erweiterte Sicherheit, Apps & Konfiguration, Compliance & Audit, Berichte & Monitoring,
Administration*. Unter **Modul-Übersicht** (`/overview`) sind alle Module als durchsuchbare
Karten erreichbar.

> Listenmodule bieten **CSV-Export**. Fehlt eine Graph-Berechtigung, erklärt das Modul
> verständlich, welche Permission fehlt (statt zu crashen) — Details unter
> Einstellungen → Berechtigungen.

---

## 4 · Module nach Gruppen

### 4.1 Identität & Zugriff

| Modul | Zweck | Wichtige Aktionen |
|---|---|---|
| Benutzer | Alle Konten mit MFA-/Anmeldestatus, Lizenzen, letztem Login | Aktivieren/Deaktivieren, MFA-Reset, Passwort-Reset, Lizenz, Bulk-Aktionen (admin) |
| Onboarding / Offboarding | Geführte Konto-Erstellung / Austritt | Anlegen; Deaktivieren, Sessions widerrufen, Lizenzen/Gruppen entziehen |
| Gastbenutzer | B2B-Gäste, Einladungsstatus | Deaktivieren, Entfernen (admin) |
| Gruppen & Teams | M365-Gruppen/Teams | Mitglieder/Owner verwalten, anlegen, löschen |
| Conditional Access · Named Locations | CA-Policies mit Lücken-Analyse + Vorlagen; vertrauenswürdige IP-/Länder-Standorte | Toggle, Anlegen, Löschen |
| **Authentifizierungsmethoden** | Tenant-Richtlinie aller Methoden mit CIS-Empfehlung | **Methode aktivieren/deaktivieren** (admin) |
| Admin-Rollen | Rollenzuweisungen | Zuweisen / Entfernen |

**Authentifizierungsmethoden (neu):** Zeigt je Methode (FIDO2, Microsoft Authenticator, SMS,
Sprachanruf, E-Mail-OTP, TAP, Software-OATH, Zertifikat) den Status und eine Empfehlung.
Leitlinie: **phishing-resistente Methoden aktivieren** (FIDO2, Authenticator), **schwache als
MFA deaktivieren** (SMS, Voice, E-Mail). Der „Aktivieren/Deaktivieren"-Button wirkt **sofort
tenant-weit** (Bestätigungsdialog). Schreiben erfordert `Policy.ReadWrite.AuthenticationMethod`.

### 4.2 Sicherheit & Härtung

| Modul | Zweck |
|---|---|
| **Security Center** (`/hardening`) | Härtungs-Score + One-Click-Toggles aller zentralen Einstellungen (Security Defaults, SharePoint-Sharing, Gast-/App-Consent, Legacy-Auth …) |
| Security Posture / DSGVO-Status | 35+ read-only Checks (Identität, CA, Geräte, Apps, Defender, DSGVO/NIS-2/BSI) |
| Compliance-Profile | Branchen-Presets (Standard/DSGVO, Healthcare, Finance, Public/BSI, Bildung) per One-Click anwenden (admin) |
| Secure Score · Defender Alerts · Risiko-Anmeldungen | Score-Verlauf; Alerts auflösen; Risiko-Benutzer bestätigen/verwerfen |
| Härtungs-Leitfaden | Schritt-Checkliste mit Fortschritt |

**Typischer Härtungs-Ablauf:** Security Center öffnen → Härtungs-Score prüfen → offene Toggles
nacheinander aktivieren (jeweils Bestätigung) → optional ein Compliance-Profil anwenden →
Ergebnis in Security Posture gegenprüfen → mit dem echten Entra/Purview-Portal abgleichen.

### 4.3 Identität & Bedrohungen

| Modul | Zweck |
|---|---|
| Break-Glass-Accounts | Health-Check der Notfall-Admins (Existenz, Global Admin, MFA, CA-Ausnahme) |
| PIM (JIT-Admin) | Aktive/eligible privilegierte Rollen, 30-Tage-Aktivierungs-Audit |
| **PIM-Einstellungen** | **Read-only:** Aktivierungsregeln je Rolle — MFA-/Begründungs-/Genehmigungspflicht, max. Dauer; privilegierte Rollen hervorgehoben |
| Auth-Strength · MFA-Fatigue · Insider-Threat · Phishing-Sim | Detektions-Ansichten |
| DLP-Vorfälle | DLP-Treffer aus dem Audit-Log |

> Hinweis: **Auto-Forward-Audit** wurde mit **Externe Weiterleitungen** zur Seite
> *Weiterleitungen & Regeln* zusammengeführt; **OAuth-App-Audit** liegt jetzt als
> *OAuth-/Enterprise-Apps* neben den App-Registrierungen.
| **DLP-Richtlinien** | **Hinweis-Seite:** DLP-Policies haben keine Graph-API → Deep-Link Purview |

**PIM-Einstellungen (neu):** Für jede Verzeichnisrolle wird angezeigt, ob bei der Aktivierung
MFA/Begründung/Genehmigung verlangt wird und wie lang aktiviert werden darf. Privilegierte
Rollen (Global Admin, Security Admin …) sind gelb markiert — dort sollten MFA + Begründung
„ja" sein. **Ändern** der Regeln erfolgt bewusst im Entra-PIM-Portal (read-only im Tool),
benötigt Entra ID P2 und `RoleManagementPolicy.Read.Directory`.

### 4.4 E-Mail, Teams, Speicher & Freigaben

| Modul | Zweck | Aktionen |
|---|---|---|
| Postfächer / **Weiterleitungen & Regeln** | Mailbox-Nutzung; externe Weiterleitung über **Postfach-Einstellung UND Posteingangsregeln** (zwei Tabs) | Forwarding/Auto-Reply setzen, Weiterleitung entfernen, Shared Mailbox anlegen |
| Mail Flow & Schutz | Service-Status + Defender-for-Office-Alerts (Anzeige) | — |
| OneDrive / SharePoint / Freigaben | Speichernutzung; externe/anonyme Freigaben | Drive (de-)provisionieren; Freigaben widerrufen |
| Freigaben-Monitor | Automatisches Monitoring externer Freigaben mit E-Mail-Review + Auto-Widerruf | Widerrufen, erinnern, Scan |
| Freigaberichtlinien | Globale & Pro-Site-Sharing-Einstellungen | Ändern (admin) |

> **Freigaben-Scan-Abdeckung:** Der Scan ist aus Performance-Gründen begrenzt (Sites/Drives/
> Ordnertiefe). Ist die Abdeckung unvollständig, zeigt das Scan-Log oben eine deutliche
> **„Abdeckung UNVOLLSTÄNDIG"**-Warnung — „keine Freigaben gefunden" heißt dann nicht „es gibt
> keine".

### 4.5 Compliance & Audit

| Modul | Zweck |
|---|---|
| Audit-Log · Sign-in-Log · Audit-Diff | Verzeichnis-Audits/Anmeldungen; A/B-Vergleich von Tenant-Snapshots |
| Access Reviews | Periodische Zugriffsüberprüfungen für Gäste/Apps |
| Geräte (Intune) | Verwaltete Geräte, Compliance, BitLocker | Sync/Retire/Wipe |
| Papierkorb | Soft-deleted Users/Groups | Wiederherstellen, permanent löschen |
| **eDiscovery-Fälle** (`/ediscovery`) | eDiscovery-Cases aus Microsoft Purview (Anzeige) |
| **Aufbewahrung (Retention)** | **Hinweis-Seite:** Retention hat keine Graph-API → Deep-Link Purview |
| Sensitivity Labels | Information-Protection-Labels (Anzeige) |

> Hinweis: `eDiscovery-Fälle` hieß intern früher „retentionpolicies" und war falsch betitelt —
> das ist jetzt korrekt benannt; eine separate, ehrliche **Aufbewahrung**-Seite erklärt die
> Graph-Grenze.

### 4.6 Administration (nur admin)

Cron & Automatisierung, Einstellungen (App/SMTP/Branding/KI …), Benutzer-Zugang, Workflows,
API-Schlüssel + API-Dokumentation, Updates, App-Audit-Log, 2FA, Berechtigungs-Audit
(Einstellungen → Berechtigungen).

### 4.7 Konfiguration & Governance

| Funktion | Zweck |
|---|---|
| **Konfigurations-Center** (`/action-center`) | Startpunkt der Tenant-Konfiguration: Sicherheits-Score, Einrichtungs-Checkliste und priorisierte „nächste Schritte" auf einer Seite. Der Score wird vom Cron `cache_warm` vorberechnet (30-Min-Cache), die Seite lädt daher sofort. |
| **Aktionsfreigaben / Vier-Augen-Prinzip** (`/approvals`) | Optional unter Einstellungen → Datenschutz aktivierbar. Kritische Aktionen (Geräte-Retire/Wipe, Konto deaktivieren, MFA-Reset) müssen von einem zweiten Admin freigegeben werden. Anfragen gelten 24 h, keine Selbst-Freigabe; alles im Audit-Log. |
| **Konfiguration sichern & übertragen** | Einstellungen → Allgemein: Export der operativen Einstellungen als JSON (Backup / Zweit-Tenant). **Secrets werden nie exportiert**; beim Import nur bekannte, nicht-sensible Schlüssel. |
| **Konfigurations-Drift** | Auf *Audit-Diff* eine Baseline festlegen; Cron `config_drift_check` vergleicht täglich den neuesten Snapshot und warnt (In-App + Alert-Webhook) bei sicherheitsrelevanten Abweichungen. |
| **Alert-Webhook (Teams/SIEM)** | Einstellungen → Benachrichtigungen: Warnungen ab gewählter Stufe zusätzlich an Microsoft-Teams-Webhook (MessageCard) oder generisches JSON-Ziel (SIEM/Sentinel/Slack). Test-Button inklusive. |
| **Daten-Retention (DSGVO)** | Einstellungen → Datenschutz: lokale Verlaufs-/PII-Daten älter als die Aufbewahrungsfrist werden täglich vom Cron `local_data_retention` gelöscht (0 = unbegrenzt). Plus Sofort-Bereinigung und unwiderrufliches „Alle lokalen Tenant-Daten löschen" — Konfiguration, Tool-Zugänge und API-Schlüssel bleiben stets erhalten. |
| **Secret-Ablauf-Warnung** | Cron `app_secret_expiry` warnt rechtzeitig, bevor Client-Secret/Zertifikat der eigenen App-Registrierung abläuft. |

> **Sprache:** Das Tool lässt sich komplett zwischen **Deutsch und Englisch** umschalten
> (oben in der Kopfzeile bzw. Einstellungen). Die Auswahl gilt für die gesamte Oberfläche.

> **Erweiterte Module („Mehr"):** Nischen-/Beta-Module (Token-Lifetime, Cross-Tenant-Access,
> Identity Provider Trust, MFA-Fatigue, Insider-Threat, Phishing-Sim, eDiscovery, Customer
> Lockbox) liegen in der jeweiligen Hub-Tableiste im **„Mehr"-Dropdown** — Hauptleiste bleibt
> aufgeräumt, alles bleibt erreichbar.

---

## 5 · REST-API (für BI/Automatisierung)

- Auth über Header `X-Api-Key` (Keys unter Einstellungen → API-Schlüssel).
- Scopes: `read` (GET), `write` (POST), `admin` (Key-Verwaltung **und** tenant-verändernde
  Aktionen wie Härtung/Compliance anwenden).
- Bei zu vielen ungültigen Key-Versuchen pro IP: `429` (Rate-Limit).
- Swagger UI: `/api/docs` (App-Login erforderlich).

## 6 · Was das Tool NICHT kann

Manche Tenant-Sicherheitsbereiche haben **keine Microsoft-Graph-Write-API** und sind daher nur
als Anzeige/Deep-Link enthalten: **Defender-for-Office-365-/EOP-Policies** (Anti-Phishing/Spam/
Malware, Safe Links/Attachments), **DLP-Richtlinien**, **Purview Sensitivity Labels & Retention**
(Erstellen/Veröffentlichen), **Transport-Regeln**, **DKIM-Aktivierung**, **External-Tag in
Outlook**. Diese konfigurierst du im jeweiligen Portal bzw. per Exchange-Online-/Security-&-
Compliance-PowerShell. Vollständige Liste: [`CIS-COVERAGE.md`](CIS-COVERAGE.md).

> **Hilfestellung im Tool:** Auf diesen Seiten (DLP-Richtlinien, Aufbewahrung, Mail Flow & Schutz,
> Domain Health/DKIM, Sensitivity Labels) findest du **direkte Deep-Links** ins richtige Portal
> **und fertige PowerShell-Befehle mit „Kopieren"-Button** — verbinden (`Connect-ExchangeOnline` /
> `Connect-IPPSSession`), Status auflisten und ein einsatzfertiges Beispiel zum Anlegen. So musst
> du die Befehle nicht selbst zusammensuchen.

## 7 · Fehlerdiagnose

| Symptom | Vorgehen |
|---|---|
| Modul zeigt „Berechtigung fehlt" | Einstellungen → Berechtigungen: fehlende Graph-Permission ergänzen + Admin-Consent |
| Aktion meldet Fehler | Meldung lesen (deutschsprachige Graph-Fehlerübersetzung); App-Audit-Log prüfen |
| Reports/Zahlen leer | Graph-Reports brauchen `Reports.Read.All` und hinken ~1–2 Tage nach |
| Cron-Jobs laufen nicht | Administration → Cron: Status/Logs; Cron-Eintrag (`run-cron.php`) prüfen |
| PIM-Einstellungen leer | Entra ID P2 erforderlich; `RoleManagementPolicy.Read.Directory` prüfen |
