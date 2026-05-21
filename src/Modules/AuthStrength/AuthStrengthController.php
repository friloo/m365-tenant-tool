<?php

namespace App\Modules\AuthStrength;

use App\Auth\LocalAuth;
use App\Core\View;

class AuthStrengthController
{
    public function index(): void
    {
        LocalAuth::require();
        $service = app_service(AuthStrengthService::class);

        if (isset($_GET['refresh'])) {
            app_graph()->getCache()->forget('auth_strength_reg');
            app_graph()->getCache()->forget('auth_strength_policies');
        }

        $report   = $service->getRegistrationReport();
        $policies = $service->getPolicies();

        $diag = null;
        if ($report['total'] === 0) {
            $diag = \App\Graph\GraphErrorTranslator::translate(
                app_graph()->getLastError(),
                'AuditLog.Read.All'
            );
        }

        View::render('authstrength/index', [
            'pageTitle' => 'Authentication-Strength',
            'report'    => $report,
            'policies'  => $policies,
            'diag'      => $diag,
        ]);
    }
}
