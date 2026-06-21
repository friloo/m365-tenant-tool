<?php use App\Core\View; $e = fn($v) => View::escape($v); ?>

<?php if (!empty($flash)): ?>
    <div class="alert alert-success alert-dismissible mb-3"><i class="bi bi-check-circle me-2"></i><?= $e($flash) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>
<?php if (!empty($error)): ?>
    <div class="alert alert-danger alert-dismissible mb-3"><i class="bi bi-exclamation-triangle me-2"></i><?= $e($error) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<div class="alert alert-info d-flex gap-3 mb-3">
    <i class="bi bi-envelope-paper-fill flex-shrink-0 mt-1" style="font-size:1.4rem;color:#0078d4;"></i>
    <div>
        <?= t('<strong>Monatlicher Executive-Report</strong> per E-Mail an die Geschäftsführung mit den wichtigsten Tenant-KPIs: Security-Score, MFA-Quote, Risiko-Benutzer, Defender-Alerts, CA-Policies, Top-Findings aus der Posture-Analyse. Cron startet am 1. jedes Monats um 07:00 Uhr (lokale Zeitzone).') ?>
    </div>
</div>

<!-- ── Konfiguration ─────────────────────────────────────────────────── -->
<div class="content-card mb-4">
    <div class="card-header-custom"><i class="bi bi-gear text-primary"></i><h6><?= te('Aktivierung & Empfänger') ?></h6></div>
    <div class="card-body-custom">
        <form method="post" action="/executivereport/save">
            <?= \App\Core\Csrf::field() ?>
            <div class="form-check form-switch mb-3">
                <input class="form-check-input" type="checkbox" name="enabled" id="execEnabled" value="1" role="switch"
                       <?= $enabled ? 'checked' : '' ?>>
                <label class="form-check-label fw-medium" for="execEnabled"><?= te('Monatlichen Executive-Report versenden') ?></label>
            </div>
            <label class="form-label fw-medium"><?= te('Empfänger') ?> <span class="text-muted small"><?= te('(kommagetrennt)') ?></span></label>
            <input type="text" name="recipients" class="form-control" value="<?= $e($recipients) ?>"
                   placeholder="cio@firma.de, gf@firma.de">
            <div class="form-text"><?= te('Standard ist die Alert-Mail-Adresse aus den allgemeinen Einstellungen.') ?></div>
            <div class="mt-3 d-flex gap-2 flex-wrap">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check2 me-1"></i><?= te('Speichern') ?>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ── Vorschau / Test-Versand ───────────────────────────────────────── -->
<div class="content-card mb-4">
    <div class="card-header-custom"><i class="bi bi-eye text-info"></i><h6><?= te('Vorschau & Test') ?></h6></div>
    <div class="card-body-custom">
        <p class="text-muted small mb-3">
            <?= te('So wird der Report aussehen wenn er versendet wird (mit den aktuellen Tenant-Daten). Der Test-Versand schickt die Mail jetzt ohne auf den 1. des Monats zu warten.') ?>
        </p>
        <div class="d-flex gap-2 flex-wrap">
            <a href="/executivereport/preview" target="_blank" rel="noopener" class="btn btn-outline-primary">
                <i class="bi bi-eye me-1"></i><?= te('Vorschau im Browser öffnen') ?>
            </a>
            <form method="post" action="/executivereport/send-now" class="d-inline"
                  onsubmit="return confirm(<?= htmlspecialchars(json_encode(t('Den Report jetzt sofort an die konfigurierten Empfänger senden?'), JSON_UNESCAPED_UNICODE), ENT_QUOTES) ?>)">
                <?= \App\Core\Csrf::field() ?>
                <button type="submit" class="btn btn-outline-success">
                    <i class="bi bi-send me-1"></i><?= te('Jetzt versenden') ?>
                </button>
            </form>
        </div>
    </div>
</div>
