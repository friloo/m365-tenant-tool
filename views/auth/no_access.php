<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kein Zugriff</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="/public/css/app.css">
</head>
<body>
<div class="login-page">
    <div class="login-card text-center">
        <div style="display:inline-flex;align-items:center;justify-content:center;width:56px;height:56px;background:#fee2e2;border-radius:14px;margin-bottom:16px;">
            <i class="bi bi-shield-x" style="font-size:24px;color:#dc2626;"></i>
        </div>
        <h5 class="mb-2 fw-bold">Kein Zugriff</h5>
        <p class="text-muted small mb-3">
            Ihr Microsoft-Konto
            <?php if (!empty($upn)): ?>
                (<strong><?= htmlspecialchars($upn) ?></strong>)
            <?php endif; ?>
            hat keinen Zugriff auf dieses Tool.
        </p>
        <p class="text-muted small mb-4">
            Bitte wenden Sie sich an den Administrator, um Zugriff zu erhalten.
        </p>
        <a href="/login" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Zurück zur Anmeldung
        </a>
    </div>
</div>
</body>
</html>
