<?php

namespace App\Modules\OAuthAudit;

use App\Auth\LocalAuth;
use App\Core\View;

class OAuthAuditController
{
    public function index(): void
    {
        LocalAuth::require();
        $service = app_service(OAuthAuditService::class);

        if (isset($_GET['refresh'])) {
            app_graph()->getCache()->forget('oauth_sps');
            app_graph()->getCache()->forget('oauth_sp_signins');
        }

        $apps    = $service->listEnterpriseApps();
        $summary = $service->getSummary($apps);

        $showOnlyThirdParty = ($_GET['filter'] ?? '') !== 'all';
        $visible = $showOnlyThirdParty
            ? array_values(array_filter($apps, fn($a) => !$a['is_microsoft']))
            : $apps;

        $diag = null;
        if (empty($apps)) {
            $diag = \App\Graph\GraphErrorTranslator::translate(
                app_graph()->getLastError(),
                'Application.Read.All'
            );
        }

        View::render('oauthaudit/index', [
            'pageTitle'          => 'OAuth-App-Audit',
            'apps'               => $visible,
            'summary'            => $summary,
            'showOnlyThirdParty' => $showOnlyThirdParty,
            'diag'               => $diag,
        ]);
    }
}
