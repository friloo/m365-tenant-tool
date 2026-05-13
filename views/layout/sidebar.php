<?php
use App\Auth\LocalAuth;

$currentPath = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');

// ── Nav definitions ────────────────────────────────────────────────────────
// Each entry: ['section' => string|null, 'icon' => string, 'label' => string,
//              'route' => string, 'admin' => bool]
// 'section' => non-null string inserts a section header before this item.
// 'admin'   => true restricts the item (and its section header) to admins.
// $_allNavRoutes is derived automatically — never maintain it by hand.
$_navDefs = [
    // ── Übersicht
    ['section' => 'Übersicht',  'icon' => 'speedometer2',       'label' => 'Dashboard',            'route' => '',                     'admin' => false],

    // ── Verzeichnis
    ['section' => 'Verzeichnis','icon' => 'people',              'label' => 'Benutzer',             'route' => 'users',                'admin' => false],
    ['section' => null,         'icon' => 'person-badge',        'label' => 'Gastbenutzer',         'route' => 'guestusers',           'admin' => false],
    ['section' => null,         'icon' => 'diagram-3',           'label' => 'Gruppen & Teams',      'route' => 'groups',               'admin' => false],
    ['section' => null,         'icon' => 'award',               'label' => 'Lizenzen',             'route' => 'licenses',             'admin' => false],
    // ['section' => null,      'icon' => 'currency-euro',       'label' => 'Lizenzkosten',         'route' => 'licensecosts',         'admin' => false],
    ['section' => null,         'icon' => 'lightbulb',           'label' => 'Lizenz-Berater',       'route' => 'licenseadvisor',       'admin' => false],
    ['section' => null,         'icon' => 'shield-lock',         'label' => 'MFA-Methoden',         'route' => 'mfamethods',           'admin' => false],
    ['section' => null,         'icon' => 'key',                 'label' => 'Passwort-Ablauf',      'route' => 'passwordexpiry',       'admin' => false],
    ['section' => null,         'icon' => 'person-plus',         'label' => 'Onboarding',           'route' => 'onboarding',           'admin' => false],
    ['section' => null,         'icon' => 'person-dash',         'label' => 'Offboarding',          'route' => 'offboarding',          'admin' => false],

    // ── Speicher & Freigaben
    ['section' => 'Speicher & Freigaben', 'icon' => 'cloud',    'label' => 'OneDrive',             'route' => 'onedrive',             'admin' => false],
    ['section' => null,         'icon' => 'share',               'label' => 'SharePoint',           'route' => 'sharepoint',           'admin' => false],
    ['section' => null,         'icon' => 'link-45deg',          'label' => 'Freigaben',            'route' => 'sharing',              'admin' => false],
    ['section' => null,         'icon' => 'eye-slash',           'label' => 'Freigaben-Monitor',    'route' => 'sharing/monitor',      'admin' => false],
    ['section' => null,         'icon' => 'sliders',             'label' => 'Freigaberichtlinien',  'route' => 'sharing/policies',     'admin' => false],

    // ── Exchange & Kommunikation
    ['section' => 'Exchange & Kommunikation', 'icon' => 'envelope',      'label' => 'Postfächer',           'route' => 'mailboxes',                    'admin' => false],
    ['section' => null,         'icon' => 'forward-fill',        'label' => 'Externe Weiterleitungen','route' => 'mailboxes/external-forwards',   'admin' => false],
    ['section' => null,         'icon' => 'airplane',            'label' => 'EXO Migration',        'route' => 'exchangemigration',    'admin' => false],
    ['section' => null,         'icon' => 'camera-video',        'label' => 'Teams-Nutzung',        'route' => 'teamsusage',           'admin' => false],
    ['section' => null,         'icon' => 'collection',          'label' => 'Teams-Übersicht',      'route' => 'teamspolicies',        'admin' => false],
    ['section' => null,         'icon' => 'people-fill',         'label' => 'Teams Governance',     'route' => 'teamsgovernance',      'admin' => false],
    ['section' => null,         'icon' => 'graph-up-arrow',      'label' => 'Adoptions-Report',     'route' => 'adoption',             'admin' => false],
    ['section' => null,         'icon' => 'bar-chart-steps',     'label' => 'Nutzungsberichte',     'route' => 'usagereports',         'admin' => false],
    ['section' => null,         'icon' => 'megaphone',           'label' => 'Message Center',       'route' => 'msgcenter',            'admin' => false],
    ['section' => null,         'icon' => 'arrow-left-right',    'label' => 'Mail Flow & Schutz',   'route' => 'mailflow',             'admin' => false],
    ['section' => null,         'icon' => 'heart-pulse',         'label' => 'Dienststatus',         'route' => 'servicehealth',        'admin' => false],

    // ── Sicherheit
    ['section' => 'Sicherheit', 'icon' => 'shield-check',       'label' => 'Sicherheit',           'route' => 'security',             'admin' => false],
    ['section' => null,         'icon' => 'shield-fill-check',   'label' => 'Security Posture',     'route' => 'securityposture',      'admin' => false],
    ['section' => null,         'icon' => 'bar-chart-line',      'label' => 'Secure Score',         'route' => 'securescore',          'admin' => false],
    ['section' => null,         'icon' => 'bell',                'label' => 'Defender Alerts',      'route' => 'defenderalerts',       'admin' => false],
    ['section' => null,         'icon' => 'exclamation-triangle','label' => 'Risiko-Anmeldungen',   'route' => 'riskysignins',         'admin' => false],
    ['section' => null,         'icon' => 'shield-shaded',       'label' => 'Conditional Access',   'route' => 'conditionalaccess',    'admin' => false],
    ['section' => null,         'icon' => 'geo-alt',             'label' => 'Named Locations',      'route' => 'namedlocations',       'admin' => false],
    ['section' => null,         'icon' => 'tags',                'label' => 'Sensitivity Labels',   'route' => 'sensitivitylabels',    'admin' => false],
    ['section' => null,         'icon' => 'grid-3x3-gap',        'label' => 'App-Registrierungen',  'route' => 'appregistrations',     'admin' => false],
    ['section' => null,         'icon' => 'person-lock',         'label' => 'Admin-Rollen',         'route' => 'adminroles',           'admin' => false],
    ['section' => null,         'icon' => 'globe2',              'label' => 'Domain Health',        'route' => 'domainhealth',         'admin' => false],

    // ── Compliance & Audit
    ['section' => 'Compliance & Audit', 'icon' => 'phone',       'label' => 'Geräte',              'route' => 'devices',              'admin' => false],
    ['section' => null,         'icon' => 'person-x',            'label' => 'Inaktive Konten',      'route' => 'staleaccounts',        'admin' => false],
    ['section' => null,         'icon' => 'clipboard-check',     'label' => 'Access Reviews',       'route' => 'accessreview',         'admin' => false],
    ['section' => null,         'icon' => 'clock-history',       'label' => 'Audit-Log',            'route' => 'auditlog',             'admin' => false],
    ['section' => null,         'icon' => 'journal-text',        'label' => 'Sign-in-Log',          'route' => 'signinlog',            'admin' => false],
    ['section' => null,         'icon' => 'shield-lock',         'label' => 'DLP-Richtlinien',      'route' => 'dlppolicies',          'admin' => false],
    ['section' => null,         'icon' => 'archive',             'label' => 'Aufbewahrung',         'route' => 'retentionpolicies',    'admin' => false],
    ['section' => null,         'icon' => 'trash2',              'label' => 'Papierkorb',           'route' => 'deletedobjects',       'admin' => false],

    // ── Handbuch (no section header)
    ['section' => null,         'icon' => 'book',                'label' => 'Handbuch',             'route' => 'manual',               'admin' => false],

    // ── Administration (admin-only)
    ['section' => 'Administration', 'icon' => 'clock',           'label' => 'Cron & Automatisierung','route' => 'cron',               'admin' => true],
    ['section' => null,         'icon' => 'gear',                'label' => 'Einstellungen',        'route' => 'settings',             'admin' => true],
    ['section' => null,         'icon' => 'people-fill',         'label' => 'Benutzer-Zugang',      'route' => 'settings/users',       'admin' => true],
    ['section' => null,         'icon' => 'cloud-arrow-down',    'label' => 'Updates',              'route' => 'settings/update',      'admin' => true],

    // ── Virtual routes (not rendered, but needed for active-state resolution)
    // Sub-routes that have no nav item of their own but would otherwise cause
    // their parent to stay active incorrectly.
    ['section' => null, 'icon' => null, 'label' => null, 'route' => 'onedrive/personal',            'admin' => false],
    ['section' => null, 'icon' => null, 'label' => null, 'route' => 'groups/inactive',              'admin' => false],
    ['section' => null, 'icon' => null, 'label' => null, 'route' => 'licenses/expiry',              'admin' => false],
    ['section' => null, 'icon' => null, 'label' => null, 'route' => 'mailboxes/shared',             'admin' => false],
    ['section' => null, 'icon' => null, 'label' => null, 'route' => 'settings/permissions',         'admin' => true],
    ['section' => null, 'icon' => null, 'label' => null, 'route' => 'settings/license-prices',       'admin' => true],
];

// Derive the full route list automatically — no manual maintenance needed
$_allNavRoutes = array_column($_navDefs, 'route');

// ── navItem renderer ───────────────────────────────────────────────────────
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

// ── Render ─────────────────────────────────────────────────────────────────
$isAdmin = LocalAuth::isAdmin();

foreach ($_navDefs as $item) {
    if ($item['icon'] === null) continue;
    if ($item['admin'] && !$isAdmin) continue;
    if ($item['section'] !== null) {
        echo '<div class="sidebar-section">' . htmlspecialchars($item['section']) . '</div>';
    }
    navItem($item['icon'], $item['label'], $item['route'], $currentPath, $_allNavRoutes);
}
