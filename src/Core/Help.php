<?php

namespace App\Core;

/**
 * Help::tip() renders a small "?" bubble that, on hover/focus, shows a
 * Bootstrap tooltip with a German plain-language explanation of a feature,
 * setting or policy. Texts come from a central catalogue so they can be
 * tuned in one place and re-used everywhere.
 *
 * Usage in views:
 *     <label>MFA-Methode <?= \App\Core\Help::tip('mfa_method') ?></label>
 */
class Help
{
    /**
     * @var array<string, array{title?: string, body: string, link?: string}>
     */
    private static array $catalogue = [
        // ── Identität & Authentifizierung ────────────────────────────────
        'mfa_method' => [
            'title' => 'Mehrstufige Authentifizierung',
            'body'  => 'Ein zweiter Faktor neben dem Passwort (z. B. Microsoft Authenticator App, FIDO2-Schlüssel oder SMS). Pflicht für alle Benutzer nach NIS-2 Art. 21 und BSI ORP.4.A23.',
        ],
        'conditional_access' => [
            'title' => 'Conditional Access',
            'body'  => 'Regeln, unter welchen Bedingungen ein Benutzer sich anmelden darf — z. B. nur von Firmengeräten, nur aus bestimmten Ländern, oder nur mit MFA. Das stärkste Werkzeug für identitätsbasierte Sicherheit in M365.',
        ],
        'pim' => [
            'title' => 'Privileged Identity Management',
            'body'  => 'Admin-Rollen sind nicht dauerhaft zugewiesen, sondern müssen bei Bedarf aktiviert werden (Just-in-Time). Alle Aktivierungen sind auditierbar. Reduziert das Risiko bei Account-Übernahme.',
        ],
        'break_glass' => [
            'title' => 'Break-Glass-Account',
            'body'  => 'Ein Notfall-Admin-Konto, das nur in echten Krisen verwendet wird (z. B. MFA-Dienst ausgefallen). Sollte von CA-Policies ausgenommen sein, FIDO2 oder sehr lange Passwörter nutzen, und alle Anmeldungen werden überwacht.',
        ],
        'security_defaults' => [
            'title' => 'Security Defaults',
            'body'  => 'Microsofts Voreinstellung: MFA für Admins, blockt Legacy-Auth. Gut als Start für kleine Tenants — bei produktivem Conditional Access aber ausschalten, weil sich beide gegenseitig stören können.',
        ],
        'legacy_auth' => [
            'title' => 'Legacy-Authentication',
            'body'  => 'Alte Protokolle wie POP3, IMAP, SMTP-Auth, EWS Basic. Diese unterstützen kein MFA und sind die häufigste Einbruchspforte. Sollten in jedem produktiven Tenant blockiert werden.',
        ],

        // ── Gäste & Externe ───────────────────────────────────────────────
        'guest_invite' => [
            'title' => 'Gäste-Einladungen',
            'body'  => 'Standard ist: jeder Benutzer darf Gäste einladen. Empfohlen: auf Admins oder dedizierte Rolle "Guest Inviter" beschränken, um Wildwuchs zu vermeiden.',
        ],
        'guest_role' => [
            'title' => 'Gast-Berechtigungen',
            'body'  => 'Welche Verzeichnisinformationen ein Gast sehen darf. "Restricted Guest" (= nicht eigene Mitglieder enumerieren) ist der DSGVO-konformste Wert für reine Datei-/Teams-Sharing-Szenarien.',
        ],
        'cross_tenant' => [
            'title' => 'Cross-Tenant Access',
            'body'  => 'Regelt, mit welchen anderen Microsoft-365-Mandanten dein Tenant Identitäten austauschen darf (B2B Collaboration, B2B Direct Connect für Teams Shared Channels).',
        ],

        // ── Geräte & Endpunkte ────────────────────────────────────────────
        'device_compliance' => [
            'title' => 'Geräte-Compliance',
            'body'  => 'Intune prüft, ob ein Gerät bestimmte Anforderungen erfüllt (Verschlüsselung, aktuelle Patches, kein Jailbreak). Conditional Access kann dann den Zugriff auf "compliant only" einschränken.',
        ],
        'wipe_retire' => [
            'title' => 'Wipe vs. Retire',
            'body'  => '"Retire" entfernt nur die Firmendaten (Mail, OneDrive) vom Gerät. "Wipe" setzt das Gerät komplett zurück und ist unwiderruflich — nur bei Diebstahl/Verlust einsetzen.',
        ],

        // ── Daten & Compliance ───────────────────────────────────────────
        'dlp' => [
            'title' => 'Data Loss Prevention',
            'body'  => 'Richtlinien, die das Versenden bestimmter Daten (z. B. Kreditkartennummern, IBANs, personenbezogene Daten) automatisch erkennen und blockieren oder klassifizieren.',
        ],
        'retention' => [
            'title' => 'Aufbewahrungsrichtlinien',
            'body'  => 'Wie lange Mails, Teams-Nachrichten und Dateien aufbewahrt werden müssen oder gelöscht werden sollen. Wichtig für DSGVO (Löschpflicht) und GoBD (Aufbewahrungspflicht).',
        ],
        'sensitivity_labels' => [
            'title' => 'Vertraulichkeitsbezeichnungen',
            'body'  => 'Labels wie "Vertraulich" oder "Streng vertraulich", die an Dokumente oder Mails geheftet werden und automatisch Verschlüsselung, Wasserzeichen und Rechteverwaltung anwenden.',
        ],
        'customer_lockbox' => [
            'title' => 'Customer Lockbox',
            'body'  => 'Microsoft-Mitarbeiter dürfen ohne deine explizite Freigabe nicht mehr auf deine Tenant-Daten zugreifen — auch nicht für Support-Fälle. DSGVO-Auftragsverarbeitungs-konform.',
        ],
        'dsgvo' => [
            'title' => 'DSGVO',
            'body'  => 'Datenschutz-Grundverordnung. Verpflichtet dich u. a. zu: Auftragsverarbeitungsvertrag mit Microsoft (DPA), technisch-organisatorischen Maßnahmen (Art. 32), Lösch- und Auskunftsfähigkeit, Verzeichnis der Verarbeitungstätigkeiten.',
        ],
        'nis2' => [
            'title' => 'NIS-2-Richtlinie',
            'body'  => 'EU-Richtlinie zur Cybersicherheit. Verpflichtend für mittlere/große Unternehmen kritischer Sektoren. Fordert u. a. Risikomanagement, MFA, Incident Response, Lieferkettensicherheit (Art. 21).',
        ],
        'bsi_grundschutz' => [
            'title' => 'BSI IT-Grundschutz',
            'body'  => 'Methodik des BSI für ein Informationssicherheits-Managementsystem (ISMS). Insbesondere für Bund/Länder und öffentliche Verwaltung relevant. Bausteine wie ORP.4 (Identitäts- und Zugriffsverwaltung) sind direkt umsetzbar.',
        ],

        // ── E-Mail & Phishing ────────────────────────────────────────────
        'spf_dkim_dmarc' => [
            'title' => 'SPF, DKIM, DMARC',
            'body'  => 'DNS-Einträge gegen E-Mail-Spoofing. SPF = wer darf für deine Domain senden, DKIM = Signatur, DMARC = Policy wenn SPF/DKIM fehlschlagen. Alle drei zusammen verhindern, dass Phishing in deinem Namen verschickt wird.',
        ],
        'safe_links' => [
            'title' => 'Safe Links',
            'body'  => 'Microsoft Defender for Office 365 schreibt Links in eingehenden Mails so um, dass sie beim Klick noch einmal gegen Malware-Listen geprüft werden. Schützt auch nach Zustellung.',
        ],
        'mfa_fatigue' => [
            'title' => 'MFA-Fatigue-Angriff',
            'body'  => 'Angreifer kennt das Passwort und versucht so lange MFA-Anfragen zu senden, bis der Benutzer aus Versehen "Approve" drückt. Schutz: Number-Matching aktivieren, Push limitieren, FIDO2 nutzen.',
        ],
        'phishing_sim' => [
            'title' => 'Phishing-Simulation',
            'body'  => 'Gezielte, harmlose Test-Phishings, die Microsoft Defender ATP versendet — um zu messen, wie viele Benutzer klicken bzw. Awareness-Training brauchen.',
        ],

        // ── Operations ────────────────────────────────────────────────────
        'cron' => [
            'title' => 'Cron-Jobs',
            'body'  => 'Wiederkehrende Hintergrundaufgaben (z. B. nächtliche Reports, Sharing-Scans). Werden durch einen externen Cron alle paar Minuten getriggert, der dann je nach Budget Jobs ausführt.',
        ],
        'audit_log' => [
            'title' => 'Audit-Log',
            'body'  => 'Microsoft Purview Audit zeichnet alle Aktivitäten im Tenant auf (Anmeldungen, Datei-Zugriffe, Admin-Aktionen). Aufbewahrung 90 Tage (E3/E5: 1 Jahr) — für Forensik unverzichtbar.',
        ],
        'access_review' => [
            'title' => 'Access Review',
            'body'  => 'Regelmäßige Prüfung, ob Benutzer/Gäste noch Zugriff brauchen. Vorgeschrieben durch NIS-2 (Least Privilege) und DSGVO (Erforderlichkeitsprinzip).',
        ],
        'secure_score' => [
            'title' => 'Microsoft Secure Score',
            'body'  => 'Microsofts Punktesystem für den Sicherheitsstand deines Tenants. Mehr Punkte = mehr empfohlene Maßnahmen umgesetzt. Gut als Trend-Indikator, ersetzt aber keine echte Risikoanalyse.',
        ],

        // ── Lizenzierung ─────────────────────────────────────────────────
        'license_advisor' => [
            'title' => 'Lizenz-Berater',
            'body'  => 'Analysiert, welche Benutzer welche Features tatsächlich nutzen und schlägt günstigere Lizenz-Stufen vor (z. B. F1 statt E3 für reine Frontline-Worker).',
        ],

        // ── Allgemeine UI-Begriffe ───────────────────────────────────────
        'graph_api' => [
            'title' => 'Microsoft Graph API',
            'body'  => 'Die zentrale REST-Schnittstelle von Microsoft 365. Dieses Tool greift ausschließlich über die Graph API auf deinen Tenant zu — keine direkten DB-Zugriffe, kein Skripting in deinem Tenant.',
        ],
        'csrf' => [
            'title' => 'CSRF-Schutz',
            'body'  => 'Cross-Site Request Forgery: ein Token wird in jedes Formular eingebettet und beim Submit verifiziert, damit kein anderer Tab/Site eine Aktion in deinem Namen auslösen kann.',
        ],
        'rest_api' => [
            'title' => 'REST-API dieses Tools',
            'body'  => 'Externe Werkzeuge (PowerBI, Grafana, n8n) können tenant-relevante KPIs als JSON abrufen. API-Keys verwalten in den Einstellungen, Spezifikation unter /api/docs.',
        ],
        'sparkline' => [
            'title' => 'Sparkline',
            'body'  => 'Mini-Diagramm der letzten 7 Tage neben einer Kennzahl — zeigt den Trend ohne Klick: steigt / fällt / stabil.',
        ],
        'audit_diff' => [
            'title' => 'Audit-Diff',
            'body'  => 'Snapshots der Tenant-Einstellungen werden täglich gespeichert. Diff zeigt, was sich seit gestern/letzter Woche/letztem Monat geändert hat — perfekt für Auditoren und Übergaben.',
        ],
        'workflow_automation' => [
            'title' => 'Workflow-Automatisierung',
            'body'  => 'Trigger + Aktion: z. B. "neuer Benutzer in Gruppe X" → "Lizenz Y zuweisen + Begrüßungsmail senden + OneDrive vorbereiten". Leichtgewichtige Alternative zu Power Automate für M365-Standard-Abläufe.',
        ],
        'compliance_profile' => [
            'title' => 'Compliance-Profil',
            'body'  => 'Branchenspezifische Härtungs-Voreinstellungen (Gesundheitswesen, Finanzwesen, öffentlicher Sektor). Wendet mit einem Klick die für deine Compliance-Anforderung typischen Hardening-Aktionen an.',
        ],
        'setup_wizard' => [
            'title' => 'Einrichtungs-Assistent',
            'body'  => 'Fünf-Schritte-Tour für die ersten 10 Minuten nach Erstinstallation: Tenant-Verbindung, Berechtigungen, Benachrichtigungen, Branding, Compliance-Profil.',
        ],
        'bestpractice' => [
            'title' => 'Best-Practice-Leitfaden',
            'body'  => 'Interaktiver Schritt-für-Schritt-Leitfaden zur Tenant-Härtung — 8 Phasen, jede mit konkreten Aufgaben, Zeitabschätzung und direktem Modul-Link. Schritte können abgehakt werden, Fortschritt wird gespeichert.',
        ],
        'notifications' => [
            'title' => 'In-App-Benachrichtigungen',
            'body'  => 'Die Glocke oben rechts zeigt Tenant-Ereignisse (neue Risiko-Anmeldungen, Hardening-Aktionen, Cron-Fehler) seit deinem letzten Besuch. Klick markiert als gelesen.',
        ],
    ];

    /**
     * Render an inline help-bubble as HTML. The bubble itself is a small
     * Bootstrap-Icons "?" inside a <span> with data attributes that the
     * client-side initializer (see base.php) wires up to Bootstrap tooltips.
     *
     * Returns an empty string if the key is unknown — never throws, never
     * outputs an "[unknown]" placeholder that could distract the user.
     */
    public static function tip(string $key, string $placement = 'top'): string
    {
        $entry = self::$catalogue[$key] ?? null;
        if ($entry === null) {
            return '';
        }
        $title = isset($entry['title']) && $entry['title'] !== '' ? t($entry['title']) : '';
        $body  = t($entry['body']);
        $html  = ($title !== '' ? '<strong>' . htmlspecialchars($title) . '</strong><br>' : '')
               . nl2br(htmlspecialchars($body));
        $safe  = htmlspecialchars($html, ENT_QUOTES);
        $placement = in_array($placement, ['top', 'bottom', 'left', 'right'], true) ? $placement : 'top';

        return '<span class="help-tip" data-bs-toggle="tooltip" data-bs-html="true" data-bs-placement="'
             . $placement . '" data-bs-title="' . $safe . '" tabindex="0" role="button" aria-label="Hilfe"><i class="bi bi-question-circle"></i></span>';
    }

    /**
     * Return raw text for a key — used by REST-API doc auto-generation
     * and by Setup-Wizard step descriptions that need to embed full text
     * (not just a tooltip).
     */
    public static function text(string $key): string
    {
        $entry = self::$catalogue[$key] ?? null;
        return $entry === null ? '' : t($entry['body']);
    }

    public static function title(string $key): string
    {
        $entry = self::$catalogue[$key] ?? null;
        return $entry === null || !isset($entry['title']) ? '' : t($entry['title']);
    }

    /**
     * @return array<string, array{title?: string, body: string, link?: string}>
     */
    public static function all(): array
    {
        return self::$catalogue;
    }
}
