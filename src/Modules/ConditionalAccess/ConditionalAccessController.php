<?php

namespace App\Modules\ConditionalAccess;

use App\Auth\LocalAuth;
use App\Core\View;

class ConditionalAccessController
{
    public function index(): void
    {
        LocalAuth::require();

        if (isset($_GET['refresh'])) {
            app_graph()->getCache()->forget('ca_policies');
        }

        /** @var ConditionalAccessService $service */
        $service  = app_service(ConditionalAccessService::class);
        $policies = $service->getPolicies();
        $gaps     = $service->analyseGaps($policies);

        $summary = [
            'enabled'    => count(array_filter($policies, fn($p) => ($p['state'] ?? '') === 'enabled')),
            'reportOnly' => count(array_filter($policies, fn($p) => ($p['state'] ?? '') === 'enabledForReportingButNotEnforced')),
            'disabled'   => count(array_filter($policies, fn($p) => ($p['state'] ?? '') === 'disabled')),
        ];

        // Pre-compute condition summaries for each policy
        $policiesWithSummary = array_map(function ($p) use ($service) {
            $p['_summary'] = $service->summariseConditions($p);
            return $p;
        }, $policies);

        // Sort: enabled first, then report-only, then disabled
        usort($policiesWithSummary, function ($a, $b) {
            $order = ['enabled' => 0, 'enabledForReportingButNotEnforced' => 1, 'disabled' => 2];
            return ($order[$a['state'] ?? ''] ?? 3) <=> ($order[$b['state'] ?? ''] ?? 3);
        });

        View::render('conditionalaccess/index', [
            'pageTitle' => 'Conditional Access',
            'policies'  => $policiesWithSummary,
            'gaps'      => $gaps,
            'summary'   => $summary,
            'lastError' => $service->getLastError(),
        ]);
    }
}
