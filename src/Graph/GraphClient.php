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
        curl_close($ch);

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

        $query['$format'] = 'application/json';
        $url   = $this->buildUrl($endpoint, $query);
        $token = $this->tokenManager->getToken();

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . $token, 'Accept: application/json'],
            CURLOPT_TIMEOUT        => 60,
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode >= 400 || !$response) {
            error_log("Graph Reports API error ({$httpCode}) on {$url}");
            return [];
        }

        $data   = json_decode($response, true) ?: [];
        $result = $data['value'] ?? [];

        // Don't cache empty results — a 403/empty-response could be stored
        // and then served indefinitely even after the permission is fixed.
        if ($cacheKey && !empty($result)) {
            $this->cache->set($cacheKey, $result, $ttl);
        }
        return $result;
    }

    public function getCache(): GraphCache
    {
        return $this->cache;
    }
}
