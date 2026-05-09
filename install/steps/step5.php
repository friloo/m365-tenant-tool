<h5 class="card-title mb-4">Schritt 5 — Zusammenfassung</h5>

<?php $db = $_SESSION['install_db'] ?? []; $azure = $_SESSION['install_azure'] ?? []; $settings = $_SESSION['install_settings'] ?? []; ?>

<div class="table-responsive mb-4">
    <table class="table table-sm table-bordered">
        <tbody>
            <tr class="table-light"><td colspan="2"><strong>Datenbank</strong></td></tr>
            <tr><td>Host</td><td><?= htmlspecialchars($db['host'] ?? '') ?>:<?= (int)($db['port'] ?? 3306) ?></td></tr>
            <tr><td>Datenbank</td><td><?= htmlspecialchars($db['name'] ?? '') ?></td></tr>
            <tr><td>Benutzer</td><td><?= htmlspecialchars($db['user'] ?? '') ?></td></tr>
            <tr class="table-light"><td colspan="2"><strong>Azure AD</strong></td></tr>
            <tr><td>Tenant ID</td><td class="font-monospace small"><?= htmlspecialchars($azure['tenantId'] ?? '') ?></td></tr>
            <tr><td>Client ID</td><td class="font-monospace small"><?= htmlspecialchars($azure['clientId'] ?? '') ?></td></tr>
            <tr><td>Client Secret</td><td>●●●●●●●●</td></tr>
            <tr class="table-light"><td colspan="2"><strong>Einstellungen</strong></td></tr>
            <tr><td>App-Name</td><td><?= htmlspecialchars($settings['appName'] ?? '') ?></td></tr>
            <tr><td>Cache-TTL</td><td><?= (int)($settings['cacheTtl'] ?? 15) ?> Minuten</td></tr>
            <tr><td>Zeitzone</td><td><?= htmlspecialchars($settings['timezone'] ?? '') ?></td></tr>
        </tbody>
    </table>
</div>

<div class="alert alert-warning small">
    <strong>Sicherheitshinweis:</strong> Nach Abschluss wird eine Datei <code>storage/app.key</code>
    erstellt. Diese enthält den Verschlüsselungsschlüssel — sichere sie und füge <code>storage/</code>
    zu deiner Apache-Konfiguration als nicht-öffentlich hinzu.
</div>

<form method="post" action="?step=5">
    <div class="d-flex justify-content-between mt-4">
        <a href="?step=4" class="btn btn-outline-secondary">← Zurück</a>
        <button type="submit" class="btn btn-success px-4">✓ Installation abschließen</button>
    </div>
</form>
