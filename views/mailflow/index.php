<?php use App\Core\View; $e = fn($v) => View::escape($v); ?>

<?php
// Helper: map Graph service status to display properties
$statusMeta = function (string $status): array {
    return match ($status) {
        'serviceOperational', 'serviceDegradationMitigated'
            => ['label' => 'Betrieb',              'color' => '#16a34a', 'badgeClass' => 'badge-enabled'],
        'serviceDegradation'
            => ['label' => 'Beeinträchtigt',       'color' => '#d97706', 'badgeClass' => 'badge-warning'],
        'serviceInterruption'
            => ['label' => 'Unterbrochen',         'color' => '#dc2626', 'badgeClass' => 'badge-danger'],
        'restoringService'
            => ['label' => 'Wird wiederhergestellt', 'color' => '#2563eb', 'badgeClass' => 'badge-info'],
        default
            => ['label' => $status ?: 'Unbekannt', 'color' => '#9ca3af', 'badgeClass' => 'badge-neutral'],
    };
};

// Helper: map alert severity to badge class
$severityBadge = fn(string $s): string => match (strtolower($s)) {
    'high'          => 'badge-danger',
    'medium'        => 'badge-warning',
    'low'           => 'badge-info',
    'informational' => 'badge-neutral',
    default         => 'badge-secondary',
};

$severityLabel = fn(string $s): string => match (strtolower($s)) {
    'high'          => 'Kritisch',
    'medium'        => 'Mittel',
    'low'           => 'Niedrig',
    'informational' => 'Info',
    default         => $s,
};

// Derive Exchange Online status from health overview
$exoStatus      = '';
$exoStatusMeta  = ['label' => 'Unbekannt', 'color' => '#9ca3af', 'badgeClass' => 'badge-neutral'];
$exoLastChecked = '';

if (!empty($healthOverview)) {
    $exo = $healthOverview[0];
    $exoStatus     = $exo['status'] ?? '';
    $exoStatusMeta = $statusMeta($exoStatus);
}

$issueCount   = count($activeIssues);
$alertCount   = count($defenderAlerts);
$isOperational = ($exoStatus === 'serviceOperational' || $exoStatus === 'serviceDegradationMitigated');
?>

<!-- Page subtitle -->
<p class="text-muted mb-4" style="font-size:14px;">
    Exchange Online Mailflow-Übersicht und Sicherheitseinstellungen
</p>

<!-- Info banner -->
<div class="alert alert-info d-flex gap-2 mb-4" role="alert">
    <i class="bi bi-info-circle flex-shrink-0 mt-1"></i>
    <span>
        Transportregeln und Anti-Spam-Richtlinien werden direkt in Exchange Online und Microsoft Defender
        verwaltet. Diese Seite zeigt den aktuellen Status und verlinkt zu den entsprechenden Admin-Bereichen.
    </span>
</div>

<!-- Metric Cards -->
<div class="row g-3 mb-4">

    <!-- Exchange Online Status -->
    <div class="col-sm-6 col-lg-3">
        <div class="metric-card">
            <div class="metric-label">Exchange Online Status</div>
            <div class="metric-value" style="font-size:1.4rem;">
                <?php if (!empty($exoStatusMeta)): ?>
                    <span class="<?= $e($exoStatusMeta['badgeClass']) ?>" style="font-size:13px;">
                        <?= $e($exoStatusMeta['label']) ?>
                    </span>
                <?php else: ?>
                    <span class="badge-neutral" style="font-size:13px;">Unbekannt</span>
                <?php endif; ?>
            </div>
            <div class="metric-sub">
                <?= $isOperational ? 'Kein bekanntes Problem' : ($exoStatus ? 'Prüfen Sie die Details' : 'Keine Daten') ?>
            </div>
        </div>
    </div>

    <!-- Active Issues -->
    <div class="col-sm-6 col-lg-3">
        <div class="metric-card">
            <div class="metric-label">Aktive Störungen</div>
            <div class="metric-value" style="color:<?= $issueCount > 0 ? '#dc2626' : '#111827' ?>;">
                <?= $issueCount ?>
            </div>
            <div class="metric-sub">
                <?php if ($issueCount > 0): ?>
                    <span class="badge-danger"><?= $issueCount ?> offen</span>
                <?php else: ?>
                    <span class="badge-enabled">Keine Störungen</span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Defender Alerts -->
    <div class="col-sm-6 col-lg-3">
        <div class="metric-card">
            <div class="metric-label">Sicherheitswarnungen</div>
            <div class="metric-value" style="color:<?= $alertCount > 0 ? '#d97706' : '#111827' ?>;">
                <?= $alertCount ?>
            </div>
            <div class="metric-sub">
                <?php if ($alertCount > 0): ?>
                    <span class="badge-warning"><?= $alertCount ?> aktiv</span>
                <?php else: ?>
                    <span class="badge-enabled">Keine Warnungen</span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Admin Areas (static) -->
    <div class="col-sm-6 col-lg-3">
        <div class="metric-card">
            <div class="metric-label">Verwaltungsbereiche</div>
            <div class="metric-value">8</div>
            <div class="metric-sub">Direkte EAC- & Defender-Links</div>
        </div>
    </div>

</div>

<!-- Active Exchange Issues (only shown when non-empty) -->
<?php if (!empty($activeIssues)): ?>
<div class="content-card mb-4" style="border-left: 4px solid #dc2626;">
    <div class="card-header-custom">
        <i class="bi bi-exclamation-octagon-fill text-danger"></i>
        <h6>Exchange Online Störungen (<?= $issueCount ?>)</h6>
    </div>
    <div class="card-body-custom p-0">
        <?php foreach ($activeIssues as $issue):
            $sev      = strtolower($issue['severity'] ?? '');
            $sevBadge = $severityBadge($sev);
            $sevLabel = $severityLabel($sev ?: ($issue['severity'] ?? '–'));
            $started  = !empty($issue['startDateTime'])
                ? date('d.m.Y H:i', strtotime($issue['startDateTime']))
                : '–';
        ?>
        <div class="px-3 py-3" style="border-bottom: 1px solid #f3f4f6;">
            <div class="d-flex align-items-start gap-2 mb-1">
                <span class="<?= $e($sevBadge) ?>" style="flex-shrink:0;margin-top:2px;">
                    <?= $e($sevLabel) ?>
                </span>
                <span style="font-size:13px;font-weight:500;"><?= $e($issue['title'] ?? '–') ?></span>
            </div>
            <div class="d-flex gap-3 flex-wrap" style="font-size:11px;color:#6b7280;">
                <span><i class="bi bi-clock me-1"></i>Beginn: <?= $e($started) ?></span>
                <?php if (!empty($issue['status'])): ?>
                    <span><i class="bi bi-circle-fill me-1" style="font-size:8px;"></i><?= $e($issue['status']) ?></span>
                <?php endif; ?>
            </div>
            <?php if (!empty($issue['impactDescription'])): ?>
                <div class="mt-1" style="font-size:12px;color:#374151;">
                    <?= $e(mb_strimwidth($issue['impactDescription'], 0, 200, '…')) ?>
                </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- Defender for Office 365 Alerts -->
<div class="content-card mb-4">
    <div class="card-header-custom">
        <i class="bi bi-shield-shaded text-primary"></i>
        <h6>Defender für Office 365 – Aktive Warnungen</h6>
    </div>
    <?php if (empty($defenderAlerts)): ?>
        <div class="card-body-custom">
            <div class="d-flex align-items-center gap-2 text-muted" style="font-size:13px;">
                <i class="bi bi-check-circle text-success"></i>
                Keine aktiven Sicherheitswarnungen von Defender für Office 365.
            </div>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Titel</th>
                        <th>Schweregrad</th>
                        <th>Kategorie</th>
                        <th>Erstellt am</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($defenderAlerts as $alert):
                        $sev      = $alert['severity'] ?? '';
                        $created  = !empty($alert['createdDateTime'])
                            ? date('d.m.Y H:i', strtotime($alert['createdDateTime']))
                            : '–';
                    ?>
                    <tr>
                        <td>
                            <div style="font-size:13px;font-weight:500;max-width:320px;">
                                <?= $e($alert['title'] ?? '–') ?>
                            </div>
                            <?php if (!empty($alert['description'])): ?>
                                <div style="font-size:11px;color:#9ca3af;margin-top:2px;">
                                    <?= $e(mb_strimwidth($alert['description'], 0, 100, '…')) ?>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="<?= $e($severityBadge($sev)) ?>">
                                <?= $e($severityLabel($sev)) ?>
                            </span>
                        </td>
                        <td style="font-size:12px;color:#6b7280;">
                            <?= $e($alert['category'] ?? '–') ?>
                        </td>
                        <td style="font-size:12px;color:#6b7280;white-space:nowrap;">
                            <?= $e($created) ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<!-- Admin Links to Exchange Online & Microsoft Defender -->
<div class="content-card mb-4">
    <div class="card-header-custom">
        <i class="bi bi-box-arrow-up-right text-secondary"></i>
        <h6>Verwaltung in Exchange Online &amp; Microsoft Defender</h6>
    </div>
    <div class="card-body-custom">
        <p class="text-muted mb-3" style="font-size:13px;">
            Diese Funktionen werden außerhalb der Graph API verwaltet — direkter Zugriff auf die Verwaltungsoberflächen:
        </p>
        <div class="row g-3">
            <?php foreach ($adminLinks as $link): ?>
            <div class="col-sm-6 col-lg-4 col-xl-3">
                <div class="h-100 p-3 rounded d-flex flex-column gap-1"
                     style="border:1px solid #e5e7eb;background:#fafafa;transition:box-shadow .15s;"
                     onmouseover="this.style.boxShadow='0 2px 8px rgba(0,0,0,.08)'"
                     onmouseout="this.style.boxShadow=''">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <i class="bi bi-<?= $e($link['icon']) ?> text-primary" style="font-size:1.1rem;flex-shrink:0;"></i>
                        <span style="font-size:13px;font-weight:600;"><?= $e($link['label']) ?></span>
                    </div>
                    <p class="text-muted mb-2" style="font-size:11px;line-height:1.4;flex-grow:1;">
                        <?= $e($link['desc']) ?>
                    </p>
                    <a href="<?= $e($link['url']) ?>"
                       target="_blank"
                       rel="noopener noreferrer"
                       class="btn btn-sm btn-outline-primary"
                       style="font-size:11px;align-self:flex-start;">
                        Öffnen <i class="bi bi-box-arrow-up-right ms-1"></i>
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Exchange Online Status Detail (shown when data is available) -->
<?php if (!empty($healthOverview)): ?>
<?php $exo = $healthOverview[0]; ?>
<div class="content-card">
    <div class="card-header-custom">
        <i class="bi bi-envelope-check text-secondary"></i>
        <h6>Exchange Online – Dienststatus</h6>
    </div>
    <div class="card-body-custom">
        <div class="d-flex align-items-center gap-3 flex-wrap">
            <div class="d-flex align-items-center gap-2">
                <span style="width:12px;height:12px;border-radius:50%;display:inline-block;
                             background:<?= $e($exoStatusMeta['color']) ?>;flex-shrink:0;"></span>
                <span style="font-size:13px;font-weight:500;"><?= $e($exo['service'] ?? 'Exchange Online') ?></span>
                <span class="<?= $e($exoStatusMeta['badgeClass']) ?>" style="font-size:11px;">
                    <?= $e($exoStatusMeta['label']) ?>
                </span>
            </div>
            <span class="text-muted ms-auto" style="font-size:11px;">
                <i class="bi bi-clock me-1"></i>Abgerufen: <?= date('d.m.Y H:i') ?> Uhr
            </span>
        </div>
        <?php if (!$isOperational && !empty($exoStatus)): ?>
            <div class="mt-2 alert alert-warning mb-0 py-2 px-3" style="font-size:12px;">
                <i class="bi bi-exclamation-triangle me-1"></i>
                Exchange Online meldet aktuell keinen Normalbetrieb. Prüfen Sie die
                <a href="https://admin.microsoft.com/Adminportal/Home#/servicehealth" target="_blank" rel="noopener noreferrer">
                    Microsoft 365 Service Health
                </a> für Details.
            </div>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<?php
// ── Schutzrichtlinien konfigurieren (Defender for Office 365 / EOP) ──
// Keine Microsoft-Graph-Write-API → Microsoft-Defender-Portal oder Exchange-Online-PowerShell.
echo \App\Core\Ui::externalCard(
    'Schutzrichtlinien konfigurieren (Defender for Office 365 / EOP)',
    'Anti-Phishing, Anti-Spam, Anti-Malware, Safe Links/Attachments und Transport-Regeln lassen sich '
    . '<strong>nicht über die Microsoft Graph API</strong> setzen. Konfiguration im '
    . '<strong>Microsoft-Defender-Portal</strong> oder per <strong>Exchange-Online-PowerShell</strong>:',
    [
        ['https://security.microsoft.com/presetSecurityPolicies', 'Preset-Sicherheitsrichtlinien (Defender)'],
        ['https://security.microsoft.com/antiphishing', 'Anti-Phishing (Defender)'],
        ['https://security.microsoft.com/safelinksv2', 'Safe Links (Defender)'],
        ['https://admin.exchange.microsoft.com/#/transportrules', 'Transport-Regeln (Exchange Admin)'],
    ],
    [
        ["Connect-ExchangeOnline -UserPrincipalName admin@deine-domain.de", 'Mit Exchange Online PowerShell verbinden'],
        ["Set-AntiPhishPolicy -Identity \"Office365 AntiPhish Default\" `\n  -EnableSpoofIntelligence \$true `\n  -EnableMailboxIntelligence \$true `\n  -EnableMailboxIntelligenceProtection \$true `\n  -EnableFirstContactSafetyTips \$true", 'Anti-Phishing härten'],
        ["Set-HostedOutboundSpamFilterPolicy -Identity Default -AutoForwardingMode Off", 'Externe Auto-Weiterleitung tenant-weit blockieren'],
        ["New-SafeLinksPolicy -Name \"SafeLinks Std\" `\n  -EnableSafeLinksForEmail \$true -EnableSafeLinksForTeams \$true `\n  -EnableSafeLinksForOffice \$true -ScanUrls \$true -DeliverMessageAfterScan \$true\n\nNew-SafeLinksRule -Name \"SafeLinks Std\" -SafeLinksPolicy \"SafeLinks Std\" `\n  -RecipientDomainIs (Get-AcceptedDomain).Name", 'Safe Links aktivieren'],
        ["New-SafeAttachmentPolicy -Name \"SafeAtt Std\" -Enable \$true -Action Block\n\nNew-SafeAttachmentRule -Name \"SafeAtt Std\" -SafeAttachmentPolicy \"SafeAtt Std\" `\n  -RecipientDomainIs (Get-AcceptedDomain).Name", 'Safe Attachments aktivieren'],
        ["Set-ExternalInOutlook -Enabled \$true", '„External\"-Tag in Outlook aktivieren'],
    ],
    'shield-shaded'
);
?>
