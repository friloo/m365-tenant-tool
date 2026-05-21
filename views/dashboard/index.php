<?php
use App\Core\View;
$e = fn($v) => View::escape($v);

// Widget config: names map to IDs
$_widgets = [
    'dash-w-metrics1'  => 'Verzeichnis & Identität',
    'dash-w-metrics2'  => 'Sicherheit & Geräte',
    'dash-w-charts'    => 'Charts & Sicherheitsstatus',
    'dash-w-infopanels'=> 'Info-Panels',
    'dash-w-quicklinks'=> 'Schnellzugriff',
];

$n = fn($v) => $v !== null ? number_format((int)$v) : '<span class="text-muted small">–</span>';

$mfaPct    = $security['mfa_pct']    ?? null;
$mfaColor  = $mfaPct === null ? '#6b7280' : ($mfaPct >= 80 ? '#16a34a' : ($mfaPct >= 50 ? '#ca8a04' : '#dc2626'));
$caEnabled = $security['ca_enabled'] ?? null;
$nonComp   = $security['non_compliant'] ?? null;
$alerts    = $security['unresolved_alerts'] ?? null;

$ext = $extended ?? [];
?>

<!-- Widget config button -->
<div class="d-flex justify-content-end mb-2">
    <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="offcanvas" data-bs-target="#widgetConfig">
        <i class="bi bi-layout-three-columns me-1"></i> Widgets
    </button>
</div>

<!-- Offcanvas: widget visibility config -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="widgetConfig" style="width:280px;">
    <div class="offcanvas-header">
        <h6 class="offcanvas-title"><i class="bi bi-layout-three-columns me-2"></i>Dashboard-Widgets</h6>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body">
        <p class="text-muted" style="font-size:12px;">Einstellungen werden im Browser gespeichert.</p>
        <?php foreach ($_widgets as $id => $label): ?>
        <div class="form-check form-switch mb-3">
            <input class="form-check-input widget-toggle" type="checkbox" id="wt-<?= $id ?>" data-widget="<?= $id ?>" checked>
            <label class="form-check-label" for="wt-<?= $id ?>"><?= $e($label) ?></label>
        </div>
        <?php endforeach; ?>
        <button class="btn btn-sm btn-outline-secondary w-100 mt-2" onclick="resetWidgets()">
            <i class="bi bi-arrow-counterclockwise me-1"></i> Zurücksetzen
        </button>
    </div>
</div>

<!-- ── Row 1: Directory & Identity ────────────────────────────── -->
<div id="dash-w-metrics1" class="row g-3 mb-3">
    <div class="col-sm-6 col-xl-3">
        <a href="/users" class="text-decoration-none">
        <div class="metric-card d-flex align-items-center gap-3 h-100">
            <div class="metric-icon" style="background:#eff6ff;">
                <i class="bi bi-people-fill" style="color:#2563eb;"></i>
            </div>
            <div>
                <div class="metric-label">Benutzer gesamt <?= \App\Core\Help::tip('graph_api') ?></div>
                <div class="metric-value"><?= $n($metrics['total_users']) ?> <?= \App\Modules\Dashboard\MetricHistoryService::sparkline('total_users') ?></div>
                <div class="metric-sub"><?= $n($metrics['enabled_users']) ?> aktiv</div>
            </div>
        </div>
        </a>
    </div>
    <div class="col-sm-6 col-xl-3">
        <a href="/licenses" class="text-decoration-none">
        <div class="metric-card d-flex align-items-center gap-3 h-100">
            <div class="metric-icon" style="background:#f0fdf4;">
                <i class="bi bi-award-fill" style="color:#16a34a;"></i>
            </div>
            <div>
                <div class="metric-label">Lizenz-Produkte</div>
                <div class="metric-value"><?= $n($metrics['license_products']) ?></div>
                <div class="metric-sub">Abonnierte SKUs</div>
            </div>
        </div>
        </a>
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
                        ? ($n($security['mfa_registered']) . ' / ' . $n($security['mfa_total']))
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
                <div class="metric-value" style="color:<?= ($caEnabled > 0) ? '#16a34a' : '#dc2626' ?>">
                    <?= $caEnabled !== null ? $n($caEnabled) : '<span class="text-muted fs-6">–</span>' ?>
                </div>
                <div class="metric-sub">
                    <?php if ($caEnabled !== null): ?>
                        aktive Richtlinien<?= ($security['ca_report_only'] ?? 0) > 0 ? ' · ' . $n($security['ca_report_only']) . ' report-only' : '' ?>
                    <?php else: ?>Keine Daten<?php endif ?>
                </div>
            </div>
        </div>
        </a>
    </div>
</div>

<!-- ── Row 2: Devices & Risks ─────────────────────────────────── -->
<div id="dash-w-metrics2" class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <a href="/devices" class="text-decoration-none">
        <div class="metric-card d-flex align-items-center gap-3 h-100">
            <div class="metric-icon" style="background:#fef9c3;">
                <i class="bi bi-phone-fill" style="color:#ca8a04;"></i>
            </div>
            <div>
                <div class="metric-label">Geräte <?= \App\Core\Help::tip('device_compliance') ?></div>
                <div class="metric-value"><?= $n($metrics['total_devices']) ?> <?= \App\Modules\Dashboard\MetricHistoryService::sparkline('total_devices') ?></div>
                <div class="metric-sub">
                    <?php if ($nonComp > 0): ?>
                        <span style="color:#dc2626;"><?= $n($nonComp) ?> nicht konform</span>
                    <?php elseif ($nonComp === 0): ?>
                        <span style="color:#16a34a;">Alle konform</span>
                    <?php else: ?>Intune verwaltet<?php endif ?>
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
                    <?= $n($metrics['risky_users']) ?> <?= \App\Modules\Dashboard\MetricHistoryService::sparkline('risky_users', 7, '#dc2626') ?>
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
                <div class="metric-value"><?= $n($metrics['total_groups']) ?> <?= \App\Modules\Dashboard\MetricHistoryService::sparkline('total_groups') ?></div>
                <div class="metric-sub">
                    <?= $ext['teams_count'] !== null ? $n($ext['teams_count']) . ' Teams' : 'Im Verzeichnis' ?>
                </div>
            </div>
        </div>
        </a>
    </div>
</div>

<!-- ── Charts + Security Status ───────────────────────────────── -->
<div id="dash-w-charts" class="row g-3 mb-4">
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
                    <?php endforeach ?>
                <?php endif ?>
            </div>
        </div>
    </div>

    <div class="col-xl-5">
        <div class="content-card h-100">
            <div class="card-header-custom">
                <i class="bi bi-shield-fill-check text-success"></i>
                <h6>Sicherheitsstatus</h6>
            </div>
            <div class="card-body-custom p-0">
                <?php
                $secureScorePct = ($ext['secure_score'] !== null && $ext['secure_score_max'] > 0)
                    ? round(($ext['secure_score'] / $ext['secure_score_max']) * 100) : null;
                $secItems = [
                    ['label' => 'MFA-Abdeckung',           'href' => '/mfamethods',
                     'ok'    => $mfaPct >= 80,              'warn' => $mfaPct !== null && $mfaPct >= 50 && $mfaPct < 80,
                     'val'   => $mfaPct !== null ? $mfaPct . '%' : '–',
                     'hint'  => $mfaPct !== null ? ($mfaPct >= 80 ? 'Gut' : ($mfaPct >= 50 ? 'Ausbaufähig' : 'Kritisch')) : 'Keine Daten'],
                    ['label' => 'Conditional Access',       'href' => '/conditionalaccess',
                     'ok'    => $caEnabled >= 3,            'warn' => $caEnabled > 0 && $caEnabled < 3,
                     'val'   => $caEnabled !== null ? $caEnabled . ' aktiv' : '–',
                     'hint'  => $caEnabled !== null ? ($caEnabled === 0 ? 'Keine Richtlinien!' : ($caEnabled < 3 ? 'Wenige' : 'Konfiguriert')) : 'Keine Daten'],
                    ['label' => 'Risikobenutzer',           'href' => '/riskysignins',
                     'ok'    => ($metrics['risky_users'] ?? 0) === 0, 'warn' => false,
                     'val'   => $n($metrics['risky_users']),
                     'hint'  => ($metrics['risky_users'] ?? 0) === 0 ? 'Keine Risiken' : 'Prüfen'],
                    ['label' => 'Nicht konforme Geräte',    'href' => '/devices',
                     'ok'    => $nonComp === 0,             'warn' => $nonComp > 0 && $nonComp <= 5,
                     'val'   => $nonComp !== null ? $n($nonComp) : '–',
                     'hint'  => $nonComp === null ? 'Keine Daten' : ($nonComp === 0 ? 'Alle konform' : 'Prüfen')],
                    ['label' => 'Offene Defender Alerts',   'href' => '/defenderalerts',
                     'ok'    => $alerts === 0,              'warn' => $alerts > 0 && $alerts <= 3,
                     'val'   => $alerts !== null ? $n($alerts) : '–',
                     'hint'  => $alerts === null ? 'Keine Daten' : ($alerts === 0 ? 'Keine offen' : 'Prüfen')],
                    ['label' => 'Secure Score',             'href' => '/securescore',
                     'ok'    => $secureScorePct >= 60,      'warn' => $secureScorePct !== null && $secureScorePct >= 40 && $secureScorePct < 60,
                     'val'   => $ext['secure_score'] !== null ? ($ext['secure_score'] . ' / ' . $ext['secure_score_max']) : '–',
                     'hint'  => $secureScorePct !== null ? $secureScorePct . '%' : 'Keine Daten'],
                ];
                foreach ($secItems as $item):
                    $noData = ($item['val'] === '–' || str_contains((string)$item['val'], 'text-muted'));
                    $icon   = $noData ? 'dash-circle' : ($item['ok'] ? 'check-circle-fill' : ($item['warn'] ? 'exclamation-triangle-fill' : 'x-circle-fill'));
                    $color  = $noData ? '#9ca3af' : ($item['ok'] ? '#16a34a' : ($item['warn'] ? '#ca8a04' : '#dc2626'));
                ?>
                <a href="<?= $item['href'] ?>" class="list-group-item list-group-item-action py-2 px-3 d-flex align-items-center gap-2 border-0 border-bottom">
                    <i class="bi bi-<?= $icon ?> flex-shrink-0" style="color:<?= $color ?>;font-size:1rem;"></i>
                    <span class="flex-grow-1" style="font-size:13px;"><?= $item['label'] ?></span>
                    <span class="fw-semibold" style="font-size:13px;color:<?= $color ?>;"><?= $item['val'] ?></span>
                    <span class="text-muted" style="font-size:11px;min-width:70px;text-align:right;"><?= $item['hint'] ?></span>
                </a>
                <?php endforeach ?>
                <div class="px-3 pt-3 pb-2">
                    <a href="/securityposture" class="btn btn-sm btn-outline-secondary w-100">
                        <i class="bi bi-shield-fill-check me-1"></i>Security Posture öffnen
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ── Info Panels ─────────────────────────────────────────────── -->
<div id="dash-w-infopanels" class="row g-3 mb-4">

    <!-- Verzeichnis & Identitäten -->
    <div class="col-md-4">
        <div class="content-card h-100">
            <div class="card-header-custom">
                <i class="bi bi-person-lines-fill text-primary"></i>
                <h6>Verzeichnis &amp; Identitäten</h6>
            </div>
            <div class="card-body-custom p-0">
                <?php
                $dirItems = [
                    ['label' => 'Gastbenutzer',       'href' => '/guestusers',   'val' => $ext['guests'],           'warn' => ($ext['guests'] ?? 0) > 20],
                    ['label' => 'Admin-Zuweisungen',  'href' => '/adminroles',   'val' => $ext['admin_assignments'],'warn' => ($ext['admin_assignments'] ?? 0) > 20],
                    ['label' => 'Teams im Tenant',    'href' => '/teamspolicies','val' => $ext['teams_count'],      'warn' => false],
                    ['label' => 'Gruppen gesamt',     'href' => '/groups',       'val' => $metrics['total_groups'], 'warn' => false],
                    ['label' => 'Inaktive Konten',    'href' => '/staleaccounts','val' => null,                     'warn' => false],
                ];
                foreach ($dirItems as $item):
                    $color   = ($item['val'] !== null && $item['warn']) ? '#ca8a04' : null;
                    $display = $item['val'] !== null ? number_format((int)$item['val']) : '<span class="text-muted small">→ öffnen</span>';
                ?>
                <a href="<?= $item['href'] ?>" class="list-group-item list-group-item-action py-2 px-3 d-flex align-items-center border-0 border-bottom">
                    <span class="flex-grow-1 text-muted" style="font-size:13px;"><?= $item['label'] ?></span>
                    <span class="fw-semibold" style="font-size:14px;<?= $color ? "color:{$color};" : '' ?>"><?= $display ?></span>
                </a>
                <?php endforeach ?>
                <div class="px-3 py-2 d-flex gap-2 flex-wrap">
                    <a href="/users"      class="btn btn-xs btn-outline-secondary" style="font-size:11px;padding:2px 8px;">Benutzer</a>
                    <a href="/guestusers" class="btn btn-xs btn-outline-secondary" style="font-size:11px;padding:2px 8px;">Gäste</a>
                    <a href="/adminroles" class="btn btn-xs btn-outline-secondary" style="font-size:11px;padding:2px 8px;">Admin-Rollen</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Dienste & Kommunikation -->
    <div class="col-md-4">
        <div class="content-card h-100">
            <div class="card-header-custom">
                <i class="bi bi-hdd-network text-info"></i>
                <h6>Dienste &amp; Kommunikation</h6>
            </div>
            <div class="card-body-custom p-0">
                <?php
                $incidentColor = $ext['service_incidents'] > 0 ? '#dc2626' : ($ext['service_incidents'] === 0 ? '#16a34a' : null);
                $svcItems = [
                    ['label' => 'Dienst-Vorfälle',     'href' => '/servicehealth','val' => $ext['service_incidents'],  'color' => $incidentColor,
                     'sub'   => !empty($ext['incident_services']) ? implode(', ', $ext['incident_services']) : null],
                    ['label' => 'Message Center',       'href' => '/msgcenter',    'val' => $ext['msg_center_count'],   'color' => null, 'sub' => 'Aktive Nachrichten'],
                    ['label' => 'Postfächer',           'href' => '/mailboxes',    'val' => null,                       'color' => null, 'sub' => '→ Modul öffnen'],
                    ['label' => 'Secure Score',         'href' => '/securescore',
                     'val'   => $ext['secure_score'] !== null ? ($ext['secure_score'] . ' / ' . $ext['secure_score_max']) : null,
                     'color' => $secureScorePct !== null ? ($secureScorePct >= 60 ? '#16a34a' : ($secureScorePct >= 40 ? '#ca8a04' : '#dc2626')) : null,
                     'sub'   => $secureScorePct !== null ? $secureScorePct . '%' : null],
                    ['label' => 'EXO Migration',        'href' => '/exchangemigration','val' => null, 'color' => null, 'sub' => '→ Readiness prüfen'],
                ];
                foreach ($svcItems as $item):
                    $display = $item['val'] !== null ? (is_int($item['val']) ? number_format($item['val']) : $item['val']) : '–';
                    $c = $item['color'];
                ?>
                <a href="<?= $item['href'] ?>" class="list-group-item list-group-item-action py-2 px-3 d-flex align-items-center border-0 border-bottom">
                    <div class="flex-grow-1">
                        <div class="text-muted" style="font-size:13px;"><?= $item['label'] ?></div>
                        <?php if ($item['sub'] ?? null): ?>
                        <div class="text-muted" style="font-size:11px;"><?= $e($item['sub']) ?></div>
                        <?php endif ?>
                    </div>
                    <span class="fw-semibold" style="font-size:14px;<?= $c ? "color:{$c};" : '' ?>"><?= $display ?></span>
                </a>
                <?php endforeach ?>
                <div class="px-3 py-2 d-flex gap-2 flex-wrap">
                    <a href="/servicehealth" class="btn btn-xs btn-outline-secondary" style="font-size:11px;padding:2px 8px;">Dienststatus</a>
                    <a href="/msgcenter"     class="btn btn-xs btn-outline-secondary" style="font-size:11px;padding:2px 8px;">Message Center</a>
                    <a href="/mailflow"      class="btn btn-xs btn-outline-secondary" style="font-size:11px;padding:2px 8px;">Mail Flow</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Nutzungsaktivität -->
    <div class="col-md-4">
        <div class="content-card h-100">
            <div class="card-header-custom">
                <i class="bi bi-graph-up-arrow text-success"></i>
                <h6>Nutzungsaktivität <span class="text-muted fw-normal small">(30 Tage)</span></h6>
            </div>
            <div class="card-body-custom p-0">
                <?php
                $adoptItems = [
                    ['label' => 'Exchange Online',  'href' => '/adoption', 'val' => $ext['adoption_exchange'],   'icon' => 'envelope',     'color' => '#2563eb'],
                    ['label' => 'Microsoft Teams',  'href' => '/adoption', 'val' => $ext['adoption_teams'],      'icon' => 'chat-dots',    'color' => '#7c3aed'],
                    ['label' => 'OneDrive',         'href' => '/adoption', 'val' => $ext['adoption_onedrive'],   'icon' => 'cloud',        'color' => '#0891b2'],
                    ['label' => 'SharePoint',       'href' => '/adoption', 'val' => $ext['adoption_sharepoint'], 'icon' => 'share',        'color' => '#059669'],
                ];
                $totalUsers = $metrics['total_users'] ?? 0;
                foreach ($adoptItems as $item):
                    $pct = ($item['val'] !== null && $totalUsers > 0)
                        ? round(($item['val'] / $totalUsers) * 100) : null;
                ?>
                <a href="<?= $item['href'] ?>" class="list-group-item list-group-item-action py-2 px-3 border-0 border-bottom">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <i class="bi bi-<?= $item['icon'] ?>" style="color:<?= $item['color'] ?>;font-size:.85rem;"></i>
                        <span class="text-muted flex-grow-1" style="font-size:13px;"><?= $item['label'] ?></span>
                        <span class="fw-semibold" style="font-size:14px;"><?= $n($item['val']) ?></span>
                        <?php if ($pct !== null): ?>
                        <span class="text-muted" style="font-size:11px;min-width:35px;text-align:right;"><?= $pct ?>%</span>
                        <?php endif ?>
                    </div>
                    <?php if ($pct !== null): ?>
                    <div class="progress" style="height:3px;">
                        <div class="progress-bar" style="width:<?= $pct ?>%;background:<?= $item['color'] ?>;"></div>
                    </div>
                    <?php endif ?>
                </a>
                <?php endforeach ?>
                <div class="px-3 py-2 d-flex gap-2 flex-wrap">
                    <a href="/adoption"   class="btn btn-xs btn-outline-secondary" style="font-size:11px;padding:2px 8px;">Adoptions-Report</a>
                    <a href="/teamsusage" class="btn btn-xs btn-outline-secondary" style="font-size:11px;padding:2px 8px;">Teams-Nutzung</a>
                    <a href="/onedrive"   class="btn btn-xs btn-outline-secondary" style="font-size:11px;padding:2px 8px;">OneDrive</a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ── Quick Access ────────────────────────────────────────────── -->
<div id="dash-w-quicklinks" class="content-card mb-2">
    <div class="card-header-custom">
        <i class="bi bi-grid text-secondary"></i>
        <h6>Schnellzugriff</h6>
    </div>
    <div class="card-body-custom">
        <div class="d-flex flex-wrap gap-2">
            <a href="/users"             class="btn btn-sm btn-outline-primary"><i class="bi bi-people me-1"></i>Benutzer</a>
            <a href="/licenses"          class="btn btn-sm btn-outline-success"><i class="bi bi-award me-1"></i>Lizenzen</a>
            <a href="/licenseadvisor"    class="btn btn-sm btn-outline-success"><i class="bi bi-lightbulb me-1"></i>Lizenz-Berater</a>
            <a href="/conditionalaccess" class="btn btn-sm btn-outline-warning"><i class="bi bi-shield-shaded me-1"></i>Conditional Access</a>
            <a href="/namedlocations"    class="btn btn-sm btn-outline-warning"><i class="bi bi-geo-alt me-1"></i>Named Locations</a>
            <a href="/devices"           class="btn btn-sm btn-outline-secondary"><i class="bi bi-phone me-1"></i>Geräte</a>
            <a href="/offboarding"       class="btn btn-sm btn-outline-secondary"><i class="bi bi-person-dash me-1"></i>Offboarding</a>
            <a href="/signinlog"         class="btn btn-sm btn-outline-secondary"><i class="bi bi-clock-history me-1"></i>Sign-in-Log</a>
            <a href="/sharing"           class="btn btn-sm btn-outline-secondary"><i class="bi bi-link-45deg me-1"></i>Freigaben</a>
            <a href="/securescore"       class="btn btn-sm btn-outline-secondary"><i class="bi bi-bar-chart me-1"></i>Secure Score</a>
        </div>
    </div>
</div>

<script>
(function () {
    const KEY = 'dash_widgets';
    const widgets = <?= json_encode(array_keys($_widgets)) ?>;

    function load() {
        try { return JSON.parse(localStorage.getItem(KEY) || '{}'); } catch { return {}; }
    }
    function save(state) { localStorage.setItem(KEY, JSON.stringify(state)); }

    function applyState(state) {
        widgets.forEach(id => {
            const el = document.getElementById(id);
            if (el) el.style.display = state[id] === false ? 'none' : '';
            const cb = document.getElementById('wt-' + id);
            if (cb) cb.checked = state[id] !== false;
        });
    }

    const state = load();
    applyState(state);

    document.querySelectorAll('.widget-toggle').forEach(cb => {
        cb.addEventListener('change', function () {
            const s = load();
            s[this.dataset.widget] = this.checked;
            save(s);
            applyState(s);
        });
    });

    window.resetWidgets = function () {
        localStorage.removeItem(KEY);
        applyState({});
    };
})();
</script>
