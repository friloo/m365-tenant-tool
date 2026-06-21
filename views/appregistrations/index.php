<?php use App\Core\View; $e = fn($v) => View::escape($v); ?>

<?php
// Check for secrets expiring within 30 days across all apps
$soonExpiring = [];
$now = time();
foreach ($apps as $app) {
    foreach ($app['passwordCredentials'] ?? [] as $cred) {
        $expTs = !empty($cred['endDateTime']) ? strtotime($cred['endDateTime']) : null;
        if ($expTs !== null && $expTs > $now && ($expTs - $now) < 30 * 86400) {
            $soonExpiring[] = $app['displayName'] ?? $app['id'];
            break;
        }
    }
}
?>

<?php if (!empty($soonExpiring)): ?>
    <div class="alert alert-danger mb-4">
        <i class="bi bi-clock-fill me-2"></i>
        <strong><?= count($soonExpiring) ?> App<?= count($soonExpiring) !== 1 ? 's' : '' ?> <?= te('mit ablaufenden Secrets (< 30 Tage):') ?></strong>
        <?= implode(', ', array_map($e, $soonExpiring)) ?>
    </div>
<?php endif; ?>

<?php if (!empty($highRiskApps)): ?>
    <div class="alert alert-danger mb-4">
        <i class="bi bi-shield-exclamation me-2"></i>
        <strong><?= count($highRiskApps) ?> App<?= count($highRiskApps) !== 1 ? 's' : '' ?> <?= te('mit hohem Risiko erkannt') ?></strong> —
        <?= te('Diese Apps besitzen weitreichende Berechtigungen und sollten überprüft werden.') ?>
    </div>
<?php endif; ?>

<!-- Stats row -->
<div class="row g-3 mb-4">
    <div class="col-sm-3">
        <div class="metric-card">
            <div class="metric-label"><?= te('App-Registrierungen') ?></div>
            <div class="metric-value"><?= count($apps) ?></div>
            <div class="metric-sub"><?= te('Eigene Apps im Tenant') ?></div>
        </div>
    </div>
    <div class="col-sm-3">
        <div class="metric-card">
            <div class="metric-label"><?= te('Hohes Risiko') ?></div>
            <div class="metric-value" style="color:<?= count($highRiskApps) > 0 ? '#dc2626' : '#16a34a' ?>;">
                <?= count($highRiskApps) ?>
                <?php if (count($highRiskApps) > 0): ?>
                    <span class="badge-danger ms-1" style="font-size:11px;">!</span>
                <?php endif; ?>
            </div>
            <div class="metric-sub"><?= te('Sensitive Berechtigungen') ?></div>
        </div>
    </div>
    <div class="col-sm-3">
        <div class="metric-card">
            <div class="metric-label"><?= te('Neu (letzte 30 Tage)') ?></div>
            <div class="metric-value" style="color:<?= $recentAppsCount > 0 ? '#f59e0b' : '#111827' ?>;">
                <?= $recentAppsCount ?>
            </div>
            <div class="metric-sub"><?= te('Kürzlich registriert') ?></div>
        </div>
    </div>
    <div class="col-sm-3">
        <div class="metric-card">
            <div class="metric-label"><?= te('Enterprise Apps') ?></div>
            <div class="metric-value"><?= count($servicePrincipals) ?></div>
            <div class="metric-sub">Service Principals</div>
        </div>
    </div>
</div>

<!-- Tabs -->
<ul class="nav nav-tabs mb-3" id="appregTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="apps-tab" data-bs-toggle="tab" data-bs-target="#apps-panel"
                type="button" role="tab">
            <i class="bi bi-code-square me-1"></i>
            <?= te('App-Registrierungen') ?>
            <span class="badge bg-secondary ms-1"><?= count($apps) ?></span>
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="sp-tab" data-bs-toggle="tab" data-bs-target="#sp-panel"
                type="button" role="tab">
            <i class="bi bi-building me-1"></i>
            <?= te('Enterprise Apps') ?>
            <span class="badge bg-secondary ms-1"><?= count($servicePrincipals) ?></span>
        </button>
    </li>
</ul>

<div class="tab-content">

    <!-- Tab: App-Registrierungen -->
    <div class="tab-pane fade show active" id="apps-panel" role="tabpanel">
        <div class="content-card">
            <div class="table-toolbar">
                <input type="text" id="appsSearch" class="search-box" placeholder="<?= te('App suchen…') ?>">
            </div>
            <div class="table-responsive">
                <table class="data-table" id="appsTable">
                    <thead>
                        <tr>
                            <th><?= te('Name') ?></th>
                            <th><?= te('App-ID') ?></th>
                            <th><?= te('Erstellt') ?></th>
                            <th><?= te('Zielgruppe') ?></th>
                            <th><?= te('Berechtigungen') ?></th>
                            <th><?= te('Secrets') ?></th>
                            <th><?= te('Risiko') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($apps as $app):
                            $appId       = $app['appId'] ?? '';
                            $objId       = $app['id'] ?? '';
                            $truncatedId = strlen($appId) > 16 ? substr($appId, 0, 8) . '…' . substr($appId, -4) : $appId;
                            $isHighRisk  = isset($highRiskAppIds[$objId]);
                            $permCount   = $service->countPermissions($app);
                            $audience    = $app['signInAudience'] ?? 'AzureADMyOrg';
                            $created     = $app['createdDateTime'] ?? null;
                            // Determine worst-case secret expiry status
                            $secretStatus = 'none'; // none | ok | warn | critical
                            $secretExpiry = null;
                            $nowTs = time();
                            foreach ($app['passwordCredentials'] ?? [] as $cred) {
                                $expTs = !empty($cred['endDateTime']) ? strtotime($cred['endDateTime']) : null;
                                if ($expTs === null) continue;
                                $diff = $expTs - $nowTs;
                                if ($diff <= 0) {
                                    $secretStatus = 'critical';
                                    $secretExpiry = $expTs;
                                    break;
                                } elseif ($diff < 30 * 86400) {
                                    $secretStatus = 'critical';
                                    $secretExpiry = $expTs;
                                } elseif ($diff < 90 * 86400 && $secretStatus !== 'critical') {
                                    $secretStatus = 'warn';
                                    $secretExpiry = $expTs;
                                } elseif ($secretStatus === 'none') {
                                    $secretStatus = 'ok';
                                    $secretExpiry = $expTs;
                                }
                            }
                        ?>
                        <tr>
                            <td>
                                <div style="font-size:13px;font-weight:500;">
                                    <?php if ($objId !== ''): ?>
                                        <a href="/appregistrations/<?= $e($objId) ?>" class="text-decoration-none"><?= $e($app['displayName'] ?? '') ?></a>
                                    <?php else: ?>
                                        <?= $e($app['displayName'] ?? '') ?>
                                    <?php endif; ?>
                                </div>
                                <?php if ($isHighRisk): ?>
                                    <div style="font-size:11px;color:#dc2626;margin-top:2px;">
                                        <i class="bi bi-exclamation-triangle-fill me-1"></i><?= implode(', ', array_map($e, $highRiskAppIds[$objId])) ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <code style="font-size:11px;background:#f3f4f6;padding:2px 5px;border-radius:3px;" title="<?= $e($appId) ?>">
                                    <?= $e($truncatedId) ?>
                                </code>
                            </td>
                            <td style="font-size:12px;color:#6b7280;white-space:nowrap;">
                                <?= $created ? date('d.m.Y', strtotime($created)) : '–' ?>
                            </td>
                            <td>
                                <?php if ($audience === 'AzureADMyOrg'): ?>
                                    <span class="badge-info"><?= te('Nur Tenant') ?></span>
                                <?php elseif ($audience === 'AzureADMultipleOrgs'): ?>
                                    <span class="badge-warning"><?= te('Multi-Tenant') ?></span>
                                <?php elseif (str_contains($audience, 'Personal')): ?>
                                    <span class="badge-warning"><?= te('Persönlich') ?></span>
                                <?php else: ?>
                                    <span class="badge-secondary"><?= $e($audience) ?></span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align:center;">
                                <?php if ($permCount > 0): ?>
                                    <span class="badge-<?= $permCount >= 5 ? 'warning' : 'neutral' ?>">
                                        <?= $permCount ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted" style="font-size:11px;">–</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($secretStatus === 'none'): ?>
                                    <span class="text-muted" style="font-size:11px;">–</span>
                                <?php elseif ($secretStatus === 'critical'): ?>
                                    <span class="badge-danger badge-pill" title="<?= $secretExpiry !== null ? date('d.m.Y', $secretExpiry) : '' ?>">
                                        <?php if ($secretExpiry !== null && $secretExpiry <= $nowTs): ?>
                                            <?= te('Abgelaufen') ?>
                                        <?php else: ?>
                                            <?= $secretExpiry !== null ? date('d.m.Y', $secretExpiry) : '!' ?>
                                        <?php endif; ?>
                                    </span>
                                <?php elseif ($secretStatus === 'warn'): ?>
                                    <span class="badge-warning badge-pill" title="<?= $secretExpiry !== null ? date('d.m.Y', $secretExpiry) : '' ?>">
                                        <?= $secretExpiry !== null ? date('d.m.Y', $secretExpiry) : '?' ?>
                                    </span>
                                <?php else: ?>
                                    <span class="badge-enabled badge-pill" title="<?= $secretExpiry !== null ? date('d.m.Y', $secretExpiry) : '' ?>">
                                        <?= $secretExpiry !== null ? date('d.m.Y', $secretExpiry) : 'OK' ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($isHighRisk): ?>
                                    <span class="badge-danger"><?= te('Hoch') ?></span>
                                <?php else: ?>
                                    <span class="badge-enabled">OK</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($apps)): ?>
                            <tr>
                                <td colspan="7">
                                    <div class="empty-state">
                                        <i class="bi bi-code-square"></i>
                                        <p><?= te('Keine App-Registrierungen gefunden') ?></p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Tab: Enterprise Apps (Service Principals) -->
    <div class="tab-pane fade" id="sp-panel" role="tabpanel">
        <div class="content-card">
            <div class="table-toolbar">
                <input type="text" id="spSearch" class="search-box" placeholder="<?= te('Enterprise App suchen…') ?>">
            </div>
            <div class="table-responsive">
                <table class="data-table" id="spTable">
                    <thead>
                        <tr>
                            <th><?= te('Name') ?></th>
                            <th><?= te('Typ') ?></th>
                            <th><?= te('Erstellt') ?></th>
                            <th><?= te('Herausgeber') ?></th>
                            <th><?= te('Aktiviert') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Determine tenant ID for external publisher detection
                        // We compare appOwnerOrganizationId — if it differs from any known local ID,
                        // flag as external. Collect all org IDs to find the majority (= own tenant).
                        $orgIds = array_filter(array_column($servicePrincipals, 'appOwnerOrganizationId'));
                        $orgIdCounts = array_count_values($orgIds);
                        arsort($orgIdCounts);
                        $ownTenantId = key($orgIdCounts);

                        foreach ($servicePrincipals as $sp):
                            $spType    = $sp['servicePrincipalType'] ?? 'Application';
                            $orgId     = $sp['appOwnerOrganizationId'] ?? null;
                            $isExternal = $orgId && $orgId !== $ownTenantId;
                            $enabled   = $sp['accountEnabled'] ?? true;
                            $created   = $sp['createdDateTime'] ?? null;
                            $tags      = $sp['tags'] ?? [];
                        ?>
                        <tr>
                            <td>
                                <div style="font-size:13px;font-weight:500;"><?= $e($sp['displayName'] ?? '') ?></div>
                                <?php if (!empty($tags)): ?>
                                    <div style="font-size:11px;color:#9ca3af;margin-top:2px;">
                                        <?= implode(', ', array_map($e, array_slice($tags, 0, 3))) ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge-info"><?= $e($spType) ?></span>
                            </td>
                            <td style="font-size:12px;color:#6b7280;white-space:nowrap;">
                                <?= $created ? date('d.m.Y', strtotime($created)) : '–' ?>
                            </td>
                            <td>
                                <?php if ($isExternal): ?>
                                    <span class="badge-warning"><?= te('Extern') ?></span>
                                <?php elseif ($orgId): ?>
                                    <span class="badge-enabled"><?= te('Eigener Tenant') ?></span>
                                <?php else: ?>
                                    <span class="badge-neutral">–</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($enabled): ?>
                                    <span class="badge-enabled"><?= te('Aktiv') ?></span>
                                <?php else: ?>
                                    <span class="badge-disabled"><?= te('Deaktiviert') ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($servicePrincipals)): ?>
                            <tr>
                                <td colspan="5">
                                    <div class="empty-state">
                                        <i class="bi bi-building"></i>
                                        <p><?= te('Keine Enterprise Apps gefunden') ?></p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<script>
initTableSearch('appsSearch', 'appsTable');
initTableSearch('spSearch', 'spTable');
</script>
