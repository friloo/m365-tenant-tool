<?php

namespace App\Modules\Auth;

use App\Auth\LocalAuth;
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
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if (LocalAuth::attempt($username, $password)) {
            Redirect::to('/');
        }

        Session::flash('error', 'Ungültige Zugangsdaten.');
        Redirect::to('/login');
    }

    public function logout(): void
    {
        LocalAuth::logout();
        Redirect::to('/login');
    }
}
