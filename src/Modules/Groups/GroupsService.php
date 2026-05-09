<?php

namespace App\Modules\Groups;

use App\Graph\GraphClient;

class GroupsService
{
    public function __construct(private GraphClient $graph) {}

    public function getAll(): array
    {
        return $this->graph->paginate(
            '/groups',
            [
                '$select' => 'id,displayName,description,groupTypes,mailEnabled,securityEnabled,membershipRule,createdDateTime,mail',
                '$top'    => '999',
            ],
            30,
            'groups_all',
            900
        );
    }

    public function getOne(string $id): array
    {
        return $this->graph->get("/groups/{$id}", [], "group_{$id}", 600);
    }

    public function getMembers(string $id): array
    {
        try {
            return $this->graph->paginate(
                "/groups/{$id}/members",
                ['$select' => 'id,displayName,userPrincipalName'],
                10,
                "group_members_{$id}",
                600
            );
        } catch (\Throwable) { return []; }
    }

    public function getOwners(string $id): array
    {
        try {
            return $this->graph->paginate(
                "/groups/{$id}/owners",
                ['$select' => 'id,displayName,userPrincipalName'],
                5,
                "group_owners_{$id}",
                600
            );
        } catch (\Throwable) { return []; }
    }

    public static function getType(array $group): string
    {
        $types = $group['groupTypes'] ?? [];
        if (in_array('Unified', $types)) return 'M365';
        if ($group['securityEnabled'] && !$group['mailEnabled']) return 'Security';
        if ($group['mailEnabled'] && !$group['securityEnabled']) return 'Distribution';
        return 'Mail-Enabled Security';
    }
}
