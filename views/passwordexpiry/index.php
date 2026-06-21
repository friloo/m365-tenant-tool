<?php use App\Core\View; $e = fn($v) => View::escape($v); ?>

<?php
$expired  = $analyzed['expired']  ?? [];
$critical = $analyzed['critical'] ?? [];
$warning  = $analyzed['warning']  ?? [];
$ok       = $analyzed['ok']       ?? [];
$never    = $analyzed['never']    ?? [];
?>

<!-- Metric cards -->
<div class="row g-3 mb-4">
    <div class="col-sm-2" style="min-width:160px;">
        <div class="metric-card">
            <div class="metric-label"><?= te('Gesamt geprüft') ?></div>
            <div class="metric-value"><?= number_format($totalChecked) ?></div>
            <div class="metric-sub"><?= te('Aktive Benutzer') ?></div>
        </div>
    </div>
    <div class="col-sm-2" style="min-width:160px;">
        <div class="metric-card">
            <div class="metric-label"><?= te('Abgelaufen') ?></div>
            <div class="metric-value" style="color:<?= count($expired) > 0 ? '#dc2626' : '#16a34a' ?>;">
                <?= number_format(count($expired)) ?>
            </div>
            <div class="metric-sub"><?= te('Passwort überfällig') ?></div>
        </div>
    </div>
    <div class="col-sm-2" style="min-width:160px;">
        <div class="metric-card">
            <div class="metric-label"><?= te('Kritisch <14 Tage') ?></div>
            <div class="metric-value" style="color:<?= count($critical) > 0 ? '#d97706' : '#16a34a' ?>;">
                <?= number_format(count($critical)) ?>
            </div>
            <div class="metric-sub"><?= te('Läuft bald ab') ?></div>
        </div>
    </div>
    <div class="col-sm-2" style="min-width:160px;">
        <div class="metric-card">
            <div class="metric-label"><?= te('Warnung <30 Tage') ?></div>
            <div class="metric-value" style="color:<?= count($warning) > 0 ? '#ca8a04' : '#16a34a' ?>;">
                <?= number_format(count($warning)) ?>
            </div>
            <div class="metric-sub"><?= te('Bald ablaufend') ?></div>
        </div>
    </div>
    <div class="col-sm-2" style="min-width:160px;">
        <div class="metric-card">
            <div class="metric-label"><?= te('Läuft nie ab') ?></div>
            <div class="metric-value" style="color:#6b7280;"><?= number_format(count($never)) ?></div>
            <div class="metric-sub">DisablePasswordExpiration</div>
        </div>
    </div>
</div>

<!-- Alert banner if expired > 0 -->
<?php if (count($expired) > 0): ?>
    <div class="alert alert-danger mb-4">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        <strong><?= count($expired) !== 1
            ? te(':n Passwörter sind abgelaufen!', ['n' => count($expired)])
            : te(':n Passwort ist abgelaufen!', ['n' => count($expired)]) ?></strong>
        <?= te('Betroffene Benutzer sollten ihr Passwort sofort ändern.') ?>
    </div>
<?php endif; ?>

<!-- Tabs -->
<ul class="nav nav-tabs mb-3" id="pwdTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link <?= count($expired) > 0 ? 'active' : '' ?>"
                id="expired-tab" data-bs-toggle="tab" data-bs-target="#expired-panel"
                type="button" role="tab">
            <i class="bi bi-x-circle me-1"></i><?= te('Abgelaufen') ?>
            <?php if (count($expired) > 0): ?>
                <span class="badge bg-danger ms-1"><?= count($expired) ?></span>
            <?php endif; ?>
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link <?= count($expired) === 0 && count($critical) > 0 ? 'active' : '' ?>"
                id="critical-tab" data-bs-toggle="tab" data-bs-target="#critical-panel"
                type="button" role="tab">
            <i class="bi bi-exclamation-triangle me-1"></i><?= te('Kritisch') ?>
            <?php if (count($critical) > 0): ?>
                <span class="badge bg-warning text-dark ms-1"><?= count($critical) ?></span>
            <?php endif; ?>
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link <?= count($expired) === 0 && count($critical) === 0 && count($warning) > 0 ? 'active' : '' ?>"
                id="warning-tab" data-bs-toggle="tab" data-bs-target="#warning-panel"
                type="button" role="tab">
            <i class="bi bi-exclamation-circle me-1"></i><?= te('Warnung') ?>
            <?php if (count($warning) > 0): ?>
                <span class="badge bg-warning text-dark ms-1"><?= count($warning) ?></span>
            <?php endif; ?>
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link <?= count($expired) === 0 && count($critical) === 0 && count($warning) === 0 ? 'active' : '' ?>"
                id="all-tab" data-bs-toggle="tab" data-bs-target="#all-panel"
                type="button" role="tab">
            <i class="bi bi-people me-1"></i><?= te('Alle') ?>
            <span class="badge bg-secondary ms-1"><?= $totalChecked ?></span>
        </button>
    </li>
</ul>

<div class="tab-content">

    <!-- Tab: Abgelaufen -->
    <div class="tab-pane fade <?= count($expired) > 0 ? 'show active' : '' ?>" id="expired-panel" role="tabpanel">
        <div class="content-card">
            <div class="table-toolbar">
                <input type="text" id="expiredSearch" class="search-box" placeholder="<?= te('Benutzer suchen…') ?>">
            </div>
            <?= renderPwdTable($expired, 'expiredTable', $e) ?>
        </div>
    </div>

    <!-- Tab: Kritisch -->
    <div class="tab-pane fade <?= count($expired) === 0 && count($critical) > 0 ? 'show active' : '' ?>"
         id="critical-panel" role="tabpanel">
        <div class="content-card">
            <div class="table-toolbar">
                <input type="text" id="criticalSearch" class="search-box" placeholder="<?= te('Benutzer suchen…') ?>">
            </div>
            <?= renderPwdTable($critical, 'criticalTable', $e) ?>
        </div>
    </div>

    <!-- Tab: Warnung -->
    <div class="tab-pane fade <?= count($expired) === 0 && count($critical) === 0 && count($warning) > 0 ? 'show active' : '' ?>"
         id="warning-panel" role="tabpanel">
        <div class="content-card">
            <div class="table-toolbar">
                <input type="text" id="warningSearch" class="search-box" placeholder="<?= te('Benutzer suchen…') ?>">
            </div>
            <?= renderPwdTable($warning, 'warningTable', $e) ?>
        </div>
    </div>

    <!-- Tab: Alle -->
    <div class="tab-pane fade <?= count($expired) === 0 && count($critical) === 0 && count($warning) === 0 ? 'show active' : '' ?>"
         id="all-panel" role="tabpanel">
        <div class="content-card">
            <div class="table-toolbar">
                <input type="text" id="allSearch" class="search-box" placeholder="<?= te('Benutzer suchen…') ?>">
            </div>
            <?php
            $allUsers = array_merge(
                $expired,
                $critical,
                $warning,
                $ok
            );
            ?>
            <?= renderPwdTable($allUsers, 'allTable', $e) ?>
        </div>
    </div>

</div>

<!-- Info box -->
<div class="content-card mt-3" style="padding:12px 16px;background:#f8fafc;border:1px dashed #cbd5e1;">
    <p style="font-size:12px;color:#64748b;margin:0;">
        <i class="bi bi-info-circle me-1"></i>
        <strong><?= te('Hinweis:') ?></strong> <?= t('Passwörter mit <em>Läuft nie ab</em> sind in dieser Ansicht nicht aufgeführt (:n Benutzer betroffen). Das Ablauf-Intervall kann in den <a href="/settings">Einstellungen</a> konfiguriert werden (aktuell: :days Tage).', ['n' => number_format(count($never)), 'days' => (int)$expiryDays]) ?><br>
        <i class="bi bi-arrow-repeat me-1"></i><?= t('<strong>Hybrid-Benutzer</strong> (AD Connect synchronisiert) haben ihre Passwortrichtlinie im on-prem Active Directory. Der Ablauf wird hier auf Basis des konfigurierten Werts (:days Tage) geschätzt — die tatsächliche AD-Richtlinie kann abweichen.', ['days' => (int)$expiryDays]) ?>
    </p>
</div>

<?php
/**
 * Render the shared password-expiry table for a given set of users.
 *
 * @param  array    $users
 * @param  string   $tableId
 * @param  callable $e        View::escape closure
 * @return string
 */
function renderPwdTable(array $users, string $tableId, callable $e): string
{
    ob_start();
    ?>
    <div class="table-responsive">
        <table class="data-table" id="<?= htmlspecialchars($tableId, ENT_QUOTES, 'UTF-8') ?>">
            <thead>
                <tr>
                    <th style="width:40px;"></th>
                    <th><?= te('Name') ?></th>
                    <th><?= te('UPN') ?></th>
                    <th><?= te('Typ') ?></th>
                    <th><?= te('Geändert am') ?></th>
                    <th><?= te('Läuft ab am') ?></th>
                    <th><?= te('Verbleibend') ?></th>
                    <th><?= te('Status') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user):
                    $displayName     = $user['displayName'] ?? '';
                    $upn             = $user['userPrincipalName'] ?? '';
                    $initial         = strtoupper(mb_substr($displayName, 0, 1) ?: '?');
                    $lastChange      = $user['lastPasswordChangeDateTime'] ?? null;
                    $expiresAt       = $user['expiresAt'] ?? null;
                    $daysUntil       = $user['daysUntilExpiry'] ?? null;
                    $isHybrid        = $user['isHybrid'] ?? false;

                    if ($daysUntil === null) {
                        $badgeClass = 'badge-neutral';
                        $badgeLabel = t('Nie');
                    } elseif ($daysUntil < 0) {
                        $badgeClass = 'badge-danger';
                        $badgeLabel = t(':nd überfällig', ['n' => abs((int)$daysUntil)]);
                    } elseif ($daysUntil <= 14) {
                        $badgeClass = 'badge-warning';
                        $badgeLabel = t(':nd verbleibend', ['n' => (int)$daysUntil]);
                    } elseif ($daysUntil <= 30) {
                        $badgeClass = 'badge-warning';
                        $badgeLabel = t(':nd verbleibend', ['n' => (int)$daysUntil]);
                    } else {
                        $badgeClass = 'badge-success';
                        $badgeLabel = t(':nd verbleibend', ['n' => (int)$daysUntil]);
                    }

                    if ($daysUntil === null) {
                        $statusClass = 'badge-neutral';
                        $statusLabel = t('Läuft nie ab');
                    } elseif ($daysUntil < 0) {
                        $statusClass = 'badge-danger';
                        $statusLabel = t('Abgelaufen');
                    } elseif ($daysUntil <= 14) {
                        $statusClass = 'badge-warning';
                        $statusLabel = t('Kritisch');
                    } elseif ($daysUntil <= 30) {
                        $statusClass = 'badge-warning';
                        $statusLabel = t('Warnung');
                    } else {
                        $statusClass = 'badge-success';
                        $statusLabel = t('OK');
                    }
                ?>
                <tr>
                    <td>
                        <div style="width:32px;height:32px;border-radius:50%;background:#e3f0fb;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:600;color:#0078d4;flex-shrink:0;">
                            <?= $e($initial) ?>
                        </div>
                    </td>
                    <td>
                        <div style="font-size:13px;font-weight:500;"><?= $e($displayName) ?></div>
                    </td>
                    <td style="font-size:12px;color:#6b7280;"><?= $e($upn) ?></td>
                    <td style="font-size:12px;white-space:nowrap;">
                        <?php if ($isHybrid): ?>
                            <span class="badge-warning badge-pill" title="<?= te('Passwort wird durch on-prem AD verwaltet. Ablauf basiert auf konfiguriertem Max-Alter.') ?>">
                                <i class="bi bi-arrow-repeat me-1"></i><?= te('Hybrid') ?>
                            </span>
                        <?php else: ?>
                            <span class="badge-info badge-pill"><i class="bi bi-cloud me-1"></i><?= te('Cloud') ?></span>
                        <?php endif; ?>
                    </td>
                    <td style="font-size:12px;color:#6b7280;white-space:nowrap;">
                        <?= $lastChange ? htmlspecialchars(date('d.m.Y', strtotime($lastChange)), ENT_QUOTES, 'UTF-8') : '–' ?>
                    </td>
                    <td style="font-size:12px;white-space:nowrap;">
                        <?= $expiresAt ? htmlspecialchars(date('d.m.Y', strtotime($expiresAt)), ENT_QUOTES, 'UTF-8') : '–' ?>
                    </td>
                    <td>
                        <span class="<?= $badgeClass ?> badge-pill"><?= $e($badgeLabel) ?></span>
                    </td>
                    <td>
                        <span class="<?= $statusClass ?>"><?= $e($statusLabel) ?></span>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="8">
                            <div class="empty-state">
                                <i class="bi bi-check-circle"></i>
                                <p><?= te('Keine Benutzer in dieser Kategorie') ?></p>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php
    return ob_get_clean();
}
?>

<script>
initTableSearch('expiredSearch',  'expiredTable');
initTableSearch('criticalSearch', 'criticalTable');
initTableSearch('warningSearch',  'warningTable');
initTableSearch('allSearch',      'allTable');
</script>
