<?php require __DIR__ . '/_brand.php'; ?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= te('Link ungültig') ?> — <?= htmlspecialchars($brandAppName) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --brand: <?= htmlspecialchars($brandColor) ?>;
            --brand-dark: <?= htmlspecialchars($brandColorDark) ?>;
            --brand-text: <?= htmlspecialchars($brandTextColor) ?>;
            --brand-rgb: <?php
                $hex = ltrim($brandColor, '#');
                if (strlen($hex) === 6) {
                    echo hexdec(substr($hex,0,2)) . ',' . hexdec(substr($hex,2,2)) . ',' . hexdec(substr($hex,4,2));
                } else { echo '0,120,212'; }
            ?>;
            --ink:    #0f172a;
            --ink-2:  #475569;
            --line:   #e2e8f0;
        }
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', system-ui, -apple-system, "Segoe UI", sans-serif;
            color: var(--ink);
            background:
                radial-gradient(ellipse 80% 50% at 50% -10%, rgba(220,38,38, 0.10), transparent 70%),
                linear-gradient(180deg, #ffffff 0%, #f4f6fb 100%);
            min-height: 100vh;
            min-height: 100dvh;
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            padding: 32px 16px calc(40px + env(safe-area-inset-bottom, 0px));
            line-height: 1.5;
            -webkit-font-smoothing: antialiased;
        }
        .brand-hero {
            width: 100%; max-width: 560px; margin: 8px auto 28px;
            display: flex; flex-direction: column; align-items: center; text-align: center;
        }
        .brand-logo-img { display: block; height: auto; max-height: 84px; max-width: 260px; width: auto; object-fit: contain; }
        .brand-logo-fallback {
            width: 76px; height: 76px; flex-shrink: 0;
            background: linear-gradient(135deg, var(--brand) 0%, var(--brand-dark) 100%);
            color: var(--brand-text);
            border-radius: 18px;
            display: flex; align-items: center; justify-content: center;
            font-weight: 800; font-size: 32px; letter-spacing: -.5px;
            box-shadow: 0 10px 24px -8px rgba(var(--brand-rgb), .45);
        }
        .brand-name {
            font-size: 13px; font-weight: 600; color: var(--ink-2);
            text-transform: uppercase; letter-spacing: 1.5px; margin-top: 14px;
        }
        .result-card {
            width: 100%; max-width: 560px;
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 20px;
            box-shadow: 0 1px 2px rgba(15,23,42,.04), 0 24px 48px -16px rgba(15,23,42,.10);
            overflow: hidden;
        }
        .card-accent { height: 4px; background: linear-gradient(90deg, #dc2626, #ef4444); }
        .card-body { padding: 40px 36px 36px; text-align: center; }
        .icon-circle {
            width: 84px; height: 84px; border-radius: 50%;
            background: linear-gradient(135deg, #fee2e2, #fecaca);
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 24px;
            box-shadow: 0 10px 30px -8px rgba(220,38,38, .25);
        }
        .icon-circle i { font-size: 42px; color: #dc2626; }
        .result-title { font-size: 22px; font-weight: 700; color: var(--ink); margin-bottom: 8px; letter-spacing: -.3px; }
        .result-lead  { font-size: 15px; color: var(--ink-2); line-height: 1.6; margin: 0 auto 28px; max-width: 440px; }
        .footer-note  {
            font-size: 12.5px; color: #94a3b8; line-height: 1.6;
            padding-top: 20px; border-top: 1px solid var(--line);
        }
        .footer-note a { color: var(--brand); text-decoration: none; }
        .footer-note a:hover { text-decoration: underline; }
        .page-footer  {
            font-size: 12px; color: #94a3b8; text-align: center;
            margin-top: 32px; padding: 0 16px; max-width: 560px;
        }
        @media (max-width: 600px) {
            body { padding: 24px 12px calc(56px + env(safe-area-inset-bottom, 0px)); }
            .brand-hero { margin: 4px auto 20px; }
            .brand-logo-img { max-height: 72px; max-width: 220px; }
            .brand-logo-fallback { width: 68px; height: 68px; font-size: 28px; border-radius: 16px; }
            .card-body { padding: 32px 22px 28px; }
            .icon-circle { width: 72px; height: 72px; }
            .icon-circle i { font-size: 36px; }
            .result-title { font-size: 19px; }
            .page-footer { margin-bottom: 8px; }
        }
    </style>
</head>
<body>

<div class="brand-hero">
    <?php if ($brandLogoUrl): ?>
        <img src="<?= htmlspecialchars($brandLogoUrl) ?>"
             alt="<?= htmlspecialchars($brandAppName) ?>"
             class="brand-logo-img"
             onerror="this.style.display='none'; var f=document.getElementById('brandFallback'); if(f) f.style.display='flex';">
    <?php endif; ?>
    <div class="brand-logo-fallback" id="brandFallback"
         style="<?= $brandLogoUrl ? 'display:none;' : '' ?>"><?= htmlspecialchars($brandLogoText) ?></div>
    <div class="brand-name"><?= htmlspecialchars($brandAppName) ?></div>
</div>

<div class="result-card">
    <div class="card-accent"></div>
    <div class="card-body">
        <?php $reason = $reason ?? 'expired'; ?>
        <div class="icon-circle">
            <?php if ($reason === 'used'): ?>
                <i class="bi bi-clock-history"></i>
            <?php elseif ($reason === 'not_found'): ?>
                <i class="bi bi-question-lg"></i>
            <?php else: ?>
                <i class="bi bi-hourglass-bottom"></i>
            <?php endif; ?>
        </div>

        <?php if ($reason === 'used'): ?>
            <h1 class="result-title"><?= te('Link bereits verwendet') ?></h1>
            <p class="result-lead">
                <?= te('Dieser Bestätigungslink wurde bereits einmal verwendet. '
                . 'Falls Sie eine neuere E-Mail erhalten haben, nutzen Sie bitte den Link aus dieser E-Mail.') ?>
            </p>
        <?php elseif ($reason === 'not_found'): ?>
            <h1 class="result-title"><?= te('Link nicht gefunden') ?></h1>
            <p class="result-lead">
                <?= te('Dieser Bestätigungslink ist ungültig oder existiert nicht. '
                . 'Bitte prüfen Sie, ob Sie den vollständigen Link aus der E-Mail kopiert haben.') ?>
            </p>
        <?php else: ?>
            <h1 class="result-title"><?= te('Link abgelaufen') ?></h1>
            <p class="result-lead">
                <?= te('Dieser Bestätigungslink ist abgelaufen. '
                . 'Wenn die Freigabe weiterhin benötigt wird, wenden Sie sich bitte an Ihren IT-Administrator.') ?>
            </p>
        <?php endif; ?>

        <div class="footer-note">
            <i class="bi bi-info-circle me-1"></i><?= te('Sie können dieses Fenster schließen.') ?>
            <?php if ($brandSupportEmail): ?>
                <br><?= te('Bei Fragen:') ?>
                <a href="mailto:<?= htmlspecialchars($brandSupportEmail) ?>"><?= htmlspecialchars($brandSupportEmail) ?></a>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if ($brandFooter): ?>
<div class="page-footer"><?= htmlspecialchars($brandFooter) ?></div>
<?php endif; ?>

</body>
</html>
