<?php

namespace App\Modules\Overview;

use App\Auth\LocalAuth;
use App\Core\Navigation;
use App\Core\View;

class OverviewController
{
    public function index(): void
    {
        LocalAuth::require();

        $groups = Navigation::groups(LocalAuth::isAdmin());
        $total  = 0;
        foreach ($groups as $g) {
            $total += count($g['items']);
        }

        View::render('overview/index', [
            'pageTitle' => t('Modul-Übersicht'),
            'groups'    => $groups,
            'total'     => $total,
        ]);
    }
}
