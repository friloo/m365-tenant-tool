<?php use App\Core\View; $e = fn($v) => View::escape($v); ?>

<?php
$mfaEnabled = count(array_filter($users, fn($u) => !empty($mfaMap[$u['userPrincipalName']]['mfaRegistered'])));
$total = count($users);
?>

<!-- Stats row -->
<div class="row g-3 mb-4">
    <div class="col-sm-4">
        <div class="metric-card">
            <div class="metric-label">Gesamt</div>
            <div class="metric-value"><?= number_format($total) ?></div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="metric-card">
            <div class="metric-label">MFA registriert</div>
            <div class="metric-value"><?= number_format($mfaEnabled) ?></div>
            <div class="metric-sub"><?= $total > 0 ? round(($mfaEnabled/$total)*100) : 0 ?>% der Benutzer</div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="metric-card">
            <div class="metric-label">Deaktiviert</div>
            <div class="metric-value"><?= number_format(count(array_filter($users, fn($u) => !($u['accountEnabled'] ?? true)))) ?></div>
        </div>
    </div>
</div>

<div class="content-card">
    <div class="table-toolbar">
        <input type="text" id="userSearch" class="search-box" placeholder="Benutzer suchen…">
        <a href="?refresh=1" class="btn btn-sm btn-outline-secondary ms-auto">
            <i class="bi bi-arrow-clockwise"></i> Aktualisieren
        </a>
    </div>

    <div class="table-responsive">
        <table class="data-table" id="userTable">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>UPN</th>
                    <th>Status</th>
                    <th>MFA</th>
                    <th>Lizenzen</th>
                    <th>Letzter Login</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <?php
                    $mfa     = $mfaMap[$user['userPrincipalName'] ?? ''] ?? null;
                    $enabled = $user['accountEnabled'] ?? true;
                    $licenses = count($user['assignedLicenses'] ?? []);
                    $lastSignIn = $user['signInActivity']['lastSignInDateTime'] ?? null;
                    ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div style="width:32px;height:32px;border-radius:50%;background:#e3f0fb;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:600;color:#0078d4;flex-shrink:0;">
                                    <?= strtoupper(substr($user['displayName'] ?? '?', 0, 1)) ?>
                                </div>
                                <div>
                                    <div style="font-weight:500;"><?= $e($user['displayName'] ?? '') ?></div>
                                    <?php if (!empty($user['jobTitle'])): ?>
                                        <div style="font-size:11px;color:#9ca3af;"><?= $e($user['jobTitle']) ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                        <td style="color:#6b7280;font-size:12px;"><?= $e($user['userPrincipalName'] ?? '') ?></td>
                        <td>
                            <?php if ($enabled): ?>
                                <span class="badge-enabled">Aktiv</span>
                            <?php else: ?>
                                <span class="badge-disabled">Deaktiviert</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($mfa): ?>
                                <?php if ($mfa['mfaRegistered']): ?>
                                    <span class="badge-enabled"><i class="bi bi-shield-check"></i> Ja</span>
                                <?php else: ?>
                                    <span class="badge-warning">Nein</span>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="badge-neutral">–</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($licenses > 0): ?>
                                <span class="badge-info"><?= $licenses ?></span>
                            <?php else: ?>
                                <span class="badge-neutral">0</span>
                            <?php endif; ?>
                        </td>
                        <td style="font-size:12px;color:#6b7280;">
                            <?php if ($lastSignIn): ?>
                                <?= date('d.m.Y', strtotime($lastSignIn)) ?>
                            <?php else: ?>
                                <span class="text-muted">Nie</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="/users/<?= $e($user['id']) ?>" class="btn btn-sm btn-link py-0" style="font-size:12px;">Detail</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>initTableSearch('userSearch', 'userTable');</script>
