<?php use App\Core\View; $e = fn($v) => View::escape($v); ?>

<?php if (!empty($diag)) include BASE_PATH . '/views/partials/graph_diagnostic.php'; ?>

<div class="alert alert-info d-flex gap-3 mb-3">
    <i class="bi bi-bullseye flex-shrink-0 mt-1" style="font-size:1.4rem;color:#0078d4;"></i>
    <div>
        <strong>Phishing-Simulationen aus Microsoft Defender Attack Simulation Training.</strong>
        Voraussetzung: <code>Microsoft Defender for Office 365 Plan 2</code> (in E5 / M365 E5
        enthalten). Im <a href="/manual#phishing-anleitung">Handbuch</a> findest du eine ausführliche
        Anleitung zum Aufsetzen einer Simulation.
    </div>
</div>

<div class="content-card mb-4">
    <div class="card-header-custom">
        <i class="bi bi-list-stars text-primary"></i>
        <h6>Durchgeführte Simulationen</h6>
        <span class="ms-auto text-muted small"><?= count($sims) ?></span>
    </div>
    <div class="card-body-custom p-0">
        <?php if (empty($sims)): ?>
            <div class="text-muted small p-4 text-center">
                Keine Phishing-Simulationen gefunden. Im
                <a href="https://security.microsoft.com/attacksimulator" target="_blank" rel="noopener">Defender-Portal</a>
                eine Simulation anlegen — Schritt-für-Schritt-Anleitung im Handbuch unter
                <a href="/manual#phishing-anleitung">Phishing-Simulationen mit Microsoft</a>.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead><tr>
                        <th>Name</th>
                        <th>Status</th>
                        <th>Empfänger</th>
                        <th>Klicks</th>
                        <th>Kompromittiert</th>
                        <th>Gemeldet</th>
                        <th>Training</th>
                        <th>Gestartet</th>
                    </tr></thead>
                    <tbody>
                    <?php foreach ($sims as $s):
                        $st = $stats[$s['id']] ?? null;
                        $recip = $st['recipientsCount'] ?? 0;
                        $compromisedPct = $recip > 0 && $st ? round($st['compromisedCount'] / $recip * 100) : null;
                        $reportPct      = $recip > 0 && $st ? round($st['reportedPhishCount'] / $recip * 100) : null;
                    ?>
                        <tr>
                            <td>
                                <div class="fw-medium"><?= $e($s['displayName']) ?></div>
                                <div class="text-muted small"><?= $e($s['attackType']) ?> · <?= $e($s['attackTechnique']) ?></div>
                            </td>
                            <td>
                                <?php $cls = match (strtolower($s['status'])) {
                                    'succeeded', 'completed' => 'bg-success',
                                    'running', 'inprogress'  => 'bg-info text-dark',
                                    'failed'                 => 'bg-danger',
                                    default                  => 'bg-secondary',
                                }; ?>
                                <span class="badge <?= $cls ?>"><?= $e($s['status']) ?></span>
                            </td>
                            <td><?= $st ? number_format($recip) : '–' ?></td>
                            <td>
                                <?php if ($st && $recip > 0): ?>
                                    <?= number_format($st['clickCount']) ?>
                                    <span class="text-muted small">(<?= round($st['clickCount'] / $recip * 100) ?>%)</span>
                                <?php else: ?>–<?php endif; ?>
                            </td>
                            <td>
                                <?php if ($compromisedPct !== null): ?>
                                    <span class="badge <?= $compromisedPct > 20 ? 'bg-danger' : ($compromisedPct > 10 ? 'bg-warning text-dark' : 'bg-success') ?>">
                                        <?= number_format($st['compromisedCount']) ?> · <?= $compromisedPct ?>%
                                    </span>
                                <?php else: ?>–<?php endif; ?>
                            </td>
                            <td>
                                <?php if ($reportPct !== null): ?>
                                    <span class="badge <?= $reportPct >= 50 ? 'bg-success' : 'bg-warning text-dark' ?>">
                                        <?= number_format($st['reportedPhishCount']) ?> · <?= $reportPct ?>%
                                    </span>
                                <?php else: ?>–<?php endif; ?>
                            </td>
                            <td>
                                <?php if ($st && ($st['trainingsAssigned'] ?? 0) > 0): ?>
                                    <span class="text-muted small">
                                        <?= number_format($st['trainingsCompleted']) ?>/<?= number_format($st['trainingsAssigned']) ?>
                                    </span>
                                <?php else: ?>–<?php endif; ?>
                            </td>
                            <td class="text-muted small">
                                <?= $s['launchDateTime'] ? $e(date('d.m.Y', strtotime($s['launchDateTime']))) : '–' ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="d-flex gap-2 flex-wrap">
    <a href="https://security.microsoft.com/attacksimulator" target="_blank" rel="noopener" class="btn btn-outline-primary">
        <i class="bi bi-box-arrow-up-right me-1"></i>Defender Attack Simulator öffnen
    </a>
    <a href="/manual#phishing-anleitung" class="btn btn-outline-secondary">
        <i class="bi bi-book me-1"></i>Anleitung im Handbuch
    </a>
</div>
