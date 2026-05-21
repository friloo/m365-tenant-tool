<?php

namespace App\Modules\PhishingSim;

use App\Auth\LocalAuth;
use App\Core\View;

class PhishingSimController
{
    public function index(): void
    {
        LocalAuth::require();
        $service = app_service(PhishingSimService::class);
        if (isset($_GET['refresh'])) app_graph()->getCache()->forget('phishing_sims');

        $sims = $service->listSimulations();
        // Stats für die ersten 10 (sonst zu viele Calls)
        $stats = [];
        foreach (array_slice($sims, 0, 10) as $s) {
            $stats[$s['id']] = $service->getSimulationStats($s['id']);
        }

        $diag = null;
        if (empty($sims)) {
            $diag = \App\Graph\GraphErrorTranslator::translate(
                app_graph()->getLastError(),
                'AttackSimulation.Read.All'
            );
        }

        View::render('phishingsim/index', [
            'pageTitle' => 'Phishing-Simulationen',
            'sims'      => $sims,
            'stats'     => $stats,
            'diag'      => $diag,
        ]);
    }
}
