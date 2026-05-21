<?php use App\Core\View; $e = fn($v) => View::escape($v); ?>

<?php if (!empty($flash)): ?>
    <div class="alert alert-success alert-dismissible mb-3"><i class="bi bi-check-circle me-2"></i><?= $e($flash) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>
<?php if (!empty($error)): ?>
    <div class="alert alert-danger alert-dismissible mb-3"><i class="bi bi-exclamation-triangle me-2"></i><?= $e($error) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<div class="alert alert-info d-flex gap-3 mb-3">
    <i class="bi bi-shield-fill-check flex-shrink-0 mt-1" style="font-size:1.4rem;color:#0078d4;"></i>
    <div>
        <strong>Quick-Härtung deines Tenants.</strong>
        Hier kannst du die wichtigsten Sicherheits-Einstellungen mit einem Klick aktivieren oder über
        Deep-Links direkt in das richtige Admin-Center springen. Jede Aktion zeigt vorher den aktuellen
        Zustand und schreibt das Audit-Log mit, damit nachvollziehbar bleibt, was, wann, von wem geändert wurde.
    </div>
</div>

<?php
$statusBadge = function (string $status): string {
    return match ($status) {
        'on'      => '<span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>OK</span>',
        'off'     => '<span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>Härten</span>',
        'warn'    => '<span class="badge bg-warning text-dark"><i class="bi bi-exclamation-triangle me-1"></i>Prüfen</span>',
        'info'    => '<span class="badge bg-info text-dark"><i class="bi bi-info-circle me-1"></i>Manuell</span>',
        default   => '<span class="badge bg-secondary">Unbekannt</span>',
    };
};
$cardBorder = fn (string $status) => match ($status) {
    'on'    => '#16a34a',
    'off'   => '#dc2626',
    'warn'  => '#d97706',
    'info'  => '#0284c7',
    default => '#9ca3af',
};
?>

<?php foreach ($byCategory as $cat => $items): ?>
<div class="content-card mb-4">
    <div class="card-header-custom">
        <i class="bi bi-folder text-primary"></i>
        <h6><?= $e($cat) ?></h6>
        <span class="ms-auto text-muted small"><?= count($items) ?> Item(s)</span>
    </div>
    <div class="card-body-custom">
        <div class="row g-3">
            <?php foreach ($items as $item): ?>
                <div class="col-lg-6">
                    <div class="p-3 rounded border h-100" style="border-left: 4px solid <?= $cardBorder($item['status']) ?> !important; background:#fff;">
                        <div class="d-flex justify-content-between align-items-start mb-2 gap-2">
                            <div class="fw-semibold"><?= $e($item['title']) ?></div>
                            <?= $statusBadge($item['status']) ?>
                        </div>
                        <p class="text-muted small mb-2"><?= $e($item['desc']) ?></p>
                        <p class="small mb-2" style="color:#475569;">
                            <i class="bi bi-bookmark-check me-1"></i><em><?= $e($item['why']) ?></em>
                        </p>
                        <?php if (!empty($item['detail'])): ?>
                            <div class="small mb-3 p-2 rounded" style="background:#f8fafc;border:1px solid #e2e8f0;">
                                <span class="text-muted">Aktueller Status:</span>
                                <?= $item['detail'] /* enthält teils HTML-Links — bewusst nicht escaped */ ?>
                            </div>
                        <?php endif; ?>
                        <div class="d-flex flex-wrap gap-2">
                            <?php foreach ($item['actions'] ?? [] as $act): ?>
                                <?php if (($act['id'] ?? '') === '__link'): ?>
                                    <a href="<?= $e($act['href']) ?>" class="btn btn-sm btn-<?= $e($act['style'] ?? 'outline-primary') ?>">
                                        <?= $e($act['label']) ?>
                                    </a>
                                <?php else: ?>
                                    <form method="post" action="/hardening/apply" class="d-inline"
                                          onsubmit="return confirm('Diese Aktion wirkt sofort tenant-weit. Fortfahren?');">
                                        <?= \App\Core\Csrf::field() ?>
                                        <input type="hidden" name="action_id" value="<?= $e($act['id']) ?>">
                                        <button type="submit" class="btn btn-sm btn-<?= $e($act['style'] ?? 'outline-primary') ?>">
                                            <?= $e($act['label']) ?>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            <?php endforeach; ?>
                            <?php if (!empty($item['admin_url'])): ?>
                                <a href="<?= $e($item['admin_url']) ?>" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-box-arrow-up-right me-1"></i>Im Admin-Center öffnen
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endforeach; ?>

<div class="alert alert-warning d-flex gap-3 mt-4">
    <i class="bi bi-exclamation-triangle-fill flex-shrink-0 mt-1" style="font-size:1.4rem;color:#b45309;"></i>
    <div>
        <strong>Wichtig:</strong> Tenant-weite Änderungen wirken sofort. Insbesondere die Conditional-Access-
        Policy „Block Legacy Auth" wird im <em>Report-Only</em>-Modus angelegt — bitte einige Tage Reports
        prüfen, bevor du sie auf <em>Enabled</em> stellst, um keine produktiven Services zu blockieren.
        Microsoft Graph schreibt jede Änderung in das Tenant-Audit-Log; das Tool zusätzlich in <em>App
        Audit-Log</em>.
    </div>
</div>
