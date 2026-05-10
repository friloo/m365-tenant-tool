<?php

namespace App\Modules\RiskySignIns;

use App\Graph\GraphClient;

class RiskySignInsService
{
    public function __construct(private GraphClient $graph) {}

    /**
     * Fetch recent risky sign-ins (atRisk or confirmedCompromised).
     * Never cached — always fresh.
     */
    public function getRecentRiskySignIns(int $hours = 168): array
    {
        try {
            $data = $this->graph->get(
                '/auditLogs/signIns',
                [
                    '$filter'  => "riskState eq 'atRisk' or riskState eq 'confirmedCompromised'",
                    '$top'     => '100',
                    '$orderby' => 'createdDateTime desc',
                ]
                // No cache key — fresh data always
            );
            return $data['value'] ?? [];
        } catch (\Throwable) {
            return [];
        }
    }

    /**
     * Fetch recent risk detections from Identity Protection.
     * Cached 5 minutes.
     */
    public function getRiskyDetections(int $limit = 50): array
    {
        try {
            $data = $this->graph->get(
                '/identityProtection/riskyDetections',
                [
                    '$top'     => (string)min($limit, 100),
                    '$orderby' => 'activityDateTime desc',
                    '$select'  => 'id,userId,userDisplayName,userPrincipalName,riskDetail,riskEventType,riskLevel,riskState,activityDateTime,ipAddress,location',
                ],
                'riskydetections_list',
                300
            );
            return $data['value'] ?? [];
        } catch (\Throwable) {
            return [];
        }
    }

    /**
     * Fetch users currently at risk.
     * Cached 5 minutes.
     */
    public function getRiskyUsers(): array
    {
        try {
            $data = $this->graph->get(
                '/identityProtection/riskyUsers',
                [
                    '$filter' => "riskState eq 'atRisk'",
                    '$select' => 'id,userDisplayName,userPrincipalName,riskLevel,riskDetail,riskLastUpdatedDateTime',
                ],
                'riskyusers_atrisk',
                300
            );
            return $data['value'] ?? [];
        } catch (\Throwable) {
            return [];
        }
    }

    /**
     * Compute summary stats from detections array.
     * Returns counts by riskLevel and top 5 riskEventTypes.
     */
    public function getStats(array $detections): array
    {
        $byLevel = ['low' => 0, 'medium' => 0, 'high' => 0, 'hidden' => 0, 'none' => 0];
        $byType  = [];
        $last24h = 0;
        $cutoff  = strtotime('-24 hours');

        foreach ($detections as $d) {
            $level = strtolower($d['riskLevel'] ?? 'none');
            if (isset($byLevel[$level])) {
                $byLevel[$level]++;
            }

            $type = $d['riskEventType'] ?? 'unknown';
            $byType[$type] = ($byType[$type] ?? 0) + 1;

            $actTime = $d['activityDateTime'] ?? null;
            if ($actTime && strtotime($actTime) >= $cutoff) {
                $last24h++;
            }
        }

        arsort($byType);
        $topTypes = array_slice($byType, 0, 5, true);

        return [
            'byLevel'    => $byLevel,
            'topTypes'   => $topTypes,
            'last24h'    => $last24h,
            'total'      => count($detections),
        ];
    }

    /**
     * Confirm user(s) as compromised.
     */
    public function confirmCompromised(string $userId): void
    {
        $this->graph->patch('/identityProtection/riskyUsers/confirmCompromised', [
            'userIds' => [$userId],
        ]);
    }

    /**
     * Dismiss risk for user(s).
     */
    public function dismissRisk(string $userId): void
    {
        $this->graph->patch('/identityProtection/riskyUsers/dismiss', [
            'userIds' => [$userId],
        ]);
    }

    /**
     * Translate riskEventType API value to German label.
     */
    public function formatRiskEventType(string $type): string
    {
        return match ($type) {
            'anonymizedIPAddress'             => 'Anonymisierte IP-Adresse',
            'atypicalTravelActivity'          => 'Ungewöhnliche Reiseaktivität',
            'impossibleTravel'                => 'Unmögliche Reise',
            'unfamiliarFeatures'              => 'Unbekannte Anmeldemerkmale',
            'maliciousIPAddress'              => 'Schädliche IP-Adresse',
            'suspiciousIPAddress'             => 'Verdächtige IP-Adresse',
            'leakedCredentials'               => 'Kompromittierte Anmeldedaten',
            'investigationsThreatIntelligence' => 'Threat Intelligence',
            'passwordSpray'                   => 'Passwort-Spray-Angriff',
            'newCountry'                      => 'Anmeldung aus neuem Land',
            'adminConfirmedUserCompromised'   => 'Admin bestätigt Kompromittierung',
            'mcasSuspiciousInboxManipulationRules' => 'Verdächtige Postfachregeln',
            'suspiciousInboxForwarding'       => 'Verdächtige Weiterleitung',
            default                           => str_replace(['_', 'Az'], [' ', 'AZ'], $type),
        };
    }
}
