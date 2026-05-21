<?php

namespace App\Modules\DlpIncidents;

use App\Auth\LocalAuth;
use App\Core\View;

class DlpIncidentsController
{
    public function index(): void
    {
        LocalAuth::require();

        $days = max(1, min(90, (int)($_GET['days'] ?? 30)));
        $service = app_service(DlpIncidentsService::class);

        if (isset($_GET['refresh'])) {
            app_graph()->getCache()->forget('dlp_incidents_' . $days . 'd');
        }

        $incidents = $service->getIncidents($days);
        $summary   = $service->summarize($incidents);

        $diag = null;
        if (empty($incidents)) {
            $diag = \App\Graph\GraphErrorTranslator::translate(
                app_graph()->getLastError(),
                'AuditLog.Read.All'
            );
        }

        View::render('dlpincidents/index', [
            'pageTitle' => 'DLP-Vorfälle',
            'incidents' => $incidents,
            'summary'   => $summary,
            'days'      => $days,
            'diag'      => $diag,
        ]);
    }
}
