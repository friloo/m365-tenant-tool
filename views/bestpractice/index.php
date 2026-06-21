<?php
use App\Core\View;
use App\Core\Csrf;

$stepState = function (array $step) use ($progress): string {
    if (isset($progress[$step['id']])) return $progress[$step['id']]; // 'done'|'skipped'
    if (isset($step['auto_done']) && is_callable($step['auto_done']) && ($step['auto_done'])()) return 'auto';
    return 'open';
};
?>
<div class="content-card mb-3 no-print">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
        <div>
            <h1 class="mb-1"><i class="bi bi-compass text-primary"></i> <?= te('Tenant-Härtungs-Leitfaden') ?></h1>
            <p class="text-muted mb-0"><?= te('Best-Practice-Schritt-für-Schritt für einen sicheren Microsoft-365-Tenant. Du kannst Schritte abhaken, überspringen, jederzeit zurückkehren. Der Fortschritt wird im Tool gespeichert.') ?></p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <button onclick="window.print()" class="btn btn-outline-secondary"><i class="bi bi-printer"></i> <?= te('Drucken / PDF') ?></button>
            <form method="post" action="/bestpractice/reset" class="d-inline" onsubmit="return confirm('<?= t('Allen Fortschritt zurücksetzen?') ?>');">
                <?= Csrf::field() ?>
                <button class="btn btn-outline-danger" type="submit"><i class="bi bi-arrow-counterclockwise"></i> <?= te('Zurücksetzen') ?></button>
            </form>
        </div>
    </div>
</div>

<?php $flash = \App\Core\Session::getFlash('success'); $err = \App\Core\Session::getFlash('error'); ?>
<?php if ($flash): ?><div class="content-card mb-3 no-print"><div class="alert alert-success mb-0"><?= View::escape($flash) ?></div></div><?php endif; ?>
<?php if ($err): ?><div class="content-card mb-3 no-print"><div class="alert alert-danger mb-0"><?= View::escape($err) ?></div></div><?php endif; ?>

<!-- ── Gesamt-Fortschritt ───────────────────────────────────── -->
<div class="content-card mb-3">
    <div class="d-flex justify-content-between align-items-baseline mb-2">
        <h5 class="mb-0"><?= te('Gesamtfortschritt') ?></h5>
        <div class="text-muted small">
            <strong class="text-success"><?= (int)$summary['done'] ?></strong> <?= te('erledigt') ?> ·
            <strong class="text-secondary"><?= (int)$summary['skipped'] ?></strong> <?= te('übersprungen') ?> ·
            <strong class="text-warning"><?= (int)$summary['open'] ?></strong> <?= te('offen') ?> ·
            <strong><?= (int)$summary['total'] ?></strong> <?= te('insgesamt') ?>
        </div>
    </div>
    <div class="progress" style="height: 12px; border-radius:8px;">
        <div class="progress-bar bg-success" role="progressbar"
             style="width: <?= (int)$summary['pct'] ?>%;"
             aria-valuenow="<?= (int)$summary['pct'] ?>" aria-valuemin="0" aria-valuemax="100">
            <?= (int)$summary['pct'] ?> %
        </div>
    </div>
    <?php if (!empty($summary['auto_detected'])): ?>
        <p class="small text-muted mt-2 mb-0"><i class="bi bi-magic"></i> <?= (int)$summary['auto_detected'] ?> <?= te('Schritte automatisch als erledigt erkannt (z. B. Setup-Wizard, Compliance-Profil, Backup-Konfiguration).') ?></p>
    <?php endif; ?>
</div>

<!-- ── Phasen ──────────────────────────────────────────────── -->
<?php foreach ($guide as $phase):
    $pSum = $summary['phases'][$phase['id']] ?? ['total' => 0, 'done' => 0, 'skipped' => 0];
    $pPct = $pSum['total'] > 0 ? round(($pSum['done'] / $pSum['total']) * 100) : 0;
    $allDone = $pSum['done'] === $pSum['total'];
?>
    <div class="content-card mb-3" id="<?= View::escape($phase['id']) ?>" style="border-left:4px solid <?= View::escape($phase['color']) ?>;">
        <details <?= $allDone ? '' : 'open' ?>>
            <summary style="cursor:pointer; list-style:none;">
                <div class="d-flex align-items-center gap-3 flex-wrap">
                    <div style="width:48px; height:48px; border-radius:12px; background:<?= View::escape($phase['color']) ?>; color:#fff; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                        <i class="bi bi-<?= View::escape($phase['icon']) ?>" style="font-size:22px;"></i>
                    </div>
                    <div class="flex-grow-1 min-width-0">
                        <h4 class="mb-1"><?= View::escape($phase['title']) ?></h4>
                        <div class="text-muted small"><?= View::escape($phase['subtitle']) ?></div>
                    </div>
                    <div class="text-end" style="min-width:140px;">
                        <div class="small text-muted mb-1"><?= (int)$pSum['done'] ?> / <?= (int)$pSum['total'] ?> Schritte</div>
                        <div class="progress" style="height:6px; border-radius:4px;">
                            <div class="progress-bar" style="background:<?= View::escape($phase['color']) ?>; width:<?= (int)$pPct ?>%;"></div>
                        </div>
                    </div>
                    <i class="bi bi-chevron-down text-muted ms-2" style="font-size:18px;"></i>
                </div>
            </summary>

            <p class="text-muted mt-3 mb-4"><?= View::escape($phase['intro']) ?></p>

            <?php foreach ($phase['steps'] as $step):
                $state = $stepState($step);
                $stateBadge = match ($state) {
                    'done'    => '<span class="badge bg-success"><i class="bi bi-check-lg"></i> ' . te('erledigt') . '</span>',
                    'auto'    => '<span class="badge bg-info text-dark" title="' . te('Vom Tool automatisch erkannt') . '"><i class="bi bi-magic"></i> ' . te('auto-erkannt') . '</span>',
                    'skipped' => '<span class="badge bg-secondary"><i class="bi bi-skip-forward"></i> ' . te('übersprungen') . '</span>',
                    default   => '<span class="badge bg-warning text-dark"><i class="bi bi-circle"></i> ' . te('offen') . '</span>',
                };
                $rowStyle = match ($state) {
                    'done', 'auto' => 'background:#f0fdf4;',
                    'skipped'      => 'background:#f9fafb; opacity:0.7;',
                    default        => 'background:#fff;',
                };
            ?>
                <div class="bp-step border rounded p-3 mb-3" id="<?= View::escape($step['id']) ?>" style="<?= $rowStyle ?>">
                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-2">
                        <h5 class="mb-0">
                            <?php if ($state === 'done' || $state === 'auto'): ?>
                                <i class="bi bi-check-circle-fill text-success"></i>
                            <?php else: ?>
                                <i class="bi bi-circle text-muted"></i>
                            <?php endif; ?>
                            <?= View::escape($step['title']) ?>
                        </h5>
                        <div class="d-flex gap-2 align-items-center">
                            <span class="badge bg-light text-dark border" title="<?= te('Geschätzter Zeitaufwand') ?>">
                                <i class="bi bi-clock"></i> ~<?= (int)$step['time'] ?> min
                            </span>
                            <?= $stateBadge ?>
                        </div>
                    </div>

                    <p class="mb-2"><strong><?= te('Warum:') ?></strong> <?= View::escape($step['why']) ?></p>

                    <?php if (!empty($step['how'])): ?>
                        <p class="mb-1"><strong><?= te('So gehst du vor:') ?></strong></p>
                        <ul class="mb-3">
                            <?php foreach ($step['how'] as $h): ?>
                                <li><?= View::escape($h) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>

                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 no-print">
                        <a href="<?= View::escape($step['link']) ?>" class="btn btn-sm btn-primary">
                            <i class="bi bi-arrow-right"></i> <?= View::escape($step['link_label']) ?>
                        </a>
                        <div class="d-flex gap-2">
                            <?php if ($state !== 'done'): ?>
                                <form method="post" action="/bestpractice/mark" class="d-inline">
                                    <?= Csrf::field() ?>
                                    <input type="hidden" name="step_id" value="<?= View::escape($step['id']) ?>">
                                    <input type="hidden" name="state" value="done">
                                    <input type="hidden" name="anchor" value="<?= View::escape($step['id']) ?>">
                                    <button class="btn btn-sm btn-outline-success" type="submit"><i class="bi bi-check-lg"></i> <?= te('Erledigt') ?></button>
                                </form>
                            <?php endif; ?>
                            <?php if ($state !== 'skipped'): ?>
                                <form method="post" action="/bestpractice/mark" class="d-inline">
                                    <?= Csrf::field() ?>
                                    <input type="hidden" name="step_id" value="<?= View::escape($step['id']) ?>">
                                    <input type="hidden" name="state" value="skipped">
                                    <input type="hidden" name="anchor" value="<?= View::escape($step['id']) ?>">
                                    <button class="btn btn-sm btn-outline-secondary" type="submit"><i class="bi bi-skip-forward"></i> <?= te('Überspringen') ?></button>
                                </form>
                            <?php endif; ?>
                            <?php if ($state !== 'open' && $state !== 'auto'): ?>
                                <form method="post" action="/bestpractice/mark" class="d-inline">
                                    <?= Csrf::field() ?>
                                    <input type="hidden" name="step_id" value="<?= View::escape($step['id']) ?>">
                                    <input type="hidden" name="state" value="open">
                                    <input type="hidden" name="anchor" value="<?= View::escape($step['id']) ?>">
                                    <button class="btn btn-sm btn-link text-muted" type="submit"><i class="bi bi-arrow-counterclockwise"></i> <?= te('wieder öffnen') ?></button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </details>
    </div>
<?php endforeach; ?>

<!-- Quick-Sprung für „nur 5 / nur 30 Minuten" -->
<div class="content-card no-print">
    <h5><i class="bi bi-stopwatch"></i> <?= te('Kurzfristige Varianten') ?></h5>
    <div class="row g-3">
        <div class="col-md-6">
            <div class="border rounded p-3 h-100">
                <h6>🏃 5 Minuten</h6>
                <ol class="mb-0 small">
                    <li><a href="#p1-setup"><?= te('Einrichtungs-Assistent') ?></a></li>
                    <li><a href="#p2-profile"><?= te('Compliance-Profil anwenden') ?></a></li>
                    <li><a href="#p3-breakglass"><?= te('Break-Glass-Account') ?></a></li>
                </ol>
            </div>
        </div>
        <div class="col-md-6">
            <div class="border rounded p-3 h-100">
                <h6>⏱️ 30 Minuten</h6>
                <p class="small mb-1"><?= te('Phase 1 + 2 + 3 — deckt bereits ~80 % des realistischen Angriffsvektors (Identität) ab.') ?></p>
                <a href="#phase1" class="btn btn-sm btn-outline-primary"><?= te('Bei Phase 1 starten') ?></a>
            </div>
        </div>
    </div>
</div>

<style>
details > summary::-webkit-details-marker { display: none; }
.bp-step h5 { font-size: 16px; font-weight: 600; }
.min-width-0 { min-width: 0; }

@media print {
    .no-print { display: none !important; }
    details { display: block !important; }
    details > summary { list-style: none; pointer-events: none; }
    details > summary i.bi-chevron-down { display: none; }
    .bp-step { break-inside: avoid; page-break-inside: avoid; }
    .content-card { break-inside: avoid; box-shadow: none; }
    .badge { border: 1px solid #444 !important; }
}
</style>
