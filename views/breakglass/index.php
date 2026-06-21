<?php use App\Core\View; $e = fn($v) => View::escape($v); ?>

<?php if (!empty($flash)): ?>
    <div class="alert alert-success alert-dismissible mb-3"><i class="bi bi-check-circle me-2"></i><?= $e($flash) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>
<?php if (!empty($error)): ?>
    <div class="alert alert-danger alert-dismissible mb-3"><i class="bi bi-exclamation-triangle me-2"></i><?= $e($error) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<div class="alert alert-info d-flex gap-3 mb-4">
    <i class="bi bi-shield-fill-exclamation flex-shrink-0 mt-1" style="font-size:1.4rem;color:#1d4ed8;"></i>
    <div>
        <strong><?= te('Was sind Break-Glass-Accounts?') ?></strong>
        <?= te('Notfall-Administratorkonten, mit denen man sich anmelden kann, wenn alle anderen Wege versagen (z.B. wenn eine fehlerhafte Conditional-Access-Policy alle anderen Admins aussperrt, oder bei MFA-Ausfall).') ?>
        <?= te('Microsoft empfiehlt') ?> <strong><?= te('2 Konten') ?></strong><?= te(', dauerhaft als Global Admin, aus allen restriktiven CA-Policies ausgeschlossen, Passwort sicher im Tresor verwahrt, regelmäßig getestet (mind. halbjährlich).') ?>
        <a href="https://learn.microsoft.com/de-de/entra/identity/role-based-access-control/security-emergency-access" target="_blank" rel="noopener" class="alert-link"><?= te('Microsoft-Doku') ?></a>
    </div>
</div>

<!-- ── Konfiguration ─────────────────────────────────────────────────── -->
<div class="content-card mb-4">
    <div class="card-header-custom">
        <i class="bi bi-gear text-primary"></i>
        <h6><?= te('Konfigurierte Notfall-Accounts') ?></h6>
    </div>
    <div class="card-body-custom">
        <form method="post" action="/breakglass/save">
            <?= \App\Core\Csrf::field() ?>
            <label class="form-label fw-medium">UPNs <span class="text-muted small"><?= te('(kommagetrennt oder eine pro Zeile)') ?></span></label>
            <textarea name="break_glass_upns" class="form-control" rows="2"
                      placeholder="emergency1@firma.de, emergency2@firma.de"><?= $e(implode("\n", $upns)) ?></textarea>
            <div class="form-text"><?= te('Empfohlen: 2 Accounts, dauerhaft Global Administrator, jeweils mit eigener Cloud-Identität (nicht synchronisiert).') ?></div>
            <button type="submit" class="btn btn-primary btn-sm mt-3">
                <i class="bi bi-check2 me-1"></i><?= te('Speichern & Prüfen') ?>
            </button>
        </form>
    </div>
</div>

<?php if (empty($upns)): ?>
    <div class="content-card">
        <div class="card-body-custom text-center py-5 text-muted">
            <i class="bi bi-info-circle" style="font-size:2.5rem;opacity:.4;"></i>
            <p class="mt-3 mb-0"><?= te('Noch keine Break-Glass-Accounts konfiguriert. Trage die UPNs oben ein, um den automatischen Health-Check zu aktivieren.') ?></p>
        </div>
    </div>
<?php else: ?>

<!-- ── Health-Status pro Account ─────────────────────────────────────── -->
<div class="row g-3">
<?php foreach ($status as $s):
    $allGood = empty($s['issues']) && $s['exists'];
    $borderColor = $allGood ? '#16a34a' : (empty($s['issues']) ? '#9ca3af' : '#dc2626');
?>
    <div class="col-lg-6">
        <div class="content-card h-100" style="border-left: 4px solid <?= $borderColor ?>;">
            <div class="card-header-custom">
                <i class="bi bi-shield-lock <?= $allGood ? 'text-success' : 'text-danger' ?>"></i>
                <h6 style="font-family:monospace;font-size:13px;"><?= $e($s['upn']) ?></h6>
                <?php if ($allGood): ?>
                    <span class="ms-auto badge bg-success"><i class="bi bi-check-circle me-1"></i>OK</span>
                <?php else: ?>
                    <span class="ms-auto badge bg-danger"><?= count($s['issues']) ?> <?= te('Problem(e)') ?></span>
                <?php endif; ?>
            </div>
            <div class="card-body-custom">
                <?php if (!$s['exists']): ?>
                    <div class="alert alert-danger small mb-0">
                        <i class="bi bi-x-circle-fill me-1"></i>
                        <strong><?= te('Account existiert nicht im Tenant.') ?></strong>
                        <?= te('Bitte UPN prüfen oder neuen Notfall-Account anlegen.') ?>
                    </div>
                <?php else: ?>

                    <dl class="row mb-3 small">
                        <dt class="col-5 text-muted"><?= te('Anzeigename') ?></dt>
                        <dd class="col-7"><?= $e($s['displayName']) ?></dd>

                        <dt class="col-5 text-muted"><?= te('Aktiv') ?></dt>
                        <dd class="col-7">
                            <?php if ($s['accountEnabled']): ?>
                                <span class="badge bg-success"><i class="bi bi-check2 me-1"></i><?= te('Ja') ?></span>
                            <?php else: ?>
                                <span class="badge bg-danger"><i class="bi bi-x me-1"></i><?= te('Deaktiviert') ?></span>
                            <?php endif; ?>
                        </dd>

                        <dt class="col-5 text-muted"><?= te('Global Admin (dauerhaft)') ?></dt>
                        <dd class="col-7">
                            <?php if ($s['isGlobalAdmin']): ?>
                                <span class="badge bg-success"><i class="bi bi-check2 me-1"></i><?= te('Ja') ?></span>
                            <?php else: ?>
                                <span class="badge bg-warning text-dark"><i class="bi bi-exclamation-triangle me-1"></i><?= te('Nein') ?></span>
                            <?php endif; ?>
                        </dd>

                        <dt class="col-5 text-muted"><?= te('MFA-Methode registriert') ?></dt>
                        <dd class="col-7">
                            <?php if ($s['mfaRegistered'] === true): ?>
                                <span class="badge bg-info"><i class="bi bi-shield-check me-1"></i><?= te('Ja') ?></span>
                            <?php elseif ($s['mfaRegistered'] === false): ?>
                                <span class="badge bg-secondary"><?= te('Nein (nur Passwort)') ?></span>
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </dd>

                        <dt class="col-5 text-muted"><?= te('Letzte Anmeldung') ?></dt>
                        <dd class="col-7">
                            <?php if ($s['daysSinceSignIn'] === null): ?>
                                <span class="badge bg-warning text-dark"><?= te('Noch nie') ?></span>
                            <?php elseif ($s['daysSinceSignIn'] > 180): ?>
                                <span class="badge bg-warning text-dark"><?= te('vor :n Tagen', ['n' => $s['daysSinceSignIn']]) ?></span>
                            <?php else: ?>
                                <?= $e($s['daysSinceSignIn']) ?> <?= te('Tage') ?>
                                <span class="text-muted small">(<?= $e(date('d.m.Y', strtotime($s['lastSignIn']))) ?>)</span>
                            <?php endif; ?>
                        </dd>

                        <dt class="col-5 text-muted"><?= te('CA-Policies ausgenommen') ?></dt>
                        <dd class="col-7">
                            <?php if ($s['caExcluded'] === null): ?>
                                <span class="text-muted small"><?= te('nicht prüfbar (Permission?)') ?></span>
                            <?php elseif (empty($s['caExcluded'])): ?>
                                <span class="badge bg-danger"><i class="bi bi-shield-x me-1"></i><?= te('Keine') ?></span>
                            <?php else: ?>
                                <span class="badge bg-success"><?= count($s['caExcluded']) ?> <?= te('Policy(s)') ?></span>
                                <div class="text-muted small mt-1">
                                    <?php foreach ($s['caExcluded'] as $p): ?>
                                        · <?= $e($p) ?><br>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </dd>
                    </dl>
                <?php endif; ?>

                <?php if (!empty($s['issues'])): ?>
                    <div class="alert alert-warning small mb-0">
                        <div class="fw-semibold mb-1"><i class="bi bi-exclamation-triangle me-1"></i>Hinweise:</div>
                        <ul class="mb-0 ps-3">
                            <?php foreach ($s['issues'] as $i): ?>
                                <li><?= $e($i) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php endforeach; ?>
</div>
<?php endif; ?>
