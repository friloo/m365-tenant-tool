<?php

namespace App\Modules\ShareReview;

use App\Core\Config;
use App\Database\DB;
use App\Graph\GraphClient;
use App\Helpers\Mailer;

class ShareReviewService
{
    private int $reviewIntervalDays;
    private int $graceDays;
    private bool $onlyAnonymous;
    private string $baseUrl;
    private ?array $tenantDomains = null;

    public function __construct(private GraphClient $graph)
    {
        $config = Config::getInstance();
        $this->reviewIntervalDays = (int)($config->get('share_review_interval_days', '30'));
        $this->graceDays          = (int)($config->get('share_review_grace_days', '7'));
        $this->onlyAnonymous      = $config->get('share_review_only_anonymous', '0') === '1';
        $this->baseUrl            = rtrim($config->get('app_base_url', ''), '/');
    }

    /** Lower-cased verified tenant domains (cached for the request). */
    private function tenantDomains(): array
    {
        if ($this->tenantDomains !== null) return $this->tenantDomains;
        $domains = [];
        try {
            $data = $this->graph->get('/domains', ['$select' => 'id,isVerified'], 'tenant_domains', 3600);
            foreach ($data['value'] ?? [] as $d) {
                if (($d['isVerified'] ?? false) && !empty($d['id'])) {
                    $domains[] = strtolower($d['id']);
                }
            }
        } catch (\Throwable) {}
        return $this->tenantDomains = $domains;
    }

    /** True if any grantee in a direct ("users") permission is outside the tenant. */
    private function grantHasExternalUser(array $grantedToIdentities): bool
    {
        $domains = $this->tenantDomains();
        // If we can't resolve tenant domains, fail safe: treat as external so a
        // genuine external share is never silently ignored.
        if (empty($domains)) return true;

        foreach ($grantedToIdentities as $identity) {
            $email = strtolower((string)($identity['user']['email'] ?? ''));
            $at    = strrpos($email, '@');
            if ($at === false) continue; // no email → can't prove internal
            $dom = substr($email, $at + 1);
            if (!in_array($dom, $domains, true)) {
                return true; // at least one grantee is external
            }
        }
        return false; // all grantees resolved to internal tenant domains
    }

    // ── Admin: list all tracked shares ──────────────────────

    public function getAllTracked(string $statusFilter = ''): array
    {
        $sql    = 'SELECT * FROM share_reviews';
        $params = [];
        if ($statusFilter) {
            $sql   .= ' WHERE status = ?';
            $params[] = $statusFilter;
        }
        $sql .= ' ORDER BY next_review_at ASC, created_at DESC';
        return DB::fetchAll($sql, $params);
    }

    public function getStats(): array
    {
        $rows = DB::fetchAll('SELECT status, COUNT(*) as cnt FROM share_reviews GROUP BY status');
        $stats = ['active' => 0, 'pending_review' => 0, 'confirmed' => 0, 'revoked' => 0, 'expired' => 0];
        foreach ($rows as $r) {
            // Only count known statuses so an unexpected value can't inflate total.
            if (array_key_exists($r['status'], $stats)) {
                $stats[$r['status']] = (int)$r['cnt'];
            }
        }
        $stats['total'] = array_sum($stats);
        $overdue = DB::fetchOne(
            "SELECT COUNT(*) as cnt FROM share_reviews WHERE auto_revoke_at IS NOT NULL AND auto_revoke_at < NOW() AND status = 'pending_review'"
        );
        $stats['overdue'] = (int)($overdue['cnt'] ?? 0);
        return $stats;
    }

    // ── Cron: scan shares and update DB ─────────────────────

    public function scanAndSync(): array
    {
        $log = [];

        $maxSites = 20;
        try {
            $sites = $this->graph->paginate('/sites', ['search' => '*', '$select' => 'id,displayName'], $maxSites);
        } catch (\Throwable $e) {
            return ["ERROR: Could not fetch sites: " . $e->getMessage()];
        }

        if (count($sites) >= $maxSites) {
            $log[] = "HINWEIS: Aus Performance-Gründen werden nur die ersten {$maxSites} Sites "
                   . "(je 3 Bibliotheken, Ordnertiefe 3) gescannt — die Abdeckung ist nicht vollständig.";
        }

        foreach (array_slice($sites, 0, $maxSites) as $site) {
            try {
                $drives = $this->graph->paginate(
                    "/sites/{$site['id']}/drives",
                    ['$select' => 'id,name'],
                    5
                );
            } catch (\Throwable) { continue; }

            foreach (array_slice($drives, 0, 3) as $drive) {
                // Walk folder tree with permissions expanded inline — no separate permission calls needed
                $this->scanFolder($drive['id'], 'root', $site, $log, 3);
            }
        }

        return $log;
    }

    /**
     * List the children of $folderId with permissions expanded, record any sharing
     * permissions, then recurse into subfolders up to $depth levels.
     */
    private function scanFolder(string $driveId, string $folderId, array $site, array &$log, int $depth): void
    {
        if ($depth < 0) return;

        try {
            $children = $this->graph->paginate(
                "/drives/{$driveId}/items/{$folderId}/children",
                [
                    '$select' => 'id,name,webUrl,createdBy,folder,permissions',
                    '$expand' => 'permissions',
                    '$top'    => '100',
                ],
                3  // max 300 items per folder
            );
        } catch (\Throwable) { return; }

        $subfolders = [];

        foreach ($children as $item) {
            foreach ($item['permissions'] ?? [] as $perm) {
                // Skip inherited/owner-only permissions (no link, no external grant)
                $scope = $perm['link']['scope'] ?? null;
                if (!$scope && !empty($perm['grantedToIdentities'])) {
                    // A direct grant counts as a trackable external share only if at
                    // least one grantee is outside the tenant. Internal-only grants
                    // must NOT be tracked — auto-revoke would strip legitimate access.
                    if (!$this->grantHasExternalUser($perm['grantedToIdentities'])) {
                        continue;
                    }
                    $scope = 'users';
                }
                if (!$scope) continue;
                if ($this->onlyAnonymous && $scope !== 'anonymous') continue;
                if (!in_array($scope, ['anonymous', 'users', 'organization'])) continue;

                $permId = $perm['id'] ?? '';
                $itemId = $item['id'];

                $ownerUpn  = $perm['createdBy']['user']['email']
                          ?? $perm['createdBy']['user']['displayName']
                          ?? ($item['createdBy']['user']['email'] ?? '');
                $ownerName = $perm['createdBy']['user']['displayName']
                          ?? ($item['createdBy']['user']['displayName'] ?? '');

                $existing = DB::fetchOne(
                    'SELECT id, owner_email FROM share_reviews WHERE drive_id=? AND item_id=? AND permission_id=?',
                    [$driveId, $itemId, $permId]
                );

                if (!$existing) {
                    $nextReview = date('Y-m-d H:i:s', strtotime("+{$this->reviewIntervalDays} days"));
                    DB::execute(
                        'INSERT INTO share_reviews
                         (drive_id, item_id, permission_id, item_name, item_url, share_scope,
                          owner_upn, owner_display_name, owner_email, site_name,
                          first_detected, next_review_at, review_interval_days, status)
                         VALUES (?,?,?,?,?,?,?,?,?,?,NOW(),?,?,?)',
                        [
                            $driveId, $itemId, $permId,
                            $item['name'] ?? '', $item['webUrl'] ?? '',
                            $scope, $ownerUpn, $ownerName, $ownerUpn,
                            $site['displayName'] ?? '',
                            $nextReview, $this->reviewIntervalDays, 'active',
                        ]
                    );
                    $log[] = "NEW: {$item['name']} ({$scope}) @ {$site['displayName']}";
                } elseif (empty($existing['owner_email']) && $ownerUpn) {
                    DB::execute(
                        'UPDATE share_reviews SET owner_upn=?, owner_display_name=?, owner_email=? WHERE id=?',
                        [$ownerUpn, $ownerName, $ownerUpn, $existing['id']]
                    );
                }
            }

            if (isset($item['folder'])) {
                $subfolders[] = $item['id'];
            }
        }

        if ($depth > 0) {
            foreach (array_slice($subfolders, 0, 15) as $subfolderId) {
                $this->scanFolder($driveId, $subfolderId, $site, $log, $depth - 1);
            }
        }
    }

    // ── Cron: send review emails for due shares ──────────────

    public function sendDueReviewEmails(): array
    {
        $log = [];

        $due = DB::fetchAll(
            "SELECT * FROM share_reviews
             WHERE status IN ('active','confirmed')
               AND next_review_at <= NOW()
               AND owner_email != ''
             ORDER BY next_review_at ASC
             LIMIT 50"
        );

        foreach ($due as $share) {
            $token    = $this->createToken($share['id']);
            $link     = $this->baseUrl . '/review/' . $token;
            $autoDate = date('d.m.Y', strtotime("+{$this->graceDays} days"));

            $body = $this->buildEmailBody($share, $link, $autoDate);
            $appName = Config::getInstance()->get('app_name', 'M365 Tenant Tool');

            $subject = "[{$appName}] Freigabe-Überprüfung erforderlich: {$share['item_name']}";

            if (Mailer::send($share['owner_email'], $subject, $body)) {
                $autoRevoke = date('Y-m-d H:i:s', strtotime("+{$this->graceDays} days"));
                DB::execute(
                    "UPDATE share_reviews
                     SET status='pending_review', reminder_sent_at=NOW(), auto_revoke_at=?
                     WHERE id=?",
                    [$autoRevoke, $share['id']]
                );
                $log[] = "Review email sent to {$share['owner_email']} for: {$share['item_name']}";
            } else {
                $log[] = "WARN: Failed to send email to {$share['owner_email']} for: {$share['item_name']}";
            }
        }

        return $log;
    }

    // ── Cron: auto-revoke overdue shares ────────────────────

    public function autoRevokeOverdue(): array
    {
        $log = [];

        $overdue = DB::fetchAll(
            "SELECT * FROM share_reviews
             WHERE status = 'pending_review'
               AND auto_revoke_at IS NOT NULL
               AND auto_revoke_at <= NOW()
             LIMIT 50"
        );

        foreach ($overdue as $share) {
            $err = $this->revokeGraphPermission($share);
            DB::execute(
                "UPDATE share_reviews SET status='revoked', revoked_at=NOW() WHERE id=?",
                [$share['id']]
            );
            if ($err === null) {
                $log[] = "AUTO-REVOKED: {$share['item_name']} (owner: {$share['owner_email']})";
                // Notify owner of revocation
                $this->sendRevocationNotice($share);
            } else {
                // Permission may already be gone — already marked revoked above.
                $log[] = "REVOKED (Graph error, may already be removed): {$share['item_name']} — {$err}";
            }
        }

        return $log;
    }

    // ── Token management ────────────────────────────────────

    public function createToken(int $shareReviewId): string
    {
        // Invalidate any existing unused token for this share
        DB::execute(
            'UPDATE share_review_tokens SET expires_at=NOW() WHERE share_review_id=? AND used_at IS NULL',
            [$shareReviewId]
        );

        $token     = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime("+{$this->graceDays} days"));

        DB::execute(
            'INSERT INTO share_review_tokens (share_review_id, token, expires_at) VALUES (?,?,?)',
            [$shareReviewId, $token, $expiresAt]
        );

        return $token;
    }

    public function resolveToken(string $token): ?array
    {
        $row = DB::fetchOne(
            'SELECT t.*, s.* FROM share_review_tokens t
             JOIN share_reviews s ON s.id = t.share_review_id
             WHERE t.token = ?',
            [$token]
        );

        if (!$row) return null;
        if ($row['used_at']) return ['error' => 'used'];
        if (strtotime($row['expires_at']) < time()) return ['error' => 'expired'];

        return $row;
    }

    public function confirmReview(string $token, string $reason): bool
    {
        $data = $this->resolveToken($token);
        if (!$data || isset($data['error'])) return false;

        $shareId = (int)$data['share_review_id'];
        $interval = (int)($data['review_interval_days'] ?? $this->reviewIntervalDays);
        $nextReview = date('Y-m-d H:i:s', strtotime("+{$interval} days"));

        DB::execute(
            "UPDATE share_reviews
             SET status='confirmed', last_reviewed=NOW(), last_review_reason=?,
                 next_review_at=?, reminder_sent_at=NULL, auto_revoke_at=NULL
             WHERE id=?",
            [$reason, $nextReview, $shareId]
        );

        DB::execute(
            'UPDATE share_review_tokens SET used_at=NOW() WHERE token=?',
            [$token]
        );

        return true;
    }

    // ── Admin: manual revoke ─────────────────────────────────

    /**
     * Best-effort revoke of the underlying Graph permission for a share row.
     * Swallows errors (the permission may already be gone). Returns the error
     * message on failure, or null on success — callers decide how to log it.
     */
    private function revokeGraphPermission(array $share): ?string
    {
        try {
            $this->graph->delete(
                "/drives/{$share['drive_id']}/items/{$share['item_id']}/permissions/{$share['permission_id']}"
            );
            return null;
        } catch (\Throwable $e) {
            return $e->getMessage();
        }
    }

    public function manualRevoke(int $id): void
    {
        $share = DB::fetchOne('SELECT * FROM share_reviews WHERE id = ?', [$id]);
        if (!$share) throw new \RuntimeException('Share not found');

        // Only mark as revoked if the Graph delete actually succeeded — otherwise
        // surface the error instead of falsely reporting success.
        $err = $this->revokeGraphPermission($share);
        if ($err !== null) {
            throw new \RuntimeException('Widerruf fehlgeschlagen: ' . $err);
        }

        DB::execute(
            "UPDATE share_reviews SET status='revoked', revoked_at=NOW() WHERE id=?",
            [$id]
        );
    }

    public function sendManualReminder(int $id): bool
    {
        $share = DB::fetchOne('SELECT * FROM share_reviews WHERE id = ?', [$id]);
        if (!$share || !$share['owner_email']) return false;

        // Reset to active so sendDueReviewEmails picks it up, or send directly
        $token    = $this->createToken((int)$share['id']);
        $link     = $this->baseUrl . '/review/' . $token;
        $autoDate = date('d.m.Y', strtotime("+{$this->graceDays} days"));
        $body     = $this->buildEmailBody($share, $link, $autoDate);
        $appName  = Config::getInstance()->get('app_name', 'M365 Tenant Tool');
        $subject  = "[{$appName}] Erinnerung: Freigabe-Überprüfung: {$share['item_name']}";

        $ok = Mailer::send($share['owner_email'], $subject, $body);
        if ($ok) {
            $autoRevoke = date('Y-m-d H:i:s', strtotime("+{$this->graceDays} days"));
            DB::execute(
                "UPDATE share_reviews SET status='pending_review', reminder_sent_at=NOW(), auto_revoke_at=? WHERE id=?",
                [$autoRevoke, $share['id']]
            );
        }
        return $ok;
    }

    // ── Email templates ──────────────────────────────────────

    private function buildEmailBody(array $share, string $link, string $autoDate): string
    {
        $appName  = Config::getInstance()->get('app_name', 'M365 Tenant Tool');
        $itemName = htmlspecialchars($share['item_name'] ?? '');
        $siteName = htmlspecialchars($share['site_name'] ?? '');
        $scope    = match($share['share_scope']) {
            'anonymous'    => '🌐 <strong>Öffentlich (Anyone-Link)</strong> — kein Login erforderlich',
            'users'        => '👥 <strong>Externe Benutzer</strong>',
            'organization' => '🏢 <strong>Organisation</strong>',
            default        => htmlspecialchars($share['share_scope']),
        };

        $itemUrl = $share['item_url'] ? "<a href=\"{$share['item_url']}\" style=\"color:#0078d4;\">Datei öffnen</a>" : '';

        $body = "
            <p>Sie haben eine Datei oder einen Ordner freigegeben, die regelmäßig überprüft werden muss:</p>
            <table style=\"border-collapse:collapse;width:100%;margin:16px 0;\">
                <tr><td style=\"padding:8px;background:#f9fafb;font-weight:600;width:140px;\">Datei/Ordner</td><td style=\"padding:8px;\">{$itemName} {$itemUrl}</td></tr>
                <tr><td style=\"padding:8px;background:#f9fafb;font-weight:600;\">Standort</td><td style=\"padding:8px;\">{$siteName}</td></tr>
                <tr><td style=\"padding:8px;background:#f9fafb;font-weight:600;\">Freigabe-Typ</td><td style=\"padding:8px;\">{$scope}</td></tr>
            </table>
            <p><strong>Ist diese Freigabe noch notwendig?</strong></p>
            <p>Klicken Sie auf den folgenden Link, geben Sie eine kurze Begründung ein und bestätigen Sie — die Freigabe wird dann automatisch um {$share['review_interval_days']} Tage verlängert:</p>
            <p style=\"text-align:center;margin:24px 0;\">
                <a href=\"{$link}\" style=\"background:#0078d4;color:#fff;padding:12px 28px;border-radius:8px;text-decoration:none;font-weight:600;display:inline-block;\">
                    ✓ Freigabe bestätigen
                </a>
            </p>
            <p style=\"color:#6b7280;font-size:13px;\">
                ⚠️ Wenn Sie nicht bis zum <strong>{$autoDate}</strong> reagieren, wird die Freigabe automatisch widerrufen.<br>
                Dieser Link ist personalisiert und kann nur einmal verwendet werden.
            </p>
        ";

        return Mailer::alertTemplate('Freigabe-Überprüfung erforderlich', $body, $appName);
    }

    private function sendRevocationNotice(array $share): void
    {
        if (!$share['owner_email']) return;
        $appName  = Config::getInstance()->get('app_name', 'M365 Tenant Tool');
        $itemName = htmlspecialchars($share['item_name'] ?? '');
        $body = "
            <p>Die folgende Freigabe wurde automatisch widerrufen, da keine Bestätigung erfolgte:</p>
            <table style=\"border-collapse:collapse;width:100%;margin:16px 0;\">
                <tr><td style=\"padding:8px;background:#f9fafb;font-weight:600;width:140px;\">Datei/Ordner</td><td style=\"padding:8px;\">{$itemName}</td></tr>
                <tr><td style=\"padding:8px;background:#f9fafb;font-weight:600;\">Standort</td><td style=\"padding:8px;\">".htmlspecialchars($share['site_name'] ?? '')."</td></tr>
            </table>
            <p style=\"color:#6b7280;font-size:13px;\">Falls diese Freigabe weiterhin benötigt wird, erstellen Sie sie bitte erneut und wenden Sie sich an Ihren Administrator.</p>
        ";
        $subject = "[{$appName}] Freigabe automatisch widerrufen: {$itemName}";
        Mailer::send($share['owner_email'], $subject, Mailer::alertTemplate('Freigabe widerrufen', $body, $appName));
    }
}
