<?php

namespace App\Modules\MfaMethods;

use App\Auth\LocalAuth;
use App\Core\View;

class MfaMethodsController
{
    public function index(): void
    {
        LocalAuth::require();

        $service = app_service(MfaMethodsService::class);

        if (isset($_GET['refresh'])) {
            // Clear both caches AND the access token — a stale token from
            // before the permission was granted would still get HTTP 403
            app_graph()->getCache()->forget('mfa_methods_detail');
            app_graph()->getCache()->forget('mfa_methods_legacy');
            \App\Database\DB::execute('DELETE FROM graph_tokens');
        }

        $users     = $service->getAll();
        $summary   = $service->getSummary($users);
        $apiError  = empty($users) ? $service->getLastError() : null;

        View::render('mfamethods/index', [
            'pageTitle' => 'MFA-Methoden',
            'users'     => $users,
            'summary'   => $summary,
            'labels'    => MfaMethodsService::methodLabels(),
            'apiError'  => $apiError,
        ]);
    }
}
