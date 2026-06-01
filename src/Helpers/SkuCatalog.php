<?php

namespace App\Helpers;

/**
 * Single source of truth for Microsoft 365 SKU part-number → friendly name.
 *
 * Previously this map existed (and diverged) in four places: LicensesService,
 * LicenseAdvisorService, DashboardService and OnboardingService — which caused
 * concrete display bugs (e.g. ENTERPRISEPREMIUM shown as "Office 365 E3" instead
 * of E5, and SPE_F1 as "F3" in one module but "F1" in another). Keep all SKU
 * naming here so it stays consistent.
 */
class SkuCatalog
{
    /** part number (uppercase) => human-readable product name */
    public const NAMES = [
        // Microsoft 365 (E/F)
        'SPE_E3'                   => 'Microsoft 365 E3',
        'SPE_E5'                   => 'Microsoft 365 E5',
        'SPE_F1'                   => 'Microsoft 365 F3',
        'SPE_F3'                   => 'Microsoft 365 F3',
        // Office 365 (E)
        'ENTERPRISEPACK'           => 'Office 365 E3',
        'ENTERPRISEPREMIUM'        => 'Office 365 E5',
        'STANDARDPACK'             => 'Office 365 E1',
        'DESKLESSPACK'             => 'Office 365 F3',
        // Business
        'SPB'                      => 'Microsoft 365 Business Premium',
        'BUSINESS_PREMIUM'         => 'Microsoft 365 Business Premium',
        'O365_BUSINESS'            => 'Microsoft 365 Apps for Business',
        'O365_BUSINESS_PREMIUM'    => 'Microsoft 365 Business Standard',
        'O365_BUSINESS_ESSENTIALS' => 'Microsoft 365 Business Basic',
        'SMB_BUSINESS_PREMIUM'     => 'Microsoft 365 Business Standard',
        'SMB_BUSINESS_ESSENTIALS'  => 'Microsoft 365 Business Basic',
        // Teams
        'TEAMS_EXPLORATORY'        => 'Teams Exploratory',
        'TEAMS_ESSENTIALS'         => 'Microsoft Teams Essentials',
        // Exchange
        'EXCHANGESTANDARD'         => 'Exchange Online Plan 1',
        'EXCHANGEENTERPRISE'       => 'Exchange Online Plan 2',
        'EXCHANGE_S_ENTERPRISE'    => 'Exchange Online Plan 2',
        // Entra ID / EMS / Intune
        'AAD_PREMIUM'              => 'Entra ID P1',
        'AAD_PREMIUM_P2'           => 'Entra ID P2',
        'EMS'                      => 'Enterprise Mobility + Security E3',
        'EMSPREMIUM'               => 'Enterprise Mobility + Security E5',
        'INTUNE_A'                 => 'Microsoft Intune Plan 1',
        // Power Platform / BI
        'POWER_BI_STANDARD'        => 'Power BI (Free)',
        'PBI_PREMIUM_P1_ADDON'     => 'Power BI Premium P1',
        'FLOW_FREE'                => 'Power Automate Free',
        // Project / Visio
        'PROJECTPREMIUM'           => 'Project Plan 5',
        'VISIOCLIENT'              => 'Visio Plan 2',
        // Telefonie / Defender
        'MCOSTANDARD'              => 'Skype for Business Online Plan 2',
        'MCOPSTN1'                 => 'Microsoft 365 Domestic Calling Plan',
        'DEFENDER_ENDPOINT_P1'     => 'Microsoft Defender for Endpoint P1',
    ];

    /** Friendly name for a SKU part number, or a humanised fallback. */
    public static function name(?string $partNumber): string
    {
        $key = strtoupper(trim((string)$partNumber));
        return self::NAMES[$key] ?? str_replace('_', ' ', (string)$partNumber);
    }
}
