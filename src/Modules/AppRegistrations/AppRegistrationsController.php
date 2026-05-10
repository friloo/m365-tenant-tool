<?php

namespace App\Modules\AppRegistrations;

use App\Auth\LocalAuth;
use App\Core\Redirect;
use App\Core\Session;
use App\Core\View;

class AppRegistrationsController
{
    public function index(): void
    {
        LocalAuth::require();

        $service          = app_service(AppRegistrationsService::class);
        $apps             = $service->getApplications();
        $servicePrincipals = $service->getServicePrincipals();
        $highRiskApps     = $service->getHighRiskApps($apps);

        // Build high-risk app ID set for quick lookup in view
        $highRiskAppIds = [];
        foreach ($highRiskApps as $entry) {
            $highRiskAppIds[$entry['app']['id']] = $entry['riskReasons'];
        }

        // Apps created in last 30 days
        $cutoff = strtotime('-30 days');
        $recentApps = array_filter($apps, function ($a) use ($cutoff) {
            $created = $a['createdDateTime'] ?? null;
            return $created && strtotime($created) >= $cutoff;
        });

        View::render('appregistrations/index', [
            'pageTitle'          => 'App-Registrierungen',
            'apps'               => $apps,
            'servicePrincipals'  => $servicePrincipals,
            'highRiskApps'       => $highRiskApps,
            'highRiskAppIds'     => $highRiskAppIds,
            'recentAppsCount'    => count($recentApps),
            'service'            => $service,
        ]);
    }

    public function show(string $id): void
    {
        LocalAuth::require();

        $service = app_service(AppRegistrationsService::class);
        $detail  = $service->getAppDetail($id);
        $flash   = Session::getFlash('success');
        $error   = Session::getFlash('error');

        View::render('appregistrations/detail', [
            'pageTitle' => $detail['displayName'] ?? 'App-Details',
            'detail'    => $detail,
            'flash'     => $flash,
            'error'     => $error,
        ]);
    }

    public function addSecret(string $id): void
    {
        LocalAuth::requireAdmin();

        $displayName  = trim($_POST['secret_name'] ?? 'Neues Secret');
        $expiryMonths = min(24, max(1, (int)($_POST['expiry_months'] ?? 12)));

        try {
            $service = app_service(AppRegistrationsService::class);
            $result  = $service->addSecret($id, $displayName, $expiryMonths);
            Session::flash('new_secret', $result['secretText'] ?? '');
            Session::flash('success', 'Secret erstellt. Kopiere den Wert jetzt — er wird nicht erneut angezeigt.');
        } catch (\Throwable $e) {
            Session::flash('error', 'Fehler: ' . $e->getMessage());
        }

        Redirect::to('/appregistrations/' . $id);
    }

    public function deleteSecret(string $id): void
    {
        LocalAuth::requireAdmin();

        $keyId = trim($_POST['key_id'] ?? '');

        try {
            $service = app_service(AppRegistrationsService::class);
            $service->deleteSecret($id, $keyId);
            Session::flash('success', 'Secret gelöscht.');
        } catch (\Throwable $e) {
            Session::flash('error', 'Fehler: ' . $e->getMessage());
        }

        Redirect::to('/appregistrations/' . $id);
    }
}
