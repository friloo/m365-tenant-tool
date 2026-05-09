<?php use App\Core\View; $e = fn($v) => View::escape($v); ?>

<div class="mb-3">
    <a href="/users" class="text-muted text-decoration-none small">← Zurück zu Benutzer</a>
</div>

<div class="row g-3">
    <div class="col-lg-4">
        <div class="content-card">
            <div class="card-body-custom text-center py-4">
                <div style="width:80px;height:80px;border-radius:50%;background:#e3f0fb;display:inline-flex;align-items:center;justify-content:center;font-size:2rem;font-weight:700;color:#0078d4;margin-bottom:12px;">
                    <?= strtoupper(substr($user['displayName'] ?? '?', 0, 1)) ?>
                </div>
                <h5 class="mb-1"><?= $e($user['displayName'] ?? '') ?></h5>
                <p class="text-muted mb-0 small"><?= $e($user['userPrincipalName'] ?? '') ?></p>
                <?php if (!empty($user['jobTitle'])): ?>
                    <p class="text-muted mb-0 small"><?= $e($user['jobTitle']) ?></p>
                <?php endif; ?>
                <div class="mt-3">
                    <?php if ($user['accountEnabled'] ?? true): ?>
                        <span class="badge-enabled">Aktiv</span>
                    <?php else: ?>
                        <span class="badge-disabled">Deaktiviert</span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card-body-custom border-top">
                <table class="table table-sm mb-0">
                    <?php
                    $fields = [
                        'E-Mail'      => $user['mail'] ?? null,
                        'Abteilung'   => $user['department'] ?? null,
                        'Telefon'     => $user['mobilePhone'] ?? null,
                        'Standort'    => $user['usageLocation'] ?? null,
                        'Erstellt'    => isset($user['createdDateTime']) ? date('d.m.Y', strtotime($user['createdDateTime'])) : null,
                    ];
                    foreach ($fields as $label => $val):
                        if (!$val) continue;
                    ?>
                        <tr>
                            <td class="text-muted small"><?= $label ?></td>
                            <td class="small fw-medium"><?= $e($val) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="content-card mb-3">
            <div class="card-header-custom">
                <i class="bi bi-award text-success"></i>
                <h6>Lizenzen</h6>
            </div>
            <div class="card-body-custom">
                <?php $licenses = $user['assignedLicenses'] ?? []; ?>
                <?php if (empty($licenses)): ?>
                    <p class="text-muted small mb-0">Keine Lizenzen zugewiesen</p>
                <?php else: ?>
                    <p class="small mb-0"><?= count($licenses) ?> Lizenz(en) zugewiesen</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="content-card">
            <div class="card-header-custom">
                <i class="bi bi-diagram-3 text-primary"></i>
                <h6>Gruppen-Mitgliedschaften (<?= count($groups) ?>)</h6>
            </div>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr><th>Gruppe</th><th>Typ</th></tr>
                    </thead>
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
