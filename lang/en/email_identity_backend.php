<?php

/**
 * English translations for backend (PHP) display strings produced in the
 * email / identity modules:
 *   - ExchangeMigration: DNS readiness labels, score readiness labels, issues
 *   - MailFlow: anti-spam deep-link labels + descriptions
 *   - Mailboxes: page titles, flash messages, CSV headers/cells
 *   - AuthMethods: method labels + recommendation notes
 *   - MfaMethods: friendly method labels
 *
 * Keys are the exact German source strings (casing/punctuation/umlauts as in
 * source). Dynamic values are substituted via :param placeholders.
 *
 * @return array<string,string>
 */
return [
    // ── ExchangeMigration: MX ───────────────────────────────────────────────
    'Kein MX-Eintrag gefunden'                                           => 'No MX record found',
    'Zeigt auf Exchange Online'                                          => 'Points to Exchange Online',
    'MX zeigt noch nicht auf Exchange Online'                            => 'MX does not point to Exchange Online yet',

    // ── ExchangeMigration: SPF ──────────────────────────────────────────────
    'SPF enthält Exchange Online'                                        => 'SPF includes Exchange Online',
    'SPF gefunden, aber ohne Exchange Online (spf.protection.outlook.com fehlt)'
        => 'SPF found, but without Exchange Online (spf.protection.outlook.com missing)',
    'Kein SPF-Eintrag gefunden'                                          => 'No SPF record found',

    // ── ExchangeMigration: DKIM ─────────────────────────────────────────────
    'DKIM für Exchange Online konfiguriert'                              => 'DKIM configured for Exchange Online',
    'DKIM-Einträge gefunden, aber nicht Exchange Online zugeordnet'      => 'DKIM records found, but not associated with Exchange Online',
    'Keine DKIM-Selektoren gefunden (selector1/selector2)'              => 'No DKIM selectors found (selector1/selector2)',

    // ── ExchangeMigration: DMARC ────────────────────────────────────────────
    'DMARC gefunden (p=:policy'                                          => 'DMARC found (p=:policy',
    ', rua vorhanden'                                                    => ', rua present',
    'Kein DMARC-Eintrag gefunden'                                        => 'No DMARC record found',

    // ── ExchangeMigration: Autodiscover ─────────────────────────────────────
    'Autodiscover → autodiscover.outlook.com (CNAME)'                   => 'Autodiscover → autodiscover.outlook.com (CNAME)',
    'Autodiscover CNAME zeigt auf: :target'                             => 'Autodiscover CNAME points to: :target',
    'Autodiscover SRV → Outlook.com'                                    => 'Autodiscover SRV → Outlook.com',
    'Autodiscover SRV zeigt auf: :target'                               => 'Autodiscover SRV points to: :target',
    'Autodiscover hat A-Eintrag (möglicherweise on-prem: :ip)'          => 'Autodiscover has an A record (possibly on-prem: :ip)',
    'Kein Autodiscover-Eintrag gefunden'                                => 'No Autodiscover record found',

    // ── ExchangeMigration: score issues ─────────────────────────────────────
    'Lizenz-Abdeckung :pct% — nicht alle Benutzer haben Exchange Online'
        => 'License coverage :pct% — not all users have Exchange Online',
    'Lizenz-Abdeckung :pct% — viele Benutzer ohne Exchange Online'
        => 'License coverage :pct% — many users without Exchange Online',
    'Lizenz-Abdeckung :pct% — zu wenige Exchange-Online-Lizenzen'
        => 'License coverage :pct% — too few Exchange Online licenses',
    'Keine Exchange-Online-Lizenzen gefunden'                           => 'No Exchange Online licenses found',
    ':check fehlt'                                                       => ':check missing',
    'Keine verifizierten Domains (außer onmicrosoft.com) gefunden'      => 'No verified domains (other than onmicrosoft.com) found',

    // ── ExchangeMigration: readiness labels ─────────────────────────────────
    'Bereit für Migration'                                              => 'Ready for migration',
    'Teilweise bereit'                                                  => 'Partially ready',
    'Nicht bereit'                                                      => 'Not ready',

    // ── MailFlow: anti-spam links (labels + descriptions) ───────────────────
    'Transportregeln (Mailflow-Regeln)'                                 => 'Transport rules (mail flow rules)',
    'Regeln für eingehende und ausgehende E-Mails erstellen und verwalten'
        => 'Create and manage rules for inbound and outbound emails',
    'Anti-Spam-Richtlinien'                                             => 'Anti-spam policies',
    'Spam-Filter und Quarantäne-Einstellungen konfigurieren'           => 'Configure spam filters and quarantine settings',
    'Anti-Malware-Richtlinien'                                         => 'Anti-malware policies',
    'Malware-Schutz für eingehende E-Mails konfigurieren'              => 'Configure malware protection for inbound emails',
    'Anti-Phishing-Richtlinien'                                        => 'Anti-phishing policies',
    'Phishing-Schutz und Impersonation-Erkennung'                      => 'Phishing protection and impersonation detection',
    'DKIM-Einstellungen'                                               => 'DKIM settings',
    'DKIM-Signaturen für ausgehende E-Mails konfigurieren'             => 'Configure DKIM signatures for outbound emails',
    'Akzeptierte Domänen'                                              => 'Accepted domains',
    'Verwaltung der akzeptierten E-Mail-Domänen'                       => 'Manage accepted email domains',
    'Connector-Konfiguration'                                          => 'Connector configuration',
    'Eingehende und ausgehende Connectors für Mail-Routing'            => 'Inbound and outbound connectors for mail routing',
    'Quarantäne'                                                       => 'Quarantine',
    'Quarantänisierte E-Mails überprüfen und freigeben'               => 'Review and release quarantined emails',

    // ── Mailboxes: page titles ──────────────────────────────────────────────
    'Postfächer'                                                       => 'Mailboxes',
    'Postfach'                                                         => 'Mailbox',
    'Weiterleitungen & Regeln'                                        => 'Forwarding & rules',
    'Freigegebene Postfächer'                                         => 'Shared mailboxes',

    // ── Mailboxes: flash messages ───────────────────────────────────────────
    'Anzeigename und Alias dürfen nicht leer sein.'                   => 'Display name and alias must not be empty.',
    "Shared Mailbox ':name' wird angelegt. Exchange Online benötigt einige Minuten zur Bereitstellung."
        => "Shared mailbox ':name' is being created. Exchange Online needs a few minutes to provision it.",
    'Fehlende Berechtigung: User.ReadWrite.All ist in der Azure App nicht erteilt.'
        => 'Missing permission: User.ReadWrite.All is not granted in the Azure app.',
    'Fehler beim Anlegen des Shared Mailbox: :msg'                    => 'Error creating the shared mailbox: :msg',
    'Weiterleitung wurde entfernt.'                                   => 'Forwarding has been removed.',
    'Weiterleitung gesetzt auf: :address'                            => 'Forwarding set to: :address',
    'Fehlende Berechtigung: MailboxSettings.ReadWrite ist in der Azure App nicht erteilt.'
        => 'Missing permission: MailboxSettings.ReadWrite is not granted in the Azure app.',
    'Fehler beim Speichern der Weiterleitung: :msg'                   => 'Error saving the forwarding: :msg',
    'Abwesenheitsnotiz aktiviert.'                                    => 'Out-of-office reply enabled.',
    'Abwesenheitsnotiz deaktiviert.'                                  => 'Out-of-office reply disabled.',
    'Fehler beim Speichern der Abwesenheitsnotiz: :msg'               => 'Error saving the out-of-office reply: :msg',
    'Weiterleitung entfernt.'                                         => 'Forwarding removed.',
    'Fehler beim Entfernen der Weiterleitung: :msg'                   => 'Error removing the forwarding: :msg',

    // ── Mailboxes: CSV headers + cells ──────────────────────────────────────
    'Anzeigename'                                                     => 'Display name',
    'UPN'                                                             => 'UPN',
    'E-Mail'                                                          => 'Email',
    'Weiterleitungsadresse'                                          => 'Forwarding address',
    'Aktiviert'                                                       => 'Enabled',
    'An Postfach und weiterleiten'                                   => 'Deliver to mailbox and forward',
    'Größe (MB)'                                                     => 'Size (MB)',
    'Elemente'                                                       => 'Items',
    'Gel. Elemente'                                                  => 'Del. items',
    'Gel. Größe (MB)'                                                => 'Del. size (MB)',
    'Gelöscht'                                                       => 'Deleted',
    'Ja'                                                             => 'Yes',
    'Nein'                                                           => 'No',

    // ── AuthMethods: method labels ──────────────────────────────────────────
    'FIDO2 Security Keys'                                            => 'FIDO2 security keys',
    'Microsoft Authenticator'                                       => 'Microsoft Authenticator',
    'Temporary Access Pass (TAP)'                                   => 'Temporary Access Pass (TAP)',
    'Software-OATH (TOTP-App)'                                      => 'Software OATH (TOTP app)',
    'Zertifikatsbasiert (CBA)'                                      => 'Certificate-based (CBA)',
    'Hardware-OATH-Token'                                           => 'Hardware OATH token',
    'SMS'                                                           => 'SMS',
    'Sprachanruf'                                                   => 'Voice call',
    'E-Mail-OTP'                                                    => 'Email OTP',

    // ── AuthMethods: recommendation notes ───────────────────────────────────
    'Phishing-resistent — empfohlen.'                               => 'Phishing-resistant — recommended.',
    'Push/Passwordless — empfohlen (mit Number-Matching).'          => 'Push/passwordless — recommended (with number matching).',
    'Onboarding/Recovery — situativ aktivieren.'                    => 'Onboarding/recovery — enable as needed.',
    'Akzeptabel als zusätzliche Methode.'                           => 'Acceptable as an additional method.',
    'Phishing-resistent, falls PKI vorhanden.'                      => 'Phishing-resistant if a PKI is in place.',
    'OK, falls Hardware-Token im Einsatz.'                          => 'OK if hardware tokens are in use.',
    'Schwach (SIM-Swapping) — möglichst deaktivieren.'              => 'Weak (SIM swapping) — disable if possible.',
    'Schwach — möglichst deaktivieren.'                             => 'Weak — disable if possible.',
    'Nur für Gäste/SSPR — als MFA schwach.'                         => 'Guests/SSPR only — weak as MFA.',

    // ── MfaMethods: friendly method labels ──────────────────────────────────
    'Microsoft Authenticator (Push)'                                => 'Microsoft Authenticator (Push)',
    'Microsoft Authenticator (Passwordless)'                        => 'Microsoft Authenticator (Passwordless)',
    'Authenticator App (TOTP)'                                      => 'Authenticator app (TOTP)',
    'Hardware-Token (TOTP)'                                         => 'Hardware token (TOTP)',
    'SMS / Anruf'                                                   => 'SMS / call',
    'SMS / Anruf (Mobil)'                                           => 'SMS / call (mobile)',
    'SMS / Anruf (Alt. Mobil)'                                      => 'SMS / call (alt. mobile)',
    'Bürotelefon'                                                   => 'Office phone',
    'FIDO2-Sicherheitsschlüssel'                                   => 'FIDO2 security key',
    'Passkey'                                                       => 'Passkey',
    'Passkey (gerätegebunden)'                                     => 'Passkey (device-bound)',
    'Windows Hello'                                                 => 'Windows Hello',
    'E-Mail OTP'                                                    => 'Email OTP',
    'Temporärer Zugangscode'                                       => 'Temporary Access Pass',
    'Sicherheitsfrage'                                             => 'Security question',
    'App-Benachrichtigung'                                         => 'App notification',
    'App-Code'                                                     => 'App code',
    'App-Passwort'                                                 => 'App password',
];
