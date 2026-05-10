<?php

namespace App\Modules\LicenseAdvisor;

use App\Auth\LocalAuth;
use App\Core\Redirect;
use App\Core\Session;
use App\Core\View;
use App\Helpers\CsvExporter;

class LicenseAdvisorController
{
    public function index(): void
    {
        LocalAuth::require();

        /** @var LicenseAdvisorService $service */
        $service = app_service(LicenseAdvisorService::class);

        $activeCriteria = $service->getActiveCriteria();
        $allSkus        = $service->getSkusWithCriteria();
        $matchingSkus   = $service->getMatchingSkus($allSkus, $activeCriteria);
        $allUsers       = $service->getAllUsers();
        $analysis       = $service->analyzeUsers($allUsers, $allSkus, $activeCriteria);

        $flash = Session::getFlash('success');
        $error = Session::getFlash('error');

        View::render('licenseadvisor/index', [
            'pageTitle'      => 'Lizenz-Advisor',
            'activeCriteria' => $activeCriteria,
            'criteriaMap'    => LicenseAdvisorService::CRITERIA_MAP,
            'allSkus'        => $allSkus,
            'matchingSkus'   => $matchingSkus,
            'analysis'       => $analysis,
            'flash'          => $flash,
            'error'          => $error,
        ]);
    }

    public function saveCriteria(): void
    {
        LocalAuth::requireAdmin();

        /** @var LicenseAdvisorService $service */
        $service = app_service(LicenseAdvisorService::class);
        $service->saveCriteria($_POST);

        Session::flash('success', 'Kriterien gespeichert.');
        Redirect::to('/licenseadvisor');
    }

    public function exportUncovered(): void
    {
        LocalAuth::require();

        /** @var LicenseAdvisorService $service */
        $service = app_service(LicenseAdvisorService::class);

        $activeCriteria = $service->getActiveCriteria();
        $allSkus        = $service->getSkusWithCriteria();
        $allUsers       = $service->getAllUsers();
        $analysis       = $service->analyzeUsers($allUsers, $allSkus, $activeCriteria);

        $criteriaMap = LicenseAdvisorService::CRITERIA_MAP;

        $users = array_merge($analysis['uncovered'], $analysis['no_license']);

        CsvExporter::download(
            'lizenz_advisor_luecken_' . date('Ymd') . '.csv',
            ['Name', 'UPN', 'Abteilung', 'Jobtitel', 'Fehlende Kriterien', 'Letzter Login'],
            array_map(function ($u) use ($criteriaMap) {
                $missing = $u['missing'] ?? [];
                $missingLabels = implode(', ', array_map(
                    fn($k) => $criteriaMap[$k]['label'] ?? $k,
                    $missing
                ));
                $lastSignIn = $u['signInActivity']['lastSignInDateTime'] ?? null;
                return [
                    $u['displayName'] ?? '',
                    $u['userPrincipalName'] ?? '',
                    $u['department'] ?? '',
                    $u['jobTitle'] ?? '',
                    $missingLabels ?: 'Keine Lizenz',
                    $lastSignIn ? date('d.m.Y', strtotime($lastSignIn)) : '',
                ];
            }, $users)
        );
    }
}
