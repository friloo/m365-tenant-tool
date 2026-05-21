<?php
use App\Core\View;
use App\Core\Csrf;

$labels = [
    1 => 'Verbindung',
    2 => 'Berechtigungen',
    3 => 'Empfänger',
    4 => 'Branding',
    5 => 'Profil',
];
?>
<div class="wizard-shell">
    <div class="wizard-head">
        <h2><i class="bi bi-magic"></i> Einrichtungs-Assistent</h2>
        <p>Fünf Schritte für einen sicher konfigurierten Mandanten — etwa zehn Minuten.</p>
    </div>

    <div class="wizard-steps">
        <?php for ($i = 1; $i <= $totalSteps; $i++): ?>
            <?php
            $cls = '';
            if ($i < $step) $cls = 'done';
            elseif ($i === $step) $cls = 'active';
            ?>
            <div class="wizard-step <?= $cls ?>">
                <div class="step-dot">
                    <?php if ($cls === 'done'): ?><i class="bi bi-check"></i><?php else: ?><?= $i ?><?php endif; ?>
                </div>
                <div class="step-label"><?= View::escape($labels[$i] ?? '') ?></div>
            </div>
        <?php endfor; ?>
    </div>

    <form method="post" action="/setup/save">
        <?= Csrf::field() ?>
        <input type="hidden" name="step" value="<?= (int)$step ?>">

        <div class="wizard-body">
            <?php if ($step === 1): ?>
                <h3>Tenant-Verbindung prüfen</h3>
                <p class="text-muted small">Sind alle drei Credentials hinterlegt und antwortet die Microsoft Graph API?</p>
                <?php foreach ($stepData['checks'] as $c): ?>
                    <div class="wizard-check-row">
                        <div class="wizard-check-icon <?= View::escape($c['status']) ?>">
                            <i class="bi bi-<?= $c['status'] === 'ok' ? 'check-lg' : ($c['status'] === 'fail' ? 'x-lg' : 'exclamation') ?>"></i>
                        </div>
                        <div class="wizard-check-text">
                            <strong><?= View::escape($c['title']) ?></strong>
                            <span><?= View::escape($c['body']) ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
                <?php if (!$stepData['all_ok']): ?>
                    <div class="alert alert-warning mt-3">
                        Du kannst trotzdem fortfahren — die fehlenden Werte bitte aber zeitnah in
                        <a href="<?= View::escape($stepData['settings_url']) ?>">/settings</a> ergänzen.
                    </div>
                <?php endif; ?>

            <?php elseif ($step === 2): ?>
                <h3>App-Berechtigungen <?= \App\Core\Help::tip('graph_api') ?></h3>
                <p class="text-muted small">Welche Graph-API-Permissions hat die hinterlegte App-Registrierung?</p>
                <?php if (!empty($stepData['error'])): ?>
                    <div class="alert alert-danger"><?= View::escape($stepData['error']) ?></div>
                <?php else: ?>
                    <?php $s = $stepData['summary']; ?>
                    <div class="row text-center mb-3">
                        <div class="col"><div class="kpi-card"><div class="kpi-num text-success"><?= (int)$s['granted'] ?></div><div class="kpi-label">erteilt</div></div></div>
                        <div class="col"><div class="kpi-card"><div class="kpi-num text-danger"><?= (int)$s['missing'] ?></div><div class="kpi-label">fehlend</div></div></div>
                        <div class="col"><div class="kpi-card"><div class="kpi-num"><?= (int)$s['total'] ?></div><div class="kpi-label">gesamt</div></div></div>
                    </div>
                    <?php if (!empty($stepData['missing'])): ?>
                        <p class="small text-muted">Erste fehlende Berechtigungen:</p>
                        <ul class="small mb-0">
                            <?php foreach ($stepData['missing'] as $perm => $row): ?>
                                <li><code><?= View::escape($perm) ?></code> — <?= View::escape($row['desc']) ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <p class="small mt-3 mb-0">Vollständige Liste in <a href="/settings/permissions">/settings/permissions</a>.</p>
                    <?php else: ?>
                        <div class="alert alert-success mb-0"><i class="bi bi-check-circle"></i> Alle erforderlichen Berechtigungen sind erteilt.</div>
                    <?php endif; ?>
                <?php endif; ?>

            <?php elseif ($step === 3): ?>
                <h3>Empfänger für Benachrichtigungen <?= \App\Core\Help::tip('notifications') ?></h3>
                <p class="text-muted small">An welche E-Mail-Adressen sollen Reports und Warnungen gesendet werden? Komma-getrennt.</p>
                <input type="text" name="notification_recipients" class="form-control"
                       value="<?= View::escape($stepData['value']) ?>"
                       placeholder="security@firma.de, it-leitung@firma.de">

            <?php elseif ($step === 4): ?>
                <h3>Branding</h3>
                <p class="text-muted small">Wie soll dein Mandant heißen? Optional URL eines Logos (PNG, max. 64×64).</p>
                <label class="form-label">Mandanten-/App-Name</label>
                <input type="text" name="app_name" class="form-control mb-3"
                       value="<?= View::escape($stepData['app_name']) ?>"
                       placeholder="Beispiel: Firma XY — Tenant-Verwaltung">

                <label class="form-label">Logo-URL (optional)</label>
                <input type="url" name="logo_url" class="form-control"
                       value="<?= View::escape($stepData['logo_url']) ?>"
                       placeholder="https://firma.de/logo.png">

            <?php elseif ($step === 5): ?>
                <h3>Compliance-Profil auswählen <?= \App\Core\Help::tip('compliance_profile') ?></h3>
                <p class="text-muted small">Branchen-typische Härtungs-Voreinstellungen. Du kannst auch jetzt überspringen und später unter /complianceprofile auswählen.</p>
                <div class="profile-grid">
                    <?php foreach ($stepData['profiles'] as $p): ?>
                        <a href="/complianceprofile" class="profile-card" style="text-decoration:none; color:inherit;">
                            <div class="profile-icon" style="background: <?= View::escape($p['color']) ?>;"><i class="bi bi-<?= View::escape($p['icon']) ?>"></i></div>
                            <h5><?= View::escape($p['name']) ?></h5>
                            <p><?= View::escape($p['short']) ?></p>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="wizard-foot">
            <div>
                <?php if ($step > 1): ?>
                    <a href="/setup?step=<?= $step - 1 ?>" class="btn btn-link text-secondary"><i class="bi bi-arrow-left"></i> Zurück</a>
                <?php endif; ?>
            </div>
            <div class="d-flex gap-2">
                <?php if ($step < $totalSteps): ?>
                    <a href="/setup?step=<?= $step + 1 ?>" class="btn btn-link text-muted">Überspringen</a>
                    <button type="submit" class="btn btn-primary">Weiter <i class="bi bi-arrow-right"></i></button>
                <?php else: ?>
                    <button type="submit" class="btn btn-success"><i class="bi bi-check2-circle"></i> Assistent abschließen</button>
                <?php endif; ?>
            </div>
        </div>
    </form>
</div>

<?php if ($allDone): ?>
    <p class="text-muted small text-center mt-3">
        <i class="bi bi-info-circle"></i> Du hast den Assistenten bereits abgeschlossen. Er erscheint nicht erneut beim Login.
        <form method="post" action="/setup/reset" class="d-inline">
            <?= Csrf::field() ?>
            <button class="btn btn-link btn-sm p-0 align-baseline" type="submit">Erneut durchlaufen</button>
        </form>
    </p>
<?php endif; ?>

<style>
.kpi-card { background: #f9fafb; border-radius: 8px; padding: 16px; }
.kpi-num { font-size: 28px; font-weight: 700; line-height: 1; }
.kpi-label { font-size: 11px; text-transform: uppercase; color: #6b7280; letter-spacing: .5px; margin-top: 4px; }
</style>
