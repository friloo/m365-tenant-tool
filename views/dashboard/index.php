<?php
use App\Core\View;
$e = fn($v) => View::escape($v);
$n = fn($v) => $v !== null ? number_format((int)$v) : '<span class="text-muted">–</span>';
?>

<!-- Metric Cards -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="metric-card d-flex align-items-center gap-3">
            <div class="metric-icon" style="background:#eff6ff;">
                <i class="bi bi-people-fill" style="color:#2563eb;"></i>
            </div>
            <div>
                <div class="metric-label">Benutzer gesamt</div>
                <div class="metric-value"><?= $n($metrics['total_users']) ?></div>
                <div class="metric-sub"><?= $n($metrics['enabled_users']) ?> aktiv</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="metric-card d-flex align-items-center gap-3">
            <div class="metric-icon" style="background:#f0fdf4;">
                <i class="bi bi-award-fill" style="color:#16a34a;"></i>
            </div>
            <div>
                <div class="metric-label">Lizenz-Produkte</div>
                <div class="metric-value"><?= $n($metrics['license_products']) ?></div>
                <div class="metric-sub">Abonnierte SKUs</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="metric-card d-flex align-items-center gap-3">
            <div class="metric-icon" style="background:#fef9c3;">
                <i class="bi bi-phone-fill" style="color:#ca8a04;"></i>
            </div>
            <div>
                <div class="metric-label">Geräte</div>
                <div class="metric-value"><?= $n($metrics['total_devices']) ?></div>
                <div class="metric-sub">Intune verwaltet</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="metric-card d-flex align-items-center gap-3">
            <div class="metric-icon" style="background:#fef2f2;">
                <i class="bi bi-exclamation-triangle-fill" style="color:#dc2626;"></i>
            </div>
            <div>
                <div class="metric-label">Risikobenutzer</div>
                <div class="metric-value" style="color:<?= ($metrics['risky_users'] ?? 0) > 0 ? '#dc2626' : '#111827' ?>">
                    <?= $n($metrics['risky_users']) ?>
                </div>
                <div class="metric-sub">Aktive Risiken</div>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($recommendations)): ?>
<div class="mb-4">
    <?php foreach ($recommendations as $rec): ?>
        <div class="alert alert-<?= $rec['type'] === 'danger' ? 'danger' : ($rec['type'] === 'warning' ? 'warning' : 'info') ?> py-2 mb-2">
            <?= $rec['msg'] ?>
        </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Charts Row -->
<div class="row g-3 mb-4">
    <!-- License Breakdown -->
    <div class="col-xl-7">
        <div class="content-card">
            <div class="card-header-custom">
                <i class="bi bi-bar-chart-fill text-primary"></i>
                <h6>Lizenz-Nutzung</h6>
            </div>
            <div class="card-body-custom">
                <?php if (empty($licenses)): ?>
                    <p class="text-muted text-center py-3">Keine Lizenzdaten verfügbar</p>
                <?php else: ?>
                    <?php foreach ($licenses as $sku): ?>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="fw-medium" style="font-size:13px;"><?= $e($sku['name']) ?></span>
                                <span class="text-muted" style="font-size:12px;">
                                    <?= number_format($sku['consumed']) ?> / <?= number_format($sku['total']) ?>
                                    <span class="ms-1"><?= $sku['pct'] ?>%</span>
                                </span>
                            </div>
                            <div class="progress-custom">
                                <div class="bar <?= $sku['pct'] >= 90 ? 'danger' : ($sku['pct'] >= 75 ? 'warning' : '') ?>"
                                     style="width:<?= min(100, $sku['pct']) ?>%"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="col-xl-5">
        <div class="content-card h-100">
            <div class="card-header-custom">
                <i class="bi bi-diagram-3-fill text-success"></i>
                <h6>Schnellübersicht</h6>
            </div>
            <div class="card-body-custom">
                <table class="table table-sm mb-0">
                    <tbody>
                        <tr>
                            <td class="text-muted" style="font-size:13px;">Gruppen & Teams</td>
                            <td class="text-end fw-medium"><?= $n($metrics['total_groups']) ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted" style="font-size:13px;">Aktivierte Benutzer</td>
                            <td class="text-end fw-medium"><?= $n($metrics['enabled_users']) ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted" style="font-size:13px;">Deaktivierte Benutzer</td>
                            <td class="text-end fw-medium">
                                <?php
                                $disabled = ($metrics['total_users'] ?? 0) - ($metrics['enabled_users'] ?? 0);
                                echo $disabled >= 0 ? number_format($disabled) : '–';
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted" style="font-size:13px;">Verwaltete Geräte</td>
                            <td class="text-end fw-medium"><?= $n($metrics['total_devices']) ?></td>
                        </tr>
                    </tbody>
                </table>

                <div class="mt-4">
                    <h6 class="small text-muted text-uppercase mb-2">Schnellzugriff</h6>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="/users" class="btn btn-sm btn-outline-primary">Benutzer</a>
                        <a href="/licenses" class="btn btn-sm btn-outline-success">Lizenzen</a>
                        <a href="/security" class="btn btn-sm btn-outline-danger">Sicherheit</a>
                        <a href="/devices" class="btn btn-sm btn-outline-warning">Geräte</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
