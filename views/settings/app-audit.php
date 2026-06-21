<?php use App\Core\View; $e = fn($v) => View::escape((string)$v); ?>

<?php if ($flash): ?>
    <div class="alert alert-success alert-dismissible mb-4">
        <i class="bi bi-check-circle me-2"></i><?= $e($flash) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="content-card">
    <div class="card-header-custom">
        <i class="bi bi-journal-check text-primary"></i>
        <h6><?= te('App Audit-Log') ?> <span class="text-muted fw-normal"><?= te('(letzte 200 Einträge)') ?></span></h6>
    </div>
    <div class="card-body-custom p-0">
        <?php if (empty($rows)): ?>
            <p class="text-muted p-3 mb-0"><?= te('Noch keine Einträge vorhanden.') ?></p>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0" style="font-size:13px;">
                <thead class="table-light">
                    <tr>
                        <th><?= te('Zeitpunkt') ?></th>
                        <th><?= te('Benutzer') ?></th>
                        <th><?= te('Aktion') ?></th>
                        <th><?= te('Modul') ?></th>
                        <th><?= te('Detail') ?></th>
                        <th>IP</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $row): ?>
                    <tr>
                        <td class="text-nowrap text-muted"><?= $e($row['created_at']) ?></td>
                        <td><?= $e($row['actor']) ?></td>
                        <td><code><?= $e($row['action']) ?></code></td>
                        <td><?= $e($row['module']) ?></td>
                        <td class="text-muted"><?= $e($row['detail']) ?></td>
                        <td class="text-muted font-monospace"><?= $e($row['ip_address']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>
