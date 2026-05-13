<?php $e = fn($v) => htmlspecialchars((string)$v, ENT_QUOTES); ?>

<div class="content-card mb-4">
  <div class="card-body-custom">
    <div class="d-flex align-items-center gap-3 mb-3">
        <div style="width:40px;height:40px;background:<?= $enabled ? '#16a34a' : '#0078d4' ?>;border-radius:10px;display:flex;align-items:center;justify-content:center;">
            <i class="bi bi-shield-lock-fill" style="color:#fff;font-size:18px;"></i>
        </div>
        <div>
            <h5 class="mb-0 fw-bold">Zwei-Faktor-Authentifizierung (TOTP)</h5>
            <p class="text-muted small mb-0">Schützt den Admin-Login mit einem zeitbasierten Einmalcode (RFC 6238).</p>
        </div>
        <div class="ms-auto">
            <?php if ($enabled): ?>
                <span class="badge bg-success fs-6 px-3 py-2"><i class="bi bi-check-circle me-1"></i>Aktiv</span>
            <?php else: ?>
                <span class="badge bg-secondary fs-6 px-3 py-2"><i class="bi bi-x-circle me-1"></i>Deaktiviert</span>
            <?php endif; ?>
        </div>
    </div>

    <?php if (!empty($flash)): ?>
        <div class="alert alert-success py-2 small mb-3"><?= $e($flash) ?></div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger py-2 small mb-3"><?= $e($error) ?></div>
    <?php endif; ?>

    <?php if (!empty($recoveryCodes)): ?>
    <!-- Recovery codes — shown ONCE after setup/regen -->
    <div class="alert alert-warning border-warning mb-4">
        <h6 class="fw-bold mb-2"><i class="bi bi-exclamation-triangle me-1"></i>Wiederherstellungscodes — jetzt speichern!</h6>
        <p class="small mb-3">Diese Codes werden nur <strong>einmal</strong> angezeigt. Speichere sie sicher (z.B. Passwort-Manager). Jeder Code kann nur einmal verwendet werden.</p>
        <div class="row g-2 mb-3">
            <?php foreach ($recoveryCodes as $rc): ?>
                <div class="col-6 col-md-3">
                    <code class="d-block text-center p-2 bg-white border rounded fw-bold" style="font-size:14px;letter-spacing:2px;">
                        <?= $e($rc) ?>
                    </code>
                </div>
            <?php endforeach; ?>
        </div>
        <button onclick="navigator.clipboard.writeText(<?= $e(json_encode(implode("\n", $recoveryCodes))) ?>).then(()=>this.textContent='✓ Kopiert')"
                class="btn btn-sm btn-warning">
            <i class="bi bi-clipboard me-1"></i>Alle Codes kopieren
        </button>
    </div>
    <?php endif; ?>

    <?php if ($enabled && !$setupSecret): ?>
    <!-- 2FA is active -->
    <div class="row g-4">
        <div class="col-md-6">
            <div class="p-3 bg-success bg-opacity-10 border border-success-subtle rounded">
                <h6 class="fw-bold text-success mb-2"><i class="bi bi-check-circle me-1"></i>2FA ist aktiv</h6>
                <p class="small text-muted mb-0">Der Admin-Login ist mit TOTP gesichert. Beim Anmelden wird dein Authenticator-Code abgefragt.</p>
            </div>
            <p class="small text-muted mt-2">
                Noch <strong><?= (int)$codesLeft ?></strong> Wiederherstellungscode(s) verfügbar.
                <?php if ($codesLeft <= 2): ?>
                    <span class="text-warning fw-bold"> — bitte neue generieren.</span>
                <?php endif; ?>
            </p>
            <form method="post" action="/settings/2fa/regen-codes" class="mt-3"
                  onsubmit="return confirm('Alle bestehenden Wiederherstellungscodes werden ungültig. Fortfahren?')">
                <?= \App\Core\Csrf::field() ?>
                <button type="submit" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-repeat me-1"></i>Neue Wiederherstellungscodes generieren
                </button>
            </form>
        </div>
        <div class="col-md-6">
            <div class="p-3 bg-danger bg-opacity-10 border border-danger-subtle rounded">
                <h6 class="fw-bold text-danger mb-2"><i class="bi bi-shield-x me-1"></i>2FA deaktivieren</h6>
                <p class="small text-muted mb-2">Gib dein aktuelles Passwort ein, um 2FA zu entfernen.</p>
                <form method="post" action="/settings/2fa/disable"
                      onsubmit="return confirm('2FA wirklich deaktivieren? Der Admin-Login wird dadurch weniger sicher.')">
                    <?= \App\Core\Csrf::field() ?>
                    <div class="input-group input-group-sm">
                        <input type="password" name="confirm_password" class="form-control"
                               placeholder="Aktuelles Passwort" required>
                        <button type="submit" class="btn btn-danger">Deaktivieren</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php elseif ($setupSecret): ?>
    <!-- Setup step: show QR code + verification -->
    <div class="row g-4 align-items-start">
        <div class="col-md-5 text-center">
            <p class="small fw-medium mb-2">QR-Code scannen</p>
            <div id="totp-qr" class="d-inline-block p-2 bg-white border rounded"></div>
            <p class="text-muted small mt-3 mb-1">Oder manuell eingeben:</p>
            <code class="d-block p-2 bg-light border rounded" style="font-size:13px;letter-spacing:3px;word-break:break-all;">
                <?= $e($setupSecret) ?>
            </code>
            <button onclick="navigator.clipboard.writeText('<?= $e($setupSecret) ?>').then(()=>this.textContent='✓ Kopiert')"
                    class="btn btn-sm btn-outline-secondary mt-2">
                <i class="bi bi-clipboard me-1"></i>Kopieren
            </button>
        </div>
        <div class="col-md-7">
            <h6 class="fw-bold mb-3">Einrichtung bestätigen</h6>
            <ol class="small text-muted mb-3">
                <li class="mb-1">Öffne deine Authenticator-App (z.B. Microsoft Authenticator, Google Authenticator, Aegis).</li>
                <li class="mb-1">Scanne den QR-Code oder gib den Schlüssel manuell ein.</li>
                <li>Gib den angezeigten 6-stelligen Code ein.</li>
            </ol>
            <form method="post" action="/settings/2fa/verify">
                <?= \App\Core\Csrf::field() ?>
                <label class="form-label fw-medium">6-stelliger Code zur Bestätigung</label>
                <div class="input-group mb-3">
                    <input type="text" name="code" class="form-control text-center fw-bold"
                           style="font-size:18px;letter-spacing:6px;"
                           maxlength="6" inputmode="numeric" pattern="[0-9]{6}"
                           placeholder="000000" autofocus required>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-lg me-1"></i>Bestätigen & Aktivieren
                    </button>
                </div>
            </form>
            <form method="post" action="/settings/2fa/cancel">
                <?= \App\Core\Csrf::field() ?>
                <button type="submit" class="btn btn-sm btn-link text-muted p-0">Setup abbrechen</button>
            </form>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
    <script>
    new QRCode(document.getElementById('totp-qr'), {
        text: <?= json_encode($totpUri) ?>,
        width: 180,
        height: 180,
        correctLevel: QRCode.CorrectLevel.M
    });
    </script>

    <?php else: ?>
    <!-- 2FA not yet set up -->
    <div class="row g-4">
        <div class="col-md-8">
            <p class="mb-3">Schütze dein Admin-Konto mit einem zeitbasierten Einmalpasswort (TOTP). Kompatibel mit <strong>Microsoft Authenticator</strong>, <strong>Google Authenticator</strong>, <strong>Aegis</strong> und allen RFC-6238-Apps.</p>
            <div class="d-flex gap-3 mb-3">
                <div class="text-center">
                    <div style="width:36px;height:36px;background:#e3f2fd;border-radius:8px;display:flex;align-items:center;justify-content:center;margin:0 auto 4px;">
                        <i class="bi bi-shield-check text-primary"></i>
                    </div>
                    <div class="small text-muted">Phishing-sicher</div>
                </div>
                <div class="text-center">
                    <div style="width:36px;height:36px;background:#e8f5e9;border-radius:8px;display:flex;align-items:center;justify-content:center;margin:0 auto 4px;">
                        <i class="bi bi-phone text-success"></i>
                    </div>
                    <div class="small text-muted">Kein Internet nötig</div>
                </div>
                <div class="text-center">
                    <div style="width:36px;height:36px;background:#fff3e0;border-radius:8px;display:flex;align-items:center;justify-content:center;margin:0 auto 4px;">
                        <i class="bi bi-key text-warning"></i>
                    </div>
                    <div class="small text-muted">Wiederherstellungscodes</div>
                </div>
            </div>
            <form method="post" action="/settings/2fa/setup">
                <?= \App\Core\Csrf::field() ?>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-shield-lock me-1"></i>2FA jetzt einrichten
                </button>
            </form>
        </div>
        <div class="col-md-4">
            <div class="p-3 bg-light rounded border">
                <h6 class="fw-bold small mb-2">BSI-Empfehlung</h6>
                <p class="small text-muted mb-1">BSI IT-Grundschutz ORP.4.A21 und NIS-2 Art. 21 Abs. 2(i) empfehlen MFA für privilegierte Konten.</p>
                <span class="badge" style="background:#1565c0;font-size:10px;">BSI ORP.4.A21</span>
                <span class="badge bg-info text-dark ms-1" style="font-size:10px;">NIS-2 Art. 21(i)</span>
            </div>
        </div>
    </div>
    <?php endif; ?>
  </div>
</div>
