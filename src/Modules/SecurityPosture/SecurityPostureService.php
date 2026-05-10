<?php

namespace App\Modules\SecurityPosture;

use App\Graph\GraphClient;

class SecurityPostureService
{
    public function __construct(private GraphClient $graph) {}

    /**
     * Run all security posture checks and return an array of check results.
     *
     * @return array<int, array{id: string, category: string, label: string, description: string, status: string, detail: string, severity: string}>
     */
    public function runChecks(): array
    {
        return [
            $this->checkCaMfaPolicy(),
            $this->checkLegacyAuthBlocked(),
            $this->checkAdminMfaCoverage(),
            $this->checkRiskyUsersOpen(),
            $this->checkSecureScoreAbove50(),
            $this->checkNoStaleLicensed(),
            $this->checkGuestUsersReviewed(),
            $this->checkAppSecretsExpiry(),
        ];
    }

    /**
     * Compute the posture score from the checks array.
     *
     * @param  array $checks  Array returned by runChecks()
     * @return array{passed: int, total: int, percent: int}
     */
    public function getScore(array $checks): array
    {
        $passed = count(array_filter($checks, fn($c) => $c['status'] === 'pass'));
        $total  = count(array_filter($checks, fn($c) => $c['status'] !== 'unknown'));
        return [
            'passed'  => $passed,
            'total'   => $total,
            'percent' => $total > 0 ? round($passed / $total * 100) : 0,
        ];
    }

    // -------------------------------------------------------------------------
    // Individual checks
    // -------------------------------------------------------------------------

    /**
     * Check 1: CA policy requiring MFA exists and is enabled.
     */
    private function checkCaMfaPolicy(): array
    {
        $base = [
            'id'          => 'ca_mfa_policy',
            'category'    => 'Identität',
            'label'       => 'MFA-Richtlinie (CA)',
            'description' => 'Mindestens eine aktive Conditional-Access-Richtlinie erzwingt MFA.',
            'severity'    => 'high',
        ];

        try {
            $data     = $this->graph->get('/identity/conditionalAccessPolicies', [], 'ca_policies', 900);
            $policies = $data['value'] ?? [];

            foreach ($policies as $policy) {
                if (strtolower($policy['state'] ?? '') !== 'enabled') {
                    continue;
                }
                $grant    = $policy['grantControls'] ?? [];
                $controls = array_map('strtolower', (array)($grant['builtInControls'] ?? []));
                if (in_array('mfa', $controls, true)) {
                    return array_merge($base, [
                        'status' => 'pass',
                        'detail' => 'Eine aktive CA-Richtlinie mit MFA-Anforderung gefunden.',
                    ]);
                }
            }

            return array_merge($base, [
                'status' => 'fail',
                'detail' => 'Keine aktive CA-Richtlinie mit MFA-Anforderung gefunden.',
            ]);
        } catch (\Throwable) {
            return array_merge($base, [
                'status' => 'unknown',
                'detail' => 'Berechtigung nicht verfügbar oder API-Fehler.',
            ]);
        }
    }

    /**
     * Check 2: Legacy authentication is blocked by a CA policy.
     */
    private function checkLegacyAuthBlocked(): array
    {
        $base = [
            'id'          => 'legacy_auth_blocked',
            'category'    => 'Identität',
            'label'       => 'Legacy-Authentifizierung blockiert',
            'description' => 'Eine CA-Richtlinie blockiert ältere Authentifizierungsprotokolle.',
            'severity'    => 'high',
        ];

        try {
            $data     = $this->graph->get('/identity/conditionalAccessPolicies', [], 'ca_policies', 900);
            $policies = $data['value'] ?? [];

            $foundEnabled  = false;
            $foundDisabled = false;

            foreach ($policies as $policy) {
                $state       = strtolower($policy['state'] ?? '');
                $conditions  = $policy['conditions'] ?? [];
                $clientTypes = array_map('strtolower', (array)($conditions['clientAppTypes'] ?? []));

                $blocksLegacy = (in_array('exchangeactivesync', $clientTypes, true) || in_array('other', $clientTypes, true));
                if (!$blocksLegacy) {
                    continue;
                }

                $grant   = $policy['grantControls'] ?? [];
                $isBlock = strtolower($grant['operator'] ?? '') === 'or'
                    ? in_array('block', array_map('strtolower', (array)($grant['builtInControls'] ?? [])), true)
                    : in_array('block', array_map('strtolower', (array)($grant['builtInControls'] ?? [])), true);

                if (!$isBlock) {
                    continue;
                }

                if ($state === 'enabled') {
                    $foundEnabled = true;
                    break;
                } elseif ($state === 'enabledforreportingbutnotenforced') {
                    $foundDisabled = true;
                }
            }

            if ($foundEnabled) {
                return array_merge($base, [
                    'status' => 'pass',
                    'detail' => 'Aktive CA-Richtlinie blockiert Legacy-Authentifizierung.',
                ]);
            }
            if ($foundDisabled) {
                return array_merge($base, [
                    'status' => 'warn',
                    'detail' => 'Legacy-Auth-Block-Richtlinie vorhanden, aber nur im Report-Modus.',
                ]);
            }

            return array_merge($base, [
                'status' => 'fail',
                'detail' => 'Keine CA-Richtlinie blockiert Legacy-Authentifizierung.',
            ]);
        } catch (\Throwable) {
            return array_merge($base, [
                'status' => 'unknown',
                'detail' => 'Berechtigung nicht verfügbar oder API-Fehler.',
            ]);
        }
    }

    /**
     * Check 3: MFA registration rate is >95%.
     */
    private function checkAdminMfaCoverage(): array
    {
        $base = [
            'id'          => 'admin_mfa_coverage',
            'category'    => 'Identität',
            'label'       => 'MFA-Registrierungsrate >95%',
            'description' => 'Mindestens 95% aller Benutzer haben eine MFA-Methode registriert.',
            'severity'    => 'high',
        ];

        try {
            $users = $this->graph->paginate(
                '/reports/authenticationMethods/userRegistrationDetails',
                [
                    '$select' => 'id,userPrincipalName,isMfaRegistered',
                    '$top'    => '999',
                ],
                50,
                'mfa_methods_detail',
                1800
            );

            $total      = count($users);
            $registered = count(array_filter($users, fn($u) => $u['isMfaRegistered'] ?? false));
            $rate       = $total > 0 ? round($registered / $total * 100, 1) : 0;

            if ($rate >= 95) {
                return array_merge($base, [
                    'status' => 'pass',
                    'detail' => "{$registered} von {$total} Benutzern haben MFA registriert ({$rate}%).",
                ]);
            }
            if ($rate >= 75) {
                return array_merge($base, [
                    'status' => 'warn',
                    'detail' => "MFA-Registrierungsrate beträgt nur {$rate}% ({$registered}/{$total}).",
                ]);
            }

            return array_merge($base, [
                'status' => 'fail',
                'detail' => "Niedrige MFA-Registrierungsrate: {$rate}% ({$registered}/{$total}).",
            ]);
        } catch (\Throwable) {
            return array_merge($base, [
                'status' => 'unknown',
                'detail' => 'Berechtigung nicht verfügbar oder API-Fehler.',
            ]);
        }
    }

    /**
     * Check 4: No (or few) risky users currently at risk.
     */
    private function checkRiskyUsersOpen(): array
    {
        $base = [
            'id'          => 'risky_users_open',
            'category'    => 'Identität',
            'label'       => 'Risikobenutzer überwacht',
            'description' => 'Anzahl der Benutzer mit aktivem Risikostate (atRisk).',
            'severity'    => 'medium',
        ];

        try {
            $data  = $this->graph->get(
                '/identityProtection/riskyUsers',
                [
                    '$filter' => "riskState eq 'atRisk'",
                    '$top'    => '1',
                ],
                'risky_users_atrisk_count',
                300
            );

            // Graph returns a count via @odata.count if we request it, but we only
            // fetch $top=1 and check if there are results. For an accurate count
            // we rely on the value array length in conjunction with @odata.count if present.
            $count = (int)($data['@odata.count'] ?? count($data['value'] ?? []));

            // If count header not present, fetch a larger set for accuracy
            if (!isset($data['@odata.count'])) {
                $allData = $this->graph->get(
                    '/identityProtection/riskyUsers',
                    [
                        '$filter' => "riskState eq 'atRisk'",
                        '$select' => 'id',
                        '$top'    => '100',
                    ],
                    'risky_users_atrisk_list',
                    300
                );
                $count = count($allData['value'] ?? []);
            }

            if ($count === 0) {
                return array_merge($base, [
                    'status' => 'pass',
                    'detail' => 'Keine Benutzer mit aktivem Risikostate gefunden.',
                ]);
            }
            if ($count <= 5) {
                return array_merge($base, [
                    'status' => 'warn',
                    'detail' => "{$count} Benutzer mit aktivem Risikostate — Überprüfung empfohlen.",
                ]);
            }

            return array_merge($base, [
                'status' => 'fail',
                'detail' => "{$count} Benutzer mit aktivem Risikostate erfordern Aufmerksamkeit.",
            ]);
        } catch (\Throwable) {
            return array_merge($base, [
                'status' => 'unknown',
                'detail' => 'Berechtigung nicht verfügbar oder API-Fehler.',
            ]);
        }
    }

    /**
     * Check 5: Secure Score is above 50% of maximum.
     */
    private function checkSecureScoreAbove50(): array
    {
        $base = [
            'id'          => 'secure_score_above_50',
            'category'    => 'Identität',
            'label'       => 'Secure Score >50%',
            'description' => 'Microsoft Secure Score liegt über 50% des erreichbaren Maximums.',
            'severity'    => 'medium',
        ];

        try {
            $data  = $this->graph->get(
                '/security/secureScores',
                ['$top' => '1'],
                'securescore_latest',
                1800
            );
            $items = $data['value'] ?? [];

            if (empty($items)) {
                return array_merge($base, [
                    'status' => 'unknown',
                    'detail' => 'Keine Secure-Score-Daten verfügbar.',
                ]);
            }

            $current = (float)($items[0]['currentScore'] ?? 0);
            $max     = (float)($items[0]['maxScore']     ?? 0);
            $ratio   = $max > 0 ? $current / $max : 0;
            $pct     = round($ratio * 100, 1);

            if ($ratio > 0.5) {
                return array_merge($base, [
                    'status' => 'pass',
                    'detail' => "Secure Score: {$current}/{$max} ({$pct}%).",
                ]);
            }
            if ($ratio >= 0.3) {
                return array_merge($base, [
                    'status' => 'warn',
                    'detail' => "Secure Score: {$current}/{$max} ({$pct}%) — Verbesserungspotenzial.",
                ]);
            }

            return array_merge($base, [
                'status' => 'fail',
                'detail' => "Niedriger Secure Score: {$current}/{$max} ({$pct}%).",
            ]);
        } catch (\Throwable) {
            return array_merge($base, [
                'status' => 'unknown',
                'detail' => 'Berechtigung nicht verfügbar oder API-Fehler.',
            ]);
        }
    }

    /**
     * Check 6: No enabled licensed users with sign-in inactive >90 days.
     */
    private function checkNoStaleLicensed(): array
    {
        $base = [
            'id'          => 'no_stale_licensed',
            'category'    => 'Daten',
            'label'       => 'Keine veralteten lizenzierten Benutzer',
            'description' => 'Aktivierte, lizenzierte Konten ohne Anmeldung in den letzten 90 Tagen.',
            'severity'    => 'low',
        ];

        try {
            $users  = $this->graph->paginate(
                '/users',
                [
                    '$select' => 'id,displayName,userPrincipalName,accountEnabled,assignedLicenses,signInActivity,createdDateTime,jobTitle,department,mail',
                    '$top'    => '999',
                ],
                50,
                'users_all',
                900
            );

            $cutoff = strtotime('-90 days');
            $stale  = 0;

            foreach ($users as $user) {
                if (!($user['accountEnabled'] ?? false)) {
                    continue;
                }
                if (empty($user['assignedLicenses'])) {
                    continue;
                }

                $lastSignIn = $user['signInActivity']['lastSignInDateTime'] ?? null;
                if ($lastSignIn === null) {
                    // Never signed in — created before cutoff counts as stale
                    $created = $user['createdDateTime'] ?? null;
                    if ($created && strtotime($created) < $cutoff) {
                        $stale++;
                    }
                    continue;
                }
                if (strtotime($lastSignIn) < $cutoff) {
                    $stale++;
                }
            }

            if ($stale === 0) {
                return array_merge($base, [
                    'status' => 'pass',
                    'detail' => 'Keine veralteten lizenzierten Benutzerkonten gefunden.',
                ]);
            }
            if ($stale <= 5) {
                return array_merge($base, [
                    'status' => 'warn',
                    'detail' => "{$stale} lizenzierte Benutzer ohne Anmeldung in 90+ Tagen.",
                ]);
            }

            return array_merge($base, [
                'status' => 'fail',
                'detail' => "{$stale} lizenzierte Benutzer ohne Anmeldung in 90+ Tagen.",
            ]);
        } catch (\Throwable) {
            return array_merge($base, [
                'status' => 'unknown',
                'detail' => 'Berechtigung nicht verfügbar oder API-Fehler.',
            ]);
        }
    }

    /**
     * Check 7: Number of enabled guest users is reviewed / kept low.
     */
    private function checkGuestUsersReviewed(): array
    {
        $base = [
            'id'          => 'guest_users_reviewed',
            'category'    => 'Identität',
            'label'       => 'Gastbenutzer überprüft',
            'description' => 'Anzahl der aktiven Gastbenutzer im Tenant.',
            'severity'    => 'low',
        ];

        try {
            $data   = $this->graph->get(
                '/users',
                [
                    '$filter' => "userType eq 'Guest' and accountEnabled eq true",
                    '$select' => 'id',
                    '$top'    => '100',
                ],
                'guest_users_enabled',
                900
            );
            $guests = count($data['value'] ?? []);

            if ($guests <= 5) {
                return array_merge($base, [
                    'status' => 'pass',
                    'detail' => "{$guests} aktive Gastbenutzer — unkritisch.",
                ]);
            }
            if ($guests <= 20) {
                return array_merge($base, [
                    'status' => 'warn',
                    'detail' => "{$guests} aktive Gastbenutzer — regelmäßige Überprüfung empfohlen.",
                ]);
            }

            return array_merge($base, [
                'status' => 'fail',
                'detail' => "{$guests} aktive Gastbenutzer — Überprüfung und Bereinigung erforderlich.",
            ]);
        } catch (\Throwable) {
            return array_merge($base, [
                'status' => 'unknown',
                'detail' => 'Berechtigung nicht verfügbar oder API-Fehler.',
            ]);
        }
    }

    /**
     * Check 8: No app secrets are expired or expiring within 30 days.
     */
    private function checkAppSecretsExpiry(): array
    {
        $base = [
            'id'          => 'app_secrets_expiry',
            'category'    => 'Apps',
            'label'       => 'App-Secrets nicht abgelaufen',
            'description' => 'Prüft, ob App-Registrierungen abgelaufene oder bald ablaufende Secrets haben.',
            'severity'    => 'medium',
        ];

        try {
            $data = $this->graph->get(
                '/applications',
                [
                    '$select' => 'id,displayName,passwordCredentials',
                    '$top'    => '100',
                ],
                'applications_secrets',
                900
            );
            $apps = $data['value'] ?? [];

            $expired    = 0;
            $expiringSoon = 0;
            $threshold  = strtotime('+30 days');
            $now        = time();

            foreach ($apps as $app) {
                foreach ((array)($app['passwordCredentials'] ?? []) as $cred) {
                    $endDate = $cred['endDateTime'] ?? null;
                    if ($endDate === null) {
                        continue;
                    }
                    $ts = strtotime($endDate);
                    if ($ts < $now) {
                        $expired++;
                    } elseif ($ts < $threshold) {
                        $expiringSoon++;
                    }
                }
            }

            if ($expired > 0) {
                return array_merge($base, [
                    'status' => 'fail',
                    'detail' => "{$expired} App-Secret(s) bereits abgelaufen" . ($expiringSoon > 0 ? ", {$expiringSoon} läuft in <30 Tagen ab." : "."),
                ]);
            }
            if ($expiringSoon > 0) {
                return array_merge($base, [
                    'status' => 'warn',
                    'detail' => "{$expiringSoon} App-Secret(s) laufen in weniger als 30 Tagen ab.",
                ]);
            }

            return array_merge($base, [
                'status' => 'pass',
                'detail' => 'Keine abgelaufenen oder bald ablaufenden App-Secrets gefunden.',
            ]);
        } catch (\Throwable) {
            return array_merge($base, [
                'status' => 'unknown',
                'detail' => 'Berechtigung nicht verfügbar oder API-Fehler.',
            ]);
        }
    }
}
