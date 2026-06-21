<?php use App\Core\View; use App\Auth\LocalAuth; $e = fn($v) => View::escape($v); ?>

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

<!-- Metric cards -->
<div class="row g-3 mb-4">
    <div class="col-sm-3">
        <div class="metric-card">
            <div class="metric-label"><?= te('Admin-Rollen aktiv') ?></div>
            <div class="metric-value"><?= count($byRole) ?></div>
            <div class="metric-sub"><?= te('Rollen mit Zuweisung') ?></div>
        </div>
    </div>
    <div class="col-sm-3">
        <div class="metric-card">
            <div class="metric-label"><?= te('Benutzer mit Adminrechten') ?></div>
            <div class="metric-value"><?= $totalAdmins ?></div>
            <div class="metric-sub"><?= te('Eindeutige Principals') ?></div>
        </div>
    </div>
    <div class="col-sm-3">
        <div class="metric-card">
            <div class="metric-label">Global Admins</div>
            <div class="metric-value" style="color:<?= $globalAdminCount > 3 ? '#dc2626' : ($globalAdminCount > 0 ? '#f59e0b' : '#111827') ?>;">
                <?= $globalAdminCount ?>
            </div>
            <div class="metric-sub"><?= $globalAdminCount > 3 ? te('Zu viele — Sicherheitsrisiko') : te('Accounts') ?></div>
        </div>
    </div>
    <div class="col-sm-3">
        <div class="metric-card" style="background:#fff7ed;border-color:#fed7aa;">
            <div class="metric-label" style="color:#9a3412;"><i class="bi bi-info-circle me-1"></i><?= te('Hinweis') ?></div>
            <div style="font-size:12px;color:#7c2d12;line-height:1.5;margin-top:6px;">
                <?= te('Zu viele Global-Admins erhöhen das Angriffspotenzial.') ?><br>
                <strong><?= te('Empfehlung: max. 2–4 Accounts.') ?></strong>
            </div>
        </div>
    </div>
</div>

<?php if (empty($byRole) && empty($definitions)): ?>
    <!-- Permission error empty state -->
    <div class="content-card mb-4">
        <div class="card-body-custom">
            <?php
            if (!empty($diag ?? null)) {
                $diagStyle = 'empty';
                $diagIcon  = 'shield-lock';
                $diagTitle = t('Keine Daten verfügbar');
                include BASE_PATH . '/views/partials/graph_diagnostic.php';
            } else { ?>
                <div class="empty-state">
                    <i class="bi bi-shield-lock" style="font-size:2.5rem;color:#d1d5db;"></i>
                    <div class="mt-3 fw-semibold"><?= te('Keine Admin-Rollen zugewiesen') ?></div>
                    <div class="text-muted small mt-1"><?= te('Der Tenant hat aktuell keine Direkt-Zuweisungen — eventuell wird PIM genutzt.') ?></div>
                </div>
            <?php } ?>
        </div>
    </div>
<?php else: ?>

<!-- Assign role form (admins only) -->
<?php if (LocalAuth::isAdmin()): ?>
<div class="content-card mb-4">
    <div class="card-header-custom">
        <i class="bi bi-person-plus-fill text-primary"></i>
        <h6><?= te('Rolle zuweisen') ?></h6>
    </div>
    <div class="card-body-custom">
        <form method="post" action="/adminroles/assign" class="row g-2 align-items-end">
            <?= \App\Core\Csrf::field() ?>
            <div class="col-md-4">
                <label class="form-label" style="font-size:12px;font-weight:600;color:#374151;"><?= te('Benutzer-ID (UUID)') ?></label>
                <input type="text"
                       name="user_id"
                       class="form-control form-control-sm"
                       placeholder="<?= te('Benutzer-ID oder UPN eingeben') ?>"
                       required>
            </div>
            <div class="col-md-5">
                <label class="form-label" style="font-size:12px;font-weight:600;color:#374151;"><?= te('Rolle') ?></label>
                <select name="role_definition_id" class="form-select form-select-sm" required>
                    <option value=""><?= te('— Rolle auswählen —') ?></option>
                    <?php foreach ($definitions as $def): ?>
                        <option value="<?= $e($def['id']) ?>"><?= $e($def['displayName']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-sm btn-primary w-100">
                    <i class="bi bi-person-plus me-1"></i> <?= te('Rolle zuweisen') ?>
                </button>
            </div>
        </form>
        <div class="mt-2" style="font-size:11px;color:#9ca3af;">
            <i class="bi bi-info-circle me-1"></i>
            <?= te('Benutzer-ID aus dem Benutzer-Modul kopieren (uuid-Format, z.B.') ?> <code>xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx</code>)
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Role cards -->
<?php foreach ($byRole as $role):
    $roleName     = $role['displayName'];
    $members      = $role['members'];
    $memberCount  = count($members);
    $isCritical   = in_array($roleName, ['Global Administrator', 'Privileged Role Administrator'], true);
    $isWarning    = in_array($roleName, $warningRoles, true);
    $description  = $role['description'] ?? '';
    $descShort    = mb_strlen($description) > 120 ? mb_substr($description, 0, 117) . '…' : $description;
    $tableId      = 'roleTable_' . preg_replace('/[^a-z0-9]/i', '_', $roleName);
    $searchId     = 'roleSearch_' . preg_replace('/[^a-z0-9]/i', '_', $roleName);
?>
<div class="content-card mb-3">
    <div class="card-header-custom">
        <i class="bi bi-person-badge<?= $isCritical ? '-fill' : '' ?> text-<?= $isCritical ? 'danger' : ($isWarning ? 'warning' : 'secondary') ?>"></i>
        <h6 class="mb-0">
            <strong><?= $e($roleName) ?></strong>
        </h6>
        <span class="badge-pill badge-info ms-2"><?= $memberCount === 1 ? te(':n Mitglied', ['n' => $memberCount]) : te(':n Mitglieder', ['n' => $memberCount]) ?></span>
        <?php if ($isCritical): ?>
            <span class="badge-pill badge-danger ms-1"><?= te('Kritisch') ?></span>
        <?php elseif ($isWarning): ?>
            <span class="badge-pill badge-warning ms-1"><?= te('Privilegiert') ?></span>
        <?php endif; ?>
    </div>
    <div class="card-body-custom" style="padding-bottom:0;">
        <?php if ($descShort !== ''): ?>
            <p style="font-size:12px;color:#6b7280;margin-bottom:10px;"><?= $e($descShort) ?></p>
        <?php endif; ?>

        <?php if ($memberCount > 5): ?>
        <div class="table-toolbar" style="padding:0 0 10px 0;">
            <input type="text"
                   id="<?= $e($searchId) ?>"
                   class="search-box"
                   placeholder="<?= te('Mitglied suchen…') ?>">
        </div>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="data-table" id="<?= $e($tableId) ?>">
                <thead>
                    <tr>
                        <th style="width:36px;"></th>
                        <th><?= te('Name') ?></th>
                        <th>UPN</th>
                        <th><?= te('Status') ?></th>
                        <?php if (LocalAuth::isAdmin()): ?>
                            <th style="width:100px;"></th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($members as $member):
                        $displayName = $member['displayName'] ?? '';
                        $upn         = $member['userPrincipalName'] ?? '';
                        $enabled     = $member['accountEnabled'] ?? null;
                        $isSp        = $member['isServicePrincipal'] ?? false;
                        $initial     = strtoupper(mb_substr($displayName ?: '?', 0, 1));
                        $assignId    = $member['assignmentId'] ?? '';
                        $avatarBg    = $isSp ? '#f3e8ff' : '#e3f0fb';
                        $avatarColor = $isSp ? '#7c3aed' : '#0078d4';
                    ?>
                    <tr>
                        <td>
                            <div style="width:32px;height:32px;border-radius:50%;background:<?= $avatarBg ?>;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:600;color:<?= $avatarColor ?>;flex-shrink:0;" title="<?= $isSp ? te('Service Principal') : te('Benutzer') ?>">
                                <?= $e($initial) ?>
                            </div>
                        </td>
                        <td style="font-weight:500;font-size:13px;"><?= $e($displayName) ?></td>
                        <td style="color:#6b7280;font-size:12px;">
                            <?php if ($isSp): ?>
                                <span style="font-size:11px;color:#7c3aed;background:#f3e8ff;padding:1px 6px;border-radius:4px;"><?= te('Service-Konto') ?></span>
                            <?php else: ?>
                                <?= $e($upn) ?>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($isSp): ?>
                                <span class="badge-pill" style="background:#f3e8ff;color:#7c3aed;font-size:11px;">App</span>
                            <?php elseif ($enabled === false): ?>
                                <span class="badge-disabled"><?= te('Deaktiviert') ?></span>
                            <?php else: ?>
                                <span class="badge-enabled"><?= te('Aktiv') ?></span>
                            <?php endif; ?>
                        </td>
                        <?php if (LocalAuth::isAdmin()): ?>
                        <td class="text-end">
                            <?php if ($assignId !== ''): ?>
                            <form method="post"
                                  action="/adminroles/<?= $e($assignId) ?>/remove"
                                  class="mb-0"
                                  onsubmit="return confirm('<?= $e(t('Rollenzuweisung für')) ?> <?= $e(addslashes($displayName)) ?> <?= $e(t('wirklich entfernen?')) ?>');">
                                <?= \App\Core\Csrf::field() ?>
                                <button type="submit"
                                        class="btn btn-sm btn-outline-danger py-0 px-2"
                                        style="font-size:11px;"
                                        title="<?= te('Zuweisung entfernen') ?>">
                                    <i class="bi bi-person-dash me-1"></i><?= te('Entfernen') ?>
                                </button>
                            </form>
                            <?php endif; ?>
                        </td>
                        <?php endif; ?>
                    </tr>
                    <?php endforeach; ?>
                    <?php if ($memberCount === 0): ?>
                    <tr>
                        <td colspan="<?= LocalAuth::isAdmin() ? 5 : 4 ?>" class="text-center text-muted py-2" style="font-size:13px;">
                            Keine Mitglieder
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endforeach; ?>

<?php if (empty($byRole)): ?>
<div class="content-card">
    <div class="card-body-custom">
        <div class="empty-state">
            <i class="bi bi-people" style="font-size:2.5rem;color:#d1d5db;"></i>
            <div class="mt-3 fw-semibold">Keine aktiven Rollenzuweisungen gefunden</div>
            <div class="text-muted small mt-1">
                Entweder sind keine Rollen zugewiesen oder die Berechtigungen
                (<code>RoleManagement.Read.All</code>) fehlen.
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php endif; ?>

<script>
<?php foreach ($byRole as $role):
    $memberCount = count($role['members']);
    if ($memberCount <= 5) continue;
    $roleName = $role['displayName'];
    $tableId  = 'roleTable_' . preg_replace('/[^a-z0-9]/i', '_', $roleName);
    $searchId = 'roleSearch_' . preg_replace('/[^a-z0-9]/i', '_', $roleName);
?>
initTableSearch('<?= $e($searchId) ?>', '<?= $e($tableId) ?>');
<?php endforeach; ?>
</script>
