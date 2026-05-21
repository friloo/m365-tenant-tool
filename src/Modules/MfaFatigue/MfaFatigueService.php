<?php

namespace App\Modules\MfaFatigue;

use App\Graph\GraphClient;

/**
 * MFA-Fatigue-Erkennung — wenn ein Angreifer ein gestohlenes Passwort hat,
 * triggert er wiederholt MFA-Push-Notifications, in der Hoffnung dass der
 * User irgendwann genervt approved. Microsoft hat dieses Angriffsmuster
 * 2022 mit Number-Matching teilweise entschärft, aber die Signatur "viele
 * Denials in kurzer Zeit" ist nach wie vor ein starker Kompromittierungs­
 * indikator.
 *
 * Wir scannen den Sign-in-Log und gruppieren pro User die Denials in
 * 30-Minuten-Cluster. Ein Cluster mit >= 5 Denials wird gemeldet, ein
 * Cluster mit nachfolgendem Success wird als 'erfolgreicher Angriff'
 * markiert.
 */
class MfaFatigueService
{
    public function __construct(private GraphClient $graph) {}

    /**
     * @return array{
     *   period_hours: int,
     *   total_denials: int,
     *   suspicious_users: int,
     *   successful_attacks: int,
     *   clusters: array<int, array<string,mixed>>
     * }
     */
    public function scan(int $hours = 168, int $denyThreshold = 5): array
    {
        $sinceIso = (new \DateTimeImmutable('-' . $hours . ' hours'))->format('Y-m-d\TH:i:s\Z');

        try {
            $events = $this->graph->paginate(
                '/auditLogs/signIns',
                [
                    '$filter' => "createdDateTime ge {$sinceIso}",
                    '$top'    => '999',
                ],
                10,
                'mfa_fatigue_signins',
                900
            );
        } catch (\Throwable $e) {
            error_log('MfaFatigue scan: ' . $e->getMessage());
            return $this->emptyResult($hours);
        }

        // Pro User Liste der MFA-bezogenen Events
        $byUser = [];
        foreach ($events as $ev) {
            $upn = $ev['userPrincipalName'] ?? '';
            if ($upn === '') continue;
            $ts  = strtotime($ev['createdDateTime'] ?? '');
            if (!$ts) continue;

            $authDetails = $ev['authenticationDetails'] ?? [];
            $hasMfa = false;
            $denied = false;
            foreach ($authDetails as $d) {
                $method = strtolower((string)($d['authenticationMethod'] ?? ''));
                $detail = strtolower((string)($d['authenticationStepResultDetail'] ?? ''));
                if (str_contains($method, 'app notification')
                 || str_contains($method, 'authenticator')
                 || str_contains($method, 'phone')
                 || str_contains($method, 'sms')) {
                    $hasMfa = true;
                    if (str_contains($detail, 'denied')
                     || str_contains($detail, 'declined')
                     || str_contains($detail, 'user did not approve')
                     || str_contains($detail, 'fatigue')) {
                        $denied = true;
                    }
                }
            }
            if (!$hasMfa) continue;

            $byUser[$upn][] = [
                'ts'       => $ts,
                'denied'   => $denied,
                'success'  => ($ev['status']['errorCode'] ?? null) === 0 && !$denied,
                'country'  => $ev['location']['countryOrRegion'] ?? '',
                'app'      => $ev['appDisplayName'] ?? '',
            ];
        }

        $clusters = [];
        $totalDenials = 0;
        $successfulAttacks = 0;
        foreach ($byUser as $upn => $list) {
            usort($list, fn($a, $b) => $a['ts'] <=> $b['ts']);
            $window = [];
            foreach ($list as $i => $ev) {
                if (!$ev['denied'] && !$ev['success']) continue;

                // Pruning: nur Events in den letzten 30 min im Fenster halten
                $window = array_filter($window, fn($w) => $ev['ts'] - $w['ts'] <= 1800);

                if ($ev['denied']) {
                    $window[] = $ev;
                    $totalDenials++;
                }

                // Cluster: >= N Denials, optional gefolgt von Success
                $denials = array_filter($window, fn($w) => $w['denied']);
                if (count($denials) >= $denyThreshold) {
                    $firstTs = min(array_map(fn($w) => $w['ts'], $denials));
                    $lastTs  = max(array_map(fn($w) => $w['ts'], $denials));
                    $clusterKey = $upn . '_' . $firstTs;
                    if (!isset($clusters[$clusterKey])) {
                        $clusters[$clusterKey] = [
                            'upn'           => $upn,
                            'started'       => date('Y-m-d H:i', $firstTs),
                            'last_denial'   => date('Y-m-d H:i', $lastTs),
                            'denial_count'  => count($denials),
                            'success_after' => false,
                            'app'           => $ev['app'] ?: '–',
                        ];
                    } else {
                        $clusters[$clusterKey]['denial_count']  = count($denials);
                        $clusters[$clusterKey]['last_denial']   = date('Y-m-d H:i', $lastTs);
                    }
                    if ($ev['success']) {
                        $clusters[$clusterKey]['success_after'] = date('Y-m-d H:i', $ev['ts']);
                        $successfulAttacks++;
                    }
                }
            }
        }

        // Sort: erfolgreicher Angriff zuerst, dann nach Denial-Anzahl absteigend
        usort($clusters, function ($a, $b) {
            if ($a['success_after'] xor $b['success_after']) return $a['success_after'] ? -1 : 1;
            return $b['denial_count'] <=> $a['denial_count'];
        });

        return [
            'period_hours'       => $hours,
            'total_denials'      => $totalDenials,
            'suspicious_users'   => count($clusters),
            'successful_attacks' => $successfulAttacks,
            'clusters'           => array_values($clusters),
        ];
    }

    private function emptyResult(int $hours): array
    {
        return [
            'period_hours'       => $hours,
            'total_denials'      => 0,
            'suspicious_users'   => 0,
            'successful_attacks' => 0,
            'clusters'           => [],
        ];
    }
}
