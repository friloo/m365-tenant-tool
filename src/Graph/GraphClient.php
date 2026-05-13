<?php

namespace App\Graph;

use App\Auth\GraphTokenManager;
use App\Cache\GraphCache;

class GraphClient
{
    private string $baseUrl = 'https://graph.microsoft.com/v1.0';
    private GraphTokenManager $tokenManager;
    private GraphCache $cache;
    private ?array $lastError = null;

    public function __construct(GraphTokenManager $tokenManager, GraphCache $cache)
    {
        $this->tokenManager = $tokenManager;
        $this->cache        = $cache;
    }

    /** Returns the last silently-swallowed Graph error (403/404), or null. */
    public function getLastError(): ?array
    {
        return $this->lastError;
    }

    public function get(string $endpoint, array $query = [], ?string $cacheKey = null, int $ttl = 900): array
    {
        if ($cacheKey) {
            $cached = $this->cache->get($cacheKey);
            if (!empty($cached)) {
                return $cached;
            }
            // Auto-invalidate stale empty entries (cached before the no-empty-cache fix)
            if ($cached === []) {
                $this->cache->forget($cacheKey);
            }
        }

        $url = $this->buildUrl($endpoint, $query);
        $result = $this->request('GET', $url);

        // Don't cache responses that came from a swallowed 403/404 — otherwise
        // the empty result sticks around long after the permission is fixed.
        if ($cacheKey && $this->lastError === null) {
            $this->cache->set($cacheKey, $result, $ttl);
        }
        return $result;
    }

    public function paginate(string $endpoint, array $query = [], int $maxPages = 20, ?string $cacheKey = null, int $ttl = 900): array
    {
        if ($cacheKey) {
            $cached = $this->cache->get($cacheKey);
            if (!empty($cached)) {
                return $cached;
            }
            if ($cached === []) {
                $this->cache->forget($cacheKey);
            }
        }

        $url = $this->buildUrl($endpoint, $query);
        $all = [];
        $pages = 0;
        $pageError = null;

        while ($url && $pages < $maxPages) {
            $result = $this->request('GET', $url);
            if ($this->lastError !== null) $pageError = $this->lastError;
            $all = array_merge($all, $result['value'] ?? []);
            $url = $result['@odata.nextLink'] ?? null;
            $pages++;
        }

        // Same: skip cache if any page returned an error, and preserve the
        // error so callers reading getLastError() after paginate() see it
        if ($pageError !== null) {
            $this->lastError = $pageError;
        } elseif ($cacheKey) {
            $this->cache->set($cacheKey, $all, $ttl);
        }
        return $all;
    }

    public function getCount(string $endpoint): int
    {
        $result = $this->request('GET', $this->buildUrl($endpoint, ['$count' => 'true', '$top' => '1']), true);
        return (int)($result['@odata.count'] ?? 0);
    }

    private function request(string $method, string $url, bool $withCount = false, ?array $body = null): array
    {
        $this->lastError = null;
        $token = $this->tokenManager->getToken();
        $headers = [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json',
            'Accept: application/json',
        ];
        if ($withCount) {
            $headers[] = 'ConsistencyLevel: eventual';
        }

        $ch = curl_init($url);
        $opts = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_CUSTOMREQUEST  => $method,
        ];
        if ($body !== null) {
            $opts[CURLOPT_POSTFIELDS] = json_encode($body);
        }
        curl_setopt_array($ch, $opts);

        $attempts = 0;
        while ($attempts < 3) {
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $attempts++;

            if ($httpCode === 429) {
                // Rate limit — respect Retry-After
                $retryAfter = (int)(curl_getinfo($ch, CURLINFO_REDIRECT_COUNT) ?: 10);
                sleep(min($retryAfter, 30));
                continue;
            }
            break;
        }
        // curl_close removed: no-op since PHP 8.0, deprecated since 8.5

        if ($httpCode >= 400) {
            $err = json_decode($response, true);
            $msg = $err['error']['message'] ?? "HTTP {$httpCode}";
            $code = $err['error']['code'] ?? '';

            // 403 / insufficient privileges: return empty result so pages
            // degrade gracefully instead of crashing. Write operations still throw.
            if ($httpCode === 403 || $code === 'Authorization_RequestDenied' || $code === 'InsufficientPrivileges') {
                if ($method === 'GET') {
                    error_log("Graph 403 (missing permission) on {$url}: {$msg}");
                    $this->lastError = ['status' => 403, 'code' => $code, 'message' => $msg, 'url' => $url];
                    return ['value' => [], '@odata.count' => 0];
                }
            }

            // 404: resource not found — return empty
            if ($httpCode === 404) {
                $this->lastError = ['status' => 404, 'code' => $code, 'message' => $msg, 'url' => $url];
                return ['value' => [], '@odata.count' => 0];
            }

            throw new \RuntimeException("Graph API error on {$url}: {$msg}");
        }

        if ($httpCode === 204 || $response === '' || $response === false) {
            return [];
        }

        return json_decode($response, true) ?: [];
    }

    private function buildUrl(string $endpoint, array $query = []): string
    {
        $url = str_starts_with($endpoint, 'https://') ? $endpoint : $this->baseUrl . $endpoint;
        if ($query) {
            $url .= (str_contains($url, '?') ? '&' : '?') . http_build_query($query);
        }
        return $url;
    }

    public function post(string $endpoint, array $body = []): array
    {
        $url = $this->buildUrl($endpoint);
        return $this->request('POST', $url, false, $body);
    }

    public function patch(string $endpoint, array $body): void
    {
        $url = $this->buildUrl($endpoint);
        $this->request('PATCH', $url, false, $body);
    }

    public function delete(string $endpoint): void
    {
        $url = $this->buildUrl($endpoint);
        $this->request('DELETE', $url);
    }

    /** GET with ConsistencyLevel: eventual — required for $search and $count queries */
    public function getEventual(string $endpoint, array $query = [], ?string $cacheKey = null, int $ttl = 900): array
    {
        if ($cacheKey) {
            $cached = $this->cache->get($cacheKey);
            if (!empty($cached)) return $cached;
            if ($cached === []) $this->cache->forget($cacheKey);
        }
        $url    = $this->buildUrl($endpoint, $query);
        $result = $this->request('GET', $url, true);
        if ($cacheKey) $this->cache->set($cacheKey, $result, $ttl);
        return $result;
    }

    /**
     * GET a Reports API endpoint (follows 302 redirect, requests JSON format).
     * Returns the parsed `value` array, or [] on error/missing permission.
     */
    public function getReport(string $endpoint, array $query = [], ?string $cacheKey = null, int $ttl = 3600): array
    {
        if ($cacheKey) {
            $cached = $this->cache->get($cacheKey);
            if (!empty($cached)) return $cached;
            if ($cached === []) $this->cache->forget($cacheKey);
        }

        $base = str_starts_with($endpoint, 'https://') ? $endpoint : ($this->baseUrl . $endpoint);
        $url  = $base;
        if (!empty($query)) {
            $url .= (str_contains($url, '?') ? '&' : '?') . http_build_query($query);
        }

        $token = $this->tokenManager->getToken();

        // Step 1: hit Graph — capture redirect location without following it,
        // so we can strip Authorization before hitting the CDN.
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_HEADER         => true,
            CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . $token],
            CURLOPT_TIMEOUT        => 30,
        ]);
        $raw      = (string)curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $hdrSize  = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        // curl_close removed: no-op since PHP 8.0, deprecated since 8.5

        $response = '';

        if ($httpCode === 302 || $httpCode === 301) {
            $headers = substr($raw, 0, $hdrSize);
            if (preg_match('/^Location:\s*(\S+)/im', $headers, $m)) {
                $location = trim($m[1]);
                error_log("Graph Reports: following redirect to " . substr($location, 0, 80));
                $ch2 = curl_init($location);
                curl_setopt_array($ch2, [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_TIMEOUT        => 60,
                ]);
                $response = (string)curl_exec($ch2);
                $httpCode = curl_getinfo($ch2, CURLINFO_HTTP_CODE);
                // curl_close removed: no-op since PHP 8.0, deprecated since 8.5
            }
        } elseif ($httpCode === 200) {
            $response = substr($raw, $hdrSize);
        }

        if ($httpCode >= 400 || $response === '') {
            error_log("Graph Reports API error (step1={$httpCode}) on {$url}");
            return [];
        }

        // Try JSON first
        $data   = json_decode($response, true) ?? [];
        $result = $data['value'] ?? [];

        // If JSON parse failed or no 'value' key, try CSV (Graph CDN always serves CSV)
        if (empty($result) && strlen($response) > 50) {
            $result = $this->parseCsvReport($response);
            if (empty($result)) {
                error_log('GraphClient::getReport() unparseable response on ' . $url
                    . ' | snippet: ' . substr($response, 0, 200));
            } else {
                $first = $result[0];
                error_log('GraphClient::getReport() parsed CSV: ' . count($result) . ' rows'
                    . ' | keys: ' . implode(',', array_keys($first))
                    . ' | upn=' . ($first['ownerPrincipalName'] ?? $first['userPrincipalName'] ?? 'MISSING')
                    . ' | isDeleted=' . var_export($first['isDeleted'] ?? 'MISSING', true));
            }
        }

        if ($cacheKey && !empty($result)) {
            $this->cache->set($cacheKey, $result, $ttl);
        }
        return $result;
    }

    private function parseCsvReport(string $csv): array
    {
        // Strip UTF-8 BOM (\xef\xbb\xbf) that Graph CDN prepends to CSV files
        if (str_starts_with($csv, "\xef\xbb\xbf")) {
            $csv = substr($csv, 3);
        }
        $lines = explode("\n", str_replace("\r\n", "\n", trim($csv)));
        if (count($lines) < 2) return [];

        $rawHeaders = str_getcsv(array_shift($lines));
        $headers    = array_map([$this, 'normaliseCsvHeader'], $rawHeaders);

        $rows = [];
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') continue;
            $values = str_getcsv($line);
            if (count($values) !== count($headers)) continue;
            $row = array_combine($headers, $values);
            // Normalise CSV boolean strings to PHP booleans (matches JSON API behaviour)
            foreach ($row as &$v) {
                if (strcasecmp((string)$v, 'TRUE') === 0)  $v = true;
                elseif (strcasecmp((string)$v, 'FALSE') === 0) $v = false;
            }
            unset($v);
            $rows[] = $row;
        }
        return $rows;
    }

    private function normaliseCsvHeader(string $h): string
    {
        static $map = [
            'Report Refresh Date'                        => 'reportRefreshDate',
            'Report Period'                              => 'reportPeriod',
            'Site Id'                                    => 'siteId',
            'Site URL'                                   => 'siteUrl',
            'Owner Display Name'                         => 'ownerDisplayName',
            'Owner Principal Name'                       => 'ownerPrincipalName',
            'Is Deleted'                                 => 'isDeleted',
            'Last Activity Date'                         => 'lastActivityDate',
            'File Count'                                 => 'fileCount',
            'Active File Count'                          => 'activeFileCount',
            'Storage Used (Byte)'                        => 'storageUsedInBytes',
            'Storage Allocated (Byte)'                   => 'storageAllocatedInBytes',
            'User Principal Name'                        => 'userPrincipalName',
            'Display Name'                               => 'displayName',
            'Exchange Last Activity Date'                => 'exchangeLastActivityDate',
            'OneDrive Last Activity Date'                => 'oneDriveLastActivityDate',
            'SharePoint Last Activity Date'              => 'sharePointLastActivityDate',
            'Teams Last Activity Date'                   => 'teamsLastActivityDate',
            'Yammer Last Activity Date'                  => 'yammerLastActivityDate',
            'Skype For Business Last Activity Date'      => 'skypeForBusinessLastActivityDate',
            'Has Exchange License'                       => 'hasExchangeLicense',
            'Has OneDrive License'                       => 'hasOneDriveLicense',
            'Has SharePoint License'                     => 'hasSharePointLicense',
            'Has Teams License'                          => 'hasTeamsLicense',
            'Has Yammer License'                         => 'hasYammerLicense',
            'Send Count'                                 => 'sendCount',
            'Receive Count'                              => 'receiveCount',
            'Read Count'                                 => 'readCount',
            'Team Chat Message Count'                    => 'teamChatMessageCount',
            'Private Chat Message Count'                 => 'privateChatMessageCount',
            'Call Count'                                 => 'callCount',
            'Meeting Count'                              => 'meetingCount',
            'Viewed Or Edited File Count'                => 'viewedOrEditedFileCount',
            'Synced File Count'                          => 'syncedFileCount',
            'Shared Internally File Count'               => 'sharedInternallyFileCount',
            'Shared Externally File Count'               => 'sharedExternallyFileCount',
            // Aggregate report column names (getOffice365ActiveUserCounts etc.)
            'Report Date'                                => 'reportDate',
            'OneDrive'                                   => 'oneDrive',
            'SharePoint'                                 => 'sharePoint',
            'Skype For Business'                         => 'skypeForBusiness',
            // Email activity counts
            'Send'                                       => 'send',
            'Receive'                                    => 'receive',
            'Read'                                       => 'read',
            // Teams activity counts
            'Team Chat Messages'                         => 'teamChatMessages',
            'Private Chat Messages'                      => 'privateChatMessages',
            'Calls'                                      => 'calls',
            'Meetings'                                   => 'meetings',
            // OneDrive activity counts
            'Viewed Or Edited'                           => 'viewedOrEdited',
            'Synced'                                     => 'synced',
            'Shared Internally'                          => 'sharedInternally',
            'Shared Externally'                          => 'sharedExternally',
        ];
        if (isset($map[$h])) return $map[$h];
        // Generic fallback: "Foo Bar" → "fooBar"
        return lcfirst(str_replace(' ', '', ucwords(strtolower($h))));
    }

    public function getCache(): GraphCache
    {
        return $this->cache;
    }
}
