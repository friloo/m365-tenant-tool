<?php use App\Core\View; $e = fn($v) => View::escape($v); ?>

<?php if ($flash): ?>
    <div class="alert alert-success alert-dismissible mb-4">
        <i class="bi bi-check-circle me-2"></i><?= $e($flash) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible mb-4">
        <i class="bi bi-exclamation-triangle me-2"></i><?= $e($error) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php
// ── Page header row ─────────────────────────────────────────────────────────
?>
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
    <div class="d-flex align-items-center gap-3">
        <div>
            <h4 class="mb-0 fw-bold">KI-Sicherheitsberater</h4>
            <div class="text-muted small mt-1">
                Konkrete Handlungsempfehlungen auf Basis von Microsoft Best Practices
            </div>
        </div>
        <?php if ($enabled): ?>
            <span class="badge rounded-pill text-bg-primary" style="font-size:11px;">
                <i class="bi bi-robot me-1"></i><?= $e($provider) ?>
            </span>
        <?php endif; ?>
    </div>
    <?php if ($enabled): ?>
    <div class="d-flex gap-2">
        <form method="post" action="/ai/analyze" id="analyzeFormTop">
            <?= \App\Core\Csrf::field() ?>
            <button type="submit" class="btn btn-primary btn-sm" id="analyzeBtnTop"
                    onclick="startAnalysis(this)">
                <i class="bi bi-arrow-clockwise me-1"></i>Analyse aktualisieren
            </button>
        </form>
        <?php if ((\App\Auth\LocalAuth::isAdmin())): ?>
        <form method="post" action="/ai/clear-cache"
              onsubmit="return confirm('Analyse-Cache wirklich löschen?')">
            <?= \App\Core\Csrf::field() ?>
            <button type="submit" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-trash me-1"></i>Cache leeren
            </button>
        </form>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<?php
// ── KI disabled ─────────────────────────────────────────────────────────────
if (!$enabled):
?>
<div class="alert alert-info d-flex align-items-start gap-3">
    <i class="bi bi-info-circle-fill flex-shrink-0 mt-1" style="font-size:20px;"></i>
    <div>
        <div class="fw-semibold mb-1">KI-Berater ist deaktiviert</div>
        Aktiviere ihn unter <a href="/settings#ai-advisor" class="alert-link">Einstellungen → KI-Sicherheitsberater</a>.
    </div>
</div>
<?php return; // nothing else to show ?>
<?php endif; ?>

<?php
// ── Enabled but no analysis yet ─────────────────────────────────────────────
if ($analysis === null):
?>
<div class="content-card mb-4" style="border-left:4px solid #0078d4;">
    <div class="card-body-custom">
        <div class="row align-items-center g-4">
            <div class="col-auto text-center" style="min-width:80px;">
                <i class="bi bi-robot" style="font-size:48px;color:#0078d4;"></i>
            </div>
            <div class="col">
                <h5 class="mb-1">Erste Analyse starten</h5>
                <p class="text-muted mb-3" style="max-width:620px;">
                    Der KI-Sicherheitsberater analysiert anonymisierte Metriken aus Security Posture, Benutzer,
                    Lizenzen, Geräten und Freigaben und liefert sofort umsetzbare, konkrete Handlungsempfehlungen.
                </p>
                <div class="alert alert-success d-flex align-items-start gap-2 mb-3" style="max-width:620px;">
                    <i class="bi bi-shield-check flex-shrink-0 mt-1"></i>
                    <div style="font-size:13px;">
                        <strong>Datenschutz:</strong> Es werden ausschließlich anonymisierte Metriken (Zahlen &amp; Prozentsätze) übertragen.
                        Keine Benutzernamen, UPNs, Tenant-IDs oder Domainnamen.
                    </div>
                </div>
                <form method="post" action="/ai/analyze" id="analyzeForm">
                    <?= \App\Core\Csrf::field() ?>
                    <button type="submit" class="btn btn-primary" id="analyzeBtn"
                            onclick="startAnalysis(this)">
                        <i class="bi bi-play-fill me-1"></i>Analyse starten
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php return; ?>
<?php endif; ?>

<?php
// ── Analysis is available ────────────────────────────────────────────────────
$severityConfig = [
    'critical' => ['color' => '#dc2626', 'bg' => '#fef2f2', 'border' => '#fecaca', 'label' => 'Kritisch',  'icon' => 'exclamation-octagon-fill'],
    'high'     => ['color' => '#ea580c', 'bg' => '#fff7ed', 'border' => '#fed7aa', 'label' => 'Hoch',      'icon' => 'exclamation-triangle-fill'],
    'medium'   => ['color' => '#ca8a04', 'bg' => '#fefce8', 'border' => '#fde68a', 'label' => 'Mittel',    'icon' => 'exclamation-circle-fill'],
    'low'      => ['color' => '#2563eb', 'bg' => '#eff6ff', 'border' => '#bfdbfe', 'label' => 'Niedrig',   'icon' => 'info-circle-fill'],
];

$recs          = $analysis['recommendations'] ?? [];
$summary       = $analysis['summary']         ?? null;
$aiScore       = $analysis['score']           ?? null;
$generatedAt   = $analysis['generated_at']    ?? null;
$cachedAt      = $analysis['cached_at']       ?? null;
$cacheExpires  = $analysis['expires_at']      ?? null;
$isStale       = !empty($analysis['is_stale']);
$ctx           = $analysis['context']         ?? [];

$criticalCount = count(array_filter($recs, fn($r) => ($r['severity'] ?? '') === 'critical'));
$highCount     = count(array_filter($recs, fn($r) => ($r['severity'] ?? '') === 'high'));
$mediumCount   = count(array_filter($recs, fn($r) => ($r['severity'] ?? '') === 'medium'));
$lowCount      = count(array_filter($recs, fn($r) => ($r['severity'] ?? '') === 'low'));

// Score color: <40 red, 40-69 orange, 70-89 yellow, >=90 green
if ($aiScore === null) {
    $scoreColor = '#6b7280';
} elseif ($aiScore >= 90) {
    $scoreColor = '#16a34a';
} elseif ($aiScore >= 70) {
    $scoreColor = '#ca8a04';
} elseif ($aiScore >= 40) {
    $scoreColor = '#ea580c';
} else {
    $scoreColor = '#dc2626';
}
?>

<?php if ($isStale && $cachedAt): ?>
<div class="alert alert-warning d-flex align-items-center gap-2 mb-3" role="alert">
    <i class="bi bi-clock-history flex-shrink-0"></i>
    <div>
        <strong>Ergebnis ist veraltet</strong> — letzte Analyse vom
        <?= $e(date('d.m.Y H:i', strtotime($cachedAt))) ?>. Die Daten werden weiterhin angezeigt,
        bis du eine neue Analyse startest.
    </div>
</div>
<?php endif; ?>

<!-- ── Summary row (2 cols) ──────────────────────────────────────────────── -->
<div class="row g-4 mb-4">

    <!-- Left: AI Summary -->
    <div class="col-lg-8">
        <div class="content-card h-100">
            <div class="card-header-custom">
                <span><i class="bi bi-robot me-2 text-primary"></i>KI-Zusammenfassung</span>
                <?php if ($summary === null): ?>
                    <span class="badge text-bg-secondary" style="font-size:11px;">Nicht verfügbar</span>
                <?php else: ?>
                    <span class="badge text-bg-success" style="font-size:11px;">
                        <i class="bi bi-shield-check me-1"></i>Keine Benutzerdaten übermittelt
                    </span>
                <?php endif; ?>
            </div>
            <div class="card-body-custom">
                <?php if ($summary): ?>
                    <p class="mb-0" style="font-size:14px;line-height:1.7;color:#374151;"><?= $e($summary) ?></p>
                <?php else: ?>
                    <p class="text-muted mb-0" style="font-size:13px;">
                        <i class="bi bi-info-circle me-1"></i>
                        KI-Zusammenfassung nicht verfügbar.
                        <?php if (!$this ?? false): // always false in view context ?>
                        <?php endif; ?>
                        Die konkreten Empfehlungen unten wurden aus der Best-Practice-Bibliothek ermittelt und sind unabhängig von der KI verfügbar.
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Right: Score gauge + meta -->
    <div class="col-lg-4">
        <div class="content-card h-100">
            <div class="card-header-custom">
                <span><i class="bi bi-speedometer2 me-2 text-primary"></i>Sicherheits-Score</span>
            </div>
            <div class="card-body-custom text-center">
                <?php if ($aiScore !== null): ?>
                <div style="display:inline-block;position:relative;margin-bottom:8px;">
                    <svg width="120" height="120" viewBox="0 0 120 120">
                        <circle cx="60" cy="60" r="50" fill="none" stroke="#e5e7eb" stroke-width="10"/>
                        <circle cx="60" cy="60" r="50" fill="none"
                                stroke="<?= $e($scoreColor) ?>" stroke-width="10"
                                stroke-dasharray="<?= round(314 * $aiScore / 100) ?> 314"
                                stroke-linecap="round"
                                transform="rotate(-90 60 60)"/>
                    </svg>
                    <div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);text-align:center;">
                        <div style="font-size:28px;font-weight:700;color:<?= $e($scoreColor) ?>;line-height:1;">
                            <?= (int)$aiScore ?>
                        </div>
                        <div style="font-size:11px;color:#9ca3af;">/&nbsp;100</div>
                    </div>
                </div>
                <?php else: ?>
                <div class="text-muted py-3">
                    <i class="bi bi-dash-circle" style="font-size:36px;"></i>
                    <div class="mt-2 small">Score nicht verfügbar<br>(KI deaktiviert oder Fehler)</div>
                </div>
                <?php endif; ?>

                <div class="mt-3">
                    <span class="badge text-bg-success mb-2" style="font-size:11px;">
                        <i class="bi bi-shield-check me-1"></i>Keine Benutzerdaten übermittelt
                    </span>
                </div>
                <?php if ($generatedAt || $cachedAt): ?>
                <div class="text-muted small mt-1">
                    <i class="bi bi-clock me-1"></i>Analysiert:
                    <?= $e($cachedAt ?? $generatedAt) ?>
                </div>
                <?php endif; ?>
                <div class="mt-1">
                    <span class="badge text-bg-light border text-dark" style="font-size:11px;">
                        <i class="bi bi-robot me-1"></i><?= $e($provider) ?>
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ── Stats row (4 metric cards) ────────────────────────────────────────── -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="content-card text-center" style="padding:16px;">
            <div style="font-size:28px;font-weight:700;color:#111827;"><?= count($recs) ?></div>
            <div class="text-muted small mt-1">Empfehlungen gesamt</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="content-card text-center" style="padding:16px;">
            <div style="font-size:28px;font-weight:700;color:#dc2626;"><?= $criticalCount ?></div>
            <div class="text-muted small mt-1">Kritisch</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="content-card text-center" style="padding:16px;">
            <div style="font-size:28px;font-weight:700;color:#ea580c;"><?= $highCount ?></div>
            <div class="text-muted small mt-1">Hoch</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="content-card text-center" style="padding:16px;">
            <div style="font-size:28px;font-weight:700;color:#ca8a04;"><?= $mediumCount ?></div>
            <div class="text-muted small mt-1">Mittel</div>
        </div>
    </div>
</div>

<!-- ── Recommendations list ───────────────────────────────────────────────── -->
<?php if (!empty($recs)): ?>
<?php
// Group by severity
$grouped = [];
foreach ($recs as $rec) {
    $sev = $rec['severity'] ?? 'low';
    $grouped[$sev][] = $rec;
}
$sevOrder = ['critical', 'high', 'medium', 'low'];
?>
<div class="content-card mb-4">
    <div class="card-header-custom">
        <span>
            <i class="bi bi-lightning-charge-fill me-2" style="color:#f59e0b;"></i>
            Empfehlungen
        </span>
        <span style="font-size:12px;color:#9ca3af;"><?= count($recs) ?> Maßnahme(n) · sortiert nach Schweregrad</span>
    </div>
    <div class="card-body-custom p-0">
        <?php foreach ($sevOrder as $sev):
            if (empty($grouped[$sev])) continue;
            $sc = $severityConfig[$sev];
        ?>
        <!-- Severity section header -->
        <div style="padding:10px 20px;background:<?= $e($sc['bg']) ?>;border-bottom:1px solid <?= $e($sc['border']) ?>;display:flex;align-items:center;gap:8px;">
            <i class="bi bi-<?= $e($sc['icon']) ?>" style="color:<?= $e($sc['color']) ?>;"></i>
            <span style="font-size:12px;font-weight:700;color:<?= $e($sc['color']) ?>;text-transform:uppercase;letter-spacing:.5px;">
                <?= $e($sc['label']) ?> (<?= count($grouped[$sev]) ?>)
            </span>
        </div>

        <?php foreach ($grouped[$sev] as $recIdx => $rec): ?>
        <div style="display:flex;align-items:stretch;border-bottom:1px solid #f3f4f6;">
            <!-- Severity bar -->
            <div style="width:4px;flex-shrink:0;background:<?= $e($sc['color']) ?>;"></div>
            <!-- Content -->
            <div style="flex:1;padding:18px 20px;background:<?= $e($sc['bg']) ?>;">
                <!-- Title row -->
                <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                    <span class="badge" style="background:<?= $e($sc['color']) ?>;color:#fff;font-size:11px;">
                        <?= $e($sc['label']) ?>
                    </span>
                    <span style="font-size:15px;font-weight:600;color:#111827;"><?= $e($rec['title'] ?? '') ?></span>
                </div>

                <?php if (!empty($rec['risk'])): ?>
                <div class="mb-3" style="font-size:13px;color:#6b7280;">
                    <i class="bi bi-exclamation-circle me-1" style="color:<?= $e($sc['color']) ?>;"></i>
                    <strong>Risiko:</strong> <?= $e($rec['risk']) ?>
                </div>
                <?php endif; ?>

                <?php if (!empty($rec['steps'])): ?>
                <div class="mb-3">
                    <div style="font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:.4px;color:#6b7280;margin-bottom:6px;">
                        <i class="bi bi-list-ol me-1"></i>Maßnahmen
                    </div>
                    <ol style="margin:0;padding-left:20px;font-size:13px;color:#374151;line-height:1.8;">
                        <?php foreach ($rec['steps'] as $step): ?>
                            <li><?= $e($step) ?></li>
                        <?php endforeach; ?>
                    </ol>
                </div>
                <?php endif; ?>

                <!-- Action buttons -->
                <div class="d-flex gap-2 flex-wrap mt-2">
                    <?php if (!empty($rec['internal_path'])): ?>
                    <a href="<?= $e($rec['internal_path']) ?>"
                       class="btn btn-sm btn-primary" style="font-size:12px;">
                        <i class="bi bi-arrow-right me-1"></i>Zum Modul
                    </a>
                    <?php endif; ?>
                    <?php if (!empty($rec['ms_doc_url'])): ?>
                    <a href="<?= $e($rec['ms_doc_url']) ?>" target="_blank" rel="noopener noreferrer"
                       class="btn btn-sm btn-outline-secondary" style="font-size:12px;">
                        <i class="bi bi-book me-1"></i>Microsoft Docs
                    </a>
                    <?php endif; ?>
                    <?php if (!empty($rec['ms_admin_url'])): ?>
                    <a href="<?= $e($rec['ms_admin_url']) ?>" target="_blank" rel="noopener noreferrer"
                       class="btn btn-sm btn-outline-dark" style="font-size:12px;">
                        <i class="bi bi-box-arrow-up-right me-1"></i>Admin Center öffnen
                    </a>
                    <?php endif; ?>
                </div>

                <?php if (!empty($rec['bsi_controls']) || !empty($rec['nis2_articles'])): ?>
                <div class="mt-2 d-flex flex-wrap gap-1">
                    <?php foreach ($rec['bsi_controls'] ?? [] as $ctrl): ?>
                        <span style="font-size:10px;padding:2px 6px;border-radius:3px;background:#f0fdf4;color:#166534;border:1px solid #bbf7d0;font-weight:500;">
                            BSI <?= htmlspecialchars($ctrl) ?>
                        </span>
                    <?php endforeach; ?>
                    <?php foreach ($rec['nis2_articles'] ?? [] as $art): ?>
                        <span style="font-size:10px;padding:2px 6px;border-radius:3px;background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe;font-weight:500;">
                            NIS-2 <?= htmlspecialchars($art) ?>
                        </span>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>

        <?php endforeach; ?>
    </div>
</div>

<?php else: ?>
<div class="content-card mb-4">
    <div class="card-body-custom text-center py-5">
        <i class="bi bi-check-circle-fill text-success" style="font-size:40px;"></i>
        <h5 class="mt-3 mb-1">Keine Empfehlungen</h5>
        <p class="text-muted mb-0">Alle geprüften Sicherheitskontrollen sind bestanden. Gut gemacht!</p>
    </div>
</div>
<?php endif; ?>

<!-- ── "Was wurde analysiert?" collapsible ───────────────────────────────── -->
<?php if (!empty($ctx)): ?>
<div class="content-card mb-4">
    <div class="card-header-custom" style="cursor:pointer;" data-bs-toggle="collapse" data-bs-target="#contextSection">
        <span><i class="bi bi-database me-2 text-secondary"></i>Was wurde analysiert?</span>
        <span style="font-size:12px;color:#9ca3af;">
            Anonymisierte Metriken anzeigen
            <i class="bi bi-chevron-down ms-1"></i>
        </span>
    </div>
    <div id="contextSection" class="collapse">
        <div class="card-body-custom">
            <div class="alert alert-info mb-3" style="font-size:12px;">
                <i class="bi bi-shield-check me-1"></i>
                <strong>Datenschutz bestätigt:</strong>
                Die folgende Tabelle zeigt exakt, welche Daten an <?= $e($provider) ?> übertragen wurden.
                Es handelt sich ausschließlich um Zahlen und Prozentwerte — keine Benutzernamen, keine Tenant-ID, keine Domainnamen.
            </div>
            <div class="alert alert-success mb-3" style="font-size:12px;">
                <i class="bi bi-lock me-1"></i>
                <strong>Hinweis:</strong>
                Keine Compliance-Daten (BSI/NIS-2) werden an die KI übertragen — diese Zuordnung erfolgt lokal im Tool.
            </div>

            <table class="table table-sm" style="font-size:13px;">
                <thead class="table-light">
                    <tr>
                        <th>Metrik</th>
                        <th>Wert</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($ctx['security_posture'])): $sp = $ctx['security_posture']; ?>
                    <tr><td colspan="2" class="fw-semibold text-muted small" style="padding-top:10px;background:#f9fafb;">
                        <i class="bi bi-shield-fill-check me-1 text-primary"></i>Sicherheitsprüfungen
                    </td></tr>
                    <tr>
                        <td class="text-muted">Bestanden / Gesamt</td>
                        <td><?= (int)($sp['passed'] ?? 0) ?> / <?= (int)($sp['total'] ?? 0) ?> Checks</td>
                    </tr>
                    <?php endif; ?>

                    <?php if (!empty($ctx['users'])): $u = $ctx['users']; ?>
                    <tr><td colspan="2" class="fw-semibold text-muted small" style="padding-top:10px;background:#f9fafb;">
                        <i class="bi bi-people me-1 text-primary"></i>Benutzer
                    </td></tr>
                    <tr><td class="text-muted">Benutzer gesamt</td><td><?= (int)($u['total'] ?? 0) ?></td></tr>
                    <tr><td class="text-muted">MFA-Quote</td><td><?= (int)($u['mfa_registered_pct'] ?? 0) ?> %</td></tr>
                    <tr><td class="text-muted">Inaktive Konten (&gt;90 Tage)</td><td><?= (int)($u['stale_90d_count'] ?? 0) ?></td></tr>
                    <tr><td class="text-muted">Aktiviert ohne Lizenz</td><td><?= (int)($u['enabled_no_license'] ?? 0) ?></td></tr>
                    <?php endif; ?>

                    <?php if (!empty($ctx['sharing'])): $sh = $ctx['sharing']; ?>
                    <tr><td colspan="2" class="fw-semibold text-muted small" style="padding-top:10px;background:#f9fafb;">
                        <i class="bi bi-link-45deg me-1 text-primary"></i>Freigaben
                    </td></tr>
                    <tr><td class="text-muted">Externe Freigaben</td><td><?= (int)($sh['external_count'] ?? 0) ?></td></tr>
                    <tr><td class="text-muted">Anonyme Freigaben</td><td><?= (int)($sh['anonymous_count'] ?? 0) ?></td></tr>
                    <?php endif; ?>

                    <?php if (!empty($ctx['devices'])): $d = $ctx['devices']; ?>
                    <tr><td colspan="2" class="fw-semibold text-muted small" style="padding-top:10px;background:#f9fafb;">
                        <i class="bi bi-phone me-1 text-primary"></i>Geräte
                    </td></tr>
                    <tr><td class="text-muted">Geräte konform</td><td><?= (int)($d['compliant_pct'] ?? 0) ?> % (<?= (int)($d['compliant'] ?? 0) ?> / <?= (int)($d['total'] ?? 0) ?>)</td></tr>
                    <?php endif; ?>

                    <?php if (!empty($ctx['licenses'])): $l = $ctx['licenses']; ?>
                    <tr><td colspan="2" class="fw-semibold text-muted small" style="padding-top:10px;background:#f9fafb;">
                        <i class="bi bi-award me-1 text-primary"></i>Lizenzen
                    </td></tr>
                    <tr><td class="text-muted">Lizenzen &gt;90 % ausgelastet</td><td><?= (int)($l['high_utilization_skus'] ?? 0) ?> SKUs</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <div class="text-muted small mt-2">
                <i class="bi bi-shield-lock me-1"></i>
                Diese Daten wurden an <strong><?= $e($provider) ?></strong> übertragen.
                Kein Bezug zu einzelnen Benutzern oder dem Tenant möglich.
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- ── Footer note ───────────────────────────────────────────────────────── -->
<div style="padding:10px 14px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;font-size:12px;color:#64748b;">
    <i class="bi bi-info-circle me-1"></i>
    Empfehlungen basieren auf Microsoft Best Practices, BSI IT-Grundschutz Kompendium 2023 und NIS-2-Richtlinie (EU 2022/2555).
    KI-Zusammenfassung durch <strong><?= $e($provider) ?></strong>.
    <a href="/settings#ai-advisor" class="ms-2">Einstellungen</a>
</div>

<!-- ── Spinner overlay ───────────────────────────────────────────────────── -->
<div id="analyzeSpinner"
     style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,.45);z-index:9999;align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:12px;padding:32px 48px;text-align:center;box-shadow:0 20px 60px rgba(0,0,0,.3);max-width:420px;">
        <div class="spinner-border text-primary mb-3" role="status" style="width:40px;height:40px;"></div>
        <div style="font-size:16px;font-weight:600;color:#111827;">Analyse läuft…</div>
        <div id="analyzeHint" style="font-size:13px;color:#6b7280;margin-top:8px;line-height:1.5;">
            Dies kann 1–3&nbsp;Minuten dauern. Du kannst diese Seite verlassen — die Analyse läuft im Hintergrund weiter und das Ergebnis ist beim nächsten Aufruf da.
        </div>
        <div id="analyzeElapsed" style="font-size:12px;color:#9ca3af;margin-top:8px;"></div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const spinner  = document.getElementById('analyzeSpinner');
    const elapsed  = document.getElementById('analyzeElapsed');
    const hint     = document.getElementById('analyzeHint');

    function showSpinner(btn) {
        spinner.style.display = 'flex';
        if (btn) {
            btn.disabled  = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Läuft…';
        }
        const start = Date.now();
        elapsed.textContent = '0 s vergangen';
        const tick = setInterval(() => {
            const s = Math.floor((Date.now() - start) / 1000);
            elapsed.textContent = s + ' s vergangen';
            if (s > 90)  hint.textContent = 'Großer Tenant — die Analyse braucht etwas länger. Bitte Geduld.';
            if (s > 180) hint.textContent = 'Sehr großer Tenant. Du kannst die Seite zur Zwischenzeit schließen — die Daten werden gespeichert, wenn die Analyse fertig ist.';
        }, 1000);
        return tick;
    }

    ['analyzeForm', 'analyzeFormTop'].forEach(function (id) {
        const form = document.getElementById(id);
        if (!form) return;
        form.addEventListener('submit', function (ev) {
            ev.preventDefault();
            const btn  = form.querySelector('button[type="submit"]');
            const tick = showSpinner(btn);
            const data = new FormData(form);
            fetch(form.action, {
                method:  'POST',
                body:    data,
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                credentials: 'same-origin',
                keepalive: true,                          // browser keeps the request alive even if tab closes
            })
            .then(r => r.json().catch(() => ({ ok: r.ok })))
            .then(res => {
                clearInterval(tick);
                if (res && res.ok) {
                    window.location.href = '/ai';         // reload to render fresh result
                } else {
                    spinner.style.display = 'none';
                    alert('Analyse fehlgeschlagen: ' + (res && res.error ? res.error : 'unbekannter Fehler'));
                    if (btn) { btn.disabled = false; btn.textContent = 'Erneut versuchen'; }
                }
            })
            .catch(err => {
                clearInterval(tick);
                // Network error / browser-side timeout. Server probably still
                // running thanks to ignore_user_abort — try a soft reload after
                // a short delay so the user lands on the freshly cached page.
                hint.textContent = 'Verbindung unterbrochen — lade Ergebnis nach…';
                setTimeout(() => window.location.href = '/ai', 5000);
            });
        });
    });
});
</script>
