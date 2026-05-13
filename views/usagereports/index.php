<?php use App\Core\View; $e = fn($v) => View::escape($v); ?>

<div class="d-flex align-items-center gap-2 mb-4 flex-wrap">
    <form method="get" action="/usagereports" class="d-flex align-items-center gap-2 mb-0">
        <label class="text-muted small me-1 mb-0">Zeitraum:</label>
        <?php foreach ([7, 30, 90] as $p): ?>
            <button type="submit" name="period" value="<?= $p ?>"
                    class="btn btn-sm <?= $period === $p ? 'btn-primary' : 'btn-outline-secondary' ?>">
                <?= $p ?> Tage
            </button>
        <?php endforeach; ?>
    </form>
    <a href="https://admin.microsoft.com/Adminportal/Home#/reportsUsage" target="_blank"
       class="btn btn-sm btn-outline-primary ms-auto">
        <i class="bi bi-box-arrow-up-right me-1"></i>M365 Admin Center
    </a>
</div>

<?php
$hasData = ($summary['exchange'] + $summary['oneDrive'] + $summary['sharePoint'] + $summary['teams']) > 0;
$hasActivity = ($summary['emailsSent'] + $summary['emailsReceived'] + $summary['teamsMessages'] + $summary['teamsMeetings'] + $summary['teamsCalls']) > 0;
?>

<h6 class="fw-semibold mb-3 text-muted">
    <i class="bi bi-people me-1"></i>Aktive Nutzer (Letzte <?= $e($period) ?> Tage)
</h6>

<div class="row g-3 mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="metric-card">
            <div class="metric-label"><i class="bi bi-envelope me-1"></i>Exchange / E-Mail</div>
            <div class="metric-value" <?= !$hasData ? 'style="color:#9ca3af;"' : '' ?>>
                <?= $hasData ? number_format($summary['exchange']) : '–' ?>
            </div>
            <div class="metric-sub">aktive Nutzer</div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="metric-card">
            <div class="metric-label"><i class="bi bi-cloud me-1"></i>OneDrive</div>
            <div class="metric-value" <?= !$hasData ? 'style="color:#9ca3af;"' : '' ?>>
                <?= $hasData ? number_format($summary['oneDrive']) : '–' ?>
            </div>
            <div class="metric-sub">aktive Nutzer</div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="metric-card">
            <div class="metric-label"><i class="bi bi-share me-1"></i>SharePoint</div>
            <div class="metric-value" <?= !$hasData ? 'style="color:#9ca3af;"' : '' ?>>
                <?= $hasData ? number_format($summary['sharePoint']) : '–' ?>
            </div>
            <div class="metric-sub">aktive Nutzer</div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="metric-card">
            <div class="metric-label"><i class="bi bi-camera-video me-1"></i>Teams</div>
            <div class="metric-value" <?= !$hasData ? 'style="color:#9ca3af;"' : '' ?>>
                <?= $hasData ? number_format($summary['teams']) : '–' ?>
            </div>
            <div class="metric-sub">aktive Nutzer</div>
        </div>
    </div>
</div>

<?php if (!$hasData): ?>
    <div class="alert alert-warning mb-4">
        <i class="bi bi-exclamation-triangle me-2"></i>
        <strong>Keine Daten verfügbar.</strong>
        Möglicherweise fehlt die Berechtigung <code>Reports.Read.All</code> oder der Datenschutzmodus für Berichte ist aktiviert.
    </div>
<?php endif; ?>

<h6 class="fw-semibold mb-3 text-muted">
    <i class="bi bi-bar-chart-line me-1"></i>Aktivität (Letzte <?= $e($period) ?> Tage, kumuliert)
</h6>

<div class="row g-3 mb-4">
    <div class="col-sm-6 col-lg-2">
        <div class="metric-card">
            <div class="metric-label"><i class="bi bi-send me-1"></i>E-Mails gesendet</div>
            <div class="metric-value" <?= !$hasActivity ? 'style="color:#9ca3af;"' : '' ?>>
                <?= $hasActivity ? number_format($summary['emailsSent']) : '–' ?>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-2">
        <div class="metric-card">
            <div class="metric-label"><i class="bi bi-inbox me-1"></i>E-Mails empfangen</div>
            <div class="metric-value" <?= !$hasActivity ? 'style="color:#9ca3af;"' : '' ?>>
                <?= $hasActivity ? number_format($summary['emailsReceived']) : '–' ?>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-2">
        <div class="metric-card">
            <div class="metric-label"><i class="bi bi-chat-dots me-1"></i>Teams-Nachrichten</div>
            <div class="metric-value" <?= !$hasActivity ? 'style="color:#9ca3af;"' : '' ?>>
                <?= $hasActivity ? number_format($summary['teamsMessages']) : '–' ?>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-2">
        <div class="metric-card">
            <div class="metric-label"><i class="bi bi-camera-video me-1"></i>Teams-Meetings</div>
            <div class="metric-value" <?= !$hasActivity ? 'style="color:#9ca3af;"' : '' ?>>
                <?= $hasActivity ? number_format($summary['teamsMeetings']) : '–' ?>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-2">
        <div class="metric-card">
            <div class="metric-label"><i class="bi bi-telephone me-1"></i>Teams-Anrufe</div>
            <div class="metric-value" <?= !$hasActivity ? 'style="color:#9ca3af;"' : '' ?>>
                <?= $hasActivity ? number_format($summary['teamsCalls']) : '–' ?>
            </div>
        </div>
    </div>
</div>

<?php if (!$hasActivity): ?>
    <div class="alert alert-warning mb-4">
        <i class="bi bi-exclamation-triangle me-2"></i>
        <strong>Keine Aktivitätsdaten verfügbar.</strong>
        Möglicherweise fehlt die Berechtigung <code>Reports.Read.All</code>.
    </div>
<?php endif; ?>

<div class="alert alert-info d-flex align-items-start gap-2 mb-0">
    <i class="bi bi-info-circle-fill flex-shrink-0 mt-1"></i>
    <div>
        Daten basieren auf aggregierten Microsoft-Berichten. Für detaillierte Nutzerauswertungen steht das
        <a href="https://admin.microsoft.com/Adminportal/Home#/reportsUsage" target="_blank" class="alert-link">
            Microsoft 365 Admin Center <i class="bi bi-box-arrow-up-right ms-1" style="font-size:11px;"></i>
        </a> zur Verfügung.
    </div>
</div>
