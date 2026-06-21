<?php

namespace App\Modules\AuditReport;

use App\Auth\LocalAuth;
use App\Core\AppAudit;
use App\Core\Config;
use App\Core\View;
use App\Modules\Hardening\HardeningService;
use App\Modules\Settings\PermissionCheckerService;

/**
 * One-click "Show me an audit-ready summary of this tenant" report
 * organised along the structure of DSGVO Art. 32 and NIS-2 Art. 21.
 * The page is rendered as a normal HTML view so the user can either
 * read it on screen or use the browser's Print → "Save as PDF" path
 * (already exposed by the print button in the topbar).
 */
class AuditReportController
{
    public function index(): void
    {
        LocalAuth::require();

        $hardening   = $this->loadHardeningGrouped();
        $permissions = $this->loadPermissionsSummary();
        $tenantInfo  = $this->loadTenantInfo();

        AppAudit::log('audit_report_view', 'auditreport', 'DSGVO/NIS-2 Audit-Report angezeigt');

        View::render('auditreport/index', [
            'pageTitle'   => t('DSGVO / NIS-2 Audit-Report'),
            'tenantInfo'  => $tenantInfo,
            'hardening'   => $hardening,
            'permissions' => $permissions,
            'profile'     => (string)Config::getInstance()->get('compliance_profile', ''),
            'generatedAt' => date('d.m.Y H:i'),
            'appName'     => Config::getInstance()->get('app_name', 'M365 Tenant Tool'),
            'user'        => LocalAuth::username(),
            'articles'    => $this->articleMapping($hardening),
        ]);
    }

    private function loadTenantInfo(): array
    {
        try {
            $r = app_graph()->get('/organization', ['$select' => 'id,displayName,verifiedDomains,countryLetterCode,createdDateTime'], 'audit_org', 3600);
            $first = $r['value'][0] ?? [];
            $domains = array_map(fn($d) => $d['name'] ?? '', $first['verifiedDomains'] ?? []);
            return [
                'name'    => $first['displayName'] ?? '—',
                'id'      => $first['id'] ?? '—',
                'country' => $first['countryLetterCode'] ?? '—',
                'created' => $first['createdDateTime'] ?? '—',
                'domains' => array_filter($domains),
            ];
        } catch (\Throwable) {
            return ['name' => '—', 'id' => '—', 'country' => '—', 'created' => '—', 'domains' => []];
        }
    }

    private function loadHardeningGrouped(): array
    {
        try {
            $items = app_service(HardeningService::class)->getItems();
            $grouped = [];
            foreach ($items as $i) $grouped[$i['category'] ?? 'Sonstiges'][] = $i;
            return $grouped;
        } catch (\Throwable) { return []; }
    }

    private function loadPermissionsSummary(): array
    {
        try {
            /** @var PermissionCheckerService $svc */
            $svc     = app_service(PermissionCheckerService::class);
            $checked = $svc->checkPermissions();
            return $svc->getSummary($checked);
        } catch (\Throwable $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Maps each DSGVO Art. 32 / NIS-2 Art. 21 requirement to the
     * concrete tenant settings the report should display. Used by the
     * view to render the regulation-organised section.
     */
    private function articleMapping(array $hardening): array
    {
        $byId = [];
        foreach ($hardening as $cat => $items) {
            foreach ($items as $it) $byId[$it['id']] = $it;
        }
        $sel = fn(array $ids) => array_values(array_filter(array_map(fn($i) => $byId[$i] ?? null, $ids)));

        return [
            [
                'art'  => 'DSGVO Art. 32 Abs. 1 lit. b',
                'name' => t('Vertraulichkeit, Integrität, Verfügbarkeit'),
                'desc' => t('Technische Maßnahmen gegen unautorisierten Zugriff auf personenbezogene Daten.'),
                'items'=> $sel(['security_defaults','block_legacy_auth','sp_sharing','sp_anon_expiry','sp_default_link','sp_onedrive_sharing','sp_external_reshare','sp_idle_signout']),
            ],
            [
                'art'  => 'DSGVO Art. 32 Abs. 1 lit. d',
                'name' => t('Regelmäßige Überprüfung'),
                'desc' => t('Verfahren zur regelmäßigen Bewertung der Wirksamkeit der Sicherheitsmaßnahmen.'),
                'items'=> $sel(['audit_log','pim_roles','app_consent']),
            ],
            [
                'art'  => 'DSGVO Art. 25',
                'name' => t('Datenschutz durch Technikgestaltung'),
                'desc' => t('Privacy by Design — Standards, die Daten von Anfang an schützen.'),
                'items'=> $sel(['guest_invite','guest_user_role','restrict_user_read','block_user_app','block_user_secgroup','block_user_tenants']),
            ],
            [
                'art'  => 'NIS-2 Art. 21 Abs. 2 lit. i',
                'name' => t('Zugriffskontrolle & MFA'),
                'desc' => t('Pflicht zur Multifaktor-Authentifizierung und sicheren Anmeldeverfahren.'),
                'items'=> $sel(['security_defaults','mfa_all','block_legacy_auth','pim_roles']),
            ],
            [
                'art'  => 'NIS-2 Art. 21 Abs. 2 lit. e',
                'name' => t('Lieferkettensicherheit'),
                'desc' => t('OAuth-Apps und Gast-Berechtigungen müssen kontrolliert werden.'),
                'items'=> $sel(['app_consent','guest_invite','guest_user_role']),
            ],
            [
                'art'  => 'NIS-2 Art. 21 Abs. 2 lit. h',
                'name' => t('Krypto & Mail-Sicherheit'),
                'desc' => t('Sichere Übertragung und Phishing-Abwehr per Defender for Office 365.'),
                'items'=> $sel(['defender_safe_links','external_sender_id']),
            ],
            [
                'art'  => 'BSI ORP.4.A23',
                'name' => t('Regelung des Passwortgebrauchs'),
                'desc' => t('Sichere Authentifizierungsmethoden, keine Legacy-Protokolle.'),
                'items'=> $sel(['block_legacy_auth','security_defaults','mfa_all']),
            ],
        ];
    }
}
