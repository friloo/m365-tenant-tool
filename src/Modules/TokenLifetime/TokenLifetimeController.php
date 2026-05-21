<?php

namespace App\Modules\TokenLifetime;

use App\Auth\LocalAuth;
use App\Core\View;

class TokenLifetimeController
{
    public function index(): void
    {
        LocalAuth::require();
        $service = app_service(TokenLifetimeService::class);

        if (isset($_GET['refresh'])) app_graph()->getCache()->forget('tokenlife_ca');

        $caPolicies = $service->getCaSignInFrequency();
        $persistent = $service->getPersistentBrowserSettings();
        $recs       = $service->getRecommendations($caPolicies);

        $diag = null;
        if (empty($caPolicies) && empty($persistent)) {
            $diag = \App\Graph\GraphErrorTranslator::translate(
                app_graph()->getLastError(),
                'Policy.Read.All'
            );
        }

        View::render('tokenlifetime/index', [
            'pageTitle'  => 'Token-Lifetime & Sign-in-Frequency',
            'caPolicies' => $caPolicies,
            'persistent' => $persistent,
            'recs'       => $recs,
            'diag'       => $diag,
        ]);
    }
}
