<?php
use App\Core\View;
?>
<div class="content-card">
    <h1 class="mb-3"><i class="bi bi-bell"></i> Benachrichtigungen <?= \App\Core\Help::tip('notifications') ?></h1>
    <p class="text-muted small mb-4">Alle Ereignisse aus diesem Tenant (jüngste zuerst). Werden 90 Tage aufbewahrt und automatisch durch den Cron getrimmt.</p>

    <?php if (empty($notifications)): ?>
        <div class="alert alert-light text-center text-muted py-5"><i class="bi bi-inbox" style="font-size:36px"></i><br>Keine Benachrichtigungen</div>
    <?php else: ?>
        <div class="list-group">
            <?php foreach ($notifications as $n): ?>
                <?php
                $icon = match ($n['severity']) {
                    'critical' => 'exclamation-triangle-fill',
                    'warn'     => 'exclamation-circle',
                    'success'  => 'check-circle',
                    default    => 'info-circle',
                };
                $cls = 'severity-' . htmlspecialchars($n['severity']);
                ?>
                <div class="notify-item <?= $cls ?>" style="cursor:default;">
                    <div class="notify-item-icon"><i class="bi bi-<?= $icon ?>"></i></div>
                    <div class="notify-item-body">
                        <div class="notify-item-title">
                            <?php if (!empty($n['link'])): ?>
                                <a href="<?= View::escape($n['link']) ?>"><?= View::escape($n['title']) ?></a>
                            <?php else: ?>
                                <?= View::escape($n['title']) ?>
                            <?php endif; ?>
                        </div>
                        <?php if (!empty($n['body'])): ?>
                            <div class="notify-item-text" style="-webkit-line-clamp: unset; display:block;"><?= nl2br(View::escape($n['body'])) ?></div>
                        <?php endif; ?>
                        <div class="notify-item-time"><?= View::escape($n['created_at']) ?> · <?= View::escape($n['category']) ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
