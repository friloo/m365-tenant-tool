<?php use App\Core\View; use App\Core\Csrf; $e = fn($v) => View::escape($v); ?>

<?php $flash = \App\Core\Session::getFlash('success'); $err = \App\Core\Session::getFlash('error'); ?>
<?php if ($flash): ?><div class="alert alert-success"><?= $e($flash) ?></div><?php endif; ?>
<?php if ($err): ?><div class="alert alert-danger"><?= $e($err) ?></div><?php endif; ?>

<?php if (!$enabled): ?>
    <div class="alert alert-secondary d-flex gap-2">
        <i class="bi bi-info-circle mt-1"></i>
        <div>
            <strong><?= te('Vier-Augen-Prinzip ist deaktiviert.') ?></strong>
            <?= te('Kritische Aktionen werden derzeit sofort ausgeführt. Aktiviere das Vier-Augen-Prinzip unter Einstellungen → Datenschutz, damit sie eine Freigabe durch einen zweiten Administrator erfordern.') ?>
            <a href="/settings#datenschutz"><?= te('Zu den Einstellungen') ?></a>
        </div>
    </div>
<?php endif; ?>

<div class="content-card mb-3">
    <div class="card-header-custom">
        <i class="bi bi-hourglass-split text-primary"></i>
        <h6><?= te('Offene Freigaben') ?></h6>
        <span class="ms-auto text-muted small"><?= count($pending) ?></span>
    </div>
    <div class="card-body-custom">
        <?php if (empty($pending)): ?>
            <div class="text-muted text-center py-4"><?= te('Keine offenen Freigabe-Anfragen.') ?></div>
        <?php else: ?>
            <div class="d-flex flex-column gap-2">
                <?php foreach ($pending as $r): ?>
                    <div class="d-flex align-items-center gap-3 p-3 rounded" style="background:#fafafa;border:1px solid #eef0f3;">
                        <div class="flex-grow-1">
                            <div class="fw-semibold"><?= $e($r['label']) ?></div>
                            <div class="text-muted small mt-1">
                                <?= te('Angefordert von :who am :when', ['who' => $e($r['requested_by']), 'when' => $e($r['requested_at'])]) ?>
                            </div>
                        </div>
                        <form method="post" action="/approvals/approve" class="m-0">
                            <?= Csrf::field() ?>
                            <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                            <button class="btn btn-sm btn-success" type="submit"><i class="bi bi-check2"></i> <?= te('Freigeben') ?></button>
                        </form>
                        <form method="post" action="/approvals/reject" class="m-0"
                              onsubmit="return confirm(<?= $e(json_encode(t('Diese Anfrage ablehnen?'), JSON_UNESCAPED_UNICODE)) ?>);">
                            <?= Csrf::field() ?>
                            <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                            <button class="btn btn-sm btn-outline-danger" type="submit"><i class="bi bi-x"></i> <?= te('Ablehnen') ?></button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="content-card">
    <div class="card-header-custom">
        <i class="bi bi-clock-history text-secondary"></i>
        <h6><?= te('Verlauf') ?></h6>
    </div>
    <div class="card-body-custom">
        <?php if (empty($recent)): ?>
            <div class="text-muted text-center py-4"><?= te('Noch keine Entscheidungen.') ?></div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead><tr>
                        <th><?= te('Aktion') ?></th>
                        <th><?= te('Status') ?></th>
                        <th><?= te('Angefordert von') ?></th>
                        <th><?= te('Entschieden von') ?></th>
                        <th><?= te('Zeitpunkt') ?></th>
                    </tr></thead>
                    <tbody>
                        <?php foreach ($recent as $r): ?>
                            <?php
                            $badge = match ($r['status']) {
                                'approved' => 'bg-success', 'executed' => 'bg-primary',
                                'rejected' => 'bg-danger', default => 'bg-secondary',
                            };
                            $statusLabel = match ($r['status']) {
                                'approved' => t('Freigegeben'), 'executed' => t('Ausgeführt'),
                                'rejected' => t('Abgelehnt'),   default    => t('Ausstehend'),
                            };
                            ?>
                            <tr>
                                <td><?= $e($r['label']) ?></td>
                                <td><span class="badge <?= $badge ?>"><?= $e($statusLabel) ?></span></td>
                                <td class="small"><?= $e($r['requested_by']) ?></td>
                                <td class="small"><?= $e($r['approved_by'] ?? '–') ?></td>
                                <td class="small text-muted"><?= $e($r['decided_at'] ?? '–') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
