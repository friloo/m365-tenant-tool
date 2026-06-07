<?php

namespace App\Helpers;

/**
 * Resolve DNS over the PUBLIC internet via DNS-over-HTTPS (Google → Cloudflare),
 * falling back to the local system resolver only when DoH is unreachable.
 *
 * This matters whenever the server sits inside its own organisation's domain
 * with split-horizon DNS: the local/AD resolver then returns an internal zone
 * that lacks the public mail-auth records (SPF/DKIM/DMARC/MX/autodiscover),
 * which made the tenant's own domain look unconfigured. Mail-routing checks
 * must reflect the PUBLIC view, which is exactly what DoH returns.
 */
class PublicDns
{
    /**
     * @param string $type TXT | CNAME | MX | A | SRV
     * @return string[] raw record "data" values:
     *   - TXT   → unquoted, multi-part joined
     *   - CNAME → target FQDN (trailing dot stripped)
     *   - A     → IP address
     *   - MX    → "<pref> <target>"
     *   - SRV   → "<pri> <weight> <port> <target>"
     */
    public static function lookup(string $name, string $type): array
    {
        $type = strtoupper($type);

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
            // HTTP 200 + valid JSON = authoritative public answer (empty = none).
            $out = [];
            foreach ($data['Answer'] ?? [] as $ans) {
                $d = trim((string)($ans['data'] ?? ''));
                if ($d === '') continue;
                if ($type === 'TXT') {
                    $d = str_replace('" "', '', $d);
                    $d = trim($d, '"');
                } else {
                    $d = rtrim($d, '.'); // strip trailing dot from FQDNs
                }
                $out[] = $d;
            }
            return $out;
        }

        return self::systemDns($name, $type);
    }

    /** Last-resort local resolver (may be wrong under split-horizon DNS). @return string[] */
    private static function systemDns(string $name, string $type): array
    {
        $flag = match ($type) {
            'CNAME' => DNS_CNAME,
            'MX'    => DNS_MX,
            'A'     => DNS_A,
            'SRV'   => DNS_SRV,
            default => DNS_TXT,
        };
        $records = @dns_get_record($name, $flag);
        if (!is_array($records)) return [];

        $out = [];
        foreach ($records as $r) {
            switch ($type) {
                case 'TXT':   $out[] = $r['txt'] ?? ($r['entries'][0] ?? ''); break;
                case 'CNAME': if (!empty($r['target'])) $out[] = rtrim($r['target'], '.'); break;
                case 'A':     if (!empty($r['ip'])) $out[] = $r['ip']; break;
                case 'MX':    $out[] = ($r['pri'] ?? 0) . ' ' . rtrim($r['target'] ?? '', '.'); break;
                case 'SRV':   $out[] = ($r['pri'] ?? 0) . ' ' . ($r['weight'] ?? 0) . ' ' . ($r['port'] ?? 0) . ' ' . rtrim($r['target'] ?? '', '.'); break;
            }
        }
        return $out;
    }
}
