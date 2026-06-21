<?php
namespace App\Modules\AiAdvisor;

/**
 * Hardcoded best-practice recommendation library.
 *
 * Returns concrete, actionable recommendations keyed by SecurityPosture check
 * IDs and metric conditions. Each entry contains a severity, a title, a risk
 * explanation, numbered step-by-step actions, a direct Microsoft admin-center
 * deep-link, an MS Learn doc URL and an optional internal module path.
 *
 * AI is NOT involved here — every recommendation is written by a human and
 * sourced from Microsoft Security Baseline / CIS Benchmark guidance.
 */
class RecommendationLibrary
{
    /** Severity sort order (lower = more important). */
    private const SEVERITY_ORDER = [
        'critical' => 0,
        'high'     => 1,
        'medium'   => 2,
        'low'      => 3,
    ];

    /**
     * Return sorted recommendations for the given failed/warning checks and
     * anonymized metric snapshot.
     *
     * @param string[] $failedCheckIds
     * @param string[] $warningCheckIds
     * @param array    $metrics       Full context array from AiAdvisorService::buildContext()
     * @param string[] $unknownIds    Check-IDs mit Status "unknown" — typischerweise
     *                                weil die Daten nicht abrufbar waren (Permission,
     *                                Lizenz, Endpunkt). Für DSGVO-Checks zeigen wir
     *                                trotzdem die Empfehlung an, gekennzeichnet als
     *                                "Status manuell verifizieren".
     * @return array[]
     */
    public static function get(array $failedCheckIds, array $warningCheckIds, array $metrics, array $unknownIds = []): array
    {
        $recs = [];

        // ── Check-based recommendations ──────────────────────────────────────

        // mfa_registration
        if (in_array('mfa_registration', $failedCheckIds, true) || in_array('mfa_registration', $warningCheckIds, true)) {
            $recs[] = [
                'id'            => 'mfa_registration',
                'severity'      => 'critical',
                'title'         => t('MFA-Registrierung für alle Benutzer erzwingen'),
                'risk'          => t('Konten ohne MFA sind das häufigste Einfallstor bei Accountübernahmen. Microsoft-Daten zeigen: MFA blockiert 99,9 % aller automatisierten Angriffe.'),
                'steps'         => [
                    t('Entra ID öffnen → Security → Conditional Access'),
                    t('Neue Richtlinie erstellen: „MFA für alle Benutzer"'),
                    t('Zuweisung: Alle Benutzer (Ausnahme: Break-Glass-Konten)'),
                    t('Cloud-Apps: Alle Cloud-Apps'),
                    t('Zugriffssteuerung → Gewähren → MFA erforderlich'),
                    t('Richtlinie erst im Report-only-Modus testen, dann aktivieren'),
                ],
                'internal_path' => '/mfamethods',
                'ms_admin_url'  => 'https://entra.microsoft.com/#view/Microsoft_AAD_ConditionalAccess/ConditionalAccessBlade/~/Policies',
                'ms_doc_url'    => 'https://learn.microsoft.com/de-de/entra/identity/conditional-access/howto-conditional-access-policy-all-users-mfa',
                'bsi_controls'  => ['ORP.4.A9', 'ORP.4.A21'],
                'nis2_articles' => ['Art. 21 Abs. 2(i)', 'Art. 21 Abs. 2(j)'],
            ];
        }

        // legacy_auth
        if (in_array('legacy_auth', $failedCheckIds, true) || in_array('legacy_auth', $warningCheckIds, true)) {
            $recs[] = [
                'id'            => 'legacy_auth',
                'severity'      => 'critical',
                'title'         => t('Legacy-Authentifizierung blockieren'),
                'risk'          => t('Protokolle wie Basic Auth, IMAP und POP3 unterstützen keine MFA. Angreifer nutzen gezielt Legacy-Auth-Endpunkte, um MFA zu umgehen.'),
                'steps'         => [
                    t('Entra ID → Security → Conditional Access → Neue Richtlinie'),
                    t('Name: „Blockiere Legacy-Authentifizierung"'),
                    t('Zuweisung: Alle Benutzer'),
                    t('Cloud-Apps: Alle Cloud-Apps'),
                    t('Bedingungen → Client-Apps → Legacy-Authentifizierungsclients (alle auswählen)'),
                    t('Zugriffssteuerung → Blockieren'),
                    t('Zuerst im Report-only-Modus testen; Dienste wie Exchange ActiveSync vorab prüfen'),
                ],
                'internal_path' => '/conditionalaccess',
                'ms_admin_url'  => 'https://entra.microsoft.com/#view/Microsoft_AAD_ConditionalAccess/ConditionalAccessBlade/~/Policies',
                'ms_doc_url'    => 'https://learn.microsoft.com/de-de/entra/identity/conditional-access/block-legacy-authentication',
                'bsi_controls'  => ['APP.5.2.A4', 'NET.1.1.A12'],
                'nis2_articles' => ['Art. 21 Abs. 2(e)', 'Art. 21 Abs. 2(i)'],
            ];
        }

        // admin_mfa
        if (in_array('admin_mfa', $failedCheckIds, true) || in_array('admin_mfa', $warningCheckIds, true)) {
            $recs[] = [
                'id'            => 'admin_mfa',
                'severity'      => 'critical',
                'title'         => t('MFA für alle Administratoren erzwingen'),
                'risk'          => t('Kompromittierte Admin-Konten ermöglichen vollständige Tenant-Übernahme. Admins sind bevorzugtes Ziel von Phishing-Kampagnen.'),
                'steps'         => [
                    t('Entra ID → Security → Conditional Access → Neue Richtlinie'),
                    t('Name: „MFA für alle Admins"'),
                    t('Zuweisung: Verzeichnisrollen → alle Admin-Rollen auswählen'),
                    t('Cloud-Apps: Alle Cloud-Apps'),
                    t('Zugriffssteuerung → Gewähren → MFA erforderlich'),
                    t('Privileged Identity Management (PIM) aktivieren für Just-in-Time-Zugriff'),
                    t('Richtlinie sofort aktivieren (kein Report-only für Admin-MFA)'),
                ],
                'internal_path' => '/adminroles',
                'ms_admin_url'  => 'https://entra.microsoft.com/#view/Microsoft_Azure_PIMCommon/CommonMenuBlade/~/quickStart',
                'ms_doc_url'    => 'https://learn.microsoft.com/de-de/entra/identity/privileged-identity-management/pim-getting-started',
                'bsi_controls'  => ['ORP.4.A21', 'ORP.4.A23'],
                'nis2_articles' => ['Art. 21 Abs. 2(i)', 'Art. 21 Abs. 2(j)'],
            ];
        }

        // ca_enabled
        if (in_array('ca_enabled', $failedCheckIds, true) || in_array('ca_enabled', $warningCheckIds, true)) {
            $recs[] = [
                'id'            => 'ca_enabled',
                'severity'      => 'high',
                'title'         => t('Conditional Access oder Security Defaults aktivieren'),
                'risk'          => t('Ohne Conditional Access oder Security Defaults fehlt der grundlegende Schutz vor unbefugtem Zugriff auf alle Cloud-Apps des Tenants.'),
                'steps'         => [
                    t('Option A – Security Defaults (einfachste Lösung für kleine Tenants):'),
                    t('  Entra ID → Properties → Manage Security Defaults → Enable → Speichern'),
                    t('Option B – Eigene CA-Richtlinien (empfohlen):'),
                    t('  Richtlinie 1: MFA für alle Benutzer (alle Cloud-Apps)'),
                    t('  Richtlinie 2: Legacy-Auth blockieren'),
                    t('  Richtlinie 3: Konformes Gerät für Admin-Rollen erforderlich'),
                    t('Report-only-Modus nutzen, um Auswirkungen zu bewerten, bevor aktiviert wird'),
                ],
                'internal_path' => '/conditionalaccess',
                'ms_admin_url'  => 'https://entra.microsoft.com/#view/Microsoft_AAD_ConditionalAccess/ConditionalAccessBlade/~/Policies',
                'ms_doc_url'    => 'https://learn.microsoft.com/de-de/entra/fundamentals/security-defaults',
                'bsi_controls'  => ['ORP.4.A9', 'ORP.4.A21', 'ORP.4.A23'],
                'nis2_articles' => ['Art. 21 Abs. 2(i)', 'Art. 21 Abs. 2(j)'],
            ];
        }

        // sspr
        if (in_array('sspr', $failedCheckIds, true) || in_array('sspr', $warningCheckIds, true)) {
            $recs[] = [
                'id'            => 'sspr',
                'severity'      => 'medium',
                'title'         => t('Self-Service Password Reset (SSPR) aktivieren'),
                'risk'          => t('Ohne SSPR müssen Benutzer den Helpdesk für Passwortzurücksetzungen kontaktieren. Dies erhöht Kosten und veranlasst Benutzer manchmal zur unsicheren Passwortweitergabe.'),
                'steps'         => [
                    t('Entra ID → Users → Password reset → Properties öffnen'),
                    t('Self-service password reset enabled → „All" auswählen'),
                    t('Authentication methods: mindestens 2 Methoden aktivieren (z.B. App + E-Mail)'),
                    t('Registration: „Require users to register when signing in" aktivieren'),
                    t('Notifications konfigurieren für Admin-Benachrichtigung bei Reset'),
                    t('Speichern und Benutzer per Intranet informieren'),
                ],
                'internal_path' => '/mfamethods',
                'ms_admin_url'  => 'https://entra.microsoft.com/#view/Microsoft_AAD_IAM/PasswordResetMenuBlade/~/Properties',
                'ms_doc_url'    => 'https://learn.microsoft.com/de-de/entra/identity/authentication/tutorial-enable-sspr',
                'bsi_controls'  => ['ORP.4.A8', 'ORP.4.A9'],
                'nis2_articles' => ['Art. 21 Abs. 2(i)'],
            ];
        }

        // privileged_access
        if (in_array('privileged_access', $failedCheckIds, true) || in_array('privileged_access', $warningCheckIds, true)) {
            $recs[] = [
                'id'            => 'privileged_access',
                'severity'      => 'high',
                'title'         => t('Privileged Identity Management (PIM) einrichten'),
                'risk'          => t('Permanente Admin-Zuweisungen stellen ein dauerhaftes Angriffsziel dar. Mit PIM erhalten Admins nur bei Bedarf zeitlich begrenzte Berechtigungen.'),
                'steps'         => [
                    t('Entra ID → Identity Governance → Privileged Identity Management öffnen'),
                    t('Für Global Administrator: Alle permanenten Zuweisungen in „Eligible" umwandeln'),
                    t('Aktivierungseinstellungen: max. 8 Stunden, Begründung erforderlich, MFA erforderlich'),
                    t('Genehmigungsworkflow für Global Admin aktivieren (zweite Person muss genehmigen)'),
                    t('Alerts einrichten: „Admins aren\'t using their privileged roles"'),
                    t('Regelmäßige Access Reviews für PIM-Rollen konfigurieren'),
                ],
                'internal_path' => '/adminroles',
                'ms_admin_url'  => 'https://entra.microsoft.com/#view/Microsoft_Azure_PIMCommon/CommonMenuBlade/~/quickStart',
                'ms_doc_url'    => 'https://learn.microsoft.com/de-de/entra/identity/privileged-identity-management/pim-configure',
                'bsi_controls'  => ['ORP.4.A23', 'ORP.4.A24', 'ISMS.1.A12'],
                'nis2_articles' => ['Art. 21 Abs. 2(i)', 'Art. 21 Abs. 2(a)'],
            ];
        }

        // guest_access
        if (in_array('guest_access', $failedCheckIds, true) || in_array('guest_access', $warningCheckIds, true)) {
            $recs[] = [
                'id'            => 'guest_access',
                'severity'      => 'medium',
                'title'         => t('Gastbenutzer-Berechtigungen einschränken'),
                'risk'          => t('Standardmäßig können Gäste das Verzeichnis durchsuchen und andere Benutzer, Gruppen und Apps einsehen. Dies kann zu ungewollter Datenweitergabe führen.'),
                'steps'         => [
                    t('Entra ID → External Identities → External collaboration settings öffnen'),
                    t('Guest user access: „Guest users have limited access to properties and memberships of directory objects" wählen'),
                    t('Guest invite settings: auf „Only users assigned to specific admin roles can invite" setzen'),
                    t('Collaboration restrictions: Domains einschränken auf bekannte Partner'),
                    t('Entra ID → Users → User settings → External user restrictions prüfen'),
                    t('Regelmäßige Access Reviews für Gastbenutzer aktivieren'),
                ],
                'internal_path' => '/guestusers',
                'ms_admin_url'  => 'https://entra.microsoft.com/#view/Microsoft_AAD_IAM/AllowlistPolicyBlade',
                'ms_doc_url'    => 'https://learn.microsoft.com/de-de/entra/external-id/external-collaboration-settings-configure',
                'bsi_controls'  => ['ORP.4.A9', 'ORP.2.A1'],
                'nis2_articles' => ['Art. 21 Abs. 2(i)', 'Art. 21 Abs. 2(a)'],
            ];
        }

        // external_sharing
        if (in_array('external_sharing', $failedCheckIds, true) || in_array('external_sharing', $warningCheckIds, true)) {
            $recs[] = [
                'id'            => 'external_sharing',
                'severity'      => 'medium',
                'title'         => t('Externe SharePoint/OneDrive-Freigaben einschränken'),
                'risk'          => t('Unreingeschränkte externe Freigaben können dazu führen, dass sensible Unternehmensdaten unkontrolliert nach außen gelangen.'),
                'steps'         => [
                    t('Microsoft 365 Admin Center → SharePoint Admin Center öffnen'),
                    t('Policies → Sharing → External sharing für SharePoint und OneDrive'),
                    t('Empfehlung: „Existing guests" oder „Only people in your organization"'),
                    t('Link expiration für „Anyone"-Links auf max. 7–14 Tage setzen'),
                    t('Link-Typ default auf „Specific people" ändern'),
                    t('Freigabe-Monitor im Tool regelmäßig prüfen (/sharing/monitor)'),
                ],
                'internal_path' => '/sharing/policies',
                'ms_admin_url'  => 'https://admin.microsoft.com/sharepoint',
                'ms_doc_url'    => 'https://learn.microsoft.com/de-de/sharepoint/turn-external-sharing-on-or-off',
                'bsi_controls'  => ['APP.5.2.A4', 'ORP.4.A9', 'CON.2.A1'],
                'nis2_articles' => ['Art. 21 Abs. 2(i)', 'Art. 21 Abs. 2(h)'],
            ];
        }

        // break_glass
        if (in_array('break_glass', $failedCheckIds, true) || in_array('break_glass', $warningCheckIds, true)) {
            $recs[] = [
                'id'            => 'break_glass',
                'severity'      => 'high',
                'title'         => t('Notfallzugangskonten (Break-Glass) einrichten'),
                'risk'          => t('Ohne Break-Glass-Konten kann bei einem MFA-Ausfall oder CA-Fehlkonfiguration kein Admin mehr auf den Tenant zugreifen — vollständiger Kontrollverlust.'),
                'steps'         => [
                    t('Entra ID → Users → New user: Zwei cloud-only Global Admin Konten anlegen'),
                    t('Format: breakglass1@<tenant>.onmicrosoft.com (KEINE eigene Domain)'),
                    t('Kein Benutzer-Postfach zuweisen, keine Lizenz, MFA-Nummer: Büro-Festnetz'),
                    t('Von ALLEN Conditional-Access-Richtlinien ausschließen'),
                    t('Starke Passwörter (32+ Zeichen) in einem physischen Tresor und einem Passwortmanager hinterlegen'),
                    t('Monitoring-Alert einrichten: Sofortbenachrichtigung bei jeder Anmeldung dieser Konten'),
                    t('Quartalsweise Zugang testen (nur lesen, keine Änderungen)'),
                ],
                'internal_path' => '/users',
                'ms_admin_url'  => 'https://entra.microsoft.com/#view/Microsoft_AAD_UsersAndTenants/UserManagementMenuBlade/~/AllUsers',
                'ms_doc_url'    => 'https://learn.microsoft.com/de-de/entra/identity/role-based-access-control/security-emergency-access',
                'bsi_controls'  => ['ORP.4.A23', 'ORP.4.A24', 'DER.2.1.A4'],
                'nis2_articles' => ['Art. 21 Abs. 2(b)', 'Art. 21 Abs. 2(i)'],
            ];
        }

        // defender_for_office
        if (in_array('defender_for_office', $failedCheckIds, true) || in_array('defender_for_office', $warningCheckIds, true)) {
            $recs[] = [
                'id'            => 'defender_for_office',
                'severity'      => 'high',
                'title'         => t('Microsoft Defender for Office 365 konfigurieren'),
                'risk'          => t('Ohne Defender-Schutz sind E-Mails, Teams-Nachrichten und Dateianhänge nicht gegen Zero-Day-Malware, Phishing und bösartige Links geschützt.'),
                'steps'         => [
                    t('Microsoft 365 Defender öffnen: security.microsoft.com'),
                    t('Email & collaboration → Policies & rules → Threat policies'),
                    t('Preset security policies: „Standard protection" oder „Strict protection" aktivieren'),
                    t('Safe Attachments: für alle Empfänger aktivieren, Aktion „Block"'),
                    t('Safe Links: für E-Mail und Teams aktivieren, „Do not allow users to click through" aktivieren'),
                    t('Anti-phishing: Impersonationsschutz für Domains und wichtige Benutzer konfigurieren'),
                    t('DMARC/DKIM/SPF für alle Domains prüfen (/domainhealth)'),
                ],
                'internal_path' => '/mailflow',
                'ms_admin_url'  => 'https://security.microsoft.com/threatpolicy',
                'ms_doc_url'    => 'https://learn.microsoft.com/de-de/microsoft-365/security/office-365-security/preset-security-policies',
                'bsi_controls'  => ['APP.5.2.A4', 'APP.5.2.A5', 'DER.1.A1'],
                'nis2_articles' => ['Art. 21 Abs. 2(e)', 'Art. 21 Abs. 2(g)'],
            ];
        }

        // audit_log
        if (in_array('audit_log', $failedCheckIds, true) || in_array('audit_log', $warningCheckIds, true)) {
            $recs[] = [
                'id'            => 'audit_log',
                'severity'      => 'high',
                'title'         => t('Unified Audit Log aktivieren'),
                'risk'          => t('Ohne Audit-Log können Sicherheitsvorfälle nicht untersucht werden. Bei einem Breach fehlt die forensische Grundlage — und in manchen Regulierungen ist die Protokollierung Pflicht.'),
                'steps'         => [
                    t('Microsoft Purview Compliance Portal öffnen: compliance.microsoft.com'),
                    t('Audit → Start recording user and admin activity (Schaltfläche klicken)'),
                    t('Bestätigen: „Turn on auditing" → Aktivierung kann bis zu 60 Minuten dauern'),
                    t('Audit-Aufbewahrung prüfen: Standard 90 Tage; für E5/Compliance-Lizenz auf 1 Jahr erweitern'),
                    t('Kritische Operationen im Audit suchen: Admin-Aktivitäten, Mailbox-Zugriffe, Dateilöschungen'),
                    t('Alert-Richtlinien für kritische Ereignisse einrichten (Insider Risk)'),
                ],
                'internal_path' => '/auditlog',
                'ms_admin_url'  => 'https://compliance.microsoft.com/auditlogsearch',
                'ms_doc_url'    => 'https://learn.microsoft.com/de-de/purview/audit-log-enable-disable',
                'bsi_controls'  => ['DER.1.A1', 'DER.1.A2', 'OPS.1.1.5.A1'],
                'nis2_articles' => ['Art. 21 Abs. 2(a)', 'Art. 21 Abs. 2(f)'],
            ];
        }

        // compliant_devices
        if (in_array('compliant_devices', $failedCheckIds, true) || in_array('compliant_devices', $warningCheckIds, true)) {
            $recs[] = [
                'id'            => 'compliant_devices',
                'severity'      => 'high',
                'title'         => t('Gerätekonformität über Intune erzwingen'),
                'risk'          => t('Nicht verwaltete oder nicht konforme Geräte können Schadsoftware einschleusen und auf Unternehmensressourcen zugreifen, ohne dass Sicherheitsstandards eingehalten werden.'),
                'steps'         => [
                    t('Microsoft Intune Admin Center öffnen: intune.microsoft.com'),
                    t('Devices → Compliance policies → Create policy'),
                    t('Windows: BitLocker aktiviert, Mindest-OS-Version, Firewall an, AV-Schutz aktiv'),
                    t('iOS/macOS: Mindest-OS, Passcode erforderlich, Jailbreak-Erkennung'),
                    t('Android: Mindest-OS, Encryption, SafetyNet-Attestation'),
                    t('Conditional Access: Richtlinie „Compliant device required" für alle Apps aktivieren'),
                    t('Nicht konforme Geräte im Tool prüfen (/devices)'),
                ],
                'internal_path' => '/devices',
                'ms_admin_url'  => 'https://intune.microsoft.com/#view/Microsoft_Intune_DeviceSettings/DevicesMenu/~/compliance',
                'ms_doc_url'    => 'https://learn.microsoft.com/de-de/mem/intune/protect/device-compliance-get-started',
                'bsi_controls'  => ['SYS.2.1.A3', 'SYS.2.1.A36', 'SYS.2.1.A38'],
                'nis2_articles' => ['Art. 21 Abs. 2(e)', 'Art. 21 Abs. 2(i)'],
            ];
        }

        // mdm_enrollment
        if (in_array('mdm_enrollment', $failedCheckIds, true) || in_array('mdm_enrollment', $warningCheckIds, true)) {
            $recs[] = [
                'id'            => 'mdm_enrollment',
                'severity'      => 'medium',
                'title'         => t('MDM-Auto-Enrollment in Intune konfigurieren'),
                'risk'          => t('Ohne automatische Geräteregistrierung werden Endgeräte nicht zentral verwaltet. Sicherheitsrichtlinien, Updates und App-Schutz können nicht durchgesetzt werden.'),
                'steps'         => [
                    t('Intune Admin Center → Devices → Enrollment → Windows enrollment'),
                    t('Automatic Enrollment: MDM-Benutzerbereich auf „All" (oder ausgewählte Gruppe) setzen'),
                    t('Windows Autopilot für neue Geräte konfigurieren (Zero-Touch-Deployment)'),
                    t('iOS/iPadOS: Apple Business Manager (ABM) mit Intune verbinden → DEP-Profil erstellen'),
                    t('Android: Android Enterprise Enrollment → Work Profile aktivieren'),
                    t('App Protection Policies (MAM) für BYOD-Geräte ohne vollständige Enrollierung einrichten'),
                ],
                'internal_path' => '/devices',
                'ms_admin_url'  => 'https://intune.microsoft.com/#view/Microsoft_Intune_DeviceSettings/DevicesMenu/~/enrollment',
                'ms_doc_url'    => 'https://learn.microsoft.com/de-de/mem/intune/enrollment/',
                'bsi_controls'  => ['SYS.2.1.A36', 'ORP.4.A9'],
                'nis2_articles' => ['Art. 21 Abs. 2(e)', 'Art. 21 Abs. 2(i)'],
            ];
        }

        // secure_score
        if (in_array('secure_score', $failedCheckIds, true) || in_array('secure_score', $warningCheckIds, true)) {
            $recs[] = [
                'id'            => 'secure_score',
                'severity'      => 'medium',
                'title'         => t('Microsoft Secure Score-Maßnahmen umsetzen'),
                'risk'          => t('Ein niedriger Secure Score zeigt konkrete Sicherheitslücken an. Microsoft priorisiert die Maßnahmen nach Aufwand und Punktegewinn.'),
                'steps'         => [
                    t('Microsoft 365 Defender öffnen → Secure Score: security.microsoft.com/securescore'),
                    t('Tab „Improvement actions" öffnen, nach „Points achieved" sortieren'),
                    t('Filter: Status „To address" → einfachste Maßnahmen zuerst umsetzen'),
                    t('Typisch hohe Punkte: MFA, Legacy-Auth, Admin-MFA, SSPR'),
                    t('Für jede Maßnahme: „Manage" klicken → direkt zur Konfiguration springen'),
                    t('Ziel: Score über 50 % als ersten Meilenstein, dann über 70 %'),
                    t('Im Tool unter /securescore den aktuellen Score verfolgen'),
                ],
                'internal_path' => '/securescore',
                'ms_admin_url'  => 'https://security.microsoft.com/securescore',
                'ms_doc_url'    => 'https://learn.microsoft.com/de-de/microsoft-365/security/defender/microsoft-secure-score',
                'bsi_controls'  => ['ISMS.1.A9', 'ISMS.1.A12'],
                'nis2_articles' => ['Art. 21 Abs. 2(a)', 'Art. 21 Abs. 2(f)'],
            ];
        }

        // password_expiry
        if (in_array('password_expiry', $failedCheckIds, true) || in_array('password_expiry', $warningCheckIds, true)) {
            $recs[] = [
                'id'            => 'password_expiry',
                'severity'      => 'low',
                'title'         => t('Passwort-Ablaufrichtlinie überprüfen und optimieren'),
                'risk'          => t('Zu häufige Passwort-Ablauffristen führen dazu, dass Benutzer schwache, vorhersehbare Passwörter wählen (z.B. „Sommer24"). Die beste Lösung ist Passwordless.'),
                'steps'         => [
                    t('Entra ID → Users → Password expiration policy prüfen'),
                    t('Empfehlung 1 (Best Practice): Password never expires aktivieren + MFA/Passwordless erzwingen'),
                    t('Empfehlung 2 (Kompromiss): 90-Tage-Ablauf behalten + SSPR + Microsoft Entra Password Protection'),
                    t('Entra Password Protection aktivieren: bannt schwache/geleakte Passwörter'),
                    t('Für maximale Sicherheit: Passwordless (FIDO2-Schlüssel, Windows Hello) einführen'),
                    t('Passwort-Ablauf-Bericht im Tool unter /passwordexpiry prüfen'),
                ],
                'internal_path' => '/passwordexpiry',
                'ms_admin_url'  => 'https://entra.microsoft.com/#view/Microsoft_AAD_UsersAndTenants/UserManagementMenuBlade/~/AllUsers',
                'ms_doc_url'    => 'https://learn.microsoft.com/de-de/entra/identity/authentication/concept-sspr-policy',
                'bsi_controls'  => ['ORP.4.A8'],
                'nis2_articles' => ['Art. 21 Abs. 2(i)'],
            ];
        }

        // alert_policy
        if (in_array('alert_policy', $failedCheckIds, true) || in_array('alert_policy', $warningCheckIds, true)) {
            $recs[] = [
                'id'            => 'alert_policy',
                'severity'      => 'medium',
                'title'         => t('Standard-Alertrichtlinien für verdächtige Aktivitäten aktivieren'),
                'risk'          => t('Ohne Alert-Richtlinien werden Angriffe wie Brute-Force, Massendownloads oder Privilege-Escalation nicht rechtzeitig erkannt.'),
                'steps'         => [
                    t('Microsoft 365 Defender → Alerts → Alert policies öffnen'),
                    t('Alle Standard-Alertrichtlinien prüfen: status sollte „On" sein'),
                    t('Wichtigste Alerts aktivieren: „Suspicious email sending patterns", „Unusual volume of file deletion", „Elevation of Exchange admin privilege"'),
                    t('Microsoft Purview → Insider Risk Management: neue Richtlinie für Datenverlust'),
                    t('Alert-Empfänger konfigurieren: IT-Team E-Mail-Adresse hinterlegen'),
                    t('Wöchentlichen E-Mail-Report im Tool aktivieren (Einstellungen → Wöchentlicher Report)'),
                ],
                'internal_path' => '/defenderalerts',
                'ms_admin_url'  => 'https://security.microsoft.com/alertpolicies',
                'ms_doc_url'    => 'https://learn.microsoft.com/de-de/purview/alert-policies',
                'bsi_controls'  => ['DER.1.A2', 'DER.2.1.A4'],
                'nis2_articles' => ['Art. 21 Abs. 2(b)', 'Art. 21 Abs. 2(f)'],
            ];
        }

        // ── Metric-based recommendations ─────────────────────────────────────

        $users    = $metrics['users']    ?? [];
        $sharing  = $metrics['sharing']  ?? [];
        $licenses = $metrics['licenses'] ?? [];
        $devices  = $metrics['devices']  ?? [];

        // metric_mfa_low: MFA < 80% AND not already covered by mfa_registration check
        $mfaPct = (int)($users['mfa_registered_pct'] ?? 100);
        if ($mfaPct < 80 && !in_array('mfa_registration', array_merge($failedCheckIds, $warningCheckIds), true)) {
            $recs[] = [
                'id'            => 'metric_mfa_low',
                'severity'      => 'critical',
                'title'         => t('MFA-Registrierungsquote kritisch niedrig (:pct %)', ['pct' => $mfaPct]),
                'risk'          => t('Nur :pct % der Benutzer haben MFA registriert. Jedes nicht durch MFA geschützte Konto ist ein potenzieller Eintrittspunkt für Angreifer.', ['pct' => $mfaPct]),
                'steps'         => [
                    t('Entra ID → Security → Conditional Access → Neue Richtlinie'),
                    t('MFA-Registrierungskampagne starten: Benutzer per E-Mail informieren (aka.ms/mfasetup)'),
                    t('CA-Richtlinie: MFA-Registrierung für alle Benutzer mit Location-Condition erzwingen'),
                    t('Report-only-Modus für 7 Tage → dann aktivieren'),
                    t('Helpdesk auf erhöhtes Aufkommen vorbereiten'),
                    t('Fortschritt täglich in /mfamethods prüfen'),
                ],
                'internal_path' => '/mfamethods',
                'ms_admin_url'  => 'https://entra.microsoft.com/#view/Microsoft_AAD_ConditionalAccess/ConditionalAccessBlade/~/Policies',
                'ms_doc_url'    => 'https://learn.microsoft.com/de-de/entra/identity/conditional-access/howto-conditional-access-policy-all-users-mfa',
                'bsi_controls'  => ['ORP.4.A9', 'ORP.4.A21'],
                'nis2_articles' => ['Art. 21 Abs. 2(i)', 'Art. 21 Abs. 2(j)'],
            ];
        }

        // metric_stale_accounts: stale > 5
        $staleCount = (int)($users['stale_90d_count'] ?? 0);
        if ($staleCount > 5) {
            $recs[] = [
                'id'            => 'metric_stale_accounts',
                'severity'      => 'medium',
                'title'         => t(':count inaktive Konten (>90 Tage) identifiziert', ['count' => $staleCount]),
                'risk'          => t('Inaktive Konten mit aktiven Lizenzen und Zugriffsrechten sind unnötige Angriffsfläche. Kompromittierte veraltete Konten bleiben oft unbemerkt.'),
                'steps'         => [
                    t('Im Tool unter /staleaccounts die Liste überprüfen'),
                    t('Für jeden inaktiven Benutzer prüfen: ausgeschieden, langzeit-krank oder vergessen?'),
                    t('Ausgeschiedene Mitarbeiter sofort deaktivieren und Lizenzen entziehen'),
                    t('Benutzer deaktivieren (nicht löschen): Entra ID → User → Block sign-in'),
                    t('Lizenzen entziehen: im Tool direkt möglich unter /staleaccounts'),
                    t('Nach 30-tägiger Aufbewahrungsfrist: Konto löschen (Postfach-Inhalte vorher sichern)'),
                    t('Automatische Lizenzfreigabe in Einstellungen → Inaktive Konten konfigurieren'),
                ],
                'internal_path' => '/staleaccounts',
                'ms_admin_url'  => 'https://entra.microsoft.com/#view/Microsoft_AAD_UsersAndTenants/UserManagementMenuBlade/~/AllUsers',
                'ms_doc_url'    => null,
                'bsi_controls'  => ['ORP.4.A9', 'ORP.2.A1'],
                'nis2_articles' => ['Art. 21 Abs. 2(i)'],
            ];
        }

        // metric_anon_shares: anonymous > 0
        $anonCount = (int)($sharing['anonymous_count'] ?? 0);
        if ($anonCount > 0) {
            $recs[] = [
                'id'            => 'metric_anon_shares',
                'severity'      => 'high',
                'title'         => t(':count aktive anonyme Freigabe(n) (Anyone-Links) gefunden', ['count' => $anonCount]),
                'risk'          => t('Anyone-Links können von beliebigen Personen ohne Authentifizierung aufgerufen werden — auch nach dem Verlassen des Unternehmens durch die ursprüngliche Person.'),
                'steps'         => [
                    t('Im Tool unter /sharing die anonymen Freigaben prüfen und widerrufen'),
                    t('SharePoint Admin Center → Policies → Sharing → „Anyone"-Links global deaktivieren'),
                    t('Alternativ: Ablaufdatum für Anyone-Links auf max. 7 Tage begrenzen'),
                    t('Bestehende Links: SharePoint Admin Center → Active sites → Sharing-Berichte'),
                    t('Freigabe-Monitor aktivieren, um künftig anonyme Links automatisch zu erkennen'),
                    t('Link-Standard auf „Specific people" ändern (SharePoint Admin → Sharing)'),
                ],
                'internal_path' => '/sharing',
                'ms_admin_url'  => 'https://admin.microsoft.com/sharepoint',
                'ms_doc_url'    => 'https://learn.microsoft.com/de-de/sharepoint/turn-external-sharing-on-or-off',
                'bsi_controls'  => ['APP.5.2.A4', 'CON.2.A1'],
                'nis2_articles' => ['Art. 21 Abs. 2(h)', 'Art. 21 Abs. 2(i)'],
            ];
        }

        // metric_no_license: enabled without license > 0
        $noLicenseCount = (int)($users['enabled_no_license'] ?? 0);
        if ($noLicenseCount > 0) {
            $recs[] = [
                'id'            => 'metric_no_license',
                'severity'      => 'low',
                'title'         => t(':count aktivierte Konten ohne Lizenz gefunden', ['count' => $noLicenseCount]),
                'risk'          => t('Aktivierte Konten ohne Lizenz können sich weiterhin per OAuth oder Legacy-Auth authentifizieren und stellen unnötige Angriffsfläche dar.'),
                'steps'         => [
                    t('Im Tool unter /users nach Benutzern ohne Lizenzen filtern'),
                    t('Für jeden Benutzer prüfen: Shared-Mailbox-Konto (OK), Dienstkonto (prüfen) oder veraltetes Konto (deaktivieren)'),
                    t('Nicht benötigte Konten sofort deaktivieren: Entra ID → User → Block sign-in'),
                    t('Dienstkonten dokumentieren und MFA-Ausnahme-Prozess prüfen'),
                    t('Microsoft 365 Admin → Licenses → Nicht zugewiesene Lizenzen prüfen'),
                ],
                'internal_path' => '/users',
                'ms_admin_url'  => 'https://admin.microsoft.com/#/licenses',
                'ms_doc_url'    => null,
                'bsi_controls'  => ['ORP.4.A9'],
                'nis2_articles' => ['Art. 21 Abs. 2(i)'],
            ];
        }

        // metric_high_license_util: high utilization SKUs > 0
        $highUtilSkus = (int)($licenses['high_utilization_skus'] ?? 0);
        if ($highUtilSkus > 0) {
            $recs[] = [
                'id'            => 'metric_high_license_util',
                'severity'      => 'medium',
                'title'         => t(':count Lizenz-SKU(s) mit über 90 % Auslastung', ['count' => $highUtilSkus]),
                'risk'          => t('SKUs mit über 90 % Auslastung können bei Neueinstellungen oder Umstrukturierungen zu sofortigen Engpässen führen und den Onboarding-Prozess blockieren.'),
                'steps'         => [
                    t('Im Tool unter /licenseadvisor die hoch ausgelasteten SKUs identifizieren'),
                    t('Microsoft 365 Admin Center → Billing → Licenses: aktuelle Bestände prüfen'),
                    t('Zusätzliche Lizenzen rechtzeitig bestellen (mindestens 2 Wochen Vorlauf)'),
                    t('Nicht zugewiesene Lizenzen von inaktiven Konten freigeben (/staleaccounts)'),
                    t('Lizenz-Advisor nutzen, um zu prüfen ob günstigere Bundles sinnvoll sind'),
                    t('Automatischen Alert konfigurieren: Einstellungen → Alert-Schwellwerte → Lizenzauslastung'),
                ],
                'internal_path' => '/licenseadvisor',
                'ms_admin_url'  => 'https://admin.microsoft.com/#/licenses',
                'ms_doc_url'    => null,
                'bsi_controls'  => ['OPS.1.1.3.A4'],
                'nis2_articles' => ['Art. 21 Abs. 2(a)'],
            ];
        }

        // metric_noncompliant_devices: non_compliant > 3
        $nonCompliant = (int)($devices['non_compliant'] ?? 0);
        if ($nonCompliant > 3 && !in_array('compliant_devices', array_merge($failedCheckIds, $warningCheckIds), true)) {
            $recs[] = [
                'id'            => 'metric_noncompliant_devices',
                'severity'      => 'high',
                'title'         => t(':count nicht konforme Geräte in Intune', ['count' => $nonCompliant]),
                'risk'          => t('Nicht konforme Geräte erfüllen die Sicherheitsanforderungen nicht (z.B. fehlendes BitLocker, veraltetes OS, kein Virenschutz) und sollten blockiert werden.'),
                'steps'         => [
                    t('Intune Admin Center → Devices → Monitor → Noncompliant devices öffnen'),
                    t('Für jedes nicht konforme Gerät den Grund prüfen (Compliance details)'),
                    t('Benutzer benachrichtigen: Automatische Benachrichtigung in Compliance Policy konfigurieren'),
                    t('Zeitlich begrenzte Übergangsfrist (Grace Period) auf max. 7 Tage setzen'),
                    t('Nach Ablauf: CA-Richtlinie blockiert Zugriff automatisch'),
                    t('Im Tool unter /devices alle nicht konformen Geräte und Besitzer einsehen'),
                ],
                'internal_path' => '/devices',
                'ms_admin_url'  => 'https://intune.microsoft.com/#view/Microsoft_Intune_DeviceSettings/DevicesMenu/~/compliance',
                'ms_doc_url'    => 'https://learn.microsoft.com/de-de/mem/intune/protect/device-compliance-get-started',
                'bsi_controls'  => ['SYS.2.1.A3', 'SYS.2.1.A36', 'SYS.2.1.A38'],
                'nis2_articles' => ['Art. 21 Abs. 2(e)', 'Art. 21 Abs. 2(i)'],
            ];
        }

        // ── DSGVO / GDPR recommendations ──────────────────────────────────────
        // Für DSGVO-Recs zählt jeder Status außer 'pass' — auch 'unknown'
        // (= konnte nicht geprüft werden) ist für den Compliance-Beauftragten
        // relevant, weil er manuell verifizieren muss.
        $gdprFailWarn = array_merge($failedCheckIds, $warningCheckIds);
        $gdprAll      = array_merge($gdprFailWarn, $unknownIds);

        // Helper: liefert eine Severity-Anpassung und einen Unknown-Hinweis,
        // falls die Check-ID nur im unknownIds-Bucket steht.
        $gdprAnnotate = function (array $rec, string $checkId) use ($gdprFailWarn, $unknownIds): array {
            if (in_array($checkId, $gdprFailWarn, true)) return $rec;
            if (in_array($checkId, $unknownIds, true)) {
                $rec['severity'] = 'medium';                      // Unknown ist weniger akut als fail
                $rec['risk']    .= t(' — Status konnte vom Tool nicht automatisiert geprüft werden (fehlende Berechtigung, Lizenz oder Endpunkt nicht erreichbar). Bitte manuell im Admin-Center verifizieren.');
            }
            return $rec;
        };

        if (in_array('gdpr_tenant_region', $gdprAll, true)) {
            $recs[] = $gdprAnnotate([
                'id'            => 'gdpr_tenant_region',
                'severity'      => 'high',
                'title'         => t('Tenant-Region außerhalb EU/EWR — Drittlandtransfer prüfen'),
                'risk'          => t('Daten werden außerhalb des EWR verarbeitet. Ohne Adäquanzbeschluss oder geeignete Garantien (Standardvertragsklauseln + Transfer Impact Assessment) ist die Übermittlung nach Art. 44–49 DSGVO unzulässig.'),
                'steps'         => [
                    t('Microsoft 365 Admin Center → Settings → Org Settings → Organization profile → Data location prüfen'),
                    t('Falls Verlagerung möglich: Multi-Geo oder Tenant in EU-Region beantragen'),
                    t('Andernfalls: DPA-Auftragsverarbeitungsvertrag + EU-Standardvertragsklauseln (Microsoft DPA) prüfen'),
                    t('Transfer Impact Assessment (Schrems-II) dokumentieren'),
                ],
                'internal_path' => '/securityposture',
                'ms_admin_url'  => 'https://admin.microsoft.com/Adminportal/Home#/companyprofile',
                'ms_doc_url'    => 'https://learn.microsoft.com/de-de/microsoft-365/enterprise/o365-data-locations',
                'gdpr_articles' => ['Art. 44', 'Art. 46', 'Art. 49'],
            ], 'gdpr_tenant_region');
        }

        if (in_array('gdpr_sharepoint_sharing', $gdprAll, true)) {
            $recs[] = $gdprAnnotate([
                'id'            => 'gdpr_sharepoint_sharing',
                'severity'      => 'high',
                'title'         => t('SharePoint-/OneDrive-Sharing einschränken'),
                'risk'          => t('Anyone-Links erlauben unbekannten Dritten den Zugriff auf personenbezogene Daten. Verletzt Privacy-by-Default (Art. 25 DSGVO) und Datensicherheit (Art. 32).'),
                'steps'         => [
                    t('SharePoint Admin Center → Policies → Sharing'),
                    t('External sharing auf „New and existing guests" oder restriktiver setzen'),
                    t('„Anyone with the link" nur in Ausnahmefällen erlauben — sonst deaktivieren'),
                    t('Pro-Site-Übersteuerung prüfen (sensitive Sites strenger als Tenant-Default)'),
                ],
                'internal_path' => '/sharing/policies',
                'ms_admin_url'  => 'https://admin.microsoft.com/sharepoint?page=sharing',
                'ms_doc_url'    => 'https://learn.microsoft.com/de-de/sharepoint/turn-external-sharing-on-or-off',
                'gdpr_articles' => ['Art. 25', 'Art. 32'],
            ], 'gdpr_sharepoint_sharing');
        }

        if (in_array('gdpr_anonymous_link_expiry', $gdprAll, true)) {
            $recs[] = $gdprAnnotate([
                'id'            => 'gdpr_anonymous_link_expiry',
                'severity'      => 'medium',
                'title'         => t('Ablauffrist für anonyme Links setzen'),
                'risk'          => t('Anyone-Links ohne Ablauf bleiben unbegrenzt nutzbar und verletzen Speicherbegrenzung (Art. 5 Abs. 1e DSGVO).'),
                'steps'         => [
                    t('SharePoint Admin Center → Policies → Sharing → File and folder links'),
                    t('„Anyone links expire in" auf maximal 90 Tage setzen'),
                    t('Standard-Linktyp auf „Only people in your organization" oder „Specific people" stellen'),
                ],
                'internal_path' => '/sharing/policies',
                'ms_admin_url'  => 'https://admin.microsoft.com/sharepoint?page=sharing',
                'ms_doc_url'    => 'https://learn.microsoft.com/de-de/sharepoint/file-and-folder-links',
                'gdpr_articles' => ['Art. 5 Abs. 1e'],
            ], 'gdpr_anonymous_link_expiry');
        }

        if (in_array('gdpr_default_sharing_link', $gdprAll, true)) {
            $recs[] = $gdprAnnotate([
                'id'            => 'gdpr_default_sharing_link',
                'severity'      => 'medium',
                'title'         => t('Default-Freigabetyp auf intern stellen'),
                'risk'          => t('Mit Anyone als Default-Linktyp werden personenbezogene Daten unbeabsichtigt nach außen geteilt.'),
                'steps'         => [
                    t('SharePoint Admin Center → Policies → Sharing → File and folder links'),
                    t('Default link type auf „Only people in your organization" oder „Specific people" setzen'),
                ],
                'ms_admin_url'  => 'https://admin.microsoft.com/sharepoint?page=sharing',
                'ms_doc_url'    => 'https://learn.microsoft.com/de-de/sharepoint/file-and-folder-links',
                'gdpr_articles' => ['Art. 25'],
            ], 'gdpr_default_sharing_link');
        }

        if (in_array('gdpr_sensitivity_labels', $gdprAll, true) || in_array('gdpr_dlp_or_labels', $gdprAll, true)) {
            $sensCheckId = in_array('gdpr_sensitivity_labels', $gdprFailWarn, true) ? 'gdpr_sensitivity_labels'
                          : (in_array('gdpr_dlp_or_labels', $gdprFailWarn, true) ? 'gdpr_dlp_or_labels'
                          : 'gdpr_sensitivity_labels');
            $recs[] = $gdprAnnotate([
                'id'            => 'gdpr_sensitivity_labels',
                'severity'      => 'high',
                'title'         => t('Sensitivity Labels einrichten & veröffentlichen'),
                'risk'          => t('Ohne Vertraulichkeitsbezeichnungen lassen sich personenbezogene Daten nicht systematisch kennzeichnen, verschlüsseln oder vor unbefugter Weitergabe schützen.'),
                'steps'         => [
                    t('Microsoft Purview → Information Protection → Labels'),
                    t('Mindestens 4 Labels: „Öffentlich", „Intern", „Vertraulich", „Streng vertraulich"'),
                    t('Verschlüsselung + Rights Management für „Vertraulich" und höher konfigurieren'),
                    t('Label-Policy veröffentlichen für alle relevanten Benutzergruppen'),
                    t('Auto-Labeling-Policy für personen­bezogene Daten (PII-Klassifizierer)'),
                ],
                'internal_path' => '/sensitivitylabels',
                'ms_admin_url'  => 'https://purview.microsoft.com/informationprotection/sensitivitylabels',
                'ms_doc_url'    => 'https://learn.microsoft.com/de-de/purview/sensitivity-labels',
                'gdpr_articles' => ['Art. 25', 'Art. 32'],
            ], $sensCheckId);
        }

        if (in_array('gdpr_retention_policies', $gdprAll, true)) {
            $recs[] = $gdprAnnotate([
                'id'            => 'gdpr_retention_policies',
                'severity'      => 'medium',
                'title'         => t('Aufbewahrungsrichtlinien definieren'),
                'risk'          => t('Ohne klare Aufbewahrungsfristen können personenbezogene Daten gegen die Speicherbegrenzung verstoßen — und das Löschrecht (Art. 17 DSGVO) ist nicht umsetzbar.'),
                'steps'         => [
                    t('Microsoft Purview → Data Lifecycle Management → Retention policies'),
                    t('Pro Datenkategorie (Mail, SharePoint, OneDrive, Teams) Frist definieren'),
                    t('Automatische Löschung am Ende der Frist aktivieren'),
                    t('Auskunfts- und Löschverfahren dokumentieren'),
                ],
                'internal_path' => '/retentionpolicies',
                'ms_admin_url'  => 'https://purview.microsoft.com/datalifecyclemanagement',
                'ms_doc_url'    => 'https://learn.microsoft.com/de-de/purview/retention',
                'gdpr_articles' => ['Art. 5 Abs. 1e', 'Art. 17'],
            ], 'gdpr_retention_policies');
        }

        if (in_array('gdpr_audit_log', $gdprAll, true)) {
            $recs[] = $gdprAnnotate([
                'id'            => 'gdpr_audit_log',
                'severity'      => 'high',
                'title'         => t('Audit-Log aktivieren & Aufbewahrung sicherstellen'),
                'risk'          => t('Ohne lückenloses Audit-Log ist die Rechenschaftspflicht (Art. 5 Abs. 2 DSGVO) und Nachweis der Sicherheitsmaßnahmen (Art. 32) nicht erfüllbar. Im Verdachts­fall keine forensische Auswertung möglich.'),
                'steps'         => [
                    t('Microsoft Purview → Audit → Audit-Log aktivieren (falls noch nicht)'),
                    t('Aufbewahrung auf mindestens 180 Tage (besser 1 Jahr) anheben'),
                    t('AuditLog.Read.All-Berechtigung für die App-Registrierung sicherstellen'),
                    t('Regelmäßige Auswertung im Audit-Log-Modul des Tools'),
                ],
                'internal_path' => '/auditlog',
                'ms_admin_url'  => 'https://purview.microsoft.com/audit',
                'ms_doc_url'    => 'https://learn.microsoft.com/de-de/purview/audit-log-enable-disable',
                'gdpr_articles' => ['Art. 5 Abs. 2', 'Art. 32'],
            ], 'gdpr_audit_log');
        }

        // ── Anomaly-driven recommendations ───────────────────────────────────
        $signinAn = $metrics['signin_anomalies'] ?? [];
        if (($signinAn['credential_stuffing_signatures'] ?? 0) > 0) {
            $recs[] = [
                'id'            => 'anomaly_credential_stuffing',
                'severity'      => 'high',
                'title'         => t('Credential-Stuffing-Signaturen erkannt'),
                'risk'          => t('Es wurden Cluster aus ≥ 5 fehlgeschlagenen Logins gefolgt von einem Erfolg innerhalb von 30 Minuten erkannt. Klassisches Muster für automatisiertes Ausprobieren geleakter Passwörter.'),
                'steps'         => [
                    t('Risk-based Sign-in CA-Policy aktivieren (Block oder MFA bei „high")'),
                    t('Smart Lockout-Schwellen in Entra ID auf 5–10 Versuche prüfen'),
                    t('Betroffene Konten im Sign-in-Log identifizieren und Passwort-Reset erzwingen'),
                ],
                'internal_path' => '/signinlog',
                'ms_admin_url'  => 'https://entra.microsoft.com/#view/Microsoft_AAD_ConditionalAccess/ConditionalAccessBlade',
                'ms_doc_url'    => 'https://learn.microsoft.com/de-de/entra/identity/authentication/howto-password-smart-lockout',
                'bsi_controls'  => ['ORP.4.A23'],
                'nis2_articles' => ['Art. 21 Abs. 2(d)'],
            ];
        }

        if (($signinAn['impossible_travel_count'] ?? 0) > 0) {
            $recs[] = [
                'id'            => 'anomaly_impossible_travel',
                'severity'      => 'high',
                'title'         => t('Impossible-Travel-Vorfälle aufklären'),
                'risk'          => t('Erfolgreiche Logins desselben Kontos aus unterschiedlichen Ländern innerhalb < 4 Stunden. Starker Indikator für Token-Theft oder geteilte Credentials.'),
                'steps'         => [
                    t('Sign-in-Log nach Risk Level „medium/high" filtern'),
                    t('Betroffene Konten: Sitzungen revoken (revokeSignInSessions)'),
                    t('Passwort-Reset erzwingen und MFA-Re-Registrierung'),
                    t('CA-Policy „Block VPN/Anonymizer" prüfen'),
                ],
                'internal_path' => '/signinlog',
                'ms_admin_url'  => 'https://entra.microsoft.com/#view/Microsoft_AAD_IAM/UsersManagementMenuBlade',
                'ms_doc_url'    => 'https://learn.microsoft.com/de-de/entra/id-protection/concept-identity-protection-risks',
                'bsi_controls'  => ['ORP.4.A23'],
                'nis2_articles' => ['Art. 21 Abs. 2(d)'],
            ];
        }

        $auditAn = $metrics['audit_log_anomalies'] ?? [];
        if (!empty($auditAn['anomalies'])) {
            $cats = array_map(fn($a) => $a['category'], $auditAn['anomalies']);
            $recs[] = [
                'id'            => 'anomaly_audit_spike',
                'severity'      => 'medium',
                'title'         => t('Auffällige Audit-Aktivität: :cats', ['cats' => implode(', ', array_slice($cats, 0, 3))]),
                'risk'          => t('Eine oder mehrere Aktivitäts-Kategorien liegen deutlich über dem 23-Tage-Schnitt. Mögliche Ursachen: legitime Massen­änderungen (Onboarding/Offboarding) oder unautorisierte Massen­aktion (z. B. Insider-Misuse).'),
                'steps'         => [
                    t('Audit-Log im Tool öffnen und nach den genannten Kategorien filtern'),
                    t('Zeitfenster und ausführende Konten verifizieren'),
                    t('Bei verdächtigem Muster: Konto sperren, Sitzungen revoken, Forensik einleiten'),
                ],
                'internal_path' => '/auditlog',
                'ms_admin_url'  => 'https://purview.microsoft.com/audit',
                'ms_doc_url'    => 'https://learn.microsoft.com/de-de/purview/audit-search',
                'bsi_controls'  => ['DER.1.A4'],
                'nis2_articles' => ['Art. 21 Abs. 2(g)'],
            ];
        }

        // ── Sort by severity ──────────────────────────────────────────────────
        usort($recs, static function (array $a, array $b): int {
            $ao = self::SEVERITY_ORDER[$a['severity']] ?? 3;
            $bo = self::SEVERITY_ORDER[$b['severity']] ?? 3;
            return $ao <=> $bo;
        });

        return $recs;
    }
}
