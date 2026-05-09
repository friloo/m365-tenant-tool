<?php use App\Core\View; $e = fn($v) => View::escape($v); ?>

<div class="mb-3">
    <a href="/groups" class="text-muted text-decoration-none small">← Zurück zu Gruppen</a>
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
                <h5 class="mb-1"><?= $e($group['displayName'] ?? '') ?></h5>
                <?php if (!empty($group['mail'])): ?>
                    <p class="text-muted small mb-2"><?= $e($group['mail']) ?></p>
                <?php endif; ?>
                <?php if (!empty($group['description'])): ?>
                    <p class="text-muted small"><?= $e($group['description']) ?></p>
                <?php endif; ?>
                <div class="mt-3">
                    <div class="text-muted small mb-1">Eigentümer</div>
                    <?php foreach ($owners as $o): ?>
                        <div class="small fw-medium"><?= $e($o['displayName'] ?? '') ?></div>
                        <div class="text-muted" style="font-size:11px;"><?= $e($o['userPrincipalName'] ?? '') ?></div>
                    <?php endforeach; ?>
                    <?php if (empty($owners)): ?>
                        <span class="text-muted small">Kein Eigentümer</span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Add member -->
            <div class="card-body-custom border-top">
                <h6 class="small text-muted text-uppercase mb-3">Mitglied hinzufügen</h6>
                <form method="post" action="/groups/<?= $e($group['id']) ?>/add-member">
                    <div class="mb-2">
                        <input type="text" name="user_search" id="userSearchInput" class="form-control form-control-sm"
                               placeholder="Benutzer-ID oder UPN…" autocomplete="off">
                        <div class="form-text">Entra-Objekt-ID oder UPN eingeben</div>
                    </div>
                    <input type="hidden" name="user_id" id="memberUserId">
                    <button type="submit" class="btn btn-sm btn-primary w-100"
                            onclick="document.getElementById('memberUserId').value=document.getElementById('userSearchInput').value">
                        <i class="bi bi-person-plus me-1"></i> Hinzufügen
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="content-card">
            <div class="card-header-custom">
                <i class="bi bi-people text-primary"></i>
                <h6>Mitglieder (<?= count($members) ?>)</h6>
            </div>
            <div class="table-toolbar">
                <input type="text" id="memberSearch" class="search-box" placeholder="Mitglied suchen…">
            </div>
            <div class="table-responsive">
                <table class="data-table" id="memberTable">
                    <thead><tr><th>Name</th><th>UPN</th><th></th></tr></thead>
                    <tbody>
                        <?php foreach ($members as $m): ?>
                            <tr>
                                <td class="fw-medium"><?= $e($m['displayName'] ?? '') ?></td>
                                <td style="font-size:12px;color:#6b7280;"><?= $e($m['userPrincipalName'] ?? '') ?></td>
                                <td>
                                    <form method="post" action="/groups/<?= $e($group['id']) ?>/remove-member/<?= $e($m['id']) ?>"
                                          onsubmit="return confirm('Mitglied entfernen?')" class="mb-0">
                                        <button type="submit" class="btn btn-xs btn-outline-danger py-0 px-2" style="font-size:11px;">
                                            <i class="bi bi-person-dash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($members)): ?>
                            <tr><td colspan="3" class="text-center text-muted">Keine Mitglieder</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script>initTableSearch('memberSearch', 'memberTable');</script>
