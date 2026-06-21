<?php

/**
 * English translations for the four-eyes (dual-control) approval feature.
 *
 * @return array<string,string>
 */
return [
    // Navigation
    'Aktionsfreigaben' => 'Approvals',

    // Service / notifications
    'Freigabe angefordert: :label' => 'Approval requested: :label',
    'Ein zweiter Administrator muss diese kritische Aktion freigeben.'
        => 'A second administrator must approve this critical action.',
    'Freigabe erteilt: :label' => 'Approval granted: :label',
    'Die Aktion kann jetzt von :who erneut ausgelöst und ausgeführt werden.'
        => 'The action can now be re-triggered and executed by :who.',

    // Controller flashes
    'Du kannst deine eigene Anfrage nicht freigeben — ein zweiter Administrator ist erforderlich.'
        => 'You cannot approve your own request — a second administrator is required.',
    'Anfrage nicht gefunden oder bereits entschieden.' => 'Request not found or already decided.',
    'Freigabe erteilt. Die Aktion kann nun ausgeführt werden.' => 'Approval granted. The action can now be executed.',
    'Anfrage abgelehnt.' => 'Request rejected.',

    // View
    'Vier-Augen-Prinzip ist deaktiviert.' => 'The four-eyes principle is disabled.',
    'Kritische Aktionen werden derzeit sofort ausgeführt. Aktiviere das Vier-Augen-Prinzip unter Einstellungen → Datenschutz, damit sie eine Freigabe durch einen zweiten Administrator erfordern.'
        => 'Critical actions currently run immediately. Enable the four-eyes principle under Settings → Privacy so they require approval by a second administrator.',
    'Zu den Einstellungen' => 'Go to settings',
    'Offene Freigaben' => 'Pending approvals',
    'Keine offenen Freigabe-Anfragen.' => 'No pending approval requests.',
    'Angefordert von :who am :when' => 'Requested by :who on :when',
    'Freigeben' => 'Approve',
    'Diese Anfrage ablehnen?' => 'Reject this request?',
    'Ablehnen' => 'Reject',
    'Verlauf' => 'History',
    'Noch keine Entscheidungen.' => 'No decisions yet.',
    'Angefordert von' => 'Requested by',
    'Entschieden von' => 'Decided by',
    'Freigegeben' => 'Approved',
    'Ausgeführt' => 'Executed',
    'Abgelehnt' => 'Rejected',
    'Ausstehend' => 'Pending',

    // Gate messages on critical actions
    'Zur Freigabe eingereicht — ein zweiter Administrator muss bestätigen.'
        => 'Submitted for approval — a second administrator must confirm.',
    'Gerät zurücksetzen (Retire): :id' => 'Reset device (retire): :id',
    'Gerät auf Werkseinstellungen zurücksetzen (Wipe): :id' => 'Factory-reset device (wipe): :id',
    'Konto deaktivieren: :name' => 'Disable account: :name',
    'MFA-Methoden zurücksetzen: :id' => 'Reset MFA methods: :id',
];
