<?php

namespace App\Modules\Pim;

use App\Graph\GraphClient;

/**
 * Privileged Identity Management — sammelt aktuelle und vergangene
 * Privileged-Role-Aktivierungen, eligible-Zuweisungen und permanente
 * Admin-Rollen. Zeigt, wer gerade aktiv erhöht ist und wie lange.
 */
class PimService
{
    public function __construct(private GraphClient $graph) {}

    /**
     * Aktuell aktive Rollen-Aktivierungen (PIM JIT) — direkt assigned
     * UND aktivierte eligible-Rollen.
     *
     * @return array<int, array<string,mixed>>
     */
    public function getActiveAssignments(): array
    {
        try {
            $data = $this->graph->paginate(
                '/roleManagement/directory/roleAssignmentScheduleInstances',
                ['$expand' => 'principal,roleDefinition', '$top' => '100'],
                5,
                'pim_active',
                300
            );
            return $this->normaliseAssignments($data);
        } catch (\Throwable $e) {
            error_log('PIM active: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Eligible (= verfügbar zur Aktivierung, aber gerade nicht aktiv).
     */
    public function getEligibleAssignments(): array
    {
        try {
            $data = $this->graph->paginate(
                '/roleManagement/directory/roleEligibilityScheduleInstances',
                ['$expand' => 'principal,roleDefinition', '$top' => '100'],
                5,
                'pim_eligible',
                900
            );
            return $this->normaliseAssignments($data);
        } catch (\Throwable $e) {
            error_log('PIM eligible: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Liefert alle Aktivierungen der letzten 30 Tage als Audit-Trail.
     */
    public function getRecentActivations(int $days = 30): array
    {
        $since = (new \DateTimeImmutable('-' . $days . ' days'))->format('Y-m-d\TH:i:s\Z');
        try {
            $rows = $this->graph->paginate(
                '/auditLogs/directoryAudits',
                [
                    '$filter' => "activityDateTime ge {$since} and category eq 'RoleManagement' and "
                               . "(activityDisplayName eq 'Add member to role (PIM activation)' or "
                               . "activityDisplayName eq 'Add eligible member to role')",
                    '$top'    => '200',
                ],
                5,
                'pim_activations_' . $days . 'd',
                1800
            );
            $result = [];
            foreach ($rows as $r) {
                $result[] = [
                    'when'  => $r['activityDateTime'] ?? '',
                    'who'   => $r['initiatedBy']['user']['userPrincipalName']
                            ?? $r['initiatedBy']['user']['displayName']
                            ?? '–',
                    'what'  => $r['activityDisplayName'] ?? '',
                    'role'  => $r['targetResources'][1]['displayName']
                            ?? ($r['targetResources'][0]['modifiedProperties'][0]['newValue'] ?? '–'),
                    'target'=> $r['targetResources'][0]['userPrincipalName']
                            ?? $r['targetResources'][0]['displayName']
                            ?? '–',
                    'result'=> $r['result'] ?? 'success',
                ];
            }
            return $result;
        } catch (\Throwable $e) {
            error_log('PIM recent: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Aggregate für die Übersichts-Karten.
     */
    public function getSummary(array $active, array $eligible): array
    {
        $now = time();
        $expiringSoon = 0;
        $permanentAdmins = 0;
        foreach ($active as $a) {
            if ($a['assignmentType'] === 'Assigned' && $a['endDateTime'] === null) {
                $permanentAdmins++;
            }
            if ($a['endDateTime'] !== null) {
                $end = strtotime($a['endDateTime']);
                if ($end && $end < $now + 86400 * 7) $expiringSoon++;
            }
        }
        return [
            'active_total'    => count($active),
            'eligible_total'  => count($eligible),
            'permanent_admins'=> $permanentAdmins,
            'expiring_7d'     => $expiringSoon,
        ];
    }

    /**
     * Normalisiert die Graph-Antworten zu einer einheitlichen Struktur.
     */
    private function normaliseAssignments(array $rows): array
    {
        $result = [];
        foreach ($rows as $r) {
            $principal = $r['principal'] ?? [];
            $role      = $r['roleDefinition'] ?? [];
            $result[] = [
                'id'              => $r['id'] ?? '',
                'principalType'   => $principal['@odata.type'] ?? '',
                'principalName'   => $principal['displayName']
                                  ?? $principal['userPrincipalName']
                                  ?? '–',
                'principalUpn'    => $principal['userPrincipalName'] ?? '',
                'roleName'        => $role['displayName']      ?? '–',
                'roleId'          => $role['id']               ?? '',
                'roleIsBuiltIn'   => $role['isBuiltIn']        ?? true,
                'assignmentType'  => $r['assignmentType']      ?? '',
                'memberType'      => $r['memberType']          ?? '',
                'startDateTime'   => $r['startDateTime']       ?? null,
                'endDateTime'     => $r['endDateTime']         ?? null,
                'directoryScopeId'=> $r['directoryScopeId']    ?? '/',
            ];
        }
        return $result;
    }
}
