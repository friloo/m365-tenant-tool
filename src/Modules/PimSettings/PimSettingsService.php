<?php

namespace App\Modules\PimSettings;

use App\Graph\GraphClient;

/**
 * Read-only view of PIM activation rules per directory role
 * (/policies/roleManagementPolicyAssignments, scoped to DirectoryRole).
 * Surfaces whether activation requires MFA / justification / approval and the
 * maximum activation duration. Editing PIM rules is intentionally left to the
 * Entra portal (writing role-management policy rules is intricate and risky).
 */
class PimSettingsService
{
    public function __construct(private GraphClient $graph) {}

    /** Returns one row per directory role with its end-user activation rules. */
    public function getRoleSettings(): array
    {
        $data = $this->graph->get(
            '/policies/roleManagementPolicyAssignments',
            [
                '$filter' => "scopeId eq '/' and scopeType eq 'DirectoryRole'",
                '$expand' => 'policy($expand=rules)',
            ],
            'pim_role_policies',
            1800
        );
        $assignments = $data['value'] ?? [];
        $roleNames   = $this->roleNames();

        $rows = [];
        foreach ($assignments as $a) {
            $rid   = (string)($a['roleDefinitionId'] ?? '');
            $rules = $a['policy']['rules'] ?? [];

            $mfa = $just = $approval = false;
            $maxDuration = null;
            foreach ($rules as $r) {
                $id   = (string)($r['id'] ?? '');
                $type = (string)($r['@odata.type'] ?? '');
                if ($id === 'Expiration_EndUser_Assignment' && str_contains($type, 'ExpirationRule')) {
                    $maxDuration = $r['maximumDuration'] ?? null;
                } elseif ($id === 'Enablement_EndUser_Assignment' && str_contains($type, 'EnablementRule')) {
                    $enabled = $r['enabledRules'] ?? [];
                    $mfa  = in_array('MultiFactorAuthentication', $enabled, true);
                    $just = in_array('Justification', $enabled, true);
                } elseif ($id === 'Approval_EndUser_Assignment' && str_contains($type, 'ApprovalRule')) {
                    $approval = (bool)($r['setting']['isApprovalRequired'] ?? false);
                }
            }

            $rows[] = [
                'role'          => $roleNames[$rid] ?? ($rid !== '' ? $rid : '(unbekannt)'),
                'mfa'           => $mfa,
                'justification' => $just,
                'approval'      => $approval,
                'maxDuration'   => $maxDuration,
            ];
        }

        usort($rows, fn($a, $b) => strcmp($a['role'], $b['role']));
        return $rows;
    }

    /** Returns the last Graph error, or null. */
    public function getLastError(): ?array
    {
        return $this->graph->getLastError();
    }

    /** roleDefinitionId → display name. */
    private function roleNames(): array
    {
        try {
            $data = $this->graph->get(
                '/roleManagement/directory/roleDefinitions',
                ['$select' => 'id,displayName', '$top' => '500'],
                'pim_role_defs',
                3600
            );
            $map = [];
            foreach ($data['value'] ?? [] as $r) {
                $map[(string)($r['id'] ?? '')] = (string)($r['displayName'] ?? '');
            }
            return $map;
        } catch (\Throwable) {
            return [];
        }
    }

    /** Format an ISO-8601 duration like "PT8H" / "P1D" into a German label. */
    public static function formatDuration(?string $iso): string
    {
        if (!$iso) return '—';
        if (!preg_match('/^P(?:(\d+)D)?(?:T(?:(\d+)H)?(?:(\d+)M)?)?$/', $iso, $m)) {
            return $iso;
        }
        $parts = [];
        if (!empty($m[1])) $parts[] = $m[1] . ' Tag' . ($m[1] === '1' ? '' : 'e');
        if (!empty($m[2])) $parts[] = $m[2] . ' Std';
        if (!empty($m[3])) $parts[] = $m[3] . ' Min';
        return $parts ? implode(' ', $parts) : $iso;
    }
}
