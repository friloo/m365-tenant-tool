<?php

namespace App\Modules\CustomerLockbox;

use App\Auth\LocalAuth;
use App\Core\Config;
use App\Core\Redirect;
use App\Core\Session;
use App\Core\View;

/**
 * Customer Lockbox — Microsoft-Support-Engineer-Zugriffe auf
 * Kundendaten benötigen ausdrückliche Freigabe durch einen Tenant-Admin.
 *
 * Microsoft Graph hat dafür keinen öffentlichen Endpunkt; wir tracken
 * den Status manuell (aktiviert ja/nein, Approver-Liste) und linken
 * zum richtigen Admin-Center.
 */
class CustomerLockboxController
{
    public function index(): void
    {
        LocalAuth::require();
        $config = Config::getInstance();
        $data = [
            'enabled'     => $config->get('lockbox_enabled', '0') === '1',
            'approvers'   => $config->get('lockbox_approvers', ''),
            'last_review' => $config->get('lockbox_last_review', ''),
            'sla_hours'   => (int)$config->get('lockbox_sla_hours', 0),
        ];
        View::render('customerlockbox/index', [
            'pageTitle' => 'Customer Lockbox',
            'data'      => $data,
            'flash'     => Session::getFlash('success'),
            'error'     => Session::getFlash('error'),
        ]);
    }

    public function save(): void
    {
        LocalAuth::requireAdmin();
        $config = Config::getInstance();
        $config->set('lockbox_enabled',    isset($_POST['enabled']) ? '1' : '0');
        $config->set('lockbox_approvers',  trim($_POST['approvers']   ?? ''));
        $config->set('lockbox_last_review',trim($_POST['last_review'] ?? ''));
        $config->set('lockbox_sla_hours',  (string)(int)($_POST['sla_hours'] ?? 0));
        Session::flash('success', 'Customer-Lockbox-Status gespeichert.');
        Redirect::to('/customerlockbox');
    }
}
