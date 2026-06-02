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
        // Reuse MfaMethodsService as the single source for the registration
        // report (modern endpoint + legacy fallback, normalised keys), then
        // reshape into the UPN-keyed map the user list expects.
        $rows = (new \App\Modules\MfaMethods\MfaMethodsService($this->graph))->getAll();

        $map = [];
        foreach ($rows as $row) {
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

    /**
     * Reset a user's password to a generated temporary one (or a supplied value)
     * and require a change at next sign-in. Returns the password so the admin can
     * hand it over once. Needs User.ReadWrite.All.
     */
    public function resetPassword(string $userId, ?string $newPassword = null, bool $forceChange = true): string
    {
        $password = ($newPassword !== null && $newPassword !== '') ? $newPassword : self::generatePassword();
        $this->graph->patch("/users/{$userId}", [
            'passwordProfile' => [
                'password'                      => $password,
                'forceChangePasswordNextSignIn' => $forceChange,
            ],
        ]);
        $this->graph->getCache()->forget("user_{$userId}");
        return $password;
    }

    /** Generate a random password that satisfies Entra complexity (upper/lower/digit/symbol). */
    private static function generatePassword(int $len = 16): string
    {
        $sets = ['ABCDEFGHJKLMNPQRSTUVWXYZ', 'abcdefghijkmnpqrstuvwxyz', '23456789', '!@#$%*?-_'];
        $pw   = '';
        foreach ($sets as $s) { $pw .= $s[random_int(0, strlen($s) - 1)]; }
        $all = implode('', $sets);
        for ($i = strlen($pw); $i < $len; $i++) { $pw .= $all[random_int(0, strlen($all) - 1)]; }
        return str_shuffle($pw);
    }
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
