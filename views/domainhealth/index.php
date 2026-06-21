<?php use App\Core\View; $e = fn($v) => View::escape($v); ?>

<div class="row g-3 mb-4">
    <div class="col-sm-3">
        <div class="metric-card">
            <div class="metric-label"><?= te('Domains gesamt') ?></div>
            <div class="metric-value"><?= $summary['total'] ?></div>
            <div class="metric-sub"><?= te('Verifizierte Domains') ?></div>
        </div>
    </div>
    <div class="col-sm-3">
        <div class="metric-card">
            <div class="metric-label"><?= te('Vollständig geschützt') ?></div>
            <div class="metric-value" style="color:<?= $summary['fullyProtected'] === $summary['total'] && $summary['total'] > 0 ? '#16a34a' : '#111827' ?>;">
                <?= $summary['fullyProtected'] ?>
            </div>
            <div class="metric-sub">SPF + DKIM + DMARC</div>
        </div>
    </div>
    <div class="col-sm-3">
        <div class="metric-card">
            <div class="metric-label"><?= te('Mit Problemen') ?></div>
            <div class="metric-value" style="color:<?= $summary['withIssues'] > 0 ? '#dc2626' : '#16a34a' ?>;">
                <?= $summary['withIssues'] ?>
            </div>
            <div class="metric-sub"><?= te('Handlungsbedarf') ?></div>
        </div>
    </div>
    <div class="col-sm-3">
        <div class="metric-card">
            <div class="metric-label">DMARC Reject</div>
            <div class="metric-value" style="color:<?= ($summary['byStatus']['dmarc_reject'] ?? 0) === $summary['total'] && $summary['total'] > 0 ? '#16a34a' : '#111827' ?>;">
                <?= $summary['byStatus']['dmarc_reject'] ?? 0 ?>
            </div>
            <div class="metric-sub"><?= te('Strikte Richtlinie') ?></div>
        </div>
    </div>
</div>

<?php
$dmarcIssues = array_filter($domains, fn($d) => in_array($d['dmarc'] ?? 'missing', ['missing', 'report_only'], true));
if (!empty($dmarcIssues)):
?>
<div class="alert alert-warning mb-4">
    <i class="bi bi-exclamation-triangle me-2"></i>
    <strong><?= te(':n Domain(s) ohne DMARC-Schutz oder mit p=none', ['n' => count($dmarcIssues)]) ?></strong> —
    <?= te('E-Mail-Spoofing auf diesen Domains ist möglich. Richten Sie DMARC mit mindestens p=quarantine ein.') ?>
</div>
<?php endif; ?>

<div class="content-card">
    <div class="table-toolbar">
        <input type="text" id="dhSearch" class="search-box" placeholder="<?= te('Domain suchen…') ?>">
        <a href="/domainhealth?refresh=1" class="btn btn-sm btn-outline-secondary ms-auto">
            <i class="bi bi-arrow-clockwise me-1"></i> <?= te('Aktualisieren') ?>
        </a>
    </div>
    <div class="table-responsive">
        <table class="data-table" id="dhTable">
            <thead>
                <tr>
                    <th><?= te('Domain') ?></th>
                    <th><?= te('Standard') ?></th>
                    <th>SPF</th>
                    <th>DKIM</th>
                    <th>DMARC</th>
                    <th><?= te('Schutzlevel') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($domains as $d):
                    $spf   = $d['spf'] ?? 'missing';
                    $dkim  = $d['dkim'] ?? 'missing';
                    $dmarc = $d['dmarc'] ?? 'missing';

                    $dmarcOk = in_array($dmarc, ['reject', 'quarantine'], true);
                    $allOk   = $spf === 'pass' && $dkim === 'pass' && $dmarcOk;
                    $noneOk  = $spf !== 'pass' && $dkim !== 'pass' && !$dmarcOk;
                ?>
                <tr>
                    <td class="fw-medium"><?= $e($d['id'] ?? '') ?></td>
                    <td>
                        <?php if ($d['isDefault'] ?? false): ?>
                            <span class="badge-info"><?= te('Standard') ?></span>
                        <?php else: ?>
                            <span class="badge-neutral">–</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($spf === 'pass'): ?>
                            <span class="badge-ok">SPF</span>
                        <?php else: ?>
                            <span class="badge-disabled"><?= te('Fehlt') ?></span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($dkim === 'pass'): ?>
                            <span class="badge-ok">DKIM</span>
                        <?php else: ?>
                            <span class="badge-disabled"><?= te('Fehlt') ?></span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($dmarc === 'reject'): ?>
                            <span class="badge-ok">Reject</span>
                        <?php elseif ($dmarc === 'quarantine'): ?>
                            <span class="badge-ok">Quarantine</span>
                        <?php elseif ($dmarc === 'report_only'): ?>
                            <span class="badge-warning">p=none</span>
                        <?php else: ?>
                            <span class="badge-disabled"><?= te('Fehlt') ?></span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($allOk): ?>
                            <span class="badge-ok"><?= te('Vollständig') ?></span>
                        <?php elseif ($noneOk): ?>
                            <span class="badge-disabled"><?= te('Kritisch') ?></span>
                        <?php else: ?>
                            <span class="badge-warning"><?= te('Teilweise') ?></span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($domains)): ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4"><?= te('Keine verifizierten Domains gefunden') ?></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="content-card mt-3" style="padding:14px 16px;background:#f0f9ff;border:1px solid #bae6fd;">
    <p style="font-size:13px;color:#0369a1;margin:0;">
        <i class="bi bi-info-circle me-2"></i>
        <strong>SPF</strong> <?= te('(Sender Policy Framework) legt fest, welche Server E-Mails für eine Domain versenden dürfen, um Spoofing zu verhindern.') ?>
        <strong>DKIM</strong> <?= te('signiert ausgehende E-Mails kryptografisch, damit Empfänger die Echtheit prüfen können.') ?>
        <strong>DMARC</strong> <?= te('definiert, wie Empfänger mit nicht konformen E-Mails umgehen sollen, und ermöglicht Berichte an den Domain-Inhaber.') ?>
    </p>
</div>

<?php
// ── DKIM aktivieren (kein Graph-Write) → Defender-Portal oder Exchange-Online-PowerShell ──
echo \App\Core\Ui::externalCard(
    t('DKIM aktivieren'),
    t('DKIM-Signierung lässt sich <strong>nicht über die Microsoft Graph API</strong> aktivieren. '
    . 'Schritt 1: Befehl ausführen (legt die Signaturkonfiguration an). Schritt 2: die beiden von '
    . 'Microsoft angezeigten <code>CNAME</code>-Records (selector1/selector2) bei deinem DNS-Anbieter '
    . 'veröffentlichen. Schritt 3: DKIM einschalten. SPF/DMARC sind reine DNS-Einträge.'),
    [
        ['https://security.microsoft.com/dkimv2', t('DKIM im Defender-Portal')],
    ],
    [
        ["Connect-ExchangeOnline -UserPrincipalName admin@deine-domain.de", t('Mit Exchange Online PowerShell verbinden')],
        ["Get-DkimSigningConfig | Format-Table Domain,Enabled,Status", t('DKIM-Status je Domain prüfen')],
        ["New-DkimSigningConfig -DomainName deine-domain.de -Enabled \$false\n# danach die angezeigten CNAMEs im DNS anlegen, dann:\nSet-DkimSigningConfig -Identity deine-domain.de -Enabled \$true", t('DKIM einrichten & aktivieren')],
    ],
    'shield-lock'
);
?>

<script>
initTableSearch('dhSearch', 'dhTable');
</script>
