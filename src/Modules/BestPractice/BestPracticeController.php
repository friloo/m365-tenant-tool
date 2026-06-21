<?php

namespace App\Modules\BestPractice;

use App\Auth\LocalAuth;
use App\Core\AppAudit;
use App\Core\Redirect;
use App\Core\Session;
use App\Core\View;

class BestPracticeController
{
    public function index(): void
    {
        LocalAuth::require();
        View::render('bestpractice/index', [
            'pageTitle' => t('Tenant-Härtungs-Leitfaden'),
            'guide'     => BestPracticeService::guide(),
            'progress'  => BestPracticeService::progress(),
            'summary'   => BestPracticeService::summary(),
        ]);
    }

    public function markStep(): void
    {
        LocalAuth::require();
        $id    = trim((string)($_POST['step_id'] ?? ''));
        $state = trim((string)($_POST['state']   ?? 'done'));
        if ($id === '') {
            Session::flash('error', t('Kein Schritt angegeben.'));
            Redirect::to('/bestpractice');
        }
        BestPracticeService::markStep($id, $state);
        AppAudit::log('bestpractice_mark', 'bestpractice', t('Schritt :id → :state', ['id' => $id, 'state' => $state]));
        // Same-page navigation: stay on the guide and keep anchor scroll
        $anchor = $_POST['anchor'] ?? '';
        $url = '/bestpractice' . ($anchor !== '' ? '#' . $anchor : '');
        Redirect::to($url);
    }

    public function reset(): void
    {
        LocalAuth::requireAdmin();
        BestPracticeService::reset();
        AppAudit::log('bestpractice_reset', 'bestpractice', t('Fortschritt zurückgesetzt'));
        Session::flash('success', t('Fortschritt zurückgesetzt.'));
        Redirect::to('/bestpractice');
    }
}
