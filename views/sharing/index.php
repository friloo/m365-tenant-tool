<?php use App\Core\View; $e = fn($v) => View::escape($v);
$items      = $summary['items']  ?? [];
$byType     = $summary['byType'] ?? [];
$hasScanned = $summary['hasScanned'] ?? false;
?>
<?php \App\Core\View::partial('partials/module_tabs', ['tabs' => [['label'=>'Freigaben','href'=>'/sharing','icon'=>'link-45deg'],['label'=>'Monitor','href'=>'/sharing/monitor','icon'=>'eye-slash'],['label'=>'Richtlinien','href'=>'/sharing/policies','icon'=>'sliders'],]]); ?>


<?php if (!empty($flash)): ?>
    <div class="alert alert-success alert-dismissible mb-3"><i class="bi bi-check-circle me-2"></i><?= $e($flash) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>
<?php if (!empty($error)): ?>
    <div class="alert alert-danger alert-dismissible mb-3"><i class="bi bi-exclamation-triangle me-2"></i><?= $e($error) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<?php if (!$hasScanned): ?>
<div class="alert alert-info mb-4">
    <i class="bi bi-info-circle me-2"></i>
    <strong>Noch kein Freigaben-Scan durchgeführt.</strong>
    Klicken Sie auf "Jetzt scannen", um alle SharePoint-Freigaben zu erfassen.
    Der erste Scan kann je nach Tenant-Größe einige Minuten dauern — bitte die Seite während des Scans geöffnet lassen.
    <div class="mt-2">
        <a href="/sharing/scan" class="btn btn-sm btn-primary" id="scanBtn" onclick="scanStart(this)">
            <i class="bi bi-search me-1"></i> Jetzt scannen
        </a>
    </div>
</div>
<?php endif; ?>

<div class="row g-3 mb-4">
    <div class="col-sm-3">
        <div class="metric-card">
            <div class="metric-label">Aktive Freigaben</div>
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
        <select id="scopeFilter" class="form-select form-select-sm ms-2" style="max-width:180px;" onchange="filterSharing()">
            <option value="">Alle Typen</option>
            <option value="anonymous" <?= ($scopeFilter ?? '') === 'anonymous' ? 'selected' : '' ?>>Anonym</option>
            <option value="users"     <?= ($scopeFilter ?? '') === 'users'     ? 'selected' : '' ?>>Externe Benutzer</option>
            <option value="organization" <?= ($scopeFilter ?? '') === 'organization' ? 'selected' : '' ?>>Organisation</option>
        </select>
        <select id="statusFilter" class="form-select form-select-sm ms-2" style="max-width:180px;" onchange="applyStatusFilter()">
            <option value=""         <?= ($statusFilter ?? '') === ''              ? 'selected' : '' ?>>Alle (ohne widerrufen)</option>
            <option value="active"   <?= ($statusFilter ?? '') === 'active'        ? 'selected' : '' ?>>Aktiv</option>
            <option value="confirmed"<?= ($statusFilter ?? '') === 'confirmed'     ? 'selected' : '' ?>>Bestätigt</option>
            <option value="pending_review" <?= ($statusFilter ?? '') === 'pending_review' ? 'selected' : '' ?>>Ausstehend</option>
            <option value="revoked"  <?= ($statusFilter ?? '') === 'revoked'       ? 'selected' : '' ?>>Widerrufen</option>
        </select>
        <a href="/sharing/scan" class="btn btn-sm btn-outline-primary ms-auto" id="scanBtn" onclick="scanStart(this)" title="Scan starten (kann einige Minuten dauern)">
            <i class="bi bi-arrow-repeat me-1"></i> Scan
        </a>
        <a href="/sharing/export" class="btn btn-sm btn-outline-secondary ms-2">
            <i class="bi bi-download me-1"></i> CSV
        </a>
    </div>
    <div class="table-responsive">
        <table class="data-table" id="sharingTable">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Standort</th>
                    <th>Freigabe-Typ</th>
                    <th>Besitzer</th>
                    <th>Erstmals erkannt</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                    <tr data-scope="<?= $e($item['scope']) ?>">
                        <td class="fw-medium">
                            <?php if (!empty($item['url'])): ?>
                                <a href="<?= $e($item['url']) ?>" target="_blank" class="text-decoration-none text-dark">
                                    <?= $e($item['name']) ?> <i class="bi bi-box-arrow-up-right" style="font-size:10px;"></i>
                                </a>
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
                        <td style="font-size:12px;">
                            <?= $e($item['owner'] ?? '') ?>
                            <?php if (!empty($item['owner_upn']) && $item['owner_upn'] !== $item['owner']): ?>
                                <br><span style="color:#9ca3af;font-size:11px;"><?= $e($item['owner_upn']) ?></span>
                            <?php endif; ?>
                        </td>
                        <td style="font-size:12px;color:#6b7280;">
                            <?= !empty($item['modified']) ? date('d.m.Y', strtotime($item['modified'])) : '–' ?>
                        </td>
                        <td>
                            <?php $status = $item['status'] ?? 'active'; ?>
                            <?php if ($status === 'active'): ?>
                                <span class="badge-info">Aktiv</span>
                            <?php elseif ($status === 'confirmed'): ?>
                                <span class="badge-ok">Bestätigt</span>
                            <?php elseif ($status === 'pending_review'): ?>
                                <span class="badge-warning">Ausstehend</span>
                            <?php elseif ($status === 'revoked'): ?>
                                <span class="badge-disabled">Widerrufen</span>
                            <?php else: ?>
                                <span class="badge-neutral"><?= $e($status) ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($status !== 'revoked'): ?>
                            <button class="btn btn-sm btn-outline-danger"
                                    onclick="confirmRevoke(this)"
                                    data-drive="<?= $e($item['drive_id']) ?>"
                                    data-item="<?= $e($item['item_id']) ?>"
                                    data-perm="<?= $e($item['permission_id']) ?>"
                                    data-name="<?= $e($item['name']) ?>"
                                    title="Freigabe widerrufen">
                                <i class="bi bi-x-circle"></i>
                            </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($items)): ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">
                            <?= $hasScanned ? 'Keine Freigaben gefunden' : 'Noch kein Scan durchgeführt — siehe Hinweis oben.' ?>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Revoke confirmation modal -->
<div class="modal fade" id="revokeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-x-circle text-danger me-2"></i>Freigabe widerrufen</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Soll die Freigabe für <strong id="revokeName"></strong> widerrufen werden?</p>
                <p class="text-muted" style="font-size:13px;">Diese Aktion entfernt die Berechtigung dauerhaft in SharePoint und kann nicht rückgängig gemacht werden.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Abbrechen</button>
                <form id="revokeForm" method="post" action="/sharing/revoke">
                    <?= \App\Core\Csrf::field() ?>
                    <input type="hidden" name="drive_id"      id="rDriveId">
                    <input type="hidden" name="item_id"       id="rItemId">
                    <input type="hidden" name="permission_id" id="rPermId">
                    <button type="submit" class="btn btn-danger"><i class="bi bi-x-circle me-1"></i>Widerrufen</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
initTableSearch('sharingSearch', 'sharingTable');

function scanStart(btn) {
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Scan läuft…';
    btn.classList.add('disabled');
    document.querySelectorAll('#scanBtn').forEach(b => { b.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Scan läuft…'; b.classList.add('disabled'); });
}

function filterSharing() {
    const val = document.getElementById('scopeFilter').value;
    document.querySelectorAll('#sharingTable tbody tr[data-scope]').forEach(r => {
        r.style.display = (!val || r.dataset.scope === val) ? '' : 'none';
    });
}

function applyStatusFilter() {
    const val = document.getElementById('statusFilter').value;
    const url = new URL(window.location.href);
    if (val) url.searchParams.set('status', val);
    else url.searchParams.delete('status');
    window.location.href = url.toString();
}

function confirmRevoke(btn) {
    document.getElementById('revokeName').textContent = btn.dataset.name;
    document.getElementById('rDriveId').value = btn.dataset.drive;
    document.getElementById('rItemId').value  = btn.dataset.item;
    document.getElementById('rPermId').value  = btn.dataset.perm;
    new bootstrap.Modal(document.getElementById('revokeModal')).show();
}
</script>
