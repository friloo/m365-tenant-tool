<?php use App\Core\View; $e = fn($v) => View::escape($v); ?>

<?php
/**
 * Helper: format bytes into a human-readable string (KB / MB / GB / TB).
 */
$fmtBytes = function (int $bytes): string {
    if ($bytes <= 0) return '0 MB';
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $i = (int)floor(log($bytes, 1024));
    $i = min($i, count($units) - 1);
    return number_format($bytes / (1024 ** $i), $i >= 3 ? 2 : 1) . ' ' . $units[$i];
};
?>

<!-- Metric Cards -->
<div class="row g-3 mb-4">
    <div class="col-sm-3">
        <div class="metric-card">
            <div class="metric-label"><?= te('Postfächer gesamt') ?></div>
            <div class="metric-value"><?= number_format($stats['total']) ?></div>
            <div class="metric-sub"><?= te('aktive Postfächer') ?></div>
        </div>
    </div>
    <div class="col-sm-3">
        <div class="metric-card">
            <div class="metric-label"><?= te('Gesamter Speicher') ?></div>
            <div class="metric-value" style="font-size:1.6rem;"><?= $fmtBytes($stats['totalBytes']) ?></div>
            <div class="metric-sub"><?= te('Ø :size pro Postfach', ['size' => $fmtBytes($stats['avgBytes'])]) ?></div>
        </div>
    </div>
    <div class="col-sm-3">
        <div class="metric-card">
            <div class="metric-label"><?= te('Postfächer &gt; 50 GB') ?></div>
            <div class="metric-value" style="color:<?= $stats['over50GB'] > 0 ? '#d97706' : '#111827' ?>;">
                <?= number_format($stats['over50GB']) ?>
            </div>
            <div class="metric-sub"><?= te('nahe Quota-Limit') ?></div>
        </div>
    </div>
    <div class="col-sm-3">
        <div class="metric-card">
            <div class="metric-label"><?= te('Nie genutzt (&lt; 1 GB)') ?></div>
            <div class="metric-value"><?= number_format($stats['under1GB']) ?></div>
            <div class="metric-sub"><?= te('sehr kleiner Speicher') ?></div>
        </div>
    </div>
</div>

<?php if (empty($usage)): ?>
<!-- Empty / permission state -->
<div class="content-card">
    <div class="card-body-custom">
        <?php
        if (!empty($diag)) {
            $diagStyle = 'empty';
            $diagIcon  = 'envelope-x';
            $diagTitle = t('Keine Postfachdaten verfügbar');
            include BASE_PATH . '/views/partials/graph_diagnostic.php';
        } else { ?>
            <div class="empty-state">
                <i class="bi bi-envelope-x text-muted" style="font-size:2.5rem;"></i>
                <p class="mt-3 mb-1 fw-medium"><?= te('Keine Postfachdaten verfügbar') ?></p>
                <p class="text-muted small"><?= te('Es wurden keine Postfächer im Tenant gefunden.') ?></p>
            </div>
        <?php } ?>
    </div>
</div>
<?php else: ?>

<!-- Table card -->
<div class="content-card">
    <div class="table-toolbar">
        <input type="text" id="mbSearch" class="search-box" placeholder="<?= te('Postfach suchen…') ?>">
        <?php if (\App\Auth\LocalAuth::isAdmin()): ?>
        <button type="button" class="btn btn-sm btn-primary ms-2"
                data-bs-toggle="modal" data-bs-target="#createSharedMailboxModal">
            <i class="bi bi-plus-circle me-1"></i><?= te('Shared Mailbox anlegen') ?>
        </button>
        <?php endif; ?>
        <a href="/mailboxes/export" class="btn btn-sm btn-outline-secondary ms-auto">
            <i class="bi bi-download"></i> <?= te('CSV Export') ?>
        </a>
    </div>

    <div class="table-responsive">
        <table class="data-table" id="mbTable">
            <thead>
                <tr>
                    <th><?= te('Anzeigename') ?></th>
                    <th><?= te('UPN') ?></th>
                    <th class="text-end"><?= te('Größe') ?></th>
                    <th class="text-end"><?= te('Elemente') ?></th>
                    <th class="text-end"><?= te('Gel. Elemente') ?></th>
                    <th class="text-end"><?= te('Gel. Größe') ?></th>
                    <th><?= te('Weiterleitung') ?></th>
                    <th><?= te('Speicherauslastung') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Determine the maximum storage for relative progress bars
                $maxBytes = max(1, max(array_column($usage, 'storageUsedBytes')));
                ?>
                <?php foreach ($usage as $u): ?>
                    <?php
                    $pct = (int)min(100, round(($u['storageUsedBytes'] / $maxBytes) * 100));
                    $barColor = $u['storageUsedBytes'] >= 50 * (1024 ** 3)
                        ? '#d97706'
                        : ($u['storageUsedBytes'] >= 20 * (1024 ** 3) ? '#3b82f6' : '#16a34a');
                    $fwdAddr = $u['forwardingSmtpAddress'] ?? '';
                    ?>
                    <tr>
                        <td class="fw-medium" style="font-size:13px;">
                            <?php if (!empty($u['id'])): ?>
                                <a href="/mailboxes/<?= $e($u['id']) ?>" class="text-decoration-none text-dark">
                                    <?= $e($u['displayName']) ?>
                                </a>
                            <?php else: ?>
                                <?= $e($u['displayName']) ?>
                            <?php endif; ?>
                            <?php if ($u['isDeleted']): ?>
                                <span class="badge-neutral ms-1"><?= te('Gelöscht') ?></span>
                            <?php endif; ?>
                        </td>
                        <td style="font-size:12px;color:#6b7280;">
                            <?php if (!empty($u['id'])): ?>
                                <a href="/mailboxes/<?= $e($u['id']) ?>" class="text-decoration-none" style="color:#6b7280;">
                                    <?= $e($u['upn']) ?>
                                </a>
                            <?php else: ?>
                                <?= $e($u['upn']) ?>
                            <?php endif; ?>
                        </td>
                        <td class="text-end" style="font-size:13px;font-weight:500;">
                            <?= $fmtBytes($u['storageUsedBytes']) ?>
                        </td>
                        <td class="text-end" style="font-size:12px;">
                            <?= number_format($u['itemCount']) ?>
                        </td>
                        <td class="text-end" style="font-size:12px;color:#6b7280;">
                            <?= number_format($u['deletedItemCount']) ?>
                        </td>
                        <td class="text-end" style="font-size:12px;color:#9ca3af;">
                            <?= $fmtBytes($u['deletedItemSizeBytes']) ?>
                        </td>
                        <td style="font-size:12px;">
                            <?php if ($fwdAddr !== ''): ?>
                                <span class="badge-warning badge-pill" title="<?= $e($fwdAddr) ?>">
                                    <i class="bi bi-forward-fill me-1"></i><?= t('Weiterleitung aktiv &rarr; :addr', ['addr' => $e($fwdAddr)]) ?>
                                </span>
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td style="min-width:120px;">
                            <div class="progress-custom" style="margin-bottom:0;">
                                <div class="bar" style="width:<?= $pct ?>%;background:<?= $barColor ?>;"></div>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php endif; ?>

<!-- ── Modal: Shared Mailbox anlegen ──────────────────────────────────────── -->
<?php if (\App\Auth\LocalAuth::isAdmin()): ?>
<div class="modal fade" id="createSharedMailboxModal" tabindex="-1"
     aria-labelledby="createSharedMailboxModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="/mailboxes/create-shared" id="createSharedMailboxForm">
                <?= \App\Core\Csrf::field() ?>
                <div class="modal-header">
                    <h5 class="modal-title" id="createSharedMailboxModalLabel">
                        <i class="bi bi-envelope-plus me-2 text-primary"></i><?= te('Shared Mailbox anlegen') ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?= te('Schließen') ?>"></button>
                </div>
                <div class="modal-body">

                    <div class="mb-3">
                        <label for="smb_display_name" class="form-label form-label-sm fw-medium">
                            <?= te('Anzeigename') ?> <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control form-control-sm"
                               id="smb_display_name" name="display_name"
                               required placeholder="<?= te('z.B. Buchhaltung') ?>">
                    </div>

                    <div class="mb-3">
                        <label for="smb_alias" class="form-label form-label-sm fw-medium">
                            <?= te('Alias') ?> <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control form-control-sm"
                               id="smb_alias" name="alias"
                               required placeholder="<?= te('z.B. buchhaltung') ?>"
                               pattern="[a-z0-9\-]+"
                               title="<?= te('Nur Kleinbuchstaben, Ziffern und Bindestriche') ?>">
                    </div>

                    <div class="mb-3">
                        <label for="smb_domain" class="form-label form-label-sm fw-medium">
                            <?= te('Domain') ?> <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control form-control-sm"
                               id="smb_domain" name="domain"
                               required placeholder="firmaname.de">
                    </div>

                    <div class="mb-3">
                        <p class="text-muted small mb-0">
                            <i class="bi bi-arrow-right-circle me-1"></i>
                            <?= te('Ergebnis-Adresse:') ?>
                            <strong id="smb_preview" class="text-primary">—</strong>
                        </p>
                    </div>

                    <div class="alert alert-warning py-2 px-3 mb-0" style="font-size:12px;">
                        <i class="bi bi-info-circle me-1"></i>
                        <?= te('Das Konto wird ohne interaktiven Login-Zugriff angelegt') ?>
                        (<code>accountEnabled=false</code>). <?= te('Exchange Online stellt das Postfach innerhalb weniger Minuten bereit.') ?>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-outline-secondary"
                            data-bs-dismiss="modal"><?= te('Abbrechen') ?></button>
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="bi bi-plus-circle me-1"></i><?= te('Anlegen') ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
initTableSearch('mbSearch', 'mbTable');

(function () {
    var nameInput   = document.getElementById('smb_display_name');
    var aliasInput  = document.getElementById('smb_alias');
    var domainInput = document.getElementById('smb_domain');
    var preview     = document.getElementById('smb_preview');

    if (!nameInput) return;

    function updatePreview() {
        var alias  = aliasInput.value.trim();
        var domain = domainInput.value.trim();
        preview.textContent = (alias && domain) ? alias + '@' + domain : '—';
    }

    // Auto-fill alias from display name (lowercase, spaces → hyphens, strip special chars)
    nameInput.addEventListener('input', function () {
        var slug = nameInput.value
            .toLowerCase()
            .replace(/\s+/g, '-')
            .replace(/[^a-z0-9\-]/g, '');
        aliasInput.value = slug;
        updatePreview();
    });

    aliasInput.addEventListener('input', updatePreview);
    domainInput.addEventListener('input', updatePreview);

    // Reset form fields when modal is closed
    var modal = document.getElementById('createSharedMailboxModal');
    if (modal) {
        modal.addEventListener('hidden.bs.modal', function () {
            document.getElementById('createSharedMailboxForm').reset();
            if (preview) preview.textContent = '—';
        });
    }
})();
</script>
