<?php

/**
 * English translations for the email / Exchange-related views
 * (mailboxes, mail flow, domain health, Exchange migration, message center).
 *
 * Keys are the exact German source strings. Strings that resolve from the
 * central glossary (lang/en.php) are intentionally NOT repeated here.
 *
 * @return array<string,string>
 */
return [
    // ── Mailboxes: index ────────────────────────────────────────────────────
    'Postfächer gesamt'                                  => 'Total mailboxes',
    'aktive Postfächer'                                  => 'active mailboxes',
    'Gesamter Speicher'                                  => 'Total storage',
    'Ø :size pro Postfach'                               => 'Ø :size per mailbox',
    'Postfächer &gt; 50 GB'                              => 'Mailboxes &gt; 50 GB',
    'nahe Quota-Limit'                                   => 'near quota limit',
    'Nie genutzt (&lt; 1 GB)'                            => 'Never used (&lt; 1 GB)',
    'sehr kleiner Speicher'                              => 'very small storage',
    'Keine Postfachdaten verfügbar'                      => 'No mailbox data available',
    'Es wurden keine Postfächer im Tenant gefunden.'     => 'No mailboxes were found in the tenant.',
    'Postfach suchen…'                                   => 'Search mailbox…',
    'Shared Mailbox anlegen'                             => 'Create shared mailbox',
    'CSV Export'                                          => 'CSV export',
    'Anzeigename'                                         => 'Display name',
    'UPN'                                                 => 'UPN',
    'Größe'                                               => 'Size',
    'Elemente'                                            => 'Items',
    'Gel. Elemente'                                       => 'Del. items',
    'Gel. Größe'                                          => 'Del. size',
    'Speicherauslastung'                                 => 'Storage usage',
    'Gelöscht'                                            => 'Deleted',
    'Weiterleitung aktiv &rarr; :addr'                   => 'Forwarding active &rarr; :addr',
    'Schließen'                                           => 'Close',
    'z.B. Buchhaltung'                                   => 'e.g. Accounting',
    'z.B. buchhaltung'                                   => 'e.g. accounting',
    'Nur Kleinbuchstaben, Ziffern und Bindestriche'      => 'Only lowercase letters, digits and hyphens',
    'Ergebnis-Adresse:'                                  => 'Resulting address:',
    'Das Konto wird ohne interaktiven Login-Zugriff angelegt'
        => 'The account is created without interactive login access',
    'Exchange Online stellt das Postfach innerhalb weniger Minuten bereit.'
        => 'Exchange Online provisions the mailbox within a few minutes.',
    'Anlegen'                                             => 'Create',

    // ── Mailboxes: detail ───────────────────────────────────────────────────
    'Zurück zu Postfächer'                               => 'Back to mailboxes',
    'Bitte erteilen Sie in der Azure App-Registrierung die Berechtigung'
        => 'Please grant the permission in the Azure app registration',
    'und genehmigen Sie sie als Administrator.'          => 'and approve it as an administrator.',
    'Deaktiviert'                                         => 'Disabled',
    'Titel'                                               => 'Title',
    'Abteilung'                                           => 'Department',
    'Zeitzone'                                            => 'Time zone',
    'E-Mail-Weiterleitung'                               => 'Email forwarding',
    'Status:'                                             => 'Status:',
    'Weitergeleitet an:'                                 => 'Forwarded to:',
    'Keine Weiterleitung aktiv.'                         => 'No forwarding active.',
    'Weiterleitung wirklich entfernen?'                  => 'Really remove forwarding?',
    'Weiterleitung entfernen'                            => 'Remove forwarding',
    'Erfordert'                                           => 'Requires',
    '-Berechtigung in der Azure App.'                    => ' permission in the Azure app.',
    'Abwesenheitsnotiz (Auto-Reply)'                     => 'Out-of-office (auto-reply)',
    'Aktuelle Nachricht:'                                => 'Current message:',
    'Abwesenheitsnotiz aktivieren'                       => 'Enable out-of-office reply',
    'Nachricht (intern &amp; extern):'                   => 'Message (internal &amp; external):',
    'Ich bin derzeit nicht erreichbar…'                  => 'I am currently unavailable…',
    'Postfachordner'                                     => 'Mailbox folders',
    'Keine Ordner verfügbar oder fehlende Berechtigung'  => 'No folders available or missing permission',
    'Ordner'                                              => 'Folder',
    'Gesamt'                                              => 'Total',
    'Ungelesen'                                           => 'Unread',
    'Kalender-Berechtigungen'                            => 'Calendar permissions',
    'Kalenderberechtigungen konnten nicht abgerufen werden. Dies erfordert entweder delegierte Berechtigungen'
        => 'Calendar permissions could not be retrieved. This requires either delegated permissions',
    'oder den Exchange Admin-Zugriff.'                   => 'or Exchange admin access.',
    '&rarr; Exchange Admin Center öffnen'                => '&rarr; Open Exchange Admin Center',
    'Benutzer'                                            => 'User',
    'Rolle'                                               => 'Role',
    'Intern'                                              => 'Internal',
    'Vollzugriff (Full Access) und &bdquo;Senden als&ldquo;-Berechtigungen werden über das'
        => 'Full Access and “Send As” permissions are managed via the',
    'verwaltet.'                                          => '.',

    // ── Mailboxes: forwarding ───────────────────────────────────────────────
    'Externe Weiterleitung ist der häufigste Exfiltrations­vektor'
        => 'External forwarding is the most common exfiltration vector',
    'bei kompromittierten Konten.'                       => 'for compromised accounts.',
    'M365 kennt dafür'                                   => 'M365 has',
    'zwei Mechanismen'                                   => 'two mechanisms',
    'die'                                                 => 'the',
    'Postfach-Weiterleitung'                             => 'Mailbox forwarding',
    '(am Postfach gesetzt)'                              => '(set on the mailbox)',
    'und'                                                 => 'and',
    'Posteingangsregeln'                                 => 'Inbox rules',
    'die Mails weiterleiten/umleiten. Beide werden hier geprüft.'
        => 'that forward/redirect mail. Both are checked here.',
    'Neu scannen'                                        => 'Rescan',
    'Weiterleitungen gesamt'                             => 'Total forwards',
    'externe Adressen'                                   => 'external addresses',
    'Aktive Weiterleitungen'                            => 'Active forwards',
    'Auch lokal zustellen'                              => 'Also deliver locally',
    'Keine Postfach-Weiterleitungen gefunden'          => 'No mailbox forwards found',
    'Kein Postfach leitet per Postfach-Einstellung extern weiter — der gewünschte Zustand.'
        => 'No mailbox forwards externally via mailbox settings — the desired state.',
    'Benutzer oder Adresse suchen…'                     => 'Search user or address…',
    'Weiterleitungsadresse'                             => 'Forwarding address',
    'Lokal&nbsp;+&nbsp;Weiterleiten'                    => 'Local&nbsp;+&nbsp;forward',
    'Aktion'                                             => 'Action',
    'Weiterleitung für :name wirklich entfernen?'       => 'Really remove forwarding for :name?',
    'Entfernen'                                          => 'Remove',
    'Externe Auto-Forwards'                             => 'External auto-forwards',
    'Regeln, die nach extern leiten'                    => 'rules forwarding externally',
    'Interne Auto-Forwards'                             => 'Internal auto-forwards',
    'in Tenant-eigene Domains'                          => 'to tenant-owned domains',
    'Lösch-Regeln'                                      => 'Delete rules',
    'verdächtig bei Phishing'                           => 'suspicious in phishing',
    'Postfächer gescannt'                               => 'Mailboxes scanned',
    ':n nicht lesbar'                                    => ':n not readable',
    'Limit erreicht — erste 500'                        => 'Limit reached — first 500',
    'alle aktiven User'                                 => 'all active users',
    'Externe Auto-Weiterleitungen'                      => 'External auto-forwards',
    'Keine Inbox-Regeln, die an externe Adressen weiterleiten.'
        => 'No inbox rules forwarding to external addresses.',
    'Regel-Name'                                         => 'Rule name',
    'Weiterleitung an'                                  => 'Forwards to',
    'Inbox-Regeln, die Mails löschen'                  => 'Inbox rules that delete mail',
    'Oft mit Phishing-Hijack kombiniert: eine Regel löscht Sicherheits-Benachrichtigungen, damit der echte User nichts merkt.'
        => 'Often combined with a phishing hijack: a rule deletes security notifications so the real user notices nothing.',
    'Interne Auto-Weiterleitungen'                      => 'Internal auto-forwards',
    '(weniger kritisch)'                                => '(less critical)',

    // ── Mailboxes: shared ───────────────────────────────────────────────────
    'Übersicht aller freigegebenen Postfächer im Tenant'
        => 'Overview of all shared mailboxes in the tenant',
    'Postfachberechtigungen'                            => 'Mailbox permissions',
    'werden über Exchange Online verwaltet und sind über die Graph API nicht direkt abrufbar.'
        => 'are managed via Exchange Online and cannot be retrieved directly through the Graph API.',
    'Verwalten Sie Berechtigungen im Exchange Admin Center.'
        => 'Manage permissions in the Exchange Admin Center.',
    'Exchange Admin Center öffnen'                      => 'Open Exchange Admin Center',
    'Mit Auto-Antwort aktiv'                            => 'With auto-reply active',
    'Auto-Reply eingeschaltet'                          => 'Auto-reply enabled',
    'Mit externer Weiterleitung'                        => 'With external forwarding',
    'Weiterleitung konfiguriert'                        => 'Forwarding configured',
    'Keine freigegebenen Postfächer gefunden'          => 'No shared mailboxes found',
    'Es wurden keine deaktivierten, lizenzierten Benutzerkonten gefunden, die als Shared Mailboxes fungieren.'
        => 'No disabled, licensed user accounts acting as shared mailboxes were found.',
    'Alle 30 Min. aktualisiert'                         => 'Updated every 30 min.',
    'Jetzt aktualisieren'                               => 'Refresh now',
    'E-Mail-Adresse'                                    => 'Email address',
    'Erstellt am'                                       => 'Created on',
    'Auto-Antwort'                                      => 'Auto-reply',
    'Aktionen'                                          => 'Actions',
    'Postfach öffnen'                                   => 'Open mailbox',

    // ── Mail flow ───────────────────────────────────────────────────────────
    'Betrieb'                                            => 'Operational',
    'Beeinträchtigt'                                    => 'Degraded',
    'Unterbrochen'                                      => 'Interrupted',
    'Wird wiederhergestellt'                            => 'Restoring',
    'Unbekannt'                                          => 'Unknown',
    'Mittel'                                             => 'Medium',
    'Niedrig'                                            => 'Low',
    'Info'                                               => 'Info',
    'Exchange Online Mailflow-Übersicht und Sicherheitseinstellungen'
        => 'Exchange Online mail flow overview and security settings',
    'Transportregeln und Anti-Spam-Richtlinien werden direkt in Exchange Online und Microsoft Defender verwaltet. Diese Seite zeigt den aktuellen Status und verlinkt zu den entsprechenden Admin-Bereichen.'
        => 'Transport rules and anti-spam policies are managed directly in Exchange Online and Microsoft Defender. This page shows the current status and links to the relevant admin areas.',
    'Exchange Online Status'                            => 'Exchange Online status',
    'Kein bekanntes Problem'                            => 'No known issue',
    'Prüfen Sie die Details'                            => 'Check the details',
    'Keine Daten'                                        => 'No data',
    'Aktive Störungen'                                  => 'Active incidents',
    ':n offen'                                           => ':n open',
    'Keine Störungen'                                   => 'No incidents',
    'Sicherheitswarnungen'                             => 'Security alerts',
    ':n aktiv'                                           => ':n active',
    'Keine Warnungen'                                   => 'No alerts',
    'Verwaltungsbereiche'                              => 'Admin areas',
    'Direkte EAC- & Defender-Links'                    => 'Direct EAC & Defender links',
    'Exchange Online Störungen'                        => 'Exchange Online incidents',
    'Beginn:'                                           => 'Start:',
    'Defender für Office 365 – Aktive Warnungen'       => 'Defender for Office 365 – Active alerts',
    'Keine aktiven Sicherheitswarnungen von Defender für Office 365.'
        => 'No active security alerts from Defender for Office 365.',
    'Titel'                                              => 'Title',
    'Schweregrad'                                       => 'Severity',
    'Kategorie'                                          => 'Category',
    'Verwaltung in Exchange Online &amp; Microsoft Defender'
        => 'Management in Exchange Online &amp; Microsoft Defender',
    'Diese Funktionen werden außerhalb der Graph API verwaltet — direkter Zugriff auf die Verwaltungsoberflächen:'
        => 'These features are managed outside the Graph API — direct access to the management interfaces:',
    'Öffnen'                                            => 'Open',
    'Exchange Online – Dienststatus'                   => 'Exchange Online – Service status',
    'Abgerufen:'                                        => 'Retrieved:',
    'Uhr'                                               => '',
    'Exchange Online meldet aktuell keinen Normalbetrieb. Prüfen Sie die'
        => 'Exchange Online is not currently reporting normal operation. Check the',
    'für Details.'                                      => 'for details.',
    'Schutzrichtlinien konfigurieren (Defender for Office 365 / EOP)'
        => 'Configure protection policies (Defender for Office 365 / EOP)',
    'Anti-Phishing, Anti-Spam, Anti-Malware, Safe Links/Attachments und Transport-Regeln lassen sich '
    . '<strong>nicht über die Microsoft Graph API</strong> setzen. Konfiguration im '
    . '<strong>Microsoft-Defender-Portal</strong> oder per <strong>Exchange-Online-PowerShell</strong>:'
        => 'Anti-phishing, anti-spam, anti-malware, Safe Links/Attachments and transport rules '
        . '<strong>cannot be set via the Microsoft Graph API</strong>. Configure them in the '
        . '<strong>Microsoft Defender portal</strong> or via <strong>Exchange Online PowerShell</strong>:',
    'Preset-Sicherheitsrichtlinien (Defender)'         => 'Preset security policies (Defender)',
    'Anti-Phishing (Defender)'                          => 'Anti-phishing (Defender)',
    'Safe Links (Defender)'                             => 'Safe Links (Defender)',
    'Transport-Regeln (Exchange Admin)'                => 'Transport rules (Exchange Admin)',
    'Mit Exchange Online PowerShell verbinden'         => 'Connect to Exchange Online PowerShell',
    'Anti-Phishing härten'                             => 'Harden anti-phishing',
    'Externe Auto-Weiterleitung tenant-weit blockieren'
        => 'Block external auto-forwarding tenant-wide',
    'Safe Links aktivieren'                            => 'Enable Safe Links',
    'Safe Attachments aktivieren'                      => 'Enable Safe Attachments',
    '„External"-Tag in Outlook aktivieren'             => 'Enable the “External” tag in Outlook',

    // ── Domain health ───────────────────────────────────────────────────────
    'Domains gesamt'                                    => 'Total domains',
    'Verifizierte Domains'                             => 'Verified domains',
    'Vollständig geschützt'                            => 'Fully protected',
    'Mit Problemen'                                     => 'With issues',
    'Handlungsbedarf'                                   => 'Action required',
    'Strikte Richtlinie'                               => 'Strict policy',
    ':n Domain(s) ohne DMARC-Schutz oder mit p=none'   => ':n domain(s) without DMARC protection or with p=none',
    'E-Mail-Spoofing auf diesen Domains ist möglich. Richten Sie DMARC mit mindestens p=quarantine ein.'
        => 'Email spoofing is possible on these domains. Set up DMARC with at least p=quarantine.',
    'Domain suchen…'                                   => 'Search domain…',
    'Standard'                                          => 'Default',
    'Schutzlevel'                                       => 'Protection level',
    'Fehlt'                                             => 'Missing',
    'Vollständig'                                       => 'Complete',
    'Teilweise'                                         => 'Partial',
    'Keine verifizierten Domains gefunden'             => 'No verified domains found',
    '(Sender Policy Framework) legt fest, welche Server E-Mails für eine Domain versenden dürfen, um Spoofing zu verhindern.'
        => '(Sender Policy Framework) defines which servers may send email for a domain, to prevent spoofing.',
    'signiert ausgehende E-Mails kryptografisch, damit Empfänger die Echtheit prüfen können.'
        => 'cryptographically signs outgoing emails so recipients can verify authenticity.',
    'definiert, wie Empfänger mit nicht konformen E-Mails umgehen sollen, und ermöglicht Berichte an den Domain-Inhaber.'
        => 'defines how recipients should handle non-compliant emails and enables reports to the domain owner.',
    'DKIM aktivieren'                                   => 'Enable DKIM',
    'DKIM-Signierung lässt sich <strong>nicht über die Microsoft Graph API</strong> aktivieren. '
    . 'Schritt 1: Befehl ausführen (legt die Signaturkonfiguration an). Schritt 2: die beiden von '
    . 'Microsoft angezeigten <code>CNAME</code>-Records (selector1/selector2) bei deinem DNS-Anbieter '
    . 'veröffentlichen. Schritt 3: DKIM einschalten. SPF/DMARC sind reine DNS-Einträge.'
        => 'DKIM signing <strong>cannot be enabled via the Microsoft Graph API</strong>. '
        . 'Step 1: run the command (creates the signing configuration). Step 2: publish the two '
        . '<code>CNAME</code> records shown by Microsoft (selector1/selector2) at your DNS provider. '
        . 'Step 3: turn DKIM on. SPF/DMARC are plain DNS records.',
    'DKIM im Defender-Portal'                          => 'DKIM in the Defender portal',
    'DKIM-Status je Domain prüfen'                     => 'Check DKIM status per domain',
    'DKIM einrichten & aktivieren'                     => 'Set up & enable DKIM',

    // ── Exchange migration ──────────────────────────────────────────────────
    'Bereit'                                            => 'Ready',
    'Achtung'                                           => 'Caution',
    'Tenant:'                                           => 'Tenant:',
    'Neu prüfen'                                        => 'Recheck',
    'Keine Custom-Domain gefunden'                     => 'No custom domain found',
    'Es wurden keine verifizierten Custom-Domains gefunden — nur'
        => 'No verified custom domains were found — only',
    'Für Exchange Online benötigst du mindestens eine eigene Domain (z.B.'
        => 'For Exchange Online you need at least one own domain (e.g.',
    'Was zu tun ist:'                                  => 'What to do:',
    'Domain im Microsoft 365 Admin Center hinzufügen, den angezeigten TXT-Eintrag bei deinem DNS-Provider eintragen, dann verifizieren.'
        => 'Add the domain in the Microsoft 365 Admin Center, enter the displayed TXT record at your DNS provider, then verify it.',
    'Domains im Admin Center öffnen'                   => 'Open domains in the Admin Center',
    'DNS-Prüfung für'                                  => 'DNS check for',
    'Prüfung'                                           => 'Check',
    'Ergebnis / Hinweis'                               => 'Result / note',
    'MX-Eintrag'                                        => 'MX record',
    'Erwartet:'                                         => 'Expected:',
    'Achtung:'                                          => 'Caution:',
    'Erst nach Abschluss der Migration umstellen!'     => 'Only switch after the migration is complete!',
    'Admin Center → Domains (MX-Wert anzeigen)'        => 'Admin Center → Domains (show MX value)',
    'Empfehlung:'                                       => 'Recommendation:',
    'Admin Center → Domains (DNS-Einträge prüfen)'     => 'Admin Center → Domains (check DNS records)',
    '(Exchange Online ✓)'                              => '(Exchange Online ✓)',
    '(nicht Exchange Online)'                          => '(not Exchange Online)',
    'nicht gefunden'                                   => 'not found',
    'DKIM in Exchange Online aktivieren: Admin Center → E-Mail-Sicherheit → DKIM. Danach die generierten CNAME-Einträge im DNS anlegen.'
        => 'Enable DKIM in Exchange Online: Admin Center → Email security → DKIM. Then create the generated CNAME records in DNS.',
    'Exchange Admin → DKIM aktivieren'                 => 'Exchange Admin → Enable DKIM',
    'Admin Center → Domains (CNAME eintragen)'         => 'Admin Center → Domains (add CNAME)',
    'Empfehlung: TXT-Eintrag'                          => 'Recommendation: TXT record',
    'mit'                                               => 'with',
    'DMARC-Generator (MXToolbox)'                      => 'DMARC generator (MXToolbox)',
    'DMARC ist vorhanden, aber'                        => 'DMARC is present, but',
    'hat keine durchsetzende Wirkung.'                 => 'has no enforcing effect.',
    'Empfohlen:'                                        => 'Recommended:',
    'oder'                                              => 'or',
    'nach Testphase.'                                  => 'after a test phase.',
    'Typ:'                                              => 'Type:',
    'Empfehlung: CNAME'                                => 'Recommendation: CNAME',
    '(erst nach Migration umstellen, damit Outlook-Clients noch auf on-prem zeigen).'
        => '(only switch after migration so Outlook clients still point to on-prem).',
    'Admin Center → Domains (Autodiscover CNAME)'      => 'Admin Center → Domains (Autodiscover CNAME)',
    'Lizenz-Abdeckung Exchange Online'                 => 'License coverage Exchange Online',
    'Keine Lizenzdaten verfügbar.'                     => 'No license data available.',
    'Benutzer mit Exchange Online'                     => 'Users with Exchange Online',
    'Aktive Mitglieder gesamt'                         => 'Total active members',
    'Abdeckung'                                         => 'Coverage',
    'Bereitgestellt'                                   => 'Provisioned',
    'Belegt'                                            => 'Consumed',
    ':n Benutzer haben keine Exchange-Online-Lizenz und können nach der Migration keine Postfächer erhalten.'
        => ':n users have no Exchange Online license and cannot receive mailboxes after the migration.',
    'Lizenzen zuweisen oder prüfen, ob diese Benutzer kein Postfach benötigen.'
        => 'Assign licenses or check whether these users do not need a mailbox.',
    'Hybrid-Status (AAD Connect)'                      => 'Hybrid status (AAD Connect)',
    'AAD Connect / Entra Sync aktiv'                   => 'AAD Connect / Entra Sync active',
    'Nein / unbekannt'                                 => 'No / unknown',
    'Letzter Sync'                                     => 'Last sync',
    'On-Prem-synchronisierte Benutzer'                => 'On-prem synchronized users',
    'Du betreibst eine Hybrid-Umgebung. Exchange Online kann parallel zu Exchange on-prem genutzt werden (Hybrid-Konfiguration empfohlen). Stelle sicher, dass das'
        => 'You operate a hybrid environment. Exchange Online can be used alongside Exchange on-prem (hybrid configuration recommended). Make sure the',
    'installiert ist, bevor du Postfächer migrierst.' => 'is installed before you migrate mailboxes.',
    'Kein Verzeichnis-Sync erkannt — Cloud-Only-Identitäten oder Sync nicht via Entra konfiguriert. Bei Migration von on-prem AD empfiehlt sich Entra Connect Sync.'
        => 'No directory sync detected — cloud-only identities or sync not configured via Entra. When migrating from on-prem AD, Entra Connect Sync is recommended.',
    'Offene Punkte'                                    => 'Open items',
    'Migrations-Checkliste'                            => 'Migration checklist',
    'Exchange Hybrid Configuration Wizard ausgeführt'  => 'Exchange Hybrid Configuration Wizard run',
    'Verbindet on-prem Exchange mit Exchange Online (HCW). Erforderlich für Hybrid-Migration.'
        => 'Connects on-prem Exchange with Exchange Online (HCW). Required for hybrid migration.',
    'Exchange Online Postfach-Migrationsbatch erstellt'
        => 'Exchange Online mailbox migration batch created',
    'In Exchange Admin Center → Migration → Neuer Batch (z. B. Remote Move).'
        => 'In Exchange Admin Center → Migration → New batch (e.g. Remote Move).',
    'MRS Proxy auf on-prem Exchange aktiv'             => 'MRS Proxy active on on-prem Exchange',
    'Mailbox Replication Service Proxy muss auf dem on-prem CAS/MBX aktiviert sein.'
        => 'The Mailbox Replication Service Proxy must be enabled on the on-prem CAS/MBX.',
    'Testpostfach migriert und überprüft'             => 'Test mailbox migrated and verified',
    'Migriere zunächst ein Testpostfach; prüfe E-Mail-Empfang, Kalender und OAB.'
        => 'Migrate a test mailbox first; check email receipt, calendar and OAB.',
    'Outlook Anywhere / MAPI-over-HTTP konfiguriert'   => 'Outlook Anywhere / MAPI-over-HTTP configured',
    'Stellt sicher, dass Outlook-Clients weiterhin auf on-prem Exchange zugreifen können während der Migrationsphase.'
        => 'Ensures Outlook clients can still access on-prem Exchange during the migration phase.',
    'Outlook-Profile der Benutzer nach Migration erneuert'
        => 'Users\' Outlook profiles refreshed after migration',
    'Autodiscover leitet Outlook nach MX-Umschaltung automatisch um; ggf. Profil neu erstellen.'
        => 'Autodiscover redirects Outlook automatically after the MX switch; recreate the profile if needed.',
    'Shared Mailboxes / Room Mailboxes migriert'       => 'Shared mailboxes / room mailboxes migrated',
    'Ressourcenpostfächer separat prüfen und ggf. in Exchange Online neu anlegen.'
        => 'Check resource mailboxes separately and recreate them in Exchange Online if needed.',
    'E-Mail-Archiv geprüft (In-Place Archive / PST)'   => 'Email archive checked (In-Place Archive / PST)',
    'PST-Dateien können über das Microsoft 365 Import Tool hochgeladen werden.'
        => 'PST files can be uploaded via the Microsoft 365 Import Tool.',
    'MX-Eintrag auf Exchange Online umgestellt'        => 'MX record switched to Exchange Online',
    'Erst nach Abschluss der Migration umstellen, damit keine E-Mails verloren gehen.'
        => 'Only switch after the migration is complete so no emails are lost.',
    'Autodiscover CNAME auf outlook.com umgestellt'    => 'Autodiscover CNAME switched to outlook.com',
    'Erst nach der Migration, damit Outlook-Clients das Exchange-Online-Postfach finden.'
        => 'Only after the migration so Outlook clients find the Exchange Online mailbox.',
    'DKIM in Exchange Online aktiviert'                => 'DKIM enabled in Exchange Online',
    'Admin Center → Sicherheit → E-Mail-Authentifizierung → DKIM; CNAME-Einträge im DNS anlegen.'
        => 'Admin Center → Security → Email authentication → DKIM; create CNAME records in DNS.',
    'DMARC-Policy auf quarantine/reject erhöht'        => 'DMARC policy raised to quarantine/reject',
    'Nach erfolgreicher DKIM/SPF-Aktivierung Richtlinie verschärfen.'
        => 'Tighten the policy after DKIM/SPF have been enabled successfully.',
    'on-prem Exchange außer Betrieb genommen (wenn gewünscht)'
        => 'On-prem Exchange decommissioned (if desired)',
    'Erst wenn alle Postfächer online sind und DNS umgestellt ist.'
        => 'Only once all mailboxes are online and DNS has been switched.',
    'Checkboxen werden lokal im Browser gespeichert.'  => 'Checkboxes are stored locally in your browser.',
    'Weiterführende Links'                             => 'Further links',
    'Exchange Admin Center – Migration'                => 'Exchange Admin Center – Migration',

    // ── Message center ──────────────────────────────────────────────────────
    'Microsoft 365 Dienstmeldungen und Änderungsankündigungen'
        => 'Microsoft 365 service messages and change announcements',
    'Nachrichten'                                      => 'Messages',
    'Ungelesene Nachrichten'                           => 'Unread messages',
    'Wichtige Änderungen'                             => 'Major changes',
    'Kritisch/Hoch'                                    => 'Critical/High',
    'Hohe Schwere'                                     => 'High severity',
    'Alle Kategorien'                                  => 'All categories',
    'Zur Information'                                  => 'Stay informed',
    'Änderung planen'                                 => 'Plan for change',
    'Problem beheben'                                  => 'Prevent or fix issue',
    'Anpassung erforderlich'                           => 'Adapt your work',
    'Schwere'                                           => 'Severity',
    'Alle'                                              => 'All',
    'Hoch'                                              => 'High',
    'Normal'                                            => 'Normal',
    'Dienst'                                            => 'Service',
    'Alle Dienste'                                     => 'All services',
    'Nur ungelesene'                                  => 'Unread only',
    'Filtern'                                           => 'Filter',
    'Zurücksetzen'                                     => 'Reset',
    'Keine Nachrichten gefunden'                       => 'No messages found',
    'Keine Nachrichten entsprechen den gewählten Filtern.'
        => 'No messages match the selected filters.',
    'Filter zurücksetzen'                             => 'Reset filters',
    'Es sind keine Message-Center-Nachrichten verfügbar.'
        => 'No message center messages are available.',
    'Im Admin Center öffnen'                          => 'Open in the Admin Center',

    // ── Shared/glossary terms used across the email views ───────────────────
    'Weiterleitung'                                    => 'Forwarding',
    'Status'                                            => 'Status',
    'Regel'                                             => 'Rule',
    'Aktiv'                                             => 'Active',
    'Inaktiv'                                           => 'Inactive',
    'Ja'                                               => 'Yes',
    'Nein'                                              => 'No',
    'Aktualisieren'                                    => 'Refresh',
    'Speichern'                                         => 'Save',
    'Abbrechen'                                         => 'Cancel',
    'Kritisch'                                          => 'Critical',
    'Domain'                                            => 'Domain',
    'Alias'                                             => 'Alias',
];
