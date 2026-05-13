<?php

namespace App\Modules\Onboarding;

use App\Graph\GraphClient;

class OnboardingService
{
    public function __construct(private GraphClient $graph) {}

    private function friendlySkuName(string $skuPartNumber): string
    {
        return match ($skuPartNumber) {
            'ENTERPRISEPACK'    => 'Office 365 E3',
            'SPE_E3'            => 'Microsoft 365 E3',
            'SPE_E5'            => 'Microsoft 365 E5',
            'BUSINESS_PREMIUM'  => 'Microsoft 365 Business Premium',
            'EXCHANGESTANDARD'  => 'Exchange Online (Plan 1)',
            'TEAMS_EXPLORATORY' => 'Teams Exploratory',
            default             => $skuPartNumber,
        };
    }

    public function getAvailableLicenses(): array
    {
        $skus = $this->graph->paginate(
            '/subscribedSkus',
            ['$select' => 'skuId,skuPartNumber,consumedUnits,prepaidUnits,servicePlans'],
            5,
            'onboarding_skus',
            900
        );

        $result = [];
        foreach ($skus as $sku) {
            $enabled   = (int)($sku['prepaidUnits']['enabled'] ?? 0);
            $consumed  = (int)($sku['consumedUnits'] ?? 0);
            $available = $enabled - $consumed;
            if ($available <= 0) {
                continue;
            }
            $result[] = [
                'skuId'     => $sku['skuId'],
                'name'      => $this->friendlySkuName($sku['skuPartNumber'] ?? ''),
                'available' => $available,
            ];
        }
        return $result;
    }

    public function getGroups(): array
    {
        // Hinweis: Der OData-Filter "NOT groupTypes/any(…)" gilt bei Microsoft
        // Graph als "advanced query" und würde zusätzlich den Header
        // ConsistencyLevel: eventual + $count=true verlangen. Statt das hier
        // hochzuziehen, holen wir alle Gruppen und filtern client-seitig —
        // dynamische Gruppen sind eine kleine Minderheit, daher unkritisch.
        $groups = $this->graph->paginate(
            '/groups',
            [
                '$select' => 'id,displayName,groupTypes,mailEnabled,resourceProvisioningOptions',
                '$top'    => '100',
            ],
            10,
            'onboarding_groups',
            900
        );

        $result = [];
        foreach ($groups as $g) {
            $groupTypes  = $g['groupTypes'] ?? [];
            // Dynamische Gruppen ausschließen — Mitglieder werden nur über
            // Regeln gesetzt, manuelles Hinzufügen ist nicht möglich.
            if (in_array('DynamicMembership', $groupTypes, true)) continue;
            $provOptions = $g['resourceProvisioningOptions'] ?? [];
            $isTeam      = in_array('Team', $provOptions, true) || in_array('Unified', $groupTypes, true);
            $isSecurity  = !($g['mailEnabled'] ?? false);

            $result[] = [
                'id'          => $g['id'],
                'displayName' => $g['displayName'] ?? '',
                'isTeam'      => $isTeam,
                'isSecurity'  => $isSecurity,
            ];
        }
        // Alphabetisch sortieren — bequemer im Dropdown
        usort($result, fn($a, $b) => strcasecmp($a['displayName'], $b['displayName']));
        return $result;
    }

    public function createUser(array $data): array
    {
        $upn           = $data['userPrincipalName'];
        $atPos         = strpos($upn, '@');
        $mailNickname  = $atPos !== false ? substr($upn, 0, $atPos) : $upn;

        return $this->graph->post('/users', [
            'displayName'       => $data['displayName'],
            'userPrincipalName' => $upn,
            'mailNickname'      => $mailNickname,
            'passwordProfile'   => [
                'forceChangePasswordNextSignIn' => true,
                'password'                      => $data['password'],
            ],
            'accountEnabled'    => true,
            'jobTitle'          => $data['jobTitle'] ?? '',
            'department'        => $data['department'] ?? '',
            'usageLocation'     => $data['usageLocation'] ?? 'DE',
        ]);
    }

    public function assignLicense(string $userId, string $skuId): void
    {
        $this->graph->post("/users/{$userId}/assignLicense", [
            'addLicenses'    => [['skuId' => $skuId]],
            'removeLicenses' => [],
        ]);
    }

    public function addToGroup(string $userId, string $groupId): void
    {
        $this->graph->post("/groups/{$groupId}/members/\$ref", [
            '@odata.id' => "https://graph.microsoft.com/v1.0/directoryObjects/{$userId}",
        ]);
    }

    public function runOnboarding(array $data): array
    {
        $errors = [];
        $user   = null;

        try {
            $user = $this->createUser($data);
        } catch (\Throwable $e) {
            return ['user' => null, 'errors' => ['Benutzer erstellen fehlgeschlagen: ' . $e->getMessage()]];
        }

        $userId = $user['id'] ?? null;

        if (!empty($data['skuId']) && $userId) {
            try {
                $this->assignLicense($userId, $data['skuId']);
            } catch (\Throwable $e) {
                $errors[] = 'Lizenz zuweisen fehlgeschlagen: ' . $e->getMessage();
            }
        }

        foreach ($data['groupIds'] ?? [] as $groupId) {
            if (empty($groupId)) {
                continue;
            }
            try {
                $this->addToGroup($userId, $groupId);
            } catch (\Throwable $e) {
                $errors[] = 'Gruppe ' . $groupId . ' fehlgeschlagen: ' . $e->getMessage();
            }
        }

        return ['user' => $user, 'errors' => $errors];
    }
}
