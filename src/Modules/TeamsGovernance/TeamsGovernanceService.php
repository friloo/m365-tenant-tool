<?php

namespace App\Modules\TeamsGovernance;

use App\Graph\GraphClient;

class TeamsGovernanceService
{
    public function __construct(private GraphClient $graph) {}

    public function getAll(int $inactiveDays = 90): array
    {
        $teams = $this->graph->paginate(
            '/groups',
            [
                '$filter' => "resourceProvisioningOptions/Any(x:x eq 'Team')",
                '$select' => 'id,displayName,createdDateTime,visibility,mailNickname,description',
                '$top'    => '999',
            ],
            20,
            'teams_governance_list',
            1800
        );

        $now    = new \DateTimeImmutable('now');
        $limit  = 100;
        $result = [];

        foreach ($teams as $index => $team) {
            $id = $team['id'] ?? '';

            $hasOwners = null;
            if ($index < $limit) {
                $hasOwners = $this->hasOwners($id);
            }

            $created   = isset($team['createdDateTime'])
                ? new \DateTimeImmutable($team['createdDateTime'])
                : null;
            $ageInDays = $created !== null ? (int)$now->diff($created)->days : 0;

            $result[] = array_merge($team, [
                'hasOwners' => $hasOwners,
                'isOld'     => $ageInDays > $inactiveDays,
                'ageInDays' => $ageInDays,
                'isPublic'  => ($team['visibility'] ?? '') === 'Public',
            ]);
        }

        return $result;
    }

    public function getSummary(array $teams): array
    {
        $total           = count($teams);
        $ownerless       = 0;
        $public          = 0;
        $oldTeams        = 0;
        $recentlyCreated = 0;

        foreach ($teams as $t) {
            if ($t['hasOwners'] === false) {
                $ownerless++;
            }
            if ($t['isPublic'] ?? false) {
                $public++;
            }
            if (($t['ageInDays'] ?? 0) > 90) {
                $oldTeams++;
            }
            if (($t['ageInDays'] ?? 0) < 30) {
                $recentlyCreated++;
            }
        }

        return [
            'total'           => $total,
            'ownerless'       => $ownerless,
            'public'          => $public,
            'oldTeams'        => $oldTeams,
            'recentlyCreated' => $recentlyCreated,
        ];
    }

    private function hasOwners(string $groupId): bool
    {
        try {
            $data = $this->graph->get(
                "/groups/{$groupId}/owners",
                ['$select' => 'id'],
                "group_owners_{$groupId}",
                3600
            );
            return !empty($data['value'] ?? $data);
        } catch (\Throwable) {
            return true;
        }
    }
}
