<?php

namespace App\Modules\MailFlow;

use App\Graph\GraphClient;

class MailFlowService
{
    public function __construct(private GraphClient $graph) {}

    /**
     * Fetch Exchange Online health overview.
     * Cached 5 minutes.
     *
     * @return array
     */
    public function getMailServiceHealth(): array
    {
        try {
            $data = $this->graph->get(
                '/admin/serviceAnnouncement/healthOverviews',
                ['$filter' => "service eq 'Exchange Online'"],
                'mailflow_health',
                300
            );
            return $data['value'] ?? [];
        } catch (\Throwable) {
            return [];
        }
    }

    /**
     * Fetch active (unresolved) Exchange Online service issues.
     * Cached 5 minutes.
     *
     * @return array
     */
    public function getActiveMailIssues(): array
    {
        try {
            $data = $this->graph->get(
                '/admin/serviceAnnouncement/issues',
                [
                    '$filter' => "service eq 'Exchange Online' and isResolved eq false",
                    '$select' => 'id,title,startDateTime,impactDescription,severity,status',
                ],
                'mailflow_issues',
                300
            );
            return $data['value'] ?? [];
        } catch (\Throwable) {
            return [];
        }
    }

    /**
     * Fetch active Defender for Office 365 security alerts.
     * Returns empty array if Defender for Office 365 is not licensed or on any error.
     * Cached 5 minutes.
     *
     * @return array
     */
    public function getMailDefenderAlerts(): array
    {
        try {
            $data = $this->graph->get(
                '/security/alerts_v2',
                [
                    '$filter' => "serviceSource eq 'microsoftDefenderForOffice365' and status ne 'resolved'",
                    '$select' => 'id,title,severity,status,createdDateTime,description,category',
                    '$top'    => '20',
                ],
                'mailflow_defender_alerts',
                300
            );
            return $data['value'] ?? [];
        } catch (\Throwable) {
            return [];
        }
    }

    /**
     * Return static deep-links to Exchange Admin Center and Microsoft Defender
     * for mail-flow management tasks not available via Graph API.
     *
     * @return array<int, array{label: string, url: string, icon: string, desc: string}>
     */
    public function getAntiSpamLinks(): array
    {
        return [
            [
                'label' => 'Transportregeln (Mailflow-Regeln)',
                'url'   => 'https://admin.exchange.microsoft.com/#/transportrules',
                'icon'  => 'arrow-left-right',
                'desc'  => 'Regeln für eingehende und ausgehende E-Mails erstellen und verwalten',
            ],
            [
                'label' => 'Anti-Spam-Richtlinien',
                'url'   => 'https://security.microsoft.com/antispam',
                'icon'  => 'shield-x',
                'desc'  => 'Spam-Filter und Quarantäne-Einstellungen konfigurieren',
            ],
            [
                'label' => 'Anti-Malware-Richtlinien',
                'url'   => 'https://security.microsoft.com/antimalwarev2',
                'icon'  => 'bug',
                'desc'  => 'Malware-Schutz für eingehende E-Mails konfigurieren',
            ],
            [
                'label' => 'Anti-Phishing-Richtlinien',
                'url'   => 'https://security.microsoft.com/antiphishing',
                'icon'  => 'fish',
                'desc'  => 'Phishing-Schutz und Impersonation-Erkennung',
            ],
            [
                'label' => 'DKIM-Einstellungen',
                'url'   => 'https://security.microsoft.com/dkimv2',
                'icon'  => 'key',
                'desc'  => 'DKIM-Signaturen für ausgehende E-Mails konfigurieren',
            ],
            [
                'label' => 'Akzeptierte Domänen',
                'url'   => 'https://admin.exchange.microsoft.com/#/accepteddomains',
                'icon'  => 'globe',
                'desc'  => 'Verwaltung der akzeptierten E-Mail-Domänen',
            ],
            [
                'label' => 'Connector-Konfiguration',
                'url'   => 'https://admin.exchange.microsoft.com/#/connectors',
                'icon'  => 'diagram-2',
                'desc'  => 'Eingehende und ausgehende Connectors für Mail-Routing',
            ],
            [
                'label' => 'Quarantäne',
                'url'   => 'https://security.microsoft.com/quarantine',
                'icon'  => 'inbox',
                'desc'  => 'Quarantänisierte E-Mails überprüfen und freigeben',
            ],
        ];
    }
}
