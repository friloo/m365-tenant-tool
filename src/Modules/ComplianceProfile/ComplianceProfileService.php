<?php

namespace App\Modules\ComplianceProfile;

use App\Graph\GraphClient;
use App\Modules\Hardening\HardeningService;

/**
 * Compliance-Profile bundles a set of hardening actions and recommended
 * settings into a "one-click preset" tailored for a specific industry's
 * regulatory baseline.
 *
 * The profile itself only describes intent — applying a profile loops
 * through its `actions` list and calls HardeningService::apply($id) for
 * each one, so the actual write paths to Graph stay in one place.
 *
 * Sources / regulations:
 *   - Healthcare       → KRITIS-Sektor "Gesundheit" + DSGVO Art. 32 + NIS-2
 *   - Finance          → BaFin BAIT/VAIT + DORA + DSGVO + MiFID II
 *   - Public Sector    → BSI IT-Grundschutz Baseline + DSGVO
 *   - Standard (KMU)   → DSGVO Mindestmaß + Microsoft Secure-Score Quick Wins
 */
class ComplianceProfileService
{
    private HardeningService $hardening;

    // Constructed by app_service(), which always injects GraphClient —
    // so we accept that and build the HardeningService ourselves.
    public function __construct(GraphClient $graph)
    {
        $this->hardening = new HardeningService($graph);
    }

    /**
     * Returns all known profiles as an associative array.
     *
     * @return array<string, array<string,mixed>>
     */
    public static function profiles(): array
    {
        return [
            'standard' => [
                'key'        => 'standard',
                'name'       => 'Standard / DSGVO-Basis',
                'icon'       => 'shield-check',
                'color'      => '#3b82f6',
                'short'      => 'Mindestmaß für jeden M365-Mandanten (DSGVO Art. 32, Secure-Score Quick Wins).',
                'regulations'=> ['DSGVO Art. 32', 'Microsoft Secure Score'],
                'actions'    => [
                    'block_legacy_auth',
                    'guest_invite_admins',
                    'guest_role_restricted',
                    'block_user_app_create',
                    'block_user_tenants',
                    'restrict_user_read',
                ],
                'note' => 'Universell sicher — empfohlen, wenn kein spezielles Branchen-Profil zutrifft.',
            ],
            'healthcare' => [
                'key'        => 'healthcare',
                'name'       => 'Gesundheitswesen (KRITIS)',
                'icon'       => 'heart-pulse',
                'color'      => '#dc2626',
                'short'      => 'Verschärfte Einstellungen für Praxen, Kliniken, MVZ und Sozialträger.',
                'regulations'=> ['KRITIS Gesundheit', 'DSGVO Art. 9 (Gesundheitsdaten)', 'NIS-2 Art. 21', 'SGB V § 75b'],
                'actions'    => [
                    'block_legacy_auth',
                    'guest_invite_admins',
                    'guest_role_restricted',
                    'block_user_app_create',
                    'block_user_secgroup',
                    'block_user_tenants',
                    'restrict_user_read',
                    'sp_sharing_strict',
                    'sp_onedrive_strict',
                    'sp_no_external_reshare',
                    'sp_anon_expiry_30',
                    'sp_default_internal',
                    'sp_idle_signout_on',
                ],
                'note' => 'Externes Sharing wird stark eingeschränkt. Patientendaten dürfen den Mandanten nur per gezielter Einladung verlassen.',
            ],
            'finance' => [
                'key'        => 'finance',
                'name'       => 'Finanzwesen (BaFin / DORA)',
                'icon'       => 'bank',
                'color'      => '#059669',
                'short'      => 'Banken, Versicherungen, Vermögensverwalter — BaFin BAIT, VAIT, DORA.',
                'regulations'=> ['BaFin BAIT/VAIT', 'DORA (EU 2022/2554)', 'DSGVO', 'MiFID II'],
                'actions'    => [
                    'block_legacy_auth',
                    'guest_invite_admins',
                    'guest_role_restricted',
                    'block_user_app_create',
                    'block_user_secgroup',
                    'block_user_tenants',
                    'restrict_user_read',
                    'sp_sharing_strict',
                    'sp_onedrive_strict',
                    'sp_no_external_reshare',
                    'sp_anon_expiry_30',
                    'sp_default_internal',
                    'sp_idle_signout_on',
                ],
                'note' => 'Auditierbarkeit aller Aktionen (audit_log) ist Pflicht — bitte zusätzlich Purview-Audit auf "alle Aktivitäten" konfigurieren.',
            ],
            'public' => [
                'key'        => 'public',
                'name'       => 'Öffentlicher Sektor / BSI',
                'icon'       => 'bank2',
                'color'      => '#7c3aed',
                'short'      => 'Behörden, Stadtwerke, Hochschulen — BSI IT-Grundschutz Baseline.',
                'regulations'=> ['BSI IT-Grundschutz', 'OZG', 'DSGVO', 'NIS-2 (Bund/Länder)'],
                'actions'    => [
                    'block_legacy_auth',
                    'guest_invite_admins',
                    'guest_role_restricted',
                    'block_user_app_create',
                    'block_user_secgroup',
                    'block_user_tenants',
                    'restrict_user_read',
                    'sp_sharing_strict',
                    'sp_no_external_reshare',
                    'sp_anon_expiry_30',
                    'sp_default_internal',
                ],
                'note' => 'Empfehlung: zusätzlich Customer Lockbox aktivieren (separater Schritt in /customerlockbox), um Microsoft-Support-Zugriffe explizit freigeben zu müssen.',
            ],
            'education' => [
                'key'        => 'education',
                'name'       => 'Bildung / Schulen',
                'icon'       => 'mortarboard',
                'color'      => '#0891b2',
                'short'      => 'Schulen und Bildungseinrichtungen — Schutz von Schülerdaten (DSGVO Art. 8).',
                'regulations'=> ['DSGVO Art. 8 (Kinder)', 'Landesdatenschutzgesetze'],
                'actions'    => [
                    'block_legacy_auth',
                    'guest_role_restricted',
                    'block_user_app_create',
                    'block_user_tenants',
                    'restrict_user_read',
                    'sp_anon_expiry_30',
                    'sp_default_internal',
                ],
                'note' => 'Externes Sharing ist häufig nötig (Elternkommunikation). Daher etwas lockerer als Healthcare/Finance, aber harte Defaults für Konto-Anlage und Anwendungen.',
            ],
        ];
    }

    /**
     * Apply a profile by running each HardeningService action in turn.
     * Each call is individually try/catch'd — one Graph failure must
     * never sink the whole cascade. We also try to raise the script
     * timeout, because healthcare/finance profiles run 13 PATCHes and
     * the shared-host default of 30 s can be tight.
     *
     * @return array{ok:bool, results: list<array{id:string, ok:bool, msg:string}>}
     */
    public function apply(string $profileKey): array
    {
        $profiles = self::profiles();
        if (!isset($profiles[$profileKey])) {
            return ['ok' => false, 'results' => [['id' => $profileKey, 'ok' => false, 'msg' => 'Unbekanntes Profil.']]];
        }
        @set_time_limit(180);

        $results = [];
        $okCount = 0;
        foreach ($profiles[$profileKey]['actions'] as $actionId) {
            try {
                $r = $this->hardening->apply($actionId);
                $ok  = (bool)($r['ok']  ?? false);
                $msg = (string)($r['msg'] ?? '');
            } catch (\Throwable $e) {
                $ok  = false;
                $msg = 'Ausnahme: ' . $e->getMessage();
            }
            $results[] = ['id' => $actionId, 'ok' => $ok, 'msg' => $msg];
            if ($ok) $okCount++;
        }
        return ['ok' => $okCount === count($results), 'results' => $results];
    }
}
