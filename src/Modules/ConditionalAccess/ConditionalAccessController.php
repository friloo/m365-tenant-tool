<?php

namespace App\Modules\ConditionalAccess;

use App\Auth\LocalAuth;
use App\Core\Redirect;
use App\Core\Session;
use App\Core\View;
use App\Modules\NamedLocations\NamedLocationsService;

class ConditionalAccessController
{
    public function index(): void
    {
        LocalAuth::require();

        if (isset($_GET['refresh'])) {
            app_graph()->getCache()->forget('ca_policies');
        }

        /** @var ConditionalAccessService $service */
        $service  = app_service(ConditionalAccessService::class);
        $policies = $service->getPolicies();
        $gaps     = $service->analyseGaps($policies);

        $summary = [
            'enabled'    => count(array_filter($policies, fn($p) => ($p['state'] ?? '') === 'enabled')),
            'reportOnly' => count(array_filter($policies, fn($p) => ($p['state'] ?? '') === 'enabledForReportingButNotEnforced')),
            'disabled'   => count(array_filter($policies, fn($p) => ($p['state'] ?? '') === 'disabled')),
        ];

        $policiesWithSummary = array_map(function ($p) use ($service) {
            $p['_summary'] = $service->summariseConditions($p);
            return $p;
        }, $policies);

        usort($policiesWithSummary, function ($a, $b) {
            $order = ['enabled' => 0, 'enabledForReportingButNotEnforced' => 1, 'disabled' => 2];
            return ($order[$a['state'] ?? ''] ?? 3) <=> ($order[$b['state'] ?? ''] ?? 3);
        });

        // Fetch named locations for the "country block" policy template dropdown
        $nlService = app_service(NamedLocationsService::class);
        $allNl     = $nlService->getAll();
        $classified = $nlService->classify($allNl);
        $countryLocations = $classified['country'];

        View::render('conditionalaccess/index', [
            'pageTitle'        => 'Conditional Access',
            'policies'         => $policiesWithSummary,
            'gaps'             => $gaps,
            'summary'          => $summary,
            'lastError'        => $service->getLastError(),
            'countryLocations' => $countryLocations,
            'flash'            => Session::getFlash('success'),
            'error'            => Session::getFlash('error'),
        ]);
    }

    public function create(): void
    {
        LocalAuth::requireAdmin();

        $def = [
            'displayName'     => trim($_POST['displayName'] ?? ''),
            'state'           => $_POST['state'] ?? 'enabledForReportingButNotEnforced',
            'template'        => $_POST['template'] ?? 'mfa_all',
            'namedLocationId' => $_POST['namedLocationId'] ?? '',
            'excludeUserId'   => trim($_POST['excludeUserId'] ?? ''),
        ];

        if ($def['displayName'] === '') {
            Session::flash('error', 'Ein Name für die Richtlinie ist erforderlich.');
            Redirect::to('/conditionalaccess');
        }

        if ($def['template'] === 'country_block' && $def['namedLocationId'] === '') {
            Session::flash('error', 'Bitte einen Länder-Standort auswählen.');
            Redirect::to('/conditionalaccess');
        }

        try {
            app_service(ConditionalAccessService::class)->createPolicy($def);
            Session::flash('success', "Richtlinie „{$def['displayName']}" wurde angelegt (im Report-Modus — zum Aktivieren umschalten).");
        } catch (\Throwable $e) {
            Session::flash('error', 'Richtlinie konnte nicht erstellt werden: ' . $e->getMessage());
        }
        Redirect::to('/conditionalaccess');
    }

    public function toggleState(string $id): void
    {
        LocalAuth::requireAdmin();
        $newState = $_POST['state'] ?? '';
        try {
            app_service(ConditionalAccessService::class)->toggleState($id, $newState);
            $labels = [
                'enabled'                           => 'Aktiviert',
                'disabled'                          => 'Deaktiviert',
                'enabledForReportingButNotEnforced' => 'Report-only',
            ];
            Session::flash('success', 'Richtlinienstatus geändert: ' . ($labels[$newState] ?? $newState));
        } catch (\Throwable $e) {
            Session::flash('error', 'Statusänderung fehlgeschlagen: ' . $e->getMessage());
        }
        Redirect::to('/conditionalaccess');
    }

    public function deletePolicy(string $id): void
    {
        LocalAuth::requireAdmin();
        try {
            app_service(ConditionalAccessService::class)->deletePolicy($id);
            Session::flash('success', 'Richtlinie wurde gelöscht.');
        } catch (\Throwable $e) {
            Session::flash('error', 'Löschen fehlgeschlagen: ' . $e->getMessage());
        }
        Redirect::to('/conditionalaccess');
    }
}
