<?php use App\Core\View; $e = fn($v) => View::escape($v); ?>

<?php if (!empty($diag)) include BASE_PATH . '/views/partials/graph_diagnostic.php'; ?>

<div class="alert alert-info d-flex gap-3 mb-3">
    <i class="bi bi-shield-fill-check flex-shrink-0 mt-1" style="font-size:1.4rem;color:#0078d4;"></i>
    <div>
        <strong>Phishing-resistente MFA</strong> ist seit 2024 Microsofts offizielle Empfehlung
        (FIDO2, Windows Hello, Certificate-Based, Hardware OATH). SMS-OTP und Voice-Call gelten
        als unsicher gegen Adversary-in-the-Middle-Angriffe. Selbst Microsoft Authenticator-Push
        ist nicht vollständig phishing-resistent — nur FIDO2 und Zertifikate sind es.
    </div>
</div>

<!-- ── Summary Tiles ─────────────────────────────────────────────────── -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="metric-card" style="border-left:4px solid <?= $report['phishing_resistant_pct'] >= 50 ? '#16a34a' : '#dc2626' ?>;">
            <div class="metric-label"><i class="bi bi-shield-fill-check me-1"></i>Phishing-resistent</div>
            <div class="metric-value" style="color:<?= $report['phishing_resistant_pct'] >= 50 ? '#16a34a' : '#dc2626' ?>;">
                <?= $report['phishing_resistant_pct'] ?>%
            </div>
            <div class="metric-sub"><?= number_format($report['phishing_resistant']) ?> von <?= number_format($report['total']) ?> Usern</div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="metric-card">
            <div class="metric-label"><i class="bi bi-phone me-1"></i>Nur Software-MFA</div>
            <div class="metric-value"><?= number_format($report['software_mfa']) ?></div>
            <div class="metric-sub">Authenticator / TOTP</div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="metric-card" style="border-left:4px solid <?= $report['weak_only'] > 0 ? '#dc2626' : '#16a34a' ?>;">
            <div class="metric-label"><i class="bi bi-exclamation-octagon me-1"></i>Nur schwache MFA</div>
            <div class="metric-value" style="color:<?= $report['weak_only'] > 0 ? '#dc2626' : '#16a34a' ?>;">
                <?= number_format($report['weak_only']) ?>
            </div>
            <div class="metric-sub">SMS / Voice / E-Mail-OTP</div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="metric-card" style="border-left:4px solid <?= $report['no_mfa'] > 0 ? '#dc2626' : '#16a34a' ?>;">
            <div class="metric-label"><i class="bi bi-shield-x me-1"></i>Keine MFA</div>
            <div class="metric-value" style="color:<?= $report['no_mfa'] > 0 ? '#dc2626' : '#16a34a' ?>;">
                <?= number_format($report['no_mfa']) ?>
            </div>
            <div class="metric-sub">nur Passwort</div>
        </div>
    </div>
</div>

<!-- ── Method Breakdown ──────────────────────────────────────────────── -->
<?php if (!empty($report['method_breakdown'])): ?>
<div class="content-card mb-4">
    <div class="card-header-custom"><i class="bi bi-bar-chart-fill text-primary"></i><h6>Methoden-Verteilung</h6></div>
    <div class="card-body-custom">
        <?php $max = max($report['method_breakdown']); ?>
        <?php foreach ($report['method_breakdown'] as $method => $count):
            $isResistant = in_array($method, ['FIDO2 Security Key', 'Windows Hello for Business', 'Certificate-Based Auth', 'Hardware OATH Token'], true);
            $isWeak      = in_array($method, ['Telefon (SMS/Call)', 'E-Mail-OTP'], true);
            $color       = $isResistant ? '#16a34a' : ($isWeak ? '#dc2626' : '#0078d4');
            $pct         = $max > 0 ? round($count / $max * 100) : 0;
        ?>
            <div class="mb-2">
                <div class="d-flex justify-content-between mb-1">
                    <span style="font-size:13px;">
                        <?php if ($isResistant): ?><i class="bi bi-shield-fill-check text-success me-1"></i><?php endif; ?>
                        <?php if ($isWeak): ?><i class="bi bi-exclamation-triangle-fill text-danger me-1"></i><?php endif; ?>
                        <?= $e($method) ?>
                    </span>
                    <span class="fw-semibold"><?= number_format($count) ?></span>
                </div>
                <div style="height:6px;background:#f3f4f6;border-radius:3px;overflow:hidden;">
                    <div style="height:100%;background:<?= $color ?>;width:<?= $pct ?>%;"></div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- ── User mit schwacher MFA ────────────────────────────────────────── -->
<?php if (!empty($report['weak_users'])): ?>
<div class="content-card mb-4">
    <div class="card-header-custom">
        <i class="bi bi-exclamation-triangle-fill text-danger"></i>
        <h6>User mit ausschließlich schwacher MFA</h6>
    </div>
    <div class="card-body-custom p-0">
        <div class="table-responsive">
            <table class="data-table">
                <thead><tr><th>UPN</th><th>Registrierte Methoden</th></tr></thead>
                <tbody>
                <?php foreach ($report['weak_users'] as $u): ?>
                    <tr>
                        <td class="font-monospace small"><?= $e($u['upn']) ?></td>
                        <td>
                            <?php foreach ($u['methods'] as $m): ?>
                                <span class="badge bg-warning text-dark me-1"><?= $e($m) ?></span>
                            <?php endforeach; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- ── Tenant-Auth-Strength-Policies ─────────────────────────────────── -->
<div class="content-card mb-4">
    <div class="card-header-custom"><i class="bi bi-sliders text-info"></i><h6>Authentication-Strength-Policies</h6></div>
    <div class="card-body-custom p-0">
        <?php if (empty($policies)): ?>
            <div class="text-muted small p-4 text-center">
                Nur Microsoft-Default-Policies aktiv. Custom-Strength-Policies können in Entra → Authentifizierungsmethoden → Authentifizierungsstärken konfiguriert werden.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead><tr><th>Name</th><th>Typ</th><th>Erlaubte Methoden</th></tr></thead>
                    <tbody>
                    <?php foreach ($policies as $p): ?>
                        <tr>
                            <td>
                                <div class="fw-medium"><?= $e($p['displayName'] ?? '–') ?></div>
                                <div class="text-muted small"><?= $e($p['description'] ?? '') ?></div>
                            </td>
                            <td>
                                <?php if (($p['policyType'] ?? '') === 'builtIn'): ?>
                                    <span class="badge bg-secondary">Built-in</span>
                                <?php else: ?>
                                    <span class="badge bg-primary">Custom</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php foreach ($p['allowedCombinations'] ?? [] as $combo): ?>
                                    <span class="badge bg-info text-dark me-1 mb-1"><?= $e($combo) ?></span>
                                <?php endforeach; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
