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

<?php if (!$enabled): ?>
<!-- ── Module disabled ─────────────────────────────────────────────────────── -->
<div class="content-card mb-4">
    <div class="card-header-custom">
        <i class="bi bi-robot text-secondary"></i>
        <h6 class="text-muted">KI-Sicherheitsberater</h6>
    </div>
    <div class="card-body-custom text-center py-5">
        <div style="font-size:48px;margin-bottom:16px;">🤖</div>
        <h5 class="mb-2">KI-Sicherheitsberater ist deaktiviert</h5>
        <p class="text-muted mb-4" style="max-width:480px;margin:0 auto;">
            Der KI-Sicherheitsberater analysiert anonymisierte Sicherheitsmetriken Ihres M365-Tenants
            und gibt priorisierte Handlungsempfehlungen.
        </p>
        <a href="/settings#ai-advisor" class="btn btn-primary">
            <i class="bi bi-gear me-2"></i>In den Einstellungen aktivieren
        </a>
    </div>
</div>

<?php else: ?>

<?php
$severityConfig = [
    'critical' => ['color' => '#dc2626', 'bg' => '#fef2f2', 'border' => '#fecaca', 'icon' => 'exclamation-octagon-fill', 'label' => 'Kritisch'],
    'high'     => ['color' => '#ea580c', 'bg' => '#fff7ed', 'border' => '#fed7aa', 'icon' => 'exclamation-triangle-fill', 'label' => 'Hoch'],
    'medium'   => ['color' => '#ca8a04', 'bg' => '#fefce8', 'border' => '#fde68a', 'icon' => 'exclamation-circle-fill',   'label' => 'Mittel'],
    'low'      => ['color' => '#2563eb', 'bg' => '#eff6ff', 'border' => '#bfdbfe', 'icon' => 'info-circle-fill',           'label' => 'Niedrig'],
];

$recs         = $analysis['recommendations'] ?? [];
$summary      = $analysis['summary']         ?? '';
$aiScore      = $analysis['score']           ?? null;
$generatedAt  = $analysis['generated_at']    ?? null;
$cachedAt     = $analysis['cached_at']       ?? null;
$ctx          = $analysis['context']         ?? [];

$criticalCount = count(array_filter($recs, fn($r) => ($r['severity'] ?? '') === 'critical'));
$highCount     = count(array_filter($recs, fn($r) => ($r['severity'] ?? '') === 'high'));

$scoreColor = $aiScore === null ? '#6b7280'
    : ($aiScore >= 75 ? '#16a34a' : ($aiScore >= 50 ? '#d97706' : '#dc2626'));
?>

<?php if ($analysis === null): ?>
<!-- ── No analysis yet ─────────────────────────────────────────────────────── -->
<div class="content-card mb-4" style="border-left:4px solid #0078d4;">
    <div class="card-body-custom">
        <div class="row align-items-center g-4">
            <div class="col-auto text-center" style="min-width:80px;">
                <i class="bi bi-robot" style="font-size:48px;color:#0078d4;"></i>
            </div>
            <div class="col">
                <h5 class="mb-1">Erste KI-Analyse starten</h5>
                <p class="text-muted mb-3" style="max-width:600px;">
                    Der KI-Sicherheitsberater analysiert anonymisierte Metriken aus Security Posture, Benutzer,
                    Lizenzen, Geräten und Freigaben — und gibt priorisierte Handlungsempfehlungen.
                </p>
                <div class="d-flex align-items-center gap-3 flex-wrap">
                    <form method="post" action="/ai/analyze" id="analyzeForm">
                        <button type="submit" class="btn btn-primary" id="analyzeBtn"
                                onclick="startAnalysis()">
                            <i class="bi bi-play-fill me-1"></i>Jetzt analysieren
                        </button>
                    </form>
                    <span class="badge-enabled badge-pill">
                        <i class="bi bi-shield-check me-1"></i>Datenschutz: Keine Benutzerdaten übermittelt
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Privacy info -->
<div class="content-card mb-4">
    <div class="card-header-custom">
        <i class="bi bi-shield-check text-success"></i>
        <h6>Datenschutz-Hinweis</h6>
    </div>
    <div class="card-body-custom">
        <div class="row g-3">
            <div class="col-md-6">
                <h6 class="text-success mb-2"><i class="bi bi-check-circle-fill me-1"></i>Was wird übertragen</h6>
                <ul class="list-unstyled mb-0" style="font-size:13px;color:#374151;">
                    <li class="mb-1"><i class="bi bi-check2 text-success me-1"></i>Anzahl und Prozentwerte (z.B. MFA-Quote)</li>
                    <li class="mb-1"><i class="bi bi-check2 text-success me-1"></i>Generische Check-IDs (z.B. "mfa_registration")</li>
                    <li class="mb-1"><i class="bi bi-check2 text-success me-1"></i>Gerätezahlen (konform / nicht konform)</li>
                    <li class="mb-1"><i class="bi bi-check2 text-success me-1"></i>Freigabe-Anzahl (extern / anonym)</li>
                    <li class="mb-1"><i class="bi bi-check2 text-success me-1"></i>Lizenz-Auslastungskategorien</li>
                </ul>
            </div>
            <div class="col-md-6">
                <h6 class="text-danger mb-2"><i class="bi bi-x-circle-fill me-1"></i>Was NICHT übertragen wird</h6>
                <ul class="list-unstyled mb-0" style="font-size:13px;color:#374151;">
                    <li class="mb-1"><i class="bi bi-x text-danger me-1"></i>Benutzernamen oder UPNs</li>
                    <li class="mb-1"><i class="bi bi-x text-danger me-1"></i>E-Mail-Adressen</li>
                    <li class="mb-1"><i class="bi bi-x text-danger me-1"></i>Tenant-ID oder Domainname</li>
                    <li class="mb-1"><i class="bi bi-x text-danger me-1"></i>Gerätename oder -IDs</li>
                    <li class="mb-1"><i class="bi bi-x text-danger me-1"></i>Dateinamen oder Site-URLs</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php else: ?>
<!-- ── Analysis results ────────────────────────────────────────────────────── -->

<!-- Header banner -->
<div class="content-card mb-4" style="background:linear-gradient(135deg,#0f172a 0%,#1e293b 100%);color:#fff;border:none;">
    <div class="card-body-custom">
        <div class="row align-items-center g-4">

            <!-- Score ring -->
            <?php if ($aiScore !== null): ?>
            <div class="col-auto text-center" style="min-width:140px;">
                <div style="position:relative;display:inline-block;">
                    <svg width="110" height="110" viewBox="0 0 110 110">
                        <circle cx="55" cy="55" r="47" fill="none" stroke="rgba(255,255,255,.1)" stroke-width="9"/>
                        <circle cx="55" cy="55" r="47" fill="none" stroke="<?= $e($scoreColor) ?>" stroke-width="9"
                                stroke-dasharray="<?= round(295 * $aiScore / 100) ?> 295"
                                stroke-linecap="round"
                                transform="rotate(-90 55 55)"/>
                    </svg>
                    <div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);text-align:center;">
                        <div style="font-size:24px;font-weight:700;line-height:1;color:<?= $e($scoreColor) ?>;"><?= (int)$aiScore ?></div>
                        <div style="font-size:9px;color:rgba(255,255,255,.5);margin-top:2px;">/ 100</div>
                    </div>
                </div>
                <div style="font-size:11px;color:rgba(255,255,255,.5);margin-top:4px;">KI-Score</div>
            </div>
            <?php endif; ?>

            <!-- Summary -->
            <div class="col">
                <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px;">
                    <i class="bi bi-robot" style="color:#60a5fa;font-size:18px;"></i>
                    <span style="font-size:16px;font-weight:600;">KI-Sicherheitsanalyse</span>
                    <span style="font-size:11px;background:rgba(34,197,94,.15);color:#4ade80;border:1px solid rgba(34,197,94,.3);border-radius:20px;padding:2px 8px;">
                        <i class="bi bi-shield-check me-1"></i>Datenschutz: Keine Benutzerdaten
                    </span>
                </div>
                <?php if ($summary): ?>
                <p style="font-size:13px;color:rgba(255,255,255,.8);line-height:1.6;margin-bottom:12px;"><?= $e($summary) ?></p>
                <?php endif; ?>
                <div style="display:flex;gap:12px;flex-wrap:wrap;">
                    <?php if ($criticalCount > 0): ?>
                    <span style="font-size:12px;background:rgba(220,38,38,.2);color:#f87171;border:1px solid rgba(220,38,38,.3);border-radius:20px;padding:3px 10px;">
                        <i class="bi bi-exclamation-octagon-fill me-1"></i><?= $criticalCount ?> Kritisch
                    </span>
                    <?php endif; ?>
                    <?php if ($highCount > 0): ?>
                    <span style="font-size:12px;background:rgba(234,88,12,.2);color:#fb923c;border:1px solid rgba(234,88,12,.3);border-radius:20px;padding:3px 10px;">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i><?= $highCount ?> Hoch
                    </span>
                    <?php endif; ?>
                    <span style="font-size:12px;color:rgba(255,255,255,.4);">
                        <?= count($recs) ?> Empfehlung(en) · <?= $e($provider) ?>
                        <?php if ($generatedAt): ?> · <?= $e($generatedAt) ?><?php endif; ?>
                    </span>
                </div>
            </div>

            <!-- Actions -->
            <div class="col-auto d-flex flex-column gap-2">
                <form method="post" action="/ai/analyze" id="analyzeForm">
                    <button type="submit" class="btn btn-sm w-100"
                            style="background:rgba(255,255,255,.15);color:#fff;border:1px solid rgba(255,255,255,.2);"
                            onclick="startAnalysis()">
                        <i class="bi bi-arrow-clockwise me-1"></i>Neu analysieren
                    </button>
                </form>
                <form method="post" action="/ai/clear-cache">
                    <button type="submit" class="btn btn-sm w-100"
                            style="background:rgba(255,255,255,.08);color:rgba(255,255,255,.6);border:1px solid rgba(255,255,255,.1);"
                            onclick="return confirm('Analyse-Cache wirklich löschen?')">
                        <i class="bi bi-trash me-1"></i>Cache leeren
                    </button>
                </form>
            </div>

        </div>
    </div>
</div>

<!-- Recommendations -->
<?php if (!empty($recs)): ?>
<div class="content-card mb-4">
    <div class="card-header-custom">
        <span><i class="bi bi-lightning-charge-fill me-2" style="color:#f59e0b;"></i>Empfehlungen</span>
        <span style="font-size:12px;color:#9ca3af;"><?= count($recs) ?> Maßnahme(n) · sortiert nach Schweregrad</span>
    </div>
    <div class="card-body-custom p-0">
        <?php foreach ($recs as $idx => $rec):
            $sev = $rec['severity'] ?? 'low';
            $sc  = $severityConfig[$sev] ?? $severityConfig['low'];
        ?>
        <div style="display:flex;align-items:flex-start;gap:0;<?= $idx > 0 ? 'border-top:1px solid #f3f4f6;' : '' ?>">

            <!-- Left severity bar -->
            <div style="width:4px;flex-shrink:0;background:<?= $e($sc['color']) ?>;border-radius:<?= $idx === 0 ? '0' : '0' ?>;align-self:stretch;"></div>

            <div style="flex:1;padding:16px 20px;background:<?= $e($sc['bg']) ?>;">
                <div style="display:flex;align-items:flex-start;gap:14px;flex-wrap:wrap;">

                    <!-- Severity icon -->
                    <div style="flex-shrink:0;text-align:center;min-width:52px;">
                        <div style="width:36px;height:36px;border-radius:50%;background:#fff;border:1px solid <?= $e($sc['border']) ?>;display:flex;align-items:center;justify-content:center;margin:0 auto 4px;">
                            <i class="bi bi-<?= $e($sc['icon']) ?>" style="color:<?= $e($sc['color']) ?>;font-size:16px;"></i>
                        </div>
                        <span style="font-size:10px;font-weight:700;color:<?= $e($sc['color']) ?>;text-transform:uppercase;letter-spacing:.5px;"><?= $e($sc['label']) ?></span>
                    </div>

                    <!-- Content -->
                    <div style="flex:1;min-width:200px;">
                        <div style="font-size:14px;font-weight:600;color:#111827;margin-bottom:6px;"><?= $e($rec['title'] ?? '') ?></div>

                        <?php if (!empty($rec['risk'])): ?>
                        <div style="font-size:12px;color:#6b7280;margin-bottom:4px;">
                            <i class="bi bi-exclamation-circle me-1" style="color:<?= $e($sc['color']) ?>;"></i>
                            <strong>Risiko:</strong> <?= $e($rec['risk']) ?>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($rec['action'])): ?>
                        <div style="font-size:12px;color:#374151;">
                            <i class="bi bi-arrow-right-circle me-1 text-primary"></i>
                            <strong>Maßnahme:</strong> <?= $e($rec['action']) ?>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Link buttons -->
                    <div style="flex-shrink:0;display:flex;flex-direction:column;gap:6px;align-items:flex-end;">
                        <?php if (!empty($rec['internal_path'])): ?>
                        <a href="<?= $e($rec['internal_path']) ?>"
                           class="btn btn-sm"
                           style="background:#0078d4;color:#fff;border:none;white-space:nowrap;font-size:12px;">
                            <i class="bi bi-arrow-right me-1"></i>Im Tool öffnen
                        </a>
                        <?php endif; ?>
                        <?php if (!empty($rec['ms_admin_url'])): ?>
                        <a href="<?= $e($rec['ms_admin_url']) ?>" target="_blank" rel="noopener noreferrer"
                           class="btn btn-sm btn-outline-secondary"
                           style="white-space:nowrap;font-size:12px;">
                            <i class="bi bi-box-arrow-up-right me-1"></i>Microsoft Admin
                        </a>
                        <?php endif; ?>
                    </div>

                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php else: ?>
<div class="content-card mb-4">
    <div class="card-body-custom text-center py-4">
        <i class="bi bi-check-circle-fill text-success" style="font-size:36px;"></i>
        <p class="mt-2 mb-0 text-muted">Keine kritischen Empfehlungen — gute Arbeit!</p>
    </div>
</div>
<?php endif; ?>

<!-- Analysierter Kontext (collapsible) -->
<?php if (!empty($ctx)): ?>
<div class="content-card mb-4">
    <div class="card-header-custom" style="cursor:pointer;" onclick="toggleContext()" id="contextToggle">
        <span><i class="bi bi-database me-2 text-secondary"></i>Was wurde analysiert?</span>
        <span style="font-size:12px;color:#9ca3af;">
            Anonymisierte Metriken anzeigen
            <i class="bi bi-chevron-down ms-1" id="contextChevron"></i>
        </span>
    </div>
    <div id="contextSection" style="display:none;">
        <div class="card-body-custom">
            <div class="alert alert-info mb-3" style="font-size:12px;">
                <i class="bi bi-shield-check me-1"></i>
                <strong>Datenschutz bestätigt:</strong> Die folgende Tabelle zeigt exakt, welche Daten an <?= $e($provider) ?> übertragen wurden.
                Es handelt sich ausschließlich um Zahlen und Prozentwerte — keine Benutzernamen, keine Tenant-ID, keine Domainnamen.
            </div>

            <?php if (!empty($ctx['security_posture'])): $sp = $ctx['security_posture']; ?>
            <h6 class="fw-semibold mb-2"><i class="bi bi-shield-fill-check me-1 text-primary"></i>Security Posture</h6>
            <table class="table table-sm mb-4" style="font-size:13px;">
                <tbody>
                    <tr><td class="text-muted">Gesamt Prüfungen</td><td><?= (int)($sp['total'] ?? 0) ?></td></tr>
                    <tr><td class="text-muted">Bestanden</td><td class="text-success"><?= (int)($sp['passed'] ?? 0) ?></td></tr>
                    <tr><td class="text-muted">Warnungen</td><td class="text-warning"><?= (int)($sp['warnings'] ?? 0) ?></td></tr>
                    <tr><td class="text-muted">Fehlgeschlagen</td><td class="text-danger"><?= (int)($sp['failed'] ?? 0) ?></td></tr>
                    <?php if (!empty($sp['failed_checks'])): ?>
                    <tr>
                        <td class="text-muted">Fehlgeschlagene Check-IDs</td>
                        <td><?= implode(', ', array_map(fn($id) => '<code>' . $e($id) . '</code>', $sp['failed_checks'])) ?></td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
            <?php endif; ?>

            <?php if (!empty($ctx['users'])): $u = $ctx['users']; ?>
            <h6 class="fw-semibold mb-2"><i class="bi bi-people me-1 text-primary"></i>Benutzer</h6>
            <table class="table table-sm mb-4" style="font-size:13px;">
                <tbody>
                    <tr><td class="text-muted">Gesamt Benutzer</td><td><?= (int)($u['total'] ?? 0) ?></td></tr>
                    <tr><td class="text-muted">Aktivierte Benutzer</td><td><?= (int)($u['enabled'] ?? 0) ?></td></tr>
                    <tr><td class="text-muted">MFA-Registrierung</td><td><?= (int)($u['mfa_registered_pct'] ?? 0) ?>%</td></tr>
                    <tr><td class="text-muted">Ohne MFA</td><td><?= (int)($u['no_mfa_count'] ?? 0) ?></td></tr>
                    <tr><td class="text-muted">Inaktiv &gt;90 Tage</td><td><?= (int)($u['stale_90d_count'] ?? 0) ?></td></tr>
                    <tr><td class="text-muted">Aktiv ohne Lizenz</td><td><?= (int)($u['enabled_no_license'] ?? 0) ?></td></tr>
                </tbody>
            </table>
            <?php endif; ?>

            <?php if (!empty($ctx['licenses'])): $l = $ctx['licenses']; ?>
            <h6 class="fw-semibold mb-2"><i class="bi bi-award me-1 text-primary"></i>Lizenzen</h6>
            <table class="table table-sm mb-4" style="font-size:13px;">
                <tbody>
                    <tr><td class="text-muted">Anzahl SKUs</td><td><?= (int)($l['sku_count'] ?? 0) ?></td></tr>
                    <tr><td class="text-muted">Hoch ausgelastet (&ge;90%)</td><td><?= (int)($l['high_utilization_skus'] ?? 0) ?></td></tr>
                    <tr><td class="text-muted">Wenig genutzt (&lt;10%)</td><td><?= (int)($l['under_utilized_skus'] ?? 0) ?></td></tr>
                </tbody>
            </table>
            <?php endif; ?>

            <?php if (!empty($ctx['devices'])): $d = $ctx['devices']; ?>
            <h6 class="fw-semibold mb-2"><i class="bi bi-phone me-1 text-primary"></i>Geräte</h6>
            <table class="table table-sm mb-4" style="font-size:13px;">
                <tbody>
                    <tr><td class="text-muted">Gesamt Geräte</td><td><?= (int)($d['total'] ?? 0) ?></td></tr>
                    <tr><td class="text-muted">Konform</td><td class="text-success"><?= (int)($d['compliant'] ?? 0) ?></td></tr>
                    <tr><td class="text-muted">Nicht konform</td><td class="text-danger"><?= (int)($d['non_compliant'] ?? 0) ?></td></tr>
                    <tr><td class="text-muted">Compliance-Quote</td><td><?= (int)($d['compliant_pct'] ?? 0) ?>%</td></tr>
                </tbody>
            </table>
            <?php endif; ?>

            <?php if (!empty($ctx['sharing'])): $sh = $ctx['sharing']; ?>
            <h6 class="fw-semibold mb-2"><i class="bi bi-link-45deg me-1 text-primary"></i>Freigaben</h6>
            <table class="table table-sm mb-4" style="font-size:13px;">
                <tbody>
                    <tr><td class="text-muted">Aktive externe Freigaben</td><td><?= (int)($sh['external_count'] ?? 0) ?></td></tr>
                    <tr><td class="text-muted">Davon anonym (Anyone-Links)</td><td><?= (int)($sh['anonymous_count'] ?? 0) ?></td></tr>
                </tbody>
            </table>
            <?php endif; ?>

        </div>
    </div>
</div>
<?php endif; ?>

<!-- Footer note -->
<div style="padding:10px 14px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;font-size:12px;color:#64748b;">
    <i class="bi bi-robot me-1"></i>
    Analyse läuft über <strong><?= $e($provider) ?></strong> —
    es wurden keine Benutzer- oder Tenant-Daten übermittelt.
    <?php if ($cachedAt): ?>
    Letzte Analyse: <?= $e($cachedAt) ?>.
    <?php endif; ?>
    <a href="/settings#ai-advisor" class="ms-2">Einstellungen</a>
</div>

<?php endif; // analysis !== null ?>

<?php endif; // enabled ?>

<!-- Analyse-Spinner (hidden by default) -->
<div id="analyzeSpinner" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,.4);z-index:9999;display:none;align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:12px;padding:32px 48px;text-align:center;box-shadow:0 20px 60px rgba(0,0,0,.3);">
        <div class="spinner-border text-primary mb-3" role="status" style="width:40px;height:40px;"></div>
        <div style="font-size:16px;font-weight:600;color:#111827;">Analyse läuft…</div>
        <div style="font-size:13px;color:#6b7280;margin-top:4px;">Dies kann bis zu 90 Sekunden dauern.</div>
    </div>
</div>

<script>
function startAnalysis() {
    var spinner = document.getElementById('analyzeSpinner');
    if (spinner) {
        spinner.style.display = 'flex';
    }
    var btn = document.getElementById('analyzeBtn');
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Analyse läuft…';
    }
}

document.addEventListener('DOMContentLoaded', function () {
    var form = document.getElementById('analyzeForm');
    if (form) {
        form.addEventListener('submit', function () {
            startAnalysis();
        });
    }
});

function toggleContext() {
    var section  = document.getElementById('contextSection');
    var chevron  = document.getElementById('contextChevron');
    if (!section) return;
    if (section.style.display === 'none') {
        section.style.display = 'block';
        if (chevron) chevron.className = 'bi bi-chevron-up ms-1';
    } else {
        section.style.display = 'none';
        if (chevron) chevron.className = 'bi bi-chevron-down ms-1';
    }
}
</script>
