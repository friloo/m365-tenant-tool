<?php
use App\Core\View;
use App\Core\Csrf;
?>
<div class="content-card mb-3">
    <h1 class="mb-2"><i class="bi bi-shield-check"></i> <?= te('Compliance-Profile') ?> <?= \App\Core\Help::tip('compliance_profile') ?></h1>
    <p class="text-muted mb-0"><?= te('Wähle ein Branchen-Profil und wende mit einem Klick die dazu passenden Hardening-Defaults an. Aktionen laufen einzeln im Browser mit Fortschritts-Anzeige; alle Schritte sind im Audit-Log nachvollziehbar und können im <a href="/hardening">Tenant-Härtungs-Modul</a> einzeln umgekehrt werden.') ?></p>

    <?php $flash = \App\Core\Session::getFlash('success'); $err = \App\Core\Session::getFlash('error'); ?>
    <?php if ($flash): ?><div class="alert alert-success mt-3 mb-0"><?= View::escape($flash) ?></div><?php endif; ?>
    <?php if ($err): ?><div class="alert alert-danger mt-3 mb-0"><?= View::escape($err) ?></div><?php endif; ?>

    <?php if ($current !== ''): ?>
        <div class="alert alert-info mt-3 mb-0">
            <i class="bi bi-info-circle"></i> <?= te('Aktuell aktives Profil:') ?> <strong><?= View::escape($profiles[$current]['name'] ?? $current) ?></strong> &mdash; <?= te('Du kannst es jederzeit überschreiben oder einzelne Items in <a href="/hardening">/hardening</a> umkehren.') ?>
        </div>
    <?php endif; ?>
</div>

<div class="profile-grid">
    <?php foreach ($profiles as $p): ?>
        <div class="profile-card <?= $current === $p['key'] ? 'selected' : '' ?>">
            <div class="profile-icon" style="background: <?= View::escape($p['color']) ?>;"><i class="bi bi-<?= View::escape($p['icon']) ?>"></i></div>
            <h5><?= View::escape($p['name']) ?></h5>
            <p><?= View::escape($p['short']) ?></p>
            <ul>
                <?php foreach ($p['regulations'] as $r): ?>
                    <li><?= View::escape($r) ?></li>
                <?php endforeach; ?>
            </ul>
            <details class="mt-3">
                <summary class="small text-muted" style="cursor:pointer;"><?= te('Aktionen anzeigen') ?> (<?= count($p['actions']) ?>)</summary>
                <ul class="small text-muted mt-2 mb-0">
                    <?php foreach ($p['actions'] as $a): ?>
                        <li><code><?= View::escape($a) ?></code></li>
                    <?php endforeach; ?>
                </ul>
            </details>
            <div class="small text-muted mt-3"><?= View::escape($p['note']) ?></div>
            <button type="button" class="btn btn-primary w-100 mt-3"
                    data-apply-profile="<?= View::escape($p['key']) ?>"
                    data-apply-name="<?= View::escape($p['name']) ?>"
                    data-apply-actions='<?= htmlspecialchars(json_encode($p['actions']), ENT_QUOTES) ?>'>
                <i class="bi bi-magic"></i> <?= te('Profil anwenden') ?>
            </button>
        </div>
    <?php endforeach; ?>
</div>

<!-- ── Fortschritts-Modal ──────────────────────────────────── -->
<div class="modal fade" id="profileApplyModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-magic"></i> <?= te('Profil anwenden:') ?> <span id="apModalProfileName">—</span></h5>
            </div>
            <div class="modal-body">
                <div class="d-flex justify-content-between small text-muted mb-1">
                    <span><?= te('Fortschritt') ?></span>
                    <span><span id="apProgressCount">0</span> / <span id="apTotal">0</span></span>
                </div>
                <div class="progress mb-3" style="height: 12px;">
                    <div class="progress-bar progress-bar-striped progress-bar-animated"
                         id="apProgressBar" role="progressbar" style="width: 0%;"></div>
                </div>
                <div id="apLog" style="max-height: 280px; overflow-y: auto; font-size: 13px; background:#f9fafb; border:1px solid #e5e7eb; border-radius:6px; padding: 8px 12px;"></div>
                <div id="apSummary" class="alert alert-success mt-3 d-none">
                    <strong><?= te('Fertig!') ?></strong> <span id="apSummaryText"></span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-link text-muted d-none" id="apCloseBtn" data-bs-dismiss="modal"><?= te('Schließen') ?></button>
                <a href="/complianceprofile" class="btn btn-primary d-none" id="apReloadBtn"><?= te('Seite neu laden') ?></a>
            </div>
        </div>
    </div>
</div>

<script>
// base.php loads bootstrap.js at the END of <body>, so this inline
// view-script runs BEFORE bootstrap is defined. Defer everything to
// DOMContentLoaded — by that point all synchronous scripts further
// down the document (including bootstrap.bundle.min.js) have loaded.
document.addEventListener('DOMContentLoaded', function () {
    const META = document.querySelector('meta[name="csrf-token"]');
    const CSRF = META ? META.content : '';

    const modalEl = document.getElementById('profileApplyModal');
    if (!modalEl || typeof bootstrap === 'undefined') {
        console.error('compliance-profile: bootstrap not loaded or modal missing');
        return;
    }
    const modal   = new bootstrap.Modal(modalEl);
    const bar     = document.getElementById('apProgressBar');
    const cntEl   = document.getElementById('apProgressCount');
    const totEl   = document.getElementById('apTotal');
    const logEl   = document.getElementById('apLog');
    const sumBox  = document.getElementById('apSummary');
    const sumText = document.getElementById('apSummaryText');
    const closeBt = document.getElementById('apCloseBtn');
    const reloadBt= document.getElementById('apReloadBtn');
    const nameEl  = document.getElementById('apModalProfileName');

    function appendLog(html, cls) {
        const div = document.createElement('div');
        div.style.padding = '4px 0';
        div.style.borderBottom = '1px solid #f3f4f6';
        if (cls) div.className = cls;
        div.innerHTML = html;
        logEl.appendChild(div);
        logEl.scrollTop = logEl.scrollHeight;
    }

    async function applyOne(profile, actionId, index, total) {
        const body = new URLSearchParams();
        body.set('_csrf', CSRF);
        body.set('profile',   profile);
        body.set('action_id', actionId);
        body.set('index',     String(index));
        if (index === total - 1) body.set('final', '1');

        const resp = await fetch('/complianceprofile/apply-step', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: body.toString(),
        });
        if (!resp.ok) {
            const text = await resp.text();
            throw new Error('HTTP ' + resp.status + ': ' + text.substring(0, 200));
        }
        return resp.json();
    }

    async function applyProfile(profile, name, actions) {
        nameEl.textContent = name;
        cntEl.textContent  = '0';
        totEl.textContent  = actions.length;
        bar.style.width    = '0%';
        bar.className      = 'progress-bar progress-bar-striped progress-bar-animated';
        logEl.innerHTML    = '';
        sumBox.classList.add('d-none');
        closeBt.classList.add('d-none');
        reloadBt.classList.add('d-none');
        modal.show();

        let okCount = 0, failCount = 0;
        for (let i = 0; i < actions.length; i++) {
            const aid = actions[i];
            appendLog('<i class="bi bi-arrow-right text-muted"></i> <code>' + aid + '</code> …');
            try {
                const r = await applyOne(profile, aid, i, actions.length);
                const lastEntry = logEl.lastElementChild;
                if (r.ok) {
                    okCount++;
                    lastEntry.innerHTML = '<i class="bi bi-check-circle-fill text-success"></i> <code>' + aid + '</code> &mdash; ' + escapeHtml(r.msg || 'OK');
                } else {
                    failCount++;
                    lastEntry.innerHTML = '<i class="bi bi-x-circle-fill text-danger"></i> <code>' + aid + '</code> &mdash; ' + escapeHtml(r.msg || <?= json_encode(t('Fehler'), JSON_UNESCAPED_UNICODE) ?>);
                }
            } catch (e) {
                failCount++;
                const lastEntry = logEl.lastElementChild;
                lastEntry.innerHTML = '<i class="bi bi-x-circle-fill text-danger"></i> <code>' + aid + '</code> &mdash; ' + escapeHtml(e.message);
            }
            const pct = Math.round(((i + 1) / actions.length) * 100);
            bar.style.width   = pct + '%';
            cntEl.textContent = (i + 1);
        }

        // Done
        bar.classList.remove('progress-bar-striped', 'progress-bar-animated');
        if (failCount === 0) {
            bar.classList.remove('bg-warning', 'bg-danger');
            bar.classList.add('bg-success');
            sumBox.classList.remove('d-none', 'alert-warning', 'alert-danger');
            sumBox.classList.add('alert-success');
            sumText.textContent = okCount + ' ' + <?= json_encode(t('Aktionen erfolgreich angewendet.'), JSON_UNESCAPED_UNICODE) ?>;
        } else if (okCount > 0) {
            bar.classList.add('bg-warning');
            sumBox.classList.remove('d-none', 'alert-success', 'alert-danger');
            sumBox.classList.add('alert-warning');
            sumText.textContent = okCount + ' OK, ' + failCount + ' ' + <?= json_encode(t('fehlgeschlagen. Details siehe Protokoll oben.'), JSON_UNESCAPED_UNICODE) ?>;
        } else {
            bar.classList.add('bg-danger');
            sumBox.classList.remove('d-none', 'alert-success', 'alert-warning');
            sumBox.classList.add('alert-danger');
            sumText.textContent = <?= json_encode(t('Alle'), JSON_UNESCAPED_UNICODE) ?> + ' ' + failCount + ' ' + <?= json_encode(t('Aktionen fehlgeschlagen. Bitte Berechtigungen prüfen.'), JSON_UNESCAPED_UNICODE) ?>;
        }
        closeBt.classList.remove('d-none');
        reloadBt.classList.remove('d-none');
    }

    function escapeHtml(s) {
        return String(s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    }

    document.querySelectorAll('[data-apply-profile]').forEach(btn => {
        btn.addEventListener('click', () => {
            const profile = btn.dataset.applyProfile;
            const name    = btn.dataset.applyName;
            const actions = JSON.parse(btn.dataset.applyActions);
            if (!confirm(<?= json_encode(t('Profil "'), JSON_UNESCAPED_UNICODE) ?> + name + <?= json_encode(t('" jetzt anwenden? Es werden '), JSON_UNESCAPED_UNICODE) ?> + actions.length + <?= json_encode(t(' Hardening-Aktionen ausgeführt — bestehende Werte werden überschrieben.'), JSON_UNESCAPED_UNICODE) ?>)) return;
            applyProfile(profile, name, actions).catch(e => {
                appendLog('<i class="bi bi-exclamation-triangle text-danger"></i> ' + <?= json_encode(t('Abgebrochen:'), JSON_UNESCAPED_UNICODE) ?> + ' ' + escapeHtml(e.message), 'text-danger');
            });
        });
    });
});
</script>
