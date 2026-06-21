<?php
$e = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
?>

<?php if ($flash): ?>
<div class="alert alert-success alert-dismissible fade show">
  <i class="bi bi-check-circle-fill me-2"></i><?= $e($flash) ?>
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif ?>
<?php if ($error): ?>
<div class="alert alert-danger alert-dismissible fade show">
  <i class="bi bi-exclamation-triangle-fill me-2"></i><?= $e($error) ?>
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif ?>

<!-- User search -->
<div class="card shadow-sm mb-4">
  <div class="card-header fw-semibold"><i class="bi bi-search me-2"></i><?= te('Benutzer suchen') ?></div>
  <div class="card-body">
    <div class="position-relative" style="max-width:500px">
      <input type="text" id="userSearch" class="form-control" placeholder="<?= te('Name oder E-Mail-Adresse eingeben...') ?>"
             autocomplete="off" value="<?= $user ? $e($user['displayName'] ?? '') : '' ?>">
      <div id="userSearchResults" class="list-group position-absolute w-100 shadow-sm z-3" style="top:100%;left:0;display:none;max-height:300px;overflow-y:auto"></div>
    </div>
  </div>
</div>

<?php if ($user && $state): ?>
<?php
$userId = $user['id'];
$displayName = $user['displayName'] ?? '–';
$upn = $user['userPrincipalName'] ?? '–';
$dept = $user['department'] ?? '–';
$title = $user['jobTitle'] ?? '–';
$enabled = $user['accountEnabled'] ?? true;
$lastSignIn = $user['signInActivity']['lastSignInDateTime'] ?? null;
?>

<!-- User profile card -->
<div class="card shadow-sm mb-4 border-<?= $enabled ? 'primary' : 'secondary' ?>">
  <div class="card-body d-flex align-items-center gap-3">
    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold"
         style="width:52px;height:52px;font-size:1.3rem;flex-shrink:0">
      <?= strtoupper(substr($displayName, 0, 1)) ?>
    </div>
    <div class="flex-grow-1">
      <div class="fw-bold fs-5"><?= $e($displayName) ?></div>
      <div class="text-muted small"><?= $e($upn) ?></div>
      <div class="text-muted small"><?= $e($dept) ?> <?= $title !== '–' ? '· ' . $e($title) : '' ?></div>
    </div>
    <div class="text-end">
      <?php if (!$enabled): ?>
        <span class="badge bg-secondary fs-6"><?= te('Deaktiviert') ?></span>
      <?php else: ?>
        <span class="badge bg-success fs-6"><?= te('Aktiv') ?></span>
      <?php endif ?>
      <?php if ($lastSignIn): ?>
      <div class="text-muted small mt-1">
        <?= te('Letzter Login') ?>: <?= date('d.m.Y', strtotime($lastSignIn)) ?>
      </div>
      <?php endif ?>
      <?php if ($state['synced'] ?? false): ?>
      <div class="badge bg-info mt-1"><?= te('On-Prem-synchronisiert') ?></div>
      <?php endif ?>
    </div>
  </div>
</div>

<!-- Offboarding steps -->
<div class="row g-4">
  <div class="col-lg-8">
    <div class="card shadow-sm">
      <div class="card-header fw-semibold"><i class="bi bi-clipboard2-check me-2"></i><?= te('Offboarding-Schritte') ?></div>
      <ul class="list-group list-group-flush">

        <!-- Step 1: Disable account -->
        <li class="list-group-item py-3">
          <div class="d-flex align-items-start gap-3">
            <div class="mt-1">
              <?php if (!$enabled): ?>
                <i class="bi bi-check-circle-fill text-success fs-5"></i>
              <?php else: ?>
                <i class="bi bi-circle text-muted fs-5"></i>
              <?php endif ?>
            </div>
            <div class="flex-grow-1">
              <div class="fw-semibold"><?= te('1. Konto deaktivieren') ?></div>
              <div class="text-muted small"><?= te('Verhindert sofort jede weitere Anmeldung.') ?></div>
              <?php if ($state['synced'] ?? false): ?>
              <div class="alert alert-warning small mt-2 mb-0 py-1">
                <i class="bi bi-info-circle me-1"></i>
                <?= te('Konto ist on-prem synchronisiert — Deaktivierung am besten im lokalen Active Directory durchführen.') ?>
              </div>
              <?php endif ?>
            </div>
            <div>
              <?php if ($enabled): ?>
              <form method="post" action="/offboarding/disable-account"
                    onsubmit="return confirm(<?= $e(json_encode(t('Konto von :name wirklich deaktivieren?', ['name' => $displayName]), JSON_UNESCAPED_UNICODE)) ?>)">
                <?= \App\Core\Csrf::field() ?>
                <input type="hidden" name="user_id" value="<?= $e($userId) ?>">
                <button class="btn btn-danger btn-sm"><i class="bi bi-person-slash me-1"></i><?= te('Deaktivieren') ?></button>
              </form>
              <?php else: ?>
              <span class="badge bg-success"><?= te('Erledigt') ?></span>
              <?php endif ?>
            </div>
          </div>
        </li>

        <!-- Step 2: Revoke sessions -->
        <li class="list-group-item py-3">
          <div class="d-flex align-items-start gap-3">
            <div class="mt-1"><i class="bi bi-circle text-muted fs-5"></i></div>
            <div class="flex-grow-1">
              <div class="fw-semibold"><?= te('2. Alle Sitzungen widerrufen') ?></div>
              <div class="text-muted small"><?= te('Macht alle bestehenden Refresh-Tokens ungültig (Outlook, Teams, Browser etc.).') ?></div>
            </div>
            <div>
              <form method="post" action="/offboarding/revoke-sessions">
                <?= \App\Core\Csrf::field() ?>
                <input type="hidden" name="user_id" value="<?= $e($userId) ?>">
                <button class="btn btn-warning btn-sm"><i class="bi bi-shield-x me-1"></i><?= te('Widerrufen') ?></button>
              </form>
            </div>
          </div>
        </li>

        <!-- Step 3: Remove licenses -->
        <li class="list-group-item py-3">
          <div class="d-flex align-items-start gap-3">
            <div class="mt-1">
              <?php if (!($state['hasLicenses'] ?? true)): ?>
                <i class="bi bi-check-circle-fill text-success fs-5"></i>
              <?php else: ?>
                <i class="bi bi-circle text-muted fs-5"></i>
              <?php endif ?>
            </div>
            <div class="flex-grow-1">
              <div class="fw-semibold"><?= te('3. Lizenzen entfernen') ?></div>
              <div class="text-muted small">
                <?= te(':n Lizenz(en) zugewiesen. Entfernen gibt die Lizenzen für andere Benutzer frei.', ['n' => $state['licenseCount']]) ?>
              </div>
              <?php if ($state['hasLicenses'] ?? false): ?>
              <div class="alert alert-info small mt-2 mb-0 py-1">
                <i class="bi bi-info-circle me-1"></i>
                <?= te('Soll das Postfach als Shared Mailbox erhalten bleiben?') ?>
                <?= te('Dann zuerst im') ?> <a href="https://admin.exchange.microsoft.com/#/mailboxes" target="_blank" rel="noopener noreferrer">Exchange Admin Center</a>
                <?= te('in Shared Mailbox umwandeln — dann benötigt es keine Lizenz mehr.') ?>
              </div>
              <?php endif ?>
            </div>
            <div>
              <?php if ($state['hasLicenses'] ?? false): ?>
              <form method="post" action="/offboarding/remove-licenses"
                    onsubmit="return confirm(<?= $e(json_encode(t('Alle :n Lizenz(en) von :name entfernen?', ['n' => $state['licenseCount'], 'name' => $displayName]), JSON_UNESCAPED_UNICODE)) ?>)">
                <?= \App\Core\Csrf::field() ?>
                <input type="hidden" name="user_id" value="<?= $e($userId) ?>">
                <button class="btn btn-outline-danger btn-sm"><i class="bi bi-award me-1"></i><?= te('Entfernen') ?> (<?= $state['licenseCount'] ?>)</button>
              </form>
              <?php else: ?>
              <span class="badge bg-success"><?= te('Keine Lizenzen') ?></span>
              <?php endif ?>
            </div>
          </div>
        </li>

        <!-- Step 4: Remove from groups -->
        <li class="list-group-item py-3">
          <div class="d-flex align-items-start gap-3">
            <div class="mt-1">
              <?php if (($state['groupCount'] ?? 0) === 0): ?>
                <i class="bi bi-check-circle-fill text-success fs-5"></i>
              <?php else: ?>
                <i class="bi bi-circle text-muted fs-5"></i>
              <?php endif ?>
            </div>
            <div class="flex-grow-1">
              <div class="fw-semibold"><?= te('4. Aus Gruppen entfernen') ?></div>
              <div class="text-muted small"><?= te(':n Gruppe(n) — dynamische Gruppen werden automatisch aktualisiert.', ['n' => $state['groupCount']]) ?></div>
              <?php if (!empty($state['groups'])): ?>
              <div class="mt-2 d-flex flex-wrap gap-1">
                <?php foreach (array_slice($state['groups'], 0, 10) as $g): ?>
                <span class="badge bg-light text-dark border small"><?= $e($g['displayName'] ?? '–') ?></span>
                <?php endforeach ?>
                <?php if (count($state['groups']) > 10): ?>
                <span class="badge bg-light text-muted border small"><?= te('+:n weitere', ['n' => count($state['groups']) - 10]) ?></span>
                <?php endif ?>
              </div>
              <?php endif ?>
            </div>
            <div>
              <?php if (($state['groupCount'] ?? 0) > 0): ?>
              <form method="post" action="/offboarding/remove-groups"
                    onsubmit="return confirm(<?= $e(json_encode(t(':name aus :n Gruppe(n) entfernen?', ['name' => $displayName, 'n' => $state['groupCount']]), JSON_UNESCAPED_UNICODE)) ?>)">
                <?= \App\Core\Csrf::field() ?>
                <input type="hidden" name="user_id" value="<?= $e($userId) ?>">
                <button class="btn btn-outline-warning btn-sm"><i class="bi bi-diagram-3 me-1"></i><?= te('Entfernen') ?></button>
              </form>
              <?php else: ?>
              <span class="badge bg-success"><?= te('Keine Gruppen') ?></span>
              <?php endif ?>
            </div>
          </div>
        </li>

        <!-- Step 5: Mailbox (manual) -->
        <li class="list-group-item py-3">
          <div class="d-flex align-items-start gap-3">
            <div class="mt-1"><i class="bi bi-circle text-muted fs-5"></i></div>
            <div class="flex-grow-1">
              <div class="fw-semibold"><?= te('5. Postfach als Shared Mailbox umwandeln (optional)') ?></div>
              <div class="text-muted small">
                <?= te('Wenn E-Mails weiterhin zugänglich sein sollen (z. B. für Vertretungen).') ?>
                <?= te('Erst nach Lizenzentfernung — ein Shared Mailbox benötigt keine eigene Lizenz.') ?>
              </div>
            </div>
            <div>
              <a href="https://admin.exchange.microsoft.com/#/mailboxes" target="_blank" rel="noopener noreferrer"
                 class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-box-arrow-up-right me-1"></i><?= te('Exchange Admin') ?>
              </a>
            </div>
          </div>
        </li>

        <!-- Step 6: OneDrive -->
        <li class="list-group-item py-3">
          <div class="d-flex align-items-start gap-3">
            <div class="mt-1"><i class="bi bi-circle text-muted fs-5"></i></div>
            <div class="flex-grow-1">
              <div class="fw-semibold"><?= te('6. OneDrive-Daten sichern / Zugriff gewähren') ?></div>
              <div class="text-muted small">
                <?= te('Einem Manager Zugriff auf das OneDrive gewähren, bevor das Konto dauerhaft gelöscht wird.') ?>
                <?= te('Gelöschte Konten behalten OneDrive-Daten 30 Tage lang.') ?>
              </div>
            </div>
            <div>
              <a href="/users/<?= $e($userId) ?>" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-cloud me-1"></i><?= te('Benutzerprofil') ?>
              </a>
            </div>
          </div>
        </li>

        <!-- Step 7: Delete or retain -->
        <li class="list-group-item py-3">
          <div class="d-flex align-items-start gap-3">
            <div class="mt-1"><i class="bi bi-circle text-muted fs-5"></i></div>
            <div class="flex-grow-1">
              <div class="fw-semibold"><?= te('7. Konto löschen (optional, unwiderruflich!)') ?></div>
              <div class="text-muted small">
                <?= te('Nur löschen, wenn Daten gesichert und alle vorherigen Schritte abgeschlossen sind.') ?>
                <?= te('Das Konto ist 30 Tage wiederherstellbar.') ?>
              </div>
            </div>
            <div>
              <a href="https://entra.microsoft.com/#view/Microsoft_AAD_UsersAndTenants/UserProfileMenuBlade/~/overview/userId/<?= $e($userId) ?>"
                 target="_blank" rel="noopener noreferrer" class="btn btn-outline-danger btn-sm">
                <i class="bi bi-box-arrow-up-right me-1"></i><?= te('In Entra öffnen') ?>
              </a>
            </div>
          </div>
        </li>

      </ul>
    </div>
  </div>

  <!-- Right: Info panel -->
  <div class="col-lg-4">
    <div class="card shadow-sm mb-3">
      <div class="card-header fw-semibold"><i class="bi bi-info-circle me-2"></i><?= te('Informationen') ?></div>
      <ul class="list-group list-group-flush small">
        <li class="list-group-item d-flex justify-content-between">
          <span><?= te('Manager') ?></span>
          <span class="text-muted"><?= $e($state['manager'] ?? '–') ?></span>
        </li>
        <li class="list-group-item d-flex justify-content-between">
          <span><?= te('Abteilung') ?></span>
          <span class="text-muted"><?= $e($dept) ?></span>
        </li>
        <li class="list-group-item d-flex justify-content-between">
          <span><?= te('Erstellt') ?></span>
          <span class="text-muted"><?= $user['createdDateTime'] ? date('d.m.Y', strtotime($user['createdDateTime'])) : '–' ?></span>
        </li>
        <li class="list-group-item d-flex justify-content-between">
          <span><?= te('On-Prem-Sync') ?></span>
          <span><?= ($state['synced'] ?? false) ? '<span class="badge bg-info">' . te('Ja') . '</span>' : '<span class="badge bg-secondary">' . te('Nein') . '</span>' ?></span>
        </li>
      </ul>
    </div>

    <div class="card shadow-sm">
      <div class="card-header fw-semibold"><i class="bi bi-link-45deg me-2"></i><?= te('Admin-Links') ?></div>
      <ul class="list-group list-group-flush small">
        <li class="list-group-item">
          <a href="https://entra.microsoft.com/#view/Microsoft_AAD_UsersAndTenants/UserProfileMenuBlade/~/overview/userId/<?= $e($userId) ?>"
             target="_blank" rel="noopener noreferrer">
            <i class="bi bi-person me-1"></i><?= te('Entra ID Profil') ?>
          </a>
        </li>
        <li class="list-group-item">
          <a href="https://admin.exchange.microsoft.com/#/mailboxes" target="_blank" rel="noopener noreferrer">
            <i class="bi bi-envelope me-1"></i><?= te('Exchange Postfach') ?>
          </a>
        </li>
        <li class="list-group-item">
          <a href="/users/<?= $e($userId) ?>" >
            <i class="bi bi-person-badge me-1"></i><?= te('Benutzerprofil (lokal)') ?>
          </a>
        </li>
      </ul>
    </div>
  </div>
</div>

<?php else: ?>
<div class="alert alert-info">
  <i class="bi bi-search me-2"></i>
  <?= te('Suche nach einem Benutzer, um den Offboarding-Prozess zu starten.') ?>
</div>
<?php endif ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const inp = document.getElementById('userSearch');
    const box = document.getElementById('userSearchResults');
    let timer;

    inp.addEventListener('input', function () {
        clearTimeout(timer);
        const q = this.value.trim();
        if (q.length < 2) { box.style.display = 'none'; return; }
        timer = setTimeout(() => {
            fetch('/offboarding/search?q=' + encodeURIComponent(q))
                .then(r => r.json())
                .then(users => {
                    box.innerHTML = '';
                    if (!users.length) {
                        box.innerHTML = '<div class="list-group-item text-muted">' + <?= json_encode(t('Keine Ergebnisse'), JSON_UNESCAPED_UNICODE) ?> + '</div>';
                    }
                    users.forEach(u => {
                        const a = document.createElement('a');
                        a.href = '/offboarding?user=' + encodeURIComponent(u.id);
                        a.className = 'list-group-item list-group-item-action d-flex align-items-center gap-2 py-2';
                        a.innerHTML = `<i class="bi bi-person-circle text-muted"></i>
                            <div>
                                <div class="fw-semibold small">${u.displayName || ''}</div>
                                <div class="text-muted" style="font-size:12px">${u.userPrincipalName || ''}</div>
                            </div>
                            ${u.accountEnabled === false ? '<span class="badge bg-secondary ms-auto">' + <?= json_encode(t('Deaktiviert'), JSON_UNESCAPED_UNICODE) ?> + '</span>' : ''}`;
                        box.appendChild(a);
                    });
                    box.style.display = 'block';
                })
                .catch(() => { box.style.display = 'none'; });
        }, 300);
    });

    document.addEventListener('click', function (e) {
        if (!inp.contains(e.target) && !box.contains(e.target)) {
            box.style.display = 'none';
        }
    });
});
</script>
