<?php

namespace App\Modules\AuditLog;

use App\Graph\GraphClient;

class AuditLogService
{
    public function __construct(private GraphClient $graph) {}

    public function getDirectoryAudits(string $from, string $to): array
    {
        try {
            return $this->graph->paginate(
                '/auditLogs/directoryAudits',
                [
                    '$filter'  => "activityDateTime ge {$from}T00:00:00Z and activityDateTime le {$to}T23:59:59Z",
                    '$orderby' => 'activityDateTime desc',
                    '$select'  => 'id,activityDateTime,activityDisplayName,category,result,initiatedBy,targetResources',
                    '$top'     => '100',
                ],
                5,
                null // no cache — always fresh
            );
        } catch (\Throwable) { return []; }
    }

    public function getSignIns(string $from, string $to, int $limit = 100): array
    {
        try {
            $data = $this->graph->get(
                '/auditLogs/signIns',
                [
                    '$filter'  => "createdDateTime ge {$from}T00:00:00Z and createdDateTime le {$to}T23:59:59Z",
                    '$orderby' => 'createdDateTime desc',
                    '$select'  => 'id,createdDateTime,userPrincipalName,appDisplayName,ipAddress,status,location,riskLevelDuringSignIn,conditionalAccessStatus,clientAppUsed',
                    '$top'     => (string)$limit,
                ]
            );
            return $data['value'] ?? [];
        } catch (\Throwable) { return []; }
    }
}
