<?php

namespace App\Modules\ComplianceProfile;

use App\Auth\LocalAuth;
use App\Core\AppAudit;
use App\Core\Config;
use App\Core\Redirect;
use App\Core\Session;
use App\Core\View;
use App\Modules\Hardening\HardeningService;
use App\Modules\Notifications\NotificationService;

class ComplianceProfileController
{
    public function index(): void
    {
        LocalAuth::require();
        View::render('complianceprofile/index', [
            'pageTitle' => t('Compliance-Profile'),
            'profiles'  => ComplianceProfileService::profiles(),
            'current'   => (string)Config::getInstance()->get('compliance_profile', ''),
        ]);
    }

    /**
     * Legacy synchronous apply — still callable, but for healthcare/finance
     * profiles (13 actions) the cumulative Graph latency can crash long
     * before the response comes back. Falls back to applyStep() under the
     * hood by looping all actions and aggregating. The UI now uses
     * applyStep() directly via JavaScript so the user sees per-action
     * progress and nothing can time out.
     */
    public function apply(): void
    {
        LocalAuth::requireAdmin();
        $key = (string)($_POST['profile'] ?? '');
        $svc = app_service(ComplianceProfileService::class);
        $result = $svc->apply($key);

        if ($result['ok']) {
            Config::getInstance()->set('compliance_profile', $key);
            Session::flash('success', t('Compliance-Profil ":key" angewendet.', ['key' => $key]));
            $ok = count($result['results']);
            NotificationService::push(
                t('Compliance-Profil angewendet'),
                t('Profil ":key" mit :count Hardening-Aktionen vollständig durchgelaufen.', ['key' => $key, 'count' => $ok]),
                'success',
                '/complianceprofile',
                'compliance'
            );
        } else {
            $failed = array_filter($result['results'], fn($r) => !$r['ok']);
            $failCount = count($failed);
            $okCount   = count($result['results']) - $failCount;
            Session::flash('error', t('Profil teilweise angewendet — :ok OK, :fail Fehler. Details siehe Audit-Log.', ['ok' => $okCount, 'fail' => $failCount]));
            NotificationService::push(
                t('Compliance-Profil teilweise angewendet'),
                t(':ok Aktionen OK, :fail fehlgeschlagen. Profil: :key', ['ok' => $okCount, 'fail' => $failCount, 'key' => $key]),
                'warn',
                '/complianceprofile',
                'compliance'
            );
        }
        AppAudit::log('compliance_profile_apply', 'complianceprofile',
            t('Profil: :key — :results', ['key' => $key, 'results' => json_encode($result['results'], JSON_UNESCAPED_UNICODE)]));

        Redirect::to('/complianceprofile');
    }

    /**
     * AJAX-style single-action endpoint. Returns JSON. The UI calls this
     * once per action in the profile, in sequence, so the user sees
     * progress and individual Graph slowness never piles up into a
     * 60+-second blocked request that web-server worker pools tend to
     * sever (= ERR_CONNECTION_CLOSED in the browser).
     *
     * On the last action it also stamps `compliance_profile` in config
     * and pushes the summary notification.
     */
    public function applyStep(): void
    {
        LocalAuth::requireAdmin();
        @set_time_limit(60);
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');

        $profileKey = (string)($_POST['profile']   ?? '');
        $actionId   = (string)($_POST['action_id'] ?? '');
        $index      = (int)($_POST['index']        ?? 0);
        $isLast     = !empty($_POST['final']);

        $profiles = ComplianceProfileService::profiles();
        if (!isset($profiles[$profileKey])) {
            echo json_encode(['ok' => false, 'msg' => t('Unbekanntes Profil.')]);
            return;
        }
        if (!in_array($actionId, $profiles[$profileKey]['actions'], true)) {
            echo json_encode(['ok' => false, 'msg' => t('Aktion ":action" gehört nicht zum Profil :profile.', ['action' => $actionId, 'profile' => $profileKey])]);
            return;
        }

        try {
            /** @var HardeningService $hs */
            $hs     = app_service(HardeningService::class);
            $result = $hs->apply($actionId);
            $ok     = (bool)($result['ok']  ?? false);
            $msg    = (string)($result['msg'] ?? '');
        } catch (\Throwable $e) {
            $ok  = false;
            $msg = t('Ausnahme: :msg', ['msg' => $e->getMessage()]);
        }

        AppAudit::log('compliance_profile_step', 'complianceprofile',
            t('Profil :profile · Schritt :step · :action · :status · :msg', [
                'profile' => $profileKey,
                'step'    => $index + 1,
                'action'  => $actionId,
                'status'  => $ok ? 'OK' : 'FEHLER',
                'msg'     => $msg,
            ]));

        if ($isLast) {
            Config::getInstance()->set('compliance_profile', $profileKey);
            NotificationService::push(
                t('Compliance-Profil angewendet'),
                t('Profil ":name" durchgelaufen.', ['name' => ($profiles[$profileKey]['name'] ?? $profileKey)]),
                'success',
                '/complianceprofile',
                'compliance'
            );
        }

        echo json_encode([
            'ok'        => $ok,
            'msg'       => $msg,
            'action_id' => $actionId,
            'index'     => $index,
        ]);
    }
}
