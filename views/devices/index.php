<?php use App\Core\View; $e = fn($v) => View::escape($v); ?>

<div class="row g-3 mb-4">
    <div class="col-sm-3">
        <div class="metric-card">
            <div class="metric-label">Geräte gesamt</div>
            <div class="metric-value"><?= number_format($stats['total']) ?></div>
        </div>
    </div>
    <div class="col-sm-3">
        <div class="metric-card">
            <div class="metric-label">Konform</div>
            <div class="metric-value" style="color:#16a34a;"><?= $stats['by_compliance']['compliant'] ?? 0 ?></div>
        </div>
    </div>
    <div class="col-sm-3">
        <div class="metric-card">
            <div class="metric-label">Nicht konform</div>
            <div class="metric-value" style="color:#dc2626;"><?= $stats['by_compliance']['noncompliant'] ?? 0 ?></div>
        </div>
    </div>
    <div class="col-sm-3">
        <div class="metric-card">
            <div class="metric-label">Verschlüsselt</div>
            <div class="metric-value"><?= $stats['encrypted'] ?></div>
        </div>
    </div>
</div>

<?php if (!empty($stats['by_os'])): ?>
<div class="content-card mb-4">
    <div class="card-header-custom">
        <i class="bi bi-pie-chart text-primary"></i>
        <h6>Betriebssysteme</h6>
    </div>
    <div class="card-body-custom">
        <div class="row g-2">
            <?php foreach ($stats['by_os'] as $os => $count): ?>
                <div class="col-sm-3">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-<?= str_contains(strtolower($os),'windows')?'windows':'phone' ?> text-muted"></i>
                        <span class="small fw-medium"><?= $e($os) ?></span>
                        <span class="badge-neutral ms-auto"><?= $count ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="content-card">
    <div class="table-toolbar">
        <input type="text" id="devSearch" class="search-box" placeholder="Gerät suchen…">
        <select id="complianceFilter" class="form-select form-select-sm ms-2" style="max-width:160px;" onchange="filterDevices()">
            <option value="">Alle Status</option>
            <option value="compliant">Konform</option>
            <option value="noncompliant">Nicht konform</option>
            <option value="unknown">Unbekannt</option>
        </select>
    </div>
    <div class="table-responsive">
        <table class="data-table" id="devTable">
            <thead>
                <tr><th>Gerätename</th><th>OS</th><th>Version</th><th>Benutzer</th><th>Compliance</th><th>Verschlüsselt</th><th>Letzter Sync</th></tr>
            </thead>
            <tbody>
                <?php foreach ($devices as $d): ?>
                    <?php $compliance = $d['complianceState'] ?? 'unknown'; ?>
                    <tr data-compliance="<?= $e($compliance) ?>">
                        <td class="fw-medium"><?= $e($d['deviceName'] ?? '') ?></td>
                        <td><?= $e($d['operatingSystem'] ?? '') ?></td>
                        <td style="font-size:12px;color:#6b7280;"><?= $e($d['osVersion'] ?? '') ?></td>
                        <td style="font-size:12px;"><?= $e($d['userPrincipalName'] ?? '') ?></td>
                        <td>
                            <?php if ($compliance === 'compliant'): ?>
                                <span class="badge-enabled">Konform</span>
                            <?php elseif ($compliance === 'noncompliant'): ?>
                                <span class="badge-disabled">Nicht konform</span>
                            <?php else: ?>
                                <span class="badge-neutral"><?= $e($compliance) ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($d['isEncrypted'] ?? false): ?>
                                <span class="badge-enabled"><i class="bi bi-lock"></i></span>
                            <?php else: ?>
                                <span class="badge-warning"><i class="bi bi-unlock"></i></span>
                            <?php endif; ?>
                        </td>
                        <td style="font-size:12px;color:#6b7280;">
                            <?= !empty($d['lastSyncDateTime']) ? date('d.m.Y H:i', strtotime($d['lastSyncDateTime'])) : '–' ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($devices)): ?>
                    <tr><td colspan="7" class="text-center text-muted py-4">Keine Geräte gefunden (Intune-Berechtigungen prüfen)</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
initTableSearch('devSearch', 'devTable');
function filterDevices() {
    const val = document.getElementById('complianceFilter').value;
    document.querySelectorAll('#devTable tbody tr').forEach(r => {
        r.style.display = (!val || r.dataset.compliance === val) ? '' : 'none';
    });
}
</script>
