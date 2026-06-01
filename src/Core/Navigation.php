<?php

namespace App\Core;

/**
 * Single source of truth for the application navigation. Both the sidebar and
 * the /overview module map consume this, so the menu only ever lives in one
 * place.
 *
 * Each entry: section (non-null starts a new collapsible group), icon (null =
 * virtual route, only used for active-state resolution), label, route, admin.
 */
class Navigation
{
    public static function defs(): array
    {
        return [
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

            // ── Sicherheit & Härtung (Status, Härtung, Scores)
            ['section' => 'Sicherheit & Härtung','icon' => 'shield-check',         'label' => 'Sicherheit',             'route' => 'security',                   'admin' => false],
            ['section' => null,                  'icon' => 'shield-fill-check',    'label' => 'Security Posture',       'route' => 'securityposture',            'admin' => false],
            ['section' => null,                  'icon' => 'file-earmark-lock',    'label' => 'DSGVO-Status',           'route' => 'securityposture#cat-dsgvo-datenschutz', 'admin' => false],
            ['section' => null,                  'icon' => 'sliders2-vertical',    'label' => 'Security Center',        'route' => 'hardening',                  'admin' => false],
            ['section' => null,                  'icon' => 'compass',              'label' => 'Härtungs-Leitfaden',     'route' => 'bestpractice',               'admin' => false],
            ['section' => null,                  'icon' => 'patch-check',          'label' => 'Compliance-Profile',     'route' => 'complianceprofile',          'admin' => true],
            ['section' => null,                  'icon' => 'bar-chart-line',       'label' => 'Secure Score',           'route' => 'securescore',                'admin' => false],
            ['section' => null,                  'icon' => 'bell',                 'label' => 'Defender Alerts',        'route' => 'defenderalerts',             'admin' => false],
            ['section' => null,                  'icon' => 'exclamation-triangle', 'label' => 'Risiko-Anmeldungen',     'route' => 'riskysignins',               'admin' => false],

            // ── Identität & Bedrohungen (Detection)
            ['section' => 'Identität & Bedrohungen','icon' => 'key-fill',          'label' => 'Break-Glass-Accounts',   'route' => 'breakglass',                 'admin' => false],
            ['section' => null,                  'icon' => 'lightning-charge',     'label' => 'PIM (JIT-Admin)',        'route' => 'pim',                        'admin' => false],
            ['section' => null,                  'icon' => 'fingerprint',          'label' => 'Auth-Strength',          'route' => 'authstrength',               'admin' => false],
            ['section' => null,                  'icon' => 'shield-slash',         'label' => 'MFA-Fatigue',            'route' => 'mfafatigue',                 'admin' => false],
            ['section' => null,                  'icon' => 'arrow-right-square',   'label' => 'Auto-Forward-Audit',     'route' => 'mailboxrules',               'admin' => false],
            ['section' => null,                  'icon' => 'app-indicator',        'label' => 'OAuth-App-Audit',        'route' => 'oauthaudit',                 'admin' => false],
            ['section' => null,                  'icon' => 'shield-shaded',        'label' => 'DLP-Vorfälle',           'route' => 'dlpincidents',               'admin' => false],
            ['section' => null,                  'icon' => 'eye-fill',             'label' => 'Insider-Threat',         'route' => 'insiderthreat',              'admin' => false],
            ['section' => null,                  'icon' => 'bullseye',             'label' => 'Phishing-Simulationen',  'route' => 'phishingsim',                'admin' => false],
            ['section' => null,                  'icon' => 'arrow-left-right',     'label' => 'Cross-Tenant-Access',    'route' => 'crosstenantaccess',          'admin' => false],
            ['section' => null,                  'icon' => 'clock-history',        'label' => 'Token-Lifetime',         'route' => 'tokenlifetime',              'admin' => false],
            ['section' => null,                  'icon' => 'person-bounding-box',  'label' => 'Identity Provider Trust','route' => 'identityproviders',          'admin' => false],

            // ── Apps & Konfiguration
            ['section' => 'Apps & Konfiguration','icon' => 'grid-3x3-gap',         'label' => 'App-Registrierungen',    'route' => 'appregistrations',           'admin' => false],
            ['section' => null,                  'icon' => 'diagram-2',            'label' => 'Lifecycle Workflows',    'route' => 'lifecycle',                  'admin' => false],
            ['section' => null,                  'icon' => 'lock-fill',            'label' => 'Customer Lockbox',       'route' => 'customerlockbox',            'admin' => false],
            ['section' => null,                  'icon' => 'globe2',               'label' => 'Domain Health',          'route' => 'domainhealth',               'admin' => false],
            ['section' => null,                  'icon' => 'database-fill-check',  'label' => 'Backup-Status',          'route' => 'backup',                     'admin' => false],
            ['section' => null,                  'icon' => 'robot',                'label' => 'KI-Berater',             'route' => 'ai',                         'admin' => false],

            // ── Compliance & Audit
            ['section' => 'Compliance & Audit',  'icon' => 'phone',                'label' => 'Geräte',                 'route' => 'devices',                    'admin' => false],
            ['section' => null,                  'icon' => 'person-x',             'label' => 'Inaktive Konten',        'route' => 'staleaccounts',              'admin' => false],
            ['section' => null,                  'icon' => 'trash2',               'label' => 'Papierkorb',             'route' => 'deletedobjects',             'admin' => false],
            ['section' => null,                  'icon' => 'clipboard-check',      'label' => 'Access Reviews',         'route' => 'accessreview',               'admin' => false],
            ['section' => null,                  'icon' => 'clock-history',        'label' => 'Audit-Log',              'route' => 'auditlog',                   'admin' => false],
            ['section' => null,                  'icon' => 'arrow-left-right',     'label' => 'Audit-Diff',             'route' => 'auditdiff',                  'admin' => false],
            ['section' => null,                  'icon' => 'file-earmark-pdf',     'label' => 'DSGVO/NIS-2 Report',     'route' => 'auditreport',                'admin' => false],
            ['section' => null,                  'icon' => 'journal-text',         'label' => 'Sign-in-Log',            'route' => 'signinlog',                  'admin' => false],
            ['section' => null,                  'icon' => 'archive',              'label' => 'eDiscovery-Fälle',       'route' => 'retentionpolicies',          'admin' => false],
            ['section' => null,                  'icon' => 'tags',                 'label' => 'Sensitivity Labels',     'route' => 'sensitivitylabels',          'admin' => false],

            // ── Berichte & Monitoring
            ['section' => 'Berichte & Monitoring','icon' => 'bar-chart-steps',     'label' => 'Nutzungsberichte',       'route' => 'usagereports',               'admin' => false],
            ['section' => null,                  'icon' => 'graph-up-arrow',       'label' => 'Adoptions-Report',       'route' => 'adoption',                   'admin' => false],
            ['section' => null,                  'icon' => 'envelope-paper',       'label' => 'Executive-Report',       'route' => 'executivereport',            'admin' => false],
            ['section' => null,                  'icon' => 'heart-pulse',          'label' => 'Dienststatus',           'route' => 'servicehealth',              'admin' => false],

            // ── Administration (admin-only)
            ['section' => 'Administration',      'icon' => 'clock',                'label' => 'Cron & Automatisierung', 'route' => 'cron',                       'admin' => true],
            ['section' => null,                  'icon' => 'gear',                 'label' => 'Einstellungen',          'route' => 'settings',                   'admin' => true],
            ['section' => null,                  'icon' => 'people-fill',          'label' => 'Benutzer-Zugang',        'route' => 'settings/users',             'admin' => true],
            ['section' => null,                  'icon' => 'magic',                'label' => 'Einrichtungs-Assistent', 'route' => 'setup',                      'admin' => true],
            ['section' => null,                  'icon' => 'diagram-2',            'label' => 'Workflows',              'route' => 'workflows',                  'admin' => true],
            ['section' => null,                  'icon' => 'key',                  'label' => 'API-Schlüssel',          'route' => 'settings/api-keys',          'admin' => true],
            ['section' => null,                  'icon' => 'book',                 'label' => 'API-Dokumentation',      'route' => 'api/docs',                   'admin' => true],
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
    }

    /** All routes (for sidebar active-state resolution). */
    public static function routes(): array
    {
        return array_column(self::defs(), 'route');
    }

    /**
     * Visible navigation grouped into sections. Virtual routes (icon === null)
     * and admin-only items (when $isAdmin is false) are excluded.
     *
     * @return array<int, array{name: string, items: array<int, array>}>
     */
    public static function groups(bool $isAdmin): array
    {
        $groups = [];
        $cur    = null;
        foreach (self::defs() as $item) {
            if ($item['icon'] === null) continue;        // virtual route
            if ($item['admin'] && !$isAdmin) continue;   // admin-only

            if ($item['section'] !== null) {
                if ($cur !== null) $groups[] = $cur;
                $cur = ['name' => $item['section'], 'items' => []];
            }
            if ($cur !== null) $cur['items'][] = $item;
        }
        if ($cur !== null) $groups[] = $cur;
        return $groups;
    }
}
