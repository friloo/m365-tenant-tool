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

        View::render('settings/users', [
            'pageTitle'   => 'Benutzer-Zugang',
            'users'       => $users,
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
            Session::flash('error', t('Ungültige E-Mail-Adresse / UPN.'));
            Redirect::to('/settings/users');
        }

        try {
            DB::execute(
                'INSERT INTO m365_users (upn, role) VALUES (?, ?)
                 ON DUPLICATE KEY UPDATE role = VALUES(role), is_active = 1',
                [$upn, $role]
            );
            Session::flash('success', t(':upn wurde hinzugefügt.', ['upn' => $upn]));
        } catch (\Throwable $e) {
            Session::flash('error', t('Fehler: :msg', ['msg' => $e->getMessage()]));
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
        Session::flash('success', t('Benutzer aktualisiert.'));
        Redirect::to('/settings/users');
    }

    public function delete(string $id): void
    {
        LocalAuth::requireAdmin();
        DB::execute('DELETE FROM m365_users WHERE id = ?', [(int)$id]);
        Session::flash('success', t('Benutzer entfernt.'));
        Redirect::to('/settings/users');
    }

    public function search(): void
    {
        LocalAuth::requireAdmin();
        header('Content-Type: application/json; charset=utf-8');

        $qRaw = trim($_GET['q'] ?? '');
        // Use mb_strtolower so umlauts (Ü → ü) and other non-ASCII letters
        // are correctly case-folded; strtolower only handles A-Z.
        $q = function_exists('mb_strtolower') ? mb_strtolower($qRaw, 'UTF-8') : strtolower($qRaw);
        if (mb_strlen($qRaw, 'UTF-8') < 2) {
            echo json_encode([]);
            return;
        }

        try {
            $allUsers = app_service(\App\Modules\Users\UsersService::class)->getAll();
            $results  = [];
            foreach ($allUsers as $u) {
                $nameRaw = (string)($u['displayName']       ?? '');
                $upnRaw  = (string)($u['userPrincipalName'] ?? '');
                $mailRaw = (string)($u['mail']              ?? '');
                $name = function_exists('mb_strtolower') ? mb_strtolower($nameRaw, 'UTF-8') : strtolower($nameRaw);
                $upn  = function_exists('mb_strtolower') ? mb_strtolower($upnRaw,  'UTF-8') : strtolower($upnRaw);
                $mail = function_exists('mb_strtolower') ? mb_strtolower($mailRaw, 'UTF-8') : strtolower($mailRaw);
                if ($name !== '' && str_contains($name, $q)
                 || $upn  !== '' && str_contains($upn,  $q)
                 || $mail !== '' && str_contains($mail, $q)) {
                    $results[] = [
                        'id'                => $u['id']                ?? '',
                        'displayName'       => $nameRaw,
                        'userPrincipalName' => $upnRaw,
                    ];
                    if (count($results) >= 15) break;
                }
            }
            echo json_encode($results, JSON_UNESCAPED_UNICODE);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
        }
    }
}
