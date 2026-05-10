<?php

namespace App\Update;

// IMPORTANT: Do NOT call new UpdateManager() directly anywhere else in the codebase.
// Always use UpdateManagerFactory::create() to ensure the correct channel is used.
final class UpdateManagerFactory
{
    public static function create(): UpdateManager
    {
        $runtimeFile = BASE_PATH . '/config/runtime.php';
        $rt = file_exists($runtimeFile) ? (include $runtimeFile) : [];
        $channel = $rt['update']['channel'] ?? 'stable';
        return new UpdateManager($channel);
    }
}
