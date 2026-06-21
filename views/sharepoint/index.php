<?php use App\Core\View; $e = fn($v) => View::escape($v); ?>

<div class="row g-3 mb-4">
    <div class="col-sm-4">
        <div class="metric-card">
            <div class="metric-label"><?= te('Sites gesamt') ?></div>
            <div class="metric-value"><?= count($sites) ?></div>
        </div>
    </div>
</div>

<div class="content-card">
    <div class="table-toolbar">
        <input type="text" id="spSearch" class="search-box" placeholder="<?= te('Site suchen…') ?>">
    </div>
    <div class="table-responsive">
        <table class="data-table" id="spTable">
            <thead>
                <tr><th><?= te('Site-Name') ?></th><th>URL</th><th><?= te('Erstellt') ?></th><th></th></tr>
            </thead>
            <tbody>
                <?php foreach ($sites as $site): ?>
                    <tr>
                        <td class="fw-medium"><?= $e($site['displayName'] ?? $site['name'] ?? '') ?></td>
                        <td style="font-size:12px;">
                            <a href="<?= $e($site['webUrl'] ?? '#') ?>" target="_blank" class="text-decoration-none text-muted">
                                <?= $e(parse_url($site['webUrl'] ?? '', PHP_URL_PATH)) ?>
                                <i class="bi bi-box-arrow-up-right ms-1" style="font-size:10px;"></i>
                            </a>
                        </td>
                        <td style="font-size:12px;color:#6b7280;">
                            <?= isset($site['createdDateTime']) ? date('d.m.Y', strtotime($site['createdDateTime'])) : '–' ?>
                        </td>
                        <td>
                            <?php $siteId = urlencode($site['id'] ?? ''); ?>
                            <a href="/sharepoint/<?= $siteId ?>" class="btn btn-sm btn-link py-0" style="font-size:12px;">Libraries</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($sites)): ?>
                    <tr><td colspan="4" class="text-center text-muted py-4"><?= te('Keine Sites gefunden') ?></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<script>initTableSearch('spSearch', 'spTable');</script>
