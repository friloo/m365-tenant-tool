<?php

namespace App\Helpers;

use App\Core\Config;

/**
 * Fan-out for security alerts to an external webhook — a Microsoft Teams
 * incoming webhook (MessageCard) or a generic JSON endpoint (SIEM/Sentinel,
 * Slack-compatible, custom). Best-effort and fully isolated: a webhook failure
 * must never break the originating action, so everything is wrapped and
 * time-boxed.
 *
 * Config keys (all optional):
 *   alert_webhook_url           target URL ('' = disabled)
 *   alert_webhook_type          'teams' | 'generic'  (default 'teams')
 *   alert_webhook_min_severity  'warn' | 'critical'  (default 'warn')
 */
class WebhookNotifier
{
    private const SEVERITY_RANK = ['info' => 0, 'success' => 0, 'warn' => 1, 'critical' => 2];

    /** True if a webhook URL is configured. */
    public static function isConfigured(): bool
    {
        return trim((string)Config::getInstance()->get('alert_webhook_url', '')) !== '';
    }

    /**
     * Dispatch an alert if a webhook is configured and the severity meets the
     * configured minimum. Returns true on a 2xx response.
     */
    public static function dispatch(string $title, string $body, string $severity, string $category = 'system', ?string $link = null): bool
    {
        $config = Config::getInstance();
        $url    = trim((string)$config->get('alert_webhook_url', ''));
        if ($url === '' || !preg_match('#^https://#i', $url)) {
            return false;
        }

        $minSev = (string)$config->get('alert_webhook_min_severity', 'warn');
        if ((self::SEVERITY_RANK[$severity] ?? 0) < (self::SEVERITY_RANK[$minSev] ?? 1)) {
            return false;
        }

        $type    = (string)$config->get('alert_webhook_type', 'teams');
        $appName = (string)$config->get('app_name', 'M365 Tenant Tool');
        $baseUrl = rtrim((string)$config->get('app_base_url', ''), '/');
        $fullLink = ($link && $baseUrl) ? $baseUrl . $link : null;

        $payload = $type === 'generic'
            ? self::genericPayload($appName, $title, $body, $severity, $category, $fullLink)
            : self::teamsPayload($appName, $title, $body, $severity, $fullLink);

        return self::post($url, $payload);
    }

    /** Generic JSON envelope for SIEM / custom consumers. */
    private static function genericPayload(string $app, string $title, string $body, string $severity, string $category, ?string $link): array
    {
        return [
            'source'    => $app,
            'title'     => $title,
            'body'      => $body,
            'severity'  => $severity,
            'category'  => $category,
            'link'      => $link,
            'timestamp' => date('c'),
        ];
    }

    /** Microsoft Teams "MessageCard" (Office 365 connector / Workflows compatible). */
    private static function teamsPayload(string $app, string $title, string $body, string $severity, ?string $link): array
    {
        $color = match ($severity) {
            'critical' => 'D13438',
            'warn'     => 'F2A900',
            'success'  => '107C10',
            default    => '0078D4',
        };
        $card = [
            '@type'      => 'MessageCard',
            '@context'   => 'https://schema.org/extensions',
            'summary'    => $title,
            'themeColor' => $color,
            'title'      => $app . ' — ' . $title,
            'text'       => $body !== '' ? $body : $title,
        ];
        if ($link) {
            $card['potentialAction'] = [[
                '@type'   => 'OpenUri',
                'name'    => t('Im Tool öffnen'),
                'targets' => [['os' => 'default', 'uri' => $link]],
            ]];
        }
        return $card;
    }

    private static function post(string $url, array $payload): bool
    {
        try {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 5,
                CURLOPT_CONNECTTIMEOUT => 3,
            ]);
            curl_exec($ch);
            $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
            return $code >= 200 && $code < 300;
        } catch (\Throwable) {
            return false;
        }
    }
}
