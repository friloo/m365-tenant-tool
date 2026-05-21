<?php

namespace App\Modules\Api;

use App\Auth\LocalAuth;
use App\Core\AppAudit;
use App\Core\Redirect;
use App\Core\Session;
use App\Core\View;
use App\Database\DB;

/**
 * UI for creating, listing and revoking API keys. Plaintext is shown
 * exactly once — at creation time — and then never persisted; only the
 * sha256 hash is stored in app_api_keys.
 */
class ApiKeysController
{
    public function index(): void
    {
        LocalAuth::requireAdmin();
        $rows = [];
        try {
            $rows = DB::fetchAll("SELECT id, name, scopes, created_by, created_at, last_used, revoked_at
                                  FROM app_api_keys ORDER BY id DESC") ?: [];
        } catch (\Throwable) {}

        View::render('api/keys', [
            'pageTitle' => 'API-Schlüssel',
            'keys'      => $rows,
            'fresh'     => Session::get('_api_fresh_key'),
        ]);
        Session::remove('_api_fresh_key');
    }

    public function create(): void
    {
        LocalAuth::requireAdmin();
        $name   = trim((string)($_POST['name']   ?? ''));
        $scopes = $_POST['scopes'] ?? ['read'];
        if (!is_array($scopes)) $scopes = [$scopes];
        $scopes = array_values(array_unique(array_filter(array_map('trim', $scopes), fn($s) => in_array($s, ['read','write','admin'], true))));
        if (empty($scopes)) $scopes = ['read'];

        if ($name === '') {
            Session::flash('error', 'Name darf nicht leer sein.');
            Redirect::to('/settings/api-keys');
        }

        $plain = ApiAuth::generate();
        $hash  = ApiAuth::hash($plain);
        try {
            DB::execute(
                "INSERT INTO app_api_keys (name, key_hash, scopes, created_by) VALUES (?, ?, ?, ?)",
                [$name, $hash, implode(',', $scopes), LocalAuth::username()]
            );
            Session::set('_api_fresh_key', ['name' => $name, 'key' => $plain]);
            AppAudit::log('api_key_create', 'api', "Name: {$name}, Scopes: " . implode(',', $scopes));
        } catch (\Throwable $e) {
            Session::flash('error', 'API-Key konnte nicht gespeichert werden: ' . $e->getMessage());
        }
        Redirect::to('/settings/api-keys');
    }

    public function revoke(string $id): void
    {
        LocalAuth::requireAdmin();
        try {
            DB::execute("UPDATE app_api_keys SET revoked_at = NOW() WHERE id = ?", [(int)$id]);
            AppAudit::log('api_key_revoke', 'api', "ID: {$id}");
            Session::flash('success', 'API-Key widerrufen.');
        } catch (\Throwable $e) {
            Session::flash('error', 'Widerruf fehlgeschlagen: ' . $e->getMessage());
        }
        Redirect::to('/settings/api-keys');
    }
}
