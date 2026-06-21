<?php

namespace App\Modules\ConditionalAccess;

use App\Graph\GraphClient;

class ConditionalAccessService
{
    public function __construct(private GraphClient $graph) {}

    /**
     * Single source of truth for fetching all Conditional Access policies as
     * full objects (one shared cache key so the tenant is queried once). Several
     * modules (Security, SecurityPosture, Hardening, BreakGlass, TokenLifetime)
     * previously each fetched this endpoint with their own cache key.
     */
    public static function fetchAllPolicies(GraphClient $graph): array
    {
        try {
            $data = $graph->get(
                '/identity/conditionalAccess/policies',
                ['$top' => '200'],
                'ca_policies',
                900
            );
            return $data['value'] ?? [];
        } catch (\Throwable $e) {
            error_log('ConditionalAccess fetchAllPolicies: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Fetch all Conditional Access policies.
     */
    public function getPolicies(): array
    {
        return self::fetchAllPolicies($this->graph);
    }

    /** Returns the last Graph error, or null. */
    public function getLastError(): ?array
    {
        return $this->graph->getLastError();
    }

    /**
     * Create a new Conditional Access policy from a structured definition.
     *
     * @param array $def Keys: displayName, state, template, namedLocationId, excludeUserId,
     *                        mfaStrength, clientAppTypes
     */
    public function createPolicy(array $def): array
    {
        $state = $def['state'] ?? 'enabledForReportingButNotEnforced';

        $excludeUsers = [];
        if (!empty($def['excludeUserId'])) {
            $excludeUsers = array_filter(array_map('trim', explode(',', $def['excludeUserId'])));
        }

        $body = [
            'displayName' => $def['displayName'],
            'state'       => $state,
            'conditions'  => [],
            'grantControls' => null,
        ];

        $body['conditions']['users'] = [
            'includeUsers' => ['All'],
            'excludeUsers' => array_values($excludeUsers),
        ];

        switch ($def['template'] ?? 'custom') {
            case 'country_block':
                $body['conditions']['applications'] = ['includeApplications' => ['All']];
                $body['conditions']['locations'] = [
                    'includeLocations' => ['All'],
                    'excludeLocations' => [$def['namedLocationId']],
                ];
                $body['grantControls'] = ['operator' => 'OR', 'builtInControls' => ['block']];
                break;

            case 'mfa_all':
                $body['conditions']['applications'] = ['includeApplications' => ['All']];
                $body['grantControls'] = ['operator' => 'OR', 'builtInControls' => ['mfa']];
                break;

            case 'block_legacy':
                $body['conditions']['applications'] = ['includeApplications' => ['All']];
                $body['conditions']['clientAppTypes'] = ['exchangeActiveSync', 'other'];
                $body['grantControls'] = ['operator' => 'OR', 'builtInControls' => ['block']];
                break;
        }

        $result = $this->graph->post('/identity/conditionalAccess/policies', $body);
        $this->graph->getCache()->forget('ca_policies');
        return $result;
    }

    /**
     * Toggle policy state: enabled | disabled | enabledForReportingButNotEnforced.
     */
    public function toggleState(string $id, string $newState): void
    {
        $allowed = ['enabled', 'disabled', 'enabledForReportingButNotEnforced'];
        if (!in_array($newState, $allowed, true)) {
            throw new \InvalidArgumentException(t('Ungültiger Status:') . ' ' . $newState);
        }
        $this->graph->patch('/identity/conditionalAccess/policies/' . $id, ['state' => $newState]);
        $this->graph->getCache()->forget('ca_policies');
    }

    /**
     * Delete a Conditional Access policy by ID.
     */
    public function deletePolicy(string $id): void
    {
        $this->graph->delete('/identity/conditionalAccess/policies/' . $id);
        $this->graph->getCache()->forget('ca_policies');
    }

    /**
     * Analyse policies for common security gaps.
     * Returns an array of findings, each with: type (ok/warning/missing), title, detail.
     */
    public function analyseGaps(array $policies): array
    {
        $enabled     = array_filter($policies, fn($p) => ($p['state'] ?? '') === 'enabled');
        $reportOnly  = array_filter($policies, fn($p) => ($p['state'] ?? '') === 'enabledForReportingButNotEnforced');

        $findings = [];

        // ── Helper: does any enabled policy require MFA?
        $hasMfaGrant = function (array $set) {
            foreach ($set as $p) {
                $grants = $p['grantControls']['builtInControls'] ?? [];
                if (in_array('mfa', $grants, true)) return true;
                // strength-based (Authentication Strength)
                if (!empty($p['grantControls']['authenticationStrength'])) return true;
            }
            return false;
        };

        // ── 1. MFA for All Users
        $mfaForAll = array_filter($enabled, function ($p) {
            $users = $p['conditions']['users'] ?? [];
            $allIncluded = in_array('All', $users['includeUsers'] ?? [], true)
                        || in_array('All', $users['includeGroups'] ?? [], true);
            $grants = $p['grantControls']['builtInControls'] ?? [];
            $hasMfa = in_array('mfa', $grants, true) || !empty($p['grantControls']['authenticationStrength']);
            return $allIncluded && $hasMfa;
        });

        if (!empty($mfaForAll)) {
            $findings[] = ['type' => 'ok', 'category' => t('MFA'), 'title' => t('MFA für alle Benutzer'),
                'detail' => t('Mindestens eine aktive Richtlinie erzwingt MFA für alle Benutzer.')];
        } else {
            // Check report-only
            $mfaReportOnly = array_filter($reportOnly, function ($p) {
                $users = $p['conditions']['users'] ?? [];
                $allIncluded = in_array('All', $users['includeUsers'] ?? [], true);
                $grants = $p['grantControls']['builtInControls'] ?? [];
                return $allIncluded && in_array('mfa', $grants, true);
            });
            if (!empty($mfaReportOnly)) {
                $findings[] = ['type' => 'warning', 'category' => t('MFA'), 'title' => t('MFA für alle Benutzer (nur Report-Modus)'),
                    'detail' => t('Eine MFA-Richtlinie für alle Benutzer existiert, ist aber nur im Report-Modus. Zum Aktivieren wechseln.'), 'template' => 'mfa_all'];
            } else {
                $findings[] = ['type' => 'missing', 'category' => t('MFA'), 'title' => t('Keine MFA-Pflicht für alle Benutzer'),
                    'detail' => t('Keine aktive Richtlinie, die MFA für alle Benutzer erzwingt. Empfehlung: Richtlinie "Require MFA for all users" anlegen.'), 'template' => 'mfa_all'];
            }
        }

        // ── 2. MFA for Admins
        $adminRoles = [
            '62e90394-69f5-4237-9190-012177145e10', // Global Admin
            'e8611ab8-c189-46e8-94e1-60213ab1f814', // Privileged Auth Admin
            '194ae4cb-b126-40b2-bd5b-6091b380977d', // Security Admin
        ];
        $mfaForAdmins = array_filter($enabled, function ($p) use ($adminRoles) {
            $roles  = $p['conditions']['users']['includeRoles'] ?? [];
            $hasAdm = !empty(array_intersect($roles, $adminRoles));
            $grants = $p['grantControls']['builtInControls'] ?? [];
            $hasMfa = in_array('mfa', $grants, true) || !empty($p['grantControls']['authenticationStrength']);
            return $hasAdm && $hasMfa;
        });
        if (!empty($mfaForAdmins)) {
            $findings[] = ['type' => 'ok', 'category' => t('MFA'), 'title' => t('MFA für Administratoren'),
                'detail' => t('Mindestens eine Richtlinie erzwingt MFA für privilegierte Rollen.')];
        } else {
            $findings[] = ['type' => 'missing', 'category' => t('MFA'), 'title' => t('Keine MFA-Pflicht für Administratoren'),
                'detail' => t('Keine Richtlinie erzwingt MFA speziell für Admin-Rollen. Besonders kritisch — Admins sollten immer MFA verwenden.'), 'template' => 'mfa_all'];
        }

        // ── 3. Block Legacy Auth
        $blockLegacy = array_filter($enabled, function ($p) {
            $clients = $p['conditions']['clientAppTypes'] ?? [];
            $hasLegacy = in_array('exchangeActiveSync', $clients, true)
                      || in_array('other', $clients, true);
            $block = in_array('block', $p['grantControls']['builtInControls'] ?? [], true);
            return $hasLegacy && $block;
        });
        if (!empty($blockLegacy)) {
            $findings[] = ['type' => 'ok', 'category' => t('Sicherheit'), 'title' => t('Legacy-Authentifizierung blockiert'),
                'detail' => t('Eine aktive Richtlinie blockiert Legacy-Auth (Exchange ActiveSync / andere Clients).')];
        } else {
            $findings[] = ['type' => 'warning', 'category' => t('Sicherheit'), 'title' => t('Legacy-Authentifizierung nicht blockiert'),
                'detail' => t('Keine Richtlinie blockiert alte Protokolle (Basic Auth, IMAP, POP, SMTP AUTH). Diese umgehen MFA und sind ein häufiger Angriffsvektor.'), 'template' => 'block_legacy'];
        }

        // ── 4. Compliant Device / Hybrid Join required
        $deviceCompliant = array_filter($enabled, function ($p) {
            $grants = $p['grantControls']['builtInControls'] ?? [];
            return in_array('compliantDevice', $grants, true)
                || in_array('domainJoinedDevice', $grants, true);
        });
        if (!empty($deviceCompliant)) {
            $findings[] = ['type' => 'ok', 'category' => t('Gerät'), 'title' => t('Gerätecompliance oder Hybrid Join gefordert'),
                'detail' => t('Mindestens eine Richtlinie verlangt ein konformes oder Hybrid-verbundenes Gerät.')];
        } else {
            $findings[] = ['type' => 'warning', 'category' => t('Gerät'), 'title' => t('Keine Gerätecompliance-Anforderung'),
                'detail' => t('Kein Conditional Access fordert ein Intune-konformes Gerät. Empfehlung für sensible Apps/Daten.')];
        }

        // ── 5. Sign-in Risk (Requires Entra ID P2)
        $riskPolicy = array_filter($enabled, function ($p) {
            $levels = $p['conditions']['signInRiskLevels'] ?? [];
            return in_array('high', $levels, true) || in_array('medium', $levels, true);
        });
        if (!empty($riskPolicy)) {
            $findings[] = ['type' => 'ok', 'category' => t('Risiko'), 'title' => t('Risikobewertung bei Anmeldung aktiv'),
                'detail' => t('Sign-in Risk Policy vorhanden (erfordert Entra ID P2 / Microsoft 365 E5).')];
        } else {
            $findings[] = ['type' => 'warning', 'category' => t('Risiko'), 'title' => t('Keine Sign-in Risk Policy'),
                'detail' => t('Keine Richtlinie reagiert auf risikoreiche Anmeldungen. Mit Entra ID P2 ist Echtzeit-Risikoschutz möglich.')];
        }

        // ── 6. Country / Location block
        $countryBlock = array_filter($enabled, function ($p) {
            $locations = $p['conditions']['locations'] ?? [];
            $grants    = $p['grantControls']['builtInControls'] ?? [];
            $hasBlock  = in_array('block', $grants, true);
            $hasLoc    = !empty($locations['excludeLocations']) && in_array('All', $locations['includeLocations'] ?? [], true);
            return $hasBlock && $hasLoc;
        });
        if (!empty($countryBlock)) {
            $findings[] = ['type' => 'ok', 'category' => t('Standort'), 'title' => t('Länder-Blockierung aktiv'),
                'detail' => t('Mindestens eine Richtlinie blockiert Anmeldungen basierend auf dem geografischen Standort.')];
        } else {
            $findings[] = ['type' => 'warning', 'category' => t('Standort'), 'title' => t('Keine Länder-Blockierung'),
                'detail' => t('Keine Richtlinie beschränkt Anmeldungen auf bestimmte Länder. Empfohlen wenn Anmeldungen aus fremden Ländern unerwünscht sind.'), 'template' => 'country_block'];
        }

        // ── 7. No policy at all
        if (empty($policies)) {
            $findings = [['type' => 'missing', 'category' => t('Allgemein'), 'title' => t('Keine Conditional-Access-Richtlinien'),
                'detail' => t('Im Tenant sind keinerlei CA-Richtlinien konfiguriert. Zugriff auf Microsoft 365 ist ohne Einschränkungen möglich.'), 'template' => 'mfa_all']];
        }

        // Sort: missing first, warning second, ok last
        usort($findings, function ($a, $b) {
            $order = ['missing' => 0, 'warning' => 1, 'ok' => 2];
            return ($order[$a['type']] ?? 1) <=> ($order[$b['type']] ?? 1);
        });

        return $findings;
    }

    /**
     * Build a human-readable summary of users/groups/apps covered by a policy.
     */
    public function summariseConditions(array $policy): array
    {
        $cond = $policy['conditions'] ?? [];

        return [
            'users'       => $this->summariseUsers($cond['users'] ?? []),
            'apps'        => $this->summariseApps($cond['applications'] ?? []),
            'platforms'   => implode(', ', $cond['platforms']['includePlatforms'] ?? []) ?: t('Alle'),
            'locations'   => implode(', ', $cond['locations']['includeLocations'] ?? []) ?: t('Alle'),
            'clientTypes' => implode(', ', $cond['clientAppTypes'] ?? []) ?: t('Alle'),
            'grant'       => $this->summariseGrant($policy['grantControls'] ?? []),
            'session'     => $this->summariseSession($policy['sessionControls'] ?? []),
        ];
    }

    private function summariseUsers(array $users): string
    {
        $include = $users['includeUsers'] ?? [];
        if (in_array('All', $include, true)) return t('Alle Benutzer');
        if (in_array('GuestsOrExternalUsers', $include, true)) return t('Gäste / externe Benutzer');
        $roles  = count($users['includeRoles']  ?? []);
        $groups = count($users['includeGroups'] ?? []);
        $parts  = [];
        if ($roles)  $parts[] = t(':n Rollen', ['n' => $roles]);
        if ($groups) $parts[] = t(':n Gruppen', ['n' => $groups]);
        if (count($include)) $parts[] = t(':n Benutzer', ['n' => count($include)]);
        return implode(', ', $parts) ?: '–';
    }

    private function summariseApps(array $apps): string
    {
        $include = $apps['includeApplications'] ?? [];
        if (in_array('All', $include, true)) return t('Alle Cloud-Apps');
        if (in_array('Office365', $include, true)) return 'Office 365';
        return t(':n App(s)', ['n' => count($include)]);
    }

    private function summariseGrant(array $grant): string
    {
        $controls = $grant['builtInControls'] ?? [];
        if (in_array('block', $controls, true)) return t('Blockieren');
        $parts = [];
        if (in_array('mfa', $controls, true))                $parts[] = 'MFA';
        if (in_array('compliantDevice', $controls, true))    $parts[] = t('Konformes Gerät');
        if (in_array('domainJoinedDevice', $controls, true)) $parts[] = 'Hybrid Join';
        if (in_array('approvedApplication', $controls, true))$parts[] = t('Genehmigte App');
        if (!empty($grant['authenticationStrength']))         $parts[] = t('Auth-Stärke');
        if (empty($parts)) return t('Zugriff erlauben');
        return t('Erfordern:') . ' ' . implode(' + ', $parts);
    }

    private function summariseSession(array $session): string
    {
        $parts = [];
        if (!empty($session['signInFrequency']['value'])) {
            $parts[] = t('Sitzungshäufigkeit:') . ' ' . $session['signInFrequency']['value'] . ' ' . ($session['signInFrequency']['type'] ?? '');
        }
        if (!empty($session['persistentBrowser']['mode'])) {
            $parts[] = t('Persist. Browser:') . ' ' . $session['persistentBrowser']['mode'];
        }
        if (!empty($session['cloudAppSecurity'])) $parts[] = 'App Control';
        return implode(', ', $parts) ?: '–';
    }
}
