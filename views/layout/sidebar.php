<?php
use App\Auth\LocalAuth;

$currentPath = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');

// ── Nav definitions ─────────────────────────────────────────────────────────
// 'section' => non-null opens a new collapsible group with that label.
// 'admin'   => true restricts the item to admins.
// 'icon'    => null marks virtual routes (never rendered, only for active-state).
$_navDefs = [

    // ── Identität & Zugriff
    ['section' => 'Identität & Zugriff', 'icon' => 'people',              'label' => 'Benutzer',               'route' => 'users',                      'admin' => false],
    ['section' => null,                  'icon' => 'person-badge',         'label' => 'Gastbenutzer',           'route' => 'guestusers',                 'admin' => false],
    ['section' => null,                  'icon' => 'diagram-3',            'label' => 'Gruppen & Teams',        'route' => 'groups',                     'admin' => false],
    ['section' => null,                  'icon' => 'person-plus',          'label' => 'Onboarding',             'route' => 'onboarding',                 'admin' => false],
    ['section' => null,                  'icon' => 'person-dash',          'label' => 'Offboarding',            'route' => 'offboarding',                'admin' => false],
    ['section' => null,                  'icon' => 'shield-lock',          'label' => 'MFA-Methoden',           'route' => 'mfamethods',                 'admin' => false],
    ['section' => null,                  'icon' => 'key',                  'label' => 'Passwort-Ablauf',        'route' => 'passwordexpiry',             'admin' => false],
    ['section' => null,                  'icon' => 'shield-shaded',        'label' => 'Conditional Access',     'route' => 'conditionalaccess',          'admin' => false],
    ['section' => null,                  'icon' => 'geo-alt',              'label' => 'Named Locations',        'route' => 'namedlocations',             'admin' => false],
    ['section' => null,                  'icon' => 'person-lock',          'label' => 'Admin-Rollen',           'route' => 'adminroles',                 'admin' => false],

    // ── Lizenzen
    ['section' => 'Lizenzen',            'icon' => 'award',                'label' => 'Lizenzen',               'route' => 'licenses',                   'admin' => false],
    ['section' => null,                  'icon' => 'lightbulb',            'label' => 'Lizenz-Berater',         'route' => 'licenseadvisor',             'admin' => false],

    // ── E-Mail & Exchange
    ['section' => 'E-Mail & Exchange',   'icon' => 'envelope',             'label' => 'Postfächer',             'route' => 'mailboxes',                  'admin' => false],
    ['section' => null,                  'icon' => 'forward-fill',         'label' => 'Externe Weiterleitungen','route' => 'mailboxes/external-forwards','admin' => false],
    ['section' => null,                  'icon' => 'airplane',             'label' => 'EXO Migration',          'route' => 'exchangemigration',          'admin' => false],
    ['section' => null,                  'icon' => 'arrow-left-right',     'label' => 'Mail Flow & Schutz',     'route' => 'mailflow',                   'admin' => false],
    ['section' => null,                  'icon' => 'megaphone',            'label' => 'Message Center',         'route' => 'msgcenter',                  'admin' => false],

    // ── Teams & Zusammenarbeit
    ['section' => 'Teams & Zusammenarbeit', 'icon' => 'collection',        'label' => 'Teams-Übersicht',        'route' => 'teamspolicies',              'admin' => false],
    ['section' => null,                  'icon' => 'camera-video',         'label' => 'Teams-Nutzung',          'route' => 'teamsusage',                 'admin' => false],
    ['section' => null,                  'icon' => 'people-fill',          'label' => 'Teams Governance',       'route' => 'teamsgovernance',            'admin' => false],
    ['section' => null,                  'icon' => 'cloud',                'label' => 'OneDrive',               'route' => 'onedrive',                   'admin' => false],
    ['section' => null,                  'icon' => 'share',                'label' => 'SharePoint',             'route' => 'sharepoint',                 'admin' => false],
    ['section' => null,                  'icon' => 'link-45deg',           'label' => 'Freigaben',              'route' => 'sharing',                    'admin' => false],
    ['section' => null,                  'icon' => 'eye-slash',            'label' => 'Freigaben-Monitor',      'route' => 'sharing/monitor',            'admin' => false],
    ['section' => null,                  'icon' => 'sliders',              'label' => 'Freigaberichtlinien',    'route' => 'sharing/policies',           'admin' => false],

    // ── Sicherheit
    ['section' => 'Sicherheit',          'icon' => 'shield-check',         'label' => 'Sicherheit',             'route' => 'security',                   'admin' => false],
    ['section' => null,                  'icon' => 'shield-fill-check',    'label' => 'Security Posture',       'route' => 'securityposture',            'admin' => false],
    ['section' => null,                  'icon' => 'file-earmark-lock',    'label' => 'DSGVO-Status',           'route' => 'securityposture#cat-dsgvo-datenschutz', 'admin' => false],
    ['section' => null,                  'icon' => 'sliders2-vertical',    'label' => 'Tenant-Härtung',         'route' => 'hardening',                  'admin' => false],
    ['section' => null,                  'icon' => 'key-fill',             'label' => 'Break-Glass-Accounts',   'route' => 'breakglass',                 'admin' => false],
    ['section' => null,                  'icon' => 'lightning-charge',     'label' => 'PIM (JIT-Admin)',        'route' => 'pim',                        'admin' => false],
    ['section' => null,                  'icon' => 'arrow-right-square',   'label' => 'Auto-Forward-Audit',     'route' => 'mailboxrules',               'admin' => false],
    ['section' => null,                  'icon' => 'app-indicator',        'label' => 'OAuth-App-Audit',        'route' => 'oauthaudit',                 'admin' => false],
    ['section' => null,                  'icon' => 'shield-shaded',        'label' => 'DLP-Vorfälle',           'route' => 'dlpincidents',               'admin' => false],
    ['section' => null,                  'icon' => 'fingerprint',          'label' => 'Auth-Strength',          'route' => 'authstrength',               'admin' => false],
    ['section' => null,                  'icon' => 'database-fill-check',  'label' => 'Backup-Status',          'route' => 'backup',                     'admin' => false],
    ['section' => null,                  'icon' => 'envelope-paper',       'label' => 'Executive-Report',       'route' => 'executivereport',            'admin' => false],
    ['section' => null,                  'icon' => 'shield-slash',         'label' => 'MFA-Fatigue',            'route' => 'mfafatigue',                 'admin' => false],
    ['section' => null,                  'icon' => 'eye-fill',             'label' => 'Insider-Threat',         'route' => 'insiderthreat',              'admin' => false],
    ['section' => null,                  'icon' => 'arrow-left-right',     'label' => 'Cross-Tenant-Access',    'route' => 'crosstenantaccess',          'admin' => false],
    ['section' => null,                  'icon' => 'bar-chart-line',       'label' => 'Secure Score',           'route' => 'securescore',                'admin' => false],
    ['section' => null,                  'icon' => 'bell',                 'label' => 'Defender Alerts',        'route' => 'defenderalerts',             'admin' => false],
    ['section' => null,                  'icon' => 'exclamation-triangle', 'label' => 'Risiko-Anmeldungen',     'route' => 'riskysignins',               'admin' => false],
    ['section' => null,                  'icon' => 'grid-3x3-gap',         'label' => 'App-Registrierungen',    'route' => 'appregistrations',           'admin' => false],
    ['section' => null,                  'icon' => 'globe2',               'label' => 'Domain Health',          'route' => 'domainhealth',               'admin' => false],
    ['section' => null,                  'icon' => 'robot',                'label' => 'KI-Berater',             'route' => 'ai',                         'admin' => false],

    // ── Compliance & Audit
    ['section' => 'Compliance & Audit',  'icon' => 'phone',                'label' => 'Geräte',                 'route' => 'devices',                    'admin' => false],
    ['section' => null,                  'icon' => 'person-x',             'label' => 'Inaktive Konten',        'route' => 'staleaccounts',              'admin' => false],
    ['section' => null,                  'icon' => 'trash2',               'label' => 'Papierkorb',             'route' => 'deletedobjects',             'admin' => false],
    ['section' => null,                  'icon' => 'clipboard-check',      'label' => 'Access Reviews',         'route' => 'accessreview',               'admin' => false],
    ['section' => null,                  'icon' => 'clock-history',        'label' => 'Audit-Log',              'route' => 'auditlog',                   'admin' => false],
    ['section' => null,                  'icon' => 'journal-text',         'label' => 'Sign-in-Log',            'route' => 'signinlog',                  'admin' => false],
    ['section' => null,                  'icon' => 'shield-lock',          'label' => 'DLP-Richtlinien',        'route' => 'dlppolicies',                'admin' => false],
    ['section' => null,                  'icon' => 'archive',              'label' => 'Aufbewahrung',           'route' => 'retentionpolicies',          'admin' => false],
    ['section' => null,                  'icon' => 'tags',                 'label' => 'Sensitivity Labels',     'route' => 'sensitivitylabels',          'admin' => false],

    // ── Berichte & Monitoring
    ['section' => 'Berichte & Monitoring','icon' => 'bar-chart-steps',     'label' => 'Nutzungsberichte',       'route' => 'usagereports',               'admin' => false],
    ['section' => null,                  'icon' => 'graph-up-arrow',       'label' => 'Adoptions-Report',       'route' => 'adoption',                   'admin' => false],
    ['section' => null,                  'icon' => 'heart-pulse',          'label' => 'Dienststatus',           'route' => 'servicehealth',              'admin' => false],

    // ── Administration (admin-only)
    ['section' => 'Administration',      'icon' => 'clock',                'label' => 'Cron & Automatisierung', 'route' => 'cron',                       'admin' => true],
    ['section' => null,                  'icon' => 'gear',                 'label' => 'Einstellungen',          'route' => 'settings',                   'admin' => true],
    ['section' => null,                  'icon' => 'people-fill',          'label' => 'Benutzer-Zugang',        'route' => 'settings/users',             'admin' => true],
    ['section' => null,                  'icon' => 'cloud-arrow-down',     'label' => 'Updates',                'route' => 'settings/update',            'admin' => true],
    ['section' => null,                  'icon' => 'journal-check',        'label' => 'App Audit-Log',          'route' => 'settings/app-audit',         'admin' => true],
    ['section' => null,                  'icon' => 'shield-lock',          'label' => '2FA-Einstellungen',      'route' => 'settings/2fa',               'admin' => true],

    // ── Virtual routes (not rendered; needed for active-state resolution only)
    ['section' => null, 'icon' => null, 'label' => null, 'route' => 'onedrive/personal',         'admin' => false],
    ['section' => null, 'icon' => null, 'label' => null, 'route' => 'groups/inactive',           'admin' => false],
    ['section' => null, 'icon' => null, 'label' => null, 'route' => 'licenses/expiry',           'admin' => false],
    ['section' => null, 'icon' => null, 'label' => null, 'route' => 'mailboxes/shared',          'admin' => false],
    ['section' => null, 'icon' => null, 'label' => null, 'route' => 'settings/permissions',      'admin' => true],
    ['section' => null, 'icon' => null, 'label' => null, 'route' => 'settings/license-prices',   'admin' => true],
    ['section' => null, 'icon' => null, 'label' => null, 'route' => 'settings/2fa',              'admin' => true],
];

$_allNavRoutes = array_column($_navDefs, 'route');

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

// ── Build groups ─────────────────────────────────────────────────────────────
$isAdmin = LocalAuth::isAdmin();
$groups  = [];
$cur     = null;

foreach ($_navDefs as $item) {
    if ($item['icon'] === null) continue;          // virtual route
    if ($item['admin'] && !$isAdmin) continue;     // admin-only

    if ($item['section'] !== null) {
        if ($cur !== null) $groups[] = $cur;
        $cur = ['name' => $item['section'], 'admin' => $item['admin'], 'items' => []];
    }
    if ($cur !== null) $cur['items'][] = $item;
}
if ($cur !== null) $groups[] = $cur;

// ── Dashboard (always visible, outside accordion) ────────────────────────────
?>
<?php navItem('speedometer2', 'Dashboard', '', $currentPath, $_allNavRoutes); ?>

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
