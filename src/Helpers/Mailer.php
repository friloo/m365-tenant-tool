<?php

namespace App\Helpers;

use App\Core\Config;

class Mailer
{
    public static function send(string $to, string $subject, string $htmlBody): bool
    {
        $config = Config::getInstance();
        $from   = $config->get('alert_email_from', 'noreply@m365tool.local');
        $appName = $config->get('app_name', 'M365 Tenant Tool');

        $headers = implode("\r\n", [
            'MIME-Version: 1.0',
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . $appName . ' <' . $from . '>',
            'X-Mailer: M365TenantTool',
        ]);

        $smtpHost = $config->get('smtp_host');
        if ($smtpHost) {
            return self::sendSmtp($to, $subject, $htmlBody, $config);
        }

        return mail($to, $subject, $htmlBody, $headers);
    }

    private static function sendSmtp(string $to, string $subject, string $htmlBody, Config $config): bool
    {
        $host = $config->get('smtp_host');
        $port = (int)$config->get('smtp_port', 587);
        $user = $config->get('smtp_user');
        $pass = $config->get('smtp_password');
        $from = $config->get('alert_email_from');

        $socket = fsockopen(($port === 465 ? 'ssl://' : '') . $host, $port, $errno, $errstr, 10);
        if (!$socket) return false;

        $read = fn() => fgets($socket, 512);
        $write = fn($cmd) => fputs($socket, $cmd . "\r\n");

        $read(); // greeting
        $write('EHLO m365tool');
        while (($line = $read()) && $line[3] !== ' ') {}

        if ($port === 587) {
            $write('STARTTLS');
            $read();
            stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
            $write('EHLO m365tool');
            while (($line = $read()) && $line[3] !== ' ') {}
        }

        if ($user) {
            $write('AUTH LOGIN');
            $read();
            $write(base64_encode($user));
            $read();
            $write(base64_encode($pass));
            $read();
        }

        $write("MAIL FROM:<{$from}>");  $read();
        $write("RCPT TO:<{$to}>");     $read();
        $write('DATA');                 $read();

        $msg = implode("\r\n", [
            "From: {$from}",
            "To: {$to}",
            "Subject: {$subject}",
            "MIME-Version: 1.0",
            "Content-Type: text/html; charset=UTF-8",
            "",
            $htmlBody,
            ".",
        ]);
        $write($msg);
        $read();
        $write('QUIT');
        fclose($socket);
        return true;
    }

    public static function alertTemplate(string $title, string $body, string $appName): string
    {
        return <<<HTML
<!DOCTYPE html><html><body style="font-family:Segoe UI,sans-serif;background:#f3f4f6;padding:24px;">
<div style="max-width:600px;margin:0 auto;background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,.08);">
  <div style="background:#0078d4;padding:20px 24px;">
    <h2 style="color:#fff;margin:0;font-size:16px;">{$appName}</h2>
    <h3 style="color:#cce4f7;margin:4px 0 0;font-size:20px;">{$title}</h3>
  </div>
  <div style="padding:24px;">{$body}</div>
  <div style="padding:12px 24px;background:#f9fafb;font-size:11px;color:#9ca3af;">
    Automatische Benachrichtigung · M365 Tenant Tool
  </div>
</div>
</body></html>
HTML;
    }
}
