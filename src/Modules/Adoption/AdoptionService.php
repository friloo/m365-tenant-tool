<?php

namespace App\Modules\Adoption;

use App\Graph\GraphClient;

class AdoptionService
{
    public function __construct(private GraphClient $graph) {}

    // ── Public report methods ─────────────────────────────────────────────────

    /**
     * Return active-user counts per service for the last 30 days.
     *
     * Primary: getOffice365ActiveUserCounts aggregate endpoint (not affected by
     *   anonymized-report settings; returns the most-recent day's counts).
     * Fallback: getOffice365ActiveUserDetail per-user CSV (requires non-anonymized reports).
     *
     * @return array{total: int, exchange: int, teams: int, sharepoint: int, onedrive: int, yammer: int}
     */
    public function getActiveUserSummary(): array
    {
        // ── Primary: aggregate counts (most recent day in the 30-day window) ──
        try {
            $rows = $this->graph->getReport(
                "/reports/getOffice365ActiveUserCounts(period='D30')",
                [],
                'adoption_active_user_counts',
                3600
            );
            if (!empty($rows)) {
                // Sort descending by date; take the most-recent row
                usort($rows, fn($a, $b) => strcmp(
                    $b['reportDate'] ?? $b['Report Date'] ?? '',
                    $a['reportDate'] ?? $a['Report Date'] ?? ''
                ));
                $latest = $rows[0];
                return [
                    'total'      => 0, // aggregate endpoint has no "total users" — SKU data used instead
                    'exchange'   => (int)($latest['exchange']   ?? 0),
                    'teams'      => (int)($latest['teams']      ?? 0),
                    'sharepoint' => (int)($latest['sharePoint'] ?? $latest['sharepoint'] ?? 0),
                    'onedrive'   => (int)($latest['oneDrive']   ?? $latest['onedrive']   ?? 0),
                    'yammer'     => (int)($latest['yammer']     ?? 0),
                ];
            }
        } catch (\Throwable $e) {
            error_log('Adoption getActiveUserSummary (aggregate): ' . $e->getMessage());
        }

        // ── Fallback: per-user detail report ───────────────────────────────────
        try {
            $rows = $this->graph->getReport(
                "/reports/getOffice365ActiveUserDetail(period='D30')",
                [],
                'adoption_active_users',
                3600
            );
            if (!empty($rows)) {
                $exchangeActive = $teamsActive = $sharepointActive = $onedriveActive = $yammerActive = 0;
                foreach ($rows as $row) {
                    if ($this->isActive($row, 'exchangeLastActivityDate',   'Exchange Active'))   $exchangeActive++;
                    if ($this->isActive($row, 'teamsLastActivityDate',      'Teams Active'))      $teamsActive++;
                    if ($this->isActive($row, 'sharePointLastActivityDate', 'SharePoint Active')) $sharepointActive++;
                    if ($this->isActive($row, 'oneDriveLastActivityDate',   'OneDrive Active'))   $onedriveActive++;
                    if ($this->isActive($row, 'yammerLastActivityDate',     'Yammer Active'))     $yammerActive++;
                }
                return [
                    'total'      => count($rows),
                    'exchange'   => $exchangeActive,
                    'teams'      => $teamsActive,
                    'sharepoint' => $sharepointActive,
                    'onedrive'   => $onedriveActive,
                    'yammer'     => $yammerActive,
                ];
            }
        } catch (\Throwable $e) {
            error_log('Adoption getActiveUserSummary (detail): ' . $e->getMessage());
        }

        return ['total' => 0, 'exchange' => 0, 'teams' => 0, 'sharepoint' => 0, 'onedrive' => 0, 'yammer' => 0];
    }

    /**
     * Return daily email activity counts for the last 30 days (sorted ASC).
     *
     * @return array<int, array{date: string, send: int, receive: int, read: int}>
     */
    public function getEmailActivityCounts(): array
    {
        $rows = $this->graph->getReport(
            "/reports/getEmailActivityCounts(period='D30')",
            [],
            'rpt_email_counts_d30',
            3600
        );

        if (empty($rows)) {
            return [];
        }

        $result = [];
        foreach ($rows as $row) {
            // JSON: reportDate | CSV: Report Date
            $date = trim($row['reportDate'] ?? $row['Report Date'] ?? '');
            if ($date === '') {
                continue;
            }
            $result[] = [
                'date'    => $date,
                'send'    => (int)($row['send']    ?? $row['Send']    ?? 0),
                'receive' => (int)($row['receive'] ?? $row['Receive'] ?? 0),
                'read'    => (int)($row['read']    ?? $row['Read']    ?? 0),
            ];
        }

        usort($result, fn($a, $b) => strcmp($a['date'], $b['date']));
        return $result;
    }

    /**
     * Return daily Teams activity counts for the last 30 days (sorted ASC).
     *
     * @return array<int, array{date: string, team_chat: int, private_chat: int, calls: int, meetings: int}>
     */
    public function getTeamsActivityCounts(): array
    {
        $rows = $this->graph->getReport(
            "/reports/getTeamsUserActivityCounts(period='D30')",
            [],
            'rpt_teams_counts_d30',
            3600
        );

        if (empty($rows)) {
            return [];
        }

        $result = [];
        foreach ($rows as $row) {
            $date = trim($row['reportDate'] ?? $row['Report Date'] ?? '');
            if ($date === '') {
                continue;
            }
            $result[] = [
                'date'         => $date,
                'team_chat'    => (int)($row['teamChatMessages']    ?? $row['Team Chat Messages']    ?? 0),
                'private_chat' => (int)($row['privateChatMessages'] ?? $row['Private Chat Messages'] ?? 0),
                'calls'        => (int)($row['calls']               ?? $row['Calls']                 ?? 0),
                'meetings'     => (int)($row['meetings']            ?? $row['Meetings']              ?? 0),
            ];
        }

        usort($result, fn($a, $b) => strcmp($a['date'], $b['date']));
        return $result;
    }

    /**
     * Return daily OneDrive activity user counts for the last 30 days (sorted ASC).
     *
     * @return array<int, array{date: string, viewed_edited: int, synced: int, shared_internal: int, shared_external: int}>
     */
    public function getOneDriveActivityCounts(): array
    {
        $rows = $this->graph->getReport(
            "/reports/getOneDriveActivityUserCounts(period='D30')",
            [],
            'adoption_onedrive_counts',
            3600
        );

        if (empty($rows)) {
            return [];
        }

        $result = [];
        foreach ($rows as $row) {
            $date = trim($row['reportDate'] ?? $row['Report Date'] ?? '');
            if ($date === '') {
                continue;
            }
            $result[] = [
                'date'            => $date,
                'viewed_edited'   => (int)($row['viewedOrEdited']   ?? $row['Viewed Or Edited']   ?? 0),
                'synced'          => (int)($row['synced']            ?? $row['Synced']              ?? 0),
                'shared_internal' => (int)($row['sharedInternally'] ?? $row['Shared Internally']   ?? 0),
                'shared_external' => (int)($row['sharedExternally'] ?? $row['Shared Externally']   ?? 0),
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

    // ── Private helpers ───────────────────────────────────────────────────────

    /**
     * Checks whether a user row indicates activity for a given service.
     * Handles both the current JSON format (non-empty date) and the older
     * CSV-based boolean format (TRUE/FALSE string).
     */
    private function isActive(array $row, string $jsonKey, string $csvKey): bool
    {
        // JSON format: non-empty date string means active
        if (array_key_exists($jsonKey, $row)) {
            return !empty(trim((string)($row[$jsonKey] ?? '')));
        }
        // Old CSV format: "TRUE" / "FALSE" string
        if (array_key_exists($csvKey, $row)) {
            return strtoupper(trim((string)($row[$csvKey] ?? ''))) === 'TRUE';
        }
        return false;
    }
}
