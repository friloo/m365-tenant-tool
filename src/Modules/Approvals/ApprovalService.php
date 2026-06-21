<?php

namespace App\Modules\Approvals;

use App\Auth\LocalAuth;
use App\Core\AppAudit;
use App\Core\Config;
use App\Database\DB;
use App\Modules\Notifications\NotificationService;

/**
 * Optional four-eyes (dual-control) gate for critical actions.
 *
 * Flow when enabled (config four_eyes_enabled = '1'):
 *   1. Admin A triggers a critical action → gate() creates a PENDING request
 *      and returns false (the controller aborts with "submitted for approval").
 *   2. A *different* admin B approves it on the Approvals page.
 *   3. Admin A re-triggers the same action → gate() finds the approved request,
 *      consumes it (status → executed) and returns true, so the action runs.
 *
 * No deferred execution / replay is involved — the action only proceeds once a
 * valid, recent (≤24h) approval by a second person exists for the exact
 * (action_key, target) pair. When the gate is disabled, gate() is a no-op
 * that returns true.
 */
class ApprovalService
{
    /** Approvals are only valid for this many hours after being granted. */
    private const APPROVAL_TTL_HOURS = 24;

    public static function enabled(): bool
    {
        return (string)Config::getInstance()->get('four_eyes_enabled', '0') === '1';
    }

    /**
     * Gate a critical action. Returns true if it may proceed now.
     *
     * @param string $actionKey stable action id, e.g. 'device_wipe'
     * @param string $target    the object the action applies to (id), for matching
     * @param string $label     human-readable description shown to the approver
     */
    public static function gate(string $actionKey, string $target, string $label): bool
    {
        if (!self::enabled()) {
            return true;
        }
        $target = (string)$target;

        // 1. A recent, approved-but-unconsumed request → consume and allow.
        $approved = DB::fetchOne(
            "SELECT id FROM app_approval_requests
             WHERE action_key = ? AND target = ? AND status = 'approved'
               AND decided_at > DATE_SUB(NOW(), INTERVAL ? HOUR)
             ORDER BY id DESC LIMIT 1",
            [$actionKey, $target, self::APPROVAL_TTL_HOURS]
        );
        if ($approved) {
            DB::execute(
                "UPDATE app_approval_requests SET status = 'executed', decided_at = NOW() WHERE id = ?",
                [(int)$approved['id']]
            );
            AppAudit::log('approval_executed', 'approvals', $actionKey . ' / ' . $target);
            return true;
        }

        // 2. Otherwise ensure a pending request exists and block.
        $pending = DB::fetchOne(
            "SELECT id FROM app_approval_requests WHERE action_key = ? AND target = ? AND status = 'pending' LIMIT 1",
            [$actionKey, $target]
        );
        if (!$pending) {
            DB::execute(
                "INSERT INTO app_approval_requests (action_key, target, label, requested_by) VALUES (?, ?, ?, ?)",
                [$actionKey, $target, mb_substr($label, 0, 255), LocalAuth::username()]
            );
            AppAudit::log('approval_requested', 'approvals', $actionKey . ' / ' . $target . ' :: ' . $label);
            NotificationService::push(
                t('Freigabe angefordert: :label', ['label' => $label]),
                t('Ein zweiter Administrator muss diese kritische Aktion freigeben.'),
                'warn',
                '/approvals',
                'approvals'
            );
        }
        return false;
    }

    /** @return list<array<string,mixed>> */
    public static function listPending(): array
    {
        try {
            return DB::fetchAll(
                "SELECT * FROM app_approval_requests WHERE status = 'pending' ORDER BY requested_at DESC"
            ) ?: [];
        } catch (\Throwable) { return []; }
    }

    /** @return list<array<string,mixed>> */
    public static function listRecent(int $limit = 30): array
    {
        try {
            return DB::fetchAll(
                "SELECT * FROM app_approval_requests WHERE status <> 'pending'
                 ORDER BY decided_at DESC, id DESC LIMIT " . max(1, min(100, $limit))
            ) ?: [];
        } catch (\Throwable) { return []; }
    }

    public static function pendingCount(): int
    {
        try {
            $r = DB::fetchOne("SELECT COUNT(*) AS c FROM app_approval_requests WHERE status = 'pending'");
            return (int)($r['c'] ?? 0);
        } catch (\Throwable) { return 0; }
    }

    /**
     * Approve a pending request. The approver MUST differ from the requester
     * (that's the whole point of four-eyes).
     *
     * @return string '' on success, otherwise an error code ('not_found'|'self')
     */
    public static function approve(int $id): string
    {
        $row = DB::fetchOne("SELECT * FROM app_approval_requests WHERE id = ? AND status = 'pending'", [$id]);
        if (!$row) return 'not_found';
        if (LocalAuth::username() === $row['requested_by']) return 'self';

        DB::execute(
            "UPDATE app_approval_requests SET status = 'approved', approved_by = ?, decided_at = NOW() WHERE id = ?",
            [LocalAuth::username(), $id]
        );
        AppAudit::log('approval_approved', 'approvals', $row['action_key'] . ' / ' . $row['target']);
        NotificationService::push(
            t('Freigabe erteilt: :label', ['label' => $row['label']]),
            t('Die Aktion kann jetzt von :who erneut ausgelöst und ausgeführt werden.', ['who' => $row['requested_by']]),
            'info',
            '/approvals',
            'approvals'
        );
        return '';
    }

    public static function reject(int $id): bool
    {
        $row = DB::fetchOne("SELECT * FROM app_approval_requests WHERE id = ? AND status = 'pending'", [$id]);
        if (!$row) return false;
        DB::execute(
            "UPDATE app_approval_requests SET status = 'rejected', approved_by = ?, decided_at = NOW() WHERE id = ?",
            [LocalAuth::username(), $id]
        );
        AppAudit::log('approval_rejected', 'approvals', $row['action_key'] . ' / ' . $row['target']);
        return true;
    }
}
