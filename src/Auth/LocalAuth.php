<?php

namespace App\Auth;

use App\Core\Config;
use App\Core\Session;

class LocalAuth
{
    public static function attempt(string $username, string $password): bool
    {
        $config = Config::getInstance();

        // Check admin
        $adminUser = $config->get('admin_username');
        $adminHash = $config->get('admin_password');
        if ($adminUser && $adminHash && $username === $adminUser && password_verify($password, $adminHash)) {
            self::setSession($username, 'admin');
            return true;
        }

        // Check operator
        $opUser = $config->get('operator_username');
        $opHash = $config->get('operator_password');
        if ($opUser && $opHash && $username === $opUser && password_verify($password, $opHash)) {
            self::setSession($username, 'operator');
            return true;
        }

        return false;
    }

    private static function setSession(string $username, string $role): void
    {
        Session::regenerate();
        Session::set('authenticated', true);
        Session::set('auth_type', 'local');
        Session::set('username', $username);
        Session::set('role', $role);
        Session::set('login_time', time());
    }

    public static function role(): string
    {
        return Session::get('role', 'admin');
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
            die('<h2>403 — Nur für Administratoren</h2>');
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
    }

    public static function username(): string
    {
        return Session::get('username', 'Admin');
    }
}
