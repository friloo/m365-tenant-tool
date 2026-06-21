<?php use App\Core\View; $e = fn($v) => View::escape($v); ?>

<p class="text-muted mb-4" style="font-size:14px;">
    <?= te('Microsoft 365 Gruppen ohne Aktivität in den letzten :n Tagen', ['n' => (int)$days]) ?>
</p>

<!-- Days filter + Export -->
<div class="d-flex align-items-center gap-2 mb-4 flex-wrap">
    <label for="daysSelect" style="font-size:13px;font-weight:500;white-space:nowrap;"><?= te('Inaktiv seit mehr als:') ?></label>
    <form method="GET" action="/groups/inactive" class="d-flex align-items-center gap-2">
        <select name="days" id="daysSelect" class="form-select form-select-sm" style="width:auto;"
                onchange="this.form.submit()">
            <?php foreach ([7, 14, 30, 60, 90] as $d): ?>
                <option value="<?= $d ?>" <?= $d === $days ? 'selected' : '' ?>><?= te(':n Tage', ['n' => $d]) ?></option>
            <?php endforeach; ?>
        </select>
    </form>
    <a href="/groups/inactive/export?days=<?= (int)$days ?>" class="btn btn-sm btn-outline-secondary ms-auto">
        <i class="bi bi-download me-1"></i><?= te('CSV Export') ?>
    </a>
    <a href="/groups/inactive?refresh=1&days=<?= (int)$days ?>" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-clockwise me-1"></i><?= te('Aktualisieren') ?>
    </a>
</div>

<?php
$totalCount   = count($groups);
$neverActive  = count(array_filter($groups, fn($r) => $r['last_activity'] === null));
$realDays     = array_filter($groups, fn($r) => $r['days_inactive'] < 9999);
$avgInactive  = count($realDays) > 0
    ? (int)round(array_sum(array_column(array_values($realDays), 'days_inactive')) / count($realDays))
    : 0;
$externalSum  = array_sum(array_column($groups, 'external_count'));
?>

<!-- Warning alert -->
<?php if ($totalCount > 10): ?>
<div class="alert alert-warning mb-4">
    <i class="bi bi-exclamation-triangle me-2"></i>
    <strong><?= te('Viele inaktive Gruppen') ?></strong> — <?= te('Viele inaktive Gruppen können auf ungenutzte Ressourcen und potenzielle Sicherheitsrisiken hinweisen. Erwägen Sie eine Bereinigung.') ?>
</div>
<?php endif; ?>

<!-- Metric cards -->
<div class="row g-3 mb-4">
    <div class="col-sm-3">
        <div class="metric-card">
            <div class="metric-label"><?= te('Inaktive Gruppen gesamt') ?></div>
            <div class="metric-value" style="color:<?= $totalCount > 0 ? '#d97706' : '#111827' ?>;">
                <?= $totalCount ?>
            </div>
            <div class="metric-sub"><?= te('Seit >:n Tagen', ['n' => (int)$days]) ?></div>
        </div>
    </div>
    <div class="col-sm-3">
        <div class="metric-card">
            <div class="metric-label"><?= te('Nie aktiv') ?></div>
            <div class="metric-value" style="color:<?= $neverActive > 0 ? '#dc2626' : '#16a34a' ?>;">
                <?= $neverActive ?>
            </div>
            <div class="metric-sub"><?= te('Kein Aktivitätsdatum') ?></div>
        </div>
    </div>
    <div class="col-sm-3">
        <div class="metric-card">
            <div class="metric-label"><?= te('Durchschn. Inaktivitätsdauer') ?></div>
            <div class="metric-value" style="color:#6b7280;">
                <?= $avgInactive ?>
            </div>
            <div class="metric-sub"><?= te('Tage (ohne „Nie aktiv")') ?></div>
        </div>
    </div>
    <div class="col-sm-3">
        <div class="metric-card">
            <div class="metric-label"><?= te('Externe Mitglieder') ?></div>
            <div class="metric-value" style="color:<?= $externalSum > 0 ? '#d97706' : '#111827' ?>;">
                <?= $externalSum ?>
            </div>
            <div class="metric-sub"><?= te('In inaktiven Gruppen') ?></div>
        </div>
    </div>
</div>

<!-- Table -->
<div class="content-card">
    <div class="table-toolbar">
        <input type="text" id="groupSearch" class="search-box" placeholder="<?= te('Gruppe suchen…') ?>">
    </div>
    <div class="table-responsive">
        <table class="data-table" id="groupsTable">
            <thead>
                <tr>
                    <th><?= te('Gruppe') ?></th>
                    <th><?= te('Besitzer') ?></th>
                    <th><?= te('Letzte Aktivität') ?></th>
                    <th><?= te('Inaktiv seit') ?></th>
                    <th><?= te('Mitglieder') ?></th>
                    <th><?= te('Aktionen') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($groups as $row): ?>
                <?php
                    $di = $row['days_inactive'];
                    $diLabel = $di >= 9999 ? '–' : t(':n Tage', ['n' => $di]);
                    $diColor = $di >= 9999 ? 'muted'
                        : ($di > 90  ? 'danger'
                        : ($di >= 60 ? 'warning'
                        : 'muted'));
                ?>
                <tr>
                    <td>
                        <div style="font-size:13px;font-weight:500;"><?= $e($row['group_name']) ?></div>
                        <?php if ($row['group_id']): ?>
                            <div style="font-size:11px;color:#9ca3af;font-family:monospace;"><?= $e($row['group_id']) ?></div>
                        <?php endif; ?>
                    </td>
                    <td style="font-size:12px;color:#6b7280;"><?= $e($row['owner'] ?: '–') ?></td>
                    <td>
                        <?php if ($row['last_activity'] === null): ?>
                            <span class="badge-danger"><?= te('Nie aktiv') ?></span>
                        <?php else: ?>
                            <span style="font-size:12px;color:#6b7280;">
                                <?= $e($row['last_activity']->format('d.m.Y')) ?>
                            </span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($di >= 9999): ?>
                            <span class="text-muted" style="font-size:12px;">–</span>
                        <?php elseif ($diColor === 'danger'): ?>
                            <span class="badge-danger"><?= $diLabel ?></span>
                        <?php elseif ($diColor === 'warning'): ?>
                            <span class="badge-warning"><?= $diLabel ?></span>
                        <?php else: ?>
                            <span class="text-muted" style="font-size:12px;"><?= $diLabel ?></span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?= (int)$row['member_count'] ?>
                        <?php if ($row['external_count'] > 0): ?>
                            <span class="badge-warning ms-1"><?= te('+:n extern', ['n' => (int)$row['external_count']]) ?></span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($row['group_id']): ?>
                            <a href="/groups/<?= $e($row['group_id']) ?>" class="btn btn-sm btn-outline-primary" style="font-size:11px;padding:2px 8px;">
                                <i class="bi bi-eye me-1"></i><?= te('Details') ?>
                            </a>
                        <?php else: ?>
                            <span class="text-muted" style="font-size:11px;">–</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($groups)): ?>
                <tr>
                    <td colspan="6">
                        <div class="empty-state">
                            <i class="bi bi-check-circle"></i>
                            <p><?= te('Keine inaktiven Gruppen gefunden — alle Gruppen waren in den letzten :n Tagen aktiv.', ['n' => (int)$days]) ?></p>
                        </div>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
initTableSearch('groupSearch', 'groupsTable');
</script>
