<?php use App\Core\View; $e = fn($v) => View::escape($v);

$pct = $score['percent'] ?? null;
$scoreColor = $pct === null ? '#6b7280' : ($pct >= 80 ? '#16a34a' : ($pct >= 50 ? '#d97706' : '#dc2626'));

$prioBadge = [
    'critical' => ['bg-danger',  t('Kritisch')],
    'high'     => ['bg-warning text-dark', t('Hoch')],
    'medium'   => ['bg-info text-dark', t('Mittel')],
    'low'      => ['bg-secondary', t('Niedrig')],
];

$setupDone  = count(array_filter($setup, fn($i) => $i['done']));
$setupTotal = count($setup);
?>

<div class="alert alert-info d-flex gap-3 mb-3">
    <i class="bi bi-compass flex-shrink-0 mt-1" style="font-size:1.4rem;color:#0078d4;"></i>
    <div>
        <strong><?= te('Dein Startpunkt zur Tenant-Konfiguration.') ?></strong>
        <?= te('Diese Seite bündelt den Sicherheits-Score, den Einrichtungsfortschritt und die wichtigsten nächsten Schritte — jeweils mit Direktlink zur Behebung.') ?>
    </div>
</div>

<div class="row g-3 mb-1">
    <!-- Score hero -->
    <div class="col-lg-4">
        <div class="content-card h-100 text-center">
            <div class="card-body-custom">
                <div class="text-muted small text-uppercase mb-2" style="letter-spacing:.5px;"><?= te('Sicherheits-Score') ?></div>
                <div style="font-size:3rem;font-weight:700;line-height:1;color:<?= $scoreColor ?>;">
                    <?= $pct === null ? '–' : (int)$pct . '%' ?>
                </div>
                <?php if ($postureError): ?>
                    <div class="text-muted small mt-2"><?= te('Score nicht verfügbar — Microsoft-365-Verbindung prüfen.') ?></div>
                <?php elseif (empty($postureReady)): ?>
                    <div class="text-muted small mt-2"><?= te('Noch nicht berechnet — wird im Hintergrund erstellt.') ?></div>
                    <a href="/action-center?refresh=1" class="btn btn-sm btn-primary mt-3"><i class="bi bi-arrow-clockwise me-1"></i><?= te('Jetzt berechnen') ?></a>
                <?php else: ?>
                    <div class="d-flex justify-content-center gap-2 mt-3 flex-wrap">
                        <span class="badge bg-success"><?= (int)$score['passed'] ?> <?= te('bestanden') ?></span>
                        <span class="badge bg-warning text-dark"><?= (int)$score['warned'] ?> <?= te('Warnung') ?></span>
                        <span class="badge bg-danger"><?= (int)$score['failed'] ?> <?= te('offen') ?></span>
                    </div>
                    <a href="/securityposture" class="btn btn-sm btn-outline-secondary mt-3"><?= te('Alle Prüfungen ansehen') ?></a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Setup completeness -->
    <div class="col-lg-8">
        <div class="content-card h-100">
            <div class="card-header-custom">
                <i class="bi bi-list-check text-primary"></i>
                <h6><?= te('Einrichtungsfortschritt') ?></h6>
                <span class="ms-auto text-muted small"><?= $setupDone ?>/<?= $setupTotal ?></span>
            </div>
            <div class="card-body-custom">
                <div class="d-flex flex-column gap-2">
                    <?php foreach ($setup as $item): ?>
                        <a href="<?= $e($item['url']) ?>" class="d-flex align-items-center gap-2 text-decoration-none p-2 rounded" style="color:#1e293b;background:<?= $item['done'] ? '#f0fdf4' : '#fafafa'; ?>;">
                            <?php if ($item['done']): ?>
                                <i class="bi bi-check-circle-fill text-success"></i>
                            <?php else: ?>
                                <i class="bi bi-circle text-muted"></i>
                            <?php endif; ?>
                            <span class="fw-medium"><?= $e($item['label']) ?></span>
                            <?php if (!$item['done']): ?>
                                <span class="text-muted small ms-auto d-none d-md-inline"><?= $e($item['hint']) ?></span>
                                <i class="bi bi-arrow-right text-muted"></i>
                            <?php endif; ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Prioritized next actions -->
<div class="content-card mt-3">
    <div class="card-header-custom">
        <i class="bi bi-lightning-charge text-primary"></i>
        <h6><?= te('Nächste empfohlene Schritte') ?></h6>
        <span class="ms-auto text-muted small"><?= count($recommendations) ?></span>
    </div>
    <div class="card-body-custom">
        <?php if ($postureError): ?>
            <div class="text-muted text-center py-4"><?= te('Empfehlungen nicht verfügbar — bitte zuerst die Microsoft-365-Verbindung in den Einstellungen konfigurieren.') ?></div>
        <?php elseif (empty($postureReady)): ?>
            <div class="text-center py-4">
                <div class="spinner-border text-secondary" role="status" style="width:1.5rem;height:1.5rem;"></div>
                <div class="mt-2 text-muted"><?= te('Die Sicherheitsanalyse wird im Hintergrund berechnet (Cache-Warm-Job) und erscheint in Kürze. Du kannst sie auch sofort berechnen:') ?></div>
                <a href="/action-center?refresh=1" class="btn btn-sm btn-primary mt-2"><i class="bi bi-arrow-clockwise me-1"></i><?= te('Jetzt berechnen') ?></a>
            </div>
        <?php elseif (empty($recommendations)): ?>
            <div class="text-center py-4">
                <i class="bi bi-check-circle text-success" style="font-size:2rem;"></i>
                <div class="mt-2 fw-medium"><?= te('Keine offenen Empfehlungen — gut gemacht!') ?></div>
            </div>
        <?php else: ?>
            <div class="d-flex flex-column gap-2">
                <?php foreach ($recommendations as $r): ?>
                    <?php [$badgeClass, $badgeLabel] = $prioBadge[$r['priority']] ?? ['bg-secondary', $r['priority']]; ?>
                    <div class="d-flex align-items-start gap-3 p-3 rounded" style="background:#fafafa;border:1px solid #eef0f3;">
                        <span class="badge <?= $badgeClass ?> flex-shrink-0 mt-1"><?= $e($badgeLabel) ?></span>
                        <div class="flex-grow-1">
                            <div class="fw-semibold"><?= $e($r['title']) ?></div>
                            <div class="text-muted small mt-1"><?= $e($r['description']) ?></div>
                        </div>
                        <a href="<?= $e($r['module_url']) ?>" class="btn btn-sm btn-primary flex-shrink-0 align-self-center">
                            <?= $e($r['action']) ?> <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
