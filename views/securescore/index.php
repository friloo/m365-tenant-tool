<?php use App\Core\View; $e = fn($v) => View::escape($v); ?>

<?php
// Determine score color class
$scoreColor = '#dc2626'; // red
if ($pct >= 70) {
    $scoreColor = '#16a34a'; // green
} elseif ($pct >= 40) {
    $scoreColor = '#d97706'; // orange
}
?>

<?php if (empty($latest)): ?>
<div class="content-card mb-4">
    <div class="card-body-custom">
        <?php
        if (!empty($diag ?? null)) {
            $diagStyle = 'empty';
            $diagIcon  = 'shield-exclamation';
            $diagTitle = t('Keine Secure-Score-Daten verfügbar');
            include BASE_PATH . '/views/partials/graph_diagnostic.php';
        } else { ?>
            <div class="empty-state">
                <i class="bi bi-shield-exclamation text-muted" style="font-size:2.5rem;"></i>
                <p class="mt-3 mb-1 fw-medium"><?= te('Keine Secure-Score-Daten verfügbar') ?></p>
                <p class="text-muted small"><?= te('Microsoft hat noch keinen Secure-Score-Snapshot für diesen Tenant erzeugt.') ?></p>
            </div>
        <?php } ?>
    </div>
</div>
<?php else: ?>

<!-- Score Hero Row -->
<div class="row g-3 mb-4 align-items-stretch">
    <div class="col-md-4">
        <div class="metric-card text-center" style="padding: 2rem 1rem;">
            <div class="metric-label mb-2"><?= te('Microsoft Secure Score') ?></div>
            <div style="font-size: 3.5rem; font-weight: 700; line-height:1; color: <?= $scoreColor ?>;">
                <?= number_format($currentScore, 0) ?>
            </div>
            <div class="metric-sub mt-1"><?= te('von :n Punkten', ['n' => number_format($maxScore, 0)]) ?></div>
            <div class="mt-3">
                <div class="progress-custom">
                    <div class="bar" style="width: <?= min(100, $pct) ?>%; background: <?= $scoreColor ?>;"></div>
                </div>
                <div class="mt-1" style="font-size: 13px; color: <?= $scoreColor ?>; font-weight: 600;">
                    <?= $pct ?>%
                    <?php if ($pct >= 70): ?>
                        <span class="badge-success ms-1"><?= te('Gut') ?></span>
                    <?php elseif ($pct >= 40): ?>
                        <span class="badge-warning ms-1"><?= te('Mittel') ?></span>
                    <?php else: ?>
                        <span class="badge-disabled ms-1"><?= te('Niedrig') ?></span>
                    <?php endif; ?>
                </div>
            </div>
            <?php if (!empty($latest['createdDateTime'])): ?>
                <div class="text-muted small mt-2">
                    <?= te('Stand:') ?> <?= date('d.m.Y', strtotime($latest['createdDateTime'])) ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="col-md-8">
        <div class="content-card h-100">
            <div class="card-header-custom">
                <i class="bi bi-graph-up text-primary"></i>
                <h6><?= te('Score-Verlauf (30 Tage)') ?></h6>
            </div>
            <div class="card-body-custom" style="position:relative; height: 200px;">
                <?php if (!empty($history)): ?>
                    <canvas id="scoreChart"></canvas>
                <?php else: ?>
                    <div class="empty-state" style="height:100%;">
                        <span class="text-muted small"><?= te('Keine Verlaufsdaten vorhanden') ?></span>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php endif; ?>

<!-- Control Score Breakdown -->
<?php if (!empty($grouped)): ?>
<div class="content-card mb-4">
    <div class="card-header-custom">
        <i class="bi bi-list-check text-success"></i>
        <h6><?= te('Kontrollpunkte nach Kategorie') ?></h6>
    </div>

    <?php
    $categoryIcons = [
        'Identity'       => 'person-check',
        'Data'           => 'database-lock',
        'Device'         => 'phone',
        'Apps'           => 'grid',
        'Infrastructure' => 'server',
        'Other'          => 'three-dots',
    ];
    ?>

    <?php foreach ($grouped as $category => $controls): ?>
        <?php
        $catTotal   = array_sum(array_column($controls, 'score'));
        $catMax     = array_sum(array_column($controls, 'maxScore'));
        $catPct     = $catMax > 0 ? (int)round(($catTotal / $catMax) * 100) : 0;
        $catColor   = $catPct >= 70 ? '#16a34a' : ($catPct >= 40 ? '#d97706' : '#dc2626');
        $icon       = $categoryIcons[$category] ?? 'shield';
        ?>
        <div class="px-3 pt-3">
            <div class="d-flex align-items-center gap-2 mb-2">
                <i class="bi bi-<?= $e($icon) ?> text-secondary"></i>
                <strong style="font-size: 13px;"><?= $e($category) ?></strong>
                <span class="badge-pill ms-1" style="background: <?= $catColor ?>1a; color: <?= $catColor ?>;">
                    <?= number_format($catTotal, 0) ?> / <?= number_format($catMax, 0) ?> &nbsp;(<?= $catPct ?>%)
                </span>
                <div class="progress-custom ms-auto" style="width: 120px; margin-bottom:0;">
                    <div class="bar" style="width:<?= min(100, $catPct) ?>%; background: <?= $catColor ?>;"></div>
                </div>
            </div>
        </div>

        <div class="table-responsive px-1">
            <table class="data-table" style="margin-bottom: 0;">
                <thead>
                    <tr>
                        <th><?= te('Kontrollpunkt') ?></th>
                        <th style="width:80px;"><?= te('Punkte') ?></th>
                        <th style="width:80px;"><?= te('Max') ?></th>
                        <th style="width:60px;">%</th>
                        <th style="width:160px;"><?= te('Fortschritt') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($controls as $ctrl): ?>
                        <?php
                        $ctrlColor = $ctrl['pct'] >= 70 ? '#16a34a' : ($ctrl['pct'] >= 40 ? '#d97706' : '#dc2626');
                        ?>
                        <tr>
                            <td style="font-size:12px;"><?= $e($ctrl['controlName']) ?></td>
                            <td style="font-size:12px; text-align:right;">
                                <?= number_format($ctrl['score'], 1) ?>
                            </td>
                            <td style="font-size:12px; text-align:right; color:#9ca3af;">
                                <?= number_format($ctrl['maxScore'], 1) ?>
                            </td>
                            <td style="font-size:12px; color: <?= $ctrlColor ?>; font-weight:600; text-align:right;">
                                <?= $ctrl['pct'] ?>%
                            </td>
                            <td>
                                <div class="progress-custom" style="margin-bottom:0;">
                                    <div class="bar" style="width:<?= min(100, $ctrl['pct']) ?>%; background: <?= $ctrlColor ?>;"></div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div style="height: 12px;"></div>
    <?php endforeach; ?>
</div>
<?php elseif (empty($latest)): ?>
    <!-- already shown empty state above -->
<?php else: ?>
<div class="content-card mb-4">
    <div class="card-body-custom">
        <div class="empty-state">
            <i class="bi bi-info-circle text-muted" style="font-size:2rem;"></i>
            <p class="mt-2 text-muted small"><?= te('Keine Kontrollpunkte verfügbar. Berechtigung') ?>
               <code>IdentityRiskyUser.Read.All</code> <?= te('oder erweiterte Security-Rollen prüfen.') ?></p>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if (!empty($history)): ?>
<script>
(function () {
    const labels  = <?= json_encode(array_column($history, 'date')) ?>;
    const scores  = <?= json_encode(array_column($history, 'currentScore')) ?>;
    const maxScores = <?= json_encode(array_column($history, 'maxScore')) ?>;

    const ctx = document.getElementById('scoreChart');
    if (!ctx) return;

    new Chart(ctx, {
        type: 'line',
        data: {
            labels,
            datasets: [
                {
                    label: <?= json_encode(t('Aktueller Score'), JSON_UNESCAPED_UNICODE) ?>,
                    data: scores,
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59,130,246,0.1)',
                    borderWidth: 2,
                    pointRadius: 3,
                    tension: 0.3,
                    fill: true,
                },
                {
                    label: <?= json_encode(t('Max. Score'), JSON_UNESCAPED_UNICODE) ?>,
                    data: maxScores,
                    borderColor: '#d1d5db',
                    borderWidth: 1.5,
                    borderDash: [4, 4],
                    pointRadius: 0,
                    tension: 0.3,
                    fill: false,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom', labels: { font: { size: 11 } } },
                tooltip: { mode: 'index', intersect: false },
            },
            scales: {
                x: { ticks: { font: { size: 10 }, maxRotation: 45 } },
                y: { beginAtZero: false, ticks: { font: { size: 11 } } },
            },
        },
    });
})();
</script>
<?php endif; ?>
