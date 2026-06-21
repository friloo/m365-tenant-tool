<?php use App\Core\View; $e = fn($v) => View::escape($v);
function fmtBytesOd(int $bytes): string {
    if ($bytes <= 0) return '0 B';
    $k = 1024; $sizes = ['B','KB','MB','GB','TB'];
    $i = min(floor(log($bytes, $k)), 4);
    return round($bytes / pow($k, $i), 1) . ' ' . $sizes[$i];
}
$total = count($list);
?>

<!-- Sub-nav tabs -->
<div class="module-tabs mb-4">
    <a href="/onedrive" class="module-tab">
        <i class="bi bi-cloud me-1"></i> <?= te('Speicher-Übersicht') ?>
    </a>
    <a href="/onedrive/personal" class="module-tab active">
        <i class="bi bi-person-circle me-1"></i> <?= te('Persönliche Laufwerke') ?>
    </a>
</div>

<?php if (!empty($flash)): ?>
    <div class="alert alert-success mb-3"><i class="bi bi-check-circle me-2"></i><?= $e($flash) ?></div>
<?php endif; ?>
<?php if (!empty($error)): ?>
    <div class="alert alert-danger mb-3"><i class="bi bi-exclamation-triangle me-2"></i><?= $e($error) ?></div>
<?php endif; ?>

<?php if (!($reportMode ?? true)): ?>
<div class="alert mb-3" style="background:#fefce8;border:1px solid #fde047;border-radius:8px;padding:12px 16px;font-size:13px;">
    <i class="bi bi-exclamation-triangle me-2" style="color:#ca8a04;"></i>
    <strong><?= te('Eingeschränkte Ansicht:') ?></strong> <?= te('Der OneDrive-Nutzungsbericht (') ?><code>Reports.Read.All</code><?= te(') ist nicht verfügbar. Die Daten werden per Einzelabfrage ermittelt (erste 150 Benutzer).') ?>
    <?= te('Mögliche Ursachen: Berechtigung fehlt, Berichtsverschleierung aktiv (M365 Admin Center →') ?>
    <?= te('Einstellungen → Dienste → Berichte → „Anonymisierte Benutzerberichte" deaktivieren),') ?>
    <?= te('oder') ?> <a href="?refresh=1"><?= te('Cache aktualisieren') ?></a>.
</div>
<?php endif; ?>

<!-- Stats -->
<div class="row g-3 mb-4">
    <div class="col-sm-4">
        <div class="metric-card">
            <div class="metric-label"><?= te('Gesamt Benutzer') ?></div>
            <div class="metric-value"><?= number_format($total) ?></div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="metric-card">
            <div class="metric-label"><?= te('Mit persönlichem OneDrive') ?></div>
            <div class="metric-value" style="color:#16a34a;"><?= number_format($provisioned) ?></div>
            <div class="metric-sub"><?= $total > 0 ? round(($provisioned/$total)*100) : 0 ?><?= te('% der Benutzer') ?></div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="metric-card">
            <div class="metric-label"><?= te('Ohne OneDrive') ?></div>
            <div class="metric-value" style="color:#6b7280;"><?= number_format($notProvisioned) ?></div>
            <div class="metric-sub"><?= te('kein Laufwerk provisioniert') ?></div>
        </div>
    </div>
</div>

<!-- Info box about provisioning groups -->
<div class="alert mb-3" style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;padding:12px 16px;display:flex;align-items:flex-start;gap:10px;">
    <i class="bi bi-info-circle" style="color:#3b82f6;font-size:16px;margin-top:2px;flex-shrink:0;"></i>
    <div style="font-size:13px;color:#1e40af;">
        <strong><?= te('Welche Gruppen dürfen OneDrives provisionieren?') ?></strong> <?= te('Diese Einstellung wird im') ?>
        <a href="https://admin.microsoft.com/Adminportal/Home#/SharePoint" target="_blank" rel="noopener" style="color:#2563eb;">SharePoint Admin Center</a>
        <?= te('unter') ?> <em><?= te('Einstellungen → OneDrive') ?></em> <?= te('verwaltet.') ?>
        <?= te('Das Tool kann die Provisionierung einzelner Benutzer direkt über die Microsoft Graph API auslösen.') ?>
    </div>
</div>

<div class="content-card">
    <div class="table-toolbar">
        <input type="text" id="odpSearch" class="search-box" placeholder="<?= te('Benutzer suchen…') ?>">
        <select id="odFilter" class="form-select form-select-sm ms-2" style="max-width:200px;" onchange="filterOdp()">
            <option value=""><?= te('Alle Benutzer') ?></option>
            <option value="provisioned"><?= te('Mit OneDrive') ?></option>
            <option value="none"><?= te('Ohne OneDrive') ?></option>
            <option value="active"><?= te('Nur aktive Konten') ?></option>
        </select>
        <a href="?refresh=1" class="btn btn-sm btn-outline-secondary ms-2">
            <i class="bi bi-arrow-clockwise"></i> <?= te('Aktualisieren') ?>
        </a>
    </div>

    <div class="table-responsive">
        <table class="data-table" id="odpTable">
            <thead>
                <tr>
                    <th><?= te('Benutzer') ?></th>
                    <th>UPN</th>
                    <th>OneDrive</th>
                    <th><?= te('Belegt') ?></th>
                    <th><?= te('Dateien') ?></th>
                    <th><?= te('Letzte Aktivität') ?></th>
                    <th style="width:160px;"><?= te('Aktionen') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($list as $u): ?>
                    <tr data-has="<?= $u['hasOneDrive'] ? '1' : '0' ?>"
                        data-enabled="<?= $u['accountEnabled'] ? '1' : '0' ?>">
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div style="width:32px;height:32px;border-radius:50%;background:#e3f0fb;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:600;color:#0078d4;flex-shrink:0;">
                                    <?= strtoupper(substr($u['displayName'], 0, 1)) ?>
                                </div>
                                <span style="font-weight:500;"><?= $e($u['displayName']) ?></span>
                            </div>
                        </td>
                        <td style="font-size:12px;color:#6b7280;"><?= $e($u['upn']) ?></td>
                        <td>
                            <?php if ($u['hasOneDrive']): ?>
                                <?php if ($u['siteUrl']): ?>
                                    <a href="<?= $e($u['siteUrl']) ?>" target="_blank" rel="noopener"
                                       class="badge-enabled text-decoration-none" style="display:inline-flex;align-items:center;gap:4px;">
                                        <i class="bi bi-check-circle"></i> <?= te('Aktiv') ?>
                                    </a>
                                <?php else: ?>
                                    <span class="badge-enabled"><i class="bi bi-check-circle"></i> <?= te('Aktiv') ?></span>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="badge-neutral">–</span>
                            <?php endif; ?>
                        </td>
                        <td style="font-size:13px;">
                            <?= $u['hasOneDrive'] ? fmtBytesOd($u['storageUsed']) : '–' ?>
                        </td>
                        <td style="font-size:13px;">
                            <?= $u['hasOneDrive'] ? number_format($u['fileCount']) : '–' ?>
                        </td>
                        <td style="font-size:12px;color:#6b7280;">
                            <?php if ($u['lastActivity']): ?>
                                <?= date('d.m.Y', strtotime($u['lastActivity'])) ?>
                            <?php else: ?>
                                <span class="text-muted">–</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (!$u['hasOneDrive']): ?>
                                <form method="post" action="/onedrive/provision/<?= $e($u['id']) ?>" style="display:inline;">
                                    <?= \App\Core\Csrf::field() ?>
                                    <button type="submit" class="btn btn-sm btn-outline-primary"
                                            title="<?= te('OneDrive provisionieren') ?>"
                                            onclick="return confirm(<?= htmlspecialchars(json_encode(te('OneDrive für :name provisionieren?', ['name' => $u['displayName']]), JSON_UNESCAPED_UNICODE), ENT_QUOTES) ?>)">
                                        <i class="bi bi-cloud-plus me-1"></i> <?= te('Provisionieren') ?>
                                    </button>
                                </form>
                            <?php else: ?>
                                <?php if (!empty($u['siteUrl'])): ?>
                                    <button type="button" class="btn btn-sm btn-outline-danger"
                                            title="<?= te('Kopiert die OneDrive-URL und öffnet das SharePoint Admin Center. Dort unter „Aktive Sites" die URL in die Suche einfügen — OneDrives sind sonst ausgeblendet — und die Site löschen.') ?>"
                                            onclick='odRemove(<?= htmlspecialchars(json_encode($u["siteUrl"]), ENT_QUOTES) ?>)'>
                                        <i class="bi bi-cloud-minus me-1"></i> <?= te('OneDrive löschen…') ?>
                                    </button>
                                <?php else: ?>
                                    <a href="https://admin.microsoft.com/sharepoint?page=siteManagement&modern=true"
                                       target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary"
                                       title="<?= te('OneDrive-Sites können nur im SharePoint Admin Center gelöscht werden (unter „Aktive Sites" nach der OneDrive-URL suchen).') ?>">
                                        <i class="bi bi-box-arrow-up-right me-1"></i> <?= te('Im SP-Admin entfernen') ?>
                                    </a>
                                <?php endif; ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($list)): ?>
                    <tr><td colspan="7" class="text-center text-muted py-4"><?= te('Keine Benutzer gefunden') ?></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
initTableSearch('odpSearch', 'odpTable');
initPagination('odpTable', 25);

function filterOdp() {
    const val = document.getElementById('odFilter').value;
    document.querySelectorAll('#odpTable tbody tr').forEach(r => {
        let show = true;
        const d = r.dataset;
        if (val === 'provisioned') show = d.has === '1';
        if (val === 'none')        show = d.has === '0';
        if (val === 'active')      show = d.enabled === '1';
        r.dataset.filterMatch = show ? '1' : '0';
    });
    document.getElementById('odpTable').dispatchEvent(new CustomEvent('hs:filter'));
}

// OneDrive sites can't be deleted via Graph. Copy the OneDrive URL to the
// clipboard and open the SharePoint Admin Center → Active sites, where the
// admin pastes the URL into the search (OneDrives are hidden otherwise) and
// deletes the site.
function odRemove(siteUrl) {
    const adminUrl = 'https://admin.microsoft.com/sharepoint?page=siteManagement&modern=true';
    const go = () => {
        alert(<?= json_encode(t('OneDrive-URL wurde in die Zwischenablage kopiert:'), JSON_UNESCAPED_UNICODE) ?> + '\n\n' + siteUrl +
              '\n\n' + <?= json_encode(t('Im gleich geöffneten SharePoint Admin Center unter „Aktive Sites" die URL in das Suchfeld einfügen, die Site auswählen und löschen.'), JSON_UNESCAPED_UNICODE) ?>);
        window.open(adminUrl, '_blank', 'noopener');
    };
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(siteUrl).then(go, go);
    } else {
        go();
    }
}

</script>
