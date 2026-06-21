<?php use App\Core\View; $e = fn($v) => View::escape($v); ?>

<?php if (!empty($diag)) include BASE_PATH . '/views/partials/graph_diagnostic.php'; ?>

<div class="alert alert-warning d-flex gap-3 mb-3">
    <i class="bi bi-shield-exclamation flex-shrink-0 mt-1" style="font-size:1.4rem;color:#b45309;"></i>
    <div>
        <strong><?= te('OAuth-Apps mit hohen Berechtigungen sind ein Top-Vektor für Tenant-Übernahme.') ?></strong>
        <?= te('Apps mit') ?> <code>Mail.ReadWrite.All</code>, <code>Files.ReadWrite.All</code>, <code>Directory.ReadWrite.All</code>
        <?= te('können wie ein Admin agieren. Besonders kritisch: Apps die seit Monaten keine Anmeldung mehr hatten, aber noch Berechtigungen halten — typisch nach Migrationen oder gekündigten 3rd-Party-Tools.') ?>
    </div>
</div>

<!-- ── Summary ───────────────────────────────────────────────────────── -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="metric-card">
            <div class="metric-label"><i class="bi bi-app-indicator me-1"></i><?= te('Apps gesamt') ?></div>
            <div class="metric-value"><?= number_format($summary['total']) ?></div>
            <div class="metric-sub"><?= number_format($summary['third_party']) ?> 3rd-Party</div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="metric-card" style="border-left:4px solid <?= $summary['high_priv'] > 0 ? '#dc2626' : '#16a34a' ?>;">
            <div class="metric-label"><i class="bi bi-shield-fill-exclamation me-1"></i><?= te('Hohe Berechtigung') ?></div>
            <div class="metric-value" style="color:<?= $summary['high_priv'] > 0 ? '#dc2626' : '#16a34a' ?>;">
                <?= number_format($summary['high_priv']) ?>
            </div>
            <div class="metric-sub">ReadWrite.All / FullControl</div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="metric-card" style="border-left:4px solid <?= $summary['unused_90d'] > 0 ? '#d97706' : '#9ca3af' ?>;">
            <div class="metric-label"><i class="bi bi-moon-stars me-1"></i><?= te('Inaktiv') ?> &gt; <?= te('90 Tage') ?></div>
            <div class="metric-value"><?= number_format($summary['unused_90d']) ?></div>
            <div class="metric-sub"><?= te('3rd-Party-Apps') ?></div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="metric-card">
            <div class="metric-label"><i class="bi bi-pause-circle me-1"></i><?= te('Deaktiviert') ?></div>
            <div class="metric-value"><?= number_format($summary['disabled']) ?></div>
            <div class="metric-sub">accountEnabled = false</div>
        </div>
    </div>
</div>

<!-- ── Filter Toolbar ───────────────────────────────────────────────── -->
<div class="d-flex flex-wrap gap-2 mb-3">
    <div class="btn-group btn-group-sm" role="group">
        <a href="?filter=third" class="btn <?= $showOnlyThirdParty ? 'btn-primary' : 'btn-outline-primary' ?>">
            <?= te('Nur 3rd-Party') ?>
        </a>
        <a href="?filter=all" class="btn <?= !$showOnlyThirdParty ? 'btn-primary' : 'btn-outline-primary' ?>">
            <?= te('Alle (inkl. Microsoft)') ?>
        </a>
    </div>
    <a href="?refresh=1<?= $showOnlyThirdParty ? '' : '&filter=all' ?>" class="btn btn-sm btn-outline-secondary ms-auto">
        <i class="bi bi-arrow-clockwise me-1"></i><?= te('Aktualisieren') ?>
    </a>
</div>

<!-- ── App-Tabelle ──────────────────────────────────────────────────── -->
<div class="content-card mb-4">
    <div class="card-header-custom">
        <i class="bi bi-list-ul text-primary"></i>
        <h6>Enterprise Apps</h6>
        <span class="ms-auto text-muted small"><?= count($apps) ?> App(s)</span>
    </div>
    <div class="card-body-custom p-0">
        <?php if (empty($apps)): ?>
            <div class="text-muted small p-4 text-center">Keine Apps gefunden.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>App</th>
                            <th>Risiko</th>
                            <th>Permissions</th>
                            <th>Letzte Anmeldung</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($apps as $a):
                        $rs    = (int)$a['risk_score'];
                        $rsCol = $rs >= 60 ? '#dc2626' : ($rs >= 30 ? '#d97706' : ($rs > 0 ? '#0284c7' : '#16a34a'));
                        $rsLbl = $rs >= 60 ? 'Hoch' : ($rs >= 30 ? 'Mittel' : ($rs > 0 ? 'Niedrig' : 'OK'));
                    ?>
                        <tr>
                            <td>
                                <div class="fw-medium">
                                    <?= $e($a['name']) ?>
                                    <?php if ($a['is_microsoft']): ?>
                                        <i class="bi bi-microsoft text-primary ms-1" title="Microsoft First-Party"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="text-muted small font-monospace"><?= $e($a['appId']) ?></div>
                            </td>
                            <td>
                                <span class="badge" style="background:<?= $rsCol ?>;color:#fff;">
                                    <?= $rs ?> · <?= $rsLbl ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-secondary"><?= $a['permissions_total'] ?> total</span>
                                <?php if ($a['permissions_high'] > 0): ?>
                                    <span class="badge bg-danger ms-1" title="High-Privilege">
                                        <i class="bi bi-shield-fill-exclamation me-1"></i><?= $a['permissions_high'] ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="text-muted small">
                                <?php if ($a['last_sign_in']): ?>
                                    <?= $e(date('d.m.Y', strtotime($a['last_sign_in']))) ?>
                                    <div class="text-muted small">vor <?= (int)$a['days_since_signin'] ?> Tagen</div>
                                <?php else: ?>
                                    <span class="text-warning">nie / kein Report</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!$a['enabled']): ?>
                                    <span class="badge bg-secondary">Deaktiviert</span>
                                <?php elseif ($a['unused'] && !$a['is_microsoft']): ?>
                                    <span class="badge bg-warning text-dark">Ungenutzt</span>
                                <?php else: ?>
                                    <span class="badge bg-success">Aktiv</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="https://entra.microsoft.com/#view/Microsoft_AAD_IAM/ManagedAppMenuBlade/~/Overview/objectId/<?= $e($a['id']) ?>/appId/<?= $e($a['appId']) ?>"
                                   target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary"
                                   title="In Entra ID öffnen">
                                    <i class="bi bi-box-arrow-up-right"></i>
                                </a>
                            </td>
                        </tr>
                        <?php if (!empty($a['high_privilege_perms'])): ?>
                        <tr class="text-muted small" style="background:#fef2f2;">
                            <td colspan="6" style="padding:4px 16px;">
                                <i class="bi bi-shield-fill-exclamation text-danger me-1"></i>
                                High-Privilege Scopes:
                                <?php foreach (array_slice($a['high_privilege_perms'], 0, 10) as $perm): ?>
                                    <code style="font-size:11px;"><?= $e($perm) ?></code>
                                <?php endforeach; ?>
                                <?php if (count($a['high_privilege_perms']) > 10): ?>
                                    <span class="text-muted">… und <?= count($a['high_privilege_perms']) - 10 ?> weitere</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endif; ?>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
