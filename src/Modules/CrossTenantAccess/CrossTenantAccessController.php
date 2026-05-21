<?php

namespace App\Modules\CrossTenantAccess;

use App\Auth\LocalAuth;
use App\Core\View;

class CrossTenantAccessController
{
    public function index(): void
    {
        LocalAuth::require();
        $service = app_service(CrossTenantAccessService::class);

        if (isset($_GET['refresh'])) {
            app_graph()->getCache()->forget('xta_default');
            app_graph()->getCache()->forget('xta_partners');
        }

        $default  = $service->getDefault();
        $partners = $service->getPartners();

        $diag = null;
        if (empty($default) && empty($partners)) {
            $diag = \App\Graph\GraphErrorTranslator::translate(
                app_graph()->getLastError(),
                'Policy.Read.All'
            );
        }

        View::render('crosstenantaccess/index', [
            'pageTitle' => 'Cross-Tenant-Access (B2B)',
            'default'   => $default,
            'partners'  => $partners,
            'diag'      => $diag,
        ]);
    }
}
