<?php

namespace App\Modules\Api;

use App\Modules\Dashboard\DashboardService;
use App\Modules\Dashboard\MetricHistoryService;
use App\Modules\Hardening\HardeningService;
use App\Modules\Notifications\NotificationService;
use App\Modules\AuditDiff\SnapshotService;
use App\Modules\ComplianceProfile\ComplianceProfileService;

/**
 * Public REST API surface — kept deliberately read-mostly for the v1.
 * All endpoints return JSON, support an `X-Api-Key` header (or
 * `?api_key=`), and are documented machine-readable in OpenApiSpec.
 */
class ApiController
{
    public function rootInfo(): void
    {
        self::json([
            'name'      => 'M365 Tenant Tool API',
            'version'   => 'v1',
            'docs'      => '/api/docs',
            'openapi'   => '/api/openapi.json',
            'endpoints' => [
                '/api/v1/dashboard/metrics',
                '/api/v1/dashboard/security',
                '/api/v1/dashboard/licenses',
                '/api/v1/metrics/{name}/history',
                '/api/v1/hardening',
                '/api/v1/compliance-profiles',
                '/api/v1/snapshots',
                '/api/v1/snapshots/{id}',
                '/api/v1/snapshots/diff?from={id}&to={id}',
                '/api/v1/notifications',
                '/api/v1/notifications/push (POST, write scope)',
                '/api/v1/audit-log',
            ],
        ]);
    }

    public function dashboardMetrics(): void
    {
        ApiAuth::require('read');
        try {
            $svc = app_service(DashboardService::class);
            self::json([
                'metrics'   => $svc->getMetrics(),
                'extended'  => $svc->getExtendedStats(),
                'captured'  => date('c'),
            ]);
        } catch (\Throwable $e) { self::err($e); }
    }

    public function dashboardSecurity(): void
    {
        ApiAuth::require('read');
        try {
            self::json(['security' => app_service(DashboardService::class)->getSecurityStatus(), 'captured' => date('c')]);
        } catch (\Throwable $e) { self::err($e); }
    }

    public function dashboardLicenses(): void
    {
        ApiAuth::require('read');
        try {
            self::json(['licenses' => app_service(DashboardService::class)->getLicenseSummary(), 'captured' => date('c')]);
        } catch (\Throwable $e) { self::err($e); }
    }

    public function metricHistory(string $name): void
    {
        ApiAuth::require('read');
        $days = (int)($_GET['days'] ?? 30);
        $days = max(2, min(90, $days));
        self::json([
            'metric'   => $name,
            'days'     => $days,
            'values'   => MetricHistoryService::history($name, $days),
            'trend'    => MetricHistoryService::trend($name, $days),
        ]);
    }

    public function hardeningList(): void
    {
        ApiAuth::require('read');
        try {
            $items = app_service(HardeningService::class)->getItems();
            // Strip closures and admin URLs we don't want to leak by default
            $out = array_map(fn($i) => [
                'id'       => $i['id']       ?? '',
                'title'    => $i['title']    ?? '',
                'category' => $i['category'] ?? '',
                'status'   => $i['status']   ?? '',
                'detail'   => $i['detail']   ?? '',
                'why'      => $i['why']      ?? '',
            ], $items);
            self::json(['items' => $out, 'count' => count($out), 'captured' => date('c')]);
        } catch (\Throwable $e) { self::err($e); }
    }

    public function complianceProfiles(): void
    {
        ApiAuth::require('read');
        self::json(['profiles' => ComplianceProfileService::profiles()]);
    }

    public function snapshotList(): void
    {
        ApiAuth::require('read');
        self::json(['snapshots' => SnapshotService::list(100)]);
    }

    public function snapshotGet(string $id): void
    {
        ApiAuth::require('read');
        $row = SnapshotService::load((int)$id);
        if (!$row) ApiAuth::reject(404, 'not_found', 'Snapshot nicht gefunden.');
        self::json($row);
    }

    public function snapshotDiff(): void
    {
        ApiAuth::require('read');
        $from = (int)($_GET['from'] ?? 0);
        $to   = (int)($_GET['to'] ?? 0);
        if (!$from || !$to) ApiAuth::reject(400, 'bad_request', 'Erforderlich: ?from=...&to=...');
        $a = SnapshotService::load($from);
        $b = SnapshotService::load($to);
        if (!$a || !$b) ApiAuth::reject(404, 'not_found', 'Mindestens ein Snapshot fehlt.');
        self::json([
            'from'  => ['id' => $a['id'], 'created_at' => $a['created_at']],
            'to'    => ['id' => $b['id'], 'created_at' => $b['created_at']],
            'diff'  => SnapshotService::diff($a['payload'], $b['payload']),
        ]);
    }

    public function notificationsList(): void
    {
        ApiAuth::require('read');
        $limit = max(1, min(100, (int)($_GET['limit'] ?? 50)));
        self::json(['notifications' => NotificationService::recent($limit)]);
    }

    public function notificationsPush(): void
    {
        ApiAuth::require('write');
        $body = self::parseJsonBody();
        $title = trim((string)($body['title'] ?? ''));
        if ($title === '') ApiAuth::reject(400, 'bad_request', 'Feld "title" erforderlich.');
        $ok = NotificationService::push(
            $title,
            (string)($body['body']      ?? ''),
            (string)($body['severity']  ?? 'info'),
            $body['link']               ?? null,
            (string)($body['category']  ?? 'external'),
            $body['dedupe_key']         ?? null
        );
        self::json(['inserted' => $ok]);
    }

    public function auditLog(): void
    {
        ApiAuth::require('read');
        $limit = max(1, min(500, (int)($_GET['limit'] ?? 100)));
        try {
            $rows = \App\Database\DB::fetchAll(
                "SELECT actor, action, module, detail, ip_address, created_at
                 FROM app_audit_log ORDER BY id DESC LIMIT " . $limit
            );
            self::json(['entries' => $rows ?: []]);
        } catch (\Throwable $e) { self::err($e); }
    }

    public function openApiSpec(): void
    {
        // No auth — spec is public so Swagger UI can introspect it.
        self::json(OpenApiSpec::build(), pretty: true);
    }

    public function docs(): void
    {
        // Swagger UI lives in a tiny standalone HTML — points at /api/openapi.json
        if (!headers_sent()) header('Content-Type: text/html; charset=utf-8');
        require BASE_PATH . '/views/api/docs.php';
    }

    private static function json(array $data, bool $pretty = false): void
    {
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Headers: X-Api-Key, Content-Type');
        }
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | ($pretty ? JSON_PRETTY_PRINT : 0));
    }

    private static function err(\Throwable $e): never
    {
        http_response_code(500);
        if (!headers_sent()) header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'error'   => 'internal_error',
            'message' => $e->getMessage(),
        ]);
        exit;
    }

    private static function parseJsonBody(): array
    {
        $raw = file_get_contents('php://input') ?: '';
        if ($raw === '') return [];
        $data = json_decode($raw, true);
        return is_array($data) ? $data : [];
    }
}
