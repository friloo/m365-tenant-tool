<?php use App\Core\View; $e = fn($v) => View::escape($v); ?>

<!-- Auto-refresh every 5 minutes -->
<meta http-equiv="refresh" content="300">

<?php
/**
 * Map Graph service status values to UI properties.
 *
 * @return array{color: string, label: string, dotClass: string}
 */
$statusMeta = function (string $status): array {
    return match ($status) {
        'serviceOperational', 'serviceDegradationMitigated'
            => ['color' => '#16a34a', 'label' => t('Normal'),            'dot' => 'bg-success'],
        'serviceDegradation'
            => ['color' => '#d97706', 'label' => t('Beeinträchtigt'),    'dot' => 'bg-warning'],
        'serviceInterruption'
            => ['color' => '#dc2626', 'label' => t('Unterbrochen'),      'dot' => 'bg-danger'],
        'restoringService'
            => ['color' => '#2563eb', 'label' => t('Wird wiederhergestellt'), 'dot' => 'bg-primary'],
        'extendedRecovery'
            => ['color' => '#7c3aed', 'label' => t('Erweitertes Recovery'), 'dot' => 'bg-purple'],
        'falsePositive'
            => ['color' => '#6b7280', 'label' => t('Falsch positiv'),    'dot' => 'bg-secondary'],
        'investigationSuspended'
            => ['color' => '#9ca3af', 'label' => t('Untersucht'),        'dot' => 'bg-secondary'],
        default
            => ['color' => '#9ca3af', 'label' => $status,             'dot' => 'bg-secondary'],
    };
};

$classificationLabel = fn(string $c): string => match (strtolower($c)) {
    'advisory'   => 'Advisory',
    'incident'   => 'Incident',
    'prevention' => 'Prevention',
    default      => $c,
};
?>

<?php if (empty($overview)): ?>
<!-- Permission / connectivity error -->
<div class="content-card mb-4">
    <div class="card-body-custom">
        <?php
        if (!empty($diag ?? null)) {
            $diagStyle = 'empty';
            $diagIcon  = 'cloud-slash';
            $diagTitle = t('Keine Service-Health-Daten verfügbar');
            include BASE_PATH . '/views/partials/graph_diagnostic.php';
        } else { ?>
            <div class="empty-state">
                <i class="bi bi-cloud-slash text-muted" style="font-size:2.5rem;"></i>
                <p class="mt-3 mb-1 fw-medium"><?= te('Keine Service-Health-Daten verfügbar') ?></p>
                <p class="text-muted small"><?= te('Microsoft hat aktuell keine Status-Daten geliefert.') ?></p>
            </div>
        <?php } ?>
    </div>
</div>
<?php else: ?>

<!-- Overall Status Banner -->
<?php $issueCount = count($issues); ?>
<?php if ($allHealthy): ?>
<div class="content-card mb-4" style="border-left: 4px solid #16a34a;">
    <div class="card-body-custom d-flex align-items-center gap-3">
        <i class="bi bi-check-circle-fill text-success" style="font-size:2rem;"></i>
        <div>
            <div class="fw-semibold" style="color:#16a34a;font-size:1.05rem;"><?= te('Alle Dienste normal') ?></div>
            <div class="text-muted small"><?= te('Keine bekannten Probleme bei Microsoft 365-Diensten.') ?></div>
        </div>
    </div>
</div>
<?php else: ?>
<div class="content-card mb-4" style="border-left: 4px solid <?= $issueCount > 0 ? '#dc2626' : '#d97706' ?>;">
    <div class="card-body-custom d-flex align-items-center gap-3">
        <i class="bi bi-exclamation-triangle-fill" style="font-size:2rem;color:<?= $issueCount > 0 ? '#dc2626' : '#d97706' ?>;"></i>
        <div>
            <div class="fw-semibold" style="color:<?= $issueCount > 0 ? '#dc2626' : '#d97706' ?>;font-size:1.05rem;">
                <?php if ($issueCount > 0): ?>
                    <?= $issueCount === 1
                        ? te(':n aktives Problem', ['n' => $issueCount])
                        : te(':n aktive Probleme', ['n' => $issueCount]) ?>
                <?php else: ?>
                    <?= te('Beeinträchtigte Dienste erkannt') ?>
                <?php endif; ?>
            </div>
            <div class="text-muted small">
                <?= $issueCount > 0 ? te('Mindestens ein Microsoft 365-Dienst meldet aktive Vorfälle.') : te('Ein oder mehrere Dienste laufen nicht im Normalbetrieb.') ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Service Status Grid -->
<div class="content-card mb-4">
    <div class="card-header-custom">
        <i class="bi bi-grid-3x3-gap text-primary"></i>
        <h6><?= te('Dienste') ?> (<?= count($overview) ?>)</h6>
    </div>
    <div class="card-body-custom">
        <div class="row g-2">
            <?php foreach ($overview as $svc): ?>
                <?php $meta = $statusMeta($svc['status']); ?>
                <div class="col-sm-6 col-md-4 col-lg-3">
                    <div class="d-flex align-items-center gap-2 p-2 rounded"
                         style="background:#f9fafb;border:1px solid #e5e7eb;">
                        <span style="width:10px;height:10px;border-radius:50%;display:inline-block;
                                     background:<?= $meta['color'] ?>;flex-shrink:0;"></span>
                        <div style="min-width:0;">
                            <div style="font-size:12px;font-weight:500;white-space:nowrap;overflow:hidden;
                                        text-overflow:ellipsis;" title="<?= $e($svc['service']) ?>">
                                <?= $e($svc['service']) ?>
                            </div>
                            <div style="font-size:11px;color:<?= $meta['color'] ?>;">
                                <?= $e($meta['label']) ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Active Incidents -->
<?php if (!empty($issues)): ?>
<div class="content-card mb-4">
    <div class="card-header-custom">
        <i class="bi bi-exclamation-octagon-fill text-danger"></i>
        <h6><?= te('Aktive Vorfälle') ?> (<?= count($issues) ?>)</h6>
    </div>
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th><?= te('Dienst') ?></th>
                    <th><?= te('Titel') ?></th>
                    <th><?= te('Beginn') ?></th>
                    <th><?= te('Status') ?></th>
                    <th><?= te('Klassifizierung') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($issues as $issue): ?>
                    <?php $meta = $statusMeta($issue['status'] ?? 'unknown'); ?>
                    <tr>
                        <td style="font-size:12px;font-weight:500;white-space:nowrap;">
                            <?= $e($issue['service'] ?? '') ?>
                        </td>
                        <td style="font-size:13px;">
                            <?= $e($issue['title'] ?? '') ?>
                            <?php if (!empty($issue['impactDescription'])): ?>
                                <div style="font-size:11px;color:#9ca3af;margin-top:2px;">
                                    <?= $e(mb_strimwidth($issue['impactDescription'], 0, 120, '…')) ?>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td style="font-size:12px;color:#6b7280;white-space:nowrap;">
                            <?= !empty($issue['startDateTime'])
                                ? date('d.m.Y H:i', strtotime($issue['startDateTime']))
                                : '–' ?>
                        </td>
                        <td>
                            <span style="font-size:11px;padding:2px 8px;border-radius:9999px;
                                         background:<?= $meta['color'] ?>1a;color:<?= $meta['color'] ?>;
                                         font-weight:500;">
                                <?= $e($meta['label']) ?>
                            </span>
                        </td>
                        <td>
                            <?php $cls = strtolower($issue['classification'] ?? ''); ?>
                            <?php if ($cls === 'incident'): ?>
                                <span class="badge-danger">Incident</span>
                            <?php elseif ($cls === 'advisory'): ?>
                                <span class="badge-warning">Advisory</span>
                            <?php else: ?>
                                <span class="badge-neutral"><?= $e($issue['classification'] ?? '–') ?></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- Recent Messages -->
<?php if (!empty($messages)): ?>
<div class="content-card">
    <div class="card-header-custom">
        <i class="bi bi-megaphone text-secondary"></i>
        <h6><?= te('Neueste Nachrichten') ?> (<?= count($messages) ?>)</h6>
    </div>
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th><?= te('Titel') ?></th>
                    <th><?= te('Dienste') ?></th>
                    <th><?= te('Geändert') ?></th>
                    <th><?= te('Typ') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($messages as $msg): ?>
                    <tr>
                        <td style="font-size:13px;font-weight:500;">
                            <?= $e($msg['title'] ?? '') ?>
                        </td>
                        <td style="font-size:11px;color:#6b7280;">
                            <?= $e(implode(', ', $msg['services'] ?? [])) ?>
                        </td>
                        <td style="font-size:12px;color:#6b7280;white-space:nowrap;">
                            <?= !empty($msg['lastModifiedDateTime'])
                                ? date('d.m.Y H:i', strtotime($msg['lastModifiedDateTime']))
                                : '–' ?>
                        </td>
                        <td>
                            <?php $cls = strtolower($msg['classification'] ?? ''); ?>
                            <?php if ($cls === 'incident'): ?>
                                <span class="badge-danger">Incident</span>
                            <?php elseif ($cls === 'advisory'): ?>
                                <span class="badge-info">Advisory</span>
                            <?php elseif ($cls === 'prevention'): ?>
                                <span class="badge-success">Prevention</span>
                            <?php else: ?>
                                <span class="badge-secondary"><?= $e($msg['classification'] ?? '–') ?></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php endif; ?>
