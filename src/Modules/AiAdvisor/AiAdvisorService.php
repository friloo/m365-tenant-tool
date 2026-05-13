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

    // Builds ONLY anonymized, aggregated metrics — zero PII, zero tenant identifiers
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

    public function analyze(): array
    {
        $ctx    = $this->buildContext();
        $raw    = $this->callApi($ctx);
        $result = $this->parseResponse($raw);
        $result['context']      = $ctx;
        $result['generated_at'] = date('Y-m-d H:i:s');

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

    private function callApi(array $ctx): string
    {
        $provider = $this->getProvider();
        $apiKey   = $this->config->get('ai_api_key', '', true);
        $model    = $this->config->get('ai_model', '') ?: $this->defaultModel();
        $baseUrl  = trim($this->config->get('ai_base_url', ''));
        $endpoint = $this->getEndpoint($provider, $baseUrl);

        $ctxJson = json_encode($ctx, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $userMsg = <<<PROMPT
Analyze the following anonymized Microsoft 365 security metrics and return a JSON security assessment.

PRIVACY RULES (strict):
- This data contains ONLY counts, percentages, and generic check IDs
- No user names, email addresses, UPNs, tenant IDs, domain names, or device names are present
- Do NOT ask for, infer, or reference specific users, organizations, or tenants

Security Metrics (anonymized):
{$ctxJson}

Return ONLY valid JSON in exactly this format — no markdown, no explanation:
{
  "summary": "2-3 sentence overall assessment of the security posture",
  "score": 0-100,
  "recommendations": [
    {
      "id": "unique_snake_case_id",
      "severity": "critical|high|medium|low",
      "title": "Short actionable title (max 60 chars)",
      "risk": "Why this is a security risk (1-2 sentences)",
      "action": "Specific action to take (1-2 sentences)",
      "internal_path": "/securityposture or /users or null",
      "ms_admin_url": "https://entra.microsoft.com/... or https://admin.microsoft.com/... or null"
    }
  ]
}
PROMPT;

        $messages = [
            [
                'role'    => 'system',
                'content' => 'You are a Microsoft 365 security advisor. You analyze ONLY anonymized, aggregated security statistics — never user names, email addresses, tenant names, domain names, or any personally identifiable information. Always respond with valid JSON only, no markdown formatting.',
            ],
            ['role' => 'user', 'content' => $userMsg],
        ];

        $payload = [
            'model'       => $model,
            'messages'    => $messages,
            'temperature' => 0.2,
        ];

        // JSON mode
        if ($provider === 'ollama') {
            $payload['stream'] = false;
            $payload['format'] = 'json';
        } else {
            $payload['response_format'] = ['type' => 'json_object'];
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
            CURLOPT_TIMEOUT        => 90,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr  = curl_error($ch);
        curl_close($ch);

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

    private function parseResponse(string $raw): array
    {
        // Strip markdown code fences if the model added them despite instructions
        $raw = preg_replace('/^```(?:json)?\s*/m', '', $raw);
        $raw = preg_replace('/^```\s*$/m', '', $raw);
        $raw = trim($raw);

        $data = json_decode($raw, true);
        if (!is_array($data)) {
            throw new \RuntimeException('KI-Antwort ist kein gültiges JSON: ' . substr($raw, 0, 300));
        }

        // Normalise structure
        if (!isset($data['recommendations'])) {
            $data['recommendations'] = [];
        }
        if (!isset($data['summary'])) {
            $data['summary'] = '';
        }
        if (!isset($data['score'])) {
            $data['score'] = null;
        }

        // Sort by severity
        $order = ['critical' => 0, 'high' => 1, 'medium' => 2, 'low' => 3];
        usort($data['recommendations'], fn($a, $b) =>
            ($order[$a['severity'] ?? 'low'] ?? 3) <=> ($order[$b['severity'] ?? 'low'] ?? 3)
        );

        return $data;
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
