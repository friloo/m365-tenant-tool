<?php

namespace App\Modules\Api;

use App\Auth\LocalAuth;
use App\Core\AppAudit;
use App\Database\DB;
use App\Modules\AuditDiff\SnapshotService;
use App\Modules\ComplianceProfile\ComplianceProfileService;
use App\Modules\Dashboard\DashboardService;
use App\Modules\Dashboard\MetricHistoryService;
use App\Modules\Hardening\HardeningService;
use App\Modules\Notifications\NotificationService;
use App\Modules\Settings\PermissionCheckerService;
use App\Modules\Workflows\WorkflowService;

/**
 * Public REST API surface — read-mostly v1.
 * Endpoint groups:
 *   - Discovery        — root info, openapi, docs (require session login)
 *   - Dashboard / KPIs — metrics, security, licenses, sparkline history
 *   - Identity         — users, guests, groups, admin roles
 *   - Devices          — Intune devices
 *   - Security         — risky users, defender alerts, CA, secure score,
 *                        sign-ins, hardening, permissions, snapshots
 *   - Operations       — mailboxes, service health, audit-log
 *   - Workflows        — list + per-workflow runs
 *   - Compliance       — profiles + apply (write)
 *   - Notifications    — list + push (write)
 *   - Tenant info      — organization, domains
 *
 * Auth model:
 *   - X-Api-Key header (or ?api_key=) verified via ApiAuth.
 *   - /api/docs and /api/openapi.json are restricted to logged-in
 *     tool users (LocalAuth) so the spec stays internal.
 */
class ApiController
{
    // ───────────────── Discovery (session-auth) ─────────────────

    public function rootInfo(): void
    {
        LocalAuth::require();
        self::json([
            'name'    => 'M365 Tenant Tool API',
            'version' => 'v1',
            'docs'    => '/api/docs',
            'openapi' => '/api/openapi.json',
            'auth'    => 'X-Api-Key header (create under /settings/api-keys)',
            'scopes'  => ['read', 'write', 'admin'],
            'note'    => 'API-Endpunkte selbst authentifizieren via API-Key; diese Discovery-URL erfordert App-Login.',
        ], pretty: true);
    }

    public function docs(): void
    {
        LocalAuth::require();
        \App\Core\View::render('api/docs', ['pageTitle' => 'API-Dokumentation']);
    }

    public function openApiSpec(): void
    {
        LocalAuth::require();
        self::json(OpenApiSpec::build(), pretty: true);
    }

    // ───────────────── Dashboard ────────────────────────────────

    public function dashboardMetrics(): void
    {
        ApiAuth::require('read');
        try {
            $svc = app_service(DashboardService::class);
            self::json([
                'metrics'  => $svc->getMetrics(),
                'extended' => $svc->getExtendedStats(),
                'captured' => date('c'),
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
        $days = max(2, min(90, (int)($_GET['days'] ?? 30)));
        self::json([
            'metric' => $name,
            'days'   => $days,
            'values' => MetricHistoryService::history($name, $days),
            'trend'  => MetricHistoryService::trend($name, $days),
        ]);
    }

    public function metricsList(): void
    {
        ApiAuth::require('read');
        try {
            $rows = DB::fetchAll("SELECT metric, COUNT(*) AS days, MIN(day) AS first_day, MAX(day) AS last_day
                                  FROM app_metric_history GROUP BY metric ORDER BY metric");
            self::json(['metrics' => $rows ?: []]);
        } catch (\Throwable $e) { self::err($e); }
    }

    // ───────────────── Tenant / Organization ────────────────────

    public function tenantInfo(): void
    {
        ApiAuth::require('read');
        try {
            $r = app_graph()->get('/organization', [
                '$select' => 'id,displayName,verifiedDomains,countryLetterCode,createdDateTime,onPremisesSyncEnabled,tenantType'
            ], 'api_org', 1800);
            $first = $r['value'][0] ?? [];
            self::json(['organization' => $first]);
        } catch (\Throwable $e) { self::err($e); }
    }

    public function domains(): void
    {
        ApiAuth::require('read');
        try {
            $r = app_graph()->get('/domains', [], 'api_domains', 1800);
            self::json(['domains' => $r['value'] ?? []]);
        } catch (\Throwable $e) { self::err($e); }
    }

    // ───────────────── Identity ─────────────────────────────────

    public function usersList(): void
    {
        ApiAuth::require('read');
        $top    = max(1, min(999, (int)($_GET['top'] ?? 100)));
        $filter = (string)($_GET['filter'] ?? '');
        $params = [
            '$select' => 'id,userPrincipalName,displayName,accountEnabled,mail,jobTitle,department,createdDateTime,userType',
            '$top'    => (string)$top,
        ];
        if ($filter !== '') $params['$filter'] = $filter;

        try {
            $r = app_graph()->get('/users', $params);
            self::json(['users' => $r['value'] ?? [], 'count' => count($r['value'] ?? [])]);
        } catch (\Throwable $e) { self::err($e); }
    }

    public function userGet(string $id): void
    {
        ApiAuth::require('read');
        try {
            $u = app_graph()->get('/users/' . urlencode($id), [
                '$select' => 'id,userPrincipalName,displayName,accountEnabled,mail,mobilePhone,jobTitle,department,'
                          .'companyName,officeLocation,city,country,createdDateTime,userType,onPremisesSyncEnabled',
            ]);
            self::json(['user' => $u]);
        } catch (\Throwable $e) { self::err($e); }
    }

    public function userMfa(string $id): void
    {
        ApiAuth::require('read');
        try {
            $r = app_graph()->get('/users/' . urlencode($id) . '/authentication/methods');
            self::json(['methods' => $r['value'] ?? []]);
        } catch (\Throwable $e) { self::err($e); }
    }

    public function guestsList(): void
    {
        ApiAuth::require('read');
        $top = max(1, min(999, (int)($_GET['top'] ?? 100)));
        try {
            $r = app_graph()->get('/users', [
                '$filter' => "userType eq 'Guest'",
                '$select' => 'id,userPrincipalName,displayName,mail,createdDateTime,externalUserState,signInActivity',
                '$top'    => (string)$top,
            ]);
            self::json(['guests' => $r['value'] ?? [], 'count' => count($r['value'] ?? [])]);
        } catch (\Throwable $e) { self::err($e); }
    }

    public function groupsList(): void
    {
        ApiAuth::require('read');
        $top = max(1, min(999, (int)($_GET['top'] ?? 100)));
        try {
            $r = app_graph()->get('/groups', [
                '$select' => 'id,displayName,mail,groupTypes,resourceProvisioningOptions,visibility,createdDateTime',
                '$top'    => (string)$top,
            ]);
            self::json(['groups' => $r['value'] ?? [], 'count' => count($r['value'] ?? [])]);
        } catch (\Throwable $e) { self::err($e); }
    }

    public function groupMembers(string $id): void
    {
        ApiAuth::require('read');
        try {
            $r = app_graph()->get('/groups/' . urlencode($id) . '/members', [
                '$select' => 'id,userPrincipalName,displayName,mail',
                '$top'    => '999',
            ]);
            self::json(['members' => $r['value'] ?? []]);
        } catch (\Throwable $e) { self::err($e); }
    }

    public function adminRoles(): void
    {
        ApiAuth::require('read');
        try {
            $roles = app_graph()->get('/directoryRoles', ['$select' => 'id,displayName,description,roleTemplateId'], 'api_admin_roles', 3600);
            $out = [];
            foreach ($roles['value'] ?? [] as $r) {
                $members = app_graph()->get('/directoryRoles/' . $r['id'] . '/members', ['$select' => 'id,userPrincipalName,displayName']);
                $out[] = ['role' => $r['displayName'], 'id' => $r['id'], 'members' => $members['value'] ?? []];
            }
            self::json(['roles' => $out]);
        } catch (\Throwable $e) { self::err($e); }
    }

    public function licensesList(): void
    {
        ApiAuth::require('read');
        try {
            $r = app_graph()->get('/subscribedSkus', [], 'api_skus', 1800);
            $out = [];
            foreach ($r['value'] ?? [] as $s) {
                $enabled  = (int)($s['prepaidUnits']['enabled']  ?? 0);
                $consumed = (int)($s['consumedUnits']            ?? 0);
                $out[] = [
                    'sku_id'        => $s['skuId']        ?? null,
                    'sku_part_number'=> $s['skuPartNumber'] ?? null,
                    'enabled'       => $enabled,
                    'consumed'      => $consumed,
                    'available'     => max(0, $enabled - $consumed),
                    'utilization_pct'=> $enabled > 0 ? round(($consumed / $enabled) * 100, 1) : 0,
                ];
            }
            self::json(['skus' => $out]);
        } catch (\Throwable $e) { self::err($e); }
    }

    // ───────────────── Devices ──────────────────────────────────

    public function devicesList(): void
    {
        ApiAuth::require('read');
        $top = max(1, min(999, (int)($_GET['top'] ?? 200)));
        try {
            $r = app_graph()->get('/deviceManagement/managedDevices', [
                '$select' => 'id,deviceName,userPrincipalName,operatingSystem,osVersion,complianceState,'
                          .'isEncrypted,lastSyncDateTime,enrolledDateTime,model,manufacturer',
                '$top' => (string)$top,
            ]);
            self::json(['devices' => $r['value'] ?? [], 'count' => count($r['value'] ?? [])]);
        } catch (\Throwable $e) { self::err($e); }
    }

    // ───────────────── Security ─────────────────────────────────

    public function riskyUsers(): void
    {
        ApiAuth::require('read');
        try {
            $r = app_graph()->get('/identityProtection/riskyUsers', [
                '$filter' => "riskState eq 'atRisk'",
                '$top' => '200',
            ]);
            self::json(['users' => $r['value'] ?? []]);
        } catch (\Throwable $e) { self::err($e); }
    }

    public function defenderAlerts(): void
    {
        ApiAuth::require('read');
        try {
            $r = app_graph()->get('/security/alerts_v2', [
                '$top' => '100', '$filter' => "status ne 'resolved'",
                '$select' => 'id,title,severity,status,createdDateTime,category,alertWebUrl',
            ]);
            self::json(['alerts' => $r['value'] ?? []]);
        } catch (\Throwable $e) { self::err($e); }
    }

    public function conditionalAccess(): void
    {
        ApiAuth::require('read');
        try {
            $r = app_graph()->get('/identity/conditionalAccess/policies',
                ['$select' => 'id,displayName,state,createdDateTime,modifiedDateTime'], 'api_ca', 600);
            self::json(['policies' => $r['value'] ?? []]);
        } catch (\Throwable $e) { self::err($e); }
    }

    public function secureScore(): void
    {
        ApiAuth::require('read');
        try {
            $r = app_graph()->get('/security/secureScores', ['$top' => '14'], 'api_securescore', 3600);
            self::json(['scores' => $r['value'] ?? []]);
        } catch (\Throwable $e) { self::err($e); }
    }

    public function signIns(): void
    {
        ApiAuth::require('read');
        $top = max(1, min(999, (int)($_GET['top'] ?? 100)));
        $filter = (string)($_GET['filter'] ?? '');
        $params = [
            '$top' => (string)$top,
            '$select' => 'id,userPrincipalName,createdDateTime,ipAddress,status,appDisplayName,location,riskLevelAggregated',
        ];
        if ($filter !== '') $params['$filter'] = $filter;
        try {
            $r = app_graph()->get('/auditLogs/signIns', $params);
            self::json(['sign_ins' => $r['value'] ?? []]);
        } catch (\Throwable $e) { self::err($e); }
    }

    public function permissionsCheck(): void
    {
        ApiAuth::require('read');
        try {
            $svc = app_service(PermissionCheckerService::class);
            $checked = $svc->checkPermissions();
            $summary = $svc->getSummary($checked);
            self::json(['summary' => $summary, 'permissions' => array_values($checked)]);
        } catch (\Throwable $e) { self::err($e); }
    }

    // ───────────────── Hardening ────────────────────────────────

    public function hardeningList(): void
    {
        ApiAuth::require('read');
        try {
            $items = app_service(HardeningService::class)->getItems();
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

    public function hardeningApply(): void
    {
        $key = ApiAuth::require('write');
        $body = self::parseJsonBody();
        $action = (string)($body['action_id'] ?? '');
        if ($action === '') ApiAuth::reject(400, 'bad_request', 'Feld "action_id" erforderlich.');
        try {
            $r = app_service(HardeningService::class)->apply($action);
            AppAudit::log('api_hardening_apply', 'api', "Key: {$key['name']} · Action: {$action} · OK: " . ($r['ok'] ? '1' : '0'));
            self::json($r);
        } catch (\Throwable $e) { self::err($e); }
    }

    // ───────────────── Compliance profiles ──────────────────────

    public function complianceProfiles(): void
    {
        ApiAuth::require('read');
        self::json(['profiles' => ComplianceProfileService::profiles()]);
    }

    public function complianceProfileApply(string $key): void
    {
        $apiKey = ApiAuth::require('write');
        try {
            $r = app_service(ComplianceProfileService::class)->apply($key);
            AppAudit::log('api_compliance_apply', 'api', "Key: {$apiKey['name']} · Profil: {$key}");
            self::json($r);
        } catch (\Throwable $e) { self::err($e); }
    }

    // ───────────────── Audit-Diff snapshots ─────────────────────

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

    public function snapshotCreate(): void
    {
        $key = ApiAuth::require('write');
        try {
            $id = app_service(SnapshotService::class)->capture('api');
            AppAudit::log('api_snapshot_create', 'api', "Key: {$key['name']} · #{$id}");
            self::json(['snapshot_id' => $id]);
        } catch (\Throwable $e) { self::err($e); }
    }

    public function snapshotDiff(): void
    {
        ApiAuth::require('read');
        $from = (int)($_GET['from'] ?? 0);
        $to   = (int)($_GET['to']   ?? 0);
        if (!$from || !$to) ApiAuth::reject(400, 'bad_request', 'Erforderlich: ?from=...&to=...');
        $a = SnapshotService::load($from);
        $b = SnapshotService::load($to);
        if (!$a || !$b) ApiAuth::reject(404, 'not_found', 'Mindestens ein Snapshot fehlt.');
        self::json([
            'from' => ['id' => $a['id'], 'created_at' => $a['created_at']],
            'to'   => ['id' => $b['id'], 'created_at' => $b['created_at']],
            'diff' => SnapshotService::diff($a['payload'], $b['payload']),
        ]);
    }

    // ───────────────── Notifications ────────────────────────────

    public function notificationsList(): void
    {
        ApiAuth::require('read');
        $limit = max(1, min(100, (int)($_GET['limit'] ?? 50)));
        self::json(['notifications' => NotificationService::recent($limit)]);
    }

    public function notificationsPush(): void
    {
        $key = ApiAuth::require('write');
        $body = self::parseJsonBody();
        $title = trim((string)($body['title'] ?? ''));
        if ($title === '') ApiAuth::reject(400, 'bad_request', 'Feld "title" erforderlich.');
        $ok = NotificationService::push(
            $title,
            (string)($body['body']     ?? ''),
            (string)($body['severity'] ?? 'info'),
            $body['link']              ?? null,
            (string)($body['category'] ?? 'external'),
            $body['dedupe_key']        ?? null
        );
        AppAudit::log('api_notification_push', 'api', "Key: {$key['name']} · Title: " . mb_substr($title, 0, 80));
        self::json(['inserted' => $ok]);
    }

    // ───────────────── Mail & Service Health ────────────────────

    public function mailboxes(): void
    {
        ApiAuth::require('read');
        try {
            $r = app_graph()->getReport("/reports/getMailboxUsageDetail(period='D30')", [], 'api_mailbox', 3600);
            self::json(['mailboxes' => $r ?: []]);
        } catch (\Throwable $e) { self::err($e); }
    }

    public function serviceHealth(): void
    {
        ApiAuth::require('read');
        try {
            $r = app_graph()->get('/admin/serviceAnnouncement/healthOverviews', [], 'api_health', 300);
            self::json(['services' => $r['value'] ?? []]);
        } catch (\Throwable $e) { self::err($e); }
    }

    public function messageCenter(): void
    {
        ApiAuth::require('read');
        $top = max(1, min(200, (int)($_GET['top'] ?? 50)));
        try {
            $r = app_graph()->get('/admin/serviceAnnouncement/messages', [
                '$top' => (string)$top,
                '$select' => 'id,title,category,severity,startDateTime,endDateTime,services,actionRequiredByDateTime',
            ], 'api_msgcenter', 900);
            self::json(['messages' => $r['value'] ?? []]);
        } catch (\Throwable $e) { self::err($e); }
    }

    // ───────────────── Workflows ────────────────────────────────

    public function workflowsList(): void
    {
        ApiAuth::require('read');
        $rows = WorkflowService::listAll();
        $out = array_map(fn($w) => [
            'id'          => (int)$w['id'],
            'name'        => $w['name'],
            'trigger_key' => $w['trigger_key'],
            'enabled'     => (bool)$w['enabled'],
            'last_run'    => $w['last_run'],
            'last_status' => $w['last_status'],
            'last_msg'    => $w['last_msg'],
        ], $rows);
        self::json(['workflows' => $out]);
    }

    public function workflowRuns(string $id): void
    {
        ApiAuth::require('read');
        $limit = max(1, min(200, (int)($_GET['limit'] ?? 50)));
        self::json(['runs' => WorkflowService::runs((int)$id, $limit)]);
    }

    // ───────────────── App-internal audit log ──────────────────

    public function auditLog(): void
    {
        ApiAuth::require('read');
        $limit = max(1, min(500, (int)($_GET['limit'] ?? 100)));
        try {
            $rows = DB::fetchAll(
                "SELECT actor, action, module, detail, ip_address, created_at
                 FROM app_audit_log ORDER BY id DESC LIMIT " . $limit
            );
            self::json(['entries' => $rows ?: []]);
        } catch (\Throwable $e) { self::err($e); }
    }

    // ───────────────── Helpers ──────────────────────────────────

    private static function json(array $data, bool $pretty = false): void
    {
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
            header('Cache-Control: no-store');
        }
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | ($pretty ? JSON_PRETTY_PRINT : 0));
    }

    private static function err(\Throwable $e): never
    {
        http_response_code(500);
        if (!headers_sent()) header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['error' => 'internal_error', 'message' => $e->getMessage()]);
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
