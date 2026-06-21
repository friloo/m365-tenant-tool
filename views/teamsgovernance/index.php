<?php use App\Core\View; $e = fn($v) => View::escape($v); ?>
<?php \App\Core\View::partial('partials/module_tabs', ['tabs' => [['label'=>t('Übersicht'),'href'=>'/teamspolicies','icon'=>'collection'],['label'=>t('Nutzung'),'href'=>'/teamsusage','icon'=>'camera-video'],['label'=>t('Governance'),'href'=>'/teamsgovernance','icon'=>'people-fill'],]]); ?>


<div class="row g-3 mb-4">
    <div class="col-sm-3">
        <div class="metric-card">
            <div class="metric-label"><?= te('Teams gesamt') ?></div>
            <div class="metric-value"><?= $summary['total'] ?></div>
            <div class="metric-sub">M365 Teams</div>
        </div>
    </div>
    <div class="col-sm-3">
        <div class="metric-card">
            <div class="metric-label"><?= te('Ohne Besitzer') ?></div>
            <div class="metric-value" style="color:<?= $summary['ownerless'] > 0 ? '#dc2626' : '#16a34a' ?>;">
                <?= $summary['ownerless'] ?>
            </div>
            <div class="metric-sub"><?= te('Kein Owner zugewiesen') ?></div>
        </div>
    </div>
    <div class="col-sm-3">
        <div class="metric-card">
            <div class="metric-label"><?= te('Öffentlich') ?></div>
            <div class="metric-value" style="color:<?= $summary['public'] > 0 ? '#d97706' : '#16a34a' ?>;">
                <?= $summary['public'] ?>
            </div>
            <div class="metric-sub"><?= te('Sichtbarkeit: Public') ?></div>
        </div>
    </div>
    <div class="col-sm-3">
        <div class="metric-card">
            <div class="metric-label"><?= te('Älter als :n Tage', ['n' => (int)$days]) ?></div>
            <div class="metric-value"><?= $summary['oldTeams'] ?></div>
            <div class="metric-sub"><?= te('Überprüfung empfohlen') ?></div>
        </div>
    </div>
</div>

<div class="d-flex align-items-center gap-2 mb-4">
    <label style="font-size:13px;font-weight:500;white-space:nowrap;"><?= te('Teams älter als:') ?></label>
    <form method="GET" action="/teamsgovernance" class="d-flex align-items-center gap-2">
        <select name="days" class="form-select form-select-sm" style="width:auto;" onchange="this.form.submit()">
            <?php foreach ([30, 60, 90, 180, 365] as $d): ?>
                <option value="<?= $d ?>" <?= $d === (int)$days ? 'selected' : '' ?>><?= te(':n Tage', ['n' => $d]) ?></option>
            <?php endforeach; ?>
        </select>
    </form>
</div>

<div class="content-card">
    <div class="table-toolbar">
        <input type="text" id="tgSearch" class="search-box" placeholder="<?= te('Team suchen…') ?>">
    </div>
    <div class="table-responsive">
        <table class="data-table" id="tgTable">
            <thead>
                <tr>
                    <th><?= te('Name') ?></th>
                    <th><?= te('Erstellt') ?></th>
                    <th><?= te('Alter') ?></th>
                    <th><?= te('Sichtbarkeit') ?></th>
                    <th><?= te('Besitzer') ?></th>
                    <th><?= te('Aktionen') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($teams as $t):
                    $age       = $t['ageInDays'] ?? 0;
                    $isPublic  = $t['isPublic'] ?? false;
                    $hasOwners = $t['hasOwners'];
                    $created   = $t['createdDateTime'] ?? null;
                    $teamId    = $t['id'] ?? '';
                ?>
                <tr>
                    <td>
                        <div class="fw-medium" style="font-size:13px;"><?= $e($t['displayName'] ?? '') ?></div>
                        <?php if (!empty($t['description'])): ?>
                            <div style="font-size:11px;color:#9ca3af;max-width:280px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                <?= $e($t['description']) ?>
                            </div>
                        <?php endif; ?>
                    </td>
                    <td style="font-size:12px;color:#6b7280;white-space:nowrap;">
                        <?= $created ? date('d.m.Y', strtotime($created)) : '–' ?>
                    </td>
                    <td>
                        <?php if ($age > 365): ?>
                            <span class="badge-warning"><?= te(':n Tage', ['n' => $age]) ?></span>
                        <?php else: ?>
                            <span style="font-size:12px;color:#6b7280;"><?= te(':n Tage', ['n' => $age]) ?></span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($isPublic): ?>
                            <span class="badge-warning"><?= te('Öffentlich') ?></span>
                        <?php else: ?>
                            <span class="badge-neutral"><?= te('Privat') ?></span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($hasOwners === false): ?>
                            <span class="badge-disabled"><?= te('Ohne Besitzer') ?></span>
                        <?php elseif ($hasOwners === true): ?>
                            <span class="badge-ok">OK</span>
                        <?php else: ?>
                            <span class="badge-neutral">?</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="https://teams.microsoft.com" target="_blank" rel="noopener noreferrer"
                           class="btn btn-sm btn-outline-primary" style="font-size:11px;padding:2px 8px;">
                            <i class="bi bi-box-arrow-up-right me-1"></i><?= te('In Teams öffnen') ?>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($teams)): ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4"><?= te('Keine Teams gefunden') ?></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
initTableSearch('tgSearch', 'tgTable');
</script>
