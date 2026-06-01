<?php

namespace App\Modules\Api;

use App\Core\Config;

/**
 * OpenAPI 3.0 spec for the public REST API. Endpoint table at the
 * bottom — adding an endpoint is a single line per HTTP method.
 */
class OpenApiSpec
{
    public static function build(): array
    {
        $title  = Config::getInstance()->get('app_name', 'M365 Tenant Tool');
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';

        return [
            'openapi' => '3.0.3',
            'info' => [
                'title'       => $title . ' — REST API',
                'description' => "Umfangreiche Lese-API für externe Werkzeuge (PowerBI, Grafana, n8n, eigene Skripte). "
                              . "Auth via `X-Api-Key`-Header. Schreib-Endpunkte erfordern den Scope `write`.\n\n"
                              . "**Hinweis:** Diese Doku ist intern (nur für angemeldete Tool-Benutzer sichtbar).",
                'version'     => '1.0.0',
                'contact'     => ['name' => 'Friederich Loheide', 'url' => 'https://loheide.eu'],
            ],
            'servers'    => [['url' => $scheme . '://' . $host, 'description' => 'Aktueller Mandant']],
            'security'   => [['ApiKeyAuth' => []]],
            'tags'       => self::tags(),
            'components' => self::components(),
            'paths'      => self::paths(),
        ];
    }

    private static function tags(): array
    {
        return [
            ['name' => 'Discovery',      'description' => 'Auflistung, OpenAPI-Spec'],
            ['name' => 'Dashboard',      'description' => 'KPIs und Trend-Werte'],
            ['name' => 'Tenant',         'description' => 'Mandant, Domains'],
            ['name' => 'Identity',       'description' => 'Benutzer, Gäste, Gruppen, Admin-Rollen, Lizenzen'],
            ['name' => 'Devices',        'description' => 'Intune-Geräte'],
            ['name' => 'Security',       'description' => 'Risk, Defender, CA, Secure-Score, Sign-Ins, Permissions'],
            ['name' => 'Hardening',      'description' => 'Tenant-Härtungs-Aktionen'],
            ['name' => 'Compliance',     'description' => 'Branchen-Compliance-Profile'],
            ['name' => 'Audit',          'description' => 'Snapshots, Diffs, App-Audit-Log'],
            ['name' => 'Operations',     'description' => 'Mailboxen, Service Health, Message Center'],
            ['name' => 'Workflows',      'description' => 'Workflow-Automatisierung'],
            ['name' => 'Notifications',  'description' => 'In-App-Benachrichtigungen'],
        ];
    }

    private static function components(): array
    {
        return [
            'securitySchemes' => [
                'ApiKeyAuth' => [
                    'type' => 'apiKey',
                    'in'   => 'header',
                    'name' => 'X-Api-Key',
                    'description' => 'API-Schlüssel, der unter /settings/api-keys in der UI erzeugt wird.',
                ],
            ],
            'schemas' => [
                'Error' => [
                    'type' => 'object',
                    'properties' => [
                        'error'   => ['type' => 'string'],
                        'message' => ['type' => 'string'],
                    ],
                ],
                'OkResult' => [
                    'type' => 'object',
                    'properties' => [
                        'ok'  => ['type' => 'boolean'],
                        'msg' => ['type' => 'string'],
                    ],
                ],
            ],
        ];
    }

    private static function paths(): array
    {
        $endpoints = self::endpoints();
        $paths = [];
        foreach ($endpoints as $e) {
            [$method, $path, $tag, $summary, $opts] = $e + [null, null, null, null, []];
            $op = [
                'tags'    => [$tag],
                'summary' => $summary,
                'responses' => [
                    '200' => ['description' => 'OK', 'content' => ['application/json' => ['schema' => ['type' => 'object']]]],
                    '401' => self::errRef(),
                    '403' => self::errRef(),
                    '500' => self::errRef(),
                ],
            ];
            if (!empty($opts['params']))   $op['parameters']  = $opts['params'];
            if (!empty($opts['body']))     $op['requestBody'] = $opts['body'];
            if (!empty($opts['extra']))    foreach ($opts['extra'] as $k => $v) $op[$k] = $v;
            $paths[$path][$method] = $op;
        }
        return $paths;
    }

    private static function errRef(): array
    {
        return ['description' => 'Fehler', 'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/Error']]]];
    }

    private static function param(string $name, string $in, string $type = 'string', bool $required = false, ?string $desc = null, $extra = []): array
    {
        $p = ['name' => $name, 'in' => $in, 'required' => $required, 'schema' => array_merge(['type' => $type], $extra)];
        if ($desc !== null) $p['description'] = $desc;
        return $p;
    }

    private static function jsonBody(array $schema, bool $required = true): array
    {
        return ['required' => $required, 'content' => ['application/json' => ['schema' => $schema]]];
    }

    /**
     * Single source of truth: each entry = [METHOD, /path, Tag, summary, opts]
     */
    private static function endpoints(): array
    {
        $intParam   = fn($n, $req = false, $desc = null, $extra = []) => self::param($n, 'query', 'integer', $req, $desc, $extra);
        $pathParam  = fn($n, $type = 'string', $desc = null) => self::param($n, 'path', $type, true, $desc);
        $queryParam = fn($n, $req = false, $desc = null) => self::param($n, 'query', 'string', $req, $desc);

        $pushBody = self::jsonBody([
            'type' => 'object', 'required' => ['title'],
            'properties' => [
                'title'      => ['type' => 'string'],
                'body'       => ['type' => 'string'],
                'severity'   => ['type' => 'string', 'enum' => ['info','success','warn','critical']],
                'link'       => ['type' => 'string'],
                'category'   => ['type' => 'string'],
                'dedupe_key' => ['type' => 'string'],
            ],
        ]);
        $hardeningBody = self::jsonBody([
            'type' => 'object', 'required' => ['action_id'],
            'properties' => ['action_id' => ['type' => 'string', 'description' => 'Eine der IDs aus GET /api/v1/hardening']],
        ]);

        return [
            // Discovery
            ['get', '/api', 'Discovery', 'API-Wurzel mit Endpunkt-Liste'],
            ['get', '/api/openapi.json', 'Discovery', 'OpenAPI-3-Spezifikation'],

            // Dashboard
            ['get', '/api/v1/dashboard/metrics',   'Dashboard', 'Alle Dashboard-Kennzahlen'],
            ['get', '/api/v1/dashboard/security',  'Dashboard', 'MFA/CA/Risk-Status'],
            ['get', '/api/v1/dashboard/licenses',  'Dashboard', 'Top-8 Lizenz-Auslastung'],
            ['get', '/api/v1/metrics',             'Dashboard', 'Verfügbare KPI-Metriken (Sparkline-Datenquelle)'],
            ['get', '/api/v1/metrics/{name}/history', 'Dashboard', 'Historie einer KPI', [
                'params' => [$pathParam('name', 'string', 'KPI-Schlüssel, z. B. total_users'),
                             $intParam('days', false, 'Tage, 2..90', ['minimum' => 2, 'maximum' => 90, 'default' => 30])],
            ]],

            // Tenant
            ['get', '/api/v1/tenant',  'Tenant', 'Organisations-Informationen'],
            ['get', '/api/v1/domains', 'Tenant', 'Verifizierte Domains'],

            // Identity
            ['get', '/api/v1/users', 'Identity', 'Benutzer-Liste', [
                'params' => [$intParam('top', false, 'Max. Treffer (1..999)'),
                             $queryParam('filter', false, '$filter-Ausdruck (Graph-Syntax)')],
            ]],
            ['get', '/api/v1/users/{id}',     'Identity', 'Einzelnen Benutzer abrufen', ['params' => [$pathParam('id')]]],
            ['get', '/api/v1/users/{id}/mfa', 'Identity', 'MFA-Methoden eines Benutzers', ['params' => [$pathParam('id')]]],
            ['get', '/api/v1/guests',         'Identity', 'Gast-Benutzer'],
            ['get', '/api/v1/groups',         'Identity', 'M365-Gruppen / Teams'],
            ['get', '/api/v1/groups/{id}/members', 'Identity', 'Gruppenmitglieder', ['params' => [$pathParam('id')]]],
            ['get', '/api/v1/admin-roles',    'Identity', 'Admin-Rollenzuweisungen'],
            ['get', '/api/v1/licenses',       'Identity', 'Lizenz-SKUs (alle, nicht nur Top 8)'],

            // Devices
            ['get', '/api/v1/devices', 'Devices', 'Intune-verwaltete Geräte', [
                'params' => [$intParam('top', false, 'Max. Treffer (1..999)')],
            ]],

            // Security
            ['get', '/api/v1/risky-users',         'Security', 'Aktuelle Risikobenutzer (atRisk)'],
            ['get', '/api/v1/defender-alerts',     'Security', 'Offene Defender-Alerts'],
            ['get', '/api/v1/conditional-access',  'Security', 'CA-Richtlinien'],
            ['get', '/api/v1/secure-score',        'Security', 'Letzte 14 Secure-Score-Werte'],
            ['get', '/api/v1/sign-ins',            'Security', 'Sign-In-Log', [
                'params' => [$intParam('top', false), $queryParam('filter', false, 'Graph $filter')],
            ]],
            ['get', '/api/v1/permissions',         'Security', 'Graph-Berechtigungs-Audit'],

            // Hardening
            ['get',  '/api/v1/hardening',       'Hardening', 'Alle Härtungs-Items mit Status'],
            ['post', '/api/v1/hardening/apply', 'Hardening', 'Hardening-Aktion anwenden (Scope: admin)', [
                'body' => $hardeningBody,
            ]],

            // Compliance
            ['get',  '/api/v1/compliance-profiles',          'Compliance', 'Verfügbare Compliance-Profile'],
            ['post', '/api/v1/compliance-profiles/{key}/apply', 'Compliance', 'Compliance-Profil anwenden (Scope: admin)', [
                'params' => [$pathParam('key', 'string', 'standard, healthcare, finance, public, education')],
            ]],

            // Audit
            ['get',  '/api/v1/snapshots',           'Audit', 'Verfügbare Tenant-Snapshots'],
            ['post', '/api/v1/snapshots',           'Audit', 'Neuen Snapshot erzeugen (Scope: write)'],
            ['get',  '/api/v1/snapshots/diff',      'Audit', 'Diff zwischen zwei Snapshots', [
                'params' => [$intParam('from', true), $intParam('to', true)],
            ]],
            ['get',  '/api/v1/snapshots/{id}',      'Audit', 'Einen Snapshot vollständig laden', ['params' => [$pathParam('id', 'integer')]]],
            ['get',  '/api/v1/audit-log',           'Audit', 'App-Audit-Log', [
                'params' => [$intParam('limit', false, 'max 500', ['maximum' => 500, 'default' => 100])],
            ]],

            // Operations
            ['get', '/api/v1/mailboxes',      'Operations', 'Postfach-Nutzung (30-Tage-Report)'],
            ['get', '/api/v1/service-health', 'Operations', 'Microsoft-365-Service-Status'],
            ['get', '/api/v1/message-center', 'Operations', 'Message-Center-Einträge'],

            // Workflows
            ['get', '/api/v1/workflows',            'Workflows', 'Alle Workflow-Definitionen'],
            ['get', '/api/v1/workflows/{id}/runs',  'Workflows', 'Run-Historie eines Workflows', [
                'params' => [$pathParam('id', 'integer'), $intParam('limit', false, '1..200')],
            ]],

            // Notifications
            ['get',  '/api/v1/notifications',      'Notifications', 'Letzte Benachrichtigungen', [
                'params' => [$intParam('limit', false, '1..100', ['minimum' => 1, 'maximum' => 100, 'default' => 50])],
            ]],
            ['post', '/api/v1/notifications/push', 'Notifications', 'Benachrichtigung erzeugen (Webhook-Stil)', [
                'body' => $pushBody,
            ]],
        ];
    }
}
