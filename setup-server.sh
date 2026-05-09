#!/bin/bash
# M365 Tenant Tool — Server Setup & Diagnose
# Auf dem Server als root ausführen: bash setup-server.sh

set -e

APP_DIR="/var/www/m365-tenant-tool"
DOMAIN="m365.dev.loheide.eu"

echo "=== M365 Tenant Tool — Server Setup ==="
echo ""

# ── 1. Prüfen ob das Verzeichnis existiert ──────────────────
if [ ! -d "$APP_DIR" ]; then
    echo "[FEHLER] Verzeichnis $APP_DIR nicht gefunden."
    echo "  Bitte zuerst: git clone <repo> $APP_DIR"
    exit 1
fi
echo "[OK] App-Verzeichnis: $APP_DIR"

# ── 2. Git Pull ──────────────────────────────────────────────
echo "[INFO] Neueste Version holen..."
cd "$APP_DIR"
git pull origin claude/m365-tenant-tool-afDAu 2>/dev/null || git pull 2>/dev/null || echo "[WARN] Git pull fehlgeschlagen — bitte manuell prüfen"

# ── 3. Composer ──────────────────────────────────────────────
if [ ! -d "$APP_DIR/vendor" ]; then
    echo "[INFO] Composer install wird ausgeführt..."
    cd "$APP_DIR" && composer install --no-dev --optimize-autoloader
else
    echo "[OK] vendor/ vorhanden"
fi

# ── 4. Apache Module ─────────────────────────────────────────
echo "[INFO] Apache-Module aktivieren..."
a2enmod rewrite
a2enmod headers
a2enmod ssl
echo "[OK] Module aktiviert"

# ── 5. PHP-FPM prüfen ───────────────────────────────────────
PHP_VERSION=$(php -r "echo PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION;")
echo "[INFO] PHP Version: $PHP_VERSION"

if systemctl is-active "php${PHP_VERSION}-fpm" &>/dev/null; then
    echo "[OK] PHP-FPM läuft (php${PHP_VERSION}-fpm)"
    a2enmod proxy_fcgi setenvif 2>/dev/null || true
    a2enconf "php${PHP_VERSION}-fpm" 2>/dev/null || true
elif systemctl is-active php-fpm &>/dev/null; then
    echo "[OK] PHP-FPM läuft (php-fpm)"
else
    echo "[WARN] PHP-FPM nicht gefunden — prüfe: systemctl status php*-fpm"
fi

# ── 6. Verzeichnisrechte ─────────────────────────────────────
echo "[INFO] Verzeichnisrechte setzen..."
chown -R www-data:www-data "$APP_DIR"
chmod -R 750 "$APP_DIR"
chmod -R 770 "$APP_DIR/storage"
chmod 640 "$APP_DIR/.htaccess"
echo "[OK] Rechte gesetzt"

# ── 7. VirtualHost schreiben ─────────────────────────────────
VHOST_FILE="/etc/apache2/sites-available/m365-tenant-tool.conf"
echo "[INFO] VirtualHost schreiben: $VHOST_FILE"

cat > "$VHOST_FILE" <<VHOST
<VirtualHost *:80>
    ServerName ${DOMAIN}
    Redirect permanent / https://${DOMAIN}/
</VirtualHost>

<VirtualHost *:443>
    ServerName ${DOMAIN}
    DocumentRoot ${APP_DIR}

    <Directory ${APP_DIR}>
        Options -Indexes -MultiViews +FollowSymLinks
        AllowOverride All
        Require all granted
        DirectoryIndex index.php
    </Directory>

    # storage/ ist nicht web-zugänglich
    <Directory ${APP_DIR}/storage>
        Require all denied
    </Directory>

    # PHP-FPM
    <FilesMatch "\.php$">
        SetHandler "proxy:unix:/run/php/php-fpm.sock|fcgi://localhost"
    </FilesMatch>

    # SSL — Zertifikat anpassen:
    SSLEngine on
    SSLCertificateFile    /etc/letsencrypt/live/${DOMAIN}/fullchain.pem
    SSLCertificateKeyFile /etc/letsencrypt/live/${DOMAIN}/privkey.pem

    ErrorLog  \${APACHE_LOG_DIR}/${DOMAIN}-error.log
    CustomLog \${APACHE_LOG_DIR}/${DOMAIN}-access.log combined
</VirtualHost>
VHOST

echo "[OK] VirtualHost geschrieben"

# ── 8. Site aktivieren & Apache neu starten ──────────────────
a2dissite 000-default.conf 2>/dev/null || true
a2ensite m365-tenant-tool.conf
echo "[INFO] Apache Konfiguration prüfen..."
apache2ctl configtest

echo "[INFO] Apache neu starten..."
systemctl restart apache2

echo ""
echo "=== Setup abgeschlossen ==="
echo "  → https://${DOMAIN}/install/"
