<?php
use App\Core\View;
$e = fn($v) => View::escape($v);
$n = fn($v) => $v !== null ? number_format((int)$v) : '<span class="text-muted">–</span>';

$mfaPct     = $security['mfa_pct']    ?? null;
$mfaColor   = $mfaPct === null ? '#6b7280' : ($mfaPct >= 80 ? '#16a34a' : ($mfaPct >= 50 ? '#ca8a04' : '#dc2626'));
$caEnabled  = $security['ca_enabled'] ?? null;
$nonComp    = $security['non_compliant'] ?? null;
$alerts     = $security['unresolved_alerts'] ?? null;
?>

<!-- Metric Cards – row 1: people & licenses -->
<div class="row g-3 mb-3">
    <div class="col-sm-6 col-xl-3">
        <div class="metric-card d-flex align-items-center gap-3">
            <div class="metric-icon" style="background:#eff6ff;">
                <i class="bi bi-people-fill" style="color:#2563eb;"></i>
            </div>
            <div>
                <div class="metric-label">Benutzer gesamt</div>
                <div class="metric-value"><?= $n($metrics['total_users']) ?></div>
                <div class="metric-sub"><?= $n($metrics['enabled_users']) ?> aktiv</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="metric-card d-flex align-items-center gap-3">
            <div class="metric-icon" style="background:#f0fdf4;">
                <i class="bi bi-award-fill" style="color:#16a34a;"></i>
            </div>
            <div>
                <div class="metric-label">Lizenz-Produkte</div>
                <div class="metric-value"><?= $n($metrics['license_products']) ?></div>
                <div class="metric-sub">Abonnierte SKUs</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <a href="/mfamethods" class="text-decoration-none">
        <div class="metric-card d-flex align-items-center gap-3 h-100">
            <div class="metric-icon" style="background:#f5f3ff;">
                <i class="bi bi-shield-check" style="color:#7c3aed;"></i>
            </div>
            <div>
                <div class="metric-label">MFA-Abdeckung</div>
                <div class="metric-value" style="color:<?= $mfaColor ?>">
                    <?= $mfaPct !== null ? $mfaPct . '%' : '<span class="text-muted fs-6">–</span>' ?>
                </div>
                <div class="metric-sub">
                    <?= $security['mfa_registered'] !== null
                        ? ($n($security['mfa_registered']) . ' / ' . $n($security['mfa_total']) . ' registriert')
                        : 'Keine Daten' ?>
                </div>
            </div>
        </div>
        </a>
    </div>
    <div class="col-sm-6 col-xl-3">
        <a href="/conditionalaccess" class="text-decoration-none">
        <div class="metric-card d-flex align-items-center gap-3 h-100">
            <div class="metric-icon" style="background:#fff7ed;">
                <i class="bi bi-shield-shaded" style="color:#ea580c;"></i>
            </div>
            <div>
                <div class="metric-label">Conditional Access</div>
                <div class="metric-value" style="color:<?= ($caEnabled !== null && $caEnabled > 0) ? '#16a34a' : '#dc2626' ?>">
                    <?= $caEnabled !== null ? $n($caEnabled) : '<span class="text-muted fs-6">–</span>' ?>
                </div>
                <div class="metric-sub">
                    <?php if ($caEnabled !== null): ?>
                        aktive Richtlinien
                        <?php if (($security['ca_report_only'] ?? 0) > 0): ?>
                            · <?= $n($security['ca_report_only']) ?> report-only
                        <?php endif ?>
                    <?php else: ?>
                        Keine Daten
                    <?php endif ?>
                </div>
            </div>
        </div>
        </a>
    </div>
</div>

<!-- Metric Cards – row 2: devices & risks -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <a href="/devices" class="text-decoration-none">
        <div class="metric-card d-flex align-items-center gap-3 h-100">
            <div class="metric-icon" style="background:#fef9c3;">
                <i class="bi bi-phone-fill" style="color:#ca8a04;"></i>
            </div>
            <div>
                <div class="metric-label">Geräte</div>
                <div class="metric-value"><?= $n($metrics['total_devices']) ?></div>
                <div class="metric-sub">
                    <?php if ($nonComp !== null && $nonComp > 0): ?>
                        <span style="color:#dc2626;"><?= $n($nonComp) ?> nicht konform</span>
                    <?php elseif ($nonComp === 0): ?>
                        <span style="color:#16a34a;">Alle konform</span>
                    <?php else: ?>
                        Intune verwaltet
                    <?php endif ?>
                </div>
            </div>
        </div>
        </a>
    </div>
    <div class="col-sm-6 col-xl-3">
        <a href="/riskysignins" class="text-decoration-none">
        <div class="metric-card d-flex align-items-center gap-3 h-100">
            <div class="metric-icon" style="background:#fef2f2;">
                <i class="bi bi-exclamation-triangle-fill" style="color:#dc2626;"></i>
            </div>
            <div>
                <div class="metric-label">Risikobenutzer</div>
                <div class="metric-value" style="color:<?= ($metrics['risky_users'] ?? 0) > 0 ? '#dc2626' : '#111827' ?>">
                    <?= $n($metrics['risky_users']) ?>
                </div>
                <div class="metric-sub">Aktive Risiken</div>
            </div>
        </div>
        </a>
    </div>
    <div class="col-sm-6 col-xl-3">
        <a href="/defenderalerts" class="text-decoration-none">
        <div class="metric-card d-flex align-items-center gap-3 h-100">
            <div class="metric-icon" style="background:#fef2f2;">
                <i class="bi bi-bell-fill" style="color:#dc2626;"></i>
            </div>
            <div>
                <div class="metric-label">Defender Alerts</div>
                <div class="metric-value" style="color:<?= ($alerts ?? 0) > 0 ? '#dc2626' : '#16a34a' ?>">
                    <?= $alerts !== null ? $n($alerts) : '<span class="text-muted fs-6">–</span>' ?>
                </div>
                <div class="metric-sub">Offen / In Bearbeitung</div>
            </div>
        </div>
        </a>
    </div>
    <div class="col-sm-6 col-xl-3">
        <a href="/groups" class="text-decoration-none">
        <div class="metric-card d-flex align-items-center gap-3 h-100">
            <div class="metric-icon" style="background:#ecfdf5;">
                <i class="bi bi-diagram-3-fill" style="color:#059669;"></i>
            </div>
            <div>
                <div class="metric-label">Gruppen & Teams</div>
                <div class="metric-value"><?= $n($metrics['total_groups']) ?></div>
                <div class="metric-sub">Im Verzeichnis</div>
            </div>
        </div>
        </a>
    </div>
</div>

<?php if (!empty($recommendations)): ?>
<div class="mb-4">
    <?php foreach ($recommendations as $rec): ?>
        <div class="alert alert-<?= $rec['type'] === 'danger' ? 'danger' : ($rec['type'] === 'warning' ? 'warning' : 'info') ?> py-2 mb-2">
            <?= $rec['msg'] ?>
        </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Charts Row -->
<div class="row g-3 mb-4">
    <!-- License Breakdown -->
    <div class="col-xl-7">
        <div class="content-card">
            <div class="card-header-custom">
                <i class="bi bi-bar-chart-fill text-primary"></i>
                <h6>Lizenz-Nutzung</h6>
            </div>
            <div class="card-body-custom">
                <?php if (empty($licenses)): ?>
                    <p class="text-muted text-center py-3">Keine Lizenzdaten verfügbar</p>
                <?php else: ?>
                    <?php foreach ($licenses as $sku): ?>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="fw-medium" style="font-size:13px;"><?= $e($sku['name']) ?></span>
                                <span class="text-muted" style="font-size:12px;">
                                    <?= number_format($sku['consumed']) ?> / <?= number_format($sku['total']) ?>
                                    <span class="ms-1"><?= $sku['pct'] ?>%</span>
                                </span>
                            </div>
                            <div class="progress-custom">
                                <div class="bar <?= $sku['pct'] >= 90 ? 'danger' : ($sku['pct'] >= 75 ? 'warning' : '') ?>"
                                     style="width:<?= min(100, $sku['pct']) ?>%"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Security Status -->
    <div class="col-xl-5">
        <div class="content-card h-100">
            <div class="card-header-custom">
                <i class="bi bi-shield-fill-check text-success"></i>
                <h6>Sicherheitsstatus</h6>
            </div>
            <div class="card-body-custom p-0">
                <?php
                $secItems = [
                    [
                        'label'  => 'MFA-Abdeckung',
                        'href'   => '/mfamethods',
                        'ok'     => $mfaPct !== null && $mfaPct >= 80,
                        'warn'   => $mfaPct !== null && $mfaPct >= 50 && $mfaPct < 80,
                        'val'    => $mfaPct !== null ? $mfaPct . '%' : '–',
                        'hint'   => $mfaPct !== null ? ($mfaPct >= 80 ? 'Gut' : ($mfaPct >= 50 ? 'Ausbaufähig' : 'Kritisch')) : 'Keine Daten',
                    ],
                    [
                        'label'  => 'Conditional Access',
                        'href'   => '/conditionalaccess',
                        'ok'     => $caEnabled !== null && $caEnabled >= 3,
                        'warn'   => $caEnabled !== null && $caEnabled > 0 && $caEnabled < 3,
                        'val'    => $caEnabled !== null ? $caEnabled . ' aktiv' : '–',
                        'hint'   => $caEnabled !== null ? ($caEnabled === 0 ? 'Keine Richtlinien!' : ($caEnabled < 3 ? 'Wenige Richtlinien' : 'Konfiguriert')) : 'Keine Daten',
                    ],
                    [
                        'label'  => 'Risikobenutzer',
                        'href'   => '/riskysignins',
                        'ok'     => ($metrics['risky_users'] ?? 0) === 0,
                        'warn'   => false,
                        'val'    => $n($metrics['risky_users']),
                        'hint'   => ($metrics['risky_users'] ?? 0) === 0 ? 'Keine Risiken' : 'Maßnahmen erforderlich',
                    ],
                    [
                        'label'  => 'Nicht konforme Geräte',
                        'href'   => '/devices',
                        'ok'     => $nonComp === 0,
                        'warn'   => $nonComp !== null && $nonComp > 0 && $nonComp <= 5,
                        'val'    => $nonComp !== null ? $n($nonComp) : '–',
                        'hint'   => $nonComp === null ? 'Keine Daten' : ($nonComp === 0 ? 'Alle konform' : $nonComp . ' Gerät(e) prüfen'),
                    ],
                    [
                        'label'  => 'Offene Defender Alerts',
                        'href'   => '/defenderalerts',
                        'ok'     => $alerts === 0,
                        'warn'   => $alerts !== null && $alerts > 0 && $alerts <= 3,
                        'val'    => $alerts !== null ? $n($alerts) : '–',
                        'hint'   => $alerts === null ? 'Keine Daten' : ($alerts === 0 ? 'Keine offenen Alerts' : $alerts . ' Alert(s) offen'),
                    ],
                ];
                ?>
                <ul class="list-group list-group-flush">
                <?php foreach ($secItems as $item): ?>
                    <?php
                    $icon  = $item['ok'] ? 'check-circle-fill' : ($item['warn'] ? 'exclamation-triangle-fill' : 'x-circle-fill');
                    $color = $item['ok'] ? '#16a34a' : ($item['warn'] ? '#ca8a04' : '#dc2626');
                    if ($item['val'] === '–' || $item['val'] === '<span class="text-muted">–</span>') {
                        $icon = 'dash-circle'; $color = '#9ca3af';
                    }
                    ?>
                    <li class="list-group-item py-2 px-3">
                        <a href="<?= $item['href'] ?>" class="text-decoration-none text-body d-flex align-items-center gap-2">
                            <i class="bi bi-<?= $icon ?> flex-shrink-0" style="color:<?= $color ?>;font-size:1rem;"></i>
                            <span class="flex-grow-1" style="font-size:13px;"><?= $item['label'] ?></span>
                            <span class="fw-semibold" style="font-size:13px;color:<?= $color ?>;"><?= $item['val'] ?></span>
                            <span class="text-muted" style="font-size:11px;"><?= $item['hint'] ?></span>
                        </a>
                    </li>
                <?php endforeach ?>
                </ul>
                <div class="px-3 pt-3 pb-2">
                    <a href="/securityposture" class="btn btn-sm btn-outline-secondary w-100">
                        <i class="bi bi-shield-fill-check me-1"></i>Security Posture öffnen
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick access -->
<div class="content-card mb-4">
    <div class="card-header-custom">
        <i class="bi bi-grid text-secondary"></i>
        <h6>Schnellzugriff</h6>
    </div>
    <div class="card-body-custom">
        <div class="d-flex flex-wrap gap-2">
            <a href="/users"           class="btn btn-sm btn-outline-primary"><i class="bi bi-people me-1"></i>Benutzer</a>
            <a href="/licenses"        class="btn btn-sm btn-outline-success"><i class="bi bi-award me-1"></i>Lizenzen</a>
            <a href="/licenseadvisor"  class="btn btn-sm btn-outline-success"><i class="bi bi-lightbulb me-1"></i>Lizenz-Berater</a>
            <a href="/conditionalaccess" class="btn btn-sm btn-outline-warning"><i class="bi bi-shield-shaded me-1"></i>Conditional Access</a>
            <a href="/namedlocations"  class="btn btn-sm btn-outline-warning"><i class="bi bi-geo-alt me-1"></i>Named Locations</a>
            <a href="/devices"         class="btn btn-sm btn-outline-secondary"><i class="bi bi-phone me-1"></i>Geräte</a>
            <a href="/mfamethods"      class="btn btn-sm btn-outline-secondary"><i class="bi bi-shield-check me-1"></i>MFA</a>
            <a href="/offboarding"     class="btn btn-sm btn-outline-secondary"><i class="bi bi-person-dash me-1"></i>Offboarding</a>
            <a href="/signinlog"       class="btn btn-sm btn-outline-secondary"><i class="bi bi-clock-history me-1"></i>Sign-in-Log</a>
            <a href="/securescore"     class="btn btn-sm btn-outline-secondary"><i class="bi bi-bar-chart me-1"></i>Secure Score</a>
        </div>
    </div>
</div>
