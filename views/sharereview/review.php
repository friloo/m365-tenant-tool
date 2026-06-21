<?php require __DIR__ . '/_brand.php'; ?>
<!DOCTYPE html>
<html lang="<?= \App\Core\View::escape(\App\Core\I18n::locale()) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= te('Freigabe bestätigen') ?> — <?= htmlspecialchars($brandAppName) ?></title>
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
            --soft:   #f8fafc;
        }
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', system-ui, -apple-system, "Segoe UI", sans-serif;
            color: var(--ink);
            background:
                radial-gradient(ellipse 80% 50% at 50% -10%, rgba(var(--brand-rgb), 0.10), transparent 70%),
                linear-gradient(180deg, #ffffff 0%, #f4f6fb 100%);
            min-height: 100vh;
            min-height: 100dvh;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 32px 16px calc(56px + env(safe-area-inset-bottom, 0px));
            line-height: 1.5;
            -webkit-font-smoothing: antialiased;
        }

        /* Demo banner */
        .demo-bar {
            width: 100%;
            background: #1d4ed8;
            color: #fff;
            text-align: center;
            padding: 10px 16px;
            font-size: 13px;
            font-weight: 600;
            letter-spacing: .2px;
            position: sticky;
            top: 0; z-index: 200;
            margin: -32px -16px 24px;
        }
        .demo-bar a { color: #bfdbfe; text-decoration: underline; margin-left: 12px; font-weight: 400; }

        /* ─── Brand-Hero (Logo über der Card) ─── */
        .brand-hero {
            width: 100%;
            max-width: 600px;
            margin: 8px auto 28px;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }
        .brand-logo-img {
            display: block;
            height: auto;
            max-height: 84px;
            max-width: 260px;
            width: auto;
            object-fit: contain;
        }
        .brand-logo-fallback {
            width: 76px; height: 76px;
            flex-shrink: 0;
            background: linear-gradient(135deg, var(--brand) 0%, var(--brand-dark) 100%);
            color: var(--brand-text);
            border-radius: 18px;
            display: flex; align-items: center; justify-content: center;
            font-weight: 800; font-size: 32px;
            letter-spacing: -.5px;
            box-shadow: 0 10px 24px -8px rgba(var(--brand-rgb), .45);
        }
        .brand-name {
            font-size: 13px;
            font-weight: 600;
            color: var(--ink-2);
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin-top: 14px;
        }

        /* ─── Card ─── */
        .review-card {
            width: 100%;
            max-width: 600px;
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 20px;
            box-shadow:
                0 1px 2px rgba(15,23,42,.04),
                0 24px 48px -16px rgba(15,23,42,.10);
            overflow: hidden;
        }
        /* schmaler farbiger Akzent-Streifen oben */
        .card-accent {
            height: 4px;
            background: linear-gradient(90deg, var(--brand) 0%, var(--brand-dark) 100%);
        }

        .card-body { padding: 32px 36px 36px; }

        .page-title {
            font-size: 22px;
            font-weight: 700;
            color: var(--ink);
            letter-spacing: -.3px;
            margin-bottom: 6px;
        }
        .page-lead {
            font-size: 14.5px;
            color: var(--ink-2);
            margin-bottom: 24px;
        }

        /* Error */
        .alert-error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 12px;
            padding: 12px 16px;
            display: flex; align-items: center; gap: 10px;
            font-size: 14px; color: #991b1b;
            margin-bottom: 20px;
        }
        .alert-error i { font-size: 16px; flex-shrink: 0; }

        /* Info blocks */
        .info-block {
            background: var(--soft);
            border: 1px solid var(--line);
            border-radius: 14px;
            overflow: hidden;
            margin-bottom: 20px;
        }
        .info-row {
            display: flex; align-items: flex-start;
            gap: 14px; padding: 14px 18px;
            border-bottom: 1px solid var(--line);
        }
        .info-row:last-child { border-bottom: none; }
        .info-icon {
            width: 36px; height: 36px; flex-shrink: 0;
            border-radius: 10px;
            background: rgba(var(--brand-rgb), .1);
            color: var(--brand);
            display: flex; align-items: center; justify-content: center;
            font-size: 16px;
            margin-top: 1px;
        }
        .info-content { min-width: 0; flex: 1; }
        .info-label {
            font-size: 11px; font-weight: 600; color: #94a3b8;
            text-transform: uppercase; letter-spacing: .8px;
            margin-bottom: 3px;
        }
        .info-value {
            font-size: 14.5px; color: var(--ink); font-weight: 500;
            line-height: 1.45; display: flex; align-items: center;
            gap: 8px; flex-wrap: wrap; word-break: break-word;
        }
        .info-value a { color: var(--brand); text-decoration: none; font-size: 13px; }
        .info-value a:hover { text-decoration: underline; }

        /* Scope badges */
        .scope-badge {
            display: inline-flex; align-items: center; gap: 5px;
            padding: 4px 10px; border-radius: 999px;
            font-size: 12px; font-weight: 600;
        }
        .scope-anonymous    { background: #fee2e2; color: #991b1b; }
        .scope-users        { background: #fef3c7; color: #92400e; }
        .scope-organization { background: #dcfce7; color: #166534; }

        /* Deadline urgency */
        .deadline-block {
            background: linear-gradient(135deg, #fffbeb 0%, #fff7ed 100%);
            border: 1px solid #fde68a;
            border-radius: 14px;
            padding: 16px 18px;
            margin-bottom: 22px;
            display: flex; align-items: center; gap: 14px;
        }
        .deadline-icon {
            width: 42px; height: 42px; flex-shrink: 0;
            border-radius: 12px;
            background: linear-gradient(135deg, #fb923c, #f97316);
            color: #fff;
            display: flex; align-items: center; justify-content: center;
            font-size: 20px;
            box-shadow: 0 4px 12px -4px rgba(249,115,22,.5);
        }
        .deadline-label { font-size: 11px; font-weight: 700; color: #92400e; text-transform: uppercase; letter-spacing: .7px; margin-bottom: 2px; }
        .deadline-date  { font-size: 16px; font-weight: 700; color: #9a3412; }
        .deadline-note  { font-size: 12.5px; color: #b45309; margin-top: 2px; }

        /* Textarea */
        .field-wrap { margin-bottom: 24px; }
        .field-label {
            display: block; font-size: 13.5px; font-weight: 600; color: var(--ink);
            margin-bottom: 8px;
        }
        .field-label span { color: #ef4444; }
        textarea.field-input {
            width: 100%;
            border: 1.5px solid var(--line);
            border-radius: 12px;
            padding: 14px 16px;
            font-size: 14.5px; font-family: inherit; color: var(--ink);
            background: #fff;
            resize: vertical; min-height: 96px;
            outline: none;
            transition: border-color .15s, box-shadow .15s;
        }
        textarea.field-input:focus {
            border-color: var(--brand);
            box-shadow: 0 0 0 4px rgba(var(--brand-rgb), .12);
        }
        .field-hint { font-size: 12.5px; color: #94a3b8; margin-top: 8px; }

        /* Submit row */
        .submit-row { display: flex; align-items: center; gap: 16px; flex-wrap: wrap; }
        .btn-confirm {
            display: inline-flex; align-items: center; gap: 8px;
            background: var(--brand);
            color: var(--brand-text);
            border: none; border-radius: 12px;
            padding: 13px 26px;
            font-size: 15px; font-weight: 600;
            font-family: inherit; cursor: pointer;
            transition: background .15s, transform .1s, box-shadow .2s;
            box-shadow: 0 6px 16px -4px rgba(var(--brand-rgb), .45);
            text-decoration: none;
        }
        .btn-confirm:hover:not(:disabled) {
            background: var(--brand-dark);
            transform: translateY(-1px);
            box-shadow: 0 10px 24px -6px rgba(var(--brand-rgb), .55);
        }
        .btn-confirm:active:not(:disabled) { transform: translateY(0); }
        .btn-confirm:disabled { opacity: .55; cursor: not-allowed; }
        .extend-note { font-size: 12.5px; color: #94a3b8; }

        /* Divider + footer note */
        .card-divider { border: none; border-top: 1px solid var(--line); margin: 28px 0 20px; }
        .footer-note {
            font-size: 12.5px; color: #94a3b8; line-height: 1.65;
            display: flex; align-items: flex-start; gap: 8px;
        }
        .footer-note i { color: var(--brand); margin-top: 2px; flex-shrink: 0; }
        .footer-note a { color: var(--brand); text-decoration: none; }
        .footer-note a:hover { text-decoration: underline; }

        /* Page footer */
        .page-footer { font-size: 12px; color: #94a3b8; text-align: center; margin-top: 32px; padding: 0 16px; max-width: 600px; }

        @media (max-width: 600px) {
            body { padding: 24px 12px calc(56px + env(safe-area-inset-bottom, 0px)); }
            .brand-hero { margin: 4px auto 20px; }
            .brand-logo-img { max-height: 72px; max-width: 220px; }
            .brand-logo-fallback { width: 68px; height: 68px; font-size: 28px; border-radius: 16px; }
            .card-body { padding: 24px 20px 28px; }
            .page-title { font-size: 19px; }
            .info-row { padding: 12px 14px; gap: 12px; }
            .info-icon { width: 32px; height: 32px; font-size: 14px; }
            .deadline-block { padding: 14px; gap: 12px; }
            .deadline-icon { width: 38px; height: 38px; font-size: 18px; }
            .btn-confirm { padding: 12px 22px; font-size: 14.5px; }
            .page-footer { margin-bottom: 8px; }
        }
    </style>
</head>
<body>

<?php if (!empty($isDemo)): ?>
<div class="demo-bar">
    <i class="bi bi-eye me-2"></i><?= te('VORSCHAU — So sehen Benutzer diese Seite nach Erhalt der Review-E-Mail') ?>
    <a href="/settings">← <?= te('Einstellungen') ?></a>
</div>
<?php endif; ?>

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

<div class="review-card">
    <div class="card-accent"></div>
    <div class="card-body">

        <?php if (!empty($error)): ?>
        <div class="alert-error">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>

        <?php if (!empty($share)): ?>

        <h1 class="page-title"><?= te('Freigabe-Überprüfung') ?></h1>
        <p class="page-lead">
            <?= te('Sie haben eine Datei oder einen Ordner freigegeben, die regelmäßig überprüft werden muss. '
            . 'Bitte bestätigen Sie, ob diese Freigabe noch benötigt wird.') ?>
        </p>

        <div class="info-block">
            <div class="info-row">
                <div class="info-icon"><i class="bi bi-file-earmark-text"></i></div>
                <div class="info-content">
                    <div class="info-label"><?= te('Datei / Ordner') ?></div>
                    <div class="info-value">
                        <?= htmlspecialchars($share['item_name'] ?? '—') ?>
                        <?php if (!empty($share['item_url'])): ?>
                            <a href="<?= htmlspecialchars($share['item_url']) ?>" target="_blank" rel="noopener" title="<?= te('Datei öffnen') ?>">
                                <i class="bi bi-box-arrow-up-right"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="info-row">
                <div class="info-icon"><i class="bi bi-building"></i></div>
                <div class="info-content">
                    <div class="info-label"><?= te('Speicherort') ?></div>
                    <div class="info-value"><?= htmlspecialchars($share['site_name'] ?? '—') ?></div>
                </div>
            </div>
            <div class="info-row">
                <div class="info-icon"><i class="bi bi-share"></i></div>
                <div class="info-content">
                    <div class="info-label"><?= te('Freigabe-Typ') ?></div>
                    <div class="info-value">
                        <?php
                        $scopeClass = match($share['share_scope'] ?? '') {
                            'anonymous'    => 'scope-anonymous',
                            'users'        => 'scope-users',
                            'organization' => 'scope-organization',
                            default        => '',
                        };
                        $scopeIcon = match($share['share_scope'] ?? '') {
                            'anonymous'    => 'globe',
                            'users'        => 'people',
                            'organization' => 'diagram-3',
                            default        => 'question-circle',
                        };
                        $scopeLabel = match($share['share_scope'] ?? '') {
                            'anonymous'    => te('Öffentlich (Anyone-Link)'),
                            'users'        => te('Externe Benutzer'),
                            'organization' => te('Gesamte Organisation'),
                            default        => htmlspecialchars($share['share_scope'] ?? ''),
                        };
                        ?>
                        <span class="scope-badge <?= $scopeClass ?>">
                            <i class="bi bi-<?= $scopeIcon ?>"></i>
                            <?= $scopeLabel ?>
                        </span>
                    </div>
                </div>
            </div>
            <div class="info-row">
                <div class="info-icon"><i class="bi bi-calendar-check"></i></div>
                <div class="info-content">
                    <div class="info-label"><?= te('Freigabe seit') ?></div>
                    <div class="info-value">
                        <?= $share['first_detected']
                            ? htmlspecialchars(date('d.m.Y', strtotime($share['first_detected'])))
                            : '—' ?>
                    </div>
                </div>
            </div>
        </div>

        <?php if (!empty($share['auto_revoke_at'])): ?>
        <div class="deadline-block">
            <div class="deadline-icon"><i class="bi bi-hourglass-split"></i></div>
            <div class="info-content">
                <div class="deadline-label"><?= te('Automatischer Widerruf am') ?></div>
                <div class="deadline-date"><?= htmlspecialchars(date('d.m.Y', strtotime($share['auto_revoke_at']))) ?></div>
                <div class="deadline-note"><?= te('Bitte bestätigen Sie rechtzeitig, um die Freigabe zu erhalten.') ?></div>
            </div>
        </div>
        <?php endif; ?>

        <form method="post" action="/review/<?= htmlspecialchars($token ?? '') ?>">
            <?= \App\Core\Csrf::field() ?>
            <div class="field-wrap">
                <label class="field-label">
                    <?= te('Begründung') ?> <span>*</span>
                </label>
                <textarea name="reason" class="field-input" rows="3" required minlength="5"
                    placeholder="<?= te('z.B. Wird für die Zusammenarbeit mit Partner XY bis Ende Q2 benötigt.') ?>"
                ></textarea>
                <div class="field-hint"><?= te('Mindestens 5 Zeichen. Ihre Begründung wird protokolliert.') ?></div>
            </div>

            <div class="submit-row">
                <button type="submit" class="btn-confirm" <?= !empty($isDemo) ? 'disabled title="' . te('Demo — Formular kann nicht abgeschickt werden') . '"' : '' ?>>
                    <i class="bi bi-check-circle-fill"></i>
                    <?= te('Freigabe bestätigen') ?>
                </button>
                <span class="extend-note">
                    <?= te('Verlängerung um :n Tage', ['n' => (int)($share['review_interval_days'] ?? 30)]) ?>
                </span>
            </div>
        </form>

        <?php else: ?>
        <div style="background:#fefce8;border:1px solid #fde68a;border-radius:12px;padding:14px 18px;font-size:14px;color:#854d0e;">
            <i class="bi bi-exclamation-circle me-2"></i><?= te('Freigabe-Daten konnten nicht geladen werden.') ?>
        </div>
        <?php endif; ?>

        <hr class="card-divider">

        <p class="footer-note">
            <i class="bi bi-shield-lock-fill"></i>
            <span>
                <?= te('Dieser Link ist personalisiert und kann nur einmal verwendet werden. Sie benötigen kein Passwort.') ?>
                <?php if ($brandSupportEmail): ?>
                    &nbsp;·&nbsp; <?= te('Bei Fragen:') ?>
                    <a href="mailto:<?= htmlspecialchars($brandSupportEmail) ?>"><?= htmlspecialchars($brandSupportEmail) ?></a>
                <?php endif; ?>
            </span>
        </p>

    </div>
</div>

<?php if ($brandFooter): ?>
<div class="page-footer"><?= htmlspecialchars($brandFooter) ?></div>
<?php endif; ?>

</body>
</html>
