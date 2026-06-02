<?php use App\Auth\LocalAuth; use App\Core\View; $e = fn($v) => View::escape($v); ?>

<?php if (!empty($flash)): ?>
    <div class="alert alert-success mb-3"><i class="bi bi-check-circle me-2"></i><?= $e($flash) ?></div>
<?php endif; ?>
<?php if (!empty($error)): ?>
    <div class="alert alert-danger mb-3"><i class="bi bi-exclamation-circle me-2"></i><?= $e($error) ?></div>
<?php endif; ?>
<?php if (!empty($diagnostic)): ?>
    <div class="alert alert-warning mb-3">
        <i class="bi bi-info-circle me-2"></i>
        <strong>Warum sehe ich nichts?</strong>
        <div class="small mt-1"><?= $e($diagnostic) ?></div>
    </div>
<?php endif; ?>

<?php if ($highRiskCount > 0): ?>
    <div class="alert alert-danger mb-4">
        <i class="bi bi-shield-x me-2"></i>
        <strong><?= $highRiskCount ?> Benutzer mit hohem Risiko!</strong>
        Diese Konten sollten sofort überprüft und gesichert werden.
    </div>
<?php endif; ?>

<!-- Stat Cards -->
<div class="row g-3 mb-4">
    <div class="col-sm-3">
        <div class="metric-card">
            <div class="metric-label">Risikobenutzer</div>
            <div class="metric-value" style="color:<?= count($riskyUsers) > 0 ? '#dc2626' : '#111827' ?>;">
                <?= count($riskyUsers) ?>
            </div>
            <div class="metric-sub">Aktuell gefährdet</div>
        </div>
    </div>
    <div class="col-sm-3">
        <div class="metric-card">
            <div class="metric-label">Hohes Risiko</div>
            <div class="metric-value" style="color:<?= $highRiskCount > 0 ? '#dc2626' : '#16a34a' ?>;">
                <?= $highRiskCount ?>
            </div>
            <div class="metric-sub">Kritische Benutzer</div>
        </div>
    </div>
    <div class="col-sm-3">
        <div class="metric-card">
            <div class="metric-label">Mittleres Risiko</div>
            <div class="metric-value" style="color:<?= $mediumRiskCount > 0 ? '#d97706' : '#111827' ?>;">
                <?= $mediumRiskCount ?>
            </div>
            <div class="metric-sub">Überwachungsbedarf</div>
        </div>
    </div>
    <div class="col-sm-3">
        <div class="metric-card">
            <div class="metric-label">Erkennungen (24h)</div>
            <div class="metric-value" style="color:<?= ($stats['last24h'] ?? 0) > 0 ? '#d97706' : '#111827' ?>;">
                <?= $stats['last24h'] ?? 0 ?>
            </div>
            <div class="metric-sub">Neue Risikoereignisse</div>
        </div>
    </div>
</div>

<!-- Tabs -->
<ul class="nav nav-tabs mb-3" id="riskyTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="users-tab" data-bs-toggle="tab" data-bs-target="#users-panel"
                type="button" role="tab">
            <i class="bi bi-person-exclamation me-1"></i>
            Risikobenutzer
            <span class="badge bg-<?= count($riskyUsers) > 0 ? 'danger' : 'secondary' ?> ms-1"><?= count($riskyUsers) ?></span>
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="detect-tab" data-bs-toggle="tab" data-bs-target="#detect-panel"
                type="button" role="tab">
            <i class="bi bi-radar me-1"></i>
            Erkennungen
            <span class="badge bg-secondary ms-1"><?= count($detections) ?></span>
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="signins-tab" data-bs-toggle="tab" data-bs-target="#signins-panel"
                type="button" role="tab">
            <i class="bi bi-box-arrow-in-right me-1"></i>
            Risiko-Anmeldungen
            <span class="badge bg-secondary ms-1"><?= count($signIns) ?></span>
        </button>
    </li>
</ul>

<div class="tab-content">

    <!-- Tab: Risikobenutzer -->
    <div class="tab-pane fade show active" id="users-panel" role="tabpanel">
        <div class="content-card">
            <div class="table-toolbar">
                <input type="text" id="riskyUsersSearch" class="search-box" placeholder="Benutzer suchen…">
            </div>
            <div class="table-responsive">
                <table class="data-table" id="riskyUsersTable">
                    <thead>
                        <tr>
                            <th>Benutzer</th>
                            <th>Risikostufe</th>
                            <th>Risikodetail</th>
                            <th>Zuletzt aktualisiert</th>
                            <?php if (LocalAuth::isAdmin()): ?>
                                <th>Aktionen</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($riskyUsers as $u):
                            $level     = strtolower($u['riskLevel'] ?? 'none');
                            $detail    = $u['riskDetail'] ?? '';
                            $updated   = $u['riskLastUpdatedDateTime'] ?? null;
                            $userId    = $u['id'] ?? '';
                        ?>
                        <tr>
                            <td>
                                <div style="font-size:13px;font-weight:500;"><?= $e($u['userDisplayName'] ?? '') ?></div>
                                <div style="font-size:11px;color:#9ca3af;"><?= $e($u['userPrincipalName'] ?? '') ?></div>
                            </td>
                            <td>
                                <?php if ($level === 'high'): ?>
                                    <span class="badge-danger">Hoch</span>
                                <?php elseif ($level === 'medium'): ?>
                                    <span class="badge-warning">Mittel</span>
                                <?php elseif ($level === 'low'): ?>
                                    <span class="badge-info">Niedrig</span>
                                <?php else: ?>
                                    <span class="badge-neutral"><?= $e($level ?: '–') ?></span>
                                <?php endif; ?>
                            </td>
                            <td style="font-size:12px;color:#6b7280;">
                                <?= $detail ? $e($detail) : '<span class="text-muted">–</span>' ?>
                            </td>
                            <td style="font-size:12px;color:#6b7280;white-space:nowrap;">
                                <?= $updated ? date('d.m.Y H:i', strtotime($updated)) : '–' ?>
                            </td>
                            <?php if (LocalAuth::isAdmin()): ?>
                            <td>
                                <div class="d-flex gap-1 flex-wrap">
                                    <form method="POST" action="/riskysignins/<?= $e($userId) ?>/confirm-compromised"
                                          onsubmit="return confirm('Benutzer als kompromittiert markieren?');">
                                        <?= \App\Core\Csrf::field() ?>
                                        <button type="submit" class="btn btn-sm btn-danger" style="font-size:11px;padding:2px 8px;">
                                            <i class="bi bi-exclamation-octagon me-1"></i>Kompromittiert
                                        </button>
                                    </form>
                                    <form method="POST" action="/riskysignins/<?= $e($userId) ?>/dismiss-risk"
                                          onsubmit="return confirm('Risiko für diesen Benutzer zurücksetzen?');">
                                        <?= \App\Core\Csrf::field() ?>
                                        <button type="submit" class="btn btn-sm btn-outline-secondary" style="font-size:11px;padding:2px 8px;">
                                            <i class="bi bi-check2 me-1"></i>Zurücksetzen
                                        </button>
                                    </form>
                                </div>
                            </td>
                            <?php endif; ?>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($riskyUsers)): ?>
                            <tr>
                                <td colspan="<?= LocalAuth::isAdmin() ? 5 : 4 ?>">
                                    <div class="empty-state">
                                        <i class="bi bi-shield-check"></i>
                                        <p>Keine Risikobenutzer gefunden — Berechtigungen prüfen (IdentityRiskyUser.Read.All)</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Tab: Erkennungen -->
    <div class="tab-pane fade" id="detect-panel" role="tabpanel">
        <div class="content-card">
            <div class="table-toolbar">
                <input type="text" id="detectSearch" class="search-box" placeholder="Erkennung suchen…">
            </div>
            <div class="table-responsive">
                <table class="data-table" id="detectTable">
                    <thead>
                        <tr>
                            <th>Zeitpunkt</th>
                            <th>Benutzer</th>
                            <th>Ereignistyp</th>
                            <th>Risikostufe</th>
                            <th>IP-Adresse</th>
                            <th>Standort</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($detections as $d):
                            $level    = strtolower($d['riskLevel'] ?? 'none');
                            $actTime  = $d['activityDateTime'] ?? null;
                            $loc      = $d['location'] ?? [];
                            $city     = $loc['city'] ?? '';
                            $country  = $loc['countryOrRegion'] ?? '';
                            $locStr   = trim("$city, $country", ', ');
                        ?>
                        <tr>
                            <td style="font-size:11px;color:#6b7280;white-space:nowrap;">
                                <?= $actTime ? date('d.m. H:i', strtotime($actTime)) : '–' ?>
                            </td>
                            <td>
                                <div style="font-size:13px;font-weight:500;"><?= $e($d['userDisplayName'] ?? '') ?></div>
                                <div style="font-size:11px;color:#9ca3af;"><?= $e($d['userPrincipalName'] ?? '') ?></div>
                            </td>
                            <td style="font-size:12px;">
                                <?= $e($service->formatRiskEventType($d['riskEventType'] ?? '')) ?>
                            </td>
                            <td>
                                <?php if ($level === 'high'): ?>
                                    <span class="badge-danger">Hoch</span>
                                <?php elseif ($level === 'medium'): ?>
                                    <span class="badge-warning">Mittel</span>
                                <?php elseif ($level === 'low'): ?>
                                    <span class="badge-info">Niedrig</span>
                                <?php elseif ($level === 'hidden'): ?>
                                    <span class="badge-neutral">Versteckt</span>
                                <?php else: ?>
                                    <span class="badge-neutral"><?= $e($level ?: '–') ?></span>
                                <?php endif; ?>
                            </td>
                            <td style="font-size:12px;color:#6b7280;font-family:monospace;">
                                <?= $e($d['ipAddress'] ?? '–') ?>
                            </td>
                            <td style="font-size:12px;color:#6b7280;">
                                <?= $locStr ? $e($locStr) : '–' ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($detections)): ?>
                            <tr>
                                <td colspan="6">
                                    <div class="empty-state">
                                        <i class="bi bi-radar"></i>
                                        <p>Keine Risikoerkennungen (IdentityRiskEvent.Read.All prüfen)</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Tab: Risiko-Anmeldungen -->
    <div class="tab-pane fade" id="signins-panel" role="tabpanel">
        <div class="content-card">
            <?php if (empty($signIns)): ?>
                <div class="card-body-custom">
                    <div class="empty-state">
                        <i class="bi bi-box-arrow-in-right"></i>
                        <p>Keine risikobehafteten Anmeldungen gefunden<br>
                        <span style="font-size:12px;color:#9ca3af;">AuditLog.Read.All und IdentityRiskEvent.Read.All Berechtigungen prüfen</span></p>
                    </div>
                </div>
            <?php else: ?>
                <div class="table-toolbar">
                    <input type="text" id="signinsSearch" class="search-box" placeholder="Anmeldung suchen…">
                </div>
                <div class="table-responsive">
                    <table class="data-table" id="signinsTable">
                        <thead>
                            <tr>
                                <th>Zeitpunkt</th>
                                <th>Benutzer</th>
                                <th>IP-Adresse</th>
                                <th>Standort</th>
                                <th>Risikozustand</th>
                                <th>Risikostufe</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($signIns as $s):
                                $riskState = $s['riskState'] ?? '';
                                $riskLevel = strtolower($s['riskLevelDuringSignIn'] ?? 'none');
                                $loc       = $s['location'] ?? [];
                                $city      = $loc['city'] ?? '';
                                $country   = $loc['countryOrRegion'] ?? '';
                                $locStr    = trim("$city, $country", ', ');
                                $created   = $s['createdDateTime'] ?? null;
                            ?>
                            <tr>
                                <td style="font-size:11px;color:#6b7280;white-space:nowrap;">
                                    <?= $created ? date('d.m. H:i', strtotime($created)) : '–' ?>
                                </td>
                                <td style="font-size:12px;"><?= $e($s['userPrincipalName'] ?? '') ?></td>
                                <td style="font-size:12px;font-family:monospace;color:#6b7280;"><?= $e($s['ipAddress'] ?? '–') ?></td>
                                <td style="font-size:12px;color:#6b7280;"><?= $locStr ? $e($locStr) : '–' ?></td>
                                <td>
                                    <?php if ($riskState === 'confirmedCompromised'): ?>
                                        <span class="badge-danger">Kompromittiert</span>
                                    <?php elseif ($riskState === 'atRisk'): ?>
                                        <span class="badge-warning">Gefährdet</span>
                                    <?php else: ?>
                                        <span class="badge-neutral"><?= $e($riskState ?: '–') ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($riskLevel === 'high'): ?>
                                        <span class="badge-danger">Hoch</span>
                                    <?php elseif ($riskLevel === 'medium'): ?>
                                        <span class="badge-warning">Mittel</span>
                                    <?php elseif ($riskLevel === 'low'): ?>
                                        <span class="badge-info">Niedrig</span>
                                    <?php else: ?>
                                        <span class="badge-neutral">–</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

</div>

<script>
initTableSearch('riskyUsersSearch', 'riskyUsersTable');
initTableSearch('detectSearch', 'detectTable');
<?php if (!empty($signIns)): ?>
initTableSearch('signinsSearch', 'signinsTable');
<?php endif; ?>
</script>
