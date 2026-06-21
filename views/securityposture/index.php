<?php use App\Core\View; $e = fn($v) => View::escape($v); ?>

<?php
$pct        = $score['percent'] ?? 0;
$passed     = $score['passed']  ?? 0;
$warned     = $score['warned']  ?? 0;
$failed     = $score['failed']  ?? 0;
$unknown    = $score['unknown'] ?? 0;
$total      = $score['total']   ?? 0;

$scoreColor = $pct >= 75 ? '#16a34a' : ($pct >= 50 ? '#d97706' : '#dc2626');
$scoreLabel = $pct >= 75 ? te('Gut') : ($pct >= 50 ? te('Verbesserungsbedarf') : te('Kritisch'));

$categoryIcons = [
    'Identität & MFA'          => 'bi-person-lock',
    'Conditional Access'       => 'bi-shield-lock',
    'Geräte & Compliance'      => 'bi-laptop',
    'E-Mail & Endpoint-Schutz' => 'bi-envelope-check',
    'Konfiguration & Apps'     => 'bi-gear',
    'DSGVO & Datenschutz'      => 'bi-file-earmark-lock',
];

$priorityColors = [
    'critical' => ['bg' => '#fef2f2', 'border' => '#fecaca', 'badge' => '#dc2626', 'label' => te('Kritisch')],
    'high'     => ['bg' => '#fff7ed', 'border' => '#fed7aa', 'badge' => '#ea580c', 'label' => te('Hoch')],
    'medium'   => ['bg' => '#fffbeb', 'border' => '#fde68a', 'badge' => '#d97706', 'label' => te('Mittel')],
    'low'      => ['bg' => '#f0f9ff', 'border' => '#bae6fd', 'badge' => '#0284c7', 'label' => te('Niedrig')],
];

$criticalRecs = array_filter($recommendations, fn($r) => $r['priority'] === 'critical');
$highRecs     = array_filter($recommendations, fn($r) => $r['priority'] === 'high');
?>

<!-- Score banner -->
<div class="content-card mb-4" style="background:linear-gradient(135deg,#0f172a 0%,#1e293b 100%);color:#fff;border:none;">
    <div class="card-body-custom">
        <div class="row align-items-center g-4">
            <!-- Score circle -->
            <div class="col-auto text-center" style="min-width:160px;">
                <div style="position:relative;display:inline-block;">
                    <svg width="120" height="120" viewBox="0 0 120 120">
                        <circle cx="60" cy="60" r="52" fill="none" stroke="rgba(255,255,255,.1)" stroke-width="10"/>
                        <circle cx="60" cy="60" r="52" fill="none" stroke="<?= $scoreColor ?>" stroke-width="10"
                                stroke-dasharray="<?= round(327 * $pct / 100) ?> 327"
                                stroke-linecap="round"
                                transform="rotate(-90 60 60)"/>
                    </svg>
                    <div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);text-align:center;">
                        <div style="font-size:28px;font-weight:700;line-height:1;color:<?= $scoreColor ?>;"><?= $pct ?>%</div>
                        <div style="font-size:10px;color:rgba(255,255,255,.6);margin-top:2px;"><?= $scoreLabel ?></div>
                    </div>
                </div>
            </div>

            <!-- Counters -->
            <div class="col">
                <div style="font-size:18px;font-weight:600;margin-bottom:16px;"><?= te('Security Posture Assessment') ?></div>
                <div class="row g-2">
                    <div class="col-6 col-sm-3">
                        <div style="background:rgba(22,163,74,.15);border:1px solid rgba(22,163,74,.3);border-radius:8px;padding:10px;text-align:center;">
                            <div style="font-size:22px;font-weight:700;color:#4ade80;"><?= $passed ?></div>
                            <div style="font-size:11px;color:rgba(255,255,255,.6);"><?= te('Bestanden') ?></div>
                        </div>
                    </div>
                    <div class="col-6 col-sm-3">
                        <div style="background:rgba(217,119,6,.15);border:1px solid rgba(217,119,6,.3);border-radius:8px;padding:10px;text-align:center;">
                            <div style="font-size:22px;font-weight:700;color:#fbbf24;"><?= $warned ?></div>
                            <div style="font-size:11px;color:rgba(255,255,255,.6);"><?= te('Warnung') ?></div>
                        </div>
                    </div>
                    <div class="col-6 col-sm-3">
                        <div style="background:rgba(220,38,38,.15);border:1px solid rgba(220,38,38,.3);border-radius:8px;padding:10px;text-align:center;">
                            <div style="font-size:22px;font-weight:700;color:#f87171;"><?= $failed ?></div>
                            <div style="font-size:11px;color:rgba(255,255,255,.6);"><?= te('Fehlgeschlagen') ?></div>
                        </div>
                    </div>
                    <div class="col-6 col-sm-3">
                        <div style="background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.1);border-radius:8px;padding:10px;text-align:center;">
                            <div style="font-size:22px;font-weight:700;color:rgba(255,255,255,.5);"><?= $unknown ?></div>
                            <div style="font-size:11px;color:rgba(255,255,255,.4);"><?= te('Unbekannt') ?></div>
                        </div>
                    </div>
                </div>
                <div style="margin-top:12px;font-size:12px;color:rgba(255,255,255,.5);">
                    <?= $total ?> <?= te('Prüfungen bewertet · gewichteter Score (Kritisch = 3×, Mittel = 2×, Niedrig = 1×) ·') ?>
                    <a href="?refresh=1" style="color:rgba(255,255,255,.6);text-decoration:none;">
                        <i class="bi bi-arrow-clockwise me-1"></i><?= te('Neu laden') ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Empfehlungen -->
<?php if (!empty($recommendations)): ?>
<div class="content-card mb-4">
    <div class="card-header-custom">
        <span><i class="bi bi-lightning-charge-fill me-2" style="color:#f59e0b;"></i><?= te('Empfehlungen') ?></span>
        <span style="font-size:12px;color:#9ca3af;"><?= count($recommendations) ?> <?= te('Maßnahme(n) · sortiert nach Priorität') ?></span>
    </div>
    <div class="card-body-custom p-0">
        <?php foreach ($recommendations as $idx => $rec):
            $p  = $rec['priority'];
            $pc = $priorityColors[$p] ?? $priorityColors['low'];
        ?>
        <div style="display:flex;align-items:flex-start;gap:16px;padding:16px 20px;<?= $idx > 0 ? 'border-top:1px solid #f3f4f6;' : '' ?>background:<?= $idx === 0 && $p === 'critical' ? '#fffbeb' : '' ?>;">

            <!-- Priority indicator -->
            <div style="flex-shrink:0;display:flex;flex-direction:column;align-items:center;gap:4px;min-width:64px;">
                <?php if ($p === 'critical'): ?>
                    <div style="width:36px;height:36px;border-radius:50%;background:#fef2f2;display:flex;align-items:center;justify-content:center;">
                        <i class="bi bi-exclamation-octagon-fill" style="color:#dc2626;font-size:18px;"></i>
                    </div>
                <?php elseif ($p === 'high'): ?>
                    <div style="width:36px;height:36px;border-radius:50%;background:#fff7ed;display:flex;align-items:center;justify-content:center;">
                        <i class="bi bi-exclamation-triangle-fill" style="color:#ea580c;font-size:18px;"></i>
                    </div>
                <?php elseif ($p === 'medium'): ?>
                    <div style="width:36px;height:36px;border-radius:50%;background:#fffbeb;display:flex;align-items:center;justify-content:center;">
                        <i class="bi bi-exclamation-circle-fill" style="color:#d97706;font-size:18px;"></i>
                    </div>
                <?php else: ?>
                    <div style="width:36px;height:36px;border-radius:50%;background:#f0f9ff;display:flex;align-items:center;justify-content:center;">
                        <i class="bi bi-info-circle-fill" style="color:#0284c7;font-size:18px;"></i>
                    </div>
                <?php endif; ?>
                <span style="font-size:10px;font-weight:600;color:<?= $pc['badge'] ?>;text-transform:uppercase;letter-spacing:.3px;"><?= $pc['label'] ?></span>
            </div>

            <!-- Content -->
            <div style="flex:1;min-width:0;">
                <div style="font-size:14px;font-weight:600;color:#111827;margin-bottom:4px;"><?= $e($rec['title']) ?></div>
                <div style="font-size:13px;color:#4b5563;line-height:1.5;"><?= $e($rec['description']) ?></div>
            </div>

            <!-- Action button -->
            <div style="flex-shrink:0;">
                <?php if (!empty($rec['ca_template'])): ?>
                    <a href="<?= $e($rec['module_url']) ?>?create=1&template=<?= $e($rec['ca_template']) ?>"
                       class="btn btn-sm"
                       style="background:#0078d4;color:#fff;border:none;white-space:nowrap;">
                        <i class="bi bi-plus-lg me-1"></i><?= $e($rec['action']) ?>
                    </a>
                <?php else: ?>
                    <a href="<?= $e($rec['module_url']) ?>"
                       class="btn btn-sm btn-outline-secondary"
                       style="white-space:nowrap;">
                        <i class="bi bi-arrow-right me-1"></i><?= $e($rec['action']) ?>
                    </a>
                <?php endif; ?>
            </div>

        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- Checks by category -->
<?php
$catOrder = ['Identität & MFA', 'Conditional Access', 'Geräte & Compliance', 'Konfiguration & Apps'];
$sortedCats = [];
foreach ($catOrder as $cat) {
    if (isset($byCategory[$cat])) {
        $sortedCats[$cat] = $byCategory[$cat];
    }
}
foreach ($byCategory as $cat => $items) {
    if (!isset($sortedCats[$cat])) {
        $sortedCats[$cat] = $items;
    }
}
?>

<div class="row g-3 mb-3">
    <?php foreach ($sortedCats as $category => $categoryChecks):
        $catPass = count(array_filter($categoryChecks, fn($c) => $c['status'] === 'pass'));
        $catFail = count(array_filter($categoryChecks, fn($c) => $c['status'] === 'fail'));
        $catWarn = count(array_filter($categoryChecks, fn($c) => $c['status'] === 'warn'));
        $catTot  = count(array_filter($categoryChecks, fn($c) => $c['status'] !== 'unknown'));
        $catPct  = $catTot > 0 ? round($catPass / $catTot * 100) : 0;
        $catColor = $catPct >= 80 ? '#16a34a' : ($catPct >= 50 ? '#d97706' : '#dc2626');
        $icon = $categoryIcons[$category] ?? 'bi-shield';
        $anchor = 'cat-' . preg_replace('/[^a-z0-9]+/i', '-', strtolower($category));
    ?>
    <div class="col-12" id="<?= $anchor ?>" style="scroll-margin-top:64px;">
    <div class="content-card mb-0">
        <div class="card-header-custom">
            <span style="display:flex;align-items:center;gap:10px;">
                <i class="bi <?= $icon ?>" style="font-size:16px;color:#0078d4;"></i>
                <strong><?= $e($category) ?></strong>
                <span style="font-size:12px;color:#9ca3af;"><?= count($categoryChecks) ?> <?= te('Prüfungen') ?></span>
            </span>
            <span style="display:flex;align-items:center;gap:8px;">
                <?php if ($catFail > 0): ?>
                    <span style="font-size:12px;color:#dc2626;"><i class="bi bi-x-circle-fill"></i> <?= $catFail ?> <?= te('fehlgeschlagen') ?></span>
                <?php endif; ?>
                <?php if ($catWarn > 0): ?>
                    <span style="font-size:12px;color:#d97706;"><i class="bi bi-exclamation-triangle-fill"></i> <?= $catWarn ?> <?= te('Warnung') ?></span>
                <?php endif; ?>
                <span style="font-size:13px;font-weight:600;color:<?= $catColor ?>;"><?= $catPct ?>%</span>
            </span>
        </div>
        <div class="card-body-custom p-0">
            <?php foreach ($categoryChecks as $idx => $check):
                $status = $check['status'];
                $rowBg  = $status === 'fail' ? '#fffafa' : ($status === 'warn' ? '#fffdf5' : '');
            ?>
            <div style="display:flex;align-items:flex-start;gap:14px;padding:13px 20px;<?= $idx > 0 ? 'border-top:1px solid #f3f4f6;' : '' ?><?= $rowBg ? 'background:' . $rowBg . ';' : '' ?>">

                <!-- Status icon -->
                <div style="flex-shrink:0;margin-top:1px;">
                    <?php if ($status === 'pass'): ?>
                        <i class="bi bi-check-circle-fill" style="color:#16a34a;font-size:17px;"></i>
                    <?php elseif ($status === 'warn'): ?>
                        <i class="bi bi-exclamation-triangle-fill" style="color:#d97706;font-size:17px;"></i>
                    <?php elseif ($status === 'fail'): ?>
                        <i class="bi bi-x-circle-fill" style="color:#dc2626;font-size:17px;"></i>
                    <?php else: ?>
                        <i class="bi bi-question-circle-fill" style="color:#d1d5db;font-size:17px;"></i>
                    <?php endif; ?>
                </div>

                <!-- Text -->
                <div style="flex:1;min-width:0;">
                    <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-bottom:2px;">
                        <span style="font-size:13px;font-weight:500;color:#111827;"><?= $e($check['label']) ?></span>
                        <?php if ($check['severity'] === 'high'): ?>
                            <span class="badge-danger badge-pill" style="font-size:10px;padding:2px 6px;"><?= te('Hoch') ?></span>
                        <?php elseif ($check['severity'] === 'medium'): ?>
                            <span class="badge-warning badge-pill" style="font-size:10px;padding:2px 6px;"><?= te('Mittel') ?></span>
                        <?php else: ?>
                            <span class="badge-info badge-pill" style="font-size:10px;padding:2px 6px;"><?= te('Niedrig') ?></span>
                        <?php endif; ?>
                    </div>
                    <div style="font-size:12px;color:#6b7280;"><?= $e($check['description']) ?></div>
                    <div style="font-size:12px;color:<?= $status === 'fail' ? '#b91c1c' : ($status === 'warn' ? '#92400e' : '#6b7280') ?>;margin-top:3px;font-style:italic;"><?= $e($check['detail']) ?></div>
                </div>

                <!-- Badge -->
                <div style="flex-shrink:0;">
                    <?php if ($status === 'pass'): ?>
                        <span class="badge-enabled badge-pill"><?= te('Bestanden') ?></span>
                    <?php elseif ($status === 'warn'): ?>
                        <span class="badge-warning badge-pill"><?= te('Warnung') ?></span>
                    <?php elseif ($status === 'fail'): ?>
                        <span class="badge-danger badge-pill"><?= te('Fehlgeschlagen') ?></span>
                    <?php else: ?>
                        <span class="badge-neutral badge-pill"><?= te('Unbekannt') ?></span>
                    <?php endif; ?>
                </div>

            </div>
            <?php endforeach; ?>
        </div>
    </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Hinweis -->
<div style="padding:12px 16px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;font-size:12px;color:#64748b;">
    <i class="bi bi-info-circle me-1"></i>
    <?= t('Prüfungen basieren auf Microsoft Graph API-Daten und Best Practices (CIS M365, Microsoft Security Baseline).
    Fehlende Berechtigungen werden als <strong>Unbekannt</strong> angezeigt.
    Einige Prüfungen nutzen gecachte Daten (5–30 Min). Für aktuelle Ergebnisse:') ?>
    <a href="?refresh=1" style="color:#0078d4;"><?= te('Aktualisieren') ?></a>.
    <?= t('Risikobasierte CA-Richtlinien (Anmelderisiko, Benutzerrisiko) erfordern <strong>Entra ID P2</strong>.') ?>
</div>
