<?php

namespace App\Modules\DomainHealth;

use App\Graph\GraphClient;

class DomainHealthService
{
    public function __construct(private GraphClient $graph) {}

    public function getDomains(): array
    {
        return $this->graph->paginate(
            '/domains',
            [
                '$select' => 'id,isDefault,isVerified,authenticationType',
                '$top'    => '999',
            ],
            20,
            'domains_all',
            1800
        );
    }

    public function getAll(): array
    {
        $domains = $this->getDomains();
        $result  = [];

        foreach ($domains as $domain) {
            $id = $domain['id'] ?? '';
            if ($id === '') {
                continue;
            }

            $spf   = $this->checkSpf($id);
            $dkim  = $this->checkDkim($id);
            $dmarc = $this->checkDmarc($id);

            $result[] = array_merge($domain, [
                'spf'   => $spf,
                'dkim'  => $dkim,
                'dmarc' => $dmarc,
            ]);
        }

        return $result;
    }

    public function getSummary(array $domains): array
    {
        $total          = count($domains);
        $fullyProtected = 0;
        $withIssues     = 0;
        $byStatus       = ['spf_pass' => 0, 'dkim_pass' => 0, 'dmarc_reject' => 0, 'dmarc_quarantine' => 0, 'dmarc_report_only' => 0, 'dmarc_missing' => 0];

        foreach ($domains as $d) {
            $spf   = $d['spf'] ?? 'missing';
            $dkim  = $d['dkim'] ?? 'missing';
            $dmarc = $d['dmarc'] ?? 'missing';

            if ($spf === 'pass') {
                $byStatus['spf_pass']++;
            }
            if ($dkim === 'pass') {
                $byStatus['dkim_pass']++;
            }
            if ($dmarc === 'reject') {
                $byStatus['dmarc_reject']++;
            } elseif ($dmarc === 'quarantine') {
                $byStatus['dmarc_quarantine']++;
            } elseif ($dmarc === 'report_only') {
                $byStatus['dmarc_report_only']++;
            } elseif ($dmarc === 'missing') {
                $byStatus['dmarc_missing']++;
            }

            $dmarcOk = in_array($dmarc, ['reject', 'quarantine'], true);
            if ($spf === 'pass' && $dkim === 'pass' && $dmarcOk) {
                $fullyProtected++;
            } else {
                $withIssues++;
            }
        }

        return [
            'total'          => $total,
            'fullyProtected' => $fullyProtected,
            'withIssues'     => $withIssues,
            'byStatus'       => $byStatus,
        ];
    }

    private function checkSpf(string $domain): string
    {
        $cacheKey = 'dns_spf_' . md5($domain);

        if (function_exists('apcu_fetch')) {
            $cached = apcu_fetch($cacheKey, $success);
            if ($success) {
                return $cached;
            }
        }

        $result  = 'missing';
        $records = @dns_get_record($domain, DNS_TXT);

        if (is_array($records)) {
            foreach ($records as $record) {
                $txt = $record['txt'] ?? $record['entries'][0] ?? '';
                if (str_contains($txt, 'v=spf1')) {
                    $result = 'pass';
                    break;
                }
            }
        }

        if (function_exists('apcu_store')) {
            apcu_store($cacheKey, $result, 3600);
        }

        return $result;
    }

    private function checkDkim(string $domain): string
    {
        $cacheKey = 'dns_dkim_' . md5($domain);

        if (function_exists('apcu_fetch')) {
            $cached = apcu_fetch($cacheKey, $success);
            if ($success) {
                return $cached;
            }
        }

        $result   = 'missing';
        $selector = 'selector1._domainkey.' . $domain;
        $records  = @dns_get_record($selector, DNS_CNAME | DNS_TXT);

        if (is_array($records) && count($records) > 0) {
            $result = 'pass';
        }

        if (function_exists('apcu_store')) {
            apcu_store($cacheKey, $result, 3600);
        }

        return $result;
    }

    private function checkDmarc(string $domain): string
    {
        $cacheKey = 'dns_dmarc_' . md5($domain);

        if (function_exists('apcu_fetch')) {
            $cached = apcu_fetch($cacheKey, $success);
            if ($success) {
                return $cached;
            }
        }

        $result  = 'missing';
        $records = @dns_get_record('_dmarc.' . $domain, DNS_TXT);

        if (is_array($records)) {
            foreach ($records as $record) {
                $txt = $record['txt'] ?? $record['entries'][0] ?? '';
                if (str_contains($txt, 'v=DMARC1')) {
                    if (preg_match('/p=reject\b/i', $txt)) {
                        $result = 'reject';
                    } elseif (preg_match('/p=quarantine\b/i', $txt)) {
                        $result = 'quarantine';
                    } elseif (preg_match('/p=none\b/i', $txt)) {
                        $result = 'report_only';
                    } else {
                        $result = 'report_only';
                    }
                    break;
                }
            }
        }

        if (function_exists('apcu_store')) {
            apcu_store($cacheKey, $result, 3600);
        }

        return $result;
    }

    public function bustDnsCache(string $domain): void
    {
        if (!function_exists('apcu_delete')) {
            return;
        }
        apcu_delete('dns_spf_' . md5($domain));
        apcu_delete('dns_dkim_' . md5($domain));
        apcu_delete('dns_dmarc_' . md5($domain));
    }
}
