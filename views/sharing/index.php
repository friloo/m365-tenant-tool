<?php use App\Core\View; $e = fn($v) => View::escape($v);
$items = $summary['items'] ?? [];
$byType = $summary['byType'] ?? [];
?>

<?php if (!empty($flash)): ?>
    <div class="alert alert-success alert-dismissible mb-3"><i class="bi bi-check-circle me-2"></i><?= $e($flash) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>
<?php if (!empty($error)): ?>
    <div class="alert alert-danger alert-dismissible mb-3"><i class="bi bi-exclamation-triangle me-2"></i><?= $e($error) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<div class="row g-3 mb-4">
    <div class="col-sm-3">
        <div class="metric-card">
            <div class="metric-label">Externe Freigaben</div>
            <div class="metric-value"><?= number_format($summary['total'] ?? 0) ?></div>
        </div>
    </div>
    <div class="col-sm-3">
        <div class="metric-card">
            <div class="metric-label">Anonym (Anyone)</div>
            <div class="metric-value" style="color:<?= ($byType['anonymous']??0) > 0 ? '#dc2626':'#111827' ?>">
                <?= $byType['anonymous'] ?? 0 ?>
            </div>
        </div>
    </div>
    <div class="col-sm-3">
        <div class="metric-card">
            <div class="metric-label">Externe Benutzer</div>
            <div class="metric-value"><?= $byType['users'] ?? 0 ?></div>
        </div>
    </div>
    <div class="col-sm-3">
        <div class="metric-card">
            <div class="metric-label">Organisation</div>
            <div class="metric-value"><?= $byType['organization'] ?? 0 ?></div>
        </div>
    </div>
</div>

<?php if (($byType['anonymous'] ?? 0) > 0): ?>
    <div class="alert alert-warning mb-4">
        <i class="bi bi-exclamation-triangle me-2"></i>
        <strong><?= $byType['anonymous'] ?> anonyme Freigaben</strong> — Dateien mit "Anyone"-Links sind für jeden ohne Anmeldung zugänglich.
    </div>
<?php endif; ?>

<div class="content-card">
    <div class="table-toolbar">
        <input type="text" id="sharingSearch" class="search-box" placeholder="Freigaben suchen…">
        <select id="scopeFilter" class="form-select form-select-sm ms-2" style="max-width:160px;" onchange="filterSharing()">
            <option value="">Alle Typen</option>
            <option value="anonymous">Anonym</option>
            <option value="users">Externe Benutzer</option>
            <option value="organization">Organisation</option>
        </select>
        <a href="/sharing/export" class="btn btn-sm btn-outline-secondary ms-auto">
            <i class="bi bi-download me-1"></i> CSV
        </a>
    </div>
    <div class="table-responsive">
        <table class="data-table" id="sharingTable">
            <thead>
                <tr><th>Typ</th><th>Name</th><th>Quelle</th><th>Freigabe-Typ</th><th>Besitzer</th><th>Geändert</th></tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                    <tr data-scope="<?= $e($item['scope']) ?>">
                        <td><span class="badge-info"><?= $e($item['type']) ?></span></td>
                        <td class="fw-medium">
                            <?php if (!empty($item['url'])): ?>
                                <a href="<?= $e($item['url']) ?>" target="_blank" class="text-decoration-none text-dark"><?= $e($item['name']) ?> <i class="bi bi-box-arrow-up-right" style="font-size:10px;"></i></a>
                            <?php else: ?>
                                <?= $e($item['name']) ?>
                            <?php endif; ?>
                        </td>
                        <td style="font-size:12px;color:#6b7280;"><?= $e($item['site'] ?? '') ?></td>
                        <td>
                            <?php $scope = $item['scope'] ?? 'unknown'; ?>
                            <?php if ($scope === 'anonymous'): ?>
                                <span class="badge-disabled">Anonym</span>
                            <?php elseif ($scope === 'users'): ?>
                                <span class="badge-warning">Externe User</span>
                            <?php elseif ($scope === 'organization'): ?>
                                <span class="badge-info">Organisation</span>
                            <?php else: ?>
                                <span class="badge-neutral"><?= $e($scope) ?></span>
                            <?php endif; ?>
                        </td>
                        <td style="font-size:12px;"><?= $e($item['owner'] ?? '') ?></td>
                        <td style="font-size:12px;color:#6b7280;">
                            <?= !empty($item['modified']) ? date('d.m.Y', strtotime($item['modified'])) : '–' ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($items)): ?>
                    <tr><td colspan="6" class="text-center text-muted py-4">Keine externen Freigaben gefunden</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
initTableSearch('sharingSearch', 'sharingTable');
function filterSharing() {
    const val = document.getElementById('scopeFilter').value;
    document.querySelectorAll('#sharingTable tbody tr').forEach(r => {
        r.style.display = (!val || r.dataset.scope === val) ? '' : 'none';
    });
}
</script>
