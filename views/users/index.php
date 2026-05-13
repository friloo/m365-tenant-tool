<?php use App\Core\View; $e = fn($v) => View::escape($v); ?>

<?php
$mfaEnabled = count(array_filter($users, fn($u) => !empty($mfaMap[$u['userPrincipalName']]['mfaRegistered'])));
$total = count($users);
?>

<!-- Stats row -->
<div class="row g-3 mb-4">
    <div class="col-sm-4">
        <div class="metric-card">
            <div class="metric-label">Gesamt</div>
            <div class="metric-value"><?= number_format($total) ?></div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="metric-card">
            <div class="metric-label">MFA registriert</div>
            <div class="metric-value"><?= number_format($mfaEnabled) ?></div>
            <div class="metric-sub"><?= $total > 0 ? round(($mfaEnabled/$total)*100) : 0 ?>% der Benutzer</div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="metric-card">
            <div class="metric-label">Deaktiviert</div>
            <div class="metric-value"><?= number_format(count(array_filter($users, fn($u) => !($u['accountEnabled'] ?? true)))) ?></div>
        </div>
    </div>
</div>

<!-- Bulk Action Form (wraps table) -->
<form method="post" action="/users/bulk-action" id="bulkForm">
<div class="content-card">
    <div class="table-toolbar">
        <input type="checkbox" id="selectAll" class="form-check-input me-2" title="Alle auswählen">
        <input type="text" id="userSearch" class="search-box" placeholder="Benutzer suchen…">
        <select id="userFilter" class="form-select form-select-sm ms-2" style="max-width:180px;" onchange="filterUsers()">
            <option value="">Alle Benutzer</option>
            <option value="active">Nur aktive</option>
            <option value="disabled">Nur deaktivierte</option>
            <option value="no-mfa">MFA nicht registriert</option>
            <option value="inactive-30">Inaktiv > 30 Tage</option>
            <option value="inactive-90">Inaktiv > 90 Tage</option>
            <option value="no-license">Keine Lizenz</option>
        </select>
        <a href="/users/export" class="btn btn-sm btn-outline-secondary ms-2">
            <i class="bi bi-download me-1"></i> CSV
        </a>
        <a href="?refresh=1" class="btn btn-sm btn-outline-secondary ms-2">
            <i class="bi bi-arrow-clockwise"></i> Aktualisieren
        </a>
    </div>

    <!-- Bulk action bar (shown when rows are selected) -->
    <div id="bulkBar" style="display:none;padding:10px 20px;background:#eff6ff;border-bottom:1px solid #bfdbfe;display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
        <span id="bulkCount" class="badge-info badge-pill me-2">0 ausgewählt</span>
        <input type="hidden" name="action" id="bulkAction" value="">
        <button type="button" class="btn btn-sm btn-outline-secondary"
                onclick="submitBulk('disable')"
                title="Ausgewählte Benutzer deaktivieren">
            <i class="bi bi-person-dash me-1"></i> Deaktivieren
        </button>
        <button type="button" class="btn btn-sm btn-outline-success"
                onclick="submitBulk('enable')">
            <i class="bi bi-person-check me-1"></i> Aktivieren
        </button>
        <button type="button" class="btn btn-sm btn-outline-warning"
                onclick="submitBulk('reset_mfa')">
            <i class="bi bi-shield-x me-1"></i> MFA zurücksetzen
        </button>
        <button type="button" class="btn btn-sm btn-outline-primary"
                onclick="openLicenseModal('assign')">
            <i class="bi bi-plus-circle me-1"></i> Lizenz zuweisen
        </button>
        <button type="button" class="btn btn-sm btn-outline-danger"
                onclick="submitBulk('remove_license')"
                title="Alle Lizenzen der ausgewählten Benutzer entfernen">
            <i class="bi bi-dash-circle me-1"></i> Lizenzen entfernen
        </button>
        <input type="hidden" name="sku_id" id="bulkSkuId" value="">
    </div>

    <!-- License assign modal -->
    <div class="modal fade" id="licenseModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-award me-2"></i>Lizenz zuweisen</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <label class="form-label fw-semibold">Lizenz auswählen</label>
                    <select id="skuSelect" class="form-select">
                        <?php foreach ($skus as $sku): ?>
                            <?php $avail = ($sku['prepaidUnits']['enabled'] ?? 0) - ($sku['consumedUnits'] ?? 0); ?>
                            <?php if ($avail <= 0) continue; ?>
                            <option value="<?= $e($sku['skuId']) ?>">
                                <?= $e($sku['skuPartNumber']) ?> (<?= $avail ?> verfügbar)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Abbrechen</button>
                    <button type="button" class="btn btn-primary" onclick="confirmLicenseAssign()">
                        <i class="bi bi-check-circle me-1"></i> Zuweisen
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="table-responsive">
        <table class="data-table" id="userTable">
            <thead>
                <tr>
                    <th style="width:36px;"></th>
                    <th>Name</th>
                    <th>UPN</th>
                    <th>Status</th>
                    <th>MFA</th>
                    <th>Lizenzen</th>
                    <th>Letzter Login</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <?php
                    $mfa        = $mfaMap[$user['userPrincipalName'] ?? ''] ?? null;
                    $enabled    = $user['accountEnabled'] ?? true;
                    $licenses   = count($user['assignedLicenses'] ?? []);
                    $lastSignIn = $user['signInActivity']['lastSignInDateTime'] ?? null;
                    $daysAgo    = $lastSignIn ? (int)floor((time() - strtotime($lastSignIn)) / 86400) : 9999;
                    $mfaReg     = $mfa['mfaRegistered'] ?? false;
                    ?>
                    <tr data-enabled="<?= $enabled ? '1' : '0' ?>"
                        data-mfa="<?= $mfaReg ? '1' : '0' ?>"
                        data-days="<?= $daysAgo ?>"
                        data-licenses="<?= $licenses ?>">
                        <td>
                            <input type="checkbox" name="user_ids[]" value="<?= $e($user['id']) ?>"
                                   class="form-check-input row-check">
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div style="width:32px;height:32px;border-radius:50%;background:#e3f0fb;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:600;color:#0078d4;flex-shrink:0;">
                                    <?= strtoupper(substr($user['displayName'] ?? '?', 0, 1)) ?>
                                </div>
                                <div>
                                    <div style="font-weight:500;"><?= $e($user['displayName'] ?? '') ?></div>
                                    <?php if (!empty($user['jobTitle'])): ?>
                                        <div style="font-size:11px;color:#9ca3af;"><?= $e($user['jobTitle']) ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                        <td style="color:#6b7280;font-size:12px;"><?= $e($user['userPrincipalName'] ?? '') ?></td>
                        <td>
                            <?php if ($enabled): ?>
                                <span class="badge-enabled">Aktiv</span>
                            <?php else: ?>
                                <span class="badge-disabled">Deaktiviert</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($mfa): ?>
                                <?php if ($mfa['mfaRegistered']): ?>
                                    <span class="badge-enabled"><i class="bi bi-shield-check"></i> Ja</span>
                                <?php else: ?>
                                    <span class="badge-warning">Nein</span>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="badge-neutral">–</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($licenses > 0): ?>
                                <span class="badge-info"><?= $licenses ?></span>
                            <?php else: ?>
                                <span class="badge-neutral">0</span>
                            <?php endif; ?>
                        </td>
                        <td style="font-size:12px;color:#6b7280;">
                            <?php if ($lastSignIn): ?>
                                <?= date('d.m.Y', strtotime($lastSignIn)) ?>
                            <?php else: ?>
                                <span class="text-muted">Nie</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="/users/<?= $e($user['id']) ?>" class="btn btn-sm btn-link py-0" style="font-size:12px;">Detail</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
</form>

<script>
initTableSearch('userSearch', 'userTable');
initPagination('userTable', 25);

function filterUsers() {
    const val = document.getElementById('userFilter').value;
    document.querySelectorAll('#userTable tbody tr').forEach(r => {
        let show = true;
        const d = r.dataset;
        if (val === 'active')      show = d.enabled === '1';
        if (val === 'disabled')    show = d.enabled === '0';
        if (val === 'no-mfa')      show = d.mfa === '0' && d.enabled === '1';
        if (val === 'inactive-30') show = parseInt(d.days) > 30;
        if (val === 'inactive-90') show = parseInt(d.days) > 90;
        if (val === 'no-license')  show = d.licenses === '0' && d.enabled === '1';
        r.dataset.filterMatch = show ? '1' : '0';
        if (!show) { const cb = r.querySelector('.row-check'); if (cb) cb.checked = false; }
    });
    updateBulkBar();
    document.getElementById('userTable').dispatchEvent(new CustomEvent('hs:filter'));
}

function updateBulkBar() {
    const checked = document.querySelectorAll('.row-check:checked');
    const bar     = document.getElementById('bulkBar');
    const countEl = document.getElementById('bulkCount');
    bar.style.display = checked.length > 0 ? 'flex' : 'none';
    countEl.textContent = checked.length + ' ausgewählt';
}

function submitBulk(action) {
    const labels = {
        disable:        'Ausgewählte Benutzer wirklich deaktivieren?',
        enable:         'Ausgewählte Benutzer aktivieren?',
        reset_mfa:      'MFA für ausgewählte Benutzer zurücksetzen?',
        remove_license: 'Alle Lizenzen der ausgewählten Benutzer entfernen?',
    };
    if (!confirm(labels[action] || 'Aktion ausführen?')) return;
    document.getElementById('bulkAction').value = action;
    document.getElementById('bulkForm').submit();
}
function openLicenseModal(type) {
    new bootstrap.Modal(document.getElementById('licenseModal')).show();
}
function confirmLicenseAssign() {
    const skuId = document.getElementById('skuSelect').value;
    if (!skuId) return;
    document.getElementById('bulkSkuId').value = skuId;
    document.getElementById('bulkAction').value = 'assign_license';
    bootstrap.Modal.getInstance(document.getElementById('licenseModal')).hide();
    document.getElementById('bulkForm').submit();
}

document.getElementById('selectAll').addEventListener('change', function () {
    document.querySelectorAll('#userTable tbody tr:not([style*="none"]) .row-check').forEach(cb => {
        cb.checked = this.checked;
    });
    updateBulkBar();
});

document.addEventListener('change', function (e) {
    if (e.target.classList.contains('row-check')) updateBulkBar();
});
</script>
