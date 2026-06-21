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

            // E-Mail & Endpoint-Schutz
            $this->checkBreakGlass(),
            $this->checkDefenderForOffice(),

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

            // DSGVO / Datenschutz
            $this->checkGdprTenantRegion(),
            $this->checkGdprSharePointSharing(),
            $this->checkGdprAnonymousLinkExpiry(),
            $this->checkGdprDefaultSharingLink(),
            $this->checkGdprSensitivityLabels(),
            $this->checkGdprRetentionPolicies(),
            $this->checkGdprAuditLogReachable(),
            $this->checkGdprDlpOrLabelsActive(),
        ];
    }

    /**
     * Cached variant of runChecks(). The full posture is expensive (~35 checks,
     * many Graph calls including full user enumeration), so the *computed
     * result* is cached as a whole rather than relying on per-call caching.
     * Page loads within the TTL are then instant. A flushed cache (?refresh)
     * or the cache-warm cron transparently recompute it.
     */
    public function runChecksCached(int $ttlSeconds = 1800): array
    {
        $cached = $this->graph->getCache()->remember(
            'posture_checks_full',
            fn() => $this->runChecks(),
            $ttlSeconds
        );
        return is_array($cached) && $cached ? $cached : $this->runChecks();
    }

    /**
     * Read the cached posture without ever computing it live. Returns null on a
     * cold cache — used by the Action Center so the landing page never blocks
     * on the (slow) full posture computation; the cache-warm cron fills it.
     */
    public function cachedChecks(): ?array
    {
        $c = $this->graph->getCache()->get('posture_checks_full');
        return is_array($c) && $c ? $c : null;
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
                    'title'       => t('MFA für alle Benutzer erzwingen'),
                    'description' => t('Keine aktive Conditional-Access-Richtlinie verlangt MFA für alle Benutzer. Ohne diese Richtlinie können Konten allein durch gestohlene Passwörter kompromittiert werden.'),
                    'action'      => t('Richtlinie erstellen'),
                    'module_url'  => '/conditionalaccess',
                    'ca_template' => 'mfa_all',
                ],
                'warn' => [
                    'priority'    => 'high',
                    'title'       => t('MFA-Richtlinie aktivieren (Report-Modus)'),
                    'description' => t('Eine MFA-Richtlinie ist im Report-Modus vorhanden, erzwingt MFA aber noch nicht. Aktivierung erforderlich.'),
                    'action'      => t('Zu CA-Richtlinien'),
                    'module_url'  => '/conditionalaccess',
                    'ca_template' => null,
                ],
            ],
            'ca_admin_mfa' => [
                'fail' => [
                    'priority'    => 'critical',
                    'title'       => t('Administratoren durch dedizierte MFA-Richtlinie schützen'),
                    'description' => t('Keine aktive CA-Richtlinie erzwingt MFA explizit für Admin-Rollen. Administratorkonten sind besonders hochwertige Angriffsziele.'),
                    'action'      => t('Richtlinie erstellen'),
                    'module_url'  => '/conditionalaccess',
                    'ca_template' => 'mfa_admins',
                ],
            ],
            'legacy_auth_blocked' => [
                'fail' => [
                    'priority'    => 'critical',
                    'title'       => t('Legacy-Authentifizierung blockieren'),
                    'description' => t('Ältere Protokolle (IMAP, POP3, SMTP AUTH, MAPI) umgehen MFA vollständig. Über 99% der Passwort-Spray-Angriffe nutzen Legacy-Auth.'),
                    'action'      => t('Richtlinie erstellen'),
                    'module_url'  => '/conditionalaccess',
                    'ca_template' => 'block_legacy',
                ],
                'warn' => [
                    'priority'    => 'high',
                    'title'       => t('Legacy-Auth-Blockierung aktivieren'),
                    'description' => t('Die Richtlinie zum Blockieren von Legacy-Authentifizierung ist nur im Report-Modus. Vollständige Aktivierung erforderlich.'),
                    'action'      => t('Zu CA-Richtlinien'),
                    'module_url'  => '/conditionalaccess',
                    'ca_template' => null,
                ],
            ],
            'sign_in_risk_policy' => [
                'fail' => [
                    'priority'    => 'high',
                    'title'       => t('Risikobasierte Anmelderichtlinie einrichten'),
                    'description' => t('Keine CA-Richtlinie reagiert auf Anmelderisiken (verdächtige IPs, unmögliche Reisen). Microsoft Entra erkennt diese Muster automatisch, sie werden aber nicht genutzt.'),
                    'action'      => t('Zu CA-Richtlinien'),
                    'module_url'  => '/conditionalaccess',
                    'ca_template' => null,
                ],
            ],
            'user_risk_policy' => [
                'fail' => [
                    'priority'    => 'high',
                    'title'       => t('Benutzerrisiko-Richtlinie einrichten'),
                    'description' => t('Keine CA-Richtlinie reagiert auf hohes Benutzerrisiko (geleakte Credentials, kompromittierte Konten). Betroffene Benutzer können ungehindert weiterarbeiten.'),
                    'action'      => t('Zu CA-Richtlinien'),
                    'module_url'  => '/conditionalaccess',
                    'ca_template' => null,
                ],
            ],
            'mfa_registration_rate' => [
                'fail' => [
                    'priority'    => 'high',
                    'title'       => t('MFA-Registrierungsrate erhöhen'),
                    'description' => t('Weniger als 75% der Benutzer haben MFA registriert. Kampagne zur MFA-Einrichtung starten oder Registrierung per CA erzwingen.'),
                    'action'      => t('MFA-Übersicht'),
                    'module_url'  => '/mfamethods',
                    'ca_template' => null,
                ],
                'warn' => [
                    'priority'    => 'medium',
                    'title'       => t('MFA-Registrierungsrate verbessern'),
                    'description' => t('Die MFA-Registrierungsrate liegt unter 95%. Nicht registrierte Benutzer identifizieren und zur Registrierung auffordern.'),
                    'action'      => t('Nicht registrierte anzeigen'),
                    'module_url'  => '/mfamethods',
                    'ca_template' => null,
                ],
            ],
            'risky_users_open' => [
                'fail' => [
                    'priority'    => 'high',
                    'title'       => t('Risikobehaftete Benutzer untersuchen'),
                    'description' => t('Mehrere Benutzer haben einen aktiven Risikostatus (atRisk). Diese Konten sind möglicherweise kompromittiert und benötigen sofortige Überprüfung.'),
                    'action'      => t('Risikobenutzer anzeigen'),
                    'module_url'  => '/hardening',
                    'ca_template' => null,
                ],
                'warn' => [
                    'priority'    => 'medium',
                    'title'       => t('Risikobenutzer überprüfen'),
                    'description' => t('Einige Benutzer haben einen aktiven Risikostatus. Überprüfung und ggf. Kennwortänderung empfohlen.'),
                    'action'      => t('Risikobenutzer anzeigen'),
                    'module_url'  => '/hardening',
                    'ca_template' => null,
                ],
            ],
            'device_compliance_rate' => [
                'fail' => [
                    'priority'    => 'medium',
                    'title'       => t('Geräte-Compliance-Rate verbessern'),
                    'description' => t('Mehr als 20% der verwalteten Geräte sind nicht konform. Nicht konforme Geräte sollten keinen Zugriff auf Unternehmensressourcen erhalten.'),
                    'action'      => t('Geräte anzeigen'),
                    'module_url'  => '/devices',
                    'ca_template' => null,
                ],
            ],
            'ca_device_compliance' => [
                'fail' => [
                    'priority'    => 'medium',
                    'title'       => t('Gerätekonformität in Conditional Access erzwingen'),
                    'description' => t('Keine CA-Richtlinie verlangt konforme oder Hybrid-Azure-AD-joinete Geräte. Ermöglicht Zugriff von unverwalteten Privatgeräten.'),
                    'action'      => t('Zu CA-Richtlinien'),
                    'module_url'  => '/conditionalaccess',
                    'ca_template' => null,
                ],
            ],
            'defender_alerts' => [
                'fail' => [
                    'priority'    => 'high',
                    'title'       => t('Offene Defender-Alerts bearbeiten'),
                    'description' => t('Es gibt viele ungelöste Microsoft Defender-Sicherheitswarnungen. Alerts sollten zeitnah untersucht und geschlossen werden.'),
                    'action'      => t('Alerts anzeigen'),
                    'module_url'  => '/hardening',
                    'ca_template' => null,
                ],
                'warn' => [
                    'priority'    => 'medium',
                    'title'       => t('Offene Defender-Alerts prüfen'),
                    'description' => t('Einige ungelöste Microsoft Defender-Sicherheitswarnungen vorhanden.'),
                    'action'      => t('Alerts anzeigen'),
                    'module_url'  => '/hardening',
                    'ca_template' => null,
                ],
            ],
            'secure_score' => [
                'fail' => [
                    'priority'    => 'medium',
                    'title'       => t('Microsoft Secure Score verbessern'),
                    'description' => t('Der Secure Score liegt unter 30%. Microsoft empfiehlt konkrete Maßnahmen im Security-Portal. Höchst-Priorität-Empfehlungen zuerst umsetzen.'),
                    'action'      => t('Sicherheitsmodul'),
                    'module_url'  => '/hardening',
                    'ca_template' => null,
                ],
                'warn' => [
                    'priority'    => 'low',
                    'title'       => t('Secure Score weiter verbessern'),
                    'description' => t('Der Secure Score liegt unter 50%. Weitere Empfehlungen aus dem Microsoft Security Center umsetzen.'),
                    'action'      => t('Sicherheitsmodul'),
                    'module_url'  => '/hardening',
                    'ca_template' => null,
                ],
            ],
            'admin_count' => [
                'fail' => [
                    'priority'    => 'high',
                    'title'       => t('Anzahl globaler Administratoren reduzieren'),
                    'description' => t('Mehr als 5 aktive globale Administratoren erhöhen das Angriffsrisiko erheblich. Microsoft empfiehlt max. 2-4 globale Admins und die Nutzung von Least-Privilege-Rollen.'),
                    'action'      => t('Benutzerrollen prüfen'),
                    'module_url'  => '/users',
                    'ca_template' => null,
                ],
                'warn' => [
                    'priority'    => 'low',
                    'title'       => t('Globale Administratoren auf Notwendigkeit prüfen'),
                    'description' => t('Es gibt 3-5 globale Administratoren. Prüfen ob Least-Privilege-Rollen ausreichen würden.'),
                    'action'      => t('Benutzerrollen prüfen'),
                    'module_url'  => '/users',
                    'ca_template' => null,
                ],
            ],
            'named_locations' => [
                'fail' => [
                    'priority'    => 'low',
                    'title'       => t('Vertrauenswürdige Standorte konfigurieren'),
                    'description' => t('Keine Named Locations konfiguriert. Vertrauenswürdige IP-Bereiche und Länder ermöglichen differenziertere CA-Richtlinien.'),
                    'action'      => t('Standorte konfigurieren'),
                    'module_url'  => '/namedlocations',
                    'ca_template' => null,
                ],
            ],
            'app_secrets_expiry' => [
                'fail' => [
                    'priority'    => 'medium',
                    'title'       => t('Abgelaufene App-Secrets erneuern'),
                    'description' => t('Abgelaufene App-Secrets können Dienste unterbrechen oder eine Sicherheitslücke darstellen wenn Rotationszyklen nicht eingehalten werden.'),
                    'action'      => t('App-Registrierungen'),
                    'module_url'  => '/hardening',
                    'ca_template' => null,
                ],
                'warn' => [
                    'priority'    => 'medium',
                    'title'       => t('App-Secrets bald ablaufend — erneuern'),
                    'description' => t('Einige App-Secrets laufen in weniger als 30 Tagen ab. Jetzt erneuern um Dienstunterbrechungen zu vermeiden.'),
                    'action'      => t('App-Registrierungen'),
                    'module_url'  => '/hardening',
                    'ca_template' => null,
                ],
            ],
            'no_stale_licensed' => [
                'fail' => [
                    'priority'    => 'medium',
                    'title'       => t('Inaktive lizenzierte Konten bereinigen'),
                    'description' => t('Mehrere aktive, lizenzierte Benutzer haben sich seit über 90 Tagen nicht angemeldet. Ungenutzte Konten sollten deaktiviert und Lizenzen freigegeben werden.'),
                    'action'      => t('Benutzer prüfen'),
                    'module_url'  => '/users',
                    'ca_template' => null,
                ],
                'warn' => [
                    'priority'    => 'low',
                    'title'       => t('Inaktive Benutzerkonten überprüfen'),
                    'description' => t('Einige lizenzierte Benutzer waren über 90 Tage inaktiv.'),
                    'action'      => t('Benutzer prüfen'),
                    'module_url'  => '/users',
                    'ca_template' => null,
                ],
            ],
            'guest_user_count' => [
                'fail' => [
                    'priority'    => 'medium',
                    'title'       => t('Gastbenutzer überprüfen und bereinigen'),
                    'description' => t('Viele aktive Gastbenutzer können das Risiko unbeabsichtigter Datenweitergabe erhöhen. Regelmäßige Zugriffsüberprüfungen (Access Reviews) empfohlen.'),
                    'action'      => t('Benutzer anzeigen'),
                    'module_url'  => '/users',
                    'ca_template' => null,
                ],
            ],
            'passwordless_capable' => [
                'fail' => [
                    'priority'    => 'low',
                    'title'       => t('Passwortlose Authentifizierung einführen'),
                    'description' => t('Noch keine Benutzer haben passwortlose Methoden (FIDO2, Windows Hello, Microsoft Authenticator Passwordless) registriert. Diese sind phishing-sicherer als klassische MFA.'),
                    'action'      => t('MFA-Methoden'),
                    'module_url'  => '/mfamethods',
                    'ca_template' => null,
                ],
            ],
            'security_defaults' => [
                'fail' => [
                    'priority'    => 'critical',
                    'title'       => t('Basis-Schutz fehlt — weder Security Defaults noch CA aktiv'),
                    'description' => t('Weder Security Defaults noch Conditional-Access-Richtlinien sind aktiv. Der Tenant hat keinen automatisierten Basisschutz gegen gängige Angriffe.'),
                    'action'      => t('CA-Richtlinien einrichten'),
                    'module_url'  => '/conditionalaccess',
                    'ca_template' => null,
                ],
                'warn' => [
                    'priority'    => 'medium',
                    'title'       => t('Security Defaults und CA gleichzeitig aktiv'),
                    'description' => t('Security Defaults und eigene CA-Richtlinien sind gleichzeitig aktiv. Dies kann zu Konflikten führen. Security Defaults deaktivieren und vollständig auf CA setzen.'),
                    'action'      => t('Zu CA-Richtlinien'),
                    'module_url'  => '/conditionalaccess',
                    'ca_template' => null,
                ],
            ],
            'admins_mfa_registered' => [
                'fail' => [
                    'priority'    => 'critical',
                    'title'       => t('Globale Admins ohne MFA-Registrierung'),
                    'description' => t('Mindestens ein globaler Administrator hat keine MFA-Methode registriert. Admin-Konten ohne MFA sind das größte Einzelrisiko in einem M365-Tenant.'),
                    'action'      => t('MFA-Status prüfen'),
                    'module_url'  => '/mfamethods',
                    'ca_template' => null,
                ],
                'warn' => [
                    'priority'    => 'high',
                    'title'       => t('Admin-MFA-Status überprüfen'),
                    'description' => t('MFA-Daten für globale Administratoren konnten nicht vollständig verifiziert werden.'),
                    'action'      => t('MFA-Status prüfen'),
                    'module_url'  => '/mfamethods',
                    'ca_template' => null,
                ],
            ],
            'app_consent_policy' => [
                'fail' => [
                    'priority'    => 'high',
                    'title'       => t('Benutzer dürfen beliebigen Apps zustimmen'),
                    'description' => t('Die aktuelle App-Zustimmungsrichtlinie erlaubt Benutzern, OAuth-Berechtigungen an Drittanbieter-Apps zu vergeben. Dies ermöglicht OAuth-Phishing-Angriffe (Consent Phishing).'),
                    'action'      => t('Richtlinie prüfen'),
                    'module_url'  => '/settings',
                    'ca_template' => null,
                ],
                'warn' => [
                    'priority'    => 'medium',
                    'title'       => t('App-Zustimmungsrichtlinie einschränken'),
                    'description' => t('Benutzer können bestimmten Apps ohne Admin-Genehmigung zustimmen. Admin-Consent-Workflow aktivieren um alle Zustimmungen zu kontrollieren.'),
                    'action'      => t('Richtlinie prüfen'),
                    'module_url'  => '/settings',
                    'ca_template' => null,
                ],
            ],
            'external_collab_policy' => [
                'fail' => [
                    'priority'    => 'medium',
                    'title'       => t('Einladungsrichtlinie für Gäste einschränken'),
                    'description' => t('Jeder (auch externe Gäste) kann neue Gäste in den Tenant einladen. Einladungen sollten auf Admins und Gast-Einlader begrenzt werden.'),
                    'action'      => t('Richtlinie prüfen'),
                    'module_url'  => '/settings',
                    'ca_template' => null,
                ],
                'warn' => [
                    'priority'    => 'low',
                    'title'       => t('Gasteinladungen nur durch Admins erlauben'),
                    'description' => t('Alle Benutzer dürfen Gäste einladen. Empfehlung: nur Admins und dedizierte Gast-Einlader.'),
                    'action'      => t('Richtlinie prüfen'),
                    'module_url'  => '/settings',
                    'ca_template' => null,
                ],
            ],
            'sspr_adoption' => [
                'fail' => [
                    'priority'    => 'medium',
                    'title'       => t('Self-Service Password Reset (SSPR) einführen'),
                    'description' => t('Kein Benutzer hat SSPR registriert. SSPR reduziert Helpdesk-Aufwand und verhindert dass Benutzer unsichere Passwort-Reset-Wege nutzen.'),
                    'action'      => t('MFA-Methoden'),
                    'module_url'  => '/mfamethods',
                    'ca_template' => null,
                ],
                'warn' => [
                    'priority'    => 'low',
                    'title'       => t('SSPR-Registrierungsrate verbessern'),
                    'description' => t('Weniger als 50% der Benutzer haben SSPR registriert. Fehlende Registrierungen erhöhen Helpdesk-Last.'),
                    'action'      => t('MFA-Methoden'),
                    'module_url'  => '/mfamethods',
                    'ca_template' => null,
                ],
            ],
            'ca_session_controls' => [
                'fail' => [
                    'priority'    => 'low',
                    'title'       => t('Sitzungslebensdauer einschränken (CA)'),
                    'description' => t('Keine CA-Richtlinie erzwingt eine maximale Sitzungsdauer oder verhindert persistente Browser-Sitzungen. Lang lebende Tokens erhöhen das Risiko bei gestohlenen Refresh-Tokens.'),
                    'action'      => t('Zu CA-Richtlinien'),
                    'module_url'  => '/conditionalaccess',
                    'ca_template' => null,
                ],
            ],
            'pim_adoption' => [
                'fail' => [
                    'priority'    => 'medium',
                    'title'       => t('Privileged Identity Management (PIM) einführen'),
                    'description' => t('Keine PIM-berechtigten Rollenzuweisungen gefunden. PIM ermöglicht Just-in-Time-Zugriff für Admin-Rollen — Admins sind nur aktiv wenn nötig, mit Genehmigungsprozess und Audit-Trail.'),
                    'action'      => t('Benutzer & Rollen'),
                    'module_url'  => '/users',
                    'ca_template' => null,
                ],
            ],
            'break_glass' => [
                'fail' => [
                    'priority'    => 'high',
                    'title'       => t('Notfallzugangskonto (Break-Glass) fehlt'),
                    'description' => t('Kein globales Admin-Konto ohne Lizenz gefunden. Microsoft empfiehlt mindestens 2 dedizierte Notfallkonten: cloud-only, kein MFA, keine Lizenz, starkes Passwort offline hinterlegt — für den Fall dass MFA oder CA nicht funktionieren.'),
                    'action'      => t('Admin-Rollen prüfen'),
                    'module_url'  => '/adminroles',
                    'ca_template' => null,
                ],
                'warn' => [
                    'priority'    => 'medium',
                    'title'       => t('Nur 1 Notfallkonto konfiguriert'),
                    'description' => t('Nur ein potenzielles Notfallkonto gefunden. Microsoft empfiehlt mindestens 2 unabhängige Break-Glass-Konten für Redundanz.'),
                    'action'      => t('Admin-Rollen prüfen'),
                    'module_url'  => '/adminroles',
                    'ca_template' => null,
                ],
            ],
            'defender_for_office' => [
                'fail' => [
                    'priority'    => 'high',
                    'title'       => t('Defender for Office 365 nicht lizenziert'),
                    'description' => t('Kein Microsoft Defender for Office 365 Abonnement gefunden. Safe Links, Safe Attachments und Anti-Phishing-Schutz sind nicht verfügbar — ein kritisches Sicherheitsrisiko für E-Mail-Angriffe.'),
                    'action'      => t('Lizenzen prüfen'),
                    'module_url'  => '/licenses',
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
            'label'       => t('MFA-Registrierungsrate'),
            'description' => t('Anteil der Benutzer mit registrierter MFA-Methode.'),
            'severity'    => 'high',
        ];
        try {
            $users      = $this->graph->paginate(
                '/reports/authenticationMethods/userRegistrationDetails',
                ['$select' => 'id,isMfaRegistered', '$top' => '999'],
                50, 'dash_mfa_registered', 1800
            );
            $total      = count($users);
            $registered = count(array_filter($users, fn($u) => $u['isMfaRegistered'] ?? false));
            $rate       = $total > 0 ? round($registered / $total * 100, 1) : 0;
            if ($rate >= 95) {
                return array_merge($base, ['status' => 'pass', 'detail' => t(':registered/:total Benutzer haben MFA registriert (:rate%).', ['registered' => $registered, 'total' => $total, 'rate' => $rate])]);
            }
            if ($rate >= 75) {
                return array_merge($base, ['status' => 'warn', 'detail' => t('MFA-Registrierungsrate: :rate% (:registered/:total) — Ziel ist ≥95%.', ['rate' => $rate, 'registered' => $registered, 'total' => $total])]);
            }
            return array_merge($base, ['status' => 'fail', 'detail' => t('Niedrige MFA-Registrierungsrate: :rate% (:registered/:total).', ['rate' => $rate, 'registered' => $registered, 'total' => $total])]);
        } catch (\Throwable) {
            return array_merge($base, ['status' => 'unknown', 'detail' => t('Berechtigung fehlt oder API-Fehler.')]);
        }
    }

    private function checkCaMfaAllUsers(array $policies): array
    {
        $base = [
            'id'          => 'ca_mfa_all_users',
            'category'    => 'Conditional Access',
            'label'       => t('MFA für alle Benutzer (CA)'),
            'description' => t('Aktive CA-Richtlinie, die MFA für alle oder die meisten Benutzer verlangt.'),
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
            return array_merge($base, ['status' => 'pass', 'detail' => t('Aktive CA-Richtlinie verlangt MFA für alle Benutzer.')]);
        }
        if ($foundReport) {
            return array_merge($base, ['status' => 'warn', 'detail' => t('MFA-Richtlinie existiert, ist aber nur im Report-Modus — noch nicht aktiv.')]);
        }
        return array_merge($base, ['status' => 'fail', 'detail' => t('Keine aktive CA-Richtlinie erzwingt MFA für alle Benutzer.')]);
    }

    private function checkCaAdminMfa(array $policies): array
    {
        $base = [
            'id'          => 'ca_admin_mfa',
            'category'    => 'Conditional Access',
            'label'       => t('MFA für Administratoren (CA)'),
            'description' => t('Aktive CA-Richtlinie, die MFA explizit für Admin-Rollen verlangt.'),
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
                return array_merge($base, ['status' => 'pass', 'detail' => t('Aktive CA-Richtlinie verlangt MFA für Admin-Rollen oder alle Benutzer.')]);
            }
        }
        return array_merge($base, ['status' => 'fail', 'detail' => t('Keine aktive CA-Richtlinie schützt explizit Admin-Rollen mit MFA.')]);
    }

    private function checkPasswordlessCapable(): array
    {
        $base = [
            'id'          => 'passwordless_capable',
            'category'    => 'Identität & MFA',
            'label'       => t('Passwortlose Authentifizierung'),
            'description' => t('Mindestens ein Benutzer nutzt FIDO2, Windows Hello oder Authenticator Passwordless.'),
            'severity'    => 'low',
        ];
        try {
            $users    = $this->graph->paginate(
                '/reports/authenticationMethods/userRegistrationDetails',
                ['$select' => 'id,isPasswordlessCapable', '$top' => '999'],
                50, 'dash_passwordless_capable', 1800
            );
            $capable = count(array_filter($users, fn($u) => $u['isPasswordlessCapable'] ?? false));
            $total   = count($users);
            if ($capable > 0) {
                return array_merge($base, ['status' => 'pass', 'detail' => t(':capable von :total Benutzer(n) sind für passwortlose Anmeldung registriert.', ['capable' => $capable, 'total' => $total])]);
            }
            return array_merge($base, ['status' => 'fail', 'detail' => t('Noch kein Benutzer hat eine passwortlose Methode (FIDO2, Windows Hello, Passwordless) registriert.')]);
        } catch (\Throwable) {
            return array_merge($base, ['status' => 'unknown', 'detail' => t('Berechtigung fehlt oder API-Fehler.')]);
        }
    }

    private function checkRiskyUsersOpen(): array
    {
        $base = [
            'id'          => 'risky_users_open',
            'category'    => 'Identität & MFA',
            'label'       => t('Risikobenutzer (atRisk)'),
            'description' => t('Anzahl der Benutzer mit aktivem Risikostatus in Entra Identity Protection.'),
            'severity'    => 'medium',
        ];
        try {
            $data  = $this->graph->getEventual(
                '/identityProtection/riskyUsers',
                ['$count' => 'true', '$top' => '1', '$select' => 'id', '$filter' => "riskState eq 'atRisk'"],
                'dash_risky', 300
            );
            // A swallowed 403 yields count 0 — don't report that as a clean "pass".
            if ($this->graph->getLastError() !== null) {
                return array_merge($base, ['status' => 'unknown', 'detail' => t('Berechtigung fehlt (IdentityRiskyUser.Read.All erforderlich).')]);
            }
            $count = (int)($data['@odata.count'] ?? count($data['value'] ?? []));
            if ($count === 0) {
                return array_merge($base, ['status' => 'pass', 'detail' => t('Keine Benutzer mit aktivem Risikostatus.')]);
            }
            if ($count <= 5) {
                return array_merge($base, ['status' => 'warn', 'detail' => t(':count Benutzer mit aktivem Risikostatus — Überprüfung empfohlen.', ['count' => $count])]);
            }
            return array_merge($base, ['status' => 'fail', 'detail' => t(':count Benutzer mit aktivem Risikostatus erfordern sofortige Aufmerksamkeit.', ['count' => $count])]);
        } catch (\Throwable) {
            return array_merge($base, ['status' => 'unknown', 'detail' => t('Berechtigung fehlt (IdentityRiskyUser.Read.All erforderlich).')]);
        }
    }

    private function checkLegacyAuthBlocked(array $policies): array
    {
        $base = [
            'id'          => 'legacy_auth_blocked',
            'category'    => 'Conditional Access',
            'label'       => t('Legacy-Authentifizierung blockiert'),
            'description' => t('CA-Richtlinie blockiert ältere Protokolle (IMAP, POP3, SMTP AUTH, MAPI).'),
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
            return array_merge($base, ['status' => 'pass', 'detail' => t('Aktive CA-Richtlinie blockiert Legacy-Authentifizierung.')]);
        }
        if ($foundReport) {
            return array_merge($base, ['status' => 'warn', 'detail' => t('Legacy-Auth-Block im Report-Modus — noch nicht aktiv.')]);
        }
        return array_merge($base, ['status' => 'fail', 'detail' => t('Keine CA-Richtlinie blockiert Legacy-Authentifizierung.')]);
    }

    private function checkSignInRiskPolicy(array $policies): array
    {
        $base = [
            'id'          => 'sign_in_risk_policy',
            'category'    => 'Conditional Access',
            'label'       => t('Anmelderisiko-Richtlinie (CA)'),
            'description' => t('Aktive CA-Richtlinie reagiert auf mittleres/hohes Anmelderisiko.'),
            'severity'    => 'medium',
        ];
        foreach ($policies as $p) {
            if (strtolower($p['state'] ?? '') !== 'enabled') {
                continue;
            }
            $riskLevels = array_map('strtolower', (array)($p['conditions']['signInRiskLevels'] ?? []));
            if (!empty(array_intersect($riskLevels, ['medium', 'high']))) {
                return array_merge($base, ['status' => 'pass', 'detail' => t('Aktive CA-Richtlinie reagiert auf Anmelderisiko (mittel/hoch).')]);
            }
        }
        return array_merge($base, ['status' => 'fail', 'detail' => t('Keine CA-Richtlinie reagiert auf Anmelderisiken. Benötigt Entra ID P2.')]);
    }

    private function checkUserRiskPolicy(array $policies): array
    {
        $base = [
            'id'          => 'user_risk_policy',
            'category'    => 'Conditional Access',
            'label'       => t('Benutzerrisiko-Richtlinie (CA)'),
            'description' => t('Aktive CA-Richtlinie reagiert auf hohes Benutzerrisiko (kompromittierte Konten).'),
            'severity'    => 'medium',
        ];
        foreach ($policies as $p) {
            if (strtolower($p['state'] ?? '') !== 'enabled') {
                continue;
            }
            $riskLevels = array_map('strtolower', (array)($p['conditions']['userRiskLevels'] ?? []));
            if (!empty(array_intersect($riskLevels, ['medium', 'high']))) {
                return array_merge($base, ['status' => 'pass', 'detail' => t('Aktive CA-Richtlinie reagiert auf hohes Benutzerrisiko.')]);
            }
        }
        return array_merge($base, ['status' => 'fail', 'detail' => t('Keine CA-Richtlinie reagiert auf Benutzerrisiken. Benötigt Entra ID P2.')]);
    }

    private function checkCaDeviceCompliance(array $policies): array
    {
        $base = [
            'id'          => 'ca_device_compliance',
            'category'    => 'Conditional Access',
            'label'       => t('Gerätekonformität in CA'),
            'description' => t('CA-Richtlinie verlangt konforme oder Hybrid-AD-joinete Geräte.'),
            'severity'    => 'medium',
        ];
        foreach ($policies as $p) {
            if (strtolower($p['state'] ?? '') !== 'enabled') {
                continue;
            }
            $controls = array_map('strtolower', (array)($p['grantControls']['builtInControls'] ?? []));
            if (!empty(array_intersect($controls, ['compliantdevice', 'domainjoinedevice']))) {
                return array_merge($base, ['status' => 'pass', 'detail' => t('Aktive CA-Richtlinie fordert konforme/Hybrid-Geräte.')]);
            }
        }
        return array_merge($base, ['status' => 'fail', 'detail' => t('Keine CA-Richtlinie erzwingt Gerätekonformität.')]);
    }

    private function checkCaGuestRestriction(array $policies): array
    {
        $base = [
            'id'          => 'ca_guest_restriction',
            'category'    => 'Conditional Access',
            'label'       => t('Gastbenutzer-CA-Richtlinie'),
            'description' => t('Aktive CA-Richtlinie mit speziellen Bedingungen für Gastbenutzer.'),
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
                return array_merge($base, ['status' => 'pass', 'detail' => t('Aktive CA-Richtlinie adressiert Gastbenutzer spezifisch.')]);
            }
        }
        return array_merge($base, ['status' => 'warn', 'detail' => t('Keine CA-Richtlinie mit expliziten Bedingungen für Gastbenutzer gefunden.')]);
    }

    private function checkDeviceComplianceRate(): array
    {
        $base = [
            'id'          => 'device_compliance_rate',
            'category'    => 'Geräte & Compliance',
            'label'       => t('Geräte-Compliance-Rate'),
            'description' => t('Anteil der verwalteten Intune-Geräte, die konform sind.'),
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
                return array_merge($base, ['status' => 'unknown', 'detail' => t('Keine Intune-Geräte gefunden oder Berechtigung fehlt.')]);
            }
            $nonComp = $this->graph->getEventual(
                '/deviceManagement/managedDevices',
                ['$count' => 'true', '$top' => '1', '$select' => 'id', '$filter' => "complianceState eq 'noncompliant'"],
                'dash_noncompliant', 600
            );
            $nonCompliantCount = (int)($nonComp['@odata.count'] ?? count($nonComp['value'] ?? []));
            $rate = round(($total - $nonCompliantCount) / $total * 100, 1);
            if ($rate >= 90) {
                return array_merge($base, ['status' => 'pass', 'detail' => t(':rate% der Geräte sind konform (:nonCompliant nicht konform von :total).', ['rate' => $rate, 'nonCompliant' => $nonCompliantCount, 'total' => $total])]);
            }
            if ($rate >= 70) {
                return array_merge($base, ['status' => 'warn', 'detail' => t('Compliance-Rate: :rate% — :nonCompliant von :total Geräten nicht konform.', ['rate' => $rate, 'nonCompliant' => $nonCompliantCount, 'total' => $total])]);
            }
            return array_merge($base, ['status' => 'fail', 'detail' => t('Niedrige Compliance-Rate: :rate% — :nonCompliant von :total Geräten nicht konform.', ['rate' => $rate, 'nonCompliant' => $nonCompliantCount, 'total' => $total])]);
        } catch (\Throwable) {
            return array_merge($base, ['status' => 'unknown', 'detail' => t('Berechtigung fehlt (DeviceManagementManagedDevices.Read.All).')]);
        }
    }

    private function checkDefenderAlerts(): array
    {
        $base = [
            'id'          => 'defender_alerts',
            'category'    => 'Geräte & Compliance',
            'label'       => t('Offene Defender-Alerts'),
            'description' => t('Anzahl ungelöster Microsoft Defender-Sicherheitswarnungen.'),
            'severity'    => 'high',
        ];
        try {
            $data  = $this->graph->get(
                '/security/alerts_v2',
                ['$filter' => "status eq 'new' or status eq 'inProgress'", '$top' => '1', '$count' => 'true'],
                'dash_alerts', 300
            );
            if ($this->graph->getLastError() !== null) {
                return array_merge($base, ['status' => 'unknown', 'detail' => t('Berechtigung fehlt oder Defender nicht lizenziert.')]);
            }
            $count = (int)($data['@odata.count'] ?? count($data['value'] ?? []));
            if ($count === 0) {
                return array_merge($base, ['status' => 'pass', 'detail' => t('Keine offenen Defender-Sicherheitswarnungen.')]);
            }
            if ($count <= 5) {
                return array_merge($base, ['status' => 'warn', 'detail' => t(':count offene Sicherheitswarnung(en) — Überprüfung empfohlen.', ['count' => $count])]);
            }
            return array_merge($base, ['status' => 'fail', 'detail' => t(':count offene Sicherheitswarnungen erfordern Aufmerksamkeit.', ['count' => $count])]);
        } catch (\Throwable) {
            return array_merge($base, ['status' => 'unknown', 'detail' => t('Berechtigung fehlt oder Defender nicht lizenziert.')]);
        }
    }

    private function checkSecureScore(): array
    {
        $base = [
            'id'          => 'secure_score',
            'category'    => 'Konfiguration & Apps',
            'label'       => t('Microsoft Secure Score'),
            'description' => t('Secure Score als Prozentwert des erreichbaren Maximums (Ziel: >50%).'),
            'severity'    => 'medium',
        ];
        try {
            $data  = $this->graph->get('/security/secureScores', ['$top' => '1', '$select' => 'currentScore,maxScore'], 'securescore_latest', 3600);
            $items = $data['value'] ?? [];
            if (empty($items)) {
                return array_merge($base, ['status' => 'unknown', 'detail' => t('Keine Secure-Score-Daten verfügbar.')]);
            }
            $current = (float)($items[0]['currentScore'] ?? 0);
            $max     = (float)($items[0]['maxScore']     ?? 0);
            $pct     = $max > 0 ? round($current / $max * 100, 1) : 0;
            if ($pct > 50) {
                return array_merge($base, ['status' => 'pass', 'detail' => t('Secure Score: :current/:max Punkte (:pct%).', ['current' => $current, 'max' => $max, 'pct' => $pct])]);
            }
            if ($pct >= 30) {
                return array_merge($base, ['status' => 'warn', 'detail' => t('Secure Score: :current/:max Punkte (:pct%) — Verbesserungspotenzial.', ['current' => $current, 'max' => $max, 'pct' => $pct])]);
            }
            return array_merge($base, ['status' => 'fail', 'detail' => t('Niedriger Secure Score: :current/:max Punkte (:pct%).', ['current' => $current, 'max' => $max, 'pct' => $pct])]);
        } catch (\Throwable) {
            return array_merge($base, ['status' => 'unknown', 'detail' => t('Berechtigung fehlt oder API-Fehler.')]);
        }
    }

    private function checkAdminCount(): array
    {
        $base = [
            'id'          => 'admin_count',
            'category'    => 'Konfiguration & Apps',
            'label'       => t('Globale Administratoren'),
            'description' => t('Anzahl der Benutzer mit der Rolle "Globaler Administrator" (Ziel: max. 4).'),
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
                return array_merge($base, ['status' => 'unknown', 'detail' => t('Globale Administratoren-Rolle nicht gefunden.')]);
            }
            $roleId  = $roles[0]['id'];
            $members = $this->graph->get("/directoryRoles/{$roleId}/members", ['$select' => 'id'], "dir_role_members_{$roleId}", 1800);
            $count   = count($members['value'] ?? []);
            if ($count <= 2) {
                return array_merge($base, ['status' => 'pass', 'detail' => t(':count globale Administrator(en) — optimal (max. 4 empfohlen).', ['count' => $count])]);
            }
            if ($count <= 4) {
                return array_merge($base, ['status' => 'warn', 'detail' => t(':count globale Administratoren — akzeptabel, aber Least-Privilege prüfen.', ['count' => $count])]);
            }
            return array_merge($base, ['status' => 'fail', 'detail' => t(':count globale Administratoren — zu viele. Microsoft empfiehlt max. 2-4.', ['count' => $count])]);
        } catch (\Throwable) {
            return array_merge($base, ['status' => 'unknown', 'detail' => t('Berechtigung fehlt oder API-Fehler.')]);
        }
    }

    private function checkNamedLocations(): array
    {
        $base = [
            'id'          => 'named_locations',
            'category'    => 'Konfiguration & Apps',
            'label'       => t('Named Locations konfiguriert'),
            'description' => t('Mindestens ein vertrauenswürdiger Standort (IP oder Land) ist konfiguriert.'),
            'severity'    => 'low',
        ];
        try {
            $data  = $this->graph->get('/identity/conditionalAccess/namedLocations', ['$top' => '100'], 'named_locations', 1800);
            $count = count($data['value'] ?? []);
            if ($count > 0) {
                return array_merge($base, ['status' => 'pass', 'detail' => t(':count Named Location(s) konfiguriert.', ['count' => $count])]);
            }
            return array_merge($base, ['status' => 'fail', 'detail' => t('Keine Named Locations konfiguriert. Vertrauenswürdige IPs/Länder fehlen.')]);
        } catch (\Throwable) {
            return array_merge($base, ['status' => 'unknown', 'detail' => t('Berechtigung fehlt oder API-Fehler.')]);
        }
    }

    private function checkAppSecretsExpiry(): array
    {
        $base = [
            'id'          => 'app_secrets_expiry',
            'category'    => 'Konfiguration & Apps',
            'label'       => t('App-Secrets Ablaufdatum'),
            'description' => t('App-Registrierungen ohne abgelaufene oder bald ablaufende Secrets.'),
            'severity'    => 'medium',
        ];
        try {
            $data      = $this->graph->get('/applications', ['$select' => 'id,displayName,passwordCredentials', '$top' => '100'], 'applications_secrets', 900);
            if ($this->graph->getLastError() !== null) {
                return array_merge($base, ['status' => 'unknown', 'detail' => t('Berechtigung fehlt (Application.Read.All erforderlich).')]);
            }
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
                return array_merge($base, ['status' => 'fail', 'detail' => t(':expired abgelaufenes Secret(s)', ['expired' => $expired]) . ($soon > 0 ? t(', :soon läuft in <30 Tagen ab.', ['soon' => $soon]) : t('.'))]);
            }
            if ($soon > 0) {
                return array_merge($base, ['status' => 'warn', 'detail' => t(':soon Secret(s) läuft in <30 Tagen ab — Erneuerung erforderlich.', ['soon' => $soon])]);
            }
            return array_merge($base, ['status' => 'pass', 'detail' => t('Keine abgelaufenen oder bald ablaufenden App-Secrets.')]);
        } catch (\Throwable) {
            return array_merge($base, ['status' => 'unknown', 'detail' => t('Berechtigung fehlt oder API-Fehler.')]);
        }
    }

    private function checkNoStaleLicensed(): array
    {
        $base = [
            'id'          => 'no_stale_licensed',
            'category'    => 'Konfiguration & Apps',
            'label'       => t('Inaktive lizenzierte Konten'),
            'description' => t('Aktive, lizenzierte Benutzer ohne Anmeldung seit mehr als 90 Tagen.'),
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
                return array_merge($base, ['status' => 'pass', 'detail' => t('Keine inaktiven lizenzierten Konten gefunden.')]);
            }
            if ($stale <= 5) {
                return array_merge($base, ['status' => 'warn', 'detail' => t(':stale lizenzierte Benutzer seit >90 Tagen inaktiv.', ['stale' => $stale])]);
            }
            return array_merge($base, ['status' => 'fail', 'detail' => t(':stale lizenzierte Benutzer seit >90 Tagen ohne Anmeldung.', ['stale' => $stale])]);
        } catch (\Throwable) {
            return array_merge($base, ['status' => 'unknown', 'detail' => t('Berechtigung fehlt oder API-Fehler.')]);
        }
    }

    private function checkGuestUserCount(): array
    {
        $base = [
            'id'          => 'guest_user_count',
            'category'    => 'Konfiguration & Apps',
            'label'       => t('Gastbenutzer'),
            'description' => t('Anzahl aktiver Gastbenutzer — sollte regelmäßig überprüft werden.'),
            'severity'    => 'low',
        ];
        try {
            $data   = $this->graph->getEventual('/users', ['$count' => 'true', '$top' => '1', '$select' => 'id', '$filter' => "userType eq 'Guest'"], 'dash_guests_count', 1800);
            $guests = (int)($data['@odata.count'] ?? count($data['value'] ?? []));
            if ($guests <= 10) {
                return array_merge($base, ['status' => 'pass', 'detail' => t(':guests aktive Gastbenutzer — unkritisch.', ['guests' => $guests])]);
            }
            if ($guests <= 30) {
                return array_merge($base, ['status' => 'warn', 'detail' => t(':guests Gastbenutzer — regelmäßige Überprüfung empfohlen.', ['guests' => $guests])]);
            }
            return array_merge($base, ['status' => 'fail', 'detail' => t(':guests Gastbenutzer — Überprüfung und Bereinigung erforderlich.', ['guests' => $guests])]);
        } catch (\Throwable) {
            return array_merge($base, ['status' => 'unknown', 'detail' => t('Berechtigung fehlt oder API-Fehler.')]);
        }
    }

    private function checkSecurityDefaults(array $policies): array
    {
        $base = [
            'id'          => 'security_defaults',
            'category'    => 'Conditional Access',
            'label'       => t('Security Defaults vs. CA'),
            'description' => t('Security Defaults und Conditional Access sollten nicht gleichzeitig aktiv sein.'),
            'severity'    => 'high',
        ];
        try {
            $data       = $this->graph->get('/policies/identitySecurityDefaultsEnforcementPolicy', [], 'security_defaults', 3600);
            $sdEnabled  = (bool)($data['isEnabled'] ?? false);
            $hasActiveCa = !empty(array_filter($policies, fn($p) => strtolower($p['state'] ?? '') === 'enabled'));

            if ($sdEnabled && $hasActiveCa) {
                return array_merge($base, ['status' => 'warn', 'detail' => t('Security Defaults ist aktiv, aber eigene CA-Richtlinien sind ebenfalls aktiviert — kann zu Konflikten führen.')]);
            }
            if (!$sdEnabled && !$hasActiveCa) {
                return array_merge($base, ['status' => 'fail', 'detail' => t('Weder Security Defaults noch aktive CA-Richtlinien vorhanden — kein Basisschutz.')]);
            }
            if ($sdEnabled && !$hasActiveCa) {
                return array_merge($base, ['status' => 'warn', 'detail' => t('Security Defaults aktiv — bietet Basisschutz, aber keine granulare Steuerung. CA-Migration empfohlen.')]);
            }
            return array_merge($base, ['status' => 'pass', 'detail' => t('Security Defaults deaktiviert, eigene CA-Richtlinien aktiv — optimal.')]);
        } catch (\Throwable) {
            return array_merge($base, ['status' => 'unknown', 'detail' => t('Berechtigung fehlt (Policy.Read.All erforderlich).')]);
        }
    }

    private function checkAdminsMfaRegistered(): array
    {
        $base = [
            'id'          => 'admins_mfa_registered',
            'category'    => 'Identität & MFA',
            'label'       => t('Alle Admins haben MFA'),
            'description' => t('Alle globalen Administratoren haben eine MFA-Methode registriert.'),
            'severity'    => 'high',
        ];
        try {
            // Get global admin role
            $roles = $this->graph->get('/directoryRoles', ['$filter' => "roleTemplateId eq '" . self::ROLE_GLOBAL_ADMIN . "'", '$select' => 'id'], 'dir_role_global_admin', 3600);
            $roleList = $roles['value'] ?? [];
            if (empty($roleList)) {
                return array_merge($base, ['status' => 'unknown', 'detail' => t('Globale Administratorrolle nicht gefunden.')]);
            }
            $roleId  = $roleList[0]['id'];
            $members = $this->graph->get("/directoryRoles/{$roleId}/members", ['$select' => 'id,userPrincipalName'], "dir_role_members_{$roleId}", 1800);
            $admins  = $members['value'] ?? [];
            if (empty($admins)) {
                return array_merge($base, ['status' => 'unknown', 'detail' => t('Keine Mitglieder der Administratorrolle gefunden.')]);
            }

            // Get MFA data — use existing cache if available
            $mfaData = $this->graph->paginate(
                '/reports/authenticationMethods/userRegistrationDetails',
                ['$select' => 'id,userPrincipalName,isMfaRegistered', '$top' => '999'],
                50, 'dash_mfa_by_upn', 1800
            );
            $mfaByUpn = [];
            foreach ($mfaData as $row) {
                $mfaByUpn[strtolower($row['userPrincipalName'] ?? '')] = $row['isMfaRegistered'] ?? false;
            }

            $noMfa = [];
            foreach ($admins as $admin) {
                $upn = strtolower($admin['userPrincipalName'] ?? '');
                // A real admin account (has a UPN) that is either flagged
                // not-registered OR missing from the MFA report counts as "no MFA".
                // Service principals/groups (no UPN) are skipped on purpose.
                if ($upn && empty($mfaByUpn[$upn])) {
                    $noMfa[] = $admin['userPrincipalName'];
                }
            }

            if (empty($noMfa)) {
                return array_merge($base, ['status' => 'pass', 'detail' => t('Alle :count globale(n) Administrator(en) haben MFA registriert.', ['count' => count($admins)])]);
            }
            return array_merge($base, ['status' => 'fail', 'detail' => t(':count Admin(s) ohne MFA: :list', ['count' => count($noMfa), 'list' => implode(', ', array_slice($noMfa, 0, 3))]) . (count($noMfa) > 3 ? t(' …') : '')]);
        } catch (\Throwable) {
            return array_merge($base, ['status' => 'unknown', 'detail' => t('Berechtigung fehlt oder API-Fehler.')]);
        }
    }

    private function checkSsprAdoption(): array
    {
        $base = [
            'id'          => 'sspr_adoption',
            'category'    => 'Identität & MFA',
            'label'       => t('Self-Service Password Reset (SSPR)'),
            'description' => t('Anteil der Benutzer mit registrierter SSPR-Methode.'),
            'severity'    => 'medium',
        ];
        try {
            $data  = $this->graph->get('/reports/authenticationMethods/usersRegisteredByFeature', [], 'sspr_feature_summary', 3600);
            $total = (int)($data['totalUserCount'] ?? 0);
            if ($total === 0) {
                return array_merge($base, ['status' => 'unknown', 'detail' => t('Keine Benutzerdaten verfügbar.')]);
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
                return array_merge($base, ['status' => 'pass', 'detail' => t(':ssprCount/:total Benutzer haben SSPR registriert (:rate%).', ['ssprCount' => $ssprCount, 'total' => $total, 'rate' => $rate])]);
            }
            if ($rate >= 1) {
                return array_merge($base, ['status' => 'warn', 'detail' => t('Nur :rate% (:ssprCount/:total) haben SSPR registriert — Ziel: >70%.', ['rate' => $rate, 'ssprCount' => $ssprCount, 'total' => $total])]);
            }
            return array_merge($base, ['status' => 'fail', 'detail' => t('Kein Benutzer hat SSPR registriert. SSPR-Einführung empfohlen.')]);
        } catch (\Throwable) {
            return array_merge($base, ['status' => 'unknown', 'detail' => t('Berechtigung fehlt (Reports.Read.All) oder SSPR nicht lizenziert.')]);
        }
    }

    private function checkCaSessionControls(array $policies): array
    {
        $base = [
            'id'          => 'ca_session_controls',
            'category'    => 'Conditional Access',
            'label'       => t('CA-Sitzungssteuerung'),
            'description' => t('CA-Richtlinie begrenzt Sitzungsdauer oder verhindert persistente Browser-Sitzungen.'),
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
                return array_merge($base, ['status' => 'pass', 'detail' => t('Aktive CA-Richtlinie steuert Sitzungslebensdauer oder persistente Sitzungen.')]);
            }
        }
        return array_merge($base, ['status' => 'fail', 'detail' => t('Keine CA-Richtlinie kontrolliert Sitzungsdauer oder Browser-Persistenz.')]);
    }

    private function checkPimAdoption(): array
    {
        $base = [
            'id'          => 'pim_adoption',
            'category'    => 'Konfiguration & Apps',
            'label'       => t('Privileged Identity Management (PIM)'),
            'description' => t('Just-in-Time Admin-Zugriff durch PIM-berechtigte Rollenzuweisungen.'),
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
                return array_merge($base, ['status' => 'pass', 'detail' => t(':count PIM-berechtigte Rollenzuweisung(en) aktiv — Just-in-Time-Zugriff wird genutzt.', ['count' => $count])]);
            }
            return array_merge($base, ['status' => 'fail', 'detail' => t('Keine PIM-berechtigten Rollenzuweisungen. Alle Admins haben dauerhaften Zugriff.')]);
        } catch (\Throwable) {
            return array_merge($base, ['status' => 'unknown', 'detail' => t('Berechtigung fehlt (RoleManagement.Read.Directory) oder Entra ID P2 nicht lizenziert.')]);
        }
    }

    private function checkAppConsentPolicy(?array $authPolicy): array
    {
        $base = [
            'id'          => 'app_consent_policy',
            'category'    => 'Konfiguration & Apps',
            'label'       => t('App-Zustimmungsrichtlinie'),
            'description' => t('Benutzer dürfen nicht ohne Admin-Genehmigung OAuth-Berechtigungen vergeben.'),
            'severity'    => 'high',
        ];
        if ($authPolicy === null) {
            return array_merge($base, ['status' => 'unknown', 'detail' => t('Berechtigung fehlt (Policy.Read.All erforderlich).')]);
        }
        try {
            $policies  = (array)($authPolicy['permissionGrantPolicyIdsAssignedToDefaultUserRole'] ?? []);

            if (empty($policies)) {
                return array_merge($base, ['status' => 'pass', 'detail' => t('Benutzer können keinen Apps ohne Admin-Genehmigung zustimmen — optimal.')]);
            }
            // Check for legacy broad consent
            $hasLegacy = !empty(array_filter($policies, fn($p) => str_contains(strtolower($p), 'legacy') && !str_contains(strtolower($p), 'admin')));
            $hasLowRisk = !empty(array_filter($policies, fn($p) => str_contains(strtolower($p), 'low-risk') || str_contains(strtolower($p), 'lowrisk')));

            if ($hasLegacy) {
                return array_merge($base, ['status' => 'fail', 'detail' => t('Benutzer dürfen beliebigen Apps zustimmen (legacy consent policy). Consent-Phishing-Risiko.')]);
            }
            if ($hasLowRisk) {
                return array_merge($base, ['status' => 'warn', 'detail' => t('Benutzer dürfen risikoarmen Apps zustimmen. Admin-Consent-Workflow für alle empfohlen.')]);
            }
            return array_merge($base, ['status' => 'warn', 'detail' => t('Consent-Richtlinie vorhanden — manuelle Überprüfung empfohlen: :policies', ['policies' => implode(', ', $policies)])]);
        } catch (\Throwable) {
            return array_merge($base, ['status' => 'unknown', 'detail' => t('Berechtigung fehlt oder API-Fehler.')]);
        }
    }

    private function checkExternalCollabPolicy(?array $authPolicy): array
    {
        $base = [
            'id'          => 'external_collab_policy',
            'category'    => 'Konfiguration & Apps',
            'label'       => t('Gasteinladungsrichtlinie'),
            'description' => t('Wer darf externe Gastbenutzer in den Tenant einladen.'),
            'severity'    => 'medium',
        ];
        if ($authPolicy === null) {
            return array_merge($base, ['status' => 'unknown', 'detail' => t('Berechtigung fehlt (Policy.Read.All erforderlich).')]);
        }
        try {
            $setting = strtolower($authPolicy['allowInvitesFrom'] ?? '');
            $labels  = [
                'none'                                 => t('Niemand darf einladen — sehr restriktiv.'),
                'adminsandguestinviters'               => t('Nur Admins und Gast-Einlader dürfen einladen — empfohlen.'),
                'adminsguestinvitersandallmembers'     => t('Alle Mitglieder dürfen einladen — moderat.'),
                'everyone'                             => t('Jeder (inkl. Gäste) darf einladen — unsicher.'),
            ];
            $detail = $labels[$setting] ?? t('Einstellung: :setting', ['setting' => $setting]);

            if (in_array($setting, ['none', 'adminsandguestinviters'], true)) {
                return array_merge($base, ['status' => 'pass', 'detail' => $detail]);
            }
            if ($setting === 'adminsguestinvitersandallmembers') {
                return array_merge($base, ['status' => 'warn', 'detail' => $detail . t(' Empfehlung: auf Admins und Gast-Einlader einschränken.')]);
            }
            return array_merge($base, ['status' => 'fail', 'detail' => $detail . t(' Einschränkung auf Admins dringend empfohlen.')]);
        } catch (\Throwable) {
            return array_merge($base, ['status' => 'unknown', 'detail' => t('Berechtigung fehlt oder API-Fehler.')]);
        }
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function fetchCaPolicies(): array
    {
        try {
            return \App\Modules\ConditionalAccess\ConditionalAccessService::fetchAllPolicies($this->graph);
        } catch (\Throwable) {
            return [];
        }
    }

    private function checkBreakGlass(): array
    {
        $base = [
            'id'          => 'break_glass',
            'category'    => 'E-Mail & Endpoint-Schutz',
            'label'       => t('Notfallzugangskonto konfiguriert'),
            'description' => t('Mindestens 2 globale Admin-Konten ohne Lizenz und ohne On-Premises-Sync vorhanden (Break-Glass-Muster).'),
            'severity'    => 'high',
        ];
        try {
            $roles = $this->graph->get(
                '/directoryRoles',
                ['$filter' => "roleTemplateId eq '" . self::ROLE_GLOBAL_ADMIN . "'", '$select' => 'id'],
                'dir_role_global_admin_bg',
                3600
            );
            $roleList = $roles['value'] ?? [];
            if (empty($roleList)) {
                return array_merge($base, ['status' => 'unknown', 'detail' => t('Globale Admin-Rolle nicht gefunden.')]);
            }
            $roleId  = $roleList[0]['id'];
            $members = $this->graph->get(
                "/directoryRoles/{$roleId}/members",
                ['$select' => 'id,displayName,userPrincipalName,assignedLicenses,onPremisesSyncEnabled'],
                "dir_role_members_bg_{$roleId}",
                1800
            );
            $candidates = 0;
            foreach ($members['value'] ?? [] as $admin) {
                $noLicense    = empty($admin['assignedLicenses'] ?? []);
                $cloudOnly    = !($admin['onPremisesSyncEnabled'] ?? false);
                if ($noLicense && $cloudOnly) {
                    $candidates++;
                }
            }
            if ($candidates >= 2) {
                return array_merge($base, ['status' => 'pass', 'detail' => t(':candidates potenzielle Notfallkonten gefunden (ohne Lizenz, Cloud-only).', ['candidates' => $candidates])]);
            }
            if ($candidates === 1) {
                return array_merge($base, ['status' => 'warn', 'detail' => t('Nur 1 Notfallkonto gefunden. Microsoft empfiehlt mindestens 2.')]);
            }
            return array_merge($base, ['status' => 'fail', 'detail' => t('Kein globales Admin-Konto ohne Lizenz gefunden. Notfallkonten sollten keine Lizenzen haben.')]);
        } catch (\Throwable $e) {
            return array_merge($base, ['status' => 'unknown', 'detail' => $e->getMessage()]);
        }
    }

    private function checkDefenderForOffice(): array
    {
        $base = [
            'id'          => 'defender_for_office',
            'category'    => 'E-Mail & Endpoint-Schutz',
            'label'       => t('Defender for Office 365 lizenziert'),
            'description' => t('Microsoft Defender for Office 365 (Safe Links, Safe Attachments, Anti-Phishing) ist aktiv.'),
            'severity'    => 'high',
        ];
        try {
            $skus = $this->graph->paginate(
                '/subscribedSkus',
                ['$select' => 'skuPartNumber,capabilityStatus'],
                5,
                'subscribed_skus_mdo',
                3600
            );
            $mdoSkus = ['ATP_ENTERPRISE', 'MDATP_Server', 'WIN_DEF_ATP', 'DEFENDER_ENDPOINT_P1'];
            $bundleSkus = ['SPE_E5', 'ENTERPRISEPREMIUM', 'M365_F5_SECURITY', 'SPE_E5_CALLINGMINUTES'];
            foreach ($skus as $sku) {
                if ($sku['capabilityStatus'] !== 'Enabled') continue;
                if (in_array($sku['skuPartNumber'], $mdoSkus, true)) {
                    return array_merge($base, ['status' => 'pass', 'detail' => t('Defender for Office 365 Lizenz aktiv: :sku', ['sku' => $sku['skuPartNumber']])]);
                }
                if (in_array($sku['skuPartNumber'], $bundleSkus, true)) {
                    return array_merge($base, ['status' => 'pass', 'detail' => t('Defender for Office 365 im Bundle enthalten: :sku', ['sku' => $sku['skuPartNumber']])]);
                }
            }
            return array_merge($base, ['status' => 'fail', 'detail' => t('Kein Defender for Office 365 Abonnement aktiv.')]);
        } catch (\Throwable $e) {
            return array_merge($base, ['status' => 'unknown', 'detail' => $e->getMessage()]);
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

    // ─────────────────────────────────────────────────────────────────────
    // DSGVO / GDPR Checks
    //
    // Alle prüfen Tenant-Einstellungen, nicht personen­bezogene Daten.
    // ─────────────────────────────────────────────────────────────────────

    private function checkGdprTenantRegion(): array
    {
        $base = [
            'id'          => 'gdpr_tenant_region',
            'category'    => 'DSGVO & Datenschutz',
            'label'       => t('Tenant-Region in EU/EWR'),
            'description' => t('Der Tenant-Standort bestimmt, in welcher Datacenter-Region M365-Daten primär gespeichert werden. EU-Standort ist für DSGVO-konforme Verarbeitung relevant.'),
            'severity'    => 'high',
        ];
        try {
            $org = $this->graph->get('/organization', ['$select' => 'countryLetterCode,country,preferredDataLocation'], 'org_region', 3600);
            $row = $org['value'][0] ?? null;
            if (!$row) return array_merge($base, ['status' => 'unknown', 'detail' => t('Organisation nicht lesbar.')]);
            $code = strtoupper($row['countryLetterCode'] ?? '');
            $pdl  = strtoupper($row['preferredDataLocation'] ?? '');
            // EU/EWR-Staaten (Stand 2026)
            $euCodes = ['AT','BE','BG','HR','CY','CZ','DK','EE','FI','FR','DE','GR','HU','IE','IT','LV','LT','LU','MT','NL','PL','PT','RO','SK','SI','ES','SE','IS','LI','NO'];
            $euPdl   = ['EUR','EU','DEU','FRA','NOR','SWE','GBR']; // GBR Übergangs-Adäquanzbeschluss
            $inEu = in_array($code, $euCodes, true) || in_array($pdl, $euPdl, true);
            if ($inEu) {
                return array_merge($base, ['status' => 'pass', 'detail' => t('Tenant-Region: :code', ['code' => $code]) . ($pdl ? t(' (preferredDataLocation=:pdl)', ['pdl' => $pdl]) : '')]);
            }
            return array_merge($base, ['status' => 'fail', 'detail' => t('Tenant-Region außerhalb EU/EWR: :code. DSGVO-Übermittlung in Drittländer prüfen (Art. 44–49).', ['code' => $code])]);
        } catch (\Throwable $e) {
            return array_merge($base, ['status' => 'unknown', 'detail' => $e->getMessage()]);
        }
    }

    private function checkGdprSharePointSharing(): array
    {
        $base = [
            'id'          => 'gdpr_sharepoint_sharing',
            'category'    => 'DSGVO & Datenschutz',
            'label'       => t('SharePoint External Sharing restriktiv'),
            'description' => t('Die Tenant-weite Freigabe-Einstellung sollte externe Freigabe einschränken — Anyone-Links sind DSGVO-kritisch (Art. 25 Privacy by Default).'),
            'severity'    => 'high',
        ];
        $s = $this->loadSpSettings();
        if (isset($s['__skip'])) return array_merge($base, ['status' => 'pass', 'detail' => $s['__skip']]);
        if (empty($s))           return array_merge($base, ['status' => 'unknown', 'detail' => t('SharePoint-Tenant-Settings nicht lesbar (Permission SharePointTenantSettings.Read.All?).')]);
        $cap = $s['sharingCapability'] ?? '';
        return match ($cap) {
            'disabled'                          => array_merge($base, ['status' => 'pass', 'detail' => t('Externe Freigabe komplett deaktiviert.')]),
            'existingExternalUserSharingOnly'   => array_merge($base, ['status' => 'pass', 'detail' => t('Nur an bekannte externe Benutzer — restriktiv.')]),
            'externalUserSharingOnly'           => array_merge($base, ['status' => 'warn', 'detail' => t('Nur an authentifizierte Externe — akzeptabel, aber prüfen.')]),
            'externalUserAndGuestSharing'       => array_merge($base, ['status' => 'fail', 'detail' => t('Anyone-Links sind aktiv — DSGVO-Risiko: unbekannte Dritte können auf Daten zugreifen.')]),
            default                             => array_merge($base, ['status' => 'unknown', 'detail' => t('Unbekannter sharingCapability-Wert: :cap', ['cap' => $cap])]),
        };
    }

    private function checkGdprAnonymousLinkExpiry(): array
    {
        $base = [
            'id'          => 'gdpr_anonymous_link_expiry',
            'category'    => 'DSGVO & Datenschutz',
            'label'       => t('Anonyme Freigabe-Links laufen ab'),
            'description' => t('Anyone-Links ohne Ablaufdatum verletzen Speicherbegrenzung (Art. 5 Abs. 1e DSGVO). Empfehlung: ≤ 90 Tage.'),
            'severity'    => 'medium',
        ];
        $s = $this->loadSpSettings();
        if (isset($s['__skip'])) return array_merge($base, ['status' => 'pass', 'detail' => $s['__skip']]);
        if (empty($s))           return array_merge($base, ['status' => 'unknown', 'detail' => t('SharePoint-Tenant-Settings nicht lesbar.')]);
        $days = (int)($s['requireAnonymousLinksExpireInDays'] ?? 0);
        if (($s['sharingCapability'] ?? '') === 'disabled') {
            return array_merge($base, ['status' => 'pass', 'detail' => t('Externe Freigabe deaktiviert — Ablauf irrelevant.')]);
        }
        if ($days <= 0) {
            return array_merge($base, ['status' => 'fail', 'detail' => t('Anyone-Links haben keinen Ablauf — DSGVO-Risiko.')]);
        }
        if ($days > 90) {
            return array_merge($base, ['status' => 'warn', 'detail' => t('Anyone-Links laufen nach :days Tagen ab — empfohlen ≤ 90.', ['days' => $days])]);
        }
        return array_merge($base, ['status' => 'pass', 'detail' => t('Anyone-Links laufen nach :days Tagen ab.', ['days' => $days])]);
    }

    private function checkGdprDefaultSharingLink(): array
    {
        $base = [
            'id'          => 'gdpr_default_sharing_link',
            'category'    => 'DSGVO & Datenschutz',
            'label'       => t('Standard-Freigabetyp ist intern'),
            'description' => t('Der Default-Linktyp sollte „internal" oder „direct" (named) sein — Anyone als Standard begünstigt versehentliche Datenweitergabe.'),
            'severity'    => 'medium',
        ];
        $s = $this->loadSpSettings();
        if (isset($s['__skip'])) return array_merge($base, ['status' => 'pass', 'detail' => $s['__skip']]);
        if (empty($s))           return array_merge($base, ['status' => 'unknown', 'detail' => t('SharePoint-Tenant-Settings nicht lesbar.')]);
        $type = $s['defaultSharingLinkType'] ?? '';
        return match ($type) {
            'direct', 'internal' => array_merge($base, ['status' => 'pass', 'detail' => t('Standard-Link: :type', ['type' => $type])]),
            'anonymousAccess'    => array_merge($base, ['status' => 'fail', 'detail' => t('Standard-Link ist Anyone — DSGVO-kritisch.')]),
            default              => array_merge($base, ['status' => 'warn', 'detail' => t('Standard-Link: :type', ['type' => $type])]),
        };
    }

    /**
     * Loads /admin/sharepoint/settings once per request and translates the
     * common "tenant has no SPO license" error into a skip marker so the
     * three SP checks above can report "nicht zutreffend" instead of an
     * alarming "Unbekannt".
     *
     * @return array{__skip?:string} the raw settings, or a one-element
     *                                array carrying the skip reason.
     */
    private function loadSpSettings(): array
    {
        static $cache = null;
        if ($cache !== null) return $cache;
        try {
            $s = $this->graph->get('/admin/sharepoint/settings', [], 'sp_tenant_settings', 1800);
            return $cache = (is_array($s) ? $s : []);
        } catch (\Throwable $e) {
            $msg = $e->getMessage();
            if (stripos($msg, 'SPO license') !== false || stripos($msg, 'SharePoint') !== false && stripos($msg, 'license') !== false) {
                return $cache = ['__skip' => t('SharePoint Online ist im Tenant nicht lizenziert — Prüfung nicht zutreffend.')];
            }
            return $cache = [];
        }
    }

    private function checkGdprSensitivityLabels(): array
    {
        $base = [
            'id'          => 'gdpr_sensitivity_labels',
            'category'    => 'DSGVO & Datenschutz',
            'label'       => t('Sensitivity Labels veröffentlicht'),
            'description' => t('Vertraulichkeitsbezeichnungen sind Voraussetzung für Information-Protection (Art. 32 DSGVO Maßnahmen zur Datenintegrität).'),
            'severity'    => 'medium',
        ];
        $labels = $this->loadSensitivityLabels();
        if (isset($labels['__skip'])) return array_merge($base, ['status' => 'pass', 'detail' => $labels['__skip']]);
        if ($labels === null)          return array_merge($base, ['status' => 'unknown', 'detail' => t('Sensitivity-Labels-Endpunkt nicht erreichbar — Berechtigung InformationProtectionPolicy.Read.All prüfen.')]);
        $active = count(array_filter($labels, fn($l) => $l['isActive'] ?? true));
        if (empty($labels))            return array_merge($base, ['status' => 'fail', 'detail' => t('Keine Sensitivity Labels gefunden.')]);
        if ($active === 0)             return array_merge($base, ['status' => 'warn', 'detail' => t(':count Labels existieren, aber keines ist aktiv.', ['count' => count($labels)])]);
        return array_merge($base, ['status' => 'pass', 'detail' => t(':active aktive Sensitivity Labels (von :total)', ['active' => $active, 'total' => count($labels)])]);
    }

    private function checkGdprDlpOrLabelsActive(): array
    {
        $base = [
            'id'          => 'gdpr_dlp_or_labels',
            'category'    => 'DSGVO & Datenschutz',
            'label'       => t('DLP-/Label-Schutz für personenbezogene Daten'),
            'description' => t('Mindestens eine Information-Protection-Schutzmaßnahme (Sensitivity Label aktiv) ist erforderlich (Art. 25 + Art. 32 DSGVO).'),
            'severity'    => 'high',
        ];
        $labels = $this->loadSensitivityLabels();
        if (isset($labels['__skip'])) return array_merge($base, ['status' => 'pass', 'detail' => $labels['__skip']]);
        if ($labels === null)          return array_merge($base, ['status' => 'unknown', 'detail' => t('Sensitivity-Labels-Endpunkt nicht erreichbar — Berechtigung InformationProtectionPolicy.Read.All prüfen.')]);
        $active = count(array_filter($labels, fn($l) => $l['isActive'] ?? true));
        if ($active > 0) {
            return array_merge($base, ['status' => 'pass', 'detail' => t(':active Sensitivity Labels aktiv.', ['active' => $active])]);
        }
        return array_merge($base, ['status' => 'fail', 'detail' => t('Keine aktive Schutzmaßnahme (DLP/Label) gefunden.')]);
    }

    /**
     * Tries the beta endpoint first (app-permissions), falls back to the
     * v1.0 delegated endpoint. Returns the labels array (possibly empty
     * = no labels published), null if every endpoint produced an error
     * (so the caller can say "Unbekannt" instead of falsely "keine").
     *
     * @return array<int,array<string,mixed>>|null
     */
    private function loadSensitivityLabels(): array|null
    {
        static $cache = false;
        if ($cache !== false) return $cache;

        $endpoints = [
            'https://graph.microsoft.com/beta/security/informationProtection/sensitivityLabels',
            '/informationProtection/policy/labels',
        ];
        $errors = 0;
        foreach ($endpoints as $i => $ep) {
            try {
                $data = $this->graph->get($ep, ['$top' => '50'], 'gdpr_sens_' . $i, 1800);
                if ($this->graph->getLastError() !== null) { $errors++; continue; }
                return $cache = ($data['value'] ?? []);
            } catch (\Throwable) {
                $errors++;
            }
        }
        return $cache = ($errors === count($endpoints) ? null : []);
    }

    private function checkGdprRetentionPolicies(): array
    {
        $base = [
            'id'          => 'gdpr_retention_policies',
            'category'    => 'DSGVO & Datenschutz',
            'label'       => t('Aufbewahrungs-/eDiscovery-Fälle aktiv'),
            'description' => t('Aufbewahrungsrichtlinien sind nötig für Speicherbegrenzung & Auskunfts-/Löschpflichten (Art. 5 + Art. 17 DSGVO).'),
            'severity'    => 'medium',
        ];
        try {
            $cases = $this->graph->paginate(
                '/security/cases/ediscoveryCases',
                ['$select' => 'id,status'],
                3,
                'gdpr_ediscovery',
                3600
            );
            $active = count(array_filter($cases, fn($c) => ($c['status'] ?? '') === 'active'));
            if (empty($cases)) {
                return array_merge($base, ['status' => 'warn', 'detail' => t('Keine eDiscovery-/Aufbewahrungsfälle konfiguriert.')]);
            }
            return array_merge($base, ['status' => 'pass', 'detail' => t(':active aktive Fälle, :total insgesamt.', ['active' => $active, 'total' => count($cases)])]);
        } catch (\Throwable $e) {
            return array_merge($base, ['status' => 'unknown', 'detail' => $e->getMessage()]);
        }
    }

    private function checkGdprAuditLogReachable(): array
    {
        $base = [
            'id'          => 'gdpr_audit_log',
            'category'    => 'DSGVO & Datenschutz',
            'label'       => t('Audit-Log aktiv & abrufbar'),
            'description' => t('Ohne Audit-Log keine Nachvollziehbarkeit von Datenzugriffen (Art. 32 DSGVO, Rechenschaftspflicht).'),
            'severity'    => 'high',
        ];
        try {
            $data = $this->graph->get(
                '/auditLogs/directoryAudits',
                ['$top' => '1', '$select' => 'id'],
                'gdpr_audit_probe',
                3600
            );
            // A swallowed 403 also returns ['value'=>[]] — don't report that as
            // "pass". Check the last error first so a missing permission is
            // surfaced instead of a false green.
            if ($this->graph->getLastError() !== null) {
                return array_merge($base, ['status' => 'warn', 'detail' => t('Audit-Log nicht abrufbar — Berechtigung AuditLog.Read.All prüfen.')]);
            }
            if (!empty($data['value'])) {
                return array_merge($base, ['status' => 'pass', 'detail' => t('Audit-Log liefert Daten.')]);
            }
            return array_merge($base, ['status' => 'warn', 'detail' => t('Audit-Log antwortet, aber leer — Permission/Ausstellungsdatum prüfen.')]);
        } catch (\Throwable $e) {
            return array_merge($base, ['status' => 'fail', 'detail' => t('Audit-Log nicht abrufbar: :error', ['error' => $e->getMessage()])]);
        }
    }

}
