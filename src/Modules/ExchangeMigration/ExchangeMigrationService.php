<?php

namespace App\Modules\ExchangeMigration;

use App\Graph\GraphClient;
use App\Helpers\PublicDns;

class ExchangeMigrationService
{
    // SKU part numbers that include Exchange Online mailbox entitlement
    private const EXCHANGE_SKUS = [
        'EXCHANGESTANDARD',
        'EXCHANGEENTERPRISE',
        'EXCHANGE_S_ENTERPRISE',
        'EXCHANGE_S_STANDARD',
        'EXCHANGE_S_DESKLESS',
        'EXCHANGE_B_STANDARD',
        'EXCHANGEESSENTIALS',
        'EXCHANGE_FOUNDATION',
        'SPE_E3',              // Microsoft 365 E3
        'SPE_E5',              // Microsoft 365 E5
        'SPE_F1',              // Microsoft 365 F3
        'O365_BUSINESS',
        'O365_BUSINESS_ESSENTIALS',
        'O365_BUSINESS_PREMIUM',
        'ENTERPRISEPACK',      // Office 365 E3
        'ENTERPRISEPREMIUM',   // Office 365 E5
        'DESKLESSPACK',        // Office 365 F1
        'STANDARDPACK',        // Office 365 E1
        'Microsoft_Teams_Essentials',
    ];

    public function __construct(private GraphClient $graph) {}

    /**
     * Fetch tenant organisation info: display name + verified domains.
     *
     * @return array{displayName: string, verifiedDomains: array}
     */
    public function getOrganization(): array
    {
        try {
            $data = $this->graph->get(
                '/organization',
                ['$select' => 'displayName,verifiedDomains,onPremisesSyncEnabled,onPremisesLastSyncDateTime'],
                'exmig_org',
                900
            );
            $org = $data['value'][0] ?? [];
            return [
                'displayName'                  => $org['displayName'] ?? '',
                'verifiedDomains'              => $org['verifiedDomains'] ?? [],
                'onPremisesSyncEnabled'        => $org['onPremisesSyncEnabled'] ?? false,
                'onPremisesLastSyncDateTime'   => $org['onPremisesLastSyncDateTime'] ?? null,
            ];
        } catch (\Throwable $e) {
            error_log('ExchangeMigration getOrganization: ' . $e->getMessage());
            return ['displayName' => '', 'verifiedDomains' => [], 'onPremisesSyncEnabled' => false, 'onPremisesLastSyncDateTime' => null];
        }
    }

    /**
     * Analyse Exchange Online license coverage.
     *
     * @return array{
     *   exchangeLicenses: int,
     *   totalUsers: int,
     *   licensedUsers: int,
     *   coveragePercent: float,
     *   skus: array
     * }
     */
    public function getLicenseCoverage(): array
    {
        $result = [
            'exchangeLicenses' => 0,
            'totalUsers'       => 0,
            'licensedUsers'    => 0,
            'coveragePercent'  => 0.0,
            'skus'             => [],
        ];

        try {
            $data = $this->graph->get('/subscribedSkus', [], 'exmig_skus', 900);
            $skus = $data['value'] ?? [];

            foreach ($skus as $sku) {
                $partNumber = strtoupper($sku['skuPartNumber'] ?? '');
                if (!in_array($partNumber, self::EXCHANGE_SKUS, true)) {
                    continue;
                }
                $consumed = (int)($sku['consumedUnits'] ?? 0);
                $enabled  = (int)($sku['prepaidUnits']['enabled'] ?? 0);
                $result['exchangeLicenses'] += $enabled;
                $result['licensedUsers']    += $consumed;
                $result['skus'][]           = [
                    'name'     => $sku['skuPartNumber'] ?? '',
                    'enabled'  => $enabled,
                    'consumed' => $consumed,
                ];
            }
        } catch (\Throwable $e) {
            error_log('ExchangeMigration getLicenseCoverage (skus): ' . $e->getMessage());
        }

        try {
            $data = $this->graph->get(
                '/users',
                ['$filter' => "userType eq 'Member' and accountEnabled eq true", '$count' => 'true', '$top' => '1', '$select' => 'id'],
                'exmig_usercount',
                900
            );
            $result['totalUsers'] = (int)($data['@odata.count'] ?? count($data['value'] ?? []));
        } catch (\Throwable $e) {
            error_log('ExchangeMigration getLicenseCoverage (users): ' . $e->getMessage());
        }

        if ($result['totalUsers'] > 0) {
            $result['coveragePercent'] = round(($result['licensedUsers'] / $result['totalUsers']) * 100, 1);
        }

        return $result;
    }

    /**
     * Count users synced from on-premises Active Directory.
     *
     * @return array{synced: int, total: int}
     */
    public function getHybridUserStats(): array
    {
        try {
            $data = $this->graph->get(
                '/users',
                [
                    '$filter' => "onPremisesSyncEnabled eq true and userType eq 'Member'",
                    '$count'  => 'true',
                    '$top'    => '1',
                    '$select' => 'id',
                ],
                'exmig_hybrid_users',
                900
            );
            $synced = (int)($data['@odata.count'] ?? count($data['value'] ?? []));

            $allData = $this->graph->get(
                '/users',
                ['$filter' => "userType eq 'Member' and accountEnabled eq true", '$count' => 'true', '$top' => '1', '$select' => 'id'],
                'exmig_usercount',
                900
            );
            $total = (int)($allData['@odata.count'] ?? 0);

            return ['synced' => $synced, 'total' => $total];
        } catch (\Throwable $e) {
            error_log('ExchangeMigration getHybridUserStats: ' . $e->getMessage());
            return ['synced' => 0, 'total' => 0];
        }
    }

    /**
     * Check DNS records for a domain and return readiness indicators.
     *
     * @return array{
     *   domain: string,
     *   mx: array,
     *   spf: array,
     *   dkim: array,
     *   dmarc: array,
     *   autodiscover: array
     * }
     */
    public function checkDomain(string $domain): array
    {
        return [
            'domain'      => $domain,
            'mx'          => $this->checkMx($domain),
            'spf'         => $this->checkSpf($domain),
            'dkim'        => $this->checkDkim($domain),
            'dmarc'       => $this->checkDmarc($domain),
            'autodiscover' => $this->checkAutodiscover($domain),
        ];
    }

    // ── DNS check helpers ───────────────────────────────────────────────────

    private function checkMx(string $domain): array
    {
        $records = PublicDns::lookup($domain, 'MX'); // entries: "<pref> <target>"
        if (empty($records)) {
            return ['status' => 'missing', 'label' => 'Kein MX-Eintrag gefunden', 'records' => []];
        }

        $targets = array_map(function ($d) {
            $parts = preg_split('/\s+/', trim($d));
            return strtolower(rtrim((string)end($parts), '.'));
        }, $records);
        $o365 = array_filter($targets, fn($t) => str_ends_with($t, '.mail.protection.outlook.com'));

        if (!empty($o365)) {
            return ['status' => 'ok', 'label' => 'Zeigt auf Exchange Online', 'records' => $targets];
        }
        return ['status' => 'warning', 'label' => 'MX zeigt noch nicht auf Exchange Online', 'records' => $targets];
    }

    private function checkSpf(string $domain): array
    {
        foreach (PublicDns::lookup($domain, 'TXT') as $txt) {
            if (stripos($txt, 'v=spf1') !== 0) continue;
            $hasO365 = stripos($txt, 'spf.protection.outlook.com') !== false;
            $all     = $this->extractSpfAll($txt);
            if ($hasO365) {
                $status = ($all === '-all' || $all === '~all') ? 'ok' : 'warning';
                $label  = 'SPF enthält Exchange Online' . ($all ? " ($all)" : '');
            } else {
                $status = 'warning';
                $label  = 'SPF gefunden, aber ohne Exchange Online (spf.protection.outlook.com fehlt)';
            }
            return ['status' => $status, 'label' => $label, 'record' => $txt];
        }
        return ['status' => 'missing', 'label' => 'Kein SPF-Eintrag gefunden', 'record' => null];
    }

    private function checkDkim(string $domain): array
    {
        $results = [];
        foreach (['selector1', 'selector2'] as $sel) {
            $host  = "{$sel}._domainkey.{$domain}";
            $cname = PublicDns::lookup($host, 'CNAME');
            if (!empty($cname)) {
                $target = strtolower($cname[0]);
                // Microsoft DKIM CNAME targets, both the classic and the newer form:
                //   …_domainkey.<tenant>.onmicrosoft.com           (classic)
                //   …_domainkey.<tenant>.<region>.dkim.mail.microsoft (current)
                $isO365 = str_contains($target, 'onmicrosoft.com')
                       || str_contains($target, 'dkim.mail.microsoft')
                       || str_contains($target, '.mail.microsoft');
                $results[$sel] = ['found' => true, 'target' => $target, 'o365' => $isO365];
            } else {
                // Some providers publish DKIM as TXT rather than CNAME.
                $txt = PublicDns::lookup($host, 'TXT');
                if (!empty($txt)) {
                    $results[$sel] = ['found' => true, 'target' => substr($txt[0], 0, 60) . '…', 'o365' => str_contains($txt[0], 'v=DKIM1')];
                } else {
                    $results[$sel] = ['found' => false, 'target' => null, 'o365' => false];
                }
            }
        }

        $anyFound = $results['selector1']['found'] || $results['selector2']['found'];
        $anyO365  = ($results['selector1']['o365'] ?? false) || ($results['selector2']['o365'] ?? false);

        if ($anyO365) {
            $status = 'ok';
            $label  = 'DKIM für Exchange Online konfiguriert';
        } elseif ($anyFound) {
            $status = 'warning';
            $label  = 'DKIM-Einträge gefunden, aber nicht Exchange Online zugeordnet';
        } else {
            $status = 'missing';
            $label  = 'Keine DKIM-Selektoren gefunden (selector1/selector2)';
        }

        return ['status' => $status, 'label' => $label, 'selectors' => $results];
    }

    private function checkDmarc(string $domain): array
    {
        foreach (PublicDns::lookup("_dmarc.{$domain}", 'TXT') as $txt) {
            if (stripos($txt, 'v=DMARC1') !== 0) continue;
            // \b avoids matching the "p=" inside "sp=".
            preg_match('/\bp=\s*([a-z]+)/i', $txt, $pm);
            $policy = strtolower(trim($pm[1] ?? 'none'));
            $hasRua = (bool)preg_match('/\brua=/i', $txt);

            $status = match ($policy) {
                'reject', 'quarantine' => 'ok',
                default => 'warning',
            };
            $label = "DMARC gefunden (p={$policy}" . ($hasRua ? ', rua vorhanden' : '') . ')';
            return ['status' => $status, 'label' => $label, 'record' => $txt, 'policy' => $policy];
        }

        return ['status' => 'missing', 'label' => 'Kein DMARC-Eintrag gefunden', 'record' => null, 'policy' => null];
    }

    private function checkAutodiscover(string $domain): array
    {
        $host = "autodiscover.{$domain}";

        // CNAME check (preferred)
        $cname = PublicDns::lookup($host, 'CNAME');
        if (!empty($cname)) {
            $target = strtolower($cname[0]);
            $isO365 = str_contains($target, 'autodiscover.outlook.com');
            return [
                'status' => $isO365 ? 'ok' : 'warning',
                'label'  => $isO365
                    ? 'Autodiscover → autodiscover.outlook.com (CNAME)'
                    : "Autodiscover CNAME zeigt auf: {$target}",
                'type'   => 'CNAME',
                'target' => $cname[0],
            ];
        }

        // SRV check (_autodiscover._tcp.<domain>) — data: "<pri> <weight> <port> <target>"
        $srv = PublicDns::lookup("_autodiscover._tcp.{$domain}", 'SRV');
        if (!empty($srv)) {
            $parts  = preg_split('/\s+/', trim($srv[0]));
            $target = strtolower((string)end($parts));
            $isO365 = str_contains($target, 'outlook.com');
            return [
                'status' => $isO365 ? 'ok' : 'warning',
                'label'  => $isO365
                    ? 'Autodiscover SRV → Outlook.com'
                    : "Autodiscover SRV zeigt auf: {$target}",
                'type'   => 'SRV',
                'target' => $target,
            ];
        }

        // A record as last resort — could be on-prem
        $a = PublicDns::lookup($host, 'A');
        if (!empty($a)) {
            return [
                'status' => 'warning',
                'label'  => 'Autodiscover hat A-Eintrag (möglicherweise on-prem: ' . $a[0] . ')',
                'type'   => 'A',
                'target' => $a[0],
            ];
        }

        return ['status' => 'missing', 'label' => 'Kein Autodiscover-Eintrag gefunden', 'type' => null, 'target' => null];
    }

    private function extractSpfAll(string $txt): string
    {
        if (preg_match('/\s(-all|~all|\+all|\?all)\s*$/i', $txt, $m)) {
            return strtolower($m[1]);
        }
        return '';
    }

    /**
     * Build the full readiness report for all domains.
     *
     * @return array{
     *   org: array,
     *   license: array,
     *   hybrid: array,
     *   domains: array,
     *   score: array
     * }
     */
    public function getFullReport(): array
    {
        $org     = $this->getOrganization();
        $license = $this->getLicenseCoverage();
        $hybrid  = $this->getHybridUserStats();

        // organization.verifiedDomains entries are ALL verified by definition and
        // carry a 'name' (not 'id' / 'isVerified' — those are /domains fields). The
        // old filter checked $d['isVerified'] which never exists here, so every
        // custom domain was dropped → "only onmicrosoft". Keep all real domains
        // except the onmicrosoft.com routing domains.
        $domains = array_values(array_filter(
            $org['verifiedDomains'],
            fn($d) => !empty($d['name'])
                   && !str_ends_with(strtolower($d['name']), '.onmicrosoft.com')
        ));

        $domainChecks = [];
        foreach ($domains as $d) {
            $domainChecks[] = $this->checkDomain($d['name']);
        }

        $score = $this->computeScore($license, $hybrid, $org, $domainChecks);

        return [
            'org'     => $org,
            'license' => $license,
            'hybrid'  => $hybrid,
            'domains' => $domainChecks,
            'score'   => $score,
        ];
    }

    /**
     * Compute an overall readiness score (0–100) and a readiness label.
     */
    private function computeScore(array $license, array $hybrid, array $org, array $domains): array
    {
        $points    = 0;
        $maxPoints = 0;
        $issues    = [];

        // License coverage (25 pts)
        $maxPoints += 25;
        if ($license['licensedUsers'] > 0) {
            $pct = $license['coveragePercent'];
            if ($pct >= 100) {
                $points += 25;
            } elseif ($pct >= 80) {
                $points += 18;
                $issues[] = "Lizenz-Abdeckung {$pct}% — nicht alle Benutzer haben Exchange Online";
            } elseif ($pct >= 50) {
                $points += 10;
                $issues[] = "Lizenz-Abdeckung {$pct}% — viele Benutzer ohne Exchange Online";
            } else {
                $points += 3;
                $issues[] = "Lizenz-Abdeckung {$pct}% — zu wenige Exchange-Online-Lizenzen";
            }
        } else {
            $issues[] = 'Keine Exchange-Online-Lizenzen gefunden';
        }

        // Per-domain checks (up to 75 pts split across DNS checks)
        if (!empty($domains)) {
            $perDomain   = 75 / count($domains);
            $checks      = ['mx', 'spf', 'dkim', 'dmarc', 'autodiscover'];
            $perCheck    = $perDomain / count($checks);

            foreach ($domains as $dc) {
                $domain = $dc['domain'];
                foreach ($checks as $check) {
                    $maxPoints += $perCheck;
                    $status    = $dc[$check]['status'] ?? 'missing';
                    if ($status === 'ok') {
                        $points += $perCheck;
                    } elseif ($status === 'warning') {
                        $points += $perCheck * 0.5;
                        $label   = $dc[$check]['label'] ?? $check;
                        $issues[] = "[{$domain}] {$label}";
                    } else {
                        $label   = $dc[$check]['label'] ?? "{$check} fehlt";
                        $issues[] = "[{$domain}] {$label}";
                    }
                }
            }
        } else {
            $maxPoints += 75;
            $issues[] = 'Keine verifizierten Domains (außer onmicrosoft.com) gefunden';
        }

        $pct = $maxPoints > 0 ? (int)round(($points / $maxPoints) * 100) : 0;

        if ($pct >= 85) {
            $readiness = 'ready';
            $readinessLabel = 'Bereit für Migration';
        } elseif ($pct >= 55) {
            $readiness = 'partial';
            $readinessLabel = 'Teilweise bereit';
        } else {
            $readiness = 'notready';
            $readinessLabel = 'Nicht bereit';
        }

        return [
            'percent'        => $pct,
            'readiness'      => $readiness,
            'readinessLabel' => $readinessLabel,
            'issues'         => $issues,
        ];
    }
}
