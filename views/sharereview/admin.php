<?php use App\Core\View; $e = fn($v) => View::escape($v); ?>
<?php \App\Core\View::partial('partials/module_tabs', ['tabs' => [['label'=>t('Freigaben'),'href'=>'/sharing','icon'=>'link-45deg'],['label'=>t('Monitor'),'href'=>'/sharing/monitor','icon'=>'eye-slash'],['label'=>t('Richtlinien'),'href'=>'/sharing/policies','icon'=>'sliders'],]]); ?>


<?php if ($flash): ?>
    <div class="alert alert-success alert-dismissible mb-4">
        <i class="bi bi-check-circle me-2"></i><?= $e($flash) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible mb-4">
        <i class="bi bi-exclamation-triangle me-2"></i><?= $e($error) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Stats -->
<div class="row g-3 mb-4">
    <?php
    $statCards = [
        ['label' => t('Gesamt'), 'key' => 'total', 'icon' => 'link-45deg', 'color' => 'primary'],
        ['label' => t('Aktiv'), 'key' => 'active', 'icon' => 'circle-fill', 'color' => 'success'],
        ['label' => t('Prüfung ausstehend'), 'key' => 'pending_review', 'icon' => 'clock-history', 'color' => 'warning'],
        ['label' => t('Überfällig'), 'key' => 'overdue', 'icon' => 'exclamation-triangle-fill', 'color' => 'danger'],
        ['label' => t('Bestätigt'), 'key' => 'confirmed', 'icon' => 'check-circle-fill', 'color' => 'info'],
        ['label' => t('Widerrufen'), 'key' => 'revoked', 'icon' => 'x-circle-fill', 'color' => 'secondary'],
    ];
    foreach ($statCards as $c): ?>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="content-card text-center p-3">
            <div class="text-<?= $c['color'] ?> mb-1"><i class="bi bi-<?= $c['icon'] ?>"></i></div>
            <div style="font-size:22px;font-weight:700;"><?= (int)($stats[$c['key']] ?? 0) ?></div>
            <div class="text-muted" style="font-size:12px;"><?= $c['label'] ?></div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Actions bar -->
<div class="content-card mb-4">
    <div class="card-body-custom d-flex gap-2 flex-wrap align-items-center">
        <a href="/sharing/monitor/scan" class="btn btn-sm btn-primary" onclick="return confirm(<?= htmlspecialchars(json_encode(t('Alle SharePoint-Freigaben jetzt scannen? Dies kann einige Minuten dauern.'), JSON_UNESCAPED_UNICODE), ENT_QUOTES) ?>)">
            <i class="bi bi-search me-1"></i><?= te('Jetzt scannen') ?>
        </a>
        <span class="text-muted" style="font-size:12px; margin-left:8px;">
            <i class="bi bi-info-circle me-1"></i><?= te('Alternativ Cron:') ?> <code>php run-share-monitor.php</code>
        </span>
        <div class="ms-auto">
            <a href="/settings#share-review" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-gear me-1"></i><?= te('Einstellungen') ?>
            </a>
        </div>
    </div>
</div>

<!-- Filter + Table -->
<div class="content-card">
    <div class="card-header-custom d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-table text-primary"></i>
            <h6><?= te('Überwachte Freigaben') ?></h6>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <?php
            $filters = ['' => t('Alle'), 'active' => t('Aktiv'), 'pending_review' => t('Ausstehend'), 'confirmed' => t('Bestätigt'), 'revoked' => t('Widerrufen')];
            foreach ($filters as $val => $label):
                $active = ($statusFilter === $val) ? 'btn-primary' : 'btn-outline-secondary';
            ?>
            <a href="/sharing/monitor<?= $val ? '?status='.$val : '' ?>" class="btn btn-sm <?= $active ?>"><?= $label ?></a>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="card-body-custom p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="shareTable">
                <thead class="table-light">
                    <tr>
                        <th><?= te('Datei/Ordner') ?></th>
                        <th><?= te('Standort') ?></th>
                        <th><?= te('Typ') ?></th>
                        <th><?= te('Besitzer') ?></th>
                        <th><?= te('Erkannt am') ?></th>
                        <th><?= te('Nächste Prüfung') ?></th>
                        <th><?= te('Status') ?></th>
                        <th><?= te('Aktionen') ?></th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($shares)): ?>
                    <tr><td colspan="8" class="text-center text-muted py-4">
                        <i class="bi bi-inbox me-2"></i><?= te('Keine Freigaben gefunden.') ?>
                        <?php if (!$statusFilter): ?>
                            <?= te('Führen Sie zuerst einen Scan durch.') ?>
                        <?php endif; ?>
                    </td></tr>
                <?php else: ?>
                <?php foreach ($shares as $s):
                    $statusClass = match($s['status']) {
                        'active'         => 'badge-success',
                        'pending_review' => 'badge-warning',
                        'confirmed'      => 'badge-info',
                        'revoked'        => 'badge-secondary',
                        default          => '',
                    };
                    $statusLabel = match($s['status']) {
                        'active'         => t('Aktiv'),
                        'pending_review' => t('Prüfung läuft'),
                        'confirmed'      => t('Bestätigt'),
                        'revoked'        => t('Widerrufen'),
                        default          => $e($s['status']),
                    };
                    $scopeLabel = match($s['share_scope']) {
                        'anonymous'    => '<span class="badge bg-danger">🌐 ' . te('Öffentlich') . '</span>',
                        'users'        => '<span class="badge bg-warning text-dark">👥 ' . te('Extern') . '</span>',
                        'organization' => '<span class="badge bg-info text-dark">🏢 ' . te('Org') . '</span>',
                        default        => $e($s['share_scope']),
                    };
                    $isOverdue = $s['auto_revoke_at'] && strtotime($s['auto_revoke_at']) < time() && $s['status'] === 'pending_review';
                ?>
                <tr class="<?= $isOverdue ? 'table-danger' : '' ?>">
                    <td>
                        <?php if ($s['item_url']): ?>
                            <a href="<?= $e($s['item_url']) ?>" target="_blank" class="text-primary fw-medium text-decoration-none">
                                <?= $e($s['item_name'] ?: '—') ?>
                                <i class="bi bi-box-arrow-up-right ms-1" style="font-size:11px;"></i>
                            </a>
                        <?php else: ?>
                            <?= $e($s['item_name'] ?: '—') ?>
                        <?php endif; ?>
                        <?php if ($s['last_review_reason']): ?>
                            <div class="text-muted" style="font-size:11px;" title="<?= te('Begründung:') ?> <?= $e($s['last_review_reason']) ?>">
                                <i class="bi bi-chat-left-text me-1"></i><?= mb_strimwidth($e($s['last_review_reason']), 0, 50, '…') ?>
                            </div>
                        <?php endif; ?>
                    </td>
                    <td class="text-muted" style="font-size:13px;"><?= $e($s['site_name'] ?: '—') ?></td>
                    <td><?= $scopeLabel ?></td>
                    <td style="font-size:13px;">
                        <?= $e($s['owner_display_name'] ?: $s['owner_upn'] ?: '—') ?>
                        <?php if ($s['owner_email'] && $s['owner_email'] !== $s['owner_display_name']): ?>
                            <div class="text-muted" style="font-size:11px;"><?= $e($s['owner_email']) ?></div>
                        <?php endif; ?>
                    </td>
                    <td class="text-muted" style="font-size:13px;"><?= $s['first_detected'] ? date('d.m.Y', strtotime($s['first_detected'])) : '—' ?></td>
                    <td style="font-size:13px;">
                        <?php if ($s['next_review_at']): ?>
                            <?php $isPast = strtotime($s['next_review_at']) < time(); ?>
                            <span class="<?= $isPast ? 'text-danger fw-semibold' : 'text-muted' ?>">
                                <?= date('d.m.Y', strtotime($s['next_review_at'])) ?>
                            </span>
                            <?php if ($s['auto_revoke_at']): ?>
                                <div class="text-danger" style="font-size:11px;">
                                    <i class="bi bi-clock-history me-1"></i><?= te('Widerruf:') ?> <?= date('d.m.Y', strtotime($s['auto_revoke_at'])) ?>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <span class="text-muted">—</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="badge-pill <?= $statusClass ?>"><?= $statusLabel ?></span>
                        <?php if ($isOverdue): ?>
                            <span class="badge bg-danger ms-1" title="<?= te('Widerruf überfällig') ?>">!</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if (!in_array($s['status'], ['revoked'])): ?>
                        <div class="d-flex gap-1">
                            <?php if ($s['owner_email'] && !in_array($s['status'], ['revoked'])): ?>
                            <form method="post" action="/sharing/monitor/remind/<?= (int)$s['id'] ?>">
                                <?= \App\Core\Csrf::field() ?>
                                <button type="submit" class="btn btn-sm btn-outline-warning" title="<?= te('Erinnerung senden') ?>">
                                    <i class="bi bi-envelope"></i>
                                </button>
                            </form>
                            <?php endif; ?>
                            <form method="post" action="/sharing/monitor/revoke/<?= (int)$s['id'] ?>"
                                  onsubmit="return confirm(<?= htmlspecialchars(json_encode(t('Freigabe wirklich widerrufen?'), JSON_UNESCAPED_UNICODE), ENT_QUOTES) ?>)">
                                <?= \App\Core\Csrf::field() ?>
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="<?= te('Freigabe widerrufen') ?>">
                                    <i class="bi bi-x-circle"></i>
                                </button>
                            </form>
                        </div>
                        <?php else: ?>
                            <span class="text-muted" style="font-size:12px;"><?= te('Widerrufen') ?></span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
// Simple live search
document.addEventListener('DOMContentLoaded', function () {
    const inp = document.createElement('input');
    inp.type = 'search';
    inp.placeholder = <?= json_encode(t('Suche …'), JSON_UNESCAPED_UNICODE) ?>;
    inp.className = 'form-control form-control-sm';
    inp.style.maxWidth = '260px';
    document.querySelector('#shareTable').closest('.content-card').querySelector('.card-header-custom').appendChild(inp);

    inp.addEventListener('input', function () {
        const q = this.value.toLowerCase();
        document.querySelectorAll('#shareTable tbody tr').forEach(tr => {
            tr.style.display = tr.textContent.toLowerCase().includes(q) ? '' : 'none';
        });
    });
});
</script>
