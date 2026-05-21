<?php

namespace App\Modules\ComplianceProfile;

use App\Auth\LocalAuth;
use App\Core\AppAudit;
use App\Core\Config;
use App\Core\Redirect;
use App\Core\Session;
use App\Core\View;
use App\Modules\Notifications\NotificationService;

class ComplianceProfileController
{
    public function index(): void
    {
        LocalAuth::require();
        View::render('complianceprofile/index', [
            'pageTitle' => 'Compliance-Profile',
            'profiles'  => ComplianceProfileService::profiles(),
            'current'   => (string)Config::getInstance()->get('compliance_profile', ''),
        ]);
    }

    public function apply(): void
    {
        LocalAuth::requireAdmin();
        $key = (string)($_POST['profile'] ?? '');
        $svc = app_service(ComplianceProfileService::class);
        $result = $svc->apply($key);

        if ($result['ok']) {
            Config::getInstance()->set('compliance_profile', $key);
            Session::flash('success', 'Compliance-Profil "' . $key . '" angewendet.');
            $ok = count($result['results']);
            NotificationService::push(
                'Compliance-Profil angewendet',
                'Profil "' . $key . '" mit ' . $ok . ' Hardening-Aktionen vollständig durchgelaufen.',
                'success',
                '/complianceprofile',
                'compliance'
            );
        } else {
            $failed = array_filter($result['results'], fn($r) => !$r['ok']);
            $failCount = count($failed);
            $okCount   = count($result['results']) - $failCount;
            Session::flash('error', 'Profil teilweise angewendet — ' . $okCount . ' OK, ' . $failCount . ' Fehler. Details siehe Audit-Log.');
            NotificationService::push(
                'Compliance-Profil teilweise angewendet',
                $okCount . ' Aktionen OK, ' . $failCount . ' fehlgeschlagen. Profil: ' . $key,
                'warn',
                '/complianceprofile',
                'compliance'
            );
        }
        AppAudit::log('compliance_profile_apply', 'complianceprofile',
            'Profil: ' . $key . ' — ' . json_encode($result['results'], JSON_UNESCAPED_UNICODE));

        Redirect::to('/complianceprofile');
    }
}
