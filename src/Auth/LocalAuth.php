<?php

namespace App\Auth;

use App\Core\Config;
use App\Core\Session;

class LocalAuth
{
    public static function attempt(string $username, string $password): bool
    {
        $creds = self::attemptCredentials($username, $password);
        if ($creds !== null) {
            self::setSession($creds['username'], $creds['role']);
            return true;
        }
        return false;
    }

    public static function attemptCredentials(string $username, string $password): ?array
    {
        $config = Config::getInstance();

        $adminUser = $config->get('admin_username');
        $adminHash = $config->get('admin_password');
        if ($adminUser && $adminHash && $username === $adminUser && password_verify($password, $adminHash)) {
            return ['username' => $username, 'role' => 'admin'];
        }

        $opUser = $config->get('operator_username');
        $opHash = $config->get('operator_password');
        if ($opUser && $opHash && $username === $opUser && password_verify($password, $opHash)) {
            return ['username' => $username, 'role' => 'operator'];
        }

        return null;
    }

    public static function setSessionDirect(array $credentials): void
    {
        self::setSession($credentials['username'], $credentials['role']);
    }

    private static function setSession(string $username, string $role): void
    {
        Session::regenerate();
        Session::set('authenticated', true);
        Session::set('auth_type', 'local');
        Session::set('username', $username);
        Session::set('role', $role);
        Session::set('login_time', time());
        // Seed last_activity so the idle-timeout window starts at login.
        Session::set('last_activity', time());
    }

    public static function role(): string
    {
        // Fail closed: if a session somehow has no role, treat it as the
        // least-privileged role (operator), never admin. All real auth paths
        // (setSession / MicrosoftAuth::loginUser) set the role explicitly.
        return Session::get('role', 'operator');
    }

    public static function isAdmin(): bool
    {
        return self::role() === 'admin';
    }

    public static function requireAdmin(): void
    {
        self::require();
        if (!self::isAdmin()) {
            http_response_code(403);
            die('<h2>' . t('403 — Nur für Administratoren') . '</h2>');
        }
    }

    public static function check(): bool
    {
        return Session::get('authenticated') === true;
    }

    public static function logout(): void
    {
        Session::destroy();
    }

    public static function require(): void
    {
        if (!self::check()) {
            header('Location: /login');
            exit;
        }

        // Check idle timeout (30 minutes)
        $timeout = 30 * 60; // 30 minutes in seconds
        $lastActivity = Session::get('last_activity');
        if ($lastActivity !== null && (time() - (int)$lastActivity) > $timeout) {
            Session::destroy();
            header('Location: /login?reason=timeout');
            exit;
        }
        Session::set('last_activity', time());
    }

    public static function username(): string
    {
        return Session::get('username', 'Admin');
    }
}
