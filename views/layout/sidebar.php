<?php
$currentPath = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');

function navItem(string $icon, string $label, string $route, string $current): void {
    $active = ($current === $route || ($route === '' && $current === '')) ? 'active' : '';
    echo "<a href=\"/{$route}\" class=\"nav-item {$active}\" data-route=\"{$route}\">
            <span class=\"nav-icon\"><i class=\"bi bi-{$icon}\"></i></span>
            <span class=\"nav-label\">{$label}</span>
          </a>";
}
?>

<div class="sidebar-section">Übersicht</div>
<?php navItem('speedometer2', 'Dashboard', '', $currentPath); ?>

<div class="sidebar-section">Verzeichnis</div>
<?php navItem('people', 'Benutzer', 'users', $currentPath); ?>
<?php navItem('diagram-3', 'Gruppen & Teams', 'groups', $currentPath); ?>
<?php navItem('award', 'Lizenzen', 'licenses', $currentPath); ?>

<div class="sidebar-section">Speicher & Freigaben</div>
<?php navItem('cloud', 'OneDrive', 'onedrive', $currentPath); ?>
<?php navItem('share', 'SharePoint', 'sharepoint', $currentPath); ?>
<?php navItem('link-45deg', 'Freigaben', 'sharing', $currentPath); ?>

<div class="sidebar-section">Sicherheit</div>
<?php navItem('shield-check', 'Sicherheit', 'security', $currentPath); ?>
<?php navItem('phone', 'Geräte', 'devices', $currentPath); ?>
