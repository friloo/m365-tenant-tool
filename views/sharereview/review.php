<?php require __DIR__ . '/_brand.php'; ?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Freigabe bestätigen — <?= htmlspecialchars($brandAppName) ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --brand: <?= htmlspecialchars($brandColor) ?>;
            --brand-dark: <?= htmlspecialchars($brandColorDark) ?>;
            --brand-text: <?= htmlspecialchars($brandTextColor) ?>;
        }
        body { background: #f3f4f6; min-height: 100vh; display: flex; flex-direction: column; align-items: center; }
        .review-card { max-width: 640px; width: 100%; margin: 40px auto; }
        .brand-bar { background: var(--brand); color: var(--brand-text); border-radius: 12px 12px 0 0; padding: 18px 28px; }
        .brand-logo { width: 38px; height: 38px; border-radius: 8px; background: rgba(255,255,255,.18); display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 17px; overflow: hidden; flex-shrink: 0; }
        .brand-logo img { width: 100%; height: 100%; object-fit: contain; }
        .card-body { background: #fff; border-radius: 0 0 12px 12px; padding: 32px; box-shadow: 0 4px 24px rgba(0,0,0,.08); }
        .scope-badge { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 13px; font-weight: 600; }
        .scope-anonymous { background: #fee2e2; color: #991b1b; }
        .scope-users { background: #fef9c3; color: #854d0e; }
        .scope-organization { background: #dcfce7; color: #166534; }
        .info-table td { padding: 10px 14px; font-size: 14px; }
        .info-table tr:nth-child(odd) td { background: #f9fafb; }
        .info-table td:first-child { font-weight: 600; width: 140px; color: #374151; }
        .btn-confirm { background: var(--brand); color: var(--brand-text) !important; border: none; padding: 12px 32px; font-size: 15px; font-weight: 600; border-radius: 8px; transition: .15s; }
        .btn-confirm:hover { background: var(--brand-dark); }
        footer.brand-footer { font-size: 12px; color: #9ca3af; text-align: center; padding: 16px; }
    </style>
</head>
<body>
<div class="review-card">
    <div class="brand-bar d-flex align-items-center gap-3">
        <div class="brand-logo">
            <?php if ($brandLogoUrl): ?>
                <img src="<?= htmlspecialchars($brandLogoUrl) ?>" alt="Logo">
            <?php else: ?>
                <?= htmlspecialchars($brandLogoText) ?>
            <?php endif; ?>
        </div>
        <div>
            <div style="font-size:17px;font-weight:700;">Freigabe-Überprüfung</div>
            <div style="font-size:13px;opacity:.85;"><?= htmlspecialchars($brandAppName) ?></div>
        </div>
    </div>
    <div class="card-body">

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger d-flex align-items-center gap-2 mb-4">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($share)): ?>
        <p class="text-muted mb-3" style="font-size:14px;">
            Sie haben eine Datei oder einen Ordner freigegeben, die regelmäßig überprüft werden muss.
            Bitte bestätigen Sie, ob diese Freigabe noch benötigt wird.
        </p>

        <table class="table info-table mb-4 rounded overflow-hidden">
            <tbody>
                <tr>
                    <td>Datei/Ordner</td>
                    <td>
                        <strong><?= htmlspecialchars($share['item_name'] ?? '—') ?></strong>
                        <?php if (!empty($share['item_url'])): ?>
                            <a href="<?= htmlspecialchars($share['item_url']) ?>" target="_blank"
                               class="ms-2" style="color:var(--brand);" title="Datei öffnen">
                                <i class="bi bi-box-arrow-up-right"></i>
                            </a>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <td>Speicherort</td>
                    <td><?= htmlspecialchars($share['site_name'] ?? '—') ?></td>
                </tr>
                <tr>
                    <td>Freigabe-Typ</td>
                    <td>
                        <?php
                        $scopeClass = match($share['share_scope'] ?? '') {
                            'anonymous'    => 'scope-anonymous',
                            'users'        => 'scope-users',
                            'organization' => 'scope-organization',
                            default        => '',
                        };
                        $scopeLabel = match($share['share_scope'] ?? '') {
                            'anonymous'    => '🌐 Öffentlich (Anyone-Link)',
                            'users'        => '👥 Externe Benutzer',
                            'organization' => '🏢 Gesamte Organisation',
                            default        => htmlspecialchars($share['share_scope'] ?? ''),
                        };
                        ?>
                        <span class="scope-badge <?= $scopeClass ?>"><?= $scopeLabel ?></span>
                    </td>
                </tr>
                <tr>
                    <td>Freigabe seit</td>
                    <td><?= $share['first_detected']
                        ? htmlspecialchars(date('d.m.Y', strtotime($share['first_detected'])))
                        : '—' ?></td>
                </tr>
                <?php if (!empty($share['auto_revoke_at'])): ?>
                <tr>
                    <td>Frist</td>
                    <td>
                        <span class="text-danger fw-semibold">
                            <i class="bi bi-clock-history me-1"></i>
                            <?= htmlspecialchars(date('d.m.Y', strtotime($share['auto_revoke_at']))) ?>
                        </span>
                        <span class="text-muted ms-1" style="font-size:12px;">(danach automatischer Widerruf)</span>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <form method="post" action="/review/<?= htmlspecialchars($token ?? '') ?>">
            <div class="mb-4">
                <label class="form-label fw-semibold">
                    Begründung <span class="text-danger">*</span>
                </label>
                <textarea name="reason" class="form-control" rows="3" required minlength="5"
                    placeholder="z.B. Wird für die Zusammenarbeit mit Partner XY bis Ende Q2 benötigt."
                ></textarea>
                <div class="form-text">Mindestens 5 Zeichen. Ihre Begründung wird protokolliert.</div>
            </div>

            <div class="d-flex gap-3 align-items-center flex-wrap">
                <button type="submit" class="btn btn-confirm">
                    <i class="bi bi-check-circle me-2"></i>Freigabe bestätigen
                </button>
                <span class="text-muted" style="font-size:12px;">
                    Verlängerung um <?= (int)($share['review_interval_days'] ?? 30) ?> Tage.
                </span>
            </div>
        </form>
        <?php else: ?>
            <div class="alert alert-warning">Freigabe-Daten konnten nicht geladen werden.</div>
        <?php endif; ?>

        <hr class="my-4">
        <p class="text-muted mb-0" style="font-size:12px;">
            <i class="bi bi-info-circle me-1"></i>
            Dieser Link ist personalisiert und kann nur einmal verwendet werden.
            Sie benötigen kein Passwort.
            <?php if ($brandSupportEmail): ?>
                · Bei Fragen: <a href="mailto:<?= htmlspecialchars($brandSupportEmail) ?>"><?= htmlspecialchars($brandSupportEmail) ?></a>
            <?php endif; ?>
        </p>
    </div>
</div>

<?php if ($brandFooter): ?>
<footer class="brand-footer"><?= htmlspecialchars($brandFooter) ?></footer>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
