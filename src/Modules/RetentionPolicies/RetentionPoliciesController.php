<?php

namespace App\Modules\RetentionPolicies;

use App\Auth\LocalAuth;
use App\Core\View;

class RetentionPoliciesController
{
    public function index(): void
    {
        LocalAuth::require();

        $cases = [];
        try {
            $cases = app_graph()->paginate(
                '/security/cases/ediscoveryCases',
                ['$select' => 'id,displayName,status,createdDateTime,closedDateTime'],
                10,
                'ediscovery_cases',
                1800
            );
        } catch (\Throwable) {
            $cases = [];
        }

        $openCount   = count(array_filter($cases, fn($c) => ($c['status'] ?? '') === 'active'));
        $closedCount = count(array_filter($cases, fn($c) => ($c['status'] ?? '') === 'closed'));

        View::render('retentionpolicies/index', [
            'pageTitle'   => 'Aufbewahrungsrichtlinien',
            'cases'       => $cases,
            'openCount'   => $openCount,
            'closedCount' => $closedCount,
        ]);
    }
}
