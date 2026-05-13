<?php

namespace App\Modules\Anomaly;

use App\Graph\GraphClient;

/**
 * Aggregates the M365 directory audit log into anonymised counters and
 * flags categories that exceed a moving-average baseline. The output is
 * pure numbers — no UPNs, no actor IDs, no IPs, no object names — and
 * can therefore be safely forwarded to an external AI provider.
 */
class AuditLogAnomalyService
{
    public function __construct(private GraphClient $graph) {}

    /**
     * Build a 7-day rollup of the directory audit log plus simple
     * anomaly flags vs. the preceding 23-day baseline.
     *
     * @return array{
     *   period_days:int,
     *   total_events:int,
     *   by_category:array<string,int>,
     *   off_hours_count:int,
     *   failure_count:int,
     *   success_count:int,
     *   anomalies:array<int,array{category:string,count:int,baseline_avg:float,sigma:float}>,
     *   note:string
     * }
     */
    public function summarize(int $recentDays = 7, int $baselineDays = 23): array
    {
        // Hard cap: a single Graph page is 1000 events; we paginate via the
        // GraphClient's built-in nextLink follower. 30 days * 1000 = 30000
        // is more than enough for small/mid tenants. Bigger tenants would
        // need a queue-backed warehouse, which is out of scope here.
        $sinceIso = (new \DateTimeImmutable('-' . ($recentDays + $baselineDays) . ' days'))
            ->format('Y-m-d\TH:i:s\Z');

        try {
            $events = $this->graph->paginate(
                '/auditLogs/directoryAudits',
                [
                    '$filter' => "activityDateTime ge {$sinceIso}",
                    '$select' => 'category,activityDateTime,result',
                    '$top'    => '1000',
                ],
                30,                          // up to 30 pages = 30k events
                'audit_anomaly_30d',
                900                          // 15 min cache
            );
        } catch (\Throwable $e) {
            return $this->emptyResult($recentDays, 'Audit-Log nicht abrufbar: ' . $e->getMessage());
        }

        if (empty($events)) {
            return $this->emptyResult($recentDays, 'Keine Audit-Events im Zeitraum.');
        }

        $now = time();
        $recentCutoff   = $now - $recentDays   * 86400;
        $baselineCutoff = $recentCutoff - $baselineDays * 86400;

        $recentByCat   = [];
        $baselineByCat = [];
        $recentTotal   = 0;
        $offHours      = 0;
        $failures      = 0;
        $successes     = 0;

        foreach ($events as $ev) {
            $ts = strtotime($ev['activityDateTime'] ?? '');
            if (!$ts || $ts < $baselineCutoff) continue;
            $cat = $this->normaliseCategory($ev['category'] ?? 'other');
            if ($ts >= $recentCutoff) {
                $recentByCat[$cat] = ($recentByCat[$cat] ?? 0) + 1;
                $recentTotal++;
                $hour = (int)date('G', $ts);
                if ($hour < 6 || $hour >= 22) $offHours++;
                $result = strtolower((string)($ev['result'] ?? ''));
                if ($result === 'failure') $failures++;
                elseif ($result === 'success') $successes++;
            } else {
                $baselineByCat[$cat] = ($baselineByCat[$cat] ?? 0) + 1;
            }
        }

        // Anomaly detection: for each category, compare daily rate in the
        // recent window with the per-day average over the baseline window.
        // A category is flagged when it exceeds (avg + 2*sqrt(avg)) — a
        // Poisson-like threshold that needs no historical variance.
        $anomalies = [];
        $perDayRecent = max(1, $recentDays);
        foreach ($recentByCat as $cat => $count) {
            $baseTotal = $baselineByCat[$cat] ?? 0;
            $baseAvgPerDay = $baseTotal / max(1, $baselineDays);
            $recentAvgPerDay = $count / $perDayRecent;
            $threshold = $baseAvgPerDay + 2 * sqrt(max(1, $baseAvgPerDay));
            if ($recentAvgPerDay > $threshold && $count >= 5) {
                $sigma = $baseAvgPerDay > 0
                    ? ($recentAvgPerDay - $baseAvgPerDay) / sqrt($baseAvgPerDay)
                    : 0;
                $anomalies[] = [
                    'category'     => $cat,
                    'count'        => $count,
                    'baseline_avg' => round($baseAvgPerDay, 2),
                    'sigma'        => round($sigma, 1),
                ];
            }
        }

        // Sort by severity (sigma desc)
        usort($anomalies, fn($a, $b) => $b['sigma'] <=> $a['sigma']);

        // Trim by_category to top-20 to keep payload small
        arsort($recentByCat);
        $topCats = array_slice($recentByCat, 0, 20, true);

        return [
            'period_days'     => $recentDays,
            'baseline_days'   => $baselineDays,
            'total_events'    => $recentTotal,
            'by_category'     => $topCats,
            'off_hours_count' => $offHours,
            'failure_count'   => $failures,
            'success_count'   => $successes,
            'anomalies'       => array_slice($anomalies, 0, 10),
            'note'            => 'Aggregierte Counts pro Kategorie, keine personenbezogenen Daten.',
        ];
    }

    private function emptyResult(int $days, string $note): array
    {
        return [
            'period_days'     => $days,
            'baseline_days'   => 0,
            'total_events'    => 0,
            'by_category'     => [],
            'off_hours_count' => 0,
            'failure_count'   => 0,
            'success_count'   => 0,
            'anomalies'       => [],
            'note'            => $note,
        ];
    }

    /**
     * Reduce free-form category strings to a stable, small vocabulary.
     * Avoids leaking custom category names (which are tenant-agnostic, but
     * still — we keep the surface small for the AI).
     */
    private function normaliseCategory(string $raw): string
    {
        $raw = strtolower(trim($raw));
        return match (true) {
            $raw === ''                                  => 'other',
            str_contains($raw, 'user')                   => 'usermanagement',
            str_contains($raw, 'group')                  => 'groupmanagement',
            str_contains($raw, 'application')            => 'application',
            str_contains($raw, 'directory')              => 'directory',
            str_contains($raw, 'policy')                 => 'policy',
            str_contains($raw, 'role')                   => 'rolemanagement',
            str_contains($raw, 'device')                 => 'device',
            str_contains($raw, 'auth')                   => 'authentication',
            str_contains($raw, 'consent')                => 'consent',
            default                                      => $raw,
        };
    }
}
