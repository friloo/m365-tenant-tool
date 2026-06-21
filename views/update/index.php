<?php use App\Core\View; $e = fn($v) => View::escape($v); ?>

<p class="text-muted mb-4"><?= te('Software-Updates und Datenbank-Migrationen') ?></p>

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

<!-- Metric cards row -->
<div class="row g-3 mb-4">
    <div class="col-sm-4">
        <div class="metric-card">
            <div class="metric-label"><?= te('Aktuelle Version') ?></div>
            <div class="metric-value">
                <?php if ($currentVersion): ?>
                    <span class="badge-success badge-pill" style="font-size:13px;"><?= $e($currentVersionShort) ?></span>
                <?php else: ?>
                    <span class="badge-warning badge-pill" style="font-size:13px;"><?= te('unbekannt') ?></span>
                <?php endif; ?>
            </div>
            <div class="metric-sub"><?= $currentVersion ? $e(substr($currentVersion, 0, 12)) . '…' : te('Keine Version gespeichert') ?></div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="metric-card">
            <div class="metric-label"><?= te('Kanal') ?></div>
            <div class="metric-value">
                <span class="badge-info badge-pill" style="font-size:13px;"><?= $e($channel) ?></span>
            </div>
            <div class="metric-sub"><?= te('Update-Kanal') ?></div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="metric-card">
            <div class="metric-label"><?= te('Ausstehende Migrationen') ?></div>
            <div class="metric-value <?= $migrationStatus['pending_count'] > 0 ? 'text-warning' : '' ?>">
                <?= (int)$migrationStatus['pending_count'] ?>
            </div>
            <div class="metric-sub"><?= te('von :n gesamt', ['n' => (int)$migrationStatus['total']]) ?></div>
        </div>
    </div>
</div>

<!-- Channel selector -->
<div class="content-card mb-4">
    <div class="card-header-custom">
        <i class="bi bi-broadcast text-primary"></i>
        <h6><?= te('Update-Kanal') ?></h6>
    </div>
    <div class="card-body-custom">
        <form method="post" action="/settings/update/channel" class="d-flex align-items-end gap-3 flex-wrap">
            <?= \App\Core\Csrf::field() ?>
            <div>
                <label class="form-label fw-medium mb-1"><?= te('Kanal wählen') ?></label>
                <select name="channel" class="form-select" style="min-width:180px;">
                    <?php foreach ($channels as $ch): ?>
                        <option value="<?= $e($ch) ?>" <?= $channel === $ch ? 'selected' : '' ?>>
                            <?= $e($ch) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save me-1"></i><?= te('Speichern') ?>
            </button>
        </form>
        <?php if ($channel === 'development'): ?>
            <div class="alert alert-warning mt-3 mb-0">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <strong><?= te('Achtung:') ?></strong> <?= te('Der Development-Kanal enthält Vorabversionen, die möglicherweise instabil sind. Bitte nur in Testumgebungen verwenden.') ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Update check card -->
<div class="content-card mb-4">
    <div class="card-header-custom">
        <i class="bi bi-cloud-arrow-down text-primary"></i>
        <h6><?= te('Software-Update') ?></h6>
        <span class="ms-auto badge-info badge-pill"><?= te('Kanal:') ?> <?= $e($channel) ?></span>
    </div>
    <div class="card-body-custom">
        <p class="text-muted small mb-3">
            <?= te('Prüfe, ob eine neue Version von m365-tool verfügbar ist.') ?>
            <?= te('Aktuelle Version:') ?> <code><?= $e($currentVersionShort) ?></code>
        </p>
        <button class="btn btn-outline-primary" id="btnCheck">
            <i class="bi bi-search me-1"></i><?= te('Auf Updates prüfen') ?>
        </button>

        <!-- Check result area -->
        <div id="checkResult" style="display:none;" class="mt-3">
            <div id="checkResultContent"></div>
        </div>

        <!-- Progress area -->
        <div id="progressArea" style="display:none;" class="mt-3">
            <div class="d-flex justify-content-between mb-1">
                <span class="small fw-medium"><?= te('Update-Fortschritt') ?></span>
                <span class="small text-muted" id="progressText"><?= te('Wird vorbereitet…') ?></span>
            </div>
            <div class="progress" style="height:20px;">
                <div class="progress-bar progress-bar-striped progress-bar-animated"
                     id="progressBar"
                     role="progressbar"
                     style="width:0%;"
                     aria-valuenow="0"
                     aria-valuemin="0"
                     aria-valuemax="100">
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Migrations card -->
<div class="content-card mb-4">
    <div class="card-header-custom">
        <i class="bi bi-database-gear text-primary"></i>
        <h6><?= te('Datenbank-Migrationen') ?></h6>
        <?php if ($migrationStatus['pending_count'] > 0): ?>
            <span class="ms-auto badge-warning badge-pill"><?= (int)$migrationStatus['pending_count'] ?> <?= te('ausstehend') ?></span>
        <?php else: ?>
            <span class="ms-auto badge-success badge-pill"><?= te('Aktuell') ?></span>
        <?php endif; ?>
        <button class="btn btn-sm btn-outline-secondary ms-2"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#migrationsTable"
                aria-expanded="false">
            <i class="bi bi-chevron-down"></i>
        </button>
    </div>
    <div class="collapse <?= $migrationStatus['pending_count'] > 0 ? 'show' : '' ?>" id="migrationsTable">
        <div class="card-body-custom">
            <?php if (empty($migrationStatus['files'])): ?>
                <div class="text-center text-muted py-3">
                    <i class="bi bi-check-circle fs-4 d-block mb-2"></i>
                    <?= te('Keine Migrationsdateien vorhanden.') ?>
                </div>
            <?php else: ?>
                <div class="table-responsive mb-3">
                    <table class="table table-sm table-hover mb-0">
                        <thead>
                            <tr>
                                <th><?= te('Dateiname') ?></th>
                                <th><?= te('Status') ?></th>
                                <th><?= te('Angewendet am') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($migrationStatus['files'] as $mig): ?>
                                <tr>
                                    <td><code><?= $e($mig['name']) ?></code></td>
                                    <td>
                                        <?php if ($mig['applied']): ?>
                                            <span class="badge-success badge-pill"><?= te('Angewendet') ?></span>
                                        <?php else: ?>
                                            <span class="badge-warning badge-pill"><?= te('Ausstehend') ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-muted small">
                                        <?= $mig['applied_at'] ? $e($mig['applied_at']) : '–' ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php if ($migrationStatus['pending_count'] > 0): ?>
                    <form method="post" action="/settings/update/migrations"
                          onsubmit="return confirm('<?= t(':n ausstehende Migration(en) jetzt ausführen?', ['n' => (int)$migrationStatus['pending_count']]) ?>')">
                        <?= \App\Core\Csrf::field() ?>
                        <button type="submit" class="btn btn-warning">
                            <i class="bi bi-play-fill me-1"></i>
                            <?= te('Ausstehende Migrationen ausführen (:n)', ['n' => (int)$migrationStatus['pending_count']]) ?>
                        </button>
                    </form>
                <?php else: ?>
                    <div class="alert alert-success mb-0">
                        <i class="bi bi-check-circle me-2"></i><?= te('Alle Migrationen angewendet.') ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// Check for updates
document.getElementById('btnCheck')?.addEventListener('click', async function() {
    this.disabled = true;
    this.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>' + <?= json_encode(t('Wird geprüft…'), JSON_UNESCAPED_UNICODE) ?>;
    try {
        const r = await fetch('/settings/update/check', {method: 'POST', headers: { 'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content ?? '' }});
        const data = await r.json();
        document.getElementById('checkResult').style.display = 'block';
        const area = document.getElementById('checkResultContent');
        if (data.error) {
            area.innerHTML = '<div class="alert alert-danger">' + data.error + '</div>';
        } else if (data.has_update) {
            area.innerHTML = `<div class="alert alert-warning">
                <strong>Update verfügbar:</strong> ${data.latest_sha?.substring(0,7)} — ${data.latest_commit?.message || ''}
                <br><small>Von: ${data.latest_commit?.author || ''} am ${data.latest_commit?.date || ''} · ${data.versions_behind || ''} Version(en) zurück</small>
                ${data.changelog ? '<hr><pre style="font-size:11px;max-height:150px;overflow-y:auto;">' + data.changelog + '</pre>' : ''}
            </div>
            <button class="btn btn-danger" id="btnInstall">
                <i class="bi bi-cloud-download me-1"></i>Update auf ${data.latest_sha?.substring(0,7)} installieren
            </button>`;
            document.getElementById('btnInstall')?.addEventListener('click', startInstall);
        } else {
            area.innerHTML = '<div class="alert alert-success"><i class="bi bi-check-circle me-2"></i>Keine Updates verfügbar. Du bist auf dem neuesten Stand.</div>';
        }
    } catch(e) {
        document.getElementById('checkResultContent').innerHTML = '<div class="alert alert-danger">Fehler: ' + e.message + '</div>';
    }
    this.disabled = false;
    this.innerHTML = '<i class="bi bi-search me-1"></i>Auf Updates prüfen';
});

// Install update
function startInstall() {
    document.getElementById('progressArea').style.display = 'block';
    document.getElementById('btnInstall').disabled = true;
    fetch('/settings/update/install', {method: 'POST', headers: { 'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content ?? '' }})
        .then(r => r.json())
        .catch(() => ({}));
    pollProgress();
}

function pollProgress() {
    fetch('/settings/update/progress')
        .then(r => r.json())
        .then(data => {
            if (!data) return;
            const pct = Math.max(0, data.pct);
            document.getElementById('progressBar').style.width = pct + '%';
            document.getElementById('progressBar').setAttribute('aria-valuenow', pct);
            document.getElementById('progressText').textContent = data.text || '';
            if (data.step === 'done') {
                document.getElementById('progressBar').classList.add('bg-success');
                setTimeout(() => location.reload(), 2000);
            } else if (data.step === 'error') {
                document.getElementById('progressBar').classList.add('bg-danger');
            } else {
                setTimeout(pollProgress, 2000);
            }
        })
        .catch(() => setTimeout(pollProgress, 3000));
}
</script>
