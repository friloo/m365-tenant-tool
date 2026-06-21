<?php
use App\Core\View;
use App\Modules\Workflows\WorkflowService;
?>
<div class="content-card">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h1 class="mb-0"><i class="bi bi-diagram-2"></i> <?= te('Workflow-Automatisierung') ?> <?= \App\Core\Help::tip('workflow_automation') ?></h1>
        <a href="/workflows/edit/0" class="btn btn-primary"><i class="bi bi-plus-lg"></i> <?= te('Neuer Workflow') ?></a>
    </div>
    <p class="text-muted"><?= te('Trigger + Aktionen, ausgeführt vom Cron alle 15 Minuten. Leichtgewichtig im Vergleich zu Power Automate, aber ausreichend für 80 % der Tenant-Standardabläufe.') ?></p>

    <?php $flash = \App\Core\Session::getFlash('success'); $err = \App\Core\Session::getFlash('error'); ?>
    <?php if ($flash): ?><div class="alert alert-success"><?= View::escape($flash) ?></div><?php endif; ?>
    <?php if ($err): ?><div class="alert alert-danger"><?= View::escape($err) ?></div><?php endif; ?>

    <?php if (empty($workflows)): ?>
        <div class="alert alert-light text-center text-muted py-5">
            <i class="bi bi-diagram-2" style="font-size:36px"></i>
            <p class="mt-2 mb-0"><?= te('Noch keine Workflows angelegt.') ?></p>
            <p class="small"><?= te('Klicke oben auf') ?> <strong><?= te('Neuer Workflow') ?></strong><?= te(', um z. B. „Neuer Gast → in Gruppe X aufnehmen + Mail an IT-Leitung" zu konfigurieren.') ?></p>
        </div>
    <?php else: ?>
        <table class="table table-sm">
            <thead><tr>
                <th><?= te('Name') ?></th><th><?= te('Trigger') ?></th><th><?= te('Aktionen') ?></th><th><?= te('Letzter Lauf') ?></th><th><?= te('Status') ?></th><th><?= te('Aktiv') ?></th><th></th>
            </tr></thead>
            <tbody>
            <?php foreach ($workflows as $w): ?>
                <?php $actions = json_decode((string)$w['actions'], true) ?: []; ?>
                <tr>
                    <td><a href="/workflows/edit/<?= (int)$w['id'] ?>"><?= View::escape($w['name']) ?></a></td>
                    <td><span class="badge bg-info text-dark"><?= View::escape(WorkflowService::TRIGGERS[$w['trigger_key']] ?? $w['trigger_key']) ?></span></td>
                    <td><?= count($actions) ?></td>
                    <td class="small text-muted"><?= View::escape($w['last_run'] ?: '—') ?></td>
                    <td>
                        <?php
                        $st = $w['last_status'] ?? '';
                        $cls = match ($st) { 'ok' => 'success', 'idle' => 'secondary', 'error' => 'danger', default => 'light text-dark' };
                        ?>
                        <span class="badge bg-<?= $cls ?>" title="<?= View::escape((string)$w['last_msg']) ?>"><?= View::escape($st ?: 'pending') ?></span>
                    </td>
                    <td>
                        <?php if ($w['enabled']): ?>
                            <span class="badge bg-success"><?= te('aktiv') ?></span>
                        <?php else: ?>
                            <span class="badge bg-secondary"><?= te('pausiert') ?></span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="/workflows/edit/<?= (int)$w['id'] ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
