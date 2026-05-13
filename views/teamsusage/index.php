<?php use App\Core\View; $e = fn($v) => View::escape($v); ?>

<meta http-equiv="refresh" content="3600">

<?php if (empty($rows)): ?>
<div class="content-card">
    <div class="card-body-custom">
        <?php
        if (!empty($diag ?? null)) {
            $diagStyle = 'empty';
            $diagIcon  = 'microsoft-teams';
            $diagTitle = 'Keine Teams-Nutzungsdaten verfügbar';
            include BASE_PATH . '/views/partials/graph_diagnostic.php';
        } else { ?>
            <div class="empty-state">
                <i class="bi bi-microsoft-teams text-muted" style="font-size:2.5rem;"></i>
                <p class="mt-3 mb-1 fw-medium">Keine Teams-Nutzungsdaten verfügbar</p>
                <p class="text-muted small">Im gewählten Zeitraum wurde keine Teams-Aktivität erfasst.</p>
            </div>
        <?php } ?>
    </div>
</div>
<?php else: ?>

<!-- Metric Cards -->
<div class="row g-3 mb-4">
    <div class="col-sm-3">
        <div class="metric-card">
            <div class="metric-label">Gesamt Nutzer</div>
            <div class="metric-value"><?= number_format($stats['total']) ?></div>
            <div class="metric-sub">im Report erfasst</div>
        </div>
    </div>
    <div class="col-sm-3">
        <div class="metric-card">
            <div class="metric-label">Aktiv (letzte 30 Tage)</div>
            <div class="metric-value" style="color:#16a34a;"><?= number_format($stats['active']) ?></div>
            <div class="metric-sub">mind. eine Aktivität</div>
        </div>
    </div>
    <div class="col-sm-3">
        <div class="metric-card">
            <div class="metric-label">Inaktiv</div>
            <div class="metric-value" style="color:<?= $stats['inactive'] > 0 ? '#d97706' : '#111827' ?>;">
                <?= number_format($stats['inactive']) ?>
            </div>
            <div class="metric-sub">keine Aktivität in 30 Tagen</div>
        </div>
    </div>
    <div class="col-sm-3">
        <div class="metric-card">
            <div class="metric-label">Ø Nachrichten/Nutzer</div>
            <div class="metric-value"><?= number_format($stats['avg_messages']) ?></div>
            <div class="metric-sub">bei aktiven Nutzern</div>
        </div>
    </div>
</div>

<!-- Top-10 Tables Row -->
<?php
// Pre-compute maxima for proportional bars
$maxChatMsgs = 1;
foreach ($stats['top_chatters'] as $r) {
    $v = $r['teamChatMessages'] + $r['privateChatMessages'];
    if ($v > $maxChatMsgs) $maxChatMsgs = $v;
}
$maxCalls = 1;
foreach ($stats['top_callers'] as $r) {
    if ($r['callCount'] > $maxCalls) $maxCalls = $r['callCount'];
}
$maxMeetings = 1;
foreach ($stats['top_meetings'] as $r) {
    if ($r['meetingCount'] > $maxMeetings) $maxMeetings = $r['meetingCount'];
}
?>
<div class="row g-3 mb-4">

    <!-- Top 10 Chat -->
    <div class="col-md-4">
        <div class="content-card h-100">
            <div class="card-header-custom">
                <i class="bi bi-chat-dots text-primary"></i>
                <h6>Top 10 Chat</h6>
            </div>
            <div class="card-body-custom p-0">
                <?php if (empty($stats['top_chatters'])): ?>
                    <div class="empty-state py-4"><span class="text-muted small">Keine Daten</span></div>
                <?php else: ?>
                <table class="data-table" style="margin-bottom:0;">
                    <thead>
                        <tr>
                            <th>Nutzer</th>
                            <th class="text-end" style="width:60px;">Msgs</th>
                            <th style="width:80px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($stats['top_chatters'] as $r): ?>
                            <?php
                            $total = $r['teamChatMessages'] + $r['privateChatMessages'];
                            $pct   = (int)min(100, round($total / $maxChatMsgs * 100));
                            ?>
                            <tr>
                                <td style="font-size:12px; max-width:140px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                                    <?= $e($r['upn']) ?>
                                </td>
                                <td class="text-end" style="font-size:12px; font-weight:500;"><?= number_format($total) ?></td>
                                <td>
                                    <div class="progress-custom" style="margin-bottom:0;">
                                        <div class="bar" style="width:<?= $pct ?>%; background:#3b82f6;"></div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Top 10 Anrufe -->
    <div class="col-md-4">
        <div class="content-card h-100">
            <div class="card-header-custom">
                <i class="bi bi-telephone text-success"></i>
                <h6>Top 10 Anrufe</h6>
            </div>
            <div class="card-body-custom p-0">
                <?php if (empty($stats['top_callers'])): ?>
                    <div class="empty-state py-4"><span class="text-muted small">Keine Daten</span></div>
                <?php else: ?>
                <table class="data-table" style="margin-bottom:0;">
                    <thead>
                        <tr>
                            <th>Nutzer</th>
                            <th class="text-end" style="width:60px;">Anrufe</th>
                            <th style="width:80px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($stats['top_callers'] as $r): ?>
                            <?php $pct = (int)min(100, round($r['callCount'] / $maxCalls * 100)); ?>
                            <tr>
                                <td style="font-size:12px; max-width:140px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                                    <?= $e($r['upn']) ?>
                                </td>
                                <td class="text-end" style="font-size:12px; font-weight:500;"><?= number_format($r['callCount']) ?></td>
                                <td>
                                    <div class="progress-custom" style="margin-bottom:0;">
                                        <div class="bar" style="width:<?= $pct ?>%; background:#16a34a;"></div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Top 10 Meetings -->
    <div class="col-md-4">
        <div class="content-card h-100">
            <div class="card-header-custom">
                <i class="bi bi-camera-video text-warning"></i>
                <h6>Top 10 Meetings</h6>
            </div>
            <div class="card-body-custom p-0">
                <?php if (empty($stats['top_meetings'])): ?>
                    <div class="empty-state py-4"><span class="text-muted small">Keine Daten</span></div>
                <?php else: ?>
                <table class="data-table" style="margin-bottom:0;">
                    <thead>
                        <tr>
                            <th>Nutzer</th>
                            <th class="text-end" style="width:70px;">Meetings</th>
                            <th style="width:80px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($stats['top_meetings'] as $r): ?>
                            <?php $pct = (int)min(100, round($r['meetingCount'] / $maxMeetings * 100)); ?>
                            <tr>
                                <td style="font-size:12px; max-width:140px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                                    <?= $e($r['upn']) ?>
                                </td>
                                <td class="text-end" style="font-size:12px; font-weight:500;"><?= number_format($r['meetingCount']) ?></td>
                                <td>
                                    <div class="progress-custom" style="margin-bottom:0;">
                                        <div class="bar" style="width:<?= $pct ?>%; background:#d97706;"></div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </div>
    </div>

</div>

<!-- Full User Table -->
<div class="content-card">
    <div class="table-toolbar">
        <input type="text" id="tuSearch" class="search-box" placeholder="Nutzer suchen…">
        <div class="ms-3 d-flex gap-2 align-items-center">
            <label class="text-muted small mb-0">Filter:</label>
            <button class="btn btn-sm btn-outline-secondary active" data-filter="all" onclick="tuFilter(this,'all')">Alle</button>
            <button class="btn btn-sm btn-outline-success" data-filter="active" onclick="tuFilter(this,'active')">Aktiv</button>
            <button class="btn btn-sm btn-outline-warning" data-filter="inactive" onclick="tuFilter(this,'inactive')">Inaktiv</button>
        </div>
    </div>

    <div class="table-responsive">
        <table class="data-table" id="tuTable">
            <thead>
                <tr>
                    <th>UPN</th>
                    <th class="text-end">Team-Chats</th>
                    <th class="text-end">Privat-Chats</th>
                    <th class="text-end">Anrufe</th>
                    <th class="text-end">Meetings</th>
                    <th>Letzte Aktivität</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $r): ?>
                    <tr data-status="<?= empty($r['lastActivity']) ? 'inactive' : 'active' ?>">
                        <td style="font-size:13px; font-weight:500;"><?= $e($r['upn']) ?></td>
                        <td class="text-end" style="font-size:13px;"><?= number_format($r['teamChatMessages']) ?></td>
                        <td class="text-end" style="font-size:13px;"><?= number_format($r['privateChatMessages']) ?></td>
                        <td class="text-end" style="font-size:13px;"><?= number_format($r['callCount']) ?></td>
                        <td class="text-end" style="font-size:13px;"><?= number_format($r['meetingCount']) ?></td>
                        <td style="font-size:12px;">
                            <?php if (!empty($r['lastActivity'])): ?>
                                <span class="badge-success"><?= $e($r['lastActivity']) ?></span>
                            <?php else: ?>
                                <span class="badge-secondary">–</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php endif; ?>

<script>
initTableSearch('tuSearch', 'tuTable');

var tuCurrentFilter = 'all';

function tuFilter(btn, status) {
    tuCurrentFilter = status;
    // Update active button
    document.querySelectorAll('[data-filter]').forEach(function(b) {
        b.classList.remove('active');
    });
    btn.classList.add('active');

    var rows = document.querySelectorAll('#tuTable tbody tr');
    rows.forEach(function(row) {
        var rowStatus = row.getAttribute('data-status');
        if (status === 'all' || rowStatus === status) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}
</script>
