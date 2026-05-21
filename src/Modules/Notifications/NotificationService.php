<?php

namespace App\Modules\Notifications;

use App\Core\Session;
use App\Database\DB;

/**
 * Lightweight in-app notifications. Other modules call ::push() to drop a
 * message into the tenant feed; the topbar bell renders them and tracks
 * per-user "last seen" timestamps so unread counts are personalised
 * without storing per-user copies of every notification.
 */
class NotificationService
{
    /** Severity levels accepted by the DB enum. */
    public const LEVELS = ['info', 'success', 'warn', 'critical'];

    /**
     * Add a new notification. Use $dedupeKey to coalesce repeated events
     * (e.g. "risky_signin:user-xyz") so the bell doesn't fill up with
     * dozens of duplicates within an hour.
     *
     * Returns true if a fresh row was inserted, false if the dedupe key
     * collided (= already-known event, silently swallowed).
     */
    public static function push(
        string $title,
        string $body = '',
        string $severity = 'info',
        ?string $link = null,
        string $category = 'system',
        ?string $dedupeKey = null
    ): bool {
        if (!in_array($severity, self::LEVELS, true)) $severity = 'info';
        try {
            DB::execute(
                "INSERT INTO app_notifications (category, severity, title, body, link, dedupe_key)
                 VALUES (?, ?, ?, ?, ?, ?)",
                [$category, $severity, mb_substr($title, 0, 250), $body, $link, $dedupeKey]
            );
            return true;
        } catch (\Throwable $e) {
            // Unique key violation on dedupe — that's the *expected* path
            // for repeated alerts. Anything else also gets swallowed,
            // because notifications are convenience and must never break
            // the originating action.
            return false;
        }
    }

    /**
     * Latest notifications for the bell panel.
     * @return list<array<string,mixed>>
     */
    public static function recent(int $limit = 25): array
    {
        try {
            $rows = DB::fetchAll(
                "SELECT id, category, severity, title, body, link, created_at
                 FROM app_notifications ORDER BY id DESC LIMIT " . max(1, min(100, $limit))
            );
            return $rows ?: [];
        } catch (\Throwable) {
            return [];
        }
    }

    /**
     * Count unread for the current actor.
     */
    public static function unreadCount(): int
    {
        $actor = self::actor();
        try {
            $row = DB::fetchOne(
                "SELECT COUNT(*) AS c FROM app_notifications n
                 LEFT JOIN app_notification_seen s ON s.actor = ?
                 WHERE s.actor IS NULL OR n.created_at > s.last_seen",
                [$actor]
            );
            return (int)($row['c'] ?? 0);
        } catch (\Throwable) {
            return 0;
        }
    }

    /**
     * Mark all current notifications as seen for the active actor.
     */
    public static function markAllSeen(): void
    {
        $actor = self::actor();
        try {
            DB::execute(
                "INSERT INTO app_notification_seen (actor, last_seen) VALUES (?, NOW())
                 ON DUPLICATE KEY UPDATE last_seen = NOW()",
                [$actor]
            );
        } catch (\Throwable) {}
    }

    /**
     * Trim the table — call from a cron job. Keeps the most recent N rows
     * regardless of age, and drops anything older than 90 days regardless
     * of count.
     */
    public static function trim(int $keep = 500, int $maxDays = 90): int
    {
        $deleted = 0;
        try {
            $deleted += DB::execute(
                "DELETE FROM app_notifications WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)",
                [$maxDays]
            );
            $row = DB::fetchOne("SELECT id FROM app_notifications ORDER BY id DESC LIMIT 1 OFFSET ?", [$keep]);
            if ($row) {
                $deleted += DB::execute("DELETE FROM app_notifications WHERE id <= ?", [(int)$row['id']]);
            }
        } catch (\Throwable) {}
        return $deleted;
    }

    private static function actor(): string
    {
        return Session::get('username') ?? Session::get('auth_upn') ?? 'anonymous';
    }
}
