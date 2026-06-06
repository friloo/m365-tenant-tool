# Sicherheits-Evaluierung & Härtung (Juni 2026)

Diese Evaluierung beantwortet die Frage: **Ist das M365 Tenant Tool geeignet, einen
Microsoft-365-Tenant abzusichern und „im Griff" zu haben — auch sicherheitstechnisch,
betrieben auf einem isolierten Server?**

Kurzfassung: **Ja**, mit dem in dieser Runde behobenen Satz an Schwachstellen und unter
Beachtung von [`DEPLOYMENT-HARDENING.md`](DEPLOYMENT-HARDENING.md). Das Fundament ist solide:
**keine SQL-Injection, keine Command-Injection**, Installer-Sperre, CSRF, Session- und
Token-Handling sowie das Secret-Handling (AES-256-GCM) wurden verifiziert und sind in Ordnung.

---

## 1 · Triage der früheren Review-Funde (REVIEW-2026-06.md)

Die in `REVIEW-2026-06.md` gelisteten Bugs wurden gegen den aktuellen Code geprüft.
**Nahezu alles war bereits in den vorangegangenen Commits behoben** (mit erklärenden
Code-Kommentaren):

| Fund | Status |
|---|---|
| K1 `assignLicense` PATCH→POST | ✅ behoben |
| K2 `confirmCompromised`/`dismiss` PATCH→POST | ✅ behoben |
| K3 Forwarding patcht `/mailboxSettings` (+ `ltrim`-Bug, nicht-existente Property) | ✅ behoben |
| K4 Cache-Key-Kollision `dash_mfa_pct` | ✅ behoben (3 distinkte Keys) |
| K5 Cron-Jobs falsche Array-Keys | ✅ behoben |
| K6 Job-Claiming nicht atomar | ✅ behoben (conditional UPDATE) |
| S1 API-Key als Query-String | ✅ behoben (nur Header) |
| S2 `Retry-After` falsches cURL-Feld | ✅ behoben (Header-Funktion) |
| S3 `getEventual` cached 403/404 | ✅ behoben (`lastError`-Guard) |
| S4 AiAdvisor falsche SKU-Keys | ✅ behoben |
| S5 Auto-Revoke entfernt interne Rechte | ✅ behoben (Tenant-Domain-Abgleich) |
| S7 Inkonsistente Rollen-Checks | ✅ behoben (`requireAdmin`) |
| S8 Ungeescapte OData-Filter | ✅ behoben (`escapeODataValue`) |
| S9 CSV-Injection | ✅ behoben (Formel-Präfix) |
| S11a API hardening/compliance Apply nur `write` | ✅ behoben (`admin`) |
| **S6** Scan suggeriert Vollständigkeit | 🔧 **in dieser Runde behoben** |
| **S10** SMTP-Mailer meldet immer Erfolg | 🔧 **in dieser Runde behoben** (siehe #2) |
| **S11b** roher `$filter`-Durchgriff (read-Key) | 🔧 **in dieser Runde behoben** |

---

## 2 · Frischer Security-Audit (gesamter Code)

Über die alte Liste hinaus wurde der gesamte Code auf SQLi, XSS, AuthZ, den öffentlichen
`/review/{token}`-Endpunkt, Installer, SSRF, Secret-Handling, CSRF, Command-Injection und
Krypto geprüft. **Verifiziert in Ordnung:** Installer-Sperre (lock + DB-Admin-Guard), CSRF
(Router erzwingt `hash_equals` auf allen state-changing Routen außer der key-authentisierten
API), ShareReview-Token (256-bit, single-use, Ablauf, Rotation), Session (HttpOnly/Strict/
Secure, Regeneration, Idle-Timeout), Secret-Handling (keine Leaks, `/health` nur Existenz),
keine SQLi, keine Command-Injection.

**Neue Funde — alle in dieser Runde behoben:**

| # | Schwere | Fund | Datei |
|---|---|---|---|
| 1 | **HIGH** | Zip Slip / Path-Traversal im OTA-Updater → RCE | `src/Update/UpdateManager.php` |
| 2 | **HIGH** | SMTP-/Mail-Header-Injection (CRLF) + Erfolg ohne Reply-Check | `src/Helpers/Mailer.php` |
| 3 | MEDIUM | XSS: dynamische Graph-Werte roh im `detail`-HTML | `src/Modules/Hardening/HardeningService.php` |
| 4 | MEDIUM | Kein Rate-Limit auf API-Key-Auth | `src/Modules/Api/ApiAuth.php` |
| 5 | MEDIUM | Open Redirect über `HTTP_REFERER` in `Redirect::back()` | `src/Core/Redirect.php` |

---

## 3 · Umgesetzte Fixes (Commit „security: fix Zip Slip …")

1. **Zip Slip (HIGH):** `extractZip` weist nun absolute Pfade, Windows-Laufwerksbuchstaben und
   jede `..`-Traversierung ab (nach Backslash-Normalisierung), plus `realpath`-Containment-Check.
   Ein manipuliertes Update-Paket kann keine beliebigen Dateien mehr überschreiben.
   *Symlink-Einträge sind unkritisch, da `getFromIndex()` + `file_put_contents()` reguläre
   Dateien schreiben.*
2. **Mail-Header-Injection (HIGH):** `$to`/`$subject`/`$from`/`$appName` werden von CR/LF/NUL
   bereinigt (beide Versandpfade: SMTP **und** `mail()`-Fallback). SMTP prüft jetzt die
   Reply-Codes (220/250/334/235/354) und gibt bei `550` etc. `false` statt fälschlich `true`
   zurück. Body wird dot-gestufft.
3. **XSS im Hardening-Detail (MED):** Alle dynamischen Graph-Werte und Exception-Texte werden
   mit `htmlspecialchars(…, ENT_QUOTES)` an der Quelle escapt; nur statisches HTML (Links)
   bleibt roh. Verbleibende rohe Interpolationen sind rein numerisch (`(int)`).
4. **API-Rate-Limit (MED):** Neue Tabelle `api_auth_failures`; pro IP max. 30 Fehlversuche in
   5 Minuten → `429`. `REMOTE_ADDR` (nicht spoofbares `X-Forwarded-*`), fail-open nur bei DB-Fehler.
5. **Open Redirect (MED):** `Redirect::back()` folgt dem Referer nur bei Same-Origin (sonst `/`);
   `//host`, fremde Hosts, userinfo-`@`-Tricks werden abgewiesen. `Redirect::to()` strippt CR/LF/NUL.
6. **S6 Scan-Abdeckung:** `scanAndSync` verfolgt Kürzungen (Sites/Drives/Unterordner) und gibt
   eine explizite **„Abdeckung UNVOLLSTÄNDIG"-Warnung** aus — „keine externen Freigaben" wird
   nie mehr fälschlich suggeriert.
7. **S11b OData-`$filter`:** read-Keys können keinen beliebigen OData mehr durchreichen;
   konservative Allowlist + Längenlimit (256), sonst `400`.

Alle Änderungen: `php -l` sauber, Logik der Schutzfunktionen per isolierten Tests verifiziert,
unabhängiger Security-Re-Review des Diffs ohne neue Funde.

---

## 4 · Bewusst zurückgestellt (mit Begründung)

Diese Punkte sind **niedriges Risiko** bzw. erfordern Infrastruktur/Refactoring, das den
Rahmen dieser Härtungsrunde sprengt. Empfehlung dokumentiert, Umsetzung optional:

- **Update-Paket-Signatur (ergänzend zu Fix #1):** Die OTA-ZIP wird nur per Magic-Byte geprüft,
  nicht signaturverifiziert. Die eigentliche RCE-Primitive (Zip Slip) ist geschlossen; eine
  Kompromittierung erfordert nun zusätzlich die Übernahme des HTTPS-Update-Endpunkts. Eine
  echte Signaturprüfung braucht eine Schlüsselverteilung. **Empfehlung:** OTA-Update deaktivieren
  und Updates manuell als Deploy-User einspielen (siehe Hardening-Guide §7).
- **CSP `'unsafe-inline'` (MEDIUM):** Die CSP erlaubt Inline-Skripte, da das Layout Inline-
  `<script>` nutzt. Vollständige Behebung erfordert das Auslagern aller Inline-Skripte. Da das
  Tool keine bekannte XSS-Senke mehr hat (Fix #3) und admin-only ist, vertretbar. **Empfehlung:**
  bei Gelegenheit Inline-Skripte in Dateien auslagern und `'unsafe-inline'` entfernen.
- **TOTP-Replay (LOW):** Ein gültiger 6-stelliger Code ist innerhalb seines ±1-Zeitfensters
  (~90 s) wiederholbar. Praktisch durch das Login-Rate-Limit (5/15 min) eng begrenzt.
  **Empfehlung:** zuletzt genutzten Zeitschritt pro Admin speichern und Wiederverwendung ablehnen.
- **AI-SSRF über `ai_base_url` (LOW):** Admin-konfigurierbare URL wird server-seitig abgerufen.
  Nur Admin, kein Master-Secret im Request. Ein Block privater IPs würde legitimen lokalen
  Ollama-Betrieb (localhost) brechen — daher **akzeptiert**; nur in Umgebungen mit striktem
  Egress-Filter relevant (siehe Hardening-Guide §0).

---

## 5 · Fazit

Das Tool ist für den abgesicherten Betrieb eines einzelnen Tenants geeignet. Nach dieser
Runde sind alle bekannten und neu gefundenen ausnutzbaren Schwachstellen (inkl. zweier HIGH-
Funde) geschlossen. Der wichtigste verbleibende Hebel liegt **im Betrieb**, nicht im Code:
isolierter Server, Least-Privilege-Graph-Permissions, CA-IP-Restriktion für die App-Anmeldung,
serverseitige Sperre von `storage/`/`install/` und getrennte Backups des `app.key`. Diese
Schritte sind in [`DEPLOYMENT-HARDENING.md`](DEPLOYMENT-HARDENING.md) abgehakt-fähig beschrieben.
