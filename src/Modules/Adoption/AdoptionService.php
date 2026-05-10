<?php

namespace App\Modules\Adoption;

use App\Graph\GraphClient;

class AdoptionService
{
    public function __construct(private GraphClient $graph) {}

    // ── CSV helper ────────────────────────────────────────────────────────────

    /**
     * Fetch a CSV report from a full Graph URL using a raw cURL call.
     * Follows redirects (Graph reports redirect to the actual CSV download).
     * Returns an array of associative arrays keyed by the CSV header row.
     * On any failure returns [].
     */
    private function fetchCsvReport(string $url): array
    {
        // Extract the access token via reflection on GraphClient's private tokenManager.
        // This is the same pattern used by TeamsUsageService and MailboxService.
        $rc     = new \ReflectionClass($this->graph);
        $tmProp = $rc->getProperty('tokenManager');
        $tmProp->setAccessible(true);
        /** @var \App\Auth\GraphTokenManager $tm */
        $tm    = $tmProp->getValue($this->graph);
        $token = $tm->getToken();

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS      => 5,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $token,
                'Accept: text/csv, application/json',
            ],
            CURLOPT_TIMEOUT        => 30,
        ]);
        $body     = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode >= 400 || $body === false || $body === '') {
            return [];
        }

        $csv = (string)$body;

        // Parse CSV: strip BOM, split lines, first row = headers
        $csv   = ltrim($csv, "\xEF\xBB\xBF");
        $lines = explode("\n", str_replace("\r\n", "\n", $csv));

        $result = [];
        $header = null;

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            $cols = str_getcsv($line);
            if ($header === null) {
                $header = $cols;   // keep original casing — callers use exact column names
                continue;
            }
            // Pad short rows
            while (count($cols) < count($header)) {
                $cols[] = '';
            }
            $result[] = array_combine(
                array_slice($header, 0, count($cols)),
                array_slice($cols, 0, count($header))
            );
        }

        return $result;
    }

    // ── Public report methods ─────────────────────────────────────────────────

    /**
     * Return active-user counts per service for the last 30 days.
     * Fetches getOffice365ActiveUserDetail(period='D30') CSV.
     *
     * @return array{total: int, exchange: int, teams: int, sharepoint: int, onedrive: int, yammer: int}
     */
    public function getActiveUserSummary(): array
    {
        $cache = $this->graph->getCache();

        $rows = $cache->remember('adoption_active_users', function () {
            return $this->fetchCsvReport(
                "https://graph.microsoft.com/v1.0/reports/getOffice365ActiveUserDetail(period='D30')"
            );
        }, 3600);

        if (empty($rows)) {
            return ['total' => 0, 'exchange' => 0, 'teams' => 0, 'sharepoint' => 0, 'onedrive' => 0, 'yammer' => 0];
        }

        $total         = count($rows);
        $exchangeActive   = 0;
        $teamsActive      = 0;
        $sharepointActive = 0;
        $onedriveActive   = 0;
        $yammerActive     = 0;

        foreach ($rows as $row) {
            if (strtoupper(trim($row['Exchange Active'] ?? '')) === 'TRUE')   $exchangeActive++;
            if (strtoupper(trim($row['Teams Active']    ?? '')) === 'TRUE')   $teamsActive++;
            if (strtoupper(trim($row['SharePoint Active'] ?? '')) === 'TRUE') $sharepointActive++;
            if (strtoupper(trim($row['OneDrive Active'] ?? '')) === 'TRUE')   $onedriveActive++;
            if (strtoupper(trim($row['Yammer Active']   ?? '')) === 'TRUE')   $yammerActive++;
        }

        return [
            'total'      => $total,
            'exchange'   => $exchangeActive,
            'teams'      => $teamsActive,
            'sharepoint' => $sharepointActive,
            'onedrive'   => $onedriveActive,
            'yammer'     => $yammerActive,
        ];
    }

    /**
     * Return daily email activity counts for the last 30 days (sorted ASC).
     * Fetches getEmailActivityCounts(period='D30') CSV.
     *
     * @return array<int, array{date: string, send: int, receive: int, read: int}>
     */
    public function getEmailActivityCounts(): array
    {
        $cache = $this->graph->getCache();

        $rows = $cache->remember('adoption_email_counts', function () {
            return $this->fetchCsvReport(
                "https://graph.microsoft.com/v1.0/reports/getEmailActivityCounts(period='D30')"
            );
        }, 3600);

        if (empty($rows)) {
            return [];
        }

        $result = [];
        foreach ($rows as $row) {
            $date = trim($row['Report Date'] ?? '');
            if ($date === '') {
                continue;
            }
            $result[] = [
                'date'    => $date,
                'send'    => (int)($row['Send']    ?? 0),
                'receive' => (int)($row['Receive'] ?? 0),
                'read'    => (int)($row['Read']    ?? 0),
            ];
        }

        usort($result, fn($a, $b) => strcmp($a['date'], $b['date']));
        return $result;
    }

    /**
     * Return daily Teams activity counts for the last 30 days (sorted ASC).
     * Fetches getTeamsUserActivityCounts(period='D30') CSV.
     *
     * @return array<int, array{date: string, team_chat: int, private_chat: int, calls: int, meetings: int}>
     */
    public function getTeamsActivityCounts(): array
    {
        $cache = $this->graph->getCache();

        $rows = $cache->remember('adoption_teams_counts', function () {
            return $this->fetchCsvReport(
                "https://graph.microsoft.com/v1.0/reports/getTeamsUserActivityCounts(period='D30')"
            );
        }, 3600);

        if (empty($rows)) {
            return [];
        }

        $result = [];
        foreach ($rows as $row) {
            $date = trim($row['Report Date'] ?? '');
            if ($date === '') {
                continue;
            }
            $result[] = [
                'date'         => $date,
                'team_chat'    => (int)($row['Team Chat Messages']    ?? 0),
                'private_chat' => (int)($row['Private Chat Messages'] ?? 0),
                'calls'        => (int)($row['Calls']                 ?? 0),
                'meetings'     => (int)($row['Meetings']              ?? 0),
            ];
        }

        usort($result, fn($a, $b) => strcmp($a['date'], $b['date']));
        return $result;
    }

    /**
     * Return daily OneDrive activity user counts for the last 30 days (sorted ASC).
     * Fetches getOneDriveActivityUserCounts(period='D30') CSV.
     *
     * @return array<int, array{date: string, viewed_edited: int, synced: int, shared_internal: int, shared_external: int}>
     */
    public function getOneDriveActivityCounts(): array
    {
        $cache = $this->graph->getCache();

        $rows = $cache->remember('adoption_onedrive_counts', function () {
            return $this->fetchCsvReport(
                "https://graph.microsoft.com/v1.0/reports/getOneDriveActivityUserCounts(period='D30')"
            );
        }, 3600);

        if (empty($rows)) {
            return [];
        }

        $result = [];
        foreach ($rows as $row) {
            $date = trim($row['Report Date'] ?? '');
            if ($date === '') {
                continue;
            }
            $result[] = [
                'date'            => $date,
                'viewed_edited'   => (int)($row['Viewed Or Edited']   ?? 0),
                'synced'          => (int)($row['Synced']              ?? 0),
                'shared_internal' => (int)($row['Shared Internally']   ?? 0),
                'shared_external' => (int)($row['Shared Externally']   ?? 0),
            ];
        }

        usort($result, fn($a, $b) => strcmp($a['date'], $b['date']));
        return $result;
    }

    /**
     * Return total consumed and enabled license units across all subscribed SKUs.
     *
     * @return array{consumed: int, total: int}
     */
    public function getSubscribedSkuTotals(): array
    {
        try {
            $data = $this->graph->get(
                '/subscribedSkus',
                ['$select' => 'skuId,skuPartNumber,consumedUnits,prepaidUnits'],
                'adoption_skus',
                3600
            );

            $skus     = $data['value'] ?? $data;
            $consumed = 0;
            $total    = 0;

            foreach ($skus as $sku) {
                $consumed += (int)($sku['consumedUnits'] ?? 0);
                $total    += (int)(($sku['prepaidUnits']['enabled'] ?? 0));
            }

            return ['consumed' => $consumed, 'total' => $total];
        } catch (\Throwable) {
            return ['consumed' => 0, 'total' => 0];
        }
    }
}
