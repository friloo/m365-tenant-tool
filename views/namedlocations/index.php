<?php
$e = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');

// Common EU/DACH country codes with display names
$countryCatalog = [
    'DE' => 'Deutschland', 'AT' => 'Österreich', 'CH' => 'Schweiz',
    'FR' => 'Frankreich',  'NL' => 'Niederlande', 'BE' => 'Belgien',
    'LU' => 'Luxemburg',   'IT' => 'Italien',      'ES' => 'Spanien',
    'PT' => 'Portugal',    'PL' => 'Polen',         'CZ' => 'Tschechien',
    'SK' => 'Slowakei',    'HU' => 'Ungarn',        'RO' => 'Rumänien',
    'BG' => 'Bulgarien',   'HR' => 'Kroatien',      'SI' => 'Slowenien',
    'SE' => 'Schweden',    'DK' => 'Dänemark',      'FI' => 'Finnland',
    'NO' => 'Norwegen',    'IE' => 'Irland',         'EE' => 'Estland',
    'LV' => 'Lettland',    'LT' => 'Litauen',        'GR' => 'Griechenland',
    'CY' => 'Zypern',      'MT' => 'Malta',          'IS' => 'Island',
    'LI' => 'Liechtenstein','GB' => 'Großbritannien',
    'US' => 'USA',          'CA' => 'Kanada',         'AU' => 'Australien',
];
?>

<?php if ($flash ?? null): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
  <i class="bi bi-check-circle-fill me-2"></i><?= $e($flash) ?>
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif ?>
<?php if ($error ?? null): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
  <i class="bi bi-x-circle-fill me-2"></i><?= $e($error) ?>
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif ?>

<?php if (!empty($diag ?? null)) include BASE_PATH . '/views/partials/graph_diagnostic.php'; ?>

<!-- Summary -->
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
    <?php $trustedCount = count(array_filter($ipLocations, fn($l) => $l['isTrusted'] ?? false)); ?>
    <div class="card text-center shadow-sm">
      <div class="card-body">
        <div class="fs-2 fw-bold text-success"><?= $trustedCount ?></div>
        <div class="small text-muted">Als vertrauenswürdig markiert</div>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-lg-3">
    <?php $totalRanges = array_sum(array_map(fn($l) => count($l['ipRanges'] ?? []), $ipLocations)); ?>
    <div class="card text-center shadow-sm">
      <div class="card-body">
        <div class="fs-2 fw-bold"><?= $totalRanges ?></div>
        <div class="small text-muted">IP-Bereiche total</div>
      </div>
    </div>
  </div>
</div>

<div class="d-flex gap-2 justify-content-end mb-3">
  <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalCountry">
    <i class="bi bi-globe2 me-1"></i>Länder-Standort anlegen
  </button>
  <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalIp">
    <i class="bi bi-hdd-network me-1"></i>IP-Standort anlegen
  </button>
  <a href="?refresh=1" class="btn btn-outline-secondary btn-sm ms-2">
    <i class="bi bi-arrow-clockwise"></i> Neu laden
  </a>
</div>

<!-- ── Country Locations ──────────────────────────────────────── -->
<div class="card shadow-sm mb-4">
  <div class="card-header fw-semibold d-flex justify-content-between align-items-center">
    <span><i class="bi bi-globe2 me-2"></i>Länder-Standorte</span>
    <span class="badge bg-secondary"><?= count($countryLocations) ?></span>
  </div>
  <?php if (empty($countryLocations)): ?>
  <div class="card-body text-muted">
    Keine Länder-Standorte konfiguriert.
    <a href="#" data-bs-toggle="modal" data-bs-target="#modalCountry">Jetzt anlegen →</a>
  </div>
  <?php else: ?>
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead class="table-light">
        <tr>
          <th>Name</th>
          <th>Länder</th>
          <th>Unbekannte Länder</th>
          <th>Erstellt</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($countryLocations as $loc): ?>
        <tr>
          <td class="fw-semibold"><?= $e($loc['displayName']) ?></td>
          <td>
            <?php foreach (($loc['countriesAndRegions'] ?? []) as $cc): ?>
              <span class="badge bg-light text-dark border me-1"
                    title="<?= $e($countryCatalog[$cc] ?? $cc) ?>"><?= $e($cc) ?></span>
            <?php endforeach ?>
          </td>
          <td>
            <?= ($loc['includeUnknownCountriesAndRegions'] ?? false)
                ? '<span class="badge bg-warning text-dark">Ja</span>'
                : '<span class="badge bg-secondary">Nein</span>' ?>
          </td>
          <td class="text-muted small"><?= $loc['createdDateTime'] ? date('d.m.Y', strtotime($loc['createdDateTime'])) : '–' ?></td>
          <td class="text-end">
            <form method="POST" action="/namedlocations/<?= $e($loc['id']) ?>/delete"
                  onsubmit="return confirm('Standort «<?= $e(addslashes($loc['displayName'])) ?>» wirklich löschen?\nAlle CA-Richtlinien, die ihn referenzieren, müssen angepasst werden.')">
              <button class="btn btn-outline-danger btn-sm"><i class="bi bi-trash3"></i></button>
            </form>
          </td>
        </tr>
        <?php endforeach ?>
      </tbody>
    </table>
  </div>
  <?php endif ?>
</div>

<!-- ── IP Locations ───────────────────────────────────────────── -->
<div class="card shadow-sm mb-4">
  <div class="card-header fw-semibold d-flex justify-content-between align-items-center">
    <span><i class="bi bi-hdd-network me-2"></i>IP-Standorte</span>
    <span class="badge bg-secondary"><?= count($ipLocations) ?></span>
  </div>
  <?php if (empty($ipLocations)): ?>
  <div class="card-body text-muted">Keine IP-Standorte konfiguriert.</div>
  <?php else: ?>
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead class="table-light">
        <tr>
          <th>Name</th>
          <th>Vertrauenswürdig</th>
          <th>IP-Bereiche</th>
          <th>Erstellt</th>
          <th></th>
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
          <td class="text-end">
            <form method="POST" action="/namedlocations/<?= $e($loc['id']) ?>/delete"
                  onsubmit="return confirm('IP-Standort «<?= $e(addslashes($loc['displayName'])) ?>» wirklich löschen?')">
              <button class="btn btn-outline-danger btn-sm"><i class="bi bi-trash3"></i></button>
            </form>
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
  Named Locations werden in Conditional-Access-Richtlinien referenziert.
  Tipp: Erst einen Länder-Standort anlegen, dann auf der
  <a href="/conditionalaccess">Conditional Access Seite</a> eine Blockierungsrichtlinie erstellen.
</div>

<!-- ── Modal: Länder-Standort anlegen ────────────────────────── -->
<div class="modal fade" id="modalCountry" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form method="POST" action="/namedlocations/create-country">
        <div class="modal-header">
          <h5 class="modal-title"><i class="bi bi-globe2 me-2"></i>Länder-Standort anlegen</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label fw-semibold">Name <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control" required
                   placeholder="z.B. Erlaubte Länder (DACH)" maxlength="100">
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Länder auswählen <span class="text-danger">*</span></label>
            <div class="row g-2">
              <?php foreach ($countryCatalog as $code => $label): ?>
              <div class="col-6 col-md-4 col-lg-3">
                <div class="form-check">
                  <input class="form-check-input country-cb" type="checkbox"
                         name="country_check[]" value="<?= $e($code) ?>"
                         id="cc_<?= $e($code) ?>"
                         <?= in_array($code, ['DE', 'AT', 'CH']) ? 'checked' : '' ?>>
                  <label class="form-check-label small" for="cc_<?= $e($code) ?>">
                    <strong><?= $e($code) ?></strong> <?= $e($label) ?>
                  </label>
                </div>
              </div>
              <?php endforeach ?>
            </div>
            <div class="mt-2">
              <input type="hidden" name="countries" id="countriesHidden">
              <small class="text-muted">Weitere Codes (kommagetrennt): </small>
              <input type="text" id="extraCodes" class="form-control form-control-sm mt-1"
                     placeholder="z.B. JP, SG, US" style="width:220px">
            </div>
          </div>

          <div class="form-check">
            <input class="form-check-input" type="checkbox" name="include_unknown" id="incUnknown">
            <label class="form-check-label" for="incUnknown">
              Anmeldungen aus unbekannten Ländern einschließen
              <span class="text-muted small">(empfohlen: deaktiviert)</span>
            </label>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Abbrechen</button>
          <button type="submit" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i>Standort anlegen
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- ── Modal: IP-Standort anlegen ────────────────────────────── -->
<div class="modal fade" id="modalIp" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="/namedlocations/create-ip">
        <div class="modal-header">
          <h5 class="modal-title"><i class="bi bi-hdd-network me-2"></i>IP-Standort anlegen</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label fw-semibold">Name <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control" required
                   placeholder="z.B. Büro Frankfurt" maxlength="100">
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">IP-Bereiche (CIDR, ein Eintrag pro Zeile) <span class="text-danger">*</span></label>
            <textarea name="cidrs" class="form-control font-monospace" rows="5" required
                      placeholder="192.168.1.0/24&#10;10.0.0.0/8&#10;2001:db8::/32"></textarea>
            <div class="form-text">IPv4 und IPv6 CIDR-Notation werden unterstützt.</div>
          </div>
          <div class="form-check">
            <input class="form-check-input" type="checkbox" name="trusted" id="ipTrusted" checked>
            <label class="form-check-label" for="ipTrusted">
              Als vertrauenswürdig markieren
              <span class="text-muted small">(ermöglicht MFA-Ausnahmen in CA-Richtlinien)</span>
            </label>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Abbrechen</button>
          <button type="submit" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i>Standort anlegen
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
// Collect checkboxes + extra codes into the hidden "countries" field
(function () {
  const form = document.querySelector('form[action="/namedlocations/create-country"]');
  if (!form) return;
  form.addEventListener('submit', function () {
    const checked = [...document.querySelectorAll('.country-cb:checked')].map(cb => cb.value);
    const extra   = (document.getElementById('extraCodes').value || '')
                      .split(',').map(s => s.trim().toUpperCase()).filter(Boolean);
    const all     = [...new Set([...checked, ...extra])];
    document.getElementById('countriesHidden').value = all.join(',');
  });
})();
</script>
