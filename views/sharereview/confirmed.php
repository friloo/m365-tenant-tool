<?php require __DIR__ . '/_brand.php'; ?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Freigabe bestätigt — <?= htmlspecialchars($brandAppName) ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root { --brand: <?= htmlspecialchars($brandColor) ?>; --brand-text: <?= htmlspecialchars($brandTextColor) ?>; }
        body { background: #f3f4f6; min-height: 100vh; display: flex; flex-direction: column; align-items: center; justify-content: center; }
        .result-card { max-width: 520px; width: 100%; margin: 40px auto; background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 24px rgba(0,0,0,.08); }
        .brand-bar { background: var(--brand); color: var(--brand-text); padding: 16px 28px; display: flex; align-items: center; gap: 12px; }
        .brand-logo { width: 32px; height: 32px; border-radius: 6px; background: rgba(255,255,255,.18); display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 15px; overflow: hidden; }
        .brand-logo img { width: 100%; height: 100%; object-fit: contain; }
        .card-body { padding: 40px 32px; text-align: center; }
        .icon-circle { width: 72px; height: 72px; border-radius: 50%; background: #dcfce7; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; }
        footer.brand-footer { font-size: 12px; color: #9ca3af; text-align: center; padding: 16px; }
    </style>
</head>
<body>
<div class="result-card">
    <div class="brand-bar">
        <div class="brand-logo">
            <?php if ($brandLogoUrl): ?>
                <img src="<?= htmlspecialchars($brandLogoUrl) ?>" alt="Logo">
            <?php else: ?>
                <?= htmlspecialchars($brandLogoText) ?>
            <?php endif; ?>
        </div>
        <span style="font-weight:600;"><?= htmlspecialchars($brandAppName) ?></span>
    </div>
    <div class="card-body">
        <div class="icon-circle">
            <i class="bi bi-check-circle-fill text-success" style="font-size:36px;"></i>
        </div>
        <h5 class="fw-bold mb-2">Freigabe bestätigt</h5>
        <p class="text-muted mb-4">
            Vielen Dank! Ihre Bestätigung wurde gespeichert und die Freigabe wurde verlängert.<br>
            Sie erhalten rechtzeitig eine erneute Erinnerung.
        </p>
        <p class="text-muted mb-0" style="font-size:12px;">
            <i class="bi bi-x-circle me-1"></i>Sie können dieses Fenster jetzt schließen.
            <?php if ($brandSupportEmail): ?>
                <br>Bei Fragen: <a href="mailto:<?= htmlspecialchars($brandSupportEmail) ?>"><?= htmlspecialchars($brandSupportEmail) ?></a>
            <?php endif; ?>
        </p>
    </div>
</div>
<?php if ($brandFooter): ?>
<footer class="brand-footer"><?= htmlspecialchars($brandFooter) ?></footer>
<?php endif; ?>
</body>
</html>
