<?php

/**
 * English translations for the Identity & Accounts CONTROLLER (backend) layer
 * — flash messages, CSV column headers/cell values, page titles and the
 * setup-wizard check labels emitted by the controllers (not the views).
 *
 * Keys are the exact German source strings used in the controllers. Dynamic
 * values (names, e-mails, counts) are passed through :param placeholders, so
 * only the surrounding static text is translated here.
 *
 * Shared glossary terms living in the central lang/en.php map win on
 * collisions, so a few short terms (Name, Status, …) are duplicated here only
 * for completeness of the controller-level CSV exports.
 *
 * @return array<string,string>
 */
return [
    // ── UsersController ──────────────────────────────────────────────────────
    'Benutzer'                          => 'Users',
    'Benutzer: '                        => 'Users: ',
    'Benutzer nicht ladbar: '           => 'Unable to load user: ',
    'Benutzer bearbeiten'               => 'Edit user',
    'Benutzer erfolgreich aktualisiert.'=> 'User updated successfully.',
    'Fehler beim Speichern: '           => 'Error while saving: ',
    'Sitzungen beendet'                 => 'Sessions ended',
    'Sitzungen beenden fehlgeschlagen: '=> 'Failed to end sessions: ',
    'Lizenzen entzogen'                 => 'Licenses revoked',
    'Lizenzen entziehen fehlgeschlagen: ' => 'Failed to revoke licenses: ',
    'E-Mail-Weiterleitung gesetzt'      => 'Email forwarding set',
    'Weiterleitung fehlgeschlagen: '    => 'Forwarding failed: ',
    'Abwesenheitsnotiz aktiviert'       => 'Out-of-office reply activated',
    'Abwesenheitsnotiz fehlgeschlagen: '=> 'Out-of-office reply failed: ',
    'Cloud-Cleanup abgeschlossen: '     => 'Cloud cleanup completed: ',
    'Aktiv'                             => 'Active',
    'Deaktiviert'                       => 'Disabled',
    'Ja'                                => 'Yes',
    'Nein'                              => 'No',
    'Deaktiviert: '                     => 'Disabled: ',
    'Aktiviert: '                       => 'Enabled: ',
    'Fehler: '                          => 'Error: ',
    'MFA-Methoden wurden zurückgesetzt.'=> 'MFA methods have been reset.',
    'MFA-Reset fehlgeschlagen: '        => 'MFA reset failed: ',
    'Temporäres Passwort gesetzt (Änderung bei nächster Anmeldung erforderlich): '
                                        => 'Temporary password set (change required at next sign-in): ',
    'Passwort-Reset fehlgeschlagen: '   => 'Password reset failed: ',
    'Lizenz zugewiesen.'                => 'License assigned.',
    'Lizenz-Zuweisung fehlgeschlagen: ' => 'License assignment failed: ',
    'Lizenz entfernt.'                  => 'License removed.',
    'Lizenz-Entfernung fehlgeschlagen: '=> 'License removal failed: ',
    'Notiz gespeichert.'                => 'Note saved.',
    'Notiz gelöscht.'                   => 'Note deleted.',
    'Ungültige Bulk-Aktion oder keine Benutzer ausgewählt.'
                                        => 'Invalid bulk action or no users selected.',
    'Keine Lizenz ausgewählt.'          => 'No license selected.',
    ':ok Lizenzen zugewiesen'           => ':ok licenses assigned',
    ', :errors Fehler.'                 => ', :errors errors.',
    ':ok Benutzer Lizenzen entfernt'    => 'Licenses removed from :ok users',
    'Deaktivieren'                      => 'Disable',
    'Aktivieren'                        => 'Enable',
    'MFA zurücksetzen'                  => 'Reset MFA',
    ':count Benutzer zum :label in die Warteschlange aufgenommen — Verarbeitung durch den Cron-Job.'
                                        => ':count users queued for :label — processing handled by the cron job.',

    // ── GroupsController ─────────────────────────────────────────────────────
    'Gruppen nicht ladbar: '            => 'Unable to load groups: ',
    'Gruppen & Teams'                   => 'Groups & Teams',
    'Gruppe nicht ladbar: '             => 'Unable to load group: ',
    'Gruppe'                            => 'Group',
    'Mitglied hinzugefügt.'             => 'Member added.',
    'Mitglied entfernt.'                => 'Member removed.',
    'Anzeigename darf nicht leer sein.' => 'Display name must not be empty.',
    'Gruppe „:name" wurde erstellt.'    => 'Group ":name" has been created.',
    'Fehler beim Erstellen: '           => 'Error while creating: ',
    'Gruppe wurde gelöscht.'            => 'Group has been deleted.',
    'Fehler beim Löschen: '             => 'Error while deleting: ',
    'Besitzer hinzugefügt.'             => 'Owner added.',
    'Besitzer entfernt.'                => 'Owner removed.',
    'Typ'                               => 'Type',
    'Inaktive Gruppen'                  => 'Inactive groups',
    'Besitzer'                          => 'Owner',
    'Letzte Aktivität'                  => 'Last activity',
    'Tage inaktiv'                      => 'Days inactive',
    'Mitglieder'                        => 'Members',
    'Externe'                           => 'External',
    'Exchange E-Mails'                  => 'Exchange emails',
    'SharePoint Dateien'                => 'SharePoint files',

    // ── GuestUsersController ─────────────────────────────────────────────────
    'Gastbenutzer'                      => 'Guest users',
    'Gastbenutzer deaktiviert.'         => 'Guest user disabled.',
    'Gastbenutzer gelöscht.'            => 'Guest user deleted.',
    'Löschen fehlgeschlagen: '          => 'Deletion failed: ',
    'Einladungsstatus'                  => 'Invitation status',

    // ── OnboardingController ─────────────────────────────────────────────────
    'Lizenzen konnten nicht geladen werden: '
                                        => 'Licenses could not be loaded: ',
    'Gruppen konnten nicht geladen werden: '
                                        => 'Groups could not be loaded: ',
    'Benutzer-Onboarding'               => 'User onboarding',
    'Benutzer ":name" erfolgreich erstellt.'
                                        => 'User ":name" created successfully.',
    ' Hinweise: '                       => ' Notes: ',
    'Onboarding fehlgeschlagen: '       => 'Onboarding failed: ',
    'Die App-Registrierung in Azure braucht die Application-Permission "User.ReadWrite.All" (+ Admin Consent). Lese-Berechtigung allein reicht für POST /users nicht. Permissions prüfen unter /settings/permissions.'
                                        => 'The Azure app registration requires the application permission "User.ReadWrite.All" (+ admin consent). Read access alone is not sufficient for POST /users. Check the permissions under /settings/permissions.',
    'Im Tenant sind keine verfügbaren Lizenzen für die gewählte SKU vorhanden.'
                                        => 'There are no available licenses in the tenant for the selected SKU.',
    'Ein Benutzer mit dieser UPN existiert bereits.'
                                        => 'A user with this UPN already exists.',

    // ── OffboardingController ────────────────────────────────────────────────
    'Offboarding-Assistent'             => 'Offboarding assistant',
    'Konto deaktiviert.'                => 'Account disabled.',
    'Alle Sitzungen widerrufen.'        => 'All sessions revoked.',
    'Alle Lizenzen entfernt.'           => 'All licenses removed.',
    ':count Gruppe(n) entfernt.'        => ':count group(s) removed.',
    'Postfach-Konvertierung muss im Exchange Admin Center manuell durchgeführt werden. Direktlink im Profil des Benutzers.'
                                        => 'Mailbox conversion must be performed manually in the Exchange Admin Center. Direct link in the user\'s profile.',

    // ── StaleAccountsController ──────────────────────────────────────────────
    'Inaktive Konten'                   => 'Inactive accounts',
    'Benutzer nicht gefunden oder hat keine Lizenzen.'
                                        => 'User not found or has no licenses.',
    'Lizenzen für :name entfernt.'      => 'Licenses removed for :name.',
    'Fehler beim Entfernen der Lizenzen: '
                                        => 'Error while removing licenses: ',
    'Position'                          => 'Position',
    'Nie'                               => 'Never',

    // ── DeletedObjectsController ─────────────────────────────────────────────
    'Papierkorb'                        => 'Recycle bin',
    'Objekt erfolgreich wiederhergestellt.'
                                        => 'Object restored successfully.',
    'Wiederherstellen fehlgeschlagen: ' => 'Restore failed: ',
    'Objekt endgültig gelöscht.'        => 'Object permanently deleted.',

    // ── SetupWizardController ────────────────────────────────────────────────
    'Einrichtungs-Assistent'            => 'Setup wizard',
    'Einrichtungs-Assistent abgeschlossen — viel Erfolg!'
                                        => 'Setup wizard completed — good luck!',
    'Einrichtungs-Assistent zurückgesetzt.'
                                        => 'Setup wizard reset.',
    'Tenant-ID gesetzt'                 => 'Tenant ID set',
    'GUID des Microsoft-365-Mandanten ist hinterlegt.'
                                        => 'The GUID of the Microsoft 365 tenant is stored.',
    'Keine Tenant-ID in den Einstellungen — bitte in /settings ergänzen.'
                                        => 'No tenant ID in the settings — please add it under /settings.',
    'Client-ID gesetzt'                 => 'Client ID set',
    'App-Registrierung ist hinterlegt.' => 'App registration is stored.',
    'Keine Client-ID — bitte in /settings ergänzen.'
                                        => 'No client ID — please add it under /settings.',
    'Client-Secret gesetzt'             => 'Client secret set',
    'Verschlüsselt gespeichert.'        => 'Stored encrypted.',
    'Kein Client-Secret — bitte in /settings ergänzen.'
                                        => 'No client secret — please add it under /settings.',
    'Token-Abruf nicht möglich, weil Tenant-Daten fehlen.'
                                        => 'Token retrieval not possible because tenant data is missing.',
    'Test-Aufruf an /organization erfolgreich.'
                                        => 'Test call to /organization succeeded.',
    'Test-Aufruf fehlgeschlagen: '      => 'Test call failed: ',
    'Graph-API erreichbar'              => 'Graph API reachable',

    // ── Shared CSV headers (controller exports) ──────────────────────────────
    'Name'                              => 'Name',
    'UPN'                               => 'UPN',
    'Status'                            => 'Status',
    'MFA'                               => 'MFA',
    'Abteilung'                         => 'Department',
    'Titel'                             => 'Title',
    'Lizenzen'                          => 'Licenses',
    'Erstellt'                          => 'Created',
    'Letzter Login'                     => 'Last sign-in',
    'E-Mail'                            => 'Email',
];
