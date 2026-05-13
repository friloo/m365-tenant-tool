<?php

namespace App\Modules\TeamsUsage;

use App\Graph\GraphClient;

class TeamsUsageService
{
    public function __construct(private GraphClient $graph) {}

    /**
     * Fetch Teams user activity detail for the last 30 days.
     * The endpoint returns a CSV (via redirect). We bypass GraphClient JSON
     * handling and make a raw cURL call using the same reflection pattern
     * as MailboxService.
     *
     * @return array<int, array{
     *   upn: string,
     *   lastActivity: string,
     *   isDeleted: bool,
     *   teamChatMessages: int,
     *   privateChatMessages: int,
     *   callCount: int,
     *   meetingCount: int,
     *   hasOtherAction: bool
     * }>
     */
    public function getUsageReport(): array
    {
        try {
            $cache = $this->graph->getCache();
            $csv   = $cache->remember('teams_usage_d30', function () {
                return $this->fetchCsvReport("/reports/getTeamsUserActivityUserDetail(period='D30')");
            }, 3600);

            $rows = $this->parseCsv((string)$csv);
            // Filter out deleted users
            return array_values(array_filter($rows, fn($r) => !$r['isDeleted']));
        } catch (\Throwable) {
            return [];
        }
    }

    /**
     * Compute summary statistics from usage rows.
     *
     * @param  array $rows  Output of getUsageReport()
     * @return array{
     *   total: int,
     *   active: int,
     *   inactive: int,
     *   top_chatters: array,
     *   top_callers: array,
     *   top_meetings: array,
     *   avg_messages: int
     * }
     */
    public function getStats(array $rows): array
    {
        $active   = array_filter($rows, fn($r) => !empty($r['lastActivity']));
        $inactive = array_filter($rows, fn($r) => empty($r['lastActivity']));

        // Top 10 chatters — sorted by teamChatMessages + privateChatMessages desc
        $chatters = $rows;
        usort($chatters, fn($a, $b) =>
            ($b['teamChatMessages'] + $b['privateChatMessages'])
            <=> ($a['teamChatMessages'] + $a['privateChatMessages'])
        );
        $topChatters = array_slice($chatters, 0, 10);

        // Top 10 callers — sorted by callCount desc
        $callers = $rows;
        usort($callers, fn($a, $b) => $b['callCount'] <=> $a['callCount']);
        $topCallers = array_slice($callers, 0, 10);

        // Top 10 meetings — sorted by meetingCount desc
        $meetings = $rows;
        usort($meetings, fn($a, $b) => $b['meetingCount'] <=> $a['meetingCount']);
        $topMeetings = array_slice($meetings, 0, 10);

        // Average messages per active user
        $activeCount = count($active);
        $totalMessages = 0;
        foreach ($active as $r) {
            $totalMessages += $r['teamChatMessages'] + $r['privateChatMessages'];
        }
        $avgMessages = $activeCount > 0 ? (int)round($totalMessages / $activeCount) : 0;

        return [
            'total'        => count($rows),
            'active'       => count($active),
            'inactive'     => count($inactive),
            'top_chatters' => $topChatters,
            'top_callers'  => $topCallers,
            'top_meetings' => $topMeetings,
            'avg_messages' => $avgMessages,
        ];
    }

    /**
     * Make a direct cURL request for the CSV report endpoint.
     * The Graph reports endpoints redirect to a CSV download URL.
     * We follow redirects with the bearer token and return raw CSV.
     */
    private function fetchCsvReport(string $endpoint): string
    {
        // Access token via reflection on GraphClient's private tokenManager
        $rc     = new \ReflectionClass($this->graph);
        $tmProp = $rc->getProperty('tokenManager');
        $tmProp->setAccessible(true);
        /** @var \App\Auth\GraphTokenManager $tm */
        $tm    = $tmProp->getValue($this->graph);
        $token = $tm->getToken();

        $url = 'https://graph.microsoft.com/v1.0' . $endpoint;

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,   // follow redirect to actual CSV
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $token,
                'Accept: text/csv, application/json',
            ],
            CURLOPT_TIMEOUT => 30,
        ]);
        $body     = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        // curl_close removed: no-op since PHP 8.0, deprecated since 8.5

        if ($httpCode >= 400 || $body === false || $body === '') {
            return '';
        }
        return (string)$body;
    }

    /**
     * Parse raw CSV from getTeamsUserActivityUserDetail.
     *
     * Expected columns (Graph v1.0):
     * Report Refresh Date, User Principal Name, Last Activity Date, Is Deleted,
     * Deleted Date, Assigned Products, Team Chat Message Count,
     * Private Chat Message Count, Call Count, Meeting Count, Has Other Action,
     * Report Period
     */
    private function parseCsv(string $csv): array
    {
        if (trim($csv) === '') {
            return [];
        }

        $lines = explode("\n", str_replace("\r\n", "\n", $csv));
        // Remove BOM if present
        $lines[0] = ltrim($lines[0], "\xEF\xBB\xBF");

        $result = [];
        $header = null;

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            $cols = str_getcsv($line);
            if ($header === null) {
                $header = array_map(fn($h) => strtolower(trim($h)), $cols);
                continue;
            }
            if (count($cols) < 3) {
                continue;
            }

            // Pad cols to header length to avoid missing key warnings
            while (count($cols) < count($header)) {
                $cols[] = '';
            }
            $row = array_combine(
                array_slice($header, 0, count($cols)),
                array_slice($cols, 0, count($header))
            );

            $isDeleted = strtolower(
                $row['is deleted'] ?? $row['isdeleted'] ?? 'false'
            ) === 'true';

            $result[] = [
                'upn'                 => $row['user principal name'] ?? $row['userprincipalname'] ?? '',
                'lastActivity'        => $row['last activity date'] ?? $row['lastactivitydate'] ?? '',
                'isDeleted'           => $isDeleted,
                'teamChatMessages'    => (int)($row['team chat message count'] ?? $row['teamchatmessagecount'] ?? 0),
                'privateChatMessages' => (int)($row['private chat message count'] ?? $row['privatechatmessagecount'] ?? 0),
                'callCount'           => (int)($row['call count'] ?? $row['callcount'] ?? 0),
                'meetingCount'        => (int)($row['meeting count'] ?? $row['meetingcount'] ?? 0),
                'hasOtherAction'      => strtolower($row['has other action'] ?? $row['hasonlyphonenumber'] ?? 'false') === 'true',
            ];
        }

        return $result;
    }
}
