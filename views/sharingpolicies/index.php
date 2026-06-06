<?php
use App\Core\View;
use App\Modules\SharingPolicies\SharingPoliciesService;

$e = fn($v) => View::escape($v);
$sp = $spSettings ?? [];
$hasSpError = isset($sp['_error']);
?>
<?php \App\Core\View::partial('partials/module_tabs', ['tabs' => [['label'=>'Freigaben','href'=>'/sharing','icon'=>'link-45deg'],['label'=>'Monitor','href'=>'/sharing/monitor','icon'=>'eye-slash'],['label'=>'Richtlinien','href'=>'/sharing/policies','icon'=>'sliders'],]]); ?>


<?php if ($flash): ?>
    <div class="alert alert-success alert-dismissible mb-4">
        <i class="bi bi-check-circle me-2"></i><?= $e($flash) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible mb-4">
        <i class="bi bi-exclamation-triangle me-2"></i><?= $e($error) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Permission hint -->
<div class="alert alert-info d-flex align-items-center gap-2 mb-4" style="font-size:13px;">
    <i class="bi bi-info-circle-fill flex-shrink-0"></i>
    <div>
        Zum <strong>Lesen und Ändern</strong> der SharePoint-Mandanteneinstellungen ist
        <code>SharePointTenantSettings.ReadWrite.All</code> erforderlich.
        Für Site-Freigabe-Übersichten reicht <code>Sites.Read.All</code>.
    </div>
</div>

<!-- Tabs -->
<ul class="nav nav-tabs mb-4" id="policyTabs">
    <li class="nav-item">
        <a class="nav-link active" data-bs-toggle="tab" href="#tab-sp">
            <i class="bi bi-share me-1"></i>SharePoint &amp; OneDrive
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-bs-toggle="tab" href="#tab-sites">
            <i class="bi bi-building me-1"></i>Einzelne Sites
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-bs-toggle="tab" href="#tab-teams">
            <i class="bi bi-people me-1"></i>Teams &amp; Extern
        </a>
    </li>
</ul>

<div class="tab-content">

    <!-- ── SharePoint / OneDrive global settings ─────────── -->
    <div class="tab-pane fade show active" id="tab-sp">
        <?php if ($hasSpError): ?>
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle me-2"></i>
                SharePoint-Einstellungen konnten nicht geladen werden.
                Möglicherweise fehlt die Berechtigung <code>SharePointTenantSettings.ReadWrite.All</code>.<br>
                <small class="text-muted"><?= $e($sp['_error']) ?></small>
            </div>
        <?php else: ?>

        <!-- Current state overview -->
        <div class="row g-3 mb-4">
            <?php
            $cap   = $sp['sharingCapability'] ?? '';
            $capInfo = SharingPoliciesService::sharingCapabilityLabel($cap);
            ?>
            <div class="col-md-4">
                <div class="content-card p-4 text-center">
                    <i class="bi bi-<?= $capInfo['icon'] ?> text-primary mb-2" style="font-size:28px;"></i>
                    <div class="fw-bold mb-1">Externer Zugriff</div>
                    <span class="badge <?= $capInfo['class'] ?> rounded-pill px-3"><?= $e($capInfo['label']) ?></span>
                </div>
            </div>
            <div class="col-md-4">
                <div class="content-card p-4 text-center">
                    <i class="bi bi-link-45deg text-primary mb-2" style="font-size:28px;"></i>
                    <div class="fw-bold mb-1">Standard-Linktyp</div>
                    <div class="text-muted small"><?= $e(SharingPoliciesService::linkTypeLabel($sp['defaultSharingLinkType'] ?? '')) ?></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="content-card p-4 text-center">
                    <i class="bi bi-pencil-square text-primary mb-2" style="font-size:28px;"></i>
                    <div class="fw-bold mb-1">Standard-Berechtigung</div>
                    <div class="text-muted small"><?= $e(SharingPoliciesService::permissionLabel($sp['defaultLinkPermission'] ?? '')) ?></div>
                </div>
            </div>
        </div>

        <form method="post" action="/sharing/policies/sharepoint">
            <?= \App\Core\Csrf::field() ?>
            <div class="content-card mb-4">
                <div class="card-header-custom">
                    <i class="bi bi-sliders text-primary"></i>
                    <h6>SharePoint &amp; OneDrive — Globale Freigabeeinstellungen</h6>
                </div>
                <div class="card-body-custom">
                    <div class="row g-4">

                        <!-- Sharing capability -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Externer Zugriff</label>
                            <p class="text-muted small mb-2">Steuert, wer Inhalte außerhalb der Organisation teilen darf.</p>
                            <?php
                            $caps = [
                                'ExternalUserAndGuestSharing'     => ['label' => 'Alle (inkl. anonyme Links)', 'desc' => 'Jeder mit Link, keine Anmeldung erforderlich', 'color' => 'danger'],
                                'ExternalUserSharingOnly'         => ['label' => 'Neue & bestehende Gäste', 'desc' => 'Externe Benutzer müssen sich anmelden', 'color' => 'warning'],
                                'ExistingExternalUserSharingOnly' => ['label' => 'Nur bestehende Gäste', 'desc' => 'Nur bereits eingeladene Externe', 'color' => 'info'],
                                'Disabled'                        => ['label' => 'Nur intern', 'desc' => 'Keine externen Freigaben möglich', 'color' => 'success'],
                            ];
                            foreach ($caps as $val => $opt): ?>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="sharingCapability"
                                       id="cap_<?= $val ?>" value="<?= $val ?>"
                                       <?= ($sp['sharingCapability'] ?? '') === $val ? 'checked' : '' ?>>
                                <label class="form-check-label" for="cap_<?= $val ?>">
                                    <span class="fw-medium text-<?= $opt['color'] ?>"><?= $opt['label'] ?></span>
                                    <span class="text-muted d-block small"><?= $opt['desc'] ?></span>
                                </label>
                            </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Link settings -->
                        <div class="col-md-6">
                            <div class="mb-4">
                                <label class="form-label fw-semibold">Standard-Linktyp</label>
                                <p class="text-muted small mb-2">Welcher Link-Typ wird standardmäßig beim Teilen vorgeschlagen?</p>
                                <select name="defaultSharingLinkType" class="form-select">
                                    <option value="anonymous" <?= ($sp['defaultSharingLinkType'] ?? '') === 'anonymous' ? 'selected' : '' ?>>🌐 Jeder mit dem Link (anonym)</option>
                                    <option value="organization" <?= ($sp['defaultSharingLinkType'] ?? '') === 'organization' ? 'selected' : '' ?>>🏢 Personen in der Organisation</option>
                                    <option value="direct" <?= ($sp['defaultSharingLinkType'] ?? '') === 'direct' ? 'selected' : '' ?>>👤 Nur bestimmte Personen</option>
                                </select>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-semibold">Standard-Berechtigung</label>
                                <select name="defaultLinkPermission" class="form-select">
                                    <option value="view" <?= ($sp['defaultLinkPermission'] ?? '') === 'view' ? 'selected' : '' ?>>👁 Anzeigen</option>
                                    <option value="edit" <?= ($sp['defaultLinkPermission'] ?? '') === 'edit' ? 'selected' : '' ?>>✏️ Bearbeiten</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Anonymer Link für Dateien</label>
                                <select name="fileAnonymousLinkType" class="form-select">
                                    <option value="view"  <?= ($sp['fileAnonymousLinkType'] ?? '') === 'view'  ? 'selected' : '' ?>>Nur anzeigen</option>
                                    <option value="edit"  <?= ($sp['fileAnonymousLinkType'] ?? '') === 'edit'  ? 'selected' : '' ?>>Anzeigen &amp; bearbeiten</option>
                                    <option value="none"  <?= ($sp['fileAnonymousLinkType'] ?? '') === 'none'  ? 'selected' : '' ?>>Keine anonymen Links</option>
                                </select>
                            </div>

                            <div>
                                <label class="form-label fw-semibold">Anonymer Link für Ordner</label>
                                <select name="folderAnonymousLinkType" class="form-select">
                                    <option value="view"  <?= ($sp['folderAnonymousLinkType'] ?? '') === 'view'  ? 'selected' : '' ?>>Nur anzeigen</option>
                                    <option value="edit"  <?= ($sp['folderAnonymousLinkType'] ?? '') === 'edit'  ? 'selected' : '' ?>>Anzeigen &amp; bearbeiten</option>
                                    <option value="none"  <?= ($sp['folderAnonymousLinkType'] ?? '') === 'none'  ? 'selected' : '' ?>>Keine anonymen Links</option>
                                </select>
                            </div>
                        </div>

                        <!-- Additional flags -->
                        <div class="col-12">
                            <hr>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Gast-Benutzer-Synchronisation</label>
                                    <select name="isGuestUserSyncToSharePointAllowed" class="form-select form-select-sm">
                                        <option value="1" <?= ($sp['isGuestUserSyncToSharePointAllowed'] ?? false) ? 'selected' : '' ?>>Aktiviert</option>
                                        <option value="0" <?= !($sp['isGuestUserSyncToSharePointAllowed'] ?? true) ? 'selected' : '' ?>>Deaktiviert</option>
                                    </select>
                                    <div class="form-text">Gäste können Inhalte über SharePoint synchronisieren.</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Self-Service-Anmeldung (Externe)</label>
                                    <select name="isExternalUserSelfServiceSignUpEnabled" class="form-select form-select-sm">
                                        <option value="1" <?= ($sp['isExternalUserSelfServiceSignUpEnabled'] ?? false) ? 'selected' : '' ?>>Aktiviert</option>
                                        <option value="0" <?= !($sp['isExternalUserSelfServiceSignUpEnabled'] ?? true) ? 'selected' : '' ?>>Deaktiviert</option>
                                    </select>
                                    <div class="form-text">Externe können sich selbst für den Zugriff registrieren.</div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary px-4 mb-4">
                <i class="bi bi-check2 me-1"></i>SharePoint-Einstellungen speichern
            </button>
        </form>
        <?php endif; ?>
    </div>

    <!-- ── Individual site settings ──────────────────────── -->
    <div class="tab-pane fade" id="tab-sites">
        <div class="content-card">
            <div class="card-header-custom">
                <i class="bi bi-building text-primary"></i>
                <h6>Freigabe-Einstellung pro Site Collection</h6>
                <span class="text-muted ms-auto" style="font-size:12px;"><?= count($sites ?? []) ?> Sites</span>
            </div>
            <div class="card-body-custom p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Site</th>
                                <th>URL</th>
                                <th>Aktuell</th>
                                <th style="width:220px;">Ändern</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($sites)): ?>
                            <tr><td colspan="4" class="text-center text-muted py-4">
                                <i class="bi bi-info-circle me-1"></i>Keine Sites geladen oder fehlende Berechtigung.
                            </td></tr>
                        <?php else: ?>
                        <?php foreach ($sites as $site):
                            $siteCapInfo = SharingPoliciesService::sharingCapabilityLabel($site['sharingCapability'] ?? '');
                        ?>
                            <tr>
                                <td class="fw-medium"><?= $e($site['displayName'] ?? '—') ?></td>
                                <td>
                                    <?php if (!empty($site['webUrl'])): ?>
                                    <a href="<?= $e($site['webUrl']) ?>" target="_blank" class="text-muted text-decoration-none" style="font-size:12px;">
                                        <?= $e($site['webUrl']) ?>
                                        <i class="bi bi-box-arrow-up-right ms-1"></i>
                                    </a>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge <?= $siteCapInfo['class'] ?> rounded-pill">
                                        <i class="bi bi-<?= $siteCapInfo['icon'] ?> me-1"></i><?= $e($siteCapInfo['label']) ?>
                                    </span>
                                </td>
                                <td>
                                    <form method="post" action="/sharing/policies/site" class="d-flex gap-2">
                                        <?= \App\Core\Csrf::field() ?>
                                        <input type="hidden" name="site_id" value="<?= $e($site['id']) ?>">
                                        <select name="capability" class="form-select form-select-sm">
                                            <option value="ExternalUserAndGuestSharing" <?= ($site['sharingCapability'] ?? '') === 'ExternalUserAndGuestSharing' ? 'selected' : '' ?>>Alle</option>
                                            <option value="ExternalUserSharingOnly" <?= ($site['sharingCapability'] ?? '') === 'ExternalUserSharingOnly' ? 'selected' : '' ?>>Neue Gäste</option>
                                            <option value="ExistingExternalUserSharingOnly" <?= ($site['sharingCapability'] ?? '') === 'ExistingExternalUserSharingOnly' ? 'selected' : '' ?>>Bestehende Gäste</option>
                                            <option value="Disabled" <?= ($site['sharingCapability'] ?? '') === 'Disabled' ? 'selected' : '' ?>>Nur intern</option>
                                        </select>
                                        <button type="submit" class="btn btn-sm btn-outline-primary" title="Speichern">
                                            <i class="bi bi-check2"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- ── Teams & external access ──────────────────────── -->
    <div class="tab-pane fade" id="tab-teams">
        <?php
        $ct = $crossTenant ?? [];
        $ctDefaults = $ct['defaults'] ?? [];
        $ctError = $ct['_error'] ?? null;
        $teamsErr = isset($teamsSettings['_error']);
        ?>

        <div class="row g-4">
            <!-- Teams general -->
            <div class="col-md-6">
                <div class="content-card h-100">
                    <div class="card-header-custom">
                        <i class="bi bi-microsoft-teams text-primary"></i>
                        <h6>Microsoft Teams Status</h6>
                    </div>
                    <div class="card-body-custom">
                        <?php if ($teamsErr): ?>
                            <div class="alert alert-warning small mb-0">
                                <i class="bi bi-exclamation-triangle me-1"></i>
                                Teamwork-Daten nicht verfügbar.
                                <div class="text-muted mt-1"><?= $e($teamsSettings['_error']) ?></div>
                            </div>
                        <?php else: ?>
                            <table class="table table-sm mb-0">
                                <tr>
                                    <td class="text-muted">Teams aktiviert</td>
                                    <td>
                                        <?php $isEnabled = ($teamsSettings['isTeamsEnabled'] ?? false) ? true : null; ?>
                                        <?php if ($isEnabled === null): ?>
                                            <span class="text-muted">—</span>
                                        <?php elseif ($isEnabled): ?>
                                            <span class="badge badge-success">Ja</span>
                                        <?php else: ?>
                                            <span class="badge badge-danger">Nein</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            </table>
                            <p class="text-muted small mt-3 mb-0">
                                <i class="bi bi-info-circle me-1"></i>
                                Erweiterte Teams-Einstellungen (Gäste, externe Channels) werden über
                                das <a href="https://admin.teams.microsoft.com" target="_blank">Teams Admin Center</a> verwaltet.
                                Die Graph API bietet hier nur lesenden Zugriff.
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Cross-tenant access -->
            <div class="col-md-6">
                <div class="content-card h-100">
                    <div class="card-header-custom">
                        <i class="bi bi-diagram-3 text-primary"></i>
                        <h6>Mandantenübergreifender Zugriff</h6>
                    </div>
                    <div class="card-body-custom">
                        <?php if ($ctError): ?>
                            <div class="alert alert-warning small mb-0">
                                <i class="bi bi-exclamation-triangle me-1"></i>
                                Cross-Tenant-Policy nicht lesbar.
                                <div class="text-muted mt-1"><?= $e($ctError) ?></div>
                            </div>
                        <?php else: ?>
                            <?php $b2bIn  = $ctDefaults['b2bCollaborationInbound']['usersAndGroups']['accessType']  ?? null;
                                  $b2bOut = $ctDefaults['b2bCollaborationOutbound']['usersAndGroups']['accessType'] ?? null; ?>
                            <table class="table table-sm mb-0">
                                <tr>
                                    <td class="text-muted">B2B eingehend</td>
                                    <td><?= $b2bIn  ? '<span class="badge badge-' . ($b2bIn  === 'allowed' ? 'success' : 'danger') . '">' . $e($b2bIn) . '</span>' : '<span class="text-muted">—</span>' ?></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">B2B ausgehend</td>
                                    <td><?= $b2bOut ? '<span class="badge badge-' . ($b2bOut === 'allowed' ? 'success' : 'danger') . '">' . $e($b2bOut) . '</span>' : '<span class="text-muted">—</span>' ?></td>
                                </tr>
                            </table>
                            <p class="text-muted small mt-3 mb-0">
                                <i class="bi bi-info-circle me-1"></i>
                                Zum Ändern der mandantenübergreifenden Richtlinien ist die Berechtigung
                                <code>Policy.ReadWrite.CrossTenantAccess</code> erforderlich.
                                Änderungen können im <a href="https://entra.microsoft.com" target="_blank">Entra Admin Center</a> vorgenommen werden.
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Quick links -->
            <div class="col-12">
                <div class="content-card">
                    <div class="card-header-custom">
                        <i class="bi bi-box-arrow-up-right text-primary"></i>
                        <h6>Admin-Portale</h6>
                    </div>
                    <div class="card-body-custom">
                        <div class="row g-3">
                            <?php $portals = [
                                ['icon' => 'microsoft',        'label' => 'M365 Admin Center',      'url' => 'https://admin.microsoft.com',                        'desc' => 'Benutzer, Lizenzen, Apps'],
                                ['icon' => 'shield-check',     'label' => 'Entra ID (Azure AD)',     'url' => 'https://entra.microsoft.com',                        'desc' => 'Identitäten, Gäste, CA-Policies'],
                                ['icon' => 'share',            'label' => 'SharePoint Admin',        'url' => 'https://admin.microsoft.com/sharepoint',             'desc' => 'Sites, Freigaben, Storage'],
                                ['icon' => 'people',           'label' => 'Teams Admin',             'url' => 'https://admin.teams.microsoft.com',                  'desc' => 'Teams, Kanäle, Meetings'],
                                ['icon' => 'phone',            'label' => 'Intune (Endpoint Mgr)',   'url' => 'https://intune.microsoft.com',                       'desc' => 'Geräte, Compliance, Apps'],
                                ['icon' => 'graph-up-arrow',   'label' => 'Defender / Purview',      'url' => 'https://security.microsoft.com',                     'desc' => 'Sicherheit, DLP, Compliance'],
                            ]; ?>
                            <?php foreach ($portals as $p): ?>
                            <div class="col-md-4">
                                <a href="<?= $e($p['url']) ?>" target="_blank"
                                   class="d-flex align-items-center gap-3 p-3 rounded border text-decoration-none text-dark"
                                   style="transition:.15s;border-color:#e5e7eb!important;"
                                   onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background=''">
                                    <i class="bi bi-<?= $p['icon'] ?> text-primary" style="font-size:20px;flex-shrink:0;"></i>
                                    <div>
                                        <div class="fw-medium" style="font-size:13px;"><?= $e($p['label']) ?></div>
                                        <div class="text-muted" style="font-size:11px;"><?= $e($p['desc']) ?></div>
                                    </div>
                                    <i class="bi bi-box-arrow-up-right ms-auto text-muted" style="font-size:11px;"></i>
                                </a>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
// Preserve active tab across page loads
document.addEventListener('DOMContentLoaded', function () {
    const hash = location.hash;
    if (hash === '#sites') {
        document.querySelector('[href="#tab-sites"]')?.click();
    } else if (hash === '#teams') {
        document.querySelector('[href="#tab-teams"]')?.click();
    }
    document.querySelectorAll('#policyTabs .nav-link').forEach(tab => {
        tab.addEventListener('shown.bs.tab', e => {
            history.replaceState(null, null, ' ');
        });
    });
});
</script>
