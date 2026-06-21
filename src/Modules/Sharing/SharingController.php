<?php

namespace App\Modules\Sharing;

use App\Auth\LocalAuth;
use App\Core\Redirect;
use App\Core\Session;
use App\Core\View;
use App\Helpers\CsvExporter;
use App\Modules\ShareReview\ShareReviewService;

class SharingController
{
    public function index(): void
    {
        LocalAuth::require();
        $service      = app_service(SharingService::class);
        $statusFilter = trim($_GET['status'] ?? '');
        $scopeFilter  = trim($_GET['scope'] ?? '');

        // Fetch all, then apply scope filter in PHP (DB already filtered by status)
        $summary = $service->getSharingSummary($statusFilter);

        if ($scopeFilter) {
            $summary['items'] = array_values(array_filter(
                $summary['items'],
                fn($i) => ($i['scope'] ?? '') === $scopeFilter
            ));
        }

        View::render('sharing/index', [
            'pageTitle'    => t('Freigaben'),
            'summary'      => $summary,
            'statusFilter' => $statusFilter,
            'scopeFilter'  => $scopeFilter,
            'flash'        => Session::getFlash('success'),
            'error'        => Session::getFlash('error'),
        ]);
    }

    public function revoke(): void
    {
        LocalAuth::require();
        $driveId      = trim($_POST['drive_id'] ?? '');
        $itemId       = trim($_POST['item_id'] ?? '');
        $permissionId = trim($_POST['permission_id'] ?? '');

        if (!$driveId || !$itemId || !$permissionId) {
            Session::flash('error', t('Ungültige Parameter.'));
            Redirect::to('/sharing');
        }

        try {
            app_service(SharingService::class)->revokePermission($driveId, $itemId, $permissionId);
            Session::flash('success', t('Freigabe wurde widerrufen.'));
        } catch (\Throwable $e) {
            Session::flash('error', t('Widerrufen fehlgeschlagen: ') . $e->getMessage());
        }
        Redirect::to('/sharing');
    }

    public function scan(): void
    {
        LocalAuth::require();
        set_time_limit(300);
        $log   = app_service(ShareReviewService::class)->scanAndSync();
        $errors = array_filter($log, fn($l) => str_starts_with($l, 'ERROR'));
        $found  = count(array_filter($log, fn($l) => str_starts_with($l, 'NEW')));

        if (!empty($errors)) {
            Session::flash('error', t('Scan-Fehler: ') . implode('; ', $errors));
        } else {
            Session::flash('success', t('Scan abgeschlossen — :found neue Freigaben gefunden.', ['found' => $found]));
        }
        Redirect::to('/sharing');
    }

    public function export(): void
    {
        LocalAuth::require();
        $data  = app_service(SharingService::class)->getSharingSummary();
        $items = $data['items'] ?? [];

        CsvExporter::download('freigaben_' . date('Ymd') . '.csv',
            [t('Typ'), t('Name'), t('Quelle'), t('Freigabe-Typ'), t('Besitzer'), t('Erstmals erkannt'), t('Status')],
            array_map(fn($i) => [
                $i['type']    ?? '',
                $i['name']    ?? '',
                $i['site']    ?? '',
                $i['scope']   ?? '',
                $i['owner']   ?? '',
                CsvExporter::formatDate($i['modified'] ?? ''),
                $i['status']  ?? '',
            ], $items)
        );
    }
}
