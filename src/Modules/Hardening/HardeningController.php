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
        $summary    = ['on' => 0, 'off' => 0, 'warn' => 0, 'info' => 0, 'unknown' => 0, 'total' => 0];
        foreach ($items as $item) {
            $byCategory[$item['category']][] = $item;
            $st = $item['status'] ?? 'unknown';
            if (!isset($summary[$st])) $st = 'unknown';
            $summary[$st]++;
            $summary['total']++;
        }
        // Hardening-Score: Anteil der bereits gehärteten ("on") Einstellungen,
        // die einen klaren Soll-Zustand haben (info/manuell zählt nicht mit).
        $scored = $summary['on'] + $summary['off'] + $summary['warn'];
        $summary['score'] = $scored > 0 ? (int)round($summary['on'] / $scored * 100) : 0;

        View::render('hardening/index', [
            'pageTitle'  => 'Security Center',
            'byCategory' => $byCategory,
            'summary'    => $summary,
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
