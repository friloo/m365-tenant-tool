<?php

namespace App\Modules\InsiderThreat;

use App\Auth\LocalAuth;
use App\Core\View;

class InsiderThreatController
{
    public function index(): void
    {
        LocalAuth::require();
        $days = max(7, min(90, (int)($_GET['days'] ?? 30)));

        if (isset($_GET['refresh'])) {
            app_graph()->getCache()->forget('insider_signins_' . $days . 'd');
            app_graph()->getCache()->forget('insider_audit_' . $days . 'd');
        }

        $report = app_service(InsiderThreatService::class)->scan($days);

        $diag = null;
        if ($report['total_users_analyzed'] === 0) {
            $diag = \App\Graph\GraphErrorTranslator::translate(
                app_graph()->getLastError(),
                'AuditLog.Read.All'
            );
        }

        View::render('insiderthreat/index', [
            'pageTitle' => 'Insider-Threat-Detection',
            'report'    => $report,
            'days'      => $days,
            'diag'      => $diag,
        ]);
    }
}
