<?php use App\Core\View; $e = fn($v) => View::escape($v);
function spFmtBytes(int $bytes): string {
    if ($bytes <= 0) return '0 B';
    $k = 1024; $s = ['B','KB','MB','GB','TB'];
    $i = min(floor(log($bytes,$k)),4);
    return round($bytes/pow($k,$i),1).' '.$s[$i];
}
?>

<div class="mb-3">
    <a href="/sharepoint" class="text-muted text-decoration-none small"><?= te('← Zurück zu SharePoint') ?></a>
</div>

<div class="content-card mb-3">
    <div class="card-body-custom">
        <h5 class="mb-1"><?= $e($site['displayName'] ?? '') ?></h5>
        <a href="<?= $e($site['webUrl'] ?? '') ?>" target="_blank" class="text-muted small text-decoration-none">
            <?= $e($site['webUrl'] ?? '') ?> <i class="bi bi-box-arrow-up-right"></i>
        </a>
        <?php if (!empty($site['description'])): ?>
            <p class="text-muted small mt-2 mb-0"><?= $e($site['description']) ?></p>
        <?php endif; ?>
    </div>
</div>

<div class="content-card">
    <div class="card-header-custom">
        <i class="bi bi-folder2-open text-primary"></i>
        <h6><?= te('Dokumentbibliotheken (:n)', ['n' => count($drives)]) ?></h6>
    </div>
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr><th><?= te('Name') ?></th><th><?= te('Typ') ?></th><th><?= te('Belegt') ?></th><th><?= te('Gesamt') ?></th></tr>
            </thead>
            <tbody>
                <?php foreach ($drives as $d): ?>
                    <tr>
                        <td class="fw-medium"><?= $e($d['name'] ?? '') ?></td>
                        <td style="font-size:12px;color:#6b7280;"><?= $e($d['driveType'] ?? '') ?></td>
                        <td><?= spFmtBytes((int)($d['quota']['used'] ?? 0)) ?></td>
                        <td><?= spFmtBytes((int)($d['quota']['total'] ?? 0)) ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($drives)): ?>
                    <tr><td colspan="4" class="text-center text-muted py-3"><?= te('Keine Bibliotheken') ?></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
