<?php
$e = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
?>

<?php if ($lastError): ?>
<div class="alert alert-warning">
  <i class="bi bi-exclamation-triangle-fill me-2"></i>
  Graph-Fehler: <?= $e($lastError['message'] ?? 'Unbekannt') ?>
  — Fehlende Berechtigung: <code>Policy.Read.All</code>
</div>
<?php endif ?>

<!-- Summary cards -->
<div class="row g-3 mb-4">
  <div class="col-sm-6 col-lg-3">
    <div class="card text-center shadow-sm">
      <div class="card-body">
        <div class="fs-2 fw-bold text-primary"><?= count($ipLocations) ?></div>
        <div class="small text-muted">IP-Standorte</div>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-lg-3">
    <div class="card text-center shadow-sm">
      <div class="card-body">
        <div class="fs-2 fw-bold text-info"><?= count($countryLocations) ?></div>
        <div class="small text-muted">Länder-Standorte</div>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-lg-3">
    <?php
    $trustedCount = count(array_filter($ipLocations, fn($l) => $l['isTrusted'] ?? false));
    ?>
    <div class="card text-center shadow-sm">
      <div class="card-body">
        <div class="fs-2 fw-bold text-success"><?= $trustedCount ?></div>
        <div class="small text-muted">Als vertrauenswürdig markiert</div>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-lg-3">
    <?php
    $totalRanges = array_sum(array_map(fn($l) => count($l['ipRanges'] ?? []), $ipLocations));
    ?>
    <div class="card text-center shadow-sm">
      <div class="card-body">
        <div class="fs-2 fw-bold"><?= $totalRanges ?></div>
        <div class="small text-muted">IP-Bereiche total</div>
      </div>
    </div>
  </div>
</div>

<div class="d-flex justify-content-end mb-3">
  <a href="?refresh=1" class="btn btn-outline-secondary btn-sm">
    <i class="bi bi-arrow-clockwise"></i> Neu laden
  </a>
</div>

<!-- IP-based Locations -->
<div class="card shadow-sm mb-4">
  <div class="card-header fw-semibold d-flex justify-content-between align-items-center">
    <span><i class="bi bi-hdd-network me-2"></i>IP-Standorte</span>
    <span class="badge bg-secondary"><?= count($ipLocations) ?></span>
  </div>
  <?php if (empty($ipLocations)): ?>
  <div class="card-body text-muted">Keine IP-Standorte konfiguriert.</div>
  <?php else: ?>
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0" id="tblIp">
      <thead class="table-light">
        <tr>
          <th>Name</th>
          <th>Vertrauenswürdig</th>
          <th>IP-Bereiche</th>
          <th>Erstellt</th>
          <th>Geändert</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($ipLocations as $loc): ?>
        <tr>
          <td class="fw-semibold"><?= $e($loc['displayName']) ?></td>
          <td>
            <?php if ($loc['isTrusted'] ?? false): ?>
              <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Ja</span>
            <?php else: ?>
              <span class="badge bg-secondary">Nein</span>
            <?php endif ?>
          </td>
          <td>
            <?php foreach (($loc['ipRanges'] ?? []) as $range): ?>
              <code class="me-1 small"><?= $e($range['cidrAddress'] ?? '') ?></code>
            <?php endforeach ?>
          </td>
          <td class="text-muted small"><?= $loc['createdDateTime'] ? date('d.m.Y', strtotime($loc['createdDateTime'])) : '–' ?></td>
          <td class="text-muted small"><?= $loc['modifiedDateTime'] ? date('d.m.Y', strtotime($loc['modifiedDateTime'])) : '–' ?></td>
        </tr>
        <?php endforeach ?>
      </tbody>
    </table>
  </div>
  <?php endif ?>
</div>

<!-- Country-based Locations -->
<div class="card shadow-sm mb-4">
  <div class="card-header fw-semibold d-flex justify-content-between align-items-center">
    <span><i class="bi bi-globe2 me-2"></i>Länder-Standorte</span>
    <span class="badge bg-secondary"><?= count($countryLocations) ?></span>
  </div>
  <?php if (empty($countryLocations)): ?>
  <div class="card-body text-muted">Keine Länder-Standorte konfiguriert.</div>
  <?php else: ?>
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0" id="tblCountry">
      <thead class="table-light">
        <tr>
          <th>Name</th>
          <th>Länder (ISO)</th>
          <th>Unbekannte Länder einschließen</th>
          <th>Erstellt</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($countryLocations as $loc): ?>
        <tr>
          <td class="fw-semibold"><?= $e($loc['displayName']) ?></td>
          <td>
            <?php foreach (($loc['countriesAndRegions'] ?? []) as $cc): ?>
              <span class="badge bg-light text-dark border me-1"><?= $e($cc) ?></span>
            <?php endforeach ?>
          </td>
          <td>
            <?= ($loc['includeUnknownCountriesAndRegions'] ?? false)
                ? '<span class="badge bg-warning text-dark">Ja</span>'
                : '<span class="badge bg-secondary">Nein</span>' ?>
          </td>
          <td class="text-muted small"><?= $loc['createdDateTime'] ? date('d.m.Y', strtotime($loc['createdDateTime'])) : '–' ?></td>
        </tr>
        <?php endforeach ?>
      </tbody>
    </table>
  </div>
  <?php endif ?>
</div>

<div class="alert alert-info small mt-2 mb-0">
  <i class="bi bi-info-circle-fill me-1"></i>
  Named Locations werden in Conditional-Access-Richtlinien als vertrauenswürdige oder gesperrte Standorte referenziert.
  Verwaltung im <a href="https://entra.microsoft.com/#view/Microsoft_AAD_ConditionalAccess/NamedLocations.ReactView" target="_blank" rel="noopener noreferrer">Microsoft Entra Admin Center</a>.
</div>
