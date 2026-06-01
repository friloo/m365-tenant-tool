<?php

namespace App\Modules\Offboarding;

use App\Graph\GraphClient;

class OffboardingService
{
    public function __construct(private GraphClient $graph) {}

    /**
     * Search for users by name or UPN.
     */
    public function searchUsers(string $q): array
    {
        try {
            $q      = \App\Graph\GraphClient::escapeODataValue($q);
            $filter = "startsWith(displayName,'{$q}') or startsWith(userPrincipalName,'{$q}')";
            $data   = $this->graph->get(
                '/users',
                [
                    '$filter'  => $filter,
                    '$select'  => 'id,displayName,userPrincipalName,accountEnabled,department,jobTitle,mail',
                    '$top'     => '15',
                ],
                null, // no cache for search
                0
            );
            return $data['value'] ?? [];
        } catch (\Throwable $e) {
            error_log('Offboarding searchUsers: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Fetch full user details including licenses and group memberships.
     */
    public function getUser(string $userId): ?array
    {
        try {
            return $this->graph->get(
                "/users/{$userId}",
                ['$select' => 'id,displayName,userPrincipalName,accountEnabled,department,jobTitle,mail,assignedLicenses,signInActivity,onPremisesSyncEnabled,createdDateTime'],
                null,
                0
            );
        } catch (\Throwable $e) {
            error_log('Offboarding getUser: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Collect offboarding state: which steps are done/pending.
     */
    public function getOffboardingState(?array $user): array
    {
        if (!$user) return [];

        $userId  = $user['id'];
        $enabled = $user['accountEnabled'] ?? true;
        $licenses = $user['assignedLicenses'] ?? [];
        $synced  = $user['onPremisesSyncEnabled'] ?? false;

        // Fetch group memberships
        $groups = [];
        try {
            $gData  = $this->graph->get("/users/{$userId}/memberOf", ['$select' => 'id,displayName,groupTypes'], null, 0);
            $groups = array_filter($gData['value'] ?? [], fn($g) => ($g['@odata.type'] ?? '') === '#microsoft.graph.group');
        } catch (\Throwable) {}

        // Fetch manager
        $manager = null;
        try {
            $m = $this->graph->get("/users/{$userId}/manager", ['$select' => 'displayName,userPrincipalName'], null, 0);
            $manager = $m['displayName'] ?? null;
        } catch (\Throwable) {}

        return [
            'accountEnabled' => $enabled,
            'hasLicenses'    => !empty($licenses),
            'licenseCount'   => count($licenses),
            'groupCount'     => count($groups),
            'groups'         => array_values($groups),
            'manager'        => $manager,
            'synced'         => $synced,
        ];
    }

    /**
     * Disable the user account.
     */
    public function disableAccount(string $userId): void
    {
        $this->graph->patch("/users/{$userId}", ['accountEnabled' => false]);
    }

    /**
     * Revoke all refresh tokens / sessions.
     */
    public function revokeSessions(string $userId): void
    {
        $this->graph->post("/users/{$userId}/revokeSignInSessions", []);
    }

    /**
     * Remove all assigned licenses.
     */
    public function removeAllLicenses(string $userId): void
    {
        $data = $this->graph->get("/users/{$userId}", ['$select' => 'assignedLicenses'], null, 0);
        $licenses = $data['assignedLicenses'] ?? [];
        if (empty($licenses)) return;

        $removeSkuIds = array_map(fn($l) => $l['skuId'], $licenses);
        $this->graph->post("/users/{$userId}/assignLicense", [
            'addLicenses'    => [],
            'removeLicenses' => $removeSkuIds,
        ]);
    }

    /**
     * Remove user from all groups (skip dynamic and synced groups).
     * Returns count of removed groups.
     */
    public function removeFromAllGroups(string $userId): int
    {
        $gData  = $this->graph->get("/users/{$userId}/memberOf", ['$select' => 'id,displayName,groupTypes,membershipRule,onPremisesSyncEnabled'], null, 0);
        $groups = array_filter($gData['value'] ?? [], fn($g) =>
            ($g['@odata.type'] ?? '') === '#microsoft.graph.group'
            && empty($g['membershipRule'])                      // skip dynamic groups
            && ($g['onPremisesSyncEnabled'] ?? null) !== true   // skip on-prem synced groups
        );

        $count = 0;
        foreach ($groups as $group) {
            try {
                $this->graph->delete("/groups/{$group['id']}/members/{$userId}/\$ref");
                $count++;
            } catch (\Throwable $e) {
                error_log('Offboarding removeFromGroup ' . $group['id'] . ': ' . $e->getMessage());
            }
        }
        return $count;
    }
}
