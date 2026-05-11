<?php
$e = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
$fmtEur = fn($v) => $v !== null ? number_format((float)$v, 2, ',', '.') . ' €' : '–';
$priceMode = $priceMode ?? 'npo';
$priceLabel = $priceMode === 'standard' ? 'Listenpreis (Netto)' : 'NPO-Preis (Netto)';
?>

<!-- Price mode toggle -->
<div class="d-flex align-items-center gap-3 mb-4">
  <form method="get" class="d-flex align-items-center gap-2">
    <label class="small text-muted fw-semibold">Preismode:</label>
    <select name="price_mode" class="form-select form-select-sm" style="width:auto" onchange="this.form.submit()">
      <option value="npo"      <?= $priceMode === 'npo'      ? 'selected' : '' ?>>NPO-Preise (Netto)</option>
      <option value="standard" <?= $priceMode === 'standard' ? 'selected' : '' ?>>Listenpreise (Netto)</option>
    </select>
  </form>
</div>

<!-- Summary cards -->
<div class="row g-3 mb-4">
  <div class="col-sm-4">
    <div class="card shadow-sm border-primary text-center">
      <div class="card-body">
        <div class="fs-2 fw-bold text-primary"><?= $fmtEur($totalMonth) ?></div>
        <div class="small text-muted">Ges. Kosten / Monat</div>
      </div>
    </div>
  </div>
  <div class="col-sm-4">
    <div class="card shadow-sm border-secondary text-center">
      <div class="card-body">
        <div class="fs-2 fw-bold"><?= $fmtEur($totalAnnual) ?></div>
        <div class="small text-muted">Ges. Kosten / Jahr</div>
      </div>
    </div>
  </div>
  <div class="col-sm-4">
    <div class="card shadow-sm border-warning text-center">
      <div class="card-body">
        <div class="fs-2 fw-bold text-warning"><?= $fmtEur($wasteMonth) ?></div>
        <div class="small text-muted">Ungenutzte Lizenzen / Monat</div>
        <div class="text-muted" style="font-size:11px"><?= $fmtEur($wasteMonth * 12) ?> / Jahr</div>
      </div>
    </div>
  </div>
</div>

<!-- Cost table -->
<div class="card shadow-sm mb-4">
  <div class="card-header fw-semibold">
    <i class="bi bi-currency-euro me-2"></i>Lizenzkosten nach SKU
  </div>
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0" id="tblCosts">
      <thead class="table-light">
        <tr>
          <th>Produkt</th>
          <th>SKU</th>
          <th class="text-end">Belegt</th>
          <th class="text-end">Verfügbar</th>
          <th class="text-end">Auslastung</th>
          <th class="text-end"><?= $e($priceLabel) ?> / User</th>
          <th class="text-end">Kosten / Monat</th>
          <th class="text-end">Ungenutzt / Mon.</th>
          <th class="text-end">Kosten / Jahr</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($rows as $row): ?>
        <tr>
          <td class="fw-semibold"><?= $e($row['name']) ?></td>
          <td><span class="badge bg-light text-dark border small"><?= $e($row['partNumber']) ?></span></td>
          <td class="text-end"><?= $row['consumed'] ?></td>
          <td class="text-end <?= $row['available'] > 0 ? 'text-warning' : '' ?>">
            <?= $row['available'] ?>
          </td>
          <td class="text-end">
            <?php if ($row['total'] > 0): ?>
            <div class="d-flex align-items-center justify-content-end gap-2">
              <div class="progress flex-grow-1" style="height:6px;min-width:50px">
                <div class="progress-bar <?= $row['pct'] >= 90 ? 'bg-danger' : ($row['pct'] >= 70 ? 'bg-success' : 'bg-warning') ?>"
                     style="width:<?= $row['pct'] ?>%"></div>
              </div>
              <small><?= $row['pct'] ?>%</small>
            </div>
            <?php else: ?>–<?php endif ?>
          </td>
          <td class="text-end small"><?= $fmtEur($row['price']) ?></td>
          <td class="text-end fw-semibold"><?= $fmtEur($row['monthlyCost']) ?></td>
          <td class="text-end <?= ($row['wastedCost'] ?? 0) > 0 ? 'text-warning' : 'text-muted' ?>">
            <?= $fmtEur($row['wastedCost']) ?>
          </td>
          <td class="text-end"><?= $fmtEur($row['annualCost']) ?></td>
        </tr>
        <?php endforeach ?>
      </tbody>
      <tfoot class="table-light fw-semibold">
        <tr>
          <td colspan="6">Gesamt</td>
          <td class="text-end"><?= $fmtEur($totalMonth) ?></td>
          <td class="text-end text-warning"><?= $fmtEur($wasteMonth) ?></td>
          <td class="text-end"><?= $fmtEur($totalAnnual) ?></td>
        </tr>
      </tfoot>
    </table>
  </div>
</div>

<?php if ($wasteMonth > 5): ?>
<div class="alert alert-warning small mb-4">
  <i class="bi bi-exclamation-triangle-fill me-2"></i>
  <strong><?= $fmtEur($wasteMonth) ?>/Monat</strong> (<?= $fmtEur($wasteMonth * 12) ?>/Jahr) werden für ungenutzte Lizenzen ausgegeben.
  Im <a href="/licenseadvisor">Lizenz-Berater</a> siehst du, welche Benutzer keine Lizenz benötigen oder inaktiv sind.
</div>
<?php endif ?>

<div class="alert alert-info small mb-0">
  <i class="bi bi-info-circle-fill me-1"></i>
  Alle Preise <strong>netto</strong> (ohne 19 % MwSt.), DE-Listenpreis, Stand Mai 2025. Nur SKUs mit hinterlegtem Preis werden berechnet.
  Tatsächliche CSP/EA-Preise können abweichen.
</div>
