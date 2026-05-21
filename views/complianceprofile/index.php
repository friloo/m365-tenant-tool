<?php
use App\Core\View;
use App\Core\Csrf;
?>
<div class="content-card">
    <h1 class="mb-2"><i class="bi bi-shield-check"></i> Compliance-Profile <?= \App\Core\Help::tip('compliance_profile') ?></h1>
    <p class="text-muted">Wähle ein Branchen-Profil und wende mit einem Klick die dazu passenden Hardening-Defaults an. Alle Aktionen sind im Audit-Log nachvollziehbar und können einzeln im <a href="/hardening">Tenant-Härtungs-Modul</a> wieder rückgängig gemacht werden.</p>

    <?php $flash = \App\Core\Session::getFlash('success'); $err = \App\Core\Session::getFlash('error'); ?>
    <?php if ($flash): ?><div class="alert alert-success"><?= View::escape($flash) ?></div><?php endif; ?>
    <?php if ($err): ?><div class="alert alert-danger"><?= View::escape($err) ?></div><?php endif; ?>

    <?php if ($current !== ''): ?>
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i> Aktuell aktives Profil: <strong><?= View::escape($profiles[$current]['name'] ?? $current) ?></strong> — Du kannst es jederzeit überschreiben oder einzelne Items in <a href="/hardening">/hardening</a> umkehren.
        </div>
    <?php endif; ?>

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
                    <summary class="small text-muted" style="cursor:pointer;">Aktionen anzeigen (<?= count($p['actions']) ?>)</summary>
                    <ul class="small text-muted mt-2 mb-0">
                        <?php foreach ($p['actions'] as $a): ?>
                            <li><code><?= View::escape($a) ?></code></li>
                        <?php endforeach; ?>
                    </ul>
                </details>
                <div class="small text-muted mt-3"><?= View::escape($p['note']) ?></div>
                <form method="post" action="/complianceprofile/apply" class="mt-3"
                      onsubmit="return confirm('Profil &quot;<?= View::escape($p['name']) ?>&quot; jetzt anwenden? Bestehende Werte werden überschrieben.');">
                    <?= Csrf::field() ?>
                    <input type="hidden" name="profile" value="<?= View::escape($p['key']) ?>">
                    <button class="btn btn-primary w-100" type="submit">
                        <i class="bi bi-magic"></i> Profil anwenden
                    </button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>
</div>
