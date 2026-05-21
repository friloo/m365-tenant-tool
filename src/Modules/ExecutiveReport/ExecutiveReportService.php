<?php

namespace App\Modules\ExecutiveReport;

use App\Core\Config;
use App\Graph\GraphClient;
use App\Helpers\Mailer;
use App\Modules\Dashboard\DashboardService;
use App\Modules\SecurityPosture\SecurityPostureService;

/**
 * Monatlicher Executive-Report — eine kompakte HTML-Mail mit den Top-KPIs
 * für die Geschäftsführung. Läuft am ersten Tag jedes Monats und nutzt
 * die existierenden Service-Klassen, damit der Report immer aktuelle
 * Werte hat.
 *
 * Config-Keys:
 *   executive_report_enabled — "1" zum Aktivieren
 *   executive_report_to      — Empfänger-Adresse (CSV erlaubt)
 *   alert_email_from         — Absender (geteilt mit Alert-System)
 *   app_name                 — App-Name für Header
 */
class ExecutiveReportService
{
    public function __construct(private GraphClient $graph) {}

    public function isEnabled(): bool
    {
        return Config::getInstance()->get('executive_report_enabled', '0') === '1';
    }

    /**
     * Erzeugt und versendet den Report. Liefert eine Log-Message für den
     * Cron-Runner. Nie ein Throwable — alle Fehler werden in der Message
     * gemeldet.
     */
    public function generate(): string
    {
        $config = Config::getInstance();
        $to     = $config->get('executive_report_to', '') ?: $config->get('alert_email_to', '');
        if (!$to) return 'Kein Empfänger konfiguriert';

        try {
            $kpis = $this->collectKpis();
        } catch (\Throwable $e) {
            return 'KPI-Sammlung fehlgeschlagen: ' . $e->getMessage();
        }

        $appName = $config->get('app_name', 'M365 Tenant Tool');
        $subject = sprintf('[%s] Executive-Report %s', $appName, date('F Y'));
        $html    = $this->renderHtml($kpis, $appName);

        $recipients = preg_split('/[\s,;]+/', $to, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $sent = 0;
        foreach ($recipients as $addr) {
            if (Mailer::send(trim($addr), $subject, $html)) $sent++;
        }
        return $sent > 0
            ? "Executive-Report versendet an {$sent} Empfänger"
            : 'Versand fehlgeschlagen — SMTP/Mail-Config prüfen';
    }

    public function previewHtml(): string
    {
        try {
            $kpis = $this->collectKpis();
        } catch (\Throwable $e) {
            $kpis = ['_error' => $e->getMessage()];
        }
        $appName = Config::getInstance()->get('app_name', 'M365 Tenant Tool');
        return $this->renderHtml($kpis, $appName);
    }

    // ── KPI-Sammlung ───────────────────────────────────────────────────────

    private function collectKpis(): array
    {
        $kpis = ['_generated_at' => date('Y-m-d H:i')];

        // Dashboard-Service liefert schon die Hauptmetriken
        try {
            $ds              = new DashboardService($this->graph);
            $kpis['metrics'] = $ds->getMetrics();
            $kpis['security']= $ds->getSecurityStatus();
            $kpis['extended']= $ds->getExtendedStats();
        } catch (\Throwable) {}

        // Posture: Score-Aggregate
        try {
            $sps     = new SecurityPostureService($this->graph);
            $checks  = $sps->runChecks();
            $kpis['posture'] = $sps->getScore($checks);
            // Top Findings — nur fail/warn
            $topFails = [];
            foreach ($checks as $c) {
                if (($c['status'] ?? '') === 'fail') {
                    $topFails[] = $c['label'] ?? $c['id'];
                    if (count($topFails) >= 5) break;
                }
            }
            $kpis['top_fails'] = $topFails;
        } catch (\Throwable) {}

        return $kpis;
    }

    // ── HTML-Template ──────────────────────────────────────────────────────

    private function renderHtml(array $kpis, string $appName): string
    {
        $month = date('F Y');
        $score = (int)($kpis['posture']['percent'] ?? 0);
        $scoreColor = $score >= 75 ? '#16a34a' : ($score >= 50 ? '#d97706' : '#dc2626');

        $m = $kpis['metrics'] ?? [];
        $s = $kpis['security'] ?? [];
        $x = $kpis['extended'] ?? [];

        $tile = function (string $label, $value, string $sub = '', string $color = '#0078d4') {
            $value = $value === null ? '–' : (is_int($value) ? number_format($value) : (string)$value);
            return '<td style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:14px;width:25%;">'
                 . '<div style="font-size:11px;color:#64748b;text-transform:uppercase;letter-spacing:.5px;">' . htmlspecialchars($label) . '</div>'
                 . '<div style="font-size:24px;font-weight:700;color:' . $color . ';margin:4px 0;">' . htmlspecialchars($value) . '</div>'
                 . '<div style="font-size:11px;color:#94a3b8;">' . htmlspecialchars($sub) . '</div>'
                 . '</td>';
        };

        $html  = '<!DOCTYPE html><html><head><meta charset="utf-8"></head>';
        $html .= '<body style="font-family:Segoe UI,system-ui,sans-serif;background:#f1f5f9;margin:0;padding:24px;color:#0f172a;">';
        $html .= '<table style="max-width:680px;margin:0 auto;background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,.06);">';
        $html .= '<tr><td style="background:linear-gradient(135deg,#0078d4,#005a9e);color:#fff;padding:24px 28px;">';
        $html .= '<div style="font-size:12px;letter-spacing:1px;text-transform:uppercase;opacity:.85;">' . htmlspecialchars($appName) . '</div>';
        $html .= '<div style="font-size:22px;font-weight:700;margin-top:4px;">Executive Report — ' . htmlspecialchars($month) . '</div>';
        $html .= '</td></tr>';

        // Score
        $html .= '<tr><td style="padding:24px 28px 8px;">';
        $html .= '<div style="display:inline-block;background:' . $scoreColor . ';color:#fff;padding:6px 14px;border-radius:999px;font-weight:600;font-size:13px;">';
        $html .= 'Security-Score: ' . $score . '%';
        $html .= '</div>';
        $html .= '<p style="margin:14px 0 0;color:#475569;font-size:14px;line-height:1.5;">'
              . 'Stand: ' . htmlspecialchars($kpis['_generated_at'] ?? '') . '. '
              . ($score >= 75 ? 'Der Tenant befindet sich in einem soliden Sicherheitszustand.'
              :  ($score >= 50 ? 'Der Tenant hat Verbesserungspotenzial in mehreren Bereichen.'
                              :  'Der Tenant weist kritische Sicherheitslücken auf — sofortiger Handlungsbedarf.'))
              . '</p>';
        $html .= '</td></tr>';

        // KPI tiles
        $html .= '<tr><td style="padding:20px 28px 8px;">';
        $html .= '<div style="font-size:11px;color:#64748b;text-transform:uppercase;letter-spacing:.5px;margin-bottom:10px;">Tenant-Kennzahlen</div>';
        $html .= '<table style="width:100%;border-collapse:separate;border-spacing:6px;"><tr>';
        $html .= $tile('Benutzer',   $m['total_users']   ?? null, ($m['enabled_users'] ?? 0) . ' aktiv');
        $html .= $tile('Geräte',     $m['total_devices'] ?? null, ($s['non_compliant'] ?? 0) . ' nicht konform');
        $html .= $tile('MFA-Quote',  ($s['mfa_pct'] ?? null) !== null ? $s['mfa_pct'] . '%' : null, '', (($s['mfa_pct'] ?? 0) >= 80 ? '#16a34a' : '#d97706'));
        $html .= $tile('CA-Policies',$s['ca_enabled']   ?? null, ($s['ca_report_only'] ?? 0) . ' report-only');
        $html .= '</tr></table>';
        $html .= '</td></tr>';

        // Risk row
        $html .= '<tr><td style="padding:8px 28px;">';
        $html .= '<table style="width:100%;border-collapse:separate;border-spacing:6px;"><tr>';
        $html .= $tile('Risikobenutzer', $m['risky_users']        ?? null, '', ($m['risky_users'] ?? 0) > 0 ? '#dc2626' : '#16a34a');
        $html .= $tile('Defender Alerts',$s['unresolved_alerts']  ?? null, 'offen/in Bearbeitung', ($s['unresolved_alerts'] ?? 0) > 0 ? '#dc2626' : '#16a34a');
        $html .= $tile('Gastbenutzer',   $x['guests']             ?? null);
        $html .= $tile('Lizenz-SKUs',    $m['license_products']   ?? null);
        $html .= '</tr></table>';
        $html .= '</td></tr>';

        // Top fails
        if (!empty($kpis['top_fails'])) {
            $html .= '<tr><td style="padding:20px 28px 8px;">';
            $html .= '<div style="font-size:11px;color:#64748b;text-transform:uppercase;letter-spacing:.5px;margin-bottom:10px;">Top-Findings — fehlgeschlagene Posture-Checks</div>';
            $html .= '<ul style="margin:0;padding-left:20px;color:#334155;font-size:14px;line-height:1.7;">';
            foreach ($kpis['top_fails'] as $f) {
                $html .= '<li>' . htmlspecialchars($f) . '</li>';
            }
            $html .= '</ul>';
            $html .= '</td></tr>';
        }

        // Footer
        $baseUrl = Config::getInstance()->get('app_base_url', '');
        $html .= '<tr><td style="padding:20px 28px 28px;color:#64748b;font-size:12px;border-top:1px solid #e2e8f0;">';
        $html .= 'Diese Mail wurde automatisch erzeugt. Den vollständigen Bericht inkl. Empfehlungen finden Sie ';
        if ($baseUrl) {
            $html .= '<a href="' . htmlspecialchars(rtrim($baseUrl, '/')) . '/ai" style="color:#0078d4;">im KI-Sicherheitsberater</a>.';
        } else {
            $html .= 'im Tool unter /ai.';
        }
        $html .= '</td></tr>';

        $html .= '</table></body></html>';
        return $html;
    }
}
