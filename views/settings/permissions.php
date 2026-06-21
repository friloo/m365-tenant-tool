<?php
$e = fn($v) => \App\Core\View::escape($v);

$pct = $summary['total'] > 0 ? round($summary['granted'] / $summary['total'] * 100) : 0;
$progressColor = $pct >= 90 ? 'success' : ($pct >= 60 ? 'warning' : 'danger');
?>
<style>
.perm-row          { display:flex; align-items:flex-start; gap:10px; padding:10px 16px; border-bottom:1px solid #f0f0f0; }
.perm-row:last-child { border-bottom:none; }
.perm-row:hover    { background:#fafafa; }
.perm-badge        { flex-shrink:0; width:22px; height:22px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:12px; margin-top:2px; }
.perm-badge.ok     { background:#dcfce7; color:#16a34a; }
.perm-badge.miss   { background:#fee2e2; color:#dc2626; }
.perm-name         { font-family:monospace; font-size:12px; color:#374151; font-weight:600; }
.perm-desc         { font-size:12px; color:#6b7280; }
.perm-features     { display:flex; flex-wrap:wrap; gap:4px; margin-top:4px; }
.perm-feature-tag  { font-size:11px; background:#f3f4f6; border:1px solid #e5e7eb; border-radius:4px; padding:1px 6px; color:#374151; }
.perm-feature-tag.miss { background:#fef2f2; border-color:#fecaca; color:#991b1b; }
.write-tag         { font-size:10px; background:#eff6ff; border:1px solid #bfdbfe; color:#1d4ed8; border-radius:3px; padding:0 5px; margin-left:4px; }
.section-header    { background:#f9fafb; border-bottom:1px solid #e5e7eb; padding:8px 16px; display:flex; align-items:center; gap:8px; }
.section-header span { font-size:11px; font-weight:600; text-transform:uppercase; letter-spacing:.5px; color:#6b7280; }
.token-info-grid   { display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:12px; }
.token-info-item   { background:#f9fafb; border:1px solid #e5e7eb; border-radius:8px; padding:12px 16px; }
.token-info-label  { font-size:11px; text-transform:uppercase; color:#9ca3af; letter-spacing:.5px; margin-bottom:4px; }
.token-info-value  { font-size:13px; font-weight:600; color:#111827; font-family:monospace; word-break:break-all; }
.how-to-card       { background:#fffbeb; border:1px solid #fde68a; border-radius:8px; padding:16px; margin-top:8px; }
</style>

<div class="d-flex align-items-center justify-content-between mb-3">
    <div>
        <div class="text-muted" style="font-size:13px;"><?= te('Prüft welche Microsoft Graph Berechtigungen dem konfigurierten App-Konto erteilt wurden und welche Features dadurch eingeschränkt sind.') ?></div>
    </div>
    <div class="d-flex gap-2">
        <a href="?refresh=1" class="btn btn-sm btn-primary"
           title="<?= te('Löscht das gecachte Token — nach Berechtigungsänderungen in Azure erforderlich') ?>">
            <i class="bi bi-arrow-clockwise me-1"></i><?= te('Token erneuern & neu prüfen') ?>
        </a>
        <a href="/settings" class="btn btn-sm btn-outline-secondary"><i class="bi bi-gear me-1"></i><?= te('Einstellungen') ?></a>
    </div>
</div>

<!-- Token Info ──────────────────────────────────────────────── -->
<div class="content-card mb-4">
    <div class="card-header-custom">
        <i class="bi bi-key me-2"></i><?= te('Aktives Access Token') ?>
    </div>
    <div class="card-body-custom">
        <div class="token-info-grid">
            <div class="token-info-item">
                <div class="token-info-label"><?= te('Mandant (Tenant)') ?></div>
                <div class="token-info-value"><?= $e($tenantName) ?></div>
            </div>
            <div class="token-info-item">
                <div class="token-info-label">Tenant ID</div>
                <div class="token-info-value" style="font-size:11px;"><?= $e($tokenInfo['tenant_id']) ?></div>
            </div>
            <div class="token-info-item">
                <div class="token-info-label">App ID (Client ID)</div>
                <div class="token-info-value" style="font-size:11px;"><?= $e($tokenInfo['app_id']) ?></div>
            </div>
            <div class="token-info-item">
                <div class="token-info-label"><?= te('Token gültig bis') ?></div>
                <div class="token-info-value" style="font-family:inherit;">
                    <?php if ($tokenInfo['expires']): ?>
                        <?php
                        $secsLeft = $tokenInfo['expires']->getTimestamp() - time();
                        $color = $secsLeft < 300 ? 'text-danger' : ($secsLeft < 900 ? 'text-warning' : 'text-success');
                        ?>
                        <span class="<?= $color ?>">
                            <?= $e($tokenInfo['expires']->format('H:i:s')) ?>
                            <?= te('(noch :min Min.)', ['min' => max(0, (int)($secsLeft / 60))]) ?>
                        </span>
                    <?php else: ?>
                        <span class="text-muted">–</span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="token-info-item">
                <div class="token-info-label"><?= te('Erteilte Berechtigungen im Token') ?></div>
                <div class="token-info-value" style="font-family:inherit;"><?= count($tokenInfo['roles']) ?> <?= te('Rollen') ?></div>
            </div>
        </div>
    </div>
</div>

<!-- Summary ─────────────────────────────────────────────────── -->
<div class="row g-3 mb-4">
    <div class="col-sm-3">
        <div class="metric-card">
            <div class="metric-value"><?= $summary['total'] ?></div>
            <div class="metric-label"><?= te('Geprüfte Berechtigungen') ?></div>
        </div>
    </div>
    <div class="col-sm-3">
        <div class="metric-card">
            <div class="metric-value text-success"><?= $summary['granted'] ?></div>
            <div class="metric-label"><?= te('Erteilt') ?> <i class="bi bi-check-circle-fill text-success ms-1"></i></div>
        </div>
    </div>
    <div class="col-sm-3">
        <div class="metric-card">
            <div class="metric-value <?= $summary['missing'] > 0 ? 'text-danger' : 'text-success' ?>"><?= $summary['missing'] ?></div>
            <div class="metric-label"><?= te('Fehlend') ?></div>
        </div>
    </div>
    <div class="col-sm-3">
        <div class="metric-card">
            <div class="metric-value"><?= $pct ?>%</div>
            <div class="metric-label">
                <div class="progress mt-1" style="height:6px;">
                    <div class="progress-bar bg-<?= $progressColor ?>" style="width:<?= $pct ?>%"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="alert alert-info d-flex gap-2 align-items-start mb-4" style="font-size:13px;">
    <i class="bi bi-info-circle-fill flex-shrink-0 mt-1"></i>
    <div>
        <strong><?= te('Berechtigung gerade erteilt aber wird noch als fehlend angezeigt?') ?></strong>
        <?= te('Das Access-Token ist bis zu 1 Stunde gecacht. Nach Änderungen in Azure einfach') ?>
        <a href="?refresh=1" class="alert-link"><?= te('Token erneuern & neu prüfen') ?></a> <?= te('klicken — das löscht das alte Token und holt sofort ein neues mit den aktuellen Rechten.') ?>
    </div>
</div>

<?php if ($summary['missing'] > 0): ?>
<!-- Missing Permissions Alert ───────────────────────────────── -->
<div class="alert alert-warning d-flex gap-3 mb-4" role="alert">
    <i class="bi bi-exclamation-triangle-fill flex-shrink-0 mt-1" style="font-size:18px;"></i>
    <div>
        <strong><?= te(':n Berechtigung(en) fehlen', ['n' => $summary['missing']]) ?></strong> —
        <?= te(':n davon sind Schreibrechte (Features zum Verwalten),', ['n' => $summary['missing_write']]) ?>
        <?= te(':n sind Leserechte (Features zum Anzeigen).', ['n' => $summary['missing_read']]) ?><br>
        <span class="text-muted" style="font-size:12px;">
            <?= te('Betroffene Features:') ?> <?= $e(implode(', ', array_slice($summary['affected_features'], 0, 8))) ?>
            <?= count($summary['affected_features']) > 8 ? ' ' . te('und :n weitere…', ['n' => count($summary['affected_features']) - 8]) : '' ?>
        </span>
    </div>
</div>

<!-- How to fix ──────────────────────────────────────────────── -->
<div class="content-card mb-4">
    <div class="card-header-custom" style="cursor:pointer;" onclick="this.nextElementSibling.classList.toggle('d-none')">
        <i class="bi bi-question-circle me-2"></i><?= te('Wie behebt man fehlende Berechtigungen?') ?>
        <i class="bi bi-chevron-down ms-auto"></i>
    </div>
    <div class="card-body-custom d-none">
        <ol class="mb-0" style="font-size:13px; line-height:1.9;">
            <li><?= t('Öffnen Sie das <strong>Azure Portal</strong> → <em>Azure Active Directory</em> → <em>App-Registrierungen</em>') ?></li>
            <li><?= t('Wählen Sie die App mit der Client ID:') ?> <code><?= $e($tokenInfo['app_id']) ?></code></li>
            <li><?= t('Navigieren Sie zu <strong>API-Berechtigungen</strong> → <em>Berechtigung hinzufügen</em>') ?></li>
            <li><?= t('Wählen Sie <strong>Microsoft Graph</strong> → <em>Anwendungsberechtigungen</em>') ?></li>
            <li><?= te('Suchen und aktivieren Sie die unten aufgeführten fehlenden Berechtigungen') ?></li>
            <li><?= t('Klicken Sie auf <strong>Administratorzustimmung erteilen</strong> (erfordert Globaler Administrator)') ?></li>
            <li><?= te('Laden Sie diese Seite neu — das Token wird beim nächsten API-Aufruf aktualisiert (max. 1 Std.)') ?></li>
        </ol>
        <div class="mt-3">
            <a href="https://portal.azure.com/#view/Microsoft_AAD_RegisteredApps/ApplicationMenuBlade/~/CallAnAPI/appId/<?= $e($tokenInfo['app_id']) ?>"
               target="_blank" class="btn btn-sm btn-primary">
                <i class="bi bi-box-arrow-up-right me-1"></i><?= te('App direkt im Azure Portal öffnen') ?>
            </a>
        </div>
    </div>
</div>
<?php else: ?>
<div class="alert alert-success mb-4">
    <i class="bi bi-check-circle-fill me-2"></i>
    <?= te('Alle :n geprüften Berechtigungen sind erteilt. Das Tool sollte vollständig funktionieren.', ['n' => $summary['total']]) ?>
</div>
<?php endif; ?>

<!-- Missing Permissions (by section) ───────────────────────── -->
<?php if (!empty($bySectionMissing)): ?>
<div class="content-card mb-4">
    <div class="card-header-custom">
        <i class="bi bi-x-circle text-danger me-2"></i><?= te('Fehlende Berechtigungen') ?>
        <span class="badge bg-danger ms-2"><?= $summary['missing'] ?></span>
    </div>
    <?php foreach ($bySectionMissing as $section => $perms): ?>
        <div class="section-header">
            <i class="bi bi-folder2"></i>
            <span><?= $e($section) ?></span>
        </div>
        <?php foreach ($perms as $p): ?>
            <div class="perm-row">
                <div class="perm-badge miss"><i class="bi bi-x"></i></div>
                <div class="flex-grow-1">
                    <div class="d-flex align-items-center gap-2">
                        <span class="perm-name"><?= $e($p['perm']) ?></span>
                        <?php if ($p['write']): ?>
                            <span class="write-tag">ReadWrite</span>
                        <?php endif; ?>
                    </div>
                    <div class="perm-desc"><?= $e($p['desc']) ?></div>
                    <div class="perm-features mt-1">
                        <?php foreach ($p['features'] as $f): ?>
                            <span class="perm-feature-tag miss"><?= $e($f) ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="flex-shrink-0">
                    <code class="text-danger" style="font-size:11px; background:#fef2f2; padding:2px 6px; border-radius:4px;"><?= te('FEHLT') ?></code>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Granted Permissions (by section) ───────────────────────── -->
<?php if (!empty($bySectionGranted)): ?>
<div class="content-card mb-4">
    <div class="card-header-custom" style="cursor:pointer;" onclick="this.nextElementSibling.classList.toggle('d-none')">
        <i class="bi bi-check-circle text-success me-2"></i><?= te('Erteilte Berechtigungen') ?>
        <span class="badge bg-success ms-2"><?= $summary['granted'] ?></span>
        <i class="bi bi-chevron-down ms-auto"></i>
    </div>
    <div class="d-none">
        <?php foreach ($bySectionGranted as $section => $perms): ?>
            <div class="section-header">
                <i class="bi bi-folder2"></i>
                <span><?= $e($section) ?></span>
            </div>
            <?php foreach ($perms as $p): ?>
                <div class="perm-row">
                    <div class="perm-badge ok"><i class="bi bi-check"></i></div>
                    <div class="flex-grow-1">
                        <div class="d-flex align-items-center gap-2">
                            <span class="perm-name"><?= $e($p['perm']) ?></span>
                            <?php if ($p['write']): ?>
                                <span class="write-tag">ReadWrite</span>
                            <?php endif; ?>
                        </div>
                        <div class="perm-desc"><?= $e($p['desc']) ?></div>
                        <div class="perm-features mt-1">
                            <?php foreach ($p['features'] as $f): ?>
                                <span class="perm-feature-tag"><?= $e($f) ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="flex-shrink-0">
                        <code class="text-success" style="font-size:11px; background:#dcfce7; padding:2px 6px; border-radius:4px;">OK</code>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- Unknown roles in token (not in our map) ─────────────────── -->
<?php
$knownPerms = array_map('strtolower', array_keys(\App\Modules\Settings\PermissionCheckerService::getRequiredPermissions()));
$unknownRoles = array_filter($tokenInfo['roles'], fn($r) => !in_array(strtolower($r), $knownPerms, true));
?>
<?php if (!empty($unknownRoles)): ?>
<div class="content-card mb-4">
    <div class="card-header-custom" style="cursor:pointer;" onclick="this.nextElementSibling.classList.toggle('d-none')">
        <i class="bi bi-info-circle text-secondary me-2"></i><?= te('Weitere Berechtigungen im Token (nicht in dieser Prüfliste)') ?>
        <span class="badge bg-secondary ms-2"><?= count($unknownRoles) ?></span>
        <i class="bi bi-chevron-down ms-auto"></i>
    </div>
    <div class="card-body-custom d-none">
        <p class="text-muted small mb-2"><?= te('Diese Berechtigungen sind dem App-Konto erteilt, werden aber von keinem Feature dieser Anwendung direkt genutzt:') ?></p>
        <div class="d-flex flex-wrap gap-2">
            <?php foreach ($unknownRoles as $r): ?>
                <code style="font-size:11px; background:#f3f4f6; border:1px solid #e5e7eb; padding:3px 8px; border-radius:4px;"><?= $e($r) ?></code>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>
