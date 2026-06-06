<?php
$e = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
$stats = $teamStats;
?>
<?php \App\Core\View::partial('partials/module_tabs', ['tabs' => [['label'=>'Übersicht','href'=>'/teamspolicies','icon'=>'collection'],['label'=>'Nutzung','href'=>'/teamsusage','icon'=>'camera-video'],['label'=>'Governance','href'=>'/teamsgovernance','icon'=>'people-fill'],]]); ?>


<!-- Summary cards -->
<div class="row g-3 mb-4">
  <div class="col-sm-6 col-lg-3">
    <div class="card text-center shadow-sm">
      <div class="card-body">
        <div class="fs-2 fw-bold text-primary"><?= $stats['total'] ?></div>
        <div class="small text-muted">Teams gesamt</div>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-lg-3">
    <div class="card text-center shadow-sm">
      <div class="card-body">
        <div class="fs-2 fw-bold text-success"><?= $stats['private'] ?></div>
        <div class="small text-muted">Privat</div>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-lg-3">
    <div class="card text-center shadow-sm border-warning">
      <div class="card-body">
        <div class="fs-2 fw-bold text-warning"><?= $stats['public'] ?></div>
        <div class="small text-muted">Öffentlich</div>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-lg-3">
    <div class="card text-center shadow-sm">
      <div class="card-body">
        <div class="fs-2 fw-bold text-info"><?= $stats['dynamic'] ?></div>
        <div class="small text-muted">Dynamische Mitgliedschaft</div>
      </div>
    </div>
  </div>
</div>

<div class="d-flex justify-content-end mb-3">
  <a href="?refresh=1" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-clockwise"></i> Neu laden</a>
</div>

<!-- App Settings -->
<?php if (!empty($appSettings)): ?>
<div class="card shadow-sm mb-4">
  <div class="card-header fw-semibold"><i class="bi bi-grid-3x3-gap me-2"></i>Teams App-Einstellungen</div>
  <div class="card-body">
    <?php foreach ($appSettings as $key => $val): ?>
    <?php if ($key === '@odata.context' || $key === 'id') continue; ?>
    <div class="d-flex justify-content-between align-items-center border-bottom py-2">
      <span class="small fw-semibold"><?= $e(preg_replace('/([A-Z])/', ' $1', ucfirst($key))) ?></span>
      <span>
        <?php if (is_bool($val)): ?>
          <span class="badge bg-<?= $val ? 'success' : 'secondary' ?>"><?= $val ? 'Aktiv' : 'Deaktiviert' ?></span>
        <?php elseif (is_string($val) && $val !== ''): ?>
          <span class="badge bg-light text-dark border"><?= $e($val) ?></span>
        <?php else: ?>
          <span class="text-muted small">–</span>
        <?php endif ?>
      </span>
    </div>
    <?php endforeach ?>
  </div>
</div>
<?php endif ?>

<!-- Org Apps -->
<?php if (!empty($orgApps)): ?>
<div class="card shadow-sm mb-4">
  <div class="card-header fw-semibold d-flex justify-content-between">
    <span><i class="bi bi-app me-2"></i>Organisationseigene Apps</span>
    <span class="badge bg-secondary"><?= count($orgApps) ?></span>
  </div>
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead class="table-light">
        <tr><th>Name</th><th>Verteilungsmethode</th></tr>
      </thead>
      <tbody>
        <?php foreach ($orgApps as $app): ?>
        <tr>
          <td class="fw-semibold"><?= $e($app['displayName'] ?? '–') ?></td>
          <td><span class="badge bg-info"><?= $e($app['distributionMethod'] ?? '') ?></span></td>
        </tr>
        <?php endforeach ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif ?>

<!-- Teams list -->
<?php if (!empty($stats['teams'])): ?>
<div class="card shadow-sm mb-4">
  <div class="card-header fw-semibold d-flex justify-content-between">
    <span><i class="bi bi-camera-video me-2"></i>Teams im Tenant</span>
    <span class="badge bg-secondary"><?= $stats['total'] ?></span>
  </div>
  <?php if ($stats['public'] > 0): ?>
  <div class="alert alert-warning small m-3 mb-0 py-2">
    <i class="bi bi-exclamation-triangle-fill me-1"></i>
    <?= $stats['public'] ?> öffentliche Teams — diese sind für alle Benutzer im Tenant sichtbar und beitrittsfähig.
    <a href="https://admin.teams.microsoft.com/teams/manage" target="_blank" rel="noopener noreferrer" class="ms-1">Teams Admin Center</a>
  </div>
  <?php endif ?>
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0" id="tblTeams">
      <thead class="table-light">
        <tr><th>Name</th><th>Sichtbarkeit</th><th>Mitgliedschaft</th><th>Erstellt</th></tr>
      </thead>
      <tbody>
        <?php foreach ($stats['teams'] as $t): ?>
        <tr>
          <td class="fw-semibold"><?= $e($t['displayName'] ?? '–') ?></td>
          <td>
            <?php $vis = $t['visibility'] ?? '–'; ?>
            <span class="badge bg-<?= $vis === 'Public' ? 'warning text-dark' : 'success' ?>"><?= $e($vis) ?></span>
          </td>
          <td>
            <?= !empty($t['membershipRule'])
                ? '<span class="badge bg-info">Dynamisch</span>'
                : '<span class="badge bg-light text-dark border">Manuell</span>' ?>
          </td>
          <td class="text-muted small"><?= $t['createdDateTime'] ? date('d.m.Y', strtotime($t['createdDateTime'])) : '–' ?></td>
        </tr>
        <?php endforeach ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif ?>

<div class="alert alert-info small mb-0">
  <i class="bi bi-info-circle-fill me-1"></i>
  Erweiterte Teams-Richtlinien (Meeting-Richtlinien, Messaging-Richtlinien, Calling-Richtlinien) sind nur über
  <a href="https://admin.teams.microsoft.com" target="_blank" rel="noopener noreferrer">Teams Admin Center</a>
  oder PowerShell (MicrosoftTeams-Modul) verwaltbar — diese APIs sind nicht Teil von Microsoft Graph.
</div>
