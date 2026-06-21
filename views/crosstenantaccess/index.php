<?php use App\Core\View; $e = fn($v) => View::escape($v); ?>

<?php if (!empty($diag)) include BASE_PATH . '/views/partials/graph_diagnostic.php'; ?>

<div class="alert alert-info d-flex gap-3 mb-3">
    <i class="bi bi-arrow-left-right flex-shrink-0 mt-1" style="font-size:1.4rem;color:#0078d4;"></i>
    <div>
        <strong><?= te('Cross-Tenant-Access') ?></strong> <?= te('regelt, welche Partner-Tenants Zugriff auf deine Ressourcen haben (Inbound) und in welche Tenants deine User dürfen (Outbound). Wichtig für B2B-Kollaboration, Microsoft Teams Federation und Trust-Settings (z. B. MFA-Trust zwischen Tenants).') ?>
    </div>
</div>

<!-- Default ─────────────────────────────────────────────── -->
<div class="content-card mb-4">
    <div class="card-header-custom">
        <i class="bi bi-globe text-primary"></i>
        <h6><?= te('Default für unbekannte Tenants') ?></h6>
    </div>
    <div class="card-body-custom">
        <?php if (empty($default)): ?>
            <div class="text-muted small"><?= te('Default-Policy nicht lesbar.') ?></div>
        <?php else: ?>
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="text-muted small mb-1"><?= te('B2B-Kollaboration eingehend') ?></div>
                    <pre style="background:#f8fafc;padding:8px;border-radius:6px;font-size:12px;"><?= $e(json_encode($default['b2bCollaborationInbound'] ?? new \stdClass(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) ?></pre>
                </div>
                <div class="col-md-6">
                    <div class="text-muted small mb-1"><?= te('B2B-Kollaboration ausgehend') ?></div>
                    <pre style="background:#f8fafc;padding:8px;border-radius:6px;font-size:12px;"><?= $e(json_encode($default['b2bCollaborationOutbound'] ?? new \stdClass(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) ?></pre>
                </div>
                <div class="col-md-6">
                    <div class="text-muted small mb-1"><?= te('Inbound-Trust (MFA / Device)') ?></div>
                    <pre style="background:#f8fafc;padding:8px;border-radius:6px;font-size:12px;"><?= $e(json_encode($default['inboundTrust'] ?? new \stdClass(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) ?></pre>
                </div>
                <div class="col-md-6">
                    <div class="text-muted small mb-1"><?= te('Default für unbekannte Tenants') ?></div>
                    <pre style="background:#f8fafc;padding:8px;border-radius:6px;font-size:12px;"><?= $e($default['isServiceProvider'] ?? 'false') ?></pre>
                </div>
            </div>
        <?php endif; ?>
        <a href="https://entra.microsoft.com/#view/Microsoft_AAD_IAM/CrossTenantAccessPolicyMenuBlade" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary mt-2">
            <i class="bi bi-box-arrow-up-right me-1"></i><?= te('In Entra konfigurieren') ?>
        </a>
    </div>
</div>

<!-- Partner-Spezifisch ─────────────────────────────────── -->
<div class="content-card mb-4">
    <div class="card-header-custom">
        <i class="bi bi-people-fill text-primary"></i>
        <h6><?= te('Partner-spezifische Konfigurationen') ?></h6>
        <span class="ms-auto text-muted small"><?= count($partners) ?> <?= te('Partner') ?></span>
    </div>
    <div class="card-body-custom p-0">
        <?php if (empty($partners)): ?>
            <div class="text-muted small p-4 text-center">
                <?= te('Keine Partner-spezifischen Cross-Tenant-Policies konfiguriert — alle externen Tenants nutzen die Default-Policy oben.') ?>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead><tr>
                        <th>Tenant-ID</th>
                        <th><?= te('B2B in/out') ?></th>
                        <th><?= te('Direct Connect in/out') ?></th>
                        <th><?= te('Trust akzeptiert') ?></th>
                        <th><?= te('Service-Provider?') ?></th>
                    </tr></thead>
                    <tbody>
                    <?php foreach ($partners as $p): ?>
                        <tr>
                            <td class="font-monospace small"><?= $e($p['tenantId']) ?></td>
                            <td><?= $e($p['inbound_b2bCollab']) ?> / <?= $e($p['outbound_b2bCollab']) ?></td>
                            <td><?= $e($p['inbound_b2bDirect']) ?> / <?= $e($p['outbound_b2bDirect']) ?></td>
                            <td><?= $e($p['inbound_trust']) ?></td>
                            <td>
                                <?php if ($p['isServiceProvider']): ?>
                                    <span class="badge bg-info text-dark">MSP</span>
                                <?php else: ?>
                                    <span class="text-muted small"><?= te('nein') ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
