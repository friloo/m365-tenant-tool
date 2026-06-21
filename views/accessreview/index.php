<?php use App\Core\View; $e = fn($v) => View::escape($v); ?>

<?php if (!empty($flash)): ?>
    <div class="alert alert-success alert-dismissible mb-3"><i class="bi bi-check-circle me-2"></i><?= $e($flash) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>
<?php if (!empty($error)): ?>
    <div class="alert alert-danger alert-dismissible mb-3"><i class="bi bi-exclamation-triangle me-2"></i><?= $e($error) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<!-- Metric Cards -->
<div class="row g-3 mb-4">
    <div class="col-sm-4">
        <div class="metric-card">
            <div class="metric-label"><?= te('Offene Prüfungen') ?></div>
            <div class="metric-value" style="color:<?= $openCount > 0 ? '#d97706' : '#16a34a' ?>">
                <?= (int)$openCount ?>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="metric-card">
            <div class="metric-label"><?= te('Prüfungen gesamt') ?></div>
            <div class="metric-value"><?= count($reviews) ?></div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="metric-card">
            <div class="metric-label"><?= te('Gäste (letzte Prüfung)') ?></div>
            <div class="metric-value"><?= (int)$totalGuests ?></div>
        </div>
    </div>
</div>

<!-- Reviews Table -->
<div class="content-card">
    <div class="table-toolbar">
        <h2 class="card-title mb-0" style="font-size:15px;font-weight:600;"><?= te('Zugriffsprüfungen') ?></h2>
        <button class="btn btn-sm btn-primary ms-auto" data-bs-toggle="modal" data-bs-target="#newReviewModal">
            <i class="bi bi-plus-circle me-1"></i> <?= te('Neue Prüfung starten') ?>
        </button>
    </div>
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th><?= te('Titel') ?></th>
                    <th><?= te('Typ') ?></th>
                    <th><?= te('Status') ?></th>
                    <th><?= te('Erstellt am') ?></th>
                    <th><?= te('Erstellt von') ?></th>
                    <th>Items</th>
                    <th><?= te('Ausstehend') ?></th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reviews as $r): ?>
                <tr>
                    <td class="fw-medium"><?= $e($r['title']) ?></td>
                    <td><span class="badge bg-secondary" style="font-size:11px;"><?= $e($r['type']) ?></span></td>
                    <td>
                        <?php if ($r['status'] === 'open'): ?>
                            <span class="badge-warning"><?= te('Offen') ?></span>
                        <?php else: ?>
                            <span class="badge-enabled"><?= te('Abgeschlossen') ?></span>
                        <?php endif; ?>
                    </td>
                    <td style="font-size:12px;color:#6b7280;">
                        <?= $r['created_at'] ? date('d.m.Y H:i', strtotime($r['created_at'])) : '—' ?>
                    </td>
                    <td style="font-size:12px;color:#6b7280;"><?= $e($r['created_by']) ?></td>
                    <td><?= (int)$r['item_count'] ?></td>
                    <td>
                        <?php if ((int)$r['pending_count'] > 0): ?>
                            <span class="badge-warning"><?= (int)$r['pending_count'] ?></span>
                        <?php else: ?>
                            <span class="badge-enabled">0</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="/accessreview/<?= (int)$r['id'] ?>" class="btn btn-xs btn-outline-primary py-0 px-2" style="font-size:11px;">
                            <i class="bi bi-eye"></i> <?= te('Öffnen') ?>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($reviews)): ?>
                    <tr><td colspan="8" class="text-center text-muted py-4"><?= te('Noch keine Prüfungen vorhanden') ?></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- New Review Modal -->
<div class="modal fade" id="newReviewModal" tabindex="-1" aria-labelledby="newReviewModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="/accessreview">
                <?= \App\Core\Csrf::field() ?>
                <div class="modal-header">
                    <h5 class="modal-title" id="newReviewModalLabel"><i class="bi bi-clipboard-check me-2"></i><?= te('Neue Prüfung starten') ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="reviewTitle" class="form-label fw-medium"><?= te('Titel der Prüfung') ?></label>
                        <input type="text" id="reviewTitle" name="title" class="form-control"
                               value="Gastbenutzer-Review <?= date('d.m.Y') ?>"
                               placeholder="Gastbenutzer-Review <?= date('d.m.Y') ?>" required>
                        <div class="form-text"><?= te('Alle aktuellen Gastbenutzer werden als Prüfungseinträge geladen.') ?></div>
                    </div>
                    <div class="alert alert-info py-2 mb-0" style="font-size:13px;">
                        <i class="bi bi-info-circle me-1"></i>
                        <?= te('Dieser Vorgang fragt alle Gastbenutzer live aus Microsoft 365 ab und kann einige Sekunden dauern.') ?>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= te('Abbrechen') ?></button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-play-circle me-1"></i> <?= te('Prüfung starten') ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
