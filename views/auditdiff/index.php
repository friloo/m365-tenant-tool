<?php
use App\Core\View;
use App\Core\Csrf;

$formatVal = function ($v) {
    if (is_bool($v))   return $v ? 'true' : 'false';
    if ($v === null)   return 'null';
    if (is_array($v))  return json_encode($v, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    return (string)$v;
};
?>
<div class="content-card mb-3">
    <h1 class="mb-2"><i class="bi bi-arrow-left-right"></i> Audit-Diff <?= \App\Core\Help::tip('audit_diff') ?></h1>
    <p class="text-muted">Vergleiche zwei Snapshots der Tenant-Einstellungen. Snapshots werden täglich automatisch erstellt (Cron-Job: <code>audit_diff_snapshot</code>) und können hier manuell ergänzt werden.</p>

    <?php $flash = \App\Core\Session::getFlash('success'); $err = \App\Core\Session::getFlash('error'); ?>
    <?php if ($flash): ?><div class="alert alert-success"><?= View::escape($flash) ?></div><?php endif; ?>
    <?php if ($err): ?><div class="alert alert-danger"><?= View::escape($err) ?></div><?php endif; ?>

    <form method="get" action="/auditdiff" class="row g-2 align-items-end">
        <div class="col-md-5">
            <label class="form-label small">Snapshot A (älter / „vorher")</label>
            <select name="left" class="form-select">
                <?php foreach ($snapshots as $s): ?>
                    <option value="<?= (int)$s['id'] ?>" <?= $left === (int)$s['id'] ? 'selected' : '' ?>>
                        #<?= (int)$s['id'] ?> · <?= View::escape($s['created_at']) ?> · <?= View::escape($s['kind']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-5">
            <label class="form-label small">Snapshot B (neuer / „nachher")</label>
            <select name="right" class="form-select">
                <?php foreach ($snapshots as $s): ?>
                    <option value="<?= (int)$s['id'] ?>" <?= $right === (int)$s['id'] ? 'selected' : '' ?>>
                        #<?= (int)$s['id'] ?> · <?= View::escape($s['created_at']) ?> · <?= View::escape($s['kind']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <button class="btn btn-primary w-100" type="submit"><i class="bi bi-arrow-left-right"></i> Vergleichen</button>
        </div>
    </form>

    <form method="post" action="/auditdiff/capture" class="mt-3">
        <?= Csrf::field() ?>
        <button class="btn btn-outline-secondary btn-sm" type="submit">
            <i class="bi bi-camera"></i> Jetzt manuellen Snapshot erstellen
        </button>
        <span class="text-muted small ms-2">Aktuell <?= count($snapshots) ?> Snapshots gespeichert (Aufbewahrung 365 Tage).</span>
    </form>
</div>

<?php if ($diff === null): ?>
    <div class="content-card text-center text-muted py-5">
        <?php if (empty($snapshots)): ?>
            <i class="bi bi-camera" style="font-size:48px;color:#9ca3af;"></i>
            <p class="mt-3">Noch keine Snapshots vorhanden. Klick oben auf <strong>"Jetzt manuellen Snapshot erstellen"</strong> oder warte auf den nächsten Cron-Lauf.</p>
        <?php else: ?>
            <p>Bitte zwei Snapshots auswählen und auf "Vergleichen" klicken.</p>
        <?php endif; ?>
    </div>
<?php else: ?>
    <div class="content-card">
        <h5>Vergleich</h5>
        <p class="text-muted small">
            Snapshot A: <strong><?= View::escape($oldRow['created_at']) ?></strong> ·
            Snapshot B: <strong><?= View::escape($newRow['created_at']) ?></strong>
        </p>

        <?php
        $changeCount = count($diff['added']) + count($diff['removed']) + count($diff['modified']);
        ?>
        <?php if ($changeCount === 0): ?>
            <div class="alert alert-success">
                <i class="bi bi-check-circle"></i> Keine Änderungen zwischen den beiden Snapshots.
            </div>
        <?php else: ?>
            <p>
                <span class="badge bg-warning text-dark"><?= count($diff['modified']) ?> geändert</span>
                <span class="badge bg-success"><?= count($diff['added']) ?> neu</span>
                <span class="badge bg-danger"><?= count($diff['removed']) ?> entfernt</span>
            </p>

            <?php if (!empty($diff['modified'])): ?>
                <h6 class="mt-3">Geänderte Werte</h6>
                <div class="diff-block border rounded">
                    <?php foreach ($diff['modified'] as $key => $entry): ?>
                        <div class="diff-line mod">
                            <span class="diff-marker">~</span>
                            <span><?= View::escape($key) ?></span>
                            <span>
                                <span class="diff-value-old"><?= View::escape($formatVal($entry['old'])) ?></span>
                                <i class="bi bi-arrow-right text-muted mx-1"></i>
                                <span class="diff-value-new"><?= View::escape($formatVal($entry['new'])) ?></span>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($diff['added'])): ?>
                <h6 class="mt-3">Hinzugefügte Werte</h6>
                <div class="diff-block border rounded">
                    <?php foreach ($diff['added'] as $key => $val): ?>
                        <div class="diff-line add">
                            <span class="diff-marker">+</span>
                            <span><?= View::escape($key) ?></span>
                            <span class="diff-value-new"><?= View::escape($formatVal($val)) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($diff['removed'])): ?>
                <h6 class="mt-3">Entfernte Werte</h6>
                <div class="diff-block border rounded">
                    <?php foreach ($diff['removed'] as $key => $val): ?>
                        <div class="diff-line del">
                            <span class="diff-marker">-</span>
                            <span><?= View::escape($key) ?></span>
                            <span class="diff-value-old"><?= View::escape($formatVal($val)) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
<?php endif; ?>
