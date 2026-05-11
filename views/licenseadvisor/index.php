<?php
use App\Core\View;
$e = fn($v) => View::escape($v);

// Helpers
$criteriaIcons = [
    'exchange_online' => 'bi-envelope',
    'office_desktop'  => 'bi-display',
    'teams'           => 'bi-chat-dots',
    'sharepoint'      => 'bi-share',
    'onedrive'        => 'bi-cloud',
    'intune'          => 'bi-phone',
];
$criteriaDesc = [
    'exchange_online' => 'Postfach, Kalender und E-Mail-Funktionen über Exchange Online.',
    'office_desktop'  => 'Installierbare Office-Apps (Word, Excel, PowerPoint, …).',
    'teams'           => 'Microsoft Teams für Chat, Meetings und Zusammenarbeit.',
    'sharepoint'      => 'SharePoint Online – Intranets, Dokumentenbibliotheken.',
    'onedrive'        => 'OneDrive for Business – persönlicher Cloud-Speicher.',
    'intune'          => 'Intune / Mobile Device Management für Geräteverwaltung.',
];

$covered       = $analysis['covered']         ?? [];
$uncovered     = $analysis['uncovered']        ?? [];
$noLicense     = $analysis['no_license']       ?? [];
$inactive      = $analysis['inactive_wasted']  ?? [];

$totalEnabled  = count($covered) + count($uncovered);
$coveredCount  = count($covered);
$uncoveredCount = count($uncovered);
$noLicenseCount = count($noLicense);
$inactiveCount  = count($inactive);

// Find recommended SKU (most available slots among matching)
$recommendedSkuId = null;
if (!empty($matchingSkus)) {
    $best = $matchingSkus[0];
    foreach ($matchingSkus as $s) {
        if ($s['available'] > $best['available']) {
            $best = $s;
        }
    }
    $recommendedSkuId = $best['skuId'];
}

$priceMode    = $priceMode    ?? 'npo';
$showCatalog  = $showCatalog  ?? false;
$matchingCatalog = $matchingCatalog ?? [];
$priceKey     = $priceMode === 'standard' ? 'price_eur' : 'price_npo_eur';
$priceLabel   = $priceMode === 'standard' ? 'Listenpreis' : 'NPO-Preis';

$fmtPrice = function ($val) {
    if ($val === null) return '<span class="text-muted">–</span>';
    if ((float)$val === 0.0) return '<span class="badge-success badge-pill" style="font-size:11px;">kostenlos*</span>';
    return number_format((float)$val, 2, ',', '.') . ' €';
};
?>

<?php if (!empty($flash)): ?>
    <div class="alert alert-success alert-dismissible mb-4">
        <i class="bi bi-check-circle me-2"></i><?= $e($flash) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>
<?php if (!empty($error)): ?>
    <div class="alert alert-danger alert-dismissible mb-4">
        <i class="bi bi-exclamation-triangle me-2"></i><?= $e($error) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (empty($activeCriteria)): ?>
    <div class="alert alert-info d-flex align-items-center gap-2 mb-4">
        <i class="bi bi-info-circle-fill fs-5"></i>
        <span>Wähle mindestens ein Kriterium aus um die Analyse zu starten.</span>
    </div>
<?php endif; ?>

<!-- ═══════════════════════════════════════════════════════════
     1. Criteria configurator
     ═══════════════════════════════════════════════════════════ -->
<div class="content-card mb-4">
    <div class="card-header-custom">
        <i class="bi bi-sliders text-primary"></i>
        <h6>Kriterien konfigurieren</h6>
    </div>
    <div class="card-body-custom">
        <p class="text-muted small mb-3">
            Aktiviere die Features, die <strong>alle</strong> Benutzer nach der Exchange-Online-Migration
            benötigen. Der Advisor zeigt dann, welche Lizenzpläne diese Kombination abdecken und
            welche Benutzer noch nicht abgedeckt sind.
        </p>
        <form method="post" action="/licenseadvisor/save-criteria">
            <div class="row g-3 mb-4">
                <?php foreach ($criteriaMap as $key => $def): ?>
                    <div class="col-sm-6 col-lg-4">
                        <div class="d-flex align-items-start gap-3 p-3 rounded border"
                             style="background:<?= in_array($key, $activeCriteria, true) ? '#f0f9ff' : '#f9fafb' ?>;">
                            <i class="bi <?= $e($criteriaIcons[$key] ?? 'bi-check-circle') ?> fs-4 mt-1"
                               style="color:<?= in_array($key, $activeCriteria, true) ? '#0078d4' : '#9ca3af' ?>;"></i>
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center justify-content-between">
                                    <label class="form-check-label fw-medium" for="crit_<?= $e($key) ?>">
                                        <?= $e($def['label']) ?>
                                    </label>
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox"
                                               id="crit_<?= $e($key) ?>"
                                               name="criteria[<?= $e($key) ?>]"
                                               value="1"
                                               <?= in_array($key, $activeCriteria, true) ? 'checked' : '' ?>>
                                    </div>
                                </div>
                                <div class="text-muted" style="font-size:12px;margin-top:2px;">
                                    <?= $e($criteriaDesc[$key] ?? '') ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <button type="submit" class="btn btn-primary px-4">
                <i class="bi bi-check2 me-1"></i> Kriterien speichern &amp; analysieren
            </button>
        </form>
    </div>
</div>

<?php if (!empty($activeCriteria)): ?>

<!-- ═══════════════════════════════════════════════════════════
     2. Metric cards
     ═══════════════════════════════════════════════════════════ -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="metric-card">
            <div class="metric-label">Gesamt (aktive Nutzer)</div>
            <div class="metric-value"><?= number_format($totalEnabled) ?></div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="metric-card">
            <div class="metric-label">Abgedeckt</div>
            <div class="metric-value" style="color:#16a34a;"><?= number_format($coveredCount) ?></div>
            <div class="metric-sub"><?= $totalEnabled > 0 ? round(($coveredCount / $totalEnabled) * 100) : 0 ?>% der aktiven Nutzer</div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="metric-card">
            <div class="metric-label">Nicht abgedeckt</div>
            <div class="metric-value" style="color:<?= $uncoveredCount > 0 ? '#dc2626' : '#16a34a' ?>;">
                <?= number_format($uncoveredCount) ?>
            </div>
            <div class="metric-sub">Fehlende Kriterien</div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="metric-card">
            <div class="metric-label">Ohne Lizenz</div>
            <div class="metric-value" style="color:<?= $noLicenseCount > 0 ? '#d97706' : '#16a34a' ?>;">
                <?= number_format($noLicenseCount) ?>
            </div>
            <div class="metric-sub">Keine Lizenz zugewiesen</div>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════
     3. Display toggles (catalog + price mode)
     ═══════════════════════════════════════════════════════════ -->
<div class="content-card mb-4" style="background:#f8fafc;">
    <div class="card-body-custom py-3">
        <form method="get" action="/licenseadvisor" class="d-flex flex-wrap align-items-center gap-3" style="font-size:13px;">
            <div class="d-flex align-items-center gap-2">
                <span class="text-muted">Preise:</span>
                <select name="price_mode" class="form-select form-select-sm" style="width:auto;" onchange="this.form.submit()">
                    <option value="npo"      <?= $priceMode === 'npo'      ? 'selected' : '' ?>>NPO (Non-Profit)</option>
                    <option value="standard" <?= $priceMode === 'standard' ? 'selected' : '' ?>>Standard (Listenpreis)</option>
                </select>
            </div>
            <div class="form-check form-switch m-0">
                <input class="form-check-input" type="checkbox" id="show_catalog_toggle"
                       name="show_catalog" value="1" <?= $showCatalog ? 'checked' : '' ?>
                       onchange="this.form.submit()">
                <label class="form-check-label" for="show_catalog_toggle">
                    Auch nicht-gekaufte Lizenzen als Vorschlag anzeigen
                </label>
            </div>
            <span class="ms-auto text-muted" style="font-size:11px;">
                Preise sind Richtwerte (€/User/Monat, jährliche Abrechnung). Stand 2024 — bitte mit dem Microsoft-Partner verifizieren.
            </span>
        </form>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════
     4. Passende Lizenzen im Tenant
     ═══════════════════════════════════════════════════════════ -->
<div class="content-card mb-4">
    <div class="card-header-custom">
        <i class="bi bi-award text-primary"></i>
        <h6>Passende Lizenzen im Tenant</h6>
        <span class="ms-auto text-muted" style="font-size:12px;">
            Pläne, die <strong>alle</strong> gewählten Kriterien erfüllen
        </span>
    </div>
    <div class="card-body-custom p-0">
        <?php if (empty($matchingSkus)): ?>
            <div class="p-4">
                <div class="alert alert-warning mb-0 d-flex align-items-center gap-2">
                    <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                    <span>Kein gekaufter Plan erfüllt alle gewählten Kriterien.</span>
                </div>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Plan-Name</th>
                            <th>Verfügbar</th>
                            <th>Verbraucht</th>
                            <th><?= $e($priceLabel) ?></th>
                            <th>Features</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($matchingSkus as $sku): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div>
                                            <div class="fw-medium d-flex align-items-center gap-2">
                                                <?= $e($sku['name']) ?>
                                                <?php if ($sku['skuId'] === $recommendedSkuId): ?>
                                                    <span class="badge-success badge-pill">Empfohlen</span>
                                                <?php endif; ?>
                                            </div>
                                            <div style="font-size:11px;color:#9ca3af;font-family:monospace;">
                                                <?= $e($sku['partNumber']) ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <?php if ($sku['available'] <= 0): ?>
                                        <span class="badge-disabled">0</span>
                                    <?php elseif ($sku['available'] <= 10): ?>
                                        <span class="badge-warning"><?= $sku['available'] ?></span>
                                    <?php else: ?>
                                        <span class="badge-success"><?= number_format($sku['available']) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td><?= number_format($sku['consumed']) ?> / <?= number_format($sku['total']) ?></td>
                                <td style="font-size:13px;font-weight:500;"><?= $fmtPrice($sku[$priceKey] ?? null) ?></td>
                                <td>
                                    <div class="d-flex flex-wrap gap-1">
                                        <?php foreach ($sku['metCriteria'] as $criterionKey): ?>
                                            <?php if (isset($criteriaMap[$criterionKey])): ?>
                                                <span class="badge-success badge-pill" style="font-size:11px;">
                                                    <i class="bi <?= $e($criteriaIcons[$criterionKey] ?? 'bi-check') ?> me-1"></i>
                                                    <?= $e($criteriaMap[$criterionKey]['label']) ?>
                                                </span>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php if ($showCatalog && !empty($matchingCatalog)): ?>
<!-- ═══════════════════════════════════════════════════════════
     4b. Alternative Lizenzen aus dem Katalog (nicht gekauft)
     ═══════════════════════════════════════════════════════════ -->
<div class="content-card mb-4" style="border-left:3px solid #3b82f6;">
    <div class="card-header-custom">
        <i class="bi bi-lightbulb text-info"></i>
        <h6>Alternative Lizenzen (nicht im Tenant)</h6>
        <span class="ms-auto text-muted" style="font-size:12px;">
            Pläne, die ebenfalls alle Kriterien erfüllen würden
        </span>
    </div>
    <div class="card-body-custom p-0">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Plan-Name</th>
                        <th>Tier</th>
                        <th><?= $e($priceLabel) ?></th>
                        <th>Kosten/Monat<br><small style="font-weight:normal;color:#9ca3af;">bei <?= number_format($coveredCount + $uncoveredCount) ?> Nutzer</small></th>
                        <th>Features</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Sort by price (ascending)
                    usort($matchingCatalog, function($a, $b) use ($priceKey) {
                        $pa = $a[$priceKey] ?? PHP_INT_MAX;
                        $pb = $b[$priceKey] ?? PHP_INT_MAX;
                        return $pa <=> $pb;
                    });
                    foreach ($matchingCatalog as $sku):
                        $price       = $sku[$priceKey] ?? null;
                        $monthlyCost = $price !== null ? $price * ($coveredCount + $uncoveredCount) : null;
                    ?>
                        <tr>
                            <td>
                                <div class="fw-medium"><?= $e($sku['name']) ?></div>
                                <div style="font-size:11px;color:#9ca3af;font-family:monospace;">
                                    <?= $e($sku['partNumber']) ?>
                                </div>
                            </td>
                            <td>
                                <span class="badge-neutral badge-pill" style="font-size:11px;">
                                    <?= $e($sku['tier'] ?? '–') ?>
                                </span>
                            </td>
                            <td style="font-size:13px;font-weight:500;"><?= $fmtPrice($price) ?></td>
                            <td style="font-size:13px;">
                                <?php if ($monthlyCost === null): ?>
                                    <span class="text-muted">–</span>
                                <?php elseif ($monthlyCost === 0.0): ?>
                                    <span class="badge-success badge-pill" style="font-size:11px;">kostenlos*</span>
                                <?php else: ?>
                                    <?= number_format($monthlyCost, 2, ',', '.') ?> €
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="d-flex flex-wrap gap-1">
                                    <?php foreach ($sku['metCriteria'] as $criterionKey): ?>
                                        <?php if (isset($criteriaMap[$criterionKey])): ?>
                                            <span class="badge-info badge-pill" style="font-size:11px;">
                                                <i class="bi <?= $e($criteriaIcons[$criterionKey] ?? 'bi-check') ?> me-1"></i>
                                                <?= $e($criteriaMap[$criterionKey]['label']) ?>
                                            </span>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="p-3" style="background:#f8fafc;border-top:1px solid #e5e7eb;font-size:11px;color:#6b7280;">
            * "kostenlos" gilt typischerweise für die ersten 10 Nutzer im NPO-Programm. Bei Microsoft 365 Business Basic / Office 365 E1.
            Preise sind ungefähre Richtwerte ohne Gewähr. Bitte beim Microsoft-Partner verifizieren.
        </div>
    </div>
</div>
<?php elseif ($showCatalog && empty($matchingCatalog) && !empty($activeCriteria)): ?>
<div class="content-card mb-4">
    <div class="card-body-custom">
        <div class="empty-state">
            <i class="bi bi-info-circle"></i>
            <p>Keine weiteren Lizenzen im Katalog erfüllen alle gewählten Kriterien.</p>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- ═══════════════════════════════════════════════════════════
     4. Benutzer ohne Abdeckung
     ═══════════════════════════════════════════════════════════ -->
<?php
$gapUsers = array_merge($uncovered, $noLicense);
?>
<div class="content-card mb-4">
    <div class="card-header-custom">
        <i class="bi bi-person-x text-danger"></i>
        <h6>Benutzer ohne Abdeckung</h6>
        <span class="badge-danger badge-pill ms-2"><?= count($gapUsers) ?></span>
        <?php if (!empty($gapUsers)): ?>
            <a href="/licenseadvisor/export" class="btn btn-sm btn-outline-secondary ms-auto">
                <i class="bi bi-download me-1"></i>CSV Export
            </a>
        <?php endif; ?>
    </div>
    <div class="card-body-custom p-0">
        <?php if (empty($gapUsers)): ?>
            <div class="p-4">
                <div class="empty-state">
                    <i class="bi bi-person-check"></i>
                    <p>Alle aktiven Benutzer erfüllen die gewählten Kriterien.</p>
                </div>
            </div>
        <?php else: ?>
            <div class="table-toolbar">
                <input type="text" id="gapSearch" class="search-box" placeholder="Benutzer suchen…">
            </div>
            <div class="table-responsive">
                <table class="data-table" id="gapTable">
                    <thead>
                        <tr>
                            <th style="width:40px;"></th>
                            <th>Name</th>
                            <th>UPN</th>
                            <th>Fehlende Kriterien</th>
                            <th>Letzter Login</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($gapUsers as $u):
                            $nameParts = explode(' ', $u['displayName'] ?? '');
                            $initials  = '';
                            foreach (array_slice($nameParts, 0, 2) as $part) {
                                $initials .= strtoupper(mb_substr($part, 0, 1));
                            }
                            $missing    = $u['missing'] ?? [];
                            $lastSignIn = $u['signInActivity']['lastSignInDateTime'] ?? null;
                        ?>
                            <tr>
                                <td>
                                    <div style="width:32px;height:32px;border-radius:50%;background:#fee2e2;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:600;color:#dc2626;">
                                        <?= $e($initials ?: '?') ?>
                                    </div>
                                </td>
                                <td>
                                    <div style="font-size:13px;font-weight:500;"><?= $e($u['displayName'] ?? '') ?></div>
                                    <?php if (!empty($u['jobTitle'])): ?>
                                        <div style="font-size:11px;color:#9ca3af;"><?= $e($u['jobTitle']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td style="font-size:12px;color:#6b7280;"><?= $e($u['userPrincipalName'] ?? '') ?></td>
                                <td>
                                    <?php if (empty($missing)): ?>
                                        <span class="badge-warning badge-pill">Keine Lizenz</span>
                                    <?php else: ?>
                                        <div class="d-flex flex-wrap gap-1">
                                            <?php foreach ($missing as $mk): ?>
                                                <?php if (isset($criteriaMap[$mk])): ?>
                                                    <span class="badge-danger badge-pill" style="font-size:11px;">
                                                        <i class="bi <?= $e($criteriaIcons[$mk] ?? 'bi-x') ?> me-1"></i>
                                                        <?= $e($criteriaMap[$mk]['label']) ?>
                                                    </span>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td style="font-size:12px;color:#6b7280;white-space:nowrap;">
                                    <?= $lastSignIn ? date('d.m.Y', strtotime($lastSignIn)) : '–' ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════
     5. Einsparpotenzial — inactive users with matching license
     ═══════════════════════════════════════════════════════════ -->
<div class="content-card mb-4">
    <div class="card-header-custom">
        <i class="bi bi-piggy-bank text-warning"></i>
        <h6>Einsparpotenzial</h6>
        <span class="ms-auto text-muted" style="font-size:12px;">
            Nutzer mit passender Lizenz, aber inaktiv &gt;90 Tage
        </span>
    </div>
    <div class="card-body-custom">
        <?php if (empty($inactive)): ?>
            <div class="empty-state">
                <i class="bi bi-check-circle text-success"></i>
                <p>Kein Einsparpotenzial gefunden – alle lizenzierten Nutzer sind aktiv.</p>
            </div>
        <?php else: ?>
            <div class="alert alert-warning d-flex align-items-center gap-2 mb-3">
                <i class="bi bi-currency-euro fs-5"></i>
                <div>
                    <strong><?= $inactiveCount ?> Benutzer</strong> haben eine passende Lizenz,
                    aber haben sich seit mehr als 90 Tagen nicht angemeldet.
                    Diese <?= $inactiveCount ?> Lizenzeinheiten könnten freigegeben werden.
                </div>
            </div>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width:40px;"></th>
                            <th>Name</th>
                            <th>UPN</th>
                            <th>Abteilung</th>
                            <th>Letzter Login</th>
                            <th>Inaktiv (Tage)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($inactive as $u):
                            $nameParts  = explode(' ', $u['displayName'] ?? '');
                            $initials   = '';
                            foreach (array_slice($nameParts, 0, 2) as $part) {
                                $initials .= strtoupper(mb_substr($part, 0, 1));
                            }
                            $lastSignIn = $u['signInActivity']['lastSignInDateTime'] ?? null;
                            $daysInactive = $lastSignIn
                                ? (int)floor((time() - strtotime($lastSignIn)) / 86400)
                                : null;
                        ?>
                            <tr>
                                <td>
                                    <div style="width:32px;height:32px;border-radius:50%;background:#fef3c7;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:600;color:#d97706;">
                                        <?= $e($initials ?: '?') ?>
                                    </div>
                                </td>
                                <td>
                                    <div style="font-size:13px;font-weight:500;"><?= $e($u['displayName'] ?? '') ?></div>
                                    <?php if (!empty($u['jobTitle'])): ?>
                                        <div style="font-size:11px;color:#9ca3af;"><?= $e($u['jobTitle']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td style="font-size:12px;color:#6b7280;"><?= $e($u['userPrincipalName'] ?? '') ?></td>
                                <td style="font-size:12px;color:#6b7280;"><?= $e($u['department'] ?? '–') ?></td>
                                <td style="font-size:12px;color:#6b7280;white-space:nowrap;">
                                    <?= $lastSignIn ? date('d.m.Y', strtotime($lastSignIn)) : '–' ?>
                                </td>
                                <td>
                                    <?php if ($daysInactive === null): ?>
                                        <span class="badge-warning">Nie</span>
                                    <?php else: ?>
                                        <span class="badge-<?= $daysInactive >= 180 ? 'danger' : 'warning' ?>">
                                            <?= $daysInactive ?>d
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php endif; // end: if activeCriteria not empty ?>

<script>
<?php if (!empty($gapUsers)): ?>
initPagination('gapTable', 25);
initTableSearch('gapSearch', 'gapTable');
<?php endif; ?>
</script>
