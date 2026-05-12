<?php

namespace App\Modules\SecurityPosture;

use App\Graph\GraphClient;

class SecurityPostureService
{
    // Well-known Azure AD role IDs (constant across all tenants)
    private const ROLE_GLOBAL_ADMIN  = '62e90394-69f5-4237-9190-012177145e10';
    private const ROLE_PRIV_ADMIN    = 'e8611ab8-c189-46e8-94e1-60213ab1f814';

    public function __construct(private GraphClient $graph) {}

    /**
     * Run all security posture checks and return results.
     *
     * @return array<int, array{id:string, category:string, label:string, description:string, status:string, detail:string, severity:string}>
     */
    public function runChecks(): array
    {
        // CA policies fetched once, shared across checks
        $policies = $this->fetchCaPolicies();

        $authPolicy = $this->fetchAuthorizationPolicy();

        return [
            // Identität & MFA
            $this->checkMfaRegistrationRate(),
            $this->checkCaMfaAllUsers($policies),
            $this->checkCaAdminMfa($policies),
            $this->checkAdminsMfaRegistered(),
            $this->checkPasswordlessCapable(),
            $this->checkSsprAdoption(),
            $this->checkRiskyUsersOpen(),

            // Conditional Access
            $this->checkSecurityDefaults($policies),
            $this->checkLegacyAuthBlocked($policies),
            $this->checkSignInRiskPolicy($policies),
            $this->checkUserRiskPolicy($policies),
            $this->checkCaDeviceCompliance($policies),
            $this->checkCaGuestRestriction($policies),
            $this->checkCaSessionControls($policies),

            // Geräte & Compliance
            $this->checkDeviceComplianceRate(),
            $this->checkDefenderAlerts(),

            // Konfiguration & Apps
            $this->checkSecureScore(),
            $this->checkAdminCount(),
            $this->checkPimAdoption(),
            $this->checkAppConsentPolicy($authPolicy),
            $this->checkExternalCollabPolicy($authPolicy),
            $this->checkNamedLocations(),
            $this->checkAppSecretsExpiry(),
            $this->checkNoStaleLicensed(),
            $this->checkGuestUserCount(),
        ];
    }

    /**
     * Compute weighted score (high=3, medium=2, low=1 points each).
     */
    public function getScore(array $checks): array
    {
        $weights = ['high' => 3, 'medium' => 2, 'low' => 1];
        $earned  = 0;
        $total   = 0;

        foreach ($checks as $c) {
            if ($c['status'] === 'unknown') {
                continue;
            }
            $w = $weights[$c['severity']] ?? 1;
            $total += $w;
            if ($c['status'] === 'pass') {
                $earned += $w;
            } elseif ($c['status'] === 'warn') {
                $earned += intdiv($w, 2);
            }
        }

        $passCnt    = count(array_filter($checks, fn($c) => $c['status'] === 'pass'));
        $warnCnt    = count(array_filter($checks, fn($c) => $c['status'] === 'warn'));
        $failCnt    = count(array_filter($checks, fn($c) => $c['status'] === 'fail'));
        $unknownCnt = count(array_filter($checks, fn($c) => $c['status'] === 'unknown'));

        return [
            'passed'     => $passCnt,
            'warned'     => $warnCnt,
            'failed'     => $failCnt,
            'unknown'    => $unknownCnt,
            'total'      => count(array_filter($checks, fn($c) => $c['status'] !== 'unknown')),
            'percent'    => $total > 0 ? (int)round($earned / $total * 100) : 0,
        ];
    }

    /**
     * Generate prioritized, actionable recommendations from check results.
     */
    public function getRecommendations(array $checks): array
    {
        $byId = array_column($checks, null, 'id');

        $map = [
            'ca_mfa_all_users' => [
                'fail' => [
                    'priority'    => 'critical',
                    'title'       => 'MFA für alle Benutzer erzwingen',
                    'description' => 'Keine aktive Conditional-Access-Richtlinie verlangt MFA für alle Benutzer. Ohne diese Richtlinie können Konten allein durch gestohlene Passwörter kompromittiert werden.',
                    'action'      => 'Richtlinie erstellen',
                    'module_url'  => '/conditionalaccess',
                    'ca_template' => 'mfa_all',
                ],
                'warn' => [
                    'priority'    => 'high',
                    'title'       => 'MFA-Richtlinie aktivieren (Report-Modus)',
                    'description' => 'Eine MFA-Richtlinie ist im Report-Modus vorhanden, erzwingt MFA aber noch nicht. Aktivierung erforderlich.',
                    'action'      => 'Zu CA-Richtlinien',
                    'module_url'  => '/conditionalaccess',
                    'ca_template' => null,
                ],
            ],
            'ca_admin_mfa' => [
                'fail' => [
                    'priority'    => 'critical',
                    'title'       => 'Administratoren durch dedizierte MFA-Richtlinie schützen',
                    'description' => 'Keine aktive CA-Richtlinie erzwingt MFA explizit für Admin-Rollen. Administratorkonten sind besonders hochwertige Angriffsziele.',
                    'action'      => 'Richtlinie erstellen',
                    'module_url'  => '/conditionalaccess',
                    'ca_template' => 'mfa_admins',
                ],
            ],
            'legacy_auth_blocked' => [
                'fail' => [
                    'priority'    => 'critical',
                    'title'       => 'Legacy-Authentifizierung blockieren',
                    'description' => 'Ältere Protokolle (IMAP, POP3, SMTP AUTH, MAPI) umgehen MFA vollständig. Über 99% der Passwort-Spray-Angriffe nutzen Legacy-Auth.',
                    'action'      => 'Richtlinie erstellen',
                    'module_url'  => '/conditionalaccess',
                    'ca_template' => 'block_legacy',
                ],
                'warn' => [
                    'priority'    => 'high',
                    'title'       => 'Legacy-Auth-Blockierung aktivieren',
                    'description' => 'Die Richtlinie zum Blockieren von Legacy-Authentifizierung ist nur im Report-Modus. Vollständige Aktivierung erforderlich.',
                    'action'      => 'Zu CA-Richtlinien',
                    'module_url'  => '/conditionalaccess',
                    'ca_template' => null,
                ],
            ],
            'sign_in_risk_policy' => [
                'fail' => [
                    'priority'    => 'high',
                    'title'       => 'Risikobasierte Anmelderichtlinie einrichten',
                    'description' => 'Keine CA-Richtlinie reagiert auf Anmelderisiken (verdächtige IPs, unmögliche Reisen). Microsoft Entra erkennt diese Muster automatisch, sie werden aber nicht genutzt.',
                    'action'      => 'Zu CA-Richtlinien',
                    'module_url'  => '/conditionalaccess',
                    'ca_template' => null,
                ],
            ],
            'user_risk_policy' => [
                'fail' => [
                    'priority'    => 'high',
                    'title'       => 'Benutzerrisiko-Richtlinie einrichten',
                    'description' => 'Keine CA-Richtlinie reagiert auf hohes Benutzerrisiko (geleakte Credentials, kompromittierte Konten). Betroffene Benutzer können ungehindert weiterarbeiten.',
                    'action'      => 'Zu CA-Richtlinien',
                    'module_url'  => '/conditionalaccess',
                    'ca_template' => null,
                ],
            ],
            'mfa_registration_rate' => [
                'fail' => [
                    'priority'    => 'high',
                    'title'       => 'MFA-Registrierungsrate erhöhen',
                    'description' => 'Weniger als 75% der Benutzer haben MFA registriert. Kampagne zur MFA-Einrichtung starten oder Registrierung per CA erzwingen.',
                    'action'      => 'MFA-Übersicht',
                    'module_url'  => '/mfamethods',
                    'ca_template' => null,
                ],
                'warn' => [
                    'priority'    => 'medium',
                    'title'       => 'MFA-Registrierungsrate verbessern',
                    'description' => 'Die MFA-Registrierungsrate liegt unter 95%. Nicht registrierte Benutzer identifizieren und zur Registrierung auffordern.',
                    'action'      => 'Nicht registrierte anzeigen',
                    'module_url'  => '/mfamethods',
                    'ca_template' => null,
                ],
            ],
            'risky_users_open' => [
                'fail' => [
                    'priority'    => 'high',
                    'title'       => 'Risikobehaftete Benutzer untersuchen',
                    'description' => 'Mehrere Benutzer haben einen aktiven Risikostatus (atRisk). Diese Konten sind möglicherweise kompromittiert und benötigen sofortige Überprüfung.',
                    'action'      => 'Risikobenutzer anzeigen',
                    'module_url'  => '/security',
                    'ca_template' => null,
                ],
                'warn' => [
                    'priority'    => 'medium',
                    'title'       => 'Risikobenutzer überprüfen',
                    'description' => 'Einige Benutzer haben einen aktiven Risikostatus. Überprüfung und ggf. Kennwortänderung empfohlen.',
                    'action'      => 'Risikobenutzer anzeigen',
                    'module_url'  => '/security',
                    'ca_template' => null,
                ],
            ],
            'device_compliance_rate' => [
                'fail' => [
                    'priority'    => 'medium',
                    'title'       => 'Geräte-Compliance-Rate verbessern',
                    'description' => 'Mehr als 20% der verwalteten Geräte sind nicht konform. Nicht konforme Geräte sollten keinen Zugriff auf Unternehmensressourcen erhalten.',
                    'action'      => 'Geräte anzeigen',
                    'module_url'  => '/devices',
                    'ca_template' => null,
                ],
            ],
            'ca_device_compliance' => [
                'fail' => [
                    'priority'    => 'medium',
                    'title'       => 'Gerätekonformität in Conditional Access erzwingen',
                    'description' => 'Keine CA-Richtlinie verlangt konforme oder Hybrid-Azure-AD-joinete Geräte. Ermöglicht Zugriff von unverwalteten Privatgeräten.',
                    'action'      => 'Zu CA-Richtlinien',
                    'module_url'  => '/conditionalaccess',
                    'ca_template' => null,
                ],
            ],
            'defender_alerts' => [
                'fail' => [
                    'priority'    => 'high',
                    'title'       => 'Offene Defender-Alerts bearbeiten',
                    'description' => 'Es gibt viele ungelöste Microsoft Defender-Sicherheitswarnungen. Alerts sollten zeitnah untersucht und geschlossen werden.',
                    'action'      => 'Alerts anzeigen',
                    'module_url'  => '/security',
                    'ca_template' => null,
                ],
                'warn' => [
                    'priority'    => 'medium',
                    'title'       => 'Offene Defender-Alerts prüfen',
                    'description' => 'Einige ungelöste Microsoft Defender-Sicherheitswarnungen vorhanden.',
                    'action'      => 'Alerts anzeigen',
                    'module_url'  => '/security',
                    'ca_template' => null,
                ],
            ],
            'secure_score' => [
                'fail' => [
                    'priority'    => 'medium',
                    'title'       => 'Microsoft Secure Score verbessern',
                    'description' => 'Der Secure Score liegt unter 30%. Microsoft empfiehlt konkrete Maßnahmen im Security-Portal. Höchst-Priorität-Empfehlungen zuerst umsetzen.',
                    'action'      => 'Sicherheitsmodul',
                    'module_url'  => '/security',
                    'ca_template' => null,
                ],
                'warn' => [
                    'priority'    => 'low',
                    'title'       => 'Secure Score weiter verbessern',
                    'description' => 'Der Secure Score liegt unter 50%. Weitere Empfehlungen aus dem Microsoft Security Center umsetzen.',
                    'action'      => 'Sicherheitsmodul',
                    'module_url'  => '/security',
                    'ca_template' => null,
                ],
            ],
            'admin_count' => [
                'fail' => [
                    'priority'    => 'high',
                    'title'       => 'Anzahl globaler Administratoren reduzieren',
                    'description' => 'Mehr als 5 aktive globale Administratoren erhöhen das Angriffsrisiko erheblich. Microsoft empfiehlt max. 2-4 globale Admins und die Nutzung von Least-Privilege-Rollen.',
                    'action'      => 'Benutzerrollen prüfen',
                    'module_url'  => '/users',
                    'ca_template' => null,
                ],
                'warn' => [
                    'priority'    => 'low',
                    'title'       => 'Globale Administratoren auf Notwendigkeit prüfen',
                    'description' => 'Es gibt 3-5 globale Administratoren. Prüfen ob Least-Privilege-Rollen ausreichen würden.',
                    'action'      => 'Benutzerrollen prüfen',
                    'module_url'  => '/users',
                    'ca_template' => null,
                ],
            ],
            'named_locations' => [
                'fail' => [
                    'priority'    => 'low',
                    'title'       => 'Vertrauenswürdige Standorte konfigurieren',
                    'description' => 'Keine Named Locations konfiguriert. Vertrauenswürdige IP-Bereiche und Länder ermöglichen differenziertere CA-Richtlinien.',
                    'action'      => 'Standorte konfigurieren',
                    'module_url'  => '/namedlocations',
                    'ca_template' => null,
                ],
            ],
            'app_secrets_expiry' => [
                'fail' => [
                    'priority'    => 'medium',
                    'title'       => 'Abgelaufene App-Secrets erneuern',
                    'description' => 'Abgelaufene App-Secrets können Dienste unterbrechen oder eine Sicherheitslücke darstellen wenn Rotationszyklen nicht eingehalten werden.',
                    'action'      => 'App-Registrierungen',
                    'module_url'  => '/security',
                    'ca_template' => null,
                ],
                'warn' => [
                    'priority'    => 'medium',
                    'title'       => 'App-Secrets bald ablaufend — erneuern',
                    'description' => 'Einige App-Secrets laufen in weniger als 30 Tagen ab. Jetzt erneuern um Dienstunterbrechungen zu vermeiden.',
                    'action'      => 'App-Registrierungen',
                    'module_url'  => '/security',
                    'ca_template' => null,
                ],
            ],
            'no_stale_licensed' => [
                'fail' => [
                    'priority'    => 'medium',
                    'title'       => 'Inaktive lizenzierte Konten bereinigen',
                    'description' => 'Mehrere aktive, lizenzierte Benutzer haben sich seit über 90 Tagen nicht angemeldet. Ungenutzte Konten sollten deaktiviert und Lizenzen freigegeben werden.',
                    'action'      => 'Benutzer prüfen',
                    'module_url'  => '/users',
                    'ca_template' => null,
                ],
                'warn' => [
                    'priority'    => 'low',
                    'title'       => 'Inaktive Benutzerkonten überprüfen',
                    'description' => 'Einige lizenzierte Benutzer waren über 90 Tage inaktiv.',
                    'action'      => 'Benutzer prüfen',
                    'module_url'  => '/users',
                    'ca_template' => null,
                ],
            ],
            'guest_user_count' => [
                'fail' => [
                    'priority'    => 'medium',
                    'title'       => 'Gastbenutzer überprüfen und bereinigen',
                    'description' => 'Viele aktive Gastbenutzer können das Risiko unbeabsichtigter Datenweitergabe erhöhen. Regelmäßige Zugriffsüberprüfungen (Access Reviews) empfohlen.',
                    'action'      => 'Benutzer anzeigen',
                    'module_url'  => '/users',
                    'ca_template' => null,
                ],
            ],
            'passwordless_capable' => [
                'fail' => [
                    'priority'    => 'low',
                    'title'       => 'Passwortlose Authentifizierung einführen',
                    'description' => 'Noch keine Benutzer haben passwortlose Methoden (FIDO2, Windows Hello, Microsoft Authenticator Passwordless) registriert. Diese sind phishing-sicherer als klassische MFA.',
                    'action'      => 'MFA-Methoden',
                    'module_url'  => '/mfamethods',
                    'ca_template' => null,
                ],
            ],
            'security_defaults' => [
                'fail' => [
                    'priority'    => 'critical',
                    'title'       => 'Basis-Schutz fehlt — weder Security Defaults noch CA aktiv',
                    'description' => 'Weder Security Defaults noch Conditional-Access-Richtlinien sind aktiv. Der Tenant hat keinen automatisierten Basisschutz gegen gängige Angriffe.',
                    'action'      => 'CA-Richtlinien einrichten',
                    'module_url'  => '/conditionalaccess',
                    'ca_template' => null,
                ],
                'warn' => [
                    'priority'    => 'medium',
                    'title'       => 'Security Defaults und CA gleichzeitig aktiv',
                    'description' => 'Security Defaults und eigene CA-Richtlinien sind gleichzeitig aktiv. Dies kann zu Konflikten führen. Security Defaults deaktivieren und vollständig auf CA setzen.',
                    'action'      => 'Zu CA-Richtlinien',
                    'module_url'  => '/conditionalaccess',
                    'ca_template' => null,
                ],
            ],
            'admins_mfa_registered' => [
                'fail' => [
                    'priority'    => 'critical',
                    'title'       => 'Globale Admins ohne MFA-Registrierung',
                    'description' => 'Mindestens ein globaler Administrator hat keine MFA-Methode registriert. Admin-Konten ohne MFA sind das größte Einzelrisiko in einem M365-Tenant.',
                    'action'      => 'MFA-Status prüfen',
                    'module_url'  => '/mfamethods',
                    'ca_template' => null,
                ],
                'warn' => [
                    'priority'    => 'high',
                    'title'       => 'Admin-MFA-Status überprüfen',
                    'description' => 'MFA-Daten für globale Administratoren konnten nicht vollständig verifiziert werden.',
                    'action'      => 'MFA-Status prüfen',
                    'module_url'  => '/mfamethods',
                    'ca_template' => null,
                ],
            ],
            'app_consent_policy' => [
                'fail' => [
                    'priority'    => 'high',
                    'title'       => 'Benutzer dürfen beliebigen Apps zustimmen',
                    'description' => 'Die aktuelle App-Zustimmungsrichtlinie erlaubt Benutzern, OAuth-Berechtigungen an Drittanbieter-Apps zu vergeben. Dies ermöglicht OAuth-Phishing-Angriffe (Consent Phishing).',
                    'action'      => 'Richtlinie prüfen',
                    'module_url'  => '/settings',
                    'ca_template' => null,
                ],
                'warn' => [
                    'priority'    => 'medium',
                    'title'       => 'App-Zustimmungsrichtlinie einschränken',
                    'description' => 'Benutzer können bestimmten Apps ohne Admin-Genehmigung zustimmen. Admin-Consent-Workflow aktivieren um alle Zustimmungen zu kontrollieren.',
                    'action'      => 'Richtlinie prüfen',
                    'module_url'  => '/settings',
                    'ca_template' => null,
                ],
            ],
            'external_collab_policy' => [
                'fail' => [
                    'priority'    => 'medium',
                    'title'       => 'Einladungsrichtlinie für Gäste einschränken',
                    'description' => 'Jeder (auch externe Gäste) kann neue Gäste in den Tenant einladen. Einladungen sollten auf Admins und Gast-Einlader begrenzt werden.',
                    'action'      => 'Richtlinie prüfen',
                    'module_url'  => '/settings',
                    'ca_template' => null,
                ],
                'warn' => [
                    'priority'    => 'low',
                    'title'       => 'Gasteinladungen nur durch Admins erlauben',
                    'description' => 'Alle Benutzer dürfen Gäste einladen. Empfehlung: nur Admins und dedizierte Gast-Einlader.',
                    'action'      => 'Richtlinie prüfen',
                    'module_url'  => '/settings',
                    'ca_template' => null,
                ],
            ],
            'sspr_adoption' => [
                'fail' => [
                    'priority'    => 'medium',
                    'title'       => 'Self-Service Password Reset (SSPR) einführen',
                    'description' => 'Kein Benutzer hat SSPR registriert. SSPR reduziert Helpdesk-Aufwand und verhindert dass Benutzer unsichere Passwort-Reset-Wege nutzen.',
                    'action'      => 'MFA-Methoden',
                    'module_url'  => '/mfamethods',
                    'ca_template' => null,
                ],
                'warn' => [
                    'priority'    => 'low',
                    'title'       => 'SSPR-Registrierungsrate verbessern',
                    'description' => 'Weniger als 50% der Benutzer haben SSPR registriert. Fehlende Registrierungen erhöhen Helpdesk-Last.',
                    'action'      => 'MFA-Methoden',
                    'module_url'  => '/mfamethods',
                    'ca_template' => null,
                ],
            ],
            'ca_session_controls' => [
                'fail' => [
                    'priority'    => 'low',
                    'title'       => 'Sitzungslebensdauer einschränken (CA)',
                    'description' => 'Keine CA-Richtlinie erzwingt eine maximale Sitzungsdauer oder verhindert persistente Browser-Sitzungen. Lang lebende Tokens erhöhen das Risiko bei gestohlenen Refresh-Tokens.',
                    'action'      => 'Zu CA-Richtlinien',
                    'module_url'  => '/conditionalaccess',
                    'ca_template' => null,
                ],
            ],
            'pim_adoption' => [
                'fail' => [
                    'priority'    => 'medium',
                    'title'       => 'Privileged Identity Management (PIM) einführen',
                    'description' => 'Keine PIM-berechtigten Rollenzuweisungen gefunden. PIM ermöglicht Just-in-Time-Zugriff für Admin-Rollen — Admins sind nur aktiv wenn nötig, mit Genehmigungsprozess und Audit-Trail.',
                    'action'      => 'Benutzer & Rollen',
                    'module_url'  => '/users',
                    'ca_template' => null,
                ],
            ],
        ];

        $priorityOrder = ['critical' => 0, 'high' => 1, 'medium' => 2, 'low' => 3];
        $recs = [];

        foreach ($map as $checkId => $statusMap) {
            $check = $byId[$checkId] ?? null;
            if (!$check) {
                continue;
            }
            $status = $check['status'];
            if (isset($statusMap[$status])) {
                $recs[] = array_merge($statusMap[$status], ['check_id' => $checkId]);
            } elseif ($status === 'fail' && isset($statusMap['warn'])) {
                $recs[] = array_merge($statusMap['warn'], ['check_id' => $checkId]);
            }
        }

        usort($recs, fn($a, $b) => ($priorityOrder[$a['priority']] ?? 9) <=> ($priorityOrder[$b['priority']] ?? 9));

        return $recs;
    }

    // -------------------------------------------------------------------------
    // Individual checks
    // -------------------------------------------------------------------------

    private function checkMfaRegistrationRate(): array
    {
        $base = [
            'id'          => 'mfa_registration_rate',
            'category'    => 'Identität & MFA',
            'label'       => 'MFA-Registrierungsrate',
            'description' => 'Anteil der Benutzer mit registrierter MFA-Methode.',
            'severity'    => 'high',
        ];
        try {
            $users      = $this->graph->paginate(
                '/reports/authenticationMethods/userRegistrationDetails',
                ['$select' => 'id,isMfaRegistered', '$top' => '999'],
                50, 'dash_mfa_pct', 1800
            );
            $total      = count($users);
            $registered = count(array_filter($users, fn($u) => $u['isMfaRegistered'] ?? false));
            $rate       = $total > 0 ? round($registered / $total * 100, 1) : 0;
            if ($rate >= 95) {
                return array_merge($base, ['status' => 'pass', 'detail' => "{$registered}/{$total} Benutzer haben MFA registriert ({$rate}%)."]);
            }
            if ($rate >= 75) {
                return array_merge($base, ['status' => 'warn', 'detail' => "MFA-Registrierungsrate: {$rate}% ({$registered}/{$total}) — Ziel ist ≥95%."]);
            }
            return array_merge($base, ['status' => 'fail', 'detail' => "Niedrige MFA-Registrierungsrate: {$rate}% ({$registered}/{$total})."]);
        } catch (\Throwable) {
            return array_merge($base, ['status' => 'unknown', 'detail' => 'Berechtigung fehlt oder API-Fehler.']);
        }
    }

    private function checkCaMfaAllUsers(array $policies): array
    {
        $base = [
            'id'          => 'ca_mfa_all_users',
            'category'    => 'Conditional Access',
            'label'       => 'MFA für alle Benutzer (CA)',
            'description' => 'Aktive CA-Richtlinie, die MFA für alle oder die meisten Benutzer verlangt.',
            'severity'    => 'high',
        ];
        $foundEnabled = $foundReport = false;
        foreach ($policies as $p) {
            $state    = strtolower($p['state'] ?? '');
            $grant    = $p['grantControls'] ?? [];
            $controls = array_map('strtolower', (array)($grant['builtInControls'] ?? []));
            if (!in_array('mfa', $controls, true)) {
                continue;
            }
            $incUsers = $p['conditions']['users']['includeUsers'] ?? [];
            $incRoles = $p['conditions']['users']['includeRoles'] ?? [];
            $isAll    = in_array('All', (array)$incUsers, true) || (empty($incRoles) && !empty($incUsers));
            if (!$isAll) {
                continue;
            }
            if ($state === 'enabled') {
                $foundEnabled = true;
                break;
            }
            if ($state === 'enabledforreportingbutnotenforced') {
                $foundReport = true;
            }
        }
        if ($foundEnabled) {
            return array_merge($base, ['status' => 'pass', 'detail' => 'Aktive CA-Richtlinie verlangt MFA für alle Benutzer.']);
        }
        if ($foundReport) {
            return array_merge($base, ['status' => 'warn', 'detail' => 'MFA-Richtlinie existiert, ist aber nur im Report-Modus — noch nicht aktiv.']);
        }
        return array_merge($base, ['status' => 'fail', 'detail' => 'Keine aktive CA-Richtlinie erzwingt MFA für alle Benutzer.']);
    }

    private function checkCaAdminMfa(array $policies): array
    {
        $base = [
            'id'          => 'ca_admin_mfa',
            'category'    => 'Conditional Access',
            'label'       => 'MFA für Administratoren (CA)',
            'description' => 'Aktive CA-Richtlinie, die MFA explizit für Admin-Rollen verlangt.',
            'severity'    => 'high',
        ];
        foreach ($policies as $p) {
            if (strtolower($p['state'] ?? '') !== 'enabled') {
                continue;
            }
            $grant    = $p['grantControls'] ?? [];
            $controls = array_map('strtolower', (array)($grant['builtInControls'] ?? []));
            if (!in_array('mfa', $controls, true)) {
                continue;
            }
            $incUsers = $p['conditions']['users']['includeUsers'] ?? [];
            $incRoles = $p['conditions']['users']['includeRoles'] ?? [];
            if (in_array('All', (array)$incUsers, true) || !empty($incRoles)) {
                return array_merge($base, ['status' => 'pass', 'detail' => 'Aktive CA-Richtlinie verlangt MFA für Admin-Rollen oder alle Benutzer.']);
            }
        }
        return array_merge($base, ['status' => 'fail', 'detail' => 'Keine aktive CA-Richtlinie schützt explizit Admin-Rollen mit MFA.']);
    }

    private function checkPasswordlessCapable(): array
    {
        $base = [
            'id'          => 'passwordless_capable',
            'category'    => 'Identität & MFA',
            'label'       => 'Passwortlose Authentifizierung',
            'description' => 'Mindestens ein Benutzer nutzt FIDO2, Windows Hello oder Authenticator Passwordless.',
            'severity'    => 'low',
        ];
        try {
            $users    = $this->graph->paginate(
                '/reports/authenticationMethods/userRegistrationDetails',
                ['$select' => 'id,isPasswordlessCapable', '$top' => '999'],
                50, 'dash_mfa_pct', 1800
            );
            $capable = count(array_filter($users, fn($u) => $u['isPasswordlessCapable'] ?? false));
            $total   = count($users);
            if ($capable > 0) {
                return array_merge($base, ['status' => 'pass', 'detail' => "{$capable} von {$total} Benutzer(n) sind für passwortlose Anmeldung registriert."]);
            }
            return array_merge($base, ['status' => 'fail', 'detail' => 'Noch kein Benutzer hat eine passwortlose Methode (FIDO2, Windows Hello, Passwordless) registriert.']);
        } catch (\Throwable) {
            return array_merge($base, ['status' => 'unknown', 'detail' => 'Berechtigung fehlt oder API-Fehler.']);
        }
    }

    private function checkRiskyUsersOpen(): array
    {
        $base = [
            'id'          => 'risky_users_open',
            'category'    => 'Identität & MFA',
            'label'       => 'Risikobenutzer (atRisk)',
            'description' => 'Anzahl der Benutzer mit aktivem Risikostatus in Entra Identity Protection.',
            'severity'    => 'medium',
        ];
        try {
            $data  = $this->graph->getEventual(
                '/identityProtection/riskyUsers',
                ['$count' => 'true', '$top' => '1', '$select' => 'id', '$filter' => "riskState eq 'atRisk'"],
                'dash_risky', 300
            );
            $count = (int)($data['@odata.count'] ?? count($data['value'] ?? []));
            if ($count === 0) {
                return array_merge($base, ['status' => 'pass', 'detail' => 'Keine Benutzer mit aktivem Risikostatus.']);
            }
            if ($count <= 5) {
                return array_merge($base, ['status' => 'warn', 'detail' => "{$count} Benutzer mit aktivem Risikostatus — Überprüfung empfohlen."]);
            }
            return array_merge($base, ['status' => 'fail', 'detail' => "{$count} Benutzer mit aktivem Risikostatus erfordern sofortige Aufmerksamkeit."]);
        } catch (\Throwable) {
            return array_merge($base, ['status' => 'unknown', 'detail' => 'Berechtigung fehlt (IdentityRiskyUser.Read.All erforderlich).']);
        }
    }

    private function checkLegacyAuthBlocked(array $policies): array
    {
        $base = [
            'id'          => 'legacy_auth_blocked',
            'category'    => 'Conditional Access',
            'label'       => 'Legacy-Authentifizierung blockiert',
            'description' => 'CA-Richtlinie blockiert ältere Protokolle (IMAP, POP3, SMTP AUTH, MAPI).',
            'severity'    => 'high',
        ];
        $foundEnabled = $foundReport = false;
        foreach ($policies as $p) {
            $state       = strtolower($p['state'] ?? '');
            $clientTypes = array_map('strtolower', (array)($p['conditions']['clientAppTypes'] ?? []));
            $hasLegacy   = in_array('exchangeactivesync', $clientTypes, true) || in_array('other', $clientTypes, true);
            if (!$hasLegacy) {
                continue;
            }
            $controls = array_map('strtolower', (array)($p['grantControls']['builtInControls'] ?? []));
            if (!in_array('block', $controls, true)) {
                continue;
            }
            if ($state === 'enabled') {
                $foundEnabled = true;
                break;
            }
            if ($state === 'enabledforreportingbutnotenforced') {
                $foundReport = true;
            }
        }
        if ($foundEnabled) {
            return array_merge($base, ['status' => 'pass', 'detail' => 'Aktive CA-Richtlinie blockiert Legacy-Authentifizierung.']);
        }
        if ($foundReport) {
            return array_merge($base, ['status' => 'warn', 'detail' => 'Legacy-Auth-Block im Report-Modus — noch nicht aktiv.']);
        }
        return array_merge($base, ['status' => 'fail', 'detail' => 'Keine CA-Richtlinie blockiert Legacy-Authentifizierung.']);
    }

    private function checkSignInRiskPolicy(array $policies): array
    {
        $base = [
            'id'          => 'sign_in_risk_policy',
            'category'    => 'Conditional Access',
            'label'       => 'Anmelderisiko-Richtlinie (CA)',
            'description' => 'Aktive CA-Richtlinie reagiert auf mittleres/hohes Anmelderisiko.',
            'severity'    => 'medium',
        ];
        foreach ($policies as $p) {
            if (strtolower($p['state'] ?? '') !== 'enabled') {
                continue;
            }
            $riskLevels = array_map('strtolower', (array)($p['conditions']['signInRiskLevels'] ?? []));
            if (!empty(array_intersect($riskLevels, ['medium', 'high']))) {
                return array_merge($base, ['status' => 'pass', 'detail' => 'Aktive CA-Richtlinie reagiert auf Anmelderisiko (mittel/hoch).']);
            }
        }
        return array_merge($base, ['status' => 'fail', 'detail' => 'Keine CA-Richtlinie reagiert auf Anmelderisiken. Benötigt Entra ID P2.']);
    }

    private function checkUserRiskPolicy(array $policies): array
    {
        $base = [
            'id'          => 'user_risk_policy',
            'category'    => 'Conditional Access',
            'label'       => 'Benutzerrisiko-Richtlinie (CA)',
            'description' => 'Aktive CA-Richtlinie reagiert auf hohes Benutzerrisiko (kompromittierte Konten).',
            'severity'    => 'medium',
        ];
        foreach ($policies as $p) {
            if (strtolower($p['state'] ?? '') !== 'enabled') {
                continue;
            }
            $riskLevels = array_map('strtolower', (array)($p['conditions']['userRiskLevels'] ?? []));
            if (!empty(array_intersect($riskLevels, ['medium', 'high']))) {
                return array_merge($base, ['status' => 'pass', 'detail' => 'Aktive CA-Richtlinie reagiert auf hohes Benutzerrisiko.']);
            }
        }
        return array_merge($base, ['status' => 'fail', 'detail' => 'Keine CA-Richtlinie reagiert auf Benutzerrisiken. Benötigt Entra ID P2.']);
    }

    private function checkCaDeviceCompliance(array $policies): array
    {
        $base = [
            'id'          => 'ca_device_compliance',
            'category'    => 'Conditional Access',
            'label'       => 'Gerätekonformität in CA',
            'description' => 'CA-Richtlinie verlangt konforme oder Hybrid-AD-joinete Geräte.',
            'severity'    => 'medium',
        ];
        foreach ($policies as $p) {
            if (strtolower($p['state'] ?? '') !== 'enabled') {
                continue;
            }
            $controls = array_map('strtolower', (array)($p['grantControls']['builtInControls'] ?? []));
            if (!empty(array_intersect($controls, ['compliantdevice', 'domainjoinedevice']))) {
                return array_merge($base, ['status' => 'pass', 'detail' => 'Aktive CA-Richtlinie fordert konforme/Hybrid-Geräte.']);
            }
        }
        return array_merge($base, ['status' => 'fail', 'detail' => 'Keine CA-Richtlinie erzwingt Gerätekonformität.']);
    }

    private function checkCaGuestRestriction(array $policies): array
    {
        $base = [
            'id'          => 'ca_guest_restriction',
            'category'    => 'Conditional Access',
            'label'       => 'Gastbenutzer-CA-Richtlinie',
            'description' => 'Aktive CA-Richtlinie mit speziellen Bedingungen für Gastbenutzer.',
            'severity'    => 'low',
        ];
        foreach ($policies as $p) {
            if (strtolower($p['state'] ?? '') !== 'enabled') {
                continue;
            }
            $incUsers = array_map('strtolower', (array)($p['conditions']['users']['includeGuestsOrExternalUsers'] ?? []));
            $incGuest = $p['conditions']['users']['includeGuestsOrExternalUsers'] ?? null;
            $incUsersArr = (array)($p['conditions']['users']['includeUsers'] ?? []);
            if ($incGuest !== null || in_array('GuestsOrExternalUsers', $incUsersArr, true)) {
                return array_merge($base, ['status' => 'pass', 'detail' => 'Aktive CA-Richtlinie adressiert Gastbenutzer spezifisch.']);
            }
        }
        return array_merge($base, ['status' => 'warn', 'detail' => 'Keine CA-Richtlinie mit expliziten Bedingungen für Gastbenutzer gefunden.']);
    }

    private function checkDeviceComplianceRate(): array
    {
        $base = [
            'id'          => 'device_compliance_rate',
            'category'    => 'Geräte & Compliance',
            'label'       => 'Geräte-Compliance-Rate',
            'description' => 'Anteil der verwalteten Intune-Geräte, die konform sind.',
            'severity'    => 'medium',
        ];
        try {
            $all = $this->graph->getEventual(
                '/deviceManagement/managedDevices',
                ['$count' => 'true', '$top' => '1', '$select' => 'id'],
                'dash_devices', 600
            );
            $total = (int)($all['@odata.count'] ?? count($all['value'] ?? []));
            if ($total === 0) {
                return array_merge($base, ['status' => 'unknown', 'detail' => 'Keine Intune-Geräte gefunden oder Berechtigung fehlt.']);
            }
            $nonComp = $this->graph->getEventual(
                '/deviceManagement/managedDevices',
                ['$count' => 'true', '$top' => '1', '$select' => 'id', '$filter' => "complianceState eq 'noncompliant'"],
                'dash_noncompliant', 600
            );
            $nonCompliantCount = (int)($nonComp['@odata.count'] ?? count($nonComp['value'] ?? []));
            $rate = round(($total - $nonCompliantCount) / $total * 100, 1);
            if ($rate >= 90) {
                return array_merge($base, ['status' => 'pass', 'detail' => "{$rate}% der Geräte sind konform ({$nonCompliantCount} nicht konform von {$total})."]);
            }
            if ($rate >= 70) {
                return array_merge($base, ['status' => 'warn', 'detail' => "Compliance-Rate: {$rate}% — {$nonCompliantCount} von {$total} Geräten nicht konform."]);
            }
            return array_merge($base, ['status' => 'fail', 'detail' => "Niedrige Compliance-Rate: {$rate}% — {$nonCompliantCount} von {$total} Geräten nicht konform."]);
        } catch (\Throwable) {
            return array_merge($base, ['status' => 'unknown', 'detail' => 'Berechtigung fehlt (DeviceManagementManagedDevices.Read.All).']);
        }
    }

    private function checkDefenderAlerts(): array
    {
        $base = [
            'id'          => 'defender_alerts',
            'category'    => 'Geräte & Compliance',
            'label'       => 'Offene Defender-Alerts',
            'description' => 'Anzahl ungelöster Microsoft Defender-Sicherheitswarnungen.',
            'severity'    => 'high',
        ];
        try {
            $data  = $this->graph->get(
                '/security/alerts_v2',
                ['$filter' => "status eq 'new' or status eq 'inProgress'", '$top' => '1', '$count' => 'true'],
                'dash_alerts', 300
            );
            $count = (int)($data['@odata.count'] ?? count($data['value'] ?? []));
            if ($count === 0) {
                return array_merge($base, ['status' => 'pass', 'detail' => 'Keine offenen Defender-Sicherheitswarnungen.']);
            }
            if ($count <= 5) {
                return array_merge($base, ['status' => 'warn', 'detail' => "{$count} offene Sicherheitswarnung(en) — Überprüfung empfohlen."]);
            }
            return array_merge($base, ['status' => 'fail', 'detail' => "{$count} offene Sicherheitswarnungen erfordern Aufmerksamkeit."]);
        } catch (\Throwable) {
            return array_merge($base, ['status' => 'unknown', 'detail' => 'Berechtigung fehlt oder Defender nicht lizenziert.']);
        }
    }

    private function checkSecureScore(): array
    {
        $base = [
            'id'          => 'secure_score',
            'category'    => 'Konfiguration & Apps',
            'label'       => 'Microsoft Secure Score',
            'description' => 'Secure Score als Prozentwert des erreichbaren Maximums (Ziel: >50%).',
            'severity'    => 'medium',
        ];
        try {
            $data  = $this->graph->get('/security/secureScores', ['$top' => '1', '$select' => 'currentScore,maxScore'], 'securescore_latest', 3600);
            $items = $data['value'] ?? [];
            if (empty($items)) {
                return array_merge($base, ['status' => 'unknown', 'detail' => 'Keine Secure-Score-Daten verfügbar.']);
            }
            $current = (float)($items[0]['currentScore'] ?? 0);
            $max     = (float)($items[0]['maxScore']     ?? 0);
            $pct     = $max > 0 ? round($current / $max * 100, 1) : 0;
            if ($pct > 50) {
                return array_merge($base, ['status' => 'pass', 'detail' => "Secure Score: {$current}/{$max} Punkte ({$pct}%)."]);
            }
            if ($pct >= 30) {
                return array_merge($base, ['status' => 'warn', 'detail' => "Secure Score: {$current}/{$max} Punkte ({$pct}%) — Verbesserungspotenzial."]);
            }
            return array_merge($base, ['status' => 'fail', 'detail' => "Niedriger Secure Score: {$current}/{$max} Punkte ({$pct}%)."]);
        } catch (\Throwable) {
            return array_merge($base, ['status' => 'unknown', 'detail' => 'Berechtigung fehlt oder API-Fehler.']);
        }
    }

    private function checkAdminCount(): array
    {
        $base = [
            'id'          => 'admin_count',
            'category'    => 'Konfiguration & Apps',
            'label'       => 'Globale Administratoren',
            'description' => 'Anzahl der Benutzer mit der Rolle "Globaler Administrator" (Ziel: max. 4).',
            'severity'    => 'high',
        ];
        try {
            $data  = $this->graph->get(
                '/directoryRoles',
                ['$filter' => "roleTemplateId eq '" . self::ROLE_GLOBAL_ADMIN . "'", '$select' => 'id'],
                'dir_role_global_admin', 3600
            );
            $roles = $data['value'] ?? [];
            if (empty($roles)) {
                return array_merge($base, ['status' => 'unknown', 'detail' => 'Globale Administratoren-Rolle nicht gefunden.']);
            }
            $roleId  = $roles[0]['id'];
            $members = $this->graph->get("/directoryRoles/{$roleId}/members", ['$select' => 'id'], "dir_role_members_{$roleId}", 1800);
            $count   = count($members['value'] ?? []);
            if ($count <= 2) {
                return array_merge($base, ['status' => 'pass', 'detail' => "{$count} globale Administrator(en) — optimal (max. 4 empfohlen)."]);
            }
            if ($count <= 4) {
                return array_merge($base, ['status' => 'warn', 'detail' => "{$count} globale Administratoren — akzeptabel, aber Least-Privilege prüfen."]);
            }
            return array_merge($base, ['status' => 'fail', 'detail' => "{$count} globale Administratoren — zu viele. Microsoft empfiehlt max. 2-4."]);
        } catch (\Throwable) {
            return array_merge($base, ['status' => 'unknown', 'detail' => 'Berechtigung fehlt oder API-Fehler.']);
        }
    }

    private function checkNamedLocations(): array
    {
        $base = [
            'id'          => 'named_locations',
            'category'    => 'Konfiguration & Apps',
            'label'       => 'Named Locations konfiguriert',
            'description' => 'Mindestens ein vertrauenswürdiger Standort (IP oder Land) ist konfiguriert.',
            'severity'    => 'low',
        ];
        try {
            $data  = $this->graph->get('/identity/conditionalAccess/namedLocations', ['$top' => '100'], 'named_locations', 1800);
            $count = count($data['value'] ?? []);
            if ($count > 0) {
                return array_merge($base, ['status' => 'pass', 'detail' => "{$count} Named Location(s) konfiguriert."]);
            }
            return array_merge($base, ['status' => 'fail', 'detail' => 'Keine Named Locations konfiguriert. Vertrauenswürdige IPs/Länder fehlen.']);
        } catch (\Throwable) {
            return array_merge($base, ['status' => 'unknown', 'detail' => 'Berechtigung fehlt oder API-Fehler.']);
        }
    }

    private function checkAppSecretsExpiry(): array
    {
        $base = [
            'id'          => 'app_secrets_expiry',
            'category'    => 'Konfiguration & Apps',
            'label'       => 'App-Secrets Ablaufdatum',
            'description' => 'App-Registrierungen ohne abgelaufene oder bald ablaufende Secrets.',
            'severity'    => 'medium',
        ];
        try {
            $data      = $this->graph->get('/applications', ['$select' => 'id,displayName,passwordCredentials', '$top' => '100'], 'applications_secrets', 900);
            $now       = time();
            $threshold = strtotime('+30 days');
            $expired = $soon = 0;
            foreach ($data['value'] ?? [] as $app) {
                foreach ((array)($app['passwordCredentials'] ?? []) as $cred) {
                    $ts = strtotime($cred['endDateTime'] ?? '');
                    if (!$ts) {
                        continue;
                    }
                    if ($ts < $now) {
                        $expired++;
                    } elseif ($ts < $threshold) {
                        $soon++;
                    }
                }
            }
            if ($expired > 0) {
                return array_merge($base, ['status' => 'fail', 'detail' => "{$expired} abgelaufenes Secret(s)" . ($soon > 0 ? ", {$soon} läuft in <30 Tagen ab." : ".")]);
            }
            if ($soon > 0) {
                return array_merge($base, ['status' => 'warn', 'detail' => "{$soon} Secret(s) läuft in <30 Tagen ab — Erneuerung erforderlich."]);
            }
            return array_merge($base, ['status' => 'pass', 'detail' => 'Keine abgelaufenen oder bald ablaufenden App-Secrets.']);
        } catch (\Throwable) {
            return array_merge($base, ['status' => 'unknown', 'detail' => 'Berechtigung fehlt oder API-Fehler.']);
        }
    }

    private function checkNoStaleLicensed(): array
    {
        $base = [
            'id'          => 'no_stale_licensed',
            'category'    => 'Konfiguration & Apps',
            'label'       => 'Inaktive lizenzierte Konten',
            'description' => 'Aktive, lizenzierte Benutzer ohne Anmeldung seit mehr als 90 Tagen.',
            'severity'    => 'low',
        ];
        try {
            $users  = $this->graph->paginate('/users', ['$select' => 'id,accountEnabled,assignedLicenses,signInActivity,createdDateTime', '$top' => '999'], 50, 'users_all', 900);
            $cutoff = strtotime('-90 days');
            $stale  = 0;
            foreach ($users as $u) {
                if (!($u['accountEnabled'] ?? false) || empty($u['assignedLicenses'])) {
                    continue;
                }
                $last = $u['signInActivity']['lastSignInDateTime'] ?? null;
                if ($last === null) {
                    if (($u['createdDateTime'] ?? null) && strtotime($u['createdDateTime']) < $cutoff) {
                        $stale++;
                    }
                    continue;
                }
                if (strtotime($last) < $cutoff) {
                    $stale++;
                }
            }
            if ($stale === 0) {
                return array_merge($base, ['status' => 'pass', 'detail' => 'Keine inaktiven lizenzierten Konten gefunden.']);
            }
            if ($stale <= 5) {
                return array_merge($base, ['status' => 'warn', 'detail' => "{$stale} lizenzierte Benutzer seit >90 Tagen inaktiv."]);
            }
            return array_merge($base, ['status' => 'fail', 'detail' => "{$stale} lizenzierte Benutzer seit >90 Tagen ohne Anmeldung."]);
        } catch (\Throwable) {
            return array_merge($base, ['status' => 'unknown', 'detail' => 'Berechtigung fehlt oder API-Fehler.']);
        }
    }

    private function checkGuestUserCount(): array
    {
        $base = [
            'id'          => 'guest_user_count',
            'category'    => 'Konfiguration & Apps',
            'label'       => 'Gastbenutzer',
            'description' => 'Anzahl aktiver Gastbenutzer — sollte regelmäßig überprüft werden.',
            'severity'    => 'low',
        ];
        try {
            $data   = $this->graph->getEventual('/users', ['$count' => 'true', '$top' => '1', '$select' => 'id', '$filter' => "userType eq 'Guest'"], 'dash_guests_count', 1800);
            $guests = (int)($data['@odata.count'] ?? count($data['value'] ?? []));
            if ($guests <= 10) {
                return array_merge($base, ['status' => 'pass', 'detail' => "{$guests} aktive Gastbenutzer — unkritisch."]);
            }
            if ($guests <= 30) {
                return array_merge($base, ['status' => 'warn', 'detail' => "{$guests} Gastbenutzer — regelmäßige Überprüfung empfohlen."]);
            }
            return array_merge($base, ['status' => 'fail', 'detail' => "{$guests} Gastbenutzer — Überprüfung und Bereinigung erforderlich."]);
        } catch (\Throwable) {
            return array_merge($base, ['status' => 'unknown', 'detail' => 'Berechtigung fehlt oder API-Fehler.']);
        }
    }

    private function checkSecurityDefaults(array $policies): array
    {
        $base = [
            'id'          => 'security_defaults',
            'category'    => 'Conditional Access',
            'label'       => 'Security Defaults vs. CA',
            'description' => 'Security Defaults und Conditional Access sollten nicht gleichzeitig aktiv sein.',
            'severity'    => 'high',
        ];
        try {
            $data       = $this->graph->get('/policies/identitySecurityDefaultsEnforcementPolicy', [], 'security_defaults', 3600);
            $sdEnabled  = (bool)($data['isEnabled'] ?? false);
            $hasActiveCa = !empty(array_filter($policies, fn($p) => strtolower($p['state'] ?? '') === 'enabled'));

            if ($sdEnabled && $hasActiveCa) {
                return array_merge($base, ['status' => 'warn', 'detail' => 'Security Defaults ist aktiv, aber eigene CA-Richtlinien sind ebenfalls aktiviert — kann zu Konflikten führen.']);
            }
            if (!$sdEnabled && !$hasActiveCa) {
                return array_merge($base, ['status' => 'fail', 'detail' => 'Weder Security Defaults noch aktive CA-Richtlinien vorhanden — kein Basisschutz.']);
            }
            if ($sdEnabled && !$hasActiveCa) {
                return array_merge($base, ['status' => 'warn', 'detail' => 'Security Defaults aktiv — bietet Basisschutz, aber keine granulare Steuerung. CA-Migration empfohlen.']);
            }
            return array_merge($base, ['status' => 'pass', 'detail' => 'Security Defaults deaktiviert, eigene CA-Richtlinien aktiv — optimal.']);
        } catch (\Throwable) {
            return array_merge($base, ['status' => 'unknown', 'detail' => 'Berechtigung fehlt (Policy.Read.All erforderlich).']);
        }
    }

    private function checkAdminsMfaRegistered(): array
    {
        $base = [
            'id'          => 'admins_mfa_registered',
            'category'    => 'Identität & MFA',
            'label'       => 'Alle Admins haben MFA',
            'description' => 'Alle globalen Administratoren haben eine MFA-Methode registriert.',
            'severity'    => 'high',
        ];
        try {
            // Get global admin role
            $roles = $this->graph->get('/directoryRoles', ['$filter' => "roleTemplateId eq '" . self::ROLE_GLOBAL_ADMIN . "'", '$select' => 'id'], 'dir_role_global_admin', 3600);
            $roleList = $roles['value'] ?? [];
            if (empty($roleList)) {
                return array_merge($base, ['status' => 'unknown', 'detail' => 'Globale Administratorrolle nicht gefunden.']);
            }
            $roleId  = $roleList[0]['id'];
            $members = $this->graph->get("/directoryRoles/{$roleId}/members", ['$select' => 'id,userPrincipalName'], "dir_role_members_{$roleId}", 1800);
            $admins  = $members['value'] ?? [];
            if (empty($admins)) {
                return array_merge($base, ['status' => 'unknown', 'detail' => 'Keine Mitglieder der Administratorrolle gefunden.']);
            }

            // Get MFA data — use existing cache if available
            $mfaData = $this->graph->paginate(
                '/reports/authenticationMethods/userRegistrationDetails',
                ['$select' => 'id,userPrincipalName,isMfaRegistered', '$top' => '999'],
                50, 'dash_mfa_pct', 1800
            );
            $mfaByUpn = [];
            foreach ($mfaData as $row) {
                $mfaByUpn[strtolower($row['userPrincipalName'] ?? '')] = $row['isMfaRegistered'] ?? false;
            }

            $noMfa = [];
            foreach ($admins as $admin) {
                $upn = strtolower($admin['userPrincipalName'] ?? '');
                if ($upn && isset($mfaByUpn[$upn]) && !$mfaByUpn[$upn]) {
                    $noMfa[] = $admin['userPrincipalName'];
                }
            }

            if (empty($noMfa)) {
                return array_merge($base, ['status' => 'pass', 'detail' => 'Alle ' . count($admins) . ' globale(n) Administrator(en) haben MFA registriert.']);
            }
            return array_merge($base, ['status' => 'fail', 'detail' => count($noMfa) . ' Admin(s) ohne MFA: ' . implode(', ', array_slice($noMfa, 0, 3)) . (count($noMfa) > 3 ? ' …' : '')]);
        } catch (\Throwable) {
            return array_merge($base, ['status' => 'unknown', 'detail' => 'Berechtigung fehlt oder API-Fehler.']);
        }
    }

    private function checkSsprAdoption(): array
    {
        $base = [
            'id'          => 'sspr_adoption',
            'category'    => 'Identität & MFA',
            'label'       => 'Self-Service Password Reset (SSPR)',
            'description' => 'Anteil der Benutzer mit registrierter SSPR-Methode.',
            'severity'    => 'medium',
        ];
        try {
            $data  = $this->graph->get('/reports/authenticationMethods/usersRegisteredByFeature', [], 'sspr_feature_summary', 3600);
            $total = (int)($data['totalUserCount'] ?? 0);
            if ($total === 0) {
                return array_merge($base, ['status' => 'unknown', 'detail' => 'Keine Benutzerdaten verfügbar.']);
            }
            $ssprCount = 0;
            foreach ((array)($data['userRegistrationFeatureCounts'] ?? []) as $item) {
                if (($item['feature'] ?? '') === 'ssprRegistered') {
                    $ssprCount = (int)($item['userCount'] ?? 0);
                    break;
                }
            }
            $rate = round($ssprCount / $total * 100, 1);
            if ($rate >= 70) {
                return array_merge($base, ['status' => 'pass', 'detail' => "{$ssprCount}/{$total} Benutzer haben SSPR registriert ({$rate}%)."]);
            }
            if ($rate >= 1) {
                return array_merge($base, ['status' => 'warn', 'detail' => "Nur {$rate}% ({$ssprCount}/{$total}) haben SSPR registriert — Ziel: >70%."]);
            }
            return array_merge($base, ['status' => 'fail', 'detail' => 'Kein Benutzer hat SSPR registriert. SSPR-Einführung empfohlen.']);
        } catch (\Throwable) {
            return array_merge($base, ['status' => 'unknown', 'detail' => 'Berechtigung fehlt (Reports.Read.All) oder SSPR nicht lizenziert.']);
        }
    }

    private function checkCaSessionControls(array $policies): array
    {
        $base = [
            'id'          => 'ca_session_controls',
            'category'    => 'Conditional Access',
            'label'       => 'CA-Sitzungssteuerung',
            'description' => 'CA-Richtlinie begrenzt Sitzungsdauer oder verhindert persistente Browser-Sitzungen.',
            'severity'    => 'low',
        ];
        foreach ($policies as $p) {
            if (strtolower($p['state'] ?? '') !== 'enabled') {
                continue;
            }
            $session = $p['sessionControls'] ?? [];
            $signInFreq  = $session['signInFrequency'] ?? null;
            $persistent  = $session['persistentBrowser'] ?? null;

            $hasFreq = ($signInFreq['isEnabled'] ?? false) === true;
            $hasNoPersist = strtolower($persistent['mode'] ?? '') === 'never';

            if ($hasFreq || $hasNoPersist) {
                return array_merge($base, ['status' => 'pass', 'detail' => 'Aktive CA-Richtlinie steuert Sitzungslebensdauer oder persistente Sitzungen.']);
            }
        }
        return array_merge($base, ['status' => 'fail', 'detail' => 'Keine CA-Richtlinie kontrolliert Sitzungsdauer oder Browser-Persistenz.']);
    }

    private function checkPimAdoption(): array
    {
        $base = [
            'id'          => 'pim_adoption',
            'category'    => 'Konfiguration & Apps',
            'label'       => 'Privileged Identity Management (PIM)',
            'description' => 'Just-in-Time Admin-Zugriff durch PIM-berechtigte Rollenzuweisungen.',
            'severity'    => 'medium',
        ];
        try {
            $data  = $this->graph->getEventual(
                '/roleManagement/directory/roleEligibilityScheduleInstances',
                ['$count' => 'true', '$top' => '1', '$select' => 'id'],
                'pim_eligibility_count', 1800
            );
            $count = (int)($data['@odata.count'] ?? count($data['value'] ?? []));
            if ($count > 0) {
                return array_merge($base, ['status' => 'pass', 'detail' => "{$count} PIM-berechtigte Rollenzuweisung(en) aktiv — Just-in-Time-Zugriff wird genutzt."]);
            }
            return array_merge($base, ['status' => 'fail', 'detail' => 'Keine PIM-berechtigten Rollenzuweisungen. Alle Admins haben dauerhaften Zugriff.']);
        } catch (\Throwable) {
            return array_merge($base, ['status' => 'unknown', 'detail' => 'Berechtigung fehlt (RoleManagement.Read.Directory) oder Entra ID P2 nicht lizenziert.']);
        }
    }

    private function checkAppConsentPolicy(?array $authPolicy): array
    {
        $base = [
            'id'          => 'app_consent_policy',
            'category'    => 'Konfiguration & Apps',
            'label'       => 'App-Zustimmungsrichtlinie',
            'description' => 'Benutzer dürfen nicht ohne Admin-Genehmigung OAuth-Berechtigungen vergeben.',
            'severity'    => 'high',
        ];
        if ($authPolicy === null) {
            return array_merge($base, ['status' => 'unknown', 'detail' => 'Berechtigung fehlt (Policy.Read.All erforderlich).']);
        }
        try {
            $policies  = (array)($authPolicy['permissionGrantPolicyIdsAssignedToDefaultUserRole'] ?? []);

            if (empty($policies)) {
                return array_merge($base, ['status' => 'pass', 'detail' => 'Benutzer können keinen Apps ohne Admin-Genehmigung zustimmen — optimal.']);
            }
            // Check for legacy broad consent
            $hasLegacy = !empty(array_filter($policies, fn($p) => str_contains(strtolower($p), 'legacy') && !str_contains(strtolower($p), 'admin')));
            $hasLowRisk = !empty(array_filter($policies, fn($p) => str_contains(strtolower($p), 'low-risk') || str_contains(strtolower($p), 'lowrisk')));

            if ($hasLegacy) {
                return array_merge($base, ['status' => 'fail', 'detail' => 'Benutzer dürfen beliebigen Apps zustimmen (legacy consent policy). Consent-Phishing-Risiko.']);
            }
            if ($hasLowRisk) {
                return array_merge($base, ['status' => 'warn', 'detail' => 'Benutzer dürfen risikoarmen Apps zustimmen. Admin-Consent-Workflow für alle empfohlen.']);
            }
            return array_merge($base, ['status' => 'warn', 'detail' => 'Consent-Richtlinie vorhanden — manuelle Überprüfung empfohlen: ' . implode(', ', $policies)]);
        } catch (\Throwable) {
            return array_merge($base, ['status' => 'unknown', 'detail' => 'Berechtigung fehlt oder API-Fehler.']);
        }
    }

    private function checkExternalCollabPolicy(?array $authPolicy): array
    {
        $base = [
            'id'          => 'external_collab_policy',
            'category'    => 'Konfiguration & Apps',
            'label'       => 'Gasteinladungsrichtlinie',
            'description' => 'Wer darf externe Gastbenutzer in den Tenant einladen.',
            'severity'    => 'medium',
        ];
        if ($authPolicy === null) {
            return array_merge($base, ['status' => 'unknown', 'detail' => 'Berechtigung fehlt (Policy.Read.All erforderlich).']);
        }
        try {
            $setting = strtolower($authPolicy['allowInvitesFrom'] ?? '');
            $labels  = [
                'none'                                 => 'Niemand darf einladen — sehr restriktiv.',
                'adminsandguestinviters'               => 'Nur Admins und Gast-Einlader dürfen einladen — empfohlen.',
                'adminsguestinvitersandallmembers'     => 'Alle Mitglieder dürfen einladen — moderat.',
                'everyone'                             => 'Jeder (inkl. Gäste) darf einladen — unsicher.',
            ];
            $detail = $labels[$setting] ?? "Einstellung: {$setting}";

            if (in_array($setting, ['none', 'adminsandguestinviters'], true)) {
                return array_merge($base, ['status' => 'pass', 'detail' => $detail]);
            }
            if ($setting === 'adminsguestinvitersandallmembers') {
                return array_merge($base, ['status' => 'warn', 'detail' => $detail . ' Empfehlung: auf Admins und Gast-Einlader einschränken.']);
            }
            return array_merge($base, ['status' => 'fail', 'detail' => $detail . ' Einschränkung auf Admins dringend empfohlen.']);
        } catch (\Throwable) {
            return array_merge($base, ['status' => 'unknown', 'detail' => 'Berechtigung fehlt oder API-Fehler.']);
        }
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function fetchCaPolicies(): array
    {
        try {
            $data = $this->graph->get('/identity/conditionalAccessPolicies', ['$top' => '200'], 'ca_policies', 900);
            return $data['value'] ?? [];
        } catch (\Throwable) {
            return [];
        }
    }

    private function fetchAuthorizationPolicy(): ?array
    {
        try {
            $data = $this->graph->get('/policies/authorizationPolicy', [], 'authorization_policy', 3600);
            // May be wrapped in value array or returned directly
            if (isset($data['value']) && is_array($data['value'])) {
                return $data['value'][0] ?? null;
            }
            return isset($data['allowInvitesFrom']) ? $data : null;
        } catch (\Throwable) {
            return null;
        }
    }
}
