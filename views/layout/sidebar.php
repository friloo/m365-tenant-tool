<?php
use App\Auth\LocalAuth;

$currentPath = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');

// All sidebar routes — pre-declared so each navItem can detect more-specific matches
$_allNavRoutes = [
    '', 'users', 'guestusers', 'groups', 'licenses', 'licenseadvisor',
    'onedrive', 'sharepoint', 'sharing', 'sharing/monitor', 'sharing/policies',
    'security', 'securescore', 'riskysignins', 'devices', 'staleaccounts', 'auditlog',
    'mailboxes', 'appregistrations', 'servicehealth', 'cron', 'settings',
    'mfamethods', 'passwordexpiry', 'defenderalerts', 'securityposture', 'teamsusage',
    'adminroles', 'signinlog', 'adoption',
    'msgcenter', 'mailflow',
    'groups/inactive', 'licenses/expiry',
    'mailboxes/shared', 'mailboxes/external-forwards',
];

function navItem(string $icon, string $label, string $route, string $current): void {
    global $_allNavRoutes;

    if ($route === '') {
        $isMatch = $current === '';
    } else {
        $isMatch = $current === $route || str_starts_with($current, $route . '/');
    }

    if ($isMatch) {
        // Only mark active if no more-specific nav route also matches the current path
        $hasMoreSpecific = false;
        foreach ($_allNavRoutes as $r) {
            if ($r !== $route && str_starts_with($r, $route . '/') &&
                ($current === $r || str_starts_with($current, $r . '/'))) {
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
?>

<div class="sidebar-section">Übersicht</div>
<?php navItem('speedometer2', 'Dashboard', '', $currentPath); ?>

<div class="sidebar-section">Verzeichnis</div>
<?php navItem('people', 'Benutzer', 'users', $currentPath); ?>
<?php navItem('person-badge', 'Gastbenutzer', 'guestusers', $currentPath); ?>
<?php navItem('diagram-3', 'Gruppen & Teams', 'groups', $currentPath); ?>
<?php navItem('award', 'Lizenzen', 'licenses', $currentPath); ?>
<?php navItem('lightbulb', 'Lizenz-Berater', 'licenseadvisor', $currentPath); ?>
<?php navItem('shield-lock', 'MFA-Methoden', 'mfamethods', $currentPath); ?>
<?php navItem('key', 'Passwort-Ablauf', 'passwordexpiry', $currentPath); ?>

<div class="sidebar-section">Speicher & Freigaben</div>
<?php navItem('cloud', 'OneDrive', 'onedrive', $currentPath); ?>
<?php navItem('share', 'SharePoint', 'sharepoint', $currentPath); ?>
<?php navItem('link-45deg', 'Freigaben', 'sharing', $currentPath); ?>
<?php navItem('eye-slash', 'Freigaben-Monitor', 'sharing/monitor', $currentPath); ?>
<?php navItem('sliders', 'Freigaberichtlinien', 'sharing/policies', $currentPath); ?>

<div class="sidebar-section">Exchange & Kommunikation</div>
<?php navItem('envelope', 'Postfächer', 'mailboxes', $currentPath); ?>
<?php navItem('camera-video', 'Teams-Nutzung', 'teamsusage', $currentPath); ?>
<?php navItem('graph-up-arrow', 'Adoptions-Report', 'adoption', $currentPath); ?>
<?php navItem('megaphone', 'Message Center', 'msgcenter', $currentPath); ?>
<?php navItem('arrow-left-right', 'Mail Flow & Schutz', 'mailflow', $currentPath); ?>
<?php navItem('heart-pulse', 'Dienststatus', 'servicehealth', $currentPath); ?>

<div class="sidebar-section">Sicherheit</div>
<?php navItem('shield-check', 'Sicherheit', 'security', $currentPath); ?>
<?php navItem('shield-fill-check', 'Security Posture', 'securityposture', $currentPath); ?>
<?php navItem('bar-chart-line', 'Secure Score', 'securescore', $currentPath); ?>
<?php navItem('bell', 'Defender Alerts', 'defenderalerts', $currentPath); ?>
<?php navItem('exclamation-triangle', 'Risiko-Anmeldungen', 'riskysignins', $currentPath); ?>
<?php navItem('grid-3x3-gap', 'App-Registrierungen', 'appregistrations', $currentPath); ?>
<?php navItem('person-lock', 'Admin-Rollen', 'adminroles', $currentPath); ?>

<div class="sidebar-section">Compliance & Audit</div>
<?php navItem('phone', 'Geräte', 'devices', $currentPath); ?>
<?php navItem('person-x', 'Inaktive Konten', 'staleaccounts', $currentPath); ?>
<?php navItem('clock-history', 'Audit-Log', 'auditlog', $currentPath); ?>
<?php navItem('journal-text', 'Sign-in-Log', 'signinlog', $currentPath); ?>

<?php if (LocalAuth::isAdmin()): ?>
<div class="sidebar-section">Administration</div>
<?php navItem('clock', 'Cron & Automatisierung', 'cron', $currentPath); ?>
<?php navItem('gear', 'Einstellungen', 'settings', $currentPath); ?>
<?php endif; ?>
