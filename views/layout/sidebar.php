<?php
use App\Auth\LocalAuth;
use App\Core\Navigation;

$currentPath = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');

// Nav definitions live in the central Navigation class (shared with /overview).
$_allNavRoutes = Navigation::routes();

// ── Helpers ─────────────────────────────────────────────────────────────────

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

function routeIsActive(string $route, string $current, array $allRoutes): bool {
    if ($route === '') return $current === '';
    if ($current !== $route && !str_starts_with($current, $route . '/')) return false;
    foreach ($allRoutes as $r) {
        if ($r !== $route && str_starts_with($r, $route . '/') &&
            ($current === $r || str_starts_with($current, $r . '/'))) {
            return false;
        }
    }
    return true;
}

// ── Build groups (from the shared Navigation source) ─────────────────────────
$isAdmin = LocalAuth::isAdmin();
$groups  = Navigation::groups($isAdmin);

// ── Dashboard + Übersicht (always visible, outside accordion) ────────────────
?>
<?php navItem('speedometer2', 'Dashboard', '', $currentPath, $_allNavRoutes); ?>
<?php navItem('grid-1x2', 'Modul-Übersicht', 'overview', $currentPath, $_allNavRoutes); ?>

<?php
// ── Render accordion groups ───────────────────────────────────────────────────
foreach ($groups as $gi => $group):
    $groupId  = 'sg' . $gi;
    $hasActive = false;
    foreach ($group['items'] as $item) {
        if (routeIsActive($item['route'], $currentPath, $_allNavRoutes)) {
            $hasActive = true;
            break;
        }
    }
    $classes = 'sidebar-group' . ($hasActive ? ' has-active' : '');
?>
<div class="<?= $classes ?>" data-group="<?= $groupId ?>">
    <div class="sidebar-section-toggle" onclick="sidebarGroupToggle(this)" title="<?= htmlspecialchars($group['name']) ?>">
        <span class="nav-group-label"><?= htmlspecialchars($group['name']) ?></span>
        <i class="bi bi-chevron-down sidebar-chevron"></i>
    </div>
    <div class="sidebar-group-items">
        <?php foreach ($group['items'] as $item): ?>
            <?php navItem($item['icon'], $item['label'], $item['route'], $currentPath, $_allNavRoutes); ?>
        <?php endforeach; ?>
    </div>
</div>
<?php endforeach; ?>

<!-- Handbuch — standalone below accordion -->
<?php navItem('book', 'Handbuch', 'manual', $currentPath, $_allNavRoutes); ?>

<script>
(function () {
    function loadState() {
        try { return JSON.parse(localStorage.getItem('sb_groups') || '{}'); } catch(e) { return {}; }
    }
    function saveState() {
        const s = {};
        document.querySelectorAll('.sidebar-group[data-group]').forEach(function(g) {
            s[g.dataset.group] = g.classList.contains('open');
        });
        try { localStorage.setItem('sb_groups', JSON.stringify(s)); } catch(e) {}
    }

    // Apply initial state: active group always open; others per localStorage (default closed)
    const saved = loadState();
    document.querySelectorAll('.sidebar-group[data-group]').forEach(function(g) {
        if (g.classList.contains('has-active')) {
            g.classList.add('open');
        } else if (saved[g.dataset.group] === true) {
            g.classList.add('open');
        }
    });

    window.sidebarGroupToggle = function(btn) {
        btn.closest('.sidebar-group').classList.toggle('open');
        saveState();
    };
})();
</script>
