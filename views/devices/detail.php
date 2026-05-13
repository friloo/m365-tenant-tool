<?php use App\Core\View; use App\Auth\LocalAuth; $e = fn($v) => View::escape($v); ?>

<?php
$deviceId = $detail['id'] ?? '';

// Storage helpers
$formatBytes = function (int $bytes): string {
    if ($bytes <= 0) return '–';
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $i = 0;
    $val = (float) $bytes;
    while ($val >= 1024 && $i < count($units) - 1) {
        $val /= 1024;
        $i++;
    }
    return round($val, 1) . ' ' . $units[$i];
};

// Relative last sync
$relativeSync = function (string $dt): string {
    if ($dt === '') return '–';
    $ts   = strtotime($dt);
    $diff = time() - $ts;
    if ($diff < 60)         return 'Gerade eben';
    if ($diff < 3600)       return 'Vor ' . floor($diff / 60) . ' Minuten';
    if ($diff < 86400)      return 'Vor ' . floor($diff / 3600) . ' Stunden';
    $days = floor($diff / 86400);
    return 'Vor ' . $days . ' Tag' . ($days === 1.0 ? '' : 'en');
};

$compliance      = $detail['complianceState'] ?? 'unknown';
$isEncrypted     = $detail['isEncrypted'] ?? false;
$freeStorage     = (int) ($detail['freeStorageSpaceInBytes'] ?? 0);
$totalStorage    = (int) ($detail['totalStorageSpaceInBytes'] ?? 0);
$lastSync        = $detail['lastSyncDateTime'] ?? '';
$enrolledDate    = !empty($detail['enrolledDateTime']) ? date('d.m.Y', strtotime($detail['enrolledDateTime'])) : '–';
?>

<div class="mb-3">
    <a href="/devices" class="text-muted text-decoration-none small"><i class="bi bi-arrow-left me-1"></i>Zurück zu Geräte</a>
</div>

<?php if (!empty($flash)): ?>
    <div class="alert alert-success alert-dismissible mb-3"><i class="bi bi-check-circle me-2"></i><?= $e($flash) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>
<?php if (!empty($error)): ?>
    <div class="alert alert-danger alert-dismissible mb-3"><i class="bi bi-exclamation-triangle me-2"></i><?= $e($error) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<!-- Section 1: Device Info -->
<div class="content-card mb-4">
    <div class="card-header-custom">
        <i class="bi bi-laptop text-primary"></i>
        <h6>Geräteinformationen</h6>
    </div>
    <div class="card-body-custom">
        <div class="row g-3">
            <!-- Left: device details -->
            <div class="col-lg-6">
                <h4 class="fw-bold mb-1"><?= $e($detail['deviceName'] ?? '–') ?></h4>
                <p class="text-muted mb-3 small">
                    <?= $e($detail['operatingSystem'] ?? '') ?>
                    <?php if (!empty($detail['osVersion'])): ?>
                        &nbsp;<span class="badge-neutral"><?= $e($detail['osVersion']) ?></span>
                    <?php endif; ?>
                </p>
                <table class="table table-sm mb-0">
                    <tbody>
                        <?php foreach ([
                            'Hersteller'    => $detail['manufacturer'] ?? null,
                            'Modell'        => $detail['model'] ?? null,
                            'Seriennummer'  => $detail['serialNumber'] ?? null,
                            'IMEI'          => $detail['imei'] ?? null,
                            'Benutzer'      => $detail['userDisplayName'] ?? null,
                            'UPN'           => $detail['userPrincipalName'] ?? null,
                            'Registriert'   => $enrolledDate !== '–' ? $enrolledDate : null,
                            'Verwaltung'    => $detail['managementState'] ?? null,
                        ] as $label => $val):
                            if ($val === null || $val === '') continue; ?>
                            <tr>
                                <td class="text-muted small" style="width:140px;"><?= $label ?></td>
                                <td class="small fw-medium"><?= $e($val) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Right: metric boxes -->
            <div class="col-lg-6">
                <div class="row g-2">
                    <!-- Compliance -->
                    <div class="col-sm-6">
                        <div class="metric-card text-center">
                            <div class="metric-label">Compliance</div>
                            <div class="metric-value" style="font-size:1.2rem;">
                                <?php if ($compliance === 'compliant'): ?>
                                    <span class="badge-enabled">Konform</span>
                                <?php elseif ($compliance === 'noncompliant'): ?>
                                    <span class="badge-disabled">Nicht konform</span>
                                <?php else: ?>
                                    <span class="badge-neutral"><?= $e($compliance) ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <!-- Encryption -->
                    <div class="col-sm-6">
                        <div class="metric-card text-center">
                            <div class="metric-label">Verschlüsselung</div>
                            <div class="metric-value" style="font-size:1.2rem;">
                                <?php if ($isEncrypted): ?>
                                    <span class="badge-enabled"><i class="bi bi-lock me-1"></i>Aktiv</span>
                                <?php else: ?>
                                    <span class="badge-warning"><i class="bi bi-unlock me-1"></i>Inaktiv</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <!-- Free Storage -->
                    <div class="col-sm-6">
                        <div class="metric-card text-center">
                            <div class="metric-label">Freier Speicher</div>
                            <div class="metric-value">
                                <?= $e($formatBytes($freeStorage)) ?>
                                <?php if ($totalStorage > 0): ?>
                                    <div class="text-muted" style="font-size:11px;">von <?= $e($formatBytes($totalStorage)) ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <!-- Last Sync -->
                    <div class="col-sm-6">
                        <div class="metric-card text-center">
                            <div class="metric-label">Letzter Sync</div>
                            <div class="metric-value" style="font-size:0.95rem;">
                                <?= $e($relativeSync($lastSync)) ?>
                                <?php if ($lastSync !== ''): ?>
                                    <div class="text-muted" style="font-size:11px;"><?= date('d.m.Y H:i', strtotime($lastSync)) ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Section 2: Actions -->
<div class="content-card mb-4">
    <div class="card-header-custom">
        <i class="bi bi-gear text-secondary"></i>
        <h6>Aktionen</h6>
    </div>
    <div class="card-body-custom">
        <div class="row g-3">
            <!-- Sync (available to all authenticated users) -->
            <div class="col-md-4">
                <div class="p-3 rounded h-100" style="background:#f0f7ff;border:1px solid #cce0ff;">
                    <h6 class="fw-semibold mb-1"><i class="bi bi-arrow-repeat text-primary me-1"></i>Synchronisieren</h6>
                    <p class="text-muted small mb-3">Fordert das Gerät auf, sich sofort mit Intune zu synchronisieren.</p>
                    <form method="post" action="/devices/<?= $e($deviceId) ?>/sync">
                        <?= \App\Core\Csrf::field() ?>
                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="bi bi-arrow-repeat me-1"></i>Sync anfordern
                        </button>
                    </form>
                </div>
            </div>

            <?php if (LocalAuth::isAdmin()): ?>
            <!-- Retire (admin only) -->
            <div class="col-md-4">
                <div class="p-3 rounded h-100" style="background:#fff8f0;border:1px solid #ffd8b0;">
                    <h6 class="fw-semibold mb-1"><i class="bi bi-box-arrow-right text-warning me-1"></i>Retire</h6>
                    <p class="text-muted small mb-3">Entfernt Unternehmensdaten, lässt persönliche Daten intakt. Geeignet für persönliche Geräte (BYOD).</p>
                    <form method="post" action="/devices/<?= $e($deviceId) ?>/retire"
                          onsubmit="return confirm('Gerät wirklich zurücksetzen (Retire)? Unternehmensdaten werden entfernt, persönliche Daten bleiben erhalten.')">
                        <?= \App\Core\Csrf::field() ?>
                        <button type="submit" class="btn btn-sm btn-warning">
                            <i class="bi bi-box-arrow-right me-1"></i>Retire ausführen
                        </button>
                    </form>
                </div>
            </div>

            <!-- Wipe (admin only) -->
            <div class="col-md-4">
                <div class="p-3 rounded h-100" style="background:#fff5f5;border:1px solid #ffd5cc;">
                    <h6 class="fw-semibold mb-1"><i class="bi bi-trash text-danger me-1"></i>Wipe (Werksreset)</h6>
                    <p class="text-muted small mb-3">Löscht alle Daten auf dem Gerät. Nicht rückgängig machbar. Nur für Unternehmensgeräte.</p>
                    <form method="post" action="/devices/<?= $e($deviceId) ?>/wipe"
                          onsubmit="return confirm('ACHTUNG: Alle Daten auf dem Gerät werden unwiderruflich gelöscht. Wirklich fortfahren?')">
                        <?= \App\Core\Csrf::field() ?>
                        <button type="submit" class="btn btn-sm btn-danger">
                            <i class="bi bi-trash me-1"></i>Wipe ausführen
                        </button>
                    </form>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Section 3: BitLocker Keys -->
<div class="content-card">
    <div class="card-header-custom">
        <i class="bi bi-key text-warning"></i>
        <h6>BitLocker-Schlüssel</h6>
    </div>
    <div class="card-body-custom">
        <?php if (empty($bitlockerKeys)): ?>
            <div class="empty-state text-center py-3">
                <i class="bi bi-key fs-2 text-muted mb-2 d-block"></i>
                <p class="text-muted mb-0">Keine BitLocker-Schlüssel gefunden. Entweder ist das Gerät nicht verschlüsselt, oder die Berechtigung <code>InformationProtection.Read.All</code> fehlt.</p>
            </div>
        <?php else: ?>
            <div class="alert alert-warning small mb-3 py-2">
                <i class="bi bi-shield-exclamation me-1"></i>
                <strong>Hinweis:</strong> Schlüssel werden nur protokolliert angezeigt. Stelle sicher, dass der Zugriff nur autorisierten Personen möglich ist.
            </div>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Erstellt am</th>
                            <th>Schlüssel-ID</th>
                            <th>Recovery-Schlüssel</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bitlockerKeys as $bk): ?>
                            <tr>
                                <td class="small text-nowrap">
                                    <?= !empty($bk['createdDateTime']) ? $e(date('d.m.Y H:i', strtotime($bk['createdDateTime']))) : '–' ?>
                                </td>
                                <td class="small text-muted" style="font-family:monospace;"><?= $e($bk['id'] ?? '–') ?></td>
                                <td>
                                    <?php if (!empty($bk['key'])): ?>
                                        <span
                                            class="bitlocker-key"
                                            id="bk-<?= $e($bk['id'] ?? '') ?>"
                                            style="filter:blur(4px);cursor:pointer;font-family:monospace;user-select:none;"
                                            title="Klicken zum Anzeigen"
                                            onclick="revealKey(this, <?= htmlspecialchars(json_encode($bk['key']), ENT_QUOTES) ?>)"
                                        >••••••••••••••••••••••••••••••••••••••••••</span>
                                        <button
                                            type="button"
                                            class="btn btn-xs btn-outline-secondary py-0 px-2 ms-2"
                                            style="font-size:11px;"
                                            onclick="copyKey(<?= htmlspecialchars(json_encode($bk['key']), ENT_QUOTES) ?>, this)"
                                            title="Schlüssel kopieren"
                                        ><i class="bi bi-clipboard"></i></button>
                                    <?php else: ?>
                                        <span class="text-muted small">Schlüsselwert nicht verfügbar</span>
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

<script>
function revealKey(el, key) {
    el.style.filter = 'none';
    el.style.cursor = 'default';
    el.style.userSelect = 'text';
    el.textContent = key;
    el.onclick = null;
    el.title = '';
}

function copyKey(key, btn) {
    navigator.clipboard.writeText(key).then(function () {
        var orig = btn.innerHTML;
        btn.innerHTML = '<i class="bi bi-check"></i>';
        btn.classList.add('btn-outline-success');
        btn.classList.remove('btn-outline-secondary');
        setTimeout(function () {
            btn.innerHTML = orig;
            btn.classList.remove('btn-outline-success');
            btn.classList.add('btn-outline-secondary');
        }, 2000);
    });
}
</script>
