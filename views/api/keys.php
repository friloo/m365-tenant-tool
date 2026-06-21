<?php
use App\Core\View;
use App\Core\Csrf;
?>
<div class="content-card mb-3">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-2">
        <div>
            <h1 class="mb-1"><i class="bi bi-key text-primary"></i> <?= te('API-Schlüssel') ?> <?= \App\Core\Help::tip('rest_api') ?></h1>
            <p class="text-muted mb-0"><?= te('API-Keys für externe Werkzeuge (PowerBI, Grafana, n8n, eigene Skripte) verwalten.') ?></p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="/api/docs" class="btn btn-outline-primary"><i class="bi bi-book"></i> <?= te('API-Dokumentation') ?></a>
            <a href="/api/openapi.json" target="_blank" class="btn btn-outline-secondary"><i class="bi bi-filetype-json"></i> <?= te('OpenAPI-Spec') ?></a>
        </div>
    </div>

    <?php $flash = \App\Core\Session::getFlash('success'); $err = \App\Core\Session::getFlash('error'); ?>
    <?php if ($flash): ?><div class="alert alert-success mt-3 mb-0"><i class="bi bi-check-circle"></i> <?= View::escape($flash) ?></div><?php endif; ?>
    <?php if ($err): ?><div class="alert alert-danger mt-3 mb-0"><i class="bi bi-exclamation-triangle"></i> <?= View::escape($err) ?></div><?php endif; ?>
</div>

<?php if (!empty($fresh)): ?>
    <div class="content-card mb-3" style="border-left: 4px solid #f59e0b;">
        <div class="d-flex justify-content-between align-items-start mb-2 gap-3">
            <div>
                <h5 class="mb-1 text-warning"><i class="bi bi-exclamation-triangle-fill"></i> <?= te('Neuer API-Key — jetzt kopieren!') ?></h5>
                <p class="small text-muted mb-0"><?= t('Wird <strong>nur einmal</strong> angezeigt und kann anschließend nicht mehr rekonstruiert werden.') ?></p>
            </div>
            <span class="badge bg-warning text-dark align-self-center"><?= View::escape($fresh['name']) ?></span>
        </div>
        <div class="d-flex gap-2 mt-3 align-items-stretch">
            <input id="freshKey" type="text" readonly class="form-control font-monospace"
                   style="background:#fffbeb; border-color:#fbbf24; font-size:13px;"
                   value="<?= View::escape($fresh['key']) ?>">
            <button type="button" class="btn btn-warning" id="copyFreshBtn">
                <i class="bi bi-clipboard"></i> <?= te('Kopieren') ?>
            </button>
        </div>
        <p class="small text-muted mt-2 mb-0">
            <?= te('Beispiel-Aufruf:') ?> <code>curl -H "X-Api-Key: <?= View::escape($fresh['key']) ?>" https://<?= View::escape($_SERVER['HTTP_HOST'] ?? 'localhost') ?>/api/v1/dashboard/metrics</code>
        </p>
    </div>
    <script>
    document.getElementById('copyFreshBtn').addEventListener('click', function () {
        const inp = document.getElementById('freshKey');
        inp.select();
        navigator.clipboard.writeText(inp.value).then(() => {
            this.innerHTML = '<i class="bi bi-check-lg"></i> ' + <?= json_encode(t('Kopiert!'), JSON_UNESCAPED_UNICODE) ?>;
            this.classList.remove('btn-warning'); this.classList.add('btn-success');
            setTimeout(() => location.href = '/settings/api-keys', 2000);
        });
    });
    </script>
<?php endif; ?>

<div class="content-card mb-3">
    <h5 class="mb-3"><i class="bi bi-plus-circle"></i> <?= te('Neuen Schlüssel erstellen') ?></h5>
    <form method="post" action="/settings/api-keys/create" class="row g-3">
        <?= Csrf::field() ?>
        <div class="col-md-6">
            <label class="form-label"><?= te('Name') ?></label>
            <input class="form-control" type="text" name="name" placeholder="<?= te('z. B. PowerBI Dashboard') ?>" required>
            <div class="form-text"><?= te('Frei wählbarer Bezeichner für deine eigene Übersicht.') ?></div>
        </div>
        <div class="col-md-6">
            <label class="form-label"><?= te('Berechtigungen (Scopes)') ?></label>
            <div class="d-flex flex-column gap-2">
                <label class="form-check m-0 p-2 border rounded" style="cursor:pointer;">
                    <input class="form-check-input me-2" type="checkbox" name="scopes[]" value="read" checked>
                    <strong>read</strong> &mdash; <?= te('lesender Zugriff auf alle GET-Endpunkte') ?>
                </label>
                <label class="form-check m-0 p-2 border rounded" style="cursor:pointer;">
                    <input class="form-check-input me-2" type="checkbox" name="scopes[]" value="write">
                    <strong>write</strong> &mdash; <?= te('Benachrichtigungen pushen, Hardening anwenden, Snapshots erstellen') ?>
                </label>
                <label class="form-check m-0 p-2 border rounded text-muted" style="cursor:pointer;">
                    <input class="form-check-input me-2" type="checkbox" name="scopes[]" value="admin">
                    <strong>admin</strong> &mdash; <?= te('reserviert für zukünftige Admin-Endpunkte') ?>
                </label>
            </div>
        </div>
        <div class="col-12">
            <button class="btn btn-primary" type="submit"><i class="bi bi-plus-lg"></i> <?= te('Schlüssel erstellen') ?></button>
        </div>
    </form>
</div>

<div class="content-card">
    <h5 class="mb-3"><i class="bi bi-list-ul"></i> <?= te('Bestehende Schlüssel') ?> <span class="badge bg-secondary"><?= count($keys) ?></span></h5>
    <?php if (empty($keys)): ?>
        <div class="text-center text-muted py-5">
            <i class="bi bi-key" style="font-size:42px; opacity:0.4;"></i>
            <p class="mt-3 mb-0"><?= te('Noch keine API-Keys angelegt.') ?></p>
            <p class="small"><?= te('Erstelle oben den ersten Key, um externe Werkzeuge anzubinden.') ?></p>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th><?= te('Name') ?></th>
                        <th><?= te('Scopes') ?></th>
                        <th><?= te('Erstellt') ?></th>
                        <th><?= te('Erstellt von') ?></th>
                        <th><?= te('Zuletzt verwendet') ?></th>
                        <th><?= te('Status') ?></th>
                        <th class="text-end"><?= te('Aktionen') ?></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($keys as $k): ?>
                    <tr>
                        <td><strong><?= View::escape($k['name']) ?></strong></td>
                        <td>
                            <?php foreach (array_filter(array_map('trim', explode(',', (string)$k['scopes']))) as $s): ?>
                                <span class="badge bg-light text-dark border me-1"><?= View::escape($s) ?></span>
                            <?php endforeach; ?>
                        </td>
                        <td class="small text-muted"><?= View::escape($k['created_at']) ?></td>
                        <td class="small text-muted"><?= View::escape($k['created_by'] ?: '—') ?></td>
                        <td class="small text-muted"><?= $k['last_used'] ? View::escape($k['last_used']) : te('— nie —') ?></td>
                        <td>
                            <?php if ($k['revoked_at']): ?>
                                <span class="badge bg-danger"><i class="bi bi-x-circle"></i> <?= te('widerrufen') ?></span>
                            <?php else: ?>
                                <span class="badge bg-success"><i class="bi bi-check-circle"></i> <?= te('aktiv') ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">
                            <?php if (!$k['revoked_at']): ?>
                                <form method="post" action="/settings/api-keys/<?= (int)$k['id'] ?>/revoke" class="d-inline"
                                      onsubmit="return confirm('<?= t('Diesen Key wirklich widerrufen? Externe Tools verlieren sofort den Zugriff.') ?>');">
                                    <?= Csrf::field() ?>
                                    <button class="btn btn-sm btn-outline-danger" type="submit">
                                        <i class="bi bi-x-circle"></i> <?= te('Widerrufen') ?>
                                    </button>
                                </form>
                            <?php else: ?>
                                <span class="text-muted small">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
