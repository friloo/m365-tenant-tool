<?php

namespace App\Modules\ShareReview;

use App\Auth\LocalAuth;
use App\Core\Redirect;
use App\Core\Session;
use App\Core\View;

class ShareReviewController
{
    // ── Public (no auth) — token-based review ────────────────

    public function demoReview(): void
    {
        LocalAuth::require();

        $config = \App\Core\Config::getInstance();

        View::render('sharereview/review', [
            'token'  => 'demo',
            'isDemo' => true,
            'share'  => [
                'item_name'           => 'Projektplanung Q3 2025.xlsx',
                'item_url'            => '#',
                'site_name'           => 'Marketing-Team',
                'share_scope'         => 'anonymous',
                'first_detected'      => date('Y-m-d', strtotime('-22 days')),
                'auto_revoke_at'      => date('Y-m-d', strtotime('+7 days')),
                'review_interval_days'=> (int)$config->get('share_review_interval_days', 30),
            ],
        ], false);
    }

    public function review(string $token): void
    {
        $service = app_service(ShareReviewService::class);
        $data    = $service->resolveToken($token);

        if (!$data) {
            View::render('sharereview/expired', ['reason' => 'not_found'], false);
            return;
        }
        if (isset($data['error'])) {
            View::render('sharereview/expired', ['reason' => $data['error']], false);
            return;
        }

        View::render('sharereview/review', [
            'token' => htmlspecialchars($token, ENT_QUOTES),
            'share' => $data,
        ], false);
    }

    public function submitReview(string $token): void
    {
        $reason = trim($_POST['reason'] ?? '');

        if (strlen($reason) < 5) {
            $service = app_service(ShareReviewService::class);
            $data    = $service->resolveToken($token);
            View::render('sharereview/review', [
                'token' => htmlspecialchars($token, ENT_QUOTES),
                'share' => $data ?? [],
                'error' => t('Bitte geben Sie eine Begründung ein (mindestens 5 Zeichen).'),
            ], false);
            return;
        }

        $service = app_service(ShareReviewService::class);
        $ok      = $service->confirmReview($token, $reason);

        if ($ok) {
            View::render('sharereview/confirmed', [], false);
        } else {
            View::render('sharereview/expired', ['reason' => 'used'], false);
        }
    }

    // ── Admin — monitoring dashboard ─────────────────────────

    public function admin(): void
    {
        LocalAuth::require();
        $service      = app_service(ShareReviewService::class);
        $statusFilter = $_GET['status'] ?? '';
        $shares       = $service->getAllTracked($statusFilter);
        $stats        = $service->getStats();

        View::render('sharereview/admin', [
            'pageTitle'    => t('Freigaben-Monitor'),
            'shares'       => $shares,
            'stats'        => $stats,
            'statusFilter' => $statusFilter,
            'flash'        => Session::getFlash('success'),
            'error'        => Session::getFlash('error'),
        ]);
    }

    public function revoke(string $id): void
    {
        LocalAuth::require();
        try {
            app_service(ShareReviewService::class)->manualRevoke((int)$id);
            Session::flash('success', t('Freigabe wurde widerrufen.'));
        } catch (\Throwable $e) {
            Session::flash('error', t('Fehler: ') . $e->getMessage());
        }
        Redirect::to('/sharing/monitor');
    }

    public function remind(string $id): void
    {
        LocalAuth::require();
        $ok = app_service(ShareReviewService::class)->sendManualReminder((int)$id);
        if ($ok) {
            Session::flash('success', t('Erinnerung wurde gesendet.'));
        } else {
            Session::flash('error', t('E-Mail konnte nicht gesendet werden. SMTP konfiguriert?'));
        }
        Redirect::to('/sharing/monitor');
    }

    public function scan(): void
    {
        LocalAuth::requireAdmin();
        $log = app_service(ShareReviewService::class)->scanAndSync();
        Session::flash('success', t(':count Freigaben gescannt/aktualisiert.', ['count' => count($log)]));
        Redirect::to('/sharing/monitor');
    }
}
