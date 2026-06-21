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
    <?= te('Übersicht aller freigegebenen Postfächer im Tenant') ?>
</p>

<!-- Permissions info alert -->
<div class="alert alert-info mb-4">
    <i class="bi bi-info-circle me-2"></i>
    Postfachberechtigungen (<strong>Full Access</strong>, <strong>Send As</strong>) werden über Exchange Online
    verwaltet und sind über die Graph API nicht direkt abrufbar.
    Verwalten Sie Berechtigungen im Exchange Admin Center.
    <a href="https://admin.exchange.microsoft.com/#/sharedmailboxes" target="_blank"
       class="btn btn-sm btn-outline-primary ms-3">
        <i class="bi bi-box-arrow-up-right me-1"></i>Exchange Admin Center öffnen
    </a>
</div>

<?php
$totalCount     = count($mailboxes);
$autoReplyCount = count(array_filter($mailboxes, fn($m) => in_array($m['autoReplyStatus'], ['alwaysEnabled', 'scheduled'], true)));
$fwdCount       = count(array_filter($mailboxes, fn($m) => $m['forwardingAddress'] !== ''));
?>

<!-- Metric cards -->
<div class="row g-3 mb-4">
    <div class="col-sm-4">
        <div class="metric-card">
            <div class="metric-label">Gesamt</div>
            <div class="metric-value"><?= $totalCount ?></div>
            <div class="metric-sub">Shared Mailboxes</div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="metric-card">
            <div class="metric-label">Mit Auto-Antwort aktiv</div>
            <div class="metric-value"><?= $autoReplyCount ?></div>
            <div class="metric-sub">Auto-Reply eingeschaltet</div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="metric-card">
            <div class="metric-label">Mit externer Weiterleitung</div>
            <div class="metric-value" style="color:<?= $fwdCount > 0 ? '#d97706' : '#111827' ?>;">
                <?= $fwdCount ?>
            </div>
            <div class="metric-sub">Weiterleitung konfiguriert</div>
        </div>
    </div>
</div>

<?php if (empty($mailboxes)): ?>
<!-- Empty state -->
<div class="content-card">
    <div class="card-body-custom">
        <div class="empty-state">
            <i class="bi bi-envelope text-muted" style="font-size:2.5rem;"></i>
            <p class="mt-3 mb-1 fw-medium">Keine freigegebenen Postfächer gefunden</p>
            <p class="text-muted small">
                Es wurden keine deaktivierten, lizenzierten Benutzerkonten gefunden,
                die als Shared Mailboxes fungieren.
            </p>
            <a href="/mailboxes" class="btn btn-sm btn-primary mt-2">
                <i class="bi bi-plus-circle me-1"></i>Shared Mailbox anlegen
            </a>
        </div>
    </div>
</div>
<?php else: ?>

<!-- Table card -->
<div class="content-card">
    <div class="table-toolbar">
        <input type="text" id="sharedSearch" class="search-box" placeholder="Postfach suchen…">
        <a href="/mailboxes" class="btn btn-sm btn-primary ms-2">
            <i class="bi bi-plus-circle me-1"></i>Shared Mailbox anlegen
        </a>
        <span class="ms-auto" style="font-size:12px;color:#6b7280;">
            <i class="bi bi-clock me-1"></i>
            Alle 30 Min. aktualisiert &mdash;
            <a href="/mailboxes/shared?refresh=1">Jetzt aktualisieren</a>
        </span>
    </div>

    <div class="table-responsive">
        <table class="data-table" id="sharedTable">
            <thead>
                <tr>
                    <th>Anzeigename</th>
                    <th>E-Mail-Adresse</th>
                    <th>Erstellt am</th>
                    <th>Auto-Antwort</th>
                    <th>Weiterleitung</th>
                    <th class="text-end">Aktionen</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($mailboxes as $mb): ?>
                    <?php
                    $autoActive = in_array($mb['autoReplyStatus'], ['alwaysEnabled', 'scheduled'], true);
                    $created    = $mb['createdDateTime'] !== ''
                        ? date('d.m.Y', strtotime($mb['createdDateTime']))
                        : '—';
                    ?>
                    <tr>
                        <td class="fw-medium" style="font-size:13px;">
                            <?= $e($mb['displayName']) ?>
                        </td>
                        <td style="font-size:12px;color:#6b7280;">
                            <?= $e($mb['mail'] ?: $mb['userPrincipalName']) ?>
                        </td>
                        <td style="font-size:12px;">
                            <?= $e($created) ?>
                        </td>
                        <td>
                            <?php if ($autoActive): ?>
                                <span class="badge-warning badge-pill">Aktiv</span>
                            <?php else: ?>
                                <span class="badge-secondary badge-pill">Inaktiv</span>
                            <?php endif; ?>
                        </td>
                        <td style="font-size:12px;">
                            <?php if ($mb['forwardingAddress'] !== ''): ?>
                                <span class="badge-warning badge-pill" title="<?= $e($mb['forwardingAddress']) ?>">
                                    <i class="bi bi-forward-fill me-1"></i><?= $e($mb['forwardingAddress']) ?>
                                </span>
                            <?php else: ?>
                                <span class="text-muted">&mdash;</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">
                            <a href="/mailboxes/<?= $e($mb['id']) ?>"
                               class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-envelope-open me-1"></i>Postfach öffnen
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php endif; ?>

<script>
initTableSearch('sharedSearch', 'sharedTable');
</script>
