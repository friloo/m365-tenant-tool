<?php use App\Core\View; $e = fn($v) => View::escape($v); ?>

<?php if (!empty($flash)): ?>
    <div class="alert alert-success alert-dismissible mb-3"><i class="bi bi-check-circle me-2"></i><?= $e($flash) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>
<?php if (!empty($error)): ?>
    <div class="alert alert-danger alert-dismissible mb-3"><i class="bi bi-exclamation-triangle me-2"></i><?= $e($error) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<div class="row g-3 mb-4">
    <div class="col-sm-3">
        <div class="metric-card">
            <div class="metric-label"><?= te('Gäste gesamt') ?></div>
            <div class="metric-value"><?= $stats['total'] ?></div>
        </div>
    </div>
    <div class="col-sm-3">
        <div class="metric-card">
            <div class="metric-label"><?= te('Ausstehende Einladung') ?></div>
            <div class="metric-value" style="color:<?= $stats['pending'] > 0 ? '#f59e0b' : '#111827' ?>">
                <?= $stats['pending'] ?>
            </div>
        </div>
    </div>
    <div class="col-sm-3">
        <div class="metric-card">
            <div class="metric-label"><?= te('Inaktiv > 90 Tage') ?></div>
            <div class="metric-value" style="color:<?= $stats['inactive_90d'] > 0 ? '#dc2626' : '#111827' ?>">
                <?= $stats['inactive_90d'] ?>
            </div>
        </div>
    </div>
    <div class="col-sm-3">
        <div class="metric-card">
            <div class="metric-label"><?= te('Nie angemeldet') ?></div>
            <div class="metric-value" style="color:<?= $stats['never_signed_in'] > 0 ? '#dc2626' : '#111827' ?>">
                <?= $stats['never_signed_in'] ?>
            </div>
        </div>
    </div>
</div>

<?php if ($stats['inactive_90d'] > 0 || $stats['never_signed_in'] > 0): ?>
    <div class="alert alert-warning mb-4">
        <i class="bi bi-exclamation-triangle me-2"></i>
        <strong><?= te(':n inaktive Gastbenutzer', ['n' => $stats['inactive_90d'] + $stats['never_signed_in']]) ?></strong> —
        <?= te('Diese sollten überprüft und ggf. entfernt werden.') ?>
    </div>
<?php endif; ?>

<div class="content-card">
    <div class="table-toolbar">
        <input type="text" id="guestSearch" class="search-box" placeholder="<?= te('Gast suchen…') ?>">
        <select id="stateFilter" class="form-select form-select-sm ms-2" style="max-width:180px;" onchange="filterGuests()">
            <option value=""><?= te('Alle') ?></option>
            <option value="pending"><?= te('Ausstehend') ?></option>
            <option value="inactive"><?= te('Inaktiv > 90 Tage') ?></option>
            <option value="never"><?= te('Nie angemeldet') ?></option>
        </select>
        <a href="/guestusers/export" class="btn btn-sm btn-outline-secondary ms-auto">
            <i class="bi bi-download me-1"></i> CSV
        </a>
    </div>
    <div class="table-responsive">
        <table class="data-table" id="guestTable">
            <thead>
                <tr><th><?= te('Name') ?></th><th><?= te('E-Mail') ?></th><th><?= te('Einladung') ?></th><th><?= te('Letzter Login') ?></th><th><?= te('Status') ?></th><th></th></tr>
            </thead>
            <tbody>
                <?php
                $now = time();
                foreach ($guests as $g):
                    $lastSignIn = $g['signInActivity']['lastSignInDateTime'] ?? null;
                    $daysAgo    = $lastSignIn ? round(($now - strtotime($lastSignIn)) / 86400) : null;
                    $isPending  = ($g['externalUserState'] ?? '') === 'PendingAcceptance';
                    $isInactive = $lastSignIn && $daysAgo > 90;
                    $isNever    = !$lastSignIn;
                    $rowState   = $isPending ? 'pending' : ($isInactive ? 'inactive' : ($isNever ? 'never' : 'ok'));
                ?>
                <tr data-state="<?= $rowState ?>">
                    <td class="fw-medium"><?= $e($g['displayName'] ?? '') ?></td>
                    <td style="font-size:12px;color:#6b7280;"><?= $e($g['mail'] ?? $g['userPrincipalName'] ?? '') ?></td>
                    <td>
                        <?php if ($isPending): ?>
                            <span class="badge-warning"><?= te('Ausstehend') ?></span>
                        <?php else: ?>
                            <span class="badge-enabled"><?= te('Akzeptiert') ?></span>
                        <?php endif; ?>
                    </td>
                    <td style="font-size:12px;">
                        <?php if ($lastSignIn): ?>
                            <?= date('d.m.Y', strtotime($lastSignIn)) ?>
                            <?php if ($daysAgo > 90): ?>
                                <span class="badge-disabled ms-1"><?= $daysAgo ?>d</span>
                            <?php endif; ?>
                        <?php else: ?>
                            <span class="badge-warning"><?= te('Nie') ?></span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($g['accountEnabled'] ?? true): ?>
                            <span class="badge-enabled"><?= te('Aktiv') ?></span>
                        <?php else: ?>
                            <span class="badge-disabled"><?= te('Deaktiviert') ?></span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            <?php if ($g['accountEnabled'] ?? true): ?>
                                <form method="post" action="/guestusers/<?= $e($g['id']) ?>/disable"
                                      onsubmit="return confirm(<?= $e(json_encode(t('Gastbenutzer deaktivieren?'), JSON_UNESCAPED_UNICODE)) ?>)" class="mb-0">
                                    <?= \App\Core\Csrf::field() ?>
                                    <button type="submit" class="btn btn-xs btn-outline-warning py-0 px-2" style="font-size:11px;" title="<?= te('Deaktivieren') ?>">
                                        <i class="bi bi-person-x"></i>
                                    </button>
                                </form>
                            <?php endif; ?>
                            <form method="post" action="/guestusers/<?= $e($g['id']) ?>/remove"
                                  onsubmit="return confirm(<?= $e(json_encode(t('Gastbenutzer wirklich löschen? Diese Aktion kann nicht rückgängig gemacht werden.'), JSON_UNESCAPED_UNICODE)) ?>)" class="mb-0">
                                <?= \App\Core\Csrf::field() ?>
                                <button type="submit" class="btn btn-xs btn-outline-danger py-0 px-2" style="font-size:11px;" title="<?= te('Löschen') ?>">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($guests)): ?>
                    <tr><td colspan="6" class="text-center text-muted py-4"><?= te('Keine Gastbenutzer gefunden') ?></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
initTableSearch('guestSearch', 'guestTable');
function filterGuests() {
    const val = document.getElementById('stateFilter').value;
    document.querySelectorAll('#guestTable tbody tr').forEach(r => {
        r.style.display = (!val || r.dataset.state === val) ? '' : 'none';
    });
}
</script>
