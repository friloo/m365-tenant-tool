<?php use App\Core\View; use App\Auth\LocalAuth; $e = fn($v) => View::escape($v); ?>

<div class="mb-3">
    <a href="/groups" class="text-muted text-decoration-none small">← <?= te('Zurück zu Gruppen') ?></a>
</div>

<?php if (!empty($flash)): ?>
    <div class="alert alert-success alert-dismissible mb-3"><i class="bi bi-check-circle me-2"></i><?= $e($flash) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>
<?php if (!empty($error)): ?>
    <div class="alert alert-danger alert-dismissible mb-3"><i class="bi bi-exclamation-triangle me-2"></i><?= $e($error) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<div class="row g-3">
    <div class="col-lg-4">
        <div class="content-card">
            <div class="card-body-custom">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h5 class="mb-1"><?= $e($group['displayName'] ?? '') ?></h5>
                        <?php if (!empty($group['mail'])): ?>
                            <p class="text-muted small mb-2"><?= $e($group['mail']) ?></p>
                        <?php endif; ?>
                        <?php if (!empty($group['description'])): ?>
                            <p class="text-muted small"><?= $e($group['description']) ?></p>
                        <?php endif; ?>
                    </div>
                    <?php if (LocalAuth::isAdmin()): ?>
                        <div class="ms-2 flex-shrink-0">
                            <?php if (!empty($group['onPremisesSyncEnabled'])): ?>
                                <span data-bs-toggle="tooltip"
                                      title="<?= te('AD-synchronisierte Gruppen können hier nicht gelöscht werden') ?>">
                                    <button type="button" class="btn btn-sm btn-outline-danger" disabled>
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </span>
                            <?php else: ?>
                                <form method="post" action="/groups/<?= $e($group['id']) ?>/delete" class="mb-0"
                                      onsubmit="return confirm(this.dataset.confirm)"
                                      data-confirm="<?= te('Gruppe wirklich löschen? Diese Aktion kann nicht rückgängig gemacht werden.') ?>">
                                    <?= \App\Core\Csrf::field() ?>
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="<?= te('Gruppe löschen') ?>">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Add member -->
            <div class="card-body-custom border-top">
                <h6 class="small text-muted text-uppercase mb-3"><?= te('Mitglied hinzufügen') ?></h6>
                <form method="post" action="/groups/<?= $e($group['id']) ?>/add-member">
                    <?= \App\Core\Csrf::field() ?>
                    <div class="mb-2">
                        <input type="text" name="user_search" id="userSearchInput" class="form-control form-control-sm"
                               placeholder="<?= te('Benutzer-ID oder UPN…') ?>" autocomplete="off">
                        <div class="form-text"><?= te('Entra-Objekt-ID oder UPN eingeben') ?></div>
                    </div>
                    <input type="hidden" name="user_id" id="memberUserId">
                    <button type="submit" class="btn btn-sm btn-primary w-100"
                            onclick="document.getElementById('memberUserId').value=document.getElementById('userSearchInput').value">
                        <i class="bi bi-person-plus me-1"></i> <?= te('Hinzufügen') ?>
                    </button>
                </form>
            </div>
        </div>

        <!-- Owners card -->
        <div class="content-card mt-3">
            <div class="card-header-custom">
                <i class="bi bi-person-badge text-primary"></i>
                <h6><?= te('Besitzer') ?> (<?= count($owners) ?>)</h6>
            </div>

            <?php if (!empty($group['onPremisesSyncEnabled'])): ?>
                <div class="card-body-custom">
                    <div class="alert alert-info py-2 mb-0" style="font-size:13px;">
                        <i class="bi bi-info-circle me-1"></i>
                        <?= te('Diese Gruppe wird aus dem lokalen Active Directory synchronisiert. Besitzer werden möglicherweise dort verwaltet.') ?>
                    </div>
                </div>
            <?php endif; ?>

            <div class="card-body-custom p-0">
                <?php if (empty($owners)): ?>
                    <div class="empty-state py-3">
                        <i class="bi bi-person-badge"></i>
                        <p><?= te('Kein Besitzer') ?></p>
                    </div>
                <?php else: ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($owners as $o): ?>
                            <?php
                                $nameParts = explode(' ', trim($o['displayName'] ?? ''), 2);
                                $initials  = strtoupper(
                                    substr($nameParts[0] ?? '', 0, 1) .
                                    substr($nameParts[1] ?? '', 0, 1)
                                );
                            ?>
                            <li class="list-group-item d-flex align-items-center gap-2 px-3 py-2">
                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center flex-shrink-0"
                                     style="width:32px;height:32px;font-size:12px;font-weight:600;">
                                    <?= $e($initials ?: '?') ?>
                                </div>
                                <div class="flex-grow-1 overflow-hidden">
                                    <div class="fw-medium small text-truncate"><?= $e($o['displayName'] ?? '') ?></div>
                                    <div class="text-muted" style="font-size:11px;" class="text-truncate"><?= $e($o['userPrincipalName'] ?? '') ?></div>
                                </div>
                                <?php if (LocalAuth::isAdmin()): ?>
                                    <form method="post"
                                          action="/groups/<?= $e($group['id']) ?>/remove-owner/<?= $e($o['id']) ?>"
                                          onsubmit="return confirm(<?= htmlspecialchars(json_encode(t('Besitzer entfernen?'), JSON_UNESCAPED_UNICODE), ENT_QUOTES) ?>)" class="mb-0 flex-shrink-0">
                                        <?= \App\Core\Csrf::field() ?>
                                        <button type="submit" class="btn btn-xs btn-outline-danger py-0 px-2" style="font-size:11px;" title="<?= te('Besitzer entfernen') ?>">
                                            <i class="bi bi-person-dash"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>

            <?php if (LocalAuth::isAdmin()): ?>
                <div class="card-body-custom border-top">
                    <h6 class="small text-muted text-uppercase mb-3"><?= te('Besitzer hinzufügen') ?></h6>
                    <form method="post" action="/groups/<?= $e($group['id']) ?>/add-owner">
                        <?= \App\Core\Csrf::field() ?>
                        <div class="mb-2">
                            <input type="text" name="user_search" id="ownerSearchInput" class="form-control form-control-sm"
                                   placeholder="<?= te('Benutzer-ID oder UPN…') ?>" autocomplete="off">
                            <div class="form-text"><?= te('Entra-Objekt-ID oder UPN eingeben') ?></div>
                        </div>
                        <input type="hidden" name="user_id" id="ownerUserId">
                        <button type="submit" class="btn btn-sm btn-outline-primary w-100"
                                onclick="document.getElementById('ownerUserId').value=document.getElementById('ownerSearchInput').value">
                            <i class="bi bi-person-plus me-1"></i> <?= te('Besitzer hinzufügen') ?>
                        </button>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="content-card">
            <div class="card-header-custom">
                <i class="bi bi-people text-primary"></i>
                <h6><?= te('Mitglieder') ?> (<?= count($members) ?>)</h6>
            </div>
            <div class="table-toolbar">
                <input type="text" id="memberSearch" class="search-box" placeholder="<?= te('Mitglied suchen…') ?>">
            </div>
            <div class="table-responsive">
                <table class="data-table" id="memberTable">
                    <thead><tr><th><?= te('Name') ?></th><th><?= te('UPN') ?></th><th></th></tr></thead>
                    <tbody>
                        <?php foreach ($members as $m): ?>
                            <tr>
                                <td class="fw-medium"><?= $e($m['displayName'] ?? '') ?></td>
                                <td style="font-size:12px;color:#6b7280;"><?= $e($m['userPrincipalName'] ?? '') ?></td>
                                <td>
                                    <form method="post" action="/groups/<?= $e($group['id']) ?>/remove-member/<?= $e($m['id']) ?>"
                                          onsubmit="return confirm(<?= htmlspecialchars(json_encode(t('Mitglied entfernen?'), JSON_UNESCAPED_UNICODE), ENT_QUOTES) ?>)" class="mb-0">
                                        <?= \App\Core\Csrf::field() ?>
                                        <button type="submit" class="btn btn-xs btn-outline-danger py-0 px-2" style="font-size:11px;">
                                            <i class="bi bi-person-dash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($members)): ?>
                            <tr><td colspan="3" class="text-center text-muted"><?= te('Keine Mitglieder') ?></td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script>
initTableSearch('memberSearch', 'memberTable');
// Bootstrap tooltip init for disabled delete button
document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
    new bootstrap.Tooltip(el);
});
</script>
