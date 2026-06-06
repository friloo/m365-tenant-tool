<?php
$e = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
?>

<div class="content-card mb-4">
  <div class="card-body p-4">
    <div class="d-flex align-items-start gap-3">
      <i class="bi bi-hourglass-split fs-2 text-primary"></i>
      <div>
        <h5 class="mb-2">Aufbewahrungs-Richtlinien &amp; -Labels</h5>
        <p class="text-muted mb-3">
          Retention-Policies und Retention-Labels (Aufbewahrungs- und Löschfristen für E-Mails,
          Dokumente, Teams-Chats etc.) lassen sich <strong>nicht über die Microsoft Graph API
          verwalten</strong>. Es existiert kein v1.0-Endpunkt zum Auflisten/Schreiben
          (Retention-Labels nur lesend in beta). Verwaltung ausschließlich im
          <strong>Microsoft-Purview-Portal</strong> bzw. per
          <strong>Security-&amp;-Compliance-PowerShell</strong>.
        </p>
        <div class="d-flex flex-wrap gap-2">
          <a href="https://purview.microsoft.com/informationprotection/recordsmanagement" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary">
            <i class="bi bi-box-arrow-up-right me-1"></i>Aufbewahrung im Purview-Portal
          </a>
          <a href="/ediscovery" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-archive me-1"></i>eDiscovery-Fälle (im Tool)
          </a>
        </div>
      </div>
    </div>
  </div>
</div>

<p class="small text-muted mb-0">
  Per PowerShell z. B.: <code>Connect-IPPSSession</code> →
  <code>Get-RetentionCompliancePolicy</code> / <code>New-RetentionCompliancePolicy</code>.
  Welche Compliance-Bereiche über Graph möglich sind, zeigt <code>docs/CIS-COVERAGE.md</code>.
</p>
