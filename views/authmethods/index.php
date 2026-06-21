<?php
$e = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');

$stateBadge = function (string $state): string {
    return match ($state) {
        'enabled'  => '<span class="badge bg-success">' . te('aktiviert') . '</span>',
        'disabled' => '<span class="badge bg-secondary">' . te('deaktiviert') . '</span>',
        default    => '<span class="badge bg-light text-dark border">' . te('Standard') . '</span>',
    };
};
$recBadge = function (string $rec): string {
    return match ($rec) {
        'enabled'  => '<span class="badge bg-success-subtle text-success-emphasis border border-success-subtle">' . te('empfohlen: an') . '</span>',
        'disabled' => '<span class="badge bg-danger-subtle text-danger-emphasis border border-danger-subtle">' . te('empfohlen: aus') . '</span>',
        default    => '<span class="badge bg-light text-dark border">' . te('situativ') . '</span>',
    };
};
?>

<?php if ($flash ?? null): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
  <i class="bi bi-check-circle-fill me-2"></i><?= $e($flash) ?>
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif ?>
<?php if ($error ?? null): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
  <i class="bi bi-x-circle-fill me-2"></i><?= $e($error) ?>
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif ?>

<?php if (!empty($diag ?? null)) include BASE_PATH . '/views/partials/graph_diagnostic.php'; ?>

<div class="alert alert-info d-flex align-items-start gap-2">
  <i class="bi bi-info-circle-fill mt-1"></i>
  <div>
    <?= te('Steuert tenant-weit, welche Authentifizierungsmethoden Nutzer registrieren/verwenden dürfen') ?>
    (<code>/policies/authenticationMethodsPolicy</code>). <?= te('Empfehlung nach CIS M365 / Microsoft: phishing-resistente Methoden (FIDO2, Authenticator) aktivieren, schwache (SMS, Sprachanruf, E-Mail-OTP) als MFA deaktivieren. Änderungen wirken') ?> <strong><?= te('sofort tenant-weit') ?></strong>.
  </div>
</div>

<div class="content-card mb-4">
  <div class="table-responsive">
    <table class="table align-middle mb-0">
      <thead>
        <tr>
          <th><?= te('Methode') ?></th>
          <th><?= te('Status') ?></th>
          <th><?= te('Empfehlung') ?></th>
          <th><?= te('Hinweis') ?></th>
          <?php if ($isAdmin ?? false): ?><th class="text-end"><?= te('Aktion') ?></th><?php endif ?>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($methods)): ?>
          <tr><td colspan="5" class="text-muted text-center py-4"><?= te('Keine Methoden gelesen — Berechtigung') ?> <code>Policy.Read.All</code> <?= te('prüfen.') ?></td></tr>
        <?php endif ?>
        <?php foreach ($methods as $m): ?>
          <tr>
            <td class="fw-semibold"><?= $e($m['label']) ?> <span class="text-muted small">(<?= $e($m['id']) ?>)</span></td>
            <td><?= $stateBadge($m['state']) ?></td>
            <td><?= $recBadge($m['recommend']) ?></td>
            <td class="small text-muted"><?= $e($m['note']) ?></td>
            <?php if ($isAdmin ?? false): ?>
            <td class="text-end">
              <?php $target = $m['state'] === 'enabled' ? 'disabled' : 'enabled'; ?>
              <form method="post" action="/authmethods/<?= $e(rawurlencode($m['id'])) ?>/set-state" class="d-inline"
                    onsubmit="return confirm('<?= $e(t('Methode')) ?> <?= $e($m['label']) ?> <?= $e(t('auf')) ?> \'<?= $e($target) ?>\' <?= $e(t('setzen? Wirkt sofort tenant-weit.')) ?>');">
                <?= \App\Core\Csrf::field() ?>
                <input type="hidden" name="state" value="<?= $e($target) ?>">
                <button type="submit" class="btn btn-sm <?= $target === 'enabled' ? 'btn-outline-success' : 'btn-outline-secondary' ?>">
                  <?= $target === 'enabled' ? te('Aktivieren') : te('Deaktivieren') ?>
                </button>
              </form>
            </td>
            <?php endif ?>
          </tr>
        <?php endforeach ?>
      </tbody>
    </table>
  </div>
</div>

<p class="small text-muted">
  <?= te('Schreiben erfordert die Graph-Berechtigung') ?> <code>Policy.ReadWrite.AuthenticationMethod</code>.
  <?= te('Feinkonfiguration (z. B. Zielgruppen je Methode, Number-Matching-Details) im') ?>
  <a href="https://entra.microsoft.com/#view/Microsoft_AAD_IAM/AuthenticationMethodsMenuBlade" target="_blank" rel="noopener"><?= te('Entra-Portal') ?></a>.
</p>
