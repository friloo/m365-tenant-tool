<?php use App\Core\View; use App\Modules\Groups\GroupsService; $e = fn($v) => View::escape($v); ?>

<div class="row g-3 mb-4">
    <div class="col-sm-4">
        <div class="metric-card">
            <div class="metric-label">Gruppen gesamt</div>
            <div class="metric-value"><?= number_format(count($groups)) ?></div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="metric-card">
            <div class="metric-label">M365-Gruppen</div>
            <div class="metric-value"><?= count(array_filter($groups, fn($g) => in_array('Unified', $g['groupTypes'] ?? []))) ?></div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="metric-card">
            <div class="metric-label">Sicherheitsgruppen</div>
            <div class="metric-value"><?= count(array_filter($groups, fn($g) => ($g['securityEnabled'] ?? false) && !in_array('Unified', $g['groupTypes'] ?? []))) ?></div>
        </div>
    </div>
</div>

<div class="content-card">
    <div class="table-toolbar">
        <input type="text" id="grpSearch" class="search-box" placeholder="Gruppe suchen…">
    </div>
    <div class="table-responsive">
        <table class="data-table" id="grpTable">
            <thead>
                <tr><th>Name</th><th>Typ</th><th>E-Mail</th><th>Erstellt</th><th></th></tr>
            </thead>
            <tbody>
                <?php foreach ($groups as $g): ?>
                    <?php $type = GroupsService::getType($g); ?>
                    <tr>
                        <td class="fw-medium"><?= $e($g['displayName'] ?? '') ?></td>
                        <td>
                            <?php if ($type === 'M365'): ?>
                                <span class="badge-info">M365</span>
                            <?php elseif ($type === 'Security'): ?>
                                <span class="badge-neutral">Security</span>
                            <?php else: ?>
                                <span class="badge-warning"><?= $e($type) ?></span>
                            <?php endif; ?>
                        </td>
                        <td style="font-size:12px;color:#6b7280;"><?= $e($g['mail'] ?? '') ?></td>
                        <td style="font-size:12px;color:#6b7280;">
                            <?= isset($g['createdDateTime']) ? date('d.m.Y', strtotime($g['createdDateTime'])) : '–' ?>
                        </td>
                        <td>
                            <a href="/groups/<?= $e($g['id']) ?>" class="btn btn-sm btn-link py-0" style="font-size:12px;">Detail</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<script>initTableSearch('grpSearch', 'grpTable');</script>
