<?php use App\Core\View; $e = fn($v) => View::escape($v); ?>

<?php if (!empty($diag)) include BASE_PATH . '/views/partials/graph_diagnostic.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div class="text-muted small">
        <?= te('Vorfälle, bei denen DLP-Regeln oder Sensitivity-Labels griffen — der eigentliche Compliance-Audit (Art. 5 + 32 DSGVO).') ?>
    </div>
    <div class="btn-group btn-group-sm" role="group">
        <?php foreach ([7, 30, 90] as $d): ?>
            <a href="?days=<?= $d ?>" class="btn <?= $days === $d ? 'btn-primary' : 'btn-outline-primary' ?>">
                <?= te('Letzte :n Tage', ['n' => $d]) ?>
            </a>
        <?php endforeach; ?>
    </div>
</div>

<!-- ── Summary ───────────────────────────────────────────────────────── -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="metric-card" style="border-left:4px solid <?= $summary['total'] > 0 ? '#dc2626' : '#16a34a' ?>;">
            <div class="metric-label"><i class="bi bi-exclamation-triangle me-1"></i><?= te('Vorfälle gesamt') ?></div>
            <div class="metric-value"><?= number_format($summary['total']) ?></div>
            <div class="metric-sub"><?= te('in den letzten :n Tagen', ['n' => $days]) ?></div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="metric-card">
            <div class="metric-label"><i class="bi bi-people me-1"></i><?= te('Beteiligte User') ?></div>
            <div class="metric-value"><?= number_format($summary['unique_actors']) ?></div>
            <div class="metric-sub"><?= te('unique') ?></div>
        </div>
    </div>
    <div class="col-sm-12 col-lg-6">
        <div class="metric-card">
            <div class="metric-label"><i class="bi bi-bar-chart me-1"></i><?= te('Trend (Vorfälle/Tag)') ?></div>
            <?php if (empty($summary['daily_trend'])): ?>
                <div class="metric-value" style="font-size:18px;color:#9ca3af;"><?= te('Keine Daten') ?></div>
            <?php else:
                $vals = array_values($summary['daily_trend']);
                $max  = max($vals);
                ?>
                <div style="display:flex;align-items:flex-end;gap:2px;height:50px;margin-top:4px;">
                    <?php foreach ($summary['daily_trend'] as $d => $count):
                        $h = $max > 0 ? max(2, (int)($count / $max * 50)) : 2;
                    ?>
                        <div style="flex:1;background:#0078d4;height:<?= $h ?>px;border-radius:2px;"
                             title="<?= $e($d) ?>: <?= $count ?>"></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- ── Top Actors / Activities ──────────────────────────────────────── -->
<?php if (!empty($summary['top_actors'])): ?>
<div class="row g-3 mb-4">
    <div class="col-lg-6">
        <div class="content-card h-100">
            <div class="card-header-custom"><i class="bi bi-person-fill text-warning"></i><h6><?= te('Top User mit DLP-Treffern') ?></h6></div>
            <div class="card-body-custom p-0">
                <table class="table table-sm mb-0">
                    <tbody>
                    <?php foreach ($summary['top_actors'] as $actor => $count): ?>
                        <tr>
                            <td><?= $e($actor) ?></td>
                            <td class="text-end"><span class="badge bg-danger"><?= $count ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="content-card h-100">
            <div class="card-header-custom"><i class="bi bi-activity text-info"></i><h6><?= te('Top Aktivitäten') ?></h6></div>
            <div class="card-body-custom p-0">
                <table class="table table-sm mb-0">
                    <tbody>
                    <?php foreach ($summary['top_activities'] as $act => $count): ?>
                        <tr>
                            <td><code style="font-size:12px;"><?= $e($act) ?></code></td>
                            <td class="text-end"><span class="badge bg-secondary"><?= $count ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- ── Liste der Vorfälle ────────────────────────────────────────────── -->
<div class="content-card mb-4">
    <div class="card-header-custom">
        <i class="bi bi-clock-history text-secondary"></i>
        <h6><?= te('Vorfälle im Detail') ?></h6>
    </div>
    <div class="card-body-custom p-0">
        <?php if (empty($incidents)): ?>
            <div class="text-muted small p-4 text-center">
                <i class="bi bi-check-circle text-success me-1"></i>
                <?= te('Keine DLP-Vorfälle im gewählten Zeitraum.') ?>
                <br><small><?= te('Falls hier nichts steht, obwohl DLP-Policies aktiv sind: prüfen Sie in Microsoft Purview, ob die Policies im "enforce" und nicht im "test" Modus laufen.') ?></small>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead><tr>
                        <th><?= te('Wann') ?></th>
                        <th><?= te('Auslöser') ?></th>
                        <th><?= te('Aktivität') ?></th>
                        <th><?= te('Ziel') ?></th>
                        <th><?= te('Resultat') ?></th>
                    </tr></thead>
                    <tbody>
                    <?php foreach ($incidents as $i): ?>
                        <tr>
                            <td class="text-muted small">
                                <?= $i['when'] ? $e(date('d.m.Y H:i', strtotime($i['when']))) : '–' ?>
                            </td>
                            <td><?= $e($i['actor']) ?></td>
                            <td>
                                <div><code style="font-size:12px;"><?= $e($i['activity']) ?></code></div>
                                <?php if ($i['details']): ?>
                                    <div class="text-muted small mt-1"><?= $e($i['details']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="text-muted small"><?= $e($i['target']) ?></td>
                            <td>
                                <?php $ok = strtolower((string)$i['result']) === 'success'; ?>
                                <span class="badge <?= $ok ? 'bg-secondary' : 'bg-danger' ?>"><?= $e($i['result'] ?: '–') ?></span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
