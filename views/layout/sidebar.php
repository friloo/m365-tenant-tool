<?php
use App\Auth\LocalAuth;
use App\Core\Navigation;

$currentPath = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
$allRoutes   = Navigation::routes();
$isAdmin     = LocalAuth::isAdmin();

// ── Standalone nav item (Dashboard / Übersicht / Handbuch) ───────────────────
function navItem(string $icon, string $label, string $route, string $current, array $allRoutes): void {
    if ($route === '') {
        $isMatch = $current === '';
    } else {
        $isMatch = $current === $route || str_starts_with($current, $route . '/');
    }
    if ($isMatch) {
        $hasMoreSpecific = false;
        foreach ($allRoutes as $r) {
            if ($r !== $route
                && str_starts_with($r, $route . '/')
                && ($current === $r || str_starts_with($current, $r . '/'))) {
                $hasMoreSpecific = true;
                break;
            }
        }
        $active = $hasMoreSpecific ? '' : 'active';
    } else {
        $active = '';
    }
    echo "<a href=\"/{$route}\" class=\"nav-item {$active}\" data-route=\"{$route}\">
            <span class=\"nav-icon\"><i class=\"bi bi-{$icon}\"></i></span>
            <span class=\"nav-label\">{$label}</span>
          </a>";
}

$activeHub = Navigation::activeHubKey($currentPath);
?>
<?php navItem('speedometer2', 'Dashboard', '', $currentPath, $allRoutes); ?>
<?php navItem('grid-1x2', 'Modul-Übersicht', 'overview', $currentPath, $allRoutes); ?>

<div class="sidebar-hub-label">Bereiche</div>
<?php foreach (Navigation::hubs() as $hub): ?>
    <?php
    $landing = Navigation::hubLandingRoute($hub['key'], $isAdmin);
    if ($landing === '') continue;                 // hub has no items visible to this role
    $active = ($activeHub === $hub['key']) ? 'active' : '';
    ?>
    <a href="/<?= htmlspecialchars($landing, ENT_QUOTES) ?>" class="nav-item <?= $active ?>" data-hub="<?= htmlspecialchars($hub['key'], ENT_QUOTES) ?>">
        <span class="nav-icon"><i class="bi bi-<?= htmlspecialchars($hub['icon'], ENT_QUOTES) ?>"></i></span>
        <span class="nav-label"><?= htmlspecialchars($hub['label'], ENT_QUOTES) ?></span>
    </a>
<?php endforeach; ?>

<div class="sidebar-hub-label">Hilfe</div>
<?php navItem('book', 'Handbuch', 'manual', $currentPath, $allRoutes); ?>
