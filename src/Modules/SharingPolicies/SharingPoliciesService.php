<?php

namespace App\Modules\SharingPolicies;

use App\Graph\GraphClient;

class SharingPoliciesService
{
    public function __construct(private GraphClient $graph) {}

    // ── SharePoint / OneDrive tenant-level settings ──────────

    public function getSharePointSettings(): array
    {
        try {
            return $this->graph->get('/admin/sharepoint/settings', [], null);
        } catch (\Throwable $e) {
            return ['_error' => $e->getMessage()];
        }
    }

    public function updateSharePointSettings(array $data): void
    {
        $this->graph->patch('/admin/sharepoint/settings', $data);
    }

    // ── Teams tenant settings ────────────────────────────────

    public function getTeamsSettings(): array
    {
        try {
            return $this->graph->get('/teamwork', [], null);
        } catch (\Throwable $e) {
            return ['_error' => $e->getMessage()];
        }
    }

    // ── Cross-tenant / external access policy ────────────────

    public function getCrossTenantPolicy(): array
    {
        try {
            $policy = $this->graph->get('/policies/crossTenantAccessPolicy', [], null);
            $defaults = [];
            try {
                $defaults = $this->graph->get('/policies/crossTenantAccessPolicy/default', [], null);
            } catch (\Throwable) {}
            return ['policy' => $policy, 'defaults' => $defaults];
        } catch (\Throwable $e) {
            return ['_error' => $e->getMessage()];
        }
    }

    // ── Per-site sharing settings ────────────────────────────

    /**
     * Returns a list of SharePoint site collections with their sharingCapability.
     * sharingCapability: Disabled | ExistingExternalUserSharingOnly | ExternalUserSharingOnly | ExternalUserAndGuestSharing
     */
    public function getSitesSharingSettings(): array
    {
        try {
            $sites = $this->graph->paginate(
                '/sites',
                ['search' => '*', '$select' => 'id,displayName,webUrl,sharingCapability', '$top' => '50'],
                10
            );
            return $sites;
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Update a site's sharing capability.
     * $capability: Disabled | ExistingExternalUserSharingOnly | ExternalUserSharingOnly | ExternalUserAndGuestSharing
     */
    public function updateSiteSharing(string $siteId, string $capability): void
    {
        $this->graph->patch("/sites/{$siteId}", ['sharingCapability' => $capability]);
    }

    // ── Helper: human-readable labels ────────────────────────

    public static function sharingCapabilityLabel(string $capability): array
    {
        return match($capability) {
            'Disabled'                        => ['label' => 'Nur intern', 'class' => 'badge-secondary', 'icon' => 'lock-fill'],
            'ExistingExternalUserSharingOnly' => ['label' => 'Nur bestehende Gäste', 'class' => 'badge-warning', 'icon' => 'person-check'],
            'ExternalUserSharingOnly'         => ['label' => 'Neue & bestehende Gäste', 'class' => 'badge-info', 'icon' => 'person-plus'],
            'ExternalUserAndGuestSharing'     => ['label' => 'Alle (inkl. Anyone-Links)', 'class' => 'badge-danger', 'icon' => 'globe'],
            default                           => ['label' => $capability ?: 'Unbekannt', 'class' => '', 'icon' => 'question-circle'],
        };
    }

    public static function linkTypeLabel(string $type): string
    {
        return match($type) {
            'anonymous'    => '🌐 Jeder mit dem Link (anonym)',
            'direct'       => '👤 Nur eingeladen Personen',
            'organization' => '🏢 Personen in der Organisation',
            default        => $type ?: '—',
        };
    }

    public static function permissionLabel(string $perm): string
    {
        return match($perm) {
            'view' => '👁 Anzeigen',
            'edit' => '✏️ Bearbeiten',
            default => $perm ?: '—',
        };
    }
}
