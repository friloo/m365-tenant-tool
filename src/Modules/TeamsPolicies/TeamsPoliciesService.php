<?php

namespace App\Modules\TeamsPolicies;

use App\Graph\GraphClient;

class TeamsPoliciesService
{
    public function __construct(private GraphClient $graph) {}

    /**
     * Fetch Teams app permission policies (what apps users can install).
     */
    public function getAppPermissionPolicies(): array
    {
        try {
            $data = $this->graph->get('/teamwork/teamsAppSettings', [], 'teams_app_settings', 900);
            return $data ?? [];
        } catch (\Throwable $e) {
            error_log('TeamsPolicies getAppPermissionPolicies: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Fetch meeting policies via the admin Graph API.
     * Note: Full Teams policy management requires PowerShell (Teams module).
     * Via Graph we can fetch teamsApp listings and some settings.
     */
    public function getTeamworkSettings(): array
    {
        try {
            $data = $this->graph->get('/teamwork', [], 'teams_teamwork', 900);
            return $data ?? [];
        } catch (\Throwable $e) {
            error_log('TeamsPolicies getTeamworkSettings: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get Teams communication compliance / messaging policy overview
     * via the organization-level settings.
     */
    public function getOrgMessagingSettings(): array
    {
        try {
            $data = $this->graph->get('/teamwork/workforceIntegrations', [], 'teams_workforce', 900);
            return $data['value'] ?? [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Fetch installed Teams apps at the organization level.
     */
    public function getOrgInstalledApps(): array
    {
        try {
            $data = $this->graph->get(
                '/appCatalogs/teamsApps',
                [
                    '$filter'  => "distributionMethod eq 'organization'",
                    '$select'  => 'id,displayName,distributionMethod,appDefinitions',
                    '$top'     => '50',
                ],
                'teams_org_apps',
                1800
            );
            return $data['value'] ?? [];
        } catch (\Throwable $e) {
            error_log('TeamsPolicies getOrgInstalledApps: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Fetch all Teams in the tenant (basic stats).
     */
    public function getTeamStats(): array
    {
        try {
            $data = $this->graph->get(
                '/groups',
                [
                    '$filter'  => "resourceProvisioningOptions/Any(x:x eq 'Team')",
                    '$select'  => 'id,displayName,visibility,membershipRule,groupTypes,createdDateTime',
                    '$top'     => '999',
                ],
                'teams_group_list',
                1800
            );
            $teams = $data['value'] ?? [];

            $public   = count(array_filter($teams, fn($t) => ($t['visibility'] ?? '') === 'Public'));
            $private  = count(array_filter($teams, fn($t) => ($t['visibility'] ?? '') === 'Private'));
            $dynamic  = count(array_filter($teams, fn($t) => !empty($t['membershipRule'])));

            return [
                'total'   => count($teams),
                'public'  => $public,
                'private' => $private,
                'dynamic' => $dynamic,
                'teams'   => $teams,
            ];
        } catch (\Throwable $e) {
            error_log('TeamsPolicies getTeamStats: ' . $e->getMessage());
            return ['total' => 0, 'public' => 0, 'private' => 0, 'dynamic' => 0, 'teams' => []];
        }
    }

    public function getLastError(): ?array
    {
        return $this->graph->getLastError();
    }
}
