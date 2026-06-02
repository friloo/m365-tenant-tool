<?php

namespace App\Modules\WeeklyReport;

use App\Core\Config;
use App\Database\DB;
use App\Graph\GraphClient;
use App\Helpers\Mailer;

/**
 * WeeklyReportService
 *
 * Builds and sends a weekly HTML status report email with key M365 tenant metrics.
 *
 * Config keys read (managed by SettingsController — do not modify here):
 *   weekly_report_enabled  — "1" to enable, "0" to disable
 *   weekly_report_day      — 1–7 (day of week, 1=Monday), default 1
 *   alert_email_to         — recipient address
 *   app_name               — application name shown in email
 *   app_base_url           — base URL for links in the email
 */
class WeeklyReportService
{
    public function __construct(private GraphClient $graph) {}

    /**
     * Whether the weekly report feature is enabled via config.
     */
    public function isEnabled(): bool
    {
        return Config::getInstance()->get('weekly_report_enabled', '0') === '1';
    }

    /**
     * Build and send the weekly HTML report email.
     *
     * @return string  Log message indicating outcome.
     */
    public function generate(): string
    {
        $config    = Config::getInstance();
        $recipient = $config->get('alert_email_to', '');
        $appName   = $config->get('app_name', 'M365 Tenant Tool');
        $baseUrl   = rtrim($config->get('app_base_url', ''), '/');

        if (empty($recipient)) {
            return 'Kein Empfänger konfiguriert';
        }

        $generatedAt = date('d.m.Y H:i');

        // ── Collect stats, each wrapped in try/catch so one failure never kills the report ──

        // 1. Benutzer: total enabled, MFA %, inactive >90 days
        $usersTotal   = '–';
        $mfaPct       = '–';
        $staleCount   = '–';
        try {
            $usersData = $this->graph->get(
                '/users',
                ['$filter' => 'accountEnabled eq true', '$select' => 'id', '$top' => '1', '$count' => 'true'],
                'weekly_users_enabled',
                300
            );
            $usersTotal = (int)($usersData['@odata.count'] ?? count($usersData['value'] ?? []));
        } catch (\Throwable) {}

        try {
            // Modern registration report (with legacy fallback) — the old
            // credentialUserRegistrationDetails endpoint 404s on current tenants.
            $mfaRows      = (new \App\Modules\MfaMethods\MfaMethodsService($this->graph))->getAll();
            $mfaRegistered = count(array_filter($mfaRows, fn($r) => !empty($r['isMfaRegistered'])));
            $mfaTotal      = count($mfaRows);
            if (is_int($usersTotal) && $usersTotal > 0) {
                $mfaPct = (int)round($mfaRegistered / $usersTotal * 100) . '%';
            } elseif ($mfaTotal > 0) {
                $mfaPct = (int)round($mfaRegistered / $mfaTotal * 100) . '%';
            }
        } catch (\Throwable) {}

        try {
            $cutoff   = date('Y-m-d\TH:i:s\Z', strtotime('-90 days'));
            $staleData = $this->graph->get(
                '/users',
                [
                    '$filter' => "accountEnabled eq true and signInActivity/lastSignInDateTime le {$cutoff}",
                    '$select' => 'id',
                    '$top'    => '1',
                    '$count'  => 'true',
                ],
                'weekly_stale_users',
                300
            );
            $staleCount = (int)($staleData['@odata.count'] ?? count($staleData['value'] ?? []));
        } catch (\Throwable) {}

        // 2. Lizenzen: total purchased, consumed, free
        $licTotal     = '–';
        $licConsumed  = '–';
        $licFree      = '–';
        try {
            $skuData = $this->graph->get(
                '/subscribedSkus',
                [],
                'weekly_subscribed_skus',
                300
            );
            $skus = $skuData['value'] ?? [];
            $purchased = 0;
            $consumed  = 0;
            foreach ($skus as $sku) {
                if (($sku['capabilityStatus'] ?? '') === 'Deleted') {
                    continue;
                }
                $purchased += (int)($sku['prepaidUnits']['enabled'] ?? 0);
                $consumed  += (int)($sku['consumedUnits'] ?? 0);
            }
            $licTotal    = $purchased;
            $licConsumed = $consumed;
            $licFree     = max(0, $purchased - $consumed);
        } catch (\Throwable) {}

        // 3. Freigaben: count by status from share_reviews table
        $sharesActive  = '–';
        $sharesPending = '–';
        $sharesRevoked = '–';
        try {
            $shareRows = DB::fetchAll(
                'SELECT status, COUNT(*) as cnt FROM share_reviews GROUP BY status'
            );
            $shareCounts = [];
            foreach ($shareRows as $row) {
                $shareCounts[$row['status']] = (int)$row['cnt'];
            }
            $sharesActive  = $shareCounts['active'] ?? 0;
            $sharesPending = $shareCounts['pending_review'] ?? 0;
            $sharesRevoked = $shareCounts['revoked'] ?? 0;
        } catch (\Throwable) {}

        // 4. Secure Score: current + max
        $secureScoreCurrent = '–';
        $secureScoreMax     = '–';
        try {
            $ssData = $this->graph->get(
                '/security/secureScores',
                ['$top' => '1'],
                'weekly_secure_score',
                300
            );
            $ssItems = $ssData['value'] ?? [];
            if (!empty($ssItems)) {
                $secureScoreCurrent = (int)($ssItems[0]['currentScore'] ?? 0);
                $secureScoreMax     = (int)($ssItems[0]['maxScore']     ?? 0);
            }
        } catch (\Throwable) {}

        // 5. Risikobenutzer: count at risk (top 100, use array length)
        $riskyUserCount = '–';
        try {
            $riskyData = $this->graph->get(
                '/identityProtection/riskyUsers',
                [
                    '$filter' => "riskState eq 'atRisk'",
                    '$top'    => '100',
                    '$select' => 'id',
                ],
                'weekly_risky_users',
                300
            );
            $riskyUserCount = count($riskyData['value'] ?? []);
        } catch (\Throwable) {}

        // 6. Dienststatus: count of active incidents
        $activeIncidents = '–';
        try {
            $issueData = $this->graph->get(
                '/admin/serviceAnnouncement/issues',
                [
                    '$filter'  => 'isResolved eq false',
                    '$select'  => 'id',
                    '$top'     => '100',
                ],
                'servicehealth_issues',   // re-use existing cache key if populated
                300
            );
            $activeIncidents = count($issueData['value'] ?? []);
        } catch (\Throwable) {}

        // ── Build email body ──────────────────────────────────────────────

        $body = $this->buildHtmlTable([
            'appName'           => $appName,
            'generatedAt'       => $generatedAt,
            'baseUrl'           => $baseUrl,
            'usersTotal'        => $usersTotal,
            'mfaPct'            => $mfaPct,
            'staleCount'        => $staleCount,
            'licTotal'          => $licTotal,
            'licConsumed'       => $licConsumed,
            'licFree'           => $licFree,
            'sharesActive'      => $sharesActive,
            'sharesPending'     => $sharesPending,
            'sharesRevoked'     => $sharesRevoked,
            'secureScoreCurrent'=> $secureScoreCurrent,
            'secureScoreMax'    => $secureScoreMax,
            'riskyUserCount'    => $riskyUserCount,
            'activeIncidents'   => $activeIncidents,
        ]);

        $subject = '[' . $appName . '] Wöchentlicher Status-Report – ' . date('d.m.Y');
        $html    = Mailer::alertTemplate('Wöchentlicher Status-Report', $body, $appName);

        Mailer::send($recipient, $subject, $html);

        return 'Report gesendet an ' . $recipient;
    }

    /**
     * Build the HTML stats table for the report body.
     */
    private function buildHtmlTable(array $d): string
    {
        $baseUrl = htmlspecialchars($d['baseUrl'], ENT_QUOTES, 'UTF-8');
        $appName = htmlspecialchars((string)$d['appName'], ENT_QUOTES, 'UTF-8');
        $ts      = htmlspecialchars((string)$d['generatedAt'], ENT_QUOTES, 'UTF-8');

        $e = fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');

        // Highlight helpers
        $warn = fn($v, bool $bad): string => $bad
            ? '<span style="color:#d97706;font-weight:600;">' . $e($v) . '</span>'
            : $e($v);

        $riskyBad     = is_int($d['riskyUserCount'])   && $d['riskyUserCount'] > 0;
        $incidentsBad = is_int($d['activeIncidents'])  && $d['activeIncidents'] > 0;
        $pendingBad   = is_int($d['sharesPending'])    && $d['sharesPending'] > 0;

        $html = <<<HTML
<p style="margin:0 0 16px; color:#374151; font-size:14px;">
    Erstellt am <strong>{$ts}</strong> &nbsp;|&nbsp; App: <strong>{$appName}</strong>
</p>

<table style="width:100%; border-collapse:collapse; font-size:14px;">
    <thead>
        <tr style="background:#f3f4f6;">
            <th style="text-align:left; padding:10px 12px; border-bottom:2px solid #e5e7eb; color:#374151;">Bereich</th>
            <th style="text-align:left; padding:10px 12px; border-bottom:2px solid #e5e7eb; color:#374151;">Kennzahl</th>
            <th style="text-align:right; padding:10px 12px; border-bottom:2px solid #e5e7eb; color:#374151;">Wert</th>
        </tr>
    </thead>
    <tbody>
        <!-- Benutzer -->
        <tr style="background:#ffffff;">
            <td rowspan="3" style="padding:10px 12px; border-bottom:1px solid #e5e7eb; vertical-align:top; color:#374151; font-weight:600;">
                👤 Benutzer
            </td>
            <td style="padding:10px 12px; border-bottom:1px solid #e5e7eb; color:#6b7280;">Aktive Nutzer gesamt</td>
            <td style="padding:10px 12px; border-bottom:1px solid #e5e7eb; text-align:right; font-weight:600;">{$e($d['usersTotal'])}</td>
        </tr>
        <tr style="background:#fafafa;">
            <td style="padding:10px 12px; border-bottom:1px solid #e5e7eb; color:#6b7280;">MFA registriert</td>
            <td style="padding:10px 12px; border-bottom:1px solid #e5e7eb; text-align:right; font-weight:600;">{$e($d['mfaPct'])}</td>
        </tr>
        <tr style="background:#ffffff;">
            <td style="padding:10px 12px; border-bottom:1px solid #e5e7eb; color:#6b7280;">Inaktiv &gt; 90 Tage</td>
            <td style="padding:10px 12px; border-bottom:1px solid #e5e7eb; text-align:right; font-weight:600;">{$e($d['staleCount'])}</td>
        </tr>

        <!-- Lizenzen -->
        <tr style="background:#f9fafb;">
            <td rowspan="3" style="padding:10px 12px; border-bottom:1px solid #e5e7eb; vertical-align:top; color:#374151; font-weight:600;">
                🪪 Lizenzen
            </td>
            <td style="padding:10px 12px; border-bottom:1px solid #e5e7eb; color:#6b7280;">Gesamt erworben</td>
            <td style="padding:10px 12px; border-bottom:1px solid #e5e7eb; text-align:right; font-weight:600;">{$e($d['licTotal'])}</td>
        </tr>
        <tr style="background:#ffffff;">
            <td style="padding:10px 12px; border-bottom:1px solid #e5e7eb; color:#6b7280;">Verbraucht</td>
            <td style="padding:10px 12px; border-bottom:1px solid #e5e7eb; text-align:right; font-weight:600;">{$e($d['licConsumed'])}</td>
        </tr>
        <tr style="background:#fafafa;">
            <td style="padding:10px 12px; border-bottom:1px solid #e5e7eb; color:#6b7280;">Freie Slots</td>
            <td style="padding:10px 12px; border-bottom:1px solid #e5e7eb; text-align:right; font-weight:600;">{$e($d['licFree'])}</td>
        </tr>

        <!-- Freigaben -->
        <tr style="background:#f9fafb;">
            <td rowspan="3" style="padding:10px 12px; border-bottom:1px solid #e5e7eb; vertical-align:top; color:#374151; font-weight:600;">
                🔗 Freigaben
            </td>
            <td style="padding:10px 12px; border-bottom:1px solid #e5e7eb; color:#6b7280;">Aktiv</td>
            <td style="padding:10px 12px; border-bottom:1px solid #e5e7eb; text-align:right; font-weight:600;">{$e($d['sharesActive'])}</td>
        </tr>
        <tr style="background:#ffffff;">
            <td style="padding:10px 12px; border-bottom:1px solid #e5e7eb; color:#6b7280;">Ausstehende Reviews</td>
            <td style="padding:10px 12px; border-bottom:1px solid #e5e7eb; text-align:right; font-weight:600;">{$warn($d['sharesPending'], $pendingBad)}</td>
        </tr>
        <tr style="background:#fafafa;">
            <td style="padding:10px 12px; border-bottom:1px solid #e5e7eb; color:#6b7280;">Widerrufen</td>
            <td style="padding:10px 12px; border-bottom:1px solid #e5e7eb; text-align:right; font-weight:600;">{$e($d['sharesRevoked'])}</td>
        </tr>

        <!-- Secure Score -->
        <tr style="background:#ffffff;">
            <td rowspan="2" style="padding:10px 12px; border-bottom:1px solid #e5e7eb; vertical-align:top; color:#374151; font-weight:600;">
                🛡 Secure Score
            </td>
            <td style="padding:10px 12px; border-bottom:1px solid #e5e7eb; color:#6b7280;">Aktueller Score</td>
            <td style="padding:10px 12px; border-bottom:1px solid #e5e7eb; text-align:right; font-weight:600;">{$e($d['secureScoreCurrent'])}</td>
        </tr>
        <tr style="background:#fafafa;">
            <td style="padding:10px 12px; border-bottom:1px solid #e5e7eb; color:#6b7280;">Maximaler Score</td>
            <td style="padding:10px 12px; border-bottom:1px solid #e5e7eb; text-align:right; font-weight:600;">{$e($d['secureScoreMax'])}</td>
        </tr>

        <!-- Risikobenutzer -->
        <tr style="background:#ffffff;">
            <td style="padding:10px 12px; border-bottom:1px solid #e5e7eb; color:#374151; font-weight:600;">
                ⚠️ Risikobenutzer
            </td>
            <td style="padding:10px 12px; border-bottom:1px solid #e5e7eb; color:#6b7280;">Nutzer mit Risikostatus „atRisk"</td>
            <td style="padding:10px 12px; border-bottom:1px solid #e5e7eb; text-align:right; font-weight:600;">{$warn($d['riskyUserCount'], $riskyBad)}</td>
        </tr>

        <!-- Dienststatus -->
        <tr style="background:#fafafa;">
            <td style="padding:10px 12px; border-bottom:1px solid #e5e7eb; color:#374151; font-weight:600;">
                🌐 Dienststatus
            </td>
            <td style="padding:10px 12px; border-bottom:1px solid #e5e7eb; color:#6b7280;">Aktive Incidents</td>
            <td style="padding:10px 12px; border-bottom:1px solid #e5e7eb; text-align:right; font-weight:600;">{$warn($d['activeIncidents'], $incidentsBad)}</td>
        </tr>
    </tbody>
</table>
HTML;

        if (!empty($d['baseUrl'])) {
            $html .= <<<HTML

<p style="margin:24px 0 0; font-size:13px; color:#6b7280;">
    <a href="{$baseUrl}/dashboard" style="color:#3b82f6;">→ Dashboard öffnen</a>
    &nbsp;|&nbsp;
    <a href="{$baseUrl}/securescore" style="color:#3b82f6;">Secure Score</a>
    &nbsp;|&nbsp;
    <a href="{$baseUrl}/sharereview" style="color:#3b82f6;">Freigaben</a>
    &nbsp;|&nbsp;
    <a href="{$baseUrl}/riskysignins" style="color:#3b82f6;">Risikobenutzer</a>
</p>
HTML;
        }

        return $html;
    }
}
