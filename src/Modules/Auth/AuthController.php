<?php

namespace App\Modules\Auth;

use App\Auth\LocalAuth;
use App\Auth\TotpService;
use App\Core\AppAudit;
use App\Core\Config;
use App\Core\Redirect;
use App\Core\Session;
use App\Core\View;
use App\Database\DB;

class AuthController
{
    public function login(): void
    {
        if (LocalAuth::check()) {
            Redirect::to('/');
        }
        $error = Session::getFlash('error');
        View::render('auth/login', [
            'error'          => $error,
            'msLoginEnabled' => \App\Auth\MicrosoftAuth::isConfigured(),
        ], false);
    }

    public function doLogin(): void
    {
        $ip          = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $window      = 15;
        $maxAttempts = 5;

        $attempts = DB::fetchOne(
            "SELECT COUNT(*) AS c FROM login_attempts
             WHERE ip_address = ? AND attempted_at > DATE_SUB(NOW(), INTERVAL ? MINUTE)",
            [$ip, $window]
        )['c'] ?? 0;

        if ((int)$attempts >= $maxAttempts) {
            View::render('auth/login', [
                'pageTitle'      => 'Anmelden',
                'error'          => "Zu viele fehlgeschlagene Anmeldeversuche. Bitte warte {$window} Minuten.",
                'msLoginEnabled' => \App\Auth\MicrosoftAuth::isConfigured(),
            ], false);
            return;
        }

        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        $creds = LocalAuth::attemptCredentials($username, $password);

        if ($creds !== null) {
            // Check if TOTP 2FA is required (admin only when secret is configured)
            $totpSecret = Config::getInstance()->get('admin_totp_secret');
            if ($creds['role'] === 'admin' && $totpSecret) {
                // Do NOT clear the attempt counter yet — login is only half done.
                // Clearing here would let an attacker with the correct password
                // reset the rate limit and brute-force 2FA codes.
                Session::set('_2fa_pending', true);
                Session::set('_2fa_credentials', $creds);
                Session::set('_2fa_ip', $ip);
                Redirect::to('/login/2fa');
                return;
            }

            DB::execute("DELETE FROM login_attempts WHERE ip_address = ?", [$ip]);
            LocalAuth::setSessionDirect($creds);
            AppAudit::log('login_success', 'auth', "User: {$username}");
            Redirect::to('/');
            return;
        }

        DB::execute("INSERT INTO login_attempts (ip_address) VALUES (?)", [$ip]);
        AppAudit::log('login_failed', 'auth', "IP: {$ip}");

        Session::flash('error', 'Ungültige Zugangsdaten.');
        Redirect::to('/login');
    }

    public function twofa(): void
    {
        if (!Session::get('_2fa_pending')) {
            Redirect::to('/login');
            return;
        }
        $error = Session::getFlash('error');
        View::render('auth/2fa', ['error' => $error], false);
    }

    public function doTwofa(): void
    {
        if (!Session::get('_2fa_pending')) {
            Redirect::to('/login');
            return;
        }

        // Rate-limit the 2FA step too (same IP window as the password step),
        // otherwise codes could be brute-forced unbounded.
        $ip = Session::get('_2fa_ip', $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
        $attempts = DB::fetchOne(
            "SELECT COUNT(*) AS c FROM login_attempts
             WHERE ip_address = ? AND attempted_at > DATE_SUB(NOW(), INTERVAL 15 MINUTE)",
            [$ip]
        )['c'] ?? 0;
        if ((int)$attempts >= 5) {
            Session::flash('error', 'Zu viele fehlgeschlagene Versuche. Bitte warte 15 Minuten.');
            Redirect::to('/login/2fa');
            return;
        }

        $creds      = Session::get('_2fa_credentials', []);
        $code       = trim($_POST['code'] ?? '');
        $config     = Config::getInstance();
        $totpSecret = $config->get('admin_totp_secret');

        // Verify TOTP code
        if (strlen(preg_replace('/\s/', '', $code)) === 6 && $totpSecret && TotpService::verify($totpSecret, $code)) {
            $this->completeTwofaLogin($creds, 'login_2fa_success');
            return;
        }

        // Verify recovery code
        $recoveryJson = $config->get('admin_totp_recovery_codes');
        if ($recoveryJson) {
            $hashes = json_decode($recoveryJson, true) ?? [];
            $idx    = TotpService::verifyRecoveryCode($code, $hashes);
            if ($idx !== false) {
                array_splice($hashes, $idx, 1);
                $config->set('admin_totp_recovery_codes', json_encode($hashes), true);
                $remaining = count($hashes);
                $this->completeTwofaLogin($creds, 'login_recovery_code');
                if ($remaining <= 2) {
                    Session::flash('success', "Wiederherstellungscode verwendet. Noch {$remaining} Code(s) übrig — bitte neue Codes generieren.");
                }
                return;
            }
        }

        // Failed attempt
        $ip = Session::get('_2fa_ip', $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
        DB::execute("INSERT INTO login_attempts (ip_address) VALUES (?)", [$ip]);
        AppAudit::log('login_2fa_failed', 'auth', "User: " . ($creds['username'] ?? '?'));

        Session::flash('error', 'Ungültiger Code. Bitte erneut versuchen.');
        Redirect::to('/login/2fa');
    }

    private function completeTwofaLogin(array $creds, string $auditAction): void
    {
        // Login fully completed → now it's safe to clear the attempt counter.
        $ip = Session::get('_2fa_ip', $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
        DB::execute("DELETE FROM login_attempts WHERE ip_address = ?", [$ip]);
        Session::remove('_2fa_pending');
        Session::remove('_2fa_credentials');
        Session::remove('_2fa_ip');
        LocalAuth::setSessionDirect($creds);
        AppAudit::log($auditAction, 'auth', "User: " . ($creds['username'] ?? '?'));
        Redirect::to('/');
    }

    public function logout(): void
    {
        AppAudit::log('logout', 'auth');
        LocalAuth::logout();
        Redirect::to('/login');
    }
}
