<?php
/** @var array $report */
$e = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');

$org     = $report['org'];
$license = $report['license'];
$hybrid  = $report['hybrid'];
$domains = $report['domains'];
$score   = $report['score'];

$statusConfig = [
    'ok'      => ['class' => 'success',  'icon' => 'check-circle-fill',  'badge' => t('Bereit')],
    'warning' => ['class' => 'warning',  'icon' => 'exclamation-triangle-fill', 'badge' => t('Achtung')],
    'missing' => ['class' => 'danger',   'icon' => 'x-circle-fill',      'badge' => t('Fehlt')],
];

$statusIcon = function(string $status) use ($statusConfig): string {
    $cfg = $statusConfig[$status] ?? $statusConfig['missing'];
    return "<i class=\"bi bi-{$cfg['icon']} text-{$cfg['class']}\"></i>";
};

$statusBadge = function(string $status) use ($statusConfig): string {
    $cfg = $statusConfig[$status] ?? $statusConfig['missing'];
    return "<span class=\"badge bg-{$cfg['class']}\">{$cfg['badge']}</span>";
};

$readinessClass = match($score['readiness']) {
    'ready'    => 'success',
    'partial'  => 'warning',
    default    => 'danger',
};

$readinessIcon = match($score['readiness']) {
    'ready'    => 'check-circle-fill',
    'partial'  => 'exclamation-triangle-fill',
    default    => 'x-circle-fill',
};
?>

<!-- Score Hero -->
<div class="row g-4 mb-4">
  <div class="col-12">
    <div class="card border-<?= $readinessClass ?> shadow-sm">
      <div class="card-body d-flex align-items-center gap-4 py-4">
        <div class="text-<?= $readinessClass ?>" style="font-size:3.5rem;line-height:1">
          <i class="bi bi-<?= $readinessIcon ?>"></i>
        </div>
        <div class="flex-grow-1">
          <div class="d-flex align-items-baseline gap-3 mb-1">
            <h2 class="mb-0 fw-bold text-<?= $readinessClass ?>"><?= $e($score['readinessLabel']) ?></h2>
            <span class="fs-4 text-muted fw-semibold"><?= $score['percent'] ?>%</span>
          </div>
          <div class="progress" style="height:10px;max-width:400px">
            <div class="progress-bar bg-<?= $readinessClass ?>" style="width:<?= $score['percent'] ?>%"></div>
          </div>
          <?php if ($org['displayName']): ?>
          <div class="text-muted small mt-2"><?= te('Tenant:') ?> <strong><?= $e($org['displayName']) ?></strong></div>
          <?php endif ?>
        </div>
        <div class="text-end">
          <a href="?refresh=1" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-clockwise"></i> <?= te('Neu prüfen') ?>
          </a>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="row g-4">

  <!-- Left column: DNS + License -->
  <div class="col-lg-8">

    <!-- DNS Checks per domain -->
    <?php if (empty($domains)): ?>
    <div class="card mb-4 border-danger">
      <div class="card-header fw-semibold text-danger-emphasis bg-danger-subtle">
        <i class="bi bi-x-circle-fill me-2"></i><?= te('Keine Custom-Domain gefunden') ?>
      </div>
      <div class="card-body">
        <p class="mb-2">
          <?= te('Es wurden keine verifizierten Custom-Domains gefunden — nur') ?> <code>*.onmicrosoft.com</code>.
          <?= te('Für Exchange Online benötigst du mindestens eine eigene Domain (z.B.') ?> <code>deinefirma.de</code>).
        </p>
        <p class="text-muted small mb-3">
          <strong><?= te('Was zu tun ist:') ?></strong> <?= te('Domain im Microsoft 365 Admin Center hinzufügen, den angezeigten TXT-Eintrag bei deinem DNS-Provider eintragen, dann verifizieren.') ?>
        </p>
        <a href="https://admin.microsoft.com/AdminPortal/Home#/Domains" target="_blank" rel="noopener noreferrer"
           class="btn btn-primary btn-sm">
          <i class="bi bi-box-arrow-up-right me-1"></i><?= te('Domains im Admin Center öffnen') ?>
        </a>
      </div>
    </div>
    <?php else: ?>
    <?php foreach ($domains as $dc): ?>
    <div class="card mb-4 shadow-sm">
      <div class="card-header d-flex align-items-center gap-2 fw-semibold">
        <i class="bi bi-globe2"></i>
        <?= te('DNS-Prüfung für') ?> <code><?= $e($dc['domain']) ?></code>
      </div>
      <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
          <thead class="table-light">
            <tr>
              <th style="width:30px"></th>
              <th style="width:130px"><?= te('Prüfung') ?></th>
              <th><?= te('Ergebnis / Hinweis') ?></th>
              <th style="width:110px"></th>
            </tr>
          </thead>
          <tbody>

            <!-- MX -->
            <?php $mx = $dc['mx']; ?>
            <tr>
              <td class="text-center"><?= $statusIcon($mx['status']) ?></td>
              <td class="fw-semibold"><?= te('MX-Eintrag') ?></td>
              <td>
                <?= $e($mx['label']) ?>
                <?php if (!empty($mx['records'])): ?>
                <div class="text-muted small mt-1">
                  <?php foreach ($mx['records'] as $r): ?>
                  <code><?= $e($r) ?></code><br>
                  <?php endforeach ?>
                </div>
                <?php endif ?>
                <?php if ($mx['status'] !== 'ok'): ?>
                <div class="text-muted small mt-1">
                  <?= te('Erwartet:') ?> <code>*.mail.protection.outlook.com</code> —
                  <strong><?= te('Achtung:') ?></strong> <?= te('Erst nach Abschluss der Migration umstellen!') ?>
                </div>
                <div class="mt-2">
                  <a href="https://admin.microsoft.com/AdminPortal/Home#/Domains" target="_blank" rel="noopener noreferrer"
                     class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-box-arrow-up-right me-1"></i><?= te('Admin Center → Domains (MX-Wert anzeigen)') ?>
                  </a>
                </div>
                <?php endif ?>
              </td>
              <td><?= $statusBadge($mx['status']) ?></td>
            </tr>

            <!-- SPF -->
            <?php $spf = $dc['spf']; ?>
            <tr>
              <td class="text-center"><?= $statusIcon($spf['status']) ?></td>
              <td class="fw-semibold">SPF</td>
              <td>
                <?= $e($spf['label']) ?>
                <?php if ($spf['record'] ?? null): ?>
                <div class="text-muted small mt-1"><code><?= $e($spf['record']) ?></code></div>
                <?php endif ?>
                <?php if ($spf['status'] !== 'ok'): ?>
                <div class="text-muted small mt-1">
                  <?= te('Empfehlung:') ?> <code>v=spf1 include:spf.protection.outlook.com -all</code>
                </div>
                <div class="mt-2">
                  <a href="https://admin.microsoft.com/AdminPortal/Home#/Domains" target="_blank" rel="noopener noreferrer"
                     class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-box-arrow-up-right me-1"></i><?= te('Admin Center → Domains (DNS-Einträge prüfen)') ?>
                  </a>
                </div>
                <?php endif ?>
              </td>
              <td><?= $statusBadge($spf['status']) ?></td>
            </tr>

            <!-- DKIM -->
            <?php $dkim = $dc['dkim']; ?>
            <tr>
              <td class="text-center"><?= $statusIcon($dkim['status']) ?></td>
              <td class="fw-semibold">DKIM</td>
              <td>
                <?= $e($dkim['label']) ?>
                <?php foreach (($dkim['selectors'] ?? []) as $sel => $info): ?>
                <div class="text-muted small mt-1">
                  <code><?= $e($sel) ?>._domainkey</code>:
                  <?php if ($info['found']): ?>
                    → <code><?= $e($info['target']) ?></code>
                    <?= $info['o365'] ? '<span class="text-success">' . t('(Exchange Online ✓)') . '</span>' : '<span class="text-warning">' . t('(nicht Exchange Online)') . '</span>' ?>
                  <?php else: ?>
                    <span class="text-danger"><?= te('nicht gefunden') ?></span>
                  <?php endif ?>
                </div>
                <?php endforeach ?>
                <?php if ($dkim['status'] !== 'ok'): ?>
                <div class="text-muted small mt-1">
                  <?= te('DKIM in Exchange Online aktivieren: Admin Center → E-Mail-Sicherheit → DKIM. Danach die generierten CNAME-Einträge im DNS anlegen.') ?>
                </div>
                <div class="mt-2 d-flex gap-2 flex-wrap">
                  <a href="https://admin.exchange.microsoft.com/#/dkimsettings" target="_blank" rel="noopener noreferrer"
                     class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-box-arrow-up-right me-1"></i><?= te('Exchange Admin → DKIM aktivieren') ?>
                  </a>
                  <a href="https://admin.microsoft.com/AdminPortal/Home#/Domains" target="_blank" rel="noopener noreferrer"
                     class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-box-arrow-up-right me-1"></i><?= te('Admin Center → Domains (CNAME eintragen)') ?>
                  </a>
                </div>
                <?php endif ?>
              </td>
              <td><?= $statusBadge($dkim['status']) ?></td>
            </tr>

            <!-- DMARC -->
            <?php $dmarc = $dc['dmarc']; ?>
            <tr>
              <td class="text-center"><?= $statusIcon($dmarc['status']) ?></td>
              <td class="fw-semibold">DMARC</td>
              <td>
                <?= $e($dmarc['label']) ?>
                <?php if ($dmarc['record'] ?? null): ?>
                <div class="text-muted small mt-1"><code><?= $e($dmarc['record']) ?></code></div>
                <?php endif ?>
                <?php if ($dmarc['status'] === 'missing'): ?>
                <div class="text-muted small mt-1">
                  <?= te('Empfehlung: TXT-Eintrag') ?> <code>_dmarc.<?= $e($dc['domain']) ?></code> <?= te('mit') ?><br>
                  <code>v=DMARC1; p=quarantine; rua=mailto:dmarc@<?= $e($dc['domain']) ?></code>
                </div>
                <div class="mt-2">
                  <a href="https://mxtoolbox.com/dmarc/<?= $e($dc['domain']) ?>" target="_blank" rel="noopener noreferrer"
                     class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-tools me-1"></i><?= te('DMARC-Generator (MXToolbox)') ?>
                  </a>
                </div>
                <?php elseif (($dmarc['policy'] ?? 'none') === 'none'): ?>
                <div class="text-muted small mt-1">
                  <?= te('DMARC ist vorhanden, aber') ?> <code>p=none</code> <?= te('hat keine durchsetzende Wirkung.') ?>
                  <?= te('Empfohlen:') ?> <code>p=quarantine</code> <?= te('oder') ?> <code>p=reject</code> <?= te('nach Testphase.') ?>
                </div>
                <?php endif ?>
              </td>
              <td><?= $statusBadge($dmarc['status']) ?></td>
            </tr>

            <!-- Autodiscover -->
            <?php $ad = $dc['autodiscover']; ?>
            <tr>
              <td class="text-center"><?= $statusIcon($ad['status']) ?></td>
              <td class="fw-semibold">Autodiscover</td>
              <td>
                <?= $e($ad['label']) ?>
                <?php if (($ad['target'] ?? null) && ($ad['type'] ?? null)): ?>
                <div class="text-muted small mt-1">
                  <?= te('Typ:') ?> <code><?= $e($ad['type']) ?></code> → <code><?= $e($ad['target']) ?></code>
                </div>
                <?php endif ?>
                <?php if ($ad['status'] !== 'ok'): ?>
                <div class="text-muted small mt-1">
                  <?= te('Empfehlung: CNAME') ?> <code>autodiscover.<?= $e($dc['domain']) ?></code>
                  → <code>autodiscover.outlook.com</code>
                  <?= te('(erst nach Migration umstellen, damit Outlook-Clients noch auf on-prem zeigen).') ?>
                </div>
                <div class="mt-2">
                  <a href="https://admin.microsoft.com/AdminPortal/Home#/Domains" target="_blank" rel="noopener noreferrer"
                     class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-box-arrow-up-right me-1"></i><?= te('Admin Center → Domains (Autodiscover CNAME)') ?>
                  </a>
                </div>
                <?php endif ?>
              </td>
              <td><?= $statusBadge($ad['status']) ?></td>
            </tr>

          </tbody>
        </table>
      </div>
    </div>
    <?php endforeach ?>
    <?php endif ?>

    <!-- License Coverage -->
    <div class="card mb-4 shadow-sm">
      <div class="card-header fw-semibold"><i class="bi bi-award me-2"></i><?= te('Lizenz-Abdeckung Exchange Online') ?></div>
      <div class="card-body">
        <?php if ($license['licensedUsers'] === 0 && $license['totalUsers'] === 0): ?>
          <div class="text-muted"><?= te('Keine Lizenzdaten verfügbar.') ?></div>
        <?php else: ?>
        <div class="row g-3 mb-3">
          <div class="col-sm-4">
            <div class="p-3 bg-light rounded text-center">
              <div class="fs-2 fw-bold text-primary"><?= $license['licensedUsers'] ?></div>
              <div class="small text-muted"><?= te('Benutzer mit Exchange Online') ?></div>
            </div>
          </div>
          <div class="col-sm-4">
            <div class="p-3 bg-light rounded text-center">
              <div class="fs-2 fw-bold"><?= $license['totalUsers'] ?></div>
              <div class="small text-muted"><?= te('Aktive Mitglieder gesamt') ?></div>
            </div>
          </div>
          <div class="col-sm-4">
            <div class="p-3 bg-light rounded text-center">
              <?php $covClass = $license['coveragePercent'] >= 100 ? 'success' : ($license['coveragePercent'] >= 80 ? 'warning' : 'danger'); ?>
              <div class="fs-2 fw-bold text-<?= $covClass ?>"><?= $license['coveragePercent'] ?>%</div>
              <div class="small text-muted"><?= te('Abdeckung') ?></div>
            </div>
          </div>
        </div>

        <?php if (!empty($license['skus'])): ?>
        <table class="table table-sm table-bordered mb-0">
          <thead class="table-light">
            <tr><th>SKU</th><th class="text-end"><?= te('Bereitgestellt') ?></th><th class="text-end"><?= te('Belegt') ?></th></tr>
          </thead>
          <tbody>
            <?php foreach ($license['skus'] as $sku): ?>
            <tr>
              <td><code><?= $e($sku['name']) ?></code></td>
              <td class="text-end"><?= $sku['enabled'] ?></td>
              <td class="text-end"><?= $sku['consumed'] ?></td>
            </tr>
            <?php endforeach ?>
          </tbody>
        </table>
        <?php endif ?>

        <?php if ($license['coveragePercent'] < 100 && $license['totalUsers'] > 0): ?>
        <div class="alert alert-warning mt-3 mb-0 small">
          <i class="bi bi-exclamation-triangle-fill me-1"></i>
          <?= te(':n Benutzer haben keine Exchange-Online-Lizenz und können nach der Migration keine Postfächer erhalten.', ['n' => $license['totalUsers'] - $license['licensedUsers']]) ?>
          <?= te('Lizenzen zuweisen oder prüfen, ob diese Benutzer kein Postfach benötigen.') ?>
        </div>
        <?php endif ?>
        <?php endif ?>
      </div>
    </div>

  </div>

  <!-- Right column: Hybrid Info + Checklist + Issues -->
  <div class="col-lg-4">

    <!-- Hybrid / AAD Connect -->
    <div class="card mb-4 shadow-sm">
      <div class="card-header fw-semibold"><i class="bi bi-arrow-left-right me-2"></i><?= te('Hybrid-Status (AAD Connect)') ?></div>
      <div class="card-body">
        <?php
        $syncEnabled  = $org['onPremisesSyncEnabled'] ?? false;
        $lastSync     = $org['onPremisesLastSyncDateTime'] ?? null;
        $syncedUsers  = $hybrid['synced'];
        ?>
        <ul class="list-group list-group-flush">
          <li class="list-group-item d-flex justify-content-between align-items-center px-0">
            <span><?= te('AAD Connect / Entra Sync aktiv') ?></span>
            <?php if ($syncEnabled): ?>
              <span class="badge bg-info"><?= te('Ja') ?></span>
            <?php else: ?>
              <span class="badge bg-secondary"><?= te('Nein / unbekannt') ?></span>
            <?php endif ?>
          </li>
          <?php if ($lastSync): ?>
          <li class="list-group-item d-flex justify-content-between align-items-center px-0">
            <span><?= te('Letzter Sync') ?></span>
            <span class="text-muted small"><?= $e(date('d.m.Y H:i', strtotime($lastSync))) ?></span>
          </li>
          <?php endif ?>
          <li class="list-group-item d-flex justify-content-between align-items-center px-0">
            <span><?= te('On-Prem-synchronisierte Benutzer') ?></span>
            <span class="fw-bold"><?= $syncedUsers ?></span>
          </li>
        </ul>

        <?php if ($syncEnabled && $syncedUsers > 0): ?>
        <div class="alert alert-info mt-3 mb-0 small">
          <i class="bi bi-info-circle-fill me-1"></i>
          <?= te('Du betreibst eine Hybrid-Umgebung. Exchange Online kann parallel zu Exchange on-prem genutzt werden (Hybrid-Konfiguration empfohlen). Stelle sicher, dass das') ?> <strong>Exchange Hybrid Agent</strong>
          <?= te('installiert ist, bevor du Postfächer migrierst.') ?>
        </div>
        <?php elseif (!$syncEnabled): ?>
        <div class="alert alert-secondary mt-3 mb-0 small">
          <i class="bi bi-info-circle me-1"></i>
          <?= te('Kein Verzeichnis-Sync erkannt — Cloud-Only-Identitäten oder Sync nicht via Entra konfiguriert. Bei Migration von on-prem AD empfiehlt sich Entra Connect Sync.') ?>
        </div>
        <?php endif ?>
      </div>
    </div>

    <!-- Offene Punkte -->
    <?php if (!empty($score['issues'])): ?>
    <div class="card mb-4 shadow-sm border-warning">
      <div class="card-header fw-semibold text-warning-emphasis bg-warning-subtle">
        <i class="bi bi-exclamation-triangle-fill me-2"></i><?= te('Offene Punkte') ?> (<?= count($score['issues']) ?>)
      </div>
      <ul class="list-group list-group-flush">
        <?php foreach ($score['issues'] as $issue): ?>
        <li class="list-group-item small py-2"><?= $e($issue) ?></li>
        <?php endforeach ?>
      </ul>
    </div>
    <?php endif ?>

    <!-- Migration Checklist -->
    <div class="card mb-4 shadow-sm">
      <div class="card-header fw-semibold"><i class="bi bi-clipboard2-check me-2"></i>Migrations-Checkliste</div>
      <ul class="list-group list-group-flush" id="migrationChecklist">
        <?php
        $checklist = [
            ['label' => 'Exchange Hybrid Configuration Wizard ausgeführt',
             'hint'  => 'Verbindet on-prem Exchange mit Exchange Online (HCW). Erforderlich für Hybrid-Migration.'],
            ['label' => 'Exchange Online Postfach-Migrationsbatch erstellt',
             'hint'  => 'In Exchange Admin Center → Migration → Neuer Batch (z. B. Remote Move).'],
            ['label' => 'MRS Proxy auf on-prem Exchange aktiv',
             'hint'  => 'Mailbox Replication Service Proxy muss auf dem on-prem CAS/MBX aktiviert sein.'],
            ['label' => 'Testpostfach migriert und überprüft',
             'hint'  => 'Migriere zunächst ein Testpostfach; prüfe E-Mail-Empfang, Kalender und OAB.'],
            ['label' => 'Outlook Anywhere / MAPI-over-HTTP konfiguriert',
             'hint'  => 'Stellt sicher, dass Outlook-Clients weiterhin auf on-prem Exchange zugreifen können während der Migrationsphase.'],
            ['label' => 'Outlook-Profile der Benutzer nach Migration erneuert',
             'hint'  => 'Autodiscover leitet Outlook nach MX-Umschaltung automatisch um; ggf. Profil neu erstellen.'],
            ['label' => 'Shared Mailboxes / Room Mailboxes migriert',
             'hint'  => 'Ressourcenpostfächer separat prüfen und ggf. in Exchange Online neu anlegen.'],
            ['label' => 'E-Mail-Archiv geprüft (In-Place Archive / PST)',
             'hint'  => 'PST-Dateien können über das Microsoft 365 Import Tool hochgeladen werden.'],
            ['label' => 'MX-Eintrag auf Exchange Online umgestellt',
             'hint'  => 'Erst nach Abschluss der Migration umstellen, damit keine E-Mails verloren gehen.'],
            ['label' => 'Autodiscover CNAME auf outlook.com umgestellt',
             'hint'  => 'Erst nach der Migration, damit Outlook-Clients das Exchange-Online-Postfach finden.'],
            ['label' => 'DKIM in Exchange Online aktiviert',
             'hint'  => 'Admin Center → Sicherheit → E-Mail-Authentifizierung → DKIM; CNAME-Einträge im DNS anlegen.'],
            ['label' => 'DMARC-Policy auf quarantine/reject erhöht',
             'hint'  => 'Nach erfolgreicher DKIM/SPF-Aktivierung Richtlinie verschärfen.'],
            ['label' => 'on-prem Exchange außer Betrieb genommen (wenn gewünscht)',
             'hint'  => 'Erst wenn alle Postfächer online sind und DNS umgestellt ist.'],
        ];
        foreach ($checklist as $i => $item):
        ?>
        <li class="list-group-item py-2 px-3">
          <div class="form-check">
            <input class="form-check-input" type="checkbox" id="chk_<?= $i ?>" onchange="saveChecklist()">
            <label class="form-check-label small" for="chk_<?= $i ?>">
              <?= $e($item['label']) ?>
            </label>
          </div>
          <?php if ($item['hint']): ?>
          <div class="text-muted" style="font-size:.78rem;padding-left:1.6rem"><?= $e($item['hint']) ?></div>
          <?php endif ?>
        </li>
        <?php endforeach ?>
      </ul>
      <div class="card-footer text-muted small">
        <i class="bi bi-info-circle me-1"></i>Checkboxen werden lokal im Browser gespeichert.
      </div>
    </div>

    <!-- Helpful Links -->
    <div class="card mb-4 shadow-sm">
      <div class="card-header fw-semibold"><i class="bi bi-box-arrow-up-right me-2"></i>Weiterführende Links</div>
      <div class="card-body p-0">
        <ul class="list-group list-group-flush small">
          <li class="list-group-item">
            <a href="https://admin.exchange.microsoft.com/#/migration" target="_blank" rel="noopener noreferrer">
              <i class="bi bi-envelope me-1"></i> Exchange Admin Center – Migration
            </a>
          </li>
          <li class="list-group-item">
            <a href="https://admin.microsoft.com/#/Domains" target="_blank" rel="noopener noreferrer">
              <i class="bi bi-globe2 me-1"></i> Microsoft 365 Admin – Domains
            </a>
          </li>
          <li class="list-group-item">
            <a href="https://admin.exchange.microsoft.com/#/dkimsettings" target="_blank" rel="noopener noreferrer">
              <i class="bi bi-shield-check me-1"></i> Exchange Admin – DKIM
            </a>
          </li>
          <li class="list-group-item">
            <a href="https://learn.microsoft.com/exchange/hybrid-deployment/hybrid-deployment" target="_blank" rel="noopener noreferrer">
              <i class="bi bi-book me-1"></i> Docs: Exchange Hybrid Deployment
            </a>
          </li>
          <li class="list-group-item">
            <a href="https://learn.microsoft.com/exchange/mailbox-migration/mailbox-migration" target="_blank" rel="noopener noreferrer">
              <i class="bi bi-book me-1"></i> Docs: Mailbox Migration Methods
            </a>
          </li>
          <li class="list-group-item">
            <a href="https://testconnectivity.microsoft.com" target="_blank" rel="noopener noreferrer">
              <i class="bi bi-wifi me-1"></i> Microsoft Remote Connectivity Analyzer
            </a>
          </li>
        </ul>
      </div>
    </div>

  </div><!-- /col-lg-4 -->
</div>

<script>
(function () {
    const KEY = 'exmig_checklist_<?= md5($org['displayName'] ?? 'default') ?>';

    function saveChecklist() {
        const state = {};
        document.querySelectorAll('#migrationChecklist input[type=checkbox]').forEach(cb => {
            state[cb.id] = cb.checked;
        });
        localStorage.setItem(KEY, JSON.stringify(state));
    }

    function loadChecklist() {
        try {
            const state = JSON.parse(localStorage.getItem(KEY) || '{}');
            document.querySelectorAll('#migrationChecklist input[type=checkbox]').forEach(cb => {
                if (state[cb.id] !== undefined) cb.checked = state[cb.id];
            });
        } catch (e) { /* ignore */ }
    }

    window.saveChecklist = saveChecklist;
    document.addEventListener('DOMContentLoaded', loadChecklist);
})();
</script>
