<?php

namespace App\Graph;

use App\Auth\GraphTokenManager;
use App\Cache\GraphCache;

class GraphClient
{
    private string $baseUrl = 'https://graph.microsoft.com/v1.0';
    private GraphTokenManager $tokenManager;
    private GraphCache $cache;

    public function __construct(GraphTokenManager $tokenManager, GraphCache $cache)
    {
        $this->tokenManager = $tokenManager;
        $this->cache        = $cache;
    }

    public function get(string $endpoint, array $query = [], ?string $cacheKey = null, int $ttl = 900): array
    {
        if ($cacheKey) {
            $cached = $this->cache->get($cacheKey);
            if ($cached !== null) {
                return $cached;
            }
        }

        $url = $this->buildUrl($endpoint, $query);
        $result = $this->request('GET', $url);

        if ($cacheKey) {
            $this->cache->set($cacheKey, $result, $ttl);
        }
        return $result;
    }

    public function paginate(string $endpoint, array $query = [], int $maxPages = 20, ?string $cacheKey = null, int $ttl = 900): array
    {
        if ($cacheKey) {
            $cached = $this->cache->get($cacheKey);
            if ($cached !== null) {
                return $cached;
            }
        }

        $url = $this->buildUrl($endpoint, $query);
        $all = [];
        $pages = 0;

        while ($url && $pages < $maxPages) {
            $result = $this->request('GET', $url);
            $all = array_merge($all, $result['value'] ?? []);
            $url = $result['@odata.nextLink'] ?? null;
            $pages++;
        }

        if ($cacheKey) {
            $this->cache->set($cacheKey, $all, $ttl);
        }
        return $all;
    }

    public function getCount(string $endpoint): int
    {
        $result = $this->request('GET', $this->buildUrl($endpoint, ['$count' => 'true', '$top' => '1']), true);
        return (int)($result['@odata.count'] ?? 0);
    }

    private function request(string $method, string $url, bool $withCount = false): array
    {
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
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_CUSTOMREQUEST  => $method,
        ]);

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
            throw new \RuntimeException("Graph API error on {$url}: {$msg}");
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

    public function getCache(): GraphCache
    {
        return $this->cache;
    }
}
