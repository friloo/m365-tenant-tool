<?php

namespace App\Modules\PimSettings;

use App\Auth\LocalAuth;
use App\Core\View;

class PimSettingsController
{
    public function index(): void
    {
        LocalAuth::require();

        if (isset($_GET['refresh'])) {
            app_graph()->getCache()->forget('pim_role_policies');
            app_graph()->getCache()->forget('pim_role_defs');
        }

        /** @var PimSettingsService $svc */
        $svc = app_service(PimSettingsService::class);
        ['data' => $rows, 'diag' => $diag] = \App\Graph\GraphErrorTranslator::guard(
            fn() => $svc->getRoleSettings(),
            'RoleManagementPolicy.Read.Directory'
        );
        $rows ??= [];

        View::render('pimsettings/index', [
            'pageTitle' => 'PIM — Rollen-Aktivierungsregeln',
            'rows'      => $rows,
            'diag'      => $diag,
        ]);
    }
}
