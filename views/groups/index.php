<?php use App\Core\View; use App\Modules\Groups\GroupsService; $e = fn($v) => View::escape($v); ?>

<div class="row g-3 mb-4">
    <div class="col-sm-4">
        <div class="metric-card">
            <div class="metric-label">Gruppen gesamt</div>
            <div class="metric-value"><?= number_format(count($groups)) ?></div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="metric-card">
            <div class="metric-label">M365-Gruppen</div>
            <div class="metric-value"><?= count(array_filter($groups, fn($g) => in_array('Unified', $g['groupTypes'] ?? []))) ?></div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="metric-card">
            <div class="metric-label">Sicherheitsgruppen</div>
            <div class="metric-value"><?= count(array_filter($groups, fn($g) => ($g['securityEnabled'] ?? false) && !in_array('Unified', $g['groupTypes'] ?? []))) ?></div>
        </div>
    </div>
</div>

<div class="content-card">
    <div class="table-toolbar">
        <input type="text" id="grpSearch" class="search-box" placeholder="Gruppe suchen…">
        <?php if (\App\Auth\LocalAuth::isAdmin()): ?>
            <button type="button" class="btn btn-sm btn-primary ms-2" data-bs-toggle="modal" data-bs-target="#createGroupModal">
                <i class="bi bi-plus-circle me-1"></i> Gruppe anlegen
            </button>
        <?php endif; ?>
    </div>
    <div class="table-responsive">
        <table class="data-table" id="grpTable">
            <thead>
                <tr><th>Name</th><th>Typ</th><th>E-Mail</th><th>Erstellt</th><th></th></tr>
            </thead>
            <tbody>
                <?php foreach ($groups as $g): ?>
                    <?php $type = GroupsService::getType($g); ?>
                    <tr>
                        <td class="fw-medium"><?= $e($g['displayName'] ?? '') ?></td>
                        <td>
                            <?php if ($type === 'M365'): ?>
                                <span class="badge-info">M365</span>
                            <?php elseif ($type === 'Security'): ?>
                                <span class="badge-neutral">Security</span>
                            <?php else: ?>
                                <span class="badge-warning"><?= $e($type) ?></span>
                            <?php endif; ?>
                        </td>
                        <td style="font-size:12px;color:#6b7280;"><?= $e($g['mail'] ?? '') ?></td>
                        <td style="font-size:12px;color:#6b7280;">
                            <?= isset($g['createdDateTime']) ? date('d.m.Y', strtotime($g['createdDateTime'])) : '–' ?>
                        </td>
                        <td>
                            <a href="/groups/<?= $e($g['id']) ?>" class="btn btn-sm btn-link py-0" style="font-size:12px;">Detail</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<script>initTableSearch('grpSearch', 'grpTable');</script>

<?php if (\App\Auth\LocalAuth::isAdmin()): ?>
<div class="modal fade" id="createGroupModal" tabindex="-1" aria-labelledby="createGroupModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="/groups/create">
                <?= \App\Core\Csrf::field() ?>
                <div class="modal-header">
                    <h5 class="modal-title" id="createGroupModalLabel"><i class="bi bi-people me-2"></i>Gruppe anlegen</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Schließen"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="cgDisplayName" class="form-label">Anzeigename <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="cgDisplayName" name="displayName" required placeholder="z.B. Marketing Team">
                    </div>
                    <div class="mb-3">
                        <label for="cgDescription" class="form-label">Beschreibung</label>
                        <textarea class="form-control" id="cgDescription" name="description" rows="2" placeholder="Optionale Beschreibung der Gruppe"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="cgType" class="form-label">Gruppentyp</label>
                        <select class="form-select" id="cgType" name="type">
                            <option value="m365">M365-Gruppe</option>
                            <option value="security">Sicherheitsgruppe</option>
                            <option value="mail_security">E-Mail-aktivierte Sicherheitsgruppe</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="cgMailNickname" class="form-label">Mail-Alias</label>
                        <input type="text" class="form-control" id="cgMailNickname" name="mailNickname" placeholder="Wird automatisch generiert">
                        <div class="form-text">Nur Kleinbuchstaben, Zahlen und Bindestriche. Leer lassen für automatische Generierung.</div>
                    </div>
                    <div class="alert alert-info py-2 mb-0" style="font-size:13px;">
                        <i class="bi bi-info-circle me-1"></i>
                        M365-Gruppen können ein Team in Microsoft Teams erhalten. Sicherheitsgruppen werden für Berechtigungen genutzt.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Abbrechen</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-plus-circle me-1"></i>Gruppe erstellen</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>
