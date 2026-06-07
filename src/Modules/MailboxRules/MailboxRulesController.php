<?php

namespace App\Modules\MailboxRules;

use App\Auth\LocalAuth;
use App\Core\Redirect;

class MailboxRulesController
{
    /**
     * Auto-Forward-Audit was merged into the unified "Weiterleitungen & Regeln"
     * page (/mailboxes/external-forwards), which now shows both mailbox-level
     * forwarding and inbox-rule forwarding. Kept as a redirect for old links.
     */
    public function index(): void
    {
        LocalAuth::require();
        Redirect::to('/mailboxes/external-forwards');
    }
}
