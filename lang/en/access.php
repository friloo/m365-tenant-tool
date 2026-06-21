<?php

/**
 * English translations for the Access & Privileges views:
 * conditionalaccess, namedlocations, authmethods, authstrength,
 * tokenlifetime, crosstenantaccess, identityproviders, adminroles,
 * pim, pimsettings, breakglass.
 *
 * Keys are the exact German source strings. Common terms (Name, Status,
 * Rolle, Aktiv, Ja, Nein, …) resolve from the central lang/en.php map and
 * are intentionally not duplicated here.
 *
 * @return array<string,string>
 */
return [

    // ── Conditional Access ──────────────────────────────────────────────────
    'Aktive Richtlinien'                          => 'Active policies',
    'Report-only'                                 => 'Report-only',
    'Neue Richtlinie'                             => 'New policy',
    'Sicherheitsanalyse'                          => 'Security analysis',
    'Alle Richtlinien'                            => 'All policies',
    'Keine Conditional-Access-Richtlinien gefunden.' => 'No conditional access policies found.',
    'Jetzt anlegen →'                             => 'Create now →',
    'Aktion'                                      => 'Action',
    'Richtlinie jetzt aktivieren? Teste sie zuerst im Report-Modus.'
        => 'Enable policy now? Test it in report mode first.',
    'Richtlinie «'                                => 'Policy «',
    '» wirklich löschen?\nDieser Vorgang kann nicht rückgängig gemacht werden.'
        => '» really delete?\nThis action cannot be undone.',
    'Bedingungen'                                 => 'Conditions',
    'Plattformen:'                                => 'Platforms:',
    'Standorte:'                                  => 'Locations:',
    'Client-Typen:'                               => 'Client types:',
    'Steuerelemente'                              => 'Controls',
    'Zugriff:'                                    => 'Access:',
    'Sitzung:'                                    => 'Session:',
    'Zuletzt geändert:'                           => 'Last modified:',
    'Verwaltung auch im'                          => 'Also manage in the',
    'Microsoft Entra Admin Center → Conditional Access'
        => 'Microsoft Entra admin center → Conditional Access',
    'Neue Richtlinien werden immer im'            => 'New policies are always created in',
    'Report-only Modus'                           => 'report-only mode',
    'angelegt — erst testen, dann aktivieren.'    => '— test first, then enable.',
    'Neue Richtlinie anlegen'                     => 'Create new policy',
    'Vorlage'                                     => 'Template',
    'Länder blockieren'                           => 'Block countries',
    'Alle Anmeldungen außerhalb erlaubter Länder blockieren.'
        => 'Block all sign-ins from outside allowed countries.',
    'MFA für alle'                                => 'MFA for everyone',
    'Multi-Faktor-Authentifizierung für alle Benutzer erzwingen.'
        => 'Enforce multi-factor authentication for all users.',
    'Legacy-Auth blockieren'                      => 'Block legacy auth',
    'Alte Protokolle (IMAP, POP, SMTP Auth) blockieren.'
        => 'Block legacy protocols (IMAP, POP, SMTP Auth).',
    'Erlaubter Länder-Standort'                   => 'Allowed country location',
    'Kein Länder-Standort vorhanden. Erst auf der'
        => 'No country location available. First, on the',
    'Named Locations Seite'                       => 'Named locations page',
    'einen Länder-Standort anlegen.'              => 'create a country location.',
    '– Bitte wählen –'                            => '– Please select –',
    'Anmeldungen aus Ländern, die in dieser Liste stehen, werden erlaubt — alle anderen blockiert.'
        => 'Sign-ins from countries in this list are allowed — all others are blocked.',
    'Name der Richtlinie'                         => 'Policy name',
    'z.B. Block: Nicht-DACH-Länder'               => 'e.g. Block: non-DACH countries',
    'Break-Glass-Konto ausschließen'              => 'Exclude break-glass account',
    'Object-ID des Notfall-Admins (optional, empfohlen)'
        => 'Object ID of the emergency admin (optional, recommended)',
    'Die Object-ID findest du in'                 => 'You can find the object ID in',
    'Entra ID → Benutzer'                         => 'Entra ID → Users',
    'Mehrere IDs kommagetrennt.'                  => 'Multiple IDs comma-separated.',
    'Anfangsstatus'                               => 'Initial state',
    'Report-only (empfohlen — erst testen!)'      => 'Report-only (recommended — test first!)',
    'Sofort aktivieren (Vorsicht!)'               => 'Enable immediately (caution!)',
    'Neue Richtlinien sollten immer im'           => 'New policies should always be started in',
    'Report-only-Modus'                           => 'report-only mode',
    'gestartet werden.'                           => '.',
    'Im Report-Modus kannst du im Sign-in-Log prüfen, wen die Richtlinie betreffen würde, bevor du sie aktivierst. Sorgfältig: Ein Break-Glass-Konto ausschließen ist Pflicht!'
        => 'In report mode you can check the sign-in log to see who the policy would affect before you enable it. Important: excluding a break-glass account is mandatory!',
    'Richtlinie erstellen'                        => 'Create policy',
    'Block: Nicht-DACH-Länder'                    => 'Block: non-DACH countries',
    'Require MFA – Alle Benutzer'                 => 'Require MFA – all users',
    'Block: Legacy-Authentifizierung'            => 'Block: legacy authentication',

    // ── Named Locations ─────────────────────────────────────────────────────
    'IP-Standorte'                                => 'IP locations',
    'Länder-Standorte'                            => 'Country locations',
    'Als vertrauenswürdig markiert'               => 'Marked as trusted',
    'IP-Bereiche total'                           => 'IP ranges total',
    'Länder-Standort anlegen'                     => 'Create country location',
    'IP-Standort anlegen'                         => 'Create IP location',
    'Keine Länder-Standorte konfiguriert.'        => 'No country locations configured.',
    'Länder'                                      => 'Countries',
    'Unbekannte Länder'                           => 'Unknown countries',
    'Standort «'                                  => 'Location «',
    '» wirklich löschen?\nAlle CA-Richtlinien, die ihn referenzieren, müssen angepasst werden.'
        => '» really delete?\nAll CA policies that reference it must be adjusted.',
    'Keine IP-Standorte konfiguriert.'            => 'No IP locations configured.',
    'Vertrauenswürdig'                            => 'Trusted',
    'IP-Bereiche'                                 => 'IP ranges',
    'IP-Standort «'                               => 'IP location «',
    '» wirklich löschen?'                         => '» really delete?',
    'Named Locations werden in Conditional-Access-Richtlinien referenziert.'
        => 'Named locations are referenced in conditional access policies.',
    'Tipp: Erst einen Länder-Standort anlegen, dann auf der'
        => 'Tip: First create a country location, then on the',
    'Conditional Access Seite'                    => 'Conditional Access page',
    'eine Blockierungsrichtlinie erstellen.'      => 'create a blocking policy.',
    'z.B. Erlaubte Länder (DACH)'                 => 'e.g. Allowed countries (DACH)',
    'Länder auswählen'                            => 'Select countries',
    'Weitere Codes (kommagetrennt):'              => 'Additional codes (comma-separated):',
    'z.B. JP, SG, US'                             => 'e.g. JP, SG, US',
    'Anmeldungen aus unbekannten Ländern einschließen'
        => 'Include sign-ins from unknown countries',
    '(empfohlen: deaktiviert)'                    => '(recommended: disabled)',
    'Standort anlegen'                            => 'Create location',
    'z.B. Büro Frankfurt'                         => 'e.g. Frankfurt office',
    'IP-Bereiche (CIDR, ein Eintrag pro Zeile)'   => 'IP ranges (CIDR, one entry per line)',
    'IPv4 und IPv6 CIDR-Notation werden unterstützt.'
        => 'IPv4 and IPv6 CIDR notation are supported.',
    'Als vertrauenswürdig markieren'              => 'Mark as trusted',
    '(ermöglicht MFA-Ausnahmen in CA-Richtlinien)'
        => '(enables MFA exceptions in CA policies)',

    // ── Authentication Methods ──────────────────────────────────────────────
    'aktiviert'                                   => 'enabled',
    'deaktiviert'                                 => 'disabled',
    'empfohlen: an'                               => 'recommended: on',
    'empfohlen: aus'                              => 'recommended: off',
    'situativ'                                    => 'situational',
    'Steuert tenant-weit, welche Authentifizierungsmethoden Nutzer registrieren/verwenden dürfen'
        => 'Controls tenant-wide which authentication methods users may register/use',
    'Empfehlung nach CIS M365 / Microsoft: phishing-resistente Methoden (FIDO2, Authenticator) aktivieren, schwache (SMS, Sprachanruf, E-Mail-OTP) als MFA deaktivieren. Änderungen wirken'
        => 'Recommendation per CIS M365 / Microsoft: enable phishing-resistant methods (FIDO2, Authenticator), disable weak ones (SMS, voice call, email OTP) as MFA. Changes take effect',
    'sofort tenant-weit'                          => 'immediately tenant-wide',
    'Methode'                                     => 'Method',
    'Empfehlung'                                  => 'Recommendation',
    'Keine Methoden gelesen — Berechtigung'       => 'No methods read — check the',
    'prüfen.'                                     => 'permission.',
    'auf'                                         => 'to',
    'setzen? Wirkt sofort tenant-weit.'           => 'set? Takes effect immediately tenant-wide.',
    'Schreiben erfordert die Graph-Berechtigung'  => 'Writing requires the Graph permission',
    'Feinkonfiguration (z. B. Zielgruppen je Methode, Number-Matching-Details) im'
        => 'Fine-grained configuration (e.g. target groups per method, number-matching details) in the',
    'Entra-Portal'                                => 'Entra portal',

    // ── Authentication Strength ─────────────────────────────────────────────
    'Phishing-resistente MFA'                     => 'Phishing-resistant MFA',
    'ist seit 2024 Microsofts offizielle Empfehlung (FIDO2, Windows Hello, Certificate-Based, Hardware OATH). SMS-OTP und Voice-Call gelten als unsicher gegen Adversary-in-the-Middle-Angriffe. Selbst Microsoft Authenticator-Push ist nicht vollständig phishing-resistent — nur FIDO2 und Zertifikate sind es.'
        => 'has been Microsoft\'s official recommendation since 2024 (FIDO2, Windows Hello, certificate-based, hardware OATH). SMS OTP and voice call are considered insecure against adversary-in-the-middle attacks. Even Microsoft Authenticator push is not fully phishing-resistant — only FIDO2 and certificates are.',
    'Phishing-resistent'                          => 'Phishing-resistant',
    'von'                                         => 'of',
    'Usern'                                       => 'users',
    'Nur Software-MFA'                            => 'Software MFA only',
    'Authenticator / TOTP'                        => 'Authenticator / TOTP',
    'Nur schwache MFA'                            => 'Weak MFA only',
    'SMS / Voice / E-Mail-OTP'                    => 'SMS / Voice / Email OTP',
    'Keine MFA'                                   => 'No MFA',
    'nur Passwort'                                => 'password only',
    'Methoden-Verteilung'                         => 'Method breakdown',
    'User mit ausschließlich schwacher MFA'       => 'Users with only weak MFA',
    'Registrierte Methoden'                       => 'Registered methods',
    'Authentication-Strength-Policies'            => 'Authentication strength policies',
    'Nur Microsoft-Default-Policies aktiv. Custom-Strength-Policies können in Entra → Authentifizierungsmethoden → Authentifizierungsstärken konfiguriert werden.'
        => 'Only Microsoft default policies active. Custom strength policies can be configured in Entra → Authentication methods → Authentication strengths.',
    'Erlaubte Methoden'                           => 'Allowed methods',

    // ── Token Lifetime ──────────────────────────────────────────────────────
    'Token-Lifetime steuert, wie lange ein User angemeldet bleibt'
        => 'Token lifetime controls how long a user stays signed in',
    ', bevor er sich neu authentifizieren muss. Microsoft hat 2021 die globalen Token-Lifetime-Policies deprecated — die einzig empfohlene Methode ist heute „Sign-in Frequency" in Conditional Access Policies. Empfehlung: Admin-Apps ≤ 4 Stunden, kritische User-Apps ≤ 24 Stunden, Standard-Apps 7 Tage. Microsoft-Default ist sonst 90 Tage Refresh-Token — viel zu lang.'
        => ' before they must re-authenticate. Microsoft deprecated the global token lifetime policies in 2021 — today the only recommended method is "Sign-in Frequency" in conditional access policies. Recommendation: admin apps ≤ 4 hours, critical user apps ≤ 24 hours, standard apps 7 days. Otherwise the Microsoft default is a 90-day refresh token — far too long.',
    'Empfehlungen'                                => 'Recommendations',
    'Sign-in-Frequency in CA-Policies'            => 'Sign-in frequency in CA policies',
    'Keine CA-Policy mit konfigurierter Sign-in-Frequency gefunden.'
        => 'No CA policy with configured sign-in frequency found.',
    'Frequenz'                                    => 'Frequency',
    'Auth-Typ'                                    => 'Auth type',
    'Report-Only'                                 => 'Report-only',
    'Jedes Mal neu'                               => 'Every time',
    'Persistente Browser-Sessions'                => 'Persistent browser sessions',
    'Modus'                                       => 'Mode',
    'Konfiguration:'                              => 'Configuration:',
    'Im'                                          => 'In the',
    'Conditional-Access-Portal'                   => 'conditional access portal',
    'eine Policy öffnen, unter „Sitzung" → „Sign-in frequency" den Wert setzen. Sinnvolle Defaults: 4 Stunden für Privileged Roles, 12 Stunden für sensitive Apps (Finance/HR), 7 Tage für Office-Standard.'
        => 'open a policy and set the value under "Session" → "Sign-in frequency". Sensible defaults: 4 hours for privileged roles, 12 hours for sensitive apps (finance/HR), 7 days for the Office standard.',

    // ── Cross-Tenant Access ─────────────────────────────────────────────────
    'Cross-Tenant-Access'                         => 'Cross-tenant access',
    'regelt, welche Partner-Tenants Zugriff auf deine Ressourcen haben (Inbound) und in welche Tenants deine User dürfen (Outbound). Wichtig für B2B-Kollaboration, Microsoft Teams Federation und Trust-Settings (z. B. MFA-Trust zwischen Tenants).'
        => 'controls which partner tenants have access to your resources (inbound) and which tenants your users may access (outbound). Important for B2B collaboration, Microsoft Teams federation and trust settings (e.g. MFA trust between tenants).',
    'Default für unbekannte Tenants'              => 'Default for unknown tenants',
    'Default-Policy nicht lesbar.'                => 'Default policy not readable.',
    'B2B-Kollaboration eingehend'                 => 'B2B collaboration inbound',
    'B2B-Kollaboration ausgehend'                 => 'B2B collaboration outbound',
    'Inbound-Trust (MFA / Device)'                => 'Inbound trust (MFA / device)',
    'In Entra konfigurieren'                      => 'Configure in Entra',
    'Partner-spezifische Konfigurationen'         => 'Partner-specific configurations',
    'Partner'                                     => 'partners',
    'Keine Partner-spezifischen Cross-Tenant-Policies konfiguriert — alle externen Tenants nutzen die Default-Policy oben.'
        => 'No partner-specific cross-tenant policies configured — all external tenants use the default policy above.',
    'B2B in/out'                                  => 'B2B in/out',
    'Direct Connect in/out'                       => 'Direct connect in/out',
    'Trust akzeptiert'                            => 'Trust accepted',
    'Service-Provider?'                           => 'Service provider?',
    'nein'                                        => 'no',

    // ── Identity Providers ──────────────────────────────────────────────────
    'Identity Provider Trust'                     => 'Identity provider trust',
    '— externe Authentifizierungs­quellen, die der Tenant akzeptiert (Google, Facebook für B2C oder SAML/WS-Fed-Federation mit ADFS, Okta, Ping Identity, …). Jeder zusätzliche IdP ist eine erweiterte Angriffsfläche und muss regelmäßig auditiert werden.'
        => '— external authentication sources the tenant accepts (Google, Facebook for B2C, or SAML/WS-Fed federation with ADFS, Okta, Ping Identity, …). Every additional IdP is an expanded attack surface and must be audited regularly.',
    'Konfigurierte Identity Providers'            => 'Configured identity providers',
    'Keine externen Identity Providers konfiguriert. (Standard, wenn der Tenant nur Microsoft-Accounts akzeptiert.)'
        => 'No external identity providers configured. (Default when the tenant accepts only Microsoft accounts.)',
    'Federierte Domains (SAML / WS-Fed)'          => 'Federated domains (SAML / WS-Fed)',
    'Keine federierten Domains. Alle Domains nutzen Cloud-only oder Pass-Through-Authentication.'
        => 'No federated domains. All domains use cloud-only or pass-through authentication.',

    // ── Admin Roles ─────────────────────────────────────────────────────────
    'Admin-Rollen aktiv'                          => 'Admin roles active',
    'Rollen mit Zuweisung'                        => 'Roles with assignment',
    'Benutzer mit Adminrechten'                   => 'Users with admin rights',
    'Eindeutige Principals'                       => 'Distinct principals',
    'Zu viele — Sicherheitsrisiko'                => 'Too many — security risk',
    'Accounts'                                    => 'Accounts',
    'Zu viele Global-Admins erhöhen das Angriffspotenzial.'
        => 'Too many global admins increase the attack surface.',
    'Empfehlung: max. 2–4 Accounts.'              => 'Recommendation: max. 2–4 accounts.',
    'Keine Daten verfügbar'                       => 'No data available',
    'Keine Admin-Rollen zugewiesen'              => 'No admin roles assigned',
    'Der Tenant hat aktuell keine Direkt-Zuweisungen — eventuell wird PIM genutzt.'
        => 'The tenant currently has no direct assignments — PIM may be in use.',
    'Rolle zuweisen'                              => 'Assign role',
    'Benutzer-ID (UUID)'                          => 'User ID (UUID)',
    'Benutzer-ID oder UPN eingeben'               => 'Enter user ID or UPN',
    '— Rolle auswählen —'                         => '— Select role —',
    'Benutzer-ID aus dem Benutzer-Modul kopieren (uuid-Format, z.B.'
        => 'Copy the user ID from the Users module (UUID format, e.g.',
    ':n Mitglied'                                 => ':n member',
    ':n Mitglieder'                               => ':n members',
    'Mitglied suchen…'                            => 'Search member…',
    'Service Principal'                           => 'Service principal',
    'Service-Konto'                               => 'Service account',
    'Rollenzuweisung für'                         => 'Role assignment for',
    'wirklich entfernen?'                         => 'really remove?',
    'Zuweisung entfernen'                         => 'Remove assignment',
    'Keine Mitglieder'                            => 'No members',
    'Keine aktiven Rollenzuweisungen gefunden'    => 'No active role assignments found',
    'Entweder sind keine Rollen zugewiesen oder die Berechtigungen'
        => 'Either no roles are assigned or the permissions',
    'fehlen.'                                     => 'are missing.',

    // ── PIM ─────────────────────────────────────────────────────────────────
    'Aktiv erhöht'                                => 'Actively elevated',
    'Just-in-Time oder dauerhaft'                 => 'Just-in-time or permanent',
    'Eligible'                                    => 'Eligible',
    'aktivierbar, gerade ungenutzt'               => 'activatable, currently unused',
    'Dauerhafte Admins'                           => 'Permanent admins',
    'Empfehlung: ≤ 2'                             => 'Recommendation: ≤ 2',
    'Läuft'                                       => 'Expires',
    '7 Tage'                                      => '7 days',
    'aktive Zuweisungen'                          => 'active assignments',
    'Aktuell aktive Privileged-Rollen'            => 'Currently active privileged roles',
    'Keine aktiven Privileged-Rollen-Zuweisungen.'
        => 'No active privileged role assignments.',
    'Identität'                                   => 'Identity',
    'Aktiv seit'                                  => 'Active since',
    'Endet am'                                    => 'Ends on',
    'JIT aktiviert'                               => 'JIT activated',
    'Dauerhaft'                                   => 'Permanent',
    'unbegrenzt'                                  => 'unlimited',
    'Eligible — verfügbar zur Aktivierung'        => 'Eligible — available for activation',
    'Keine Eligible-Zuweisungen — alle Admin-Rollen sind dauerhaft oder PIM ist nicht in Verwendung.'
        => 'No eligible assignments — all admin roles are permanent or PIM is not in use.',
    'Empfehlung: dauerhafte Admins zu Eligible umstellen (BSI ORP.4.A23).'
        => 'Recommendation: switch permanent admins to eligible (BSI ORP.4.A23).',
    'Verfügbar bis'                               => 'Available until',
    'Aktivierungen der letzten 30 Tage'           => 'Activations in the last 30 days',
    'Keine PIM-Aktivierungen in den letzten 30 Tagen.'
        => 'No PIM activations in the last 30 days.',
    'Wann'                                        => 'When',
    'Wer'                                         => 'Who',
    'Ziel'                                        => 'Target',
    'Resultat'                                    => 'Result',
    'Best Practice (BSI ORP.4.A23, NIS-2 Art. 21(j))'
        => 'Best practice (BSI ORP.4.A23, NIS-2 Art. 21(j))',
    'Privilegierte Konten sollten als <em>Eligible</em> konfiguriert werden, nicht dauerhaft zugewiesen. Bei Bedarf aktiviert sich der User für eine begrenzte Zeit (max. 8 h) mit MFA + Begründung — außerhalb dieser Zeitfenster hat er nur Standard-Berechtigungen.'
        => 'Privileged accounts should be configured as <em>eligible</em>, not permanently assigned. When needed, the user activates for a limited time (max. 8 h) with MFA + justification — outside that window they only hold standard permissions.',
    'Entra → PIM'                                 => 'Entra → PIM',

    // ── PIM Settings ────────────────────────────────────────────────────────
    'ja'                                          => 'yes',
    'Zeigt die'                                   => 'Shows the',
    'Aktivierungsregeln je Verzeichnisrolle'      => 'activation rules per directory role',
    'aus Privileged Identity Management (benötigt Entra ID P2). Sicherheitsleitlinie: privilegierte Rollen sollten bei der Aktivierung'
        => 'from Privileged Identity Management (requires Entra ID P2). Security guideline: privileged roles should require',
    'und eine'                                    => 'and a',
    'Begründung'                                  => 'Justification',
    'verlangen, kritische Rollen zusätzlich eine' => 'on activation, critical roles additionally an',
    'Genehmigung'                                 => 'Approval',
    ', und eine begrenzte maximale Aktivierungsdauer.'
        => ', and a limited maximum activation duration.',
    'Diese Ansicht ist'                           => 'This view is',
    '— Regeln werden im'                          => '— rules are changed in the',
    'Entra-PIM-Portal'                            => 'Entra PIM portal',
    'geändert.'                                   => '.',
    'MFA bei Aktivierung'                         => 'MFA on activation',
    'Max. Dauer'                                  => 'Max. duration',
    'Keine PIM-Richtlinien gelesen — Entra ID P2 erforderlich, Berechtigung'
        => 'No PIM policies read — Entra ID P2 required, check the',
    'privilegiert'                                => 'privileged',

    // ── Break-Glass ─────────────────────────────────────────────────────────
    'Was sind Break-Glass-Accounts?'              => 'What are break-glass accounts?',
    'Notfall-Administratorkonten, mit denen man sich anmelden kann, wenn alle anderen Wege versagen (z.B. wenn eine fehlerhafte Conditional-Access-Policy alle anderen Admins aussperrt, oder bei MFA-Ausfall).'
        => 'Emergency administrator accounts you can sign in with when all other paths fail (e.g. when a faulty conditional access policy locks out all other admins, or during an MFA outage).',
    'Microsoft empfiehlt'                         => 'Microsoft recommends',
    '2 Konten'                                    => '2 accounts',
    ', dauerhaft als Global Admin, aus allen restriktiven CA-Policies ausgeschlossen, Passwort sicher im Tresor verwahrt, regelmäßig getestet (mind. halbjährlich).'
        => ', permanently global admin, excluded from all restrictive CA policies, password stored securely in a vault, tested regularly (at least every six months).',
    'Microsoft-Doku'                              => 'Microsoft docs',
    'Konfigurierte Notfall-Accounts'              => 'Configured emergency accounts',
    '(kommagetrennt oder eine pro Zeile)'         => '(comma-separated or one per line)',
    'Empfohlen: 2 Accounts, dauerhaft Global Administrator, jeweils mit eigener Cloud-Identität (nicht synchronisiert).'
        => 'Recommended: 2 accounts, permanently global administrator, each with its own cloud identity (not synchronized).',
    'Speichern & Prüfen'                          => 'Save & check',
    'Noch keine Break-Glass-Accounts konfiguriert. Trage die UPNs oben ein, um den automatischen Health-Check zu aktivieren.'
        => 'No break-glass accounts configured yet. Enter the UPNs above to enable the automatic health check.',
    'Problem(e)'                                  => 'problem(s)',
    'Account existiert nicht im Tenant.'          => 'Account does not exist in the tenant.',
    'Bitte UPN prüfen oder neuen Notfall-Account anlegen.'
        => 'Please check the UPN or create a new emergency account.',
    'Global Admin (dauerhaft)'                    => 'Global admin (permanent)',
    'MFA-Methode registriert'                     => 'MFA method registered',
    'Nein (nur Passwort)'                         => 'No (password only)',
    'Letzte Anmeldung'                            => 'Last sign-in',
    'Noch nie'                                    => 'Never',
    'vor :n Tagen'                                => ':n days ago',
    'Tage'                                        => 'days',
    'CA-Policies ausgenommen'                     => 'CA policies excluded',
    'nicht prüfbar (Permission?)'                 => 'not checkable (permission?)',
    'Keine'                                       => 'None',
    'Policy(s)'                                   => 'policy(s)',
    'Hinweise:'                                   => 'Notes:',
];
