<?php

namespace App\Modules\DlpIncidents;

use App\Graph\GraphClient;

/**
 * DLP-Treffer (= echte Vorfälle, nicht Policies) aus den directory audit
 * logs. Erfasst Auslöser, Service und Severity.
 *
 * Endpoint: /auditLogs/directoryAudits mit Filter auf DLP-Kategorie und
 * Aktivitäts­namen. Microsoft schreibt DLP-Treffer auch in das Unified
 * Audit-Log unter /security/auditLog/queries, das setzt aber Microsoft
 * Purview voraus und liefert deutlich mehr Detail — als zweite Quelle
 * sinnvoll, hier aber bewusst weggelassen, um nicht auf eine separate
 * Lizenz angewiesen zu sein.
 */
class DlpIncidentsService
{
    public function __construct(private GraphClient $graph) {}

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getIncidents(int $days = 30, int $max = 200): array
    {
        $since = (new \DateTimeImmutable('-' . $days . ' days'))->format('Y-m-d\TH:i:s\Z');
        try {
            $rows = $this->graph->paginate(
                '/auditLogs/directoryAudits',
                [
                    '$filter' => "activityDateTime ge {$since} and "
                               . "(category eq 'DataLossPrevention' or "
                               . "startswith(activityDisplayName, 'DLP ') or "
                               . "startswith(activityDisplayName, 'Sensitivity label') or "
                               . "activityDisplayName eq 'DlpRuleMatch')",
                    '$top'    => '999',
                ],
                10,
                'dlp_incidents_' . $days . 'd',
                900
            );
        } catch (\Throwable $e) {
            error_log('DLP incidents: ' . $e->getMessage());
            return [];
        }

        $result = [];
        foreach (array_slice($rows, 0, $max) as $r) {
            $result[] = [
                'when'     => $r['activityDateTime'] ?? '',
                'actor'    => $r['initiatedBy']['user']['userPrincipalName']
                           ?? $r['initiatedBy']['app']['displayName']
                           ?? '–',
                'activity' => $r['activityDisplayName'] ?? '',
                'category' => $r['category'] ?? '',
                'result'   => $r['result']   ?? '',
                'target'   => $r['targetResources'][0]['displayName']
                           ?? $r['targetResources'][0]['userPrincipalName']
                           ?? '–',
                'details'  => $this->extractDetails($r),
            ];
        }
        return $result;
    }

    /**
     * Aggregate über die Vorfälle: Top-Auslöser, Top-Aktivitäten, Trend.
     */
    public function summarize(array $incidents): array
    {
        $byActor    = [];
        $byActivity = [];
        $byDay      = [];
        foreach ($incidents as $i) {
            $byActor[$i['actor']]       = ($byActor[$i['actor']] ?? 0) + 1;
            $byActivity[$i['activity']] = ($byActivity[$i['activity']] ?? 0) + 1;
            $day = $i['when'] ? substr($i['when'], 0, 10) : '';
            if ($day !== '') $byDay[$day] = ($byDay[$day] ?? 0) + 1;
        }
        arsort($byActor);
        arsort($byActivity);
        ksort($byDay);
        return [
            'total'        => count($incidents),
            'unique_actors'=> count($byActor),
            'top_actors'   => array_slice($byActor, 0, 5, true),
            'top_activities' => array_slice($byActivity, 0, 5, true),
            'daily_trend'  => $byDay,
        ];
    }

    private function extractDetails(array $row): string
    {
        $additional = $row['additionalDetails'] ?? [];
        $parts = [];
        foreach ($additional as $kv) {
            $key   = $kv['key']   ?? '';
            $value = $kv['value'] ?? '';
            if ($key !== '' && $value !== '') {
                $parts[] = $key . ': ' . (is_string($value) ? $value : json_encode($value));
            }
        }
        return implode(' | ', $parts);
    }
}
