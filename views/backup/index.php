<?php use App\Core\View; $e = fn($v) => View::escape($v); ?>

<?php if (!empty($flash)): ?>
    <div class="alert alert-success alert-dismissible mb-3"><i class="bi bi-check-circle me-2"></i><?= $e($flash) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>
<?php if (!empty($error)): ?>
    <div class="alert alert-danger alert-dismissible mb-3"><i class="bi bi-exclamation-triangle me-2"></i><?= $e($error) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<div class="alert alert-warning d-flex gap-3 mb-3">
    <i class="bi bi-exclamation-triangle-fill flex-shrink-0 mt-1" style="font-size:1.4rem;color:#b45309;"></i>
    <div>
        <strong>Microsoft 365 sichert deine Daten NICHT.</strong>
        Die Recycle-Bin-Frist von 30-93 Tagen ist kein Backup —
        nach Ransomware, versehentlichem Löschen, kompromittierten Admin-Konten oder Kündigungen
        sind die Daten weg. Für DSGVO Art. 32 (Verfügbarkeit), ISO 27001 A.12.3 und NIS-2 Art. 21(d)
        ist ein 3rd-Party-Backup Pflicht.
    </div>
</div>

<!-- ── Health-Score ──────────────────────────────────────────────────── -->
<div class="content-card mb-4" style="border-left: 4px solid <?= $health['score'] >= 75 ? '#16a34a' : ($health['score'] >= 50 ? '#d97706' : '#dc2626') ?>;">
    <div class="card-body-custom d-flex align-items-center gap-3 flex-wrap">
        <div style="font-size: 38px; font-weight: 700; color: <?= $health['score'] >= 75 ? '#16a34a' : ($health['score'] >= 50 ? '#d97706' : '#dc2626') ?>;">
            <?= $health['score'] ?>
        </div>
        <div class="flex-grow-1">
            <div class="fw-semibold" style="font-size:16px;">Backup-Health-Score</div>
            <div class="text-muted small">
                <?= $health['score'] >= 75 ? 'Backup-Setup ist gut konfiguriert.' :
                   ($health['score'] >= 50 ? 'Backup-Setup hat Lücken.' : 'Backup-Setup hat kritische Lücken oder fehlt komplett.') ?>
            </div>
        </div>
        <?php if (!empty($data['provider'])): ?>
            <div class="text-end">
                <div class="text-muted small">Anbieter</div>
                <div class="fw-medium">
                    <?php if ($data['provider_url']): ?>
                        <a href="<?= $e($data['provider_url']) ?>" target="_blank" rel="noopener"><?= $e($data['provider']) ?></a>
                    <?php else: ?>
                        <?= $e($data['provider']) ?>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- ── Findings ──────────────────────────────────────────────────────── -->
<?php if (!empty($health['issues'])): ?>
<div class="content-card mb-4">
    <div class="card-header-custom"><i class="bi bi-list-check text-warning"></i><h6>Findings</h6></div>
    <div class="card-body-custom">
        <ul class="mb-0 ps-3">
            <?php foreach ($health['issues'] as $i):
                $cls = match ($i['severity']) { 'critical' => 'text-danger', 'high' => 'text-warning', default => 'text-muted' };
            ?>
                <li class="<?= $cls ?> mb-2">
                    <strong>[<?= $e(strtoupper($i['severity'])) ?>]</strong>
                    <?= $e($i['msg']) ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>
<?php endif; ?>

<!-- ── Konfig-Form ───────────────────────────────────────────────────── -->
<div class="content-card mb-4">
    <div class="card-header-custom"><i class="bi bi-gear text-primary"></i><h6>Backup-Konfiguration eintragen</h6></div>
    <div class="card-body-custom">
        <form method="post" action="/backup/save">
            <?= \App\Core\Csrf::field() ?>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-medium">Anbieter</label>
                    <input type="text" name="provider" class="form-control" value="<?= $e($data['provider']) ?>"
                           placeholder="z.B. Veeam, Druva, Spanning, AvePoint, Acronis">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-medium">Anbieter-URL <span class="text-muted small">(optional)</span></label>
                    <input type="url" name="provider_url" class="form-control" value="<?= $e($data['provider_url']) ?>"
                           placeholder="https://console.veeam.com/...">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-medium">Letzter Backup-Lauf</label>
                    <input type="datetime-local" name="last_run" class="form-control" value="<?= $e($data['last_run']) ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-medium">Status</label>
                    <select name="last_run_status" class="form-select">
                        <option value="">— wählen —</option>
                        <?php foreach (['success' => 'Erfolgreich', 'partial' => 'Teilweise erfolgreich', 'failed' => 'Fehlgeschlagen'] as $k => $v): ?>
                            <option value="<?= $k ?>" <?= $data['last_run_status'] === $k ? 'selected' : '' ?>><?= $e($v) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-medium">Aufbewahrung (Tage)</label>
                    <input type="number" name="retention_days" class="form-control" min="0" max="3650"
                           value="<?= $e($data['retention_days']) ?>" placeholder="z.B. 365">
                </div>
                <div class="col-12">
                    <label class="form-label fw-medium">Geschützte Workloads</label>
                    <div class="d-flex flex-wrap gap-3">
                        <?php foreach (['covers_mail' => 'Exchange / Mail', 'covers_onedrive' => 'OneDrive', 'covers_sp' => 'SharePoint', 'covers_teams' => 'Teams'] as $key => $label): ?>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="<?= $key ?>" id="<?= $key ?>" value="1"
                                       <?= $data[$key === 'covers_sp' ? 'covers_sp' : $key] ? 'checked' : '' ?>>
                                <label class="form-check-label" for="<?= $key ?>"><?= $e($label) ?></label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-medium">Letzter Restore-Test</label>
                    <input type="date" name="restore_tested" class="form-control" value="<?= $e($data['restore_tested']) ?>">
                    <div class="form-text">Mindestens einmal jährlich.</div>
                </div>
                <div class="col-12">
                    <label class="form-label fw-medium">Notizen <span class="text-muted small">(optional)</span></label>
                    <textarea name="notes" class="form-control" rows="2"
                              placeholder="z.B. Backup läuft täglich 02:00 — Wiederherstellungs-Verantwortung bei IT-Dienstleister XY"><?= $e($data['notes']) ?></textarea>
                </div>
            </div>
            <button type="submit" class="btn btn-primary mt-3">
                <i class="bi bi-check2 me-1"></i>Speichern
            </button>
        </form>
    </div>
</div>
