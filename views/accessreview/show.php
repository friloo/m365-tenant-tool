<?php use App\Core\View; use App\Auth\LocalAuth; $e = fn($v) => View::escape($v); ?>

<?php if (!empty($flash)): ?>
    <div class="alert alert-success alert-dismissible mb-3"><i class="bi bi-check-circle me-2"></i><?= $e($flash) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>
<?php if (!empty($error)): ?>
    <div class="alert alert-danger alert-dismissible mb-3"><i class="bi bi-exclamation-triangle me-2"></i><?= $e($error) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<?php $isClosed = $review['status'] === 'completed'; ?>
<?php $reviewId = (int)$review['id']; ?>

<!-- Header -->
<div class="d-flex align-items-start justify-content-between mb-4 flex-wrap gap-2">
    <div>
        <h2 style="font-size:18px;font-weight:700;margin-bottom:4px;"><?= $e($review['title']) ?></h2>
        <div style="font-size:13px;color:#6b7280;">
            <i class="bi bi-calendar3 me-1"></i><?= te('Erstellt:') ?> <?= date('d.m.Y H:i', strtotime($review['created_at'])) ?>
            &nbsp;&bull;&nbsp;
            <i class="bi bi-person me-1"></i><?= $e($review['created_by']) ?>
            <?php if ($isClosed && $review['completed_at']): ?>
                &nbsp;&bull;&nbsp;<i class="bi bi-check2-circle me-1"></i><?= te('Abgeschlossen:') ?> <?= date('d.m.Y H:i', strtotime($review['completed_at'])) ?>
            <?php endif; ?>
        </div>
    </div>
    <div>
        <?php if ($isClosed): ?>
            <span class="badge-enabled" style="font-size:13px;padding:5px 12px;"><?= te('Abgeschlossen') ?></span>
        <?php else: ?>
            <span class="badge-warning" style="font-size:13px;padding:5px 12px;"><?= te('Offen') ?></span>
        <?php endif; ?>
    </div>
</div>

<!-- Metric Cards -->
<div class="row g-3 mb-4">
    <div class="col-sm-3">
        <div class="metric-card">
            <div class="metric-label"><?= te('Einträge gesamt') ?></div>
            <div class="metric-value"><?= (int)$review['item_count'] ?></div>
        </div>
    </div>
    <div class="col-sm-3">
        <div class="metric-card">
            <div class="metric-label"><?= te('Ausstehend') ?></div>
            <div class="metric-value" style="color:<?= (int)$review['pending_count'] > 0 ? '#d97706' : '#16a34a' ?>">
                <?= (int)$review['pending_count'] ?>
            </div>
        </div>
    </div>
    <div class="col-sm-3">
        <div class="metric-card">
            <div class="metric-label"><?= te('Genehmigt') ?></div>
            <div class="metric-value" style="color:#16a34a"><?= (int)$review['approve_count'] ?></div>
        </div>
    </div>
    <div class="col-sm-3">
        <div class="metric-card">
            <div class="metric-label"><?= te('Widerrufen') ?></div>
            <div class="metric-value" style="color:<?= (int)$review['revoke_count'] > 0 ? '#dc2626' : '#111827' ?>">
                <?= (int)$review['revoke_count'] ?>
            </div>
        </div>
    </div>
</div>

<!-- Info banner -->
<div class="alert alert-warning py-2 mb-3" style="font-size:13px;">
    <i class="bi bi-exclamation-triangle me-1"></i>
    <strong><?= te('Hinweis:') ?></strong> <?= te('Widerrufen deaktiviert das Konto in Microsoft 365.
    Diese Aktion kann durch einen Administrator wieder rückgängig gemacht werden.') ?>
</div>

<?php if ($isClosed): ?>
    <div class="alert alert-success py-2 mb-3" style="font-size:13px;">
        <i class="bi bi-check2-circle me-1"></i>
        <?= te('Diese Prüfung wurde abgeschlossen. Entscheidungen können nicht mehr geändert werden.') ?>
    </div>
<?php endif; ?>

<!-- Action Buttons -->
<?php if (!$isClosed): ?>
<div class="d-flex gap-2 mb-4 flex-wrap">
    <form method="post" action="/accessreview/<?= $reviewId ?>/bulk" class="mb-0">
        <?= \App\Core\Csrf::field() ?>
        <input type="hidden" name="decision" value="approve">
        <button type="submit" class="btn btn-sm btn-outline-success">
            <i class="bi bi-check-all me-1"></i> <?= te('Alle ausstehenden genehmigen') ?>
        </button>
    </form>
    <form method="post" action="/accessreview/<?= $reviewId ?>/bulk" class="mb-0">
        <?= \App\Core\Csrf::field() ?>
        <input type="hidden" name="decision" value="revoke">
        <button type="submit" class="btn btn-sm btn-outline-danger"
                onclick="return confirm('<?= t('Alle ausstehenden Einträge widerrufen?') ?>')">
            <i class="bi bi-x-circle me-1"></i> <?= te('Alle ausstehenden widerrufen') ?>
        </button>
    </form>
    <?php if (LocalAuth::isAdmin()): ?>
    <form method="post" action="/accessreview/<?= $reviewId ?>/apply" class="mb-0 ms-auto">
        <?= \App\Core\Csrf::field() ?>
        <button type="submit" class="btn btn-sm btn-danger"
                onclick="return confirm('<?= t('Entscheidungen anwenden und Prüfung abschließen?\n\nAlle als „Widerrufen" markierten Konten werden deaktiviert. Diese Aktion kann nicht rückgängig gemacht werden.') ?>')">
            <i class="bi bi-play-fill me-1"></i> <?= te('Entscheidungen anwenden &amp; abschließen') ?>
        </button>
    </form>
    <?php endif; ?>
</div>
<?php endif; ?>

<!-- Filter Buttons -->
<div class="d-flex gap-2 mb-3 flex-wrap">
    <button class="btn btn-sm btn-outline-secondary filter-btn active" data-filter="all">
        <?= te('Alle') ?> <span class="badge bg-secondary ms-1"><?= (int)$review['item_count'] ?></span>
    </button>
    <button class="btn btn-sm btn-outline-warning filter-btn" data-filter="pending">
        <?= te('Ausstehend') ?> <span class="badge bg-warning text-dark ms-1"><?= (int)$review['pending_count'] ?></span>
    </button>
    <button class="btn btn-sm btn-outline-success filter-btn" data-filter="approve">
        <?= te('Genehmigt') ?> <span class="badge bg-success ms-1"><?= (int)$review['approve_count'] ?></span>
    </button>
    <button class="btn btn-sm btn-outline-danger filter-btn" data-filter="revoke">
        <?= te('Widerrufen') ?> <span class="badge bg-danger ms-1"><?= (int)$review['revoke_count'] ?></span>
    </button>
</div>

<!-- Items Table -->
<div class="content-card">
    <div class="table-responsive">
        <table class="data-table" id="reviewTable">
            <thead>
                <tr>
                    <th style="width:40px;"></th>
                    <th><?= te('Name') ?></th>
                    <th>UPN</th>
                    <th><?= te('Letzter Login') ?></th>
                    <th><?= te('Entscheidung') ?></th>
                    <th><?= te('Entschieden von') ?></th>
                    <?php if (!$isClosed): ?>
                    <th style="width:160px;"><?= te('Aktion') ?></th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($review['items'] as $item): ?>
                <?php
                    $initial = mb_strtoupper(mb_substr($item['user_name'] ?: $item['user_upn'], 0, 1));
                    $lastLogin = $item['last_signin']
                        ? date('d.m.Y', strtotime($item['last_signin']))
                        : null;
                    $decision = $item['decision'];
                ?>
                <tr data-decision="<?= $e($decision) ?>">
                    <td>
                        <div style="width:32px;height:32px;border-radius:50%;background:#6366f1;color:#fff;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:600;">
                            <?= $e($initial) ?>
                        </div>
                    </td>
                    <td class="fw-medium"><?= $e($item['user_name']) ?></td>
                    <td style="font-size:12px;color:#6b7280;"><?= $e($item['user_upn']) ?></td>
                    <td style="font-size:12px;">
                        <?php if ($lastLogin): ?>
                            <?= $e($lastLogin) ?>
                            <?php
                                $daysAgo = (int)floor((time() - strtotime($item['last_signin'])) / 86400);
                                if ($daysAgo > 90): ?>
                                <span class="badge-danger ms-1"><?= $daysAgo ?>d</span>
                            <?php endif; ?>
                        <?php else: ?>
                            <span class="badge-warning"><?= te('Nie') ?></span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($decision === 'approve'): ?>
                            <span class="badge-enabled"><?= te('Genehmigt') ?></span>
                        <?php elseif ($decision === 'revoke'): ?>
                            <span class="badge-danger"><?= te('Widerrufen') ?></span>
                        <?php else: ?>
                            <span class="badge-warning"><?= te('Ausstehend') ?></span>
                        <?php endif; ?>
                    </td>
                    <td style="font-size:12px;color:#6b7280;">
                        <?php if ($item['decided_by']): ?>
                            <?= $e($item['decided_by']) ?>
                            <?php if ($item['decided_at']): ?>
                                <br><span style="font-size:11px;"><?= date('d.m.Y H:i', strtotime($item['decided_at'])) ?></span>
                            <?php endif; ?>
                        <?php else: ?>
                            —
                        <?php endif; ?>
                    </td>
                    <?php if (!$isClosed): ?>
                    <td>
                        <div class="d-flex gap-1">
                            <form method="post" action="/accessreview/<?= $reviewId ?>/decide/<?= (int)$item['id'] ?>" class="mb-0">
                                <?= \App\Core\Csrf::field() ?>
                                <input type="hidden" name="decision" value="approve">
                                <button type="submit"
                                        class="btn btn-xs <?= $decision === 'approve' ? 'btn-success' : 'btn-outline-success' ?> py-0 px-2"
                                        style="font-size:11px;" title="<?= te('Genehmigen') ?>">
                                    <i class="bi bi-check"></i>
                                </button>
                            </form>
                            <form method="post" action="/accessreview/<?= $reviewId ?>/decide/<?= (int)$item['id'] ?>" class="mb-0">
                                <?= \App\Core\Csrf::field() ?>
                                <input type="hidden" name="decision" value="revoke">
                                <button type="submit"
                                        class="btn btn-xs <?= $decision === 'revoke' ? 'btn-danger' : 'btn-outline-danger' ?> py-0 px-2"
                                        style="font-size:11px;" title="<?= te('Widerrufen') ?>">
                                    <i class="bi bi-x"></i>
                                </button>
                            </form>
                            <?php if ($decision !== 'pending'): ?>
                            <form method="post" action="/accessreview/<?= $reviewId ?>/decide/<?= (int)$item['id'] ?>" class="mb-0">
                                <?= \App\Core\Csrf::field() ?>
                                <input type="hidden" name="decision" value="pending">
                                <button type="submit"
                                        class="btn btn-xs btn-outline-secondary py-0 px-2"
                                        style="font-size:11px;" title="<?= te('Zurücksetzen') ?>">
                                    <i class="bi bi-arrow-counterclockwise"></i>
                                </button>
                            </form>
                            <?php endif; ?>
                        </div>
                    </td>
                    <?php endif; ?>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($review['items'])): ?>
                    <tr><td colspan="7" class="text-center text-muted py-4"><?= te('Keine Einträge vorhanden') ?></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
document.querySelectorAll('.filter-btn').forEach(btn => {
    btn.addEventListener('click', function () {
        document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        const filter = this.dataset.filter;
        document.querySelectorAll('#reviewTable tbody tr').forEach(row => {
            if (filter === 'all' || row.dataset.decision === filter) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });
});
</script>
