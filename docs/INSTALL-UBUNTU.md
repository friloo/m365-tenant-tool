# Installations- & Härtungs-Guide — von blankem Ubuntu bis Produktiv

End-to-End-Anleitung: von einem **frisch installierten Ubuntu Server (24.04 LTS)** mit einem
**sudo-fähigen Benutzer** bis zur gehärteten, laufenden Installation des M365 Tenant Tools.
Jeder Schritt ist zum Kopieren gedacht. Sicherheits-Härtung ist direkt eingebaut; die tiefere
Begründung steht in [`DEPLOYMENT-HARDENING.md`](DEPLOYMENT-HARDENING.md).

> **Bedrohungsannahme:** Dieser Server hält (verschlüsselt) das Azure-Client-Secret — faktisch
> einen tenant-weiten Generalschlüssel. Wer Code-Ausführung auf dem Server erlangt, übernimmt
> den Tenant. Darum: isolierter Host, minimale Rechte, minimale Angriffsfläche.

---

## Voraussetzungen

- Frisch installiertes **Ubuntu Server 24.04 LTS**, dediziert (keine anderen Dienste).
- Ein **sudo-Benutzer** (im Guide `deploy` genannt — ersetze ihn durch deinen).
- Eine **DNS-A/AAAA-Record**, der auf den Server zeigt (im Guide `m365.example.org`).
- Ports **80 + 443** aus dem Internet erreichbar (für Let's-Encrypt-Zertifikat).
- SSH-Zugang als `deploy`.

### Platzhalter, die du ersetzt

```
DOMAIN          = m365.example.org
APP_DIR         = /var/www/m365-tenant-tool
DEPLOY_USER     = deploy
DB_NAME         = m365tool
DB_USER         = m365app
REPO            = https://github.com/friloo/m365-tenant-tool.git
```

---

## 1 · System aktualisieren & Grund-Härtung

```bash
sudo apt update && sudo apt -y full-upgrade

# Zeitzone & Zeit-Sync (wichtig für TOTP-2FA & Audit-Korrelation)
sudo timedatectl set-timezone Europe/Berlin
sudo systemctl enable --now systemd-timesyncd

# Automatische Sicherheitsupdates
sudo apt -y install unattended-upgrades
sudo dpkg-reconfigure -plow unattended-upgrades   # "Yes" wählen
```

### Firewall (eingehend nur SSH + HTTPS + HTTP-für-Zertifikat)

```bash
sudo apt -y install ufw
sudo ufw default deny incoming
sudo ufw default allow outgoing
sudo ufw allow OpenSSH
sudo ufw allow 80/tcp     # nur für Let's Encrypt; kann später entfallen (Redirect bleibt nötig)
sudo ufw allow 443/tcp
sudo ufw enable
```

> Wenn möglich, SSH (22) nur aus deinem Admin-Netz/VPN erlauben:
> `sudo ufw allow from <DEIN_NETZ>/24 to any port 22` statt `allow OpenSSH`.

### SSH härten (nur Key-Login)

Stelle **vorher** sicher, dass du dich per SSH-Key einloggen kannst, sonst sperrst du dich aus.

```bash
sudo tee /etc/ssh/sshd_config.d/99-hardening.conf >/dev/null <<'EOF'
PasswordAuthentication no
PermitRootLogin no
KbdInteractiveAuthentication no
EOF
sudo systemctl restart ssh
```

### Brute-Force-Schutz (optional, empfohlen)

```bash
sudo apt -y install fail2ban
sudo systemctl enable --now fail2ban
```

---

## 2 · Pakete installieren (Apache, PHP-FPM, MariaDB, Composer)

Ubuntu 24.04 liefert **PHP 8.3** (erfüllt die Anforderung PHP 8.1+).

```bash
sudo apt -y install \
  apache2 \
  php8.3-fpm php8.3-mysql php8.3-curl php8.3-mbstring php8.3-xml php8.3-zip \
  mariadb-server \
  composer git unzip \
  certbot python3-certbot-apache

# Apache-Module
sudo a2enmod rewrite headers ssl proxy_fcgi setenvif
sudo a2enconf php8.3-fpm
sudo systemctl enable --now php8.3-fpm apache2
```

> Der PHP-FPM-Socket liegt unter `/run/php/php8.3-fpm.sock` — diesen Pfad nutzen wir im
> VirtualHost. Bei abweichender PHP-Version den Pfad anpassen (`ls /run/php/`).

### MariaDB absichern

```bash
sudo mysql_secure_installation
# - root-Passwort setzen, anonyme User entfernen, Test-DB entfernen, Reload: alles "Yes"
```

Sicherstellen, dass MariaDB **nur lokal** lauscht:

```bash
echo -e "[mysqld]\nbind-address = 127.0.0.1" | sudo tee /etc/mysql/mariadb.conf.d/99-bind-local.cnf
sudo systemctl restart mariadb
```

---

## 3 · Datenbank & Benutzer anlegen

```bash
sudo mysql <<'SQL'
CREATE DATABASE m365tool CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'm365app'@'localhost' IDENTIFIED BY 'HIER_LANGES_ZUFALLSPASSWORT';
-- Volle Rechte auf NUR diese DB: nötig, damit der Web-Installer das Schema anlegen kann.
GRANT ALL PRIVILEGES ON m365tool.* TO 'm365app'@'localhost';
FLUSH PRIVILEGES;
SQL
```

Ein starkes Passwort erzeugst du z. B. mit `openssl rand -base64 24`. Merke es dir — du gibst
es gleich im Web-Installer ein.

> Nach der Installation schränken wir diesen User auf **DML** ein (Schritt 9). Das ist sicher:
> Das idempotente `CREATE TABLE IF NOT EXISTS` beim App-Start ist in `try/catch` gekapselt und
> toleriert fehlende DDL-Rechte (die Tabellen existieren dann ja bereits).

---

## 4 · Code holen & Abhängigkeiten installieren

Als `deploy`-User klonen und Composer laufen lassen (noch ohne root-Eigentum, damit Composer
schreiben kann):

```bash
sudo mkdir -p /var/www
sudo chown $USER:$USER /var/www
git clone https://github.com/friloo/m365-tenant-tool.git /var/www/m365-tenant-tool
cd /var/www/m365-tenant-tool
composer install --no-dev --optimize-autoloader
```

---

## 5 · Dateirechte (Code unveränderbar, nur `storage/` schreibbar)

```bash
APP=/var/www/m365-tenant-tool

# Code gehört root, Gruppe www-data (Webserver darf lesen, NICHT schreiben → keine Web-Shell)
sudo chown -R root:www-data "$APP"
sudo find "$APP" -type d -exec chmod 750 {} \;
sudo find "$APP" -type f -exec chmod 640 {} \;

# storage/ ist das EINZIGE Verzeichnis, in das die App (www-data) schreiben darf.
# Der Web-Installer legt hier app.key, db_bootstrap.ini und installed.lock an.
sudo mkdir -p "$APP/storage"
sudo chown -R www-data:www-data "$APP/storage"
sudo chmod 750 "$APP/storage"
```

---

## 6 · Apache VirtualHost (gehärtet)

Zuerst HTTP-only (damit certbot das Zertifikat ausstellen kann):

```bash
sudo tee /etc/apache2/sites-available/m365.conf >/dev/null <<'VHOST'
<VirtualHost *:80>
    ServerName m365.example.org
    DocumentRoot /var/www/m365-tenant-tool

    <Directory /var/www/m365-tenant-tool>
        Options -Indexes -MultiViews +FollowSymLinks
        AllowOverride None
        Require all granted
        DirectoryIndex index.php
    </Directory>

    # Sensible Verzeichnisse hart sperren (unabhängig von .htaccess)
    <DirectoryMatch "/var/www/m365-tenant-tool/(storage|src|views|vendor|database|install)">
        Require all denied
    </DirectoryMatch>
    <FilesMatch "\.(key|ini|lock|sql|log)$">
        Require all denied
    </FilesMatch>
    <FilesMatch "^(composer\.(json|lock)|\.env)$">
        Require all denied
    </FilesMatch>

    <FilesMatch "\.php$">
        SetHandler "proxy:unix:/run/php/php8.3-fpm.sock|fcgi://localhost"
    </FilesMatch>

    # Security-Header (mit AllowOverride None greift .htaccess nicht → hier setzen)
    Header always set X-Frame-Options "SAMEORIGIN"
    Header always set X-Content-Type-Options "nosniff"
    Header always set Referrer-Policy "strict-origin-when-cross-origin"
    Header always set Content-Security-Policy "default-src 'self'; script-src 'self' cdn.jsdelivr.net 'unsafe-inline'; style-src 'self' cdn.jsdelivr.net 'unsafe-inline'; img-src 'self' data: blob:; font-src 'self' cdn.jsdelivr.net data:; connect-src 'self'; frame-ancestors 'none'; object-src 'none'; base-uri 'self';"
    Header always set Permissions-Policy "geolocation=(), microphone=(), camera=(), payment=()"

    ErrorLog  ${APACHE_LOG_DIR}/m365-error.log
    CustomLog ${APACHE_LOG_DIR}/m365-access.log combined
</VirtualHost>
VHOST

sudo a2dissite 000-default.conf
sudo a2ensite m365.conf
sudo apache2ctl configtest && sudo systemctl reload apache2
```

> **Hinweis Installer:** `install/` ist oben gesperrt. Für den Web-Installer in Schritt 8 muss
> der Block **vorübergehend** auskommentiert werden — das macht Schritt 8 ausdrücklich.

---

## 7 · TLS-Zertifikat (Let's Encrypt)

```bash
sudo certbot --apache -d m365.example.org
# E-Mail angeben, AGB akzeptieren, "Redirect" (HTTP→HTTPS erzwingen) wählen.
```

certbot legt einen zweiten `*:443`-VirtualHost an und richtet die Auto-Erneuerung ein. HSTS
ergänzen:

```bash
sudo sed -i '/SSLEngine on/a \    Header always set Strict-Transport-Security "max-age=63072000; includeSubDomains"' \
  /etc/apache2/sites-available/m365-le-ssl.conf
sudo apache2ctl configtest && sudo systemctl reload apache2
```

> Prüfe die TLS-Konfig anschließend z. B. mit `https://www.ssllabs.com/ssltest/`.

---

## 8 · Web-Installer durchlaufen

Der `install/`-Block muss kurz geöffnet werden. **Direkt danach wieder schließen.**

```bash
# install/ vorübergehend erreichbar machen
sudo sed -i 's#(storage|src|views|vendor|database|install)#(storage|src|views|vendor|database)#g' \
  /etc/apache2/sites-available/m365.conf /etc/apache2/sites-available/m365-le-ssl.conf
sudo systemctl reload apache2
```

Im Browser öffnen: **`https://m365.example.org/install/`** und die 5 Schritte ausfüllen:

| Schritt | Inhalt |
|---|---|
| 1 — Datenbank | Host `127.0.0.1`, DB `m365tool`, User `m365app`, das Passwort aus Schritt 3. Schema wird automatisch eingespielt. |
| 2 — Admin-Konto | Lokaler Admin-Benutzername + starkes Passwort. |
| 3 — Azure AD | Tenant-ID, Client-ID, Client-Secret (siehe Schritt 8a) + Verbindungstest. |
| 4 — Einstellungen | App-Name, öffentliche URL (`https://m365.example.org`), Cache-TTL, Zeitzone. |
| 5 — Fertig | Zusammenfassung → Login. Der Installer schreibt `storage/app.key`, `storage/db_bootstrap.ini` und `storage/installed.lock`. |

`install/`-Sperre **sofort wieder aktivieren** und das Verzeichnis am besten ganz entfernen:

```bash
sudo sed -i 's#(storage|src|views|vendor|database)#(storage|src|views|vendor|database|install)#g' \
  /etc/apache2/sites-available/m365.conf /etc/apache2/sites-available/m365-le-ssl.conf
sudo rm -rf /var/www/m365-tenant-tool/install
sudo systemctl reload apache2
```

### 8a · Azure-AD-App registrieren (Least Privilege)

1. [Entra Admin Center](https://entra.microsoft.com) → **App-Registrierungen → Neue Registrierung**
   → Name `M365 Tenant Tool`, Kontotyp *Nur dieser Verzeichnisinstanz*.
2. **Zertifikate & Geheimnisse → Neuer geheimer Clientschlüssel** (kurze Laufzeit, z. B. 6 Monate)
   → Wert **sofort** kopieren (für Installer-Schritt 3).
3. **API-Berechtigungen → Microsoft Graph → Anwendungsberechtigungen** hinzufügen.
   **Nur erteilen, was du brauchst.** Wenn du nur lesen/auditieren willst, vergib ausschließlich
   die `*.Read.All`-Varianten — jede `ReadWrite`-Permission vergrößert den Schaden bei
   Secret-Diebstahl. Die vollständige Permissions-Tabelle steht im [README](../README.md#azure-ad-app-registrierung).
4. **Administratorzustimmung erteilen** klicken (erfordert Global Admin).
5. **Empfohlen:** Conditional-Access-Policy für die Workload-Identity, die die Anmeldung des
   Service Principals auf die **statische IP deines Servers** beschränkt. Dann ist das Secret
   außerhalb dieses Servers wertlos.

---

## 9 · Post-Install-Härtung

### app.key abriegeln

```bash
sudo chmod 600 /var/www/m365-tenant-tool/storage/app.key
sudo chmod 600 /var/www/m365-tenant-tool/storage/db_bootstrap.ini
```

(Beide gehören `www-data` und werden von PHP-FPM als `www-data` gelesen — `600` ist korrekt.)

### DB-Benutzer auf DML einschränken

```bash
sudo mysql <<'SQL'
REVOKE ALL PRIVILEGES ON m365tool.* FROM 'm365app'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON m365tool.* TO 'm365app'@'localhost';
FLUSH PRIVILEGES;
SQL
```

> Bei späteren **Updates/Schema-Änderungen** vorübergehend wieder `GRANT ALL PRIVILEGES ON
> m365tool.* …`, danach erneut auf DML zurückstellen.

### OTA-Update deaktivieren, Updates manuell einspielen

Da der Code `root` gehört, schlägt der eingebaute Git-Pull als `www-data` ohnehin fehl (gewollt).
Updates spielst du kontrolliert als `deploy` ein:

```bash
cd /var/www/m365-tenant-tool
sudo -u root git config --global --add safe.directory /var/www/m365-tenant-tool
sudo git pull --ff-only
sudo composer install --no-dev --optimize-autoloader
# Rechte nach dem Pull erneut setzen:
sudo chown -R root:www-data /var/www/m365-tenant-tool
sudo find /var/www/m365-tenant-tool -type d -exec chmod 750 {} \;
sudo find /var/www/m365-tenant-tool -type f -exec chmod 640 {} \;
sudo chown -R www-data:www-data /var/www/m365-tenant-tool/storage
sudo chmod 600 /var/www/m365-tenant-tool/storage/app.key /var/www/m365-tenant-tool/storage/db_bootstrap.ini
```

### PHP-FPM Session-Cookies härten

```bash
sudo tee /etc/php/8.3/fpm/conf.d/99-m365-session.ini >/dev/null <<'EOF'
session.cookie_httponly = 1
session.cookie_secure   = 1
session.cookie_samesite = Lax   ; Lax (nicht Strict) — sonst bricht der Microsoft-OAuth-Login
session.use_strict_mode = 1
expose_php = Off
EOF
sudo systemctl restart php8.3-fpm
```

### 2FA für den Admin aktivieren

Im Tool einloggen → **Einstellungen → 2FA** (`/settings/2fa`) → TOTP einrichten,
Wiederherstellungscodes **offline** sichern.

---

## 10 · Cron einrichten (ein einziger Eintrag)

```bash
sudo touch /var/log/m365-cron.log
sudo chown www-data:www-data /var/log/m365-cron.log
( sudo crontab -u www-data -l 2>/dev/null; \
  echo "* * * * * php /var/www/m365-tenant-tool/run-cron.php >> /var/log/m365-cron.log 2>&1" ) \
  | sudo crontab -u www-data -
```

> **Genau ein** Eintrag. `run-cron.php` entscheidet intern, welche Jobs fällig sind. Die alten
> Einzelskripte (`run-alerts.php`, `run-stale-cleanup.php`, `run-share-monitor.php`) **nicht**
> zusätzlich einplanen — sonst laufen Jobs doppelt.

---

## 11 · Backup einrichten (DB + app.key getrennt!)

Ohne `storage/app.key` sind alle Credentials unbrauchbar — Key **getrennt** von der DB sichern.

```bash
sudo install -d -m 700 /root/m365-backups
sudo tee /usr/local/sbin/m365-backup.sh >/dev/null <<'EOF'
#!/bin/bash
set -e
TS=$(date +%F)
mysqldump --single-transaction m365tool | gzip > /root/m365-backups/db-$TS.sql.gz
find /root/m365-backups -name 'db-*.sql.gz' -mtime +14 -delete
EOF
sudo chmod 700 /usr/local/sbin/m365-backup.sh
( sudo crontab -l 2>/dev/null; echo "30 2 * * * /usr/local/sbin/m365-backup.sh" ) | sudo crontab -
```

- **`storage/app.key` einmalig** an einen **separaten** sicheren Ort kopieren (Passwort-Manager/
  Offline) — **nicht** ins selbe Backup wie die DB.
- DB-Dumps idealerweise auf einen anderen Host replizieren/verschlüsseln.
- Restore mindestens einmal auf einer Test-VM durchspielen.

---

## 12 · Funktionstest & Verifikation

```bash
# Health-Endpunkt (ohne Secrets): PHP-Version, Extensions, storage-Dateien
curl -sk https://m365.example.org/health

# Sensible Pfade müssen blockiert sein → erwartet 403/404, NICHT 200:
for p in storage/app.key install/ src/ composer.json; do
  printf "%-18s -> " "$p"; curl -s -o /dev/null -w "%{http_code}\n" "https://m365.example.org/$p"
done

# Cron testweise manuell als www-data laufen lassen:
sudo -u www-data php /var/www/m365-tenant-tool/run-cron.php
```

Im Browser: Login funktioniert, Dashboard lädt, unter **Einstellungen → Berechtigungen**
(`/settings/permissions`) prüfen, dass die erteilten Graph-Permissions stimmen.

### Go-Live-Checkliste

```
[ ] OS gepatcht, unattended-upgrades aktiv, Zeit synchron
[ ] ufw: eingehend nur 443 (+22 aus Admin-Netz), SSH key-only
[ ] TLS gültig, HTTP→HTTPS-Redirect, HSTS aktiv
[ ] storage/ + src/ + install/ per Apache-Config gesperrt (curl-Test 403/404)
[ ] install/-Verzeichnis entfernt
[ ] app.key & db_bootstrap.ini chmod 600; Code gehört root:www-data (640/750)
[ ] DB-User auf SELECT/INSERT/UPDATE/DELETE eingeschränkt; MariaDB bind 127.0.0.1
[ ] Azure-App: nur benötigte Permissions, Secret-Rotation geplant, CA-IP-Restriktion
[ ] 2FA für Admin aktiv, Recovery-Codes offline gesichert
[ ] OTA-Update deaktiviert (Updates manuell als deploy)
[ ] Cron läuft (genau ein Eintrag), Logfile wird geschrieben
[ ] Backups: DB verschlüsselt + app.key getrennt, Restore getestet
[ ] M365_DEBUG NICHT gesetzt
```

---

## Troubleshooting

| Symptom | Vorgehen |
|---|---|
| „Please run `composer install` first" | `vendor/` fehlt → Schritt 4 wiederholen, dann Rechte (Schritt 5). |
| Weiße Seite / 500 | `sudo tail -f /var/log/apache2/m365-error.log` und `/var/log/php8.3-fpm.log`. |
| Installer meldet DB-Fehler | DB-Host `127.0.0.1`, User/Passwort prüfen; `sudo mysql -u m365app -p m365tool`. |
| 403 auf der ganzen Seite | `<DirectoryMatch>` greift zu breit oder `index.php` wird geblockt — Pfad prüfen. |
| Modul „Berechtigung fehlt" | `/settings/permissions` → fehlende Graph-Permission + Admin-Consent. |
| Cron läuft nicht | `sudo -u www-data php /var/www/m365-tenant-tool/run-cron.php` manuell, Ausgabe prüfen. |

Für die vertiefte Härtung (Egress-Allowlist, NGINX-Variante, Deploy außerhalb DocumentRoot,
Monitoring) siehe [`DEPLOYMENT-HARDENING.md`](DEPLOYMENT-HARDENING.md).
