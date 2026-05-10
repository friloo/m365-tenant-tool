<?php

namespace App\Modules\MessageCenter;

use App\Graph\GraphClient;

class MessageCenterService
{
    public function __construct(private GraphClient $graph) {}

    /**
     * Fetch all Message Center messages from Microsoft Graph,
     * apply optional PHP-side filters, and return sorted by startDateTime DESC.
     *
     * @param array $filters  Optional: category, severity, unread, service
     * @return array
     */
    public function getMessages(array $filters = []): array
    {
        try {
            $data = $this->graph->get(
                '/admin/serviceAnnouncement/messages',
                [
                    '$select' => 'id,title,category,severity,startDateTime,endDateTime,isMajorChange,services,body,viewPoint',
                    '$top'    => '100',
                ],
                'msgcenter_messages',
                900
            );

            $messages = $data['value'] ?? [];

            // PHP-side filtering
            if (!empty($filters['category'])) {
                $cat = $filters['category'];
                $messages = array_filter($messages, fn($m) => ($m['category'] ?? '') === $cat);
            }

            if (!empty($filters['severity'])) {
                $sev = $filters['severity'];
                $messages = array_filter($messages, fn($m) => ($m['severity'] ?? '') === $sev);
            }

            if (($filters['unread'] ?? '') === '1') {
                $messages = array_filter($messages, function ($m) {
                    $vp = $m['viewPoint'] ?? null;
                    return $vp === null || ($vp['isRead'] ?? false) === false;
                });
            }

            if (!empty($filters['service'])) {
                $svc = $filters['service'];
                $messages = array_filter($messages, fn($m) => in_array($svc, $m['services'] ?? [], true));
            }

            // Re-index and sort by startDateTime DESC
            $messages = array_values($messages);
            usort($messages, function ($a, $b) {
                $dtA = $a['startDateTime'] ?? '';
                $dtB = $b['startDateTime'] ?? '';
                return strcmp($dtB, $dtA);
            });

            return $messages;
        } catch (\Throwable) {
            return [];
        }
    }

    /**
     * Compute summary statistics over a set of messages.
     *
     * @param array $messages
     * @return array{total: int, unread: int, major: int, high_severity: int}
     */
    public function getStats(array $messages): array
    {
        $unread       = 0;
        $major        = 0;
        $highSeverity = 0;

        foreach ($messages as $m) {
            $vp = $m['viewPoint'] ?? null;
            if ($vp === null || ($vp['isRead'] ?? false) === false) {
                $unread++;
            }

            if ($m['isMajorChange'] ?? false) {
                $major++;
            }

            $sev = strtolower($m['severity'] ?? '');
            if ($sev === 'high' || $sev === 'critical') {
                $highSeverity++;
            }
        }

        return [
            'total'         => count($messages),
            'unread'        => $unread,
            'major'         => $major,
            'high_severity' => $highSeverity,
        ];
    }

    /**
     * Return a sorted list of unique service names across all messages.
     *
     * @param array $messages
     * @return string[]
     */
    public function getDistinctServices(array $messages): array
    {
        $services = [];
        foreach ($messages as $m) {
            foreach ($m['services'] ?? [] as $svc) {
                $services[$svc] = true;
            }
        }
        $result = array_keys($services);
        sort($result);
        return $result;
    }

    /**
     * Return a sorted list of unique category strings across all messages.
     *
     * @param array $messages
     * @return string[]
     */
    public function getDistinctCategories(array $messages): array
    {
        $categories = [];
        foreach ($messages as $m) {
            $cat = $m['category'] ?? '';
            if ($cat !== '') {
                $categories[$cat] = true;
            }
        }
        $result = array_keys($categories);
        sort($result);
        return $result;
    }
}
