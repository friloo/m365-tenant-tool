<?php

/**
 * Translations for labels that live in PHP `const` arrays (which cannot call
 * t()) and are therefore translated at the view display site, plus the Auth
 * module messages. Merged by the I18n directory loader.
 *
 * @return array<string,string>
 */
return [
    // SecurityPosture category headings (used as lookup keys in the view, so
    // the German value stays the key and is translated only on display).
    'Identität & MFA'          => 'Identity & MFA',
    'Geräte & Compliance'      => 'Devices & Compliance',
    'Konfiguration & Apps'     => 'Configuration & Apps',
    'DSGVO & Datenschutz'      => 'GDPR & Data Protection',
    'E-Mail & Endpoint-Schutz' => 'Email & Endpoint Protection',
    'Prüfungen'                => 'checks',

    // Workflow triggers / actions (WorkflowService::TRIGGERS / ::ACTIONS consts)
    'Zeitplan (alle X Minuten)'        => 'Schedule (every X minutes)',
    'Neuer Gast-Benutzer'              => 'New guest user',
    'Neuer Benutzer in Gruppe'         => 'New user in group',
    'Lizenz zuweisen'                  => 'Assign license',
    'Zu Gruppe hinzufügen'             => 'Add to group',
    'E-Mail senden'                    => 'Send email',
    'In-App-Benachrichtigung erzeugen' => 'Create in-app notification',

    // LicenseAdvisor criteria labels (CRITERIA_MAP const)
    'Office Desktop-Apps'        => 'Office desktop apps',
    'Intune / Geräteverwaltung'  => 'Intune / Device management',

    // Settings test email
    'Test-E-Mail' => 'Test email',
    'Diese E-Mail bestätigt, dass der E-Mail-Versand korrekt konfiguriert ist.'
        => 'This email confirms that email delivery is configured correctly.',

    // Auth module (login / 2FA)
    'Ungültige Zugangsdaten.'                 => 'Invalid credentials.',
    'Ungültiger Code. Bitte erneut versuchen.' => 'Invalid code. Please try again.',
    'Wiederherstellungscode verwendet. Noch :n Code(s) übrig — bitte neue Codes generieren.'
        => 'Recovery code used. :n code(s) remaining — please generate new codes.',
];
