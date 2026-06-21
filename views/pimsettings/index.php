<?php
$e = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
$yn = fn(bool $b) => $b
    ? '<span class="badge bg-success">' . te('ja') . '</span>'
    : '<span class="badge bg-danger-subtle text-danger-emphasis border border-danger-subtle">' . te('nein') . '</span>';
$fmt = [\App\Modules\PimSettings\PimSettingsService::class, 'formatDuration'];

// Roles where weak activation rules matter most (highly privileged).
$privileged = [
    'Global Administrator', 'Globaler Administrator',
    'Privileged Role Administrator', 'Privilegierter Rollenadministrator',
    'Security Administrator', 'Sicherheitsadministrator',
    'Exchange Administrator', 'Exchange-Administrator',
    'SharePoint Administrator', 'SharePoint-Administrator',
    'User Administrator', 'Benutzeradministrator',
    'Application Administrator', 'Anwendungsadministrator',
    'Conditional Access Administrator',
];
?>

<?php if (!empty($diag ?? null)) include BASE_PATH . '/views/partials/graph_diagnostic.php'; ?>

<div class="alert alert-info d-flex align-items-start gap-2">
  <i class="bi bi-info-circle-fill mt-1"></i>
  <div>
    <?= te('Zeigt die') ?> <strong><?= te('Aktivierungsregeln je Verzeichnisrolle') ?></strong> <?= te('aus Privileged Identity Management (benötigt Entra ID P2). Sicherheitsleitlinie: privilegierte Rollen sollten bei der Aktivierung') ?> <strong>MFA</strong> <?= te('und eine') ?> <strong><?= te('Begründung') ?></strong> <?= te('verlangen, kritische Rollen zusätzlich eine') ?> <strong><?= te('Genehmigung') ?></strong><?= te(', und eine begrenzte maximale Aktivierungsdauer.') ?>
    <?= te('Diese Ansicht ist') ?> <strong>read-only</strong> <?= te('— Regeln werden im') ?>
    <a href="https://entra.microsoft.com/#view/Microsoft_Azure_PIMCommon/ResourceMenuBlade" target="_blank" rel="noopener"><?= te('Entra-PIM-Portal') ?></a> <?= te('geändert.') ?>
  </div>
</div>

<div class="content-card">
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead>
        <tr>
          <th><?= te('Rolle') ?></th>
          <th><?= te('MFA bei Aktivierung') ?></th>
          <th><?= te('Begründung') ?></th>
          <th><?= te('Genehmigung') ?></th>
          <th><?= te('Max. Dauer') ?></th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($rows)): ?>
          <tr><td colspan="5" class="text-muted text-center py-4">
            <?= te('Keine PIM-Richtlinien gelesen — Entra ID P2 erforderlich, Berechtigung') ?>
            <code>RoleManagementPolicy.Read.Directory</code> <?= te('prüfen.') ?>
          </td></tr>
        <?php endif ?>
        <?php foreach ($rows as $r): ?>
          <?php $isPriv = in_array($r['role'], $privileged, true); ?>
          <tr class="<?= $isPriv ? 'table-warning' : '' ?>">
            <td class="fw-semibold">
              <?= $e($r['role']) ?>
              <?php if ($isPriv): ?><span class="badge bg-warning text-dark ms-1"><?= te('privilegiert') ?></span><?php endif ?>
            </td>
            <td><?= $yn($r['mfa']) ?></td>
            <td><?= $yn($r['justification']) ?></td>
            <td><?= $yn($r['approval']) ?></td>
            <td><?= $e($fmt($r['maxDuration'])) ?></td>
          </tr>
        <?php endforeach ?>
      </tbody>
    </table>
  </div>
</div>
