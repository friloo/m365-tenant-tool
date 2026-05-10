<?php use App\Core\View; use App\Core\Session; use App\Auth\LocalAuth; $e = fn($v) => View::escape($v); ?>

<div class="mb-3">
    <a href="/appregistrations" class="text-muted text-decoration-none small">
        <i class="bi bi-arrow-left me-1"></i>Zurück zu App-Registrierungen
    </a>
</div>

<?php if (!empty($flash)): ?>
    <div class="alert alert-success alert-dismissible mb-3">
        <i class="bi bi-check-circle me-2"></i><?= $e($flash) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>
<?php if (!empty($error)): ?>
    <div class="alert alert-danger alert-dismissible mb-3">
        <i class="bi bi-exclamation-triangle me-2"></i><?= $e($error) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php
$newSecret = \App\Core\Session::getFlash('new_secret');
if (!empty($newSecret)):
?>
    <div class="alert alert-success mb-4" style="border:2px solid #16a34a;">
        <div class="d-flex align-items-start gap-2">
            <i class="bi bi-key-fill text-success mt-1" style="font-size:1.2rem;"></i>
            <div class="flex-grow-1">
                <strong>Neues Secret — nur einmal sichtbar!</strong>
                <div class="mt-2 p-2 bg-white rounded border" style="font-family:monospace;">
                    <code id="newSecretValue" style="word-break:break-all;font-size:13px;"><?= $e($newSecret) ?></code>
                </div>
            </div>
            <button type="button"
                    class="btn btn-sm btn-outline-success"
                    onclick="navigator.clipboard.writeText(document.getElementById('newSecretValue').innerText).then(()=>{this.innerHTML='<i class=\'bi bi-check2\'></i> Kopiert';setTimeout(()=>{this.innerHTML='<i class=\'bi bi-clipboard\'></i> Kopieren'},2000)})">
                <i class="bi bi-clipboard"></i> Kopieren
            </button>
        </div>
    </div>
<?php endif; ?>

<?php
$appObjectId     = $detail['id'] ?? '';
$appClientId     = $detail['appId'] ?? '';
$displayName     = $detail['displayName'] ?? '';
$signInAudience  = $detail['signInAudience'] ?? '';
$createdDateTime = $detail['createdDateTime'] ?? null;
$passwordCreds   = $detail['passwordCredentials'] ?? [];
$keyCreds        = $detail['keyCredentials'] ?? [];
$nowTs           = time();
?>

<!-- Section 1: App Info -->
<div class="content-card mb-4">
    <div class="card-header-custom">
        <i class="bi bi-code-square text-primary"></i>
        <h6><?= $e($displayName) ?></h6>
    </div>
    <div class="card-body-custom">
        <table class="table table-sm mb-0">
            <tbody>
                <tr>
                    <td class="text-muted small" style="width:180px;">Anzeigename</td>
                    <td class="small fw-medium"><?= $e($displayName) ?></td>
                </tr>
                <tr>
                    <td class="text-muted small">App-ID (Client-ID)</td>
                    <td>
                        <code id="appClientIdVal" style="font-size:12px;background:#f3f4f6;padding:2px 6px;border-radius:3px;"><?= $e($appClientId) ?></code>
                        <button type="button"
                                class="btn btn-sm btn-link p-0 ms-2"
                                style="font-size:11px;"
                                onclick="navigator.clipboard.writeText('<?= $e($appClientId) ?>').then(()=>{this.innerHTML='Kopiert';setTimeout(()=>{this.innerHTML='Kopieren'},2000)})">
                            Kopieren
                        </button>
                    </td>
                </tr>
                <tr>
                    <td class="text-muted small">Zielgruppe</td>
                    <td class="small">
                        <?php if ($signInAudience === 'AzureADMyOrg'): ?>
                            <span class="badge-info">Nur Tenant</span>
                        <?php elseif ($signInAudience === 'AzureADMultipleOrgs'): ?>
                            <span class="badge-warning">Multi-Tenant</span>
                        <?php elseif (str_contains($signInAudience, 'Personal')): ?>
                            <span class="badge-warning">Persönlich</span>
                        <?php else: ?>
                            <span class="badge-secondary"><?= $e($signInAudience) ?></span>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <td class="text-muted small">Erstellt</td>
                    <td class="small"><?= $createdDateTime ? date('d.m.Y H:i', strtotime($createdDateTime)) : '–' ?></td>
                </tr>
                <?php if (!empty($detail['web']['redirectUris'])): ?>
                <tr>
                    <td class="text-muted small">Redirect URIs</td>
                    <td class="small">
                        <?php foreach ($detail['web']['redirectUris'] as $uri): ?>
                            <div><code style="font-size:11px;"><?= $e($uri) ?></code></div>
                        <?php endforeach; ?>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Section 2: Client Secrets -->
<div class="content-card mb-4">
    <div class="card-header-custom d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-key text-warning"></i>
            <h6 class="mb-0">Client Secrets (<?= count($passwordCreds) ?>)</h6>
        </div>
        <?php if (LocalAuth::isAdmin() && $appObjectId !== ''): ?>
            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addSecretModal">
                <i class="bi bi-plus-circle me-1"></i>Neues Secret anlegen
            </button>
        <?php endif; ?>
    </div>
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Erstellt</th>
                    <th>Läuft ab</th>
                    <th>Status</th>
                    <?php if (LocalAuth::isAdmin()): ?>
                        <th style="width:80px;"></th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($passwordCreds as $cred):
                    $keyId     = $cred['keyId'] ?? '';
                    $credName  = $cred['displayName'] ?? '(ohne Name)';
                    $startTs   = !empty($cred['startDateTime']) ? strtotime($cred['startDateTime']) : null;
                    $endTs     = !empty($cred['endDateTime'])   ? strtotime($cred['endDateTime'])   : null;
                    $diff      = $endTs !== null ? ($endTs - $nowTs) : null;
                    if ($diff === null) {
                        $statusClass = 'badge-neutral';
                        $statusLabel = 'Unbekannt';
                    } elseif ($diff <= 0) {
                        $statusClass = 'badge-danger';
                        $statusLabel = 'Abgelaufen';
                    } elseif ($diff < 30 * 86400) {
                        $statusClass = 'badge-danger';
                        $statusLabel = '< 30 Tage';
                    } elseif ($diff < 90 * 86400) {
                        $statusClass = 'badge-warning';
                        $statusLabel = '< 90 Tage';
                    } else {
                        $statusClass = 'badge-enabled';
                        $statusLabel = 'Aktiv';
                    }
                ?>
                <tr>
                    <td style="font-size:13px;"><?= $e($credName) ?></td>
                    <td style="font-size:12px;color:#6b7280;white-space:nowrap;">
                        <?= $startTs ? date('d.m.Y', $startTs) : '–' ?>
                    </td>
                    <td style="font-size:12px;white-space:nowrap;<?= $diff !== null && $diff < 90 * 86400 ? 'font-weight:500;' : '' ?>">
                        <?= $endTs ? date('d.m.Y', $endTs) : '–' ?>
                    </td>
                    <td><span class="<?= $statusClass ?>"><?= $statusLabel ?></span></td>
                    <?php if (LocalAuth::isAdmin()): ?>
                        <td>
                            <form method="post"
                                  action="/appregistrations/<?= $e($appObjectId) ?>/delete-secret"
                                  onsubmit="return confirm('Secret wirklich löschen? Apps die dieses Secret verwenden können sich nicht mehr anmelden.')"
                                  class="mb-0">
                                <input type="hidden" name="key_id" value="<?= $e($keyId) ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    <?php endif; ?>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($passwordCreds)): ?>
                    <tr>
                        <td colspan="<?= LocalAuth::isAdmin() ? '5' : '4' ?>">
                            <div class="empty-state">
                                <i class="bi bi-key"></i>
                                <p>Keine Client Secrets vorhanden</p>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Section 3: Certificates -->
<div class="content-card mb-4">
    <div class="card-header-custom">
        <i class="bi bi-patch-check text-secondary"></i>
        <h6>Zertifikate (<?= count($keyCreds) ?>)</h6>
    </div>
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Fingerabdruck</th>
                    <th>Läuft ab</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($keyCreds as $cert):
                    $certName  = $cert['displayName'] ?? '(ohne Name)';
                    $thumb     = $cert['customKeyIdentifier'] ?? '';
                    $certEndTs = !empty($cert['endDateTime']) ? strtotime($cert['endDateTime']) : null;
                ?>
                <tr>
                    <td style="font-size:13px;"><?= $e($certName) ?></td>
                    <td>
                        <?php if ($thumb !== ''): ?>
                            <code style="font-size:11px;background:#f3f4f6;padding:2px 5px;border-radius:3px;"><?= $e(strtoupper(substr($thumb, 0, 20))) ?>…</code>
                        <?php else: ?>
                            <span class="text-muted" style="font-size:11px;">–</span>
                        <?php endif; ?>
                    </td>
                    <td style="font-size:12px;white-space:nowrap;">
                        <?= $certEndTs ? date('d.m.Y', $certEndTs) : '–' ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($keyCreds)): ?>
                    <tr>
                        <td colspan="3">
                            <div class="empty-state">
                                <i class="bi bi-patch-check"></i>
                                <p>Keine Zertifikate vorhanden</p>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if (LocalAuth::isAdmin() && $appObjectId !== ''): ?>
<!-- Add Secret Modal -->
<div class="modal fade" id="addSecretModal" tabindex="-1" aria-labelledby="addSecretModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="/appregistrations/<?= $e($appObjectId) ?>/add-secret">
                <div class="modal-header">
                    <h5 class="modal-title" id="addSecretModalLabel">
                        <i class="bi bi-key-fill me-2 text-warning"></i>Neues Client Secret anlegen
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Schließen"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning mb-3" style="font-size:13px;">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i>
                        Der Secret-Wert wird <strong>nur einmal</strong> angezeigt. Kopiere ihn sofort nach dem Erstellen.
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-medium" for="secret_name">Name / Beschreibung</label>
                        <input type="text"
                               id="secret_name"
                               name="secret_name"
                               class="form-control form-control-sm"
                               value="Neues Secret <?= date('Y-m') ?>"
                               maxlength="100"
                               required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-medium" for="expiry_months">Gültigkeitsdauer</label>
                        <select id="expiry_months" name="expiry_months" class="form-select form-select-sm">
                            <option value="1">1 Monat</option>
                            <option value="3">3 Monate</option>
                            <option value="6">6 Monate</option>
                            <option value="12" selected>12 Monate</option>
                            <option value="18">18 Monate</option>
                            <option value="24">24 Monate</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Abbrechen</button>
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="bi bi-plus-circle me-1"></i>Secret erstellen
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>
