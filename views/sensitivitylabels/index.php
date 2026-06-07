<?php
$e = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
?>

<?php if ($lastError): ?>
<div class="alert alert-warning">
  <i class="bi bi-exclamation-triangle-fill me-2"></i>
  <?= $e($lastError['message'] ?? 'Fehler beim Abruf') ?> —
  Benötigte Berechtigung: <code>InformationProtectionPolicy.Read.All</code>
</div>
<?php endif ?>

<div class="d-flex justify-content-end mb-3">
  <a href="?refresh=1" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-clockwise"></i> Neu laden</a>
</div>

<!-- Policy settings -->
<?php if (!empty($settings)): ?>
<div class="card shadow-sm mb-4">
  <div class="card-header fw-semibold"><i class="bi bi-gear me-2"></i>Richtlinieneinstellungen</div>
  <div class="card-body small">
    <div class="row g-3">
      <?php foreach ($settings as $key => $val): ?>
      <?php if ($key === '@odata.context' || $key === 'id') continue; ?>
      <div class="col-sm-6 col-lg-4">
        <div class="fw-semibold text-muted"><?= $e(preg_replace('/([A-Z])/', ' $1', ucfirst($key))) ?></div>
        <div>
          <?php if (is_bool($val)): ?>
            <span class="badge bg-<?= $val ? 'success' : 'secondary' ?>"><?= $val ? 'Ja' : 'Nein' ?></span>
          <?php elseif (is_array($val)): ?>
            <span class="text-muted"><?= count($val) ?> Einträge</span>
          <?php else: ?>
            <?= $e((string)$val) ?>
          <?php endif ?>
        </div>
      </div>
      <?php endforeach ?>
    </div>
  </div>
</div>
<?php endif ?>

<!-- Labels -->
<?php if (empty($labels)): ?>
<div class="card shadow-sm mb-4">
  <div class="card-body text-center py-5">
    <i class="bi bi-tag text-muted" style="font-size:3rem"></i>
    <p class="mt-3 text-muted">
      Keine Vertraulichkeitsbezeichnungen gefunden.<br>
      <span class="small">Entweder sind keine konfiguriert, oder die Berechtigung <code>InformationProtectionPolicy.Read.All</code> fehlt.</span>
    </p>
    <a href="https://compliance.microsoft.com/informationprotection" target="_blank" rel="noopener noreferrer"
       class="btn btn-outline-primary btn-sm mt-2">
      <i class="bi bi-box-arrow-up-right me-1"></i>Microsoft Purview öffnen
    </a>
  </div>
</div>
<?php else: ?>
<div class="card shadow-sm mb-4">
  <div class="card-header fw-semibold d-flex justify-content-between">
    <span><i class="bi bi-tag me-2"></i>Vertraulichkeitsbezeichnungen</span>
    <span class="badge bg-secondary"><?= count($labels) ?></span>
  </div>
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0" id="tblLabels">
      <thead class="table-light">
        <tr>
          <th>Name</th>
          <th>Beschreibung</th>
          <th>Verschlüsselung</th>
          <th>Markierung</th>
          <th>Priorität</th>
          <th>Aktiv</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($labels as $label): ?>
        <tr>
          <td>
            <span class="fw-semibold"><?= $e($label['name'] ?? $label['displayName'] ?? '–') ?></span>
            <?php
            $color = $label['color'] ?? $label['labelColor'] ?? null;
            if ($color && preg_match('/^#[0-9a-fA-F]{6}$/', $color)):
            ?>
            <span class="ms-2" style="display:inline-block;width:12px;height:12px;border-radius:50%;background:<?= $e($color) ?>;border:1px solid #ccc" title="<?= $e($color) ?>"></span>
            <?php endif ?>
          </td>
          <td class="text-muted small"><?= $e(mb_strimwidth($label['description'] ?? $label['tooltip'] ?? '', 0, 80, '…')) ?></td>
          <td>
            <?php $enc = $label['encryptionEnabled'] ?? !empty($label['protectionSettings']); ?>
            <span class="badge bg-<?= $enc ? 'warning text-dark' : 'secondary' ?>">
              <?= $enc ? 'Ja' : 'Nein' ?>
            </span>
          </td>
          <td>
            <?php
            $marking = [];
            if (!empty($label['headerEnabled'] ?? $label['applyToDocumentBody']['isEnabled'] ?? false)) $marking[] = 'Kopfzeile';
            if (!empty($label['footerEnabled'] ?? false)) $marking[] = 'Fußzeile';
            if (!empty($label['watermarkEnabled'] ?? false)) $marking[] = 'Wasserzeichen';
            echo $marking ? $e(implode(', ', $marking)) : '<span class="text-muted">–</span>';
            ?>
          </td>
          <td class="text-muted small"><?= $label['priority'] ?? $label['rank'] ?? '–' ?></td>
          <td>
            <?php $active = $label['isActive'] ?? true; ?>
            <span class="badge bg-<?= $active ? 'success' : 'secondary' ?>"><?= $active ? 'Ja' : 'Nein' ?></span>
          </td>
        </tr>
        <?php endforeach ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif ?>

<?php
// Anzeige der Labels erfolgt oben (Graph, read-only). Anlegen/Veröffentlichen hat keine
// Graph-Write-API → Purview-Portal oder Security-&-Compliance-PowerShell.
echo \App\Core\Ui::externalCard(
    'Labels anlegen &amp; veröffentlichen',
    'Sensitivity Labels lassen sich über Graph nur <strong>lesen</strong> (oben). Anlegen, '
    . 'Verschlüsselung/Markierung konfigurieren und per Label-Policy veröffentlichen erfolgt im '
    . '<strong>Microsoft-Purview-Portal</strong> oder per <strong>PowerShell</strong>. '
    . 'Erfordert Microsoft 365 E3/E5 bzw. Azure Information Protection.',
    [
        ['https://purview.microsoft.com/informationprotection/labels', 'Labels im Purview-Portal'],
    ],
    [
        ["Connect-IPPSSession -UserPrincipalName admin@deine-domain.de", 'Mit Security & Compliance PowerShell verbinden'],
        ["Get-Label | Format-Table Name,DisplayName,IsEnabled", 'Vorhandene Labels auflisten'],
        ["New-Label -Name \"Vertraulich\" -DisplayName \"Vertraulich\" `\n  -Tooltip \"Nur intern, vertraulich\" -EncryptionEnabled \$true\n\nNew-LabelPolicy -Name \"Standard\" -Labels \"Vertraulich\" `\n  -ExchangeLocation All", 'Label anlegen & veröffentlichen'],
    ],
    'tags'
);
?>
