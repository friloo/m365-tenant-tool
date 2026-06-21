<?php use App\Core\View; $e = fn($v) => View::escape($v); ?>

<?php if (!empty($diag)) include BASE_PATH . '/views/partials/graph_diagnostic.php'; ?>

<div class="alert alert-info d-flex gap-3 mb-3">
    <i class="bi bi-person-bounding-box flex-shrink-0 mt-1" style="font-size:1.4rem;color:#0078d4;"></i>
    <div>
        <strong><?= te('Identity Provider Trust') ?></strong> <?= te('— externe Authentifizierungs­quellen, die der Tenant akzeptiert (Google, Facebook für B2C oder SAML/WS-Fed-Federation mit ADFS, Okta, Ping Identity, …). Jeder zusätzliche IdP ist eine erweiterte Angriffsfläche und muss regelmäßig auditiert werden.') ?>
    </div>
</div>

<!-- Social / B2C-IdPs ───────────────────────────────────── -->
<div class="content-card mb-4">
    <div class="card-header-custom"><i class="bi bi-globe2 text-primary"></i><h6><?= te('Konfigurierte Identity Providers') ?></h6></div>
    <div class="card-body-custom p-0">
        <?php if (empty($idps)): ?>
            <div class="text-muted small p-4 text-center">
                Keine externen Identity Providers konfiguriert. (Standard, wenn der Tenant nur
                Microsoft-Accounts akzeptiert.)
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead><tr><th>Name</th><th>Typ</th><th>Client-ID</th></tr></thead>
                    <tbody>
                    <?php foreach ($idps as $idp): ?>
                        <tr>
                            <td class="fw-medium"><?= $e($idp['displayName'] ?? '–') ?></td>
                            <td><span class="badge bg-info text-dark"><?= $e($idp['identityProviderType'] ?? $idp['@odata.type'] ?? '–') ?></span></td>
                            <td class="font-monospace small"><?= $e($idp['clientId'] ?? '–') ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Federated Domains ─────────────────────────────────── -->
<div class="content-card mb-4">
    <div class="card-header-custom"><i class="bi bi-shield-shaded text-primary"></i><h6>Federierte Domains (SAML / WS-Fed)</h6></div>
    <div class="card-body-custom p-0">
        <?php if (empty($feds)): ?>
            <div class="text-muted small p-4 text-center">
                Keine federierten Domains. Alle Domains nutzen Cloud-only oder Pass-Through-Authentication.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead><tr><th>Domain</th><th>Verifiziert</th><th>Default</th></tr></thead>
                    <tbody>
                    <?php foreach ($feds as $f): ?>
                        <tr>
                            <td class="font-monospace"><?= $e($f['name']) ?></td>
                            <td><?php if ($f['isVerified']): ?>
                                <span class="badge bg-success">Ja</span>
                            <?php else: ?>
                                <span class="badge bg-warning text-dark">Nein</span>
                            <?php endif; ?></td>
                            <td><?= $f['isDefault'] ? '<span class="badge bg-primary">Standard</span>' : '–' ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<a href="https://entra.microsoft.com/#view/Microsoft_AAD_IAM/IdentityProvidersListBlade" target="_blank" rel="noopener" class="btn btn-outline-primary">
    <i class="bi bi-box-arrow-up-right me-1"></i>In Entra konfigurieren
</a>
