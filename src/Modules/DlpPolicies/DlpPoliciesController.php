<?php

namespace App\Modules\DlpPolicies;

use App\Auth\LocalAuth;
use App\Core\View;

class DlpPoliciesController
{
    public function index(): void
    {
        LocalAuth::require();

        // Sensitivity-Labels: Microsoft hat den Pfad /security/informationProtection/
        // sensitivityLabels in v1.0 entfernt — er existiert nur noch in beta.
        // Probiere beta zuerst, dann v1.0 delegated als Fallback.
        $graph  = app_graph();
        $labels = [];
        $diag   = null;
        foreach ([
            'https://graph.microsoft.com/beta/security/informationProtection/sensitivityLabels',
            '/informationProtection/policy/labels',
        ] as $endpoint) {
            try {
                $data = $graph->get($endpoint, ['$select' => 'id,name,isActive,priority'], 'dlp_sens_labels_' . md5($endpoint), 1800);
                if ($graph->getLastError() !== null) continue;
                $labels = $data['value'] ?? [];
                $diag   = null;
                break;
            } catch (\Throwable $e) {
                $diag = \App\Graph\GraphErrorTranslator::fromThrowable($e, 'InformationProtectionPolicy.Read.All');
            }
        }
        if (empty($labels) && $diag === null) {
            $diag = \App\Graph\GraphErrorTranslator::translate($graph->getLastError(), 'InformationProtectionPolicy.Read.All');
        }

        $activeCount = count(array_filter($labels, fn($l) => $l['isActive'] ?? false));

        View::render('dlppolicies/index', [
            'pageTitle'   => 'Vertraulichkeitslabels (DLP)',
            'labels'      => $labels,
            'activeCount' => $activeCount,
            'diag'        => $diag,
        ]);
    }
}
