<?php use App\Auth\LocalAuth; use App\Core\View; $e = fn($v) => View::escape($v); ?>

<?php if (!empty($flash)): ?>
    <div class="alert alert-success mb-3"><i class="bi bi-check-circle me-2"></i><?= $e($flash) ?></div>
<?php endif; ?>
<?php if (!empty($error)): ?>
    <div class="alert alert-danger mb-3"><i class="bi bi-exclamation-circle me-2"></i><?= $e($error) ?></div>
<?php endif; ?>

<!-- Days filter -->
<div class="d-flex align-items-center gap-2 mb-4">
    <label for="staleDaysSelect" style="font-size:13px;font-weight:500;white-space:nowrap;">Inaktiv seit mehr als:</label>
    <form method="GET" action="/staleaccounts" class="d-flex align-items-center gap-2">
        <select name="stale_days" id="staleDaysSelect" class="form-select form-select-sm" style="width:auto;"
                onchange="this.form.submit()">
            <?php foreach ([30, 60, 90, 120, 180] as $d): ?>
                <option value="<?= $d ?>" <?= $d === $days ? 'selected' : '' ?>><?= $d ?> Tage</option>
            <?php endforeach; ?>
        </select>
    </form>
    <a href="/staleaccounts/export?stale_days=<?= $days ?>" class="btn btn-sm btn-outline-secondary ms-auto">
        <i class="bi bi-download me-1"></i>CSV Export
    </a>
</div>

<?php if ($stats['withLicenses'] > 0): ?>
    <div class="alert alert-warning mb-4">
        <i class="bi bi-currency-dollar me-2"></i>
        <strong><?= $stats['withLicenses'] ?> Benutzer mit Lizenzen sind seit &gt;<?= $days ?> Tagen inaktiv</strong> —
        geschätzte <?= $stats['costRisk'] ?> Lizenz-Einheiten könnten freigegeben werden.
    </div>
<?php endif; ?>

<!-- Stat Cards -->
<div class="row g-3 mb-4">
    <div class="col-sm-3">
        <div class="metric-card">
            <div class="metric-label">Inaktive Konten</div>
            <div class="metric-value" style="color:<?= $stats['total'] > 0 ? '#d97706' : '#111827' ?>;">
                <?= $stats['total'] ?>
            </div>
            <div class="metric-sub">Seit &gt;<?= $days ?> Tagen</div>
        </div>
    </div>
    <div class="col-sm-3">
        <div class="metric-card">
            <div class="metric-label">Mit Lizenzen</div>
            <div class="metric-value" style="color:<?= $stats['withLicenses'] > 0 ? '#dc2626' : '#16a34a' ?>;">
                <?= $stats['withLicenses'] ?>
            </div>
            <div class="metric-sub">Verschwendetes Budget</div>
        </div>
    </div>
    <div class="col-sm-3">
        <div class="metric-card">
            <div class="metric-label">Nie angemeldet</div>
            <div class="metric-value" style="color:<?= $stats['neverSignedIn'] > 0 ? '#d97706' : '#111827' ?>;">
                <?= $stats['neverSignedIn'] ?>
            </div>
            <div class="metric-sub">Kein Login-Verlauf</div>
        </div>
    </div>
    <div class="col-sm-3">
        <div class="metric-card">
            <div class="metric-label">Kostenrisiko</div>
            <div class="metric-value" style="color:<?= $stats['costRisk'] > 0 ? '#dc2626' : '#16a34a' ?>;">
                <?= $stats['costRisk'] ?>
            </div>
            <div class="metric-sub">Lizenzeinheiten freigab.</div>
        </div>
    </div>
</div>

<!-- Tabs -->
<ul class="nav nav-tabs mb-3" id="staleTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="stale-tab" data-bs-toggle="tab" data-bs-target="#stale-panel"
                type="button" role="tab">
            <i class="bi bi-person-slash me-1"></i>
            Inaktive Konten
            <span class="badge bg-secondary ms-1"><?= $stats['total'] ?></span>
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="log-tab" data-bs-toggle="tab" data-bs-target="#log-panel"
                type="button" role="tab">
            <i class="bi bi-journal-text me-1"></i>
            Protokoll
            <span class="badge bg-secondary ms-1"><?= count($log) ?></span>
        </button>
    </li>
</ul>

<div class="tab-content">

    <!-- Tab: Inaktive Konten -->
    <div class="tab-pane fade show active" id="stale-panel" role="tabpanel">
        <div class="content-card">
            <div class="table-toolbar">
                <input type="text" id="staleSearch" class="search-box" placeholder="Benutzer suchen…">
            </div>
            <div class="table-responsive">
                <table class="data-table" id="staleTable">
                    <thead>
                        <tr>
                            <th style="width:40px;"></th>
                            <th>Name</th>
                            <th>UPN</th>
                            <th>Abteilung</th>
                            <th>Inaktiv (Tage)</th>
                            <th>Lizenzen</th>
                            <th>Letzter Login</th>
                            <?php if (LocalAuth::isAdmin()): ?>
                                <th>Aktion</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $u):
                            $initials    = '';
                            $nameParts   = explode(' ', $u['displayName'] ?? '');
                            foreach (array_slice($nameParts, 0, 2) as $part) {
                                $initials .= strtoupper(mb_substr($part, 0, 1));
                            }
                            $hasLicense  = !empty($u['assignedLicenses']);
                            $licCount    = count($u['assignedLicenses'] ?? []);
                            $neverIn     = $u['neverSignedIn'] ?? false;
                            $daysVal     = $neverIn ? null : ($u['daysInactive'] ?? null);
                            $lastSignIn  = $u['signInActivity']['lastSignInDateTime'] ?? null;
                            $userId      = $u['id'] ?? '';
                        ?>
                        <tr>
                            <td>
                                <div style="width:32px;height:32px;border-radius:50%;background:#e5e7eb;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:600;color:#374151;">
                                    <?= $e($initials ?: '?') ?>
                                </div>
                            </td>
                            <td>
                                <div style="font-size:13px;font-weight:500;"><?= $e($u['displayName'] ?? '') ?></div>
                                <?php if (!empty($u['jobTitle'])): ?>
                                    <div style="font-size:11px;color:#9ca3af;"><?= $e($u['jobTitle']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td style="font-size:12px;color:#6b7280;"><?= $e($u['userPrincipalName'] ?? '') ?></td>
                            <td style="font-size:12px;color:#6b7280;"><?= $e($u['department'] ?? '–') ?></td>
                            <td>
                                <?php if ($neverIn): ?>
                                    <span class="badge-warning">Nie</span>
                                <?php elseif ($daysVal !== null): ?>
                                    <span class="badge-<?= $daysVal >= 180 ? 'danger' : ($daysVal >= 90 ? 'warning' : 'neutral') ?>">
                                        <?= $daysVal ?>d
                                    </span>
                                <?php else: ?>
                                    <span class="badge-neutral">–</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($hasLicense): ?>
                                    <span class="badge-warning"><?= $licCount ?> Lizenz<?= $licCount !== 1 ? 'en' : '' ?></span>
                                <?php else: ?>
                                    <span class="badge-neutral">Keine</span>
                                <?php endif; ?>
                            </td>
                            <td style="font-size:12px;color:#6b7280;white-space:nowrap;">
                                <?= $lastSignIn ? date('d.m.Y', strtotime($lastSignIn)) : '–' ?>
                            </td>
                            <?php if (LocalAuth::isAdmin()): ?>
                            <td>
                                <?php if ($hasLicense): ?>
                                    <form method="POST" action="/staleaccounts/<?= $e($userId) ?>/remove-license"
                                          onsubmit="return confirm('Alle Lizenzen für diesen Benutzer entfernen?');">
                                        <?= \App\Core\Csrf::field() ?>
                                        <button type="submit" class="btn btn-sm btn-warning" style="font-size:11px;padding:2px 8px;">
                                            <i class="bi bi-x-circle me-1"></i>Lizenzen entfernen
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <span class="text-muted" style="font-size:11px;">–</span>
                                <?php endif; ?>
                            </td>
                            <?php endif; ?>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($users)): ?>
                            <tr>
                                <td colspan="<?= LocalAuth::isAdmin() ? 8 : 7 ?>">
                                    <div class="empty-state">
                                        <i class="bi bi-person-check"></i>
                                        <p>Keine inaktiven Konten für den gewählten Zeitraum gefunden</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="content-card mt-3" style="padding:12px 16px;background:#f8fafc;border:1px dashed #cbd5e1;">
            <p style="font-size:12px;color:#64748b;margin:0;">
                <i class="bi bi-gear me-1"></i>
                <strong>Hinweis:</strong> Auto-Freigabe von Lizenzen für inaktive Konten kann in den
                <a href="/settings">Einstellungen</a> konfiguriert werden (Schlüssel: <code>stale_account_days</code>).
            </p>
        </div>
    </div>

    <!-- Tab: Protokoll -->
    <div class="tab-pane fade" id="log-panel" role="tabpanel">
        <div class="content-card">
            <?php if (empty($log)): ?>
                <div class="card-body-custom">
                    <div class="empty-state">
                        <i class="bi bi-journal-text"></i>
                        <p>Noch keine Aktionen protokolliert</p>
                    </div>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Zeitpunkt</th>
                                <th>Benutzer (UPN)</th>
                                <th>Aktion</th>
                                <th>Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($log as $entry):
                                $details = is_string($entry['details']) ? json_decode($entry['details'], true) : ($entry['details'] ?? []);
                                $action  = $entry['action'] ?? '';
                            ?>
                            <tr>
                                <td style="font-size:11px;color:#6b7280;white-space:nowrap;">
                                    <?= !empty($entry['created_at']) ? date('d.m.Y H:i', strtotime($entry['created_at'])) : '–' ?>
                                </td>
                                <td style="font-size:12px;"><?= $e($entry['user_upn'] ?? $entry['user_id'] ?? '') ?></td>
                                <td>
                                    <?php if ($action === 'license_removed'): ?>
                                        <span class="badge-warning">Lizenz entfernt</span>
                                    <?php elseif ($action === 'account_disabled'): ?>
                                        <span class="badge-disabled">Konto deaktiviert</span>
                                    <?php elseif ($action === 'skipped'): ?>
                                        <span class="badge-neutral">Übersprungen</span>
                                    <?php else: ?>
                                        <span class="badge-secondary"><?= $e($action) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td style="font-size:12px;color:#6b7280;">
                                    <?php if (!empty($details)): ?>
                                        <?php foreach ($details as $k => $v): ?>
                                            <span><?= $e($k) ?>: <?= $e(is_array($v) ? implode(', ', $v) : (string)$v) ?></span>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        –
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
initTableSearch('staleSearch', 'staleTable');
</script>
