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
    Zeigt die <strong>Aktivierungsregeln je Verzeichnisrolle</strong> aus Privileged Identity
    Management (benötigt Entra ID P2). Sicherheitsleitlinie: privilegierte Rollen sollten bei der
    Aktivierung <strong>MFA</strong> und eine <strong>Begründung</strong> verlangen, kritische
    Rollen zusätzlich eine <strong>Genehmigung</strong>, und eine begrenzte maximale Aktivierungsdauer.
    Diese Ansicht ist <strong>read-only</strong> — Regeln werden im
    <a href="https://entra.microsoft.com/#view/Microsoft_Azure_PIMCommon/ResourceMenuBlade" target="_blank" rel="noopener">Entra-PIM-Portal</a> geändert.
  </div>
</div>

<div class="content-card">
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead>
        <tr>
          <th>Rolle</th>
          <th>MFA bei Aktivierung</th>
          <th>Begründung</th>
          <th>Genehmigung</th>
          <th>Max. Dauer</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($rows)): ?>
          <tr><td colspan="5" class="text-muted text-center py-4">
            Keine PIM-Richtlinien gelesen — Entra ID P2 erforderlich, Berechtigung
            <code>RoleManagementPolicy.Read.Directory</code> prüfen.
          </td></tr>
        <?php endif ?>
        <?php foreach ($rows as $r): ?>
          <?php $isPriv = in_array($r['role'], $privileged, true); ?>
          <tr class="<?= $isPriv ? 'table-warning' : '' ?>">
            <td class="fw-semibold">
              <?= $e($r['role']) ?>
              <?php if ($isPriv): ?><span class="badge bg-warning text-dark ms-1">privilegiert</span><?php endif ?>
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
