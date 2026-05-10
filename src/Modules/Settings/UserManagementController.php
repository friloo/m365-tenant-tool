<?php

namespace App\Modules\Settings;

use App\Auth\LocalAuth;
use App\Auth\MicrosoftAuth;
use App\Core\Redirect;
use App\Core\Session;
use App\Core\View;
use App\Database\DB;

class UserManagementController
{
    public function index(): void
    {
        LocalAuth::requireAdmin();
        $users = DB::fetchAll('SELECT * FROM m365_users ORDER BY created_at DESC');

        // Preload tenant users (cached) so the picker can filter client-side
        // without an AJAX round trip
        try {
            $tenantUsers = app_service(\App\Modules\Users\UsersService::class)->getAll();
            $tenantUsers = array_map(fn($u) => [
                'displayName'       => $u['displayName']       ?? '',
                'userPrincipalName' => $u['userPrincipalName'] ?? '',
            ], $tenantUsers);
        } catch (\Throwable) {
            $tenantUsers = [];
        }

        View::render('settings/users', [
            'pageTitle'   => 'Benutzer-Zugang',
            'users'       => $users,
            'tenantUsers' => $tenantUsers,
            'redirectUri' => MicrosoftAuth::redirectUri(),
            'flash'       => Session::getFlash('success'),
            'error'       => Session::getFlash('error'),
        ]);
    }

    public function add(): void
    {
        LocalAuth::requireAdmin();

        $upn  = strtolower(trim($_POST['upn'] ?? ''));
        $role = in_array($_POST['role'] ?? '', ['operator', 'admin']) ? $_POST['role'] : 'operator';

        if (!$upn || !filter_var($upn, FILTER_VALIDATE_EMAIL)) {
            Session::flash('error', 'Ungültige E-Mail-Adresse / UPN.');
            Redirect::to('/settings/users');
        }

        try {
            DB::execute(
                'INSERT INTO m365_users (upn, role) VALUES (?, ?)
                 ON DUPLICATE KEY UPDATE role = VALUES(role), is_active = 1',
                [$upn, $role]
            );
            Session::flash('success', "{$upn} wurde hinzugefügt.");
        } catch (\Throwable $e) {
            Session::flash('error', 'Fehler: ' . $e->getMessage());
        }

        Redirect::to('/settings/users');
    }

    public function update(string $id): void
    {
        LocalAuth::requireAdmin();

        $role   = in_array($_POST['role'] ?? '', ['operator', 'admin']) ? $_POST['role'] : 'operator';
        $active = isset($_POST['is_active']) ? 1 : 0;

        DB::execute(
            'UPDATE m365_users SET role = ?, is_active = ? WHERE id = ?',
            [$role, $active, (int)$id]
        );
        Session::flash('success', 'Benutzer aktualisiert.');
        Redirect::to('/settings/users');
    }

    public function delete(string $id): void
    {
        LocalAuth::requireAdmin();
        DB::execute('DELETE FROM m365_users WHERE id = ?', [(int)$id]);
        Session::flash('success', 'Benutzer entfernt.');
        Redirect::to('/settings/users');
    }

    public function search(): void
    {
        LocalAuth::requireAdmin();
        header('Content-Type: application/json');

        $q = strtolower(trim($_GET['q'] ?? ''));
        if (strlen($q) < 2) {
            echo json_encode([]);
            return;
        }

        try {
            $allUsers = app_service(\App\Modules\Users\UsersService::class)->getAll();
            $results  = [];
            foreach ($allUsers as $u) {
                $name = strtolower($u['displayName']       ?? '');
                $upn  = strtolower($u['userPrincipalName'] ?? '');
                if (str_contains($name, $q) || str_contains($upn, $q)) {
                    $results[] = [
                        'id'                => $u['id']                ?? '',
                        'displayName'       => $u['displayName']       ?? '',
                        'userPrincipalName' => $u['userPrincipalName'] ?? '',
                    ];
                    if (count($results) >= 15) break;
                }
            }
            echo json_encode($results);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
}
