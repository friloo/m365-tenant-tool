<?php use App\Core\View; $e = fn($v) => View::escape($v); ?>

<?php if (!empty($diag)) include BASE_PATH . '/views/partials/graph_diagnostic.php'; ?>

<div class="alert alert-info d-flex gap-3 mb-3">
    <i class="bi bi-clock-history flex-shrink-0 mt-1" style="font-size:1.4rem;color:#0078d4;"></i>
    <div>
        <strong>Token-Lifetime steuert, wie lange ein User angemeldet bleibt</strong>, bevor er sich
        neu authentifizieren muss. Microsoft hat 2021 die globalen Token-Lifetime-Policies deprecated —
        die einzig empfohlene Methode ist heute „Sign-in Frequency" in Conditional Access Policies.
        Empfehlung: Admin-Apps ≤ 4 Stunden, kritische User-Apps ≤ 24 Stunden, Standard-Apps
        7 Tage. Microsoft-Default ist sonst 90 Tage Refresh-Token — viel zu lang.
    </div>
</div>

<?php if (!empty($recs)): ?>
<div class="content-card mb-4">
    <div class="card-header-custom"><i class="bi bi-exclamation-triangle text-warning"></i><h6>Empfehlungen</h6></div>
    <div class="card-body-custom">
        <?php foreach ($recs as $r):
            $cls = $r['severity'] === 'high' ? 'text-danger' : 'text-warning';
        ?>
            <p class="<?= $cls ?> mb-2"><strong>[<?= strtoupper($r['severity']) ?>]</strong> <?= $e($r['msg']) ?></p>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<div class="content-card mb-4">
    <div class="card-header-custom"><i class="bi bi-stopwatch text-primary"></i><h6>Sign-in-Frequency in CA-Policies</h6></div>
    <div class="card-body-custom p-0">
        <?php if (empty($caPolicies)): ?>
            <div class="text-muted small p-4 text-center">
                Keine CA-Policy mit konfigurierter Sign-in-Frequency gefunden.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead><tr>
                        <th>CA-Policy</th>
                        <th>State</th>
                        <th>Frequenz</th>
                        <th>Auth-Typ</th>
                    </tr></thead>
                    <tbody>
                    <?php foreach ($caPolicies as $p): ?>
                        <tr>
                            <td class="fw-medium"><?= $e($p['name']) ?></td>
                            <td>
                                <?php if ($p['state'] === 'enabled'): ?>
                                    <span class="badge bg-success">Aktiv</span>
                                <?php elseif ($p['state'] === 'enabledForReportingButNotEnforced'): ?>
                                    <span class="badge bg-warning text-dark">Report-Only</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary"><?= $e($p['state']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($p['frequency_interval'] === 'everyTime'): ?>
                                    <span class="badge bg-info text-dark">Jedes Mal neu</span>
                                <?php else: ?>
                                    <?= (int)$p['value'] ?> <?= $e($p['type']) ?>
                                <?php endif; ?>
                            </td>
                            <td class="text-muted small"><?= $e($p['authentication_type']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php if (!empty($persistent)): ?>
<div class="content-card mb-4">
    <div class="card-header-custom"><i class="bi bi-window-stack text-primary"></i><h6>Persistente Browser-Sessions</h6></div>
    <div class="card-body-custom p-0">
        <div class="table-responsive">
            <table class="data-table">
                <thead><tr><th>CA-Policy</th><th>State</th><th>Modus</th></tr></thead>
                <tbody>
                <?php foreach ($persistent as $p): ?>
                    <tr>
                        <td><?= $e($p['name']) ?></td>
                        <td><?= $e($p['state']) ?></td>
                        <td><?= $e($p['mode']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="alert alert-info d-flex gap-3">
    <i class="bi bi-info-circle-fill flex-shrink-0 mt-1"></i>
    <div>
        <strong>Konfiguration:</strong> Im
        <a href="https://entra.microsoft.com/#view/Microsoft_AAD_ConditionalAccess/ConditionalAccessBlade/~/Policies" target="_blank" rel="noopener">Conditional-Access-Portal</a>
        eine Policy öffnen, unter „Sitzung" → „Sign-in frequency" den Wert setzen.
        Sinnvolle Defaults: 4 Stunden für Privileged Roles, 12 Stunden für sensitive Apps (Finance/HR),
        7 Tage für Office-Standard.
    </div>
</div>
