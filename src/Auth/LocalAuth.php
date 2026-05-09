<?php

namespace App\Auth;

use App\Core\Config;
use App\Core\Session;

class LocalAuth
{
    public static function attempt(string $username, string $password): bool
    {
        $config = Config::getInstance();
        $storedUser = $config->get('admin_username');
        $storedHash = $config->get('admin_password');

        if (!$storedUser || !$storedHash) {
            return false;
        }
        if ($username !== $storedUser) {
            return false;
        }
        if (!password_verify($password, $storedHash)) {
            return false;
        }

        Session::regenerate();
        Session::set('authenticated', true);
        Session::set('username', $username);
        Session::set('login_time', time());
        return true;
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
