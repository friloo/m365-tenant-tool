<?php use App\Core\View; use App\Auth\LocalAuth; $e = fn($v) => View::escape($v); ?>

<div class="mb-3">
    <a href="/users" class="text-muted text-decoration-none small">← Zurück zu Benutzer</a>
</div>

<?php if (!empty($flash)): ?>
    <div class="alert alert-success alert-dismissible mb-3"><i class="bi bi-check-circle me-2"></i><?= $e($flash) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>
<?php if (!empty($error)): ?>
    <div class="alert alert-danger alert-dismissible mb-3"><i class="bi bi-exclamation-triangle me-2"></i><?= $e($error) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<div class="row g-3">
    <!-- Profile card -->
    <div class="col-lg-4">
        <div class="content-card mb-3">
            <div class="card-body-custom text-center py-4">
                <div style="width:80px;height:80px;border-radius:50%;background:#e3f0fb;display:inline-flex;align-items:center;justify-content:center;font-size:2rem;font-weight:700;color:#0078d4;margin-bottom:12px;">
                    <?= strtoupper(substr($user['displayName'] ?? '?', 0, 1)) ?>
                </div>
                <h5 class="mb-1"><?= $e($user['displayName'] ?? '') ?></h5>
                <p class="text-muted mb-0 small"><?= $e($user['userPrincipalName'] ?? '') ?></p>
                <?php if (!empty($user['jobTitle'])): ?>
                    <p class="text-muted mb-0 small"><?= $e($user['jobTitle']) ?></p>
                <?php endif; ?>
                <div class="mt-3 mb-2">
                    <?php $enabled = $user['accountEnabled'] ?? true; ?>
                    <?php if ($enabled): ?>
                        <span class="badge-enabled">Aktiv</span>
                    <?php else: ?>
                        <span class="badge-disabled">Deaktiviert</span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Properties -->
            <div class="card-body-custom border-top">
                <table class="table table-sm mb-0">
                    <?php foreach (['E-Mail' => $user['mail'] ?? null, 'Abteilung' => $user['department'] ?? null, 'Telefon' => $user['mobilePhone'] ?? null, 'Standort' => $user['usageLocation'] ?? null, 'Erstellt' => isset($user['createdDateTime']) ? date('d.m.Y', strtotime($user['createdDateTime'])) : null] as $label => $val):
                        if (!$val) continue; ?>
                        <tr>
                            <td class="text-muted small"><?= $label ?></td>
                            <td class="small fw-medium"><?= $e($val) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </div>

            <!-- Actions -->
            <div class="card-body-custom border-top">
                <h6 class="small text-muted text-uppercase mb-3">Aktionen</h6>
                <div class="d-grid gap-2">
                    <!-- Toggle enable/disable -->
                    <form method="post" action="/users/<?= $e($user['id']) ?>/toggle-enabled"
                          onsubmit="return confirm('Benutzer wirklich <?= $enabled ? 'deaktivieren' : 'aktivieren' ?>?')">
                        <button type="submit" class="btn btn-sm w-100 <?= $enabled ? 'btn-outline-warning' : 'btn-outline-success' ?>">
                            <i class="bi bi-<?= $enabled ? 'person-x' : 'person-check' ?> me-1"></i>
                            <?= $enabled ? 'Benutzer deaktivieren' : 'Benutzer aktivieren' ?>
                        </button>
                    </form>
                    <!-- MFA Reset -->
                    <form method="post" action="/users/<?= $e($user['id']) ?>/reset-mfa"
                          onsubmit="return confirm('MFA-Methoden für diesen Benutzer wirklich zurücksetzen? Der Benutzer muss MFA neu registrieren.')">
                        <button type="submit" class="btn btn-sm btn-outline-danger w-100">
                            <i class="bi bi-shield-x me-1"></i> MFA zurücksetzen
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Right column -->
    <div class="col-lg-8">

        <!-- Licenses -->
        <div class="content-card mb-3">
            <div class="card-header-custom">
                <i class="bi bi-award text-success"></i>
                <h6>Lizenzen (<?= count($user['assignedLicenses'] ?? []) ?>)</h6>
            </div>
            <div class="card-body-custom">
                <?php
                $service = app_service(\App\Modules\Licenses\LicensesService::class);
                $assigned = $user['assignedLicenses'] ?? [];
                $assignedSkuIds = array_column($assigned, 'skuId');
                ?>

                <!-- Assigned licenses -->
                <?php if ($assigned): ?>
                    <div class="mb-3">
                        <?php foreach ($assigned as $lic): ?>
                            <div class="d-flex align-items-center justify-content-between mb-2 p-2 rounded" style="background:#f9fafb;">
                                <span class="small fw-medium"><?= $e($service->friendlyName($lic['skuPartNumber'] ?? '')) ?></span>
                                <form method="post" action="/users/<?= $e($user['id']) ?>/remove-license"
                                      onsubmit="return confirm('Lizenz entfernen?')" class="mb-0">
                                    <input type="hidden" name="sku_id" value="<?= $e($lic['skuId']) ?>">
                                    <button type="submit" class="btn btn-xs btn-outline-danger py-0 px-2" style="font-size:11px;">
                                        <i class="bi bi-x"></i> Entfernen
                                    </button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-muted small mb-3">Keine Lizenzen zugewiesen.</p>
                <?php endif; ?>

                <!-- Assign license -->
                <?php $availableSkus = array_filter($skus, fn($s) => !in_array($s['skuId'], $assignedSkuIds) && $s['available'] > 0); ?>
                <?php if ($availableSkus): ?>
                    <form method="post" action="/users/<?= $e($user['id']) ?>/assign-license" class="d-flex gap-2">
                        <select name="sku_id" class="form-select form-select-sm">
                            <option value="">Lizenz auswählen…</option>
                            <?php foreach ($availableSkus as $sku): ?>
                                <option value="<?= $e($sku['skuId']) ?>">
                                    <?= $e($sku['name']) ?> (<?= $sku['available'] ?> verfügbar)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="btn btn-sm btn-primary text-nowrap">
                            <i class="bi bi-plus me-1"></i>Zuweisen
                        </button>
                    </form>
                <?php else: ?>
                    <p class="text-muted small mb-0">Alle verfügbaren Lizenzen bereits zugewiesen.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Groups -->
        <div class="content-card">
            <div class="card-header-custom">
                <i class="bi bi-diagram-3 text-primary"></i>
                <h6>Gruppen-Mitgliedschaften (<?= count($groups) ?>)</h6>
            </div>
            <div class="table-responsive">
                <table class="data-table">
                    <thead><tr><th>Gruppe</th><th>Typ</th></tr></thead>
                    <tbody>
                        <?php foreach ($groups as $g): ?>
                            <tr>
                                <td><?= $e($g['displayName'] ?? '') ?></td>
                                <td>
                                    <?php $types = $g['groupTypes'] ?? []; ?>
                                    <?php if (in_array('Unified', $types)): ?>
                                        <span class="badge-info">M365</span>
                                    <?php elseif ($g['securityEnabled'] ?? false): ?>
                                        <span class="badge-neutral">Security</span>
                                    <?php else: ?>
                                        <span class="badge-neutral">Distribution</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($groups)): ?>
                            <tr><td colspan="2" class="text-center text-muted">Keine Mitgliedschaften</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
