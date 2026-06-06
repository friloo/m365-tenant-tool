<?php
/** @var array $prices   partNum → [name, tier, price_eur, price_npo_eur, default_eur, default_npo] */
/** @var string|null $flash */
/** @var string|null $error */
?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h3 mb-1">Lizenzpreise konfigurieren</h1>
        <p class="text-muted mb-0">
            Überschreibe die Katalog-Standardpreise mit deinen tatsächlichen Partner- oder CSP-Preisen.
            Leer lassen = Katalog-Standard verwenden.
        </p>
    </div>
    <a href="https://www.microsoft.com/de-de/microsoft-365/business/compare-all-microsoft-365-business-products"
       target="_blank" rel="noopener noreferrer" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-box-arrow-up-right me-1"></i>Microsoft Preisseite (DE)
    </a>
</div>

<?php if ($flash): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($flash) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($error) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="alert alert-info d-flex align-items-start gap-2 mb-4">
    <i class="bi bi-info-circle-fill mt-1 flex-shrink-0"></i>
    <div>
        Alle Preise sind <strong>Netto pro Nutzer/Monat</strong> (Jahresabonnement).
        Katalog-Standardwerte stammen aus der Microsoft Deutschland Preisseite (Stand Mai 2025).
        Konfigurierte Preise überschreiben die Standardwerte in Lizenz-Berater und Lizenzkosten.
    </div>
</div>

<form method="POST" action="/settings/license-prices/save">
    <?= \App\Core\Csrf::field() ?>

<?php
$tiers = [];
foreach ($prices as $partNum => $p) {
    $tiers[$p['tier']][$partNum] = $p;
}
ksort($tiers);
?>

<?php foreach ($tiers as $tierName => $tierSkus): ?>
<div class="card mb-4">
    <div class="card-header fw-semibold"><?= htmlspecialchars($tierName) ?></div>
    <div class="card-body p-0">
        <table class="table table-sm mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>SKU</th>
                    <th>Part Number</th>
                    <th style="width:200px">Preis Standard (€)</th>
                    <th style="width:200px">Preis NPO (€)</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($tierSkus as $partNum => $p): ?>
            <tr>
                <td class="fw-medium"><?= htmlspecialchars($p['name']) ?></td>
                <td class="text-muted small font-monospace"><?= htmlspecialchars($partNum) ?></td>
                <td>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text">€</span>
                        <input type="number"
                               name="price_eur[<?= htmlspecialchars($partNum) ?>]"
                               class="form-control"
                               step="0.01" min="0"
                               value="<?= htmlspecialchars($p['price_eur']) ?>"
                               placeholder="<?= $p['default_eur'] !== null ? number_format($p['default_eur'], 2, ',', '.') : '–' ?>">
                    </div>
                    <?php if ($p['default_eur'] !== null): ?>
                    <small class="text-muted">Standard: <?= number_format($p['default_eur'], 2, ',', '.') ?> €</small>
                    <?php endif; ?>
                </td>
                <td>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text">€</span>
                        <input type="number"
                               name="price_npo_eur[<?= htmlspecialchars($partNum) ?>]"
                               class="form-control"
                               step="0.01" min="0"
                               value="<?= htmlspecialchars($p['price_npo_eur']) ?>"
                               placeholder="<?= $p['default_npo'] !== null ? number_format($p['default_npo'], 2, ',', '.') : '–' ?>">
                    </div>
                    <?php if ($p['default_npo'] !== null): ?>
                    <small class="text-muted">Standard: <?= number_format($p['default_npo'], 2, ',', '.') ?> €</small>
                    <?php elseif ($p['default_npo'] === null): ?>
                    <small class="text-muted">Kein NPO-Preis bekannt</small>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endforeach; ?>

<div class="d-flex gap-2 mb-5">
    <button type="submit" class="btn btn-primary">
        <i class="bi bi-check-lg me-1"></i>Preise speichern
    </button>
    <button type="reset" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-counterclockwise me-1"></i>Zurücksetzen
    </button>
    <a href="/settings" class="btn btn-outline-secondary ms-auto">
        Zurück zu Einstellungen
    </a>
</div>

</form>
