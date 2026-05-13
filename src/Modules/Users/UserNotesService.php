<?php

namespace App\Modules\Users;

use App\Database\DB;

class UserNotesService
{
    public function getForUser(string $azureId): array
    {
        return DB::fetchAll(
            'SELECT id, note, created_by, created_at FROM user_notes WHERE user_azure_id = ? ORDER BY created_at DESC',
            [$azureId]
        );
    }

    public function add(string $azureId, string $note, string $author): int
    {
        DB::execute(
            'INSERT INTO user_notes (user_azure_id, note, created_by) VALUES (?, ?, ?)',
            [$azureId, $note, $author]
        );
        return (int) DB::lastInsertId();
    }

    public function delete(int $id, string $azureId): void
    {
        DB::execute(
            'DELETE FROM user_notes WHERE id = ? AND user_azure_id = ?',
            [$id, $azureId]
        );
    }
}
