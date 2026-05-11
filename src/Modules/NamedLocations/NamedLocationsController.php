<?php

namespace App\Modules\NamedLocations;

use App\Auth\LocalAuth;
use App\Core\View;

class NamedLocationsController
{
    public function index(): void
    {
        LocalAuth::require();

        if (isset($_GET['refresh'])) {
            app_graph()->getCache()->forget('named_locations');
        }

        /** @var NamedLocationsService $service */
        $service   = app_service(NamedLocationsService::class);
        $all       = $service->getAll();
        $classified = $service->classify($all);

        View::render('namedlocations/index', [
            'pageTitle'     => 'Named Locations (Vertrauenswürdige Standorte)',
            'ipLocations'   => $classified['ip'],
            'countryLocations' => $classified['country'],
            'lastError'     => $service->getLastError(),
        ]);
    }
}
