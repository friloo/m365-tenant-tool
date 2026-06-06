<?php

namespace App\Helpers;

use App\Core\Config;

class Mailer
{
    public static function send(string $to, string $subject, string $htmlBody): bool
    {
        $config = Config::getInstance();
        $from   = self::sanitizeHeader($config->get('alert_email_from', 'noreply@m365tool.local'));
        $appName = self::sanitizeHeader($config->get('app_name', 'M365 Tenant Tool'));

        // $to and $subject originate from Graph/SharePoint data (owner emails, file
        // names) — strip CR/LF so they cannot inject extra SMTP commands or headers.
        $to      = self::sanitizeHeader($to);
        $subject = self::sanitizeHeader($subject);
        if ($to === '' || $from === '') return false;

        $headers = implode("\r\n", [
            'MIME-Version: 1.0',
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . $appName . ' <' . $from . '>',
            'X-Mailer: M365TenantTool',
        ]);

        $smtpHost = $config->get('smtp_host');
        if ($smtpHost) {
            return self::sendSmtp($to, $subject, $htmlBody, $from, $config);
        }

        return mail($to, $subject, $htmlBody, $headers);
    }

    /** Remove CR/LF/NUL to prevent SMTP command / mail-header injection. */
    private static function sanitizeHeader(string $value): string
    {
        return trim(str_replace(["\r", "\n", "\0"], '', $value));
    }

    private static function sendSmtp(string $to, string $subject, string $htmlBody, string $from, Config $config): bool
    {
        $host = $config->get('smtp_host');
        $port = (int)$config->get('smtp_port', 587);
        $user = $config->get('smtp_user');
        $pass = (string)$config->get('smtp_password');

        $socket = fsockopen(($port === 465 ? 'ssl://' : '') . $host, $port, $errno, $errstr, 10);
        if (!$socket) return false;
        stream_set_timeout($socket, 10);

        // Read a (possibly multi-line) SMTP reply and return its 3-digit status code.
        // Continuation lines have '-' as the 4th char; a space marks the final line.
        $readReply = function () use ($socket): int {
            $code = 0;
            while (($line = fgets($socket, 515)) !== false) {
                $code = (int)substr($line, 0, 3);
                if (strlen($line) < 4 || $line[3] !== '-') break;
            }
            return $code;
        };
        $write = fn($cmd) => fputs($socket, $cmd . "\r\n");
        // Verify a step's reply code; abort the whole exchange on mismatch.
        $expect = function (int $code, array $ok) use ($socket): bool {
            if (in_array($code, $ok, true)) return true;
            @fclose($socket);
            return false;
        };

        if (!$expect($readReply(), [220])) return false;          // greeting
        $write('EHLO m365tool');
        if (!$expect($readReply(), [250])) return false;

        if ($port === 587) {
            $write('STARTTLS');
            if (!$expect($readReply(), [220])) return false;
            if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                fclose($socket);
                return false;
            }
            $write('EHLO m365tool');
            if (!$expect($readReply(), [250])) return false;
        }

        if ($user) {
            $write('AUTH LOGIN');
            if (!$expect($readReply(), [334])) return false;
            $write(base64_encode($user));
            if (!$expect($readReply(), [334])) return false;
            $write(base64_encode($pass));
            if (!$expect($readReply(), [235])) return false;       // 235 = auth accepted
        }

        $write("MAIL FROM:<{$from}>");
        if (!$expect($readReply(), [250])) return false;
        $write("RCPT TO:<{$to}>");
        if (!$expect($readReply(), [250, 251])) return false;      // 550 etc. → fail (no false "sent")
        $write('DATA');
        if (!$expect($readReply(), [354])) return false;

        // Dot-stuff: a body line consisting solely of "." would otherwise end DATA early.
        $body = preg_replace('/^\./m', '..', $htmlBody);
        $msg = implode("\r\n", [
            "From: {$from}",
            "To: {$to}",
            "Subject: {$subject}",
            "MIME-Version: 1.0",
            "Content-Type: text/html; charset=UTF-8",
            "",
            $body,
            ".",
        ]);
        $write($msg);
        $delivered = $expect($readReply(), [250]);                 // 250 = message accepted
        if (!$delivered) return false;

        $write('QUIT');
        fclose($socket);
        return true;
    }

    public static function alertTemplate(string $title, string $body, string $appName): string
    {
        $config      = Config::getInstance();
        $brandColor  = $config->get('brand_primary_color', '#0078d4');
        $logoText    = $config->get('brand_logo_text', '') ?: mb_strtoupper(substr($appName, 0, 1));
        $logoUrl     = $config->get('brand_logo_url', '');
        $footer      = $config->get('brand_review_footer', '') ?: 'Automatische Benachrichtigung · ' . $appName;

        $logoHtml = $logoUrl
            ? "<img src=\"{$logoUrl}\" style=\"width:28px;height:28px;object-fit:contain;border-radius:4px;vertical-align:middle;\" alt=\"Logo\">"
            : "<span style=\"display:inline-block;width:28px;height:28px;line-height:28px;text-align:center;background:rgba(255,255,255,.2);border-radius:4px;font-weight:700;font-size:14px;color:#fff;\">{$logoText}</span>";

        return <<<HTML
<!DOCTYPE html><html><body style="font-family:Segoe UI,Arial,sans-serif;background:#f3f4f6;padding:24px;margin:0;">
<div style="max-width:600px;margin:0 auto;background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,.08);">
  <div style="background:{$brandColor};padding:18px 24px;display:flex;align-items:center;gap:12px;">
    {$logoHtml}
    <div>
      <div style="color:rgba(255,255,255,.8);font-size:12px;">{$appName}</div>
      <div style="color:#fff;font-size:18px;font-weight:700;margin-top:2px;">{$title}</div>
    </div>
  </div>
  <div style="padding:24px 28px;">{$body}</div>
  <div style="padding:12px 24px;background:#f9fafb;font-size:11px;color:#9ca3af;border-top:1px solid #f0f0f0;">
    {$footer}
  </div>
</div>
</body></html>
HTML;
    }
}
