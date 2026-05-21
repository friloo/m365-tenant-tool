<?php

namespace App\Modules\Pim;

use App\Auth\LocalAuth;
use App\Core\View;

class PimController
{
    public function index(): void
    {
        LocalAuth::require();

        $service  = app_service(PimService::class);
        $active   = $service->getActiveAssignments();
        $eligible = $service->getEligibleAssignments();
        $recent   = $service->getRecentActivations(30);
        $summary  = $service->getSummary($active, $eligible);

        $diag = null;
        if (empty($active) && empty($eligible) && empty($recent)) {
            $diag = \App\Graph\GraphErrorTranslator::translate(
                app_graph()->getLastError(),
                'RoleManagement.Read.Directory'
            );
        }

        View::render('pim/index', [
            'pageTitle' => 'Privileged Identity Management',
            'active'    => $active,
            'eligible'  => $eligible,
            'recent'    => $recent,
            'summary'   => $summary,
            'diag'      => $diag,
        ]);
    }
}
