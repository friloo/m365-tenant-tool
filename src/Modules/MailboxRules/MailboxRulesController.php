<?php

namespace App\Modules\MailboxRules;

use App\Auth\LocalAuth;
use App\Core\View;

class MailboxRulesController
{
    public function index(): void
    {
        LocalAuth::require();
        $service = app_service(MailboxRulesService::class);

        if (isset($_GET['refresh'])) $service->clearCache();

        $report = $service->scanAll(500);

        $diag = null;
        if ($report['scanned_users'] === 0) {
            $diag = \App\Graph\GraphErrorTranslator::translate(
                app_graph()->getLastError(),
                'MailboxSettings.Read'
            );
        }

        View::render('mailboxrules/index', [
            'pageTitle' => 'Outlook-Regeln & Auto-Weiterleitungen',
            'report'    => $report,
            'diag'      => $diag,
        ]);
    }
}
