<?php use App\Core\View; $e = fn($v) => View::escape($v); ?>

<div class="mb-3">
    <a href="/groups" class="text-muted text-decoration-none small">← Zurück zu Gruppen</a>
</div>

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
                    <div class="text-muted small">Eigentümer</div>
                    <?php foreach ($owners as $o): ?>
                        <div class="small fw-medium"><?= $e($o['displayName'] ?? '') ?></div>
                        <div class="text-muted" style="font-size:11px;"><?= $e($o['userPrincipalName'] ?? '') ?></div>
                    <?php endforeach; ?>
                    <?php if (empty($owners)): ?>
                        <span class="text-muted small">Kein Eigentümer</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="content-card">
            <div class="card-header-custom">
                <i class="bi bi-people text-primary"></i>
                <h6>Mitglieder (<?= count($members) ?>)</h6>
            </div>
            <div class="table-responsive">
                <table class="data-table">
                    <thead><tr><th>Name</th><th>UPN</th></tr></thead>
                    <tbody>
                        <?php foreach ($members as $m): ?>
                            <tr>
                                <td class="fw-medium"><?= $e($m['displayName'] ?? '') ?></td>
                                <td style="font-size:12px;color:#6b7280;"><?= $e($m['userPrincipalName'] ?? '') ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($members)): ?>
                            <tr><td colspan="2" class="text-center text-muted">Keine Mitglieder</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
