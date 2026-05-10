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

    public function getDevice(string $deviceId): array
    {
        return $this->graph->get("/deviceManagement/managedDevices/{$deviceId}", [], null, 0);
    }

    public function syncDevice(string $deviceId): void
    {
        $this->graph->post("/deviceManagement/managedDevices/{$deviceId}/syncDevice", []);
    }

    public function retireDevice(string $deviceId): void
    {
        $this->graph->post("/deviceManagement/managedDevices/{$deviceId}/retire", []);
    }

    public function wipeDevice(string $deviceId): void
    {
        $this->graph->post("/deviceManagement/managedDevices/{$deviceId}/wipe", [
            'keepEnrollmentData' => false,
            'keepUserData'       => false,
        ]);
    }

    public function getBitLockerKeys(string $azureAdDeviceId): array
    {
        if ($azureAdDeviceId === '') {
            return [];
        }
        try {
            $result = $this->graph->get(
                '/informationProtection/bitlocker/recoveryKeys',
                [
                    '$filter' => "deviceId eq '{$azureAdDeviceId}'",
                    '$select' => 'id,createdDateTime,deviceId',
                ],
                null,
                0
            );

            $keys = $result['value'] ?? (isset($result['id']) ? [$result] : []);
            $output = [];
            foreach ($keys as $keyMeta) {
                $keyId = $keyMeta['id'] ?? '';
                if ($keyId === '') continue;
                try {
                    $keyDetail = $this->graph->get(
                        "/informationProtection/bitlocker/recoveryKeys/{$keyId}",
                        ['$select' => 'key'],
                        null,
                        0
                    );
                    $output[] = [
                        'id'              => $keyId,
                        'createdDateTime' => $keyMeta['createdDateTime'] ?? '',
                        'key'             => $keyDetail['key'] ?? '',
                    ];
                } catch (\Throwable) {
                    $output[] = [
                        'id'              => $keyId,
                        'createdDateTime' => $keyMeta['createdDateTime'] ?? '',
                        'key'             => '',
                    ];
                }
            }
            return $output;
        } catch (\Throwable) {
            return [];
        }
    }

    public function getDeviceDetail(string $deviceId): array
    {
        return $this->graph->get(
            "/deviceManagement/managedDevices/{$deviceId}",
            [
                '$select' => 'id,deviceName,operatingSystem,osVersion,complianceState,managementState,enrolledDateTime,lastSyncDateTime,userDisplayName,userPrincipalName,azureADDeviceId,manufacturer,model,serialNumber,imei,totalStorageSpaceInBytes,freeStorageSpaceInBytes,isEncrypted,jailBroken',
            ],
            null,
            0
        );
    }
}
