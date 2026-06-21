<?php

/**
 * English translations for the Identity & Accounts views
 * (users, guest users, onboarding, offboarding).
 *
 * Keys are the exact German source strings used in the views. Shared glossary
 * terms (Name, Status, Speichern, Abbrechen, Aktiv, Deaktiviert, …) live in the
 * central lang/en.php map and win on collisions, so they are intentionally not
 * duplicated here.
 *
 * @return array<string,string>
 */
return [
    // ── views/users/index.php ───────────────────────────────────────────────
    'MFA registriert'                 => 'MFA registered',
    '% der Benutzer'                  => '% of users',
    'Alle auswählen'                  => 'Select all',
    'Benutzer suchen…'                => 'Search users…',
    'Alle Benutzer'                   => 'All users',
    'Nur aktive'                      => 'Active only',
    'Nur deaktivierte'                => 'Disabled only',
    'MFA nicht registriert'           => 'MFA not registered',
    'Inaktiv > 30 Tage'               => 'Inactive > 30 days',
    'Inaktiv > 90 Tage'               => 'Inactive > 90 days',
    'Keine Lizenz'                    => 'No license',
    'ausgewählt'                      => 'selected',
    'Ausgewählte Benutzer deaktivieren' => 'Disable selected users',
    'MFA zurücksetzen'                => 'Reset MFA',
    'Lizenz zuweisen'                 => 'Assign license',
    'Lizenzen entfernen'              => 'Remove licenses',
    'Alle Lizenzen der ausgewählten Benutzer entfernen' => 'Remove all licenses from the selected users',
    'Lizenz auswählen'               => 'Select license',
    'verfügbar'                       => 'available',
    'UPN'                             => 'UPN',
    'MFA'                             => 'MFA',
    'Letzter Login'                   => 'Last sign-in',
    'Nie'                             => 'Never',
    'Detail'                          => 'Details',
    // Inline JS (confirm dialogs / labels)
    'Ausgewählte Benutzer wirklich deaktivieren?'        => 'Really disable the selected users?',
    'Ausgewählte Benutzer aktivieren?'                   => 'Enable the selected users?',
    'MFA für ausgewählte Benutzer zurücksetzen?'         => 'Reset MFA for the selected users?',
    'Alle Lizenzen der ausgewählten Benutzer entfernen?' => 'Remove all licenses from the selected users?',
    'Aktion ausführen?'              => 'Perform action?',

    // ── views/users/edit.php ────────────────────────────────────────────────
    'Zurück zu'                       => 'Back to',
    'Benutzer bearbeiten'             => 'Edit user',
    'Dieser Benutzer wird aus dem lokalen Active Directory synchronisiert. Felder wie Abteilung und Jobtitel können beim nächsten Sync überschrieben werden.'
        => 'This user is synchronized from the on-premises Active Directory. Fields such as department and job title may be overwritten on the next sync.',
    'Jobtitel'                        => 'Job title',
    'Mobiltelefon'                    => 'Mobile phone',
    'Bürostandort'                    => 'Office location',

    // ── views/users/detail.php ──────────────────────────────────────────────
    'Zurück zu Benutzer'              => 'Back to users',
    'Läuft nicht ab'                  => 'Does not expire',
    'Abgelaufen'                      => 'Expired',
    'Läuft in :n Tag(en) ab'          => 'Expires in :n day(s)',
    'Telefon'                         => 'Phone',
    'Passwort geändert'               => 'Password changed',
    'Passwort-Ablauf'                 => 'Password expiry',
    'Reset nur im lokalen AD möglich' => 'Reset only possible in the on-premises AD',
    'Benutzer deaktivieren'           => 'Disable user',
    'Benutzer aktivieren'             => 'Enable user',
    'Benutzer wirklich deaktivieren?' => 'Really disable this user?',
    'Benutzer wirklich aktivieren?'   => 'Really enable this user?',
    'MFA-Methoden für diesen Benutzer wirklich zurücksetzen? Der Benutzer muss MFA neu registrieren.'
        => 'Really reset the MFA methods for this user? The user will have to re-register MFA.',
    'Passwort zurücksetzen'           => 'Reset password',
    'Ein neues temporäres Passwort erzeugen? Der Benutzer muss es bei der nächsten Anmeldung ändern. Das Passwort wird einmalig angezeigt.'
        => 'Generate a new temporary password? The user must change it at the next sign-in. The password is shown only once.',
    'Lizenz entfernen?'               => 'Remove license?',
    'Keine Lizenzen zugewiesen.'      => 'No licenses assigned.',
    'Lizenz auswählen…'               => 'Select license…',
    'Alle verfügbaren Lizenzen bereits zugewiesen.' => 'All available licenses are already assigned.',
    'Anmeldungen'                     => 'Sign-ins',
    'Keine Mitgliedschaften'          => 'No memberships',
    'Keine Anmeldedaten in den letzten 30 Tagen oder die Berechtigung'
        => 'No sign-in data in the last 30 days, or the permission',
    'fehlt. Genaue Diagnose unter'    => 'is missing. Detailed diagnostics under',
    'Anmeldeprotokoll'                => 'Sign-in log',
    'Datum/Uhrzeit'                   => 'Date/time',
    'App'                             => 'App',
    'IP-Adresse'                      => 'IP address',
    'Gerät (OS)'                      => 'Device (OS)',
    'Ergebnis'                        => 'Result',
    'Erfolgreich'                     => 'Successful',
    'Fehlgeschlagen'                  => 'Failed',
    'Cloud-Cleanup (nach AD-Deaktivierung)' => 'Cloud cleanup (after AD deactivation)',
    'Führe diese Schritte aus, nachdem du den Benutzer im lokalen AD deaktiviert hast.'
        => 'Run these steps after you have disabled the user in the on-premises AD.',
    'Cloud-Cleanup starten…'          => 'Start cloud cleanup…',
    'Interne Notizen'                 => 'Internal notes',
    'Notiz wirklich löschen?'         => 'Really delete this note?',
    'Noch keine Notizen vorhanden.'   => 'No notes yet.',
    'Interne Notiz eingeben…'         => 'Enter internal note…',
    'Notiz hinzufügen'                => 'Add note',
    'Cloud-Cleanup für'               => 'Cloud cleanup for',
    'Wähle die Aktionen, die ausgeführt werden sollen. Bereits abgeschlossene Schritte können übersprungen werden.'
        => 'Select the actions to run. Steps that are already completed can be skipped.',
    'Alle aktiven Sitzungen sofort beenden' => 'End all active sessions immediately',
    'Meldet den Benutzer sofort von allen Geräten und Apps ab.'
        => 'Signs the user out of all devices and apps immediately.',
    'Alle Lizenzen entziehen'         => 'Revoke all licenses',
    'Entfernt alle zugewiesenen Microsoft 365-Lizenzen.' => 'Removes all assigned Microsoft 365 licenses.',
    'E-Mail-Weiterleitung setzen'     => 'Set email forwarding',
    'Erfordert'                       => 'Requires',
    '-Berechtigung.'                  => ' permission.',
    'Abwesenheitsnotiz aktivieren'    => 'Enable out-of-office reply',
    'Der Mitarbeiter hat das Unternehmen verlassen...' => 'The employee has left the company...',
    'Cloud-Cleanup wirklich ausführen? Diese Aktion kann nicht rückgängig gemacht werden.'
        => 'Really run the cloud cleanup? This action cannot be undone.',
    'Cloud-Cleanup ausführen'         => 'Run cloud cleanup',

    // ── views/guestusers/index.php ──────────────────────────────────────────
    'Gäste gesamt'                    => 'Total guests',
    'Ausstehende Einladung'           => 'Pending invitation',
    'Nie angemeldet'                  => 'Never signed in',
    ':n inaktive Gastbenutzer'        => ':n inactive guest users',
    'Diese sollten überprüft und ggf. entfernt werden.' => 'These should be reviewed and removed if necessary.',
    'Gast suchen…'                    => 'Search guests…',
    'Ausstehend'                      => 'Pending',
    'Einladung'                       => 'Invitation',
    'Akzeptiert'                      => 'Accepted',
    'Gastbenutzer deaktivieren?'      => 'Disable guest user?',
    'Gastbenutzer wirklich löschen? Diese Aktion kann nicht rückgängig gemacht werden.'
        => 'Really delete this guest user? This action cannot be undone.',
    'Keine Gastbenutzer gefunden'     => 'No guest users found',

    // ── views/onboarding/wizard.php ─────────────────────────────────────────
    'Benutzerdaten'                   => 'User details',
    'Zusammenfassung'                 => 'Summary',
    'Schritt 1 – Benutzerdaten'       => 'Step 1 – User details',
    'Vorname Nachname'                => 'First name Last name',
    'Benutzerprinzipalname (UPN)'     => 'User principal name (UPN)',
    'Standard'                        => 'Default',
    'Domain aus dem Dropdown wählen — es werden nur im Tenant verifizierte Domains angeboten.'
        => 'Select a domain from the dropdown — only domains verified in the tenant are offered.',
    'Tenant-Domains konnten nicht gelesen werden (Berechtigung'
        => 'Tenant domains could not be read (check the',
    'prüfen).'                        => 'permission).',
    'Bitte UPN manuell eingeben — nur Domains, die im Tenant verifiziert sind, werden von Microsoft akzeptiert.'
        => 'Please enter the UPN manually — only domains verified in the tenant are accepted by Microsoft.',
    'Mind. 8 Zeichen'                 => 'Min. 8 characters',
    'Mind. 8 Zeichen, Groß-/Kleinbuchstaben, Zahlen empfohlen'
        => 'Min. 8 characters; upper/lowercase letters and numbers recommended',
    'Berufsbezeichnung'               => 'Job title',
    'z. B. Entwickler'                => 'e.g. Developer',
    'z. B. IT'                        => 'e.g. IT',
    'Nutzungsstandort'                => 'Usage location',
    'DE – Deutschland'                => 'DE – Germany',
    'AT – Österreich'                 => 'AT – Austria',
    'CH – Schweiz'                    => 'CH – Switzerland',
    'US – USA'                        => 'US – USA',
    'GB – Vereinigtes Königreich'     => 'GB – United Kingdom',
    'Schritt 2 – Lizenz zuweisen'     => 'Step 2 – Assign license',
    'Keine verfügbaren Lizenzen gefunden. Entweder sind alle Lizenzen vergeben oder die Berechtigung fehlt.'
        => 'No available licenses found. Either all licenses are assigned or the permission is missing.',
    'Lizenz später manuell zuweisen'  => 'Assign a license manually later',
    'Schritt 3 – Gruppen & Teams'     => 'Step 3 – Groups & Teams',
    'Keine statischen Gruppen gefunden.' => 'No static groups found.',
    'Gruppen durchsuchen…'            => 'Search groups…',
    'Gruppen / Verteiler'             => 'Groups / distribution lists',
    'Es werden maximal 50 Gruppen angezeigt.' => 'A maximum of 50 groups are shown.',
    'Schritt 4 – Zusammenfassung & Erstellen' => 'Step 4 – Summary & create',
    'Ich bestätige die Erstellung dieses Benutzerkontos' => 'I confirm the creation of this user account',
    'Benutzer erstellen'              => 'Create user',
    'Passwortstärke:'                 => 'Password strength:',
    'Schwach'                         => 'Weak',
    'Gut'                             => 'Good',
    'Stark'                           => 'Strong',

    // ── views/offboarding/index.php ─────────────────────────────────────────
    'Benutzer suchen'                 => 'Search user',
    'Name oder E-Mail-Adresse eingeben...' => 'Enter name or email address...',
    'On-Prem-synchronisiert'          => 'On-prem synchronized',
    'Offboarding-Schritte'            => 'Offboarding steps',
    '1. Konto deaktivieren'           => '1. Disable account',
    'Verhindert sofort jede weitere Anmeldung.' => 'Immediately prevents any further sign-in.',
    'Konto ist on-prem synchronisiert — Deaktivierung am besten im lokalen Active Directory durchführen.'
        => 'Account is on-prem synchronized — it is best to disable it in the on-premises Active Directory.',
    'Konto von :name wirklich deaktivieren?' => 'Really disable the account of :name?',
    'Erledigt'                        => 'Done',
    '2. Alle Sitzungen widerrufen'    => '2. Revoke all sessions',
    'Macht alle bestehenden Refresh-Tokens ungültig (Outlook, Teams, Browser etc.).'
        => 'Invalidates all existing refresh tokens (Outlook, Teams, browser, etc.).',
    'Widerrufen'                      => 'Revoke',
    '3. Lizenzen entfernen'           => '3. Remove licenses',
    ':n Lizenz(en) zugewiesen. Entfernen gibt die Lizenzen für andere Benutzer frei.'
        => ':n license(s) assigned. Removing them frees the licenses for other users.',
    'Soll das Postfach als Shared Mailbox erhalten bleiben?' => 'Should the mailbox be kept as a shared mailbox?',
    'Dann zuerst im'                  => 'Then first convert it to a shared mailbox in the',
    'in Shared Mailbox umwandeln — dann benötigt es keine Lizenz mehr.'
        => '— then it no longer needs a license.',
    'Alle :n Lizenz(en) von :name entfernen?' => 'Remove all :n license(s) from :name?',
    'Keine Lizenzen'                  => 'No licenses',
    '4. Aus Gruppen entfernen'        => '4. Remove from groups',
    ':n Gruppe(n) — dynamische Gruppen werden automatisch aktualisiert.'
        => ':n group(s) — dynamic groups are updated automatically.',
    '+:n weitere'                     => '+:n more',
    ':name aus :n Gruppe(n) entfernen?' => 'Remove :name from :n group(s)?',
    'Keine Gruppen'                   => 'No groups',
    '5. Postfach als Shared Mailbox umwandeln (optional)'
        => '5. Convert mailbox to shared mailbox (optional)',
    'Wenn E-Mails weiterhin zugänglich sein sollen (z. B. für Vertretungen).'
        => 'If emails should remain accessible (e.g. for stand-ins).',
    'Erst nach Lizenzentfernung — ein Shared Mailbox benötigt keine eigene Lizenz.'
        => 'Only after removing the license — a shared mailbox does not need its own license.',
    'Exchange Admin'                  => 'Exchange Admin',
    '6. OneDrive-Daten sichern / Zugriff gewähren'
        => '6. Back up OneDrive data / grant access',
    'Einem Manager Zugriff auf das OneDrive gewähren, bevor das Konto dauerhaft gelöscht wird.'
        => 'Grant a manager access to the OneDrive before the account is permanently deleted.',
    'Gelöschte Konten behalten OneDrive-Daten 30 Tage lang.'
        => 'Deleted accounts retain OneDrive data for 30 days.',
    'Benutzerprofil'                  => 'User profile',
    '7. Konto löschen (optional, unwiderruflich!)'
        => '7. Delete account (optional, irreversible!)',
    'Nur löschen, wenn Daten gesichert und alle vorherigen Schritte abgeschlossen sind.'
        => 'Only delete once data is backed up and all previous steps are completed.',
    'Das Konto ist 30 Tage wiederherstellbar.' => 'The account can be restored for 30 days.',
    'In Entra öffnen'                 => 'Open in Entra',
    'Informationen'                   => 'Information',
    'Manager'                         => 'Manager',
    'On-Prem-Sync'                    => 'On-prem sync',
    'Admin-Links'                     => 'Admin links',
    'Entra ID Profil'                 => 'Entra ID profile',
    'Exchange Postfach'               => 'Exchange mailbox',
    'Benutzerprofil (lokal)'          => 'User profile (local)',
    'Suche nach einem Benutzer, um den Offboarding-Prozess zu starten.'
        => 'Search for a user to start the offboarding process.',
];
