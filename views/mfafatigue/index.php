<?php use App\Core\View; $e = fn($v) => View::escape($v); ?>

<?php if (!empty($diag)) include BASE_PATH . '/views/partials/graph_diagnostic.php'; ?>

<div class="alert alert-warning d-flex gap-3 mb-3">
    <i class="bi bi-shield-exclamation flex-shrink-0 mt-1" style="font-size:1.4rem;color:#b45309;"></i>
    <div>
        <strong>MFA-Fatigue:</strong> ein Angreifer hat das Passwort und triggert wiederholt MFA-Pushs, bis
        der genervte User „Approve" tippt. Wir gruppieren MFA-Denials in 30-Minuten-Cluster — ≥ 5 Denials
        sind verdächtig, mit nachfolgendem Success ist es ein wahrscheinlich erfolgreicher Angriff.
    </div>
</div>

<div class="d-flex justify-content-end mb-3 flex-wrap gap-2">
    <div class="btn-group btn-group-sm" role="group">
        <?php foreach ([24, 168, 720] as $h): ?>
            <a href="?hours=<?= $h ?>" class="btn <?= $hours === $h ? 'btn-primary' : 'btn-outline-primary' ?>">
                Letzte <?= $h >= 168 ? ($h / 24) . ' Tage' : $h . ' Std.' ?>
            </a>
        <?php endforeach; ?>
    </div>
    <a href="?hours=<?= $hours ?>&refresh=1" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-clockwise me-1"></i>Neu scannen
    </a>
</div>

<div class="row g-3 mb-4">
    <div class="col-sm-4">
        <div class="metric-card">
            <div class="metric-label"><i class="bi bi-x-octagon me-1"></i>MFA-Denials gesamt</div>
            <div class="metric-value"><?= number_format($report['total_denials']) ?></div>
            <div class="metric-sub">im Zeitraum</div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="metric-card" style="border-left:4px solid <?= $report['suspicious_users'] > 0 ? '#d97706' : '#16a34a' ?>;">
            <div class="metric-label"><i class="bi bi-people me-1"></i>Verdächtige Cluster</div>
            <div class="metric-value" style="color:<?= $report['suspicious_users'] > 0 ? '#d97706' : '#16a34a' ?>;">
                <?= number_format($report['suspicious_users']) ?>
            </div>
            <div class="metric-sub">≥ 5 Denials in 30 min</div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="metric-card" style="border-left:4px solid <?= $report['successful_attacks'] > 0 ? '#dc2626' : '#16a34a' ?>;">
            <div class="metric-label"><i class="bi bi-exclamation-octagon-fill me-1"></i>Erfolgreich (Approve!)</div>
            <div class="metric-value" style="color:<?= $report['successful_attacks'] > 0 ? '#dc2626' : '#16a34a' ?>;">
                <?= number_format($report['successful_attacks']) ?>
            </div>
            <div class="metric-sub">Sofort-Reaktion nötig</div>
        </div>
    </div>
</div>

<div class="content-card mb-4">
    <div class="card-header-custom">
        <i class="bi bi-list-ul text-secondary"></i>
        <h6>Verdächtige Cluster</h6>
    </div>
    <div class="card-body-custom p-0">
        <?php if (empty($report['clusters'])): ?>
            <div class="text-muted small p-4 text-center">
                <i class="bi bi-check-circle text-success me-1"></i>
                Keine MFA-Fatigue-Cluster im Zeitraum.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>UPN</th>
                            <th>App</th>
                            <th>Erstes Denial</th>
                            <th>Letztes Denial</th>
                            <th class="text-end">Denials</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($report['clusters'] as $c): ?>
                        <tr <?= $c['success_after'] ? 'style="background:#fee2e2;"' : '' ?>>
                            <td class="font-monospace small"><?= $e($c['upn']) ?></td>
                            <td class="text-muted small"><?= $e($c['app']) ?></td>
                            <td class="text-muted small"><?= $e($c['started']) ?></td>
                            <td class="text-muted small"><?= $e($c['last_denial']) ?></td>
                            <td class="text-end fw-semibold"><?= number_format($c['denial_count']) ?></td>
                            <td>
                                <?php if ($c['success_after']): ?>
                                    <span class="badge bg-danger">
                                        <i class="bi bi-exclamation-octagon me-1"></i>
                                        Approve am <?= $e($c['success_after']) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark">
                                        <i class="bi bi-shield me-1"></i>Verdächtig
                                    </span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="alert alert-info d-flex gap-3">
    <i class="bi bi-info-circle-fill flex-shrink-0 mt-1"></i>
    <div>
        <strong>Reaktion auf einen erfolgreichen Fatigue-Angriff:</strong> Konto sperren, alle aktiven
        Sitzungen revoken (im Benutzer-Detail unter <a href="/users">Benutzer</a>), Passwort-Reset
        erzwingen, Inbox-Regeln prüfen (<a href="/mailboxrules">Auto-Forward-Audit</a>), zuletzt
        erteilte App-Consents prüfen (<a href="/oauthaudit">OAuth-App-Audit</a>). Langfristig: zu
        Number-Matching umstellen oder FIDO2 erzwingen.
    </div>
</div>
