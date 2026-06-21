<?php use App\Core\View; $e = fn($v) => View::escape($v); ?>

<!-- Date filter -->
<form method="get" class="d-flex align-items-center gap-2 mb-4 flex-wrap">
    <input type="hidden" name="tab" value="<?= $e($tab) ?>">
    <label class="fw-medium small"><?= te('Von') ?></label>
    <input type="date" name="from" class="form-control form-control-sm" style="max-width:160px;" value="<?= $e($from) ?>">
    <label class="fw-medium small"><?= te('Bis') ?></label>
    <input type="date" name="to" class="form-control form-control-sm" style="max-width:160px;" value="<?= $e($to) ?>">
    <button type="submit" class="btn btn-primary btn-sm">
        <i class="bi bi-search me-1"></i> <?= te('Laden') ?>
    </button>
    <a href="?from=<?= $e($from) ?>&to=<?= $e($to) ?>&tab=<?= $e($tab) ?>&export=1"
       class="btn btn-outline-secondary btn-sm ms-auto">
        <i class="bi bi-download me-1"></i> CSV
    </a>
</form>

<!-- Tabs -->
<ul class="nav nav-tabs mb-4">
    <li class="nav-item">
        <a class="nav-link <?= $tab === 'directory' ? 'active' : '' ?>"
           href="?from=<?= $e($from) ?>&to=<?= $e($to) ?>&tab=directory">
            <i class="bi bi-folder me-1"></i> <?= te('Verzeichnis-Audit') ?>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= $tab === 'signins' ? 'active' : '' ?>"
           href="?from=<?= $e($from) ?>&to=<?= $e($to) ?>&tab=signins">
            <i class="bi bi-box-arrow-in-right me-1"></i> <?= te('Anmeldungen') ?>
        </a>
    </li>
</ul>

<?php if ($tab === 'directory'): ?>
    <div class="content-card">
        <div class="table-toolbar">
            <input type="text" id="auditSearch" class="search-box" placeholder="<?= te('Suchen…') ?>">
            <span class="ms-auto text-muted small"><?= count($directoryAudits) ?> <?= te('Einträge') ?></span>
        </div>
        <div class="table-responsive">
            <table class="data-table" id="auditTable">
                <thead>
                    <tr><th><?= te('Zeitpunkt') ?></th><th><?= te('Aktion') ?></th><th><?= te('Kategorie') ?></th><th><?= te('Ergebnis') ?></th><th><?= te('Initiiert von') ?></th><th><?= te('Ziel') ?></th></tr>
                </thead>
                <tbody>
                    <?php foreach ($directoryAudits as $a): ?>
                        <?php $actor = $a['initiatedBy']['user']['userPrincipalName'] ?? $a['initiatedBy']['app']['displayName'] ?? '–'; ?>
                        <tr>
                            <td style="font-size:11px;color:#6b7280;white-space:nowrap;">
                                <?= !empty($a['activityDateTime']) ? date('d.m.Y H:i', strtotime($a['activityDateTime'])) : '–' ?>
                            </td>
                            <td style="font-size:12px;font-weight:500;"><?= $e($a['activityDisplayName'] ?? '') ?></td>
                            <td><span class="badge-neutral"><?= $e($a['category'] ?? '') ?></span></td>
                            <td>
                                <?php $r = strtolower($a['result'] ?? ''); ?>
                                <?= $r === 'success' ? '<span class="badge-enabled">OK</span>' : ($r === 'failure' ? '<span class="badge-disabled">'.te('Fehler').'</span>' : '<span class="badge-neutral">'.$e($r).'</span>') ?>
                            </td>
                            <td style="font-size:12px;color:#6b7280;"><?= $e($actor) ?></td>
                            <td style="font-size:12px;">
                                <?= $e(implode(', ', array_column($a['targetResources'] ?? [], 'displayName'))) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($directoryAudits)): ?>
                        <tr><td colspan="6" class="text-center text-muted py-4"><?= te('Keine Einträge im gewählten Zeitraum') ?></td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <script>initTableSearch('auditSearch', 'auditTable');</script>

<?php else: ?>
    <div class="content-card">
        <div class="table-toolbar">
            <input type="text" id="siSearch" class="search-box" placeholder="<?= te('Suchen…') ?>">
            <span class="ms-auto text-muted small"><?= count($signIns) ?> <?= te('Einträge') ?></span>
        </div>
        <div class="table-responsive">
            <table class="data-table" id="siTable">
                <thead>
                    <tr><th>Zeitpunkt</th><th>Benutzer</th><th>App</th><th>IP</th><th>Status</th><th>Risiko</th><th>CA</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($signIns as $s): ?>
                        <?php $success = ($s['status']['errorCode'] ?? 1) === 0; ?>
                        <tr>
                            <td style="font-size:11px;color:#6b7280;white-space:nowrap;">
                                <?= !empty($s['createdDateTime']) ? date('d.m.Y H:i', strtotime($s['createdDateTime'])) : '–' ?>
                            </td>
                            <td style="font-size:12px;"><?= $e($s['userPrincipalName'] ?? '') ?></td>
                            <td style="font-size:12px;color:#6b7280;"><?= $e($s['appDisplayName'] ?? '') ?></td>
                            <td style="font-size:11px;color:#9ca3af;"><?= $e($s['ipAddress'] ?? '') ?></td>
                            <td><?= $success ? '<span class="badge-enabled">OK</span>' : '<span class="badge-disabled">Fehler</span>' ?></td>
                            <td>
                                <?php $risk = strtolower($s['riskLevelDuringSignIn'] ?? 'none'); ?>
                                <?php if ($risk !== 'none' && $risk !== ''): ?>
                                    <span class="badge-warning"><?= $e($risk) ?></span>
                                <?php else: echo '–'; endif; ?>
                            </td>
                            <td style="font-size:11px;">
                                <?php $ca = $s['conditionalAccessStatus'] ?? ''; ?>
                                <?= $ca === 'success' ? '<span class="badge-enabled">OK</span>' : ($ca ? '<span class="badge-warning">'.$e($ca).'</span>' : '–') ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($signIns)): ?>
                        <tr><td colspan="7" class="text-center text-muted py-4">Keine Einträge im gewählten Zeitraum</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <script>initTableSearch('siSearch', 'siTable');</script>
<?php endif; ?>
