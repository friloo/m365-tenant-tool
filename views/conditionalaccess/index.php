<?php
$e = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');

$stateLabel = [
    'enabled'                                => ['label' => 'Aktiv',        'class' => 'success'],
    'enabledForReportingButNotEnforced'      => ['label' => 'Report-only',  'class' => 'warning'],
    'disabled'                               => ['label' => 'Deaktiviert',  'class' => 'secondary'],
];
$gapIcon = ['ok' => 'check-circle-fill', 'warning' => 'exclamation-triangle-fill', 'missing' => 'x-circle-fill'];
$gapClass = ['ok' => 'success', 'warning' => 'warning', 'missing' => 'danger'];
?>

<?php if ($lastError): ?>
<div class="alert alert-warning mb-3">
  <i class="bi bi-exclamation-triangle-fill me-2"></i>
  <?= $e($lastError['message'] ?? 'Unbekannter Fehler') ?>
  — Fehlende Berechtigung: <code>Policy.Read.All</code>
</div>
<?php endif ?>

<!-- Summary -->
<div class="row g-3 mb-4">
  <div class="col-sm-4">
    <div class="card text-center shadow-sm border-success">
      <div class="card-body">
        <div class="fs-2 fw-bold text-success"><?= $summary['enabled'] ?></div>
        <div class="small text-muted">Aktive Richtlinien</div>
      </div>
    </div>
  </div>
  <div class="col-sm-4">
    <div class="card text-center shadow-sm border-warning">
      <div class="card-body">
        <div class="fs-2 fw-bold text-warning"><?= $summary['reportOnly'] ?></div>
        <div class="small text-muted">Report-only</div>
      </div>
    </div>
  </div>
  <div class="col-sm-4">
    <div class="card text-center shadow-sm">
      <div class="card-body">
        <div class="fs-2 fw-bold text-secondary"><?= $summary['disabled'] ?></div>
        <div class="small text-muted">Deaktiviert</div>
      </div>
    </div>
  </div>
</div>

<div class="d-flex justify-content-end mb-3">
  <a href="?refresh=1" class="btn btn-outline-secondary btn-sm">
    <i class="bi bi-arrow-clockwise"></i> Neu laden
  </a>
</div>

<!-- Security Gap Analysis -->
<?php if (!empty($gaps)): ?>
<div class="card shadow-sm mb-4">
  <div class="card-header fw-semibold"><i class="bi bi-shield-exclamation me-2"></i>Sicherheitsanalyse</div>
  <ul class="list-group list-group-flush">
    <?php foreach ($gaps as $gap): ?>
    <li class="list-group-item py-3">
      <div class="d-flex align-items-start gap-3">
        <div class="text-<?= $gapClass[$gap['type']] ?> fs-5 mt-1">
          <i class="bi bi-<?= $gapIcon[$gap['type']] ?>"></i>
        </div>
        <div>
          <div class="fw-semibold">
            <span class="badge bg-light text-dark border me-1 small"><?= $e($gap['category']) ?></span>
            <?= $e($gap['title']) ?>
          </div>
          <div class="text-muted small mt-1"><?= $e($gap['detail']) ?></div>
        </div>
      </div>
    </li>
    <?php endforeach ?>
  </ul>
</div>
<?php endif ?>

<!-- Policy List -->
<div class="card shadow-sm mb-4">
  <div class="card-header fw-semibold d-flex justify-content-between align-items-center">
    <span><i class="bi bi-shield-lock me-2"></i>Alle Richtlinien</span>
    <span class="badge bg-secondary"><?= count($policies) ?></span>
  </div>
  <?php if (empty($policies)): ?>
  <div class="card-body text-muted">Keine Conditional-Access-Richtlinien gefunden.</div>
  <?php else: ?>
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0" id="tblCa">
      <thead class="table-light">
        <tr>
          <th>Name</th>
          <th>Status</th>
          <th>Benutzer</th>
          <th>Apps</th>
          <th>Aktion</th>
          <th>Erstellt</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($policies as $p): ?>
        <?php
          $state = $p['state'] ?? 'disabled';
          $sc    = $stateLabel[$state] ?? ['label' => $state, 'class' => 'secondary'];
          $sum   = $p['_summary'];
        ?>
        <tr>
          <td class="fw-semibold"><?= $e($p['displayName'] ?? '–') ?></td>
          <td><span class="badge bg-<?= $sc['class'] ?>"><?= $e($sc['label']) ?></span></td>
          <td class="small"><?= $e($sum['users']) ?></td>
          <td class="small"><?= $e($sum['apps']) ?></td>
          <td class="small"><?= $e($sum['grant']) ?></td>
          <td class="text-muted small"><?= $p['createdDateTime'] ? date('d.m.Y', strtotime($p['createdDateTime'])) : '–' ?></td>
          <td>
            <button class="btn btn-outline-secondary btn-sm" type="button"
                    data-bs-toggle="collapse" data-bs-target="#ca-<?= $e($p['id']) ?>">
              <i class="bi bi-chevron-down"></i>
            </button>
          </td>
        </tr>
        <tr class="collapse" id="ca-<?= $e($p['id']) ?>">
          <td colspan="7" class="bg-light py-3 px-4">
            <div class="row g-3 small">
              <div class="col-md-6">
                <div class="fw-semibold mb-1">Bedingungen</div>
                <ul class="mb-0 ps-3">
                  <li><strong>Plattformen:</strong> <?= $e($sum['platforms']) ?></li>
                  <li><strong>Standorte:</strong> <?= $e($sum['locations']) ?></li>
                  <li><strong>Client-Typen:</strong> <?= $e($sum['clientTypes']) ?></li>
                </ul>
              </div>
              <div class="col-md-6">
                <div class="fw-semibold mb-1">Steuerelemente</div>
                <ul class="mb-0 ps-3">
                  <li><strong>Zugriff:</strong> <?= $e($sum['grant']) ?></li>
                  <li><strong>Sitzung:</strong> <?= $e($sum['session']) ?></li>
                </ul>
              </div>
              <?php if ($p['modifiedDateTime'] ?? null): ?>
              <div class="col-12 text-muted">
                Zuletzt geändert: <?= date('d.m.Y H:i', strtotime($p['modifiedDateTime'])) ?>
              </div>
              <?php endif ?>
            </div>
          </td>
        </tr>
        <?php endforeach ?>
      </tbody>
    </table>
  </div>
  <?php endif ?>
</div>

<div class="alert alert-info small mb-0">
  <i class="bi bi-info-circle-fill me-1"></i>
  Verwaltung im <a href="https://entra.microsoft.com/#view/Microsoft_AAD_ConditionalAccess/ConditionalAccessBlade/~/Overview" target="_blank" rel="noopener noreferrer">Microsoft Entra Admin Center → Conditional Access</a>.
  Für Änderungen wird <code>Policy.ReadWrite.ConditionalAccess</code> benötigt.
</div>
