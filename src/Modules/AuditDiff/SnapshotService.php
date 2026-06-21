<?php

namespace App\Modules\AuditDiff;

use App\Database\DB;
use App\Graph\GraphClient;

/**
 * Captures point-in-time snapshots of the most sensitive tenant settings,
 * stores them as JSON blobs and can render diff views between any two
 * snapshots. The diff highlights every changed field so an auditor can
 * answer "What did somebody touch since last month?" without scrolling
 * through 10 separate admin centers.
 *
 * Snapshots run inside the daily cron job (kind = 'daily') but admins
 * can trigger an on-demand snapshot from the UI as well (kind = 'manual').
 */
class SnapshotService
{
    public function __construct(private GraphClient $graph) {}

    /**
     * Collect a snapshot of all tracked tenant settings. Each section is
     * defensively wrapped so a missing permission (e.g. Defender not
     * licensed) doesn't break the whole capture — that section is just
     * recorded as ['error' => '…'].
     */
    public function capture(string $kind = 'manual'): int
    {
        $payload = [
            'captured_at'    => date('c'),
            'authorization'  => $this->capAuthorization(),
            'security_defaults' => $this->capSecurityDefaults(),
            'auth_methods'   => $this->capAuthMethods(),
            'sharepoint'     => $this->capSharePoint(),
            'organization'   => $this->capOrganization(),
            'conditional_access' => $this->capCondAccess(),
            'admin_roles'    => $this->capAdminRoles(),
            'guest_settings' => $this->capGuestSettings(),
        ];

        DB::execute(
            "INSERT INTO app_tenant_snapshots (kind, payload) VALUES (?, ?)",
            [$kind, json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]
        );
        return (int)DB::lastInsertId();
    }

    /**
     * @return list<array{id:int, kind:string, created_at:string}>
     */
    public static function list(int $limit = 50): array
    {
        try {
            return DB::fetchAll(
                "SELECT id, kind, created_at FROM app_tenant_snapshots
                 ORDER BY id DESC LIMIT " . max(1, min(200, $limit))
            ) ?: [];
        } catch (\Throwable) { return []; }
    }

    public static function load(int $id): ?array
    {
        try {
            $row = DB::fetchOne("SELECT id, kind, created_at, payload FROM app_tenant_snapshots WHERE id = ?", [$id]);
            if (!$row) return null;
            $row['payload'] = json_decode((string)$row['payload'], true) ?: [];
            return $row;
        } catch (\Throwable) { return null; }
    }

    /**
     * Flatten a nested array into dot-paths for diffing.
     * ['a' => ['b' => 1]] → ['a.b' => 1]
     */
    public static function flatten(array $arr, string $prefix = ''): array
    {
        $out = [];
        foreach ($arr as $k => $v) {
            $path = $prefix === '' ? (string)$k : $prefix . '.' . $k;
            if (is_array($v) && !empty($v) && array_keys($v) !== range(0, count($v) - 1)) {
                $out += self::flatten($v, $path);
            } else {
                $out[$path] = $v;
            }
        }
        return $out;
    }

    /**
     * Compare two snapshots, returning a list of diff entries.
     *
     * @return array{added:array<string,mixed>, removed:array<string,mixed>, modified:array<string,array{old:mixed,new:mixed}>}
     */
    public static function diff(array $oldPayload, array $newPayload): array
    {
        $a = self::flatten($oldPayload);
        $b = self::flatten($newPayload);
        $added = $removed = $modified = [];
        foreach ($b as $k => $v) {
            if (str_starts_with($k, 'captured_at')) continue;
            if (!array_key_exists($k, $a)) { $added[$k] = $v; }
            elseif (json_encode($a[$k]) !== json_encode($v)) {
                $modified[$k] = ['old' => $a[$k], 'new' => $v];
            }
        }
        foreach ($a as $k => $v) {
            if (str_starts_with($k, 'captured_at')) continue;
            if (!array_key_exists($k, $b)) { $removed[$k] = $v; }
        }
        return ['added' => $added, 'removed' => $removed, 'modified' => $modified];
    }

    // ── Drift baseline ──────────────────────────────────────────────────────

    private const BASELINE_KEY = 'drift_baseline_snapshot_id';

    /** The snapshot id currently pinned as the configuration baseline, or 0. */
    public static function getBaselineId(): int
    {
        return (int)\App\Core\Config::getInstance()->get(self::BASELINE_KEY, 0);
    }

    public static function setBaselineId(int $id): void
    {
        \App\Core\Config::getInstance()->set(self::BASELINE_KEY, (string)$id);
    }

    /**
     * Diff the latest snapshot against the pinned baseline.
     *
     * @return array{baseline_id:int, latest_id:int, diff:array}|null  null if no baseline / no newer snapshot
     */
    public static function driftAgainstBaseline(): ?array
    {
        $baselineId = self::getBaselineId();
        if ($baselineId <= 0) return null;

        $list = self::list(1);
        $latestId = $list[0]['id'] ?? 0;
        if (!$latestId || $latestId === $baselineId) return null;

        $base   = self::load($baselineId);
        $latest = self::load($latestId);
        if (!$base || !$latest) return null;

        return [
            'baseline_id' => $baselineId,
            'latest_id'   => $latestId,
            'diff'        => self::diff($base['payload'], $latest['payload']),
        ];
    }

    /** Total number of changed fields in a diff result. */
    public static function diffCount(array $diff): int
    {
        return count($diff['added'] ?? []) + count($diff['removed'] ?? []) + count($diff['modified'] ?? []);
    }

    public static function trim(int $keepDays = 365, int $maxRows = 365): int
    {
        $deleted = 0;
        try {
            $deleted += DB::execute(
                "DELETE FROM app_tenant_snapshots WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)",
                [$keepDays]
            );
            $row = DB::fetchOne("SELECT id FROM app_tenant_snapshots ORDER BY id DESC LIMIT 1 OFFSET ?", [$maxRows]);
            if ($row) $deleted += DB::execute("DELETE FROM app_tenant_snapshots WHERE id <= ?", [(int)$row['id']]);
        } catch (\Throwable) {}
        return $deleted;
    }

    // ── Capture helpers ─────────────────────────────────────────────────────

    private function capAuthorization(): array
    {
        try {
            $r = $this->graph->get('/policies/authorizationPolicy', [], 'snap_auth', 0);
            return [
                'allowedToCreateApps'             => $r['defaultUserRolePermissions']['allowedToCreateApps']             ?? null,
                'allowedToCreateSecurityGroups'   => $r['defaultUserRolePermissions']['allowedToCreateSecurityGroups']   ?? null,
                'allowedToCreateTenants'          => $r['defaultUserRolePermissions']['allowedToCreateTenants']          ?? null,
                'allowedToReadOtherUsers'         => $r['defaultUserRolePermissions']['allowedToReadOtherUsers']         ?? null,
                'allowInvitesFrom'                => $r['allowInvitesFrom']                                              ?? null,
                'guestUserRoleId'                 => $r['guestUserRoleId']                                               ?? null,
            ];
        } catch (\Throwable $e) { return ['error' => $e->getMessage()]; }
    }

    private function capSecurityDefaults(): array
    {
        try {
            $r = $this->graph->get('/policies/identitySecurityDefaultsEnforcementPolicy', [], 'snap_secdef', 0);
            return ['isEnabled' => $r['isEnabled'] ?? null];
        } catch (\Throwable $e) { return ['error' => $e->getMessage()]; }
    }

    private function capAuthMethods(): array
    {
        try {
            $r = $this->graph->get('/policies/authenticationMethodsPolicy', [], 'snap_authmethods', 0);
            $out = [];
            foreach ($r['authenticationMethodConfigurations'] ?? [] as $m) {
                $out[$m['id'] ?? 'unknown'] = $m['state'] ?? null;
            }
            return $out;
        } catch (\Throwable $e) { return ['error' => $e->getMessage()]; }
    }

    private function capSharePoint(): array
    {
        try {
            $r = $this->graph->get('/admin/sharepoint/settings', [], 'snap_sp', 0);
            return [
                'sharingCapability'                => $r['sharingCapability']                ?? null,
                'oneDriveSharingCapability'        => $r['oneDriveSharingCapability']        ?? null,
                'defaultSharingLinkType'           => $r['defaultSharingLinkType']           ?? null,
                'defaultLinkPermission'            => $r['defaultLinkPermission']            ?? null,
                'requireAnonymousLinksExpireInDays'=> $r['requireAnonymousLinksExpireInDays']?? null,
                'isResharingByExternalUsersEnabled'=> $r['isResharingByExternalUsersEnabled']?? null,
                'idleSessionSignOut'               => $r['idleSessionSignOut']               ?? null,
            ];
        } catch (\Throwable $e) { return ['error' => $e->getMessage()]; }
    }

    private function capOrganization(): array
    {
        try {
            $r = $this->graph->get('/organization', ['$select' => 'id,displayName,verifiedDomains,onPremisesSyncEnabled,countryLetterCode'], 'snap_org', 0);
            $first = $r['value'][0] ?? [];
            return [
                'displayName'         => $first['displayName']         ?? null,
                'countryLetterCode'   => $first['countryLetterCode']   ?? null,
                'onPremisesSyncEnabled' => $first['onPremisesSyncEnabled'] ?? null,
                'verified_domain_count' => count($first['verifiedDomains'] ?? []),
            ];
        } catch (\Throwable $e) { return ['error' => $e->getMessage()]; }
    }

    private function capCondAccess(): array
    {
        try {
            $r = $this->graph->get('/identity/conditionalAccess/policies', ['$select' => 'id,displayName,state'], 'snap_ca', 0);
            $out = [];
            foreach ($r['value'] ?? [] as $p) {
                $out[(string)$p['displayName']] = $p['state'] ?? null;
            }
            return $out;
        } catch (\Throwable $e) { return ['error' => $e->getMessage()]; }
    }

    private function capAdminRoles(): array
    {
        try {
            $r = $this->graph->get('/roleManagement/directory/roleAssignments',
                ['$count' => 'true', '$top' => '1'], 'snap_admincount', 0);
            return ['total_assignments' => (int)($r['@odata.count'] ?? 0)];
        } catch (\Throwable $e) { return ['error' => $e->getMessage()]; }
    }

    private function capGuestSettings(): array
    {
        try {
            $r = $this->graph->get('/users', [
                '$count' => 'true', '$top' => '1', '$select' => 'id',
                '$filter' => "userType eq 'Guest'",
            ], 'snap_guests', 0);
            return ['total_guests' => (int)($r['@odata.count'] ?? 0)];
        } catch (\Throwable $e) { return ['error' => $e->getMessage()]; }
    }
}
