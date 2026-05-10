<?php

namespace App\Modules\Auth;

use App\Auth\MicrosoftAuth;
use App\Core\Redirect;
use App\Core\Session;
use App\Core\View;

class MicrosoftAuthController
{
    public function redirect(): void
    {
        if (!MicrosoftAuth::isConfigured()) {
            Session::flash('error', 'Microsoft-Anmeldung ist nicht konfiguriert.');
            Redirect::to('/login');
        }

        header('Location: ' . MicrosoftAuth::getAuthUrl());
        exit;
    }

    public function callback(): void
    {
        $error = $_GET['error'] ?? '';
        if ($error) {
            $desc = htmlspecialchars($_GET['error_description'] ?? $error);
            Session::flash('error', 'Microsoft-Anmeldung fehlgeschlagen: ' . $desc);
            Redirect::to('/login');
        }

        $code  = $_GET['code']  ?? '';
        $state = $_GET['state'] ?? '';

        if (!$code) {
            Session::flash('error', 'Kein Authorisierungscode empfangen.');
            Redirect::to('/login');
        }

        $me = MicrosoftAuth::handleCallback($code, $state);

        if (!$me) {
            Session::flash('error', 'Microsoft-Authentifizierung fehlgeschlagen. Bitte erneut versuchen.');
            Redirect::to('/login');
        }

        $dbUser = MicrosoftAuth::findDbUser($me['object_id'], $me['upn']);

        if (!$dbUser) {
            View::render('auth/no_access', [
                'upn' => $me['upn'],
            ], false);
            return;
        }

        MicrosoftAuth::loginUser($dbUser, $me['display_name'], $me['upn']);
        Redirect::to('/');
    }
}
