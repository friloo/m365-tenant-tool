<?php

namespace App\Modules\Users;

use App\Graph\GraphClient;

class UsersService
{
    public function __construct(private GraphClient $graph) {}

    public function getAll(): array
    {
        return $this->graph->paginate(
            '/users',
            [
                '$select' => 'id,displayName,userPrincipalName,accountEnabled,assignedLicenses,createdDateTime,jobTitle,department,mail',
                '$top'    => '999',
            ],
            50,
            'users_all',
            900
        );
    }

    public function getOne(string $id): array
    {
        return $this->graph->get(
            "/users/{$id}",
            ['$select' => 'id,displayName,userPrincipalName,accountEnabled,assignedLicenses,signInActivity,createdDateTime,jobTitle,department,mail,mobilePhone,usageLocation,assignedPlans'],
            "user_{$id}",
            300
        );
    }

    public function getMfaStatus(): array
    {
        $map = [];

        // Modern endpoint (works on all current tenants)
        try {
            $data = $this->graph->paginate(
                '/reports/authenticationMethods/userRegistrationDetails',
                ['$select' => 'id,userPrincipalName,userDisplayName,isMfaRegistered,isMfaCapable,methodsRegistered,defaultMfaMethod', '$top' => '999'],
                50,
                'mfa_methods_detail',
                1800
            );
            if (!empty($data)) {
                foreach ($data as $row) {
                    $upn = $row['userPrincipalName'] ?? '';
                    if ($upn === '') continue;
                    $map[$upn] = [
                        'mfaRegistered' => $row['isMfaRegistered'] ?? false,
                        'mfaCapable'    => $row['isMfaCapable']    ?? false,
                        'methods'       => $row['methodsRegistered'] ?? [],
                    ];
                }
                return $map;
            }
        } catch (\Throwable) {}

        // Legacy fallback
        try {
            $data = $this->graph->paginate(
                '/reports/credentialUserRegistrationDetails',
                [],
                50,
                'mfa_methods_legacy',
                1800
            );
            foreach ($data as $row) {
                $upn = $row['userPrincipalName'] ?? '';
                if ($upn === '') continue;
                $map[$upn] = [
                    'mfaRegistered' => $row['isMfaRegistered'] ?? false,
                    'mfaCapable'    => $row['isCapable']       ?? ($row['isMfaRegistered'] ?? false),
                    'methods'       => $row['authMethods']     ?? [],
                ];
            }
        } catch (\Throwable) {}

        return $map;
    }

    public function setAccountEnabled(string $userId, bool $enabled): void
    {
        $this->graph->patch("/users/{$userId}", ['accountEnabled' => $enabled]);
        $this->graph->getCache()->forget('users_all');
        $this->graph->getCache()->forget("user_{$userId}");
    }

    public function resetMfa(string $userId): void
    {
        // Retrieve and delete all non-password auth methods
        $methods = $this->graph->get("/users/{$userId}/authentication/methods");
        foreach ($methods['value'] ?? [] as $method) {
            $type = $method['@odata.type'] ?? '';
            if (str_contains($type, 'password')) continue;
            $id = $method['id'] ?? '';
            if (!$id) continue;
            $endpoint = match(true) {
                str_contains($type, 'microsoftAuthenticator') => "/users/{$userId}/authentication/microsoftAuthenticatorMethods/{$id}",
                str_contains($type, 'phone')                  => "/users/{$userId}/authentication/phoneMethods/{$id}",
                str_contains($type, 'fido2')                  => "/users/{$userId}/authentication/fido2Methods/{$id}",
                default => null,
            };
            if ($endpoint) {
                try { $this->graph->delete($endpoint); } catch (\Throwable) {}
            }
        }
        $this->graph->getCache()->forget("user_{$userId}");
    }

    public function assignLicense(string $userId, string $skuId): void
    {
        $this->graph->post("/users/{$userId}/assignLicense", [
            'addLicenses'    => [['skuId' => $skuId, 'disabledPlans' => []]],
            'removeLicenses' => [],
        ]);
        $this->graph->getCache()->forget('users_all');
        $this->graph->getCache()->forget("user_{$userId}");
        $this->graph->getCache()->forget('licenses_users');
    }

    public function removeLicense(string $userId, string $skuId): void
    {
        $this->graph->post("/users/{$userId}/assignLicense", [
            'addLicenses'    => [],
            'removeLicenses' => [$skuId],
        ]);
        $this->graph->getCache()->forget('users_all');
        $this->graph->getCache()->forget("user_{$userId}");
        $this->graph->getCache()->forget('licenses_users');
    }

    public function getMemberOf(string $userId): array
    {
        try {
            return $this->graph->paginate(
                "/users/{$userId}/memberOf",
                ['$select' => 'id,displayName,groupTypes'],
                10,
                "user_groups_{$userId}",
                600
            );
        } catch (\Throwable) { return []; }
    }

    public function getSignInHistory(string $userId): array
    {
        try {
            $result = $this->graph->get(
                '/auditLogs/signIns',
                [
                    '$filter'  => "userId eq '{$userId}'",
                    '$top'     => '25',
                    '$orderby' => 'createdDateTime desc',
                    '$select'  => 'createdDateTime,appDisplayName,ipAddress,location,status,deviceDetail,conditionalAccessStatus,riskEventTypesV2',
                ],
                null,
                0
            );
            return $result['value'] ?? [];
        } catch (\Throwable) { return []; }
    }

    public function updateUser(string $userId, array $data): void
    {
        $this->graph->patch("/users/{$userId}", $data);
        $this->graph->getCache()->forget('users_all');
        $this->graph->getCache()->forget("user_{$userId}");
    }

    public function revokeSignInSessions(string $userId): void
    {
        $this->graph->post("/users/{$userId}/revokeSignInSessions", []);
    }

    public function removeAllLicenses(string $userId): void
    {
        $user = $this->graph->get(
            "/users/{$userId}",
            ['$select' => 'assignedLicenses'],
            null,
            0
        );
        $licenses = $user['assignedLicenses'] ?? [];
        if (empty($licenses)) {
            return;
        }
        $skuIds = array_column($licenses, 'skuId');
        $this->graph->post("/users/{$userId}/assignLicense", [
            'addLicenses'    => [],
            'removeLicenses' => $skuIds,
        ]);
        $this->graph->getCache()->forget('users_all');
        $this->graph->getCache()->forget("user_{$userId}");
        $this->graph->getCache()->forget('licenses_users');
    }

    public function removeFromAllGroups(string $userId): array
    {
        $result = $this->graph->get(
            "/users/{$userId}/memberOf",
            ['$select' => 'id,displayName,groupTypes,membershipRule,onPremisesSyncEnabled'],
            null,
            0
        );
        $memberships = $result['value'] ?? [];
        $removed = [];
        foreach ($memberships as $group) {
            // Skip anything that isn't a real group, plus dynamic and on-prem
            // synced groups (membership there can't be changed via Graph).
            if (($group['@odata.type'] ?? '') !== '#microsoft.graph.group') continue;
            if (($group['onPremisesSyncEnabled'] ?? null) === true) continue;
            if (!empty($group['membershipRule'])) continue;
            $groupId = $group['id'] ?? '';
            if (!$groupId) continue;
            try {
                $this->graph->delete("/groups/{$groupId}/members/{$userId}/\$ref");
                $removed[] = $group['displayName'] ?? $groupId;
            } catch (\Throwable) {}
        }
        $this->graph->getCache()->forget("user_groups_{$userId}");
        return $removed;
    }
}
