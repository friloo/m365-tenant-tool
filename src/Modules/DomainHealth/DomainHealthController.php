<?php

namespace App\Modules\DomainHealth;

use App\Auth\LocalAuth;
use App\Core\View;

class DomainHealthController
{
    public function index(): void
    {
        LocalAuth::require();

        $service = app_service(DomainHealthService::class);

        $refresh = ($_GET['refresh'] ?? '') === '1';
        if ($refresh) {
            app_graph()->getCache()->forget('domains_all');
        }

        $domains = $service->getAll($refresh);
        $summary = $service->getSummary($domains);

        View::render('domainhealth/index', [
            'pageTitle' => 'Domain Health (SPF/DKIM/DMARC)',
            'domains'   => $domains,
            'summary'   => $summary,
        ]);
    }
}
