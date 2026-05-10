<?php

namespace App\Modules\Mailboxes;

use App\Graph\GraphClient;

class MailboxService
{
    public function __construct(private GraphClient $graph) {}

    // ── New detail / settings methods ─────────────────────────────────────────

    /**
     * Fetch raw mailboxSettings for a user (no cache).
     * Returns the full settings object or [] on error.
     */
    public function getMailboxSettings(string $userId): array
    {
        try {
            return $this->graph->get("/users/{$userId}/mailboxSettings", [], null, 0);
        } catch (\Throwable) {
            return [];
        }
    }

    /**
     * Enable email forwarding for a user.
     * Requires MailboxSettings.ReadWrite on the Azure App.
     */
    public function setForwarding(string $userId, string $forwardTo): void
    {
        $this->graph->patch("/users/{$userId}", [
            'forwardingSmtpAddress'    => $forwardTo,
            'deliverToMailboxAndForward' => true,
        ]);
    }

    /**
     * Remove email forwarding for a user.
     */
    public function removeForwarding(string $userId): void
    {
        $this->graph->patch("/users/{$userId}", [
            'forwardingSmtpAddress'    => null,
            'deliverToMailboxAndForward' => false,
        ]);
    }

    /**
     * Enable or disable the automatic-reply (Out-of-Office) setting.
     */
    public function setAutoReply(string $userId, string $message, bool $enabled): void
    {
        $this->graph->patch("/users/{$userId}/mailboxSettings", [
            'automaticRepliesSetting' => [
                'status'               => $enabled ? 'alwaysEnabled' : 'disabled',
                'internalReplyMessage' => $message,
                'externalReplyMessage' => $message,
            ],
        ]);
    }

    /**
     * Return a merged array of basic user fields + mailboxSettings.
     * Always fresh (no cache).
     */
    public function getMailboxDetail(string $userId): array
    {
        try {
            $user = $this->graph->get(
                "/users/{$userId}",
                ['$select' => 'id,displayName,userPrincipalName,mail,assignedLicenses,accountEnabled,jobTitle,department,forwardingSmtpAddress,deliverToMailboxAndForward'],
                null,
                0
            );
        } catch (\Throwable) {
            $user = [];
        }

        $settings = $this->getMailboxSettings($userId);

        return array_merge($user, $settings);
    }

    /**
     * Return the top-20 mail folders for a user (no cache).
     *
     * @return array<int, array{id: string, displayName: string, totalItemCount: int, unreadItemCount: int}>
     */
    public function getMailFolders(string $userId): array
    {
        try {
            $data = $this->graph->get(
                "/users/{$userId}/mailFolders",
                ['$select' => 'id,displayName,totalItemCount,unreadItemCount', '$top' => 20],
                null,
                0
            );
            return $data['value'] ?? [];
        } catch (\Throwable) {
            return [];
        }
    }

    // ── Existing usage-report methods ─────────────────────────────────────────

    /**
     * Fetch mailbox usage for the last 30 days via the reports API.
     * The endpoint returns CSV text; we parse it into a structured array.
     *
     * @return array<int, array{displayName: string, upn: string, itemCount: int,
     *                           storageUsedBytes: int, deletedItemCount: int,
     *                           deletedItemSizeBytes: int, isDeleted: bool}>
     */
    public function getUsageSummary(): array
    {
        try {
            // Graph reports endpoints return CSV.  The GraphClient json_decodes the
            // response, so a non-JSON body yields an empty array — we handle that by
            // making a raw HTTP call via cURL if the structured response is empty.
            $raw = $this->fetchCsvReport("/reports/getMailboxUsageDetail(period='D30')");
            return $this->parseCsv($raw);
        } catch (\Throwable) {
            return [];
        }
    }

    /**
     * Make a direct cURL request for a report endpoint that returns CSV.
     * We bypass the GraphClient JSON handling and get the raw text.
     */
    private function fetchCsvReport(string $endpoint): string
    {
        // Obtain token via the same token manager the GraphClient uses.
        // We access it through a small reflection trick or simply re-implement
        // the HTTP call inline.  Since GraphClient exposes no raw-fetch helper
        // we re-use the stored graph reference to get the token indirectly:
        // GraphClient->getCache() lets us detect the cache, but for the token
        // we need a different path.  The cleanest approach supported by the
        // existing codebase is to let the client try and fall back to a cURL call
        // using the token retrieved from the underlying GraphTokenManager.

        // We call a non-existent property to retrieve what we need safely:
        // Instead, use the graph object via its public interface by encoding
        // a sentinel approach: try graph->get() which will throw on non-JSON,
        // then fall back to a manual HTTP call.

        // Try via GraphClient::get() first — if the response happens to have
        // been wrapped (e.g. $skipToken next link etc.) it would return [].
        // In practice Graph CSV endpoints redirect and the final CSV body is
        // returned with Content-Type: text/csv; the client decodes it as [].
        // We therefore reach into the response via our own cURL call.
        // We need the access token. We can access it through Reflection on the
        // GraphClient's private tokenManager.

        $rc      = new \ReflectionClass($this->graph);
        $tmProp  = $rc->getProperty('tokenManager');
        $tmProp->setAccessible(true);
        /** @var \App\Auth\GraphTokenManager $tm */
        $tm    = $tmProp->getValue($this->graph);
        $token = $tm->getToken();

        $url = 'https://graph.microsoft.com/v1.0' . $endpoint;

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,   // follow the redirect to actual CSV
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

    /**
     * Parse the raw CSV returned by getMailboxUsageDetail.
     *
     * Expected header (Graph v1.0 30-day report):
     * Report Refresh Date,User Principal Name,Display Name,Is Deleted,
     * Deleted Date,Created Date,Last Activity Date,Item Count,
     * Storage Used (Byte),Issue Warning Quota (Byte),Prohibit Send Quota (Byte),
     * Prohibit Send/Receive Quota (Byte),Deleted Item Count,
     * Deleted Item Size (Byte),Report Period
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
                // Normalise header to lowercase keys without spaces
                $header = array_map(fn($h) => strtolower(trim($h)), $cols);
                continue;
            }
            if (count($cols) < count($header)) {
                continue;
            }
            $row = array_combine(array_slice($header, 0, count($cols)), array_slice($cols, 0, count($header)));

            $result[] = [
                'displayName'         => $row['display name']                     ?? $row['displayname']                     ?? '',
                'upn'                 => $row['user principal name']               ?? $row['userprincipalname']               ?? '',
                'itemCount'           => (int)($row['item count']                 ?? $row['itemcount']                 ?? 0),
                'storageUsedBytes'    => (int)($row['storage used (byte)']        ?? $row['storageused(byte)']        ?? 0),
                'deletedItemCount'    => (int)($row['deleted item count']         ?? $row['deleteditemcount']         ?? 0),
                'deletedItemSizeBytes'=> (int)($row['deleted item size (byte)']   ?? $row['deleteditemsize(byte)']   ?? 0),
                'isDeleted'           => strtolower($row['is deleted']            ?? $row['isdeleted']            ?? 'false') === 'true',
            ];
        }
        return $result;
    }

    /**
     * Create a shared mailbox by provisioning a disabled user account.
     *
     * Exchange Online provisions the mailbox asynchronously after user creation,
     * so only Step 1 (POST /users) is performed here.  The caller should inform
     * the user that it may take a few minutes before the mailbox is ready.
     *
     * @throws \Throwable  Re-throws on Graph API errors so the controller can
     *                     surface a meaningful flash message.
     */
    public function createSharedMailbox(string $displayName, string $alias, string $domain): array
    {
        $upn      = "{$alias}@{$domain}";
        $password = bin2hex(random_bytes(10)); // 20 hex chars

        $user = $this->graph->post('/users', [
            'accountEnabled'    => false,
            'displayName'       => $displayName,
            'mailNickname'      => $alias,
            'userPrincipalName' => $upn,
            'passwordProfile'   => [
                'forceChangePasswordNextSignIn' => false,
                'password'                      => $password,
            ],
            'usageLocation' => 'DE',
        ]);

        // Invalidate cached usage report so the next load picks up the new account.
        try {
            // GraphClient caches by key; bust the usage key if the client exposes a
            // clearCache helper — otherwise silently skip (cache will expire naturally).
            if (method_exists($this->graph, 'clearCache')) {
                $this->graph->clearCache('mailboxes_usage');
            }
        } catch (\Throwable) {
            // Non-critical — ignore.
        }

        return is_array($user) ? $user : [];
    }

    /**
     * Fetch calendar permissions for a user.
     *
     * NOTE: GET /users/{id}/calendar/calendarPermissions typically requires
     * delegated permissions (Calendars.Read) and will fail with application-only
     * (client credentials) auth.  Returns [] gracefully on any error so the
     * detail view can show an informational empty state instead.
     */
    public function getCalendarPermissions(string $userId): array
    {
        try {
            $data = $this->graph->get(
                "/users/{$userId}/calendar/calendarPermissions",
                [],
                null,
                0
            );
            return $data['value'] ?? [];
        } catch (\Throwable) {
            return [];
        }
    }

    // ── External Forwards ─────────────────────────────────────────────────────

    /**
     * Fetch all licensed member users and check each for an external
     * forwarding address in their mailboxSettings.
     *
     * Only users whose forwardingSmtpAddress domain differs from their own
     * UPN domain are included in the result.
     *
     * Cached for 1 hour (expensive: one Graph call per user).
     *
     * @return array<int, array{
     *   id: string,
     *   displayName: string,
     *   userPrincipalName: string,
     *   mail: string,
     *   forwardingAddress: string,
     *   forwardingEnabled: bool,
     *   deliverToMailboxAndForward: bool,
     * }>
     */
    public function getExternalForwards(): array
    {
        $cacheKey = 'mailbox_external_forwards';

        // Try cache first (result is already filtered)
        $cached = $this->graph->getCache()->get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        // Step 1: fetch all licensed member users
        try {
            $usersData = $this->graph->get(
                '/users',
                [
                    '$select'          => 'id,displayName,userPrincipalName,mail,accountEnabled',
                    '$filter'          => "assignedLicenses/\$count ne 0 and userType eq 'Member'",
                    '$count'           => 'true',
                    '$top'             => '999',
                    'ConsistencyLevel' => 'eventual',
                ],
                null,
                0
            );
        } catch (\Throwable) {
            return [];
        }

        $users = $usersData['value'] ?? [];
        if (empty($users)) {
            return [];
        }

        // Step 2: for each user, fetch mailboxSettings and look for an external forward
        $result = [];

        foreach ($users as $user) {
            $userId = $user['id'] ?? '';
            $upn    = $user['userPrincipalName'] ?? '';
            if ($userId === '' || $upn === '') {
                continue;
            }

            // Determine the user's own domain from their UPN
            $upnDomain = strtolower(substr($upn, (int)strpos($upn, '@') + 1));

            try {
                $settings = $this->graph->get(
                    "/users/{$userId}/mailboxSettings",
                    ['$select' => 'forwardingSmtpAddress,deliverToMailboxAndForward,automaticRepliesSetting'],
                    null,
                    0
                );
            } catch (\Throwable) {
                // Mailbox may not exist or permission denied — skip silently
                continue;
            }

            $fwdAddress = $settings['forwardingSmtpAddress'] ?? '';
            if ($fwdAddress === '' || $fwdAddress === null) {
                continue;
            }

            // Extract domain from forwarding address (strip leading "smtp:" if present)
            $cleanAddr = ltrim($fwdAddress, 'sSmMtTpP:');
            $atPos     = strpos($cleanAddr, '@');
            if ($atPos === false) {
                continue;
            }
            $fwdDomain = strtolower(substr($cleanAddr, $atPos + 1));

            // Only flag if the target domain is different from the user's domain
            if ($fwdDomain === $upnDomain) {
                continue;
            }

            $result[] = [
                'id'                        => $userId,
                'displayName'               => $user['displayName']        ?? '',
                'userPrincipalName'         => $upn,
                'mail'                      => $user['mail']               ?? '',
                'forwardingAddress'         => $fwdAddress,
                'forwardingEnabled'         => true,
                'deliverToMailboxAndForward'=> (bool)($settings['deliverToMailboxAndForward'] ?? false),
            ];
        }

        $this->graph->getCache()->put($cacheKey, $result, 3600);

        return $result;
    }

    /**
     * Remove an external forwarding configuration from a user's mailbox.
     * Sets forwardingSmtpAddress to null and disables automatic forwarding.
     */
    public function removeExternalForward(string $userId): void
    {
        $this->graph->patch("/users/{$userId}/mailboxSettings", [
            'forwardingSmtpAddress'      => null,
            'automaticForwardingEnabled' => false,
        ]);
    }

    // ── Shared Mailboxes ──────────────────────────────────────────────────────

    /**
     * Fetch all shared mailboxes (disabled member accounts with a license).
     *
     * Exchange Online shared mailboxes are typically represented as disabled
     * user accounts in Azure AD.  We enrich each entry with basic mailbox-
     * settings (auto-reply status, forwarding address).
     *
     * Cached for 30 minutes.
     *
     * @return array<int, array{
     *   id: string,
     *   displayName: string,
     *   userPrincipalName: string,
     *   mail: string,
     *   createdDateTime: string,
     *   assignedLicenses: array,
     *   autoReplyStatus: string,
     *   forwardingAddress: string,
     * }>
     */
    public function getSharedMailboxes(): array
    {
        $cacheKey = 'mailbox_shared_list';

        $cached = $this->graph->getCache()->get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        try {
            $usersData = $this->graph->get(
                '/users',
                [
                    '$select'          => 'id,displayName,userPrincipalName,mail,assignedLicenses,createdDateTime',
                    '$filter'          => "accountEnabled eq false and userType eq 'Member' and assignedLicenses/\$count ne 0",
                    '$count'           => 'true',
                    '$top'             => '999',
                    'ConsistencyLevel' => 'eventual',
                ],
                null,
                0
            );
        } catch (\Throwable) {
            return [];
        }

        $users = $usersData['value'] ?? [];

        $result = [];
        foreach ($users as $user) {
            $userId = $user['id'] ?? '';
            if ($userId === '') {
                continue;
            }

            $autoReplyStatus = 'disabled';
            $fwdAddress      = '';

            try {
                $settings = $this->graph->get(
                    "/users/{$userId}/mailboxSettings",
                    ['$select' => 'automaticRepliesSetting,forwardingSmtpAddress'],
                    null,
                    0
                );
                $autoReplyStatus = $settings['automaticRepliesSetting']['status'] ?? 'disabled';
                $fwdAddress      = $settings['forwardingSmtpAddress'] ?? '';
            } catch (\Throwable) {
                // No mailbox yet or permission denied — use defaults
            }

            $result[] = [
                'id'               => $userId,
                'displayName'      => $user['displayName']      ?? '',
                'userPrincipalName'=> $user['userPrincipalName'] ?? '',
                'mail'             => $user['mail']              ?? '',
                'createdDateTime'  => $user['createdDateTime']   ?? '',
                'assignedLicenses' => $user['assignedLicenses']  ?? [],
                'autoReplyStatus'  => $autoReplyStatus,
                'forwardingAddress'=> $fwdAddress,
            ];
        }

        $this->graph->getCache()->put($cacheKey, $result, 1800);

        return $result;
    }

    /**
     * Returns mailbox permissions for a shared mailbox.
     *
     * Note: Full Access and Send As permissions are managed by Exchange Online
     * and are not directly accessible via Microsoft Graph v1.0 or beta.
     * Use the Exchange Admin Center to manage permissions.
     *
     * @return array  Always returns an empty array — see note above.
     */
    public function getSharedMailboxPermissions(string $userId): array
    {
        return [];
    }

    // ── Usage statistics ──────────────────────────────────────────────────────

    /**
     * Compute summary statistics from the usage rows.
     *
     * @param  array $usage  Output of getUsageSummary()
     * @return array{total: int, totalBytes: int, avgBytes: int, over50GB: int, under1GB: int}
     */
    public function getStats(array $usage): array
    {
        $active = array_filter($usage, fn($u) => !$u['isDeleted']);
        $total  = count($active);

        if ($total === 0) {
            return ['total' => 0, 'totalBytes' => 0, 'avgBytes' => 0, 'over50GB' => 0, 'under1GB' => 0];
        }

        $bytes    = array_sum(array_column($active, 'storageUsedBytes'));
        $over50   = count(array_filter($active, fn($u) => $u['storageUsedBytes'] >= 50 * 1024 ** 3));
        $under1   = count(array_filter($active, fn($u) => $u['storageUsedBytes'] < 1 * 1024 ** 3));

        return [
            'total'      => $total,
            'totalBytes' => $bytes,
            'avgBytes'   => (int)($bytes / $total),
            'over50GB'   => $over50,
            'under1GB'   => $under1,
        ];
    }
}
