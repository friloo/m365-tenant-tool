<?php

namespace App\Modules\Hardening;

use App\Auth\LocalAuth;
use App\Core\Redirect;
use App\Core\Session;
use App\Core\View;

class HardeningController
{
    public function index(): void
    {
        LocalAuth::require();
        $service = app_service(HardeningService::class);
        $items   = $service->getItems();

        // Gruppieren nach Kategorie
        $byCategory = [];
        foreach ($items as $item) {
            $byCategory[$item['category']][] = $item;
        }

        View::render('hardening/index', [
            'pageTitle'  => 'Tenant-Härtung',
            'byCategory' => $byCategory,
            'flash'      => Session::getFlash('success'),
            'error'      => Session::getFlash('error'),
        ]);
    }

    public function apply(): void
    {
        LocalAuth::requireAdmin();
        $id = trim($_POST['action_id'] ?? '');
        if ($id === '') {
            Session::flash('error', 'Keine Aktion angegeben.');
            Redirect::to('/hardening');
        }
        $result = app_service(HardeningService::class)->apply($id);
        if ($result['ok']) {
            Session::flash('success', $result['msg']);
            \App\Core\AppAudit::log('hardening_apply', 'hardening', "Aktion: {$id}");
        } else {
            Session::flash('error', $result['msg']);
        }
        Redirect::to('/hardening');
    }
}
