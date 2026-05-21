<?php

namespace App\Modules\PhishingSim;

use App\Graph\GraphClient;

/**
 * Microsoft Defender Attack Simulation Training — übersicht der durch-
 * geführten Phishing-Simulationen mit Click-Quoten und Reporting-Quoten.
 *
 * Endpoint ist aktuell nur in der beta-API verfügbar: /security/
 * attackSimulation/simulations
 *
 * Lizenz-Voraussetzung: Microsoft Defender for Office 365 Plan 2 (oder
 * E5/Microsoft 365 E5).
 */
class PhishingSimService
{
    public function __construct(private GraphClient $graph) {}

    public function listSimulations(): array
    {
        try {
            $data = $this->graph->paginate(
                'https://graph.microsoft.com/beta/security/attackSimulation/simulations',
                ['$top' => '50'],
                3,
                'phishing_sims',
                3600
            );
        } catch (\Throwable $e) {
            error_log('PhishingSim list: ' . $e->getMessage());
            return [];
        }
        $result = [];
        foreach ($data as $s) {
            $result[] = [
                'id'                => $s['id'] ?? '',
                'displayName'       => $s['displayName'] ?? '–',
                'status'            => $s['status']      ?? '',
                'launchDateTime'    => $s['launchDateTime']    ?? null,
                'completionDateTime'=> $s['completionDateTime']?? null,
                'description'       => $s['description'] ?? '',
                'attackType'        => $s['attackType']  ?? '',
                'attackTechnique'   => $s['attackTechnique'] ?? '',
                'payloadDeliveryPlatform' => $s['payloadDeliveryPlatform'] ?? '',
                'isAutomated'       => $s['isAutomated'] ?? false,
            ];
        }
        usort($result, fn($a, $b) => strcmp(
            $b['launchDateTime'] ?? '', $a['launchDateTime'] ?? ''
        ));
        return $result;
    }

    public function getSimulationStats(string $simId): ?array
    {
        try {
            $report = $this->graph->get(
                'https://graph.microsoft.com/beta/security/attackSimulation/simulations/' . $simId . '/report/overview',
                [],
                null, 0
            );
            return [
                'recipientsCount'      => $report['recipientsCount']       ?? 0,
                'clickCount'           => $report['clickCount']            ?? 0,
                'compromisedCount'     => $report['compromisedCount']      ?? 0,
                'readCount'            => $report['readCount']             ?? 0,
                'reportedPhishCount'   => $report['reportedPhishCount']    ?? 0,
                'trainingsAssigned'    => $report['trainingsAssignedCount']?? 0,
                'trainingsCompleted'   => $report['trainingsCompletedCount']?? 0,
            ];
        } catch (\Throwable) {
            return null;
        }
    }
}
