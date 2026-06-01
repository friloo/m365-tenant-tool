<?php

namespace App\Modules\LicenseAdvisor;

use App\Core\Config;
use App\Graph\GraphClient;
use App\Helpers\SkuCatalog;

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

    /**
     * Static catalog of common Microsoft 365 SKUs with their feature coverage
     * and list prices in EUR (NETTO, ohne MwSt., pro Nutzer/Monat, Jahresabo).
     *
     * Quellen: Microsoft Deutschland Preisseite, Stand Mai 2025.
     * NPO-Preise: Microsoft for Nonprofits / Tech for Social Impact (DE).
     * Angaben sind Netto-Listenpreise — Partnerrabatte und CSP-Preise können abweichen.
     * Bitte beim Microsoft-Partner verifizieren.
     */
    const LICENSE_CATALOG = [
        'SPB' => [
            'name'          => 'Microsoft 365 Business Premium',
            'criteria'      => ['exchange_online', 'office_desktop', 'teams', 'sharepoint', 'onedrive', 'intune'],
            'price_eur'     => 22.00,
            'price_npo_eur' => 5.50,
            'tier'          => 'Business',
            'max_users'     => 300,
        ],
        'O365_BUSINESS_PREMIUM' => [
            'name'          => 'Microsoft 365 Business Standard',
            'criteria'      => ['exchange_online', 'office_desktop', 'teams', 'sharepoint', 'onedrive'],
            'price_eur'     => 12.50,
            'price_npo_eur' => 3.00,
            'tier'          => 'Business',
            'max_users'     => 300,
        ],
        'O365_BUSINESS_ESSENTIALS' => [
            'name'          => 'Microsoft 365 Business Basic',
            'criteria'      => ['exchange_online', 'teams', 'sharepoint', 'onedrive'],
            'price_eur'     => 6.00,
            'price_npo_eur' => 1.60, // erste 10 Seats gratis, danach ca. 1,60 €
            'tier'          => 'Business',
            'max_users'     => 300,
        ],
        'O365_BUSINESS' => [
            'name'          => 'Microsoft 365 Apps for Business',
            'criteria'      => ['office_desktop'],
            'price_eur'     => 10.50,
            'price_npo_eur' => 4.50,
            'tier'          => 'Business',
            'max_users'     => 300,
        ],
        'SPE_E3' => [
            'name'          => 'Microsoft 365 E3',
            'criteria'      => ['exchange_online', 'office_desktop', 'teams', 'sharepoint', 'onedrive', 'intune'],
            'price_eur'     => 36.00,
            'price_npo_eur' => 10.50,
            'tier'          => 'Enterprise',
        ],
        'SPE_E5' => [
            'name'          => 'Microsoft 365 E5',
            'criteria'      => ['exchange_online', 'office_desktop', 'teams', 'sharepoint', 'onedrive', 'intune'],
            'price_eur'     => 57.00,
            'price_npo_eur' => 16.10,
            'tier'          => 'Enterprise',
        ],
        'ENTERPRISEPACK' => [
            'name'          => 'Office 365 E3',
            'criteria'      => ['exchange_online', 'office_desktop', 'teams', 'sharepoint', 'onedrive'],
            'price_eur'     => 24.00,
            'price_npo_eur' => 4.50,
            'tier'          => 'Enterprise',
        ],
        'ENTERPRISEPREMIUM' => [
            'name'          => 'Office 365 E5',
            'criteria'      => ['exchange_online', 'office_desktop', 'teams', 'sharepoint', 'onedrive'],
            'price_eur'     => 38.00,
            'price_npo_eur' => 11.80,
            'tier'          => 'Enterprise',
        ],
        'STANDARDPACK' => [
            'name'          => 'Office 365 E1',
            'criteria'      => ['exchange_online', 'teams', 'sharepoint', 'onedrive'],
            'price_eur'     => 8.00,
            'price_npo_eur' => 1.60, // erste 10 Seats gratis
            'tier'          => 'Enterprise',
        ],
        'SPE_F1' => [
            'name'          => 'Microsoft 365 F1',
            'criteria'      => ['exchange_online', 'teams', 'sharepoint', 'onedrive', 'intune'],
            'price_eur'     => 2.25,
            'price_npo_eur' => 0.00, // gratis für NPO
            'tier'          => 'Frontline',
        ],
        'SPE_F3' => [
            'name'          => 'Microsoft 365 F3',
            'criteria'      => ['exchange_online', 'office_desktop', 'teams', 'sharepoint', 'onedrive', 'intune'],
            'price_eur'     => 8.00,
            'price_npo_eur' => 2.25,
            'tier'          => 'Frontline',
        ],
        'EXCHANGESTANDARD' => [
            'name'          => 'Exchange Online Plan 1',
            'criteria'      => ['exchange_online'],
            'price_eur'     => 4.20,
            'price_npo_eur' => 1.60,
            'tier'          => 'Standalone',
        ],
        'EXCHANGEENTERPRISE' => [
            'name'          => 'Exchange Online Plan 2',
            'criteria'      => ['exchange_online'],
            'price_eur'     => 8.40,
            'price_npo_eur' => 3.00,
            'tier'          => 'Standalone',
        ],
        'TEAMS_ESSENTIALS' => [
            'name'          => 'Microsoft Teams Essentials',
            'criteria'      => ['teams'],
            'price_eur'     => 4.20,
            'price_npo_eur' => null,
            'tier'          => 'Standalone',
        ],
        'INTUNE_A' => [
            'name'          => 'Microsoft Intune Plan 1',
            'criteria'      => ['intune'],
            'price_eur'     => 8.00,
            'price_npo_eur' => null,
            'tier'          => 'Standalone',
        ],
        'EMS' => [
            'name'          => 'Enterprise Mobility + Security E3',
            'criteria'      => ['intune'],
            'price_eur'     => 9.40,
            'price_npo_eur' => 2.10,
            'tier'          => 'Standalone',
        ],
    ];

    public function __construct(private GraphClient $graph) {}

    /**
     * Returns price_eur and price_npo_eur for a SKU part number,
     * preferring admin-configured values from Config over catalog defaults.
     */
    public function resolvePrice(string $partNum): array
    {
        $config  = Config::getInstance();
        $catalog = self::LICENSE_CATALOG[$partNum] ?? null;

        $eur    = $config->get('lic_price_eur_' . $partNum, '');
        $npo    = $config->get('lic_price_npo_eur_' . $partNum, '');

        return [
            'price_eur'     => $eur !== '' ? (float)$eur : ($catalog['price_eur']     ?? null),
            'price_npo_eur' => $npo !== '' ? (float)$npo : ($catalog['price_npo_eur'] ?? null),
        ];
    }

    /**
     * Save admin-configured prices for all catalog SKUs.
     * Called from Settings controller.
     */
    public function savePrices(array $post): void
    {
        $config = Config::getInstance();
        foreach (array_keys(self::LICENSE_CATALOG) as $partNum) {
            $eur = trim($post['price_eur'][$partNum] ?? '');
            $npo = trim($post['price_npo_eur'][$partNum] ?? '');
            // Allow empty string to mean "use catalog default"
            $config->set('lic_price_eur_' . $partNum,     $eur);
            $config->set('lic_price_npo_eur_' . $partNum, $npo);
        }
    }

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
            $catalog   = self::LICENSE_CATALOG[$partNum] ?? null;
            $prices    = $this->resolvePrice($partNum);

            $result[] = [
                'skuId'         => $sku['skuId'] ?? '',
                'partNumber'    => $partNum,
                'name'          => SkuCatalog::name($partNum),
                'consumed'      => $consumed,
                'total'         => $enabled,
                'available'     => max(0, $enabled - $consumed),
                'suspended'     => (int)($sku['prepaidUnits']['suspended'] ?? 0),
                'pct'           => $enabled > 0 ? round(($consumed / $enabled) * 100) : 0,
                'metCriteria'   => $metCriteria,
                'inTenant'      => true,
                'price_eur'     => $prices['price_eur'],
                'price_npo_eur' => $prices['price_npo_eur'],
                'tier'          => $catalog['tier'] ?? null,
            ];
        }

        usort($result, fn($a, $b) => $b['consumed'] <=> $a['consumed']);
        return $result;
    }

    /**
     * Returns SKUs from the static catalog that are NOT currently in the tenant.
     * Used to suggest alternative licenses the customer could buy.
     *
     * @param array $tenantSkus Result of getSkusWithCriteria()
     */
    public function getCatalogOnlySkus(array $tenantSkus): array
    {
        $owned = array_flip(array_column($tenantSkus, 'partNumber'));
        $result = [];
        foreach (self::LICENSE_CATALOG as $partNum => $def) {
            if (isset($owned[$partNum])) continue;
            $prices = $this->resolvePrice($partNum);
            $result[] = [
                'skuId'         => '',
                'partNumber'    => $partNum,
                'name'          => $def['name'],
                'consumed'      => 0,
                'total'         => 0,
                'available'     => 0,
                'suspended'     => 0,
                'pct'           => 0,
                'metCriteria'   => $def['criteria'],
                'inTenant'      => false,
                'price_eur'     => $prices['price_eur'],
                'price_npo_eur' => $prices['price_npo_eur'],
                'tier'          => $def['tier'] ?? null,
            ];
        }
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
                // Disabled but still licensed = the most wasteful case → surface it
                // instead of skipping. Disabled + unlicensed is genuinely irrelevant.
                if (!empty($user['assignedLicenses'] ?? [])) {
                    $inactiveWasted[] = $user;
                }
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
