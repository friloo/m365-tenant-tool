<?php

namespace App\Modules\Lifecycle;

use App\Auth\LocalAuth;
use App\Core\View;

class LifecycleController
{
    public function index(): void
    {
        LocalAuth::require();
        if (isset($_GET['refresh'])) app_graph()->getCache()->forget('lifecycle_workflows');

        $service   = app_service(LifecycleService::class);
        $workflows = $service->listWorkflows();
        $runs      = [];
        foreach (array_slice($workflows, 0, 10) as $w) {
            $runs[$w['id']] = $service->getLastRuns($w['id'], 3);
        }

        $diag = null;
        if (empty($workflows)) {
            $diag = \App\Graph\GraphErrorTranslator::translate(
                app_graph()->getLastError(),
                'LifecycleWorkflows.Read.All'
            );
        }

        View::render('lifecycle/index', [
            'pageTitle' => 'Lifecycle Workflows',
            'workflows' => $workflows,
            'runs'      => $runs,
            'diag'      => $diag,
        ]);
    }
}
