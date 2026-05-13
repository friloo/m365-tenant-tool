<?php

namespace App\Modules\Sharing;

use App\Database\DB;
use App\Graph\GraphClient;

class SharingService
{
    public function __construct(private GraphClient $graph) {}

    /**
     * Returns all tracked shares from the DB (populated by the share_scan cron job).
     * Excludes revoked entries by default.
     */
    public function getAll(string $statusFilter = ''): array
    {
        $sql    = "SELECT * FROM share_reviews WHERE status != 'revoked'";
        $params = [];
        if ($statusFilter) {
            $sql    = 'SELECT * FROM share_reviews WHERE status = ?';
            $params = [$statusFilter];
        }
        $sql .= ' ORDER BY first_detected DESC';
        return DB::fetchAll($sql, $params);
    }

    /**
     * True when the share_reviews table has at least one row (scan has run before).
     */
    public function hasBeenScanned(): bool
    {
        $row = DB::fetchOne('SELECT 1 FROM share_reviews LIMIT 1');
        return (bool)$row;
    }

    public function getSharingSummary(string $statusFilter = ''): array
    {
        $items  = $this->getAll($statusFilter);
        $byType = ['organization' => 0, 'users' => 0, 'anonymous' => 0, 'unknown' => 0];

        foreach ($items as $row) {
            $scope = $row['share_scope'] ?? 'unknown';
            $byType[$scope] = ($byType[$scope] ?? 0) + 1;
        }

        // Map DB rows to the shape the view expects
        $mapped = array_map(fn($row) => [
            'id'           => $row['id'],
            'drive_id'     => $row['drive_id'],
            'item_id'      => $row['item_id'],
            'permission_id'=> $row['permission_id'],
            'type'         => 'SharePoint',
            'site'         => $row['site_name'] ?? '',
            'name'         => $row['item_name'] ?? '',
            'url'          => $row['item_url'] ?? '',
            'scope'        => $row['share_scope'] ?? 'unknown',
            'owner'        => $row['owner_display_name'] ?: ($row['owner_upn'] ?? ''),
            'owner_upn'    => $row['owner_upn'] ?? '',
            'modified'     => $row['first_detected'] ?? '',
            'status'       => $row['status'] ?? 'active',
        ], $items);

        return [
            'total'      => count($mapped),
            'byType'     => $byType,
            'items'      => $mapped,
            'hasScanned' => $this->hasBeenScanned(),
        ];
    }

    public function revokePermission(string $driveId, string $itemId, string $permissionId): void
    {
        $this->graph->delete("/drives/{$driveId}/items/{$itemId}/permissions/{$permissionId}");

        // Mark as revoked in DB
        DB::execute(
            "UPDATE share_reviews SET status='revoked', revoked_at=NOW()
             WHERE drive_id=? AND item_id=? AND permission_id=?",
            [$driveId, $itemId, $permissionId]
        );
    }
}
