<?php

namespace App\Modules\Favorites;

use App\Auth\LocalAuth;
use App\Core\View;

/**
 * Favorites page. Favorites are stored client-side (localStorage) per browser —
 * no server state needed. The page is rendered by app.js from that store; this
 * controller only provides the shell + route.
 */
class FavoritesController
{
    public function index(): void
    {
        LocalAuth::require();
        View::render('favorites/index', [
            'pageTitle' => 'Favoriten',
        ]);
    }
}
