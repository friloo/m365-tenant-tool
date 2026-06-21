<?php use App\Core\View; $e = fn($v) => View::escape($v); ?>

<?php if (!empty($diag)) include BASE_PATH . '/views/partials/graph_diagnostic.php'; ?>

<div class="alert alert-info d-flex gap-3 mb-3">
    <i class="bi bi-diagram-2 flex-shrink-0 mt-1" style="font-size:1.4rem;color:#0078d4;"></i>
    <div>
        <strong>Lifecycle Workflows</strong> <?= te('automatisieren Joiner/Mover/Leaver-Prozesse — z. B. „bei neuem Mitarbeiter automatisch in die Standard-Gruppen aufnehmen, Welcome-Mail senden, Manager benachrichtigen" oder „bei Austritt Konto deaktivieren, Lizenzen entfernen, 30 Tage warten, dann löschen". Lizenz-Voraussetzung:') ?> <strong>Microsoft Entra ID Governance</strong>.
    </div>
</div>

<div class="content-card mb-4">
    <div class="card-header-custom">
        <i class="bi bi-list-ul text-primary"></i>
        <h6><?= te('Workflows') ?></h6>
        <span class="ms-auto text-muted small"><?= count($workflows) ?></span>
    </div>
    <div class="card-body-custom p-0">
        <?php if (empty($workflows)): ?>
            <div class="text-muted small p-4 text-center">
                <?= te('Keine Lifecycle-Workflows definiert. Konfiguration unter') ?>
                <a href="https://entra.microsoft.com/#view/Microsoft_AAD_ERM/DashboardBlade/~/Workflows" target="_blank" rel="noopener">Entra → Identity Governance → Lifecycle Workflows</a>.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead><tr>
                        <th><?= te('Workflow') ?></th>
                        <th><?= te('Kategorie') ?></th>
                        <th><?= te('Aktiv') ?></th>
                        <th><?= te('Geplant') ?></th>
                        <th><?= te('Tasks') ?></th>
                        <th><?= te('Letzter Lauf') ?></th>
                    </tr></thead>
                    <tbody>
                    <?php foreach ($workflows as $w):
                        $last = $runs[$w['id']][0] ?? null;
                    ?>
                        <tr>
                            <td>
                                <div class="fw-medium"><?= $e($w['displayName']) ?></div>
                                <?php if ($w['description']): ?>
                                    <div class="text-muted small"><?= $e($w['description']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php $cls = match ($w['category']) {
                                    'joiner' => 'bg-success', 'leaver'  => 'bg-danger',
                                    'mover'  => 'bg-info text-dark', default => 'bg-secondary',
                                }; ?>
                                <span class="badge <?= $cls ?>"><?= $e($w['category']) ?></span>
                            </td>
                            <td>
                                <?php if ($w['isEnabled']): ?>
                                    <span class="badge bg-success"><i class="bi bi-check2"></i></span>
                                <?php else: ?>
                                    <span class="badge bg-secondary"><?= te('Inaktiv') ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($w['isSchedulingEnabled']): ?>
                                    <span class="badge bg-info text-dark">Auto</span>
                                <?php else: ?>
                                    <span class="text-muted small"><?= te('manuell') ?></span>
                                <?php endif; ?>
                            </td>
                            <td><?= (int)$w['taskCount'] ?></td>
                            <td class="text-muted small">
                                <?php if ($last): ?>
                                    <?= $e(date('d.m.Y H:i', strtotime($last['startedDateTime'] ?? ''))) ?>
                                    <div><?= te('Status') ?>: <?= $e($last['processingStatus'] ?? '–') ?></div>
                                <?php else: ?>—<?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<a href="https://entra.microsoft.com/#view/Microsoft_AAD_ERM/DashboardBlade/~/Workflows" target="_blank" rel="noopener" class="btn btn-outline-primary">
    <i class="bi bi-box-arrow-up-right me-1"></i><?= te('In Entra konfigurieren') ?>
</a>
