<?php

namespace App\Modules\DlpPolicies;

use App\Auth\LocalAuth;
use App\Core\View;

class DlpPoliciesController
{
    public function index(): void
    {
        LocalAuth::require();

        // DLP policies have NO Microsoft Graph API (neither v1.0 nor beta). They are
        // managed only in the Purview portal / Security & Compliance PowerShell.
        // This page is therefore an honest pointer, not a (previously misleading)
        // sensitivity-label list. Live DLP *incidents* are available via the
        // dedicated "DLP-Vorfälle" module (audit-log based).
        View::render('dlppolicies/index', [
            'pageTitle' => 'Data Loss Prevention (DLP)',
        ]);
    }
}
