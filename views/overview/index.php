<?php use App\Core\View; $e = fn($v) => View::escape($v); ?>

<div class="alert alert-info d-flex gap-3 mb-3">
    <i class="bi bi-grid-1x2 flex-shrink-0 mt-1" style="font-size:1.4rem;color:#0078d4;"></i>
    <div>
        <strong><?= te('Alle :count Module auf einen Blick.', ['count' => (int)$total]) ?></strong>
        <?= te('Diese Seite listet jeden Bereich des Tools gruppiert auf — nutze sie zum schnellen Einstieg oder die Suche oben, um direkt zu einem Modul zu springen.') ?>
    </div>
</div>

<input type="text" id="ovFilter" class="form-control mb-3" placeholder="<?= te('Module filtern …') ?>" autocomplete="off">

<div class="row g-3" id="ovGrid">
<?php foreach ($groups as $group): ?>
    <div class="col-lg-4 col-md-6 ov-group">
        <div class="content-card h-100">
            <div class="card-header-custom">
                <i class="bi bi-folder2-open text-primary"></i>
                <h6><?= $e($group['name']) ?></h6>
                <span class="ms-auto text-muted small"><?= count($group['items']) ?></span>
            </div>
            <div class="card-body-custom">
                <div class="d-flex flex-column gap-1">
                    <?php foreach ($group['items'] as $item): ?>
                        <a href="/<?= $e($item['route']) ?>"
                           class="d-flex align-items-center gap-2 text-decoration-none p-2 rounded ov-item"
                           data-label="<?= $e(mb_strtolower($item['label'])) ?>"
                           style="color:#1e293b;">
                            <span class="nav-icon"><i class="bi bi-<?= $e($item['icon']) ?>"></i></span>
                            <span><?= $e($item['label']) ?></span>
                            <?php if (!empty($item['admin'])): ?>
                                <span class="badge bg-light text-muted ms-auto" style="font-size:.65rem;"><?= te('Admin') ?></span>
                            <?php endif; ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>
</div>

<style>
.ov-item:hover { background:#f1f5f9; }
</style>
<script>
(function () {
    var input = document.getElementById('ovFilter');
    if (!input) return;
    input.addEventListener('input', function () {
        var q = this.value.trim().toLowerCase();
        document.querySelectorAll('.ov-group').forEach(function (grp) {
            var any = false;
            grp.querySelectorAll('.ov-item').forEach(function (it) {
                var match = !q || (it.dataset.label || '').indexOf(q) !== -1;
                it.style.display = match ? '' : 'none';
                if (match) any = true;
            });
            grp.style.display = any ? '' : 'none';
        });
    });
})();
</script>
