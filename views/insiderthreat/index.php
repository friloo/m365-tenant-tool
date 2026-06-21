<?php use App\Core\View; $e = fn($v) => View::escape($v); ?>

<?php if (!empty($diag)) include BASE_PATH . '/views/partials/graph_diagnostic.php'; ?>

<div class="alert alert-info d-flex gap-3 mb-3">
    <i class="bi bi-eye-fill flex-shrink-0 mt-1" style="font-size:1.4rem;color:#0078d4;"></i>
    <div>
        <strong><?= te('Light-Insider-Threat-Detection.') ?></strong>
        <?= te('Statistische Anomalien pro User aus Sign-in- und Audit-Logs. Signale: Off-Hours-Anmeldungen, viele Länder, Mass-Downloads (≥ 50 Files/h), Mass-Mail-Send (≥ 100/h), viele Lösch-Events, viele Sharing-Events. Echtes Insider-Risk-Management (Microsoft Purview) ist umfangreicher und lizenz-pflichtig, aber diese Light-Variante deckt die häufigsten Signale ab.') ?>
    </div>
</div>

<div class="d-flex justify-content-end mb-3 flex-wrap gap-2">
    <div class="btn-group btn-group-sm" role="group">
        <?php foreach ([7, 30, 90] as $d): ?>
            <a href="?days=<?= $d ?>" class="btn <?= $days === $d ? 'btn-primary' : 'btn-outline-primary' ?>">Letzte <?= $d ?> Tage</a>
        <?php endforeach; ?>
    </div>
    <a href="?days=<?= $days ?>&refresh=1" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-clockwise me-1"></i>Neu scannen
    </a>
</div>

<div class="row g-3 mb-4">
    <div class="col-sm-6 col-lg-4">
        <div class="metric-card">
            <div class="metric-label"><i class="bi bi-people me-1"></i>User analysiert</div>
            <div class="metric-value"><?= number_format($report['total_users_analyzed']) ?></div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-4">
        <div class="metric-card" style="border-left:4px solid <?= $report['high_risk_users'] > 0 ? '#dc2626' : '#16a34a' ?>;">
            <div class="metric-label"><i class="bi bi-shield-fill-exclamation me-1"></i>High-Risk (Score ≥ 50)</div>
            <div class="metric-value" style="color:<?= $report['high_risk_users'] > 0 ? '#dc2626' : '#16a34a' ?>;">
                <?= number_format($report['high_risk_users']) ?>
            </div>
        </div>
    </div>
    <div class="col-sm-12 col-lg-4">
        <div class="metric-card">
            <div class="metric-label"><i class="bi bi-calendar-range me-1"></i>Zeitraum</div>
            <div class="metric-value" style="font-size:22px;">Letzte <?= $days ?> Tage</div>
        </div>
    </div>
</div>

<div class="content-card mb-4">
    <div class="card-header-custom">
        <i class="bi bi-list-stars text-primary"></i>
        <h6>Top-50 User nach Risk-Score</h6>
    </div>
    <div class="card-body-custom p-0">
        <?php if (empty($report['users'])): ?>
            <div class="text-muted small p-4 text-center">Keine User-Aktivität im Zeitraum.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead><tr>
                        <th>UPN</th>
                        <th class="text-end">Score</th>
                        <th class="text-end">Sign-ins</th>
                        <th class="text-end">Länder</th>
                        <th>Signale</th>
                    </tr></thead>
                    <tbody>
                    <?php foreach ($report['users'] as $u):
                        $rs = (int)$u['risk_score'];
                        $col = $rs >= 60 ? '#dc2626' : ($rs >= 30 ? '#d97706' : ($rs > 0 ? '#0284c7' : '#16a34a'));
                    ?>
                        <tr>
                            <td class="font-monospace small"><?= $e($u['upn']) ?></td>
                            <td class="text-end">
                                <span class="badge" style="background:<?= $col ?>;color:#fff;"><?= $rs ?></span>
                            </td>
                            <td class="text-end"><?= number_format($u['signin_count']) ?></td>
                            <td class="text-end"><?= number_format($u['country_count']) ?></td>
                            <td>
                                <?php if (empty($u['signals'])): ?>
                                    <span class="text-muted small">unauffällig</span>
                                <?php else: foreach ($u['signals'] as $s): ?>
                                    <span class="badge bg-warning text-dark me-1 mb-1"><?= $e($s) ?></span>
                                <?php endforeach; endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
