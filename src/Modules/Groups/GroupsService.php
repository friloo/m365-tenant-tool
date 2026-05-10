<?php

namespace App\Modules\Groups;

use App\Graph\GraphClient;

class GroupsService
{
    public function __construct(private GraphClient $graph) {}

    public function getAll(): array
    {
        return $this->graph->paginate(
            '/groups',
            [
                '$select' => 'id,displayName,description,groupTypes,mailEnabled,securityEnabled,membershipRule,createdDateTime,mail',
                '$top'    => '999',
            ],
            30,
            'groups_all',
            900
        );
    }

    public function getOne(string $id): array
    {
        return $this->graph->get("/groups/{$id}", [], "group_{$id}", 600);
    }

    public function getMembers(string $id): array
    {
        try {
            return $this->graph->paginate(
                "/groups/{$id}/members",
                ['$select' => 'id,displayName,userPrincipalName'],
                10,
                "group_members_{$id}",
                600
            );
        } catch (\Throwable) { return []; }
    }

    public function getOwners(string $id): array
    {
        try {
            $data = $this->graph->get(
                "/groups/{$id}/owners",
                ['$select' => 'id,displayName,userPrincipalName,mail'],
                null,
                0
            );
            return $data['value'] ?? [];
        } catch (\Throwable) { return []; }
    }

    public function addMember(string $groupId, string $userId): void
    {
        $this->graph->post("/groups/{$groupId}/members/\$ref", [
            '@odata.id' => "https://graph.microsoft.com/v1.0/directoryObjects/{$userId}",
        ]);
        $this->graph->getCache()->forget("group_members_{$groupId}");
    }

    public function removeMember(string $groupId, string $userId): void
    {
        $this->graph->delete("/groups/{$groupId}/members/{$userId}/\$ref");
        $this->graph->getCache()->forget("group_members_{$groupId}");
    }

    public function createGroup(
        string $displayName,
        string $description,
        string $type,
        bool   $mailEnabled,
        string $mailNickname
    ): array {
        if ($mailNickname === '') {
            $mailNickname = preg_replace('/[^a-z0-9\-]/', '', strtolower(str_replace(' ', '-', $displayName)));
        }

        $payload = [
            'displayName'  => $displayName,
            'description'  => $description,
            'mailNickname' => $mailNickname,
        ];

        switch ($type) {
            case 'm365':
                $payload['groupTypes']      = ['Unified'];
                $payload['mailEnabled']     = true;
                $payload['securityEnabled'] = false;
                break;
            case 'security':
                $payload['groupTypes']      = [];
                $payload['mailEnabled']     = false;
                $payload['securityEnabled'] = true;
                break;
            case 'mail_security':
            default:
                $payload['groupTypes']      = [];
                $payload['mailEnabled']     = true;
                $payload['securityEnabled'] = true;
                break;
        }

        $result = $this->graph->post('/groups', $payload);
        $this->graph->getCache()->forget('groups_all');
        return $result;
    }

    public function deleteGroup(string $groupId): void
    {
        $this->graph->delete("/groups/{$groupId}");
        $this->graph->getCache()->forget('groups_all');
    }

    public function addOwner(string $groupId, string $userId): void
    {
        $this->graph->post("/groups/{$groupId}/owners/\$ref", [
            '@odata.id' => "https://graph.microsoft.com/v1.0/users/{$userId}",
        ]);
    }

    public function removeOwner(string $groupId, string $userId): void
    {
        $this->graph->delete("/groups/{$groupId}/owners/{$userId}/\$ref");
    }

    public function searchUsers(string $query): array
    {
        try {
            return $this->graph->get('/users', [
                '$select' => 'id,displayName,userPrincipalName',
                '$filter' => "startswith(displayName,'{$query}') or startswith(userPrincipalName,'{$query}')",
                '$top'    => '10',
            ]);
        } catch (\Throwable) { return []; }
    }

    public static function getType(array $group): string
    {
        $types = $group['groupTypes'] ?? [];
        if (in_array('Unified', $types)) return 'M365';
        if ($group['securityEnabled'] && !$group['mailEnabled']) return 'Security';
        if ($group['mailEnabled'] && !$group['securityEnabled']) return 'Distribution';
        return 'Mail-Enabled Security';
    }

    public function getInactiveGroups(int $days = 30): array
    {
        $cache = $this->graph->getCache();
        return $cache->remember('groups_inactive_report', function () use ($days) {
            $csv  = $this->fetchGroupsActivityCsv();
            $rows = $this->parseGroupsActivityCsv($csv);
            $today = new \DateTimeImmutable('today');

            $result = [];
            foreach ($rows as $row) {
                // Skip deleted groups
                if (strtolower($row['is deleted'] ?? 'false') === 'true') {
                    continue;
                }

                $lastActivityRaw = trim($row['last activity date'] ?? '');
                $lastActivity    = null;
                $daysInactive    = 0;

                if ($lastActivityRaw !== '') {
                    $dt = \DateTimeImmutable::createFromFormat('Y-m-d', $lastActivityRaw);
                    if ($dt !== false) {
                        $lastActivity = $dt;
                        $daysInactive = (int)$today->diff($dt)->days;
                        // Only include if inactive for more than $days
                        if ($daysInactive <= $days) {
                            continue;
                        }
                    }
                } else {
                    // No activity date → treat as inactive, days from epoch → very large
                    $daysInactive = 9999;
                }

                $result[] = [
                    'group_name'       => $row['group display name'] ?? '',
                    'group_id'         => $row['group id'] ?? '',
                    'owner'            => $row['owner principal name'] ?? '',
                    'last_activity'    => $lastActivity,
                    'member_count'     => (int)($row['member count'] ?? 0),
                    'external_count'   => (int)($row['external member count'] ?? 0),
                    'days_inactive'    => $daysInactive,
                    'exchange_emails'  => (int)($row['exchange received email count'] ?? 0),
                    'sharepoint_files' => (int)($row['sharepoint active file count'] ?? 0),
                ];
            }

            // Sort: nulls (no activity) first, then oldest first
            usort($result, function (array $a, array $b) {
                if ($a['last_activity'] === null && $b['last_activity'] === null) return 0;
                if ($a['last_activity'] === null) return -1;
                if ($b['last_activity'] === null) return 1;
                return $a['last_activity'] <=> $b['last_activity'];
            });

            return $result;
        }, 3600);
    }

    private function fetchGroupsActivityCsv(): string
    {
        // Extract access token via reflection (same pattern as TeamsUsageService)
        $rc     = new \ReflectionClass($this->graph);
        $tmProp = $rc->getProperty('tokenManager');
        $tmProp->setAccessible(true);
        /** @var \App\Auth\GraphTokenManager $tm */
        $tm    = $tmProp->getValue($this->graph);
        $token = $tm->getToken();

        $url = "https://graph.microsoft.com/v1.0/reports/getOffice365GroupsActivityDetail(period='D90')";

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $token,
                'Accept: text/csv, application/json',
            ],
            CURLOPT_TIMEOUT => 30,
        ]);
        $body     = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode >= 400 || $body === false || $body === '') {
            return '';
        }
        return (string)$body;
    }

    private function parseGroupsActivityCsv(string $csv): array
    {
        if (trim($csv) === '') {
            return [];
        }

        $lines   = explode("\n", str_replace("\r\n", "\n", $csv));
        $lines[0] = ltrim($lines[0], "\xEF\xBB\xBF");

        $header = null;
        $result = [];

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
}
