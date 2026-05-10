<?php

namespace App\Modules\Security;

use App\Graph\GraphClient;

class SecurityService
{
    public function __construct(private GraphClient $graph) {}

    public function getConditionalAccessPolicies(): array
    {
        try {
            return $this->graph->paginate(
                '/identity/conditionalAccessPolicies',
                ['$select' => 'id,displayName,state,createdDateTime,modifiedDateTime,conditions,grantControls'],
                10,
                'security_ca',
                1800
            );
        } catch (\Throwable) { return []; }
    }

    public function getRiskyUsers(): array
    {
        try {
            return $this->graph->paginate(
                '/identityProtection/riskyUsers',
                ['$select' => 'id,userDisplayName,userPrincipalName,riskLevel,riskState,riskDetail,riskLastUpdatedDateTime', '$top' => '100'],
                5,
                'security_risky',
                300
            );
        } catch (\Throwable) { return []; }
    }

    public function getRecentSignIns(int $limit = 50): array
    {
        try {
            $data = $this->graph->get(
                '/auditLogs/signIns',
                [
                    '$select' => 'id,createdDateTime,userPrincipalName,appDisplayName,ipAddress,status,location,riskLevelDuringSignIn,conditionalAccessStatus',
                    '$top'    => (string)$limit,
                    '$orderby'=> 'createdDateTime desc',
                ],
                'security_signins',
                300
            );
            return $data['value'] ?? [];
        } catch (\Throwable) { return []; }
    }

    public function getMfaSummary(): array
    {
        try {
            $data = $this->graph->paginate(
                '/reports/credentialUserRegistrationDetails',
                [],
                50,
                'security_mfa',
                1800
            );
            $total = count($data);
            $registered = count(array_filter($data, fn($u) => $u['isMfaRegistered'] ?? false));
            $capable    = count(array_filter($data, fn($u) => $u['isMfaCapable'] ?? false));
            return [
                'total'      => $total,
                'registered' => $registered,
                'capable'    => $capable,
                'pct'        => $total > 0 ? round(($registered / $total) * 100) : 0,
            ];
        } catch (\Throwable) { return ['total' => 0, 'registered' => 0, 'capable' => 0, 'pct' => 0]; }
    }

    public function toggleCaPolicy(string $policyId, string $newState): void
    {
        $this->graph->patch(
            '/identity/conditionalAccessPolicies/' . $policyId,
            ['state' => $newState]
        );
        $this->graph->getCache()->forget('ca_policies');
    }
}
