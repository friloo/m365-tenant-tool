<?php use App\Core\View; $e = fn($v) => View::escape($v); ?>

<?php
// ── Helper: compute colour class based on adoption percentage ─────────────
$adoptionColor = function (int $active, int $total): string {
    if ($total <= 0) return '#6b7280';
    $pct = ($active / $total) * 100;
    if ($pct >= 70) return '#16a34a';
    if ($pct >= 40) return '#d97706';
    return '#dc2626';
};

$adoptionBadge = function (int $active, int $total): string {
    if ($total <= 0) return 'badge-secondary';
    $pct = ($active / $total) * 100;
    if ($pct >= 70) return 'badge-success';
    if ($pct >= 40) return 'badge-warning';
    return 'badge-disabled';
};

$pctOf = function (int $active, int $total): string {
    if ($total <= 0) return '0';
    return number_format(($active / $total) * 100, 1);
};

$totalUsers = (int)($skuTotals['consumed'] ?? 0);
$totalActive = (int)($activeUsers['total'] ?? 0);
// For percentage display use licensed users (SKU consumed); fall back to report total
$denominator = $totalUsers > 0 ? $totalUsers : $totalActive;
?>

<!-- Page subtitle -->
<p class="text-muted small mb-4">
    <i class="bi bi-bar-chart-line me-1"></i>
    Nutzungsstatistiken der letzten 30 Tage aus Microsoft 365 Reports
</p>

<!-- ── Section 1: Metric cards ──────────────────────────────────────────── -->
<div class="row g-3 mb-4">

    <!-- Total licensed users (from SKU data) -->
    <div class="col-sm-6 col-lg-2">
        <div class="metric-card">
            <div class="metric-label">
                <i class="bi bi-people me-1"></i>Lizenzierte Nutzer
            </div>
            <div class="metric-value"><?= number_format($totalUsers) ?></div>
            <div class="metric-sub">
                <?php if ($skuTotals['total'] > 0): ?>
                    von <?= number_format($skuTotals['total']) ?> verfügbar
                <?php else: ?>
                    Lizenzen gesamt
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Exchange aktiv -->
    <?php
    $exActive = (int)($activeUsers['exchange'] ?? 0);
    $exColor  = $adoptionColor($exActive, $denominator);
    ?>
    <div class="col-sm-6 col-lg-<?= empty($activeUsers) ? '6' : '2' ?>">
        <div class="metric-card">
            <div class="metric-label">
                <i class="bi bi-envelope me-1"></i>Exchange aktiv
            </div>
            <div class="metric-value" style="color:<?= $exColor ?>;"><?= number_format($exActive) ?></div>
            <div class="metric-sub">
                <?php if (!empty($activeUsers) && $denominator > 0): ?>
                    <span class="<?= $adoptionBadge($exActive, $denominator) ?>"><?= $pctOf($exActive, $denominator) ?>%</span>
                <?php else: ?>
                    &mdash;
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Teams aktiv -->
    <?php $tmActive = (int)($activeUsers['teams'] ?? 0); $tmColor = $adoptionColor($tmActive, $denominator); ?>
    <div class="col-sm-6 col-lg-2">
        <div class="metric-card">
            <div class="metric-label">
                <i class="bi bi-microsoft-teams me-1"></i>Teams aktiv
            </div>
            <div class="metric-value" style="color:<?= $tmColor ?>;"><?= number_format($tmActive) ?></div>
            <div class="metric-sub">
                <?php if (!empty($activeUsers) && $denominator > 0): ?>
                    <span class="<?= $adoptionBadge($tmActive, $denominator) ?>"><?= $pctOf($tmActive, $denominator) ?>%</span>
                <?php else: ?>
                    &mdash;
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- SharePoint aktiv -->
    <?php $spActive = (int)($activeUsers['sharepoint'] ?? 0); $spColor = $adoptionColor($spActive, $denominator); ?>
    <div class="col-sm-6 col-lg-2">
        <div class="metric-card">
            <div class="metric-label">
                <i class="bi bi-share me-1"></i>SharePoint aktiv
            </div>
            <div class="metric-value" style="color:<?= $spColor ?>;"><?= number_format($spActive) ?></div>
            <div class="metric-sub">
                <?php if (!empty($activeUsers) && $denominator > 0): ?>
                    <span class="<?= $adoptionBadge($spActive, $denominator) ?>"><?= $pctOf($spActive, $denominator) ?>%</span>
                <?php else: ?>
                    &mdash;
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- OneDrive aktiv -->
    <?php $odActive = (int)($activeUsers['onedrive'] ?? 0); $odColor = $adoptionColor($odActive, $denominator); ?>
    <div class="col-sm-6 col-lg-2">
        <div class="metric-card">
            <div class="metric-label">
                <i class="bi bi-cloud me-1"></i>OneDrive aktiv
            </div>
            <div class="metric-value" style="color:<?= $odColor ?>;"><?= number_format($odActive) ?></div>
            <div class="metric-sub">
                <?php if (!empty($activeUsers) && $denominator > 0): ?>
                    <span class="<?= $adoptionBadge($odActive, $denominator) ?>"><?= $pctOf($odActive, $denominator) ?>%</span>
                <?php else: ?>
                    &mdash;
                <?php endif; ?>
            </div>
        </div>
    </div>

</div>

<!-- ── Section 2: Adoption overview bar chart ────────────────────────────── -->
<div class="content-card mb-4">
    <div class="card-header-custom">
        <i class="bi bi-bar-chart-line text-primary"></i>
        <h6>Service Adoption Übersicht</h6>
        <?php if ($denominator > 0): ?>
            <span class="text-muted small ms-auto">Basis: <?= number_format($denominator) ?> Nutzer</span>
        <?php endif; ?>
    </div>
    <div class="card-body-custom">
        <?php
        $hasAnyServiceData = ($activeUsers['exchange'] ?? 0) + ($activeUsers['teams'] ?? 0)
                           + ($activeUsers['sharepoint'] ?? 0) + ($activeUsers['onedrive'] ?? 0) > 0;
        ?>
        <?php if (empty($activeUsers) || !$hasAnyServiceData): ?>
            <div class="empty-state">
                <i class="bi bi-shield-exclamation text-muted" style="font-size:2.5rem;"></i>
                <p class="mt-3 mb-1 fw-medium">Keine Adoption-Daten verfügbar</p>
                <p class="text-muted small">
                    <code>Reports.Read.All</code> Berechtigung fehlt möglicherweise.<br>
                    Prüfen Sie auch, ob der Datenschutzmodus für Berichte deaktiviert ist.
                </p>
            </div>
        <?php else: ?>
            <div style="position:relative; height:250px;">
                <canvas id="adoptionBarChart"></canvas>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- ── Section 3: Activity trend charts (2-column) ───────────────────────── -->
<div class="row g-3 mb-4">

    <!-- E-Mail-Aktivität -->
    <div class="col-lg-6">
        <div class="content-card h-100">
            <div class="card-header-custom">
                <i class="bi bi-envelope-open text-primary"></i>
                <h6>E-Mail-Aktivität (letzte 30 Tage)</h6>
            </div>
            <div class="card-body-custom">
                <?php if (empty($emailCounts)): ?>
                    <div class="empty-state">
                        <i class="bi bi-envelope text-muted" style="font-size:2rem;"></i>
                        <p class="mt-2 text-muted small"><code>Reports.Read.All</code> Berechtigung fehlt möglicherweise.</p>
                    </div>
                <?php else: ?>
                    <div style="position:relative; height:250px;">
                        <canvas id="emailChart"></canvas>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Teams-Aktivität -->
    <div class="col-lg-6">
        <div class="content-card h-100">
            <div class="card-header-custom">
                <i class="bi bi-chat-dots text-info"></i>
                <h6>Teams-Aktivität (letzte 30 Tage)</h6>
            </div>
            <div class="card-body-custom">
                <?php if (empty($teamsCounts)): ?>
                    <div class="empty-state">
                        <i class="bi bi-microsoft-teams text-muted" style="font-size:2rem;"></i>
                        <p class="mt-2 text-muted small"><code>Reports.Read.All</code> Berechtigung fehlt möglicherweise.</p>
                    </div>
                <?php else: ?>
                    <div style="position:relative; height:250px;">
                        <canvas id="teamsChart"></canvas>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

</div>

<!-- ── Section 4: OneDrive-Aktivität (full width) ────────────────────────── -->
<?php if (!empty($onedriveCounts)): ?>
<div class="content-card mb-4">
    <div class="card-header-custom">
        <i class="bi bi-cloud-arrow-up text-success"></i>
        <h6>OneDrive-Aktivität (letzte 30 Tage)</h6>
    </div>
    <div class="card-body-custom">
        <div style="position:relative; height:250px;">
            <canvas id="onedriveChart"></canvas>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- ── Chart.js initialisation ───────────────────────────────────────────── -->
<script>
(function () {
    'use strict';

    // Shared chart defaults
    var sharedOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { position: 'bottom', labels: { font: { size: 11 }, boxWidth: 12 } },
            tooltip: { mode: 'index', intersect: false },
        },
        scales: {
            x: {
                ticks: {
                    font: { size: 10 },
                    maxRotation: 45,
                    maxTicksLimit: 10,
                    callback: function (val, idx) {
                        // Show only MM-DD portion of the date string
                        var label = this.getLabelForValue(val);
                        if (label && label.length >= 10) return label.substring(5); // YYYY-MM-DD → MM-DD
                        return label;
                    },
                },
            },
            y: { beginAtZero: true, ticks: { font: { size: 11 } } },
        },
    };

    // ── Adoption horizontal bar chart ────────────────────────────────────
    var adoptionCtx = document.getElementById('adoptionBarChart');
    if (adoptionCtx) {
        var totalDenom = <?= (int)$denominator ?>;
        var adoptionValues = [
            <?= (int)($activeUsers['exchange']   ?? 0) ?>,
            <?= (int)($activeUsers['teams']      ?? 0) ?>,
            <?= (int)($activeUsers['sharepoint'] ?? 0) ?>,
            <?= (int)($activeUsers['onedrive']   ?? 0) ?>,
            <?= (int)($activeUsers['yammer']     ?? 0) ?>,
        ];

        new Chart(adoptionCtx, {
            type: 'bar',
            data: {
                labels: ['Exchange', 'Teams', 'SharePoint', 'OneDrive', 'Yammer'],
                datasets: [{
                    label: 'Aktive Nutzer',
                    data: adoptionValues,
                    backgroundColor: [
                        'rgba(0, 120, 212, 0.8)',    // Exchange blue
                        'rgba(100, 65, 164, 0.8)',   // Teams purple
                        'rgba(0, 183, 95, 0.8)',     // SharePoint green
                        'rgba(0, 164, 239, 0.8)',    // OneDrive teal
                        'rgba(229, 77, 46, 0.8)',    // Yammer red
                    ],
                    borderColor: [
                        'rgba(0, 120, 212, 1)',
                        'rgba(100, 65, 164, 1)',
                        'rgba(0, 183, 95, 1)',
                        'rgba(0, 164, 239, 1)',
                        'rgba(229, 77, 46, 1)',
                    ],
                    borderWidth: 1,
                    borderRadius: 4,
                }],
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function (ctx) {
                                var val = ctx.parsed.x;
                                var pct = totalDenom > 0 ? ((val / totalDenom) * 100).toFixed(1) : 0;
                                return ' ' + val.toLocaleString('de-DE') + ' Nutzer (' + pct + '%)';
                            },
                        },
                    },
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: { font: { size: 11 } },
                    },
                    y: { ticks: { font: { size: 12 } } },
                },
            },
        });
    }

    // ── E-Mail activity line chart ───────────────────────────────────────
    var emailCtx = document.getElementById('emailChart');
    if (emailCtx) {
        var emailData = <?= json_encode($emailCounts, JSON_UNESCAPED_UNICODE) ?>;
        var emailLabels  = emailData.map(function (r) { return r.date; });
        var emailSend    = emailData.map(function (r) { return r.send; });
        var emailReceive = emailData.map(function (r) { return r.receive; });
        var emailRead    = emailData.map(function (r) { return r.read; });

        new Chart(emailCtx, {
            type: 'line',
            data: {
                labels: emailLabels,
                datasets: [
                    {
                        label: 'Gesendet',
                        data: emailSend,
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59,130,246,0.08)',
                        borderWidth: 2,
                        pointRadius: 2,
                        tension: 0.3,
                        fill: true,
                    },
                    {
                        label: 'Empfangen',
                        data: emailReceive,
                        borderColor: '#16a34a',
                        backgroundColor: 'rgba(22,163,74,0.06)',
                        borderWidth: 2,
                        pointRadius: 2,
                        tension: 0.3,
                        fill: false,
                    },
                    {
                        label: 'Gelesen',
                        data: emailRead,
                        borderColor: '#d97706',
                        backgroundColor: 'rgba(217,119,6,0.06)',
                        borderWidth: 2,
                        pointRadius: 2,
                        tension: 0.3,
                        fill: false,
                    },
                ],
            },
            options: sharedOptions,
        });
    }

    // ── Teams activity line chart ────────────────────────────────────────
    var teamsCtx = document.getElementById('teamsChart');
    if (teamsCtx) {
        var teamsData     = <?= json_encode($teamsCounts, JSON_UNESCAPED_UNICODE) ?>;
        var teamsLabels   = teamsData.map(function (r) { return r.date; });
        var teamsTeamChat = teamsData.map(function (r) { return r.team_chat; });
        var teamsPrivChat = teamsData.map(function (r) { return r.private_chat; });
        var teamsCalls    = teamsData.map(function (r) { return r.calls; });
        var teamsMeetings = teamsData.map(function (r) { return r.meetings; });

        new Chart(teamsCtx, {
            type: 'line',
            data: {
                labels: teamsLabels,
                datasets: [
                    {
                        label: 'Team-Chat',
                        data: teamsTeamChat,
                        borderColor: '#6441a4',
                        backgroundColor: 'rgba(100,65,164,0.08)',
                        borderWidth: 2,
                        pointRadius: 2,
                        tension: 0.3,
                        fill: true,
                    },
                    {
                        label: 'Privat-Chat',
                        data: teamsPrivChat,
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59,130,246,0.06)',
                        borderWidth: 2,
                        pointRadius: 2,
                        tension: 0.3,
                        fill: false,
                    },
                    {
                        label: 'Anrufe',
                        data: teamsCalls,
                        borderColor: '#16a34a',
                        backgroundColor: 'rgba(22,163,74,0.06)',
                        borderWidth: 2,
                        pointRadius: 2,
                        tension: 0.3,
                        fill: false,
                    },
                    {
                        label: 'Meetings',
                        data: teamsMeetings,
                        borderColor: '#d97706',
                        backgroundColor: 'rgba(217,119,6,0.06)',
                        borderWidth: 2,
                        pointRadius: 2,
                        tension: 0.3,
                        fill: false,
                    },
                ],
            },
            options: sharedOptions,
        });
    }

    // ── OneDrive activity line chart ─────────────────────────────────────
    var onedriveCtx = document.getElementById('onedriveChart');
    if (onedriveCtx) {
        var odData         = <?= json_encode($onedriveCounts, JSON_UNESCAPED_UNICODE) ?>;
        var odLabels       = odData.map(function (r) { return r.date; });
        var odViewed       = odData.map(function (r) { return r.viewed_edited; });
        var odSynced       = odData.map(function (r) { return r.synced; });
        var odSharedInt    = odData.map(function (r) { return r.shared_internal; });
        var odSharedExt    = odData.map(function (r) { return r.shared_external; });

        new Chart(onedriveCtx, {
            type: 'line',
            data: {
                labels: odLabels,
                datasets: [
                    {
                        label: 'Angesehen / Bearbeitet',
                        data: odViewed,
                        borderColor: '#0078d4',
                        backgroundColor: 'rgba(0,120,212,0.08)',
                        borderWidth: 2,
                        pointRadius: 2,
                        tension: 0.3,
                        fill: true,
                    },
                    {
                        label: 'Synchronisiert',
                        data: odSynced,
                        borderColor: '#16a34a',
                        backgroundColor: 'rgba(22,163,74,0.06)',
                        borderWidth: 2,
                        pointRadius: 2,
                        tension: 0.3,
                        fill: false,
                    },
                    {
                        label: 'Intern geteilt',
                        data: odSharedInt,
                        borderColor: '#d97706',
                        backgroundColor: 'rgba(217,119,6,0.06)',
                        borderWidth: 2,
                        pointRadius: 2,
                        tension: 0.3,
                        fill: false,
                    },
                    {
                        label: 'Extern geteilt',
                        data: odSharedExt,
                        borderColor: '#dc2626',
                        backgroundColor: 'rgba(220,38,38,0.06)',
                        borderWidth: 2,
                        pointRadius: 2,
                        tension: 0.3,
                        fill: false,
                    },
                ],
            },
            options: sharedOptions,
        });
    }

})();
</script>
