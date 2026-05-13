<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Zwei-Faktor-Authentifizierung — M365 Tenant Tool</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="/public/css/app.css">
</head>
<body>
<div class="login-page">
    <div class="login-card">
        <div class="text-center mb-4">
            <div style="display:inline-flex;align-items:center;justify-content:center;width:56px;height:56px;background:#0078d4;border-radius:14px;margin-bottom:16px;">
                <i class="bi bi-shield-lock-fill" style="font-size:24px;color:#fff;"></i>
            </div>
            <h5 class="mb-1 fw-bold">Zwei-Faktor-Authentifizierung</h5>
            <p class="text-muted small">Gib den Code aus deiner Authenticator-App ein.</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger py-2 small"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="post" action="/login/2fa" id="twofaForm">
            <?= \App\Core\Csrf::field() ?>
            <div class="mb-3">
                <label class="form-label fw-medium">6-stelliger Code</label>
                <input type="text" name="code" class="form-control text-center fw-bold"
                       style="font-size:22px;letter-spacing:8px;"
                       maxlength="6" inputmode="numeric" pattern="[0-9]{6}"
                       autofocus autocomplete="one-time-code" placeholder="000000" required>
            </div>
            <button type="submit" class="btn btn-primary w-100 py-2 fw-medium">
                <i class="bi bi-check-lg me-1"></i> Verifizieren
            </button>
        </form>

        <div class="mt-3">
            <button class="btn btn-link btn-sm text-muted p-0 w-100" type="button"
                    data-bs-toggle="collapse" data-bs-target="#recoverySection">
                Wiederherstellungscode verwenden
            </button>
            <div class="collapse mt-2" id="recoverySection">
                <form method="post" action="/login/2fa">
                    <?= \App\Core\Csrf::field() ?>
                    <div class="input-group">
                        <input type="text" name="code" class="form-control form-control-sm"
                               placeholder="XXXX-XXXX-XXXX" autocomplete="off"
                               style="letter-spacing:2px;">
                        <button type="submit" class="btn btn-sm btn-outline-secondary">Verwenden</button>
                    </div>
                </form>
            </div>
        </div>

        <a href="/login" class="d-block text-center text-muted small mt-3">
            <i class="bi bi-arrow-left me-1"></i> Zurück zur Anmeldung
        </a>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Auto-submit on 6 digits
document.querySelector('#twofaForm input[name=code]').addEventListener('input', function() {
    if (this.value.replace(/\s/g,'').length === 6) this.form.submit();
});
</script>
</body>
</html>
