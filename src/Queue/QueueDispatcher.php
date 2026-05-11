<?php

namespace App\Queue;

use App\Database\DB;

class QueueDispatcher
{
    /**
     * Push a job onto the async queue.
     *
     * @param string $jobType   One of: license_assign|license_remove|user_toggle|mfa_reset
     * @param array  $payload   Arbitrary data passed to the handler
     * @param int    $delay     Seconds to delay execution (0 = immediate)
     * @param int    $maxAttempts  How many times to retry on failure
     */
    public static function dispatch(
        string $jobType,
        array  $payload,
        int    $delay       = 0,
        int    $maxAttempts = 3
    ): int {
        DB::execute(
            'INSERT INTO job_queue (job_type, payload, status, max_attempts, available_at)
             VALUES (?, ?, \'pending\', ?, DATE_ADD(NOW(), INTERVAL ? SECOND))',
            [$jobType, json_encode($payload, JSON_UNESCAPED_UNICODE), $maxAttempts, $delay]
        );
        return (int)DB::lastInsertId();
    }

    /** Dispatch multiple jobs atomically. Returns count dispatched. */
    public static function dispatchBatch(string $jobType, array $payloads, int $maxAttempts = 3): int
    {
        $count = 0;
        foreach ($payloads as $payload) {
            self::dispatch($jobType, $payload, 0, $maxAttempts);
            $count++;
        }
        return $count;
    }

    /** Count pending jobs (optionally by type). */
    public static function pendingCount(?string $jobType = null): int
    {
        if ($jobType) {
            $row = DB::fetchOne(
                "SELECT COUNT(*) AS cnt FROM job_queue WHERE status = 'pending' AND job_type = ?",
                [$jobType]
            );
        } else {
            $row = DB::fetchOne("SELECT COUNT(*) AS cnt FROM job_queue WHERE status = 'pending'");
        }
        return (int)($row['cnt'] ?? 0);
    }

    /** Summary stats for the queue UI. */
    public static function stats(): array
    {
        $rows = DB::fetchAll(
            "SELECT status, COUNT(*) AS cnt FROM job_queue GROUP BY status"
        );
        $map = ['pending' => 0, 'processing' => 0, 'done' => 0, 'failed' => 0];
        foreach ($rows as $r) {
            $map[$r['status']] = (int)$r['cnt'];
        }
        $map['total'] = array_sum($map);
        return $map;
    }

    /** Retry all failed jobs (reset to pending, clear error). */
    public static function retryFailed(): int
    {
        return DB::execute(
            "UPDATE job_queue SET status = 'pending', attempts = 0, last_error = NULL,
             available_at = NOW() WHERE status = 'failed'"
        );
    }

    /** Delete completed jobs older than X hours (housekeeping). */
    public static function pruneCompleted(int $olderThanHours = 24): int
    {
        return DB::execute(
            "DELETE FROM job_queue WHERE status = 'done'
             AND processed_at < DATE_SUB(NOW(), INTERVAL ? HOUR)",
            [$olderThanHours]
        );
    }
}
