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

<p class="text-muted mb-3" style="font-size:13px;">
    Postfächer die E-Mails extern weiterleiten &mdash; Sicherheitsrisiko prüfen
</p>

<!-- Security warning -->
<div class="alert alert-warning mb-4">
    <i class="bi bi-exclamation-triangle me-2"></i>
    <strong>Hinweis:</strong> Externe Weiterleitungen können sensible Daten aus Ihrer Organisation leiten.
    Prüfen Sie alle aufgeführten Weiterleitungen auf Autorisierung.
</div>

<?php
$totalCount   = count($forwards);
$activeCount  = count(array_filter($forwards, fn($f) => $f['forwardingEnabled']));
$localAndFwdCount = count(array_filter($forwards, fn($f) => $f['deliverToMailboxAndForward']));
?>

<!-- Metric cards -->
<div class="row g-3 mb-4">
    <div class="col-sm-4">
        <div class="metric-card">
            <div class="metric-label">Weiterleitungen gesamt</div>
            <div class="metric-value" style="color:<?= $totalCount > 0 ? '#d97706' : '#111827' ?>;">
                <?= $totalCount ?>
            </div>
            <div class="metric-sub">externe Adressen</div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="metric-card">
            <div class="metric-label">Aktive Weiterleitungen</div>
            <div class="metric-value" style="color:<?= $activeCount > 0 ? '#dc2626' : '#16a34a' ?>;">
                <?= $activeCount ?>
            </div>
            <div class="metric-sub">forwardingEnabled = true</div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="metric-card">
            <div class="metric-label">Auch lokal zustellen</div>
            <div class="metric-value"><?= $localAndFwdCount ?></div>
            <div class="metric-sub">deliverToMailboxAndForward</div>
        </div>
    </div>
</div>

<?php if (empty($forwards)): ?>
<!-- Empty state — this is the desired state -->
<div class="content-card">
    <div class="card-body-custom">
        <div class="empty-state">
            <i class="bi bi-check-circle text-success" style="font-size:2.5rem;"></i>
            <p class="mt-3 mb-1 fw-medium">Keine externen Weiterleitungen gefunden</p>
            <p class="text-muted small">
                Kein Postfach leitet E-Mails an eine externe Adresse weiter.
                Das ist der gewünschte Zustand.
            </p>
        </div>
    </div>
</div>
<?php else: ?>

<!-- Table card -->
<div class="content-card">
    <div class="table-toolbar">
        <input type="text" id="fwdSearch" class="search-box" placeholder="Benutzer oder Adresse suchen…">
        <a href="/mailboxes/external-forwards/export" class="btn btn-sm btn-outline-secondary ms-auto">
            <i class="bi bi-download me-1"></i>CSV Export
        </a>
    </div>

    <div class="table-responsive">
        <table class="data-table" id="fwdTable">
            <thead>
                <tr>
                    <th>Benutzer</th>
                    <th>Weiterleitungsadresse</th>
                    <th>Status</th>
                    <th>Lokal&nbsp;+&nbsp;Weiterleiten</th>
                    <th class="text-end">Aktion</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($forwards as $fwd): ?>
                    <tr>
                        <td>
                            <div class="fw-medium" style="font-size:13px;">
                                <?= $e($fwd['displayName']) ?>
                            </div>
                            <div style="font-size:11px;color:#6b7280;">
                                <?= $e($fwd['userPrincipalName']) ?>
                            </div>
                        </td>
                        <td style="font-size:13px;">
                            <i class="bi bi-forward-fill me-1 text-warning"></i>
                            <?= $e($fwd['forwardingAddress']) ?>
                        </td>
                        <td>
                            <?php if ($fwd['forwardingEnabled']): ?>
                                <span class="badge-danger badge-pill">Aktiv</span>
                            <?php else: ?>
                                <span class="badge-secondary badge-pill">Inaktiv</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($fwd['deliverToMailboxAndForward']): ?>
                                <span class="badge-info badge-pill">Ja</span>
                            <?php else: ?>
                                <span class="badge-neutral badge-pill">Nein</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">
                            <form method="post" action="/mailboxes/external-forwards/remove"
                                  onsubmit="return confirm('Weiterleitung für <?= $e(addslashes($fwd['displayName'])) ?> wirklich entfernen?');">
                                <input type="hidden" name="user_id" value="<?= $e($fwd['id']) ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="bi bi-x-circle me-1"></i>Weiterleitung entfernen
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php endif; ?>

<p class="text-muted small mt-3">
    <i class="bi bi-clock me-1"></i>
    Diese Seite wird stündlich aktualisiert.
    <a href="/mailboxes/external-forwards?refresh=1">Jetzt aktualisieren</a>
</p>

<script>
initTableSearch('fwdSearch', 'fwdTable');
</script>
