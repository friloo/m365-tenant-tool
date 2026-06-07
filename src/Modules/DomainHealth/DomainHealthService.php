<?php

namespace App\Modules\DomainHealth;

use App\Graph\GraphClient;

class DomainHealthService
{
    /** Set per request when ?refresh=1 — bypasses the DNS result cache. */
    private bool $refresh = false;

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

    public function getAll(bool $refresh = false): array
    {
        $this->refresh = $refresh;
        $domains = $this->getDomains();
        $result  = [];

        foreach ($domains as $domain) {
            $id = $domain['id'] ?? '';
            if ($id === '') {
                continue;
            }

            $result[] = array_merge($domain, [
                'spf'   => $this->checkSpf($id),
                'dkim'  => $this->checkDkim($id),
                'dmarc' => $this->checkDmarc($id),
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

            if ($spf === 'pass')  $byStatus['spf_pass']++;
            if ($dkim === 'pass') $byStatus['dkim_pass']++;
            if ($dmarc === 'reject')          $byStatus['dmarc_reject']++;
            elseif ($dmarc === 'quarantine')  $byStatus['dmarc_quarantine']++;
            elseif ($dmarc === 'report_only') $byStatus['dmarc_report_only']++;
            elseif ($dmarc === 'missing')     $byStatus['dmarc_missing']++;

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
        return $this->cached('dns_spf_' . md5($domain), function () use ($domain) {
            foreach ($this->dnsLookup($domain, 'TXT') as $txt) {
                if (stripos($txt, 'v=spf1') !== false) return 'pass';
            }
            return 'missing';
        });
    }

    private function checkDkim(string $domain): string
    {
        return $this->cached('dns_dkim_' . md5($domain), function () use ($domain) {
            // Microsoft 365 publishes selector1 AND selector2 CNAMEs; either present
            // means DKIM records are configured. (DNS presence ≠ signing enabled —
            // that is only verifiable via Exchange Online PowerShell.)
            foreach (['selector1', 'selector2'] as $sel) {
                if (!empty($this->dnsLookup($sel . '._domainkey.' . $domain, 'CNAME'))) {
                    return 'pass';
                }
            }
            return 'missing';
        });
    }

    private function checkDmarc(string $domain): string
    {
        return $this->cached('dns_dmarc_' . md5($domain), function () use ($domain) {
            foreach ($this->dnsLookup('_dmarc.' . $domain, 'TXT') as $txt) {
                if (stripos($txt, 'v=DMARC1') === false) continue;
                if (preg_match('/p=\s*reject\b/i', $txt))     return 'reject';
                if (preg_match('/p=\s*quarantine\b/i', $txt)) return 'quarantine';
                return 'report_only'; // p=none or unspecified
            }
            return 'missing';
        });
    }

    /** apcu-cached wrapper (1h), bypassed on ?refresh=1. */
    private function cached(string $key, callable $fn): string
    {
        if (!$this->refresh && function_exists('apcu_fetch')) {
            $hit = apcu_fetch($key, $ok);
            if ($ok) return $hit;
        }
        $val = $fn();
        if (function_exists('apcu_store')) {
            apcu_store($key, $val, 3600);
        }
        return $val;
    }

    /**
     * Resolve DNS records over the PUBLIC internet, not the server's local
     * resolver. This is essential because the server may sit inside the org's
     * own domain with split-horizon DNS (an internal AD zone that lacks the
     * public SPF/DKIM/DMARC records) — which made the tenant's own domain show
     * up as "missing". DNS-over-HTTPS (Google → Cloudflare) always returns the
     * public view; the system resolver is only a last-resort fallback.
     *
     * @return string[] record data strings (TXT contents or CNAME targets)
     */
    private function dnsLookup(string $name, string $type): array
    {
        foreach ([
            'https://dns.google/resolve?name=' . rawurlencode($name) . '&type=' . $type,
            'https://cloudflare-dns.com/dns-query?name=' . rawurlencode($name) . '&type=' . $type,
        ] as $url) {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 5,
                CURLOPT_HTTPHEADER     => ['accept: application/dns-json'],
            ]);
            $resp = curl_exec($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if ($resp === false || $code !== 200) {
                continue; // resolver unreachable → try next provider
            }
            $data = json_decode($resp, true);
            if (!is_array($data)) {
                continue;
            }
            // HTTP 200 + valid JSON = authoritative public answer (even if empty).
            $out = [];
            foreach ($data['Answer'] ?? [] as $ans) {
                $d = trim((string)($ans['data'] ?? ''));
                if ($d === '') continue;
                // TXT data is quoted and long values are split: "part1" "part2"
                $d = str_replace('" "', '', $d);
                $d = trim($d, '"');
                $out[] = $d;
            }
            return $out;
        }

        // DoH unavailable (e.g. egress blocked) → system resolver (may be wrong
        // for the server's own domain under split-horizon DNS).
        return $this->systemDns($name, $type);
    }

    /** @return string[] */
    private function systemDns(string $name, string $type): array
    {
        $records = @dns_get_record($name, $type === 'CNAME' ? DNS_CNAME : DNS_TXT);
        if (!is_array($records)) return [];
        $out = [];
        foreach ($records as $r) {
            if ($type === 'CNAME') {
                if (!empty($r['target'])) $out[] = $r['target'];
            } else {
                $out[] = $r['txt'] ?? ($r['entries'][0] ?? '');
            }
        }
        return $out;
    }
}
