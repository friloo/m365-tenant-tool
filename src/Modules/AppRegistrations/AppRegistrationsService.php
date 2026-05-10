<?php

namespace App\Modules\AppRegistrations;

use App\Graph\GraphClient;

class AppRegistrationsService
{
    // Microsoft Graph API resource ID
    private const GRAPH_RESOURCE_ID = '00000003-0000-0000-c000-000000000000';

    // Known high-risk Graph permission role IDs
    private const HIGH_RISK_PERMISSIONS = [
        '62a82d76-70ea-4859-a378-d225e10e96e3' => 'Group.ReadWrite.All',
        '741f803b-c850-494e-b5df-cde7c675a1ca' => 'User.ReadWrite.All',
        '810c84a8-4a9e-49e6-bf7d-12d183f40d01' => 'Mail.Read',
        '01d4889c-1287-42c6-ac1f-5d1e02578ef6' => 'Files.ReadWrite.All',
    ];

    // Lower-risk but notable permissions (still flagged with lower severity)
    private const NOTABLE_PERMISSIONS = [
        'df021288-bdef-4463-88db-98f22de89214' => 'User.Read.All',
    ];

    public function __construct(private GraphClient $graph) {}

    public function getApplications(): array
    {
        try {
            return $this->graph->paginate(
                '/applications',
                ['$select' => 'id,displayName,createdDateTime,signInAudience,requiredResourceAccess,appId'],
                5,
                'appreg_applications',
                1800
            );
        } catch (\Throwable) {
            return [];
        }
    }

    public function getServicePrincipals(): array
    {
        try {
            return $this->graph->paginate(
                '/servicePrincipals',
                [
                    '$select' => 'id,displayName,appId,accountEnabled,tags,createdDateTime,appOwnerOrganizationId,servicePrincipalType',
                    '$filter' => "servicePrincipalType eq 'Application'",
                ],
                5,
                'appreg_serviceprincipals',
                1800
            );
        } catch (\Throwable) {
            return [];
        }
    }

    /**
     * Filters apps that have sensitive permissions and returns array of
     * ['app' => [...], 'riskReasons' => [...]] entries.
     */
    public function getHighRiskApps(array $apps): array
    {
        $highRisk = [];

        foreach ($apps as $app) {
            $riskReasons = [];
            $resourceAccess = $app['requiredResourceAccess'] ?? [];

            foreach ($resourceAccess as $resource) {
                if (($resource['resourceAppId'] ?? '') !== self::GRAPH_RESOURCE_ID) {
                    continue;
                }

                foreach ($resource['resourceAccess'] ?? [] as $perm) {
                    $roleId = $perm['id'] ?? '';
                    if (isset(self::HIGH_RISK_PERMISSIONS[$roleId])) {
                        $riskReasons[] = self::HIGH_RISK_PERMISSIONS[$roleId];
                    }
                }
            }

            if (!empty($riskReasons)) {
                $highRisk[] = [
                    'app'         => $app,
                    'riskReasons' => array_unique($riskReasons),
                ];
            }
        }

        return $highRisk;
    }

    /**
     * Count total permissions across all resource accesses for an app.
     */
    public function countPermissions(array $app): int
    {
        $count = 0;
        foreach ($app['requiredResourceAccess'] ?? [] as $resource) {
            $count += count($resource['resourceAccess'] ?? []);
        }
        return $count;
    }

    public function getAppDetail(string $appId): array
    {
        $data = $this->graph->get(
            '/applications/' . $appId,
            ['$select' => 'id,appId,displayName,passwordCredentials,keyCredentials,createdDateTime,signInAudience,web'],
            null,
            null
        );
        return $data ?? [];
    }

    public function addSecret(string $appId, string $displayName, int $expiryMonths): array
    {
        $endDateTime = (new \DateTimeImmutable('now', new \DateTimeZone('UTC')))
            ->modify("+{$expiryMonths} months")
            ->format('Y-m-d\TH:i:s\Z');

        $result = $this->graph->post(
            '/applications/' . $appId . '/addPassword',
            [
                'passwordCredential' => [
                    'displayName' => $displayName,
                    'endDateTime' => $endDateTime,
                ],
            ]
        );
        return $result ?? [];
    }

    public function deleteSecret(string $appId, string $keyId): void
    {
        $this->graph->post(
            '/applications/' . $appId . '/removePassword',
            ['keyId' => $keyId]
        );
    }
}
