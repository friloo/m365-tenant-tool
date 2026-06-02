<?php
/**
 * Reusable sub-navigation tab bar for grouping related modules into one view.
 * Expects:
 *   $tabs = [ ['label' => '…', 'href' => '/…', 'icon' => 'bootstrap-icon'], … ]
 * Active tab is derived from the current request path.
 */
$__cur = '/' . trim(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH), '/');
?>
<div class="module-tabs mb-4">
    <?php foreach (($tabs ?? []) as $__t): ?>
        <?php $__active = ($__cur === ($__t['href'] ?? '')) ? 'active' : ''; ?>
        <a href="<?= htmlspecialchars($__t['href'] ?? '#', ENT_QUOTES) ?>" class="module-tab <?= $__active ?>">
            <i class="bi bi-<?= htmlspecialchars($__t['icon'] ?? 'dot', ENT_QUOTES) ?> me-1"></i><?= htmlspecialchars($__t['label'] ?? '', ENT_QUOTES) ?>
        </a>
    <?php endforeach; ?>
</div>
