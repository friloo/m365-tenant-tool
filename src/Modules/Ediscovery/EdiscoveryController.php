<?php

namespace App\Modules\Ediscovery;

use App\Auth\LocalAuth;
use App\Core\View;

class EdiscoveryController
{
    public function index(): void
    {
        LocalAuth::require();

        ['data' => $cases, 'diag' => $diag] = \App\Graph\GraphErrorTranslator::guard(
            fn() => app_graph()->paginate(
                '/security/cases/ediscoveryCases',
                ['$select' => 'id,displayName,status,createdDateTime,closedDateTime'],
                10,
                'ediscovery_cases',
                1800
            ),
            'eDiscovery.Read.All'
        );
        $cases ??= [];

        $openCount   = count(array_filter($cases, fn($c) => ($c['status'] ?? '') === 'active'));
        $closedCount = count(array_filter($cases, fn($c) => ($c['status'] ?? '') === 'closed'));

        View::render('ediscovery/index', [
            'pageTitle'   => t('eDiscovery-Fälle'),
            'cases'       => $cases,
            'openCount'   => $openCount,
            'closedCount' => $closedCount,
            'diag'        => $diag,
        ]);
    }
}
