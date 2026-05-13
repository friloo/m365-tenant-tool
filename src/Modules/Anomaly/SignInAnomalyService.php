<?php

namespace App\Modules\Anomaly;

use App\Graph\GraphClient;

/**
 * Detects sign-in anomalies in a tenant and emits ONLY aggregate counters.
 * No UPNs, no IPs, no country names, no timestamps of individual events
 * leave this service. The output is suitable for forwarding to an AI
 * provider as part of the security advisor context.
 *
 * Anomaly categories:
 *  - failed_signins              (raw count)
 *  - successful_signins          (raw count)
 *  - new_country_signins         (signins from a country code that had 0
 *                                 successful signins in the preceding
 *                                 baseline window)
 *  - off_hours_signins           (sign-ins between 22:00 and 06:00 tenant-
 *                                 local time)
 *  - credential_stuffing_signatures
 *                                (per-user clusters of >=5 failures within
 *                                 30 min followed by a success — counted)
 *  - impossible_travel_count     (per-user pairs of successful sign-ins
 *                                 less than 4h apart but with different
 *                                 country codes)
 *  - atrisk_signins              (riskState=atRisk or confirmedCompromised)
 */
class SignInAnomalyService
{
    public function __construct(private GraphClient $graph) {}

    /**
     * @return array{
     *   period_hours:int,
     *   successful_signins:int,
     *   failed_signins:int,
     *   from_new_countries:int,
     *   off_hours_count:int,
     *   credential_stuffing_signatures:int,
     *   impossible_travel_count:int,
     *   atrisk_signins:int,
     *   note:string
     * }
     */
    public function summarize(int $recentHours = 24, int $baselineDays = 30): array
    {
        $recentIso = (new \DateTimeImmutable('-' . $recentHours . ' hours'))
            ->format('Y-m-d\TH:i:s\Z');
        $baselineIso = (new \DateTimeImmutable('-' . $baselineDays . ' days'))
            ->format('Y-m-d\TH:i:s\Z');

        try {
            $recent = $this->graph->paginate(
                '/auditLogs/signIns',
                [
                    '$filter' => "createdDateTime ge {$recentIso}",
                    '$top'    => '999',
                ],
                5,                              // up to 5k events in 24h is plenty
                'signin_anomaly_recent',
                1800                            // 30 min
            );
        } catch (\Throwable $e) {
            return $this->emptyResult($recentHours, 'Sign-in-Log nicht abrufbar: ' . $e->getMessage());
        }

        if (empty($recent)) {
            return $this->emptyResult($recentHours, 'Keine Sign-ins im Zeitraum.');
        }

        // Baseline: only successful sign-ins, country codes only, to build
        // the "known countries" allow-list. Cached longer.
        $baselineCountries = [];
        try {
            $baseline = $this->graph->paginate(
                '/auditLogs/signIns',
                [
                    '$filter' => "createdDateTime ge {$baselineIso} and status/errorCode eq 0",
                    '$select' => 'location',
                    '$top'    => '999',
                ],
                10,                            // 10k baseline events is enough to collect country codes
                'signin_anomaly_baseline_countries',
                21600                          // 6 h — country baseline doesn't change quickly
            );
            foreach ($baseline as $ev) {
                $code = strtoupper($ev['location']['countryOrRegion'] ?? '');
                if ($code !== '') $baselineCountries[$code] = true;
            }
        } catch (\Throwable) {
            // Without baseline we just can't detect new countries — keep going
        }

        $successful    = 0;
        $failed        = 0;
        $offHours      = 0;
        $atRisk        = 0;
        $newCountry    = 0;
        $byUser        = [];

        foreach ($recent as $ev) {
            $errorCode = $ev['status']['errorCode'] ?? null;
            $isSuccess = ($errorCode === 0);
            if ($isSuccess) $successful++; else $failed++;

            $ts = strtotime($ev['createdDateTime'] ?? '');
            if ($ts) {
                $hour = (int)date('G', $ts);
                if ($hour < 6 || $hour >= 22) $offHours++;
            }

            $riskState = strtolower((string)($ev['riskState'] ?? ''));
            if (in_array($riskState, ['atrisk', 'confirmedcompromised'], true)) $atRisk++;

            $code = strtoupper($ev['location']['countryOrRegion'] ?? '');
            if ($isSuccess && $code !== '' && !isset($baselineCountries[$code])) {
                $newCountry++;
            }

            // For per-user analysis, key by the user-id (NOT the UPN), and
            // discard the key after counting — we only emit the counter.
            $userKey = (string)($ev['userId'] ?? '');
            if ($userKey === '') continue;
            $byUser[$userKey][] = ['ts' => $ts, 'ok' => $isSuccess, 'country' => $code];
        }

        $credStuffing = 0;
        $impossible   = 0;
        foreach ($byUser as $events) {
            usort($events, fn($a, $b) => $a['ts'] <=> $b['ts']);
            $credStuffing += $this->countCredentialStuffing($events);
            $impossible   += $this->countImpossibleTravel($events);
        }

        return [
            'period_hours'                   => $recentHours,
            'baseline_days'                  => $baselineDays,
            'successful_signins'             => $successful,
            'failed_signins'                 => $failed,
            'from_new_countries'             => $newCountry,
            'off_hours_count'                => $offHours,
            'credential_stuffing_signatures' => $credStuffing,
            'impossible_travel_count'        => $impossible,
            'atrisk_signins'                 => $atRisk,
            'note'                           => 'Aggregierte Zähler, keine Benutzer-, IP- oder Länder-Details.',
        ];
    }

    /**
     * Count clusters of >=5 failures within a 30-min window that end in a
     * success — a classic credential-stuffing fingerprint.
     */
    private function countCredentialStuffing(array $events): int
    {
        $count = 0;
        $n = count($events);
        for ($i = 0; $i < $n; $i++) {
            if (!$events[$i]['ok']) continue;
            // Look back: how many failures in the preceding 30 min?
            $window = $events[$i]['ts'] - 1800;
            $fails  = 0;
            for ($j = $i - 1; $j >= 0; $j--) {
                if ($events[$j]['ts'] < $window) break;
                if (!$events[$j]['ok']) $fails++;
            }
            if ($fails >= 5) $count++;
        }
        return $count;
    }

    /**
     * Count successful-sign-in pairs <4h apart from different countries.
     */
    private function countImpossibleTravel(array $events): int
    {
        $count = 0;
        $last = null;
        foreach ($events as $ev) {
            if (!$ev['ok']) continue;
            if ($last && $ev['country'] !== '' && $last['country'] !== ''
                && $ev['country'] !== $last['country']
                && ($ev['ts'] - $last['ts']) < 4 * 3600) {
                $count++;
            }
            $last = $ev;
        }
        return $count;
    }

    private function emptyResult(int $hours, string $note): array
    {
        return [
            'period_hours'                   => $hours,
            'baseline_days'                  => 0,
            'successful_signins'             => 0,
            'failed_signins'                 => 0,
            'from_new_countries'             => 0,
            'off_hours_count'                => 0,
            'credential_stuffing_signatures' => 0,
            'impossible_travel_count'        => 0,
            'atrisk_signins'                 => 0,
            'note'                           => $note,
        ];
    }
}
