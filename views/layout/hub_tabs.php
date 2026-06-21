<?php
use App\Auth\LocalAuth;
use App\Core\Navigation;

$currentPath = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
$allRoutes   = Navigation::routes();
$tabs        = Navigation::tabsFor($currentPath, LocalAuth::isAdmin());

if ($tabs && count($tabs['items']) >= 2):
?>
<nav class="hub-tabs" aria-label="<?= htmlspecialchars($tabs['label'], ENT_QUOTES) ?>">
    <div class="hub-tabs-list">
        <?php foreach ($tabs['items'] as $i => $item): ?>
            <?php
            $route = $item['route'];
            $base  = explode('#', $route)[0];
            $isActive = $base !== '' && ($currentPath === $base || str_starts_with($currentPath, $base . '/'));
            if ($isActive) {
                foreach ($allRoutes as $r) {
                    $rb = explode('#', $r)[0];
                    if ($rb !== '' && $rb !== $base
                        && str_starts_with($rb, $base . '/')
                        && ($currentPath === $rb || str_starts_with($currentPath, $rb . '/'))) {
                        $isActive = false;
                        break;
                    }
                }
            }
            ?>
            <a href="/<?= htmlspecialchars($route, ENT_QUOTES) ?>" class="hub-tab <?= $isActive ? 'active' : '' ?>" data-order="<?= $i ?>"<?= !empty($item['advanced']) ? ' data-advanced="1"' : '' ?>>
                <i class="bi bi-<?= htmlspecialchars($item['icon'], ENT_QUOTES) ?>"></i>
                <span><?= htmlspecialchars($item['label'], ENT_QUOTES) ?></span>
            </a>
        <?php endforeach; ?>
    </div>
    <div class="hub-tabs-more" style="display:none;">
        <button type="button" class="hub-tab hub-more-btn" aria-label="<?= te('Weitere Module') ?>">
            <i class="bi bi-three-dots"></i><span><?= te('Mehr') ?></span><i class="bi bi-chevron-down hub-more-caret"></i>
        </button>
        <div class="hub-more-menu" role="menu"></div>
    </div>
</nav>
<?php endif; ?>
