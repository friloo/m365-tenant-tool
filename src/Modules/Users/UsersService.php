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
                '$select' => 'id,displayName,userPrincipalName,accountEnabled,assignedLicenses,signInActivity,createdDateTime,jobTitle,department,mail',
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
        try {
            $data = $this->graph->paginate(
                '/reports/credentialUserRegistrationDetails',
                [],
                50,
                'users_mfa',
                1800
            );
            $map = [];
            foreach ($data as $row) {
                $map[$row['userPrincipalName']] = [
                    'mfaRegistered' => $row['isMfaRegistered'] ?? false,
                    'mfaCapable'    => $row['isMfaCapable'] ?? false,
                    'methods'       => $row['authMethods'] ?? [],
                ];
            }
            return $map;
        } catch (\Throwable) { return []; }
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
}
