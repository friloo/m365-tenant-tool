<?php

namespace App\Modules\SecureScore;

use App\Graph\GraphClient;

class SecureScoreService
{
    public function __construct(private GraphClient $graph) {}

    /**
     * Fetch the most recent Secure Score entry (with control scores).
     */
    public function getLatest(): array
    {
        try {
            $data = $this->graph->get(
                '/security/secureScores',
                [
                    '$top'    => '1',
                    '$expand' => 'controlScores',
                ],
                'securescore_latest',
                3600
            );
            $items = $data['value'] ?? [];
            return $items[0] ?? [];
        } catch (\Throwable) {
            return [];
        }
    }

    /**
     * Fetch the last 30 Secure Score snapshots and return simplified history rows.
     *
     * @return array<int, array{date: string, currentScore: float, maxScore: float}>
     */
    public function getHistory(int $days = 30): array
    {
        try {
            $data = $this->graph->get(
                '/security/secureScores',
                ['$top' => (string)$days],
                'securescore_history',
                3600
            );
            $items = $data['value'] ?? [];

            // Reverse so oldest first (Graph returns newest first)
            $items = array_reverse($items);

            return array_map(fn($item) => [
                'date'         => substr($item['createdDateTime'] ?? '', 0, 10),
                'currentScore' => (float)($item['currentScore'] ?? 0),
                'maxScore'     => (float)($item['maxScore']     ?? 0),
            ], $items);
        } catch (\Throwable) {
            return [];
        }
    }

    /**
     * Group control scores by category.
     * Returns an associative array keyed by category name.
     *
     * @param  array $controlScores  Raw controlScores from getLatest()
     * @return array<string, array<int, array{controlName: string, score: float, maxScore: float, pct: int}>>
     */
    public function groupByCategory(array $controlScores): array
    {
        $knownCategories = ['Identity', 'Data', 'Device', 'Apps', 'Infrastructure'];
        $groups = array_fill_keys($knownCategories, []);
        $groups['Other'] = [];

        foreach ($controlScores as $c) {
            $category = $c['controlCategory'] ?? 'Other';
            if (!isset($groups[$category])) {
                $groups[$category] = [];
            }
            $max = (float)($c['maxScore'] ?? 0);
            $score = (float)($c['score'] ?? 0);
            $groups[$category][] = [
                'controlName' => $c['controlName'] ?? '',
                'score'       => $score,
                'maxScore'    => $max,
                'pct'         => $max > 0 ? (int)round(($score / $max) * 100) : 0,
            ];
        }

        // Remove empty categories
        return array_filter($groups, fn($entries) => count($entries) > 0);
    }
}
