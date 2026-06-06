<?php

namespace App\Modules\DefenderAlerts;

use App\Graph\GraphClient;

class DefenderAlertsService
{
    public function __construct(private GraphClient $graph) {}

    /**
     * Fetch active (non-resolved) Defender alerts.
     * Returns empty array if permission is not granted or on any error.
     */
    public function getAlerts(): array
    {
        try {
            $data = $this->graph->get(
                '/security/alerts_v2',
                [
                    '$filter'  => "status ne 'resolved'",
                    '$orderby' => 'createdDateTime desc',
                    '$top'     => '100',
                    '$select'  => 'id,title,severity,status,category,createdDateTime,lastUpdateDateTime,description,userStates,hostStates,assignedTo,classification,determination',
                ],
                'defender_alerts',
                300
            );
            return $data['value'] ?? [];
        } catch (\Throwable) {
            return [];
        }
    }

    /**
     * Compute summary statistics from an alerts array.
     *
     * @param  array $alerts  Array returned by getAlerts()
     * @return array{bySeverity: array, byStatus: array, byCategory: array}
     */
    public function getStats(array $alerts): array
    {
        $bySeverity = ['high' => 0, 'medium' => 0, 'low' => 0, 'informational' => 0];
        $byStatus   = ['new' => 0, 'inProgress' => 0, 'resolved' => 0];
        $byCategory = [];

        foreach ($alerts as $alert) {
            $severity = strtolower($alert['severity'] ?? '');
            if (array_key_exists($severity, $bySeverity)) {
                $bySeverity[$severity]++;
            }

            $status = $alert['status'] ?? '';
            if (array_key_exists($status, $byStatus)) {
                $byStatus[$status]++;
            }

            $category = $alert['category'] ?? 'Unknown';
            $byCategory[$category] = ($byCategory[$category] ?? 0) + 1;
        }

        arsort($byCategory);

        return [
            'bySeverity' => $bySeverity,
            'byStatus'   => $byStatus,
            'byCategory' => $byCategory,
        ];
    }

    /**
     * Update the status of a single alert.
     * Only 'resolved' and 'inProgress' are valid target statuses.
     */
    public function updateAlertStatus(string $alertId, string $status): void
    {
        if (!in_array($status, ['resolved', 'inProgress'], true)) {
            throw new \InvalidArgumentException("Invalid alert status: {$status}");
        }
        $this->graph->patch("/security/alerts_v2/{$alertId}", ['status' => $status]);
        // The list is cached under 'defender_alerts' (filtered to non-resolved) —
        // bust it so a resolved alert disappears immediately.
        $this->graph->getCache()->forget('defender_alerts');
    }

    /**
     * Return display metadata for a severity level.
     */
    public static function severityMeta(string $severity): array
    {
        return match (strtolower($severity)) {
            'high'          => ['label' => 'Kritisch',  'badge' => 'badge-danger'],
            'medium'        => ['label' => 'Mittel',    'badge' => 'badge-warning'],
            'low'           => ['label' => 'Niedrig',   'badge' => 'badge-info'],
            'informational' => ['label' => 'Info',      'badge' => 'badge-neutral'],
            default         => ['label' => $severity,   'badge' => 'badge-secondary'],
        };
    }
}
