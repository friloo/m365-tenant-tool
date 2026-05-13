<?php
namespace App\Modules\AiAdvisor;

use App\Core\Config;
use App\Database\DB;

class AiAdvisorService
{
    private Config $config;

    public function __construct()
    {
        $this->config = Config::getInstance();
    }

    public function isEnabled(): bool
    {
        return $this->config->get('ai_enabled', '0') === '1';
    }

    public function getProvider(): string
    {
        return $this->config->get('ai_provider', 'openai');
    }

    public function getProviderLabel(): string
    {
        return match($this->getProvider()) {
            'deepseek' => 'DeepSeek',
            'ollama'   => 'Ollama (lokal)',
            default    => 'OpenAI',
        };
    }

    /**
     * Builds ONLY anonymized, aggregated metrics — zero PII, zero tenant identifiers.
     */
    public function buildContext(): array
    {
        $ctx = ['generated_at' => date('c'), 'privacy_note' => 'anonymized_aggregated_only'];

        // Security Posture checks
        try {
            $posture = app_service(\App\Modules\SecurityPosture\SecurityPostureService::class)->runChecks();
            $byStatus = ['pass' => [], 'warn' => [], 'fail' => []];
            foreach ($posture as $c) {
                $byStatus[$c['status'] ?? 'warn'][] = $c['id'];
            }
            $ctx['security_posture'] = [
                'total'          => count($posture),
                'passed'         => count($byStatus['pass']),
                'warnings'       => count($byStatus['warn']),
                'failed'         => count($byStatus['fail']),
                'failed_checks'  => $byStatus['fail'],   // generic IDs, no tenant data
                'warning_checks' => $byStatus['warn'],
            ];
        } catch (\Throwable) {}

        // Users — counts and percentages ONLY
        try {
            $svc    = app_service(\App\Modules\Users\UsersService::class);
            $users  = $svc->getAll();
            $mfaMap = $svc->getMfaStatus();
            $total  = count($users);
            $enabled = $mfaReg = $stale = $noLicenseEnabled = 0;
            foreach ($users as $u) {
                $isEnabled = $u['accountEnabled'] ?? true;
                if ($isEnabled) $enabled++;
                $upn = $u['userPrincipalName'] ?? '';
                if ($mfaMap[$upn]['mfaRegistered'] ?? false) $mfaReg++;
                $last = $u['signInActivity']['lastSignInDateTime'] ?? null;
                $days = $last ? (int)floor((time() - strtotime($last)) / 86400) : 9999;
                if ($days > 90 && $isEnabled) $stale++;
                if ($isEnabled && empty($u['assignedLicenses'])) $noLicenseEnabled++;
            }
            $ctx['users'] = [
                'total'                => $total,
                'enabled'              => $enabled,
                'mfa_registered_pct'   => $total > 0 ? round($mfaReg / $total * 100) : 0,
                'no_mfa_count'         => $total - $mfaReg,
                'stale_90d_count'      => $stale,
                'enabled_no_license'   => $noLicenseEnabled,
            ];
        } catch (\Throwable) {}

        // Licenses — utilization % only, no SKU names
        try {
            $skus = app_service(\App\Modules\Licenses\LicensesService::class)->getSkus();
            $skuCount = $highUtil = $overProvisioned = 0;
            foreach ($skus as $sku) {
                $cap = $sku['prepaidUnits']['enabled'] ?? 0;
                if ($cap <= 0) continue;
                $skuCount++;
                $used = $sku['consumedUnits'] ?? 0;
                $pct  = $used / $cap * 100;
                if ($pct >= 90) $highUtil++;
                if ($pct < 10 && $cap > 5) $overProvisioned++;
            }
            $ctx['licenses'] = [
                'sku_count'             => $skuCount,
                'high_utilization_skus' => $highUtil,        // >=90%
                'under_utilized_skus'   => $overProvisioned, // <10% used
            ];
        } catch (\Throwable) {}

        // Devices — counts only, no names
        try {
            $devices   = app_service(\App\Modules\Devices\DevicesService::class)->getAll();
            $total     = count($devices);
            $compliant = count(array_filter($devices, fn($d) => ($d['complianceState'] ?? '') === 'compliant'));
            $ctx['devices'] = [
                'total'          => $total,
                'compliant'      => $compliant,
                'non_compliant'  => $total - $compliant,
                'compliant_pct'  => $total > 0 ? round($compliant / $total * 100) : 0,
            ];
        } catch (\Throwable) {}

        // Sharing — counts only
        try {
            $ext  = (int)(DB::fetchOne("SELECT COUNT(*) AS c FROM share_reviews WHERE status != 'revoked'")['c'] ?? 0);
            $anon = (int)(DB::fetchOne("SELECT COUNT(*) AS c FROM share_reviews WHERE status != 'revoked' AND share_scope = 'anonymous'")['c'] ?? 0);
            $ctx['sharing'] = [
                'external_count'  => $ext,
                'anonymous_count' => $anon,
            ];
        } catch (\Throwable) {}

        return $ctx;
    }

    public function getCachedAnalysis(): ?array
    {
        try {
            $row = DB::fetchOne(
                "SELECT data, created_at FROM cache WHERE cache_key = 'ai_security_analysis' AND expires_at > NOW()"
            );
            if ($row) {
                $data = json_decode($row['data'], true);
                if (is_array($data)) {
                    $data['cached_at'] = $row['created_at'];
                    return $data;
                }
            }
        } catch (\Throwable) {}
        return null;
    }

    /**
     * Run analysis:
     * 1. Build anonymized context.
     * 2. Get concrete recommendations from RecommendationLibrary (no AI needed).
     * 3. If AI is enabled, call the API for a short executive summary + score only.
     * 4. Cache and return result.
     */
    public function analyze(): array
    {
        $ctx = $this->buildContext();

        $failedChecks  = $ctx['security_posture']['failed_checks']  ?? [];
        $warningChecks = $ctx['security_posture']['warning_checks']  ?? [];

        // Always get concrete library recommendations (works without AI)
        $libraryRecs = RecommendationLibrary::get($failedChecks, $warningChecks, $ctx);

        $summary = null;
        $score   = null;

        // Only call AI for the tiny executive summary + score
        if ($this->isEnabled()) {
            try {
                $raw     = $this->callApi($ctx, $failedChecks, $warningChecks);
                $parsed  = $this->parseAiResponse($raw);
                $summary = $parsed['summary'] ?? null;
                $score   = isset($parsed['score']) ? (int)$parsed['score'] : null;
            } catch (\Throwable) {
                // AI failure is non-fatal — recommendations still shown
                $summary = null;
                $score   = null;
            }
        }

        $result = [
            'summary'         => $summary,
            'score'           => $score,
            'recommendations' => $libraryRecs,
            'context'         => $ctx,
            'generated_at'    => date('Y-m-d H:i:s'),
        ];

        $hours = max(1, (int)$this->config->get('ai_cache_hours', '24'));
        try {
            DB::execute(
                "INSERT INTO cache (cache_key, data, expires_at)
                 VALUES (?, ?, DATE_ADD(NOW(), INTERVAL ? HOUR))
                 ON DUPLICATE KEY UPDATE data = VALUES(data), expires_at = VALUES(expires_at), created_at = NOW()",
                ['ai_security_analysis', json_encode($result, JSON_UNESCAPED_UNICODE), $hours]
            );
        } catch (\Throwable) {}

        return $result;
    }

    public function clearCache(): void
    {
        try {
            DB::execute("DELETE FROM cache WHERE cache_key = 'ai_security_analysis'");
        } catch (\Throwable) {}
    }

    // ── Private helpers ──────────────────────────────────────────────────────

    /**
     * Minimal AI call — only asks for a 2-3 sentence German summary + score 0-100.
     */
    private function callApi(array $ctx, array $failedChecks, array $warningChecks): string
    {
        $provider = $this->getProvider();
        $apiKey   = $this->config->get('ai_api_key', '', true);
        $model    = $this->config->get('ai_model', '') ?: $this->defaultModel();
        $baseUrl  = trim($this->config->get('ai_base_url', ''));
        $endpoint = $this->getEndpoint($provider, $baseUrl);

        $mfaPct      = (int)($ctx['users']['mfa_registered_pct'] ?? 0);
        $nonCompliant = (int)($ctx['devices']['non_compliant'] ?? 0);
        $anonShares  = (int)($ctx['sharing']['anonymous_count'] ?? 0);

        $failedList  = implode(', ', $failedChecks)  ?: 'keine';
        $warningList = implode(', ', $warningChecks) ?: 'keine';

        $userMsg = <<<PROMPT
Given these M365 security issues found (anonymized, no PII):
Failed checks: {$failedList}
Warning checks: {$warningList}
Users without MFA: {$mfaPct}%
Non-compliant devices: {$nonCompliant}
Anonymous shares: {$anonShares}

Return JSON: {"score": 0-100, "summary": "2-3 German sentences assessing overall security posture and most critical risk"}
PROMPT;

        $messages = [
            [
                'role'    => 'system',
                'content' => 'You are a Microsoft 365 security advisor. Respond only in German. Respond with valid JSON only.',
            ],
            ['role' => 'user', 'content' => $userMsg],
        ];

        $payload = [
            'model'       => $model,
            'messages'    => $messages,
            'temperature' => 0.2,
        ];

        if ($provider === 'ollama') {
            $payload['stream'] = false;
            $payload['format'] = 'json';
        }

        $headers = ['Content-Type: application/json'];
        if ($apiKey) {
            $headers[] = "Authorization: Bearer {$apiKey}";
        }

        $ch = curl_init($endpoint);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 60,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr  = curl_error($ch);
        // curl_close removed: no-op since PHP 8.0, deprecated since 8.5

        if ($curlErr) {
            throw new \RuntimeException("Verbindungsfehler: {$curlErr}");
        }
        if ($httpCode >= 400) {
            $msg = json_decode($response, true)['error']['message'] ?? substr($response, 0, 200);
            throw new \RuntimeException("API-Fehler {$httpCode}: {$msg}");
        }

        $decoded = json_decode($response, true);
        $content = $decoded['choices'][0]['message']['content']
            ?? $decoded['message']['content']  // Ollama native format
            ?? null;

        if ($content === null) {
            throw new \RuntimeException('Keine Antwort vom KI-Provider erhalten.');
        }

        return $content;
    }

    private function parseAiResponse(string $raw): array
    {
        // Strip markdown code fences if the model added them despite instructions
        $raw = preg_replace('/^```(?:json)?\s*/m', '', $raw);
        $raw = preg_replace('/^```\s*$/m', '', $raw);
        $raw = trim($raw);

        $data = json_decode($raw, true);
        if (!is_array($data)) {
            return ['summary' => null, 'score' => null];
        }

        return [
            'summary' => isset($data['summary']) ? (string)$data['summary'] : null,
            'score'   => isset($data['score'])   ? (int)$data['score']      : null,
        ];
    }

    private function getEndpoint(string $provider, string $baseUrl): string
    {
        if ($baseUrl !== '') {
            return rtrim($baseUrl, '/') . '/v1/chat/completions';
        }
        return match($provider) {
            'deepseek' => 'https://api.deepseek.com/v1/chat/completions',
            'ollama'   => 'http://localhost:11434/v1/chat/completions',
            default    => 'https://api.openai.com/v1/chat/completions',
        };
    }

    private function defaultModel(): string
    {
        return match($this->getProvider()) {
            'deepseek' => 'deepseek-chat',
            'ollama'   => 'llama3.2',
            default    => 'gpt-4o-mini',
        };
    }
}
