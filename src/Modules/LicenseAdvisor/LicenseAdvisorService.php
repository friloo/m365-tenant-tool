<?php

namespace App\Modules\LicenseAdvisor;

use App\Core\Config;
use App\Graph\GraphClient;

class LicenseAdvisorService
{
    const CRITERIA_MAP = [
        'exchange_online' => [
            'label' => 'Exchange Online',
            'plans' => [
                'EXCHANGE_S_ENTERPRISE', 'EXCHANGE_S_STANDARD', 'EXCHANGESTANDARD',
                'EXCHANGE_S_DESKLESS', 'EXCHANGEENTERPRISE', 'EXCHANGE_B_STANDARD',
                'EXCHANGEESSENTIALS', 'EXCHANGE_FOUNDATION',
            ],
        ],
        'office_desktop' => [
            'label' => 'Office Desktop-Apps',
            'plans' => [
                'OFFICESUBSCRIPTION', 'OFFICE_SHARED_COMPUTER_ACTIVATION', 'OFFICE_FORMS_PLAN_2',
            ],
        ],
        'teams' => [
            'label' => 'Microsoft Teams',
            'plans' => ['TEAMS1', 'TEAMS_FREE', 'TEAMS_ESSENTIALS'],
        ],
        'sharepoint' => [
            'label' => 'SharePoint Online',
            'plans' => [
                'SHAREPOINT_S_ENTERPRISE', 'SHAREPOINT_S_STANDARD', 'SHAREPOINTSTANDARD',
                'SHAREPOINTENTERPRISE', 'SHAREPOINTDESKLESS',
            ],
        ],
        'onedrive' => [
            'label' => 'OneDrive for Business',
            'plans' => [
                'ONEDRIVE_BASIC', 'ONEDRIVE_STANDARD', 'ONEDRIVESTANDARD',
                'ONEDRIVE_BUSINESS', 'MYSITEUPGRADE',
            ],
        ],
        'intune' => [
            'label' => 'Intune / Geräteverwaltung',
            'plans' => ['INTUNE_A', 'MDM_CORE', 'INTUNE_A_VL', 'INTUNE_O365'],
        ],
    ];

    const SKU_NAMES = [
        'SPE_E3'                  => 'Microsoft 365 E3',
        'SPE_E5'                  => 'Microsoft 365 E5',
        'SPE_F1'                  => 'Microsoft 365 F1',
        'ENTERPRISEPACK'          => 'Office 365 E3',
        'ENTERPRISEPREMIUM'       => 'Office 365 E5',
        'SPB'                     => 'Microsoft 365 Business Premium',
        'O365_BUSINESS_PREMIUM'   => 'Microsoft 365 Business Standard',
        'O365_BUSINESS_ESSENTIALS'=> 'Microsoft 365 Business Basic',
        'SMB_BUSINESS_PREMIUM'    => 'Microsoft 365 Business Standard',
        'SMB_BUSINESS_ESSENTIALS' => 'Microsoft 365 Business Basic',
        'EXCHANGESTANDARD'        => 'Exchange Online Plan 1',
        'EXCHANGE_S_ENTERPRISE'   => 'Exchange Online Plan 2',
        'TEAMS_ESSENTIALS'        => 'Microsoft Teams Essentials',
        'FLOW_FREE'               => 'Power Automate Free',
    ];

    public function __construct(private GraphClient $graph) {}

    /**
     * Fetches all subscribed SKUs and enriches each with the criteria keys it satisfies.
     */
    public function getSkusWithCriteria(): array
    {
        $data   = $this->graph->get('/subscribedSkus', [], 'license_skus_full', 1800);
        $result = [];

        foreach ($data['value'] ?? [] as $sku) {
            $planNames = array_map(
                fn($p) => strtoupper($p['servicePlanName'] ?? ''),
                $sku['servicePlans'] ?? []
            );

            $metCriteria = [];
            foreach (self::CRITERIA_MAP as $key => $def) {
                foreach ($def['plans'] as $required) {
                    if (in_array($required, $planNames, true)) {
                        $metCriteria[] = $key;
                        break;
                    }
                }
            }

            $consumed  = (int)($sku['consumedUnits'] ?? 0);
            $enabled   = (int)($sku['prepaidUnits']['enabled'] ?? 0);
            $partNum   = $sku['skuPartNumber'] ?? '';

            $result[] = [
                'skuId'       => $sku['skuId'] ?? '',
                'partNumber'  => $partNum,
                'name'        => self::SKU_NAMES[$partNum] ?? str_replace('_', ' ', $partNum),
                'consumed'    => $consumed,
                'total'       => $enabled,
                'available'   => max(0, $enabled - $consumed),
                'suspended'   => (int)($sku['prepaidUnits']['suspended'] ?? 0),
                'pct'         => $enabled > 0 ? round(($consumed / $enabled) * 100) : 0,
                'metCriteria' => $metCriteria,
            ];
        }

        usort($result, fn($a, $b) => $b['consumed'] <=> $a['consumed']);
        return $result;
    }

    /**
     * Returns the array of active criteria keys based on Config toggles.
     */
    public function getActiveCriteria(): array
    {
        $config = Config::getInstance();
        $map = [
            'exchange_online' => 'lic_need_exchange_online',
            'office_desktop'  => 'lic_need_office_desktop',
            'teams'           => 'lic_need_teams',
            'sharepoint'      => 'lic_need_sharepoint',
            'onedrive'        => 'lic_need_onedrive',
            'intune'          => 'lic_need_intune',
        ];

        $active = [];
        foreach ($map as $criterionKey => $configKey) {
            if ($config->get($configKey, '0') === '1') {
                $active[] = $criterionKey;
            }
        }
        return $active;
    }

    /**
     * Filters SKUs that satisfy ALL active criteria.
     */
    public function getMatchingSkus(array $skus, array $activeCriteria): array
    {
        if (empty($activeCriteria)) {
            return [];
        }

        return array_values(array_filter($skus, function ($sku) use ($activeCriteria) {
            foreach ($activeCriteria as $criterion) {
                if (!in_array($criterion, $sku['metCriteria'], true)) {
                    return false;
                }
            }
            return true;
        }));
    }

    /**
     * Analyzes users against the active criteria and matching SKUs.
     *
     * @return array{covered: array, uncovered: array, no_license: array, inactive_wasted: array}
     */
    public function analyzeUsers(array $allUsers, array $allSkus, array $activeCriteria): array
    {
        // Build a quick lookup: skuId -> metCriteria
        $skuCriteriaMap = [];
        foreach ($allSkus as $sku) {
            $skuCriteriaMap[$sku['skuId']] = $sku['metCriteria'];
        }

        // Build set of matching sku IDs (those that cover ALL active criteria)
        $matchingSkuIds = [];
        foreach ($allSkus as $sku) {
            $coversAll = true;
            foreach ($activeCriteria as $criterion) {
                if (!in_array($criterion, $sku['metCriteria'], true)) {
                    $coversAll = false;
                    break;
                }
            }
            if ($coversAll) {
                $matchingSkuIds[$sku['skuId']] = true;
            }
        }

        $covered       = [];
        $uncovered     = [];
        $noLicense     = [];
        $inactiveWasted = [];

        $cutoff = time() - (90 * 86400);

        foreach ($allUsers as $user) {
            if (!($user['accountEnabled'] ?? true)) {
                continue;
            }

            $assignedLicenses = $user['assignedLicenses'] ?? [];

            // No license at all
            if (empty($assignedLicenses)) {
                $noLicense[] = $user;
                continue;
            }

            if (empty($activeCriteria)) {
                $covered[] = $user;
                continue;
            }

            // Compute which criteria this user's licenses cover
            $userCriteria = [];
            foreach ($assignedLicenses as $lic) {
                $skuId = $lic['skuId'] ?? '';
                if (isset($skuCriteriaMap[$skuId])) {
                    foreach ($skuCriteriaMap[$skuId] as $c) {
                        $userCriteria[$c] = true;
                    }
                }
            }

            $missing = [];
            foreach ($activeCriteria as $criterion) {
                if (!isset($userCriteria[$criterion])) {
                    $missing[] = $criterion;
                }
            }

            if (empty($missing)) {
                // Check if user has a matching (full-coverage) license but is inactive >90 days
                $hasMatchingLicense = false;
                foreach ($assignedLicenses as $lic) {
                    if (isset($matchingSkuIds[$lic['skuId'] ?? ''])) {
                        $hasMatchingLicense = true;
                        break;
                    }
                }

                $lastSignIn = $user['signInActivity']['lastSignInDateTime'] ?? null;
                $inactive   = false;
                if ($hasMatchingLicense) {
                    if ($lastSignIn === null) {
                        $inactive = true;
                    } elseif (strtotime($lastSignIn) < $cutoff) {
                        $inactive = true;
                    }
                }

                if ($inactive) {
                    $inactiveWasted[] = $user;
                }

                $covered[] = $user;
            } else {
                $user['missing'] = $missing;
                $uncovered[] = $user;
            }
        }

        return [
            'covered'          => $covered,
            'uncovered'        => $uncovered,
            'no_license'       => $noLicense,
            'inactive_wasted'  => $inactiveWasted,
        ];
    }

    /**
     * Persists the criteria toggles to Config.
     */
    public function saveCriteria(array $post): void
    {
        $config = Config::getInstance();
        $map = [
            'exchange_online' => 'lic_need_exchange_online',
            'office_desktop'  => 'lic_need_office_desktop',
            'teams'           => 'lic_need_teams',
            'sharepoint'      => 'lic_need_sharepoint',
            'onedrive'        => 'lic_need_onedrive',
            'intune'          => 'lic_need_intune',
        ];

        foreach ($map as $criterionKey => $configKey) {
            $value = isset($post['criteria'][$criterionKey]) ? '1' : '0';
            $config->set($configKey, $value);
        }
    }

    /**
     * Fetches all users (reuses the users_all cache populated by UsersService).
     */
    public function getAllUsers(): array
    {
        return $this->graph->paginate(
            '/users',
            [
                '$select' => 'id,displayName,userPrincipalName,accountEnabled,assignedLicenses,signInActivity,department,jobTitle',
                '$top'    => '999',
            ],
            50,
            'users_all',
            900
        );
    }
}
