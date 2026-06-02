<?php

namespace App\Modules\AdminRoles;

use App\Graph\GraphClient;

class AdminRolesService
{
    public function __construct(private GraphClient $graph) {}

    /**
     * Returns directory roles with their members (legacy endpoint).
     * Only roles with at least one member are included.
     */
    public function getRoleAssignments(): array
    {
        try {
            $data = $this->graph->get(
                '/directoryRoles',
                ['$select' => 'id,displayName,description,roleTemplateId'],
                'admin_roles_list',
                1800
            );
            $roles = $data['value'] ?? [];
        } catch (\Throwable) {
            return [];
        }

        $result = [];
        foreach ($roles as $role) {
            $roleId = $role['id'] ?? '';
            if ($roleId === '') {
                continue;
            }

            try {
                $membersData = $this->graph->get(
                    '/directoryRoles/' . $roleId . '/members',
                    ['$select' => 'id,displayName,userPrincipalName,accountEnabled'],
                    null,
                    null
                );
                $members = $membersData['value'] ?? [];
            } catch (\Throwable) {
                $members = [];
            }

            if (count($members) > 0) {
                $role['members'] = $members;
                $result[] = $role;
            }
        }

        return $result;
    }

    /**
     * Assign a role to a user via the unified RBAC API.
     *
     * @throws \Throwable on failure
     */
    public function assignRole(string $userId, string $roleTemplateId): void
    {
        $this->graph->post('/roleManagement/directory/roleAssignments', [
            'principalId'      => $userId,
            'roleDefinitionId' => $roleTemplateId,
            'directoryScopeId' => '/',
        ]);
        $this->bustAssignmentCache();
    }

    /**
     * Remove a role assignment by its assignment ID.
     *
     * @throws \Throwable on failure
     */
    public function removeRoleAssignment(string $assignmentId): void
    {
        $this->graph->delete('/roleManagement/directory/roleAssignments/' . $assignmentId);
        $this->bustAssignmentCache();
    }

    /** Invalidate cached assignment lists so the UI reflects writes immediately. */
    private function bustAssignmentCache(): void
    {
        $cache = $this->graph->getCache();
        $cache->forget('admin_role_assignments');
        $cache->forget('admin_roles_list');
    }

    /**
     * Returns all enabled built-in role definitions, sorted by displayName.
     */
    public function getAllRoleDefinitions(): array
    {
        try {
            $data = $this->graph->get(
                '/roleManagement/directory/roleDefinitions',
                [
                    '$select' => 'id,displayName,description,isBuiltIn,isEnabled',
                    '$top'    => '500',
                ],
                'admin_role_definitions',
                3600
            );
            $definitions = $data['value'] ?? [];
        } catch (\Throwable) {
            return [];
        }

        // Filter disabled roles in PHP to avoid OData $filter header requirements
        $definitions = array_values(array_filter($definitions, fn($d) => $d['isEnabled'] ?? true));

        usort($definitions, fn($a, $b) => strcmp($a['displayName'] ?? '', $b['displayName'] ?? ''));

        return $definitions;
    }

    /**
     * Returns role assignments expanded with principal details.
     */
    public function getRoleAssignmentsWithPrincipals(): array
    {
        try {
            $data = $this->graph->get(
                '/roleManagement/directory/roleAssignments',
                [
                    '$expand' => 'principal($select=id,displayName,userPrincipalName,accountEnabled)',
                    '$select' => 'id,principalId,roleDefinitionId,directoryScopeId',
                ],
                'admin_role_assignments',
                600
            );
            return $data['value'] ?? [];
        } catch (\Throwable) {
            return [];
        }
    }
}
