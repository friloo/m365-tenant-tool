<?php
$e = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
?>

<div class="content-card mb-4">
  <div class="card-body p-4">
    <div class="d-flex align-items-start gap-3">
      <i class="bi bi-shield-shaded fs-2 text-primary"></i>
      <div>
        <h5 class="mb-2">Data-Loss-Prevention-Richtlinien</h5>
        <p class="text-muted mb-3">
          DLP-Richtlinien (Regeln gegen den Abfluss sensibler Daten in Exchange, SharePoint,
          OneDrive, Teams und Endpunkten) lassen sich <strong>nicht über die Microsoft Graph API
          verwalten</strong> — weder lesend noch schreibend. Es gibt dafür keinen v1.0- oder
          beta-Endpunkt. Die Verwaltung erfolgt ausschließlich im
          <strong>Microsoft-Purview-Portal</strong> bzw. per
          <strong>Security-&amp;-Compliance-PowerShell</strong>.
        </p>
        <div class="d-flex flex-wrap gap-2">
          <a href="https://purview.microsoft.com/datalossprevention/policies" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary">
            <i class="bi bi-box-arrow-up-right me-1"></i>DLP-Richtlinien im Purview-Portal
          </a>
          <a href="/dlpincidents" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-activity me-1"></i>DLP-Vorfälle ansehen (im Tool)
          </a>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="alert alert-light border d-flex align-items-start gap-2">
  <i class="bi bi-lightbulb mt-1"></i>
  <div class="small">
    Was das Tool stattdessen bietet: <strong>DLP-Vorfälle</strong> (aus dem Audit-Log),
    <strong>Sensitivity Labels</strong> (Anzeige) und die <strong>Coverage-Matrix</strong>
    (<code>docs/CIS-COVERAGE.md</code>), die zeigt, welche Compliance-Einstellungen über Graph
    möglich sind und welche das Purview-Portal/PowerShell erfordern.
  </div>
</div>

<p class="small text-muted mb-0">
  Per PowerShell z. B.: <code>Connect-IPPSSession</code> →
  <code>Get-DlpCompliancePolicy</code> / <code>New-DlpCompliancePolicy</code>.
</p>
