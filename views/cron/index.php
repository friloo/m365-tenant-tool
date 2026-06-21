<?php use App\Core\View; $e = fn($v) => View::escape($v); ?>

<?php
function cronIntervalLabel(int $minutes): string {
    if ($minutes === 1)    return t('Jede Minute');
    if ($minutes < 60)     return t('Alle :n Min.', ['n' => $minutes]);
    if ($minutes === 60)   return t('Stündlich');
    if ($minutes < 1440)   return t('Alle :n Std.', ['n' => $minutes / 60]);
    if ($minutes === 1440) return t('Täglich');
    return t('Alle :n Tage', ['n' => round($minutes / 1440)]);
}
function cronAgo(?string $dt): string {
    if (!$dt) return '–';
    $diff = time() - strtotime($dt);
    if ($diff < 60)   return t('Gerade eben');
    if ($diff < 3600) return t('Vor :n Min.', ['n' => floor($diff / 60)]);
    if ($diff < 86400) return t('Vor :n Std.', ['n' => floor($diff / 3600)]);
    return date('d.m.Y H:i', strtotime($dt));
}
function cronIn(?string $dt): string {
    if (!$dt) return '–';
    $diff = strtotime($dt) - time();
    if ($diff <= 0)   return t('Überfällig');
    if ($diff < 60)   return t('In < 1 Min.');
    if ($diff < 3600) return t('In :n Min.', ['n' => ceil($diff / 60)]);
    return t('In :n Std.', ['n' => ceil($diff / 3600)]);
}
?>

<!-- Cron setup info -->
<div class="content-card mb-4">
    <div class="card-header-custom">
        <i class="bi bi-terminal text-primary"></i>
        <h6><?= te('Cron-Job einrichten') ?></h6>
        <span class="ms-auto badge-info badge-pill"><?= te('Einmalig auf dem Server') ?></span>
    </div>
    <div class="card-body-custom">
        <p class="text-muted small mb-3">
            <?= te('Füge folgenden Eintrag in die') ?> <strong><?= te('crontab des Webserver-Benutzers') ?></strong> (<code>www-data</code>) <?= te('ein. Der Cron läuft jede Minute und steuert alle Aufgaben intern über die konfigurierten Intervalle.') ?>
        </p>
        <div class="d-flex align-items-center gap-2 mb-3">
            <code class="flex-1 d-block p-3 rounded" id="cronCmd"
                  style="background:#1a1a2e;color:#c8ccd6;font-size:13px;word-break:break-all;">
                * * * * * php <?= $e(BASE_PATH) ?>/run-cron.php >> /var/log/m365-cron.log 2>&1
            </code>
            <button class="btn btn-sm btn-outline-secondary flex-shrink-0"
                    onclick="navigator.clipboard.writeText(document.getElementById('cronCmd').textContent.trim()); showToast('<?= te('Kopiert!') ?>')">
                <i class="bi bi-clipboard"></i>
            </button>
        </div>
        <p class="text-muted small mb-0">
            <i class="bi bi-info-circle me-1"></i>
            <?= te('Bearbeiten mit:') ?> <code>crontab -u www-data -e</code>
        </p>
    </div>
</div>

<!-- Queue stats row -->
<div class="row g-3 mb-4">
    <div class="col-sm-3">
        <div class="metric-card">
            <div class="metric-label"><?= te('Ausstehend') ?></div>
            <div class="metric-value"><?= number_format($queueStats['pending']) ?></div>
            <div class="metric-sub"><?= te('Warteschlange') ?></div>
        </div>
    </div>
    <div class="col-sm-3">
        <div class="metric-card">
            <div class="metric-label"><?= te('In Bearbeitung') ?></div>
            <div class="metric-value"><?= number_format($queueStats['processing']) ?></div>
            <div class="metric-sub"><?= te('Wird verarbeitet') ?></div>
        </div>
    </div>
    <div class="col-sm-3">
        <div class="metric-card">
            <div class="metric-label"><?= te('Erledigt (24h)') ?></div>
            <div class="metric-value"><?= number_format($queueStats['done']) ?></div>
            <div class="metric-sub"><?= te('Erfolgreich') ?></div>
        </div>
    </div>
    <div class="col-sm-3">
        <div class="metric-card">
            <div class="metric-label"><?= te('Fehlgeschlagen') ?></div>
            <div class="metric-value <?= $queueStats['failed'] > 0 ? 'text-danger' : '' ?>">
                <?= number_format($queueStats['failed']) ?>
            </div>
            <div class="metric-sub">
                <?php if ($queueStats['failed'] > 0): ?>
                    <form method="post" action="/cron/queue/retry" class="d-inline">
                        <?= \App\Core\Csrf::field() ?>
                        <button class="btn btn-link btn-sm p-0" style="font-size:11px;"><?= te('Alle wiederholen') ?></button>
                    </form>
                <?php else: ?>
                    <?= te('Alle OK') ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Scheduled jobs -->
<div class="content-card mb-4">
    <div class="card-header-custom">
        <i class="bi bi-clock text-primary"></i>
        <h6><?= te('Geplante Aufgaben') ?></h6>
        <span class="ms-auto text-muted" style="font-size:12px;"><?= count($jobs) ?> Jobs</span>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th><?= te('Aufgabe') ?></th>
                <th><?= te('Status') ?></th>
                <th><?= te('Letzter Lauf') ?></th>
                <th><?= te('Nächster Lauf') ?></th>
                <th><?= te('Intervall') ?></th>
                <th style="width:160px;"><?= te('Aktionen') ?></th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($jobs as $job): ?>
            <?php
            $enabled      = (bool)($job['enabled'] ?? true);
            $lastStatus   = $job['last_run_status'] ?? null;
            $interval     = (int)($job['interval_minutes'] ?? $job['default_interval']);
            $jobKey       = $job['job_key'];
            ?>
            <tr>
                <td>
                    <div style="font-weight:500;"><?= $e($job['label']) ?></div>
                    <div style="font-size:11px;color:#9ca3af;max-width:380px;"><?= $e($job['description']) ?></div>
                </td>
                <td>
                    <?php if (!$enabled): ?>
                        <span class="badge-disabled"><?= te('Deaktiviert') ?></span>
                    <?php elseif ($lastStatus === 'success'): ?>
                        <span class="badge-enabled">OK</span>
                    <?php elseif ($lastStatus === 'error'): ?>
                        <span class="badge-danger" title="<?= $e($job['last_run_log'] ?? '') ?>"><?= te('Fehler') ?></span>
                    <?php else: ?>
                        <span class="badge-neutral"><?= te('Noch nicht gelaufen') ?></span>
                    <?php endif; ?>
                    <?php if ($job['last_run_seconds']): ?>
                        <span style="font-size:10px;color:#9ca3af;margin-left:4px;"><?= number_format((float)$job['last_run_seconds'], 1) ?>s</span>
                    <?php endif; ?>
                    <?php if ($lastStatus === 'error' && !empty($job['last_run_log'])): ?>
                        <div style="font-size:10px;color:#ef4444;max-width:260px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"
                             title="<?= $e($job['last_run_log']) ?>"><?= $e(substr($job['last_run_log'], 0, 80)) ?></div>
                    <?php elseif (!empty($job['last_run_log'])): ?>
                        <div style="font-size:10px;color:#9ca3af;max-width:260px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= $e($job['last_run_log']) ?></div>
                    <?php endif; ?>
                </td>
                <td style="font-size:12px;color:#6b7280;"><?= cronAgo($job['last_run_at']) ?></td>
                <td style="font-size:12px;color:#6b7280;">
                    <?= $enabled ? cronIn($job['next_run_at']) : '–' ?>
                </td>
                <td style="font-size:12px;">
                    <span class="badge-info badge-pill"><?= $e(cronIntervalLabel($interval)) ?></span>
                </td>
                <td>
                    <div class="d-flex gap-1 flex-wrap">
                        <!-- Run now -->
                        <form method="post" action="/cron/run-job/<?= $e($jobKey) ?>">
                            <?= \App\Core\Csrf::field() ?>
                            <button type="submit" class="btn btn-sm btn-outline-primary"
                                    title="<?= te('Jetzt ausführen') ?>"
                                    onclick="return confirm('<?= te('Job jetzt ausführen?') ?>')">
                                <i class="bi bi-play"></i>
                            </button>
                        </form>
                        <!-- Configure -->
                        <button type="button" class="btn btn-sm btn-outline-secondary"
                                title="<?= te('Konfigurieren') ?>"
                                onclick="openJobConfig('<?= $e($jobKey) ?>', <?= (int)$enabled ?>, <?= $interval ?>)">
                            <i class="bi bi-gear"></i>
                        </button>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Job Queue recent items -->
<?php if (!empty($queueItems)): ?>
<div class="content-card mb-4">
    <div class="card-header-custom">
        <i class="bi bi-list-task text-secondary"></i>
        <h6><?= te('Job-Warteschlange (letzte 30 Einträge)') ?></h6>
        <?php if ($queueStats['done'] > 0): ?>
        <form method="post" action="/cron/queue/prune" class="ms-auto">
            <?= \App\Core\Csrf::field() ?>
            <button type="submit" class="btn btn-sm btn-outline-secondary"
                    onclick="return confirm('<?= te('Abgeschlossene Jobs löschen?') ?>')">
                <i class="bi bi-trash me-1"></i> <?= te('Erledigte löschen') ?>
            </button>
        </form>
        <?php endif; ?>
    </div>
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th><?= te('ID') ?></th>
                    <th><?= te('Typ') ?></th>
                    <th><?= te('Status') ?></th>
                    <th><?= te('Versuche') ?></th>
                    <th><?= te('Erstellt') ?></th>
                    <th><?= te('Verarbeitet') ?></th>
                    <th><?= te('Fehler') ?></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($queueItems as $item): ?>
                <?php
                $statusBadge = match($item['status']) {
                    'pending'    => 'badge-warning',
                    'processing' => 'badge-info',
                    'done'       => 'badge-enabled',
                    'failed'     => 'badge-danger',
                    default      => 'badge-neutral',
                };
                $payload = json_decode($item['payload'], true) ?? [];
                $upn     = $payload['user_upn'] ?? $payload['user_id'] ?? '–';
                ?>
                <tr>
                    <td style="color:#9ca3af;font-size:12px;">#<?= $item['id'] ?></td>
                    <td style="font-size:12px;">
                        <code><?= $e($item['job_type']) ?></code>
                        <div style="font-size:10px;color:#9ca3af;"><?= $e($upn) ?></div>
                    </td>
                    <td><span class="<?= $statusBadge ?> badge-pill"><?= $e($item['status']) ?></span></td>
                    <td style="font-size:12px;"><?= $item['attempts'] ?>/<?= $item['max_attempts'] ?></td>
                    <td style="font-size:11px;color:#6b7280;"><?= $item['created_at'] ? date('d.m. H:i', strtotime($item['created_at'])) : '–' ?></td>
                    <td style="font-size:11px;color:#6b7280;"><?= $item['processed_at'] ? date('d.m. H:i', strtotime($item['processed_at'])) : '–' ?></td>
                    <td style="font-size:11px;color:#ef4444;max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"
                        title="<?= $e($item['last_error'] ?? '') ?>">
                        <?= $e(substr($item['last_error'] ?? '', 0, 60)) ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- Job config modal -->
<div class="modal fade" id="jobConfigModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?= te('Job konfigurieren') ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="post" id="jobConfigForm" action="">
                <?= \App\Core\Csrf::field() ?>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-medium">Intervall</label>
                        <select name="interval_minutes" id="modalInterval" class="form-select">
                            <option value="1">Jede Minute</option>
                            <option value="5">Alle 5 Min.</option>
                            <option value="15">Alle 15 Min.</option>
                            <option value="30">Alle 30 Min.</option>
                            <option value="60">Stündlich</option>
                            <option value="120">Alle 2 Stunden</option>
                            <option value="360">Alle 6 Stunden</option>
                            <option value="720">Alle 12 Stunden</option>
                            <option value="1440">Täglich</option>
                            <option value="10080">Wöchentlich</option>
                        </select>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="enabled" id="modalEnabled" value="1">
                        <label class="form-check-label" for="modalEnabled">Job aktiviert</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Abbrechen</button>
                    <button type="submit" class="btn btn-primary">Speichern</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openJobConfig(jobKey, enabled, interval) {
    const modal    = new bootstrap.Modal(document.getElementById('jobConfigModal'));
    const form     = document.getElementById('jobConfigForm');
    const selInt   = document.getElementById('modalInterval');
    const chkEna   = document.getElementById('modalEnabled');

    form.action = '/cron/update-job/' + encodeURIComponent(jobKey);
    chkEna.checked = !!enabled;

    // Select closest option
    let best = '60';
    for (const opt of selInt.options) {
        if (parseInt(opt.value) <= interval) best = opt.value;
    }
    selInt.value = interval; // try exact
    if (!selInt.value) selInt.value = best;

    modal.show();
}
</script>
