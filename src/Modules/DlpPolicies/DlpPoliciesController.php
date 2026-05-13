<?php

namespace App\Modules\DlpPolicies;

use App\Auth\LocalAuth;
use App\Core\View;

class DlpPoliciesController
{
    public function index(): void
    {
        LocalAuth::require();

        $labels = [];
        try {
            $labels = app_graph()->paginate(
                '/security/informationProtection/sensitivityLabels',
                ['$select' => 'id,name,isActive,priority'],
                5,
                'sensitivity_labels_dlp',
                1800
            );
        } catch (\Throwable) {
            $labels = [];
        }

        $activeCount = count(array_filter($labels, fn($l) => $l['isActive'] ?? false));

        View::render('dlppolicies/index', [
            'pageTitle'   => 'DLP-Richtlinien',
            'labels'      => $labels,
            'activeCount' => $activeCount,
        ]);
    }
}
