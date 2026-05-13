<?php

namespace App\Modules\Licenses;

use App\Auth\LocalAuth;
use App\Core\Session;
use App\Core\View;
use App\Helpers\CsvExporter;

class LicensesController
{
    public function index(): void
    {
        LocalAuth::require();
        $service = app_service(LicensesService::class);

        $skus = []; $loadErr = null;
        try { $skus = $service->getSkus(); }
        catch (\Throwable $e) { $loadErr = 'Lizenzen nicht ladbar: ' . $e->getMessage(); error_log('Licenses index: ' . $e->getMessage()); }

        View::render('licenses/index', [
            'pageTitle' => 'Lizenzen',
            'skus'      => $skus,
            'error'     => Session::getFlash('error') ?: $loadErr,
        ]);
    }

    public function export(): void
    {
        LocalAuth::require();
        try { $skus = app_service(LicensesService::class)->getSkus(); }
        catch (\Throwable $e) { error_log('Licenses export: ' . $e->getMessage()); $skus = []; }
        CsvExporter::download('lizenzen_' . date('Ymd') . '.csv',
            ['Produkt', 'SKU', 'Genutzt', 'Gesamt', 'Verfügbar', 'Nutzung %'],
            array_map(fn($s) => [
                $s['name'],
                $s['partNumber'],
                $s['consumed'],
                $s['total'],
                $s['available'],
                $s['pct'],
            ], $skus)
        );
    }

    public function expiry(): void
    {
        LocalAuth::require();
        $service = app_service(LicensesService::class);

        if (($_GET['refresh'] ?? '') === '1') {
            app_graph()->getCache()->forget('license_expiry');
        }

        $subscriptions = []; $expiringSoon = []; $loadErr = null;
        try { $subscriptions = $service->getSubscriptionExpiry(); }
        catch (\Throwable $e) { $loadErr = 'Abos: ' . $e->getMessage(); error_log('Licenses expiry subs: ' . $e->getMessage()); }
        try { $expiringSoon = $service->getExpiringSoon(60); }
        catch (\Throwable $e) { error_log('Licenses expiry soon: ' . $e->getMessage()); }

        View::render('licenses/expiry', [
            'pageTitle'     => 'Lizenz-Ablauf',
            'subscriptions' => $subscriptions,
            'expiringSoon'  => $expiringSoon,
            'error'         => Session::getFlash('error') ?: $loadErr,
        ]);
    }
}
