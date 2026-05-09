<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Link ungültig</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background: #f3f4f6; min-height: 100vh; display: flex; align-items: center; }
        .result-card { max-width: 520px; width: 100%; margin: 40px auto; background: #fff; border-radius: 12px; padding: 48px 40px; box-shadow: 0 4px 24px rgba(0,0,0,.08); text-align: center; }
        .icon-circle { width: 80px; height: 80px; border-radius: 50%; background: #fee2e2; display: flex; align-items: center; justify-content: center; margin: 0 auto 24px; }
    </style>
</head>
<body>
<div class="result-card">
    <div class="icon-circle">
        <i class="bi bi-x-circle-fill text-danger" style="font-size: 40px;"></i>
    </div>
    <?php
    $reason = $reason ?? 'expired';
    if ($reason === 'used'): ?>
        <h4 class="fw-bold mb-2">Link bereits verwendet</h4>
        <p class="text-muted mb-4">
            Dieser Bestätigungslink wurde bereits verwendet.<br>
            Falls Sie eine neue Überprüfungs-E-Mail erhalten haben, nutzen Sie bitte den Link aus der neuesten E-Mail.
        </p>
    <?php elseif ($reason === 'not_found'): ?>
        <h4 class="fw-bold mb-2">Link nicht gefunden</h4>
        <p class="text-muted mb-4">
            Dieser Bestätigungslink ist ungültig oder existiert nicht.<br>
            Bitte prüfen Sie, ob Sie den vollständigen Link aus der E-Mail kopiert haben.
        </p>
    <?php else: ?>
        <h4 class="fw-bold mb-2">Link abgelaufen</h4>
        <p class="text-muted mb-4">
            Dieser Bestätigungslink ist abgelaufen.<br>
            Wenn die Freigabe weiterhin benötigt wird, wenden Sie sich bitte an Ihren IT-Administrator.
        </p>
    <?php endif; ?>
    <p class="text-muted mb-0" style="font-size:13px;">
        <i class="bi bi-info-circle me-1"></i>
        Sie können dieses Fenster schließen.
    </p>
</div>
</body>
</html>
