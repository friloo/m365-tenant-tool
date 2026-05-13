<?php

namespace App\Core;

use App\Database\DB;

class AppAudit
{
    public static function log(string $action, string $module = '', string $detail = ''): void
    {
        try {
            $actor = Session::get('username') ?? Session::get('auth_upn') ?? 'system';
            $ip    = $_SERVER['REMOTE_ADDR'] ?? '';
            DB::getInstance()->execute(
                "INSERT INTO app_audit_log (actor, action, module, detail, ip_address) VALUES (?, ?, ?, ?, ?)",
                [$actor, $action, $module, $detail, $ip]
            );
        } catch (\Throwable) {
            // Never let audit logging break the application
        }
    }
}
