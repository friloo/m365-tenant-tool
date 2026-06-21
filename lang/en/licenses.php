<?php

/**
 * English translations for the Licenses & Reports views.
 *
 * Keys are the exact German source strings wrapped in t()/te() across the
 * licenses, licenseadvisor, licensecosts, usagereports, adoption,
 * executivereport and servicehealth views. The central map (lang/en.php)
 * wins on collisions, so shared glossary terms stay consistent.
 *
 * @return array<string,string>
 */
return [
    // ── licenses/index.php ──────────────────────────────────────────────────
    'Produkte'                       => 'Products',
    'Lizenzen gesamt'                => 'Total licenses',
    'In Verwendung'                  => 'In use',
    'belegt'                         => 'used',
    'Lizenz suchen…'                 => 'Search license…',
    'Produkt'                        => 'Product',
    'Genutzt'                        => 'Used',
    'Verfügbar'                      => 'Available',
    'Nutzung'                        => 'Usage',
    'Keine Lizenzen gefunden'        => 'No licenses found',

    // ── licenses/expiry.php ─────────────────────────────────────────────────
    'Ablaufende und kritische Microsoft 365 Lizenzen'
        => 'Expiring and critical Microsoft 365 licenses',
    'Achtung:'                       => 'Attention:',
    'Lizenz(en) laufen in weniger als 30 Tagen ab!'
        => 'license(s) expire in less than 30 days!',
    'Ablaufdaten sind nur über die Microsoft 365 Admin Center-API verfügbar. Die Graph API v1.0 liefert diese Information nicht für alle Tenant-Typen.'
        => 'Expiry dates are only available via the Microsoft 365 Admin Center API. Graph API v1.0 does not return this information for all tenant types.',
    'Gesamt Abonnements'            => 'Total subscriptions',
    'Alle Lizenzen'                 => 'All licenses',
    'Ablaufend (≤60 Tage)'          => 'Expiring (≤60 days)',
    ':n bald fällig'                => ':n due soon',
    'Keine bald fällig'             => 'None due soon',
    'Kritisch (≤30 Tage)'           => 'Critical (≤30 days)',
    'Sofortige Aufmerksamkeit'      => 'Immediate attention',
    'Gesperrt/Warnung'              => 'Blocked/Warning',
    'Status nicht „Enabled"'        => 'Status not "Enabled"',
    'Lizenz-Abonnements'            => 'License subscriptions',
    'Lizenzübersicht'               => 'License overview',
    'Ablaufdatum'                   => 'Expiry date',
    'Tage verbleibend'              => 'Days remaining',
    'Nutzungsgrad'                  => 'Utilization',
    'Abgelaufen'                    => 'Expired',
    ':n Tage'                       => ':n days',
    'Keine Abonnement-Informationen verfügbar.'
        => 'No subscription information available.',

    // ── licenseadvisor/index.php ────────────────────────────────────────────
    'Postfach, Kalender und E-Mail-Funktionen über Exchange Online.'
        => 'Mailbox, calendar and email features via Exchange Online.',
    'Installierbare Office-Apps (Word, Excel, PowerPoint, …).'
        => 'Installable Office apps (Word, Excel, PowerPoint, …).',
    'Microsoft Teams für Chat, Meetings und Zusammenarbeit.'
        => 'Microsoft Teams for chat, meetings and collaboration.',
    'SharePoint Online – Intranets, Dokumentenbibliotheken.'
        => 'SharePoint Online – intranets, document libraries.',
    'OneDrive for Business – persönlicher Cloud-Speicher.'
        => 'OneDrive for Business – personal cloud storage.',
    'Intune / Mobile Device Management für Geräteverwaltung.'
        => 'Intune / Mobile Device Management for device management.',
    'Listenpreis (Netto)'           => 'List price (net)',
    'NPO-Preis (Netto)'             => 'NPO price (net)',
    'kostenlos*'                    => 'free*',
    'Wähle mindestens ein Kriterium aus um die Analyse zu starten.'
        => 'Select at least one criterion to start the analysis.',
    'Kriterien konfigurieren'       => 'Configure criteria',
    'Aktiviere die Features, die <strong>alle</strong> Benutzer nach der Exchange-Online-Migration benötigen. Der Advisor zeigt dann, welche Lizenzpläne diese Kombination abdecken und welche Benutzer noch nicht abgedeckt sind.'
        => 'Enable the features that <strong>all</strong> users need after the Exchange Online migration. The advisor then shows which license plans cover this combination and which users are not yet covered.',
    'Kriterien speichern & analysieren' => 'Save criteria & analyze',
    'Gesamt (aktive Nutzer)'        => 'Total (active users)',
    'Abgedeckt'                     => 'Covered',
    'der aktiven Nutzer'            => 'of active users',
    'Nicht abgedeckt'               => 'Not covered',
    'Fehlende Kriterien'            => 'Missing criteria',
    'Ohne Lizenz'                   => 'Without license',
    'Keine Lizenz zugewiesen'       => 'No license assigned',
    'Preise:'                       => 'Prices:',
    'NPO (Non-Profit)'              => 'NPO (Non-Profit)',
    'Standard (Listenpreis)'        => 'Standard (list price)',
    'Auch nicht-gekaufte Lizenzen als Vorschlag anzeigen'
        => 'Also show non-purchased licenses as suggestions',
    'Alle Preise <strong>netto</strong> (ohne 19 % MwSt.), pro Nutzer/Monat, Jahresabo, DE-Listenpreis. Stand Mai 2025 — bitte beim Microsoft-Partner verifizieren.'
        => 'All prices <strong>net</strong> (excl. 19% VAT), per user/month, annual subscription, German list price. As of May 2025 — please verify with your Microsoft partner.',
    'Passende Lizenzen im Tenant'   => 'Matching licenses in the tenant',
    'Pläne, die <strong>alle</strong> gewählten Kriterien erfüllen'
        => 'Plans that meet <strong>all</strong> selected criteria',
    'Kein gekaufter Plan erfüllt alle gewählten Kriterien.'
        => 'No purchased plan meets all selected criteria.',
    'Plan-Name'                     => 'Plan name',
    'Verbraucht'                    => 'Consumed',
    'Empfohlen'                     => 'Recommended',
    'Alternative Lizenzen (nicht im Tenant)'
        => 'Alternative licenses (not in the tenant)',
    'Pläne, die ebenfalls alle Kriterien erfüllen würden'
        => 'Plans that would also meet all criteria',
    'Tier'                          => 'Tier',
    'Kosten/Monat'                  => 'Cost/month',
    'bei :n Nutzer'                 => 'for :n users',
    '* "kostenlos" gilt typischerweise für die ersten 10 Nutzer im NPO-Programm. Bei Microsoft 365 Business Basic / Office 365 E1. Netto-Listenpreise (ohne MwSt.), Stand Mai 2025. Partnerrabatte und CSP-Preise können abweichen. Bitte beim Microsoft-Partner verifizieren.'
        => '* "free" typically applies to the first 10 users in the NPO program. For Microsoft 365 Business Basic / Office 365 E1. Net list prices (excl. VAT), as of May 2025. Partner discounts and CSP prices may differ. Please verify with your Microsoft partner.',
    'Keine weiteren Lizenzen im Katalog erfüllen alle gewählten Kriterien.'
        => 'No further licenses in the catalog meet all selected criteria.',
    'Benutzer ohne Abdeckung'       => 'Users without coverage',
    'CSV Export'                    => 'CSV export',
    'Alle aktiven Benutzer erfüllen die gewählten Kriterien.'
        => 'All active users meet the selected criteria.',
    'Benutzer suchen…'              => 'Search user…',
    'Letzter Login'                 => 'Last login',
    'Keine Lizenz'                  => 'No license',
    'Einsparpotenzial'              => 'Savings potential',
    'Nutzer mit passender Lizenz, aber inaktiv >90 Tage'
        => 'Users with a matching license but inactive >90 days',
    'Kein Einsparpotenzial gefunden – alle lizenzierten Nutzer sind aktiv.'
        => 'No savings potential found – all licensed users are active.',
    '<strong>:n Benutzer</strong> haben eine passende Lizenz, aber haben sich seit mehr als 90 Tagen nicht angemeldet. Diese :n Lizenzeinheiten könnten freigegeben werden.'
        => '<strong>:n users</strong> have a matching license but have not signed in for more than 90 days. These :n license units could be released.',
    'Inaktiv (Tage)'                => 'Inactive (days)',
    'Nie'                           => 'Never',

    // ── licensecosts/index.php ──────────────────────────────────────────────
    'Preismode:'                    => 'Price mode:',
    'NPO-Preise (Netto)'            => 'NPO prices (net)',
    'Listenpreise (Netto)'          => 'List prices (net)',
    'Ges. Kosten / Monat'           => 'Total cost / month',
    'Ges. Kosten / Jahr'            => 'Total cost / year',
    'Ungenutzte Lizenzen / Monat'   => 'Unused licenses / month',
    'Jahr'                          => 'year',
    'Lizenzkosten nach SKU'         => 'License costs by SKU',
    'Belegt'                        => 'Used',
    'Auslastung'                    => 'Utilization',
    'Kosten / Monat'                => 'Cost / month',
    'Ungenutzt / Mon.'              => 'Unused / mo.',
    'Kosten / Jahr'                 => 'Cost / year',
    '<strong>:m/Monat</strong> (:y/Jahr) werden für ungenutzte Lizenzen ausgegeben. Im <a href="/licenseadvisor">Lizenz-Berater</a> siehst du, welche Benutzer keine Lizenz benötigen oder inaktiv sind.'
        => '<strong>:m/month</strong> (:y/year) is spent on unused licenses. In the <a href="/licenseadvisor">License advisor</a> you can see which users do not need a license or are inactive.',
    'Alle Preise <strong>netto</strong> (ohne 19 % MwSt.), DE-Listenpreis, Stand Mai 2025. Nur SKUs mit hinterlegtem Preis werden berechnet. Tatsächliche CSP/EA-Preise können abweichen.'
        => 'All prices <strong>net</strong> (excl. 19% VAT), German list price, as of May 2025. Only SKUs with a stored price are calculated. Actual CSP/EA prices may differ.',

    // ── usagereports/index.php ──────────────────────────────────────────────
    'Zeitraum:'                     => 'Period:',
    'Aktive Nutzer (Letzte :n Tage)' => 'Active users (last :n days)',
    'Exchange / E-Mail'             => 'Exchange / Email',
    'aktive Nutzer'                 => 'active users',
    'Zur Lösung'                    => 'Go to solution',
    'Keine Daten verfügbar. Microsoft braucht ca. 48 Stunden nach Tenant-Erstellung, bis Aktivitätsberichte aggregiert werden.'
        => 'No data available. Microsoft needs around 48 hours after tenant creation before activity reports are aggregated.',
    'Aktivität (Letzte :n Tage, kumuliert)'
        => 'Activity (last :n days, cumulative)',
    'E-Mails gesendet'              => 'Emails sent',
    'E-Mails empfangen'             => 'Emails received',
    'Teams-Nachrichten'             => 'Teams messages',
    'Teams-Meetings'                => 'Teams meetings',
    'Teams-Anrufe'                  => 'Teams calls',
    'Keine Aktivitätsdaten in diesem Zeitraum.'
        => 'No activity data in this period.',
    'Daten basieren auf aggregierten Microsoft-Berichten. Für detaillierte Nutzerauswertungen steht das'
        => 'Data is based on aggregated Microsoft reports. For detailed per-user analysis the',
    'zur Verfügung.'                => 'is available.',

    // ── usagereports/combined.php ───────────────────────────────────────────
    'Nutzungsberichte'              => 'Usage reports',
    'Adoption'                      => 'Adoption',

    // ── adoption/index.php ──────────────────────────────────────────────────
    'Keine Daten — möglicherweise sind die Berichte aktuell noch nicht generiert worden (Microsoft braucht ca. 48 h Verzögerung).'
        => 'No data — the reports may not have been generated yet (Microsoft has a delay of around 48 h).',
    'Nutzungsstatistiken der letzten 30 Tage aus Microsoft 365 Reports'
        => 'Usage statistics of the last 30 days from Microsoft 365 reports',
    'Lizenzierte Nutzer'            => 'Licensed users',
    'von :n verfügbar'             => 'of :n available',
    'Exchange aktiv'                => 'Exchange active',
    'Teams aktiv'                   => 'Teams active',
    'SharePoint aktiv'              => 'SharePoint active',
    'OneDrive aktiv'                => 'OneDrive active',
    'Service Adoption Übersicht'    => 'Service adoption overview',
    'Basis: :n Nutzer'             => 'Basis: :n users',
    'Keine Adoption-Daten verfügbar' => 'No adoption data available',
    'E-Mail-Aktivität (letzte 30 Tage)' => 'Email activity (last 30 days)',
    'Teams-Aktivität (letzte 30 Tage)'  => 'Teams activity (last 30 days)',
    'OneDrive-Aktivität (letzte 30 Tage)' => 'OneDrive activity (last 30 days)',
    'Aktive Nutzer'                 => 'Active users',
    'Nutzer'                        => 'users',
    'Gesendet'                      => 'Sent',
    'Empfangen'                     => 'Received',
    'Gelesen'                       => 'Read',
    'Team-Chat'                     => 'Team chat',
    'Privat-Chat'                   => 'Private chat',
    'Anrufe'                        => 'Calls',
    'Meetings'                      => 'Meetings',
    'Angesehen / Bearbeitet'        => 'Viewed / Edited',
    'Synchronisiert'                => 'Synced',
    'Intern geteilt'                => 'Shared internally',
    'Extern geteilt'                => 'Shared externally',

    // ── executivereport/index.php ───────────────────────────────────────────
    '<strong>Monatlicher Executive-Report</strong> per E-Mail an die Geschäftsführung mit den wichtigsten Tenant-KPIs: Security-Score, MFA-Quote, Risiko-Benutzer, Defender-Alerts, CA-Policies, Top-Findings aus der Posture-Analyse. Cron startet am 1. jedes Monats um 07:00 Uhr (lokale Zeitzone).'
        => '<strong>Monthly executive report</strong> via email to management with the most important tenant KPIs: security score, MFA rate, risky users, Defender alerts, CA policies, top findings from the posture analysis. Cron starts on the 1st of each month at 07:00 (local time zone).',
    'Aktivierung & Empfänger'       => 'Activation & recipients',
    'Monatlichen Executive-Report versenden'
        => 'Send monthly executive report',
    'Empfänger'                     => 'Recipients',
    '(kommagetrennt)'               => '(comma-separated)',
    'Standard ist die Alert-Mail-Adresse aus den allgemeinen Einstellungen.'
        => 'The default is the alert email address from the general settings.',
    'Vorschau & Test'               => 'Preview & test',
    'So wird der Report aussehen wenn er versendet wird (mit den aktuellen Tenant-Daten). Der Test-Versand schickt die Mail jetzt ohne auf den 1. des Monats zu warten.'
        => 'This is how the report will look when it is sent (using the current tenant data). The test send delivers the email now without waiting for the 1st of the month.',
    'Vorschau im Browser öffnen'    => 'Open preview in browser',
    'Den Report jetzt sofort an die konfigurierten Empfänger senden?'
        => 'Send the report to the configured recipients right now?',
    'Jetzt versenden'               => 'Send now',

    // ── servicehealth/index.php ─────────────────────────────────────────────
    'Normal'                        => 'Normal',
    'Beeinträchtigt'                => 'Degraded',
    'Unterbrochen'                  => 'Interrupted',
    'Wird wiederhergestellt'        => 'Restoring',
    'Erweitertes Recovery'          => 'Extended recovery',
    'Falsch positiv'                => 'False positive',
    'Untersucht'                    => 'Investigating',
    'Keine Service-Health-Daten verfügbar' => 'No service health data available',
    'Microsoft hat aktuell keine Status-Daten geliefert.'
        => 'Microsoft has not provided any status data at the moment.',
    'Alle Dienste normal'           => 'All services normal',
    'Keine bekannten Probleme bei Microsoft 365-Diensten.'
        => 'No known issues with Microsoft 365 services.',
    ':n aktives Problem'            => ':n active issue',
    ':n aktive Probleme'           => ':n active issues',
    'Beeinträchtigte Dienste erkannt' => 'Degraded services detected',
    'Mindestens ein Microsoft 365-Dienst meldet aktive Vorfälle.'
        => 'At least one Microsoft 365 service is reporting active incidents.',
    'Ein oder mehrere Dienste laufen nicht im Normalbetrieb.'
        => 'One or more services are not operating normally.',
    'Dienste'                       => 'Services',
    'Aktive Vorfälle'               => 'Active incidents',
    'Dienst'                        => 'Service',
    'Titel'                         => 'Title',
    'Beginn'                        => 'Start',
    'Klassifizierung'               => 'Classification',
    'Neueste Nachrichten'           => 'Latest messages',
    'Geändert'                      => 'Modified',
];
