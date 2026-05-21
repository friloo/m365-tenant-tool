<?php

namespace App\Modules\BreakGlass;

use App\Auth\LocalAuth;
use App\Core\Config;
use App\Core\Redirect;
use App\Core\Session;
use App\Core\View;

class BreakGlassController
{
    public function index(): void
    {
        LocalAuth::require();
        $service = app_service(BreakGlassService::class);

        $upns   = $service->getConfiguredUpns();
        $status = empty($upns) ? [] : $service->getStatus();

        View::render('breakglass/index', [
            'pageTitle' => 'Break-Glass-Accounts',
            'upns'      => $upns,
            'status'    => $status,
            'flash'     => Session::getFlash('success'),
            'error'     => Session::getFlash('error'),
        ]);
    }

    public function save(): void
    {
        LocalAuth::requireAdmin();
        $raw = trim($_POST['break_glass_upns'] ?? '');
        // Normalize: split on , or newline, lowercase, dedupe, filter empty
        $parts = preg_split('/[\s,]+/', $raw, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $clean = array_values(array_unique(array_map('strtolower', array_map('trim', $parts))));
        Config::getInstance()->set('break_glass_upns', implode(',', $clean));
        Session::flash('success', count($clean) . ' Break-Glass-Account(s) gespeichert.');
        Redirect::to('/breakglass');
    }
}
