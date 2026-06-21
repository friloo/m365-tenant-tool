<?php
$e = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');

$stateLabel = [
    'enabled'                           => ['label' => t('Aktiv'),       'class' => 'success'],
    'enabledForReportingButNotEnforced' => ['label' => t('Report-only'), 'class' => 'warning'],
    'disabled'                          => ['label' => t('Deaktiviert'), 'class' => 'secondary'],
];
$gapIcon  = ['ok' => 'check-circle-fill',         'warning' => 'exclamation-triangle-fill', 'missing' => 'x-circle-fill'];
$gapClass = ['ok' => 'success',                    'warning' => 'warning',                   'missing' => 'danger'];
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
  <div class="col-sm-4">
    <div class="card text-center shadow-sm border-success">
      <div class="card-body">
        <div class="fs-2 fw-bold text-success"><?= $summary['enabled'] ?></div>
        <div class="small text-muted"><?= te('Aktive Richtlinien') ?></div>
      </div>
    </div>
  </div>
  <div class="col-sm-4">
    <div class="card text-center shadow-sm border-warning">
      <div class="card-body">
        <div class="fs-2 fw-bold text-warning"><?= $summary['reportOnly'] ?></div>
        <div class="small text-muted"><?= te('Report-only') ?></div>
      </div>
    </div>
  </div>
  <div class="col-sm-4">
    <div class="card text-center shadow-sm">
      <div class="card-body">
        <div class="fs-2 fw-bold text-secondary"><?= $summary['disabled'] ?></div>
        <div class="small text-muted"><?= te('Deaktiviert') ?></div>
      </div>
    </div>
  </div>
</div>

<div class="d-flex gap-2 justify-content-end mb-3">
  <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalCreate">
    <i class="bi bi-plus-circle me-1"></i><?= te('Neue Richtlinie') ?>
  </button>
  <a href="?refresh=1" class="btn btn-outline-secondary btn-sm">
    <i class="bi bi-arrow-clockwise"></i> <?= te('Neu laden') ?>
  </a>
</div>

<!-- Security Gap Analysis -->
<?php if (!empty($gaps)): ?>
<div class="card shadow-sm mb-4">
  <div class="card-header fw-semibold"><i class="bi bi-shield-exclamation me-2"></i><?= te('Sicherheitsanalyse') ?></div>
  <ul class="list-group list-group-flush">
    <?php foreach ($gaps as $gap): ?>
    <li class="list-group-item py-3">
      <div class="d-flex align-items-start gap-3">
        <div class="text-<?= $gapClass[$gap['type']] ?> fs-5 mt-1">
          <i class="bi bi-<?= $gapIcon[$gap['type']] ?>"></i>
        </div>
        <div class="flex-grow-1">
          <div class="fw-semibold">
            <span class="badge bg-light text-dark border me-1 small"><?= $e($gap['category']) ?></span>
            <?= $e($gap['title']) ?>
          </div>
          <div class="text-muted small mt-1"><?= $e($gap['detail']) ?></div>
        </div>
        <?php if ($gap['type'] === 'missing'): ?>
        <button class="btn btn-outline-primary btn-sm flex-shrink-0"
                data-bs-toggle="modal" data-bs-target="#modalCreate"
                data-template="<?= match(true) {
                    str_contains($gap['title'], 'MFA für alle')  => 'mfa_all',
                    str_contains($gap['title'], 'Legacy')         => 'block_legacy',
                    default                                        => 'mfa_all',
                } ?>">
          <i class="bi bi-plus-circle me-1"></i><?= te('Anlegen') ?>
        </button>
        <?php endif ?>
      </div>
    </li>
    <?php endforeach ?>
  </ul>
</div>
<?php endif ?>

<!-- Policy List -->
<div class="card shadow-sm mb-4">
  <div class="card-header fw-semibold d-flex justify-content-between align-items-center">
    <span><i class="bi bi-shield-lock me-2"></i><?= te('Alle Richtlinien') ?></span>
    <span class="badge bg-secondary"><?= count($policies) ?></span>
  </div>
  <?php if (empty($policies)): ?>
  <div class="card-body text-muted">
    <?= te('Keine Conditional-Access-Richtlinien gefunden.') ?>
    <a href="#" data-bs-toggle="modal" data-bs-target="#modalCreate"><?= te('Jetzt anlegen →') ?></a>
  </div>
  <?php else: ?>
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0" id="tblCa">
      <thead class="table-light">
        <tr>
          <th><?= te('Name') ?></th>
          <th><?= te('Status') ?></th>
          <th><?= te('Benutzer') ?></th>
          <th><?= te('Apps') ?></th>
          <th><?= te('Aktion') ?></th>
          <th><?= te('Erstellt') ?></th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($policies as $p): ?>
        <?php
          $state = $p['state'] ?? 'disabled';
          $sc    = $stateLabel[$state] ?? ['label' => $state, 'class' => 'secondary'];
          $sum   = $p['_summary'];
          $pid   = $e($p['id']);
        ?>
        <tr>
          <td class="fw-semibold"><?= $e($p['displayName'] ?? '–') ?></td>
          <td>
            <div class="dropdown">
              <button class="btn btn-<?= $sc['class'] ?> btn-sm dropdown-toggle" data-bs-toggle="dropdown">
                <?= $e($sc['label']) ?>
              </button>
              <ul class="dropdown-menu dropdown-menu-sm">
                <?php
                $stateOptions = [
                    'enabled'                           => t('Aktivieren'),
                    'enabledForReportingButNotEnforced' => t('Report-only'),
                    'disabled'                          => t('Deaktivieren'),
                ];
                foreach ($stateOptions as $val => $label): ?>
                <li>
                  <form method="POST" action="/conditionalaccess/<?= $pid ?>/toggle">
                      <?= \App\Core\Csrf::field() ?>
                    <input type="hidden" name="state" value="<?= $e($val) ?>">
                    <button class="dropdown-item <?= $val === $state ? 'active' : '' ?>" type="submit"
                            <?= $val === 'enabled' ? 'onclick="return confirm(' . $e(json_encode(t('Richtlinie jetzt aktivieren? Teste sie zuerst im Report-Modus.'), JSON_UNESCAPED_UNICODE)) . ')"' : '' ?>>
                      <?= $e($label) ?>
                    </button>
                  </form>
                </li>
                <?php endforeach ?>
              </ul>
            </div>
          </td>
          <td class="small"><?= $e($sum['users']) ?></td>
          <td class="small"><?= $e($sum['apps']) ?></td>
          <td class="small"><?= $e($sum['grant']) ?></td>
          <td class="text-muted small"><?= $p['createdDateTime'] ? date('d.m.Y', strtotime($p['createdDateTime'])) : '–' ?></td>
          <td class="text-end">
            <div class="d-flex gap-1 justify-content-end">
              <button class="btn btn-outline-secondary btn-sm" type="button"
                      data-bs-toggle="collapse" data-bs-target="#ca-<?= $pid ?>" title="<?= te('Details') ?>">
                <i class="bi bi-chevron-down"></i>
              </button>
              <form method="POST" action="/conditionalaccess/<?= $pid ?>/delete"
                    onsubmit="return confirm('<?= $e(t('Richtlinie «')) ?><?= $e(addslashes($p['displayName'] ?? '')) ?><?= $e(t('» wirklich löschen?\nDieser Vorgang kann nicht rückgängig gemacht werden.')) ?>')">
                  <?= \App\Core\Csrf::field() ?>
                <button class="btn btn-outline-danger btn-sm" title="<?= te('Löschen') ?>">
                  <i class="bi bi-trash3"></i>
                </button>
              </form>
            </div>
          </td>
        </tr>
        <tr class="collapse" id="ca-<?= $pid ?>">
          <td colspan="7" class="bg-light py-3 px-4">
            <div class="row g-3 small">
              <div class="col-md-6">
                <div class="fw-semibold mb-1"><?= te('Bedingungen') ?></div>
                <ul class="mb-0 ps-3">
                  <li><strong><?= te('Plattformen:') ?></strong> <?= $e($sum['platforms']) ?></li>
                  <li><strong><?= te('Standorte:') ?></strong> <?= $e($sum['locations']) ?></li>
                  <li><strong><?= te('Client-Typen:') ?></strong> <?= $e($sum['clientTypes']) ?></li>
                </ul>
              </div>
              <div class="col-md-6">
                <div class="fw-semibold mb-1"><?= te('Steuerelemente') ?></div>
                <ul class="mb-0 ps-3">
                  <li><strong><?= te('Zugriff:') ?></strong> <?= $e($sum['grant']) ?></li>
                  <li><strong><?= te('Sitzung:') ?></strong> <?= $e($sum['session']) ?></li>
                </ul>
              </div>
              <?php if ($p['modifiedDateTime'] ?? null): ?>
              <div class="col-12 text-muted">
                <?= te('Zuletzt geändert:') ?> <?= date('d.m.Y H:i', strtotime($p['modifiedDateTime'])) ?>
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
  <?= te('Verwaltung auch im') ?> <a href="https://entra.microsoft.com/#view/Microsoft_AAD_ConditionalAccess/ConditionalAccessBlade/~/Overview" target="_blank" rel="noopener noreferrer"><?= te('Microsoft Entra Admin Center → Conditional Access') ?></a>.
  <?= te('Neue Richtlinien werden immer im') ?> <strong><?= te('Report-only Modus') ?></strong> <?= te('angelegt — erst testen, dann aktivieren.') ?>
</div>

<!-- ── Modal: Neue CA-Richtlinie ─────────────────────────────── -->
<div class="modal fade" id="modalCreate" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form method="POST" action="/conditionalaccess/create" id="formCreateCa">
          <?= \App\Core\Csrf::field() ?>
        <div class="modal-header">
          <h5 class="modal-title"><i class="bi bi-shield-plus me-2"></i><?= te('Neue Richtlinie anlegen') ?></h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">

          <!-- Template picker -->
          <div class="mb-4">
            <label class="form-label fw-semibold"><?= te('Vorlage') ?></label>
            <div class="row g-2">
              <div class="col-md-4">
                <input type="radio" class="btn-check" name="template" id="tplCountry" value="country_block">
                <label class="btn btn-outline-danger w-100 h-100 text-start p-3" for="tplCountry">
                  <i class="bi bi-geo-alt-fill d-block fs-4 mb-1"></i>
                  <strong><?= te('Länder blockieren') ?></strong>
                  <div class="small text-muted mt-1"><?= te('Alle Anmeldungen außerhalb erlaubter Länder blockieren.') ?></div>
                </label>
              </div>
              <div class="col-md-4">
                <input type="radio" class="btn-check" name="template" id="tplMfa" value="mfa_all" checked>
                <label class="btn btn-outline-primary w-100 h-100 text-start p-3" for="tplMfa">
                  <i class="bi bi-shield-check d-block fs-4 mb-1"></i>
                  <strong><?= te('MFA für alle') ?></strong>
                  <div class="small text-muted mt-1"><?= te('Multi-Faktor-Authentifizierung für alle Benutzer erzwingen.') ?></div>
                </label>
              </div>
              <div class="col-md-4">
                <input type="radio" class="btn-check" name="template" id="tplLegacy" value="block_legacy">
                <label class="btn btn-outline-warning w-100 h-100 text-start p-3" for="tplLegacy">
                  <i class="bi bi-ban d-block fs-4 mb-1"></i>
                  <strong><?= te('Legacy-Auth blockieren') ?></strong>
                  <div class="small text-muted mt-1"><?= te('Alte Protokolle (IMAP, POP, SMTP Auth) blockieren.') ?></div>
                </label>
              </div>
            </div>
          </div>

          <!-- Country location picker (only for country_block) -->
          <div id="rowLocation" class="mb-3" style="display:none">
            <label class="form-label fw-semibold">
              <?= te('Erlaubter Länder-Standort') ?> <span class="text-danger">*</span>
            </label>
            <?php if (empty($countryLocations)): ?>
            <div class="alert alert-warning small">
              <i class="bi bi-exclamation-triangle-fill me-1"></i>
              <?= te('Kein Länder-Standort vorhanden. Erst auf der') ?>
              <a href="/namedlocations"><?= te('Named Locations Seite') ?></a> <?= te('einen Länder-Standort anlegen.') ?>
            </div>
            <?php else: ?>
            <select name="namedLocationId" class="form-select" id="selLocation">
              <option value=""><?= te('– Bitte wählen –') ?></option>
              <?php foreach ($countryLocations as $loc): ?>
              <option value="<?= $e($loc['id']) ?>">
                <?= $e($loc['displayName']) ?>
                (<?= $e(implode(', ', $loc['countriesAndRegions'] ?? [])) ?>)
              </option>
              <?php endforeach ?>
            </select>
            <div class="form-text">
              <?= te('Anmeldungen aus Ländern, die in dieser Liste stehen, werden erlaubt — alle anderen blockiert.') ?>
            </div>
            <?php endif ?>
          </div>

          <!-- Name -->
          <div class="mb-3">
            <label class="form-label fw-semibold"><?= te('Name der Richtlinie') ?> <span class="text-danger">*</span></label>
            <input type="text" name="displayName" id="inpName" class="form-control" required
                   maxlength="256" placeholder="<?= te('z.B. Block: Nicht-DACH-Länder') ?>">
          </div>

          <!-- Break-glass exclusion -->
          <div class="mb-3">
            <label class="form-label fw-semibold"><?= te('Break-Glass-Konto ausschließen') ?></label>
            <input type="text" name="excludeUserId" class="form-control font-monospace" id="inpExclude"
                   placeholder="<?= te('Object-ID des Notfall-Admins (optional, empfohlen)') ?>">
            <div class="form-text">
              <?= te('Die Object-ID findest du in') ?>
              <a href="https://entra.microsoft.com/#view/Microsoft_AAD_UsersAndTenants/UserManagementMenuBlade/~/AllUsers" target="_blank" rel="noopener noreferrer"><?= te('Entra ID → Benutzer') ?></a>.
              <?= te('Mehrere IDs kommagetrennt.') ?>
            </div>
          </div>

          <!-- Initial state -->
          <div class="mb-3">
            <label class="form-label fw-semibold"><?= te('Anfangsstatus') ?></label>
            <select name="state" class="form-select">
              <option value="enabledForReportingButNotEnforced" selected>
                <?= te('Report-only (empfohlen — erst testen!)') ?>
              </option>
              <option value="disabled"><?= te('Deaktiviert') ?></option>
              <option value="enabled"><?= te('Sofort aktivieren (Vorsicht!)') ?></option>
            </select>
          </div>

          <div class="alert alert-warning small mb-0">
            <i class="bi bi-exclamation-triangle-fill me-1"></i>
            <?= te('Neue Richtlinien sollten immer im') ?> <strong><?= te('Report-only-Modus') ?></strong> <?= te('gestartet werden.') ?>
            <?= te('Im Report-Modus kannst du im Sign-in-Log prüfen, wen die Richtlinie betreffen würde, bevor du sie aktivierst. Sorgfältig: Ein Break-Glass-Konto ausschließen ist Pflicht!') ?>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= te('Abbrechen') ?></button>
          <button type="submit" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i><?= te('Richtlinie erstellen') ?>
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
(function () {
  // Show/hide location picker based on template
  const radios = document.querySelectorAll('[name="template"]');
  const rowLoc = document.getElementById('rowLocation');
  const inpName = document.getElementById('inpName');

  const templates = {
    country_block: <?= json_encode(t('Block: Nicht-DACH-Länder'), JSON_UNESCAPED_UNICODE) ?>,
    mfa_all:       <?= json_encode(t('Require MFA – Alle Benutzer'), JSON_UNESCAPED_UNICODE) ?>,
    block_legacy:  <?= json_encode(t('Block: Legacy-Authentifizierung'), JSON_UNESCAPED_UNICODE) ?>,
  };

  function onTemplateChange() {
    const val = document.querySelector('[name="template"]:checked')?.value;
    rowLoc.style.display = val === 'country_block' ? '' : 'none';
    if (inpName.value === '' || Object.values(templates).includes(inpName.value)) {
      inpName.value = templates[val] ?? '';
    }
  }

  radios.forEach(r => r.addEventListener('change', onTemplateChange));
  onTemplateChange();

  // Pre-select template when opened from gap analysis buttons
  const modal = document.getElementById('modalCreate');
  modal.addEventListener('show.bs.modal', function (ev) {
    const tpl = ev.relatedTarget?.dataset?.template;
    if (tpl) {
      const radio = document.getElementById({ country_block: 'tplCountry', mfa_all: 'tplMfa', block_legacy: 'tplLegacy' }[tpl]);
      if (radio) { radio.checked = true; onTemplateChange(); }
    }
  });
})();
</script>
