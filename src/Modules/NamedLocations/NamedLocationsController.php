<?php

namespace App\Modules\NamedLocations;

use App\Auth\LocalAuth;
use App\Core\Redirect;
use App\Core\Session;
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
        $service = app_service(NamedLocationsService::class);
        ['data' => $all, 'diag' => $diag] = \App\Graph\GraphErrorTranslator::guard(
            fn() => $service->getAll(),
            'Policy.Read.All'
        );
        $all        ??= [];
        $classified = $service->classify($all);

        View::render('namedlocations/index', [
            'pageTitle'        => t('Named Locations (Vertrauenswürdige Standorte)'),
            'ipLocations'      => $classified['ip'],
            'countryLocations' => $classified['country'],
            'lastError'        => $service->getLastError(),
            'diag'             => $diag,
            'flash'            => Session::getFlash('success'),
            'error'            => Session::getFlash('error'),
        ]);
    }

    public function createCountry(): void
    {
        LocalAuth::requireAdmin();
        $name     = trim($_POST['name'] ?? '');
        $codes    = array_filter(array_map('strtoupper', array_map('trim', explode(',', $_POST['countries'] ?? ''))));
        $unknown  = !empty($_POST['include_unknown']);

        if ($name === '' || empty($codes)) {
            Session::flash('error', t('Name und mindestens ein Ländercode sind erforderlich.'));
            Redirect::to('/namedlocations');
        }

        try {
            app_service(NamedLocationsService::class)->createCountryLocation($name, $codes, $unknown);
            Session::flash('success', t('Länder-Standort ":name" wurde angelegt.', ['name' => $name]));
        } catch (\Throwable $e) {
            Session::flash('error', t('Fehler: ') . $e->getMessage());
        }
        Redirect::to('/namedlocations');
    }

    public function createIp(): void
    {
        LocalAuth::requireAdmin();
        $name    = trim($_POST['name'] ?? '');
        $cidrs   = array_filter(array_map('trim', explode("\n", $_POST['cidrs'] ?? '')));
        $trusted = !empty($_POST['trusted']);

        if ($name === '' || empty($cidrs)) {
            Session::flash('error', t('Name und mindestens ein IP-Bereich sind erforderlich.'));
            Redirect::to('/namedlocations');
        }

        try {
            app_service(NamedLocationsService::class)->createIpLocation($name, $cidrs, $trusted);
            Session::flash('success', t('IP-Standort ":name" wurde angelegt.', ['name' => $name]));
        } catch (\Throwable $e) {
            Session::flash('error', t('Fehler: ') . $e->getMessage());
        }
        Redirect::to('/namedlocations');
    }

    public function delete(string $id): void
    {
        LocalAuth::requireAdmin();
        try {
            app_service(NamedLocationsService::class)->delete($id);
            Session::flash('success', t('Standort wurde gelöscht.'));
        } catch (\Throwable $e) {
            Session::flash('error', t('Löschen fehlgeschlagen: ') . $e->getMessage());
        }
        Redirect::to('/namedlocations');
    }
}
