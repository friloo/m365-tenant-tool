<?php use App\Core\View; $e = fn($v) => View::escape($v); ?>

<p class="text-muted mb-4" style="font-size:14px;">
    <?= te('Ablaufende und kritische Microsoft 365 Lizenzen') ?>
</p>

<?php
$totalCount     = count($subscriptions);
$expiringCount  = count(array_filter($subscriptions, fn($s) => $s['is_expiring_soon']));
$criticalCount  = count(array_filter($subscriptions, fn($s) => $s['days_until_expiry'] !== null && $s['days_until_expiry'] <= 30 && $s['days_until_expiry'] > 0));
$expiredCount   = count(array_filter($subscriptions, fn($s) => $s['is_expired']));
$warningCount   = count(array_filter($subscriptions, fn($s) => !in_array($s['status'], ['Enabled', 'Success', 'Active'], true)));
$noDatesAvail   = !array_filter($subscriptions, fn($s) => $s['next_lifecycle'] !== null);

// Subscriptions expiring within 30 days (not yet expired)
$critical30 = array_filter($subscriptions, fn($s) => $s['days_until_expiry'] !== null && $s['days_until_expiry'] <= 30 && $s['days_until_expiry'] > 0);
?>

<?php if (!empty($critical30)): ?>
<div class="alert alert-danger mb-4">
    <i class="bi bi-exclamation-triangle-fill me-2"></i>
    <strong><?= te('Achtung:') ?></strong>
    <span class="badge-danger ms-1"><?= count($critical30) ?></span>
    <?= te('Lizenz(en) laufen in weniger als 30 Tagen ab!') ?>
</div>
<?php endif; ?>

<?php if ($noDatesAvail): ?>
<div class="alert alert-info mb-4">
    <i class="bi bi-info-circle me-2"></i>
    <?= te('Ablaufdaten sind nur über die Microsoft 365 Admin Center-API verfügbar. Die Graph API v1.0 liefert diese Information nicht für alle Tenant-Typen.') ?>
</div>
<?php endif; ?>

<!-- Metric cards -->
<div class="row g-3 mb-4">
    <div class="col-sm-3">
        <div class="metric-card">
            <div class="metric-label"><?= te('Gesamt Abonnements') ?></div>
            <div class="metric-value"><?= $totalCount ?></div>
            <div class="metric-sub"><?= te('Alle Lizenzen') ?></div>
        </div>
    </div>
    <div class="col-sm-3">
        <div class="metric-card">
            <div class="metric-label"><?= te('Ablaufend (≤60 Tage)') ?></div>
            <div class="metric-value" style="color:<?= $expiringCount > 0 ? '#dc2626' : '#16a34a' ?>;">
                <?= $expiringCount ?>
            </div>
            <div class="metric-sub">
                <?php if ($expiringCount > 0): ?>
                    <span class="badge-danger"><?= te(':n bald fällig', ['n' => $expiringCount]) ?></span>
                <?php else: ?>
                    <?= te('Keine bald fällig') ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-sm-3">
        <div class="metric-card">
            <div class="metric-label"><?= te('Kritisch (≤30 Tage)') ?></div>
            <div class="metric-value" style="color:<?= $criticalCount > 0 ? '#dc2626' : '#16a34a' ?>;">
                <?= $criticalCount ?>
            </div>
            <div class="metric-sub"><?= te('Sofortige Aufmerksamkeit') ?></div>
        </div>
    </div>
    <div class="col-sm-3">
        <div class="metric-card">
            <div class="metric-label"><?= te('Gesperrt/Warnung') ?></div>
            <div class="metric-value" style="color:<?= $warningCount > 0 ? '#d97706' : '#16a34a' ?>;">
                <?= $warningCount ?>
            </div>
            <div class="metric-sub"><?= te('Status nicht „Enabled"') ?></div>
        </div>
    </div>
</div>

<!-- Table -->
<div class="content-card">
    <div class="card-header-custom d-flex align-items-center justify-content-between">
        <span><i class="bi bi-calendar-check me-2"></i><?= te('Lizenz-Abonnements') ?></span>
        <div class="d-flex gap-2">
            <a href="/licenses/expiry?refresh=1" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-clockwise me-1"></i><?= te('Aktualisieren') ?>
            </a>
            <a href="/licenses" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-grid me-1"></i><?= te('Lizenzübersicht') ?>
            </a>
        </div>
    </div>
    <div class="card-body-custom">
        <div class="table-responsive">
            <table class="data-table" id="expiryTable">
                <thead>
                    <tr>
                        <th><?= te('Lizenz') ?></th>
                        <th><?= te('Status') ?></th>
                        <th><?= te('Ablaufdatum') ?></th>
                        <th><?= te('Tage verbleibend') ?></th>
                        <th><?= te('Lizenzen gesamt') ?></th>
                        <th><?= te('Genutzt') ?></th>
                        <th><?= te('Nutzungsgrad') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($subscriptions as $sub):
                        $total    = (int)$sub['total_licenses'];
                        $consumed = (int)$sub['consumed_licenses'];
                        $pct      = $total > 0 ? min(100, (int)round(($consumed / $total) * 100)) : 0;
                        $barClass = $pct > 90 ? 'danger' : ($pct >= 70 ? 'warning' : '');
                        $status   = $sub['status'] ?? '';
                        $daysLeft = $sub['days_until_expiry'];
                        $nextDt   = $sub['next_lifecycle'];

                        if (in_array($status, ['Enabled', 'Success', 'Active'], true)) {
                            $statusBadge = 'badge-enabled';
                        } elseif ($status === 'Warning') {
                            $statusBadge = 'badge-warning';
                        } else {
                            $statusBadge = 'badge-danger';
                        }
                    ?>
                    <tr>
                        <td class="fw-medium"><?= $e($sub['sku_name']) ?></td>
                        <td>
                            <span class="<?= $statusBadge ?>"><?= $e($status) ?></span>
                        </td>
                        <td>
                            <?php if ($nextDt === null): ?>
                                <span class="text-muted" style="font-size:12px;"><?= te('Nicht verfügbar') ?></span>
                            <?php elseif ($sub['is_expired']): ?>
                                <span class="badge-danger"><?= $e($nextDt->format('d.m.Y')) ?></span>
                            <?php else: ?>
                                <span style="font-size:12px;"><?= $e($nextDt->format('d.m.Y')) ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($daysLeft === null): ?>
                                <span class="text-muted">–</span>
                            <?php elseif ($daysLeft <= 0): ?>
                                <span class="badge-danger"><?= te('Abgelaufen') ?></span>
                            <?php elseif ($daysLeft <= 30): ?>
                                <span class="badge-danger"><?= te(':n Tage', ['n' => $daysLeft]) ?></span>
                            <?php elseif ($daysLeft <= 60): ?>
                                <span class="badge-warning"><?= te(':n Tage', ['n' => $daysLeft]) ?></span>
                            <?php else: ?>
                                <span class="text-muted" style="font-size:12px;"><?= te(':n Tage', ['n' => $daysLeft]) ?></span>
                            <?php endif; ?>
                        </td>
                        <td><?= number_format($total) ?></td>
                        <td><?= number_format($consumed) ?></td>
                        <td style="min-width:140px;">
                            <div class="d-flex align-items-center gap-2">
                                <div class="progress flex-grow-1" style="height:6px;">
                                    <div class="progress-bar<?= $barClass ? ' bg-' . $barClass : ' bg-success' ?>"
                                         role="progressbar"
                                         style="width:<?= $pct ?>%;"
                                         aria-valuenow="<?= $pct ?>"
                                         aria-valuemin="0"
                                         aria-valuemax="100"></div>
                                </div>
                                <span style="font-size:11px;width:34px;text-align:right;"><?= $pct ?>%</span>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($subscriptions)): ?>
                    <tr>
                        <td colspan="7">
                            <div class="empty-state">
                                <i class="bi bi-calendar-x"></i>
                                <p><?= te('Keine Abonnement-Informationen verfügbar.') ?></p>
                            </div>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
