<?php

namespace App\Modules\AdminRoles;

use App\Auth\LocalAuth;
use App\Core\Redirect;
use App\Core\Session;
use App\Core\View;

class AdminRolesController
{
    // Roles shown with a critical badge and sorted to the top.
    private const PRIVILEGED_ROLES = [
        'Global Administrator',
        'Privileged Role Administrator',
        'Security Administrator',
    ];

    // Roles that receive a warning badge (badge-danger) in the UI.
    private const WARNING_ROLES = [
        'Global Administrator',
        'Privileged Role Administrator',
        'Security Administrator',
        'Exchange Administrator',
    ];

    public function index(): void
    {
        LocalAuth::require();

        if (isset($_GET['refresh'])) {
            app_graph()->getCache()->forget('admin_role_definitions');
            app_graph()->getCache()->forget('admin_role_assignments');
            app_graph()->getCache()->forget('admin_roles_list');
        }

        $service = app_service(AdminRolesService::class);
        ['data' => $assignments, 'diag' => $diag] = \App\Graph\GraphErrorTranslator::guard(
            fn() => $service->getRoleAssignmentsWithPrincipals(),
            'RoleManagement.Read.Directory'
        );
        $assignments ??= [];
        $definitions = $service->getAllRoleDefinitions();

        // Build a lookup map: roleDefinitionId → definition metadata.
        $defMap = [];
        foreach ($definitions as $def) {
            $defMap[$def['id']] = $def;
        }

        // Group assignments by roleDefinitionId.
        $byRole = [];
        foreach ($assignments as $assignment) {
            $roleDefId = $assignment['roleDefinitionId'] ?? '';
            if ($roleDefId === '') {
                continue;
            }

            if (!isset($byRole[$roleDefId])) {
                $def = $defMap[$roleDefId] ?? [];
                $byRole[$roleDefId] = [
                    'roleDefinitionId' => $roleDefId,
                    'displayName'      => $def['displayName'] ?? $roleDefId,
                    'description'      => $def['description'] ?? '',
                    'members'          => [],
                ];
            }

            $principal = $assignment['principal'] ?? null;
            if ($principal !== null) {
                $principal['assignmentId'] = $assignment['id'] ?? '';
                // Identify service principals (they have no userPrincipalName)
                $odataType = $principal['@odata.type'] ?? '';
                $principal['isServicePrincipal'] = str_contains($odataType, 'servicePrincipal')
                    || (($principal['userPrincipalName'] ?? '') === '' && ($principal['displayName'] ?? '') !== '');
                $byRole[$roleDefId]['members'][] = $principal;
            }
        }

        // Drop roles with no members.
        $byRole = array_filter($byRole, fn($r) => count($r['members']) > 0);

        // Sort: privileged roles first, then alphabetical.
        usort($byRole, function (array $a, array $b): int {
            $aPriv = array_search($a['displayName'], self::PRIVILEGED_ROLES, true);
            $bPriv = array_search($b['displayName'], self::PRIVILEGED_ROLES, true);

            if ($aPriv !== false && $bPriv !== false) {
                return $aPriv <=> $bPriv;
            }
            if ($aPriv !== false) {
                return -1;
            }
            if ($bPriv !== false) {
                return 1;
            }

            return strcmp($a['displayName'], $b['displayName']);
        });

        // Stats.
        $principalIds   = array_unique(array_column($assignments, 'principalId'));
        $totalAdmins    = count($principalIds);

        $globalAdminCount = 0;
        foreach ($byRole as $role) {
            if ($role['displayName'] === 'Global Administrator') {
                $globalAdminCount = count($role['members']);
                break;
            }
        }

        View::render('adminroles/index', [
            'pageTitle'        => 'Admin-Rollen',
            'byRole'           => array_values($byRole),
            'definitions'      => $definitions,
            'totalAdmins'      => $totalAdmins,
            'globalAdminCount' => $globalAdminCount,
            'warningRoles'     => self::WARNING_ROLES,
            'diag'             => $diag,
            'flash'            => Session::getFlash('success'),
            'error'            => Session::getFlash('error'),
        ]);
    }

    public function assignRole(): void
    {
        LocalAuth::requireAdmin();

        $userId           = trim($_POST['user_id'] ?? '');
        $roleDefinitionId = trim($_POST['role_definition_id'] ?? '');

        if ($userId === '' || $roleDefinitionId === '') {
            Session::flash('error', 'Benutzer-ID und Rolle sind erforderlich.');
            Redirect::to('/adminroles');
            return;
        }

        try {
            $service = app_service(AdminRolesService::class);
            $service->assignRole($userId, $roleDefinitionId);
            Session::flash('success', 'Rolle zugewiesen.');
        } catch (\Throwable $e) {
            Session::flash('error', 'Fehler: ' . $e->getMessage());
        }

        Redirect::to('/adminroles');
    }

    public function removeAssignment(string $assignmentId): void
    {
        LocalAuth::requireAdmin();

        try {
            $service = app_service(AdminRolesService::class);
            $service->removeRoleAssignment($assignmentId);
            Session::flash('success', 'Rollenzuweisung entfernt.');
        } catch (\Throwable $e) {
            Session::flash('error', 'Fehler: ' . $e->getMessage());
        }

        Redirect::to('/adminroles');
    }
}
