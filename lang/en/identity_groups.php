<?php

/**
 * English translations for the Identity & Accounts group of views:
 * Groups (index/detail/inactive), MFA methods, Password expiry,
 * Stale accounts and Deleted objects.
 *
 * Keys are the exact German source strings used in the views. Shared/common
 * terms resolve from the central lang/en.php glossary, so they are not
 * duplicated here.
 *
 * @return array<string,string>
 */
return [
    // ── Groups: index ───────────────────────────────────────────────────────
    'Gruppen gesamt'              => 'Total groups',
    'M365-Gruppen'                => 'M365 groups',
    'Sicherheitsgruppen'          => 'Security groups',
    'Gruppe suchen…'              => 'Search group…',
    'Gruppe anlegen'              => 'Create group',
    'Erstellt'                    => 'Created',
    'Detail'                      => 'Detail',
    'Anzeigename'                 => 'Display name',
    'z.B. Marketing Team'         => 'e.g. Marketing Team',
    'Optionale Beschreibung der Gruppe' => 'Optional description of the group',
    'Gruppentyp'                  => 'Group type',
    'M365-Gruppe'                 => 'Microsoft 365 group',
    'E-Mail-aktivierte Sicherheitsgruppe' => 'Mail-enabled security group',
    'Mail-Alias'                  => 'Mail alias',
    'Wird automatisch generiert'  => 'Generated automatically',
    'Nur Kleinbuchstaben, Zahlen und Bindestriche. Leer lassen für automatische Generierung.'
        => 'Lowercase letters, numbers and hyphens only. Leave blank for automatic generation.',
    'M365-Gruppen können ein Team in Microsoft Teams erhalten. Sicherheitsgruppen werden für Berechtigungen genutzt.'
        => 'Microsoft 365 groups can receive a team in Microsoft Teams. Security groups are used for permissions.',
    'Gruppe erstellen'            => 'Create group',

    // ── Groups: detail ──────────────────────────────────────────────────────
    'Zurück zu Gruppen'           => 'Back to groups',
    'AD-synchronisierte Gruppen können hier nicht gelöscht werden'
        => 'AD-synchronized groups cannot be deleted here',
    'Gruppe wirklich löschen? Diese Aktion kann nicht rückgängig gemacht werden.'
        => 'Really delete group? This action cannot be undone.',
    'Gruppe löschen'              => 'Delete group',
    'Mitglied hinzufügen'         => 'Add member',
    'Benutzer-ID oder UPN…'       => 'User ID or UPN…',
    'Entra-Objekt-ID oder UPN eingeben' => 'Enter Entra object ID or UPN',
    'Besitzer'                    => 'Owner',
    'Diese Gruppe wird aus dem lokalen Active Directory synchronisiert. Besitzer werden möglicherweise dort verwaltet.'
        => 'This group is synchronized from the on-premises Active Directory. Owners may be managed there.',
    'Kein Besitzer'               => 'No owner',
    'Besitzer entfernen?'         => 'Remove owner?',
    'Besitzer entfernen'          => 'Remove owner',
    'Besitzer hinzufügen'         => 'Add owner',
    'Mitglieder'                  => 'Members',
    'Mitglied suchen…'            => 'Search member…',
    'UPN'                         => 'UPN',
    'Mitglied entfernen?'         => 'Remove member?',
    'Keine Mitglieder'            => 'No members',

    // ── Groups: inactive ────────────────────────────────────────────────────
    'Microsoft 365 Gruppen ohne Aktivität in den letzten :n Tagen'
        => 'Microsoft 365 groups with no activity in the last :n days',
    'Inaktiv seit mehr als:'      => 'Inactive for more than:',
    ':n Tage'                     => ':n days',
    'CSV Export'                  => 'CSV export',
    'Viele inaktive Gruppen'      => 'Many inactive groups',
    'Viele inaktive Gruppen können auf ungenutzte Ressourcen und potenzielle Sicherheitsrisiken hinweisen. Erwägen Sie eine Bereinigung.'
        => 'Many inactive groups can indicate unused resources and potential security risks. Consider cleaning them up.',
    'Inaktive Gruppen gesamt'     => 'Total inactive groups',
    'Seit >:n Tagen'              => 'For >:n days',
    'Nie aktiv'                   => 'Never active',
    'Kein Aktivitätsdatum'        => 'No activity date',
    'Durchschn. Inaktivitätsdauer' => 'Avg. inactivity period',
    'Tage (ohne „Nie aktiv")'     => 'days (excluding “Never active”)',
    'Externe Mitglieder'          => 'External members',
    'In inaktiven Gruppen'        => 'In inactive groups',
    'Letzte Aktivität'            => 'Last activity',
    'Inaktiv seit'                => 'Inactive since',
    '+:n extern'                  => '+:n external',
    'Keine inaktiven Gruppen gefunden — alle Gruppen waren in den letzten :n Tagen aktiv.'
        => 'No inactive groups found — all groups were active in the last :n days.',

    // ── MFA methods ─────────────────────────────────────────────────────────
    'Microsoft Graph antwortet mit HTTP :status — Daten können nicht geladen werden.'
        => 'Microsoft Graph responded with HTTP :status — data cannot be loaded.',
    'Mögliche Ursachen:'          => 'Possible causes:',
    'Berechtigung fehlt:'         => 'Permission missing:',
    'Der Endpunkt benötigt'       => 'The endpoint requires',
    'als <em>Anwendungs</em>-Berechtigung mit Admin-Consent.'
        => 'as an <em>application</em> permission with admin consent.',
    'Token ist veraltet:'         => 'Token is stale:',
    'Nach dem Hinzufügen neuer Berechtigungen muss der gecachte Access-Token erneuert werden. Klicke auf'
        => 'After adding new permissions, the cached access token must be renewed. Click',
    '— das leert auch den Token-Cache.'
        => '— this also clears the token cache.',
    'Azure AD Premium-Lizenz fehlt:' => 'Azure AD Premium license missing:',
    'Der Bericht'                 => 'The report',
    'setzt mindestens eine'       => 'requires at least one',
    '-Lizenz im Tenant voraus. Ohne P1/P2 liefert die API HTTP 403 selbst mit vollständigen Berechtigungen.'
        => ' license in the tenant. Without P1/P2 the API returns HTTP 403 even with full permissions.',
    'Vollständige Endpunkt-URL:'  => 'Full endpoint URL:',
    'Microsoft Graph hat den Aufruf akzeptiert, aber eine leere Antwort geliefert. Mögliche Ursachen:'
        => 'Microsoft Graph accepted the call but returned an empty response. Possible causes:',
    'Cache noch veraltet:'        => 'Cache still stale:',
    'Klicke auf'                  => 'Click',
    'um Token und Cache neu zu laden.' => 'to reload the token and cache.',
    'Berichtsdaten noch nicht verfügbar:' => 'Report data not yet available:',
    'kann im Tenant einige Stunden brauchen, bevor er nach der ersten Aktivierung Daten liefert.'
        => 'may take a few hours in the tenant before it returns data after first activation.',
    'Entra ID P1/P2-Lizenz:'      => 'Entra ID P1/P2 license:',
    'Der Bericht setzt mindestens eine' => 'The report requires at least one',
    '-Lizenz im Tenant voraus.'   => ' license in the tenant.',
    'Berichtsverschleierung aktiv:' => 'Report concealment active:',
    'Wenn im Microsoft 365 Admin Center unter <em>Einstellungen → Dienste → Berichte</em> die Option „Anonymisierte Benutzerberichte" aktiviert ist, sind Berichte für Apps gesperrt. Diese Einstellung muss deaktiviert sein.'
        => 'If the “Anonymized user reports” option is enabled in the Microsoft 365 admin center under <em>Settings → Services → Reports</em>, reports are blocked for apps. This setting must be disabled.',
    'Benutzer analysiert'         => 'Users analyzed',
    'MFA registriert'             => 'MFA registered',
    ':pct% der Benutzer'          => ':pct% of users',
    'Kein MFA'                    => 'No MFA',
    ':pct% ohne MFA'              => ':pct% without MFA',
    'MFA-fähig'                   => 'MFA capable',
    'Methoden-Verteilung'         => 'Method distribution',
    'Keine Methoden-Daten verfügbar' => 'No method data available',
    'Standard-Methode'            => 'Default method',
    'Keine Standard-Methoden-Daten verfügbar' => 'No default method data available',
    'Keine Angabe'                => 'Not specified',
    'MFA-Status'                  => 'MFA status',
    'Nach Methode'                => 'By method',
    'Registrierte Methoden'       => 'Registered methods',
    'Registriert'                 => 'Registered',
    'Keine Benutzer-Daten verfügbar' => 'No user data available',

    // ── Password expiry ─────────────────────────────────────────────────────
    'Gesamt geprüft'              => 'Total checked',
    'Aktive Benutzer'             => 'Active users',
    'Abgelaufen'                  => 'Expired',
    'Passwort überfällig'         => 'Password overdue',
    'Kritisch <14 Tage'           => 'Critical <14 days',
    'Läuft bald ab'               => 'Expiring soon',
    'Warnung <30 Tage'            => 'Warning <30 days',
    'Bald ablaufend'              => 'Expiring soon',
    'Läuft nie ab'                => 'Never expires',
    ':n Passwörter sind abgelaufen!' => ':n passwords have expired!',
    ':n Passwort ist abgelaufen!' => ':n password has expired!',
    'Betroffene Benutzer sollten ihr Passwort sofort ändern.'
        => 'Affected users should change their password immediately.',
    'Hinweis:'                    => 'Note:',
    'Passwörter mit <em>Läuft nie ab</em> sind in dieser Ansicht nicht aufgeführt (:n Benutzer betroffen). Das Ablauf-Intervall kann in den <a href="/settings">Einstellungen</a> konfiguriert werden (aktuell: :days Tage).'
        => 'Passwords set to <em>never expire</em> are not listed in this view (:n users affected). The expiry interval can be configured in <a href="/settings">Settings</a> (currently: :days days).',
    '<strong>Hybrid-Benutzer</strong> (AD Connect synchronisiert) haben ihre Passwortrichtlinie im on-prem Active Directory. Der Ablauf wird hier auf Basis des konfigurierten Werts (:days Tage) geschätzt — die tatsächliche AD-Richtlinie kann abweichen.'
        => '<strong>Hybrid users</strong> (synchronized via AD Connect) have their password policy in the on-premises Active Directory. The expiry is estimated here based on the configured value (:days days) — the actual AD policy may differ.',
    'Geändert am'                 => 'Changed on',
    'Läuft ab am'                 => 'Expires on',
    'Verbleibend'                 => 'Remaining',
    'Nie'                         => 'Never',
    ':nd überfällig'              => ':nd overdue',
    ':nd verbleibend'             => ':nd remaining',
    'Passwort wird durch on-prem AD verwaltet. Ablauf basiert auf konfiguriertem Max-Alter.'
        => 'Password is managed by the on-premises AD. Expiry is based on the configured maximum age.',
    'Hybrid'                      => 'Hybrid',
    'Cloud'                       => 'Cloud',
    'Keine Benutzer in dieser Kategorie' => 'No users in this category',

    // ── Stale accounts ──────────────────────────────────────────────────────
    ':n Benutzer mit Lizenzen sind seit >:days Tagen inaktiv'
        => ':n users with licenses have been inactive for >:days days',
    'geschätzte :n Lizenz-Einheiten könnten freigegeben werden.'
        => 'an estimated :n license units could be reclaimed.',
    'Mit Lizenzen'                => 'With licenses',
    'Verschwendetes Budget'       => 'Wasted budget',
    'Nie angemeldet'              => 'Never signed in',
    'Kein Login-Verlauf'          => 'No sign-in history',
    'Kostenrisiko'                => 'Cost risk',
    'Lizenzeinheiten freigab.'    => 'License units reclaimable',
    'Protokoll'                   => 'Log',
    'Inaktiv (Tage)'              => 'Inactive (days)',
    'Letzter Login'               => 'Last sign-in',
    ':n Lizenzen'                 => ':n licenses',
    ':n Lizenz'                   => ':n license',
    'Alle Lizenzen für diesen Benutzer entfernen?'
        => 'Remove all licenses for this user?',
    'Lizenzen entfernen'          => 'Remove licenses',
    'Keine inaktiven Konten für den gewählten Zeitraum gefunden'
        => 'No inactive accounts found for the selected period',
    'Auto-Freigabe von Lizenzen für inaktive Konten kann in den <a href="/settings">Einstellungen</a> konfiguriert werden (Schlüssel: <code>stale_account_days</code>).'
        => 'Automatic license reclamation for inactive accounts can be configured in <a href="/settings">Settings</a> (key: <code>stale_account_days</code>).',
    'Noch keine Aktionen protokolliert' => 'No actions logged yet',
    'Benutzer (UPN)'              => 'User (UPN)',
    'Lizenz entfernt'             => 'License removed',
    'Konto deaktiviert'           => 'Account disabled',
    'Übersprungen'                => 'Skipped',

    // ── Deleted objects ─────────────────────────────────────────────────────
    'Gelöschte Objekte werden nach <strong>30 Tagen</strong> automatisch und unwiderruflich gelöscht. Objekte mit weniger als 7 verbleibenden Tagen sind rot markiert.'
        => 'Deleted objects are permanently and automatically removed after <strong>30 days</strong>. Objects with fewer than 7 days remaining are highlighted in red.',
    'Gelöschte Benutzer'          => 'Deleted users',
    'im Papierkorb'               => 'in the recycle bin',
    'Gelöschte Gruppen'           => 'Deleted groups',
    'Gelöscht am'                 => 'Deleted on',
    'Wiederherstellen'            => 'Restore',
    'Endgültig löschen'           => 'Permanently delete',
    'Benutzer endgültig löschen? Diese Aktion kann nicht rückgängig gemacht werden.'
        => 'Permanently delete user? This action cannot be undone.',
    'Keine gelöschten Benutzer gefunden' => 'No deleted users found',
    'Sicherheitsgruppe'           => 'Security group',
    'Gruppe endgültig löschen? Diese Aktion kann nicht rückgängig gemacht werden.'
        => 'Permanently delete group? This action cannot be undone.',
    'Keine gelöschten Gruppen gefunden' => 'No deleted groups found',
];
