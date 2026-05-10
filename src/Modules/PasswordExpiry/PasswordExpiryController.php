<?php

namespace App\Modules\PasswordExpiry;

use App\Auth\LocalAuth;
use App\Core\Config;
use App\Core\View;

class PasswordExpiryController
{
    public function index(): void
    {
        LocalAuth::require();

        $expiryDays = (int)(Config::getInstance()->get('password_expiry_days', 90) ?: 90);

        $service  = app_service(PasswordExpiryService::class);
        $users    = $service->getAll();
        $analyzed = $service->analyzeUsers($users, $expiryDays);

        $totalChecked = count($analyzed['expired'])
            + count($analyzed['critical'])
            + count($analyzed['warning'])
            + count($analyzed['ok']);

        View::render('passwordexpiry/index', [
            'pageTitle'    => 'Passwort-Ablauf',
            'analyzed'     => $analyzed,
            'totalChecked' => $totalChecked,
            'expiryDays'   => $expiryDays,
        ]);
    }
}
