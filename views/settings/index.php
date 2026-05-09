<?php use App\Core\View; $e = fn($v) => View::escape($v); $s = $settings; ?>

<?php if ($flash): ?>
    <div class="alert alert-success alert-dismissible mb-4">
        <i class="bi bi-check-circle me-2"></i><?= $e($flash) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible mb-4">
        <i class="bi bi-exclamation-triangle me-2"></i><?= $e($error) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-lg-8">
        <form method="post" action="/settings/save">

        <!-- General -->
        <div class="content-card mb-4">
            <div class="card-header-custom">
                <i class="bi bi-gear text-primary"></i>
                <h6>Allgemein</h6>
            </div>
            <div class="card-body-custom">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-medium">App-Name</label>
                        <input type="text" name="app_name" class="form-control" value="<?= $e($s['app_name']) ?>" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-medium">Cache-Dauer</label>
                        <select name="cache_ttl" class="form-select">
                            <?php foreach ([5,15,30,60] as $t): ?>
                                <option value="<?= $t ?>" <?= $s['cache_ttl'] == $t ? 'selected' : '' ?>><?= $t ?> Min.</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-medium">Zeitzone</label>
                        <select name="timezone" class="form-select">
                            <?php foreach (['Europe/Berlin','Europe/Vienna','Europe/Zurich','UTC'] as $tz): ?>
                                <option value="<?= $tz ?>" <?= $s['timezone'] === $tz ? 'selected' : '' ?>><?= $tz ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Admin Password -->
        <div class="content-card mb-4">
            <div class="card-header-custom">
                <i class="bi bi-person-lock text-primary"></i>
                <h6>Admin-Passwort ändern</h6>
            </div>
            <div class="card-body-custom">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Neues Passwort</label>
                        <input type="password" name="admin_password" class="form-control" minlength="8" placeholder="Leer lassen = keine Änderung">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Bestätigung</label>
                        <input type="password" name="admin_password_confirm" class="form-control">
                    </div>
                </div>
            </div>
        </div>

        <!-- Operator Account -->
        <div class="content-card mb-4">
            <div class="card-header-custom">
                <i class="bi bi-person-badge text-warning"></i>
                <h6>Operator-Konto <span class="badge-warning ms-2">Schreibzugriff eingeschränkt</span></h6>
            </div>
            <div class="card-body-custom">
                <p class="text-muted small mb-3">
                    Der Operator kann Benutzer de/aktivieren, Lizenzen zuweisen und Gruppen verwalten —
                    aber keine Einstellungen ändern.
                </p>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-medium">Benutzername</label>
                        <input type="text" name="operator_username" class="form-control" value="<?= $e($s['operator_username']) ?>" placeholder="z.B. operator">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-medium">Passwort</label>
                        <input type="password" name="operator_password" class="form-control" placeholder="Leer lassen = keine Änderung">
                    </div>
                </div>
            </div>
        </div>

        <!-- Email Alerts -->
        <div class="content-card mb-4">
            <div class="card-header-custom">
                <i class="bi bi-envelope text-primary"></i>
                <h6>E-Mail-Benachrichtigungen</h6>
            </div>
            <div class="card-body-custom">
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Alert-Empfänger</label>
                        <input type="email" name="alert_email_to" class="form-control" value="<?= $e($s['alert_email_to']) ?>" placeholder="admin@firma.de">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Absender</label>
                        <input type="email" name="alert_email_from" class="form-control" value="<?= $e($s['alert_email_from']) ?>" placeholder="noreply@firma.de">
                    </div>
                </div>
                <hr>
                <h6 class="small text-muted text-uppercase mb-3">SMTP (optional, sonst PHP mail())</h6>
                <div class="row g-3">
                    <div class="col-md-5">
                        <label class="form-label fw-medium">SMTP-Host</label>
                        <input type="text" name="smtp_host" class="form-control" value="<?= $e($s['smtp_host']) ?>" placeholder="smtp.firma.de">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-medium">Port</label>
                        <input type="number" name="smtp_port" class="form-control" value="<?= $e($s['smtp_port']) ?>">
                    </div>
                    <div class="col-md-2half">
                        <label class="form-label fw-medium">Benutzer</label>
                        <input type="text" name="smtp_user" class="form-control" value="<?= $e($s['smtp_user']) ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-medium">Passwort</label>
                        <input type="password" name="smtp_password" class="form-control" placeholder="Leer = keine Änderung">
                    </div>
                </div>
                <hr>
                <h6 class="small text-muted text-uppercase mb-3">Trigger</h6>
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="alert_risky_users" id="chkRisky" value="1"
                                   <?= $s['alert_risky_users'] ? 'checked' : '' ?>>
                            <label class="form-check-label" for="chkRisky">Neue Risikobenutzer</label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="alert_anon_shares" id="chkAnon" value="1"
                                   <?= $s['alert_anon_shares'] ? 'checked' : '' ?>>
                            <label class="form-check-label" for="chkAnon">Anonyme Freigaben</label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-medium">MFA-Schwellwert</label>
                        <div class="input-group input-group-sm">
                            <input type="number" name="alert_mfa_threshold" class="form-control"
                                   value="<?= $e($s['alert_mfa_threshold']) ?>" min="0" max="100">
                            <span class="input-group-text">%</span>
                        </div>
                        <div class="form-text">Alert wenn MFA-Quote darunter fällt</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary px-4">
                <i class="bi bi-check2 me-1"></i> Einstellungen speichern
            </button>
        </div>
        </form>
    </div>

    <!-- Sidebar actions -->
    <div class="col-lg-4">
        <div class="content-card mb-3">
            <div class="card-header-custom">
                <i class="bi bi-database text-secondary"></i>
                <h6>Cache</h6>
            </div>
            <div class="card-body-custom">
                <p class="small text-muted mb-3">Graph-API-Daten werden lokal gecacht. Hier komplett leeren für frische Daten.</p>
                <a href="/settings/clear-cache" class="btn btn-outline-secondary btn-sm w-100"
                   onclick="return confirm('Cache wirklich leeren?')">
                    <i class="bi bi-trash me-1"></i> Cache leeren
                </a>
            </div>
        </div>

        <div class="content-card mb-3">
            <div class="card-header-custom">
                <i class="bi bi-envelope text-secondary"></i>
                <h6>E-Mail testen</h6>
            </div>
            <div class="card-body-custom">
                <p class="small text-muted mb-3">Sendet eine Test-E-Mail an den konfigurierten Alert-Empfänger.</p>
                <a href="/settings/test-mail" class="btn btn-outline-primary btn-sm w-100">
                    <i class="bi bi-send me-1"></i> Test-E-Mail senden
                </a>
            </div>
        </div>

        <div class="content-card">
            <div class="card-header-custom">
                <i class="bi bi-info-circle text-secondary"></i>
                <h6>Rollen-Übersicht</h6>
            </div>
            <div class="card-body-custom">
                <table class="table table-sm mb-0" style="font-size:12px;">
                    <thead><tr><th>Aktion</th><th>Admin</th><th>Operator</th></tr></thead>
                    <tbody>
                        <tr><td>Einsehen</td><td>✓</td><td>✓</td></tr>
                        <tr><td>CSV-Export</td><td>✓</td><td>✓</td></tr>
                        <tr><td>Benutzer de/aktivieren</td><td>✓</td><td>✓</td></tr>
                        <tr><td>Lizenzen zuweisen</td><td>✓</td><td>✓</td></tr>
                        <tr><td>Gruppen verwalten</td><td>✓</td><td>✓</td></tr>
                        <tr><td>Freigaben widerrufen</td><td>✓</td><td>✓</td></tr>
                        <tr><td>Einstellungen</td><td>✓</td><td>–</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
