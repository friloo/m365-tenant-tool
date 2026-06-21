<?php

/**
 * English translations for the Information Protection module views
 * (Sensitivity Labels, DLP policies/incidents, Retention, eDiscovery)
 * and the external-facing Share Review pages.
 *
 * Keys are the exact German source strings used in the views.
 *
 * @return array<string,string>
 */
return [
    // ── Sensitivity labels (views/sensitivitylabels/index.php) ──────────────
    'Fehler beim Abruf'                 => 'Error retrieving data',
    'Benötigte Berechtigung:'           => 'Required permission:',
    'Richtlinieneinstellungen'          => 'Policy settings',
    ':n Einträge'                       => ':n entries',
    'Keine Vertraulichkeitsbezeichnungen gefunden.' => 'No sensitivity labels found.',
    'Entweder sind keine konfiguriert, oder die Berechtigung :perm fehlt.'
        => 'Either none are configured, or the :perm permission is missing.',
    'Microsoft Purview öffnen'          => 'Open Microsoft Purview',
    'Vertraulichkeitsbezeichnungen'     => 'Sensitivity labels',
    'Verschlüsselung'                   => 'Encryption',
    'Markierung'                        => 'Marking',
    'Kopfzeile'                         => 'Header',
    'Fußzeile'                          => 'Footer',
    'Wasserzeichen'                     => 'Watermark',
    'Labels anlegen &amp; veröffentlichen' => 'Create &amp; publish labels',
    'Sensitivity Labels lassen sich über Graph nur <strong>lesen</strong> (oben). Anlegen, '
    . 'Verschlüsselung/Markierung konfigurieren und per Label-Policy veröffentlichen erfolgt im '
    . '<strong>Microsoft-Purview-Portal</strong> oder per <strong>PowerShell</strong>. '
    . 'Erfordert Microsoft 365 E3/E5 bzw. Azure Information Protection.'
        => 'Sensitivity labels can only be <strong>read</strong> via Graph (above). Creating them, '
        . 'configuring encryption/marking and publishing via a label policy is done in the '
        . '<strong>Microsoft Purview portal</strong> or via <strong>PowerShell</strong>. '
        . 'Requires Microsoft 365 E3/E5 or Azure Information Protection.',
    'Labels im Purview-Portal'          => 'Labels in the Purview portal',
    'Mit Security & Compliance PowerShell verbinden' => 'Connect with Security & Compliance PowerShell',
    'Vorhandene Labels auflisten'       => 'List existing labels',
    'Label anlegen & veröffentlichen'   => 'Create & publish a label',

    // ── DLP policies (views/dlppolicies/index.php) ──────────────────────────
    'Data-Loss-Prevention-Richtlinien'  => 'Data Loss Prevention policies',
    'DLP-Richtlinien (Regeln gegen den Abfluss sensibler Daten in Exchange, SharePoint, OneDrive, '
    . 'Teams und Endpunkten) lassen sich <strong>nicht über die Microsoft Graph API verwalten</strong> — '
    . 'weder lesend noch schreibend. Verwaltung im <strong>Microsoft-Purview-Portal</strong> oder per '
    . '<strong>Security-&amp;-Compliance-PowerShell</strong>. Nutze die Deep-Links oder kopiere die Befehle:'
        => 'DLP policies (rules against the leakage of sensitive data in Exchange, SharePoint, OneDrive, '
        . 'Teams and endpoints) <strong>cannot be managed via the Microsoft Graph API</strong> — '
        . 'neither for reading nor writing. Management is done in the <strong>Microsoft Purview portal</strong> or via '
        . '<strong>Security &amp; Compliance PowerShell</strong>. Use the deep links or copy the commands:',
    'DLP-Richtlinien im Purview-Portal' => 'DLP policies in the Purview portal',
    'DLP-Vorfälle im Tool'              => 'DLP incidents in the tool',
    'Vorhandene DLP-Richtlinien auflisten' => 'List existing DLP policies',
    'Beispiel: DLP-Richtlinie für Kreditkartennummern anlegen' => 'Example: create a DLP policy for credit card numbers',
    'Was das Tool selbst bietet: <strong>DLP-Vorfälle</strong> (aus dem Audit-Log) und '
    . '<strong>Sensitivity Labels</strong> (Anzeige). Vollständige Gegenüberstellung Tool ↔ Portal:'
        => 'What the tool itself offers: <strong>DLP incidents</strong> (from the audit log) and '
        . '<strong>Sensitivity labels</strong> (display). Full comparison of tool ↔ portal:',

    // ── DLP incidents (views/dlpincidents/index.php) ────────────────────────
    'Vorfälle, bei denen DLP-Regeln oder Sensitivity-Labels griffen — der eigentliche Compliance-Audit (Art. 5 + 32 DSGVO).'
        => 'Incidents where DLP rules or sensitivity labels were triggered — the actual compliance audit (Art. 5 + 32 GDPR).',
    'Letzte :n Tage'                    => 'Last :n days',
    'Vorfälle gesamt'                   => 'Total incidents',
    'in den letzten :n Tagen'           => 'in the last :n days',
    'Beteiligte User'                   => 'Users involved',
    'unique'                            => 'unique',
    'Trend (Vorfälle/Tag)'              => 'Trend (incidents/day)',
    'Keine Daten'                       => 'No data',
    'Top User mit DLP-Treffern'         => 'Top users with DLP hits',
    'Top Aktivitäten'                   => 'Top activities',
    'Vorfälle im Detail'                => 'Incidents in detail',
    'Keine DLP-Vorfälle im gewählten Zeitraum.' => 'No DLP incidents in the selected period.',
    'Falls hier nichts steht, obwohl DLP-Policies aktiv sind: prüfen Sie in Microsoft Purview, ob die Policies im "enforce" und nicht im "test" Modus laufen.'
        => 'If nothing appears here even though DLP policies are active: check in Microsoft Purview whether the policies are running in "enforce" mode and not in "test" mode.',
    'Wann'                              => 'When',
    'Auslöser'                          => 'Trigger',
    'Aktivität'                         => 'Activity',
    'Ziel'                              => 'Target',
    'Resultat'                          => 'Result',

    // ── Retention (views/retention/index.php) ───────────────────────────────
    'Aufbewahrungs-Richtlinien &amp; -Labels' => 'Retention policies &amp; labels',
    'Retention-Policies und -Labels (Aufbewahrungs-/Löschfristen für E-Mails, Dokumente, Teams-Chats) '
    . 'lassen sich <strong>nicht über die Microsoft Graph API verwalten</strong>. Verwaltung im '
    . '<strong>Microsoft-Purview-Portal</strong> oder per <strong>Security-&amp;-Compliance-PowerShell</strong>:'
        => 'Retention policies and labels (retention/deletion periods for emails, documents, Teams chats) '
        . '<strong>cannot be managed via the Microsoft Graph API</strong>. Management is done in the '
        . '<strong>Microsoft Purview portal</strong> or via <strong>Security &amp; Compliance PowerShell</strong>:',
    'Aufbewahrung im Purview-Portal'    => 'Retention in the Purview portal',
    'eDiscovery-Fälle im Tool'          => 'eDiscovery cases in the tool',
    'Vorhandene Aufbewahrungs-Richtlinien auflisten' => 'List existing retention policies',
    'Beispiel: 7-Jahres-Aufbewahrung anlegen' => 'Example: create a 7-year retention',
    'Welche Compliance-Bereiche über Graph möglich sind, zeigt :doc.'
        => 'Which compliance areas are possible via Graph is shown in :doc.',

    // ── eDiscovery (views/ediscovery/index.php) ─────────────────────────────
    'werden im Microsoft Purview Compliance Portal konfiguriert.'
        => 'are configured in the Microsoft Purview Compliance Portal.',
    'Hier werden verwandte Compliance-Daten aus Graph angezeigt.'
        => 'Related compliance data from Graph is shown here.',
    'Microsoft Purview – Information Governance öffnen' => 'Open Microsoft Purview – Information Governance',
    'Offene eDiscovery-Cases'           => 'Open eDiscovery cases',
    'Geschlossene Cases'                => 'Closed cases',
    'eDiscovery-Cases'                  => 'eDiscovery cases',
    'Keine eDiscovery-Cases vorhanden. Im Microsoft Purview unter'
        => 'No eDiscovery cases available. In Microsoft Purview under',
    'können neue Aufbewahrungsverfahren angelegt werden.'
        => 'new holds can be created.',
    'Erstellt'                          => 'Created',
    'Geschlossen'                       => 'Closed',
    'Aktiv'                             => 'Active',
    'Löschung ausstehend'               => 'Deletion pending',
    'Direktlinks – Purview Compliance Portal' => 'Direct links – Purview Compliance Portal',
    'Aufbewahrungsrichtlinien'          => 'Retention policies',
    'Richtlinien für Datenaufbewahrung verwalten' => 'Manage data retention policies',
    'Aufbewahrungsbezeichnungen'        => 'Retention labels',
    'Labels für Inhalte definieren und verwalten' => 'Define and manage labels for content',
    'Fälle und Inhaltssuche verwalten'  => 'Manage cases and content search',
    'Kommunikations-Compliance'         => 'Communication compliance',
    'Kommunikationsrichtlinien überwachen' => 'Monitor communication policies',

    // ── Share Review: recipient-facing pages ────────────────────────────────
    // review.php
    'Freigabe bestätigen'               => 'Confirm share',
    'VORSCHAU — So sehen Benutzer diese Seite nach Erhalt der Review-E-Mail'
        => 'PREVIEW — This is how users see this page after receiving the review email',
    'Einstellungen'                     => 'Settings',
    'Freigabe-Überprüfung'              => 'Share review',
    'Sie haben eine Datei oder einen Ordner freigegeben, die regelmäßig überprüft werden muss. '
    . 'Bitte bestätigen Sie, ob diese Freigabe noch benötigt wird.'
        => 'You have shared a file or folder that must be reviewed regularly. '
        . 'Please confirm whether this share is still needed.',
    'Datei / Ordner'                    => 'File / folder',
    'Datei öffnen'                      => 'Open file',
    'Speicherort'                       => 'Storage location',
    'Freigabe-Typ'                      => 'Share type',
    'Öffentlich (Anyone-Link)'          => 'Public (Anyone link)',
    'Externe Benutzer'                  => 'External users',
    'Gesamte Organisation'              => 'Entire organization',
    'Freigabe seit'                     => 'Shared since',
    'Automatischer Widerruf am'         => 'Automatic revocation on',
    'Bitte bestätigen Sie rechtzeitig, um die Freigabe zu erhalten.'
        => 'Please confirm in time to keep the share.',
    'Begründung'                        => 'Justification',
    'z.B. Wird für die Zusammenarbeit mit Partner XY bis Ende Q2 benötigt.'
        => 'e.g. Needed for collaboration with partner XY until the end of Q2.',
    'Mindestens 5 Zeichen. Ihre Begründung wird protokolliert.'
        => 'At least 5 characters. Your justification will be logged.',
    'Demo — Formular kann nicht abgeschickt werden' => 'Demo — the form cannot be submitted',
    'Verlängerung um :n Tage'           => 'Extension by :n days',
    'Freigabe-Daten konnten nicht geladen werden.' => 'Share data could not be loaded.',
    'Dieser Link ist personalisiert und kann nur einmal verwendet werden. Sie benötigen kein Passwort.'
        => 'This link is personalized and can only be used once. You do not need a password.',
    'Bei Fragen:'                       => 'Questions?',

    // expired.php
    'Link ungültig'                     => 'Invalid link',
    'Link bereits verwendet'            => 'Link already used',
    'Dieser Bestätigungslink wurde bereits einmal verwendet. '
    . 'Falls Sie eine neuere E-Mail erhalten haben, nutzen Sie bitte den Link aus dieser E-Mail.'
        => 'This confirmation link has already been used once. '
        . 'If you have received a more recent email, please use the link from that email.',
    'Link nicht gefunden'               => 'Link not found',
    'Dieser Bestätigungslink ist ungültig oder existiert nicht. '
    . 'Bitte prüfen Sie, ob Sie den vollständigen Link aus der E-Mail kopiert haben.'
        => 'This confirmation link is invalid or does not exist. '
        . 'Please check whether you copied the complete link from the email.',
    'Link abgelaufen'                   => 'Link expired',
    'Dieser Bestätigungslink ist abgelaufen. '
    . 'Wenn die Freigabe weiterhin benötigt wird, wenden Sie sich bitte an Ihren IT-Administrator.'
        => 'This confirmation link has expired. '
        . 'If the share is still needed, please contact your IT administrator.',
    'Sie können dieses Fenster schließen.' => 'You can close this window.',

    // confirmed.php
    'Freigabe bestätigt'                => 'Share confirmed',
    'Vielen Dank! Ihre Bestätigung wurde gespeichert und die Freigabe wurde verlängert. '
    . 'Sie erhalten rechtzeitig eine erneute Erinnerung.'
        => 'Thank you! Your confirmation has been saved and the share has been extended. '
        . 'You will receive a new reminder in good time.',
    'Sie können dieses Fenster jetzt schließen.' => 'You can close this window now.',

    // ── Share Review: admin page (views/sharereview/admin.php) ──────────────
    'Freigaben'                         => 'Shares',
    'Monitor'                           => 'Monitor',
    'Richtlinien'                       => 'Policies',
    'Prüfung ausstehend'                => 'Review pending',
    'Überfällig'                        => 'Overdue',
    'Bestätigt'                         => 'Confirmed',
    'Widerrufen'                        => 'Revoked',
    'Alle SharePoint-Freigaben jetzt scannen? Dies kann einige Minuten dauern.'
        => 'Scan all SharePoint shares now? This may take a few minutes.',
    'Jetzt scannen'                     => 'Scan now',
    'Alternativ Cron:'                  => 'Alternatively cron:',
    'Überwachte Freigaben'              => 'Monitored shares',
    'Ausstehend'                        => 'Pending',
    'Datei/Ordner'                      => 'File/folder',
    'Besitzer'                          => 'Owner',
    'Erkannt am'                        => 'Detected on',
    'Nächste Prüfung'                   => 'Next review',
    'Keine Freigaben gefunden.'         => 'No shares found.',
    'Führen Sie zuerst einen Scan durch.' => 'Run a scan first.',
    'Prüfung läuft'                     => 'Review in progress',
    'Öffentlich'                        => 'Public',
    'Extern'                            => 'External',
    'Org'                               => 'Org',
    'Begründung:'                       => 'Justification:',
    'Widerruf:'                         => 'Revocation:',
    'Widerruf überfällig'               => 'Revocation overdue',
    'Erinnerung senden'                 => 'Send reminder',
    'Freigabe wirklich widerrufen?'     => 'Really revoke this share?',
    'Freigabe widerrufen'               => 'Revoke share',
    'Suche …'                           => 'Search …',
];
