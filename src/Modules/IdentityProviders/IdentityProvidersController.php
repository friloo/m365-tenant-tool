<?php

namespace App\Modules\IdentityProviders;

use App\Auth\LocalAuth;
use App\Core\View;

class IdentityProvidersController
{
    public function index(): void
    {
        LocalAuth::require();
        if (isset($_GET['refresh'])) {
            app_graph()->getCache()->forget('idp_providers');
            app_graph()->getCache()->forget('idp_domains');
        }

        $service = app_service(IdentityProvidersService::class);
        $idps    = $service->listIdentityProviders();
        $feds    = $service->listFederatedDomains();

        $diag = null;
        if (empty($idps) && empty($feds)) {
            $diag = \App\Graph\GraphErrorTranslator::translate(
                app_graph()->getLastError(),
                'IdentityProvider.Read.All'
            );
        }

        View::render('identityproviders/index', [
            'pageTitle' => 'Identity Provider Trust',
            'idps'      => $idps,
            'feds'      => $feds,
            'diag'      => $diag,
        ]);
    }
}
