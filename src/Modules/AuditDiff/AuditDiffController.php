<?php

namespace App\Modules\AuditDiff;

use App\Auth\LocalAuth;
use App\Core\AppAudit;
use App\Core\Redirect;
use App\Core\Session;
use App\Core\View;
use App\Modules\Notifications\NotificationService;

class AuditDiffController
{
    public function index(): void
    {
        LocalAuth::require();
        $snaps = SnapshotService::list(100);

        $left  = isset($_GET['left'])  ? (int)$_GET['left']  : ($snaps[1]['id'] ?? 0);
        $right = isset($_GET['right']) ? (int)$_GET['right'] : ($snaps[0]['id'] ?? 0);
        $diff  = null; $oldRow = null; $newRow = null;

        if ($left && $right) {
            $oldRow = SnapshotService::load($left);
            $newRow = SnapshotService::load($right);
            if ($oldRow && $newRow) {
                $diff = SnapshotService::diff($oldRow['payload'], $newRow['payload']);
            }
        }

        View::render('auditdiff/index', [
            'pageTitle' => 'Audit-Diff',
            'snapshots' => $snaps,
            'left'      => $left,
            'right'     => $right,
            'oldRow'    => $oldRow,
            'newRow'    => $newRow,
            'diff'      => $diff,
        ]);
    }

    public function capture(): void
    {
        LocalAuth::requireAdmin();
        try {
            $id = app_service(SnapshotService::class)->capture('manual');
            AppAudit::log('snapshot_capture', 'auditdiff', "Snapshot #{$id}");
            Session::flash('success', "Snapshot #{$id} erstellt.");
            NotificationService::push(
                'Manueller Tenant-Snapshot',
                'Snapshot #' . $id . ' wurde manuell erstellt.',
                'info', '/auditdiff', 'auditdiff'
            );
        } catch (\Throwable $e) {
            Session::flash('error', 'Snapshot fehlgeschlagen: ' . $e->getMessage());
        }
        Redirect::to('/auditdiff');
    }
}
