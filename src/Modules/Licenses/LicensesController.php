<?php

namespace App\Modules\Licenses;

use App\Auth\LocalAuth;
use App\Core\View;
use App\Helpers\CsvExporter;

class LicensesController
{
    public function index(): void
    {
        LocalAuth::require();
        $service = app_service(LicensesService::class);
        $skus    = $service->getSkus();

        View::render('licenses/index', [
            'pageTitle' => 'Lizenzen',
            'skus'      => $skus,
        ]);
    }

    public function export(): void
    {
        LocalAuth::require();
        $skus = app_service(LicensesService::class)->getSkus();
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
}
