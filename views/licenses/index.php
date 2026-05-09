<?php use App\Core\View; $e = fn($v) => View::escape($v);
$totalConsumed = array_sum(array_column($skus, 'consumed'));
$totalLicenses = array_sum(array_column($skus, 'total'));
?>

<div class="row g-3 mb-4">
    <div class="col-sm-4">
        <div class="metric-card">
            <div class="metric-label">Produkte</div>
            <div class="metric-value"><?= count($skus) ?></div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="metric-card">
            <div class="metric-label">Lizenzen gesamt</div>
            <div class="metric-value"><?= number_format($totalLicenses) ?></div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="metric-card">
            <div class="metric-label">In Verwendung</div>
            <div class="metric-value"><?= number_format($totalConsumed) ?></div>
            <div class="metric-sub"><?= $totalLicenses > 0 ? round(($totalConsumed/$totalLicenses)*100) : 0 ?>% belegt</div>
        </div>
    </div>
</div>

<div class="content-card">
    <div class="table-toolbar">
        <input type="text" id="licSearch" class="search-box" placeholder="Lizenz suchen…">
    </div>
    <div class="table-responsive">
        <table class="data-table" id="licTable">
            <thead>
                <tr><th>Produkt</th><th>SKU</th><th>Genutzt</th><th>Gesamt</th><th>Verfügbar</th><th>Nutzung</th></tr>
            </thead>
            <tbody>
                <?php foreach ($skus as $sku): ?>
                    <?php $barCls = $sku['pct'] >= 90 ? 'danger' : ($sku['pct'] >= 75 ? 'warning' : ''); ?>
                    <tr>
                        <td class="fw-medium"><?= $e($sku['name']) ?></td>
                        <td style="font-size:11px;color:#9ca3af;font-family:monospace;"><?= $e($sku['partNumber']) ?></td>
                        <td><?= number_format($sku['consumed']) ?></td>
                        <td><?= number_format($sku['total']) ?></td>
                        <td>
                            <?php if ($sku['available'] <= 0): ?>
                                <span class="badge-disabled">0</span>
                            <?php elseif ($sku['available'] <= 10): ?>
                                <span class="badge-warning"><?= $sku['available'] ?></span>
                            <?php else: ?>
                                <?= number_format($sku['available']) ?>
                            <?php endif; ?>
                        </td>
                        <td style="min-width:140px;">
                            <div class="d-flex align-items-center gap-2">
                                <div class="progress-custom" style="flex:1;">
                                    <div class="bar <?= $barCls ?>" style="width:<?= min(100,$sku['pct']) ?>%;"></div>
                                </div>
                                <span style="font-size:11px;width:34px;text-align:right;"><?= $sku['pct'] ?>%</span>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($skus)): ?>
                    <tr><td colspan="6" class="text-center text-muted py-4">Keine Lizenzen gefunden</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<script>initTableSearch('licSearch', 'licTable');</script>
