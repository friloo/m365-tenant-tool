<?php

namespace App\Modules\MfaFatigue;

use App\Auth\LocalAuth;
use App\Core\View;

class MfaFatigueController
{
    public function index(): void
    {
        LocalAuth::require();
        $hours = max(24, min(720, (int)($_GET['hours'] ?? 168)));

        if (isset($_GET['refresh'])) app_graph()->getCache()->forget('mfa_fatigue_signins');

        $service = app_service(MfaFatigueService::class);
        $report  = $service->scan($hours);

        $diag = null;
        if ($report['total_denials'] === 0 && empty($report['clusters'])) {
            $diag = \App\Graph\GraphErrorTranslator::translate(
                app_graph()->getLastError(),
                'AuditLog.Read.All'
            );
        }

        View::render('mfafatigue/index', [
            'pageTitle' => 'MFA-Fatigue-Erkennung',
            'report'    => $report,
            'hours'     => $hours,
            'diag'      => $diag,
        ]);
    }
}
