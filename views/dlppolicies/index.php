<?php
use App\Core\Ui;
$e = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');

echo Ui::externalCard(
    t('Data-Loss-Prevention-Richtlinien'),
    t('DLP-Richtlinien (Regeln gegen den Abfluss sensibler Daten in Exchange, SharePoint, OneDrive, '
    . 'Teams und Endpunkten) lassen sich <strong>nicht über die Microsoft Graph API verwalten</strong> — '
    . 'weder lesend noch schreibend. Verwaltung im <strong>Microsoft-Purview-Portal</strong> oder per '
    . '<strong>Security-&amp;-Compliance-PowerShell</strong>. Nutze die Deep-Links oder kopiere die Befehle:'),
    [
        ['https://purview.microsoft.com/datalossprevention/policies', t('DLP-Richtlinien im Purview-Portal')],
        ['/dlpincidents', t('DLP-Vorfälle im Tool')],
    ],
    [
        ["Connect-IPPSSession -UserPrincipalName admin@deine-domain.de", t('Mit Security & Compliance PowerShell verbinden')],
        ["Get-DlpCompliancePolicy | Format-Table Name,Mode,Enabled", t('Vorhandene DLP-Richtlinien auflisten')],
        ["New-DlpCompliancePolicy -Name \"DSGVO - Kreditkarten\" `\n  -ExchangeLocation All -SharePointLocation All -OneDriveLocation All `\n  -Mode Enable\n\nNew-DlpComplianceRule -Name \"CreditCard\" -Policy \"DSGVO - Kreditkarten\" `\n  -ContentContainsSensitiveInformation @{Name=\"Credit Card Number\";minCount=\"1\"} `\n  -BlockAccess \$true", t('Beispiel: DLP-Richtlinie für Kreditkartennummern anlegen')],
    ],
    'shield-shaded'
);
?>

<div class="alert alert-light border d-flex align-items-start gap-2">
  <i class="bi bi-lightbulb mt-1"></i>
  <div class="small">
    <?= t('Was das Tool selbst bietet: <strong>DLP-Vorfälle</strong> (aus dem Audit-Log) und '
    . '<strong>Sensitivity Labels</strong> (Anzeige). Vollständige Gegenüberstellung Tool ↔ Portal:') ?>
    <code>docs/CIS-COVERAGE.md</code>.
  </div>
</div>
