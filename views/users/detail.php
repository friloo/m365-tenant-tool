<?php use App\Core\View; use App\Auth\LocalAuth; use App\Core\Config; $e = fn($v) => View::escape($v); ?>

<div class="mb-3">
    <a href="/users" class="text-muted text-decoration-none small">← Zurück zu Benutzer</a>
</div>

<?php if (!empty($flash)): ?>
    <div class="alert alert-success alert-dismissible mb-3"><i class="bi bi-check-circle me-2"></i><?= $e($flash) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>
<?php if (!empty($error)): ?>
    <div class="alert alert-danger alert-dismissible mb-3"><i class="bi bi-exclamation-triangle me-2"></i><?= $e($error) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<?php
// ── Password expiry helpers ──────────────────────────────────────────────────
$expiryDays       = (int) Config::getInstance()->get('password_expiry_days', '90');
$lastPwChange     = $user['lastPasswordChangeDateTime'] ?? null;
$onPremSync       = !empty($user['onPremisesSyncEnabled']);
$pwPolicies       = $user['passwordPolicies'] ?? '';
$neverExpires     = str_contains((string)$pwPolicies, 'DisablePasswordExpiration') || ($onPremSync && !$lastPwChange);

$pwDateFormatted  = null;
$pwExpiryLabel    = null;
$pwExpiryClass    = 'text-muted';

if ($lastPwChange) {
    $pwDateFormatted = date('d.m.Y', strtotime($lastPwChange));
}

if ($neverExpires) {
    $pwExpiryLabel = 'Läuft nicht ab';
    $pwExpiryClass = 'text-muted';
} elseif ($lastPwChange) {
    $expiryTs   = strtotime($lastPwChange) + ($expiryDays * 86400);
    $daysLeft   = (int) ceil(($expiryTs - time()) / 86400);
    if ($daysLeft <= 0) {
        $pwExpiryLabel = 'Abgelaufen';
        $pwExpiryClass = 'text-danger fw-semibold';
    } elseif ($daysLeft <= 14) {
        $pwExpiryLabel = "Läuft in {$daysLeft} Tag(en) ab";
        $pwExpiryClass = 'text-danger fw-semibold';
    } elseif ($daysLeft <= 30) {
        $pwExpiryLabel = "Läuft in {$daysLeft} Tag(en) ab";
        $pwExpiryClass = 'text-warning fw-semibold';
    } else {
        $pwExpiryLabel = date('d.m.Y', $expiryTs);
        $pwExpiryClass = 'text-success';
    }
}
?>

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
                <div class="mt-2">
                    <a href="/users/<?= $e($user['id']) ?>/edit" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-pencil me-1"></i>Bearbeiten
                    </a>
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

                    <!-- Password info row -->
                    <?php if ($pwDateFormatted || $pwExpiryLabel): ?>
                        <tr>
                            <td class="text-muted small">Passwort geändert</td>
                            <td class="small fw-medium"><?= $e($pwDateFormatted ?? '–') ?></td>
                        </tr>
                        <?php if ($pwExpiryLabel): ?>
                        <tr>
                            <td class="text-muted small">Passwort-Ablauf</td>
                            <td class="small <?= $pwExpiryClass ?>">
                                <?= $e($pwExpiryLabel) ?>
                                <?php if ($onPremSync): ?>
                                    <br><span class="text-muted" style="font-size:10px;">Reset nur im lokalen AD möglich</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endif; ?>
                    <?php endif; ?>
                </table>
            </div>

            <!-- Actions -->
            <div class="card-body-custom border-top">
                <h6 class="small text-muted text-uppercase mb-3">Aktionen</h6>
                <div class="d-grid gap-2">
                    <!-- Toggle enable/disable -->
                    <form method="post" action="/users/<?= $e($user['id']) ?>/toggle-enabled"
                          onsubmit="return confirm('Benutzer wirklich <?= $enabled ? 'deaktivieren' : 'aktivieren' ?>?')">
                        <?= \App\Core\Csrf::field() ?>
                        <button type="submit" class="btn btn-sm w-100 <?= $enabled ? 'btn-outline-warning' : 'btn-outline-success' ?>">
                            <i class="bi bi-<?= $enabled ? 'person-x' : 'person-check' ?> me-1"></i>
                            <?= $enabled ? 'Benutzer deaktivieren' : 'Benutzer aktivieren' ?>
                        </button>
                    </form>
                    <!-- MFA Reset -->
                    <form method="post" action="/users/<?= $e($user['id']) ?>/reset-mfa"
                          onsubmit="return confirm('MFA-Methoden für diesen Benutzer wirklich zurücksetzen? Der Benutzer muss MFA neu registrieren.')">
                        <?= \App\Core\Csrf::field() ?>
                        <button type="submit" class="btn btn-sm btn-outline-danger w-100">
                            <i class="bi bi-shield-x me-1"></i> MFA zurücksetzen
                        </button>
                    </form>
                    <?php if (\App\Auth\LocalAuth::isAdmin()): ?>
                    <!-- Passwort-Reset (Admin) -->
                    <form method="post" action="/users/<?= $e($user['id']) ?>/reset-password" class="mt-2"
                          onsubmit="return confirm('Ein neues temporäres Passwort erzeugen? Der Benutzer muss es bei der nächsten Anmeldung ändern. Das Passwort wird einmalig angezeigt.')">
                        <?= \App\Core\Csrf::field() ?>
                        <button type="submit" class="btn btn-sm btn-outline-warning w-100">
                            <i class="bi bi-key me-1"></i> Passwort zurücksetzen
                        </button>
                    </form>
                    <?php endif; ?>
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
                $assigned = $user['assignedLicenses'] ?? [];
                $assignedSkuIds = array_column($assigned, 'skuId');
                // Build skuId → name map from the $skus list (assignedLicenses only has skuId, not skuPartNumber)
                $skuNameMap = array_column($skus, 'name', 'skuId');
                ?>

                <!-- Assigned licenses -->
                <?php if ($assigned): ?>
                    <div class="mb-3">
                        <?php foreach ($assigned as $lic): ?>
                            <div class="d-flex align-items-center justify-content-between mb-2 p-2 rounded" style="background:#f9fafb;">
                                <span class="small fw-medium"><?= $e($skuNameMap[$lic['skuId']] ?? $lic['skuId']) ?></span>
                                <form method="post" action="/users/<?= $e($user['id']) ?>/remove-license"
                                      onsubmit="return confirm('Lizenz entfernen?')" class="mb-0">
                                    <?= \App\Core\Csrf::field() ?>
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
                        <?= \App\Core\Csrf::field() ?>
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

        <!-- Tabs: Groups + Sign-in History -->
        <div class="content-card mb-3">
            <ul class="nav nav-tabs px-3 pt-2" id="userDetailTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="tab-groups" data-bs-toggle="tab" data-bs-target="#pane-groups" type="button" role="tab">
                        <i class="bi bi-diagram-3 me-1"></i>Gruppen (<?= count($groups) ?>)
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab-signins" data-bs-toggle="tab" data-bs-target="#pane-signins" type="button" role="tab">
                        <i class="bi bi-clock-history me-1"></i>Anmeldungen
                    </button>
                </li>
            </ul>
            <div class="tab-content">

                <!-- Groups tab -->
                <div class="tab-pane fade show active" id="pane-groups" role="tabpanel">
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

                <!-- Sign-in history tab -->
                <div class="tab-pane fade" id="pane-signins" role="tabpanel">
                    <?php if (empty($signIns)): ?>
                        <div class="empty-state p-4 text-center">
                            <i class="bi bi-clock-history fs-2 text-muted mb-2 d-block"></i>
                            <p class="text-muted mb-0">Keine Anmeldedaten in den letzten 30 Tagen oder die Berechtigung <code>AuditLog.Read.All</code> fehlt. Genaue Diagnose unter <a href="/signinlog?user=<?= urlencode($user['id'] ?? '') ?>">Anmeldeprotokoll</a>.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Datum/Uhrzeit</th>
                                        <th>App</th>
                                        <th>IP-Adresse</th>
                                        <th>Standort</th>
                                        <th>Gerät (OS)</th>
                                        <th>Ergebnis</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($signIns as $s): ?>
                                        <?php
                                        $dt       = isset($s['createdDateTime']) ? date('d.m.Y H:i', strtotime($s['createdDateTime'])) : '–';
                                        $app      = $s['appDisplayName'] ?? '–';
                                        $ip       = $s['ipAddress'] ?? '–';
                                        $city     = $s['location']['city'] ?? '';
                                        $country  = $s['location']['countryOrRegion'] ?? '';
                                        $location = implode(', ', array_filter([$city, $country])) ?: '–';
                                        $os       = $s['deviceDetail']['operatingSystem'] ?? '–';
                                        $errCode  = $s['status']['errorCode'] ?? -1;
                                        $success  = $errCode === 0;
                                        ?>
                                        <tr>
                                            <td class="small text-nowrap"><?= $e($dt) ?></td>
                                            <td class="small"><?= $e($app) ?></td>
                                            <td class="small text-nowrap"><?= $e($ip) ?></td>
                                            <td class="small"><?= $e($location) ?></td>
                                            <td class="small"><?= $e($os) ?></td>
                                            <td>
                                                <?php if ($success): ?>
                                                    <span class="badge-success">Erfolgreich</span>
                                                <?php else: ?>
                                                    <span class="badge-danger">Fehlgeschlagen</span>
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
        </div>

        <!-- Cloud-Cleanup (Offboarding) card -->
        <div class="content-card mb-3">
            <div class="card-header-custom" style="background: linear-gradient(135deg, #fff5f5 0%, #fff0e6 100%); border-bottom: 1px solid #ffd5cc;">
                <i class="bi bi-box-arrow-right text-danger"></i>
                <div>
                    <h6 class="mb-0 text-danger">Cloud-Cleanup (nach AD-Deaktivierung)</h6>
                    <p class="text-muted small mb-0">Führe diese Schritte aus, nachdem du den Benutzer im lokalen AD deaktiviert hast.</p>
                </div>
            </div>
            <div class="card-body-custom">
                <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#offboardingModal">
                    <i class="bi bi-box-arrow-right me-1"></i>Cloud-Cleanup starten…
                </button>
            </div>
        </div>

        <!-- Internal Notes card -->
        <div class="content-card">
            <div class="card-header-custom">
                <i class="bi bi-sticky text-warning"></i>
                <h6>Interne Notizen</h6>
            </div>
            <div class="card-body-custom">
                <?php if (!empty($notes)): ?>
                    <ul class="list-unstyled mb-3">
                        <?php foreach ($notes as $n): ?>
                            <li class="d-flex align-items-start justify-content-between mb-2 p-2 rounded" style="background:#f9fafb;">
                                <div>
                                    <div class="small text-muted mb-1">
                                        <?= $e($n['created_by']) ?> &middot; <?= $e(date('d.m.Y H:i', strtotime($n['created_at']))) ?>
                                    </div>
                                    <div class="small"><?= nl2br($e($n['note'])) ?></div>
                                </div>
                                <?php if (LocalAuth::role() === 'admin'): ?>
                                    <form method="post" action="/users/<?= $e($user['id']) ?>/notes/<?= (int)$n['id'] ?>"
                                          onsubmit="return confirm('Notiz wirklich löschen?')" class="ms-2 mb-0 flex-shrink-0">
                                        <?= \App\Core\Csrf::field() ?>
                                        <input type="hidden" name="_method" value="DELETE">
                                        <button type="submit" class="btn btn-xs btn-outline-danger py-0 px-2" style="font-size:11px;">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p class="text-muted small mb-3">Noch keine Notizen vorhanden.</p>
                <?php endif; ?>

                <?php if (LocalAuth::role() === 'admin'): ?>
                    <form method="post" action="/users/<?= $e($user['id']) ?>/notes">
                        <?= \App\Core\Csrf::field() ?>
                        <div class="mb-2">
                            <textarea name="note" class="form-control form-control-sm" rows="3"
                                      placeholder="Interne Notiz eingeben…" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="bi bi-plus me-1"></i>Notiz hinzufügen
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>

<!-- Offboarding Modal -->
<?php $licenseCount = count($user['assignedLicenses'] ?? []); ?>
<div class="modal fade" id="offboardingModal" tabindex="-1" aria-labelledby="offboardingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header border-bottom-0" style="background:#fff5f5;">
                <h5 class="modal-title text-danger" id="offboardingModalLabel">
                    <i class="bi bi-box-arrow-right me-2"></i>Cloud-Cleanup für <?= $e($user['displayName'] ?? '') ?>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Schließen"></button>
            </div>
            <form method="post" action="/users/<?= $e($user['id']) ?>/offboarding">
                <?= \App\Core\Csrf::field() ?>
                <div class="modal-body">
                    <p class="text-muted small mb-3">Wähle die Aktionen, die ausgeführt werden sollen. Bereits abgeschlossene Schritte können übersprungen werden.</p>

                    <!-- Revoke sessions -->
                    <div class="form-check mb-3 p-3 rounded" style="background:#f9fafb;">
                        <input class="form-check-input" type="checkbox" name="revoke_sessions" value="1" id="cb_revoke" checked>
                        <label class="form-check-label fw-medium" for="cb_revoke">
                            <i class="bi bi-stop-circle text-danger me-1"></i>Alle aktiven Sitzungen sofort beenden
                        </label>
                        <div class="text-muted small mt-1">Meldet den Benutzer sofort von allen Geräten und Apps ab.</div>
                    </div>

                    <!-- Remove licenses -->
                    <div class="form-check mb-3 p-3 rounded" style="background:#f9fafb;">
                        <input class="form-check-input" type="checkbox" name="remove_licenses" value="1" id="cb_licenses"
                               <?= $licenseCount > 0 ? 'checked' : '' ?>>
                        <label class="form-check-label fw-medium" for="cb_licenses">
                            <i class="bi bi-award text-warning me-1"></i>Alle Lizenzen entziehen
                            <span class="badge-secondary ms-1"><?= $licenseCount ?> Lizenz<?= $licenseCount !== 1 ? 'en' : '' ?></span>
                        </label>
                        <div class="text-muted small mt-1">Entfernt alle zugewiesenen Microsoft 365-Lizenzen.</div>
                    </div>

                    <!-- E-Mail forwarding -->
                    <div class="mb-3 p-3 rounded" style="background:#f9fafb;">
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="set_forwarding" value="1" id="cb_forward"
                                   onchange="document.getElementById('forward_to_wrap').style.display=this.checked?'block':'none'">
                            <label class="form-check-label fw-medium" for="cb_forward">
                                <i class="bi bi-envelope-forward text-info me-1"></i>E-Mail-Weiterleitung setzen
                            </label>
                        </div>
                        <div id="forward_to_wrap" style="display:none;">
                            <input type="email" class="form-control form-control-sm" name="forward_to"
                                   placeholder="weiterleitung@firma.de">
                            <div class="text-muted small mt-1">
                                <i class="bi bi-info-circle me-1"></i>Erfordert <code>MailboxSettings.ReadWrite</code>-Berechtigung.
                            </div>
                        </div>
                    </div>

                    <!-- Out-of-office -->
                    <div class="mb-2 p-3 rounded" style="background:#f9fafb;">
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="set_ooo" value="1" id="cb_ooo"
                                   onchange="document.getElementById('ooo_wrap').style.display=this.checked?'block':'none'">
                            <label class="form-check-label fw-medium" for="cb_ooo">
                                <i class="bi bi-chat-left-text text-secondary me-1"></i>Abwesenheitsnotiz aktivieren
                            </label>
                        </div>
                        <div id="ooo_wrap" style="display:none;">
                            <textarea class="form-control form-control-sm" name="ooo_message" rows="3"
                                      placeholder="Der Mitarbeiter hat das Unternehmen verlassen..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="background:#fff5f5;">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Abbrechen</button>
                    <button type="submit" class="btn btn-danger"
                            onclick="return confirm('Cloud-Cleanup wirklich ausführen? Diese Aktion kann nicht rückgängig gemacht werden.')">
                        <i class="bi bi-box-arrow-right me-1"></i>Cloud-Cleanup ausführen
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
