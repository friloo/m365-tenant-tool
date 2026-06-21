<?php
use App\Core\Ui;
$e = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');

echo Ui::externalCard(
    t('Aufbewahrungs-Richtlinien &amp; -Labels'),
    t('Retention-Policies und -Labels (Aufbewahrungs-/Löschfristen für E-Mails, Dokumente, Teams-Chats) '
    . 'lassen sich <strong>nicht über die Microsoft Graph API verwalten</strong>. Verwaltung im '
    . '<strong>Microsoft-Purview-Portal</strong> oder per <strong>Security-&amp;-Compliance-PowerShell</strong>:'),
    [
        ['https://purview.microsoft.com/informationprotection/recordsmanagement', t('Aufbewahrung im Purview-Portal')],
        ['/ediscovery', t('eDiscovery-Fälle im Tool')],
    ],
    [
        ["Connect-IPPSSession -UserPrincipalName admin@deine-domain.de", t('Mit Security & Compliance PowerShell verbinden')],
        ["Get-RetentionCompliancePolicy | Format-Table Name,Enabled,Mode", t('Vorhandene Aufbewahrungs-Richtlinien auflisten')],
        ["New-RetentionCompliancePolicy -Name \"Aufbewahrung 7 Jahre\" `\n  -ExchangeLocation All -SharePointLocation All\n\nNew-RetentionComplianceRule -Name \"7y\" `\n  -Policy \"Aufbewahrung 7 Jahre\" -RetentionDuration 2555 `\n  -RetentionComplianceAction Keep", t('Beispiel: 7-Jahres-Aufbewahrung anlegen')],
    ],
    'hourglass-split'
);
?>

<p class="small text-muted mb-0">
  <?= t('Welche Compliance-Bereiche über Graph möglich sind, zeigt :doc.', ['doc' => '<code>docs/CIS-COVERAGE.md</code>']) ?>
</p>
