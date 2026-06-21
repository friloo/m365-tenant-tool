<?php

/**
 * English translations for backend (PHP-layer) display strings of the
 * Apps, Workflows, Licenses and Reports modules.
 *
 * Keys are the exact German source strings passed to t() in the
 * controllers/services listed below. Only display labels, report section
 * headings/labels and Session::flash messages are translated here — code
 * fields, array keys, Graph/API enums and status codes stay untouched.
 *
 * Covered files:
 *   Workflows/WorkflowsController.php, Workflows/WorkflowService.php
 *   WeeklyReport/WeeklyReportService.php, ExecutiveReport/ExecutiveReportService.php
 *   Licenses/LicensesController.php
 *   AppRegistrations/AppRegistrationsController.php
 *   DlpIncidents/DlpIncidentsController.php, Ediscovery/EdiscoveryController.php
 *   Overview/OverviewController.php, Search/SearchService.php
 *
 * @return array<string,string>
 */
return [
    // ── Workflows (controller: page titles + flash) ─────────────────────────
    'Workflow-Automatisierung'             => 'Workflow automation',
    'Workflow: :name'                      => 'Workflow: :name',
    'Neuer Workflow'                       => 'New Workflow',
    'Workflow gespeichert.'                => 'Workflow saved.',
    'Workflow gelöscht.'                   => 'Workflow deleted.',
    'Workflow nicht gefunden.'             => 'Workflow not found.',
    'Workflow ausgeführt (siehe Run-Log).' => 'Workflow executed (see run log).',
    'Ausführung fehlgeschlagen: :error'    => 'Execution failed: :error',

    // ── Workflows (service: run status + action detail) ─────────────────────
    'Keine Treffer'                        => 'No matches',
    'Treffer: :count'                      => 'Matches: :count',
    'übersprungen (keine SKU/User)'        => 'skipped (no SKU/user)',
    'Lizenz :sku an :user zugewiesen'      => 'License :sku assigned to :user',
    'übersprungen (keine Gruppe/User)'     => 'skipped (no group/user)',
    ':user zu Gruppe :group'               => ':user added to group :group',
    'Workflow-Benachrichtigung'            => 'Workflow Notification',
    'keine Empfänger-Adresse'              => 'no recipient address',
    'Mail an :to'                          => 'Mail sent to :to',
    'Mailer fehlt'                         => 'Mailer missing',
    'In-App-Benachrichtigung erzeugt'      => 'In-app notification created',
    'Unbekannte Aktion :type'              => 'Unknown action :type',

    // ── Licenses ────────────────────────────────────────────────────────────
    'Lizenzen nicht ladbar: :error'        => 'Licenses could not be loaded: :error',
    'Lizenzen'                             => 'Licenses',
    'Produkt'                              => 'Product',
    'Genutzt'                              => 'Used',
    'Gesamt'                               => 'Total',
    'Verfügbar'                            => 'Available',
    'Nutzung %'                            => 'Usage %',
    'Abos: :error'                         => 'Subscriptions: :error',
    'Lizenz-Ablauf'                        => 'License Expiry',

    // ── App Registrations ───────────────────────────────────────────────────
    'App-Registrierungen'                  => 'App registrations',
    'App-Details'                          => 'App Details',
    'Neues Secret'                         => 'New Secret',
    'Secret erstellt. Kopiere den Wert jetzt — er wird nicht erneut angezeigt.'
                                           => 'Secret created. Copy the value now — it will not be shown again.',
    'Fehler: :error'                       => 'Error: :error',
    'Secret gelöscht.'                     => 'Secret deleted.',

    // ── DLP / eDiscovery / Overview ─────────────────────────────────────────
    'DLP-Vorfälle'                         => 'DLP incidents',
    'eDiscovery-Fälle'                     => 'eDiscovery cases',
    'Modul-Übersicht'                      => 'Module overview',

    // ── Global search (subtitles) ───────────────────────────────────────────
    'Gruppe'                               => 'Group',
    'Gerät'                                => 'Device',

    // ── Weekly Report ───────────────────────────────────────────────────────
    'Kein Empfänger konfiguriert'          => 'No recipient configured',
    'Wöchentlicher Status-Report'          => 'Weekly Status Report',
    'Report gesendet an :recipient'        => 'Report sent to :recipient',
    'Erstellt am'                          => 'Created on',
    'App:'                                 => 'App:',
    'Bereich'                              => 'Area',
    'Kennzahl'                             => 'Metric',
    'Wert'                                 => 'Value',
    'Benutzer'                             => 'Users',
    'Aktive Nutzer gesamt'                 => 'Total active users',
    'MFA registriert'                      => 'MFA registered',
    'Inaktiv > 90 Tage'                    => 'Inactive > 90 days',
    'Gesamt erworben'                      => 'Total purchased',
    'Verbraucht'                           => 'Consumed',
    'Freie Slots'                          => 'Free slots',
    'Freigaben'                            => 'Shares',
    'Aktiv'                                => 'Active',
    'Ausstehende Reviews'                  => 'Pending reviews',
    'Widerrufen'                           => 'Revoked',
    'Aktueller Score'                      => 'Current score',
    'Maximaler Score'                      => 'Maximum score',
    'Risikobenutzer'                       => 'Risky users',
    'Nutzer mit Risikostatus „atRisk"'     => 'Users with risk state “atRisk”',
    'Dienststatus'                         => 'Service Health',
    'Aktive Incidents'                     => 'Active incidents',
    'Dashboard öffnen'                     => 'Open dashboard',

    // ── Executive Report ────────────────────────────────────────────────────
    'KPI-Sammlung fehlgeschlagen: :error'  => 'KPI collection failed: :error',
    'Executive-Report versendet an :count Empfänger'
                                           => 'Executive report sent to :count recipients',
    'Versand fehlgeschlagen — SMTP/Mail-Config prüfen'
                                           => 'Delivery failed — check SMTP/mail config',
    'Executive Report'                     => 'Executive Report',
    'Security-Score:'                      => 'Security Score:',
    'Stand:'                               => 'As of:',
    'Der Tenant befindet sich in einem soliden Sicherheitszustand.'
                                           => 'The tenant is in a solid security posture.',
    'Der Tenant hat Verbesserungspotenzial in mehreren Bereichen.'
                                           => 'The tenant has room for improvement in several areas.',
    'Der Tenant weist kritische Sicherheitslücken auf — sofortiger Handlungsbedarf.'
                                           => 'The tenant has critical security gaps — immediate action required.',
    'Tenant-Kennzahlen'                    => 'Tenant Metrics',
    'Geräte'                               => 'Devices',
    'MFA-Quote'                            => 'MFA Rate',
    'CA-Policies'                          => 'CA Policies',
    'aktiv'                                => 'active',
    'nicht konform'                        => 'non-compliant',
    'Defender Alerts'                      => 'Defender Alerts',
    'offen/in Bearbeitung'                 => 'open/in progress',
    'Gastbenutzer'                         => 'Guest users',
    'Lizenz-SKUs'                          => 'License SKUs',
    'Top-Findings — fehlgeschlagene Posture-Checks'
                                           => 'Top Findings — failed posture checks',
    'Diese Mail wurde automatisch erzeugt. Den vollständigen Bericht inkl. Empfehlungen finden Sie '
                                           => 'This email was generated automatically. You can find the full report incl. recommendations ',
    'im KI-Sicherheitsberater'             => 'in the AI Security Advisor',
    'im Tool unter /ai.'                   => 'in the tool under /ai.',
];
