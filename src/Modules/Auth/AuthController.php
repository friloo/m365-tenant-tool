<?php

namespace App\Modules\Auth;

use App\Auth\LocalAuth;
use App\Core\AppAudit;
use App\Core\Redirect;
use App\Core\Session;
use App\Core\View;

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
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $window = 15; // minutes
        $maxAttempts = 5;

        // Check if IP is locked out
        $attempts = \App\Database\DB::getInstance()->fetchOne(
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

        if (LocalAuth::attempt($username, $password)) {
            // Clear attempts after successful login
            \App\Database\DB::getInstance()->execute(
                "DELETE FROM login_attempts WHERE ip_address = ?",
                [$ip]
            );
            AppAudit::log('login_success', 'auth', "User: {$username}");
            Redirect::to('/');
        }

        // Record failed attempt
        \App\Database\DB::getInstance()->execute(
            "INSERT INTO login_attempts (ip_address) VALUES (?)",
            [$ip]
        );
        AppAudit::log('login_failed', 'auth', "IP: {$ip}");

        Session::flash('error', 'Ungültige Zugangsdaten.');
        Redirect::to('/login');
    }

    public function logout(): void
    {
        AppAudit::log('logout', 'auth');
        LocalAuth::logout();
        Redirect::to('/login');
    }
}
