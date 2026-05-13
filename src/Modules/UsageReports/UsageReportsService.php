<?php

namespace App\Modules\UsageReports;

use App\Graph\GraphClient;

class UsageReportsService
{
    public function __construct(private GraphClient $graph) {}

    public function getSummary(int $period = 30): array
    {
        $exchange   = 0;
        $oneDrive   = 0;
        $sharePoint = 0;
        $teams      = 0;

        try {
            $rows = $this->graph->getReport(
                "/reports/getOffice365ServicesUserCounts(period='D{$period}')",
                [],
                "usage_service_counts_{$period}",
                3600
            );
            if (!empty($rows)) {
                usort($rows, fn($a, $b) => strcmp(
                    $b['reportRefreshDate'] ?? '',
                    $a['reportRefreshDate'] ?? ''
                ));
                $latest     = $rows[0];
                $exchange   = (int)($latest['exchange']   ?? 0);
                $oneDrive   = (int)($latest['oneDrive']   ?? $latest['onedrive']   ?? 0);
                $sharePoint = (int)($latest['sharePoint'] ?? $latest['sharepoint'] ?? 0);
                $teams      = (int)($latest['teams']      ?? $latest['microsoft365'] ?? 0);
            }
        } catch (\Throwable $e) {
            error_log('UsageReports getServiceUserCounts: ' . $e->getMessage());
        }

        $emailsSent     = 0;
        $emailsReceived = 0;

        try {
            $rows = $this->graph->getReport(
                "/reports/getEmailActivityCounts(period='D{$period}')",
                [],
                "usage_email_counts_{$period}",
                3600
            );
            foreach ($rows as $row) {
                $emailsSent     += (int)($row['send']    ?? $row['Send']    ?? 0);
                $emailsReceived += (int)($row['receive'] ?? $row['Receive'] ?? 0);
            }
        } catch (\Throwable $e) {
            error_log('UsageReports getEmailActivityCounts: ' . $e->getMessage());
        }

        $teamsMessages = 0;
        $teamsMeetings = 0;
        $teamsCalls    = 0;

        try {
            $rows = $this->graph->getReport(
                "/reports/getTeamsUserActivityCounts(period='D{$period}')",
                [],
                "usage_teams_counts_{$period}",
                3600
            );
            foreach ($rows as $row) {
                $teamsMessages += (int)($row['teamChatMessages']    ?? $row['Team Chat Messages']    ?? 0)
                                + (int)($row['privateChatMessages'] ?? $row['Private Chat Messages'] ?? 0);
                $teamsMeetings += (int)($row['meetings']            ?? $row['Meetings']              ?? 0);
                $teamsCalls    += (int)($row['calls']               ?? $row['Calls']                 ?? 0);
            }
        } catch (\Throwable $e) {
            error_log('UsageReports getTeamsActivityCounts: ' . $e->getMessage());
        }

        return [
            'period'         => $period,
            'exchange'       => $exchange,
            'oneDrive'       => $oneDrive,
            'sharePoint'     => $sharePoint,
            'teams'          => $teams,
            'emailsSent'     => $emailsSent,
            'emailsReceived' => $emailsReceived,
            'teamsMessages'  => $teamsMessages,
            'teamsMeetings'  => $teamsMeetings,
            'teamsCalls'     => $teamsCalls,
        ];
    }
}
