<?php use App\Core\View; $e = fn($v) => View::escape($v); ?>

<?php if (!empty($flash)): ?>
    <div class="alert alert-success alert-dismissible mb-3"><i class="bi bi-check-circle me-2"></i><?= $e($flash) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>
<?php if (!empty($error)): ?>
    <div class="alert alert-danger alert-dismissible mb-3"><i class="bi bi-exclamation-triangle me-2"></i><?= $e($error) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<style>
/* Step-Indicator (oben) */
.step-indicator {
    display:flex; align-items:flex-start;
    margin: 0 auto 2rem; max-width:760px;
    padding: 0 8px;
}
.step-indicator .step {
    display:flex; align-items:center; justify-content:center;
    width:36px; height:36px; border-radius:50%;
    font-weight:700; font-size:14px;
    border:2px solid #d1d5db; color:#6b7280; background:#fff;
    flex-shrink:0; position:relative; z-index:1;
    transition: background .2s, border-color .2s, color .2s;
}
.step-indicator .step.active { background:#2563eb; border-color:#2563eb; color:#fff; box-shadow:0 0 0 4px rgba(37,99,235,.12); }
.step-indicator .step.done   { background:#16a34a; border-color:#16a34a; color:#fff; }
.step-indicator .step-label { font-size:12px; color:#6b7280; margin-top:8px; text-align:center; white-space:nowrap; }
.step-indicator .step-wrapper.active .step-label { color:#1d4ed8; font-weight:600; }
.step-indicator .step-wrapper.done   .step-label { color:#15803d; font-weight:500; }
.step-indicator .step-connector { flex:1; height:2px; background:#d1d5db; margin: 18px 4px 0; min-width:24px; }
.step-indicator .step-connector.done { background:#16a34a; }
.step-indicator .step-wrapper { display:flex; flex-direction:column; align-items:center; min-width:80px; }

/* Card-Inhalt — die nackten .content-card im Wizard hatten kein Padding,
   alles klebte an der Border. Wir geben jedem wizard-step-Card hier
   einheitliches Padding + Header-Style. */
.wizard-step .content-card { padding: 24px 28px; }
.wizard-step .content-card > h5 {
    font-size:18px; font-weight:600; color:#111827;
    margin: 0 -28px 20px; padding: 0 28px 16px;
    border-bottom: 1px solid #f3f4f6;
}

/* Aktions-Buttons unter dem Step (vorhandene mt-4 d-flex-Container) */
.wizard-step .content-card > .d-flex.mt-4 {
    margin: 20px -28px -4px;
    padding: 16px 28px 0;
    border-top: 1px solid #f3f4f6;
}

.strength-bar { height:4px; border-radius:2px; margin-top:4px; transition:width .3s,background .3s; }
.group-search-box { margin-bottom:.75rem; }

@media (max-width: 768px) {
    .step-indicator { overflow-x: auto; padding-bottom: 4px; }
    .step-indicator .step-label { font-size: 11px; }
    .wizard-step .content-card { padding: 18px; }
    .wizard-step .content-card > h5 { margin: 0 -18px 16px; padding: 0 18px 12px; }
    .wizard-step .content-card > .d-flex.mt-4 { margin: 16px -18px -2px; padding: 14px 18px 0; }
}
</style>

<form method="post" action="/onboarding/create" id="onboardingForm">
<?= \App\Core\Csrf::field() ?>

<div class="step-indicator">
    <div class="step-wrapper">
        <div class="step active" id="ind-1">1</div>
        <div class="step-label">Benutzerdaten</div>
    </div>
    <div class="step-connector"></div>
    <div class="step-wrapper">
        <div class="step" id="ind-2">2</div>
        <div class="step-label">Lizenz</div>
    </div>
    <div class="step-connector"></div>
    <div class="step-wrapper">
        <div class="step" id="ind-3">3</div>
        <div class="step-label">Gruppen</div>
    </div>
    <div class="step-connector"></div>
    <div class="step-wrapper">
        <div class="step" id="ind-4">4</div>
        <div class="step-label">Zusammenfassung</div>
    </div>
</div>

<!-- Step 1: Benutzerdaten -->
<div class="wizard-step" id="step-1">
    <div class="content-card">
        <h5 class="mb-4"><i class="bi bi-person-plus me-2"></i>Schritt 1 – Benutzerdaten</h5>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label fw-semibold">Anzeigename <span class="text-danger">*</span></label>
                <input type="text" name="displayName" id="inp-displayName" class="form-control" required
                       placeholder="Vorname Nachname" autocomplete="off">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Benutzerprinzipalname (UPN) <span class="text-danger">*</span></label>
                <input type="email" name="userPrincipalName" id="inp-upn" class="form-control" required
                       placeholder="vorname.nachname@unternehmen.com" autocomplete="off">
                <div class="form-text">Format: vorname.nachname@unternehmen.com</div>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Passwort <span class="text-danger">*</span></label>
                <input type="password" name="password" id="inp-password" class="form-control" required
                       minlength="8" placeholder="Mind. 8 Zeichen" autocomplete="new-password">
                <div class="strength-bar" id="strengthBar" style="width:0;background:#e5e7eb;"></div>
                <div class="form-text" id="strengthText">Mind. 8 Zeichen, Groß-/Kleinbuchstaben, Zahlen empfohlen</div>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Berufsbezeichnung</label>
                <input type="text" name="jobTitle" id="inp-jobTitle" class="form-control"
                       placeholder="z. B. Entwickler" autocomplete="off">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Abteilung</label>
                <input type="text" name="department" id="inp-department" class="form-control"
                       placeholder="z. B. IT" autocomplete="off">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Nutzungsstandort</label>
                <select name="usageLocation" id="inp-usageLocation" class="form-select">
                    <option value="DE" selected>DE – Deutschland</option>
                    <option value="AT">AT – Österreich</option>
                    <option value="CH">CH – Schweiz</option>
                    <option value="US">US – USA</option>
                    <option value="GB">GB – Vereinigtes Königreich</option>
                </select>
            </div>
        </div>
        <div class="d-flex justify-content-end mt-4">
            <button type="button" class="btn btn-primary" onclick="nextStep()">
                Weiter <i class="bi bi-arrow-right ms-1"></i>
            </button>
        </div>
    </div>
</div>

<!-- Step 2: Lizenz -->
<div class="wizard-step" id="step-2" style="display:none">
    <div class="content-card">
        <h5 class="mb-4"><i class="bi bi-award me-2"></i>Schritt 2 – Lizenz zuweisen</h5>

        <?php if (empty($licenses)): ?>
            <div class="alert alert-info">
                <i class="bi bi-info-circle me-2"></i>
                Keine verfügbaren Lizenzen gefunden. Entweder sind alle Lizenzen vergeben oder die Berechtigung fehlt.
            </div>
        <?php endif; ?>

        <div class="mb-3">
            <div class="form-check mb-2">
                <input class="form-check-input" type="radio" name="skuId" id="sku-none" value="" checked>
                <label class="form-check-label" for="sku-none">
                    <strong>Keine Lizenz</strong>
                    <span class="text-muted ms-2 small">Lizenz später manuell zuweisen</span>
                </label>
            </div>
            <?php foreach ($licenses as $i => $lic): ?>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="radio" name="skuId"
                           id="sku-<?= $e($lic['skuId']) ?>" value="<?= $e($lic['skuId']) ?>">
                    <label class="form-check-label" for="sku-<?= $e($lic['skuId']) ?>">
                        <strong><?= $e($lic['name']) ?></strong>
                        <span class="badge-info ms-2"><?= (int)$lic['available'] ?> verfügbar</span>
                    </label>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="d-flex justify-content-between mt-4">
            <button type="button" class="btn btn-outline-secondary" onclick="prevStep()">
                <i class="bi bi-arrow-left me-1"></i> Zurück
            </button>
            <button type="button" class="btn btn-primary" onclick="nextStep()">
                Weiter <i class="bi bi-arrow-right ms-1"></i>
            </button>
        </div>
    </div>
</div>

<!-- Step 3: Gruppen & Teams -->
<div class="wizard-step" id="step-3" style="display:none">
    <div class="content-card">
        <h5 class="mb-4"><i class="bi bi-diagram-3 me-2"></i>Schritt 3 – Gruppen &amp; Teams</h5>

        <?php
        $teams     = array_filter($groups, fn($g) => $g['isTeam']);
        $otherGrps = array_filter($groups, fn($g) => !$g['isTeam']);
        $allGroups = array_slice($groups, 0, 50);
        ?>

        <?php if (empty($groups)): ?>
            <div class="alert alert-info">
                <i class="bi bi-info-circle me-2"></i>
                Keine statischen Gruppen gefunden.
            </div>
        <?php else: ?>
            <input type="text" id="groupSearchInput" class="form-control group-search-box"
                   placeholder="Gruppen durchsuchen…" oninput="filterGroups(this.value)">

            <?php if (!empty($teams)): ?>
                <div class="mb-3" id="sectionTeams">
                    <div class="fw-semibold mb-2 text-primary"><i class="bi bi-microsoft-teams me-1"></i>Teams</div>
                    <?php foreach (array_slice($teams, 0, 50) as $g): ?>
                        <div class="form-check group-item mb-1" data-name="<?= strtolower($e($g['displayName'])) ?>">
                            <input class="form-check-input" type="checkbox" name="groupIds[]"
                                   id="grp-<?= $e($g['id']) ?>" value="<?= $e($g['id']) ?>">
                            <label class="form-check-label" for="grp-<?= $e($g['id']) ?>">
                                <?= $e($g['displayName']) ?>
                                <span class="badge-info ms-1" style="font-size:10px;">Team</span>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($otherGrps)): ?>
                <div class="mb-3" id="sectionGroups">
                    <div class="fw-semibold mb-2 text-secondary"><i class="bi bi-people me-1"></i>Gruppen / Verteiler</div>
                    <?php foreach (array_slice($otherGrps, 0, 50) as $g): ?>
                        <div class="form-check group-item mb-1" data-name="<?= strtolower($e($g['displayName'])) ?>">
                            <input class="form-check-input" type="checkbox" name="groupIds[]"
                                   id="grp-<?= $e($g['id']) ?>" value="<?= $e($g['id']) ?>">
                            <label class="form-check-label" for="grp-<?= $e($g['id']) ?>">
                                <?= $e($g['displayName']) ?>
                                <?php if ($g['isSecurity']): ?>
                                    <span class="badge-neutral ms-1" style="font-size:10px;">Security</span>
                                <?php endif; ?>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if (count($groups) > 50): ?>
                <div class="text-muted small mt-1">Es werden maximal 50 Gruppen angezeigt.</div>
            <?php endif; ?>
        <?php endif; ?>

        <div class="d-flex justify-content-between mt-4">
            <button type="button" class="btn btn-outline-secondary" onclick="prevStep()">
                <i class="bi bi-arrow-left me-1"></i> Zurück
            </button>
            <button type="button" class="btn btn-primary" onclick="nextStep()">
                Weiter <i class="bi bi-arrow-right ms-1"></i>
            </button>
        </div>
    </div>
</div>

<!-- Step 4: Zusammenfassung -->
<div class="wizard-step" id="step-4" style="display:none">
    <div class="content-card">
        <h5 class="mb-4"><i class="bi bi-clipboard2-check me-2"></i>Schritt 4 – Zusammenfassung &amp; Erstellen</h5>

        <div class="table-responsive mb-4">
            <table class="table table-bordered table-sm">
                <tbody>
                    <tr><th style="width:180px">Anzeigename</th><td id="sum-displayName">–</td></tr>
                    <tr><th>UPN</th><td id="sum-upn">–</td></tr>
                    <tr><th>Berufsbezeichnung</th><td id="sum-jobTitle">–</td></tr>
                    <tr><th>Abteilung</th><td id="sum-department">–</td></tr>
                    <tr><th>Nutzungsstandort</th><td id="sum-usageLocation">–</td></tr>
                    <tr><th>Lizenz</th><td id="sum-license">Keine Lizenz</td></tr>
                    <tr><th>Gruppen</th><td id="sum-groups">Keine</td></tr>
                </tbody>
            </table>
        </div>

        <div class="form-check mb-4">
            <input class="form-check-input" type="checkbox" id="confirmCreate" required>
            <label class="form-check-label fw-semibold" for="confirmCreate">
                Ich bestätige die Erstellung dieses Benutzerkontos
            </label>
        </div>

        <div class="d-flex justify-content-between">
            <button type="button" class="btn btn-outline-secondary" onclick="prevStep()">
                <i class="bi bi-arrow-left me-1"></i> Zurück
            </button>
            <button type="submit" id="btnSubmit" class="btn btn-success" disabled>
                <i class="bi bi-person-check me-1"></i> Benutzer erstellen
            </button>
        </div>
    </div>
</div>

</form>

<script>
let currentStep = 1;
const totalSteps = 4;

function showStep(n) {
    document.querySelectorAll('.wizard-step').forEach((s, i) => s.style.display = i + 1 === n ? '' : 'none');
    document.querySelectorAll('.step-indicator .step-wrapper').forEach((w, i) => {
        const isActive = i + 1 === n;
        const isDone   = i + 1 < n;
        w.classList.toggle('active', isActive);
        w.classList.toggle('done',   isDone);
        const s = w.querySelector('.step');
        if (s) {
            s.classList.toggle('active', isActive);
            s.classList.toggle('done',   isDone);
            if (isDone) s.innerHTML = '<i class="bi bi-check-lg"></i>';
            else        s.textContent = i + 1;
        }
    });
    document.querySelectorAll('.step-indicator .step-connector').forEach((c, i) => {
        c.classList.toggle('done', i + 1 < n);
    });
    currentStep = n;
    updateSummary();
}

function nextStep() {
    if (validateStep(currentStep) && currentStep < totalSteps) showStep(currentStep + 1);
}

function prevStep() {
    if (currentStep > 1) showStep(currentStep - 1);
}

function validateStep(n) {
    if (n === 1) {
        const dn  = document.getElementById('inp-displayName');
        const upn = document.getElementById('inp-upn');
        const pw  = document.getElementById('inp-password');
        if (!dn.value.trim()) { dn.focus(); dn.classList.add('is-invalid'); return false; }
        dn.classList.remove('is-invalid');
        if (!upn.value.trim() || !upn.value.includes('@')) { upn.focus(); upn.classList.add('is-invalid'); return false; }
        upn.classList.remove('is-invalid');
        if (pw.value.length < 8) { pw.focus(); pw.classList.add('is-invalid'); return false; }
        pw.classList.remove('is-invalid');
    }
    return true;
}

function updateSummary() {
    if (currentStep !== 4) return;

    document.getElementById('sum-displayName').textContent  = document.getElementById('inp-displayName').value || '–';
    document.getElementById('sum-upn').textContent          = document.getElementById('inp-upn').value || '–';
    document.getElementById('sum-jobTitle').textContent     = document.getElementById('inp-jobTitle').value || '–';
    document.getElementById('sum-department').textContent   = document.getElementById('inp-department').value || '–';

    const locSel = document.getElementById('inp-usageLocation');
    document.getElementById('sum-usageLocation').textContent = locSel.options[locSel.selectedIndex]?.text || '–';

    const licRadio = document.querySelector('[name="skuId"]:checked');
    const licLabel = licRadio ? (document.querySelector('label[for="' + licRadio.id + '"]')?.innerText?.trim() || 'Keine Lizenz') : 'Keine Lizenz';
    document.getElementById('sum-license').textContent = licLabel;

    const checkedGroups = [...document.querySelectorAll('[name="groupIds[]"]:checked')];
    const groupNames = checkedGroups.map(cb => {
        const lbl = document.querySelector('label[for="' + cb.id + '"]');
        return lbl ? lbl.firstChild.textContent.trim() : cb.value;
    });
    document.getElementById('sum-groups').textContent = groupNames.length ? groupNames.join(', ') : 'Keine';
}

function filterGroups(query) {
    const q = query.toLowerCase();
    document.querySelectorAll('.group-item').forEach(item => {
        item.style.display = (!q || item.dataset.name.includes(q)) ? '' : 'none';
    });
}

document.getElementById('confirmCreate').addEventListener('change', function () {
    document.getElementById('btnSubmit').disabled = !this.checked;
});

document.getElementById('inp-password').addEventListener('input', function () {
    const val = this.value;
    const bar = document.getElementById('strengthBar');
    const txt = document.getElementById('strengthText');
    let strength = 0;
    if (val.length >= 8)  strength++;
    if (/[A-Z]/.test(val)) strength++;
    if (/[0-9]/.test(val)) strength++;
    if (/[^A-Za-z0-9]/.test(val)) strength++;
    const colors = ['#ef4444', '#f59e0b', '#22c55e', '#16a34a'];
    const labels = ['Schwach', 'Mittel', 'Gut', 'Stark'];
    bar.style.width = (strength * 25) + '%';
    bar.style.background = colors[strength - 1] || '#e5e7eb';
    txt.textContent = strength > 0 ? 'Passwortstärke: ' + (labels[strength - 1] || '') : 'Mind. 8 Zeichen, Groß-/Kleinbuchstaben, Zahlen empfohlen';
});

showStep(1);
</script>
