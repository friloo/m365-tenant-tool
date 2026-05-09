<?php

namespace App\Core;

class Redirect
{
    public static function to(string $url): never
    {
        header('Location: ' . $url);
        exit;
    }

    public static function back(): never
    {
        $ref = $_SERVER['HTTP_REFERER'] ?? '/';
        self::to($ref);
    }

    public static function withFlash(string $url, string $key, string $message): never
    {
        Session::start();
        Session::flash($key, $message);
        self::to($url);
    }
}
