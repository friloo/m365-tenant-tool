<?php

namespace App\Modules\ExecutiveReport;

use App\Auth\LocalAuth;
use App\Core\Config;
use App\Core\Redirect;
use App\Core\Session;
use App\Core\View;

class ExecutiveReportController
{
    public function index(): void
    {
        LocalAuth::require();
        $config = Config::getInstance();

        View::render('executivereport/index', [
            'pageTitle'  => 'Executive-Report',
            'enabled'    => $config->get('executive_report_enabled', '0') === '1',
            'recipients' => $config->get('executive_report_to', '') ?: $config->get('alert_email_to', ''),
            'flash'      => Session::getFlash('success'),
            'error'      => Session::getFlash('error'),
        ]);
    }

    public function save(): void
    {
        LocalAuth::requireAdmin();
        $config = Config::getInstance();
        $config->set('executive_report_enabled', isset($_POST['enabled']) ? '1' : '0');
        $config->set('executive_report_to', trim($_POST['recipients'] ?? ''));
        Session::flash('success', 'Executive-Report-Einstellungen gespeichert.');
        Redirect::to('/executivereport');
    }

    public function sendNow(): void
    {
        LocalAuth::requireAdmin();
        $service = app_service(ExecutiveReportService::class);
        $result = $service->generate();
        Session::flash('success', 'Test-Versand: ' . $result);
        Redirect::to('/executivereport');
    }

    public function preview(): void
    {
        LocalAuth::require();
        $service = app_service(ExecutiveReportService::class);
        header('Content-Type: text/html; charset=utf-8');
        echo $service->previewHtml();
    }
}
