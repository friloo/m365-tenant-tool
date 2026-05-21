<?php

namespace App\Modules\Lifecycle;

use App\Graph\GraphClient;

/**
 * Entra ID Lifecycle Workflows — automatisierte Joiner/Mover/Leaver-
 * Prozesse. Liest die definierten Workflows und ihre letzten Runs.
 *
 * Lizenz-Voraussetzung: Microsoft Entra ID Governance.
 *
 * Endpoint: /identityGovernance/lifecycleWorkflows/workflows (beta)
 */
class LifecycleService
{
    public function __construct(private GraphClient $graph) {}

    public function listWorkflows(): array
    {
        try {
            $data = $this->graph->paginate(
                'https://graph.microsoft.com/beta/identityGovernance/lifecycleWorkflows/workflows',
                ['$top' => '50'],
                3,
                'lifecycle_workflows',
                3600
            );
        } catch (\Throwable $e) {
            error_log('Lifecycle list: ' . $e->getMessage());
            return [];
        }
        $result = [];
        foreach ($data as $w) {
            $result[] = [
                'id'             => $w['id'] ?? '',
                'displayName'    => $w['displayName'] ?? '–',
                'description'    => $w['description'] ?? '',
                'category'       => $w['category']    ?? '',
                'isEnabled'      => $w['isEnabled']   ?? false,
                'isSchedulingEnabled' => $w['isSchedulingEnabled'] ?? false,
                'createdDateTime'=> $w['createdDateTime'] ?? null,
                'lastModifiedDateTime' => $w['lastModifiedDateTime'] ?? null,
                'taskCount'      => count($w['tasks'] ?? []),
            ];
        }
        usort($result, fn($a, $b) => strcmp($a['displayName'], $b['displayName']));
        return $result;
    }

    public function getLastRuns(string $workflowId, int $count = 5): array
    {
        try {
            $data = $this->graph->get(
                'https://graph.microsoft.com/beta/identityGovernance/lifecycleWorkflows/workflows/' . $workflowId . '/runs',
                ['$top' => (string)$count, '$orderby' => 'startedDateTime desc'],
                null, 0
            );
            return $data['value'] ?? [];
        } catch (\Throwable) {
            return [];
        }
    }
}
