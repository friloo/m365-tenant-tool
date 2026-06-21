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
                'id' => 'phase1', 'title' => t('Phase 1 — Fundament'), 'subtitle' => t('Einmalig · ca. 10 Minuten'),
                'icon' => 'building', 'color' => '#0078d4',
                'intro' => t('Die Grundlagen, ohne die alles andere nichts hilft: stabile Tenant-Verbindung, korrekte Berechtigungen, dein eigenes Admin-Konto abgesichert.'),
                'steps' => [
                    [
                        'id' => 'p1-setup', 'title' => t('Einrichtungs-Assistent durchlaufen'), 'time' => 5,
                        'why' => t('Prüft, dass Tenant-ID/Client-Secret korrekt sind, ein Token bezogen werden kann, die Permissions reichen — und fragt Benachrichtigungs-Empfänger und Branding ab.'),
                        'how' => [
                            t('Öffne den Assistenten und klicke alle fünf Schritte durch.'),
                            t('Bei „Empfänger" mindestens eine E-Mail eintragen (Security-Postfach), damit dich später Alerts erreichen.'),
                            t('Im letzten Schritt das passende Compliance-Profil auswählen — Details dazu in Phase 2.'),
                        ],
                        'link' => '/setup', 'link_label' => t('Zum Assistenten'),
                        'auto_done' => $autoSetup,
                    ],
                    [
                        'id' => 'p1-perms', 'title' => t('Graph-API-Berechtigungen vollständig'), 'time' => 5,
                        'why' => t('Ohne die richtigen App-Permissions sind viele Module blind. Fehlende Berechtigungen müssen mit Admin-Consent in Entra erteilt werden.'),
                        'how' => [
                            t('Auf /settings/permissions sollten alle Zeilen grün sein.'),
                            t('Fehlende Permissions: in der Entra-App-Registrierung „API permissions" → „Add a permission" → „Microsoft Graph" → „Application permissions".'),
                            t('Anschließend „Grant admin consent for …" klicken (das ist die zweite Schaltfläche, häufig übersehen).'),
                            t('Im Tool dann „Cache leeren" und Permissions-Seite neu laden.'),
                        ],
                        'link' => '/settings/permissions', 'link_label' => t('Berechtigungs-Audit öffnen'),
                    ],
                    [
                        'id' => 'p1-2fa', 'title' => t('Eigene 2FA aktivieren'), 'time' => 2,
                        'why' => t('Das Tool selbst hat einen Admin-Login. Ohne 2FA ist es ein einzelnes Passwort weg vom kompletten Tenant. TOTP-RFC-6238, kompatibel mit Microsoft/Google Authenticator, Aegis usw.'),
                        'how' => [
                            t('QR-Code mit der Authenticator-App scannen.'),
                            t('Zur Verifikation den 6-stelligen Code eingeben.'),
                            t('Wiederherstellungs-Codes ausdrucken oder im Passwort-Manager ablegen — wirklich, nicht überspringen.'),
                        ],
                        'link' => '/settings/2fa', 'link_label' => t('2FA einrichten'),
                    ],
                    [
                        'id' => 'p1-users', 'title' => t('Tool-Benutzer aufräumen'), 'time' => 3,
                        'why' => t('Shared-Accounts (z. B. „admin@firma.de" mit drei Personen) gehen gegen jede Compliance. Sauberer: jeder Admin/Operator hat eigenen Zugang im Tool — verknüpft mit dem Entra-Konto.'),
                        'how' => [
                            t('Pro Person einen Eintrag anlegen, Rolle „Admin" oder „Operator".'),
                            t('Generische/geteilte Logins entfernen.'),
                            t('Operator-Rolle für Helpdesk: kann scannen, Lizenzen umschichten, kein Zugriff auf Einstellungen.'),
                        ],
                        'link' => '/settings/users', 'link_label' => t('Benutzer-Zugang'),
                    ],
                ],
            ],

            // ── PHASE 2 ────────────────────────────────────────────────
            [
                'id' => 'phase2', 'title' => t('Phase 2 — One-Click-Härtung'), 'subtitle' => t('Einmalig · ca. 5 Minuten'),
                'icon' => 'magic', 'color' => '#7c3aed',
                'intro' => t('Die größten Hebel mit wenigen Klicks. Profile bündeln branchen-typische Härtung; das Hardening-Modul lässt dich einzelne Items feintunen.'),
                'steps' => [
                    [
                        'id' => 'p2-profile', 'title' => t('Compliance-Profil anwenden'), 'time' => 3,
                        'why' => t('Setzt mit einem Klick 6–13 Hardening-Defaults entlang einer bekannten Regulierung (DSGVO, KRITIS, BaFin/DORA, BSI).'),
                        'how' => [
                            t('Standard / DSGVO-Basis — für jeden Tenant ein guter Start.'),
                            t('Gesundheitswesen — wenn Patienten-/Klientendaten verarbeitet werden.'),
                            t('Finanzwesen — Banken, Versicherungen, Asset Manager.'),
                            t('Öffentlicher Sektor — Behörden, Stadtwerke, Hochschulen.'),
                            t('Bildung — Schulen, Kitas (Schutz von Kinder-Daten gem. DSGVO Art. 8).'),
                        ],
                        'link' => '/complianceprofile', 'link_label' => t('Profile öffnen'),
                        'auto_done' => $autoProfile,
                    ],
                    [
                        'id' => 'p2-hardening', 'title' => t('Restliche Hardening-Items abarbeiten'), 'time' => 5,
                        'why' => t('Das Profil deckt 60–80 % ab. Der Rest sind Items, die nicht in jedes Branchen-Muster passen — z. B. App-Anlage durch Endbenutzer blockieren oder Idle-Session-Signout.'),
                        'how' => [
                            t('Alles auf „unkritisch" / grün bringen, was Graph direkt schalten kann.'),
                            t('Wo „Admin-Center öffnen" steht: Deep-Link folgen, Wert prüfen, ggf. anpassen.'),
                            t('Items mit Status „unbekannt" → meist nur fehlende Permission. Zurück zu Phase 1, Schritt 2.'),
                        ],
                        'link' => '/hardening', 'link_label' => t('Hardening-Modul'),
                    ],
                ],
            ],

            // ── PHASE 3 ────────────────────────────────────────────────
            [
                'id' => 'phase3', 'title' => t('Phase 3 — Identity & Zugriff'), 'subtitle' => t('Einmalig · ca. 15 Minuten'),
                'icon' => 'shield-lock', 'color' => '#dc2626',
                'intro' => t('80 % der echten Angriffe gehen über kompromittierte Identitäten. Dieser Block bringt den größten Sicherheitsgewinn pro Minute.'),
                'steps' => [
                    [
                        'id' => 'p3-breakglass', 'title' => t('Break-Glass-Accounts einrichten'), 'time' => 5,
                        'why' => t('Falls dein MFA-Dienst ausfällt oder dein Conditional-Access dich aussperrt, brauchst du ein Notfall-Konto. Pflicht laut BSI und allen Audit-Standards.'),
                        'how' => [
                            t('Zwei dedizierte Konten anlegen (z. B. breakglass1@…, breakglass2@…).'),
                            t('Global Admin permanent zuweisen — diese Accounts sind nicht für PIM.'),
                            t('FIDO2-Hardware-Key (z. B. YubiKey) registrieren, in einen Safe legen.'),
                            t('Conditional-Access-Policies müssen diese UPNs **ausnehmen**.'),
                            t('Test-Login alle 90 Tage dokumentieren.'),
                        ],
                        'link' => '/breakglass', 'link_label' => t('Break-Glass-Modul'),
                    ],
                    [
                        'id' => 'p3-ca', 'title' => t('Conditional Access mindestens auf Baseline'), 'time' => 10,
                        'why' => t('Conditional Access ist das stärkste Identity-Werkzeug in M365. Wer das nicht hat, ist auf Security-Defaults angewiesen — bei mittlerer Komplexität nicht mehr ausreichend.'),
                        'how' => [
                            t('Mindest-Set: „MFA für alle Benutzer", „Block Legacy Auth", „MFA für Admin-Rollen" (separate strenge Policy).'),
                            t('Empfehlung: „Compliant device required" für sensible Apps (SharePoint, Exchange).'),
                            t('Test-Mode („Report-only") zuerst — eine Woche beobachten, dann aktivieren.'),
                            t('Break-Glass-Accounts **explizit ausnehmen** (siehe oben).'),
                        ],
                        'link' => '/conditionalaccess', 'link_label' => t('CA-Policies'),
                    ],
                    [
                        'id' => 'p3-locations', 'title' => t('Named Locations definieren'), 'time' => 5,
                        'why' => t('Mit benannten Standorten kannst du CA-Policies geo-fence-artig einschränken („nur aus DE/AT/CH") oder die Office-IPs als „trusted" markieren.'),
                        'how' => [
                            t('Eigene öffentliche Office-IPs anlegen (statische Ausgangs-IPs).'),
                            t('Länder-Whitelist mit den tatsächlich genutzten Ländern.'),
                            t('Reise-Whitelist temporär ergänzen, wenn Mitarbeiter ins Ausland fahren.'),
                        ],
                        'link' => '/namedlocations', 'link_label' => t('Named Locations'),
                    ],
                    [
                        'id' => 'p3-authstrength', 'title' => t('Authentication-Strength: phishing-resistent'), 'time' => 5,
                        'why' => t('SMS-MFA ist veraltet, klassisches App-Push ist anfällig für MFA-Fatigue. Standard sollte FIDO2 / Authenticator mit Number-Matching sein.'),
                        'how' => [
                            t('Im Authentication-Strength-Modul Tenant-Policy auf „phishing-resistant" prüfen.'),
                            t('Im Hardening-Modul Number-Matching aktivieren falls noch nicht erfolgt.'),
                            t('Für Admin-Rollen: CA-Policy mit „phishing-resistant MFA" als Bedingung.'),
                        ],
                        'link' => '/authstrength', 'link_label' => t('Auth-Strength'),
                    ],
                    [
                        'id' => 'p3-pim', 'title' => t('PIM: Permanente Admin-Rollen eliminieren'), 'time' => 10,
                        'why' => t('Wer 24/7 Global Admin ist, ist 24/7 ein lohnendes Ziel. PIM macht Admin-Rechte Just-in-Time aktivierbar — alle Aktivierungen sind auditierbar.'),
                        'how' => [
                            t('Liste der permanenten Admin-Zuweisungen anschauen.'),
                            t('Auf „Eligible" (PIM-fähig) umstellen, statt „Active".'),
                            t('Aktivierungs-Zeitraum auf 4–8 Stunden begrenzen.'),
                            t('Genehmigungs-Workflow optional bei Global Admin / Privileged Role Admin.'),
                        ],
                        'link' => '/pim', 'link_label' => t('PIM-Übersicht'),
                    ],
                    [
                        'id' => 'p3-token', 'title' => t('Token-Lifetime / Sign-in-Frequency'), 'time' => 3,
                        'why' => t('Standard ist „Token bleibt 90 Tage gültig". Bei einem kompromittierten Refresh-Token kann sich der Angreifer Wochen lang halten, auch nach Passwort-Reset.'),
                        'how' => [
                            t('CA-Policy mit „Sign-in frequency" anlegen.'),
                            t('Admin-Rollen: 4–8 Stunden.'),
                            t('Normale Benutzer: 12–24 Stunden.'),
                            t('Sehr sensible Anwendungen (Finanzen): jeweils ganz neu authentifizieren.'),
                        ],
                        'link' => '/tokenlifetime', 'link_label' => t('Token-Lifetime'),
                    ],
                    [
                        'id' => 'p3-adminroles', 'title' => t('Admin-Rollen ausmisten'), 'time' => 5,
                        'why' => t('Erfahrungsgemäß haben 2–3× so viele Personen Admin-Rollen wie nötig — angesammelt über Jahre. Audit: ist diese Person noch in der Position?'),
                        'how' => [
                            t('Pro Rolle: ist sie wirklich überall nötig? Kann eine weniger privilegierte Rolle reichen?'),
                            t('Global Admin: maximal 2–5 Personen (BSI-Empfehlung).'),
                            t('Bei Unsicherheit: Rolle entziehen → 2 Wochen warten → meldet sich niemand → bleibt entzogen.'),
                        ],
                        'link' => '/adminroles', 'link_label' => t('Admin-Rollen'),
                    ],
                ],
            ],

            // ── PHASE 4 ────────────────────────────────────────────────
            [
                'id' => 'phase4', 'title' => t('Phase 4 — Daten & E-Mail'), 'subtitle' => t('Einmalig · ca. 15 Minuten'),
                'icon' => 'envelope-paper', 'color' => '#059669',
                'intro' => t('Hier geht es darum, dass Daten nicht ungewollt rausfließen — und dass eingehende Mails nicht so leicht zu fälschen sind.'),
                'steps' => [
                    [
                        'id' => 'p4-sharing', 'title' => t('SharePoint/OneDrive-Sharing einschränken'), 'time' => 3,
                        'why' => t('Standard ist „Anyone-Links erlaubt, beliebige externe Mails einladbar". DSGVO-konform ist eher: nur bestehende Gäste dürfen Inhalte erhalten, Anon-Links laufen automatisch ab.'),
                        'how' => [
                            t('„existingExternalUserSharingOnly" als Capability.'),
                            t('Anonym-Link-Ablauf: 30 Tage (oder weniger).'),
                            t('Re-Sharing durch externe Benutzer deaktivieren.'),
                            t('Idle-Session-Signout aktivieren (4h Idle → automatisch ausloggen).'),
                        ],
                        'link' => '/sharing/policies', 'link_label' => t('Sharing-Richtlinien'),
                    ],
                    [
                        'id' => 'p4-domainhealth', 'title' => t('SPF / DKIM / DMARC für alle Domains'), 'time' => 10,
                        'why' => t('Ohne DMARC kann jeder im Internet deine Domain als Absender fälschen. Mit SPF + DKIM + DMARC mit „reject"-Policy ist Spoofing technisch ausgeschlossen.'),
                        'how' => [
                            t('Pro Domain alle drei DNS-Records grün haben.'),
                            t('SPF: alle berechtigten Sender (EXO + Marketing-Tools wie Mailchimp + ERP-System) eintragen.'),
                            t('DKIM: in Defender / Exchange Admin Center aktivieren.'),
                            t('DMARC: erst „p=none" → Reports prüfen → dann „p=quarantine" → später „p=reject".'),
                        ],
                        'link' => '/domainhealth', 'link_label' => t('Domain Health'),
                    ],
                    [
                        'id' => 'p4-mailflow', 'title' => t('Defender for Office: Safe Links / Safe Attachments'), 'time' => 5,
                        'why' => t('Klassische Phishing-Mail-Filter erkennen 80 %. Safe-Links + Safe-Attachments fangen die restlichen 20 % ab — durch Sandbox-Detonation und Time-of-Click-Re-Check.'),
                        'how' => [
                            t('In Defender for Office: Standard-Preset-Policies aktivieren.'),
                            t('External-Sender-Identifier („Externe Mail" Tag) einschalten.'),
                            t('Verdächtige Domains, die häufig Phishing senden, in den Block-Listen pflegen.'),
                        ],
                        'link' => '/mailflow', 'link_label' => t('Mail Flow & Schutz'),
                    ],
                    [
                        'id' => 'p4-labels', 'title' => t('Sensitivity Labels einrichten'), 'time' => 5,
                        'why' => t('Labels ermöglichen Verschlüsselung, Wasserzeichen und Rechte-Verwaltung „on save". Auch wenn ein Dokument den Tenant verlässt, bleibt es geschützt.'),
                        'how' => [
                            t('Mindest-Set: Öffentlich · Intern · Vertraulich · Streng vertraulich.'),
                            t('„Vertraulich" und „Streng vertraulich" automatisch verschlüsseln.'),
                            t('Auto-Apply-Regeln (z. B. Kreditkartennummer → automatisch „Vertraulich").'),
                        ],
                        'link' => '/sensitivitylabels', 'link_label' => t('Sensitivity Labels'),
                    ],
                    [
                        'id' => 'p4-dlp', 'title' => t('DLP-Policy für die kritischste Datenkategorie'), 'time' => 5,
                        'why' => t('Eine einzige sinnvoll konfigurierte DLP-Policy ist besser als zehn überregulierende — Mitarbeiter umgehen sonst alles.'),
                        'how' => [
                            t('Branchen-typische erste Policy: Healthcare → Gesundheitsdaten, Finance → IBAN/Kreditkarte, B2B → Geschäftsgeheimnisse.'),
                            t('Erst „Tip" + Audit, dann nach 2 Wochen „Block" für externe Empfänger.'),
                            t('False-Positives sammeln und Policy iterieren.'),
                        ],
                        'link' => '/dlppolicies', 'link_label' => t('DLP-Richtlinien'),
                    ],
                    [
                        'id' => 'p4-retention', 'title' => t('Aufbewahrungs-Policy nach DSGVO/GoBD'), 'time' => 5,
                        'why' => t('Du **musst** löschen (DSGVO: nur so lang wie nötig) — und **musst** aufbewahren (GoBD: Geschäftsbriefe 6 Jahre, Buchhaltung 10 Jahre). Beide Anforderungen in eine Policy gegossen.'),
                        'how' => [
                            t('Mail-Postfächer: 10 Jahre für Buchhaltung / Geschäftsführung, 6 Jahre Standard.'),
                            t('Teams-Chats: 1–2 Jahre.'),
                            t('OneDrive: 30 Tage nach Account-Löschung dann hard delete.'),
                            t('Rechtliche Klärung mit Datenschutz/Steuerberater vor Aktivierung.'),
                        ],
                        'link' => '/retentionpolicies', 'link_label' => t('Aufbewahrung'),
                    ],
                    [
                        'id' => 'p4-lockbox', 'title' => t('Customer Lockbox aktivieren'), 'time' => 3,
                        'why' => t('Standardmäßig kann ein Microsoft-Support-Mitarbeiter auf deine Tenant-Daten zugreifen, wenn ein Ticket es nahelegt. Mit Customer Lockbox musst du jeden Zugriff explizit freigeben.'),
                        'how' => [
                            t('Erfordert M365 E5 oder Customer-Lockbox-Add-On.'),
                            t('Freigabe-Workflow im Tool dokumentieren (wer entscheidet, wie schnell).'),
                            t('DSGVO-Auftragsverarbeitungsvertrag mit Microsoft entsprechend ergänzen.'),
                        ],
                        'link' => '/customerlockbox', 'link_label' => t('Customer Lockbox'),
                        'auto_done' => $autoLockbox,
                    ],
                ],
            ],

            // ── PHASE 5 ────────────────────────────────────────────────
            [
                'id' => 'phase5', 'title' => t('Phase 5 — Gäste, Apps & externe Identitäten'), 'subtitle' => t('Einmalig · ca. 10 Minuten'),
                'icon' => 'people', 'color' => '#ea580c',
                'intro' => t('Gäste und OAuth-Apps sind oft Jahre alt und wurden nie überprüft. Hier räumst du auf, was sich „eingenistet" hat.'),
                'steps' => [
                    [
                        'id' => 'p5-guests', 'title' => t('Alte Gast-Benutzer entfernen'), 'time' => 5,
                        'why' => t('Jeder Gast ist ein potentieller Angriffsvektor — Konten, die zu einer fremden Organisation gehören, die du nicht kontrollierst. Ohne Sign-In seit 90+ Tagen: meist nicht mehr nötig.'),
                        'how' => [
                            t('Filter: „Last sign-in > 90 days" oder „nie".'),
                            t('Bevor du entfernst: Owner der Sharing-Beziehung kurz informieren.'),
                            t('Bulk-deaktivieren statt sofort löschen — 30 Tage Beobachtungs-Phase.'),
                        ],
                        'link' => '/guestusers', 'link_label' => t('Gast-Benutzer'),
                    ],
                    [
                        'id' => 'p5-review', 'title' => t('Access Review für Gäste anlegen'), 'time' => 5,
                        'why' => t('NIS-2 und DSGVO verlangen periodische Berechtigungs-Überprüfung. Mit einem Access Review fragst du automatisiert alle Owner: „Brauchen die diesen Zugriff noch?"'),
                        'how' => [
                            t('Quartals-Rhythmus für Gäste.'),
                            t('Halbjährlich für interne sensible Gruppen (Geschäftsführung, IT-Admins).'),
                            t('Auto-revoke bei „no response" aktivieren.'),
                        ],
                        'link' => '/accessreview', 'link_label' => t('Access Reviews'),
                    ],
                    [
                        'id' => 'p5-crosstenant', 'title' => t('Cross-Tenant Access regeln'), 'time' => 3,
                        'why' => t('Standard ist „mit jedem M365-Tenant darf B2B-Collaboration laufen". Wenn du nur mit zwei Partnern arbeitest, ist eine Allow-List sicherer als die offene Welt.'),
                        'how' => [
                            t('Partner-Tenants explizit erlauben.'),
                            t('Default-Policy auf „inbound block" stellen.'),
                            t('Teams Shared Channels nur mit ausgewählten Tenants.'),
                        ],
                        'link' => '/crosstenantaccess', 'link_label' => t('Cross-Tenant'),
                    ],
                    [
                        'id' => 'p5-oauth', 'title' => t('OAuth-Apps mit Risk-Score prüfen'), 'time' => 5,
                        'why' => t('Drittanbieter-Apps können „Mail.ReadWrite" oder schlimmer haben — und niemand erinnert sich, wer das vor 3 Jahren genehmigt hat. Diese Module zeigt High-Privilege × Inaktivität als Risiko.'),
                        'how' => [
                            t('Apps mit Risk-Score > 50 untersuchen.'),
                            t('Inaktiv seit 180+ Tagen → revoken.'),
                            t('Microsoft-Apps in Ruhe lassen (selbst wenn der Score hoch ist).'),
                            t('App-Consent-Policy verschärfen: User-Consent nur für Low-Risk-Permissions.'),
                        ],
                        'link' => '/oauthaudit', 'link_label' => t('OAuth-App-Audit'),
                    ],
                    [
                        'id' => 'p5-apps', 'title' => t('Eigene App-Registrierungen verwalten'), 'time' => 3,
                        'why' => t('Client Secrets sollten unter 1 Jahr alt sein und in einem Geheimnis-Manager liegen. Verwaiste Apps löschen — jede ist eine potentielle Hintertür.'),
                        'how' => [
                            t('Secrets, die in den nächsten 30 Tagen ablaufen → erneuern, alte revoken.'),
                            t('Apps ohne Owner: identifizieren und Owner zuweisen.'),
                            t('Unbenutzte Apps löschen.'),
                        ],
                        'link' => '/appregistrations', 'link_label' => t('App-Registrierungen'),
                    ],
                ],
            ],

            // ── PHASE 6 ────────────────────────────────────────────────
            [
                'id' => 'phase6', 'title' => t('Phase 6 — Automatisierung scharfschalten'), 'subtitle' => t('Einmalig · ca. 5 Minuten'),
                'icon' => 'gear-fill', 'color' => '#0891b2',
                'intro' => t('Damit das Tool ab jetzt für dich arbeitet — Reports verschickt, Alerts pusht, Snapshots erstellt.'),
                'steps' => [
                    [
                        'id' => 'p6-cron', 'title' => t('Cron-Jobs aktiviert prüfen'), 'time' => 3,
                        'why' => t('Ohne aktive Cron-Jobs gibt es keine Reports, keine Sharing-Scans, keine Audit-Diff-Snapshots. Voraussetzung: System-Cron alle Minute auf run-cron.php zeigt.'),
                        'how' => [
                            t('Server-Cron prüfen: `* * * * * /usr/bin/php /pfad/zu/run-cron.php`.'),
                            t('Im Tool unter /cron alle Jobs „enabled" lassen (insbesondere alert_new_defender, alert_new_risky_users, audit_diff_snapshot).'),
                            t('Einen Job manuell „Run now" auslösen — sollte „success" liefern.'),
                        ],
                        'link' => '/cron', 'link_label' => t('Cron-Übersicht'),
                    ],
                    [
                        'id' => 'p6-recipients', 'title' => t('Benachrichtigungs-Empfänger sind gesetzt'), 'time' => 2,
                        'why' => t('Alle Alerts (Risk, Defender, Service-Health) gehen an konfigurierte Empfänger. Ohne diese Adresse passiert „leise" und du erfährst von Vorfällen erst aus dem Audit-Log.'),
                        'how' => [
                            t('Mindestens eine Security-Mailbox als Empfänger.'),
                            t('Optional eine zweite Adresse für ITSM-Tickets (Jira, ServiceNow per Mail-Gateway).'),
                            t('Test-Mail aus den Einstellungen auslösen.'),
                        ],
                        'link' => '/settings', 'link_label' => t('Einstellungen (Tab „Benachrichtigungen")'),
                        'auto_done' => $autoRecipients,
                    ],
                    [
                        'id' => 'p6-executive', 'title' => t('Executive-Report monatlich an Leitung'), 'time' => 3,
                        'why' => t('Eine Seite, einmal im Monat, mit KPIs & Trends. Hält das Thema Sicherheit oben auf der GF-Agenda — ohne dass du jedes Mal Folien bauen musst.'),
                        'how' => [
                            t('Aktivieren, Empfänger setzen (Geschäftsführung, IT-Leitung).'),
                            t('Erste Vorschau im Tool ansehen, ggf. anpassen.'),
                            t('Test-Versand starten.'),
                        ],
                        'link' => '/executivereport', 'link_label' => t('Executive-Report'),
                    ],
                    [
                        'id' => 'p6-backup', 'title' => t('Backup-Status tracken'), 'time' => 3,
                        'why' => t('Microsoft 365 hat kein klassisches Backup. Wenn ein Mitarbeiter zwischen Tag 31 und Tag 60 eine Datei löscht, ist sie weg. Drittanbieter-Backup (Veeam, AvePoint, Acronis, etc.) dokumentieren.'),
                        'how' => [
                            t('Backup-Anbieter eintragen.'),
                            t('Letzten erfolgreichen Lauf einmalig manuell setzen — der Health-Score nutzt das danach.'),
                            t('Wenn noch kein Backup: jetzt evaluieren (durchschnittlich 3–6 €/User/Monat).'),
                        ],
                        'link' => '/backup', 'link_label' => t('Backup-Status'),
                        'auto_done' => $autoBackup,
                    ],
                    [
                        'id' => 'p6-workflows', 'title' => t('Mindestens einen Workflow anlegen'), 'time' => 5,
                        'why' => t('Workflows nehmen Routine ab. Beispiel: „Neuer Gast → Mail an Compliance-Team + Notification im Tool" — passiert dann automatisch, ohne dass jemand drauf achten muss.'),
                        'how' => [
                            t('Trigger „Neuer Gast-Benutzer" wählen.'),
                            t('Aktion „In-App-Benachrichtigung erzeugen" + „Mail senden" verketten.'),
                            t('Workflow aktivieren, eine Woche beobachten.'),
                        ],
                        'link' => '/workflows', 'link_label' => t('Workflows'),
                    ],
                    [
                        'id' => 'p6-sharing-monitor', 'title' => t('Sharing-Monitor mit Owner-Befragung'), 'time' => 5,
                        'why' => t('Externe Freigaben werden jeden Monat automatisch geprüft, der Owner bekommt eine Mail mit „Brauchst du das noch?". Antwortet keiner → automatischer Widerruf nach 14 Tagen.'),
                        'how' => [
                            t('Aktivieren im Sharing-Monitor.'),
                            t('Befragungs-Mail-Text personalisieren (Firmen-Logo, Tonalität).'),
                            t('Auto-Widerruf konservativ einstellen (lieber Erinnerung statt sofortiger Widerruf).'),
                        ],
                        'link' => '/sharing/monitor', 'link_label' => t('Sharing-Monitor'),
                    ],
                ],
            ],

            // ── PHASE 7 ────────────────────────────────────────────────
            [
                'id' => 'phase7', 'title' => t('Phase 7 — Wöchentliche Routine'), 'subtitle' => t('Jede Woche · ca. 5 Minuten'),
                'icon' => 'calendar-week', 'color' => '#16a34a',
                'intro' => t('Diese Liste wandert in deinen Kalender. Sie ersetzt nicht die täglichen E-Mail-Alerts — sie sorgt dafür, dass nichts „stillschweigend" stehen bleibt.'),
                'steps' => [
                    [
                        'id' => 'p7-dashboard', 'title' => t('Dashboard + Notifications-Glocke'), 'time' => 1,
                        'why' => t('KPI-Sparklines zeigen Trend-Veränderungen, die einzelne Module nicht erkennen können (z. B. „MFA-Abdeckung sinkt seit 3 Wochen langsam").'),
                        'how' => [
                            t('Sparklines auf signifikante Ausschläge nach unten prüfen.'),
                            t('Glocke (oben rechts) → letzte Events kurz durchgehen.'),
                            t('Bei rotem Pfeil: zugehöriges Modul öffnen, Ursache klären.'),
                        ],
                        'link' => '/', 'link_label' => t('Dashboard'),
                    ],
                    [
                        'id' => 'p7-risky', 'title' => t('Risk-Module bewerten'), 'time' => 2,
                        'why' => t('Riskante Anmeldungen, Defender-Alerts und MFA-Fatigue / Insider-Threat-Signale gehören jede Woche durchgesehen — auch wenn null. Dann ist es ein „kein-Vorfall"-Beleg.'),
                        'how' => [
                            t('Riskante Anmeldungen: jede bewerten („compromised" oder „dismissed").'),
                            t('Defender-Alerts: jede triagieren.'),
                            t('MFA-Fatigue: ungewöhnliche Zahlen → Auth-Strength prüfen.'),
                            t('Insider-Threat: Top-3 Kandidaten ansehen.'),
                        ],
                        'link' => '/riskysignins', 'link_label' => t('Risiko-Anmeldungen'),
                    ],
                    [
                        'id' => 'p7-forwards', 'title' => t('Auto-Forward-Audit'), 'time' => 1,
                        'why' => t('Eine Inbox-Regel, die alle Mails an eine externe Adresse weiterleitet, ist der häufigste Exfiltrations-Pfad nach Account-Übernahme. Neue Treffer → sofort prüfen.'),
                        'how' => [
                            t('Treffer auf externe Domains öffnen.'),
                            t('Mit dem User Rücksprache halten — meist „Phishing-Vorfall vor 2 Monaten".'),
                            t('Regel entfernen, MFA reset, Sessions revoken.'),
                        ],
                        'link' => '/mailboxrules', 'link_label' => t('Auto-Forward-Audit'),
                    ],
                    [
                        'id' => 'p7-stale', 'title' => t('Inaktive Konten & DLP-Vorfälle'), 'time' => 2,
                        'why' => t('Inaktive Lizenzen kosten Geld; DLP-Treffer zeigen, was Mitarbeiter wirklich versucht haben weiterzuleiten.'),
                        'how' => [
                            t('Stale-Account-Liste durchgehen, Lizenzen entziehen.'),
                            t('DLP-Treffer: ist es ein false-positive oder ein echter Vorfall? Bei echtem Vorfall: Mitarbeiter ansprechen, Policy ggf. anpassen.'),
                        ],
                        'link' => '/staleaccounts', 'link_label' => t('Inaktive Konten'),
                    ],
                    [
                        'id' => 'p7-diff', 'title' => t('Audit-Diff prüfen'), 'time' => 1,
                        'why' => t('Vergleicht den heutigen Snapshot mit dem von vor 7 Tagen. Zeigt unerwartete Änderungen sofort — z. B. „CA-Policy wurde deaktiviert" oder „Sharing-Capability wurde geöffnet".'),
                        'how' => [
                            t('Auf /auditdiff den heutigen und den vor 7 Tagen vergleichen.'),
                            t('Bei unerwarteten Änderungen → Audit-Log nach „wer war das" durchsuchen.'),
                        ],
                        'link' => '/auditdiff', 'link_label' => t('Audit-Diff'),
                    ],
                ],
            ],

            // ── PHASE 8 ────────────────────────────────────────────────
            [
                'id' => 'phase8', 'title' => t('Phase 8 — Monatlich & vierteljährlich'), 'subtitle' => t('Routine · ca. 30 Minuten/Monat'),
                'icon' => 'calendar-month', 'color' => '#7c3aed',
                'intro' => t('Was nicht jede Woche dran sein muss — aber jeden Monat oder jedes Quartal.'),
                'steps' => [
                    [
                        'id' => 'p8-pdf', 'title' => t('DSGVO/NIS-2-PDF generieren & archivieren'), 'time' => 3,
                        'why' => t('Auditfähiger Bericht mit Tenant-Stammdaten + allen Hardening-Items + Zuordnung zu DSGVO/NIS-2/BSI-Artikeln. Monatlich für die Compliance-Akte.'),
                        'how' => [
                            t('Auf /auditreport → „Als PDF speichern" im Browser.'),
                            t('PDF in den Compliance-Ordner (separat archivieren).'),
                            t('Datenschutzbeauftragten/IT-Leitung CC informieren.'),
                        ],
                        'link' => '/auditreport', 'link_label' => t('Audit-Report'),
                    ],
                    [
                        'id' => 'p8-review', 'title' => t('Access-Review-Lauf abschließen'), 'time' => 10,
                        'why' => t('Wenn der Quartals-Review läuft: Entscheidungen einsammeln, anwenden, neuen Review-Lauf für nächstes Quartal anlegen.'),
                        'how' => [
                            t('Offenen Review öffnen, alle Entscheidungen durchgehen.'),
                            t('„Apply" klicken — Entscheidungen werden ausgeführt.'),
                            t('Neuen Review für nächstes Quartal anlegen.'),
                        ],
                        'link' => '/accessreview', 'link_label' => t('Access Reviews'),
                    ],
                    [
                        'id' => 'p8-licenses', 'title' => t('Lizenz-Berater & Kosten-Trend'), 'time' => 5,
                        'why' => t('Welche Lizenzen werden tatsächlich genutzt? Wer hat zu hohe Lizenz für seinen Bedarf? Spart oft 5–15 % der M365-Kosten.'),
                        'how' => [
                            t('Lizenz-Berater → Empfehlungen anschauen.'),
                            t('Mit HR/Geschäftsführung absprechen.'),
                            t('Schrittweise umstellen, nicht alles auf einmal.'),
                        ],
                        'link' => '/licenseadvisor', 'link_label' => t('Lizenz-Berater'),
                    ],
                    [
                        'id' => 'p8-securescore', 'title' => t('Secure-Score-Trend ansehen'), 'time' => 5,
                        'why' => t('Microsofts eigene Score-Metrik. Punkte-Quelle: noch nicht umgesetzte Empfehlungen → wenig Aufwand für viel Score.'),
                        'how' => [
                            t('30/90-Tage-Trend ansehen.'),
                            t('Top-5 ungeöffnete Empfehlungen umsetzen (oft 5–10 Minuten Aufwand).'),
                            t('Score-Sprung im Executive-Report dokumentieren.'),
                        ],
                        'link' => '/securescore', 'link_label' => t('Secure Score'),
                    ],
                    [
                        'id' => 'p8-ai', 'title' => t('KI-Sicherheitsberater anstoßen'), 'time' => 5,
                        'why' => t('Konsolidierte Bewertung über BSI + NIS-2 + DSGVO + Anomalien hinweg. Nennt konkrete Top-Empfehlungen mit Quellen-Artikel.'),
                        'how' => [
                            t('Auf /ai → „Analyse starten".'),
                            t('Empfehlungen priorisieren — was hat den höchsten Impact für den geringsten Aufwand?'),
                            t('In dein Ticket-System übernehmen.'),
                        ],
                        'link' => '/ai', 'link_label' => t('KI-Berater'),
                    ],
                    [
                        'id' => 'p8-breakglass-test', 'title' => t('Break-Glass-Login testen'), 'time' => 2,
                        'why' => t('Wenn du es nicht regelmäßig testest, weißt du im Ernstfall nicht, ob es noch funktioniert. Mindestens vierteljährlich.'),
                        'how' => [
                            t('Mit dem hinterlegten FIDO2-Key einloggen.'),
                            t('Datum + Tester im Break-Glass-Modul dokumentieren.'),
                            t('Nach Test sofort wieder ausloggen.'),
                        ],
                        'link' => '/breakglass', 'link_label' => t('Break-Glass'),
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
