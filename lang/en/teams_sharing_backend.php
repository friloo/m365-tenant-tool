<?php

/**
 * English translations for the Teams / Sharing backend layer (SharingPolicies,
 * ShareReview, Sharing, OneDrive, TeamsPolicies, NamedLocations, Backup,
 * BreakGlass, TokenLifetime services & controllers) — display labels, finding
 * texts, email/notification strings and flash messages generated in PHP and
 * rendered into views via t().
 *
 * Keys match the German source EXACTLY. API field names, enums, status codes
 * and comparison values are intentionally NOT included here.
 *
 * @return array<string,string>
 */
return [

    // ── SharingPoliciesService: capability / link / permission labels ──
    'Nur intern'                  => 'Internal only',
    'Nur bestehende Gäste'        => 'Existing guests only',
    'Neue & bestehende Gäste'     => 'New & existing guests',
    'Alle (inkl. Anyone-Links)'   => 'Everyone (incl. anyone links)',
    'Unbekannt'                   => 'Unknown',
    '🌐 Jeder mit dem Link (anonym)' => '🌐 Anyone with the link (anonymous)',
    '👤 Nur eingeladen Personen'  => '👤 Invited people only',
    '🏢 Personen in der Organisation' => '🏢 People in the organisation',
    '👁 Anzeigen'                 => '👁 View',
    '✏️ Bearbeiten'               => '✏️ Edit',

    // ── SharingPolicies / page titles ──
    'Freigaberichtlinien'         => 'Sharing policies',

    // ── SharingPoliciesController: flash messages ──
    'SharePoint-Freigabeeinstellungen gespeichert.' => 'SharePoint sharing settings saved.',
    'Fehler beim Speichern: '     => 'Error while saving: ',
    'Ungültige Eingabe.'          => 'Invalid input.',
    'Freigabe-Einstellung für die Site aktualisiert.' => 'Sharing setting for the site updated.',
    'Fehler: '                    => 'Error: ',

    // ── ShareReviewController: page titles & flash messages ──
    'Freigaben-Monitor'           => 'Sharing monitor',
    'Bitte geben Sie eine Begründung ein (mindestens 5 Zeichen).' => 'Please enter a justification (at least 5 characters).',
    'Freigabe wurde widerrufen.'  => 'Share has been revoked.',
    'Erinnerung wurde gesendet.'  => 'Reminder has been sent.',
    'E-Mail konnte nicht gesendet werden. SMTP konfiguriert?' => 'Email could not be sent. Is SMTP configured?',

    // ── ShareReviewService: revoke error ──
    'Widerruf fehlgeschlagen: '   => 'Revocation failed: ',

    // ── ShareReviewService: scan coverage notes (cron log / UI) ──
    'WARNUNG: Abdeckung UNVOLLSTÄNDIG — aus Performance-Gründen wurden nur die ersten :max Sites (je 3 Bibliotheken, Ordnertiefe 3, max. 15 Unterordner/Ebene) gescannt. Nicht gefundene Freigaben bedeuten NICHT, dass keine existieren.'
        => 'WARNING: Coverage INCOMPLETE — for performance reasons only the first :max sites (3 libraries each, folder depth 3, max. 15 subfolders/level) were scanned. Shares that were not found do NOT mean that none exist.',
    'Abdeckung vollständig im Rahmen der Limits (:max Sites, Ordnertiefe 3).'
        => 'Coverage complete within the limits (:max sites, folder depth 3).',

    // ── ShareReviewService: email scope labels ──
    '🌐 <strong>Öffentlich (Anyone-Link)</strong> — kein Login erforderlich'
        => '🌐 <strong>Public (anyone link)</strong> — no login required',
    '👥 <strong>Externe Benutzer</strong>' => '👥 <strong>External users</strong>',
    '🏢 <strong>Organisation</strong>'     => '🏢 <strong>Organisation</strong>',

    // ── ShareReviewService: review email body ──
    'Freigabe-Überprüfung erforderlich' => 'Share review required',
    'Datei öffnen'                => 'Open file',
    'Datei/Ordner'                => 'File/folder',
    'Standort'                    => 'Location',
    'Freigabe-Typ'                => 'Share type',
    'Sie haben eine Datei oder einen Ordner freigegeben, die regelmäßig überprüft werden muss:'
        => 'You have shared a file or folder that must be reviewed regularly:',
    'Ist diese Freigabe noch notwendig?' => 'Is this share still necessary?',
    'Klicken Sie auf den folgenden Link, geben Sie eine kurze Begründung ein und bestätigen Sie — die Freigabe wird dann automatisch um :days Tage verlängert:'
        => 'Click the following link, enter a short justification and confirm — the share will then be extended automatically by :days days:',
    '✓ Freigabe bestätigen'       => '✓ Confirm share',
    '⚠️ Wenn Sie nicht bis zum :date reagieren, wird die Freigabe automatisch widerrufen.'
        => '⚠️ If you do not respond by :date, the share will be revoked automatically.',
    'Dieser Link ist personalisiert und kann nur einmal verwendet werden.'
        => 'This link is personalised and can only be used once.',

    // ── ShareReviewService: email subjects ──
    'Freigabe-Überprüfung erforderlich: :item' => 'Share review required: :item',
    'Erinnerung: Freigabe-Überprüfung: :item'  => 'Reminder: share review: :item',
    'Freigabe automatisch widerrufen: :item'   => 'Share revoked automatically: :item',

    // ── ShareReviewService: revocation notice ──
    'Freigabe widerrufen'         => 'Share revoked',
    'Die folgende Freigabe wurde automatisch widerrufen, da keine Bestätigung erfolgte:'
        => 'The following share was revoked automatically because no confirmation was received:',
    'Falls diese Freigabe weiterhin benötigt wird, erstellen Sie sie bitte erneut und wenden Sie sich an Ihren Administrator.'
        => 'If this share is still needed, please create it again and contact your administrator.',

    // ── SharingController: page titles & flash messages ──
    'Freigaben'                   => 'Shares',
    'Ungültige Parameter.'        => 'Invalid parameters.',
    'Widerrufen fehlgeschlagen: ' => 'Revocation failed: ',
    'Scan-Fehler: '               => 'Scan error: ',
    'Scan abgeschlossen — :found neue Freigaben gefunden.' => 'Scan complete — :found new shares found.',
    ':count Freigaben gescannt/aktualisiert.' => ':count shares scanned/updated.',

    // ── SharingController: CSV export headers ──
    'Typ'                         => 'Type',
    'Name'                        => 'Name',
    'Quelle'                      => 'Source',
    'Besitzer'                    => 'Owner',
    'Erstmals erkannt'            => 'First detected',
    'Status'                      => 'Status',

    // ── OneDriveController: page titles ──
    'OneDrive – Persönliche Laufwerke' => 'OneDrive – Personal drives',

    // ── OneDriveController: flash & load error messages ──
    'Benutzer nicht ladbar: '     => 'Users could not be loaded: ',
    'OneDrive wurde erfolgreich provisioniert.' => 'OneDrive was provisioned successfully.',
    'Provisionierung fehlgeschlagen — prüfen Sie Lizenzzuweisung und Berechtigungen.'
        => 'Provisioning failed — check the license assignment and permissions.',
    'OneDrive wurde gelöscht (Papierkorb). Endgültige Löschung nach 93 Tagen.'
        => 'OneDrive was deleted (recycle bin). Permanent deletion after 93 days.',
    'Fehler beim Löschen: '       => 'Error while deleting: ',

    // ── OneDriveService: deprovision error ──
    'OneDrive-Sites können nicht über die Graph-API gelöscht werden. Bitte im SharePoint Admin Center → „Aktive Sites" nach der OneDrive-URL des Benutzers suchen und dort löschen (alternativ per PnP PowerShell: Remove-SPOSite).'
        => 'OneDrive sites cannot be deleted via the Graph API. Please search the SharePoint Admin Center → "Active sites" for the user\'s OneDrive URL and delete it there (alternatively via PnP PowerShell: Remove-SPOSite).',

    // ── TeamsPoliciesController: page title ──
    'Teams-Übersicht & Richtlinien' => 'Teams overview & policies',

    // ── NamedLocationsController: page title & flash messages ──
    'Named Locations (Vertrauenswürdige Standorte)' => 'Named locations (trusted locations)',
    'Name und mindestens ein Ländercode sind erforderlich.' => 'A name and at least one country code are required.',
    'Länder-Standort ":name" wurde angelegt.' => 'Country location ":name" was created.',
    'Name und mindestens ein IP-Bereich sind erforderlich.' => 'A name and at least one IP range are required.',
    'IP-Standort ":name" wurde angelegt.'     => 'IP location ":name" was created.',
    'Standort wurde gelöscht.'    => 'Location was deleted.',
    'Löschen fehlgeschlagen: '    => 'Deletion failed: ',

    // ── BackupController: page title & flash messages ──
    'Backup-Status (3rd-Party)'   => 'Backup status (3rd-party)',
    'Backup-Konfiguration gespeichert.' => 'Backup configuration saved.',

    // ── BackupController: health findings ──
    'Kein Backup-Anbieter eingetragen — M365-Daten haben keine echte Wiederherstellung über die Microsoft-Recycle-Bin-Frist hinaus (30-93 Tage).'
        => 'No backup provider configured — M365 data has no real recovery beyond the Microsoft recycle-bin window (30-93 days).',
    'Backup-Anbieter eingetragen, aber keine Workloads markiert — Coverage unbekannt.'
        => 'Backup provider configured, but no workloads marked — coverage unknown.',
    'Nicht alle M365-Workloads (Mail, OneDrive, SharePoint, Teams) gesichert.'
        => 'Not all M365 workloads (Mail, OneDrive, SharePoint, Teams) are backed up.',
    'Kein Datum für letzten Backup-Lauf eingetragen.'
        => 'No date entered for the last backup run.',
    'Letzter Backup-Lauf vor :days Tagen — sollte täglich laufen.'
        => 'Last backup run :days days ago — it should run daily.',
    'Letzter Backup-Lauf vor :days Tagen.' => 'Last backup run :days days ago.',
    'Letzter Backup-Lauf war nicht erfolgreich: :status'
        => 'The last backup run was not successful: :status',
    'Aufbewahrungsfrist nicht dokumentiert.' => 'Retention period not documented.',
    'Aufbewahrung :days Tage — empfohlen mindestens 90, bei DSGVO-Pflicht oft 7 Jahre.'
        => 'Retention :days days — at least 90 recommended, often 7 years where required by GDPR.',
    'Restore-Test nicht dokumentiert — ein nie getesteter Backup ist ein unbekannter Backup.'
        => 'Restore test not documented — a backup that has never been tested is an unknown backup.',
    'Letzter Restore-Test vor mehr als einem Jahr.'
        => 'Last restore test more than a year ago.',

    // ── BreakGlassService: account check findings ──
    'Konto nicht gefunden: '      => 'Account not found: ',
    'Konto nicht gefunden im Tenant.' => 'Account not found in the tenant.',
    'Konto ist deaktiviert — im Notfall nicht nutzbar!'
        => 'Account is disabled — unusable in an emergency!',
    'Hat keine permanente Global-Administrator-Rolle (auch nicht über eine Gruppe). Nur via PIM-Eligible wäre im Notfall problematisch, weil die Aktivierung MFA verlangt.'
        => 'Has no permanent Global Administrator role (not even via a group). PIM-eligible only would be problematic in an emergency, because activation requires MFA.',
    'Account ist aus KEINER aktiven CA-Policy ausgeschlossen (weder direkt noch über eine Gruppe) — Gefahr: bei einem CA-Fehler sperrst du dich aus.'
        => 'Account is excluded from NO active CA policy (neither directly nor via a group) — risk: a CA misconfiguration could lock you out.',
    'Account hat sich noch nie angemeldet — bitte einmal testen, ob er funktioniert.'
        => 'Account has never signed in — please test once that it works.',
    'Letzter Login vor :days Tagen — Account-Test wird empfohlen (≤ 180 Tage).'
        => 'Last login :days days ago — an account test is recommended (≤ 180 days).',

    // ── TokenLifetimeService: recommendations ──
    'Keine CA-Policy mit Sign-in-Frequency konfiguriert. Microsoft-Default ist 90 Tage Refresh-Token — für Admin-Konten viel zu lang.'
        => 'No CA policy with sign-in frequency configured. The Microsoft default is a 90-day refresh token — far too long for admin accounts.',
    'CA-Policy ":name" hat Sign-in-Frequency > 30 Tage — für administrative Apps zu lang.'
        => 'CA policy ":name" has a sign-in frequency > 30 days — too long for administrative apps.',
];
