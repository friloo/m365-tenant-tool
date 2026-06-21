<?php

/**
 * English translations for the Teams / OneDrive / SharePoint / Sharing views.
 *
 * Keys are the exact German source strings used in the views. Whenever a
 * German string is wrapped in t()/te() and not yet present here, the German
 * original is shown as a graceful fallback.
 *
 * @return array<string,string>
 */
return [
    // ── Module tabs ─────────────────────────────────────────────────────────
    'Übersicht'                        => 'Overview',
    'Nutzung'                          => 'Usage',
    'Governance'                       => 'Governance',
    'Freigaben'                        => 'Shares',
    'Monitor'                          => 'Monitor',
    'Richtlinien'                      => 'Policies',

    // ── teamspolicies/index.php ─────────────────────────────────────────────
    'Teams gesamt'                     => 'Teams total',
    'Privat'                           => 'Private',
    'Öffentlich'                       => 'Public',
    'Dynamische Mitgliedschaft'        => 'Dynamic membership',
    'Neu laden'                        => 'Reload',
    'Teams App-Einstellungen'          => 'Teams app settings',
    'Aktiv'                            => 'Active',
    'Deaktiviert'                      => 'Disabled',
    'Organisationseigene Apps'         => 'Organization-owned apps',
    'Name'                             => 'Name',
    'Verteilungsmethode'               => 'Distribution method',
    'Teams im Tenant'                  => 'Teams in tenant',
    ':n öffentliche Teams — diese sind für alle Benutzer im Tenant sichtbar und beitrittsfähig.'
        => ':n public Teams — these are visible to and joinable by all users in the tenant.',
    'Teams Admin Center'               => 'Teams Admin Center',
    'Sichtbarkeit'                     => 'Visibility',
    'Mitgliedschaft'                   => 'Membership',
    'Erstellt'                         => 'Created',
    'Dynamisch'                        => 'Dynamic',
    'Manuell'                          => 'Manual',
    'Erweiterte Teams-Richtlinien (Meeting-Richtlinien, Messaging-Richtlinien, Calling-Richtlinien) sind nur über'
        => 'Advanced Teams policies (meeting policies, messaging policies, calling policies) are only manageable through',
    'oder PowerShell (MicrosoftTeams-Modul) verwaltbar — diese APIs sind nicht Teil von Microsoft Graph.'
        => 'or PowerShell (MicrosoftTeams module) — these APIs are not part of Microsoft Graph.',

    // ── teamsusage/index.php ────────────────────────────────────────────────
    'Keine Teams-Nutzungsdaten verfügbar' => 'No Teams usage data available',
    'Im gewählten Zeitraum wurde keine Teams-Aktivität erfasst.'
        => 'No Teams activity was recorded in the selected period.',
    'Gesamt Nutzer'                    => 'Total users',
    'im Report erfasst'                => 'recorded in the report',
    'Aktiv (letzte 30 Tage)'           => 'Active (last 30 days)',
    'mind. eine Aktivität'             => 'at least one activity',
    'Inaktiv'                          => 'Inactive',
    'keine Aktivität in 30 Tagen'      => 'no activity in 30 days',
    'Ø Nachrichten/Nutzer'             => 'Avg. messages/user',
    'bei aktiven Nutzern'              => 'among active users',
    'Top 10 Chat'                      => 'Top 10 Chat',
    'Keine Daten'                      => 'No data',
    'Nutzer'                           => 'User',
    'Top 10 Anrufe'                    => 'Top 10 Calls',
    'Anrufe'                           => 'Calls',
    'Top 10 Meetings'                  => 'Top 10 Meetings',
    'Nutzer suchen…'                   => 'Search users…',
    'Filter:'                          => 'Filter:',
    'Alle'                             => 'All',
    'Team-Chats'                       => 'Team chats',
    'Privat-Chats'                     => 'Private chats',
    'Letzte Aktivität'                 => 'Last activity',

    // ── teamsgovernance/index.php ───────────────────────────────────────────
    'Ohne Besitzer'                    => 'No owner',
    'Kein Owner zugewiesen'            => 'No owner assigned',
    'Sichtbarkeit: Public'             => 'Visibility: Public',
    'Älter als :n Tage'                => 'Older than :n days',
    'Überprüfung empfohlen'            => 'Review recommended',
    'Teams älter als:'                 => 'Teams older than:',
    ':n Tage'                          => ':n days',
    'Team suchen…'                     => 'Search team…',
    'Alter'                            => 'Age',
    'Besitzer'                         => 'Owner',
    'Aktionen'                         => 'Actions',
    'In Teams öffnen'                  => 'Open in Teams',
    'Keine Teams gefunden'             => 'No Teams found',

    // ── onedrive/index.php ──────────────────────────────────────────────────
    'Speicher-Übersicht'               => 'Storage overview',
    'Persönliche Laufwerke'            => 'Personal drives',
    'provisionierte Laufwerke'         => 'provisioned drives',
    'Gesamt belegt'                    => 'Total used',
    'Top-Verbraucher'                  => 'Top consumer',
    'Benutzer suchen…'                 => 'Search users…',
    'Alle persönlichen Laufwerke'      => 'All personal drives',
    'Benutzer'                         => 'User',
    'Belegt'                           => 'Used',
    'Gesamt'                           => 'Total',
    'Status'                           => 'Status',
    'Normal'                           => 'Normal',
    'Warnung'                          => 'Warning',
    'Keine Daten verfügbar'            => 'No data available',

    // ── onedrive/personal.php ───────────────────────────────────────────────
    'Eingeschränkte Ansicht:'          => 'Restricted view:',
    'Der OneDrive-Nutzungsbericht ('   => 'The OneDrive usage report (',
    ') ist nicht verfügbar. Die Daten werden per Einzelabfrage ermittelt (erste 150 Benutzer).'
        => ') is not available. Data is determined by individual queries (first 150 users).',
    'Mögliche Ursachen: Berechtigung fehlt, Berichtsverschleierung aktiv (M365 Admin Center →'
        => 'Possible causes: permission missing, report obfuscation active (M365 Admin Center →',
    'Einstellungen → Dienste → Berichte → „Anonymisierte Benutzerberichte" deaktivieren),'
        => 'Settings → Services → Reports → disable "Anonymized user reports"),',
    'oder'                             => 'or',
    'Cache aktualisieren'              => 'refresh cache',
    'Mit persönlichem OneDrive'        => 'With personal OneDrive',
    '% der Benutzer'                   => '% of users',
    'Ohne OneDrive'                    => 'Without OneDrive',
    'kein Laufwerk provisioniert'      => 'no drive provisioned',
    'Welche Gruppen dürfen OneDrives provisionieren?'
        => 'Which groups may provision OneDrives?',
    'Diese Einstellung wird im'        => 'This setting is managed in the',
    'unter'                            => 'under',
    'Einstellungen → OneDrive'         => 'Settings → OneDrive',
    'Das Tool kann die Provisionierung einzelner Benutzer direkt über die Microsoft Graph API auslösen.'
        => 'The tool can trigger provisioning for individual users directly via the Microsoft Graph API.',
    'Alle Benutzer'                    => 'All users',
    'Mit OneDrive'                     => 'With OneDrive',
    'Nur aktive Konten'                => 'Active accounts only',
    'Aktualisieren'                    => 'Refresh',
    'Dateien'                          => 'Files',
    'OneDrive provisionieren'          => 'Provision OneDrive',
    'Provisionieren'                   => 'Provision',
    'Kopiert die OneDrive-URL und öffnet das SharePoint Admin Center. Dort unter „Aktive Sites" die URL in die Suche einfügen — OneDrives sind sonst ausgeblendet — und die Site löschen.'
        => 'Copies the OneDrive URL and opens the SharePoint Admin Center. There, under "Active sites", paste the URL into the search — OneDrives are hidden otherwise — and delete the site.',
    'OneDrive löschen…'                => 'Delete OneDrive…',
    'OneDrive-Sites können nur im SharePoint Admin Center gelöscht werden (unter „Aktive Sites" nach der OneDrive-URL suchen).'
        => 'OneDrive sites can only be deleted in the SharePoint Admin Center (search for the OneDrive URL under "Active sites").',
    'Im SP-Admin entfernen'            => 'Remove in SP Admin',
    'Keine Benutzer gefunden'          => 'No users found',
    'OneDrive für :name provisionieren?' => 'Provision OneDrive for :name?',
    'OneDrive-URL wurde in die Zwischenablage kopiert:'
        => 'OneDrive URL has been copied to the clipboard:',
    'Im gleich geöffneten SharePoint Admin Center unter „Aktive Sites" die URL in das Suchfeld einfügen, die Site auswählen und löschen.'
        => 'In the SharePoint Admin Center that just opened, under "Active sites", paste the URL into the search field, select the site, and delete it.',

    // ── sharepoint/index.php ────────────────────────────────────────────────
    'Sites gesamt'                     => 'Sites total',
    'Site suchen…'                     => 'Search site…',
    'Site-Name'                        => 'Site name',
    'Keine Sites gefunden'             => 'No sites found',

    // ── sharepoint/site.php ─────────────────────────────────────────────────
    '← Zurück zu SharePoint'           => '← Back to SharePoint',
    'Dokumentbibliotheken (:n)'        => 'Document libraries (:n)',
    'Typ'                              => 'Type',
    'Keine Bibliotheken'               => 'No libraries',

    // ── sharing/index.php ───────────────────────────────────────────────────
    'Noch kein Freigaben-Scan durchgeführt.' => 'No sharing scan performed yet.',
    'Klicken Sie auf "Jetzt scannen", um alle SharePoint-Freigaben zu erfassen.'
        => 'Click "Scan now" to capture all SharePoint shares.',
    'Der erste Scan kann je nach Tenant-Größe einige Minuten dauern — bitte die Seite während des Scans geöffnet lassen.'
        => 'The first scan can take a few minutes depending on tenant size — please keep the page open during the scan.',
    'Jetzt scannen'                    => 'Scan now',
    'Aktive Freigaben'                 => 'Active shares',
    'Anonym (Anyone)'                  => 'Anonymous (Anyone)',
    'Externe Benutzer'                 => 'External users',
    'Organisation'                     => 'Organization',
    ':n anonyme Freigaben'             => ':n anonymous shares',
    ' — Dateien mit "Anyone"-Links sind für jeden ohne Anmeldung zugänglich.'
        => ' — files with "Anyone" links are accessible to anyone without signing in.',
    'Freigaben suchen…'                => 'Search shares…',
    'Alle Typen'                       => 'All types',
    'Anonym'                           => 'Anonymous',
    'Alle (ohne widerrufen)'           => 'All (excluding revoked)',
    'Bestätigt'                        => 'Confirmed',
    'Ausstehend'                       => 'Pending',
    'Widerrufen'                       => 'Revoked',
    'Scan starten (kann einige Minuten dauern)' => 'Start scan (may take a few minutes)',
    'Standort'                         => 'Location',
    'Freigabe-Typ'                     => 'Share type',
    'Erstmals erkannt'                 => 'First detected',
    'Externe User'                     => 'External users',
    'Freigabe widerrufen'              => 'Revoke share',
    'Keine Freigaben gefunden'         => 'No shares found',
    'Noch kein Scan durchgeführt — siehe Hinweis oben.'
        => 'No scan performed yet — see the note above.',
    'Soll die Freigabe für'            => 'Should the share for',
    'widerrufen werden?'               => 'be revoked?',
    'Diese Aktion entfernt die Berechtigung dauerhaft in SharePoint und kann nicht rückgängig gemacht werden.'
        => 'This action permanently removes the permission in SharePoint and cannot be undone.',
    'Abbrechen'                        => 'Cancel',
    'Scan läuft…'                      => 'Scan running…',

    // ── sharingpolicies/index.php ───────────────────────────────────────────
    'Zum'                              => 'For',
    'Lesen und Ändern'                 => 'reading and changing',
    'der SharePoint-Mandanteneinstellungen ist'
        => 'the SharePoint tenant settings,',
    'erforderlich.'                    => 'is required.',
    'Für Site-Freigabe-Übersichten reicht'
        => 'Site sharing overviews require only',
    'Einzelne Sites'                   => 'Individual sites',
    'Teams &amp; Extern'               => 'Teams &amp; External',
    'SharePoint-Einstellungen konnten nicht geladen werden.'
        => 'SharePoint settings could not be loaded.',
    'Möglicherweise fehlt die Berechtigung'
        => 'The permission may be missing:',
    'Externer Zugriff'                 => 'External access',
    'Standard-Linktyp'                 => 'Default link type',
    'Standard-Berechtigung'            => 'Default permission',
    'SharePoint &amp; OneDrive — Globale Freigabeeinstellungen'
        => 'SharePoint &amp; OneDrive — Global sharing settings',
    'Steuert, wer Inhalte außerhalb der Organisation teilen darf.'
        => 'Controls who may share content outside the organization.',
    'Alle (inkl. anonyme Links)'       => 'All (incl. anonymous links)',
    'Jeder mit Link, keine Anmeldung erforderlich'
        => 'Anyone with the link, no sign-in required',
    'Neue & bestehende Gäste'          => 'New & existing guests',
    'Externe Benutzer müssen sich anmelden'
        => 'External users must sign in',
    'Nur bestehende Gäste'             => 'Existing guests only',
    'Nur bereits eingeladene Externe'  => 'Only already invited externals',
    'Nur intern'                       => 'Internal only',
    'Keine externen Freigaben möglich' => 'No external sharing possible',
    'Welcher Link-Typ wird standardmäßig beim Teilen vorgeschlagen?'
        => 'Which link type is suggested by default when sharing?',
    '🌐 Jeder mit dem Link (anonym)'   => '🌐 Anyone with the link (anonymous)',
    '🏢 Personen in der Organisation'  => '🏢 People in the organization',
    '👤 Nur bestimmte Personen'        => '👤 Specific people only',
    '👁 Anzeigen'                       => '👁 View',
    '✏️ Bearbeiten'                    => '✏️ Edit',
    'Anonymer Link für Dateien'        => 'Anonymous link for files',
    'Nur anzeigen'                     => 'View only',
    'Anzeigen &amp; bearbeiten'        => 'View &amp; edit',
    'Keine anonymen Links'             => 'No anonymous links',
    'Anonymer Link für Ordner'         => 'Anonymous link for folders',
    'Gast-Benutzer-Synchronisation'    => 'Guest user synchronization',
    'Aktiviert'                        => 'Enabled',
    'Gäste können Inhalte über SharePoint synchronisieren.'
        => 'Guests can synchronize content via SharePoint.',
    'Self-Service-Anmeldung (Externe)' => 'Self-service sign-up (externals)',
    'Externe können sich selbst für den Zugriff registrieren.'
        => 'Externals can register for access themselves.',
    'SharePoint-Einstellungen speichern' => 'Save SharePoint settings',
    'Freigabe-Einstellung pro Site Collection'
        => 'Sharing setting per site collection',
    'Site'                             => 'Site',
    'Aktuell'                          => 'Current',
    'Ändern'                           => 'Change',
    'Keine Sites geladen oder fehlende Berechtigung.'
        => 'No sites loaded or missing permission.',
    'Neue Gäste'                       => 'New guests',
    'Bestehende Gäste'                 => 'Existing guests',
    'Speichern'                        => 'Save',
    'Microsoft Teams Status'           => 'Microsoft Teams status',
    'Teamwork-Daten nicht verfügbar.'  => 'Teamwork data not available.',
    'Teams aktiviert'                  => 'Teams enabled',
    'Ja'                               => 'Yes',
    'Nein'                             => 'No',
    'Erweiterte Teams-Einstellungen (Gäste, externe Channels) werden über'
        => 'Advanced Teams settings (guests, external channels) are managed through',
    'das'                              => 'the',
    'Die Graph API bietet hier nur lesenden Zugriff.'
        => 'The Graph API offers only read access here.',
    'Mandantenübergreifender Zugriff'  => 'Cross-tenant access',
    'Cross-Tenant-Policy nicht lesbar.' => 'Cross-tenant policy not readable.',
    'B2B eingehend'                    => 'B2B inbound',
    'B2B ausgehend'                    => 'B2B outbound',
    'Zum Ändern der mandantenübergreifenden Richtlinien ist die Berechtigung'
        => 'To change the cross-tenant policies, the permission',
    'Änderungen können im'             => 'Changes can be made in the',
    'vorgenommen werden.'              => '.',
    'Admin-Portale'                    => 'Admin portals',
    'Benutzer, Lizenzen, Apps'         => 'Users, licenses, apps',
    'Identitäten, Gäste, CA-Policies'  => 'Identities, guests, CA policies',
    'Sites, Freigaben, Storage'        => 'Sites, shares, storage',
    'Teams, Kanäle, Meetings'          => 'Teams, channels, meetings',
    'Geräte, Compliance, Apps'         => 'Devices, compliance, apps',
    'Sicherheit, DLP, Compliance'      => 'Security, DLP, compliance',
];
