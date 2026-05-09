<h5 class="card-title mb-3">Schritt 3 — Azure AD App-Registrierung</h5>

<?php if (!empty($tenantName)): ?>
    <div class="alert alert-success d-flex align-items-center gap-2">
        <i class="bi bi-check-circle-fill"></i>
        Verbindung erfolgreich — Tenant: <strong><?= htmlspecialchars($tenantName) ?></strong>
    </div>
<?php endif; ?>

<!-- Collapsible Permissions Guide -->
<div class="accordion mb-4" id="permAccordion">
    <div class="accordion-item border-0 bg-light rounded">
        <h2 class="accordion-header">
            <button class="accordion-button collapsed bg-light rounded fw-semibold" style="font-size:14px;"
                    type="button" data-bs-toggle="collapse" data-bs-target="#permGuide">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-key me-2 text-primary" viewBox="0 0 16 16"><path d="M0 8a4 4 0 0 1 7.465-2H14a.5.5 0 0 1 .354.146l1.5 1.5a.5.5 0 0 1 0 .708l-1.5 1.5a.5.5 0 0 1-.708 0L13 9.207l-.646.647a.5.5 0 0 1-.708 0L11 9.207l-.646.647a.5.5 0 0 1-.354.146H7.465A4 4 0 0 1 0 8m4-3a3 3 0 1 0 0 6 3 3 0 0 0 0-6"/></svg>
                Anleitung: Azure AD App anlegen &amp; Berechtigungen setzen
                <span class="badge bg-primary ms-2" style="font-size:11px;">Klicken zum öffnen</span>
            </button>
        </h2>
        <div id="permGuide" class="accordion-collapse collapse">
            <div class="accordion-body" style="font-size:13px;">

                <ol class="mb-4" style="padding-left:20px;line-height:1.9;">
                    <li>Öffne das <a href="https://entra.microsoft.com" target="_blank"><strong>Microsoft Entra Admin Center</strong></a></li>
                    <li>Navigiere zu <strong>Anwendungen → App-Registrierungen → Neue Registrierung</strong></li>
                    <li>Name: z.B. <code>M365 Tenant Tool</code> · Kontotyp: <em>Nur dieser Verzeichnisinstanz</em></li>
                    <li>Klicke <strong>Registrieren</strong> und kopiere <strong>Verzeichnis-ID (Tenant ID)</strong> und <strong>Anwendungs-ID (Client ID)</strong></li>
                    <li>Navigiere zu <strong>Zertifikate &amp; Geheimnisse → Neuer geheimer Clientschlüssel</strong></li>
                    <li>Kopiere den generierten <strong>Client Secret</strong> (wird nur einmal angezeigt!)</li>
                    <li>Navigiere zu <strong>API-Berechtigungen → Berechtigung hinzufügen → Microsoft Graph → Anwendungsberechtigungen</strong></li>
                    <li>Füge alle unten aufgeführten Berechtigungen hinzu</li>
                    <li>Klicke <strong>Administratorzustimmung erteilen für [Tenant-Name]</strong></li>
                </ol>

                <p class="fw-semibold mb-2">Erforderliche Berechtigungen (Application Permissions):</p>
                <table class="table table-sm table-bordered mb-3" style="font-size:12px;">
                    <thead class="table-light">
                        <tr><th>Berechtigung</th><th>Zweck</th><th>Typ</th></tr>
                    </thead>
                    <tbody>
                        <tr class="table-primary"><td colspan="3" class="fw-semibold">Kern — Verzeichnis & Benutzer</td></tr>
                        <tr><td><code>User.Read.All</code></td><td>Benutzer, MFA-Status, Anmeldungen</td><td><span class="badge bg-danger">Erforderlich</span></td></tr>
                        <tr><td><code>UserAuthenticationMethod.ReadWrite.All</code></td><td>MFA-Methoden zurücksetzen</td><td><span class="badge bg-danger">Erforderlich</span></td></tr>
                        <tr><td><code>Directory.Read.All</code></td><td>Verzeichnisdaten, Gastbenutzer</td><td><span class="badge bg-danger">Erforderlich</span></td></tr>
                        <tr><td><code>AuditLog.Read.All</code></td><td>Anmeldeprotokolle, Audit-Log</td><td><span class="badge bg-danger">Erforderlich</span></td></tr>

                        <tr class="table-primary"><td colspan="3" class="fw-semibold">Gruppen &amp; Teams</td></tr>
                        <tr><td><code>Group.Read.All</code></td><td>Gruppen &amp; Teams lesen</td><td><span class="badge bg-danger">Erforderlich</span></td></tr>
                        <tr><td><code>GroupMember.ReadWrite.All</code></td><td>Mitglieder hinzufügen/entfernen</td><td><span class="badge bg-danger">Erforderlich</span></td></tr>
                        <tr><td><code>TeamMember.Read.All</code></td><td>Teams-Mitglieder lesen</td><td><span class="badge bg-warning text-dark">Empfohlen</span></td></tr>

                        <tr class="table-primary"><td colspan="3" class="fw-semibold">SharePoint, OneDrive &amp; Freigaben</td></tr>
                        <tr><td><code>Sites.Read.All</code></td><td>SharePoint-Sites lesen</td><td><span class="badge bg-danger">Erforderlich</span></td></tr>
                        <tr><td><code>Files.ReadWrite.All</code></td><td>Freigaben lesen &amp; widerrufen</td><td><span class="badge bg-danger">Erforderlich</span></td></tr>
                        <tr><td><code>SharePoint.ReadWrite.All</code></td><td>Globale Freigaberichtlinien ändern</td><td><span class="badge bg-warning text-dark">Empfohlen</span></td></tr>

                        <tr class="table-primary"><td colspan="3" class="fw-semibold">Lizenzen &amp; Berichte</td></tr>
                        <tr><td><code>User.ReadWrite.All</code></td><td>Lizenzen zuweisen/entfernen, Benutzer aktivieren</td><td><span class="badge bg-danger">Erforderlich</span></td></tr>
                        <tr><td><code>Reports.Read.All</code></td><td>Nutzungsberichte (OneDrive, SharePoint)</td><td><span class="badge bg-danger">Erforderlich</span></td></tr>

                        <tr class="table-primary"><td colspan="3" class="fw-semibold">Geräte &amp; Sicherheit</td></tr>
                        <tr><td><code>DeviceManagementManagedDevices.Read.All</code></td><td>Intune-Geräteverwaltung</td><td><span class="badge bg-warning text-dark">Empfohlen</span></td></tr>
                        <tr><td><code>IdentityRiskyUser.Read.All</code></td><td>Risikobenutzer-Erkennung</td><td><span class="badge bg-warning text-dark">Empfohlen</span></td></tr>
                        <tr><td><code>Policy.Read.All</code></td><td>Conditional Access Policies lesen</td><td><span class="badge bg-warning text-dark">Empfohlen</span></td></tr>
                        <tr><td><code>Policy.ReadWrite.CrossTenantAccess</code></td><td>Mandantenübergreifende Richtlinien lesen/schreiben</td><td><span class="badge bg-secondary">Optional</span></td></tr>
                    </tbody>
                </table>

                <div class="alert alert-warning mb-0 p-2" style="font-size:12px;">
                    <strong>⚠ Wichtig:</strong> Nach dem Hinzufügen aller Berechtigungen muss ein <strong>Global Administrator</strong>
                    die Zustimmung erteilen (<em>Administratorzustimmung erteilen</em>-Button).
                    Ohne diese Zustimmung schlagen alle API-Calls fehl.
                </div>
            </div>
        </div>
    </div>
</div>

<form method="post" action="?step=3">
    <div class="mb-3">
        <label class="form-label fw-semibold">Tenant ID (Verzeichnis-ID)</label>
        <input type="text" name="tenant_id" class="form-control font-monospace"
               placeholder="xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx" required
               value="<?= htmlspecialchars($_SESSION['install_azure']['tenantId'] ?? '') ?>">
        <div class="form-text">Zu finden unter: Entra Admin Center → Identität → Übersicht</div>
    </div>
    <div class="mb-3">
        <label class="form-label fw-semibold">Application (Client) ID</label>
        <input type="text" name="client_id" class="form-control font-monospace"
               placeholder="xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx" required
               value="<?= htmlspecialchars($_SESSION['install_azure']['clientId'] ?? '') ?>">
    </div>
    <div class="mb-3">
        <label class="form-label fw-semibold">Client Secret</label>
        <input type="password" name="client_secret" class="form-control" required
               autocomplete="new-password">
        <div class="form-text">
            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="#198754" class="bi bi-shield-lock me-1" viewBox="0 0 16 16"><path d="M5.338 1.59a61 61 0 0 0-2.837.856.48.48 0 0 0-.328.39c-.554 4.157.726 7.19 2.253 9.188a10.7 10.7 0 0 0 2.287 2.233c.346.244.652.42.893.533q.18.085.293.118a1 1 0 0 0 .101.025 1 1 0 0 0 .1-.025q.114-.034.294-.118c.24-.113.547-.29.893-.533a10.7 10.7 0 0 0 2.287-2.233c1.527-1.997 2.807-5.031 2.253-9.188a.48.48 0 0 0-.328-.39c-.651-.213-1.75-.56-2.837-.855C9.552 1.29 8.531 1.067 8 1.067c-.53 0-1.552.223-2.662.524z"/><path d="M11.354 6.354a.5.5 0 0 1 0 .707l-5 5a.5.5 0 0 1-.707-.707l1.5-1.5a.5.5 0 0 1 .707.707L7.207 11l4.146-4.146a.5.5 0 0 1 .707 0z"/></svg>
            Wird AES-256-GCM verschlüsselt gespeichert.
        </div>
    </div>
    <div class="d-flex justify-content-between mt-4">
        <a href="?step=2" class="btn btn-outline-secondary">← Zurück</a>
        <button type="submit" class="btn btn-primary px-4">Verbindung testen &amp; weiter →</button>
    </div>
</form>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
