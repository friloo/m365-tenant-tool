# Deployment- & Härtungs-Leitfaden (isolierter Server)

> Dieser Leitfaden ist für den Betrieb des M365 Tenant Tools auf einem **dedizierten,
> isolierten Server** gedacht, auf den außer dir niemand Zugriff hat. Er geht über die
> Kurzfassung im README hinaus und ist als **Schritt-für-Schritt-Checkliste** aufgebaut.

## Warum dieser Server besonders schützenswert ist

Die Azure-AD-App-Registrierung, mit der das Tool arbeitet, sammelt **Application-Permissions**
(`User.ReadWrite.All`, `RoleManagement.ReadWrite.Directory`, `Policy.ReadWrite.*`,
`Files.ReadWrite.All`, Geräte-`Wipe` u. a.). Das in der Datenbank verschlüsselt abgelegte
**Client-Secret ist damit faktisch ein tenant-weiter Generalschlüssel**.

Daraus folgt die Kern-Bedrohungsannahme:

> **Wer Root/Code-Ausführung auf diesem Server erlangt, kann den gesamten M365-Tenant
> übernehmen** — auch ohne ein einziges Benutzer-Passwort. Schlüssel zum Entschlüsseln
> (`storage/app.key`), DB-Zugang und damit das Secret liegen alle hier.

Deshalb gilt: minimale Angriffsfläche, minimale Berechtigungen, lückenlose Protokollierung.

---

## 0 · Server-Grundlage

- [ ] **Dedizierter Host/VM**, keine weiteren Dienste/Mandanten darauf.
- [ ] **Voll gepatchtes OS**, automatische Sicherheitsupdates aktiv (`unattended-upgrades`).
- [ ] **SSH gehärtet**: nur Key-Auth (`PasswordAuthentication no`), kein Root-Login
      (`PermitRootLogin no`), idealerweise nur über VPN/Bastion erreichbar.
- [ ] **Host-Firewall (nftables/ufw)**: eingehend nur 443 (und 22 nur aus deinem
      Admin-Netz). Alles andere `deny`.
- [ ] **Festplattenverschlüsselung (LUKS)** — schützt `app.key`/DB-Dateien bei
      physischem Zugriff oder Snapshot-Diebstahl.
- [ ] **Zeit synchron** (`chrony`/`systemd-timesyncd`) — wichtig für TOTP-2FA und
      Token-/Audit-Korrelation.

### Ausgehende Verbindungen (egress) einschränken

Das Tool muss nur eine Handvoll Microsoft-Endpunkte erreichen. Ein ausgehender
Allowlist-Filter begrenzt den Schaden bei einer Kompromittierung erheblich (Exfiltration,
C2). Erlaube ausgehend per Proxy/Firewall nur:

```
login.microsoftonline.com      # OAuth2-Token (Client Credentials)
graph.microsoft.com            # Microsoft Graph API
<dein-smtp-host>:587           # nur falls SMTP-Versand genutzt wird
```

Plus, falls genutzt: dein Paket-Mirror und `github.com` (nur wenn die OTA-Update-Funktion
aktiv bleiben soll — siehe §6).

---

## 1 · Code außerhalb des DocumentRoot deployen (wichtigste Maßnahme)

Das mitgelieferte `setup-server.sh` setzt `DocumentRoot` **auf das App-Verzeichnis selbst**.
Das funktioniert, ist aber riskant: Schutz von `storage/`, `src/`, `install/` hängt dann
allein an `.htaccess`/`<Directory>`-Regeln. Fällt eine Regel weg (NGINX, `AllowOverride None`,
Konfig-Fehler), ist der **AES-Master-Key web-öffentlich**.

**Sicherer: nur `public/` (bzw. `index.php` + Assets) ausliefern, alles andere außerhalb des
Webroots.** Empfohlenes Layout:

```
/opt/m365-tenant-tool/        # Code-Wurzel, NICHT im DocumentRoot
├── index.php
├── src/  views/  vendor/  install/
├── storage/                  # app.key, db_bootstrap.ini, *.lock
└── public/                   # css/, js/  →  DocumentRoot zeigt hierauf
```

> Hinweis: Der aktuelle Front-Controller erwartet `index.php` in der Wurzel. Bis das Repo
> ein dediziertes `public/`-Front-Controller-Layout mitbringt, ist die **gleichwertige
> Absicherung** weiter unten (§2) Pflicht: `storage/`, `src/`, `views/`, `install/`,
> `vendor/`, `*.ini`, `*.key` per Server-Config hart sperren — **nicht** nur per `.htaccess`.

---

## 2 · Webserver-Konfiguration

### Apache (gehärteter VirtualHost)

```apache
<VirtualHost *:80>
    ServerName m365.example.org
    Redirect permanent / https://m365.example.org/
</VirtualHost>

<VirtualHost *:443>
    ServerName m365.example.org
    DocumentRoot /opt/m365-tenant-tool

    <Directory /opt/m365-tenant-tool>
        Options -Indexes -MultiViews +FollowSymLinks
        AllowOverride None          # .htaccess NICHT als alleinige Schutzschicht nutzen
        Require all granted
        DirectoryIndex index.php
    </Directory>

    # Sensible Verzeichnisse hart sperren (unabhängig von .htaccess)
    <DirectoryMatch "/opt/m365-tenant-tool/(storage|src|views|vendor|database|install)">
        Require all denied
    </DirectoryMatch>

    # Sensible Dateitypen sperren
    <FilesMatch "\.(key|ini|lock|sql|md|lock|json|log)$">
        Require all denied
    </FilesMatch>
    # composer.json/.lock & READMEs nicht ausliefern
    <FilesMatch "^(composer\.(json|lock)|\.env)$">
        Require all denied
    </FilesMatch>

    <FilesMatch "\.php$">
        SetHandler "proxy:unix:/run/php/php-fpm.sock|fcgi://localhost"
    </FilesMatch>

    # TLS — moderne Cipher-Suite
    SSLEngine on
    SSLProtocol -all +TLSv1.2 +TLSv1.3
    SSLHonorCipherOrder off
    SSLCertificateFile    /etc/letsencrypt/live/m365.example.org/fullchain.pem
    SSLCertificateKeyFile /etc/letsencrypt/live/m365.example.org/privkey.pem

    Header always set Strict-Transport-Security "max-age=63072000; includeSubDomains; preload"

    ErrorLog  ${APACHE_LOG_DIR}/m365-error.log
    CustomLog ${APACHE_LOG_DIR}/m365-access.log combined
</VirtualHost>
```

> Die App liefert CSP/Frame-Options/Referrer-Policy bereits über `.htaccess` aus. Mit
> `AllowOverride None` musst du diese Header in den VHost übernehmen (aus `.htaccess`
> kopieren), sonst greifen sie nicht.

### NGINX (falls statt Apache eingesetzt)

`.htaccess` wird von NGINX **ignoriert** — die Sperren müssen zwingend in die Server-Config:

```nginx
server {
    listen 443 ssl http2;
    server_name m365.example.org;
    root /opt/m365-tenant-tool;
    index index.php;

    ssl_protocols TLSv1.2 TLSv1.3;
    add_header Strict-Transport-Security "max-age=63072000; includeSubDomains" always;

    # Sensibles hart sperren
    location ~* ^/(storage|config|src|views|vendor|database|install)/ { deny all; return 404; }
    location ~* \.(key|ini|lock|sql|log)$ { deny all; return 404; }
    location ~* ^/(composer\.(json|lock)|\.env)$ { deny all; return 404; }

    location / { try_files $uri $uri/ /index.php?$query_string; }

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php-fpm.sock;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }
}
```

---

## 3 · Dateirechte & Eigentümer

```bash
APP=/opt/m365-tenant-tool

# Eigentümer: App läuft als www-data, Dateien gehören NICHT www-data (Code unveränderbar)
chown -R root:www-data "$APP"
find "$APP" -type d -exec chmod 750 {} \;
find "$APP" -type f -exec chmod 640 {} \;

# storage/ ist das einzige Verzeichnis, in das die App schreiben darf
chown -R www-data:www-data "$APP/storage"
chmod 750 "$APP/storage"

# Master-Key & DB-Bootstrap: nur Eigentümer lesen
chmod 600 "$APP/storage/app.key" "$APP/storage/db_bootstrap.ini" 2>/dev/null || true
```

Prinzip: **Der Webserver-User darf Code nicht überschreiben** (gehört `root:www-data`,
`640`). So kann ein kompromittierter PHP-Prozess keine Web-Shell in `src/` ablegen.
Schreibbar ist nur `storage/`.

> Achtung: Das mitgelieferte `setup-server.sh` setzt `chown -R www-data:www-data` auf das
> **ganze** Verzeichnis und `chmod 750`/`770`. Das macht den gesamten Code für den
> Webserver-User schreibbar — für den isolierten Produktivbetrieb auf das obige,
> restriktivere Schema umstellen.

---

## 4 · Installer abriegeln (nach dem Setup)

Der Installer sperrt sich nach Abschluss selbst (`storage/installed.lock`) und verweigert
sich, wenn bereits ein Admin-Passwort in der DB steht. **Verlasse dich nicht allein darauf** —
entferne das Verzeichnis nach erfolgreichem Setup vollständig:

```bash
# Bestätigen, dass Setup abgeschlossen ist:
test -f /opt/m365-tenant-tool/storage/installed.lock && echo "installed.lock vorhanden"

# Installer entfernen (Backup vorher, falls du ihn behalten willst):
rm -rf /opt/m365-tenant-tool/install
```

Zusätzlich ist `install/` in §2 bereits per Server-Config gesperrt — doppelter Boden.

---

## 5 · Datenbank minimal berechtigen

```sql
CREATE DATABASE m365tool CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'm365app'@'localhost' IDENTIFIED BY '<langes-zufalls-passwort>';

-- Nur DML auf die App-DB — KEIN DROP/ALTER/GRANT/FILE im Normalbetrieb
GRANT SELECT, INSERT, UPDATE, DELETE ON m365tool.* TO 'm365app'@'localhost';
FLUSH PRIVILEGES;
```

- [ ] DB **nur auf `localhost`** lauschen (`bind-address = 127.0.0.1`), kein Netzwerk-Port offen.
- [ ] Für das initiale Schema-Einspielen kurzzeitig `CREATE`/`ALTER` gewähren, danach wieder entziehen.
- [ ] DB-Passwort lang & zufällig; es wird vom Tool AES-verschlüsselt abgelegt, das Klartext-
      Passwort steht nur in `storage/db_bootstrap.ini` (deshalb `chmod 600`).

---

## 6 · Azure-AD-App: Least Privilege

- [ ] **Nur die Permissions erteilen, die du wirklich nutzt.** Brauchst du keine Schreib-
      Aktionen, vergib ausschließlich die `*.Read.All`-Varianten. Jede `ReadWrite`-Permission
      vergrößert den Schaden bei Secret-Diebstahl.
- [ ] **Client-Secret mit kurzer Laufzeit** (6 Monate) + Kalendererinnerung zur Rotation.
      Besser noch: auf **Zertifikats-Authentifizierung** umstellen, sobald das Tool das
      unterstützt (Secret im Klartext entfällt damit).
- [ ] **Conditional Access für die App selbst**: die Service-Principal-Anmeldung auf die
      **statische IP deines Servers** einschränken (CA-Policy „Locations" auf Workload-
      Identities). Dann ist das Secret außerhalb deines Servers wertlos.
- [ ] Secret **separat vom DB-Backup** aufbewahren (siehe §8).

---

## 7 · Tool-interne Härtung

- [ ] **2FA für den Admin-Login aktivieren** (`/settings/2fa`) — TOTP, Wiederherstellungs-
      codes sicher offline verwahren.
- [ ] **Operator-Konto** nur anlegen, wenn nötig; Rollen-Trennung admin/operator nutzen.
- [ ] **REST-API-Keys** nur erzeugen, wenn BI/Automatisierung sie braucht. Keys haben Scopes
      (`read`/`write`/`admin`) — pro Anwendungsfall den **kleinsten** Scope vergeben und
      ungenutzte Keys widerrufen.
- [ ] **`M365_DEBUG` niemals dauerhaft in Produktion** setzen (nur temporär zur Diagnose,
      server-seitig per Env-Var, nie per Cookie/URL).
- [ ] **OTA-Update (`/updates`, git-pull)**: praktisch, aber es lässt den Webserver-Prozess
      Code verändern. Auf dem gehärteten Server (Code gehört `root`, §3) schlägt git-pull als
      `www-data` ohnehin fehl — das ist gewollt. Updates stattdessen **manuell als
      Deploy-User** einspielen:
      ```bash
      sudo -u deploy git -C /opt/m365-tenant-tool pull --ff-only
      sudo -u deploy composer install --no-dev --optimize-autoloader
      ```
- [ ] Idle-Timeout (30 Min.) ist eingebaut; Session-Cookies `HttpOnly`/`SameSite=Lax` (Lax wegen OAuth-Login)
      sicherstellen (PHP-FPM-Pool/`php.ini`, da `php_value` in `.htaccess` mit FPM nicht greift):
      ```ini
      session.cookie_httponly = 1
      session.cookie_secure   = 1
      session.cookie_samesite = Lax   ; Lax (nicht Strict) — sonst bricht der Microsoft-OAuth-Login
      session.use_strict_mode = 1
      ```

---

## 8 · Backup & Wiederherstellung

Ohne `storage/app.key` sind **alle** verschlüsselten Credentials unbrauchbar — der Key gehört
ins Backup, aber **getrennt** von der DB, damit ein einzelnes geleaktes Backup nicht beides enthält.

- [ ] **DB-Dump** regelmäßig, verschlüsselt (z. B. `mysqldump | age -r ...`).
- [ ] **`storage/app.key`** an einem **separaten** sicheren Ort (Passwort-Manager/HSM/Offline).
- [ ] Restore mindestens einmal **testen** (auf einer isolierten Test-VM).
- [ ] Backups **nicht** auf demselben Host und nicht im Webroot lagern.

---

## 9 · Monitoring & Protokollierung

- [ ] **Cron-Logfile** überwachen (`/var/log/m365-cron.log`) — Fehler dort zeigen
      fehlgeschlagene Jobs (z. B. Auto-Widerruf, Lizenz-Recycling).
- [ ] **App-Audit-Log** (`/audit-log` im Tool) regelmäßig sichten — protokolliert Logins,
      fehlgeschlagene 2FA, schreibende Aktionen.
- [ ] **Webserver-Access-Log** auf Auffälligkeiten prüfen (wiederholte Zugriffe auf `/install/`,
      `/storage/`, ungewöhnliche User-Agents). `fail2ban` auf wiederholte `login_failed`/403 setzen.
- [ ] **Entra-seitig**: Sign-in-Logs der App (Service Principal) überwachen — jede Anmeldung
      von einer **anderen IP als deinem Server** ist ein Alarm (siehe CA-Policy §6).
- [ ] Optional: `/health` liefert ohne DB/Autoload PHP-Version + Extension-Status — nützlich,
      gibt aber **keine** Secrets preis. Bei Bedarf zusätzlich hinter Basic-Auth legen.

---

## 10 · Schnell-Checkliste (vor Go-Live)

```
[ ] OS gepatcht, automatische Updates aktiv
[ ] Firewall: eingehend nur 443 (+22 aus Admin-Netz), egress allowlisted
[ ] TLS gültig, HTTP→HTTPS-Redirect, HSTS aktiv
[ ] storage/ + src/ + install/ per Server-Config gesperrt (nicht nur .htaccess)
[ ] install/ nach Setup entfernt
[ ] app.key chmod 600, Code gehört root:www-data (640/750)
[ ] DB-User nur SELECT/INSERT/UPDATE/DELETE, bind 127.0.0.1
[ ] Azure-App: nur benötigte Permissions, Secret-Rotation geplant, CA-IP-Restriktion
[ ] 2FA für Admin aktiv, Recovery-Codes offline gesichert
[ ] M365_DEBUG aus
[ ] Backups: DB verschlüsselt + app.key getrennt, Restore getestet
[ ] Cron-Log + App-Audit-Log + Entra-Sign-in-Logs im Monitoring
```
