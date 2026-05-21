<?php
use App\Core\View;
use App\Core\Csrf;
?>
<div class="content-card mb-3">
    <h1 class="mb-2"><i class="bi bi-key"></i> API-Schlüssel <?= \App\Core\Help::tip('rest_api') ?></h1>
    <p class="text-muted">Verwalte API-Keys für externe Werkzeuge (PowerBI, Grafana, n8n, Skripte). Spezifikation unter <a href="/api/docs" target="_blank">/api/docs</a> (Swagger UI), Roh-Schema unter <a href="/api/openapi.json" target="_blank">/api/openapi.json</a>.</p>

    <?php $flash = \App\Core\Session::getFlash('success'); $err = \App\Core\Session::getFlash('error'); ?>
    <?php if ($flash): ?><div class="alert alert-success"><?= View::escape($flash) ?></div><?php endif; ?>
    <?php if ($err): ?><div class="alert alert-danger"><?= View::escape($err) ?></div><?php endif; ?>

    <?php if (!empty($fresh)): ?>
        <div class="alert alert-warning">
            <h6 class="mb-2"><i class="bi bi-exclamation-triangle"></i> Neuer API-Key — bitte jetzt kopieren!</h6>
            <p class="small mb-2">Dieser Wert wird <strong>nur einmal</strong> angezeigt und ist anschließend nicht mehr rekonstruierbar.</p>
            <p class="small mb-2"><strong>Name:</strong> <?= View::escape($fresh['name']) ?></p>
            <code style="background:#fff; padding:8px 12px; display:block; border:1px solid #fbbf24; word-break:break-all;"><?= View::escape($fresh['key']) ?></code>
            <button class="btn btn-sm btn-outline-warning mt-2" onclick="navigator.clipboard.writeText(<?= json_encode($fresh['key']) ?>); this.textContent='Kopiert ✓';">
                <i class="bi bi-clipboard"></i> In Zwischenablage kopieren
            </button>
        </div>
    <?php endif; ?>

    <h6 class="mt-4">Neuen Key erstellen</h6>
    <form method="post" action="/settings/api-keys/create" class="row g-2 align-items-end">
        <?= Csrf::field() ?>
        <div class="col-md-5"><label class="form-label small">Name</label>
            <input class="form-control" type="text" name="name" placeholder="z. B. PowerBI Dashboard"></div>
        <div class="col-md-5"><label class="form-label small">Scopes</label>
            <select multiple name="scopes[]" class="form-select" size="3" required>
                <option value="read" selected>read — lesender Zugriff</option>
                <option value="write">write — Benachrichtigungen erzeugen</option>
                <option value="admin">admin — (Reserviert)</option>
            </select></div>
        <div class="col-md-2"><button class="btn btn-primary w-100" type="submit"><i class="bi bi-plus-lg"></i> Erstellen</button></div>
    </form>
</div>

<div class="content-card">
    <h6>Bestehende Keys</h6>
    <?php if (empty($keys)): ?>
        <p class="text-muted small">Noch keine API-Keys angelegt.</p>
    <?php else: ?>
        <table class="table table-sm">
            <thead><tr>
                <th>Name</th><th>Scopes</th><th>Erstellt</th><th>Erstellt von</th><th>Zuletzt verwendet</th><th>Status</th><th></th>
            </tr></thead>
            <tbody>
            <?php foreach ($keys as $k): ?>
                <tr>
                    <td><?= View::escape($k['name']) ?></td>
                    <td><?php foreach (explode(',', (string)$k['scopes']) as $s): ?>
                        <span class="badge bg-secondary"><?= View::escape($s) ?></span>
                    <?php endforeach; ?></td>
                    <td class="small text-muted"><?= View::escape($k['created_at']) ?></td>
                    <td class="small text-muted"><?= View::escape($k['created_by']) ?></td>
                    <td class="small text-muted"><?= View::escape($k['last_used'] ?: '—') ?></td>
                    <td>
                        <?php if ($k['revoked_at']): ?>
                            <span class="badge bg-danger">widerrufen</span>
                        <?php else: ?>
                            <span class="badge bg-success">aktiv</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if (!$k['revoked_at']): ?>
                            <form method="post" action="/settings/api-keys/<?= (int)$k['id'] ?>/revoke" class="d-inline"
                                  onsubmit="return confirm('Diesen Key wirklich widerrufen? Externe Tools, die ihn nutzen, verlieren sofort den Zugriff.');">
                                <?= Csrf::field() ?>
                                <button class="btn btn-sm btn-outline-danger" type="submit"><i class="bi bi-x-circle"></i> Widerrufen</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
