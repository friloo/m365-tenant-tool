<?php use App\Core\View; $e = fn($v) => View::escape($v);
function fmtBytes(int $bytes): string {
    if ($bytes <= 0) return '0 B';
    $k = 1024; $sizes = ['B','KB','MB','GB','TB'];
    $i = min(floor(log($bytes, $k)), 4);
    return round($bytes / pow($k, $i), 1) . ' ' . $sizes[$i];
}
$totalUsed = array_sum(array_column($drives, 'used'));
?>

<!-- Sub-nav tabs -->
<div class="module-tabs mb-4">
    <a href="/onedrive" class="module-tab active">
        <i class="bi bi-cloud me-1"></i> Speicher-Übersicht
    </a>
    <a href="/onedrive/personal" class="module-tab">
        <i class="bi bi-person-circle me-1"></i> Persönliche Laufwerke
    </a>
</div>

<div class="row g-3 mb-4">
    <div class="col-sm-4">
        <div class="metric-card">
            <div class="metric-label">OneDrives<?= !empty($sample) ? ' (Stichprobe)' : '' ?></div>
            <div class="metric-value"><?= count($drives) ?></div>
            <div class="metric-sub"><?= !empty($sample) ? 'von max. 50 Benutzern' : 'alle provisionierten Laufwerke' ?></div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="metric-card">
            <div class="metric-label">Gesamt belegt</div>
            <div class="metric-value"><?= fmtBytes($totalUsed) ?></div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="metric-card">
            <div class="metric-label">Top-Verbraucher</div>
            <div class="metric-value" style="font-size:1.2rem;">
                <?= $e($drives[0]['user'] ?? '–') ?>
            </div>
            <div class="metric-sub"><?= fmtBytes($drives[0]['used'] ?? 0) ?></div>
        </div>
    </div>
</div>

<div class="content-card">
    <div class="table-toolbar">
        <input type="text" id="odSearch" class="search-box" placeholder="Benutzer suchen…">
        <a href="/onedrive/personal" class="btn btn-sm btn-outline-primary ms-auto">
            <i class="bi bi-person-circle me-1"></i> Alle persönlichen Laufwerke
        </a>
    </div>
    <div class="table-responsive">
        <table class="data-table" id="odTable">
            <thead>
                <tr>
                    <th>Benutzer</th>
                    <th>UPN</th>
                    <th>Belegt</th>
                    <th>Gesamt</th>
                    <th>Nutzung</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($drives as $d): ?>
                    <?php
                    $pct    = $d['total'] > 0 ? round(($d['used'] / $d['total']) * 100) : 0;
                    $barCls = $pct >= 90 ? 'danger' : ($pct >= 75 ? 'warning' : '');
                    ?>
                    <tr>
                        <td class="fw-medium"><?= $e($d['user']) ?></td>
                        <td style="font-size:12px;color:#6b7280;"><?= $e($d['upn']) ?></td>
                        <td><?= fmtBytes($d['used']) ?></td>
                        <td><?= fmtBytes($d['total']) ?></td>
                        <td style="min-width:120px;">
                            <div class="d-flex align-items-center gap-2">
                                <div class="progress-custom flex-1" style="flex:1;">
                                    <div class="bar <?= $barCls ?>" style="width:<?= min(100,$pct) ?>%;"></div>
                                </div>
                                <span style="font-size:11px;width:34px;text-align:right;"><?= $pct ?>%</span>
                            </div>
                        </td>
                        <td>
                            <?php if ($d['state'] === 'normal'): ?>
                                <span class="badge-enabled">Normal</span>
                            <?php elseif ($d['state'] === 'warning'): ?>
                                <span class="badge-warning">Warnung</span>
                            <?php else: ?>
                                <span class="badge-disabled"><?= $e($d['state']) ?></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($drives)): ?>
                    <tr><td colspan="6" class="text-center text-muted py-4">Keine Daten verfügbar</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
initTableSearch('odSearch', 'odTable');
initPagination('odTable', 25);
</script>
