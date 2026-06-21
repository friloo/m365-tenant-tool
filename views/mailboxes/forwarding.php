<?php use App\Core\View; $e = fn($v) => View::escape($v); ?>

<?php if (!empty($flash)): ?>
    <div class="alert alert-success alert-dismissible mb-3">
        <i class="bi bi-check-circle me-2"></i><?= $e($flash) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>
<?php if (!empty($error)): ?>
    <div class="alert alert-danger alert-dismissible mb-3">
        <i class="bi bi-exclamation-triangle me-2"></i><?= $e($error) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="alert alert-warning d-flex gap-3 mb-3">
    <i class="bi bi-exclamation-triangle-fill flex-shrink-0 mt-1" style="font-size:1.4rem;color:#b45309;"></i>
    <div>
        <strong><?= te('Externe Weiterleitung ist der häufigste Exfiltrations­vektor') ?></strong> <?= te('bei kompromittierten Konten.') ?>
        <?= te('M365 kennt dafür') ?> <strong><?= te('zwei Mechanismen') ?></strong>: <?= te('die') ?> <em><?= te('Postfach-Weiterleitung') ?></em> <?= te('(am Postfach gesetzt)') ?>
        <?= te('und') ?> <em><?= te('Posteingangsregeln') ?></em>, <?= te('die Mails weiterleiten/umleiten. Beide werden hier geprüft.') ?>
    </div>
</div>

<?php
$fwdActive = count(array_filter($forwards, fn($f) => $f['forwardingEnabled']));
$ruleExt   = count($report['external_forward'] ?? []);
?>

<ul class="nav nav-tabs mb-3" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-mbx" type="button" role="tab">
            <i class="bi bi-forward-fill me-1"></i><?= te('Postfach-Weiterleitung') ?>
            <span class="badge rounded-pill bg-<?= count($forwards) > 0 ? 'warning text-dark' : 'secondary' ?> ms-1"><?= count($forwards) ?></span>
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-rules" type="button" role="tab">
            <i class="bi bi-arrow-right-square me-1"></i><?= te('Posteingangsregeln') ?>
            <span class="badge rounded-pill bg-<?= $ruleExt > 0 ? 'danger' : 'secondary' ?> ms-1"><?= $ruleExt ?></span>
        </button>
    </li>
</ul>

<div class="d-flex justify-content-end mb-3">
    <a href="/mailboxes/external-forwards?refresh=1" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-clockwise me-1"></i><?= te('Neu scannen') ?>
    </a>
</div>

<div class="tab-content">
    <!-- ── Tab 1: Postfach-Weiterleitung (forwardingSmtpAddress) ─────────── -->
    <div class="tab-pane fade show active" id="tab-mbx" role="tabpanel">
        <?php
        $totalCount       = count($forwards);
        $activeCount      = $fwdActive;
        $localAndFwdCount = count(array_filter($forwards, fn($f) => $f['deliverToMailboxAndForward']));
        ?>
        <div class="row g-3 mb-4">
            <div class="col-sm-4"><div class="metric-card">
                <div class="metric-label"><?= te('Weiterleitungen gesamt') ?></div>
                <div class="metric-value" style="color:<?= $totalCount > 0 ? '#d97706' : '#111827' ?>;"><?= $totalCount ?></div>
                <div class="metric-sub"><?= te('externe Adressen') ?></div>
            </div></div>
            <div class="col-sm-4"><div class="metric-card">
                <div class="metric-label"><?= te('Aktive Weiterleitungen') ?></div>
                <div class="metric-value" style="color:<?= $activeCount > 0 ? '#dc2626' : '#16a34a' ?>;"><?= $activeCount ?></div>
                <div class="metric-sub">forwardingEnabled = true</div>
            </div></div>
            <div class="col-sm-4"><div class="metric-card">
                <div class="metric-label"><?= te('Auch lokal zustellen') ?></div>
                <div class="metric-value"><?= $localAndFwdCount ?></div>
                <div class="metric-sub">deliverToMailboxAndForward</div>
            </div></div>
        </div>

        <?php if (empty($forwards)): ?>
            <div class="content-card"><div class="card-body-custom"><div class="empty-state">
                <i class="bi bi-check-circle text-success" style="font-size:2.5rem;"></i>
                <p class="mt-3 mb-1 fw-medium"><?= te('Keine Postfach-Weiterleitungen gefunden') ?></p>
                <p class="text-muted small"><?= te('Kein Postfach leitet per Postfach-Einstellung extern weiter — der gewünschte Zustand.') ?></p>
            </div></div></div>
        <?php else: ?>
            <div class="content-card">
                <div class="table-toolbar">
                    <input type="text" id="fwdSearch" class="search-box" placeholder="<?= te('Benutzer oder Adresse suchen…') ?>">
                    <a href="/mailboxes/external-forwards/export" class="btn btn-sm btn-outline-secondary ms-auto">
                        <i class="bi bi-download me-1"></i><?= te('CSV Export') ?>
                    </a>
                </div>
                <div class="table-responsive">
                    <table class="data-table" id="fwdTable">
                        <thead><tr>
                            <th><?= te('Benutzer') ?></th><th><?= te('Weiterleitungsadresse') ?></th><th><?= te('Status') ?></th>
                            <th><?= te('Lokal&nbsp;+&nbsp;Weiterleiten') ?></th><th class="text-end"><?= te('Aktion') ?></th>
                        </tr></thead>
                        <tbody>
                        <?php foreach ($forwards as $fwd): ?>
                            <tr>
                                <td>
                                    <div class="fw-medium" style="font-size:13px;"><?= $e($fwd['displayName']) ?></div>
                                    <div style="font-size:11px;color:#6b7280;"><?= $e($fwd['userPrincipalName']) ?></div>
                                </td>
                                <td style="font-size:13px;"><i class="bi bi-forward-fill me-1 text-warning"></i><?= $e($fwd['forwardingAddress']) ?></td>
                                <td><?php if ($fwd['forwardingEnabled']): ?><span class="badge-danger badge-pill"><?= te('Aktiv') ?></span><?php else: ?><span class="badge-secondary badge-pill"><?= te('Inaktiv') ?></span><?php endif; ?></td>
                                <td><?php if ($fwd['deliverToMailboxAndForward']): ?><span class="badge-info badge-pill"><?= te('Ja') ?></span><?php else: ?><span class="badge-neutral badge-pill"><?= te('Nein') ?></span><?php endif; ?></td>
                                <td class="text-end">
                                    <form method="post" action="/mailboxes/external-forwards/remove"
                                          onsubmit="return confirm('<?= $e(t('Weiterleitung für :name wirklich entfernen?', ['name' => addslashes($fwd['displayName'])])) ?>');">
                                        <?= \App\Core\Csrf::field() ?>
                                        <input type="hidden" name="user_id" value="<?= $e($fwd['id']) ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-x-circle me-1"></i><?= te('Entfernen') ?></button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- ── Tab 2: Posteingangsregeln (messageRules) ──────────────────────── -->
    <div class="tab-pane fade" id="tab-rules" role="tabpanel">
        <?php if (!empty($rulesDiag)) { $diag = $rulesDiag; include BASE_PATH . '/views/partials/graph_diagnostic.php'; } ?>

        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-lg-3"><div class="metric-card" style="border-left:4px solid <?= $ruleExt > 0 ? '#dc2626' : '#16a34a' ?>;">
                <div class="metric-label"><i class="bi bi-arrow-right-square me-1"></i><?= te('Externe Auto-Forwards') ?></div>
                <div class="metric-value" style="color:<?= $ruleExt > 0 ? '#dc2626' : '#16a34a' ?>;"><?= number_format($ruleExt) ?></div>
                <div class="metric-sub"><?= te('Regeln, die nach extern leiten') ?></div>
            </div></div>
            <div class="col-sm-6 col-lg-3"><div class="metric-card">
                <div class="metric-label"><i class="bi bi-arrow-right me-1"></i><?= te('Interne Auto-Forwards') ?></div>
                <div class="metric-value"><?= number_format(count($report['internal_forward'] ?? [])) ?></div>
                <div class="metric-sub"><?= te('in Tenant-eigene Domains') ?></div>
            </div></div>
            <div class="col-sm-6 col-lg-3"><div class="metric-card" style="border-left:4px solid <?= count($report['delete_rules'] ?? []) > 0 ? '#d97706' : '#9ca3af' ?>;">
                <div class="metric-label"><i class="bi bi-trash me-1"></i><?= te('Lösch-Regeln') ?></div>
                <div class="metric-value"><?= number_format(count($report['delete_rules'] ?? [])) ?></div>
                <div class="metric-sub"><?= te('verdächtig bei Phishing') ?></div>
            </div></div>
            <div class="col-sm-6 col-lg-3"><div class="metric-card">
                <div class="metric-label"><i class="bi bi-people me-1"></i><?= te('Postfächer gescannt') ?></div>
                <div class="metric-value"><?= number_format($report['scanned_users'] ?? 0) ?></div>
                <div class="metric-sub">
                    <?php if (($report['skipped_errors'] ?? 0) > 0): ?><?= te(':n nicht lesbar', ['n' => $report['skipped_errors']]) ?>
                    <?php elseif (!empty($report['truncated'])): ?><?= te('Limit erreicht — erste 500') ?>
                    <?php else: ?><?= te('alle aktiven User') ?><?php endif; ?>
                </div>
            </div></div>
        </div>

        <div class="content-card mb-4">
            <div class="card-header-custom"><i class="bi bi-arrow-right-square-fill text-danger"></i><h6><?= te('Externe Auto-Weiterleitungen') ?></h6></div>
            <div class="card-body-custom p-0">
                <?php if (empty($report['external_forward'])): ?>
                    <div class="text-muted small p-4 text-center"><i class="bi bi-check-circle text-success me-1"></i><?= te('Keine Inbox-Regeln, die an externe Adressen weiterleiten.') ?></div>
                <?php else: ?>
                    <div class="table-responsive"><table class="data-table">
                        <thead><tr><th><?= te('Benutzer') ?></th><th><?= te('Regel-Name') ?></th><th><?= te('Weiterleitung an') ?></th><th><?= te('Status') ?></th></tr></thead>
                        <tbody>
                        <?php foreach ($report['external_forward'] as $r): ?>
                            <tr>
                                <td><div class="fw-medium"><?= $e($r['name']) ?></div><div class="text-muted small"><?= $e($r['upn']) ?></div></td>
                                <td><code><?= $e($r['rule']) ?></code></td>
                                <td><span class="badge bg-danger"><i class="bi bi-globe me-1"></i><?= $e($r['forwards_to']) ?></span></td>
                                <td><?php if ($r['enabled']): ?><span class="badge bg-warning text-dark"><i class="bi bi-play-fill me-1"></i><?= te('Aktiv') ?></span><?php else: ?><span class="badge bg-secondary"><?= te('Inaktiv') ?></span><?php endif; ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table></div>
                <?php endif; ?>
            </div>
        </div>

        <?php if (!empty($report['delete_rules'])): ?>
        <div class="content-card mb-4">
            <div class="card-header-custom"><i class="bi bi-trash-fill text-warning"></i><h6>Inbox-Regeln, die Mails löschen</h6></div>
            <div class="card-body-custom">
                <p class="text-muted small mb-3">Oft mit Phishing-Hijack kombiniert: eine Regel löscht Sicherheits-Benachrichtigungen, damit der echte User nichts merkt.</p>
                <div class="table-responsive"><table class="data-table">
                    <thead><tr><th>Benutzer</th><th>Regel</th><th>Status</th></tr></thead>
                    <tbody>
                    <?php foreach ($report['delete_rules'] as $r): ?>
                        <tr>
                            <td><div class="fw-medium"><?= $e($r['name']) ?></div><div class="text-muted small"><?= $e($r['upn']) ?></div></td>
                            <td><code><?= $e($r['rule']) ?></code></td>
                            <td><?php if ($r['enabled']): ?><span class="badge bg-warning text-dark">Aktiv</span><?php else: ?><span class="badge bg-secondary">Inaktiv</span><?php endif; ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table></div>
            </div>
        </div>
        <?php endif; ?>

        <?php if (!empty($report['internal_forward'])): ?>
        <div class="content-card mb-4">
            <div class="card-header-custom"><i class="bi bi-arrow-right text-info"></i><h6>Interne Auto-Weiterleitungen <span class="text-muted small ms-2">(weniger kritisch)</span></h6></div>
            <div class="card-body-custom p-0">
                <div class="table-responsive"><table class="data-table">
                    <thead><tr><th>Benutzer</th><th>Regel</th><th>Weiterleitung an</th><th>Status</th></tr></thead>
                    <tbody>
                    <?php foreach (array_slice($report['internal_forward'], 0, 100) as $r): ?>
                        <tr>
                            <td><div class="fw-medium"><?= $e($r['name']) ?></div><div class="text-muted small"><?= $e($r['upn']) ?></div></td>
                            <td><code><?= $e($r['rule']) ?></code></td>
                            <td class="text-muted small"><?= $e($r['forwards_to']) ?></td>
                            <td><?php if ($r['enabled']): ?><span class="badge bg-success">Aktiv</span><?php else: ?><span class="badge bg-secondary">Inaktiv</span><?php endif; ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table></div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
initTableSearch('fwdSearch', 'fwdTable');
</script>
