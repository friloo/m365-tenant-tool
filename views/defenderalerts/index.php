<?php use App\Auth\LocalAuth; use App\Core\View; $e = fn($v) => View::escape($v); ?>

<?php if (!empty($flash)): ?>
    <div class="alert alert-success mb-3"><i class="bi bi-check-circle me-2"></i><?= $e($flash) ?></div>
<?php endif; ?>
<?php if (!empty($error)): ?>
    <div class="alert alert-danger mb-3"><i class="bi bi-exclamation-circle me-2"></i><?= $e($error) ?></div>
<?php endif; ?>

<!-- Metric Cards -->
<div class="row g-3 mb-4">
    <div class="col-sm-3">
        <div class="metric-card">
            <div class="metric-label">Gesamt offen</div>
            <div class="metric-value"><?= count($alerts) ?></div>
            <div class="metric-sub">Aktive Warnungen</div>
        </div>
    </div>
    <div class="col-sm-3">
        <div class="metric-card">
            <div class="metric-label">Kritisch</div>
            <div class="metric-value" style="color:<?= ($stats['bySeverity']['high'] ?? 0) > 0 ? '#dc2626' : '#111827' ?>;">
                <?= $stats['bySeverity']['high'] ?? 0 ?>
            </div>
            <div class="metric-sub">Hohe Schwere</div>
        </div>
    </div>
    <div class="col-sm-3">
        <div class="metric-card">
            <div class="metric-label">Mittel</div>
            <div class="metric-value" style="color:<?= ($stats['bySeverity']['medium'] ?? 0) > 0 ? '#d97706' : '#111827' ?>;">
                <?= $stats['bySeverity']['medium'] ?? 0 ?>
            </div>
            <div class="metric-sub">Mittlere Schwere</div>
        </div>
    </div>
    <div class="col-sm-3">
        <div class="metric-card">
            <div class="metric-label">Niedrig</div>
            <div class="metric-value"><?= $stats['bySeverity']['low'] ?? 0 ?></div>
            <div class="metric-sub">Niedrige Schwere</div>
        </div>
    </div>
</div>

<?php if (empty($alerts)): ?>
    <!-- Permission / no-data empty state -->
    <div class="content-card">
        <div class="card-body-custom">
            <?php
            if (!empty($diag ?? null)) {
                $diagStyle = 'empty';
                $diagIcon  = 'shield-slash';
                $diagTitle = 'Keine Defender-Daten verfügbar';
                include BASE_PATH . '/views/partials/graph_diagnostic.php';
            } else { ?>
                <div class="empty-state">
                    <i class="bi bi-shield-slash"></i>
                    <p>Aktuell keine Defender-Warnungen — alles ruhig.</p>
                </div>
            <?php } ?>
        </div>
    </div>
<?php else: ?>

    <?php $highCount = $stats['bySeverity']['high'] ?? 0; ?>
    <?php if ($highCount > 0): ?>
        <div class="alert alert-danger d-flex align-items-center mb-4">
            <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
            <strong><?= $highCount ?> kritische Sicherheitswarnungen erfordern sofortige Aufmerksamkeit</strong>
        </div>
    <?php endif; ?>

    <!-- Alerts Table -->
    <div class="content-card">
        <div class="card-header-custom">
            <span><i class="bi bi-shield-exclamation me-2"></i>Aktive Sicherheitswarnungen</span>
        </div>
        <div class="card-body-custom">
            <div class="table-toolbar">
                <input type="text" id="alertsSearch" class="search-box" placeholder="Warnungen suchen…">
            </div>
            <div class="table-responsive">
                <table class="data-table" id="alertsTable">
                    <thead>
                        <tr>
                            <th>Schweregrad</th>
                            <th>Titel</th>
                            <th>Kategorie</th>
                            <th>Erstellt am</th>
                            <th>Letztes Update</th>
                            <th>Status</th>
                            <?php if (LocalAuth::isAdmin()): ?>
                                <th>Aktionen</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($alerts as $alert):
                            $severity   = strtolower($alert['severity'] ?? '');
                            $meta       = $service::severityMeta($severity);
                            $status     = $alert['status'] ?? '';
                            $created    = $alert['createdDateTime'] ?? null;
                            $updated    = $alert['lastUpdateDateTime'] ?? null;
                            $alertId    = $alert['id'] ?? '';
                            $category   = $alert['category'] ?? '–';
                            $title      = $alert['title'] ?? '–';
                        ?>
                        <tr>
                            <td>
                                <span class="<?= $e($meta['badge']) ?> badge-pill">
                                    <?= $e($meta['label']) ?>
                                </span>
                            </td>
                            <td>
                                <div style="font-size:13px;font-weight:500;max-width:300px;">
                                    <?= $e($title) ?>
                                </div>
                                <?php if (!empty($alert['assignedTo'])): ?>
                                    <div style="font-size:11px;color:#9ca3af;">
                                        <i class="bi bi-person me-1"></i><?= $e($alert['assignedTo']) ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td style="font-size:12px;color:#6b7280;"><?= $e($category) ?></td>
                            <td style="font-size:12px;color:#6b7280;white-space:nowrap;">
                                <?= $created ? date('d.m.Y H:i', strtotime($created)) : '–' ?>
                            </td>
                            <td style="font-size:12px;color:#6b7280;white-space:nowrap;">
                                <?= $updated ? date('d.m.Y H:i', strtotime($updated)) : '–' ?>
                            </td>
                            <td>
                                <?php if ($status === 'new'): ?>
                                    <span class="badge-danger badge-pill">Neu</span>
                                <?php elseif ($status === 'inProgress'): ?>
                                    <span class="badge-warning badge-pill">In Bearbeitung</span>
                                <?php elseif ($status === 'resolved'): ?>
                                    <span class="badge-success badge-pill">Gelöst</span>
                                <?php else: ?>
                                    <span class="badge-secondary badge-pill"><?= $e($status ?: '–') ?></span>
                                <?php endif; ?>
                            </td>
                            <?php if (LocalAuth::isAdmin()): ?>
                            <td>
                                <?php if ($status !== 'resolved'): ?>
                                    <form method="POST"
                                          action="/defenderalerts/<?= $e($alertId) ?>/resolve"
                                          onsubmit="return confirm('Warnung als gelöst markieren?');">
                                        <?= \App\Core\Csrf::field() ?>
                                        <button type="submit"
                                                class="btn btn-sm btn-outline-success"
                                                style="font-size:11px;padding:2px 8px;">
                                            <i class="bi bi-check2 me-1"></i>Lösen
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <span style="font-size:11px;color:#9ca3af;">–</span>
                                <?php endif; ?>
                            </td>
                            <?php endif; ?>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

<?php endif; ?>

<script>
initTableSearch('alertsSearch', 'alertsTable');
</script>
