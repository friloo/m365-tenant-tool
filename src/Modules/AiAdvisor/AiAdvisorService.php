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

        // Security Posture checks — split into general + GDPR groups so the
        // AI can weight them separately and the recommendation library can
        // attach the right citation (BSI/NIS-2 vs DSGVO).
        try {
            $posture = app_service(\App\Modules\SecurityPosture\SecurityPostureService::class)->runChecks();
            $byStatus     = ['pass' => [], 'warn' => [], 'fail' => [], 'unknown' => []];
            $gdprByStatus = ['pass' => [], 'warn' => [], 'fail' => [], 'unknown' => []];
            foreach ($posture as $c) {
                $status = $c['status'] ?? 'warn';
                if (!isset($byStatus[$status])) $status = 'unknown';
                if (($c['category'] ?? '') === 'DSGVO & Datenschutz') {
                    $gdprByStatus[$status][] = $c['id'];
                } else {
                    $byStatus[$status][] = $c['id'];
                }
            }
            $ctx['security_posture'] = [
                'total'          => array_sum(array_map('count', $byStatus)),
                'passed'         => count($byStatus['pass']),
                'warnings'       => count($byStatus['warn']),
                'failed'         => count($byStatus['fail']),
                'unknown'        => count($byStatus['unknown']),
                'failed_checks'  => $byStatus['fail'],
                'warning_checks' => $byStatus['warn'],
                'unknown_checks' => $byStatus['unknown'],
            ];
            $ctx['gdpr_posture'] = [
                'total'          => array_sum(array_map('count', $gdprByStatus)),
                'passed'         => count($gdprByStatus['pass']),
                'warnings'       => count($gdprByStatus['warn']),
                'failed'         => count($gdprByStatus['fail']),
                'unknown'        => count($gdprByStatus['unknown']),
                'failed_checks'  => $gdprByStatus['fail'],
                'warning_checks' => $gdprByStatus['warn'],
                'unknown_checks' => $gdprByStatus['unknown'],
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
                // getSkus() returns normalised keys total/consumed, not the raw
                // Graph keys prepaidUnits/consumedUnits.
                $cap = $sku['total'] ?? 0;
                if ($cap <= 0) continue;
                $skuCount++;
                $used = $sku['consumed'] ?? 0;
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

        // Risky Users / Detections — counts only, no UPNs
        try {
            $rsi = app_service(\App\Modules\RiskySignIns\RiskySignInsService::class);
            $risky = $rsi->getRiskyUsers();
            $det   = $rsi->getRiskyDetections(100);
            $ctx['risky'] = [
                'at_risk_users' => count($risky),
                'high_risk'     => count(array_filter($risky, fn($u) => strtolower($u['riskLevel'] ?? '') === 'high')),
                'medium_risk'   => count(array_filter($risky, fn($u) => strtolower($u['riskLevel'] ?? '') === 'medium')),
                'detections'    => count($det),
            ];
        } catch (\Throwable) {}

        // Defender Alerts — counts by status, no titles
        try {
            $alerts = app_graph()->get(
                '/security/alerts_v2',
                ['$top' => '200', '$select' => 'status,severity'],
                'ai_defender_alerts', 600
            );
            $list = $alerts['value'] ?? [];
            $ctx['defender_alerts'] = [
                'open'        => count(array_filter($list, fn($a) => ($a['status'] ?? '') === 'new')),
                'in_progress' => count(array_filter($list, fn($a) => ($a['status'] ?? '') === 'inProgress')),
                'high'        => count(array_filter($list, fn($a) => ($a['severity'] ?? '') === 'high')),
            ];
        } catch (\Throwable) {}

        // Conditional Access — counts by state
        try {
            $ca = app_graph()->get(
                '/identity/conditionalAccess/policies',
                ['$top' => '200', '$select' => 'state'],
                'ca_policies', 900
            );
            $pol = $ca['value'] ?? [];
            $ctx['conditional_access'] = [
                'total'       => count($pol),
                'enabled'     => count(array_filter($pol, fn($p) => ($p['state'] ?? '') === 'enabled')),
                'report_only' => count(array_filter($pol, fn($p) => ($p['state'] ?? '') === 'enabledForReportingButNotEnforced')),
                'disabled'    => count(array_filter($pol, fn($p) => ($p['state'] ?? '') === 'disabled')),
            ];
        } catch (\Throwable) {}

        // Secure Score
        try {
            $score = app_graph()->get(
                '/security/secureScores',
                ['$top' => '1', '$select' => 'currentScore,maxScore'],
                'securescore_latest', 3600
            );
            $latest = $score['value'][0] ?? null;
            if ($latest && ($latest['maxScore'] ?? 0) > 0) {
                $ctx['secure_score'] = [
                    'current' => round((float)($latest['currentScore'] ?? 0)),
                    'max'     => round((float)($latest['maxScore']     ?? 0)),
                    'pct'     => round((float)$latest['currentScore'] / (float)$latest['maxScore'] * 100),
                ];
            }
        } catch (\Throwable) {}

        // Admin role assignments — total only
        try {
            $r = app_graph()->getEventual(
                '/roleManagement/directory/roleAssignments',
                ['$count' => 'true', '$top' => '1', '$select' => 'id'],
                'dash_admin_count', 1800
            );
            $ctx['admin_roles'] = [
                'assignments' => (int)($r['@odata.count'] ?? 0),
            ];
        } catch (\Throwable) {}

        // Guest users
        try {
            $r = app_graph()->getEventual(
                '/users',
                ['$count' => 'true', '$top' => '1', '$select' => 'id', '$filter' => "userType eq 'Guest'"],
                'dash_guests_count', 1800
            );
            $ctx['guest_users'] = ['total' => (int)($r['@odata.count'] ?? 0)];
        } catch (\Throwable) {}

        // Teams in tenant
        try {
            $r = app_graph()->getEventual(
                '/groups',
                ['$count' => 'true', '$top' => '1', '$select' => 'id', '$filter' => "resourceProvisioningOptions/Any(x:x eq 'Team')"],
                'dash_teams_count', 1800
            );
            $ctx['teams'] = ['total' => (int)($r['@odata.count'] ?? 0)];
        } catch (\Throwable) {}

        // App registrations — counts + expiring secrets (no app names)
        try {
            $apps = app_graph()->paginate(
                '/applications',
                ['$select' => 'id,passwordCredentials,keyCredentials', '$top' => '200'],
                10,
                'ai_app_registrations', 1800
            );
            $now = time();
            $secretsSoon = 0; $secretsExpired = 0;
            foreach ($apps as $app) {
                foreach (($app['passwordCredentials'] ?? []) as $cred) {
                    $end = strtotime($cred['endDateTime'] ?? '');
                    if (!$end) continue;
                    if ($end < $now) $secretsExpired++;
                    elseif ($end < $now + 30*86400) $secretsSoon++;
                }
            }
            $ctx['app_registrations'] = [
                'total'                => count($apps),
                'secrets_expiring_30d' => $secretsSoon,
                'secrets_expired'      => $secretsExpired,
            ];
        } catch (\Throwable) {}

        // Named locations — count by type
        try {
            $r = app_graph()->get(
                '/identity/conditionalAccess/namedLocations',
                ['$top' => '100'],
                'ai_named_locations', 1800
            );
            $list = $r['value'] ?? [];
            $trusted = 0;
            foreach ($list as $loc) {
                if (($loc['@odata.type'] ?? '') === '#microsoft.graph.ipNamedLocation' && ($loc['isTrusted'] ?? false)) {
                    $trusted++;
                }
            }
            $ctx['named_locations'] = ['total' => count($list), 'trusted' => $trusted];
        } catch (\Throwable) {}

        // Domain health (verified vs unverified)
        try {
            $r = app_graph()->paginate(
                '/domains',
                ['$select' => 'id,isVerified,isDefault,authenticationType'],
                5,
                'ai_domains', 3600
            );
            $verified = count(array_filter($r, fn($d) => $d['isVerified'] ?? false));
            $ctx['domains'] = ['total' => count($r), 'verified' => $verified, 'unverified' => count($r) - $verified];
        } catch (\Throwable) {}

        // Audit-Log Anomalien (7-Tage-Rollup + 23-Tage-Baseline)
        try {
            $ctx['audit_log_anomalies'] = app_service(\App\Modules\Anomaly\AuditLogAnomalyService::class)->summarize(7, 23);
        } catch (\Throwable) {}

        // Sign-in Anomalien (24h + 30-Tage-Country-Baseline)
        try {
            $ctx['signin_anomalies'] = app_service(\App\Modules\Anomaly\SignInAnomalyService::class)->summarize(24, 30);
        } catch (\Throwable) {}

        return $ctx;
    }

    /**
     * Returns the most recent analysis if there is one, INCLUDING expired
     * entries (marked as stale). The user can choose to re-run; until then
     * we keep the last result on screen instead of going blank.
     */
    public function getCachedAnalysis(): ?array
    {
        try {
            $row = DB::fetchOne(
                "SELECT data, created_at, expires_at FROM cache WHERE cache_key = 'ai_security_analysis' ORDER BY created_at DESC LIMIT 1"
            );
            if ($row) {
                $data = json_decode($row['data'], true);
                if (is_array($data)) {
                    $data['cached_at']  = $row['created_at'];
                    $data['expires_at'] = $row['expires_at'];
                    $data['is_stale']   = strtotime($row['expires_at']) < time();
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

        $secFailed     = $ctx['security_posture']['failed_checks']  ?? [];
        $secWarning    = $ctx['security_posture']['warning_checks'] ?? [];
        // For the recommendation library we want EVERY failing/warning check —
        // including DSGVO — so the library can attach the right concrete
        // recommendation. The AI prompt itself receives the two buckets
        // separately (handled inside buildPrompt) to avoid double-listing.
        //
        // GDPR-Checks im Status "unknown" werden zusätzlich übergeben — der
        // Status bedeutet meist "konnte nicht geprüft werden, Permission/Lizenz
        // fehlt", was für den Compliance-Verantwortlichen genauso handlungs-
        // relevant ist wie ein echter Fail.
        $allFailed     = array_merge($secFailed,  $ctx['gdpr_posture']['failed_checks']  ?? []);
        $allWarning    = array_merge($secWarning, $ctx['gdpr_posture']['warning_checks'] ?? []);
        $unknownIds    = $ctx['gdpr_posture']['unknown_checks'] ?? [];

        // Always get concrete library recommendations (works without AI)
        $libraryRecs = RecommendationLibrary::get($allFailed, $allWarning, $ctx, $unknownIds);

        $summary = null;
        $score   = null;

        // Only call AI for the tiny executive summary + score
        if ($this->isEnabled()) {
            try {
                $raw     = $this->callApi($ctx, $secFailed, $secWarning);
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
     * Builds the prompt JSON payload sent to the AI provider.
     * Returns the array exactly as it will be transmitted, so a verbatim copy
     * can be stored for the protocol modal.
     */
    private function buildPrompt(array $ctx, array $failedChecks, array $warningChecks): array
    {
        $failedList     = implode(', ', $failedChecks)  ?: 'keine';
        $warningList    = implode(', ', $warningChecks) ?: 'keine';
        $gdprFailed     = implode(', ', $ctx['gdpr_posture']['failed_checks']  ?? []) ?: 'keine';
        $gdprWarning    = implode(', ', $ctx['gdpr_posture']['warning_checks'] ?? []) ?: 'keine';

        // Only aggregated, anonymized metrics — no UPNs/domains/tenant IDs.
        $metrics = [
            'users'               => $ctx['users']               ?? null,
            'licenses'            => $ctx['licenses']            ?? null,
            'devices'             => $ctx['devices']             ?? null,
            'sharing'             => $ctx['sharing']             ?? null,
            'risky'               => $ctx['risky']               ?? null,
            'defender_alerts'     => $ctx['defender_alerts']     ?? null,
            'conditional_access'  => $ctx['conditional_access']  ?? null,
            'secure_score'        => $ctx['secure_score']        ?? null,
            'admin_roles'         => $ctx['admin_roles']         ?? null,
            'guest_users'         => $ctx['guest_users']         ?? null,
            'teams'               => $ctx['teams']               ?? null,
            'app_registrations'   => $ctx['app_registrations']   ?? null,
            'named_locations'     => $ctx['named_locations']     ?? null,
            'domains'             => $ctx['domains']             ?? null,
            'audit_log_anomalies' => $ctx['audit_log_anomalies'] ?? null,
            'signin_anomalies'    => $ctx['signin_anomalies']    ?? null,
        ];
        $metricsJson = json_encode(array_filter($metrics, fn($v) => $v !== null), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        $userMsg = <<<PROMPT
Beurteile die Microsoft-365-Sicherheits- und Datenschutzlage anhand dieser aggregierten, vollständig anonymisierten Metriken (keine PII, keine Tenant-Kennungen, keine UPNs, keine Domain-Namen, keine Länder­namen einzelner Vorfälle, keine IP-Adressen).

Sicherheits-Posture (BSI / NIS-2):
  Fehlgeschlagen: {$failedList}
  Warnung:       {$warningList}

DSGVO-Posture (Art. 5, 25, 32, 44–49):
  Fehlgeschlagen: {$gdprFailed}
  Warnung:       {$gdprWarning}

Aggregierte Metriken & Anomalien (JSON):
{$metricsJson}

Bewerte ganzheitlich:
- Sicherheit (BSI IT-Grundschutz, NIS-2 Art. 21): MFA, Conditional Access, Risiko-Benutzer, Defender-Alerts, Secure Score, Admin-Rollen, App-Secrets, nicht-konforme Geräte.
- Datenschutz (DSGVO): Tenant-Region, SharePoint-/OneDrive-Sharing-Einstellungen, Sensitivity Labels, Aufbewahrung, Audit-Log.
- Anomalien: ungewöhnliche Audit-Kategorien, Credential-Stuffing-Signaturen, Impossible-Travel, Logins aus neuen Ländern.

Identifiziere das kritischste Risiko und nenne, ob es Sicherheit (BSI/NIS-2) oder Datenschutz (DSGVO) betrifft.

Antwort streng als JSON mit diesem Schema (keine Markdown-Fences, keine zusätzlichen Felder):
{"score": 0-100, "summary": "3-4 deutsche Sätze: Gesamtlage, kritischstes Risiko mit Bereich (Sicherheit oder DSGVO), Empfehlung."}
PROMPT;

        $messages = [
            [
                'role'    => 'system',
                'content' => 'You are a Microsoft 365 security & GDPR compliance advisor. '
                           . 'Use ONLY the data given. Do not invent or guess any value. '
                           . 'Respond only in German. Output strictly valid JSON, no markdown fences.',
            ],
            ['role' => 'user', 'content' => $userMsg],
        ];

        return ['messages' => $messages, 'metrics' => $metrics];
    }

    /**
     * Minimal AI call — only asks for a 2-3 sentence German summary + score 0-100.
     * Persists the exact transmitted payload + raw response so the admin
     * can audit what was sent to the provider.
     */
    private function callApi(array $ctx, array $failedChecks, array $warningChecks): string
    {
        $provider = $this->getProvider();
        $apiKey   = $this->config->get('ai_api_key', '', true);
        $model    = $this->config->get('ai_model', '') ?: $this->defaultModel();
        $baseUrl  = trim($this->config->get('ai_base_url', ''));
        $endpoint = $this->getEndpoint($provider, $baseUrl);

        $built    = $this->buildPrompt($ctx, $failedChecks, $warningChecks);
        $messages = $built['messages'];

        $payload = [
            'model'       => $model,
            'messages'    => $messages,
            'temperature' => 0,
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

        // Persist the exact request + response so an admin can audit what
        // was sent. Stored before throwing on error so failures are visible
        // in the protocol modal too.
        $this->saveLastPayload([
            'sent_at'  => date('Y-m-d H:i:s'),
            'provider' => $provider,
            'endpoint' => $endpoint,
            'model'    => $model,
            'request'  => [
                'system_prompt'   => $messages[0]['content'] ?? '',
                'user_prompt'     => $messages[1]['content'] ?? '',
                'metrics_sent'    => $built['metrics'],
                'temperature' => 0,
            ],
            'response' => [
                'http_code' => $httpCode,
                'curl_err'  => $curlErr ?: null,
                'body'      => is_string($response) ? $response : null,
            ],
        ]);

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

    /**
     * Save the last sent payload + raw response for audit purposes.
     * Stored in the existing cache table with a long TTL.
     */
    private function saveLastPayload(array $payload): void
    {
        try {
            DB::execute(
                "INSERT INTO cache (cache_key, data, expires_at)
                 VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 90 DAY))
                 ON DUPLICATE KEY UPDATE data = VALUES(data), expires_at = VALUES(expires_at), created_at = NOW()",
                ['ai_last_payload', json_encode($payload, JSON_UNESCAPED_UNICODE)]
            );
        } catch (\Throwable) {}
    }

    /**
     * Read the most recent payload sent to the AI provider, if any.
     * Returned shape matches saveLastPayload(). null if never called.
     */
    public function getLastPayload(): ?array
    {
        try {
            $row = DB::fetchOne(
                "SELECT data, created_at FROM cache WHERE cache_key = 'ai_last_payload'"
            );
            if ($row) {
                $data = json_decode($row['data'], true);
                if (is_array($data)) {
                    $data['stored_at'] = $row['created_at'];
                    return $data;
                }
            }
        } catch (\Throwable) {}
        return null;
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
