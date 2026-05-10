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
            $data = $this->graph->get(
                "/groups/{$id}/owners",
                ['$select' => 'id,displayName,userPrincipalName,mail'],
                null,
                0
            );
            return $data['value'] ?? [];
        } catch (\Throwable) { return []; }
    }

    public function addMember(string $groupId, string $userId): void
    {
        $this->graph->post("/groups/{$groupId}/members/\$ref", [
            '@odata.id' => "https://graph.microsoft.com/v1.0/directoryObjects/{$userId}",
        ]);
        $this->graph->getCache()->forget("group_members_{$groupId}");
    }

    public function removeMember(string $groupId, string $userId): void
    {
        $this->graph->delete("/groups/{$groupId}/members/{$userId}/\$ref");
        $this->graph->getCache()->forget("group_members_{$groupId}");
    }

    public function createGroup(
        string $displayName,
        string $description,
        string $type,
        bool   $mailEnabled,
        string $mailNickname
    ): array {
        if ($mailNickname === '') {
            $mailNickname = preg_replace('/[^a-z0-9\-]/', '', strtolower(str_replace(' ', '-', $displayName)));
        }

        $payload = [
            'displayName'  => $displayName,
            'description'  => $description,
            'mailNickname' => $mailNickname,
        ];

        switch ($type) {
            case 'm365':
                $payload['groupTypes']      = ['Unified'];
                $payload['mailEnabled']     = true;
                $payload['securityEnabled'] = false;
                break;
            case 'security':
                $payload['groupTypes']      = [];
                $payload['mailEnabled']     = false;
                $payload['securityEnabled'] = true;
                break;
            case 'mail_security':
            default:
                $payload['groupTypes']      = [];
                $payload['mailEnabled']     = true;
                $payload['securityEnabled'] = true;
                break;
        }

        $result = $this->graph->post('/groups', $payload);
        $this->graph->getCache()->forget('groups_all');
        return $result;
    }

    public function deleteGroup(string $groupId): void
    {
        $this->graph->delete("/groups/{$groupId}");
        $this->graph->getCache()->forget('groups_all');
    }

    public function addOwner(string $groupId, string $userId): void
    {
        $this->graph->post("/groups/{$groupId}/owners/\$ref", [
            '@odata.id' => "https://graph.microsoft.com/v1.0/users/{$userId}",
        ]);
    }

    public function removeOwner(string $groupId, string $userId): void
    {
        $this->graph->delete("/groups/{$groupId}/owners/{$userId}/\$ref");
    }

    public function searchUsers(string $query): array
    {
        try {
            return $this->graph->get('/users', [
                '$select' => 'id,displayName,userPrincipalName',
                '$filter' => "startswith(displayName,'{$query}') or startswith(userPrincipalName,'{$query}')",
                '$top'    => '10',
            ]);
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
