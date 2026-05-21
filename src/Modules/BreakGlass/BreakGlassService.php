<?php

namespace App\Modules\BreakGlass;

use App\Core\Config;
use App\Graph\GraphClient;

/**
 * Break-Glass-Account-Monitoring. Diese Notfall-Accounts (typisch 2 Stück)
 * sind die letzte Eskalationsstufe wenn alle anderen Admin-Konten gesperrt
 * sind. Sie sollten:
 *
 * - permanent als Global Administrator zugewiesen sein
 * - mit einem starken, in einem Tresor gespeicherten Passwort
 * - durch genau EINE CA-Policy ausgeschlossen sein (sonst lockt man sich aus)
 * - regelmäßig getestet werden (sonst weiß man im Notfall nicht, ob sie noch gehen)
 *
 * Dieser Service prüft jeden konfigurierten Account und liefert eine
 * Statusübersicht.
 */
class BreakGlassService
{
    // Well-known Global Administrator Role ID
    private const ROLE_GLOBAL_ADMIN = '62e90394-69f5-4237-9190-012177145e10';

    public function __construct(private GraphClient $graph) {}

    /**
     * Liefert die Liste der konfigurierten Break-Glass-UPNs (CSV in app_config).
     * @return string[]
     */
    public function getConfiguredUpns(): array
    {
        $raw = (string)Config::getInstance()->get('break_glass_upns', '');
        if ($raw === '') return [];
        return array_values(array_filter(array_map(
            fn($u) => strtolower(trim($u)),
            explode(',', $raw)
        )));
    }

    /**
     * Health-Status pro konfiguriertem Break-Glass-Account.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getStatus(): array
    {
        $upns = $this->getConfiguredUpns();
        if (empty($upns)) return [];

        $result = [];
        foreach ($upns as $upn) {
            $result[] = $this->checkOne($upn);
        }
        return $result;
    }

    private function checkOne(string $upn): array
    {
        $entry = [
            'upn'                 => $upn,
            'exists'              => false,
            'id'                  => null,
            'displayName'         => null,
            'accountEnabled'      => null,
            'isGlobalAdmin'       => false,
            'mfaRegistered'       => null,
            'lastSignIn'          => null,
            'daysSinceSignIn'     => null,
            'passwordPolicies'    => null,
            'caExcluded'          => null,
            'issues'              => [],
        ];

        try {
            $user = $this->graph->get(
                '/users/' . rawurlencode($upn),
                ['$select' => 'id,displayName,userPrincipalName,accountEnabled,signInActivity,passwordPolicies'],
                null, 0
            );
        } catch (\Throwable $e) {
            $entry['issues'][] = 'Konto nicht gefunden: ' . $e->getMessage();
            return $entry;
        }
        if (empty($user['id'])) {
            $entry['issues'][] = 'Konto nicht gefunden im Tenant.';
            return $entry;
        }

        $entry['exists']           = true;
        $entry['id']               = $user['id'];
        $entry['displayName']      = $user['displayName'] ?? $upn;
        $entry['accountEnabled']   = (bool)($user['accountEnabled'] ?? false);
        $entry['passwordPolicies'] = $user['passwordPolicies'] ?? null;
        $last = $user['signInActivity']['lastSignInDateTime'] ?? null;
        if ($last) {
            $entry['lastSignIn']      = $last;
            $entry['daysSinceSignIn'] = (int)floor((time() - strtotime($last)) / 86400);
        }

        if (!$entry['accountEnabled']) {
            $entry['issues'][] = 'Konto ist deaktiviert — im Notfall nicht nutzbar!';
        }

        // Global Admin? Direct assignment prüfen
        try {
            $roleAssign = $this->graph->get(
                '/roleManagement/directory/roleAssignments',
                [
                    '$filter' => "principalId eq '{$user['id']}' and roleDefinitionId eq '" . self::ROLE_GLOBAL_ADMIN . "'",
                    '$top'    => '1',
                ],
                null, 0
            );
            $entry['isGlobalAdmin'] = !empty($roleAssign['value']);
        } catch (\Throwable) {}
        if (!$entry['isGlobalAdmin']) {
            $entry['issues'][] = 'Hat keine permanent Global-Administrator-Rolle (oder via PIM Eligible — das ist im Notfall problematisch, weil PIM-Aktivierung MFA verlangt).';
        }

        // MFA-Registration
        try {
            $authMethods = $this->graph->get(
                '/users/' . rawurlencode($upn) . '/authentication/methods',
                ['$select' => 'id'],
                null, 0
            );
            $methods = $authMethods['value'] ?? [];
            // password-only filtern
            $strong = array_filter($methods, fn($m) =>
                !empty($m['@odata.type']) && !str_contains($m['@odata.type'], 'password')
            );
            $entry['mfaRegistered'] = count($strong) > 0;
        } catch (\Throwable) {}

        // CA-Policy-Exclusion (für die App-Permission braucht's Policy.Read.All)
        try {
            $policies = $this->graph->get(
                '/identity/conditionalAccess/policies',
                ['$select' => 'id,displayName,state,conditions', '$top' => '200'],
                'ca_policies_for_bg',
                300
            );
            $excludedFrom = [];
            foreach ($policies['value'] ?? [] as $p) {
                if (($p['state'] ?? '') !== 'enabled') continue;
                $excludedUsers = $p['conditions']['users']['excludeUsers'] ?? [];
                if (in_array($user['id'], $excludedUsers, true)) {
                    $excludedFrom[] = $p['displayName'] ?? $p['id'];
                }
            }
            $entry['caExcluded'] = $excludedFrom;
            if (empty($excludedFrom)) {
                $entry['issues'][] = 'Account ist aus KEINER CA-Policy ausgeschlossen — Gefahr: bei einem CA-Fehler sperrst du dich aus.';
            }
        } catch (\Throwable) {
            $entry['caExcluded'] = null;
        }

        // Last sign-in
        if ($entry['daysSinceSignIn'] === null) {
            $entry['issues'][] = 'Account hat sich noch nie angemeldet — bitte einmal testen, ob er funktioniert.';
        } elseif ($entry['daysSinceSignIn'] > 180) {
            $entry['issues'][] = "Letzter Login vor {$entry['daysSinceSignIn']} Tagen — Account-Test wird empfohlen (≤ 180 Tage).";
        }

        return $entry;
    }
}
