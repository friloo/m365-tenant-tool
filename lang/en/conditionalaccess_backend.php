<?php

/**
 * English translations for the Conditional Access backend layer
 * (ConditionalAccessService / ConditionalAccessController) — gap-analysis
 * findings, policy-summary labels and flash messages that are generated in
 * PHP and rendered into the view.
 *
 * @return array<string,string>
 */
return [
    // Gap-analysis categories
    'Sicherheit' => 'Security',
    'Gerät'      => 'Device',
    'Risiko'     => 'Risk',
    'Standort'   => 'Location',
    'Allgemein'  => 'General',

    // Gap-analysis findings (title + detail)
    'MFA für alle Benutzer'                                          => 'MFA for all users',
    'Mindestens eine aktive Richtlinie erzwingt MFA für alle Benutzer.' => 'At least one active policy enforces MFA for all users.',
    'MFA für alle Benutzer (nur Report-Modus)'                       => 'MFA for all users (report-only)',
    'Eine MFA-Richtlinie für alle Benutzer existiert, ist aber nur im Report-Modus. Zum Aktivieren wechseln.'
        => 'An MFA policy for all users exists but is in report-only mode. Switch it on to enforce it.',
    'Keine MFA-Pflicht für alle Benutzer'                           => 'No MFA requirement for all users',
    'Keine aktive Richtlinie, die MFA für alle Benutzer erzwingt. Empfehlung: Richtlinie "Require MFA for all users" anlegen.'
        => 'No active policy enforces MFA for all users. Recommendation: create a “Require MFA for all users” policy.',
    'MFA für Administratoren'                                        => 'MFA for administrators',
    'Mindestens eine Richtlinie erzwingt MFA für privilegierte Rollen.' => 'At least one policy enforces MFA for privileged roles.',
    'Keine MFA-Pflicht für Administratoren'                         => 'No MFA requirement for administrators',
    'Keine Richtlinie erzwingt MFA speziell für Admin-Rollen. Besonders kritisch — Admins sollten immer MFA verwenden.'
        => 'No policy enforces MFA specifically for admin roles. Especially critical — admins should always use MFA.',
    'Legacy-Authentifizierung blockiert'                            => 'Legacy authentication blocked',
    'Eine aktive Richtlinie blockiert Legacy-Auth (Exchange ActiveSync / andere Clients).'
        => 'An active policy blocks legacy auth (Exchange ActiveSync / other clients).',
    'Legacy-Authentifizierung nicht blockiert'                      => 'Legacy authentication not blocked',
    'Keine Richtlinie blockiert alte Protokolle (Basic Auth, IMAP, POP, SMTP AUTH). Diese umgehen MFA und sind ein häufiger Angriffsvektor.'
        => 'No policy blocks legacy protocols (Basic Auth, IMAP, POP, SMTP AUTH). They bypass MFA and are a common attack vector.',
    'Gerätecompliance oder Hybrid Join gefordert'                   => 'Device compliance or Hybrid Join required',
    'Mindestens eine Richtlinie verlangt ein konformes oder Hybrid-verbundenes Gerät.'
        => 'At least one policy requires a compliant or hybrid-joined device.',
    'Keine Gerätecompliance-Anforderung'                            => 'No device compliance requirement',
    'Kein Conditional Access fordert ein Intune-konformes Gerät. Empfehlung für sensible Apps/Daten.'
        => 'No Conditional Access requires an Intune-compliant device. Recommended for sensitive apps/data.',
    'Risikobewertung bei Anmeldung aktiv'                           => 'Sign-in risk evaluation active',
    'Sign-in Risk Policy vorhanden (erfordert Entra ID P2 / Microsoft 365 E5).'
        => 'Sign-in risk policy present (requires Entra ID P2 / Microsoft 365 E5).',
    'Keine Sign-in Risk Policy'                                     => 'No sign-in risk policy',
    'Keine Richtlinie reagiert auf risikoreiche Anmeldungen. Mit Entra ID P2 ist Echtzeit-Risikoschutz möglich.'
        => 'No policy responds to risky sign-ins. With Entra ID P2, real-time risk protection is possible.',
    'Länder-Blockierung aktiv'                                      => 'Country blocking active',
    'Mindestens eine Richtlinie blockiert Anmeldungen basierend auf dem geografischen Standort.'
        => 'At least one policy blocks sign-ins based on geographic location.',
    'Keine Länder-Blockierung'                                      => 'No country blocking',
    'Keine Richtlinie beschränkt Anmeldungen auf bestimmte Länder. Empfohlen wenn Anmeldungen aus fremden Ländern unerwünscht sind.'
        => 'No policy restricts sign-ins to specific countries. Recommended if sign-ins from foreign countries are undesirable.',
    'Keine Conditional-Access-Richtlinien'                          => 'No Conditional Access policies',
    'Im Tenant sind keinerlei CA-Richtlinien konfiguriert. Zugriff auf Microsoft 365 ist ohne Einschränkungen möglich.'
        => 'No CA policies are configured in the tenant at all. Access to Microsoft 365 is possible without restrictions.',

    // Policy-summary labels
    'Alle Benutzer'            => 'All users',
    'Gäste / externe Benutzer' => 'Guests / external users',
    ':n Rollen'                => ':n roles',
    ':n Gruppen'               => ':n groups',
    ':n Benutzer'              => ':n users',
    'Alle Cloud-Apps'          => 'All cloud apps',
    ':n App(s)'                => ':n app(s)',
    'Blockieren'               => 'Block',
    'Konformes Gerät'          => 'Compliant device',
    'Genehmigte App'           => 'Approved app',
    'Auth-Stärke'              => 'Auth strength',
    'Zugriff erlauben'         => 'Allow access',
    'Erfordern:'               => 'Require:',
    'Sitzungshäufigkeit:'      => 'Sign-in frequency:',
    'Persist. Browser:'        => 'Persistent browser:',

    // Flash / exception messages
    'Ein Name für die Richtlinie ist erforderlich.' => 'A name for the policy is required.',
    'Bitte einen Länder-Standort auswählen.'        => 'Please select a country location.',
    'Richtlinie „:name" wurde angelegt (im Report-Modus — zum Aktivieren umschalten).'
        => 'Policy “:name” was created (in report-only mode — switch it on to enforce).',
    'Richtlinie konnte nicht erstellt werden:'      => 'Policy could not be created:',
    'Richtlinienstatus geändert:'                   => 'Policy status changed:',
    'Statusänderung fehlgeschlagen:'                => 'Status change failed:',
    'Richtlinie wurde gelöscht.'                    => 'Policy was deleted.',
    'Löschen fehlgeschlagen:'                        => 'Deletion failed:',
    'Ungültiger Status:'                            => 'Invalid status:',
    'Aktiviert'                                     => 'Enabled',
    'Report-only'                                   => 'Report-only',
];
