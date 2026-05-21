<?php use App\Core\View; $e = fn($v) => View::escape($v); ?>

<?php if (!empty($diag)) include BASE_PATH . '/views/partials/graph_diagnostic.php'; ?>

<div class="alert alert-warning d-flex gap-3 mb-3">
    <i class="bi bi-exclamation-triangle-fill flex-shrink-0 mt-1" style="font-size:1.4rem;color:#b45309;"></i>
    <div>
        <strong>Auto-Forward an externe Domains ist der häufigste Exfiltrations­vektor</strong> bei
        kompromittierten Konten. Eine versteckte Inbox-Regel leitet alle eingehenden Mails an die
        Angreifer-Adresse weiter — meist Tage bevor der User es bemerkt.
        Externe Weiterleitungen sollten immer geprüft und mit Exchange-Online-Mail-Flow-Regeln
        blockiert werden.
    </div>
</div>

<!-- ── Summary Tiles ─────────────────────────────────────────────────── -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="metric-card" style="border-left:4px solid <?= count($report['external_forward']) > 0 ? '#dc2626' : '#16a34a' ?>;">
            <div class="metric-label"><i class="bi bi-arrow-right-square me-1"></i>Externe Auto-Forwards</div>
            <div class="metric-value" style="color:<?= count($report['external_forward']) > 0 ? '#dc2626' : '#16a34a' ?>;">
                <?= number_format(count($report['external_forward'])) ?>
            </div>
            <div class="metric-sub">Regeln, die nach extern leiten</div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="metric-card">
            <div class="metric-label"><i class="bi bi-arrow-right me-1"></i>Interne Auto-Forwards</div>
            <div class="metric-value"><?= number_format(count($report['internal_forward'])) ?></div>
            <div class="metric-sub">in Tenant-eigene Domains</div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="metric-card" style="border-left:4px solid <?= count($report['delete_rules']) > 0 ? '#d97706' : '#9ca3af' ?>;">
            <div class="metric-label"><i class="bi bi-trash me-1"></i>Lösch-Regeln</div>
            <div class="metric-value"><?= number_format(count($report['delete_rules'])) ?></div>
            <div class="metric-sub">verdächtig bei Phishing</div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="metric-card">
            <div class="metric-label"><i class="bi bi-people me-1"></i>Postfächer gescannt</div>
            <div class="metric-value"><?= number_format($report['scanned_users']) ?></div>
            <div class="metric-sub">
                <?php if ($report['skipped_errors'] > 0): ?>
                    <?= $report['skipped_errors'] ?> nicht lesbar
                <?php elseif ($report['truncated']): ?>
                    Limit erreicht — erste 500
                <?php else: ?>
                    alle aktiven User
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="d-flex justify-content-end mb-3">
    <a href="?refresh=1" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-clockwise me-1"></i>Neu scannen
    </a>
</div>

<!-- ── Externe Forwards (kritisch) ────────────────────────────────────── -->
<div class="content-card mb-4">
    <div class="card-header-custom">
        <i class="bi bi-arrow-right-square-fill text-danger"></i>
        <h6>Externe Auto-Weiterleitungen</h6>
    </div>
    <div class="card-body-custom p-0">
        <?php if (empty($report['external_forward'])): ?>
            <div class="text-muted small p-4 text-center">
                <i class="bi bi-check-circle text-success me-1"></i>
                Keine Inbox-Regeln gefunden, die Mails an externe Adressen weiterleiten.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Benutzer</th>
                            <th>Regel-Name</th>
                            <th>Weiterleitung an</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($report['external_forward'] as $r): ?>
                        <tr>
                            <td>
                                <div class="fw-medium"><?= $e($r['name']) ?></div>
                                <div class="text-muted small"><?= $e($r['upn']) ?></div>
                            </td>
                            <td><code><?= $e($r['rule']) ?></code></td>
                            <td>
                                <span class="badge bg-danger"><i class="bi bi-globe me-1"></i><?= $e($r['forwards_to']) ?></span>
                            </td>
                            <td>
                                <?php if ($r['enabled']): ?>
                                    <span class="badge bg-warning text-dark"><i class="bi bi-play-fill me-1"></i>Aktiv</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Inaktiv</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- ── Lösch-Regeln ──────────────────────────────────────────────────── -->
<?php if (!empty($report['delete_rules'])): ?>
<div class="content-card mb-4">
    <div class="card-header-custom">
        <i class="bi bi-trash-fill text-warning"></i>
        <h6>Inbox-Regeln, die Mails löschen</h6>
    </div>
    <div class="card-body-custom">
        <p class="text-muted small mb-3">
            Lösch-Regeln werden oft mit Phishing-Hijack kombiniert — der Angreifer richtet eine Regel ein,
            die alle Antworten/Sicherheits-Benachrichtigungen direkt löscht, damit der echte User nichts merkt.
        </p>
        <div class="table-responsive">
            <table class="data-table">
                <thead><tr><th>Benutzer</th><th>Regel</th><th>Status</th></tr></thead>
                <tbody>
                <?php foreach ($report['delete_rules'] as $r): ?>
                    <tr>
                        <td>
                            <div class="fw-medium"><?= $e($r['name']) ?></div>
                            <div class="text-muted small"><?= $e($r['upn']) ?></div>
                        </td>
                        <td><code><?= $e($r['rule']) ?></code></td>
                        <td>
                            <?php if ($r['enabled']): ?>
                                <span class="badge bg-warning text-dark">Aktiv</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Inaktiv</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- ── Interne Forwards (Info) ───────────────────────────────────────── -->
<?php if (!empty($report['internal_forward'])): ?>
<div class="content-card mb-4">
    <div class="card-header-custom">
        <i class="bi bi-arrow-right text-info"></i>
        <h6>Interne Auto-Weiterleitungen <span class="text-muted small ms-2">(weniger kritisch)</span></h6>
    </div>
    <div class="card-body-custom p-0">
        <div class="table-responsive">
            <table class="data-table">
                <thead><tr><th>Benutzer</th><th>Regel</th><th>Weiterleitung an</th><th>Status</th></tr></thead>
                <tbody>
                <?php foreach (array_slice($report['internal_forward'], 0, 100) as $r): ?>
                    <tr>
                        <td>
                            <div class="fw-medium"><?= $e($r['name']) ?></div>
                            <div class="text-muted small"><?= $e($r['upn']) ?></div>
                        </td>
                        <td><code><?= $e($r['rule']) ?></code></td>
                        <td class="text-muted small"><?= $e($r['forwards_to']) ?></td>
                        <td>
                            <?php if ($r['enabled']): ?>
                                <span class="badge bg-success">Aktiv</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Inaktiv</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>
