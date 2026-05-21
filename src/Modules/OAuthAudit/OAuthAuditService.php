<?php

namespace App\Modules\OAuthAudit;

use App\Graph\GraphClient;

/**
 * Audit der OAuth-/Enterprise-Apps im Tenant. Service Principals mit
 * hoher Permission ohne aktuellen Sign-in sind ein bekannter Angriffs­
 * vektor (Tenant-Übernahme via verwaiste App-Registrierungen 2023/2024).
 */
class OAuthAuditService
{
    /**
     * Permissions, die wir als "high privilege" markieren — typische
     * Lese-/Schreib-Permissions auf den gesamten Tenant.
     */
    private const HIGH_PRIVILEGE = [
        'Mail.ReadWrite', 'Mail.ReadWrite.All', 'Mail.Send', 'Mail.Send.All',
        'Files.ReadWrite.All', 'Sites.ReadWrite.All', 'Sites.FullControl.All',
        'User.ReadWrite.All', 'Directory.ReadWrite.All', 'Directory.AccessAsUser.All',
        'Application.ReadWrite.All', 'AppRoleAssignment.ReadWrite.All',
        'RoleManagement.ReadWrite.Directory', 'Policy.ReadWrite.ConditionalAccess',
        'full_access_as_app',
    ];

    public function __construct(private GraphClient $graph) {}

    /**
     * Hauptscan: alle Enterprise Apps (ServicePrincipals) mit aggregierten
     * Metadaten — App-Permissions, Sign-in-Aktivität, Risk-Score.
     *
     * @return array<int, array<string, mixed>>
     */
    public function listEnterpriseApps(): array
    {
        try {
            $sps = $this->graph->paginate(
                '/servicePrincipals',
                [
                    '$select' => 'id,displayName,appId,servicePrincipalType,accountEnabled,'
                               . 'appRoleAssignmentRequired,signInAudience,createdDateTime,tags',
                    '$filter' => "servicePrincipalType eq 'Application'",
                    '$top'    => '200',
                ],
                20,
                'oauth_sps',
                3600
            );
        } catch (\Throwable $e) {
            error_log('OAuthAudit list: ' . $e->getMessage());
            return [];
        }

        // Sign-in-Aktivität pro App via aggregated report (letzte 30 Tage)
        $signInMap = $this->getSignInActivityMap();

        $result = [];
        foreach ($sps as $sp) {
            $appId = $sp['appId'] ?? '';
            $id    = $sp['id']    ?? '';
            if ($id === '') continue;

            // Microsoft-eigene Service Principals (gleiche Tenant-ID, intern)
            // sind weniger interessant — Tag "WindowsAzureActiveDirectoryIntegratedApp"
            // filtern wir trotzdem mit; aber Microsoft First-Party-Apps (z.B.
            // 'Microsoft Teams') werden meist als 'Application' und gehören
            // dazugehen — Admin sieht sie aber überfüllend. Wir markieren sie.
            $isMicrosoft = $this->looksLikeMicrosoftFirstParty($sp);

            // App-Rollen-Zuweisungen abrufen (= granted App-Permissions)
            $appRoles = $this->getAppRoleAssignments($id);
            $delegated = $this->getDelegatedPermissions($id);

            $allScopes    = array_merge($appRoles, $delegated);
            $highPriv     = array_filter($allScopes, fn($s) => $this->isHighPrivilege($s));
            $lastSignIn   = $signInMap[$appId] ?? null;
            $daysSince    = $lastSignIn ? (int)floor((time() - strtotime($lastSignIn)) / 86400) : null;

            $result[] = [
                'id'             => $id,
                'appId'          => $appId,
                'name'           => $sp['displayName'] ?? '(ohne Name)',
                'type'           => $sp['servicePrincipalType'] ?? '',
                'enabled'        => $sp['accountEnabled'] ?? true,
                'created'        => $sp['createdDateTime'] ?? null,
                'permissions_total'  => count($allScopes),
                'permissions_high'   => count($highPriv),
                'permissions_app'    => $appRoles,
                'permissions_user'   => $delegated,
                'high_privilege_perms' => array_values($highPriv),
                'last_sign_in'   => $lastSignIn,
                'days_since_signin'  => $daysSince,
                'unused'         => $daysSince === null || $daysSince > 90,
                'is_microsoft'   => $isMicrosoft,
                'risk_score'     => $this->riskScore(count($highPriv), $daysSince, $isMicrosoft),
            ];
        }

        // Höchstes Risiko zuerst
        usort($result, fn($a, $b) => $b['risk_score'] <=> $a['risk_score']);
        return $result;
    }

    public function getSummary(array $apps): array
    {
        $total = count($apps);
        $high  = count(array_filter($apps, fn($a) => $a['permissions_high'] > 0));
        $unused= count(array_filter($apps, fn($a) => $a['unused'] && !$a['is_microsoft']));
        $disabled = count(array_filter($apps, fn($a) => !$a['enabled']));
        $thirdParty = count(array_filter($apps, fn($a) => !$a['is_microsoft']));

        return [
            'total'         => $total,
            'third_party'   => $thirdParty,
            'high_priv'     => $high,
            'unused_90d'    => $unused,
            'disabled'      => $disabled,
        ];
    }

    // ── Private helpers ────────────────────────────────────────────────────

    private function getAppRoleAssignments(string $spId): array
    {
        try {
            $data = $this->graph->get(
                "/servicePrincipals/{$spId}/appRoleAssignments",
                ['$top' => '100'],
                null, 0
            );
            $list = $data['value'] ?? [];
            // appRoleId zum Permission-Namen mappen würde extra Lookups
            // brauchen — wir zeigen erstmal die appRoleId-GUID, die Liste
            // wird in der View durch eine Permission-Map verschönert.
            return array_map(fn($r) => (string)($r['appRoleId'] ?? ''), $list);
        } catch (\Throwable) {
            return [];
        }
    }

    private function getDelegatedPermissions(string $spId): array
    {
        try {
            $data = $this->graph->get(
                '/oauth2PermissionGrants',
                ['$filter' => "clientId eq '{$spId}'", '$top' => '100'],
                null, 0
            );
            $scopes = [];
            foreach ($data['value'] ?? [] as $grant) {
                $raw = trim($grant['scope'] ?? '');
                if ($raw === '') continue;
                foreach (preg_split('/\s+/', $raw) as $s) {
                    if ($s !== '') $scopes[] = $s;
                }
            }
            return array_values(array_unique($scopes));
        } catch (\Throwable) {
            return [];
        }
    }

    private function isHighPrivilege(string $scope): bool
    {
        // Permissions sind teils Strings ("Mail.ReadWrite") teils GUIDs
        // (appRole IDs). Strings vergleichen wir direkt; für GUIDs gibt
        // es derzeit keine Mapping-Tabelle hier, daher nur String-Match.
        foreach (self::HIGH_PRIVILEGE as $hp) {
            if (strcasecmp($scope, $hp) === 0) return true;
            if (str_contains($scope, '.ReadWrite.All')) return true;
            if (str_contains($scope, '.FullControl'))   return true;
        }
        return false;
    }

    private function getSignInActivityMap(): array
    {
        try {
            $data = $this->graph->paginate(
                '/reports/servicePrincipalSignInActivities',
                ['$top' => '999'],
                10,
                'oauth_sp_signins',
                3600
            );
            $map = [];
            foreach ($data as $row) {
                $appId = $row['appId'] ?? '';
                if ($appId === '') continue;
                $last = $row['lastSignInActivity']['lastSignInDateTime']
                     ?? $row['applicationAuthenticationClientSignInActivity']['lastSignInDateTime']
                     ?? null;
                if ($last) $map[$appId] = $last;
            }
            return $map;
        } catch (\Throwable) {
            return [];
        }
    }

    private function looksLikeMicrosoftFirstParty(array $sp): bool
    {
        $tags = $sp['tags'] ?? [];
        if (in_array('WindowsAzureActiveDirectoryIntegratedApp', $tags, true)) return false;
        // Microsoft-eigene Apps haben oft den AppOwnerOrganizationId aus
        // der Microsoft-Tenant — wir nutzen vereinfacht die Heuristik:
        // appId beginnt mit '00000003-…' oder Name beginnt mit 'Microsoft'/'Office'
        $name = strtolower($sp['displayName'] ?? '');
        return str_starts_with($name, 'microsoft ')
            || str_starts_with($name, 'office ')
            || str_starts_with($name, 'azure ')
            || str_starts_with($name, 'windows ')
            || $name === 'microsoft graph';
    }

    private function riskScore(int $highPriv, ?int $daysSince, bool $isMs): int
    {
        if ($isMs) return 0;                  // First-Party gilt als safe
        $score = $highPriv * 20;              // pro high-Permission +20
        if ($daysSince === null)       $score += 25;  // nie angemeldet
        elseif ($daysSince > 365)      $score += 30;
        elseif ($daysSince > 180)      $score += 15;
        elseif ($daysSince > 90)       $score += 5;
        return min(100, $score);
    }
}
