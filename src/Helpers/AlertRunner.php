<?php

namespace App\Helpers;

use App\Core\Config;
use App\Graph\GraphClient;

class AlertRunner
{
    public function __construct(
        private GraphClient $graph,
        private Config $config
    ) {}

    public function run(): array
    {
        $to      = $this->config->get('alert_email_to');
        $appName = $this->config->get('app_name', 'M365 Tenant Tool');
        $sent    = [];

        if (!$to) return $sent;

        // Alert: Risky users
        if ($this->config->get('alert_risky_users', '1') === '1') {
            $risky = $this->getRiskyUsers();
            if ($risky > 0) {
                $body = Mailer::alertTemplate(
                    '⚠️ Risikobenutzer erkannt',
                    "<p><strong>{$risky} Benutzer</strong> sind aktuell als risikobehaftet eingestuft.</p>
                     <p><a href='/security' style='color:#0078d4;'>→ Sicherheitsmodul öffnen</a></p>",
                    $appName
                );
                if (Mailer::send($to, "[{$appName}] {$risky} Risikobenutzer", $body)) {
                    $sent[] = "Risky users alert sent ({$risky} users)";
                }
            }
        }

        // Alert: MFA threshold
        $threshold = (int)$this->config->get('alert_mfa_threshold', '80');
        if ($threshold > 0) {
            [$pct, $total, $registered] = $this->getMfaPct();
            if ($pct < $threshold) {
                $body = Mailer::alertTemplate(
                    '🔐 MFA-Quote unter Schwellwert',
                    "<p>Nur <strong>{$pct}%</strong> der Benutzer haben MFA registriert
                     ({$registered} von {$total}).</p>
                     <p>Konfigurierter Schwellwert: <strong>{$threshold}%</strong></p>
                     <p><a href='/security' style='color:#0078d4;'>→ Sicherheitsmodul öffnen</a></p>",
                    $appName
                );
                if (Mailer::send($to, "[{$appName}] MFA-Quote {$pct}% (Schwellwert: {$threshold}%)", $body)) {
                    $sent[] = "MFA alert sent ({$pct}% < {$threshold}%)";
                }
            }
        }

        // Alert: Anonymous shares
        if ($this->config->get('alert_anon_shares', '1') === '1') {
            $anonCount = $this->getAnonymousShareCount();
            if ($anonCount > 0) {
                $body = Mailer::alertTemplate(
                    '🔗 Anonyme Freigaben gefunden',
                    "<p><strong>{$anonCount} Dateien/Ordner</strong> sind mit 'Anyone'-Links freigegeben
                     (kein Login erforderlich).</p>
                     <p><a href='/sharing' style='color:#0078d4;'>→ Freigaben-Modul öffnen</a></p>",
                    $appName
                );
                if (Mailer::send($to, "[{$appName}] {$anonCount} anonyme Freigaben", $body)) {
                    $sent[] = "Anonymous shares alert sent ({$anonCount})";
                }
            }
        }

        return $sent;
    }

    private function getRiskyUsers(): int
    {
        try {
            $data = $this->graph->get(
                '/identityProtection/riskyUsers',
                ['$count' => 'true', '$top' => '1', '$filter' => "riskState eq 'atRisk'"]
            );
            return (int)($data['@odata.count'] ?? count($data['value'] ?? []));
        } catch (\Throwable) { return 0; }
    }

    private function getMfaPct(): array
    {
        try {
            $data = $this->graph->paginate('/reports/credentialUserRegistrationDetails', [], 50);
            $total = count($data);
            $reg   = count(array_filter($data, fn($u) => $u['isMfaRegistered'] ?? false));
            $pct   = $total > 0 ? (int)round(($reg / $total) * 100) : 100;
            return [$pct, $total, $reg];
        } catch (\Throwable) { return [100, 0, 0]; }
    }

    private function getAnonymousShareCount(): int
    {
        try {
            $sites = $this->graph->paginate('/sites', ['search' => '*', '$select' => 'id'], 5);
            $count = 0;
            foreach (array_slice($sites, 0, 5) as $site) {
                $drives = $this->graph->paginate("/sites/{$site['id']}/drives", ['$select' => 'id'], 3);
                foreach (array_slice($drives, 0, 2) as $drive) {
                    $items = $this->graph->paginate(
                        "/drives/{$drive['id']}/root/search(q='')",
                        ['$select' => 'id,shared', '$filter' => "shared ne null", '$top' => '50'],
                        2
                    );
                    foreach ($items as $item) {
                        if (($item['shared']['scope'] ?? '') === 'anonymous') $count++;
                    }
                }
            }
            return $count;
        } catch (\Throwable) { return 0; }
    }
}
