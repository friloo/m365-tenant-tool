<?php use App\Core\View; $e = fn($v) => View::escape($v); ?>

<?php if (!empty($diag)) include BASE_PATH . '/views/partials/graph_diagnostic.php'; ?>

<!-- ── Summary Tiles ─────────────────────────────────────────────────── -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="metric-card">
            <div class="metric-label"><i class="bi bi-lightning-charge me-1"></i><?= te('Aktiv erhöht') ?></div>
            <div class="metric-value"><?= $e($summary['active_total']) ?></div>
            <div class="metric-sub"><?= te('Just-in-Time oder dauerhaft') ?></div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="metric-card">
            <div class="metric-label"><i class="bi bi-stopwatch me-1"></i><?= te('Eligible') ?></div>
            <div class="metric-value"><?= $e($summary['eligible_total']) ?></div>
            <div class="metric-sub"><?= te('aktivierbar, gerade ungenutzt') ?></div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="metric-card" style="border-left:4px solid <?= $summary['permanent_admins'] > 2 ? '#dc2626' : '#16a34a' ?>;">
            <div class="metric-label"><i class="bi bi-shield-exclamation me-1"></i><?= te('Dauerhafte Admins') ?></div>
            <div class="metric-value" style="color:<?= $summary['permanent_admins'] > 2 ? '#dc2626' : '#16a34a' ?>;">
                <?= $e($summary['permanent_admins']) ?>
            </div>
            <div class="metric-sub"><?= te('Empfehlung: ≤ 2') ?></div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="metric-card" style="border-left:4px solid <?= $summary['expiring_7d'] > 0 ? '#d97706' : '#9ca3af' ?>;">
            <div class="metric-label"><i class="bi bi-hourglass-bottom me-1"></i><?= te('Läuft') ?> &lt; <?= te('7 Tage') ?></div>
            <div class="metric-value"><?= $e($summary['expiring_7d']) ?></div>
            <div class="metric-sub"><?= te('aktive Zuweisungen') ?></div>
        </div>
    </div>
</div>

<!-- ── Aktive Zuweisungen ────────────────────────────────────────────── -->
<div class="content-card mb-4">
    <div class="card-header-custom">
        <i class="bi bi-lightning-charge-fill text-warning"></i>
        <h6><?= te('Aktuell aktive Privileged-Rollen') ?></h6>
    </div>
    <div class="card-body-custom p-0">
        <?php if (empty($active)): ?>
            <div class="text-muted small p-4 text-center"><?= te('Keine aktiven Privileged-Rollen-Zuweisungen.') ?></div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th><?= te('Identität') ?></th>
                            <th><?= te('Rolle') ?></th>
                            <th><?= te('Typ') ?></th>
                            <th><?= te('Aktiv seit') ?></th>
                            <th><?= te('Endet am') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($active as $a): ?>
                        <tr>
                            <td>
                                <div class="fw-medium"><?= $e($a['principalName']) ?></div>
                                <?php if ($a['principalUpn'] && $a['principalUpn'] !== $a['principalName']): ?>
                                    <div class="text-muted small"><?= $e($a['principalUpn']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td><?= $e($a['roleName']) ?></td>
                            <td>
                                <?php if ($a['assignmentType'] === 'Activated'): ?>
                                    <span class="badge bg-warning text-dark"><i class="bi bi-stopwatch me-1"></i><?= te('JIT aktiviert') ?></span>
                                <?php elseif ($a['endDateTime'] === null): ?>
                                    <span class="badge bg-danger"><i class="bi bi-infinity me-1"></i><?= te('Dauerhaft') ?></span>
                                <?php else: ?>
                                    <span class="badge bg-secondary"><?= $e($a['assignmentType']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="text-muted small">
                                <?= $a['startDateTime'] ? $e(date('d.m.Y H:i', strtotime($a['startDateTime']))) : '–' ?>
                            </td>
                            <td class="text-muted small">
                                <?php if ($a['endDateTime']): ?>
                                    <?= $e(date('d.m.Y H:i', strtotime($a['endDateTime']))) ?>
                                <?php else: ?>
                                    <span class="text-danger fw-medium"><?= te('unbegrenzt') ?></span>
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

<!-- ── Eligible Zuweisungen ──────────────────────────────────────────── -->
<div class="content-card mb-4">
    <div class="card-header-custom">
        <i class="bi bi-person-check text-info"></i>
        <h6><?= te('Eligible — verfügbar zur Aktivierung') ?></h6>
    </div>
    <div class="card-body-custom p-0">
        <?php if (empty($eligible)): ?>
            <div class="text-muted small p-4 text-center">
                <?= te('Keine Eligible-Zuweisungen — alle Admin-Rollen sind dauerhaft oder PIM ist nicht in Verwendung.') ?>
                <?php if ($summary['permanent_admins'] > 0): ?>
                    <br><span class="text-warning"><?= te('Empfehlung: dauerhafte Admins zu Eligible umstellen (BSI ORP.4.A23).') ?></span>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead><tr><th><?= te('Identität') ?></th><th><?= te('Rolle') ?></th><th><?= te('Verfügbar bis') ?></th></tr></thead>
                    <tbody>
                    <?php foreach ($eligible as $a): ?>
                        <tr>
                            <td>
                                <div class="fw-medium"><?= $e($a['principalName']) ?></div>
                                <?php if ($a['principalUpn'] && $a['principalUpn'] !== $a['principalName']): ?>
                                    <div class="text-muted small"><?= $e($a['principalUpn']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td><?= $e($a['roleName']) ?></td>
                            <td class="text-muted small">
                                <?= $a['endDateTime']
                                    ? $e(date('d.m.Y', strtotime($a['endDateTime'])))
                                    : '<span class="text-success">' . te('unbegrenzt') . '</span>' ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- ── Recent Activations (Audit) ─────────────────────────────────────── -->
<div class="content-card mb-4">
    <div class="card-header-custom">
        <i class="bi bi-clock-history text-secondary"></i>
        <h6><?= te('Aktivierungen der letzten 30 Tage') ?></h6>
    </div>
    <div class="card-body-custom p-0">
        <?php if (empty($recent)): ?>
            <div class="text-muted small p-4 text-center"><?= te('Keine PIM-Aktivierungen in den letzten 30 Tagen.') ?></div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead><tr><th><?= te('Wann') ?></th><th><?= te('Wer') ?></th><th><?= te('Rolle') ?></th><th><?= te('Ziel') ?></th><th><?= te('Resultat') ?></th></tr></thead>
                    <tbody>
                    <?php foreach (array_slice($recent, 0, 50) as $r): ?>
                        <tr>
                            <td class="text-muted small">
                                <?= $r['when'] ? $e(date('d.m.Y H:i', strtotime($r['when']))) : '–' ?>
                            </td>
                            <td><?= $e($r['who']) ?></td>
                            <td><?= $e($r['role']) ?></td>
                            <td class="text-muted small"><?= $e($r['target']) ?></td>
                            <td>
                                <span class="badge <?= $r['result'] === 'success' ? 'bg-success' : 'bg-danger' ?>">
                                    <?= $e($r['result']) ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- ── BSI / NIS-2 Best-Practice Hinweis ─────────────────────────────── -->
<div class="alert alert-info d-flex gap-3">
    <i class="bi bi-info-circle-fill flex-shrink-0 mt-1"></i>
    <div>
        <strong><?= te('Best Practice (BSI ORP.4.A23, NIS-2 Art. 21(j))') ?></strong>:
        <?= t('Privilegierte Konten sollten als <em>Eligible</em> konfiguriert werden, nicht dauerhaft zugewiesen. Bei Bedarf aktiviert sich der User für eine begrenzte Zeit (max. 8 h) mit MFA + Begründung — außerhalb dieser Zeitfenster hat er nur Standard-Berechtigungen.') ?>
        <?= te('Konfiguration:') ?> <a href="https://entra.microsoft.com/#view/Microsoft_Azure_PIMCommon/CommonMenuBlade" target="_blank" rel="noopener"><?= te('Entra → PIM') ?></a>.
    </div>
</div>
