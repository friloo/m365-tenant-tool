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
     * @param array    $metrics  Full context array from AiAdvisorService::buildContext()
     * @return array[]
     */
    public static function get(array $failedCheckIds, array $warningCheckIds, array $metrics): array
    {
        $recs = [];

        // ── Check-based recommendations ──────────────────────────────────────

        // mfa_registration
        if (in_array('mfa_registration', $failedCheckIds, true) || in_array('mfa_registration', $warningCheckIds, true)) {
            $recs[] = [
                'id'            => 'mfa_registration',
                'severity'      => 'critical',
                'title'         => 'MFA-Registrierung für alle Benutzer erzwingen',
                'risk'          => 'Konten ohne MFA sind das häufigste Einfallstor bei Accountübernahmen. Microsoft-Daten zeigen: MFA blockiert 99,9 % aller automatisierten Angriffe.',
                'steps'         => [
                    'Entra ID öffnen → Security → Conditional Access',
                    'Neue Richtlinie erstellen: „MFA für alle Benutzer"',
                    'Zuweisung: Alle Benutzer (Ausnahme: Break-Glass-Konten)',
                    'Cloud-Apps: Alle Cloud-Apps',
                    'Zugriffssteuerung → Gewähren → MFA erforderlich',
                    'Richtlinie erst im Report-only-Modus testen, dann aktivieren',
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
                'title'         => 'Legacy-Authentifizierung blockieren',
                'risk'          => 'Protokolle wie Basic Auth, IMAP und POP3 unterstützen keine MFA. Angreifer nutzen gezielt Legacy-Auth-Endpunkte, um MFA zu umgehen.',
                'steps'         => [
                    'Entra ID → Security → Conditional Access → Neue Richtlinie',
                    'Name: „Blockiere Legacy-Authentifizierung"',
                    'Zuweisung: Alle Benutzer',
                    'Cloud-Apps: Alle Cloud-Apps',
                    'Bedingungen → Client-Apps → Legacy-Authentifizierungsclients (alle auswählen)',
                    'Zugriffssteuerung → Blockieren',
                    'Zuerst im Report-only-Modus testen; Dienste wie Exchange ActiveSync vorab prüfen',
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
                'title'         => 'MFA für alle Administratoren erzwingen',
                'risk'          => 'Kompromittierte Admin-Konten ermöglichen vollständige Tenant-Übernahme. Admins sind bevorzugtes Ziel von Phishing-Kampagnen.',
                'steps'         => [
                    'Entra ID → Security → Conditional Access → Neue Richtlinie',
                    'Name: „MFA für alle Admins"',
                    'Zuweisung: Verzeichnisrollen → alle Admin-Rollen auswählen',
                    'Cloud-Apps: Alle Cloud-Apps',
                    'Zugriffssteuerung → Gewähren → MFA erforderlich',
                    'Privileged Identity Management (PIM) aktivieren für Just-in-Time-Zugriff',
                    'Richtlinie sofort aktivieren (kein Report-only für Admin-MFA)',
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
                'title'         => 'Conditional Access oder Security Defaults aktivieren',
                'risk'          => 'Ohne Conditional Access oder Security Defaults fehlt der grundlegende Schutz vor unbefugtem Zugriff auf alle Cloud-Apps des Tenants.',
                'steps'         => [
                    'Option A – Security Defaults (einfachste Lösung für kleine Tenants):',
                    '  Entra ID → Properties → Manage Security Defaults → Enable → Speichern',
                    'Option B – Eigene CA-Richtlinien (empfohlen):',
                    '  Richtlinie 1: MFA für alle Benutzer (alle Cloud-Apps)',
                    '  Richtlinie 2: Legacy-Auth blockieren',
                    '  Richtlinie 3: Konformes Gerät für Admin-Rollen erforderlich',
                    'Report-only-Modus nutzen, um Auswirkungen zu bewerten, bevor aktiviert wird',
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
                'title'         => 'Self-Service Password Reset (SSPR) aktivieren',
                'risk'          => 'Ohne SSPR müssen Benutzer den Helpdesk für Passwortzurücksetzungen kontaktieren. Dies erhöht Kosten und veranlasst Benutzer manchmal zur unsicheren Passwortweitergabe.',
                'steps'         => [
                    'Entra ID → Users → Password reset → Properties öffnen',
                    'Self-service password reset enabled → „All" auswählen',
                    'Authentication methods: mindestens 2 Methoden aktivieren (z.B. App + E-Mail)',
                    'Registration: „Require users to register when signing in" aktivieren',
                    'Notifications konfigurieren für Admin-Benachrichtigung bei Reset',
                    'Speichern und Benutzer per Intranet informieren',
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
                'title'         => 'Privileged Identity Management (PIM) einrichten',
                'risk'          => 'Permanente Admin-Zuweisungen stellen ein dauerhaftes Angriffsziel dar. Mit PIM erhalten Admins nur bei Bedarf zeitlich begrenzte Berechtigungen.',
                'steps'         => [
                    'Entra ID → Identity Governance → Privileged Identity Management öffnen',
                    'Für Global Administrator: Alle permanenten Zuweisungen in „Eligible" umwandeln',
                    'Aktivierungseinstellungen: max. 8 Stunden, Begründung erforderlich, MFA erforderlich',
                    'Genehmigungsworkflow für Global Admin aktivieren (zweite Person muss genehmigen)',
                    'Alerts einrichten: „Admins aren\'t using their privileged roles"',
                    'Regelmäßige Access Reviews für PIM-Rollen konfigurieren',
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
                'title'         => 'Gastbenutzer-Berechtigungen einschränken',
                'risk'          => 'Standardmäßig können Gäste das Verzeichnis durchsuchen und andere Benutzer, Gruppen und Apps einsehen. Dies kann zu ungewollter Datenweitergabe führen.',
                'steps'         => [
                    'Entra ID → External Identities → External collaboration settings öffnen',
                    'Guest user access: „Guest users have limited access to properties and memberships of directory objects" wählen',
                    'Guest invite settings: auf „Only users assigned to specific admin roles can invite" setzen',
                    'Collaboration restrictions: Domains einschränken auf bekannte Partner',
                    'Entra ID → Users → User settings → External user restrictions prüfen',
                    'Regelmäßige Access Reviews für Gastbenutzer aktivieren',
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
                'title'         => 'Externe SharePoint/OneDrive-Freigaben einschränken',
                'risk'          => 'Unreingeschränkte externe Freigaben können dazu führen, dass sensible Unternehmensdaten unkontrolliert nach außen gelangen.',
                'steps'         => [
                    'Microsoft 365 Admin Center → SharePoint Admin Center öffnen',
                    'Policies → Sharing → External sharing für SharePoint und OneDrive',
                    'Empfehlung: „Existing guests" oder „Only people in your organization"',
                    'Link expiration für „Anyone"-Links auf max. 7–14 Tage setzen',
                    'Link-Typ default auf „Specific people" ändern',
                    'Freigabe-Monitor im Tool regelmäßig prüfen (/sharing/monitor)',
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
                'title'         => 'Notfallzugangskonten (Break-Glass) einrichten',
                'risk'          => 'Ohne Break-Glass-Konten kann bei einem MFA-Ausfall oder CA-Fehlkonfiguration kein Admin mehr auf den Tenant zugreifen — vollständiger Kontrollverlust.',
                'steps'         => [
                    'Entra ID → Users → New user: Zwei cloud-only Global Admin Konten anlegen',
                    'Format: breakglass1@<tenant>.onmicrosoft.com (KEINE eigene Domain)',
                    'Kein Benutzer-Postfach zuweisen, keine Lizenz, MFA-Nummer: Büro-Festnetz',
                    'Von ALLEN Conditional-Access-Richtlinien ausschließen',
                    'Starke Passwörter (32+ Zeichen) in einem physischen Tresor und einem Passwortmanager hinterlegen',
                    'Monitoring-Alert einrichten: Sofortbenachrichtigung bei jeder Anmeldung dieser Konten',
                    'Quartalsweise Zugang testen (nur lesen, keine Änderungen)',
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
                'title'         => 'Microsoft Defender for Office 365 konfigurieren',
                'risk'          => 'Ohne Defender-Schutz sind E-Mails, Teams-Nachrichten und Dateianhänge nicht gegen Zero-Day-Malware, Phishing und bösartige Links geschützt.',
                'steps'         => [
                    'Microsoft 365 Defender öffnen: security.microsoft.com',
                    'Email & collaboration → Policies & rules → Threat policies',
                    'Preset security policies: „Standard protection" oder „Strict protection" aktivieren',
                    'Safe Attachments: für alle Empfänger aktivieren, Aktion „Block"',
                    'Safe Links: für E-Mail und Teams aktivieren, „Do not allow users to click through" aktivieren',
                    'Anti-phishing: Impersonationsschutz für Domains und wichtige Benutzer konfigurieren',
                    'DMARC/DKIM/SPF für alle Domains prüfen (/domainhealth)',
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
                'title'         => 'Unified Audit Log aktivieren',
                'risk'          => 'Ohne Audit-Log können Sicherheitsvorfälle nicht untersucht werden. Bei einem Breach fehlt die forensische Grundlage — und in manchen Regulierungen ist die Protokollierung Pflicht.',
                'steps'         => [
                    'Microsoft Purview Compliance Portal öffnen: compliance.microsoft.com',
                    'Audit → Start recording user and admin activity (Schaltfläche klicken)',
                    'Bestätigen: „Turn on auditing" → Aktivierung kann bis zu 60 Minuten dauern',
                    'Audit-Aufbewahrung prüfen: Standard 90 Tage; für E5/Compliance-Lizenz auf 1 Jahr erweitern',
                    'Kritische Operationen im Audit suchen: Admin-Aktivitäten, Mailbox-Zugriffe, Dateilöschungen',
                    'Alert-Richtlinien für kritische Ereignisse einrichten (Insider Risk)',
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
                'title'         => 'Gerätekonformität über Intune erzwingen',
                'risk'          => 'Nicht verwaltete oder nicht konforme Geräte können Schadsoftware einschleusen und auf Unternehmensressourcen zugreifen, ohne dass Sicherheitsstandards eingehalten werden.',
                'steps'         => [
                    'Microsoft Intune Admin Center öffnen: intune.microsoft.com',
                    'Devices → Compliance policies → Create policy',
                    'Windows: BitLocker aktiviert, Mindest-OS-Version, Firewall an, AV-Schutz aktiv',
                    'iOS/macOS: Mindest-OS, Passcode erforderlich, Jailbreak-Erkennung',
                    'Android: Mindest-OS, Encryption, SafetyNet-Attestation',
                    'Conditional Access: Richtlinie „Compliant device required" für alle Apps aktivieren',
                    'Nicht konforme Geräte im Tool prüfen (/devices)',
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
                'title'         => 'MDM-Auto-Enrollment in Intune konfigurieren',
                'risk'          => 'Ohne automatische Geräteregistrierung werden Endgeräte nicht zentral verwaltet. Sicherheitsrichtlinien, Updates und App-Schutz können nicht durchgesetzt werden.',
                'steps'         => [
                    'Intune Admin Center → Devices → Enrollment → Windows enrollment',
                    'Automatic Enrollment: MDM-Benutzerbereich auf „All" (oder ausgewählte Gruppe) setzen',
                    'Windows Autopilot für neue Geräte konfigurieren (Zero-Touch-Deployment)',
                    'iOS/iPadOS: Apple Business Manager (ABM) mit Intune verbinden → DEP-Profil erstellen',
                    'Android: Android Enterprise Enrollment → Work Profile aktivieren',
                    'App Protection Policies (MAM) für BYOD-Geräte ohne vollständige Enrollierung einrichten',
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
                'title'         => 'Microsoft Secure Score-Maßnahmen umsetzen',
                'risk'          => 'Ein niedriger Secure Score zeigt konkrete Sicherheitslücken an. Microsoft priorisiert die Maßnahmen nach Aufwand und Punktegewinn.',
                'steps'         => [
                    'Microsoft 365 Defender öffnen → Secure Score: security.microsoft.com/securescore',
                    'Tab „Improvement actions" öffnen, nach „Points achieved" sortieren',
                    'Filter: Status „To address" → einfachste Maßnahmen zuerst umsetzen',
                    'Typisch hohe Punkte: MFA, Legacy-Auth, Admin-MFA, SSPR',
                    'Für jede Maßnahme: „Manage" klicken → direkt zur Konfiguration springen',
                    'Ziel: Score über 50 % als ersten Meilenstein, dann über 70 %',
                    'Im Tool unter /securescore den aktuellen Score verfolgen',
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
                'title'         => 'Passwort-Ablaufrichtlinie überprüfen und optimieren',
                'risk'          => 'Zu häufige Passwort-Ablauffristen führen dazu, dass Benutzer schwache, vorhersehbare Passwörter wählen (z.B. „Sommer24"). Die beste Lösung ist Passwordless.',
                'steps'         => [
                    'Entra ID → Users → Password expiration policy prüfen',
                    'Empfehlung 1 (Best Practice): Password never expires aktivieren + MFA/Passwordless erzwingen',
                    'Empfehlung 2 (Kompromiss): 90-Tage-Ablauf behalten + SSPR + Microsoft Entra Password Protection',
                    'Entra Password Protection aktivieren: bannt schwache/geleakte Passwörter',
                    'Für maximale Sicherheit: Passwordless (FIDO2-Schlüssel, Windows Hello) einführen',
                    'Passwort-Ablauf-Bericht im Tool unter /passwordexpiry prüfen',
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
                'title'         => 'Standard-Alertrichtlinien für verdächtige Aktivitäten aktivieren',
                'risk'          => 'Ohne Alert-Richtlinien werden Angriffe wie Brute-Force, Massendownloads oder Privilege-Escalation nicht rechtzeitig erkannt.',
                'steps'         => [
                    'Microsoft 365 Defender → Alerts → Alert policies öffnen',
                    'Alle Standard-Alertrichtlinien prüfen: status sollte „On" sein',
                    'Wichtigste Alerts aktivieren: „Suspicious email sending patterns", „Unusual volume of file deletion", „Elevation of Exchange admin privilege"',
                    'Microsoft Purview → Insider Risk Management: neue Richtlinie für Datenverlust',
                    'Alert-Empfänger konfigurieren: IT-Team E-Mail-Adresse hinterlegen',
                    'Wöchentlichen E-Mail-Report im Tool aktivieren (Einstellungen → Wöchentlicher Report)',
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
                'title'         => 'MFA-Registrierungsquote kritisch niedrig (' . $mfaPct . ' %)',
                'risk'          => 'Nur ' . $mfaPct . ' % der Benutzer haben MFA registriert. Jedes nicht durch MFA geschützte Konto ist ein potenzieller Eintrittspunkt für Angreifer.',
                'steps'         => [
                    'Entra ID → Security → Conditional Access → Neue Richtlinie',
                    'MFA-Registrierungskampagne starten: Benutzer per E-Mail informieren (aka.ms/mfasetup)',
                    'CA-Richtlinie: MFA-Registrierung für alle Benutzer mit Location-Condition erzwingen',
                    'Report-only-Modus für 7 Tage → dann aktivieren',
                    'Helpdesk auf erhöhtes Aufkommen vorbereiten',
                    'Fortschritt täglich in /mfamethods prüfen',
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
                'title'         => $staleCount . ' inaktive Konten (>90 Tage) identifiziert',
                'risk'          => 'Inaktive Konten mit aktiven Lizenzen und Zugriffsrechten sind unnötige Angriffsfläche. Kompromittierte veraltete Konten bleiben oft unbemerkt.',
                'steps'         => [
                    'Im Tool unter /staleaccounts die Liste überprüfen',
                    'Für jeden inaktiven Benutzer prüfen: ausgeschieden, langzeit-krank oder vergessen?',
                    'Ausgeschiedene Mitarbeiter sofort deaktivieren und Lizenzen entziehen',
                    'Benutzer deaktivieren (nicht löschen): Entra ID → User → Block sign-in',
                    'Lizenzen entziehen: im Tool direkt möglich unter /staleaccounts',
                    'Nach 30-tägiger Aufbewahrungsfrist: Konto löschen (Postfach-Inhalte vorher sichern)',
                    'Automatische Lizenzfreigabe in Einstellungen → Inaktive Konten konfigurieren',
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
                'title'         => $anonCount . ' aktive anonyme Freigabe(n) (Anyone-Links) gefunden',
                'risk'          => 'Anyone-Links können von beliebigen Personen ohne Authentifizierung aufgerufen werden — auch nach dem Verlassen des Unternehmens durch die ursprüngliche Person.',
                'steps'         => [
                    'Im Tool unter /sharing die anonymen Freigaben prüfen und widerrufen',
                    'SharePoint Admin Center → Policies → Sharing → „Anyone"-Links global deaktivieren',
                    'Alternativ: Ablaufdatum für Anyone-Links auf max. 7 Tage begrenzen',
                    'Bestehende Links: SharePoint Admin Center → Active sites → Sharing-Berichte',
                    'Freigabe-Monitor aktivieren, um künftig anonyme Links automatisch zu erkennen',
                    'Link-Standard auf „Specific people" ändern (SharePoint Admin → Sharing)',
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
                'title'         => $noLicenseCount . ' aktivierte Konten ohne Lizenz gefunden',
                'risk'          => 'Aktivierte Konten ohne Lizenz können sich weiterhin per OAuth oder Legacy-Auth authentifizieren und stellen unnötige Angriffsfläche dar.',
                'steps'         => [
                    'Im Tool unter /users nach Benutzern ohne Lizenzen filtern',
                    'Für jeden Benutzer prüfen: Shared-Mailbox-Konto (OK), Dienstkonto (prüfen) oder veraltetes Konto (deaktivieren)',
                    'Nicht benötigte Konten sofort deaktivieren: Entra ID → User → Block sign-in',
                    'Dienstkonten dokumentieren und MFA-Ausnahme-Prozess prüfen',
                    'Microsoft 365 Admin → Licenses → Nicht zugewiesene Lizenzen prüfen',
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
                'title'         => $highUtilSkus . ' Lizenz-SKU(s) mit über 90 % Auslastung',
                'risk'          => 'SKUs mit über 90 % Auslastung können bei Neueinstellungen oder Umstrukturierungen zu sofortigen Engpässen führen und den Onboarding-Prozess blockieren.',
                'steps'         => [
                    'Im Tool unter /licenseadvisor die hoch ausgelasteten SKUs identifizieren',
                    'Microsoft 365 Admin Center → Billing → Licenses: aktuelle Bestände prüfen',
                    'Zusätzliche Lizenzen rechtzeitig bestellen (mindestens 2 Wochen Vorlauf)',
                    'Nicht zugewiesene Lizenzen von inaktiven Konten freigeben (/staleaccounts)',
                    'Lizenz-Advisor nutzen, um zu prüfen ob günstigere Bundles sinnvoll sind',
                    'Automatischen Alert konfigurieren: Einstellungen → Alert-Schwellwerte → Lizenzauslastung',
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
                'title'         => $nonCompliant . ' nicht konforme Geräte in Intune',
                'risk'          => 'Nicht konforme Geräte erfüllen die Sicherheitsanforderungen nicht (z.B. fehlendes BitLocker, veraltetes OS, kein Virenschutz) und sollten blockiert werden.',
                'steps'         => [
                    'Intune Admin Center → Devices → Monitor → Noncompliant devices öffnen',
                    'Für jedes nicht konforme Gerät den Grund prüfen (Compliance details)',
                    'Benutzer benachrichtigen: Automatische Benachrichtigung in Compliance Policy konfigurieren',
                    'Zeitlich begrenzte Übergangsfrist (Grace Period) auf max. 7 Tage setzen',
                    'Nach Ablauf: CA-Richtlinie blockiert Zugriff automatisch',
                    'Im Tool unter /devices alle nicht konformen Geräte und Besitzer einsehen',
                ],
                'internal_path' => '/devices',
                'ms_admin_url'  => 'https://intune.microsoft.com/#view/Microsoft_Intune_DeviceSettings/DevicesMenu/~/compliance',
                'ms_doc_url'    => 'https://learn.microsoft.com/de-de/mem/intune/protect/device-compliance-get-started',
                'bsi_controls'  => ['SYS.2.1.A3', 'SYS.2.1.A36', 'SYS.2.1.A38'],
                'nis2_articles' => ['Art. 21 Abs. 2(e)', 'Art. 21 Abs. 2(i)'],
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
