<?php require __DIR__ . '/_brand.php'; ?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Freigabe bestätigen — <?= htmlspecialchars($brandAppName) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
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
        }
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            min-height: 100vh;
            background:
                radial-gradient(ellipse at 20% 50%, rgba(var(--brand-rgb), 0.08) 0%, transparent 60%),
                radial-gradient(ellipse at 80% 20%, rgba(var(--brand-rgb), 0.05) 0%, transparent 50%),
                #f0f2f5;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 24px 16px 48px;
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
            letter-spacing: .3px;
            position: sticky;
            top: 0;
            z-index: 200;
            margin: -24px -16px 24px;
        }
        .demo-bar a { color: #93c5fd; text-decoration: underline; margin-left: 16px; font-weight: 400; }

        /* Card */
        .review-card {
            width: 100%;
            max-width: 560px;
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 8px 40px rgba(0,0,0,.10), 0 2px 8px rgba(0,0,0,.06);
            overflow: hidden;
        }

        /* Header */
        .card-header {
            background: var(--brand);
            color: var(--brand-text);
            padding: 28px 32px 24px;
            position: relative;
            overflow: hidden;
        }
        .card-header::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(255,255,255,.12) 0%, transparent 60%);
        }
        .card-header::after {
            content: '';
            position: absolute;
            bottom: -40px; right: -40px;
            width: 160px; height: 160px;
            border-radius: 50%;
            background: rgba(255,255,255,.06);
        }
        .brand-logo {
            width: 44px; height: 44px;
            border-radius: 12px;
            background: rgba(255,255,255,.2);
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: 18px;
            overflow: hidden; flex-shrink: 0;
            backdrop-filter: blur(4px);
            border: 1px solid rgba(255,255,255,.25);
        }
        .brand-logo img { width: 100%; height: 100%; object-fit: contain; }
        .header-inner { position: relative; z-index: 1; display: flex; align-items: center; gap: 14px; }
        .header-title { font-size: 18px; font-weight: 700; line-height: 1.2; }
        .header-sub { font-size: 13px; opacity: .75; margin-top: 2px; }

        /* Body */
        .card-body { padding: 28px 32px 32px; }

        /* Error */
        .alert-error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 10px;
            padding: 12px 16px;
            display: flex; align-items: center; gap: 10px;
            font-size: 14px; color: #991b1b;
            margin-bottom: 20px;
        }
        .alert-error i { font-size: 16px; flex-shrink: 0; }

        /* Intro text */
        .intro-text {
            font-size: 14px; color: #6b7280;
            margin-bottom: 20px; line-height: 1.6;
        }

        /* Info blocks */
        .info-block {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            overflow: hidden;
            margin-bottom: 20px;
        }
        .info-row {
            display: flex; align-items: flex-start;
            gap: 12px; padding: 12px 16px;
            border-bottom: 1px solid #f3f4f6;
        }
        .info-row:last-child { border-bottom: none; }
        .info-icon {
            width: 32px; height: 32px; flex-shrink: 0;
            border-radius: 8px;
            background: rgba(var(--brand-rgb), .1);
            color: var(--brand);
            display: flex; align-items: center; justify-content: center;
            font-size: 15px;
            margin-top: 1px;
        }
        .info-label { font-size: 11px; font-weight: 600; color: #9ca3af; text-transform: uppercase; letter-spacing: .5px; margin-bottom: 2px; }
        .info-value { font-size: 14px; color: #111827; font-weight: 500; line-height: 1.4; display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
        .info-value a { color: var(--brand); text-decoration: none; font-size: 13px; }
        .info-value a:hover { text-decoration: underline; }

        /* Scope badges */
        .scope-badge {
            display: inline-flex; align-items: center; gap: 5px;
            padding: 3px 10px; border-radius: 20px; font-size: 12px; font-weight: 600;
        }
        .scope-anonymous { background: #fee2e2; color: #991b1b; }
        .scope-users     { background: #fef9c3; color: #854d0e; }
        .scope-organization { background: #dcfce7; color: #166534; }

        /* Deadline urgency block */
        .deadline-block {
            background: linear-gradient(135deg, #fff7ed, #fef3c7);
            border: 1px solid #fed7aa;
            border-radius: 12px;
            padding: 14px 16px;
            margin-bottom: 20px;
            display: flex; align-items: center; gap: 12px;
        }
        .deadline-icon {
            width: 38px; height: 38px; flex-shrink: 0;
            border-radius: 10px;
            background: #f97316;
            color: #fff;
            display: flex; align-items: center; justify-content: center;
            font-size: 18px;
        }
        .deadline-label { font-size: 11px; font-weight: 600; color: #92400e; text-transform: uppercase; letter-spacing: .5px; margin-bottom: 2px; }
        .deadline-date { font-size: 15px; font-weight: 700; color: #c2410c; }
        .deadline-note { font-size: 12px; color: #b45309; margin-top: 1px; }

        /* Textarea */
        .field-wrap { margin-bottom: 24px; }
        .field-label {
            display: block; font-size: 13px; font-weight: 600; color: #374151;
            margin-bottom: 8px;
        }
        .field-label span { color: #ef4444; }
        textarea.field-input {
            width: 100%;
            border: 1.5px solid #e5e7eb;
            border-radius: 10px;
            padding: 12px 14px;
            font-size: 14px; font-family: inherit; color: #111827;
            background: #fafafa;
            resize: vertical; min-height: 90px;
            outline: none;
            transition: border-color .15s, box-shadow .15s, background .15s;
        }
        textarea.field-input:focus {
            border-color: var(--brand);
            box-shadow: 0 0 0 3px rgba(var(--brand-rgb), .12);
            background: #fff;
        }
        .field-hint { font-size: 12px; color: #9ca3af; margin-top: 6px; }

        /* Submit row */
        .submit-row { display: flex; align-items: center; gap: 16px; flex-wrap: wrap; }
        .btn-confirm {
            display: inline-flex; align-items: center; gap: 8px;
            background: var(--brand);
            color: var(--brand-text);
            border: none; border-radius: 10px;
            padding: 12px 24px; font-size: 15px; font-weight: 600;
            font-family: inherit; cursor: pointer;
            transition: background .15s, transform .1s, box-shadow .15s;
            box-shadow: 0 2px 8px rgba(var(--brand-rgb), .35);
            text-decoration: none;
        }
        .btn-confirm:hover:not(:disabled) {
            background: var(--brand-dark);
            transform: translateY(-1px);
            box-shadow: 0 4px 16px rgba(var(--brand-rgb), .4);
        }
        .btn-confirm:active:not(:disabled) { transform: translateY(0); }
        .btn-confirm:disabled { opacity: .55; cursor: not-allowed; }
        .extend-note { font-size: 12px; color: #9ca3af; }

        /* Divider */
        .card-divider { border: none; border-top: 1px solid #f3f4f6; margin: 24px 0; }

        /* Footer note */
        .footer-note { font-size: 12px; color: #9ca3af; line-height: 1.6; }
        .footer-note a { color: var(--brand); text-decoration: none; }
        .footer-note a:hover { text-decoration: underline; }

        /* Page footer */
        .page-footer { font-size: 12px; color: #c0c4cc; text-align: center; margin-top: 24px; padding: 0 16px; }

        @media (max-width: 600px) {
            body { padding: 16px 12px 40px; }
            .card-header { padding: 22px 20px 18px; }
            .card-body { padding: 20px 20px 24px; }
        }
    </style>
</head>
<body>

<?php if (!empty($isDemo)): ?>
<div class="demo-bar">
    <i class="bi bi-eye me-2"></i>VORSCHAU — So sehen Benutzer diese Seite nach Erhalt der Review-E-Mail
    <a href="/settings">← Einstellungen</a>
</div>
<?php endif; ?>

<div class="review-card">
    <div class="card-header">
        <div class="header-inner">
            <div class="brand-logo">
                <?php if ($brandLogoUrl): ?>
                    <img src="<?= htmlspecialchars($brandLogoUrl) ?>" alt="Logo">
                <?php else: ?>
                    <?= htmlspecialchars($brandLogoText) ?>
                <?php endif; ?>
            </div>
            <div>
                <div class="header-title">Freigabe-Überprüfung</div>
                <div class="header-sub"><?= htmlspecialchars($brandAppName) ?></div>
            </div>
        </div>
    </div>

    <div class="card-body">

        <?php if (!empty($error)): ?>
        <div class="alert-error">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>

        <?php if (!empty($share)): ?>

        <p class="intro-text">
            Sie haben eine Datei oder einen Ordner freigegeben, die regelmäßig überprüft werden muss.
            Bitte bestätigen Sie, ob diese Freigabe noch benötigt wird.
        </p>

        <div class="info-block">
            <div class="info-row">
                <div class="info-icon"><i class="bi bi-file-earmark-text"></i></div>
                <div>
                    <div class="info-label">Datei / Ordner</div>
                    <div class="info-value">
                        <?= htmlspecialchars($share['item_name'] ?? '—') ?>
                        <?php if (!empty($share['item_url'])): ?>
                            <a href="<?= htmlspecialchars($share['item_url']) ?>" target="_blank" title="Datei öffnen">
                                <i class="bi bi-box-arrow-up-right"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="info-row">
                <div class="info-icon"><i class="bi bi-building"></i></div>
                <div>
                    <div class="info-label">Speicherort</div>
                    <div class="info-value"><?= htmlspecialchars($share['site_name'] ?? '—') ?></div>
                </div>
            </div>
            <div class="info-row">
                <div class="info-icon"><i class="bi bi-share"></i></div>
                <div>
                    <div class="info-label">Freigabe-Typ</div>
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
                            'anonymous'    => 'Öffentlich (Anyone-Link)',
                            'users'        => 'Externe Benutzer',
                            'organization' => 'Gesamte Organisation',
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
                <div>
                    <div class="info-label">Freigabe seit</div>
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
            <div>
                <div class="deadline-label">Automatischer Widerruf am</div>
                <div class="deadline-date"><?= htmlspecialchars(date('d.m.Y', strtotime($share['auto_revoke_at']))) ?></div>
                <div class="deadline-note">Bitte bestätigen Sie rechtzeitig, um die Freigabe zu erhalten.</div>
            </div>
        </div>
        <?php endif; ?>

        <form method="post" action="/review/<?= htmlspecialchars($token ?? '') ?>">
            <div class="field-wrap">
                <label class="field-label">
                    Begründung <span>*</span>
                </label>
                <textarea name="reason" class="field-input" rows="3" required minlength="5"
                    placeholder="z.B. Wird für die Zusammenarbeit mit Partner XY bis Ende Q2 benötigt."
                ></textarea>
                <div class="field-hint">Mindestens 5 Zeichen. Ihre Begründung wird protokolliert.</div>
            </div>

            <div class="submit-row">
                <button type="submit" class="btn-confirm" <?= !empty($isDemo) ? 'disabled title="Demo — Formular kann nicht abgeschickt werden"' : '' ?>>
                    <i class="bi bi-check-circle-fill"></i>
                    Freigabe bestätigen
                </button>
                <span class="extend-note">
                    Verlängerung um <?= (int)($share['review_interval_days'] ?? 30) ?> Tage
                </span>
            </div>
        </form>

        <?php else: ?>
        <div style="background:#fefce8;border:1px solid #fde68a;border-radius:10px;padding:14px 16px;font-size:14px;color:#854d0e;">
            <i class="bi bi-exclamation-circle me-2"></i>Freigabe-Daten konnten nicht geladen werden.
        </div>
        <?php endif; ?>

        <hr class="card-divider">

        <p class="footer-note">
            <i class="bi bi-lock me-1"></i>
            Dieser Link ist personalisiert und kann nur einmal verwendet werden. Sie benötigen kein Passwort.
            <?php if ($brandSupportEmail): ?>
                &nbsp;·&nbsp; Bei Fragen:
                <a href="mailto:<?= htmlspecialchars($brandSupportEmail) ?>"><?= htmlspecialchars($brandSupportEmail) ?></a>
            <?php endif; ?>
        </p>

    </div>
</div>

<?php if ($brandFooter): ?>
<div class="page-footer"><?= htmlspecialchars($brandFooter) ?></div>
<?php endif; ?>

</body>
</html>
