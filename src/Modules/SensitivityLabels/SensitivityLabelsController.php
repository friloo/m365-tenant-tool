<?php

namespace App\Modules\SensitivityLabels;

use App\Auth\LocalAuth;
use App\Core\View;

class SensitivityLabelsController
{
    public function index(): void
    {
        LocalAuth::require();

        if (isset($_GET['refresh'])) {
            $cache = app_graph()->getCache();
            $cache->forget('sensitivity_labels');
            $cache->forget('sensitivity_labels_v2');
            $cache->forget('sensitivity_policy_settings');
        }

        /** @var SensitivityLabelsService $service */
        $service  = app_service(SensitivityLabelsService::class);
        $labels   = $service->getLabels();
        $settings = $service->getPolicySettings();

        View::render('sensitivitylabels/index', [
            'pageTitle' => 'Vertraulichkeitsbezeichnungen (Sensitivity Labels)',
            'labels'    => $labels,
            'settings'  => $settings,
            'lastError' => $service->getLastError(),
        ]);
    }
}
