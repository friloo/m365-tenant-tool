<?php

namespace App\Queue;

use App\Database\DB;
use App\Graph\GraphClient;
use App\Modules\Users\UsersService;

class QueueWorker
{
    public function __construct(private GraphClient $graph) {}

    /**
     * Fetch up to $max pending jobs and process them.
     * Returns number of jobs actually processed.
     */
    public function processNext(int $max = 20): int
    {
        $processed = 0;

        for ($i = 0; $i < $max; $i++) {
            $job = $this->fetchNext();
            if (!$job) break;

            $this->execute($job);
            $processed++;
        }

        return $processed;
    }

    /** Pull the next available job and mark it as processing. */
    private function fetchNext(): ?array
    {
        // Claim atomically: pick a candidate id, then claim it with a conditional
        // UPDATE guarded on status='pending'. Only one worker can win that row
        // (MySQL row lock); the loser sees 0 affected and tries the next candidate.
        // This avoids the "UPDATE LIMIT 1 + separate SELECT" race that could hand
        // the same job to two parallel workers.
        for ($try = 0; $try < 25; $try++) {
            $cand = DB::fetchOne(
                "SELECT id FROM job_queue
                 WHERE status = 'pending' AND available_at <= NOW()
                 ORDER BY id ASC LIMIT 1"
            );
            if (!$cand) return null;

            $id = (int)$cand['id'];
            $affected = DB::execute(
                "UPDATE job_queue
                 SET status = 'processing', attempts = attempts + 1, updated_at = NOW()
                 WHERE id = ? AND status = 'pending'",
                [$id]
            );

            if ($affected === 1) {
                return DB::fetchOne("SELECT * FROM job_queue WHERE id = ?", [$id]);
            }
            // Lost the race — another worker claimed it. Try the next candidate.
        }

        return null;
    }

    private function execute(array $job): void
    {
        $id      = (int)$job['id'];
        $type    = $job['job_type'];
        $payload = json_decode($job['payload'], true) ?? [];
        $maxAtt  = (int)($job['max_attempts'] ?? 3);
        $attempt = (int)($job['attempts'] ?? 1);

        try {
            $this->handle($type, $payload);

            DB::execute(
                "UPDATE job_queue SET status = 'done', processed_at = NOW(), updated_at = NOW() WHERE id = ?",
                [$id]
            );
        } catch (\Throwable $e) {
            $failed = $attempt >= $maxAtt;
            DB::execute(
                "UPDATE job_queue
                 SET status = ?, last_error = ?, updated_at = NOW(),
                     available_at = DATE_ADD(NOW(), INTERVAL ? SECOND)
                 WHERE id = ?",
                [
                    $failed ? 'failed' : 'pending',
                    substr($e->getMessage(), 0, 2000),
                    min(60 * (2 ** $attempt), 3600), // exponential backoff, max 1h
                    $id,
                ]
            );
        }
    }

    /**
     * Route job type to the correct handler.
     * All handlers throw on failure so the worker can record the error.
     */
    private function handle(string $type, array $payload): void
    {
        $service = new UsersService($this->graph);

        match($type) {
            'license_assign' => $service->assignLicense(
                $payload['user_id'],
                $payload['sku_id']
            ),
            'license_remove' => $service->removeLicense(
                $payload['user_id'],
                $payload['sku_id']
            ),
            'user_toggle' => $service->setAccountEnabled(
                $payload['user_id'],
                (bool)$payload['enabled']
            ),
            'mfa_reset' => $service->resetMfa($payload['user_id']),
            default => throw new \InvalidArgumentException("Unknown job type: {$type}"),
        };
    }

    /** Return recent queue items for the UI (mixed statuses). */
    public static function recentItems(int $limit = 50): array
    {
        return DB::fetchAll(
            "SELECT * FROM job_queue ORDER BY id DESC LIMIT ?",
            [$limit]
        );
    }
}
