<h5 class="card-title mb-4">Schritt 3 — Azure AD App-Registrierung</h5>
<p class="text-muted small mb-3">
    Registriere eine App in <strong>Azure AD</strong> und trage die Zugangsdaten ein.
    <a href="https://portal.azure.com/#view/Microsoft_AAD_RegisteredApps/ApplicationsListBlade" target="_blank" class="small">→ Azure Portal öffnen</a>
</p>

<?php if (!empty($tenantName)): ?>
    <div class="alert alert-success">
        ✓ Verbindung erfolgreich — Tenant: <strong><?= htmlspecialchars($tenantName) ?></strong>
    </div>
<?php endif; ?>

<div class="alert alert-secondary small mb-4">
    <strong>Erforderliche App-Berechtigungen (Application):</strong><br>
    User.Read.All · Directory.Read.All · Sites.Read.All · Files.Read.All · Reports.Read.All ·
    AuditLog.Read.All · Group.Read.All · DeviceManagementManagedDevices.Read.All ·
    Policy.Read.All · IdentityRiskyUser.Read.All
    <br><em>→ Anschließend Admin-Zustimmung erteilen</em>
</div>

<form method="post" action="?step=3">
    <div class="mb-3">
        <label class="form-label">Tenant ID (Directory ID)</label>
        <input type="text" name="tenant_id" class="form-control font-monospace"
               placeholder="xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx" required
               value="<?= htmlspecialchars($_SESSION['install_azure']['tenantId'] ?? '') ?>">
    </div>
    <div class="mb-3">
        <label class="form-label">Application (Client) ID</label>
        <input type="text" name="client_id" class="form-control font-monospace"
               placeholder="xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx" required
               value="<?= htmlspecialchars($_SESSION['install_azure']['clientId'] ?? '') ?>">
    </div>
    <div class="mb-3">
        <label class="form-label">Client Secret</label>
        <input type="password" name="client_secret" class="form-control" required>
        <div class="form-text">Alle Zugangsdaten werden AES-256-GCM verschlüsselt gespeichert.</div>
    </div>
    <div class="d-flex justify-content-between mt-4">
        <a href="?step=2" class="btn btn-outline-secondary">← Zurück</a>
        <button type="submit" class="btn btn-primary px-4">Verbindung testen &amp; weiter →</button>
    </div>
</form>
