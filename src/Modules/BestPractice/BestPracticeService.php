<?php

namespace App\Modules\BestPractice;

use App\Core\Config;
use App\Database\DB;

/**
 * Best-Practice-Leitfaden für die schrittweise Härtung eines Microsoft-365-
 * Tenants. Das ist kein "magisches Profil" — sondern ein kuratiertes
 * Vorgehen: jede Phase hat einzelne, abhakbare Schritte mit Erklärung,
 * Zeitabschätzung und direkter Verlinkung in das zuständige Modul.
 *
 * Fortschritt wird als JSON in app_config gespeichert (geteilt zwischen
 * allen Admins, weil typischerweise ein Team gemeinsam abarbeitet).
 * Einzelne Schritte können sich automatisch als erledigt erkennen
 * (z. B. „Compliance-Profil angewendet" prüft `compliance_profile`-Config).
 */
class BestPracticeService
{
    private const PROGRESS_KEY = 'bestpractice_progress';

    /**
     * Vollständige Struktur des Leitfadens.
     * @return list<array<string,mixed>>
     */
    public static function guide(): array
    {
        $cfg = Config::getInstance();

        $autoSetup     = fn() => (string)$cfg->get('setup_wizard_completed', '0') === '1';
        $autoProfile   = fn() => trim((string)$cfg->get('compliance_profile', '')) !== '';
        $autoBackup    = fn() => trim((string)$cfg->get('backup_provider', '')) !== '';
        $autoLockbox   = fn() => (string)$cfg->get('customer_lockbox_aware', '0') === '1';
        $autoRecipients= fn() => trim((string)$cfg->get('notification_recipients', '')) !== ''
                                || trim((string)$cfg->get('alert_email_to', '')) !== '';

        return [
            // ── PHASE 1 ────────────────────────────────────────────────
            [
                'id' => 'phase1', 'title' => 'Phase 1 — Fundament', 'subtitle' => 'Einmalig · ca. 10 Minuten',
                'icon' => 'building', 'color' => '#0078d4',
                'intro' => 'Die Grundlagen, ohne die alles andere nichts hilft: stabile Tenant-Verbindung, korrekte Berechtigungen, dein eigenes Admin-Konto abgesichert.',
                'steps' => [
                    [
                        'id' => 'p1-setup', 'title' => 'Einrichtungs-Assistent durchlaufen', 'time' => 5,
                        'why' => 'Prüft, dass Tenant-ID/Client-Secret korrekt sind, ein Token bezogen werden kann, die Permissions reichen — und fragt Benachrichtigungs-Empfänger und Branding ab.',
                        'how' => [
                            'Öffne den Assistenten und klicke alle fünf Schritte durch.',
                            'Bei „Empfänger" mindestens eine E-Mail eintragen (Security-Postfach), damit dich später Alerts erreichen.',
                            'Im letzten Schritt das passende Compliance-Profil auswählen — Details dazu in Phase 2.',
                        ],
                        'link' => '/setup', 'link_label' => 'Zum Assistenten',
                        'auto_done' => $autoSetup,
                    ],
                    [
                        'id' => 'p1-perms', 'title' => 'Graph-API-Berechtigungen vollständig', 'time' => 5,
                        'why' => 'Ohne die richtigen App-Permissions sind viele Module blind. Fehlende Berechtigungen müssen mit Admin-Consent in Entra erteilt werden.',
                        'how' => [
                            'Auf /settings/permissions sollten alle Zeilen grün sein.',
                            'Fehlende Permissions: in der Entra-App-Registrierung „API permissions" → „Add a permission" → „Microsoft Graph" → „Application permissions".',
                            'Anschließend „Grant admin consent for …" klicken (das ist die zweite Schaltfläche, häufig übersehen).',
                            'Im Tool dann „Cache leeren" und Permissions-Seite neu laden.',
                        ],
                        'link' => '/settings/permissions', 'link_label' => 'Berechtigungs-Audit öffnen',
                    ],
                    [
                        'id' => 'p1-2fa', 'title' => 'Eigene 2FA aktivieren', 'time' => 2,
                        'why' => 'Das Tool selbst hat einen Admin-Login. Ohne 2FA ist es ein einzelnes Passwort weg vom kompletten Tenant. TOTP-RFC-6238, kompatibel mit Microsoft/Google Authenticator, Aegis usw.',
                        'how' => [
                            'QR-Code mit der Authenticator-App scannen.',
                            'Zur Verifikation den 6-stelligen Code eingeben.',
                            'Wiederherstellungs-Codes ausdrucken oder im Passwort-Manager ablegen — wirklich, nicht überspringen.',
                        ],
                        'link' => '/settings/2fa', 'link_label' => '2FA einrichten',
                    ],
                    [
                        'id' => 'p1-users', 'title' => 'Tool-Benutzer aufräumen', 'time' => 3,
                        'why' => 'Shared-Accounts (z. B. „admin@firma.de" mit drei Personen) gehen gegen jede Compliance. Sauberer: jeder Admin/Operator hat eigenen Zugang im Tool — verknüpft mit dem Entra-Konto.',
                        'how' => [
                            'Pro Person einen Eintrag anlegen, Rolle „Admin" oder „Operator".',
                            'Generische/geteilte Logins entfernen.',
                            'Operator-Rolle für Helpdesk: kann scannen, Lizenzen umschichten, kein Zugriff auf Einstellungen.',
                        ],
                        'link' => '/settings/users', 'link_label' => 'Benutzer-Zugang',
                    ],
                ],
            ],

            // ── PHASE 2 ────────────────────────────────────────────────
            [
                'id' => 'phase2', 'title' => 'Phase 2 — One-Click-Härtung', 'subtitle' => 'Einmalig · ca. 5 Minuten',
                'icon' => 'magic', 'color' => '#7c3aed',
                'intro' => 'Die größten Hebel mit wenigen Klicks. Profile bündeln branchen-typische Härtung; das Hardening-Modul lässt dich einzelne Items feintunen.',
                'steps' => [
                    [
                        'id' => 'p2-profile', 'title' => 'Compliance-Profil anwenden', 'time' => 3,
                        'why' => 'Setzt mit einem Klick 6–13 Hardening-Defaults entlang einer bekannten Regulierung (DSGVO, KRITIS, BaFin/DORA, BSI).',
                        'how' => [
                            'Standard / DSGVO-Basis — für jeden Tenant ein guter Start.',
                            'Gesundheitswesen — wenn Patienten-/Klientendaten verarbeitet werden.',
                            'Finanzwesen — Banken, Versicherungen, Asset Manager.',
                            'Öffentlicher Sektor — Behörden, Stadtwerke, Hochschulen.',
                            'Bildung — Schulen, Kitas (Schutz von Kinder-Daten gem. DSGVO Art. 8).',
                        ],
                        'link' => '/complianceprofile', 'link_label' => 'Profile öffnen',
                        'auto_done' => $autoProfile,
                    ],
                    [
                        'id' => 'p2-hardening', 'title' => 'Restliche Hardening-Items abarbeiten', 'time' => 5,
                        'why' => 'Das Profil deckt 60–80 % ab. Der Rest sind Items, die nicht in jedes Branchen-Muster passen — z. B. App-Anlage durch Endbenutzer blockieren oder Idle-Session-Signout.',
                        'how' => [
                            'Alles auf „unkritisch" / grün bringen, was Graph direkt schalten kann.',
                            'Wo „Admin-Center öffnen" steht: Deep-Link folgen, Wert prüfen, ggf. anpassen.',
                            'Items mit Status „unbekannt" → meist nur fehlende Permission. Zurück zu Phase 1, Schritt 2.',
                        ],
                        'link' => '/hardening', 'link_label' => 'Hardening-Modul',
                    ],
                ],
            ],

            // ── PHASE 3 ────────────────────────────────────────────────
            [
                'id' => 'phase3', 'title' => 'Phase 3 — Identity & Zugriff', 'subtitle' => 'Einmalig · ca. 15 Minuten',
                'icon' => 'shield-lock', 'color' => '#dc2626',
                'intro' => '80 % der echten Angriffe gehen über kompromittierte Identitäten. Dieser Block bringt den größten Sicherheitsgewinn pro Minute.',
                'steps' => [
                    [
                        'id' => 'p3-breakglass', 'title' => 'Break-Glass-Accounts einrichten', 'time' => 5,
                        'why' => 'Falls dein MFA-Dienst ausfällt oder dein Conditional-Access dich aussperrt, brauchst du ein Notfall-Konto. Pflicht laut BSI und allen Audit-Standards.',
                        'how' => [
                            'Zwei dedizierte Konten anlegen (z. B. breakglass1@…, breakglass2@…).',
                            'Global Admin permanent zuweisen — diese Accounts sind nicht für PIM.',
                            'FIDO2-Hardware-Key (z. B. YubiKey) registrieren, in einen Safe legen.',
                            'Conditional-Access-Policies müssen diese UPNs **ausnehmen**.',
                            'Test-Login alle 90 Tage dokumentieren.',
                        ],
                        'link' => '/breakglass', 'link_label' => 'Break-Glass-Modul',
                    ],
                    [
                        'id' => 'p3-ca', 'title' => 'Conditional Access mindestens auf Baseline', 'time' => 10,
                        'why' => 'Conditional Access ist das stärkste Identity-Werkzeug in M365. Wer das nicht hat, ist auf Security-Defaults angewiesen — bei mittlerer Komplexität nicht mehr ausreichend.',
                        'how' => [
                            'Mindest-Set: „MFA für alle Benutzer", „Block Legacy Auth", „MFA für Admin-Rollen" (separate strenge Policy).',
                            'Empfehlung: „Compliant device required" für sensible Apps (SharePoint, Exchange).',
                            'Test-Mode („Report-only") zuerst — eine Woche beobachten, dann aktivieren.',
                            'Break-Glass-Accounts **explizit ausnehmen** (siehe oben).',
                        ],
                        'link' => '/conditionalaccess', 'link_label' => 'CA-Policies',
                    ],
                    [
                        'id' => 'p3-locations', 'title' => 'Named Locations definieren', 'time' => 5,
                        'why' => 'Mit benannten Standorten kannst du CA-Policies geo-fence-artig einschränken („nur aus DE/AT/CH") oder die Office-IPs als „trusted" markieren.',
                        'how' => [
                            'Eigene öffentliche Office-IPs anlegen (statische Ausgangs-IPs).',
                            'Länder-Whitelist mit den tatsächlich genutzten Ländern.',
                            'Reise-Whitelist temporär ergänzen, wenn Mitarbeiter ins Ausland fahren.',
                        ],
                        'link' => '/namedlocations', 'link_label' => 'Named Locations',
                    ],
                    [
                        'id' => 'p3-authstrength', 'title' => 'Authentication-Strength: phishing-resistent', 'time' => 5,
                        'why' => 'SMS-MFA ist veraltet, klassisches App-Push ist anfällig für MFA-Fatigue. Standard sollte FIDO2 / Authenticator mit Number-Matching sein.',
                        'how' => [
                            'Im Authentication-Strength-Modul Tenant-Policy auf „phishing-resistant" prüfen.',
                            'Im Hardening-Modul Number-Matching aktivieren falls noch nicht erfolgt.',
                            'Für Admin-Rollen: CA-Policy mit „phishing-resistant MFA" als Bedingung.',
                        ],
                        'link' => '/authstrength', 'link_label' => 'Auth-Strength',
                    ],
                    [
                        'id' => 'p3-pim', 'title' => 'PIM: Permanente Admin-Rollen eliminieren', 'time' => 10,
                        'why' => 'Wer 24/7 Global Admin ist, ist 24/7 ein lohnendes Ziel. PIM macht Admin-Rechte Just-in-Time aktivierbar — alle Aktivierungen sind auditierbar.',
                        'how' => [
                            'Liste der permanenten Admin-Zuweisungen anschauen.',
                            'Auf „Eligible" (PIM-fähig) umstellen, statt „Active".',
                            'Aktivierungs-Zeitraum auf 4–8 Stunden begrenzen.',
                            'Genehmigungs-Workflow optional bei Global Admin / Privileged Role Admin.',
                        ],
                        'link' => '/pim', 'link_label' => 'PIM-Übersicht',
                    ],
                    [
                        'id' => 'p3-token', 'title' => 'Token-Lifetime / Sign-in-Frequency', 'time' => 3,
                        'why' => 'Standard ist „Token bleibt 90 Tage gültig". Bei einem kompromittierten Refresh-Token kann sich der Angreifer Wochen lang halten, auch nach Passwort-Reset.',
                        'how' => [
                            'CA-Policy mit „Sign-in frequency" anlegen.',
                            'Admin-Rollen: 4–8 Stunden.',
                            'Normale Benutzer: 12–24 Stunden.',
                            'Sehr sensible Anwendungen (Finanzen): jeweils ganz neu authentifizieren.',
                        ],
                        'link' => '/tokenlifetime', 'link_label' => 'Token-Lifetime',
                    ],
                    [
                        'id' => 'p3-adminroles', 'title' => 'Admin-Rollen ausmisten', 'time' => 5,
                        'why' => 'Erfahrungsgemäß haben 2–3× so viele Personen Admin-Rollen wie nötig — angesammelt über Jahre. Audit: ist diese Person noch in der Position?',
                        'how' => [
                            'Pro Rolle: ist sie wirklich überall nötig? Kann eine weniger privilegierte Rolle reichen?',
                            'Global Admin: maximal 2–5 Personen (BSI-Empfehlung).',
                            'Bei Unsicherheit: Rolle entziehen → 2 Wochen warten → meldet sich niemand → bleibt entzogen.',
                        ],
                        'link' => '/adminroles', 'link_label' => 'Admin-Rollen',
                    ],
                ],
            ],

            // ── PHASE 4 ────────────────────────────────────────────────
            [
                'id' => 'phase4', 'title' => 'Phase 4 — Daten & E-Mail', 'subtitle' => 'Einmalig · ca. 15 Minuten',
                'icon' => 'envelope-paper', 'color' => '#059669',
                'intro' => 'Hier geht es darum, dass Daten nicht ungewollt rausfließen — und dass eingehende Mails nicht so leicht zu fälschen sind.',
                'steps' => [
                    [
                        'id' => 'p4-sharing', 'title' => 'SharePoint/OneDrive-Sharing einschränken', 'time' => 3,
                        'why' => 'Standard ist „Anyone-Links erlaubt, beliebige externe Mails einladbar". DSGVO-konform ist eher: nur bestehende Gäste dürfen Inhalte erhalten, Anon-Links laufen automatisch ab.',
                        'how' => [
                            '„existingExternalUserSharingOnly" als Capability.',
                            'Anonym-Link-Ablauf: 30 Tage (oder weniger).',
                            'Re-Sharing durch externe Benutzer deaktivieren.',
                            'Idle-Session-Signout aktivieren (4h Idle → automatisch ausloggen).',
                        ],
                        'link' => '/sharing/policies', 'link_label' => 'Sharing-Richtlinien',
                    ],
                    [
                        'id' => 'p4-domainhealth', 'title' => 'SPF / DKIM / DMARC für alle Domains', 'time' => 10,
                        'why' => 'Ohne DMARC kann jeder im Internet deine Domain als Absender fälschen. Mit SPF + DKIM + DMARC mit „reject"-Policy ist Spoofing technisch ausgeschlossen.',
                        'how' => [
                            'Pro Domain alle drei DNS-Records grün haben.',
                            'SPF: alle berechtigten Sender (EXO + Marketing-Tools wie Mailchimp + ERP-System) eintragen.',
                            'DKIM: in Defender / Exchange Admin Center aktivieren.',
                            'DMARC: erst „p=none" → Reports prüfen → dann „p=quarantine" → später „p=reject".',
                        ],
                        'link' => '/domainhealth', 'link_label' => 'Domain Health',
                    ],
                    [
                        'id' => 'p4-mailflow', 'title' => 'Defender for Office: Safe Links / Safe Attachments', 'time' => 5,
                        'why' => 'Klassische Phishing-Mail-Filter erkennen 80 %. Safe-Links + Safe-Attachments fangen die restlichen 20 % ab — durch Sandbox-Detonation und Time-of-Click-Re-Check.',
                        'how' => [
                            'In Defender for Office: Standard-Preset-Policies aktivieren.',
                            'External-Sender-Identifier („Externe Mail" Tag) einschalten.',
                            'Verdächtige Domains, die häufig Phishing senden, in den Block-Listen pflegen.',
                        ],
                        'link' => '/mailflow', 'link_label' => 'Mail Flow & Schutz',
                    ],
                    [
                        'id' => 'p4-labels', 'title' => 'Sensitivity Labels einrichten', 'time' => 5,
                        'why' => 'Labels ermöglichen Verschlüsselung, Wasserzeichen und Rechte-Verwaltung „on save". Auch wenn ein Dokument den Tenant verlässt, bleibt es geschützt.',
                        'how' => [
                            'Mindest-Set: Öffentlich · Intern · Vertraulich · Streng vertraulich.',
                            '„Vertraulich" und „Streng vertraulich" automatisch verschlüsseln.',
                            'Auto-Apply-Regeln (z. B. Kreditkartennummer → automatisch „Vertraulich").',
                        ],
                        'link' => '/sensitivitylabels', 'link_label' => 'Sensitivity Labels',
                    ],
                    [
                        'id' => 'p4-dlp', 'title' => 'DLP-Policy für die kritischste Datenkategorie', 'time' => 5,
                        'why' => 'Eine einzige sinnvoll konfigurierte DLP-Policy ist besser als zehn überregulierende — Mitarbeiter umgehen sonst alles.',
                        'how' => [
                            'Branchen-typische erste Policy: Healthcare → Gesundheitsdaten, Finance → IBAN/Kreditkarte, B2B → Geschäftsgeheimnisse.',
                            'Erst „Tip" + Audit, dann nach 2 Wochen „Block" für externe Empfänger.',
                            'False-Positives sammeln und Policy iterieren.',
                        ],
                        'link' => '/dlppolicies', 'link_label' => 'DLP-Richtlinien',
                    ],
                    [
                        'id' => 'p4-retention', 'title' => 'Aufbewahrungs-Policy nach DSGVO/GoBD', 'time' => 5,
                        'why' => 'Du **musst** löschen (DSGVO: nur so lang wie nötig) — und **musst** aufbewahren (GoBD: Geschäftsbriefe 6 Jahre, Buchhaltung 10 Jahre). Beide Anforderungen in eine Policy gegossen.',
                        'how' => [
                            'Mail-Postfächer: 10 Jahre für Buchhaltung / Geschäftsführung, 6 Jahre Standard.',
                            'Teams-Chats: 1–2 Jahre.',
                            'OneDrive: 30 Tage nach Account-Löschung dann hard delete.',
                            'Rechtliche Klärung mit Datenschutz/Steuerberater vor Aktivierung.',
                        ],
                        'link' => '/retentionpolicies', 'link_label' => 'Aufbewahrung',
                    ],
                    [
                        'id' => 'p4-lockbox', 'title' => 'Customer Lockbox aktivieren', 'time' => 3,
                        'why' => 'Standardmäßig kann ein Microsoft-Support-Mitarbeiter auf deine Tenant-Daten zugreifen, wenn ein Ticket es nahelegt. Mit Customer Lockbox musst du jeden Zugriff explizit freigeben.',
                        'how' => [
                            'Erfordert M365 E5 oder Customer-Lockbox-Add-On.',
                            'Freigabe-Workflow im Tool dokumentieren (wer entscheidet, wie schnell).',
                            'DSGVO-Auftragsverarbeitungsvertrag mit Microsoft entsprechend ergänzen.',
                        ],
                        'link' => '/customerlockbox', 'link_label' => 'Customer Lockbox',
                        'auto_done' => $autoLockbox,
                    ],
                ],
            ],

            // ── PHASE 5 ────────────────────────────────────────────────
            [
                'id' => 'phase5', 'title' => 'Phase 5 — Gäste, Apps & externe Identitäten', 'subtitle' => 'Einmalig · ca. 10 Minuten',
                'icon' => 'people', 'color' => '#ea580c',
                'intro' => 'Gäste und OAuth-Apps sind oft Jahre alt und wurden nie überprüft. Hier räumst du auf, was sich „eingenistet" hat.',
                'steps' => [
                    [
                        'id' => 'p5-guests', 'title' => 'Alte Gast-Benutzer entfernen', 'time' => 5,
                        'why' => 'Jeder Gast ist ein potentieller Angriffsvektor — Konten, die zu einer fremden Organisation gehören, die du nicht kontrollierst. Ohne Sign-In seit 90+ Tagen: meist nicht mehr nötig.',
                        'how' => [
                            'Filter: „Last sign-in > 90 days" oder „nie".',
                            'Bevor du entfernst: Owner der Sharing-Beziehung kurz informieren.',
                            'Bulk-deaktivieren statt sofort löschen — 30 Tage Beobachtungs-Phase.',
                        ],
                        'link' => '/guestusers', 'link_label' => 'Gast-Benutzer',
                    ],
                    [
                        'id' => 'p5-review', 'title' => 'Access Review für Gäste anlegen', 'time' => 5,
                        'why' => 'NIS-2 und DSGVO verlangen periodische Berechtigungs-Überprüfung. Mit einem Access Review fragst du automatisiert alle Owner: „Brauchen die diesen Zugriff noch?"',
                        'how' => [
                            'Quartals-Rhythmus für Gäste.',
                            'Halbjährlich für interne sensible Gruppen (Geschäftsführung, IT-Admins).',
                            'Auto-revoke bei „no response" aktivieren.',
                        ],
                        'link' => '/accessreview', 'link_label' => 'Access Reviews',
                    ],
                    [
                        'id' => 'p5-crosstenant', 'title' => 'Cross-Tenant Access regeln', 'time' => 3,
                        'why' => 'Standard ist „mit jedem M365-Tenant darf B2B-Collaboration laufen". Wenn du nur mit zwei Partnern arbeitest, ist eine Allow-List sicherer als die offene Welt.',
                        'how' => [
                            'Partner-Tenants explizit erlauben.',
                            'Default-Policy auf „inbound block" stellen.',
                            'Teams Shared Channels nur mit ausgewählten Tenants.',
                        ],
                        'link' => '/crosstenantaccess', 'link_label' => 'Cross-Tenant',
                    ],
                    [
                        'id' => 'p5-oauth', 'title' => 'OAuth-Apps mit Risk-Score prüfen', 'time' => 5,
                        'why' => 'Drittanbieter-Apps können „Mail.ReadWrite" oder schlimmer haben — und niemand erinnert sich, wer das vor 3 Jahren genehmigt hat. Diese Module zeigt High-Privilege × Inaktivität als Risiko.',
                        'how' => [
                            'Apps mit Risk-Score > 50 untersuchen.',
                            'Inaktiv seit 180+ Tagen → revoken.',
                            'Microsoft-Apps in Ruhe lassen (selbst wenn der Score hoch ist).',
                            'App-Consent-Policy verschärfen: User-Consent nur für Low-Risk-Permissions.',
                        ],
                        'link' => '/oauthaudit', 'link_label' => 'OAuth-App-Audit',
                    ],
                    [
                        'id' => 'p5-apps', 'title' => 'Eigene App-Registrierungen verwalten', 'time' => 3,
                        'why' => 'Client Secrets sollten unter 1 Jahr alt sein und in einem Geheimnis-Manager liegen. Verwaiste Apps löschen — jede ist eine potentielle Hintertür.',
                        'how' => [
                            'Secrets, die in den nächsten 30 Tagen ablaufen → erneuern, alte revoken.',
                            'Apps ohne Owner: identifizieren und Owner zuweisen.',
                            'Unbenutzte Apps löschen.',
                        ],
                        'link' => '/appregistrations', 'link_label' => 'App-Registrierungen',
                    ],
                ],
            ],

            // ── PHASE 6 ────────────────────────────────────────────────
            [
                'id' => 'phase6', 'title' => 'Phase 6 — Automatisierung scharfschalten', 'subtitle' => 'Einmalig · ca. 5 Minuten',
                'icon' => 'gear-fill', 'color' => '#0891b2',
                'intro' => 'Damit das Tool ab jetzt für dich arbeitet — Reports verschickt, Alerts pusht, Snapshots erstellt.',
                'steps' => [
                    [
                        'id' => 'p6-cron', 'title' => 'Cron-Jobs aktiviert prüfen', 'time' => 3,
                        'why' => 'Ohne aktive Cron-Jobs gibt es keine Reports, keine Sharing-Scans, keine Audit-Diff-Snapshots. Voraussetzung: System-Cron alle Minute auf run-cron.php zeigt.',
                        'how' => [
                            'Server-Cron prüfen: `* * * * * /usr/bin/php /pfad/zu/run-cron.php`.',
                            'Im Tool unter /cron alle Jobs „enabled" lassen (insbesondere alert_new_defender, alert_new_risky_users, audit_diff_snapshot).',
                            'Einen Job manuell „Run now" auslösen — sollte „success" liefern.',
                        ],
                        'link' => '/cron', 'link_label' => 'Cron-Übersicht',
                    ],
                    [
                        'id' => 'p6-recipients', 'title' => 'Benachrichtigungs-Empfänger sind gesetzt', 'time' => 2,
                        'why' => 'Alle Alerts (Risk, Defender, Service-Health) gehen an konfigurierte Empfänger. Ohne diese Adresse passiert „leise" und du erfährst von Vorfällen erst aus dem Audit-Log.',
                        'how' => [
                            'Mindestens eine Security-Mailbox als Empfänger.',
                            'Optional eine zweite Adresse für ITSM-Tickets (Jira, ServiceNow per Mail-Gateway).',
                            'Test-Mail aus den Einstellungen auslösen.',
                        ],
                        'link' => '/settings', 'link_label' => 'Einstellungen (Tab „Benachrichtigungen")',
                        'auto_done' => $autoRecipients,
                    ],
                    [
                        'id' => 'p6-executive', 'title' => 'Executive-Report monatlich an Leitung', 'time' => 3,
                        'why' => 'Eine Seite, einmal im Monat, mit KPIs & Trends. Hält das Thema Sicherheit oben auf der GF-Agenda — ohne dass du jedes Mal Folien bauen musst.',
                        'how' => [
                            'Aktivieren, Empfänger setzen (Geschäftsführung, IT-Leitung).',
                            'Erste Vorschau im Tool ansehen, ggf. anpassen.',
                            'Test-Versand starten.',
                        ],
                        'link' => '/executivereport', 'link_label' => 'Executive-Report',
                    ],
                    [
                        'id' => 'p6-backup', 'title' => 'Backup-Status tracken', 'time' => 3,
                        'why' => 'Microsoft 365 hat kein klassisches Backup. Wenn ein Mitarbeiter zwischen Tag 31 und Tag 60 eine Datei löscht, ist sie weg. Drittanbieter-Backup (Veeam, AvePoint, Acronis, etc.) dokumentieren.',
                        'how' => [
                            'Backup-Anbieter eintragen.',
                            'Letzten erfolgreichen Lauf einmalig manuell setzen — der Health-Score nutzt das danach.',
                            'Wenn noch kein Backup: jetzt evaluieren (durchschnittlich 3–6 €/User/Monat).',
                        ],
                        'link' => '/backup', 'link_label' => 'Backup-Status',
                        'auto_done' => $autoBackup,
                    ],
                    [
                        'id' => 'p6-workflows', 'title' => 'Mindestens einen Workflow anlegen', 'time' => 5,
                        'why' => 'Workflows nehmen Routine ab. Beispiel: „Neuer Gast → Mail an Compliance-Team + Notification im Tool" — passiert dann automatisch, ohne dass jemand drauf achten muss.',
                        'how' => [
                            'Trigger „Neuer Gast-Benutzer" wählen.',
                            'Aktion „In-App-Benachrichtigung erzeugen" + „Mail senden" verketten.',
                            'Workflow aktivieren, eine Woche beobachten.',
                        ],
                        'link' => '/workflows', 'link_label' => 'Workflows',
                    ],
                    [
                        'id' => 'p6-sharing-monitor', 'title' => 'Sharing-Monitor mit Owner-Befragung', 'time' => 5,
                        'why' => 'Externe Freigaben werden jeden Monat automatisch geprüft, der Owner bekommt eine Mail mit „Brauchst du das noch?". Antwortet keiner → automatischer Widerruf nach 14 Tagen.',
                        'how' => [
                            'Aktivieren im Sharing-Monitor.',
                            'Befragungs-Mail-Text personalisieren (Firmen-Logo, Tonalität).',
                            'Auto-Widerruf konservativ einstellen (lieber Erinnerung statt sofortiger Widerruf).',
                        ],
                        'link' => '/sharing/monitor', 'link_label' => 'Sharing-Monitor',
                    ],
                ],
            ],

            // ── PHASE 7 ────────────────────────────────────────────────
            [
                'id' => 'phase7', 'title' => 'Phase 7 — Wöchentliche Routine', 'subtitle' => 'Jede Woche · ca. 5 Minuten',
                'icon' => 'calendar-week', 'color' => '#16a34a',
                'intro' => 'Diese Liste wandert in deinen Kalender. Sie ersetzt nicht die täglichen E-Mail-Alerts — sie sorgt dafür, dass nichts „stillschweigend" stehen bleibt.',
                'steps' => [
                    [
                        'id' => 'p7-dashboard', 'title' => 'Dashboard + Notifications-Glocke', 'time' => 1,
                        'why' => 'KPI-Sparklines zeigen Trend-Veränderungen, die einzelne Module nicht erkennen können (z. B. „MFA-Abdeckung sinkt seit 3 Wochen langsam").',
                        'how' => [
                            'Sparklines auf signifikante Ausschläge nach unten prüfen.',
                            'Glocke (oben rechts) → letzte Events kurz durchgehen.',
                            'Bei rotem Pfeil: zugehöriges Modul öffnen, Ursache klären.',
                        ],
                        'link' => '/', 'link_label' => 'Dashboard',
                    ],
                    [
                        'id' => 'p7-risky', 'title' => 'Risk-Module bewerten', 'time' => 2,
                        'why' => 'Riskante Anmeldungen, Defender-Alerts und MFA-Fatigue / Insider-Threat-Signale gehören jede Woche durchgesehen — auch wenn null. Dann ist es ein „kein-Vorfall"-Beleg.',
                        'how' => [
                            'Riskante Anmeldungen: jede bewerten („compromised" oder „dismissed").',
                            'Defender-Alerts: jede triagieren.',
                            'MFA-Fatigue: ungewöhnliche Zahlen → Auth-Strength prüfen.',
                            'Insider-Threat: Top-3 Kandidaten ansehen.',
                        ],
                        'link' => '/riskysignins', 'link_label' => 'Risiko-Anmeldungen',
                    ],
                    [
                        'id' => 'p7-forwards', 'title' => 'Auto-Forward-Audit', 'time' => 1,
                        'why' => 'Eine Inbox-Regel, die alle Mails an eine externe Adresse weiterleitet, ist der häufigste Exfiltrations-Pfad nach Account-Übernahme. Neue Treffer → sofort prüfen.',
                        'how' => [
                            'Treffer auf externe Domains öffnen.',
                            'Mit dem User Rücksprache halten — meist „Phishing-Vorfall vor 2 Monaten".',
                            'Regel entfernen, MFA reset, Sessions revoken.',
                        ],
                        'link' => '/mailboxrules', 'link_label' => 'Auto-Forward-Audit',
                    ],
                    [
                        'id' => 'p7-stale', 'title' => 'Inaktive Konten & DLP-Vorfälle', 'time' => 2,
                        'why' => 'Inaktive Lizenzen kosten Geld; DLP-Treffer zeigen, was Mitarbeiter wirklich versucht haben weiterzuleiten.',
                        'how' => [
                            'Stale-Account-Liste durchgehen, Lizenzen entziehen.',
                            'DLP-Treffer: ist es ein false-positive oder ein echter Vorfall? Bei echtem Vorfall: Mitarbeiter ansprechen, Policy ggf. anpassen.',
                        ],
                        'link' => '/staleaccounts', 'link_label' => 'Inaktive Konten',
                    ],
                    [
                        'id' => 'p7-diff', 'title' => 'Audit-Diff prüfen', 'time' => 1,
                        'why' => 'Vergleicht den heutigen Snapshot mit dem von vor 7 Tagen. Zeigt unerwartete Änderungen sofort — z. B. „CA-Policy wurde deaktiviert" oder „Sharing-Capability wurde geöffnet".',
                        'how' => [
                            'Auf /auditdiff den heutigen und den vor 7 Tagen vergleichen.',
                            'Bei unerwarteten Änderungen → Audit-Log nach „wer war das" durchsuchen.',
                        ],
                        'link' => '/auditdiff', 'link_label' => 'Audit-Diff',
                    ],
                ],
            ],

            // ── PHASE 8 ────────────────────────────────────────────────
            [
                'id' => 'phase8', 'title' => 'Phase 8 — Monatlich & vierteljährlich', 'subtitle' => 'Routine · ca. 30 Minuten/Monat',
                'icon' => 'calendar-month', 'color' => '#7c3aed',
                'intro' => 'Was nicht jede Woche dran sein muss — aber jeden Monat oder jedes Quartal.',
                'steps' => [
                    [
                        'id' => 'p8-pdf', 'title' => 'DSGVO/NIS-2-PDF generieren & archivieren', 'time' => 3,
                        'why' => 'Auditfähiger Bericht mit Tenant-Stammdaten + allen Hardening-Items + Zuordnung zu DSGVO/NIS-2/BSI-Artikeln. Monatlich für die Compliance-Akte.',
                        'how' => [
                            'Auf /auditreport → „Als PDF speichern" im Browser.',
                            'PDF in den Compliance-Ordner (separat archivieren).',
                            'Datenschutzbeauftragten/IT-Leitung CC informieren.',
                        ],
                        'link' => '/auditreport', 'link_label' => 'Audit-Report',
                    ],
                    [
                        'id' => 'p8-review', 'title' => 'Access-Review-Lauf abschließen', 'time' => 10,
                        'why' => 'Wenn der Quartals-Review läuft: Entscheidungen einsammeln, anwenden, neuen Review-Lauf für nächstes Quartal anlegen.',
                        'how' => [
                            'Offenen Review öffnen, alle Entscheidungen durchgehen.',
                            '„Apply" klicken — Entscheidungen werden ausgeführt.',
                            'Neuen Review für nächstes Quartal anlegen.',
                        ],
                        'link' => '/accessreview', 'link_label' => 'Access Reviews',
                    ],
                    [
                        'id' => 'p8-licenses', 'title' => 'Lizenz-Berater & Kosten-Trend', 'time' => 5,
                        'why' => 'Welche Lizenzen werden tatsächlich genutzt? Wer hat zu hohe Lizenz für seinen Bedarf? Spart oft 5–15 % der M365-Kosten.',
                        'how' => [
                            'Lizenz-Berater → Empfehlungen anschauen.',
                            'Mit HR/Geschäftsführung absprechen.',
                            'Schrittweise umstellen, nicht alles auf einmal.',
                        ],
                        'link' => '/licenseadvisor', 'link_label' => 'Lizenz-Berater',
                    ],
                    [
                        'id' => 'p8-securescore', 'title' => 'Secure-Score-Trend ansehen', 'time' => 5,
                        'why' => 'Microsofts eigene Score-Metrik. Punkte-Quelle: noch nicht umgesetzte Empfehlungen → wenig Aufwand für viel Score.',
                        'how' => [
                            '30/90-Tage-Trend ansehen.',
                            'Top-5 ungeöffnete Empfehlungen umsetzen (oft 5–10 Minuten Aufwand).',
                            'Score-Sprung im Executive-Report dokumentieren.',
                        ],
                        'link' => '/securescore', 'link_label' => 'Secure Score',
                    ],
                    [
                        'id' => 'p8-ai', 'title' => 'KI-Sicherheitsberater anstoßen', 'time' => 5,
                        'why' => 'Konsolidierte Bewertung über BSI + NIS-2 + DSGVO + Anomalien hinweg. Nennt konkrete Top-Empfehlungen mit Quellen-Artikel.',
                        'how' => [
                            'Auf /ai → „Analyse starten".',
                            'Empfehlungen priorisieren — was hat den höchsten Impact für den geringsten Aufwand?',
                            'In dein Ticket-System übernehmen.',
                        ],
                        'link' => '/ai', 'link_label' => 'KI-Berater',
                    ],
                    [
                        'id' => 'p8-breakglass-test', 'title' => 'Break-Glass-Login testen', 'time' => 2,
                        'why' => 'Wenn du es nicht regelmäßig testest, weißt du im Ernstfall nicht, ob es noch funktioniert. Mindestens vierteljährlich.',
                        'how' => [
                            'Mit dem hinterlegten FIDO2-Key einloggen.',
                            'Datum + Tester im Break-Glass-Modul dokumentieren.',
                            'Nach Test sofort wieder ausloggen.',
                        ],
                        'link' => '/breakglass', 'link_label' => 'Break-Glass',
                    ],
                ],
            ],
        ];
    }

    /**
     * Returns the current progress map: step_id => 'done' | 'skipped'.
     */
    public static function progress(): array
    {
        $raw = (string)Config::getInstance()->get(self::PROGRESS_KEY, '{}');
        $data = json_decode($raw, true);
        return is_array($data) ? $data : [];
    }

    public static function markStep(string $stepId, string $state): void
    {
        if (!in_array($state, ['done', 'skipped', 'open'], true)) return;
        $p = self::progress();
        if ($state === 'open') {
            unset($p[$stepId]);
        } else {
            $p[$stepId] = $state;
        }
        Config::getInstance()->set(self::PROGRESS_KEY, json_encode($p, JSON_UNESCAPED_UNICODE));
    }

    public static function reset(): void
    {
        Config::getInstance()->set(self::PROGRESS_KEY, '{}');
    }

    /**
     * Summary stats: total / done / skipped / phase-by-phase counts.
     */
    public static function summary(): array
    {
        $guide   = self::guide();
        $prog    = self::progress();
        $total   = 0; $done = 0; $skipped = 0; $auto = 0;
        $phases  = [];

        foreach ($guide as $phase) {
            $pTotal = 0; $pDone = 0; $pSkipped = 0;
            foreach ($phase['steps'] as $s) {
                $pTotal++;
                $total++;
                $state = $prog[$s['id']] ?? null;
                $isAutoDone = isset($s['auto_done']) && is_callable($s['auto_done']) && ($s['auto_done'])();
                if ($state === 'done' || $isAutoDone) { $done++; $pDone++; if ($isAutoDone && $state !== 'done') $auto++; }
                if ($state === 'skipped') { $skipped++; $pSkipped++; }
            }
            $phases[$phase['id']] = ['total' => $pTotal, 'done' => $pDone, 'skipped' => $pSkipped];
        }
        return [
            'total'   => $total,
            'done'    => $done,
            'skipped' => $skipped,
            'open'    => max(0, $total - $done - $skipped),
            'auto_detected' => $auto,
            'pct'     => $total > 0 ? round(($done / $total) * 100) : 0,
            'phases'  => $phases,
        ];
    }
}
