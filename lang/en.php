<?php

/**
 * English translations.
 *
 * Keys are the exact German source strings used in the code/views. Whenever a
 * German string is wrapped in t()/te() and not yet present here, the German
 * original is shown as a graceful fallback — so this file can grow
 * incrementally without breaking the UI.
 *
 * @return array<string,string>
 */
return [
    // ── Sidebar: standalone items ───────────────────────────────────────────
    'Dashboard'              => 'Dashboard',
    'Favoriten'              => 'Favorites',
    'Modul-Übersicht'        => 'Module overview',
    'Bereiche'               => 'Sections',
    'Hilfe'                  => 'Help',
    'Handbuch'               => 'Manual',

    // ── Navigation hubs ─────────────────────────────────────────────────────
    'Identität & Konten'        => 'Identity & Accounts',
    'Zugriff & Privilegien'     => 'Access & Privileges',
    'Bedrohungen & Response'    => 'Threats & Response',
    'E-Mail-Sicherheit'         => 'Email Security',
    'Teams, Sharing & Speicher' => 'Teams, Sharing & Storage',
    'Information Protection'     => 'Information Protection',
    'Härtung & Posture'         => 'Hardening & Posture',
    'Compliance & Audit'        => 'Compliance & Audit',
    'Lizenzen & Berichte'       => 'Licenses & Reports',
    'Apps & Automatisierung'    => 'Apps & Automation',
    'Administration'            => 'Administration',

    // ── Navigation module labels ────────────────────────────────────────────
    'Benutzer'                  => 'Users',
    'Gastbenutzer'              => 'Guest users',
    'Gruppen & Teams'           => 'Groups & Teams',
    'Onboarding'                => 'Onboarding',
    'Offboarding'               => 'Offboarding',
    'MFA-Methoden'              => 'MFA methods',
    'Passwort-Ablauf'           => 'Password expiry',
    'Inaktive Konten'           => 'Inactive accounts',
    'Papierkorb'                => 'Recycle bin',
    'Conditional Access'        => 'Conditional Access',
    'Named Locations'           => 'Named Locations',
    'Authentifizierungsmethoden' => 'Authentication methods',
    'Auth-Strength'             => 'Auth Strength',
    'Token-Lifetime'            => 'Token Lifetime',
    'Cross-Tenant-Access'       => 'Cross-Tenant Access',
    'Identity Provider Trust'   => 'Identity Provider Trust',
    'Admin-Rollen'              => 'Admin roles',
    'PIM (JIT-Admin)'           => 'PIM (JIT admin)',
    'PIM-Einstellungen'         => 'PIM settings',
    'Break-Glass-Accounts'      => 'Break-glass accounts',
    'Secure Score'              => 'Secure Score',
    'Defender Alerts'           => 'Defender Alerts',
    'Risiko-Anmeldungen'        => 'Risky sign-ins',
    'MFA-Fatigue'               => 'MFA Fatigue',
    'Insider-Threat'            => 'Insider Threat',
    'Phishing-Simulationen'     => 'Phishing simulations',
    'Postfächer'                => 'Mailboxes',
    'Weiterleitungen & Regeln'  => 'Forwarding & rules',
    'Mail Flow & Schutz'        => 'Mail flow & protection',
    'Domain Health'             => 'Domain Health',
    'EXO Migration'             => 'EXO Migration',
    'Message Center'            => 'Message Center',
    'Teams-Übersicht'           => 'Teams overview',
    'Teams-Nutzung'             => 'Teams usage',
    'Teams Governance'          => 'Teams Governance',
    'OneDrive'                  => 'OneDrive',
    'SharePoint'                => 'SharePoint',
    'Freigaben'                 => 'Sharing',
    'Sensitivity Labels'        => 'Sensitivity Labels',
    'DLP-Richtlinien'           => 'DLP policies',
    'DLP-Vorfälle'              => 'DLP incidents',
    'Aufbewahrung (Retention)'  => 'Retention',
    'eDiscovery-Fälle'          => 'eDiscovery cases',
    'Security Center'           => 'Security Center',
    'Security Posture'          => 'Security Posture',
    'DSGVO-Status'              => 'GDPR status',
    'Härtungs-Leitfaden'        => 'Hardening guide',
    'Compliance-Profile'        => 'Compliance profiles',
    'Customer Lockbox'          => 'Customer Lockbox',
    'Backup-Status'             => 'Backup status',
    'Geräte'                    => 'Devices',
    'Access Reviews'            => 'Access Reviews',
    'Audit-Log'                 => 'Audit log',
    'Audit-Diff'                => 'Audit Diff',
    'DSGVO/NIS-2 Report'        => 'GDPR/NIS-2 report',
    'Sign-in-Log'               => 'Sign-in log',
    'Lizenzen'                  => 'Licenses',
    'Lizenz-Berater'            => 'License advisor',
    'Lizenzkosten'              => 'License costs',
    'Nutzung & Adoption'        => 'Usage & adoption',
    'Executive-Report'          => 'Executive report',
    'Dienststatus'              => 'Service health',
    'App-Registrierungen'       => 'App registrations',
    'OAuth-/Enterprise-Apps'    => 'OAuth / Enterprise apps',
    'Lifecycle Workflows'       => 'Lifecycle Workflows',
    'KI-Berater'                => 'AI advisor',
    'Workflows'                 => 'Workflows',
    'Cron & Automatisierung'    => 'Cron & automation',
    'Einstellungen'             => 'Settings',
    'Benutzer-Zugang'           => 'User access',
    'Einrichtungs-Assistent'    => 'Setup wizard',
    'API-Schlüssel'             => 'API keys',
    'API-Dokumentation'         => 'API documentation',
    'Updates'                   => 'Updates',
    'App Audit-Log'             => 'App audit log',
    '2FA-Einstellungen'         => '2FA settings',

    // ── Topbar / global chrome ──────────────────────────────────────────────
    'Abmelden'                          => 'Sign out',
    'Sidebar ein-/ausblenden'           => 'Toggle sidebar',
    'Schnellsuche (Strg+K)'             => 'Quick search (Ctrl+K)',
    'Suchen…'                           => 'Search…',
    'Letzter Datenabruf'                => 'Last data refresh',
    'Zu Favoriten hinzufügen'           => 'Add to favorites',
    'Daten aktualisieren'               => 'Refresh data',
    'Seite drucken / als PDF speichern' => 'Print page / save as PDF',
    'Benachrichtigungen'                => 'Notifications',
    'Alle anzeigen'                     => 'Show all',
    'Keine Ereignisse'                  => 'No events',
    'Operator'                          => 'Operator',
    'Schnellsuche'                      => 'Quick search',
    'Seite oder Einstellung suchen…'    => 'Search page or setting…',
    'Weitere Module'                    => 'More modules',
    'Mehr'                              => 'More',
    'Entwickelt von'                    => 'Developed by',
    'Sprache'                           => 'Language',
    'Sprache wechseln'                  => 'Change language',
    'Standardsprache der Oberfläche. Jeder Nutzer kann sie über das Sprachmenü oben rechts wechseln.'
        => 'Default interface language. Each user can change it via the language menu at the top right.',

    // Relative time suffixes used in the notification dropdown.
    'gerade eben' => 'just now',

    // ── Module overview (/overview) ─────────────────────────────────────────
    'Module filtern …'                  => 'Filter modules …',
    'Admin'                             => 'Admin',
    'Alle :count Module auf einen Blick.' => 'All :count modules at a glance.',
    'Diese Seite listet jeden Bereich des Tools gruppiert auf — nutze sie zum schnellen Einstieg oder die Suche oben, um direkt zu einem Modul zu springen.'
        => 'This page lists every area of the tool grouped together — use it as a quick entry point, or the search at the top to jump straight to a module.',

    // ── Pager / table chrome (also surfaced to JS) ──────────────────────────
    'Keine Einträge gefunden'           => 'No entries found',
    'von'                               => 'of',

    // ── Login ───────────────────────────────────────────────────────────────
    'Anmelden'                                            => 'Sign in',
    'Administrator-Anmeldung'                             => 'Administrator sign-in',
    'Deine Sitzung ist abgelaufen. Bitte melde dich erneut an.' => 'Your session has expired. Please sign in again.',
    'Benutzername'                                        => 'Username',
    'Passwort'                                            => 'Password',
    'oder'                                                => 'or',
    'Mit Microsoft anmelden'                              => 'Sign in with Microsoft',
];
