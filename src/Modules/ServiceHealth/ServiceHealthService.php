<?php

namespace App\Modules\ServiceHealth;

use App\Graph\GraphClient;

class ServiceHealthService
{
    public function __construct(private GraphClient $graph) {}

    /**
     * Fetch the health overview for all Microsoft 365 services.
     * Cached for 5 minutes.
     *
     * @return array<int, array{id: string, service: string, status: string}>
     */
    public function getOverview(): array
    {
        try {
            $data = $this->graph->get(
                '/admin/serviceAnnouncement/healthOverviews',
                ['$select' => 'id,service,status'],
                'servicehealth_overview',
                300
            );
            $items = $data['value'] ?? [];
            return array_map(fn($item) => [
                'id'      => $item['id']      ?? '',
                'service' => $item['service'] ?? '',
                'status'  => $item['status']  ?? 'unknown',
            ], $items);
        } catch (\Throwable) {
            return [];
        }
    }

    /**
     * Fetch all active (unresolved) service incidents.
     * Cached for 5 minutes.
     *
     * @return array<int, array{id: string, title: string, service: string, status: string,
     *                           startDateTime: string, classification: string}>
     */
    public function getActiveIssues(): array
    {
        try {
            $data = $this->graph->get(
                '/admin/serviceAnnouncement/issues',
                [
                    '$filter'  => 'isResolved eq false',
                    '$select'  => 'id,title,service,status,startDateTime,classification,impactDescription',
                    '$orderby' => 'startDateTime desc',
                ],
                'servicehealth_issues',
                300
            );
            return $data['value'] ?? [];
        } catch (\Throwable) {
            return [];
        }
    }

    /**
     * Fetch recent service announcement messages (MC posts etc.).
     * Cached for 15 minutes.
     *
     * @return array<int, array{id: string, title: string, lastModifiedDateTime: string,
     *                           classification: string, services: array}>
     */
    public function getRecentMessages(int $limit = 10): array
    {
        try {
            $data = $this->graph->get(
                '/admin/serviceAnnouncement/messages',
                [
                    '$top'     => (string)$limit,
                    '$orderby' => 'lastModifiedDateTime desc',
                    '$select'  => 'id,title,lastModifiedDateTime,classification,services',
                ],
                'servicehealth_messages',
                900
            );
            return $data['value'] ?? [];
        } catch (\Throwable) {
            return [];
        }
    }
}
