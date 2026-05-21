<?php

namespace App\Modules\Api;

use App\Core\Config;

/**
 * Generates the OpenAPI 3.0 spec for the public REST API. Kept inline
 * (no separate YAML file) so that adding a new endpoint touches exactly
 * one place — the array below.
 */
class OpenApiSpec
{
    public static function build(): array
    {
        $title = Config::getInstance()->get('app_name', 'M365 Tenant Tool');
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';

        return [
            'openapi' => '3.0.3',
            'info' => [
                'title'       => $title . ' — REST API',
                'description' => 'Lese-API für externe Werkzeuge (PowerBI, Grafana, n8n, etc.). Authentifizierung via X-Api-Key. Schreib-Endpunkte erfordern den Scope "write".',
                'version'     => '1.0.0',
                'contact'     => ['name' => 'Friederich Loheide', 'url' => 'https://loheide.eu'],
            ],
            'servers' => [
                ['url' => $scheme . '://' . $host, 'description' => 'Aktueller Mandant'],
            ],
            'components' => [
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
                    'Notification' => [
                        'type' => 'object',
                        'properties' => [
                            'id'         => ['type' => 'integer'],
                            'category'   => ['type' => 'string'],
                            'severity'   => ['type' => 'string', 'enum' => ['info','success','warn','critical']],
                            'title'      => ['type' => 'string'],
                            'body'       => ['type' => 'string'],
                            'link'       => ['type' => 'string', 'nullable' => true],
                            'created_at' => ['type' => 'string', 'format' => 'date-time'],
                        ],
                    ],
                ],
            ],
            'security' => [['ApiKeyAuth' => []]],
            'tags' => [
                ['name' => 'Dashboard',        'description' => 'KPIs und Trend-Werte für das Tenant-Dashboard'],
                ['name' => 'Hardening',        'description' => 'Tenant-Härtungs-Aktionen'],
                ['name' => 'Compliance',       'description' => 'Branchen-Compliance-Profile'],
                ['name' => 'Audit',            'description' => 'Snapshots und Diffs sicherheitsrelevanter Einstellungen'],
                ['name' => 'Notifications',    'description' => 'In-App-Benachrichtigungen'],
                ['name' => 'Audit-Log',        'description' => 'Internes App-Audit-Log'],
            ],
            'paths' => self::paths(),
        ];
    }

    private static function paths(): array
    {
        $okJson = function (string $schemaRef = '', string $desc = 'OK') use (&$okJson) {
            return [
                'description' => $desc,
                'content'     => ['application/json' => ['schema' => $schemaRef ? ['$ref' => $schemaRef] : ['type' => 'object']]],
            ];
        };
        $errJson = ['description' => 'Fehler', 'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/Error']]]];

        return [
            '/api/v1/dashboard/metrics' => [
                'get' => [
                    'tags' => ['Dashboard'], 'summary' => 'Alle Dashboard-Kennzahlen abrufen',
                    'responses' => [
                        '200' => $okJson('', 'Kennzahlen-Bundle'),
                        '401' => $errJson, '500' => $errJson,
                    ],
                ],
            ],
            '/api/v1/dashboard/security' => [
                'get' => [
                    'tags' => ['Dashboard'], 'summary' => 'MFA/CA/Risk-Übersicht abrufen',
                    'responses' => ['200' => $okJson(), '401' => $errJson],
                ],
            ],
            '/api/v1/dashboard/licenses' => [
                'get' => [
                    'tags' => ['Dashboard'], 'summary' => 'Lizenz-Nutzung (Top 8)',
                    'responses' => ['200' => $okJson(), '401' => $errJson],
                ],
            ],
            '/api/v1/metrics/{name}/history' => [
                'get' => [
                    'tags' => ['Dashboard'], 'summary' => 'Historische Werte einer KPI (für Sparklines/Charts)',
                    'parameters' => [
                        ['name' => 'name', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'string'],
                            'description' => 'KPI-Schlüssel, z. B. total_users, enabled_users, risky_users, total_devices, total_groups, guests, teams_count, admin_assignments, secure_score'],
                        ['name' => 'days', 'in' => 'query', 'required' => false, 'schema' => ['type' => 'integer', 'minimum' => 2, 'maximum' => 90]],
                    ],
                    'responses' => ['200' => $okJson(), '401' => $errJson],
                ],
            ],
            '/api/v1/hardening' => [
                'get' => [
                    'tags' => ['Hardening'], 'summary' => 'Aktuelle Härtungs-Items mit Status',
                    'responses' => ['200' => $okJson(), '401' => $errJson],
                ],
            ],
            '/api/v1/compliance-profiles' => [
                'get' => [
                    'tags' => ['Compliance'], 'summary' => 'Verfügbare Compliance-Profile',
                    'responses' => ['200' => $okJson(), '401' => $errJson],
                ],
            ],
            '/api/v1/snapshots' => [
                'get' => [
                    'tags' => ['Audit'], 'summary' => 'Verfügbare Tenant-Snapshots',
                    'responses' => ['200' => $okJson(), '401' => $errJson],
                ],
            ],
            '/api/v1/snapshots/{id}' => [
                'get' => [
                    'tags' => ['Audit'], 'summary' => 'Einen Snapshot vollständig laden',
                    'parameters' => [['name' => 'id', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']]],
                    'responses' => ['200' => $okJson(), '404' => $errJson, '401' => $errJson],
                ],
            ],
            '/api/v1/snapshots/diff' => [
                'get' => [
                    'tags' => ['Audit'], 'summary' => 'Diff zwischen zwei Snapshots',
                    'parameters' => [
                        ['name' => 'from', 'in' => 'query', 'required' => true, 'schema' => ['type' => 'integer']],
                        ['name' => 'to',   'in' => 'query', 'required' => true, 'schema' => ['type' => 'integer']],
                    ],
                    'responses' => ['200' => $okJson(), '400' => $errJson, '404' => $errJson],
                ],
            ],
            '/api/v1/notifications' => [
                'get' => [
                    'tags' => ['Notifications'], 'summary' => 'Letzte Benachrichtigungen',
                    'parameters' => [['name' => 'limit', 'in' => 'query', 'schema' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 100, 'default' => 50]]],
                    'responses' => ['200' => $okJson('', 'Liste'), '401' => $errJson],
                ],
            ],
            '/api/v1/notifications/push' => [
                'post' => [
                    'tags' => ['Notifications'], 'summary' => 'Eigene Benachrichtigung erzeugen (Webhook-Stil)',
                    'requestBody' => [
                        'required' => true,
                        'content'  => ['application/json' => ['schema' => [
                            'type' => 'object',
                            'required' => ['title'],
                            'properties' => [
                                'title'      => ['type' => 'string'],
                                'body'       => ['type' => 'string'],
                                'severity'   => ['type' => 'string', 'enum' => ['info','success','warn','critical']],
                                'link'       => ['type' => 'string'],
                                'category'   => ['type' => 'string'],
                                'dedupe_key' => ['type' => 'string'],
                            ],
                        ]]],
                    ],
                    'responses' => ['200' => $okJson(), '400' => $errJson, '403' => $errJson],
                ],
            ],
            '/api/v1/audit-log' => [
                'get' => [
                    'tags' => ['Audit-Log'], 'summary' => 'Einträge des internen App-Audit-Logs',
                    'parameters' => [['name' => 'limit', 'in' => 'query', 'schema' => ['type' => 'integer', 'maximum' => 500, 'default' => 100]]],
                    'responses' => ['200' => $okJson(), '401' => $errJson],
                ],
            ],
        ];
    }
}
