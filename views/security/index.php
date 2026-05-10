<?php use App\Core\View; use App\Core\Session; $e = fn($v) => View::escape($v); ?>

<?php $flash = Session::getFlash('success'); $error = Session::getFlash('error'); ?>
<?php if ($flash): ?>
    <div class="alert alert-success alert-dismissible mb-3"><i class="bi bi-check-circle me-2"></i><?= $e($flash) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible mb-3"><i class="bi bi-exclamation-triangle me-2"></i><?= $e($error) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<!-- MFA + Stats row -->
<div class="row g-3 mb-4">
    <div class="col-sm-3">
        <div class="metric-card">
            <div class="metric-label">MFA registriert</div>
            <div class="metric-value" style="color:#16a34a;"><?= $mfa['registered'] ?></div>
            <div class="metric-sub">von <?= $mfa['total'] ?> Benutzern (<?= $mfa['pct'] ?>%)</div>
        </div>
    </div>
    <div class="col-sm-3">
        <div class="metric-card">
            <div class="metric-label">Risikobenutzer</div>
            <div class="metric-value" style="color:<?= count($riskyUsers) > 0 ? '#dc2626' : '#111827' ?>;">
                <?= count($riskyUsers) ?>
            </div>
        </div>
    </div>
    <div class="col-sm-3">
        <div class="metric-card">
            <div class="metric-label">CA-Policies</div>
            <div class="metric-value"><?= count($policies) ?></div>
            <div class="metric-sub"><?= count(array_filter($policies, fn($p) => $p['state']==='enabled')) ?> aktiv</div>
        </div>
    </div>
    <div class="col-sm-3">
        <div class="metric-card">
            <div class="metric-label">Letzte Anmeldungen</div>
            <div class="metric-value"><?= count($signIns) ?></div>
            <div class="metric-sub">Letzte Einträge</div>
        </div>
    </div>
</div>

<!-- MFA Progress -->
<div class="content-card mb-4">
    <div class="card-header-custom">
        <i class="bi bi-shield-lock-fill text-primary"></i>
        <h6>MFA-Adoption</h6>
    </div>
    <div class="card-body-custom">
        <div class="d-flex justify-content-between mb-1">
            <span style="font-size:13px;">Registriert</span>
            <span class="text-muted small"><?= $mfa['registered'] ?> / <?= $mfa['total'] ?></span>
        </div>
        <div class="progress-custom mb-3">
            <div class="bar" style="width:<?= min(100,$mfa['pct']) ?>%;"></div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <!-- Conditional Access Policies -->
    <div class="col-lg-6">
        <div class="content-card">
            <div class="card-header-custom">
                <i class="bi bi-shield-check text-success"></i>
                <h6>Conditional Access Policies (<?= count($policies) ?>)</h6>
            </div>
            <?php if (!empty($policies)): ?>
                <div class="alert alert-warning mb-0" style="border-radius:0;border-left:none;border-right:none;font-size:12px;padding:8px 14px;">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i>
                    Änderungen an CA-Richtlinien wirken sich sofort auf alle Benutzer aus. Teste neue Richtlinien zunächst im Berichtsmodus.
                </div>
            <?php endif; ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead><tr><th>Name</th><th>Status</th></tr></thead>
                    <tbody>
                        <?php foreach ($policies as $p):
                            $pId    = $p['id'] ?? '';
                            $pState = $p['state'] ?? 'disabled';
                        ?>
                            <tr>
                                <td style="font-size:13px;"><?= $e($p['displayName'] ?? '') ?></td>
                                <td style="white-space:nowrap;">
                                    <?php if ($pState === 'enabled'): ?>
                                        <span class="badge-enabled">Aktiv</span>
                                    <?php elseif ($pState === 'enabledForReportingButNotEnforced'): ?>
                                        <span class="badge-info">Nur Bericht</span>
                                    <?php else: ?>
                                        <span class="badge-neutral">Deaktiviert</span>
                                    <?php endif; ?>
                                    <?php if (\App\Auth\LocalAuth::isAdmin() && $pId !== ''): ?>
                                        <div class="dropdown d-inline-block ms-1">
                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle py-0 px-1"
                                                    style="font-size:11px;line-height:1.6;"
                                                    type="button"
                                                    data-bs-toggle="dropdown"
                                                    aria-expanded="false">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end" style="min-width:170px;">
                                                <li>
                                                    <form method="post" action="/security/ca/<?= $e($pId) ?>/toggle" class="mb-0">
                                                        <input type="hidden" name="state" value="enabled">
                                                        <button type="submit"
                                                                class="dropdown-item <?= $pState === 'enabled' ? 'active' : '' ?>"
                                                                style="font-size:13px;">
                                                            <?= $pState === 'enabled' ? '<i class="bi bi-check2 me-1"></i>' : '' ?>Aktiv
                                                        </button>
                                                    </form>
                                                </li>
                                                <li>
                                                    <form method="post" action="/security/ca/<?= $e($pId) ?>/toggle" class="mb-0">
                                                        <input type="hidden" name="state" value="enabledForReportingButNotEnforced">
                                                        <button type="submit"
                                                                class="dropdown-item <?= $pState === 'enabledForReportingButNotEnforced' ? 'active' : '' ?>"
                                                                style="font-size:13px;">
                                                            <?= $pState === 'enabledForReportingButNotEnforced' ? '<i class="bi bi-check2 me-1"></i>' : '' ?>Nur Bericht
                                                        </button>
                                                    </form>
                                                </li>
                                                <li>
                                                    <form method="post" action="/security/ca/<?= $e($pId) ?>/toggle" class="mb-0">
                                                        <input type="hidden" name="state" value="disabled">
                                                        <button type="submit"
                                                                class="dropdown-item <?= $pState === 'disabled' ? 'active' : '' ?>"
                                                                style="font-size:13px;">
                                                            <?= $pState === 'disabled' ? '<i class="bi bi-check2 me-1"></i>' : '' ?>Deaktiviert
                                                        </button>
                                                    </form>
                                                </li>
                                            </ul>
                                        </div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($policies)): ?>
                            <tr><td colspan="2" class="text-center text-muted">Keine CA-Policies (Berechtigungen prüfen)</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Risky Users -->
    <div class="col-lg-6">
        <div class="content-card">
            <div class="card-header-custom">
                <i class="bi bi-exclamation-triangle-fill text-danger"></i>
                <h6>Risikobenutzer (<?= count($riskyUsers) ?>)</h6>
            </div>
            <div class="table-responsive">
                <table class="data-table">
                    <thead><tr><th>Benutzer</th><th>Risiko</th><th>Zustand</th></tr></thead>
                    <tbody>
                        <?php foreach ($riskyUsers as $u): ?>
                            <tr>
                                <td>
                                    <div style="font-size:13px;font-weight:500;"><?= $e($u['userDisplayName'] ?? '') ?></div>
                                    <div style="font-size:11px;color:#9ca3af;"><?= $e($u['userPrincipalName'] ?? '') ?></div>
                                </td>
                                <td>
                                    <?php $lvl = strtolower($u['riskLevel'] ?? 'none'); ?>
                                    <?php if ($lvl === 'high'): ?>
                                        <span class="badge-disabled">Hoch</span>
                                    <?php elseif ($lvl === 'medium'): ?>
                                        <span class="badge-warning">Mittel</span>
                                    <?php else: ?>
                                        <span class="badge-neutral"><?= $e($lvl) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td style="font-size:12px;"><?= $e($u['riskState'] ?? '') ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($riskyUsers)): ?>
                            <tr><td colspan="3" class="text-center text-muted">Keine Risikobenutzer ✓</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Recent Sign-ins -->
<div class="content-card">
    <div class="card-header-custom">
        <i class="bi bi-clock-history text-secondary"></i>
        <h6>Letzte Anmeldungen</h6>
    </div>
    <div class="table-responsive">
        <table class="data-table">
            <thead><tr><th>Zeitpunkt</th><th>Benutzer</th><th>App</th><th>IP</th><th>Status</th><th>Risiko</th></tr></thead>
            <tbody>
                <?php foreach ($signIns as $s): ?>
                    <?php $success = ($s['status']['errorCode'] ?? 1) === 0; ?>
                    <tr>
                        <td style="font-size:11px;color:#6b7280;white-space:nowrap;">
                            <?= !empty($s['createdDateTime']) ? date('d.m. H:i', strtotime($s['createdDateTime'])) : '–' ?>
                        </td>
                        <td style="font-size:12px;"><?= $e($s['userPrincipalName'] ?? '') ?></td>
                        <td style="font-size:12px;color:#6b7280;"><?= $e($s['appDisplayName'] ?? '') ?></td>
                        <td style="font-size:11px;color:#9ca3af;"><?= $e($s['ipAddress'] ?? '') ?></td>
                        <td>
                            <?= $success ? '<span class="badge-enabled">OK</span>' : '<span class="badge-disabled">Fehler</span>' ?>
                        </td>
                        <td>
                            <?php $risk = strtolower($s['riskLevelDuringSignIn'] ?? 'none'); ?>
                            <?php if ($risk !== 'none' && $risk !== ''): ?>
                                <span class="badge-warning"><?= $e($risk) ?></span>
                            <?php else: ?>
                                <span class="text-muted" style="font-size:11px;">–</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($signIns)): ?>
                    <tr><td colspan="6" class="text-center text-muted py-3">Keine Daten (AuditLog-Berechtigungen prüfen)</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
