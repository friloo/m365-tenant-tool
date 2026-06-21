<?php use App\Core\View; $e = fn($v) => View::escape($v); ?>

<style>
.msg-item { border-bottom: 1px solid #e5e7eb; }
.msg-item:last-child { border-bottom: none; }
.msg-header { display:flex; align-items:center; gap:10px; padding:12px 16px; cursor:pointer; }
.msg-header:hover { background:#f9fafb; }
.msg-body { padding:16px; background:#f9fafb; border-top:1px solid #e5e7eb; display:none; }
.msg-body.open { display:block; }
.msg-unread-dot { width:8px; height:8px; background:#0078d4; border-radius:50%; flex-shrink:0; }
.msg-chevron { transition:transform 0.2s; }
.msg-chevron.open { transform:rotate(180deg); }
</style>

<!-- Page subtitle -->
<p class="text-muted mb-4"><?= te('Microsoft 365 Dienstmeldungen und Änderungsankündigungen') ?></p>

<!-- Metric Cards -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="metric-card">
            <div class="metric-label"><i class="bi bi-chat-square-text me-1"></i><?= te('Gesamt') ?></div>
            <div class="metric-value"><?= $e($stats['total']) ?></div>
            <div class="metric-sub"><?= te('Nachrichten') ?></div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="metric-card">
            <div class="metric-label"><i class="bi bi-envelope-open me-1"></i><?= te('Ungelesen') ?></div>
            <div class="metric-value">
                <?php if ($stats['unread'] > 0): ?>
                    <span class="badge-warning badge-pill"><?= $e($stats['unread']) ?></span>
                <?php else: ?>
                    <?= $e($stats['unread']) ?>
                <?php endif; ?>
            </div>
            <div class="metric-sub"><?= te('Ungelesene Nachrichten') ?></div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="metric-card">
            <div class="metric-label"><i class="bi bi-exclamation-diamond me-1"></i>Major Changes</div>
            <div class="metric-value">
                <?php if ($stats['major'] > 0): ?>
                    <span class="badge-danger badge-pill"><?= $e($stats['major']) ?></span>
                <?php else: ?>
                    <?= $e($stats['major']) ?>
                <?php endif; ?>
            </div>
            <div class="metric-sub"><?= te('Wichtige Änderungen') ?></div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="metric-card">
            <div class="metric-label"><i class="bi bi-shield-exclamation me-1"></i><?= te('Kritisch/Hoch') ?></div>
            <div class="metric-value"><?= $e($stats['high_severity']) ?></div>
            <div class="metric-sub"><?= te('Hohe Schwere') ?></div>
        </div>
    </div>
</div>

<!-- Filter Bar -->
<div class="content-card mb-4">
    <div class="card-body-custom">
        <form method="get" action="/msgcenter" class="row g-2 align-items-end">
            <!-- Category -->
            <div class="col-sm-6 col-md-3">
                <label class="form-label small fw-medium mb-1"><?= te('Kategorie') ?></label>
                <select name="category" class="form-select form-select-sm">
                    <option value=""><?= te('Alle Kategorien') ?></option>
                    <?php
                    $categoryLabels = [
                        'stayInformed'      => t('Zur Information'),
                        'planForChange'     => t('Änderung planen'),
                        'preventOrFixIssue' => t('Problem beheben'),
                        'adaptYourWork'     => t('Anpassung erforderlich'),
                    ];
                    foreach ($categories as $cat):
                        $label = $categoryLabels[$cat] ?? $cat;
                        $sel   = ($filters['category'] === $cat) ? 'selected' : '';
                    ?>
                        <option value="<?= $e($cat) ?>" <?= $sel ?>><?= $e($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Severity -->
            <div class="col-sm-6 col-md-2">
                <label class="form-label small fw-medium mb-1"><?= te('Schwere') ?></label>
                <select name="severity" class="form-select form-select-sm">
                    <option value=""><?= te('Alle') ?></option>
                    <?php
                    $severityLabels = [
                        'critical' => t('Kritisch'),
                        'high'     => t('Hoch'),
                        'normal'   => t('Normal'),
                    ];
                    foreach ($severityLabels as $val => $label):
                        $sel = ($filters['severity'] === $val) ? 'selected' : '';
                    ?>
                        <option value="<?= $e($val) ?>" <?= $sel ?>><?= $e($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Service -->
            <div class="col-sm-6 col-md-3">
                <label class="form-label small fw-medium mb-1"><?= te('Dienst') ?></label>
                <select name="service" class="form-select form-select-sm">
                    <option value=""><?= te('Alle Dienste') ?></option>
                    <?php foreach ($services as $svc):
                        $sel = ($filters['service'] === $svc) ? 'selected' : '';
                    ?>
                        <option value="<?= $e($svc) ?>" <?= $sel ?>><?= $e($svc) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Unread checkbox -->
            <div class="col-sm-6 col-md-2 d-flex align-items-end">
                <div class="form-check mb-1">
                    <input class="form-check-input" type="checkbox" name="unread" value="1" id="chkUnread"
                        <?= ($filters['unread'] === '1') ? 'checked' : '' ?>>
                    <label class="form-check-label small" for="chkUnread"><?= te('Nur ungelesene') ?></label>
                </div>
            </div>

            <!-- Buttons -->
            <div class="col-sm-12 col-md-2 d-flex gap-2 align-items-end">
                <button type="submit" class="btn btn-sm btn-primary">
                    <i class="bi bi-funnel me-1"></i><?= te('Filtern') ?>
                </button>
                <a href="/msgcenter" class="btn btn-sm btn-outline-secondary"><?= te('Zurücksetzen') ?></a>
            </div>
        </form>
    </div>
</div>

<!-- Message List -->
<div class="content-card">
    <div class="card-header-custom">
        <i class="bi bi-chat-square-text text-primary"></i>
        <h6><?= te('Nachrichten') ?> (<?= count($messages) ?>)</h6>
        <a href="/msgcenter?refresh=1" class="ms-auto btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-clockwise me-1"></i><?= te('Aktualisieren') ?>
        </a>
    </div>

    <?php if (empty($messages)): ?>
        <div class="card-body-custom">
            <div class="empty-state">
                <i class="bi bi-chat-square-text text-muted" style="font-size:2.5rem;"></i>
                <p class="mt-3 mb-1 fw-medium"><?= te('Keine Nachrichten gefunden') ?></p>
                <p class="text-muted small">
                    <?php if (array_filter($filters)): ?>
                        <?= te('Keine Nachrichten entsprechen den gewählten Filtern.') ?>
                        <a href="/msgcenter"><?= te('Filter zurücksetzen') ?></a>
                    <?php else: ?>
                        <?= te('Es sind keine Message-Center-Nachrichten verfügbar.') ?>
                    <?php endif; ?>
                </p>
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($messages as $message):
            $vp       = $message['viewPoint'] ?? null;
            $isRead   = ($vp !== null) && (($vp['isRead'] ?? false) === true);
            $severity = strtolower($message['severity'] ?? 'normal');
            $sevBadge = match ($severity) {
                'critical' => 'badge-danger',
                'high'     => 'badge-warning',
                'normal'   => 'badge-info',
                default    => 'badge-secondary',
            };
            $sevLabel = match ($severity) {
                'critical' => t('Kritisch'),
                'high'     => t('Hoch'),
                'normal'   => t('Normal'),
                default    => $severity,
            };
            $startDate     = !empty($message['startDateTime'])
                ? date('d.m.Y', strtotime($message['startDateTime']))
                : '–';
            $servicesLabel = implode(', ', $message['services'] ?? []);
        ?>
        <div class="msg-item">
            <div class="msg-header">
                <?php if (!$isRead): ?>
                    <span class="msg-unread-dot" title="<?= te('Ungelesen') ?>"></span>
                <?php else: ?>
                    <span style="width:8px;height:8px;flex-shrink:0;"></span>
                <?php endif; ?>

                <span class="<?= $sevBadge ?>"><?= $e($sevLabel) ?></span>

                <?php if ($message['isMajorChange'] ?? false): ?>
                    <span class="badge-danger">Major Change</span>
                <?php endif; ?>

                <span class="fw-semibold flex-grow-1" style="font-size:14px;min-width:0;">
                    <?= $e($message['title'] ?? '') ?>
                </span>

                <?php if ($servicesLabel !== ''): ?>
                    <span class="text-muted small d-none d-md-inline" style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:200px;" title="<?= $e($servicesLabel) ?>">
                        <?= $e($servicesLabel) ?>
                    </span>
                <?php endif; ?>

                <span class="text-muted small" style="white-space:nowrap;"><?= $e($startDate) ?></span>

                <i class="bi bi-chevron-down msg-chevron text-muted"></i>
            </div>
            <div class="msg-body">
                <div class="msg-body-content mb-3" style="font-size:14px;line-height:1.6;">
                    <?= $message['body']['content'] ?? '' ?>
                </div>
                <a href="https://admin.microsoft.com/Adminportal/Home#/MessageCenter"
                   target="_blank" rel="noopener noreferrer"
                   class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-box-arrow-up-right me-1"></i>Im Admin Center öffnen
                </a>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<script>
document.querySelectorAll('.msg-header').forEach(h => {
    h.addEventListener('click', () => {
        const body = h.nextElementSibling;
        const chev = h.querySelector('.msg-chevron');
        body.classList.toggle('open');
        chev?.classList.toggle('open');
    });
});
</script>
