<?php

namespace App\Modules\TokenLifetime;

use App\Graph\GraphClient;

/**
 * Token-Lifetime + Sign-in-Frequency-Übersicht. Microsoft hat zwei
 * Mechanismen, mit denen sich Token-Gültigkeitsdauern steuern lassen:
 *
 *  1. Sign-in-Frequency in Conditional Access Policies — die häufigste
 *     und empfohlene Methode.
 *  2. Tenant-Default-Token-Lifetime-Policies (Legacy, nicht mehr
 *     anpassbar für die wichtigsten Tokens — Microsoft hat 2021
 *     viele dieser Einstellungen deprecated).
 *
 * Dieses Modul listet alle CA-Policies mit Sign-in-Frequency-Setting
 * und zeigt die globale Default-Konfiguration.
 */
class TokenLifetimeService
{
    public function __construct(private GraphClient $graph) {}

    public function getCaSignInFrequency(): array
    {
        try {
            $policies = \App\Modules\ConditionalAccess\ConditionalAccessService::fetchAllPolicies($this->graph);
        } catch (\Throwable $e) {
            error_log('TokenLifetime CA: ' . $e->getMessage());
            return [];
        }

        $result = [];
        foreach ($policies as $p) {
            $sif = $p['sessionControls']['signInFrequency'] ?? null;
            if (!$sif || !($sif['isEnabled'] ?? false)) continue;
            $result[] = [
                'name'           => $p['displayName'] ?? '–',
                'id'             => $p['id'] ?? '',
                'state'          => $p['state'] ?? '',
                'type'           => $sif['type']  ?? 'days',
                'value'          => (int)($sif['value'] ?? 0),
                'frequency_interval' => $sif['frequencyInterval'] ?? 'timeBased',
                'authentication_type' => $sif['authenticationType'] ?? 'primaryAndSecondaryAuthentication',
            ];
        }
        return $result;
    }

    /**
     * Persistent-Browser-Session Setting aus den CA-Policies extrahieren.
     */
    public function getPersistentBrowserSettings(): array
    {
        try {
            $data = $this->graph->get(
                '/identity/conditionalAccess/policies',
                ['$top' => '200'],
                'tokenlife_ca',
                900
            );
        } catch (\Throwable) {
            return [];
        }
        $result = [];
        foreach ($data['value'] ?? [] as $p) {
            $pb = $p['sessionControls']['persistentBrowser'] ?? null;
            if (!$pb || !($pb['isEnabled'] ?? false)) continue;
            $result[] = [
                'name'  => $p['displayName'] ?? '–',
                'state' => $p['state']       ?? '',
                'mode'  => $pb['mode']       ?? '',
            ];
        }
        return $result;
    }

    /**
     * Empfehlungen ableiten.
     */
    public function getRecommendations(array $caPolicies): array
    {
        $rec = [];
        if (empty($caPolicies)) {
            $rec[] = [
                'severity' => 'high',
                'msg' => 'Keine CA-Policy mit Sign-in-Frequency konfiguriert. Microsoft-Default ist 90 Tage Refresh-Token — für Admin-Konten viel zu lang.',
            ];
        } else {
            foreach ($caPolicies as $p) {
                $hours = $p['type'] === 'days' ? $p['value'] * 24 : $p['value'];
                if ($hours > 24 * 30) {
                    $rec[] = [
                        'severity' => 'medium',
                        'msg' => "CA-Policy \"{$p['name']}\" hat Sign-in-Frequency > 30 Tage — für administrative Apps zu lang.",
                    ];
                }
            }
        }
        return $rec;
    }
}
