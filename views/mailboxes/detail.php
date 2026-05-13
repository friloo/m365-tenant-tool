<?php use App\Core\View; $e = fn($v) => View::escape($v); ?>

<div class="mb-3">
    <a href="/mailboxes" class="text-muted text-decoration-none small">
        <i class="bi bi-arrow-left me-1"></i>Zurück zu Postfächer
    </a>
</div>

<?php if (!empty($flash)): ?>
    <div class="alert alert-success alert-dismissible mb-3">
        <i class="bi bi-check-circle me-2"></i><?= $e($flash) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>
<?php if (!empty($error)): ?>
    <div class="alert alert-danger alert-dismissible mb-3">
        <i class="bi bi-exclamation-triangle me-2"></i><?= $e($error) ?>
        <?php if (str_contains($error ?? '', 'MailboxSettings.ReadWrite')): ?>
            <br><small class="mt-1 d-block">
                Bitte erteilen Sie in der Azure App-Registrierung die Berechtigung
                <strong>MailboxSettings.ReadWrite</strong> (Application) und genehmigen Sie sie als Administrator.
            </small>
        <?php endif; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php
$userId      = $detail['id'] ?? '';
$displayName = $detail['displayName'] ?? '';
$upn         = $detail['userPrincipalName'] ?? '';
$mail        = $detail['mail'] ?? '';
$jobTitle    = $detail['jobTitle'] ?? '';
$department  = $detail['department'] ?? '';
$enabled     = $detail['accountEnabled'] ?? true;
$fwdAddr     = $detail['forwardingSmtpAddress'] ?? '';
$autoReplies = $detail['automaticRepliesSetting'] ?? [];
$autoStatus  = $autoReplies['status'] ?? 'disabled';
$autoActive  = ($autoStatus === 'alwaysEnabled' || $autoStatus === 'scheduled');
$autoMsg     = $autoReplies['internalReplyMessage'] ?? '';
$tz          = $detail['timeZone'] ?? '';
?>

<div class="row g-3">

    <!-- ── Section 1: Info card ──────────────────────────────────────────────── -->
    <div class="col-lg-4">
        <div class="content-card mb-3">
            <div class="card-body-custom text-center py-4">
                <div style="width:72px;height:72px;border-radius:50%;background:#e3f0fb;display:inline-flex;align-items:center;justify-content:center;font-size:1.8rem;font-weight:700;color:#0078d4;margin-bottom:12px;">
                    <?= $e(strtoupper(substr($displayName ?: '?', 0, 1))) ?>
                </div>
                <h5 class="mb-1"><?= $e($displayName) ?></h5>
                <p class="text-muted mb-0 small"><?= $e($upn) ?></p>
                <?php if ($mail && $mail !== $upn): ?>
                    <p class="text-muted mb-0 small"><?= $e($mail) ?></p>
                <?php endif; ?>
                <div class="mt-3">
                    <?php if ($enabled): ?>
                        <span class="badge-enabled">Aktiv</span>
                    <?php else: ?>
                        <span class="badge-disabled">Deaktiviert</span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card-body-custom border-top">
                <table class="table table-sm mb-0">
                    <?php
                    $props = [
                        'Titel'      => $jobTitle,
                        'Abteilung'  => $department,
                        'Zeitzone'   => $tz,
                    ];
                    foreach ($props as $label => $val):
                        if ($val === '' || $val === null) continue;
                    ?>
                        <tr>
                            <td class="text-muted small"><?= $e($label) ?></td>
                            <td class="small fw-medium"><?= $e($val) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        </div>
    </div>

    <!-- ── Right column ───────────────────────────────────────────────────────── -->
    <div class="col-lg-8">

        <!-- ── Section 2: Weiterleitung ───────────────────────────────────────── -->
        <div class="content-card mb-3">
            <div class="card-header-custom">
                <i class="bi bi-forward text-warning"></i>
                <h6>E-Mail-Weiterleitung</h6>
            </div>
            <div class="card-body-custom">

                <!-- Current forwarding status -->
                <div class="mb-3">
                    <?php if ($fwdAddr !== ''): ?>
                        <p class="mb-1 small">
                            <strong>Status:</strong>
                            <span class="badge-warning badge-pill ms-1">
                                <i class="bi bi-forward-fill me-1"></i>Aktiv
                            </span>
                        </p>
                        <p class="mb-0 small text-muted">
                            Weitergeleitet an: <strong><?= $e($fwdAddr) ?></strong>
                        </p>
                    <?php else: ?>
                        <p class="mb-0 small text-muted">
                            <i class="bi bi-dash-circle me-1"></i>Keine Weiterleitung aktiv.
                        </p>
                    <?php endif; ?>
                </div>

                <!-- Set / update forwarding -->
                <form method="post" action="/mailboxes/<?= $e($userId) ?>/forwarding" class="mb-2">
                    <?= \App\Core\Csrf::field() ?>
                    <div class="d-flex gap-2 align-items-center flex-wrap">
                        <input type="email" name="forward_to"
                               value="<?= $e($fwdAddr) ?>"
                               class="form-control form-control-sm"
                               style="max-width:320px;"
                               placeholder="ziel@beispiel.de">
                        <button type="submit" class="btn btn-sm btn-primary text-nowrap">
                            <i class="bi bi-save me-1"></i>Speichern
                        </button>
                    </div>
                </form>

                <?php if ($fwdAddr !== ''): ?>
                <!-- Remove forwarding -->
                <form method="post" action="/mailboxes/<?= $e($userId) ?>/forwarding"
                      onsubmit="return confirm('Weiterleitung wirklich entfernen?')">
                    <?= \App\Core\Csrf::field() ?>
                    <input type="hidden" name="forward_to" value="">
                    <button type="submit" class="btn btn-sm btn-outline-danger">
                        <i class="bi bi-x-circle me-1"></i>Weiterleitung entfernen
                    </button>
                </form>
                <?php endif; ?>

                <p class="mt-2 mb-0 text-muted" style="font-size:11px;">
                    <i class="bi bi-info-circle me-1"></i>
                    Erfordert <code>MailboxSettings.ReadWrite</code>-Berechtigung in der Azure App.
                </p>
            </div>
        </div>

        <!-- ── Section 3: Abwesenheitsnotiz ───────────────────────────────────── -->
        <div class="content-card mb-3">
            <div class="card-header-custom">
                <i class="bi bi-reply-all text-info"></i>
                <h6>Abwesenheitsnotiz (Auto-Reply)</h6>
            </div>
            <div class="card-body-custom">

                <!-- Current auto-reply status -->
                <div class="mb-3">
                    <span class="small me-2"><strong>Status:</strong></span>
                    <?php if ($autoActive): ?>
                        <span class="badge-success badge-pill">
                            <i class="bi bi-check-circle me-1"></i>Aktiv
                        </span>
                        <?php if ($autoMsg !== ''): ?>
                            <div class="mt-2">
                                <label class="form-label small text-muted">Aktuelle Nachricht:</label>
                                <textarea class="form-control form-control-sm" rows="3" readonly
                                          style="font-size:12px;background:#f9fafb;"><?= $e($autoMsg) ?></textarea>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <span class="badge-neutral badge-pill">
                            <i class="bi bi-dash-circle me-1"></i>Inaktiv
                        </span>
                    <?php endif; ?>
                </div>

                <!-- Toggle form -->
                <form method="post" action="/mailboxes/<?= $e($userId) ?>/auto-reply">
                    <?= \App\Core\Csrf::field() ?>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="auto_reply_enabled"
                               id="autoReplyEnabled" <?= $autoActive ? 'checked' : '' ?>>
                        <label class="form-check-label small" for="autoReplyEnabled">
                            Abwesenheitsnotiz aktivieren
                        </label>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small text-muted">Nachricht (intern &amp; extern):</label>
                        <textarea class="form-control form-control-sm" name="auto_reply_message"
                                  rows="4" placeholder="Ich bin derzeit nicht erreichbar…"
                                  style="font-size:13px;"><?= $e($autoMsg) ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="bi bi-save me-1"></i>Speichern
                    </button>
                </form>

                <p class="mt-2 mb-0 text-muted" style="font-size:11px;">
                    <i class="bi bi-info-circle me-1"></i>
                    Erfordert <code>MailboxSettings.ReadWrite</code>-Berechtigung in der Azure App.
                </p>
            </div>
        </div>

        <!-- ── Section 4: Postfachordner (collapsible) ───────────────────────── -->
        <div class="content-card">
            <div class="card-header-custom" style="cursor:pointer;" data-bs-toggle="collapse"
                 data-bs-target="#foldersCollapse" aria-expanded="false" aria-controls="foldersCollapse">
                <i class="bi bi-folder2 text-secondary"></i>
                <h6 class="mb-0">Postfachordner</h6>
                <i class="bi bi-chevron-down ms-auto" id="foldersChevron"
                   style="transition:transform 0.2s;"></i>
            </div>
            <div class="collapse" id="foldersCollapse">
                <?php if (empty($folders)): ?>
                    <div class="card-body-custom">
                        <div class="empty-state py-3">
                            <i class="bi bi-folder-x text-muted" style="font-size:2rem;"></i>
                            <p class="mt-2 mb-0 text-muted small">
                                Keine Ordner verfügbar oder fehlende Berechtigung (<code>Mail.Read</code>).
                            </p>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="data-table" style="font-size:13px;">
                            <thead>
                                <tr>
                                    <th>Ordner</th>
                                    <th class="text-end">Gesamt</th>
                                    <th class="text-end">Ungelesen</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($folders as $folder): ?>
                                    <tr>
                                        <td>
                                            <i class="bi bi-folder me-1 text-muted"></i>
                                            <?= $e($folder['displayName'] ?? '') ?>
                                        </td>
                                        <td class="text-end"><?= number_format((int)($folder['totalItemCount'] ?? 0)) ?></td>
                                        <td class="text-end">
                                            <?php $unread = (int)($folder['unreadItemCount'] ?? 0); ?>
                                            <?php if ($unread > 0): ?>
                                                <span class="badge-info badge-pill"><?= number_format($unread) ?></span>
                                            <?php else: ?>
                                                <span class="text-muted">0</span>
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

        <!-- ── Section 5: Kalender-Berechtigungen ───────────────────────────── -->
        <div class="content-card mt-3">
            <div class="card-header-custom">
                <i class="bi bi-calendar-check text-primary"></i>
                <h6>Kalender-Berechtigungen</h6>
            </div>
            <div class="card-body-custom">
                <?php if (empty($calendarPermissions)): ?>
                    <div class="alert alert-info py-2 px-3 mb-0" style="font-size:13px;">
                        <i class="bi bi-info-circle me-1"></i>
                        Kalenderberechtigungen konnten nicht abgerufen werden. Dies erfordert entweder
                        delegierte Berechtigungen (<code>Calendars.Read</code>) oder den Exchange
                        Admin-Zugriff.
                        <a href="https://admin.exchange.microsoft.com" target="_blank" rel="noopener"
                           class="ms-1">&rarr; Exchange Admin Center öffnen</a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="data-table" style="font-size:13px;">
                            <thead>
                                <tr>
                                    <th>Benutzer</th>
                                    <th>Rolle</th>
                                    <th>Intern</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($calendarPermissions as $perm):
                                    $emailName = $perm['emailAddress']['name'] ?? '';
                                    $emailAddr = $perm['emailAddress']['address'] ?? '';
                                    $role      = $perm['role'] ?? '';
                                    $isInside  = (bool)($perm['isInsideOrganization'] ?? false);

                                    $roleBadge = match (strtolower($role)) {
                                        'owner'       => 'badge-info',
                                        'write', 'editor' => 'badge-success',
                                        'read', 'reviewer'  => 'badge-secondary',
                                        'freebusyread' => 'badge-neutral',
                                        default        => 'badge-neutral',
                                    };
                                ?>
                                <tr>
                                    <td>
                                        <span class="fw-medium"><?= $e($emailName) ?></span>
                                        <?php if ($emailAddr && $emailAddr !== $emailName): ?>
                                            <br><span class="text-muted" style="font-size:11px;"><?= $e($emailAddr) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="<?= $e($roleBadge) ?> badge-pill">
                                            <?= $e($role) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($isInside): ?>
                                            <span class="badge-enabled badge-pill">Ja</span>
                                        <?php else: ?>
                                            <span class="badge-neutral badge-pill">Nein</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <p class="mt-2 mb-0 text-muted" style="font-size:11px;">
                        <i class="bi bi-info-circle me-1"></i>
                        Vollzugriff (Full Access) und &bdquo;Senden als&ldquo;-Berechtigungen werden über das
                        <a href="https://admin.exchange.microsoft.com" target="_blank" rel="noopener">Exchange Admin Center</a>
                        verwaltet.
                    </p>
                <?php endif; ?>
            </div>
        </div>

    </div><!-- /col-lg-8 -->
</div><!-- /row -->

<script>
// Rotate chevron when folders collapse is toggled
(function () {
    var el = document.getElementById('foldersCollapse');
    var chevron = document.getElementById('foldersChevron');
    if (!el || !chevron) return;
    el.addEventListener('show.bs.collapse', function () {
        chevron.style.transform = 'rotate(180deg)';
    });
    el.addEventListener('hide.bs.collapse', function () {
        chevron.style.transform = 'rotate(0deg)';
    });
})();
</script>
