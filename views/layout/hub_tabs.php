<?php
use App\Auth\LocalAuth;
use App\Core\Navigation;

$currentPath = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
$allRoutes   = Navigation::routes();
$tabs        = Navigation::tabsFor($currentPath, LocalAuth::isAdmin());

if ($tabs && count($tabs['items']) >= 2):
?>
<nav class="hub-tabs" aria-label="<?= htmlspecialchars($tabs['label'], ENT_QUOTES) ?>">
    <?php foreach ($tabs['items'] as $item): ?>
        <?php
        $route = $item['route'];
        $base  = explode('#', $route)[0];
        $isActive = $base !== '' && ($currentPath === $base || str_starts_with($currentPath, $base . '/'));
        if ($isActive) {
            // Defer to a more-specific sibling route if one also matches.
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
        <a href="/<?= htmlspecialchars($route, ENT_QUOTES) ?>" class="hub-tab <?= $isActive ? 'active' : '' ?>">
            <i class="bi bi-<?= htmlspecialchars($item['icon'], ENT_QUOTES) ?>"></i>
            <span><?= htmlspecialchars($item['label'], ENT_QUOTES) ?></span>
        </a>
    <?php endforeach; ?>
</nav>
<?php endif; ?>
