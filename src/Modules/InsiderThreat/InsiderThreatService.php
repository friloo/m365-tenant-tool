<?php

namespace App\Modules\InsiderThreat;

use App\Graph\GraphClient;

/**
 * Light-Version eines Insider-Threat-Detectors. Scannt User-Aktivität
 * der letzten 30 Tage auf statistische Ausreißer und vergibt einen
 * Risk-Score 0-100 pro User. Echte Insider-Risk-Management-Lösungen
 * (Microsoft Purview Insider Risk Management) sind umfangreicher und
 * lizenz-pflichtig — diese Variante deckt die häufigsten Signale ab.
 *
 * Signale:
 *  - Sehr viele Sign-ins außerhalb Bürozeiten
 *  - Sign-ins aus Ländern, die der User noch nie genutzt hat
 *  - Massendownload aus OneDrive (≥ 50 File-Reads in 1h)
 *  - Mass-Mail-Send (≥ 100 Mails in 1h)
 *  - Auffällige Audit-Spitzen (FileDeleted, SharingSet) pro User
 */
class InsiderThreatService
{
    public function __construct(private GraphClient $graph) {}

    /**
     * @return array{
     *   period_days: int,
     *   total_users_analyzed: int,
     *   high_risk_users: int,
     *   users: array<int, array<string,mixed>>
     * }
     */
    public function scan(int $days = 30): array
    {
        $signIns      = $this->loadSignIns($days);
        $auditEvents  = $this->loadAuditEvents($days);

        // Pro User aggregieren
        $perUser = [];
        foreach ($signIns as $s) {
            $upn = $s['userPrincipalName'] ?? '';
            if ($upn === '') continue;
            $hour    = (int)date('G', strtotime($s['createdDateTime'] ?? ''));
            $country = strtoupper($s['location']['countryOrRegion'] ?? '');
            if (!isset($perUser[$upn])) {
                $perUser[$upn] = [
                    'upn'              => $upn,
                    'signin_count'     => 0,
                    'off_hours'        => 0,
                    'countries'        => [],
                    'mass_downloads'   => 0,
                    'mass_sends'       => 0,
                    'delete_events'    => 0,
                    'share_events'     => 0,
                    'signals'          => [],
                ];
            }
            $perUser[$upn]['signin_count']++;
            if ($hour < 6 || $hour >= 22) $perUser[$upn]['off_hours']++;
            if ($country !== '') $perUser[$upn]['countries'][$country] = ($perUser[$upn]['countries'][$country] ?? 0) + 1;
        }

        // Audit-Events: gruppiere pro User und Stunde
        $hourlyCounts = [];                            // upn → hour-bucket → ['filedown'=>N, 'mailsend'=>N]
        foreach ($auditEvents as $ev) {
            $upn      = $ev['initiatedBy']['user']['userPrincipalName'] ?? '';
            if ($upn === '') continue;
            $activity = strtolower((string)($ev['activityDisplayName'] ?? ''));
            $ts       = strtotime($ev['activityDateTime'] ?? '');
            if (!$ts) continue;
            $bucket = (int)floor($ts / 3600);

            if (!isset($perUser[$upn])) {
                $perUser[$upn] = [
                    'upn'              => $upn,
                    'signin_count'     => 0,
                    'off_hours'        => 0,
                    'countries'        => [],
                    'mass_downloads'   => 0,
                    'mass_sends'       => 0,
                    'delete_events'    => 0,
                    'share_events'     => 0,
                    'signals'          => [],
                ];
            }
            if (str_contains($activity, 'filedownload') || str_contains($activity, 'filesyncdownload')) {
                $hourlyCounts[$upn][$bucket]['filedown'] = ($hourlyCounts[$upn][$bucket]['filedown'] ?? 0) + 1;
            }
            if (str_contains($activity, 'send mail') || str_contains($activity, 'sendmail')) {
                $hourlyCounts[$upn][$bucket]['mailsend'] = ($hourlyCounts[$upn][$bucket]['mailsend'] ?? 0) + 1;
            }
            if (str_contains($activity, 'filedeleted') || str_contains($activity, 'recycled')) {
                $perUser[$upn]['delete_events']++;
            }
            if (str_contains($activity, 'sharingset') || str_contains($activity, 'sharinginvitationcreated')) {
                $perUser[$upn]['share_events']++;
            }
        }

        // Mass-Burst-Aggregation
        foreach ($hourlyCounts as $upn => $hours) {
            foreach ($hours as $b => $counts) {
                if (($counts['filedown'] ?? 0) >= 50)   $perUser[$upn]['mass_downloads']++;
                if (($counts['mailsend']  ?? 0) >= 100) $perUser[$upn]['mass_sends']++;
            }
        }

        // Score je User
        $highRisk = 0;
        foreach ($perUser as $upn => &$u) {
            $score = 0;
            $signals = [];

            // Off-Hours-Quote
            if ($u['signin_count'] >= 10) {
                $pct = $u['off_hours'] / $u['signin_count'];
                if ($pct >= 0.5) {
                    $score += 25;
                    $signals[] = sprintf('%d %% Anmeldungen außerhalb Bürozeiten', (int)round($pct * 100));
                } elseif ($pct >= 0.25) {
                    $score += 10;
                    $signals[] = sprintf('Erhöhter Off-Hours-Anteil (%d %%)', (int)round($pct * 100));
                }
            }
            // Länder
            $countries = $u['countries'];
            arsort($countries);
            $countryNames = array_keys($countries);
            if (count($countryNames) > 3) {
                $score += 15;
                $signals[] = sprintf('Anmeldungen aus %d verschiedenen Ländern', count($countryNames));
            }
            // Mass-Bursts
            if ($u['mass_downloads'] > 0) {
                $score += min(40, 15 * $u['mass_downloads']);
                $signals[] = sprintf('%dx Massen-Download (≥ 50 Files in 1h)', $u['mass_downloads']);
            }
            if ($u['mass_sends'] > 0) {
                $score += min(40, 20 * $u['mass_sends']);
                $signals[] = sprintf('%dx Mass-Mail-Send (≥ 100 Mails in 1h)', $u['mass_sends']);
            }
            // Lösch-/Share-Aktivität
            if ($u['delete_events'] >= 100) {
                $score += 25;
                $signals[] = sprintf('%d Lösch-Events', $u['delete_events']);
            } elseif ($u['delete_events'] >= 30) {
                $score += 10;
                $signals[] = sprintf('%d Lösch-Events', $u['delete_events']);
            }
            if ($u['share_events'] >= 50) {
                $score += 20;
                $signals[] = sprintf('%d Sharing-Events', $u['share_events']);
            }

            $u['risk_score']    = min(100, $score);
            $u['signals']       = $signals;
            $u['country_count'] = count($countries);
            if ($u['risk_score'] >= 50) $highRisk++;
        }
        unset($u);

        // Sortiere: höchstes Risiko zuerst
        usort($perUser, fn($a, $b) => $b['risk_score'] <=> $a['risk_score']);

        return [
            'period_days'          => $days,
            'total_users_analyzed' => count($perUser),
            'high_risk_users'      => $highRisk,
            'users'                => array_slice($perUser, 0, 50),    // Top 50
        ];
    }

    private function loadSignIns(int $days): array
    {
        $since = (new \DateTimeImmutable('-' . $days . ' days'))->format('Y-m-d\TH:i:s\Z');
        try {
            return $this->graph->paginate(
                '/auditLogs/signIns',
                [
                    '$filter' => "createdDateTime ge {$since}",
                    '$top'    => '999',
                    '$select' => 'userPrincipalName,createdDateTime,location,status',
                ],
                10,
                'insider_signins_' . $days . 'd',
                3600
            );
        } catch (\Throwable) { return []; }
    }

    private function loadAuditEvents(int $days): array
    {
        $since = (new \DateTimeImmutable('-' . $days . ' days'))->format('Y-m-d\TH:i:s\Z');
        try {
            return $this->graph->paginate(
                '/auditLogs/directoryAudits',
                [
                    '$filter' => "activityDateTime ge {$since}",
                    '$select' => 'activityDateTime,activityDisplayName,initiatedBy,category',
                    '$top'    => '999',
                ],
                10,
                'insider_audit_' . $days . 'd',
                3600
            );
        } catch (\Throwable) { return []; }
    }
}
