<?php

namespace App\Core;

/**
 * Single source of truth for the application navigation.
 *
 * Two-level model:
 *   - HUBS  → the (short) sidebar list. See hubs().
 *   - ITEMS → individual module pages, each assigned to a hub. Rendered as
 *             horizontal tabs inside the active hub (see tabsFor()).
 *
 * Each item: hub (hub key), icon (null = virtual route, only for active-state
 * resolution), label, route, admin.
 *
 * Backwards-compatible helpers kept for existing consumers:
 *   - routes()        → flat list of every route (sidebar active-state, /overview)
 *   - groups($admin)  → [{name, items[]}] grouped by hub (used by /overview)
 */
class Navigation
{
    /** Ordered top-level hubs shown in the sidebar. */
    public static function hubs(): array
    {
        return [
            ['key' => 'identitaet',     'label' => 'Identität & Konten',       'icon' => 'people'],
            ['key' => 'zugriff',        'label' => 'Zugriff & Privilegien',    'icon' => 'shield-lock'],
            ['key' => 'bedrohungen',    'label' => 'Bedrohungen & Response',   'icon' => 'shield-exclamation'],
            ['key' => 'email',          'label' => 'E-Mail-Sicherheit',        'icon' => 'envelope'],
            ['key' => 'teams',          'label' => 'Teams, Sharing & Speicher','icon' => 'collection'],
            ['key' => 'infoprotection', 'label' => 'Information Protection',    'icon' => 'file-earmark-lock'],
            ['key' => 'haertung',       'label' => 'Härtung & Posture',        'icon' => 'sliders2-vertical'],
            ['key' => 'compliance',     'label' => 'Compliance & Audit',       'icon' => 'clipboard-check'],
            ['key' => 'lizenzen',       'label' => 'Lizenzen & Berichte',      'icon' => 'award'],
            ['key' => 'apps',           'label' => 'Apps & Automatisierung',   'icon' => 'grid-3x3-gap'],
            ['key' => 'administration', 'label' => 'Administration',           'icon' => 'gear'],
        ];
    }

    /**
     * Every module item, in hub order. icon === null marks a virtual route
     * (not rendered, but kept so routes() resolves sub-pages to the right hub).
     */
    public static function defs(): array
    {
        return [
            // ── Identität & Konten ──────────────────────────────────────
            ['hub' => 'identitaet', 'icon' => 'people',         'label' => 'Benutzer',                'route' => 'users',                  'admin' => false],
            ['hub' => 'identitaet', 'icon' => 'person-badge',   'label' => 'Gastbenutzer',            'route' => 'guestusers',             'admin' => false],
            ['hub' => 'identitaet', 'icon' => 'diagram-3',      'label' => 'Gruppen & Teams',         'route' => 'groups',                 'admin' => false],
            ['hub' => 'identitaet', 'icon' => 'person-plus',    'label' => 'Onboarding',              'route' => 'onboarding',             'admin' => false],
            ['hub' => 'identitaet', 'icon' => 'person-dash',    'label' => 'Offboarding',             'route' => 'offboarding',            'admin' => false],
            ['hub' => 'identitaet', 'icon' => 'shield-lock',    'label' => 'MFA-Methoden',            'route' => 'mfamethods',             'admin' => false],
            ['hub' => 'identitaet', 'icon' => 'key',            'label' => 'Passwort-Ablauf',         'route' => 'passwordexpiry',         'admin' => false],
            ['hub' => 'identitaet', 'icon' => 'person-x',       'label' => 'Inaktive Konten',         'route' => 'staleaccounts',          'admin' => false],
            ['hub' => 'identitaet', 'icon' => 'trash2',         'label' => 'Papierkorb',              'route' => 'deletedobjects',         'admin' => false],
            ['hub' => 'identitaet', 'icon' => null, 'label' => null, 'route' => 'groups/inactive',    'admin' => false],

            // ── Zugriff & Privilegien ───────────────────────────────────
            ['hub' => 'zugriff', 'icon' => 'shield-shaded',     'label' => 'Conditional Access',      'route' => 'conditionalaccess',      'admin' => false],
            ['hub' => 'zugriff', 'icon' => 'geo-alt',           'label' => 'Named Locations',         'route' => 'namedlocations',         'admin' => false],
            ['hub' => 'zugriff', 'icon' => 'fingerprint',       'label' => 'Authentifizierungsmethoden','route' => 'authmethods',          'admin' => false],
            ['hub' => 'zugriff', 'icon' => 'patch-check',       'label' => 'Auth-Strength',           'route' => 'authstrength',           'admin' => false],
            ['hub' => 'zugriff', 'icon' => 'clock-history',     'label' => 'Token-Lifetime',          'route' => 'tokenlifetime',          'admin' => false],
            ['hub' => 'zugriff', 'icon' => 'arrow-left-right',  'label' => 'Cross-Tenant-Access',     'route' => 'crosstenantaccess',      'admin' => false],
            ['hub' => 'zugriff', 'icon' => 'person-bounding-box','label' => 'Identity Provider Trust','route' => 'identityproviders',      'admin' => false],
            ['hub' => 'zugriff', 'icon' => 'person-lock',       'label' => 'Admin-Rollen',            'route' => 'adminroles',             'admin' => false],
            ['hub' => 'zugriff', 'icon' => 'lightning-charge',  'label' => 'PIM (JIT-Admin)',         'route' => 'pim',                    'admin' => false],
            ['hub' => 'zugriff', 'icon' => 'sliders',           'label' => 'PIM-Einstellungen',       'route' => 'pimsettings',            'admin' => false],
            ['hub' => 'zugriff', 'icon' => 'key-fill',          'label' => 'Break-Glass-Accounts',    'route' => 'breakglass',             'admin' => false],

            // ── Bedrohungen & Response ──────────────────────────────────
            ['hub' => 'bedrohungen', 'icon' => 'shield-check',       'label' => 'Sicherheit',          'route' => 'security',          'admin' => false],
            ['hub' => 'bedrohungen', 'icon' => 'bar-chart-line',     'label' => 'Secure Score',        'route' => 'securescore',       'admin' => false],
            ['hub' => 'bedrohungen', 'icon' => 'bell',               'label' => 'Defender Alerts',     'route' => 'defenderalerts',    'admin' => false],
            ['hub' => 'bedrohungen', 'icon' => 'exclamation-triangle','label' => 'Risiko-Anmeldungen', 'route' => 'riskysignins',      'admin' => false],
            ['hub' => 'bedrohungen', 'icon' => 'shield-slash',       'label' => 'MFA-Fatigue',         'route' => 'mfafatigue',        'admin' => false],
            ['hub' => 'bedrohungen', 'icon' => 'eye-fill',           'label' => 'Insider-Threat',      'route' => 'insiderthreat',     'admin' => false],
            ['hub' => 'bedrohungen', 'icon' => 'bullseye',           'label' => 'Phishing-Simulationen','route' => 'phishingsim',      'admin' => false],
            ['hub' => 'bedrohungen', 'icon' => 'app-indicator',      'label' => 'OAuth-App-Audit',     'route' => 'oauthaudit',        'admin' => false],
            ['hub' => 'bedrohungen', 'icon' => 'arrow-right-square', 'label' => 'Auto-Forward-Audit',  'route' => 'mailboxrules',      'admin' => false],

            // ── E-Mail-Sicherheit ───────────────────────────────────────
            ['hub' => 'email', 'icon' => 'envelope',        'label' => 'Postfächer',             'route' => 'mailboxes',                   'admin' => false],
            ['hub' => 'email', 'icon' => 'forward-fill',    'label' => 'Externe Weiterleitungen','route' => 'mailboxes/external-forwards', 'admin' => false],
            ['hub' => 'email', 'icon' => 'arrow-left-right','label' => 'Mail Flow & Schutz',     'route' => 'mailflow',                    'admin' => false],
            ['hub' => 'email', 'icon' => 'globe2',          'label' => 'Domain Health',          'route' => 'domainhealth',                'admin' => false],
            ['hub' => 'email', 'icon' => 'airplane',        'label' => 'EXO Migration',          'route' => 'exchangemigration',           'admin' => false],
            ['hub' => 'email', 'icon' => 'megaphone',       'label' => 'Message Center',         'route' => 'msgcenter',                   'admin' => false],
            ['hub' => 'email', 'icon' => null, 'label' => null, 'route' => 'mailboxes/shared',    'admin' => false],

            // ── Teams, Sharing & Speicher ───────────────────────────────
            ['hub' => 'teams', 'icon' => 'collection',   'label' => 'Teams-Übersicht',  'route' => 'teamspolicies',   'admin' => false],
            ['hub' => 'teams', 'icon' => 'camera-video', 'label' => 'Teams-Nutzung',    'route' => 'teamsusage',      'admin' => false],
            ['hub' => 'teams', 'icon' => 'people-fill',  'label' => 'Teams Governance', 'route' => 'teamsgovernance', 'admin' => false],
            ['hub' => 'teams', 'icon' => 'cloud',        'label' => 'OneDrive',         'route' => 'onedrive',        'admin' => false],
            ['hub' => 'teams', 'icon' => 'share',        'label' => 'SharePoint',       'route' => 'sharepoint',      'admin' => false],
            ['hub' => 'teams', 'icon' => 'link-45deg',   'label' => 'Freigaben',        'route' => 'sharing',         'admin' => false],
            ['hub' => 'teams', 'icon' => null, 'label' => null, 'route' => 'sharing/monitor',  'admin' => false],
            ['hub' => 'teams', 'icon' => null, 'label' => null, 'route' => 'sharing/policies', 'admin' => false],
            ['hub' => 'teams', 'icon' => null, 'label' => null, 'route' => 'onedrive/personal','admin' => false],

            // ── Information Protection ───────────────────────────────────
            ['hub' => 'infoprotection', 'icon' => 'tags',           'label' => 'Sensitivity Labels',     'route' => 'sensitivitylabels', 'admin' => false],
            ['hub' => 'infoprotection', 'icon' => 'file-earmark-x', 'label' => 'DLP-Richtlinien',        'route' => 'dlppolicies',       'admin' => false],
            ['hub' => 'infoprotection', 'icon' => 'shield-shaded',  'label' => 'DLP-Vorfälle',           'route' => 'dlpincidents',      'admin' => false],
            ['hub' => 'infoprotection', 'icon' => 'hourglass-split','label' => 'Aufbewahrung (Retention)','route' => 'retention',        'admin' => false],
            ['hub' => 'infoprotection', 'icon' => 'archive',        'label' => 'eDiscovery-Fälle',       'route' => 'ediscovery',        'admin' => false],

            // ── Härtung & Posture ───────────────────────────────────────
            ['hub' => 'haertung', 'icon' => 'sliders2-vertical', 'label' => 'Security Center',      'route' => 'hardening',                            'admin' => false],
            ['hub' => 'haertung', 'icon' => 'shield-fill-check', 'label' => 'Security Posture',     'route' => 'securityposture',                      'admin' => false],
            ['hub' => 'haertung', 'icon' => 'file-earmark-lock', 'label' => 'DSGVO-Status',         'route' => 'securityposture#cat-dsgvo-datenschutz','admin' => false],
            ['hub' => 'haertung', 'icon' => 'compass',           'label' => 'Härtungs-Leitfaden',  'route' => 'bestpractice',                         'admin' => false],
            ['hub' => 'haertung', 'icon' => 'patch-check',       'label' => 'Compliance-Profile',  'route' => 'complianceprofile',                    'admin' => true],
            ['hub' => 'haertung', 'icon' => 'lock-fill',         'label' => 'Customer Lockbox',    'route' => 'customerlockbox',                      'admin' => false],
            ['hub' => 'haertung', 'icon' => 'database-fill-check','label' => 'Backup-Status',       'route' => 'backup',                               'admin' => false],

            // ── Compliance & Audit ──────────────────────────────────────
            ['hub' => 'compliance', 'icon' => 'phone',           'label' => 'Geräte',              'route' => 'devices',       'admin' => false],
            ['hub' => 'compliance', 'icon' => 'clipboard-check', 'label' => 'Access Reviews',      'route' => 'accessreview',  'admin' => false],
            ['hub' => 'compliance', 'icon' => 'clock-history',   'label' => 'Audit-Log',           'route' => 'auditlog',      'admin' => false],
            ['hub' => 'compliance', 'icon' => 'arrow-left-right','label' => 'Audit-Diff',          'route' => 'auditdiff',     'admin' => false],
            ['hub' => 'compliance', 'icon' => 'file-earmark-pdf','label' => 'DSGVO/NIS-2 Report',  'route' => 'auditreport',   'admin' => false],
            ['hub' => 'compliance', 'icon' => 'journal-text',    'label' => 'Sign-in-Log',         'route' => 'signinlog',     'admin' => false],

            // ── Lizenzen & Berichte ─────────────────────────────────────
            ['hub' => 'lizenzen', 'icon' => 'award',           'label' => 'Lizenzen',          'route' => 'licenses',         'admin' => false],
            ['hub' => 'lizenzen', 'icon' => 'lightbulb',       'label' => 'Lizenz-Berater',    'route' => 'licenseadvisor',   'admin' => false],
            ['hub' => 'lizenzen', 'icon' => 'cash-coin',       'label' => 'Lizenzkosten',      'route' => 'licensecosts',     'admin' => false],
            ['hub' => 'lizenzen', 'icon' => 'bar-chart-steps', 'label' => 'Nutzungsberichte',  'route' => 'usagereports',     'admin' => false],
            ['hub' => 'lizenzen', 'icon' => 'graph-up-arrow',  'label' => 'Adoptions-Report',  'route' => 'adoption',         'admin' => false],
            ['hub' => 'lizenzen', 'icon' => 'envelope-paper',  'label' => 'Executive-Report',  'route' => 'executivereport',  'admin' => false],
            ['hub' => 'lizenzen', 'icon' => 'heart-pulse',     'label' => 'Dienststatus',      'route' => 'servicehealth',    'admin' => false],
            ['hub' => 'lizenzen', 'icon' => null, 'label' => null, 'route' => 'licenses/expiry', 'admin' => false],

            // ── Apps & Automatisierung ──────────────────────────────────
            ['hub' => 'apps', 'icon' => 'grid-3x3-gap', 'label' => 'App-Registrierungen', 'route' => 'appregistrations', 'admin' => false],
            ['hub' => 'apps', 'icon' => 'diagram-2',    'label' => 'Lifecycle Workflows', 'route' => 'lifecycle',        'admin' => false],
            ['hub' => 'apps', 'icon' => 'robot',        'label' => 'KI-Berater',          'route' => 'ai',              'admin' => false],
            ['hub' => 'apps', 'icon' => 'diagram-2',    'label' => 'Workflows',           'route' => 'workflows',       'admin' => true],
            ['hub' => 'apps', 'icon' => 'clock',        'label' => 'Cron & Automatisierung','route' => 'cron',          'admin' => true],

            // ── Administration (admin-only) ─────────────────────────────
            ['hub' => 'administration', 'icon' => 'gear',             'label' => 'Einstellungen',          'route' => 'settings',            'admin' => true],
            ['hub' => 'administration', 'icon' => 'people-fill',      'label' => 'Benutzer-Zugang',        'route' => 'settings/users',      'admin' => true],
            ['hub' => 'administration', 'icon' => 'magic',            'label' => 'Einrichtungs-Assistent', 'route' => 'setup',               'admin' => true],
            ['hub' => 'administration', 'icon' => 'key',              'label' => 'API-Schlüssel',          'route' => 'settings/api-keys',   'admin' => true],
            ['hub' => 'administration', 'icon' => 'book',             'label' => 'API-Dokumentation',      'route' => 'api/docs',            'admin' => true],
            ['hub' => 'administration', 'icon' => 'cloud-arrow-down', 'label' => 'Updates',                'route' => 'settings/update',     'admin' => true],
            ['hub' => 'administration', 'icon' => 'journal-check',    'label' => 'App Audit-Log',          'route' => 'settings/app-audit',  'admin' => true],
            ['hub' => 'administration', 'icon' => 'shield-lock',      'label' => '2FA-Einstellungen',      'route' => 'settings/2fa',        'admin' => true],
            ['hub' => 'administration', 'icon' => null, 'label' => null, 'route' => 'settings/permissions',    'admin' => true],
            ['hub' => 'administration', 'icon' => null, 'label' => null, 'route' => 'settings/license-prices', 'admin' => true],
        ];
    }

    /** All routes (for sidebar active-state resolution). */
    public static function routes(): array
    {
        return array_column(self::defs(), 'route');
    }

    /**
     * Visible items grouped by hub (used by /overview). Virtual routes
     * (icon === null) and admin-only items (when $isAdmin is false) are excluded.
     *
     * @return array<int, array{name: string, items: array<int, array>}>
     */
    public static function groups(bool $isAdmin): array
    {
        $byHub = [];
        foreach (self::defs() as $item) {
            if ($item['icon'] === null) continue;
            if ($item['admin'] && !$isAdmin) continue;
            $byHub[$item['hub']][] = $item;
        }
        $groups = [];
        foreach (self::hubs() as $hub) {
            if (empty($byHub[$hub['key']])) continue;
            $groups[] = ['name' => $hub['label'], 'items' => $byHub[$hub['key']]];
        }
        return $groups;
    }

    /** The hub key that owns the current request path, or null. */
    public static function activeHubKey(string $currentPath): ?string
    {
        $best = null;
        $bestLen = -1;
        foreach (self::defs() as $item) {
            $route = $item['route'];
            if ($route === '' || str_contains($route, '#')) continue;
            if ($currentPath === $route || str_starts_with($currentPath, $route . '/')) {
                $len = strlen($route);
                if ($len > $bestLen) {
                    $bestLen = $len;
                    $best = $item['hub'];
                }
            }
        }
        return $best;
    }

    /**
     * The active hub's visible tabs for the tab bar, or null if the current
     * path is not inside a hub (e.g. dashboard / overview / manual).
     *
     * @return array{key:string,label:string,items:array<int,array>}|null
     */
    public static function tabsFor(string $currentPath, bool $isAdmin): ?array
    {
        $key = self::activeHubKey($currentPath);
        if ($key === null) return null;

        $label = '';
        foreach (self::hubs() as $hub) {
            if ($hub['key'] === $key) { $label = $hub['label']; break; }
        }
        $items = [];
        foreach (self::defs() as $item) {
            if ($item['hub'] !== $key) continue;
            if ($item['icon'] === null) continue;
            if ($item['admin'] && !$isAdmin) continue;
            $items[] = $item;
        }
        return ['key' => $key, 'label' => $label, 'items' => $items];
    }

    /** First visible route of a hub (its landing page), or '' if none. */
    public static function hubLandingRoute(string $key, bool $isAdmin): string
    {
        foreach (self::defs() as $item) {
            if ($item['hub'] !== $key) continue;
            if ($item['icon'] === null) continue;
            if ($item['admin'] && !$isAdmin) continue;
            return $item['route'];
        }
        return '';
    }
}
