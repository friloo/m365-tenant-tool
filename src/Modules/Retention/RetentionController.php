<?php

namespace App\Modules\Retention;

use App\Auth\LocalAuth;
use App\Core\View;

/**
 * Honest placeholder for retention policies/labels. Like DLP, retention is NOT
 * manageable via Microsoft Graph (no v1.0/beta list/write endpoint) — it lives
 * in Microsoft Purview. eDiscovery cases (which DO have a Graph API) are in
 * their own module at /ediscovery.
 */
class RetentionController
{
    public function index(): void
    {
        LocalAuth::require();
        View::render('retention/index', [
            'pageTitle' => 'Aufbewahrung (Retention)',
        ]);
    }
}
