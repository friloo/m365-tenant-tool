<?php
use App\Core\View;
use App\Core\Csrf;

/**
 * Renders one action row in the workflow editor. Declared at the top so
 * the foreach below it can call it freely.
 */
function _wfActionRow(int $idx, string $type, array $cfg, array $actions): string {
    $opts = '<option value="">' . htmlspecialchars(t('— wählen —')) . '</option>';
    foreach ($actions as $k => $label) {
        $sel = $type === $k ? ' selected' : '';
        $opts .= '<option value="' . htmlspecialchars($k) . '"' . $sel . '>' . htmlspecialchars($label) . '</option>';
    }
    $fields = '';
    $templates = [
        'assign_license'    => ['sku_id'],
        'add_to_group'      => ['group_id'],
        'send_mail'         => ['to', 'subject', 'body'],
        'send_notification' => ['title', 'body', 'severity'],
    ];
    foreach ($templates[$type] ?? [] as $k) {
        $v = htmlspecialchars((string)($cfg[$k] ?? ''));
        $fields .= '<div class="mb-2"><label class="form-label small">' . htmlspecialchars($k) . '</label>'
                . '<input class="form-control form-control-sm" name="actions[' . $idx . '][cfg][' . htmlspecialchars($k) . ']" value="' . $v . '" placeholder="' . htmlspecialchars($k) . '"></div>';
    }
    return '<div class="action-row border rounded p-2 mb-2">'
         . '<div class="d-flex gap-2 align-items-center">'
         . '<select class="form-select form-select-sm" style="max-width: 280px;" name="actions[' . $idx . '][type]"'
         . ' onchange="renderActionFields(this, ' . $idx . ')">' . $opts . '</select>'
         . '<button type="button" class="btn btn-sm btn-outline-danger ms-auto" onclick="this.closest(\'.action-row\').remove()"><i class="bi bi-x"></i></button>'
         . '</div>'
         . '<div class="action-fields mt-2" id="action-fields-' . $idx . '">' . $fields . '</div>'
         . '</div>';
}

$existing        = $workflow;
$existingActions = $existing ? (json_decode((string)$existing['actions'], true) ?: []) : [];
$existingTrigCfg = $existing ? (json_decode((string)($existing['trigger_cfg'] ?? '{}'), true) ?: []) : [];
?>
<div class="content-card">
    <h1 class="mb-2"><i class="bi bi-diagram-2"></i> <?= $existing ? te('Workflow bearbeiten') : te('Neuer Workflow') ?></h1>
    <p class="text-muted small"><a href="/workflows"><?= te('← zurück zur Übersicht') ?></a></p>

    <?php $flash = \App\Core\Session::getFlash('success'); $err = \App\Core\Session::getFlash('error'); ?>
    <?php if ($flash): ?><div class="alert alert-success"><?= View::escape($flash) ?></div><?php endif; ?>
    <?php if ($err): ?><div class="alert alert-danger"><?= View::escape($err) ?></div><?php endif; ?>

    <form method="post" action="/workflows/save" id="wfForm">
        <?= Csrf::field() ?>
        <input type="hidden" name="id" value="<?= (int)($existing['id'] ?? 0) ?>">

        <div class="row g-3 mb-3">
            <div class="col-md-7">
                <label class="form-label"><?= te('Name') ?></label>
                <input type="text" name="name" class="form-control" required value="<?= View::escape($existing['name'] ?? '') ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label"><?= te('Trigger') ?></label>
                <select name="trigger_key" class="form-select" id="wfTrigger" onchange="document.querySelectorAll('.trig-cfg').forEach(e=>e.style.display='none');document.getElementById('trig-'+this.value).style.display='block';">
                    <?php foreach ($triggers as $key => $label): ?>
                        <option value="<?= View::escape($key) ?>" <?= ($existing['trigger_key'] ?? '') === $key ? 'selected' : '' ?>><?= View::escape($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label"><?= te('Aktiv') ?></label>
                <div class="form-check form-switch mt-2">
                    <input class="form-check-input" type="checkbox" name="enabled" value="1" <?= !empty($existing['enabled']) ? 'checked' : '' ?>>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header bg-light"><strong><?= te('Trigger-Konfiguration') ?></strong></div>
            <div class="card-body">
                <div class="trig-cfg" id="trig-schedule" style="<?= ($existing['trigger_key'] ?? 'schedule') === 'schedule' ? '' : 'display:none;' ?>">
                    <label class="form-label small"><?= te('Intervall (Minuten, mindestens 15)') ?></label>
                    <input type="number" min="15" class="form-control" name="trigger_cfg[interval_minutes]" value="<?= View::escape($existingTrigCfg['interval_minutes'] ?? '60') ?>">
                </div>
                <div class="trig-cfg" id="trig-new_guest_user" style="<?= ($existing['trigger_key'] ?? '') === 'new_guest_user' ? '' : 'display:none;' ?>">
                    <p class="text-muted small mb-0"><?= te('Keine Konfiguration nötig — der Trigger feuert für jeden neuen Gast-Benutzer seit dem letzten Lauf.') ?></p>
                </div>
                <div class="trig-cfg" id="trig-new_user_in_group" style="<?= ($existing['trigger_key'] ?? '') === 'new_user_in_group' ? '' : 'display:none;' ?>">
                    <label class="form-label small"><?= te('Gruppen-ID (GUID aus /groups)') ?></label>
                    <input type="text" class="form-control" name="trigger_cfg[group_id]" value="<?= View::escape($existingTrigCfg['group_id'] ?? '') ?>" placeholder="00000000-0000-0000-0000-000000000000">
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <strong><?= te('Aktionen') ?></strong>
                <button type="button" class="btn btn-sm btn-outline-primary" onclick="addAction()"><i class="bi bi-plus-lg"></i> <?= te('Aktion hinzufügen') ?></button>
            </div>
            <div class="card-body" id="actionsList">
                <?php foreach ($existingActions as $i => $a): ?>
                    <?= _wfActionRow($i, $a['type'] ?? '', $a['cfg'] ?? [], $actions) ?>
                <?php endforeach; ?>
                <?php if (empty($existingActions)): ?>
                    <p class="text-muted small mb-0" id="noActionsHint"><?= te('Noch keine Aktionen — bitte mindestens eine hinzufügen.') ?></p>
                <?php endif; ?>
            </div>
        </div>

        <div class="d-flex gap-2 align-items-center">
            <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> <?= te('Speichern') ?></button>
        </div>
    </form>

    <?php if ($existing): ?>
        <div class="d-flex gap-2 mt-3">
            <form method="post" action="/workflows/<?= (int)$existing['id'] ?>/run-now">
                <?= Csrf::field() ?>
                <button type="submit" class="btn btn-outline-success"><i class="bi bi-play"></i> <?= te('Jetzt ausführen') ?></button>
            </form>
            <form method="post" action="/workflows/<?= (int)$existing['id'] ?>/delete"
                  onsubmit="return confirm('<?= te('Workflow wirklich löschen?') ?>');" class="ms-auto">
                <?= Csrf::field() ?>
                <button type="submit" class="btn btn-outline-danger"><i class="bi bi-trash"></i> <?= te('Löschen') ?></button>
            </form>
        </div>
    <?php endif; ?>
</div>

<?php if ($existing && !empty($runs)): ?>
    <div class="content-card mt-3">
        <h5><?= te('Letzte Läufe') ?></h5>
        <table class="table table-sm">
            <thead><tr><th><?= te('Zeit') ?></th><th><?= te('Ziel') ?></th><th><?= te('Status') ?></th><th><?= te('Detail') ?></th></tr></thead>
            <tbody>
                <?php foreach ($runs as $r): ?>
                    <tr>
                        <td class="small text-muted"><?= View::escape($r['ran_at']) ?></td>
                        <td><?= View::escape($r['target'] ?: '—') ?></td>
                        <td>
                            <?php $cls = $r['status'] === 'ok' ? 'success' : ($r['status'] === 'error' ? 'danger' : 'secondary'); ?>
                            <span class="badge bg-<?= $cls ?>"><?= View::escape($r['status']) ?></span>
                        </td>
                        <td class="small"><?= View::escape($r['detail']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<script>
const ACTIONS = <?= json_encode($actions, JSON_UNESCAPED_UNICODE) ?>;
const ACTION_TPL = {
    assign_license:    ['sku_id'],
    add_to_group:      ['group_id'],
    send_mail:         ['to', 'subject', 'body'],
    send_notification: ['title', 'body', 'severity'],
};
let actionIdx = <?= count($existingActions) ?>;

function addAction() {
    const hint = document.getElementById('noActionsHint');
    if (hint) hint.remove();
    const idx = actionIdx++;
    document.getElementById('actionsList').insertAdjacentHTML('beforeend', renderActionRowJs(idx));
}

function renderActionRowJs(idx) {
    let opts = '<option value="">' + <?= json_encode(t('— wählen —'), JSON_UNESCAPED_UNICODE) ?> + '</option>';
    for (const k in ACTIONS) opts += `<option value="${k}">${ACTIONS[k]}</option>`;
    return `<div class="action-row border rounded p-2 mb-2">
        <div class="d-flex gap-2 align-items-center">
            <select class="form-select form-select-sm" style="max-width: 280px;" name="actions[${idx}][type]"
                    onchange="renderActionFields(this, ${idx})">${opts}</select>
            <button type="button" class="btn btn-sm btn-outline-danger ms-auto" onclick="this.closest('.action-row').remove()"><i class="bi bi-x"></i></button>
        </div>
        <div class="action-fields mt-2" id="action-fields-${idx}"></div>
    </div>`;
}

function renderActionFields(select, idx) {
    const type = select.value;
    const target = document.getElementById('action-fields-' + idx);
    if (!type || !ACTION_TPL[type]) { target.innerHTML = ''; return; }
    let html = '';
    ACTION_TPL[type].forEach(k => {
        html += `<div class="mb-2"><label class="form-label small">${k}</label>
                  <input class="form-control form-control-sm" name="actions[${idx}][cfg][${k}]" placeholder="${k}"></div>`;
    });
    html += '<p class="small text-muted mb-0">' + <?= json_encode(t('Verfügbare Variablen:'), JSON_UNESCAPED_UNICODE) ?> + ' <code>{{user.userPrincipalName}}</code>, <code>{{user.displayName}}</code>, <code>{{user.id}}</code>, <code>{{trigger}}</code></p>';
    target.innerHTML = html;
}
</script>
