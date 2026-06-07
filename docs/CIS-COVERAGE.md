# Coverage-Matrix: M365 Tenant Tool ↔ Sicherheits-Baseline

Diese Matrix zeigt **ehrlich**, welche sicherheitsrelevanten Tenant-Einstellungen du **direkt
im Tool konfigurieren** kannst, welche es nur **anzeigt/verlinkt**, und welche **gar nicht über
die Microsoft Graph API möglich** sind (→ Microsoft-Portal oder Exchange-Online-/Security-&-
Compliance-PowerShell nötig).

Orientierung: **CIS Microsoft 365 Foundations Benchmark** und **Microsoft Secure Score**.

**Legende:**

| Symbol | Bedeutung |
|:--:|---|
| 🟢 | **Konfigurierbar im Tool** (Schreib-Aktion über Graph) |
| 🟡 | **Nur Anzeige / Deep-Link** ins Admin-Portal (kein In-Tool-Write) |
| 🔴 | **Nicht über Graph möglich** — nur Microsoft-Portal oder PowerShell |

> Grundprinzip: Das Tool nutzt ausschließlich die **Microsoft Graph API** (Client-Credentials).
> Alles, was Graph nicht schreibend unterstützt (großer Teil von Exchange Online, Microsoft
> Purview und Defender for Office 365), kann eine Graph-only-App **prinzipiell nicht**
> konfigurieren — unabhängig von der Implementierung.

---

## 1 · Identität & Authentifizierung

| Einstellung (CIS-Bezug) | Status | Modul / Hinweis |
|---|:--:|---|
| Security Defaults ein/aus | 🟢 | Security Center (`/hardening`) |
| Conditional-Access-Policies anlegen/aktivieren/löschen | 🟢 | Conditional Access (Vorlagen) |
| Legacy-Authentifizierung blockieren (CA) | 🟢 | Security Center → erzeugt Report-Only-CA-Policy |
| Named Locations (Länder/IP) | 🟢 | Named Locations |
| **Authentifizierungsmethoden-Richtlinie** (SMS/Voice deaktivieren, FIDO2/Authenticator/TAP aktivieren) | 🟢 | **Authentifizierungsmethoden** (`/authmethods`) — *neu* |
| MFA-Registrierungsstatus / Methoden je Nutzer | 🟡 | MFA-Methoden (Anzeige) |
| Auth-Strength-Policies | 🟡 | Auth-Strength (Anzeige) |
| Conditional Access detailliert (alle Bedingungen/Controls) | 🟡 | nur Vorlagen + Toggle; Vollauthoring im Entra-Portal |
| Number-Matching / Authenticator-Feature-Settings | 🟢 | teilweise via Authentifizierungsmethoden-Modul (Methoden-Status) |

## 2 · Privilegierter Zugriff (PIM / Rollen)

| Einstellung | Status | Modul / Hinweis |
|---|:--:|---|
| Admin-Rollen zuweisen/entfernen | 🟢 | Admin-Rollen |
| Aktive vs. dauerhafte privilegierte Rollen (JIT) | 🟡 | PIM (Anzeige) |
| **PIM-Aktivierungsregeln je Rolle** (MFA-/Begründungs-/Approval-Pflicht, max. Dauer) | 🟡 | **PIM-Einstellungen** (`/pimsettings`) — *neu, read-only* |
| PIM-Regeln **ändern** (Approval-Flow, Dauer setzen) | 🟡 | Entra-Portal — Schreiben über Graph möglich, aber bewusst nicht umgesetzt (Risiko); Read-only im Tool |
| Break-Glass-Accounts Health-Check | 🟢 | Break-Glass (Konfiguration/Tracking) |

## 3 · Gäste & externe Identitäten

| Einstellung | Status | Modul / Hinweis |
|---|:--:|---|
| Gast-Einladungen einschränken (`allowInvitesFrom`) | 🟢 | Security Center |
| Gast-Standardrolle „Restricted" | 🟢 | Security Center |
| Gast deaktivieren/entfernen | 🟢 | Gastbenutzer |
| Cross-Tenant-Access-Policy | 🟡 | Cross-Tenant-Access (Anzeige; Write über Graph möglich, derzeit nicht umgesetzt) |

## 4 · Anwendungen & Consent

| Einstellung | Status | Modul / Hinweis |
|---|:--:|---|
| User dürfen keine App-Registrierungen anlegen | 🟢 | Security Center |
| User dürfen keine Tenants/Security-Gruppen anlegen | 🟢 | Security Center |
| App-Consent-Policy (User-Consent einschränken) | 🟢 | Security Center |
| OAuth-/Enterprise-Apps Risiko-Audit | 🟡 | OAuth-App-Audit (Deep-Link Entra) |
| App-Secrets verwalten | 🟢 | App-Registrierungen |

## 5 · SharePoint / OneDrive / Freigaben

| Einstellung | Status | Modul / Hinweis |
|---|:--:|---|
| External-Sharing-Capability (SharePoint & OneDrive) | 🟢 | Security Center / Freigaberichtlinien |
| Anonyme Link-Ablaufzeit | 🟢 | Security Center |
| Default-Link-Typ (intern) | 🟢 | Security Center |
| Externes Re-Sharing blockieren | 🟢 | Security Center |
| Pro-Site-Sharing-Einstellungen | 🟢 | Freigaberichtlinien |
| Externe Freigaben prüfen/widerrufen + Governance | 🟢 | Freigaben / Freigaben-Monitor |

## 6 · Exchange Online / E-Mail-Sicherheit

| Einstellung | Status | Modul / Hinweis |
|---|:--:|---|
| Externe Auto-Weiterleitung pro Postfach erkennen/entfernen | 🟢 | Externe Weiterleitungen / Postfächer |
| Mailbox-Forwarding / Auto-Reply setzen | 🟢 | Postfächer |
| Shared Mailbox anlegen | 🟢 | Postfächer |
| **Anti-Phishing-Policy** (Impersonation, Mailbox-Intelligence) | 🔴 | Exchange-Online-PowerShell / Defender-Portal |
| **Anti-Spam- / Anti-Malware-Policy** | 🔴 | Exchange-Online-PowerShell / Defender-Portal |
| **Safe Links / Safe Attachments** (Defender for Office 365) | 🔴 | Defender-Portal / PowerShell |
| **Transport-/Mail-Flow-Regeln** | 🔴 | Exchange-Online-PowerShell |
| **DKIM-Signierung aktivieren** | 🔴 | Exchange-Online-PowerShell (SPF/DMARC = DNS) |
| **„External"-Tag in Outlook** | 🔴 | `Set-ExternalInOutlook` (EXO-PowerShell) |
| Externe Weiterleitung tenant-weit blockieren (Outbound-Spam-Policy) | 🔴 | EXO-PowerShell (`Set-HostedOutboundSpamFilterPolicy`) |
| DKIM/DMARC/SPF-Status prüfen | 🟡 | Domain Health (Anzeige) |

## 7 · Daten-Governance (Microsoft Purview)

| Einstellung | Status | Modul / Hinweis |
|---|:--:|---|
| **DLP-Richtlinien** anlegen/verwalten | 🔴 | Purview-Portal / Security-&-Compliance-PowerShell — *keine Graph-API* |
| DLP-Vorfälle (aus Audit-Log) | 🟡 | DLP-Vorfälle |
| **Sensitivity Labels** anlegen/veröffentlichen | 🔴 | Purview-Portal — Graph nur **Lesen** (beta) |
| Sensitivity Labels anzeigen | 🟡 | Sensitivity Labels |
| **Retention-Policies/-Labels** verwalten | 🔴 | Purview-Portal — *keine Graph-API* (Retention-Labels nur beta-read) |
| eDiscovery-Fälle | 🟡 | eDiscovery (`/ediscovery`) |
| Unified Audit Log aktivieren | 🔴 | EXO-PowerShell (heute meist default-an) |
| Audit-/Sign-in-Logs auswerten | 🟡 | Audit-Log / Sign-in-Log / Audit-Diff |

## 8 · Geräte (Intune)

| Einstellung | Status | Modul / Hinweis |
|---|:--:|---|
| Geräte Sync / Retire / Wipe | 🟢 | Geräte (Aktionen) |
| Compliance-/Konfigurations-Policies **authoring** | 🟡 | nur Anzeige; Policy-Erstellung im Intune-Portal |
| BitLocker-Recovery-Keys | 🟡 | Geräte (Anzeige, `BitLockerKey.Read.All`) |

## 9 · Defender / Detection & Response

| Einstellung | Status | Modul / Hinweis |
|---|:--:|---|
| Secure Score & Verlauf | 🟡 | Secure Score |
| Defender-Alerts auflösen | 🟢 | Defender Alerts (Response) |
| Risiko-Benutzer bestätigen/verwerfen | 🟢 | Risiko-Anmeldungen |
| Defender-for-Endpoint/Identity/Cloud-Apps-**Settings** | 🔴 | jeweiliges Defender-Portal |
| Phishing-Simulationen | 🟡 | Phishing-Simulationen (Anzeige) |

---

## Zusammenfassung

- **🟢 Voll im Tool konfigurierbar:** der gesamte **Graph-schreibbare Identitäts-, App-,
  Gast- und Sharing-Block** — das deckt einen großen Teil der CIS-/Secure-Score-„Quick Wins" ab.
- **🟡 Nur Anzeige/Response:** Posture-Checks, Logs, Secure Score, PIM-Settings, Cross-Tenant —
  gut für Überblick & Audit, Konfiguration teils im Portal.
- **🔴 Nicht über Graph:** **Defender for Office 365 / EOP-Policies, DLP, Purview-Labels &
  Retention, Transport-Regeln, DKIM, External-Tag**. Diese erfordern zwingend
  **Exchange-Online-/Security-&-Compliance-PowerShell** bzw. die jeweiligen Portale.
  Für genau diese Bereiche bietet das Tool auf den betreffenden Seiten **direkte Deep-Links**
  ins Portal **und kopierbare PowerShell-Befehle** (Verbinden, Auflisten, einsatzfertiges
  Beispiel), sodass die Konfiguration mit einem Klick/Copy startet.

**Praxis-Empfehlung:** Das Tool als zentrales Cockpit für Identität/Apps/Gäste/Sharing nutzen
(inkl. der hier neuen Module Authentifizierungsmethoden & PIM-Einstellungen). Für einen
**vollständigen** CIS-/Secure-Score-Baseline ergänzend **EXO-PowerShell** (Defender-Office,
DKIM, Transport) und das **Purview-Portal** (DLP, Labels, Retention) verwenden.
