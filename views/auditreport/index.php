<?php
use App\Core\View;

$statusIcon = function (string $st): string {
    return match ($st) {
        'ok'      => '<span style="color:#16a34a;">●</span> erfüllt',
        'warn'    => '<span style="color:#ea580c;">●</span> Achtung',
        'fail'    => '<span style="color:#dc2626;">●</span> nicht erfüllt',
        'unknown' => '<span style="color:#9ca3af;">●</span> unbekannt',
        default   => '<span style="color:#9ca3af;">●</span> ' . htmlspecialchars($st),
    };
};
?>
<div class="content-card">
    <div class="d-flex justify-content-between align-items-start mb-3 no-print">
        <div>
            <h1 class="mb-1"><i class="bi bi-file-earmark-pdf"></i> DSGVO / NIS-2 Audit-Report</h1>
            <p class="text-muted mb-0">Auditfähige Übersicht der Tenant-Konfiguration entlang der wichtigsten Compliance-Anforderungen. Drucken oder als PDF speichern über den Drucker-Knopf oben rechts.</p>
        </div>
        <button onclick="window.print()" class="btn btn-primary"><i class="bi bi-printer"></i> Als PDF speichern</button>
    </div>

    <!-- ── Cover sheet (always visible, also printed) ────────── -->
    <div class="report-cover mb-4 p-4 border rounded" style="background:linear-gradient(135deg,#eff6ff,#dbeafe);">
        <h2 class="mb-1">Tenant-Sicherheitsbericht</h2>
        <p class="text-muted mb-2"><?= View::escape($appName) ?> — Generiert am <?= View::escape($generatedAt) ?> von <?= View::escape($user) ?></p>
        <table class="table table-sm table-borderless mb-0" style="background:transparent;">
            <tr><th style="width:200px;">Tenant-Name</th><td><?= View::escape($tenantInfo['name']) ?></td></tr>
            <tr><th>Tenant-ID</th><td><code><?= View::escape($tenantInfo['id']) ?></code></td></tr>
            <tr><th>Land</th><td><?= View::escape($tenantInfo['country']) ?></td></tr>
            <tr><th>Verifizierte Domains</th><td><?= !empty($tenantInfo['domains']) ? View::escape(implode(', ', $tenantInfo['domains'])) : '—' ?></td></tr>
            <tr><th>Erstellt am</th><td><?= View::escape($tenantInfo['created']) ?></td></tr>
            <tr><th>Compliance-Profil</th><td><?= $profile !== '' ? View::escape($profile) : '<em class="text-muted">nicht gesetzt</em>' ?></td></tr>
        </table>
    </div>

    <!-- ── Berechtigungen ─────────────────────────────────────── -->
    <h3>1. Graph-API-Berechtigungen</h3>
    <?php if (!empty($permissions['error'])): ?>
        <div class="alert alert-warning"><?= View::escape($permissions['error']) ?></div>
    <?php else: ?>
        <table class="table table-bordered">
            <tr><th>Gesamt</th><td><?= (int)$permissions['total'] ?></td></tr>
            <tr><th>Erteilt</th><td style="color:#16a34a;"><?= (int)$permissions['granted'] ?></td></tr>
            <tr><th>Fehlend</th><td style="color:#dc2626;"><?= (int)$permissions['missing'] ?> (davon <?= (int)$permissions['missing_write'] ?> Schreib-Permissions)</td></tr>
        </table>
        <?php if (!empty($permissions['affected_features'])): ?>
            <p class="small">Eingeschränkte Funktionen: <em><?= View::escape(implode(', ', array_slice($permissions['affected_features'], 0, 12))) ?><?= count($permissions['affected_features']) > 12 ? ', …' : '' ?></em></p>
        <?php endif; ?>
    <?php endif; ?>

    <!-- ── Hardening-Liste, gruppiert nach Kategorie ─────────── -->
    <h3 class="mt-4">2. Tenant-Härtung (alle 21 Items)</h3>
    <?php foreach ($hardening as $cat => $items): ?>
        <h5 class="mt-3"><?= View::escape($cat) ?></h5>
        <table class="table table-sm">
            <thead><tr><th>Item</th><th>Status</th><th>Begründung (BSI / NIS-2 / DSGVO)</th></tr></thead>
            <tbody>
            <?php foreach ($items as $i): ?>
                <tr>
                    <td><strong><?= View::escape($i['title']) ?></strong>
                        <div class="text-muted small"><?= View::escape($i['desc']) ?></div></td>
                    <td style="white-space:nowrap;"><?= $statusIcon($i['status']) ?></td>
                    <td class="small"><?= View::escape($i['why'] ?? '') ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endforeach; ?>

    <!-- ── Regulatorische Zuordnung ──────────────────────────── -->
    <h3 class="mt-4 page-break-before">3. Zuordnung zu Compliance-Artikeln</h3>
    <p class="text-muted small">Wie die oben dokumentierten Hardening-Items konkret die rechtlichen Anforderungen abdecken.</p>
    <?php foreach ($articles as $a): ?>
        <div class="border rounded p-3 mb-3">
            <h6 class="mb-1"><span class="badge bg-primary"><?= View::escape($a['art']) ?></span> <?= View::escape($a['name']) ?></h6>
            <p class="small text-muted"><?= View::escape($a['desc']) ?></p>
            <?php if (empty($a['items'])): ?>
                <p class="small text-muted mb-0"><em>Keine konkreten Hardening-Items zugeordnet.</em></p>
            <?php else: ?>
                <table class="table table-sm mb-0">
                    <?php foreach ($a['items'] as $it): ?>
                        <tr>
                            <td><?= View::escape($it['title']) ?></td>
                            <td style="white-space:nowrap; width:160px;"><?= $statusIcon($it['status']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>

    <p class="text-muted small mt-4 text-center">
        — Ende des Berichts —<br>
        Dieser Bericht wurde am <?= View::escape($generatedAt) ?> automatisiert aus der Microsoft Graph API erzeugt und ersetzt keine externe Auditierung.
    </p>
</div>

<style>
@media print {
    .no-print { display: none !important; }
    .report-cover { background: #fff !important; border: 2px solid #0078d4 !important; }
    .badge { border: 1px solid #444; color: #000 !important; background: #fff !important; }
    .page-break-before { page-break-before: always; }
    h3 { margin-top: 24px; }
}
</style>
