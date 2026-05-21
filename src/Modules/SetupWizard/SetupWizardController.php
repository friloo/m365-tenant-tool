<?php

namespace App\Modules\SetupWizard;

use App\Auth\LocalAuth;
use App\Core\AppAudit;
use App\Core\Config;
use App\Core\Redirect;
use App\Core\Session;
use App\Core\View;
use App\Modules\Settings\PermissionCheckerService;

/**
 * Setup-Wizard — a five-step assistant that greets first-time admins
 * and walks them through the minimum a fresh tenant needs:
 *
 *   1. Tenant-Verbindung      — credentials present + a Graph call works
 *   2. Berechtigungen          — required app-permissions granted
 *   3. Benachrichtigungen      — at least one recipient email
 *   4. Branding                — app name and (optional) logo set
 *   5. Compliance-Profil       — pick a hardening preset (or skip)
 *
 * Completion is stored as `setup_wizard_completed=1` in app_config so
 * the wizard never re-appears, but admins can re-run it from the
 * Settings page any time.
 */
class SetupWizardController
{
    private const TOTAL_STEPS = 5;

    public function index(): void
    {
        LocalAuth::requireAdmin();
        $step = max(1, min(self::TOTAL_STEPS, (int)($_GET['step'] ?? 1)));
        $data = $this->dataForStep($step);

        View::render('setupwizard/index', [
            'pageTitle' => 'Einrichtungs-Assistent',
            'step'      => $step,
            'totalSteps'=> self::TOTAL_STEPS,
            'stepData'  => $data,
            'allDone'   => self::isCompleted(),
        ]);
    }

    public function save(): void
    {
        LocalAuth::requireAdmin();
        $step = max(1, min(self::TOTAL_STEPS, (int)($_POST['step'] ?? 1)));
        $cfg  = Config::getInstance();

        if ($step === 3) {
            $rcpt = trim((string)($_POST['notification_recipients'] ?? ''));
            $cfg->set('notification_recipients', $rcpt);
        } elseif ($step === 4) {
            $name = trim((string)($_POST['app_name'] ?? ''));
            if ($name !== '') $cfg->set('app_name', $name);
            $logo = trim((string)($_POST['logo_url'] ?? ''));
            if ($logo !== '') $cfg->set('logo_url', $logo);
        }

        AppAudit::log('setup_wizard_step', 'setupwizard', "Schritt {$step} bestätigt");
        $next = $step + 1;
        if ($next > self::TOTAL_STEPS) {
            $cfg->set('setup_wizard_completed', '1');
            AppAudit::log('setup_wizard_done', 'setupwizard', 'Assistent abgeschlossen');
            Session::flash('success', 'Einrichtungs-Assistent abgeschlossen — viel Erfolg!');
            Redirect::to('/');
        }
        Redirect::to('/setup?step=' . $next);
    }

    public function reset(): void
    {
        LocalAuth::requireAdmin();
        Config::getInstance()->delete('setup_wizard_completed');
        Session::flash('success', 'Einrichtungs-Assistent zurückgesetzt.');
        Redirect::to('/setup');
    }

    /**
     * Public helper used by the routing layer to decide whether to
     * auto-redirect a fresh admin to the wizard on their first login.
     */
    public static function isCompleted(): bool
    {
        return (string)Config::getInstance()->get('setup_wizard_completed', '0') === '1';
    }

    // ── Per-step data collectors ────────────────────────────────────────────

    private function dataForStep(int $step): array
    {
        return match ($step) {
            1 => $this->checkTenantConnection(),
            2 => $this->checkPermissions(),
            3 => $this->loadRecipients(),
            4 => $this->loadBranding(),
            5 => $this->loadComplianceProfiles(),
            default => [],
        };
    }

    private function checkTenantConnection(): array
    {
        $cfg      = Config::getInstance();
        $tenantId = (string)$cfg->get('tenant_id', '');
        $clientId = (string)$cfg->get('client_id', '');
        $secret   = (string)$cfg->get('client_secret', '');

        $checks = [];
        $checks[] = $this->mkCheck(
            $tenantId !== '',
            'Tenant-ID gesetzt',
            $tenantId !== '' ? 'GUID des Microsoft-365-Mandanten ist hinterlegt.' : 'Keine Tenant-ID in den Einstellungen — bitte in /settings ergänzen.'
        );
        $checks[] = $this->mkCheck(
            $clientId !== '',
            'Client-ID gesetzt',
            $clientId !== '' ? 'App-Registrierung ist hinterlegt.' : 'Keine Client-ID — bitte in /settings ergänzen.'
        );
        $checks[] = $this->mkCheck(
            $secret !== '',
            'Client-Secret gesetzt',
            $secret !== '' ? 'Verschlüsselt gespeichert.' : 'Kein Client-Secret — bitte in /settings ergänzen.'
        );

        $tokenOk = false; $tokenMsg = 'Token-Abruf nicht möglich, weil Tenant-Daten fehlen.';
        if ($tenantId !== '' && $clientId !== '' && $secret !== '') {
            try {
                app_graph()->get('/organization?$select=id,displayName');
                $tokenOk  = true;
                $tokenMsg = 'Test-Aufruf an /organization erfolgreich.';
            } catch (\Throwable $e) {
                $tokenMsg = 'Test-Aufruf fehlgeschlagen: ' . $e->getMessage();
            }
        }
        $checks[] = $this->mkCheck($tokenOk, 'Graph-API erreichbar', $tokenMsg, $tokenOk ? 'ok' : 'fail');

        $allOk = !array_filter($checks, fn($c) => $c['status'] === 'fail');
        return ['checks' => $checks, 'all_ok' => $allOk, 'settings_url' => '/settings'];
    }

    private function checkPermissions(): array
    {
        try {
            /** @var PermissionCheckerService $svc */
            $svc     = app_service(PermissionCheckerService::class);
            $checked = $svc->checkPermissions();
            $summary = $svc->getSummary($checked);
            $missing = array_filter($checked, fn($r) => !$r['granted']);
            return [
                'summary'   => $summary,
                'missing'   => array_slice($missing, 0, 10, true),
                'all_count' => count($checked),
                'error'     => null,
            ];
        } catch (\Throwable $e) {
            return ['error' => $e->getMessage(), 'summary' => null, 'missing' => [], 'all_count' => 0];
        }
    }

    private function loadRecipients(): array
    {
        return [
            'value' => (string)Config::getInstance()->get('notification_recipients', ''),
        ];
    }

    private function loadBranding(): array
    {
        $cfg = Config::getInstance();
        return [
            'app_name' => (string)$cfg->get('app_name', 'M365 Tenant Tool'),
            'logo_url' => (string)$cfg->get('logo_url', ''),
        ];
    }

    private function loadComplianceProfiles(): array
    {
        return [
            'profiles' => \App\Modules\ComplianceProfile\ComplianceProfileService::profiles(),
        ];
    }

    private function mkCheck(bool $ok, string $title, string $body, ?string $forceStatus = null): array
    {
        return [
            'status' => $forceStatus ?? ($ok ? 'ok' : 'warn'),
            'title'  => $title,
            'body'   => $body,
        ];
    }
}
