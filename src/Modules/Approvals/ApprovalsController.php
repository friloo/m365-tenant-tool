<?php

namespace App\Modules\Approvals;

use App\Auth\LocalAuth;
use App\Core\Redirect;
use App\Core\Session;
use App\Core\View;

class ApprovalsController
{
    public function index(): void
    {
        LocalAuth::requireAdmin();
        View::render('approvals/index', [
            'pageTitle' => t('Aktionsfreigaben'),
            'pending'   => ApprovalService::listPending(),
            'recent'    => ApprovalService::listRecent(30),
            'enabled'   => ApprovalService::enabled(),
        ]);
    }

    public function approve(): void
    {
        LocalAuth::requireAdmin();
        $err = ApprovalService::approve((int)($_POST['id'] ?? 0));
        if ($err === 'self') {
            Session::flash('error', t('Du kannst deine eigene Anfrage nicht freigeben — ein zweiter Administrator ist erforderlich.'));
        } elseif ($err === 'not_found') {
            Session::flash('error', t('Anfrage nicht gefunden oder bereits entschieden.'));
        } else {
            Session::flash('success', t('Freigabe erteilt. Die Aktion kann nun ausgeführt werden.'));
        }
        Redirect::to('/approvals');
    }

    public function reject(): void
    {
        LocalAuth::requireAdmin();
        if (ApprovalService::reject((int)($_POST['id'] ?? 0))) {
            Session::flash('success', t('Anfrage abgelehnt.'));
        } else {
            Session::flash('error', t('Anfrage nicht gefunden oder bereits entschieden.'));
        }
        Redirect::to('/approvals');
    }
}
