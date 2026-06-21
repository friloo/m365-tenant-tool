<?php use App\Core\View; $e = fn($v) => View::escape($v); ?>

<?php if (!empty($flash)): ?>
    <div class="alert alert-success mb-3"><i class="bi bi-check-circle me-2"></i><?= $e($flash) ?></div>
<?php endif; ?>
<?php if (!empty($error)): ?>
    <div class="alert alert-danger mb-3"><i class="bi bi-exclamation-circle me-2"></i><?= $e($error) ?></div>
<?php endif; ?>

<!-- Metric Cards -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-md-3 col-lg">
        <div class="metric-card">
            <div class="metric-label"><?= te('Gesamt') ?></div>
            <div class="metric-value"><?= $stats['total'] ?></div>
            <div class="metric-sub"><?= te('Anmeldungen') ?></div>
        </div>
    </div>
    <div class="col-sm-6 col-md-3 col-lg">
        <div class="metric-card">
            <div class="metric-label"><?= te('Erfolgreich') ?></div>
            <div class="metric-value" style="color:#16a34a;"><?= $stats['success'] ?></div>
            <div class="metric-sub"><?= te('Anmeldungen OK') ?></div>
        </div>
    </div>
    <div class="col-sm-6 col-md-3 col-lg">
        <div class="metric-card">
            <div class="metric-label">Fehlgeschlagen</div>
            <div class="metric-value" style="color:<?= $stats['failure'] > 0 ? '#dc2626' : '#111827' ?>;">
                <?= $stats['failure'] ?>
            </div>
            <div class="metric-sub"><?= $stats['failure'] > 0 ? 'Fehler aufgetreten' : 'Keine Fehler' ?></div>
        </div>
    </div>
    <div class="col-sm-6 col-md-3 col-lg">
        <div class="metric-card">
            <div class="metric-label">Eindeutige Benutzer</div>
            <div class="metric-value"><?= $stats['unique_users'] ?></div>
            <div class="metric-sub">verschiedene Konten</div>
        </div>
    </div>
    <div class="col-sm-6 col-md-3 col-lg">
        <div class="metric-card">
            <div class="metric-label">Eindeutige IPs</div>
            <div class="metric-value"><?= $stats['unique_ips'] ?></div>
            <div class="metric-sub">verschiedene Adressen</div>
        </div>
    </div>
</div>

<!-- Filter Bar -->
<?php
$activeFilters = count(array_filter([
    $filters['user']    ?? '',
    $filters['status']  ?? '',
    $filters['app']     ?? '',
    $filters['country'] ?? '',
    $filters['risk']    ?? '',
    (($filters['days'] ?? '7') !== '7') ? $filters['days'] : '',
]));

// Build export query string from current filters
$exportParams = http_build_query(array_filter([
    'user'    => $filters['user']    ?? '',
    'status'  => $filters['status']  ?? '',
    'app'     => $filters['app']     ?? '',
    'country' => $filters['country'] ?? '',
    'risk'    => $filters['risk']    ?? '',
    'days'    => $filters['days']    ?? '7',
]));
?>
<div class="content-card mb-4">
    <div class="card-header-custom">
        <span>
            <i class="bi bi-funnel me-2"></i>Filter
            <?php if ($activeFilters > 0): ?>
                <span class="badge-pill badge-info ms-1"><?= $activeFilters ?></span>
            <?php endif; ?>
        </span>
    </div>
    <div class="card-body-custom">
        <form method="get" action="/signinlog" class="row g-2 align-items-end">
            <div class="col-12 col-sm-6 col-md-3 col-lg-2">
                <label class="form-label form-label-sm small fw-medium mb-1">Benutzer</label>
                <input type="text"
                       name="user"
                       class="form-control form-control-sm"
                       placeholder="Name oder UPN"
                       value="<?= $e($filters['user'] ?? '') ?>">
            </div>
            <div class="col-6 col-sm-4 col-md-2 col-lg-1">
                <label class="form-label form-label-sm small fw-medium mb-1">Zeitraum</label>
                <select name="days" class="form-select form-select-sm">
                    <?php foreach ([1 => '1 Tag', 7 => '7 Tage', 14 => '14 Tage', 30 => '30 Tage'] as $val => $label): ?>
                        <option value="<?= $val ?>" <?= (int)($filters['days'] ?? 7) === $val ? 'selected' : '' ?>>
                            <?= $label ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-6 col-sm-4 col-md-2 col-lg-1">
                <label class="form-label form-label-sm small fw-medium mb-1">Ergebnis</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="" <?= ($filters['status'] ?? '') === '' ? 'selected' : '' ?>>Alle</option>
                    <option value="success" <?= ($filters['status'] ?? '') === 'success' ? 'selected' : '' ?>>Nur Erfolg</option>
                    <option value="failure" <?= ($filters['status'] ?? '') === 'failure' ? 'selected' : '' ?>>Nur Fehler</option>
                </select>
            </div>
            <div class="col-6 col-sm-4 col-md-2 col-lg-2">
                <label class="form-label form-label-sm small fw-medium mb-1">App</label>
                <select name="app" class="form-select form-select-sm">
                    <option value="">Alle</option>
                    <?php foreach ($apps as $appName): ?>
                        <option value="<?= $e($appName) ?>" <?= ($filters['app'] ?? '') === $appName ? 'selected' : '' ?>>
                            <?= $e($appName) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-6 col-sm-4 col-md-2 col-lg-2">
                <label class="form-label form-label-sm small fw-medium mb-1">Land</label>
                <select name="country" class="form-select form-select-sm">
                    <option value="">Alle</option>
                    <?php foreach ($countries as $country): ?>
                        <option value="<?= $e($country) ?>" <?= ($filters['country'] ?? '') === $country ? 'selected' : '' ?>>
                            <?= $e($country) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-6 col-sm-4 col-md-2 col-lg-1">
                <label class="form-label form-label-sm small fw-medium mb-1">Risiko</label>
                <select name="risk" class="form-select form-select-sm">
                    <option value="" <?= ($filters['risk'] ?? '') === '' ? 'selected' : '' ?>>Alle</option>
                    <option value="none"   <?= ($filters['risk'] ?? '') === 'none'   ? 'selected' : '' ?>>Keine</option>
                    <option value="low"    <?= ($filters['risk'] ?? '') === 'low'    ? 'selected' : '' ?>>Niedrig</option>
                    <option value="medium" <?= ($filters['risk'] ?? '') === 'medium' ? 'selected' : '' ?>>Mittel</option>
                    <option value="high"   <?= ($filters['risk'] ?? '') === 'high'   ? 'selected' : '' ?>>Hoch</option>
                </select>
            </div>
            <div class="col-12 col-lg d-flex align-items-end gap-2 flex-wrap">
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="bi bi-search me-1"></i>Filtern
                </button>
                <a href="/signinlog" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-x-circle me-1"></i>Zurücksetzen
                </a>
                <a href="/signinlog/export<?= $exportParams ? '?' . $e($exportParams) : '' ?>"
                   class="btn btn-outline-success btn-sm ms-auto">
                    <i class="bi bi-download me-1"></i>CSV-Export
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Top Apps + Top Länder -->
<?php if (!empty($stats['top_apps']) || !empty($stats['top_countries'])): ?>
<div class="row g-3 mb-4">
    <!-- Top Apps -->
    <div class="col-md-6">
        <div class="content-card h-100">
            <div class="card-header-custom">
                <span><i class="bi bi-grid me-2"></i>Top Apps</span>
            </div>
            <div class="card-body-custom p-0">
                <?php if (empty($stats['top_apps'])): ?>
                    <div class="empty-state py-3"><span class="text-muted small">Keine Daten</span></div>
                <?php else: ?>
                    <?php $maxApp = max($stats['top_apps']); ?>
                    <table class="data-table" style="margin-bottom:0;">
                        <tbody>
                            <?php foreach ($stats['top_apps'] as $appName => $count): ?>
                                <?php $pct = $maxApp > 0 ? (int)min(100, round($count / $maxApp * 100)) : 0; ?>
                                <tr>
                                    <td style="font-size:12px;max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                        <?= $e($appName) ?>
                                    </td>
                                    <td class="text-end" style="font-size:12px;font-weight:500;width:45px;"><?= $count ?></td>
                                    <td style="width:100px;">
                                        <div class="progress-custom" style="margin-bottom:0;">
                                            <div class="bar" style="width:<?= $pct ?>%;background:#3b82f6;"></div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Top Länder -->
    <div class="col-md-6">
        <div class="content-card h-100">
            <div class="card-header-custom">
                <span><i class="bi bi-globe me-2"></i>Top Länder</span>
            </div>
            <div class="card-body-custom p-0">
                <?php if (empty($stats['top_countries'])): ?>
                    <div class="empty-state py-3"><span class="text-muted small">Keine Daten</span></div>
                <?php else: ?>
                    <?php $maxCountry = max($stats['top_countries']); ?>
                    <table class="data-table" style="margin-bottom:0;">
                        <tbody>
                            <?php foreach ($stats['top_countries'] as $countryName => $count): ?>
                                <?php $pct = $maxCountry > 0 ? (int)min(100, round($count / $maxCountry * 100)) : 0; ?>
                                <tr>
                                    <td style="font-size:12px;max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                        <?= $e($countryName) ?>
                                    </td>
                                    <td class="text-end" style="font-size:12px;font-weight:500;width:45px;"><?= $count ?></td>
                                    <td style="width:100px;">
                                        <div class="progress-custom" style="margin-bottom:0;">
                                            <div class="bar" style="width:<?= $pct ?>%;background:#16a34a;"></div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Sign-in Table -->
<div class="content-card">
    <div class="table-toolbar">
        <input type="text" id="signinSearch" class="search-box" placeholder="Tabelle durchsuchen…">
        <span class="ms-auto text-muted small">
            <?= count($logs) ?> Einträge
            <?php if (count($logs) >= 200): ?>
                <span class="badge-warning ms-1">Limit: 200</span>
            <?php endif; ?>
        </span>
    </div>

    <?php if (empty($logs)): ?>
        <?php
        $hasActiveFilter = array_filter([
            $filters['user']    ?? '',
            $filters['status']  ?? '',
            $filters['app']     ?? '',
            $filters['country'] ?? '',
            $filters['risk']    ?? '',
        ]);
        ?>
        <div class="card-body-custom">
            <div class="empty-state">
                <i class="bi bi-<?= $hasActiveFilter ? 'search' : 'journal-x' ?>"></i>
                <?php if ($hasActiveFilter): ?>
                    <p>Keine Ergebnisse für diese Filter<br>
                    <a href="/signinlog" class="text-muted small">Filter zurücksetzen</a></p>
                <?php elseif (!empty($diag)): ?>
                    <p class="fw-semibold" style="color:#b45309;"><?= $e($diag['short']) ?></p>
                    <p style="font-size:13px;color:#6b7280;max-width:560px;margin:0 auto;">
                        <?= $e($diag['detail']) ?>
                    </p>
                    <?php if (!empty($diag['fix_url'])): ?>
                        <a href="<?= $e($diag['fix_url']) ?>" class="btn btn-sm btn-outline-secondary mt-3">
                            <i class="bi bi-arrow-right-circle me-1"></i>Zur Lösung
                        </a>
                    <?php endif; ?>
                <?php else: ?>
                    <p>Keine Anmeldedaten im gewählten Zeitraum</p>
                <?php endif; ?>
            </div>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="data-table" id="signinTable">
                <thead>
                    <tr>
                        <th>Datum/Uhrzeit</th>
                        <th>Benutzer</th>
                        <th>App</th>
                        <th>IP / Standort</th>
                        <th>Gerät (OS)</th>
                        <th>Ergebnis</th>
                        <th>Risiko</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs as $log):
                        $success       = ($log['status']['errorCode'] ?? 1) === 0;
                        $errorCode     = $log['status']['errorCode'] ?? null;
                        $failureReason = $log['status']['failureReason'] ?? '';
                        $risk          = strtolower($log['riskLevelDuringSignIn'] ?? 'none');
                        $caStatus      = $log['conditionalAccessStatus'] ?? '';
                        $loc           = $log['location'] ?? [];
                        $city          = $loc['city'] ?? '';
                        $countryCode   = $loc['countryOrRegion'] ?? '';
                        $locStr        = trim(implode(', ', array_filter([$city, $countryCode])));
                        $os            = $log['deviceDetail']['operatingSystem'] ?? '';
                        $createdDt     = !empty($log['createdDateTime'])
                            ? date('d.m.Y H:i', strtotime($log['createdDateTime']))
                            : '–';
                        $truncatedReason = mb_strlen($failureReason) > 40
                            ? mb_substr($failureReason, 0, 40) . '…'
                            : $failureReason;
                    ?>
                    <tr>
                        <td style="font-size:11px;color:#6b7280;white-space:nowrap;">
                            <?= $createdDt ?>
                        </td>
                        <td>
                            <div style="font-size:13px;font-weight:500;"><?= $e($log['userDisplayName'] ?? '') ?></div>
                            <div style="font-size:11px;color:#9ca3af;"><?= $e($log['userPrincipalName'] ?? '') ?></div>
                        </td>
                        <td style="font-size:12px;color:#374151;"><?= $e($log['appDisplayName'] ?? '–') ?></td>
                        <td>
                            <div style="font-size:12px;font-family:monospace;color:#6b7280;"><?= $e($log['ipAddress'] ?? '–') ?></div>
                            <?php if ($locStr): ?>
                                <div style="font-size:11px;color:#9ca3af;"><?= $e($locStr) ?></div>
                            <?php endif; ?>
                        </td>
                        <td style="font-size:12px;color:#6b7280;"><?= $os ? $e($os) : '–' ?></td>
                        <td>
                            <?php if ($success): ?>
                                <span class="badge-enabled">Erfolgreich</span>
                            <?php else: ?>
                                <span class="badge-disabled">Fehlgeschlagen</span>
                                <?php if ($truncatedReason): ?>
                                    <div style="font-size:10px;color:#9ca3af;margin-top:2px;"
                                         title="<?= $e($failureReason) ?>">
                                        <?= $e($truncatedReason) ?>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                            <?php if ($caStatus === 'failure'): ?>
                                <span class="badge-danger ms-1" style="font-size:10px;">CA-Fehler</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($risk === 'high'): ?>
                                <span class="badge-danger">Hoch</span>
                            <?php elseif ($risk === 'medium'): ?>
                                <span class="badge-warning">Mittel</span>
                            <?php elseif ($risk === 'low'): ?>
                                <span class="badge-info">Niedrig</span>
                            <?php else: ?>
                                <span class="text-muted" style="font-size:12px;">–</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php if (count($logs) >= 200): ?>
            <div class="card-body-custom pt-2 pb-2">
                <p class="text-muted small mb-0">
                    <i class="bi bi-info-circle me-1"></i>
                    Es werden maximal 200 Einträge angezeigt. Verwenden Sie engere Filter oder den CSV-Export für vollständige Daten.
                </p>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php if (!empty($logs)): ?>
<script>initTableSearch('signinSearch', 'signinTable');</script>
<?php endif; ?>
