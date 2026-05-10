<?php use App\Core\View; $e = fn($v) => View::escape($v); ?>

<?php
$total         = $summary['total'];
$mfaRegistered = $summary['mfa_registered'];
$noMfa         = $summary['no_mfa'];
$mfaCapable    = $summary['mfa_capable'];
$byMethod      = $summary['by_method'];
$byDefault     = $summary['by_default'];
$maxMethod     = !empty($byMethod)  ? max($byMethod)  : 1;
$maxDefault    = !empty($byDefault) ? max($byDefault) : 1;
?>

<?php if (!empty($apiError)): ?>
<div class="alert mb-4" style="background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:16px;">
    <div class="d-flex align-items-start gap-3">
        <i class="bi bi-exclamation-triangle-fill" style="color:#dc2626;font-size:20px;flex-shrink:0;margin-top:2px;"></i>
        <div style="flex:1;">
            <div class="fw-semibold mb-1" style="color:#991b1b;">
                Microsoft Graph antwortet mit HTTP <?= (int)$apiError['status'] ?> — Daten können nicht geladen werden.
            </div>
            <div class="small mb-2" style="color:#7f1d1d;">
                <code><?= $e($apiError['code'] ?: 'Error') ?></code>: <?= $e($apiError['message']) ?>
            </div>
            <div class="small" style="color:#7f1d1d;">
                Mögliche Ursachen:
                <ul class="mb-2 mt-1">
                    <li><strong>Berechtigung fehlt:</strong> Der Endpunkt benötigt
                        <code>AuditLog.Read.All</code> + <code>Reports.Read.All</code>
                        als <em>Anwendungs</em>-Berechtigung mit Admin-Consent.</li>
                    <li><strong>Token ist veraltet:</strong> Nach dem Hinzufügen neuer Berechtigungen muss der
                        gecachte Access-Token erneuert werden. Klicke auf
                        <a href="?refresh=1"><strong>Aktualisieren</strong></a> — das leert auch den Token-Cache.</li>
                    <li><strong>Azure AD Premium-Lizenz fehlt:</strong> Der Bericht
                        <code>userRegistrationDetails</code> setzt mindestens eine
                        <strong>Azure AD / Entra ID P1 oder P2</strong>-Lizenz im Tenant voraus.
                        Ohne P1/P2 liefert die API HTTP 403 selbst mit vollständigen Berechtigungen.</li>
                </ul>
                Vollständige Endpunkt-URL: <code><?= $e($apiError['url']) ?></code>
            </div>
        </div>
    </div>
</div>
<?php elseif (empty($users)): ?>
<div class="alert mb-4" style="background:#fffbeb;border:1px solid #fde68a;border-radius:8px;padding:16px;">
    <div class="d-flex align-items-start gap-3">
        <i class="bi bi-info-circle-fill" style="color:#d97706;font-size:20px;flex-shrink:0;margin-top:2px;"></i>
        <div style="flex:1;">
            <div class="fw-semibold mb-1" style="color:#92400e;">Keine Daten verfügbar</div>
            <div class="small" style="color:#78350f;">
                Microsoft Graph hat den Aufruf akzeptiert, aber eine leere Antwort geliefert.
                Klicke auf <a href="?refresh=1"><strong>Aktualisieren</strong></a> um Token und Cache neu zu laden.
                Falls die Liste danach weiterhin leer ist, kontrolliere die Berechtigungen
                <code>AuditLog.Read.All</code> + <code>Reports.Read.All</code> und stelle sicher,
                dass der Tenant eine <strong>Entra ID P1/P2-Lizenz</strong> besitzt.
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Metric cards -->
<div class="row g-3 mb-4">
    <div class="col-sm-3">
        <div class="metric-card">
            <div class="metric-label">Gesamt</div>
            <div class="metric-value"><?= number_format($total) ?></div>
            <div class="metric-sub">Benutzer analysiert</div>
        </div>
    </div>
    <div class="col-sm-3">
        <div class="metric-card">
            <div class="metric-label">MFA registriert</div>
            <div class="metric-value" style="color:#16a34a;"><?= number_format($mfaRegistered) ?></div>
            <div class="metric-sub"><?= $total > 0 ? round(($mfaRegistered / $total) * 100) : 0 ?>% der Benutzer</div>
        </div>
    </div>
    <div class="col-sm-3">
        <div class="metric-card">
            <div class="metric-label">Kein MFA</div>
            <div class="metric-value" style="color:<?= $noMfa > 0 ? '#dc2626' : '#16a34a' ?>;">
                <?= number_format($noMfa) ?>
            </div>
            <div class="metric-sub"><?= $total > 0 ? round(($noMfa / $total) * 100) : 0 ?>% ohne MFA</div>
        </div>
    </div>
    <div class="col-sm-3">
        <div class="metric-card">
            <div class="metric-label">MFA-fähig</div>
            <div class="metric-value"><?= number_format($mfaCapable) ?></div>
            <div class="metric-sub"><?= $total > 0 ? round(($mfaCapable / $total) * 100) : 0 ?>% der Benutzer</div>
        </div>
    </div>
</div>

<!-- Charts row -->
<div class="row g-3 mb-4">

    <!-- Left: Methoden-Verteilung -->
    <div class="col-md-6">
        <div class="content-card" style="height:100%;">
            <div class="card-header-custom">
                <i class="bi bi-bar-chart-horizontal me-2"></i>Methoden-Verteilung
            </div>
            <div class="card-body-custom">
                <?php if (empty($byMethod)): ?>
                    <div class="empty-state">
                        <i class="bi bi-shield-x"></i>
                        <p>Keine Methoden-Daten verfügbar</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($byMethod as $key => $count):
                        $label = $labels[$key] ?? $key;
                        $pct   = $maxMethod > 0 ? round(($count / $maxMethod) * 100) : 0;
                    ?>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span style="font-size:13px;font-weight:500;"><?= $e($label) ?></span>
                            <span style="font-size:12px;color:#6b7280;"><?= number_format($count) ?></span>
                        </div>
                        <div class="progress progress-custom" style="height:10px;">
                            <div class="progress-bar" role="progressbar"
                                 style="width:<?= $pct ?>%;background:#0078d4;"
                                 aria-valuenow="<?= $pct ?>" aria-valuemin="0" aria-valuemax="100">
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Right: Standard-Methode -->
    <div class="col-md-6">
        <div class="content-card" style="height:100%;">
            <div class="card-header-custom">
                <i class="bi bi-shield-check me-2"></i>Standard-Methode
            </div>
            <div class="card-body-custom">
                <?php if (empty($byDefault)): ?>
                    <div class="empty-state">
                        <i class="bi bi-shield-x"></i>
                        <p>Keine Standard-Methoden-Daten verfügbar</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($byDefault as $key => $count):
                        $label = $labels[$key] ?? ($key !== '' ? $key : 'Keine Angabe');
                        $pct   = $maxDefault > 0 ? round(($count / $maxDefault) * 100) : 0;
                    ?>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span style="font-size:13px;font-weight:500;"><?= $e($label) ?></span>
                            <span style="font-size:12px;color:#6b7280;"><?= number_format($count) ?></span>
                        </div>
                        <div class="progress progress-custom" style="height:10px;">
                            <div class="progress-bar" role="progressbar"
                                 style="width:<?= $pct ?>%;background:#7c3aed;"
                                 aria-valuenow="<?= $pct ?>" aria-valuemin="0" aria-valuemax="100">
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

</div>

<!-- User table -->
<div class="content-card">
    <div class="table-toolbar">
        <input type="text" id="mfaSearch" class="search-box" placeholder="Benutzer suchen…">
        <select id="mfaFilter" class="form-select form-select-sm ms-2" style="max-width:200px;"
                onchange="filterMfaTable()">
            <option value="">Alle</option>
            <option value="no-mfa">Kein MFA</option>
            <option value="sms">SMS (unsicher)</option>
            <option value="fido2">FIDO2</option>
        </select>
        <a href="?refresh=1" class="btn btn-sm btn-outline-secondary ms-2">
            <i class="bi bi-arrow-clockwise"></i> Aktualisieren
        </a>
    </div>

    <div class="table-responsive">
        <table class="data-table" id="mfaTable">
            <thead>
                <tr>
                    <th style="width:40px;"></th>
                    <th>Name</th>
                    <th>UPN</th>
                    <th>MFA-Status</th>
                    <th>Registrierte Methoden</th>
                    <th>Standard-Methode</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user):
                    $isMfaRegistered = $user['isMfaRegistered'] ?? false;
                    $displayName     = $user['userDisplayName'] ?? $user['displayName'] ?? '';
                    $upn             = $user['userPrincipalName'] ?? '';
                    $initial         = strtoupper(mb_substr($displayName, 0, 1) ?: '?');

                    $methods = $user['methodsRegistered'] ?? [];
                    if (is_string($methods)) {
                        $methods = array_filter(array_map('trim', explode(',', $methods)));
                    }

                    $methodNames = array_map(
                        fn($m) => $labels[trim($m)] ?? trim($m),
                        $methods
                    );

                    $defaultKey    = trim((string)($user['defaultMfaMethod'] ?? ''));
                    $defaultLabel  = $defaultKey !== '' ? ($labels[$defaultKey] ?? $defaultKey) : '–';

                    $hasSmsOnly    = in_array('phoneAuthentication', (array)$methods, true)
                                     && count((array)$methods) === 1;
                    $hasFido2      = in_array('fido2SecurityKey', (array)$methods, true);
                    $hasSms        = in_array('phoneAuthentication', (array)$methods, true);
                ?>
                <tr data-mfa="<?= $isMfaRegistered ? '1' : '0' ?>"
                    data-sms="<?= $hasSms ? '1' : '0' ?>"
                    data-fido2="<?= $hasFido2 ? '1' : '0' ?>">
                    <td>
                        <div style="width:32px;height:32px;border-radius:50%;background:#e3f0fb;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:600;color:#0078d4;flex-shrink:0;">
                            <?= $e($initial) ?>
                        </div>
                    </td>
                    <td>
                        <div style="font-weight:500;font-size:13px;"><?= $e($displayName) ?></div>
                    </td>
                    <td style="font-size:12px;color:#6b7280;"><?= $e($upn) ?></td>
                    <td>
                        <?php if ($isMfaRegistered): ?>
                            <span class="badge-enabled"><i class="bi bi-shield-check"></i> Registriert</span>
                        <?php else: ?>
                            <span class="badge-danger"><i class="bi bi-shield-x"></i> Kein MFA</span>
                        <?php endif; ?>
                    </td>
                    <td style="font-size:12px;">
                        <?php if (empty($methodNames)): ?>
                            <span class="badge-neutral">–</span>
                        <?php else: ?>
                            <?php foreach ($methodNames as $mName): ?>
                                <?php
                                $isSmsMethod = ($mName === ($labels['phoneAuthentication'] ?? 'phoneAuthentication'));
                                $badgeClass  = $isSmsMethod ? 'badge-warning' : 'badge-info';
                                ?>
                                <span class="<?= $badgeClass ?> badge-pill me-1"><?= $e($mName) ?></span>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </td>
                    <td style="font-size:12px;">
                        <?php if ($defaultKey !== ''): ?>
                            <span class="badge-secondary badge-pill"><?= $e($defaultLabel) ?></span>
                        <?php else: ?>
                            <span class="badge-neutral">–</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="6">
                            <div class="empty-state">
                                <i class="bi bi-shield-exclamation"></i>
                                <p>Keine Benutzer-Daten verfügbar</p>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
initTableSearch('mfaSearch', 'mfaTable');

function filterMfaTable() {
    const val = document.getElementById('mfaFilter').value;
    document.querySelectorAll('#mfaTable tbody tr').forEach(function(row) {
        const d = row.dataset;
        let show = true;
        if (val === 'no-mfa')  show = d.mfa  === '0';
        if (val === 'sms')     show = d.sms   === '1';
        if (val === 'fido2')   show = d.fido2 === '1';
        row.style.display = show ? '' : 'none';
    });
}
</script>
