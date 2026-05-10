<?php use App\Core\View; $e = fn($v) => View::escape($v); ?>

<?php
// Score color
$pct = $score['percent'] ?? 0;
$scoreColor = $pct >= 75 ? '#16a34a' : ($pct >= 50 ? '#d97706' : '#dc2626');

// Check counts by status
$passCnt    = count(array_filter($checks, fn($c) => $c['status'] === 'pass'));
$warnCnt    = count(array_filter($checks, fn($c) => $c['status'] === 'warn'));
$failCnt    = count(array_filter($checks, fn($c) => $c['status'] === 'fail'));
$unknownCnt = count(array_filter($checks, fn($c) => $c['status'] === 'unknown'));
?>

<!-- Score Overview Card -->
<div class="content-card mb-4">
    <div class="card-header-custom">
        <span><i class="bi bi-shield-check me-2"></i>Security Posture Score</span>
        <a href="?refresh=1" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-clockwise me-1"></i>Aktualisieren
        </a>
    </div>
    <div class="card-body-custom">
        <div class="row align-items-center g-4">
            <!-- Big score number -->
            <div class="col-auto text-center" style="min-width:140px;">
                <div style="font-size:56px;font-weight:700;line-height:1;color:<?= $scoreColor ?>;">
                    <?= $pct ?>%
                </div>
                <div style="font-size:13px;color:#6b7280;margin-top:4px;">
                    <?= $score['passed'] ?> von <?= $score['total'] ?> Prüfungen bestanden
                </div>
            </div>
            <!-- Status counters -->
            <div class="col">
                <div class="row g-2">
                    <div class="col-6 col-sm-3">
                        <div class="metric-card" style="background:#f0fdf4;">
                            <div class="metric-label" style="color:#16a34a;">Bestanden</div>
                            <div class="metric-value" style="color:#16a34a;"><?= $passCnt ?></div>
                        </div>
                    </div>
                    <div class="col-6 col-sm-3">
                        <div class="metric-card" style="background:#fffbeb;">
                            <div class="metric-label" style="color:#d97706;">Warnung</div>
                            <div class="metric-value" style="color:#d97706;"><?= $warnCnt ?></div>
                        </div>
                    </div>
                    <div class="col-6 col-sm-3">
                        <div class="metric-card" style="background:#fef2f2;">
                            <div class="metric-label" style="color:#dc2626;">Fehlgeschlagen</div>
                            <div class="metric-value" style="color:#dc2626;"><?= $failCnt ?></div>
                        </div>
                    </div>
                    <div class="col-6 col-sm-3">
                        <div class="metric-card" style="background:#f9fafb;">
                            <div class="metric-label" style="color:#9ca3af;">Unbekannt</div>
                            <div class="metric-value" style="color:#9ca3af;"><?= $unknownCnt ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Progress bar -->
        <div class="mt-3">
            <div class="progress-custom" style="height:10px;border-radius:5px;background:#e5e7eb;">
                <div style="width:<?= $pct ?>%;height:100%;border-radius:5px;background:<?= $scoreColor ?>;transition:width .4s ease;"></div>
            </div>
        </div>
    </div>
</div>

<!-- Checks grouped by category -->
<?php foreach ($byCategory as $category => $categoryChecks): ?>
<div class="content-card mb-3">
    <div class="card-header-custom">
        <span>
            <?php if ($category === 'Identität'): ?>
                <i class="bi bi-person-lock me-2"></i>
            <?php elseif ($category === 'Geräte'): ?>
                <i class="bi bi-laptop me-2"></i>
            <?php elseif ($category === 'Daten'): ?>
                <i class="bi bi-database-lock me-2"></i>
            <?php elseif ($category === 'Apps'): ?>
                <i class="bi bi-app-indicator me-2"></i>
            <?php else: ?>
                <i class="bi bi-shield me-2"></i>
            <?php endif; ?>
            <?= $e($category) ?>
        </span>
        <span style="font-size:12px;color:#9ca3af;"><?= count($categoryChecks) ?> Prüfungen</span>
    </div>
    <div class="card-body-custom p-0">
        <?php foreach ($categoryChecks as $idx => $check):
            $status = $check['status'];
        ?>
        <div style="display:flex;align-items:flex-start;gap:12px;padding:14px 20px;<?= $idx > 0 ? 'border-top:1px solid #f3f4f6;' : '' ?>">

            <!-- Status icon -->
            <div style="flex-shrink:0;margin-top:2px;">
                <?php if ($status === 'pass'): ?>
                    <span style="color:#16a34a;font-size:18px;"><i class="bi bi-check-circle-fill"></i></span>
                <?php elseif ($status === 'warn'): ?>
                    <span style="color:#d97706;font-size:18px;"><i class="bi bi-exclamation-triangle-fill"></i></span>
                <?php elseif ($status === 'fail'): ?>
                    <span style="color:#dc2626;font-size:18px;"><i class="bi bi-x-circle-fill"></i></span>
                <?php else: ?>
                    <span style="color:#9ca3af;font-size:18px;"><i class="bi bi-question-circle-fill"></i></span>
                <?php endif; ?>
            </div>

            <!-- Label + description + detail -->
            <div style="flex:1;min-width:0;">
                <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                    <span style="font-size:14px;font-weight:500;color:#111827;"><?= $e($check['label']) ?></span>
                    <!-- Severity badge -->
                    <?php if ($check['severity'] === 'high'): ?>
                        <span class="badge-danger badge-pill" style="font-size:10px;">Hoch</span>
                    <?php elseif ($check['severity'] === 'medium'): ?>
                        <span class="badge-warning badge-pill" style="font-size:10px;">Mittel</span>
                    <?php else: ?>
                        <span class="badge-info badge-pill" style="font-size:10px;">Niedrig</span>
                    <?php endif; ?>
                </div>
                <div style="font-size:12px;color:#6b7280;margin-top:2px;"><?= $e($check['description']) ?></div>
                <div style="font-size:12px;color:#9ca3af;margin-top:4px;font-style:italic;"><?= $e($check['detail']) ?></div>
            </div>

            <!-- Status label (right-aligned) -->
            <div style="flex-shrink:0;text-align:right;">
                <?php if ($status === 'pass'): ?>
                    <span class="badge-success badge-pill">Bestanden</span>
                <?php elseif ($status === 'warn'): ?>
                    <span class="badge-warning badge-pill">Warnung</span>
                <?php elseif ($status === 'fail'): ?>
                    <span class="badge-danger badge-pill">Fehlgeschlagen</span>
                <?php else: ?>
                    <span class="badge-neutral badge-pill">Unbekannt</span>
                <?php endif; ?>
            </div>

        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endforeach; ?>

<!-- Info box -->
<div class="content-card mt-2" style="border-left:4px solid #3b82f6;">
    <div class="card-body-custom">
        <div style="display:flex;align-items:flex-start;gap:10px;">
            <i class="bi bi-info-circle-fill" style="color:#3b82f6;font-size:18px;flex-shrink:0;margin-top:1px;"></i>
            <p style="font-size:13px;color:#374151;margin:0;">
                Diese Prüfungen geben einen Überblick und ersetzen keine vollständige Sicherheitsanalyse.
                Fehlende Berechtigungen werden als <strong>Unbekannt</strong> angezeigt.
                Einige Prüfungen verwenden gecachte Daten (5–30 Minuten). Für aktuelle Ergebnisse
                <a href="?refresh=1">Seite aktualisieren</a>.
            </p>
        </div>
    </div>
</div>
