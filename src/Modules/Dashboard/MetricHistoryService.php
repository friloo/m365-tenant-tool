<?php

namespace App\Modules\Dashboard;

use App\Database\DB;

/**
 * Persists daily snapshots of dashboard KPIs so we can draw 7-day
 * sparklines next to each metric and surface trend deltas like
 * "MFA: 87% ↑ 3 pp diese Woche".
 *
 * The dashboard controller calls ::record() once per day per metric;
 * the view layer calls ::history()/::trend() to render the chart.
 */
class MetricHistoryService
{
    /**
     * Store today's value, overwriting any earlier entry for the same
     * day (so calling this multiple times per day is idempotent and
     * shows the most recent reading).
     */
    public static function record(string $metric, float $value, ?string $day = null): void
    {
        $day = $day ?: date('Y-m-d');
        try {
            DB::execute(
                "INSERT INTO app_metric_history (metric, day, value) VALUES (?, ?, ?)
                 ON DUPLICATE KEY UPDATE value = VALUES(value)",
                [$metric, $day, $value]
            );
        } catch (\Throwable) {}
    }

    /**
     * Convenience: record an array of metric => value pairs.
     */
    public static function recordMany(array $values, ?string $day = null): void
    {
        foreach ($values as $k => $v) {
            if ($v === null || !is_numeric($v)) continue;
            self::record($k, (float)$v, $day);
        }
    }

    /**
     * Returns last $days values for one metric, padded with the
     * previous-day's value on missing days so the sparkline always
     * has $days points (= continuous line, not dotted).
     *
     * @return list<float>
     */
    public static function history(string $metric, int $days = 7): array
    {
        $days = max(2, min(90, $days));
        try {
            $rows = DB::fetchAll(
                "SELECT day, value FROM app_metric_history
                 WHERE metric = ? AND day >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
                 ORDER BY day ASC",
                [$metric, $days - 1]
            );
        } catch (\Throwable) { $rows = []; }
        if (empty($rows)) return [];

        $byDay = [];
        foreach ($rows as $r) $byDay[$r['day']] = (float)$r['value'];

        $out = [];
        $last = (float)reset($rows)['value'];
        for ($i = $days - 1; $i >= 0; $i--) {
            $d = date('Y-m-d', strtotime("-{$i} days"));
            if (isset($byDay[$d])) $last = $byDay[$d];
            $out[] = $last;
        }
        return $out;
    }

    /**
     * 7-day-ago vs. today percentage delta, suitable for an arrow badge.
     *
     * @return array{first:?float, last:?float, delta:?float, dir:string}
     */
    public static function trend(string $metric, int $days = 7): array
    {
        $hist = self::history($metric, $days);
        if (count($hist) < 2) return ['first' => null, 'last' => null, 'delta' => null, 'dir' => 'flat'];
        $first = $hist[0];
        $last  = end($hist);
        if ($first == 0.0) {
            $delta = $last == 0.0 ? 0.0 : 100.0;
        } else {
            $delta = (($last - $first) / abs($first)) * 100.0;
        }
        $dir = abs($delta) < 1.0 ? 'flat' : ($delta > 0 ? 'up' : 'down');
        return ['first' => $first, 'last' => $last, 'delta' => $delta, 'dir' => $dir];
    }

    /**
     * Render an HTML <span> wrapping a canvas-sparkline + a trend badge.
     * Use with: <?= MetricHistoryService::sparkline('total_users') ?>
     */
    public static function sparkline(string $metric, int $days = 7, ?string $color = null): string
    {
        $hist = self::history($metric, $days);
        if (count($hist) < 2) return '';
        $trend = self::trend($metric, $days);
        $values = implode(',', array_map(fn($v) => rtrim(rtrim(number_format($v, 4, '.', ''), '0'), '.'), $hist));
        $color = $color ?? '#0078d4';

        $delta = $trend['delta'];
        $dir   = $trend['dir'];
        $sign  = $delta > 0 ? '+' : '';
        $deltaHtml = $delta === null ? '' :
            '<span class="sparkline-delta ' . $dir . '">'
            . ($dir === 'up' ? '↑ ' : ($dir === 'down' ? '↓ ' : '→ '))
            . htmlspecialchars($sign . number_format($delta, 1, ',', '.') . '%')
            . '</span>';

        return '<span class="sparkline-wrap">'
             . '<canvas class="sparkline-canvas" data-values="' . htmlspecialchars($values, ENT_QUOTES) . '" data-color="' . htmlspecialchars($color, ENT_QUOTES) . '"></canvas>'
             . $deltaHtml
             . '</span>';
    }
}
