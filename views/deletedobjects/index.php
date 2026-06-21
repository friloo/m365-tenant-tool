<?php use App\Core\View; $e = fn($v) => View::escape($v); ?>

<?php if (!empty($flash)): ?>
    <div class="alert alert-success alert-dismissible mb-3">
        <i class="bi bi-check-circle me-2"></i><?= $e($flash) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>
<?php if (!empty($error)): ?>
    <div class="alert alert-danger alert-dismissible mb-3">
        <i class="bi bi-exclamation-triangle me-2"></i><?= $e($error) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="alert alert-warning d-flex align-items-start gap-2 mb-4">
    <i class="bi bi-exclamation-triangle-fill flex-shrink-0 mt-1"></i>
    <div>
        <?= t('Gelöschte Objekte werden nach <strong>30 Tagen</strong> automatisch und unwiderruflich gelöscht. Objekte mit weniger als 7 verbleibenden Tagen sind rot markiert.') ?>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="metric-card">
            <div class="metric-label"><i class="bi bi-person-x me-1"></i><?= te('Gelöschte Benutzer') ?></div>
            <div class="metric-value"><?= count($users) ?></div>
            <div class="metric-sub"><?= te('im Papierkorb') ?></div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="metric-card">
            <div class="metric-label"><i class="bi bi-collection me-1"></i><?= te('Gelöschte Gruppen') ?></div>
            <div class="metric-value"><?= count($groups) ?></div>
            <div class="metric-sub"><?= te('im Papierkorb') ?></div>
        </div>
    </div>
</div>

<div class="content-card">
    <ul class="nav nav-tabs px-3 pt-3" id="deletedTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="users-tab" data-bs-toggle="tab"
                    data-bs-target="#users-panel" type="button" role="tab">
                <i class="bi bi-person-x me-1"></i><?= te('Benutzer') ?>
                <span class="badge bg-secondary ms-1"><?= count($users) ?></span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="groups-tab" data-bs-toggle="tab"
                    data-bs-target="#groups-panel" type="button" role="tab">
                <i class="bi bi-collection me-1"></i><?= te('Gruppen') ?>
                <span class="badge bg-secondary ms-1"><?= count($groups) ?></span>
            </button>
        </li>
    </ul>

    <div class="tab-content" id="deletedTabContent">

        <!-- Users tab -->
        <div class="tab-pane fade show active" id="users-panel" role="tabpanel">
            <div class="table-toolbar">
                <input type="text" id="usersSearch" class="search-box" placeholder="<?= te('Benutzer suchen…') ?>">
            </div>
            <div class="table-responsive">
                <table class="data-table" id="usersTable">
                    <thead>
                        <tr>
                            <th><?= te('Name') ?></th>
                            <th><?= te('UPN') ?></th>
                            <th><?= te('Abteilung') ?></th>
                            <th><?= te('Gelöscht am') ?></th>
                            <th><?= te('Verbleibend') ?></th>
                            <th><?= te('Aktionen') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $u):
                            $daysRemaining = !empty($u['deletedDateTime'])
                                ? max(0, 30 - (int)floor((time() - strtotime($u['deletedDateTime'])) / 86400))
                                : 30;
                            $isUrgent = $daysRemaining < 7;
                        ?>
                        <tr>
                            <td class="fw-medium"><?= $e($u['displayName'] ?? '') ?></td>
                            <td style="font-size:12px;color:#6b7280;"><?= $e($u['userPrincipalName'] ?? '') ?></td>
                            <td style="font-size:12px;"><?= $e($u['department'] ?? '') ?></td>
                            <td style="font-size:12px;">
                                <?= !empty($u['deletedDateTime']) ? date('d.m.Y', strtotime($u['deletedDateTime'])) : '–' ?>
                            </td>
                            <td>
                                <?php if ($isUrgent): ?>
                                    <span class="badge-disabled"><?= $daysRemaining ?>d</span>
                                <?php else: ?>
                                    <span class="badge-neutral"><?= $daysRemaining ?>d</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <form method="post" action="/deletedobjects/<?= $e($u['id']) ?>/restore" class="mb-0">
                                        <?= \App\Core\Csrf::field() ?>
                                        <button type="submit" class="btn btn-sm btn-outline-success py-0 px-2"
                                                style="font-size:11px;" title="<?= te('Wiederherstellen') ?>">
                                            <i class="bi bi-arrow-counterclockwise me-1"></i><?= te('Wiederherstellen') ?>
                                        </button>
                                    </form>
                                    <?php if (\App\Auth\LocalAuth::role() === 'admin'): ?>
                                    <form method="post" action="/deletedobjects/<?= $e($u['id']) ?>/permanent-delete" class="mb-0"
                                          onsubmit="return confirm(<?= htmlspecialchars(json_encode(t('Benutzer endgültig löschen? Diese Aktion kann nicht rückgängig gemacht werden.'), JSON_UNESCAPED_UNICODE), ENT_QUOTES) ?>)">
                                        <?= \App\Core\Csrf::field() ?>
                                        <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2"
                                                style="font-size:11px;" title="<?= te('Endgültig löschen') ?>">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($users)): ?>
                            <tr><td colspan="6" class="text-center text-muted py-4"><?= te('Keine gelöschten Benutzer gefunden') ?></td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Groups tab -->
        <div class="tab-pane fade" id="groups-panel" role="tabpanel">
            <div class="table-toolbar">
                <input type="text" id="groupsSearch" class="search-box" placeholder="<?= te('Gruppe suchen…') ?>">
            </div>
            <div class="table-responsive">
                <table class="data-table" id="groupsTable">
                    <thead>
                        <tr>
                            <th><?= te('Name') ?></th>
                            <th><?= te('E-Mail') ?></th>
                            <th><?= te('Typ') ?></th>
                            <th><?= te('Gelöscht am') ?></th>
                            <th><?= te('Verbleibend') ?></th>
                            <th><?= te('Aktionen') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($groups as $g):
                            $daysRemaining = !empty($g['deletedDateTime'])
                                ? max(0, 30 - (int)floor((time() - strtotime($g['deletedDateTime'])) / 86400))
                                : 30;
                            $isUrgent   = $daysRemaining < 7;
                            $groupTypes = $g['groupTypes'] ?? [];
                            $isM365     = in_array('Unified', $groupTypes, true);
                            $typeLabel  = $isM365 ? 'M365' : t('Sicherheitsgruppe');
                            $typeBadge  = $isM365 ? 'badge-info' : 'badge-neutral';
                        ?>
                        <tr>
                            <td class="fw-medium"><?= $e($g['displayName'] ?? '') ?></td>
                            <td style="font-size:12px;color:#6b7280;"><?= $e($g['mail'] ?? '') ?></td>
                            <td><span class="<?= $typeBadge ?>"><?= $e($typeLabel) ?></span></td>
                            <td style="font-size:12px;">
                                <?= !empty($g['deletedDateTime']) ? date('d.m.Y', strtotime($g['deletedDateTime'])) : '–' ?>
                            </td>
                            <td>
                                <?php if ($isUrgent): ?>
                                    <span class="badge-disabled"><?= $daysRemaining ?>d</span>
                                <?php else: ?>
                                    <span class="badge-neutral"><?= $daysRemaining ?>d</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <form method="post" action="/deletedobjects/<?= $e($g['id']) ?>/restore" class="mb-0">
                                        <?= \App\Core\Csrf::field() ?>
                                        <button type="submit" class="btn btn-sm btn-outline-success py-0 px-2"
                                                style="font-size:11px;" title="<?= te('Wiederherstellen') ?>">
                                            <i class="bi bi-arrow-counterclockwise me-1"></i><?= te('Wiederherstellen') ?>
                                        </button>
                                    </form>
                                    <?php if (\App\Auth\LocalAuth::role() === 'admin'): ?>
                                    <form method="post" action="/deletedobjects/<?= $e($g['id']) ?>/permanent-delete" class="mb-0"
                                          onsubmit="return confirm(<?= htmlspecialchars(json_encode(t('Gruppe endgültig löschen? Diese Aktion kann nicht rückgängig gemacht werden.'), JSON_UNESCAPED_UNICODE), ENT_QUOTES) ?>)">
                                        <?= \App\Core\Csrf::field() ?>
                                        <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2"
                                                style="font-size:11px;" title="<?= te('Endgültig löschen') ?>">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($groups)): ?>
                            <tr><td colspan="6" class="text-center text-muted py-4"><?= te('Keine gelöschten Gruppen gefunden') ?></td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<script>
initTableSearch('usersSearch', 'usersTable');
initTableSearch('groupsSearch', 'groupsTable');
</script>
