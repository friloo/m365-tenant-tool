<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Anmelden — M365 Tenant Tool</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="/public/css/app.css">
</head>
<body>
<div class="login-page">
    <div class="login-card">
        <div class="text-center mb-4">
            <div style="display:inline-flex;align-items:center;justify-content:center;width:56px;height:56px;background:#0078d4;border-radius:14px;margin-bottom:16px;">
                <i class="bi bi-microsoft" style="font-size:24px;color:#fff;"></i>
            </div>
            <h5 class="mb-1 fw-bold">M365 Tenant Tool</h5>
            <p class="text-muted small">Administrator-Anmeldung</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger py-2 small"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="post" action="/login">
            <div class="mb-3">
                <label class="form-label fw-medium">Benutzername</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-person text-muted"></i></span>
                    <input type="text" name="username" class="form-control border-start-0" autofocus required
                           placeholder="admin">
                </div>
            </div>
            <div class="mb-4">
                <label class="form-label fw-medium">Passwort</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-lock text-muted"></i></span>
                    <input type="password" name="password" class="form-control border-start-0" required>
                </div>
            </div>
            <button type="submit" class="btn btn-primary w-100 py-2 fw-medium">
                <i class="bi bi-box-arrow-in-right me-1"></i> Anmelden
            </button>
        </form>

        <p class="text-center text-muted mt-4 mb-0" style="font-size:11px;">
            Microsoft 365 Tenant Management Tool
        </p>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
