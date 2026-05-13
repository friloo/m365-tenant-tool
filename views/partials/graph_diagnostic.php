<?php
/**
 * Wiederverwendbarer Diagnose-Banner für Graph-API-Fehler.
 *
 * Aufruf:
 *   <?php $diag = $someDiag; include BASE_PATH . '/views/partials/graph_diagnostic.php'; ?>
 *
 * Erwartet:
 *   $diag — array{type:string, short:string, detail:string, fix_url?:string} | null
 *
 * Optionale Variablen:
 *   $diagStyle    — 'alert' (default) | 'empty' (zentriert, größeres Icon)
 *   $diagIcon     — Bootstrap-Icon-Name (ohne 'bi-' Präfix)
 *   $diagTitle    — Optionaler Titel ÜBER der Diagnose (z.B. "Keine Geräte verfügbar")
 */

if (empty($diag) || !is_array($diag)) return;

$esc       = fn($v) => htmlspecialchars((string)$v, ENT_QUOTES);
$style     = $diagStyle ?? 'alert';
$iconName  = $diagIcon  ?? 'exclamation-triangle-fill';
$title     = $diagTitle ?? null;

if ($style === 'empty'):
?>
<div class="empty-state text-center py-4">
    <i class="bi bi-<?= $esc($iconName) ?> text-muted" style="font-size:2.2rem;color:#b45309;"></i>
    <?php if ($title): ?>
        <p class="mt-3 mb-1 fw-semibold"><?= $esc($title) ?></p>
    <?php endif; ?>
    <p class="mt-2 fw-semibold" style="color:#b45309;"><?= $esc($diag['short']) ?></p>
    <p class="text-muted small mb-0" style="max-width:560px;margin:0 auto;line-height:1.5;">
        <?= $esc($diag['detail']) ?>
    </p>
    <?php if (!empty($diag['fix_url'])): ?>
        <a href="<?= $esc($diag['fix_url']) ?>" class="btn btn-sm btn-outline-secondary mt-3">
            <i class="bi bi-arrow-right-circle me-1"></i>Zur Lösung
        </a>
    <?php endif; ?>
</div>
<?php else: // alert ?>
<div class="alert alert-warning d-flex gap-3 mb-3" role="alert">
    <i class="bi bi-<?= $esc($iconName) ?> flex-shrink-0 mt-1" style="font-size:1.4rem;color:#b45309;"></i>
    <div class="flex-grow-1">
        <?php if ($title): ?>
            <div class="fw-semibold mb-1"><?= $esc($title) ?></div>
        <?php endif; ?>
        <div class="fw-semibold mb-1"><?= $esc($diag['short']) ?></div>
        <div class="small text-muted"><?= $esc($diag['detail']) ?></div>
        <?php if (!empty($diag['fix_url'])): ?>
            <a href="<?= $esc($diag['fix_url']) ?>" class="btn btn-sm btn-outline-secondary mt-2">
                <i class="bi bi-arrow-right-circle me-1"></i>Zur Lösung
            </a>
        <?php endif; ?>
    </div>
</div>
<?php endif;
// Reset für nachfolgende Aufrufe im selben View
unset($diagStyle, $diagIcon, $diagTitle);
