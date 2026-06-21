<?php

/**
 * English translations for the Settings backend layer
 * (PermissionCheckerService, SettingsController, UserManagementController and
 * the Core\Help tooltip catalogue). Keys are the exact German source strings
 * passed to t(); only human-readable display values are translated — Graph
 * permission scope identifiers (e.g. 'User.Read.All') stay untouched in code.
 *
 * @return array<string,string>
 */
return [

    // ── PermissionCheckerService: section headers ────────────────────────
    'Verzeichnis'             => 'Directory',
    'Lizenzen'                => 'Licenses',
    'Speicher & Freigaben'    => 'Storage & Sharing',
    'Exchange & Kommunikation' => 'Exchange & Communication',
    'Berichte'                => 'Reports',
    'Sicherheit'              => 'Security',
    'Geräte & Compliance'     => 'Devices & Compliance',
    'Compliance & Schutz'     => 'Compliance & Protection',
    'Administration'          => 'Administration',

    // ── PermissionCheckerService: permission descriptions (desc) ─────────
    'Benutzerprofile lesen'                              => 'Read user profiles',
    'Benutzer anlegen, bearbeiten und löschen'           => 'Create, edit and delete users',
    'Benutzerkonten aktivieren/deaktivieren'             => 'Enable/disable user accounts',
    'Authentifizierungsmethoden verwalten'               => 'Manage authentication methods',
    'Authentifizierungsmethoden lesen und schreiben'     => 'Read and write authentication methods',
    'Verzeichnis lesen (Gruppen, Rollen, Geräte)'        => 'Read the directory (groups, roles, devices)',
    'Gruppen lesen und schreiben'                        => 'Read and write groups',
    'Organisationsinformationen lesen'                   => 'Read organisation information',
    'Lizenzen zuweisen und entfernen'                    => 'Assign and remove licenses',
    'SharePoint-Sites lesen'                             => 'Read SharePoint sites',
    'Dateien und Freigabeberechtigungen verwalten'       => 'Manage files and sharing permissions',
    'SharePoint-Tenant-Einstellungen lesen und schreiben' => 'Read and write SharePoint tenant settings',
    'Authorization-Policy ändern (Gast-Einladungs-Regeln, App-Consent-Defaults, Gast-Rolle, User-Default-Permissions)'
        => 'Modify the authorization policy (guest invitation rules, app consent defaults, guest role, user default permissions)',
    'Security Defaults ein-/ausschalten'                 => 'Turn security defaults on/off',
    'Basisinfos zu Postfächern lesen'                    => 'Read basic mailbox information',
    'Mailbox-Inhalte und Inbox-Regeln tenant-weit lesen' => 'Read mailbox contents and inbox rules tenant-wide',
    'Postfacheinstellungen lesen und schreiben'          => 'Read and write mailbox settings',
    'Aktivitäts- und Nutzungsberichte lesen'             => 'Read activity and usage reports',
    'Audit-Log und Anmeldeprotokolle lesen'              => 'Read the audit log and sign-in logs',
    'Alle Richtlinien lesen (CA, Named Locations, Auth-Richtlinien)'
        => 'Read all policies (CA, named locations, authentication policies)',
    'Conditional Access Richtlinien lesen und schreiben' => 'Read and write Conditional Access policies',
    'Authentifizierungsmethoden-Richtlinie lesen und schreiben'
        => 'Read and write the authentication methods policy',
    'PIM-Rollenrichtlinien (Aktivierungsregeln) lesen'   => 'Read PIM role policies (activation rules)',
    'Sicherheitswarnungen lesen und verwalten'           => 'Read and manage security alerts',
    'Risiko-Benutzer lesen (Entra ID Protection)'        => 'Read risky users (Entra ID Protection)',
    'Risiko-Benutzer verwalten (bestätigen / verwerfen)' => 'Manage risky users (confirm / dismiss)',
    'Risiko-Erkennungen lesen (Entra ID Protection)'     => 'Read risk detections (Entra ID Protection)',
    'Sicherheitsereignisse und Secure Score lesen'       => 'Read security events and Secure Score',
    'Admin-Rollenzuweisungen + PIM lesen'                => 'Read admin role assignments + PIM',
    'Admin-Rollenzuweisungen lesen und schreiben'        => 'Read and write admin role assignments',
    'App-Registrierungen und Service-Principals lesen'   => 'Read app registrations and service principals',
    'App-Registrierungen lesen und verwalten'            => 'Read and manage app registrations',
    'Defender Attack-Simulation-Daten lesen'             => 'Read Defender attack simulation data',
    'Entra-ID-Governance-Lifecycle-Workflows lesen'      => 'Read Entra ID Governance lifecycle workflows',
    'Externe Identity Providers lesen'                   => 'Read external identity providers',
    'Teams-App-Katalog lesen'                            => 'Read the Teams app catalog',
    'Tenant-weite Teams-Einstellungen lesen'             => 'Read tenant-wide Teams settings',
    'Intune-Geräte lesen und verwalten'                  => 'Read and manage Intune devices',
    'Privilegierte Intune-Aktionen (Sync, Retire, Wipe)' => 'Privileged Intune actions (sync, retire, wipe)',
    'BitLocker-Wiederherstellungsschlüssel lesen'        => 'Read BitLocker recovery keys',
    'Information-Protection-Richtlinien & Sensitivity Labels lesen'
        => 'Read information protection policies & sensitivity labels',
    'eDiscovery-Fälle (Aufbewahrungsrichtlinien) lesen'  => 'Read eDiscovery cases (retention policies)',
    'Domains des Tenants lesen'                          => 'Read the tenant domains',
    'Dienststatus lesen'                                 => 'Read service health',
    'Message Center Nachrichten lesen'                   => 'Read Message Center posts',

    // ── PermissionCheckerService: feature labels (features[]) ────────────
    'Benutzer (Liste & Detail)'                          => 'Users (list & detail)',
    'MFA-Methoden'                                       => 'MFA methods',
    'Passwort-Ablauf'                                    => 'Password expiry',
    'Inaktive Konten'                                    => 'Inactive accounts',
    'Sign-in-Log (Benutzerfilter)'                       => 'Sign-in log (user filter)',
    'Offboarding'                                        => 'Offboarding',
    'Externe Weiterleitungen'                            => 'External forwarding',
    'Onboarding (neuen Benutzer anlegen)'                => 'Onboarding (create new user)',
    'Benutzerdaten ändern (Job-Titel, Abteilung, Telefon)' => 'Edit user data (job title, department, phone)',
    'Benutzer aktivieren/deaktivieren'                   => 'Enable/disable user',
    'Offboarding (Konto deaktivieren)'                   => 'Offboarding (disable account)',
    'MFA zurücksetzen'                                   => 'Reset MFA',
    'Anmeldesitzungen widerrufen'                        => 'Revoke sign-in sessions',
    'MFA-Methoden (Detail)'                              => 'MFA methods (detail)',
    'Gruppen & Teams'                                    => 'Groups & Teams',
    'Admin-Rollen'                                       => 'Admin roles',
    'Gastbenutzer'                                       => 'Guest users',
    'Lizenz-Berater'                                     => 'License advisor',
    'App-Registrierungen'                                => 'App registrations',
    'Gruppe anlegen'                                     => 'Create group',
    'Gruppe löschen'                                     => 'Delete group',
    'Mitglieder hinzufügen/entfernen'                    => 'Add/remove members',
    'Gruppenbesitzer verwalten'                          => 'Manage group owners',
    'Dashboard (Mandanteninfo)'                          => 'Dashboard (tenant info)',
    'Lizenz-Ablauf (beta)'                               => 'License expiry (beta)',
    'Lizenz zuweisen'                                    => 'Assign license',
    'Lizenz entfernen'                                   => 'Remove license',
    'Offboarding (Lizenzen entfernen)'                   => 'Offboarding (remove licenses)',
    'SharePoint'                                         => 'SharePoint',
    'OneDrive'                                           => 'OneDrive',
    'Freigaben'                                          => 'Shares',
    'Externe Freigabe widerrufen'                        => 'Revoke external share',
    'Freigaben-Monitor'                                  => 'Sharing monitor',
    'Freigaberichtlinien lesen'                          => 'Read sharing policies',
    'Freigaberichtlinien setzen'                         => 'Set sharing policies',
    'Tenant-Härtung (Sharing-Toggles)'                   => 'Tenant hardening (sharing toggles)',
    'Security Center: Gast-Einladungen einschränken'     => 'Security Center: restrict guest invitations',
    'Security Center: Gast-Rolle & User-Standardrechte'  => 'Security Center: guest role & user default permissions',
    'Security Center: App-Consent'                       => 'Security Center: app consent',
    'Security Center: Security Defaults umschalten'      => 'Security Center: toggle security defaults',
    'Postfächer (Liste)'                                 => 'Mailboxes (list)',
    'Postfach-Ordner'                                    => 'Mailbox folders',
    'Auto-Forward-Audit (Mailbox-Regeln scannen)'        => 'Auto-forward audit (scan mailbox rules)',
    'Postfach-Detail'                                    => 'Mailbox detail',
    'Auto-Antwort setzen'                                => 'Set auto-reply',
    'Weiterleitung setzen/entfernen'                     => 'Set/remove forwarding',
    'Freigegebene Postfächer'                            => 'Shared mailboxes',
    'Teams-Nutzung'                                      => 'Teams usage',
    'Adoptions-Report'                                   => 'Adoption report',
    'Inaktive Gruppen'                                   => 'Inactive groups',
    'OneDrive-Bericht'                                   => 'OneDrive report',
    'Security Posture (MFA-Registrierungsrate, SSPR-Adoption, Admin-MFA-Prüfung)'
        => 'Security posture (MFA registration rate, SSPR adoption, admin MFA check)',
    'Audit-Log'                                          => 'Audit log',
    'Sign-in-Log'                                        => 'Sign-in log',
    'Benutzer Sign-in-Verlauf'                           => 'User sign-in history',
    'Conditional Access (Übersicht & Analyse)'           => 'Conditional Access (overview & analysis)',
    'Named Locations'                                    => 'Named locations',
    'Authentifizierungsmethoden (Anzeige)'               => 'Authentication methods (display)',
    'Security Posture (Security Defaults, App-Zustimmungsrichtlinie, Gasteinladungsrichtlinie, CA-Sitzungssteuerung)'
        => 'Security posture (security defaults, app consent policy, guest invitation policy, CA session controls)',
    'Sicherheit (CA-Richtlinien anzeigen)'               => 'Security (display CA policies)',
    'CA-Richtlinie aktivieren/deaktivieren'              => 'Enable/disable CA policy',
    'Authentifizierungsmethoden aktivieren/deaktivieren (FIDO2, Authenticator, SMS, Voice …)'
        => 'Enable/disable authentication methods (FIDO2, Authenticator, SMS, Voice …)',
    'PIM-Einstellungen (MFA-/Begründungs-/Genehmigungspflicht, max. Aktivierungsdauer je Rolle)'
        => 'PIM settings (required MFA/justification/approval, max. activation duration per role)',
    'Defender Alerts'                                    => 'Defender alerts',
    'Mail Flow & Schutz (Alerts)'                        => 'Mail flow & protection (alerts)',
    'Alert auflösen'                                     => 'Resolve alert',
    'Risiko-Anmeldungen (Liste der gefährdeten Benutzer)' => 'Risky sign-ins (list of at-risk users)',
    'Dashboard-Kachel "Risikobenutzer"'                  => 'Dashboard tile “Risky users”',
    'Risiko bestätigen'                                  => 'Confirm risk',
    'Risiko verwerfen'                                   => 'Dismiss risk',
    'Risiko-Anmeldungen (Erkennungen / Risk Detections)' => 'Risky sign-ins (detections / risk detections)',
    'Secure Score'                                       => 'Secure Score',
    'Security Posture'                                   => 'Security posture',
    'PIM-Übersicht (JIT-Admin)'                          => 'PIM overview (JIT admin)',
    'Admin-Rollen lesen (read-only)'                     => 'Read admin roles (read-only)',
    'Admin-Rollen (Übersicht)'                           => 'Admin roles (overview)',
    'Admin-Rolle zuweisen'                               => 'Assign admin role',
    'Admin-Rolle entfernen'                              => 'Remove admin role',
    'Security Posture (Admin-MFA-Prüfung, PIM-Adoption)' => 'Security posture (admin MFA check, PIM adoption)',
    'OAuth-App-Audit (Enterprise Apps Inventur + Risk-Score)'
        => 'OAuth app audit (enterprise apps inventory + risk score)',
    'App-Registrierungen (Detail)'                       => 'App registrations (detail)',
    'App-Secret hinzufügen'                              => 'Add app secret',
    'App-Secret löschen'                                 => 'Delete app secret',
    'Enterprise Apps (Service Principals)'               => 'Enterprise apps (service principals)',
    'Phishing-Simulationen (durchgeführte Kampagnen, Klick-/Compromised-Quote)'
        => 'Phishing simulations (completed campaigns, click/compromised rate)',
    'Lifecycle-Workflows-Übersicht (Joiner/Mover/Leaver)' => 'Lifecycle workflows overview (joiner/mover/leaver)',
    'Identity Provider Trust (Google, Facebook, SAML/WS-Fed)'
        => 'Identity provider trust (Google, Facebook, SAML/WS-Fed)',
    'Teams Governance (App-Übersicht)'                   => 'Teams governance (app overview)',
    'Teams Policies (App-Setup-Richtlinien)'             => 'Teams policies (app setup policies)',
    'Teams Governance'                                   => 'Teams governance',
    'Teams Policies (Tenant-Einstellungen)'              => 'Teams policies (tenant settings)',
    'Geräte (Liste & Detail)'                            => 'Devices (list & detail)',
    'Gerät zurücksetzen (Retire)'                        => 'Reset device (retire)',
    'Gerät wischen (Wipe)'                               => 'Wipe device',
    'Gerät synchronisieren (syncDevice)'                 => 'Sync device (syncDevice)',
    'Retire / Wipe'                                      => 'Retire / Wipe',
    'Gerät: BitLocker-Schlüssel anzeigen'                => 'Device: show BitLocker key',
    'Sensitivity Labels (Übersicht & Policy-Settings)'   => 'Sensitivity labels (overview & policy settings)',
    'DLP-Richtlinien (Label-Übersicht)'                  => 'DLP policies (label overview)',
    'Aufbewahrungsrichtlinien (eDiscovery-Fälle)'        => 'Retention policies (eDiscovery cases)',
    'Domain Health (DNS/DKIM/DMARC-Checks)'              => 'Domain health (DNS/DKIM/DMARC checks)',
    'Dienststatus'                                       => 'Service health',
    'Mail Flow & Schutz (Exchange-Status)'               => 'Mail flow & protection (Exchange status)',
    'Message Center'                                     => 'Message Center',

    // ── SettingsController: flash messages ───────────────────────────────
    'Passwörter stimmen nicht überein.'                  => 'Passwords do not match.',
    'Einstellungen gespeichert.'                         => 'Settings saved.',
    'Fehler: :msg'                                       => 'Error: :msg',
    'Cache erfolgreich geleert.'                         => 'Cache cleared successfully.',
    'Keine Alert-E-Mail-Adresse konfiguriert.'           => 'No alert email address configured.',
    'Test-E-Mail gesendet an :to'                        => 'Test email sent to :to',
    'E-Mail-Versand fehlgeschlagen. SMTP-Einstellungen prüfen.'
        => 'Email delivery failed. Check the SMTP settings.',
    'Token gelöscht — ein neues wird beim nächsten API-Aufruf geholt.'
        => 'Token deleted — a new one will be fetched on the next API call.',
    'Lizenzpreise gespeichert.'                          => 'License prices saved.',
    'Kein Setup-Geheimnis gefunden. Bitte beginne von vorne.'
        => 'No setup secret found. Please start over.',
    'Ungültiger Code. Bitte überprüfe deine Authenticator-App und versuche es erneut.'
        => 'Invalid code. Please check your authenticator app and try again.',
    'Falsches Passwort — 2FA wurde nicht deaktiviert.'   => 'Wrong password — 2FA was not disabled.',
    '2FA wurde erfolgreich deaktiviert.'                 => '2FA was disabled successfully.',

    // ── UserManagementController: flash messages ─────────────────────────
    'Ungültige E-Mail-Adresse / UPN.'                    => 'Invalid email address / UPN.',
    ':upn wurde hinzugefügt.'                            => ':upn was added.',
    'Benutzer aktualisiert.'                             => 'User updated.',
    'Benutzer entfernt.'                                 => 'User removed.',

    // ── Core\Help: tooltip catalogue (title + body) ──────────────────────
    'Mehrstufige Authentifizierung'                      => 'Multi-factor authentication',
    'Ein zweiter Faktor neben dem Passwort (z. B. Microsoft Authenticator App, FIDO2-Schlüssel oder SMS). Pflicht für alle Benutzer nach NIS-2 Art. 21 und BSI ORP.4.A23.'
        => 'A second factor in addition to the password (e.g. the Microsoft Authenticator app, a FIDO2 key or SMS). Mandatory for all users under NIS-2 Art. 21 and BSI ORP.4.A23.',
    'Conditional Access'                                 => 'Conditional Access',
    'Regeln, unter welchen Bedingungen ein Benutzer sich anmelden darf — z. B. nur von Firmengeräten, nur aus bestimmten Ländern, oder nur mit MFA. Das stärkste Werkzeug für identitätsbasierte Sicherheit in M365.'
        => 'Rules defining the conditions under which a user may sign in — e.g. only from corporate devices, only from certain countries, or only with MFA. The most powerful tool for identity-based security in M365.',
    'Privileged Identity Management'                     => 'Privileged Identity Management',
    'Admin-Rollen sind nicht dauerhaft zugewiesen, sondern müssen bei Bedarf aktiviert werden (Just-in-Time). Alle Aktivierungen sind auditierbar. Reduziert das Risiko bei Account-Übernahme.'
        => 'Admin roles are not assigned permanently but must be activated on demand (just-in-time). All activations are auditable. Reduces the risk in the event of account takeover.',
    'Break-Glass-Account'                                => 'Break-glass account',
    'Ein Notfall-Admin-Konto, das nur in echten Krisen verwendet wird (z. B. MFA-Dienst ausgefallen). Sollte von CA-Policies ausgenommen sein, FIDO2 oder sehr lange Passwörter nutzen, und alle Anmeldungen werden überwacht.'
        => 'An emergency admin account used only in genuine crises (e.g. when the MFA service is down). It should be excluded from CA policies, use FIDO2 or very long passwords, and all of its sign-ins are monitored.',
    'Security Defaults'                                  => 'Security Defaults',
    'Microsofts Voreinstellung: MFA für Admins, blockt Legacy-Auth. Gut als Start für kleine Tenants — bei produktivem Conditional Access aber ausschalten, weil sich beide gegenseitig stören können.'
        => 'Microsoft’s default setting: MFA for admins, blocks legacy auth. A good starting point for small tenants — but turn it off once Conditional Access is in production, as the two can interfere with each other.',
    'Legacy-Authentication'                              => 'Legacy authentication',
    'Alte Protokolle wie POP3, IMAP, SMTP-Auth, EWS Basic. Diese unterstützen kein MFA und sind die häufigste Einbruchspforte. Sollten in jedem produktiven Tenant blockiert werden.'
        => 'Old protocols such as POP3, IMAP, SMTP auth and EWS Basic. They do not support MFA and are the most common entry point for breaches. They should be blocked in every production tenant.',
    'Gäste-Einladungen'                                  => 'Guest invitations',
    'Standard ist: jeder Benutzer darf Gäste einladen. Empfohlen: auf Admins oder dedizierte Rolle "Guest Inviter" beschränken, um Wildwuchs zu vermeiden.'
        => 'By default, every user may invite guests. Recommended: restrict this to admins or a dedicated “Guest Inviter” role to avoid sprawl.',
    'Gast-Berechtigungen'                                => 'Guest permissions',
    'Welche Verzeichnisinformationen ein Gast sehen darf. "Restricted Guest" (= nicht eigene Mitglieder enumerieren) ist der DSGVO-konformste Wert für reine Datei-/Teams-Sharing-Szenarien.'
        => 'Which directory information a guest may see. “Restricted Guest” (= cannot enumerate other members) is the most GDPR-compliant value for pure file/Teams sharing scenarios.',
    'Cross-Tenant Access'                                => 'Cross-tenant access',
    'Regelt, mit welchen anderen Microsoft-365-Mandanten dein Tenant Identitäten austauschen darf (B2B Collaboration, B2B Direct Connect für Teams Shared Channels).'
        => 'Controls which other Microsoft 365 tenants your tenant may exchange identities with (B2B collaboration, B2B direct connect for Teams shared channels).',
    'Geräte-Compliance'                                  => 'Device compliance',
    'Intune prüft, ob ein Gerät bestimmte Anforderungen erfüllt (Verschlüsselung, aktuelle Patches, kein Jailbreak). Conditional Access kann dann den Zugriff auf "compliant only" einschränken.'
        => 'Intune checks whether a device meets certain requirements (encryption, up-to-date patches, no jailbreak). Conditional Access can then restrict access to “compliant only”.',
    'Wipe vs. Retire'                                    => 'Wipe vs. retire',
    '"Retire" entfernt nur die Firmendaten (Mail, OneDrive) vom Gerät. "Wipe" setzt das Gerät komplett zurück und ist unwiderruflich — nur bei Diebstahl/Verlust einsetzen.'
        => '“Retire” removes only corporate data (mail, OneDrive) from the device. “Wipe” resets the device completely and is irreversible — use it only in case of theft/loss.',
    'Data Loss Prevention'                               => 'Data Loss Prevention',
    'Richtlinien, die das Versenden bestimmter Daten (z. B. Kreditkartennummern, IBANs, personenbezogene Daten) automatisch erkennen und blockieren oder klassifizieren.'
        => 'Policies that automatically detect and block or classify the sending of certain data (e.g. credit card numbers, IBANs, personal data).',
    'Aufbewahrungsrichtlinien'                           => 'Retention policies',
    'Wie lange Mails, Teams-Nachrichten und Dateien aufbewahrt werden müssen oder gelöscht werden sollen. Wichtig für DSGVO (Löschpflicht) und GoBD (Aufbewahrungspflicht).'
        => 'How long emails, Teams messages and files must be retained or should be deleted. Important for the GDPR (obligation to delete) and GoBD (obligation to retain).',
    'Vertraulichkeitsbezeichnungen'                      => 'Sensitivity labels',
    'Labels wie "Vertraulich" oder "Streng vertraulich", die an Dokumente oder Mails geheftet werden und automatisch Verschlüsselung, Wasserzeichen und Rechteverwaltung anwenden.'
        => 'Labels such as “Confidential” or “Highly confidential” that are attached to documents or emails and automatically apply encryption, watermarks and rights management.',
    'Customer Lockbox'                                   => 'Customer Lockbox',
    'Microsoft-Mitarbeiter dürfen ohne deine explizite Freigabe nicht mehr auf deine Tenant-Daten zugreifen — auch nicht für Support-Fälle. DSGVO-Auftragsverarbeitungs-konform.'
        => 'Microsoft employees may no longer access your tenant data without your explicit approval — not even for support cases. Compliant with GDPR data processing requirements.',
    'DSGVO'                                              => 'GDPR',
    'Datenschutz-Grundverordnung. Verpflichtet dich u. a. zu: Auftragsverarbeitungsvertrag mit Microsoft (DPA), technisch-organisatorischen Maßnahmen (Art. 32), Lösch- und Auskunftsfähigkeit, Verzeichnis der Verarbeitungstätigkeiten.'
        => 'General Data Protection Regulation. Among other things, it obliges you to: have a data processing agreement with Microsoft (DPA), implement technical and organisational measures (Art. 32), provide the ability to delete and disclose data, and maintain a record of processing activities.',
    'NIS-2-Richtlinie'                                   => 'NIS-2 Directive',
    'EU-Richtlinie zur Cybersicherheit. Verpflichtend für mittlere/große Unternehmen kritischer Sektoren. Fordert u. a. Risikomanagement, MFA, Incident Response, Lieferkettensicherheit (Art. 21).'
        => 'EU directive on cybersecurity. Mandatory for medium-sized/large companies in critical sectors. It requires, among other things, risk management, MFA, incident response and supply chain security (Art. 21).',
    'BSI IT-Grundschutz'                                 => 'BSI IT-Grundschutz',
    'Methodik des BSI für ein Informationssicherheits-Managementsystem (ISMS). Insbesondere für Bund/Länder und öffentliche Verwaltung relevant. Bausteine wie ORP.4 (Identitäts- und Zugriffsverwaltung) sind direkt umsetzbar.'
        => 'The BSI methodology for an information security management system (ISMS). Particularly relevant for federal/state government and public administration. Modules such as ORP.4 (identity and access management) can be implemented directly.',
    'SPF, DKIM, DMARC'                                   => 'SPF, DKIM, DMARC',
    'DNS-Einträge gegen E-Mail-Spoofing. SPF = wer darf für deine Domain senden, DKIM = Signatur, DMARC = Policy wenn SPF/DKIM fehlschlagen. Alle drei zusammen verhindern, dass Phishing in deinem Namen verschickt wird.'
        => 'DNS records against email spoofing. SPF = who may send on behalf of your domain, DKIM = signature, DMARC = policy when SPF/DKIM fail. Together, all three prevent phishing from being sent in your name.',
    'Safe Links'                                         => 'Safe Links',
    'Microsoft Defender for Office 365 schreibt Links in eingehenden Mails so um, dass sie beim Klick noch einmal gegen Malware-Listen geprüft werden. Schützt auch nach Zustellung.'
        => 'Microsoft Defender for Office 365 rewrites links in incoming emails so that they are checked against malware lists again when clicked. Protects even after delivery.',
    'MFA-Fatigue-Angriff'                                => 'MFA fatigue attack',
    'Angreifer kennt das Passwort und versucht so lange MFA-Anfragen zu senden, bis der Benutzer aus Versehen "Approve" drückt. Schutz: Number-Matching aktivieren, Push limitieren, FIDO2 nutzen.'
        => 'The attacker knows the password and keeps sending MFA requests until the user accidentally taps “Approve”. Protection: enable number matching, rate-limit push prompts, use FIDO2.',
    'Phishing-Simulation'                                => 'Phishing simulation',
    'Gezielte, harmlose Test-Phishings, die Microsoft Defender ATP versendet — um zu messen, wie viele Benutzer klicken bzw. Awareness-Training brauchen.'
        => 'Targeted, harmless test phishing messages sent by Microsoft Defender ATP — to measure how many users click and which ones need awareness training.',
    'Cron-Jobs'                                          => 'Cron jobs',
    'Wiederkehrende Hintergrundaufgaben (z. B. nächtliche Reports, Sharing-Scans). Werden durch einen externen Cron alle paar Minuten getriggert, der dann je nach Budget Jobs ausführt.'
        => 'Recurring background tasks (e.g. nightly reports, sharing scans). They are triggered every few minutes by an external cron, which then runs jobs as the budget allows.',
    'Audit-Log'                                          => 'Audit log',
    'Microsoft Purview Audit zeichnet alle Aktivitäten im Tenant auf (Anmeldungen, Datei-Zugriffe, Admin-Aktionen). Aufbewahrung 90 Tage (E3/E5: 1 Jahr) — für Forensik unverzichtbar.'
        => 'Microsoft Purview Audit records all activity in the tenant (sign-ins, file access, admin actions). Retention is 90 days (E3/E5: 1 year) — indispensable for forensics.',
    'Access Review'                                      => 'Access review',
    'Regelmäßige Prüfung, ob Benutzer/Gäste noch Zugriff brauchen. Vorgeschrieben durch NIS-2 (Least Privilege) und DSGVO (Erforderlichkeitsprinzip).'
        => 'Regular review of whether users/guests still need access. Required by NIS-2 (least privilege) and the GDPR (principle of necessity).',
    'Microsoft Secure Score'                             => 'Microsoft Secure Score',
    'Microsofts Punktesystem für den Sicherheitsstand deines Tenants. Mehr Punkte = mehr empfohlene Maßnahmen umgesetzt. Gut als Trend-Indikator, ersetzt aber keine echte Risikoanalyse.'
        => 'Microsoft’s scoring system for your tenant’s security posture. More points = more recommended measures implemented. Useful as a trend indicator, but no substitute for a real risk analysis.',
    'Lizenz-Berater'                                     => 'License advisor',
    'Analysiert, welche Benutzer welche Features tatsächlich nutzen und schlägt günstigere Lizenz-Stufen vor (z. B. F1 statt E3 für reine Frontline-Worker).'
        => 'Analyses which users actually use which features and suggests cheaper license tiers (e.g. F1 instead of E3 for pure frontline workers).',
    'Microsoft Graph API'                                => 'Microsoft Graph API',
    'Die zentrale REST-Schnittstelle von Microsoft 365. Dieses Tool greift ausschließlich über die Graph API auf deinen Tenant zu — keine direkten DB-Zugriffe, kein Skripting in deinem Tenant.'
        => 'The central REST interface of Microsoft 365. This tool accesses your tenant exclusively via the Graph API — no direct database access, no scripting inside your tenant.',
    'CSRF-Schutz'                                        => 'CSRF protection',
    'Cross-Site Request Forgery: ein Token wird in jedes Formular eingebettet und beim Submit verifiziert, damit kein anderer Tab/Site eine Aktion in deinem Namen auslösen kann.'
        => 'Cross-site request forgery: a token is embedded in every form and verified on submit, so that no other tab/site can trigger an action in your name.',
    'REST-API dieses Tools'                              => 'This tool’s REST API',
    'Externe Werkzeuge (PowerBI, Grafana, n8n) können tenant-relevante KPIs als JSON abrufen. API-Keys verwalten in den Einstellungen, Spezifikation unter /api/docs.'
        => 'External tools (Power BI, Grafana, n8n) can retrieve tenant-relevant KPIs as JSON. Manage API keys in the settings; the specification is available at /api/docs.',
    'Sparkline'                                          => 'Sparkline',
    'Mini-Diagramm der letzten 7 Tage neben einer Kennzahl — zeigt den Trend ohne Klick: steigt / fällt / stabil.'
        => 'A mini chart of the last 7 days next to a metric — shows the trend at a glance: rising / falling / stable.',
    'Audit-Diff'                                         => 'Audit diff',
    'Snapshots der Tenant-Einstellungen werden täglich gespeichert. Diff zeigt, was sich seit gestern/letzter Woche/letztem Monat geändert hat — perfekt für Auditoren und Übergaben.'
        => 'Snapshots of the tenant settings are saved daily. The diff shows what has changed since yesterday/last week/last month — perfect for auditors and handovers.',
    'Workflow-Automatisierung'                           => 'Workflow automation',
    'Trigger + Aktion: z. B. "neuer Benutzer in Gruppe X" → "Lizenz Y zuweisen + Begrüßungsmail senden + OneDrive vorbereiten". Leichtgewichtige Alternative zu Power Automate für M365-Standard-Abläufe.'
        => 'Trigger + action: e.g. “new user in group X” → “assign license Y + send welcome email + prepare OneDrive”. A lightweight alternative to Power Automate for standard M365 workflows.',
    'Compliance-Profil'                                  => 'Compliance profile',
    'Branchenspezifische Härtungs-Voreinstellungen (Gesundheitswesen, Finanzwesen, öffentlicher Sektor). Wendet mit einem Klick die für deine Compliance-Anforderung typischen Hardening-Aktionen an.'
        => 'Industry-specific hardening presets (healthcare, finance, public sector). Applies the hardening actions typical for your compliance requirement with a single click.',
    'Einrichtungs-Assistent'                             => 'Setup wizard',
    'Fünf-Schritte-Tour für die ersten 10 Minuten nach Erstinstallation: Tenant-Verbindung, Berechtigungen, Benachrichtigungen, Branding, Compliance-Profil.'
        => 'A five-step tour for the first 10 minutes after the initial installation: tenant connection, permissions, notifications, branding, compliance profile.',
    'Best-Practice-Leitfaden'                            => 'Best-practice guide',
    'Interaktiver Schritt-für-Schritt-Leitfaden zur Tenant-Härtung — 8 Phasen, jede mit konkreten Aufgaben, Zeitabschätzung und direktem Modul-Link. Schritte können abgehakt werden, Fortschritt wird gespeichert.'
        => 'An interactive step-by-step guide to tenant hardening — 8 phases, each with concrete tasks, a time estimate and a direct module link. Steps can be checked off and progress is saved.',
    'In-App-Benachrichtigungen'                          => 'In-app notifications',
    'Die Glocke oben rechts zeigt Tenant-Ereignisse (neue Risiko-Anmeldungen, Hardening-Aktionen, Cron-Fehler) seit deinem letzten Besuch. Klick markiert als gelesen.'
        => 'The bell in the top right shows tenant events (new risky sign-ins, hardening actions, cron errors) since your last visit. Clicking marks them as read.',
];
