<?php

namespace App\Modules\Devices;

use App\Graph\GraphClient;

class DevicesService
{
    public function __construct(private GraphClient $graph) {}

    public function getAll(): array
    {
        return $this->graph->paginate(
            '/deviceManagement/managedDevices',
            [
                '$select' => 'id,deviceName,operatingSystem,osVersion,complianceState,lastSyncDateTime,enrolledDateTime,userPrincipalName,manufacturer,model,managementState,isEncrypted',
                '$top'    => '999',
            ],
            30,
            'devices_all',
            900
        );
    }

    public function getOne(string $id): array
    {
        return $this->graph->get("/deviceManagement/managedDevices/{$id}", [], "device_{$id}", 600);
    }

    public function getStats(array $devices): array
    {
        $stats = ['total' => count($devices), 'by_os' => [], 'by_compliance' => [], 'encrypted' => 0];
        foreach ($devices as $d) {
            $os = $d['operatingSystem'] ?? 'Unknown';
            $stats['by_os'][$os] = ($stats['by_os'][$os] ?? 0) + 1;

            $compliance = $d['complianceState'] ?? 'unknown';
            $stats['by_compliance'][$compliance] = ($stats['by_compliance'][$compliance] ?? 0) + 1;

            if ($d['isEncrypted'] ?? false) $stats['encrypted']++;
        }
        arsort($stats['by_os']);
        return $stats;
    }
}
