<?php
$roleLabel = fn(string $r) => $r === 'admin' ? 'Administrator' : 'Operator';
$roleClass = fn(string $r) => $r === 'admin' ? 'badge bg-danger' : 'badge bg-primary';
?>

<?php if (!empty($flash)): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-2"></i><?= htmlspecialchars($flash) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>
<?php if (!empty($error)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle me-2"></i><?= htmlspecialchars($error) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row g-4">

    <!-- User table -->
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                <div>
                    <h6 class="mb-0 fw-semibold"><i class="bi bi-microsoft me-2 text-primary"></i>M365-Benutzer mit Tool-Zugriff</h6>
                    <p class="text-muted small mb-0 mt-1">
                        Benutzer melden sich mit ihrem Microsoft-Konto an. UPN = Azure-Anmeldeadresse (z.B. user@firma.de).
                    </p>
                </div>
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addUserModal">
                    <i class="bi bi-person-plus me-1"></i>Benutzer hinzufügen
                </button>
            </div>
            <div class="card-body p-0">
                <?php if (empty($users)): ?>
                    <div class="text-center text-muted py-5">
                        <i class="bi bi-people" style="font-size:2.5rem;opacity:.3;"></i>
                        <p class="mt-3 mb-0">Noch keine Benutzer konfiguriert.</p>
                        <p class="small">Fügen Sie M365-Benutzer hinzu, damit diese sich anmelden können.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>UPN / E-Mail</th>
                                    <th>Anzeigename</th>
                                    <th>Rolle</th>
                                    <th>Status</th>
                                    <th>Letzter Login</th>
                                    <th class="text-end">Aktionen</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($users as $u): ?>
                                <tr>
                                    <td>
                                        <span class="fw-medium"><?= htmlspecialchars($u['upn']) ?></span>
                                        <?php if (!$u['azure_object_id']): ?>
                                            <span class="badge bg-warning text-dark ms-1" title="Noch kein Login — wird beim ersten Anmelden verknüpft">
                                                <i class="bi bi-clock"></i> ausstehend
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-muted small">
                                        <?= htmlspecialchars($u['display_name'] ?: '—') ?>
                                    </td>
                                    <td>
                                        <span class="<?= $roleClass($u['role']) ?> rounded-pill px-2">
                                            <?= $roleLabel($u['role']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($u['is_active']): ?>
                                            <span class="badge bg-success rounded-pill"><i class="bi bi-check2 me-1"></i>Aktiv</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary rounded-pill"><i class="bi bi-dash me-1"></i>Deaktiviert</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-muted small">
                                        <?= $u['last_login'] ? htmlspecialchars(date('d.m.Y H:i', strtotime($u['last_login']))) : '—' ?>
                                    </td>
                                    <td class="text-end">
                                        <button class="btn btn-sm btn-outline-secondary"
                                                data-bs-toggle="modal"
                                                data-bs-target="#editModal<?= (int)$u['id'] ?>"
                                                title="Bearbeiten">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger ms-1"
                                                data-bs-toggle="modal"
                                                data-bs-target="#delModal<?= (int)$u['id'] ?>"
                                                title="Entfernen">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- OAuth redirect URI hint -->
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-info-circle me-2 text-info"></i>Azure App-Konfiguration</h6>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-2">
                    Damit M365-Benutzer sich anmelden können, muss in Ihrer Azure App-Registrierung eine
                    <strong>Redirect-URI</strong> hinterlegt sein. Tragen Sie folgende URI ein:
                </p>
                <div class="input-group input-group-sm">
                    <input type="text" class="form-control font-monospace" id="redirectUriField"
                           value="<?= htmlspecialchars($redirectUri) ?>" readonly>
                    <button class="btn btn-outline-secondary" type="button" onclick="
                        navigator.clipboard.writeText(document.getElementById('redirectUriField').value);
                        this.innerHTML='<i class=\'bi bi-check\'></i>';
                        setTimeout(() => this.innerHTML='<i class=\'bi bi-clipboard\'></i>', 1500);
                    "><i class="bi bi-clipboard"></i></button>
                </div>
                <p class="text-muted small mt-2 mb-0">
                    Pfad in Azure: <strong>App-Registrierungen → Ihre App → Authentifizierung → Redirect-URIs</strong><br>
                    Außerdem benötigt die App die delegierte Berechtigung <code>User.Read</code>.
                </p>
            </div>
        </div>
    </div>

    <!-- Role explanation -->
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-shield-check me-2 text-success"></i>Rollen</h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="p-3 rounded-3 border">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span class="badge bg-primary rounded-pill">Operator</span>
                                <span class="fw-medium">Standard-IT-Mitarbeiter</span>
                            </div>
                            <ul class="text-muted small mb-0 ps-3">
                                <li>Alle Monitoring-Module lesen</li>
                                <li>Scans ausführen, Erinnerungen senden</li>
                                <li>Freigaben manuell widerrufen</li>
                                <li>Kein Zugriff auf Einstellungen &amp; Updates</li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 rounded-3 border">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span class="badge bg-danger rounded-pill">Administrator</span>
                                <span class="fw-medium">Vollzugriff</span>
                            </div>
                            <ul class="text-muted small mb-0 ps-3">
                                <li>Alle Operator-Rechte</li>
                                <li>Einstellungen bearbeiten</li>
                                <li>Benutzer verwalten</li>
                                <li>Updates einspielen</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Add user modal -->
<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-person-plus me-2"></i>Benutzer hinzufügen</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="post" action="/settings/users" id="addUserForm">
                <?= \App\Core\Csrf::field() ?>
                <div class="modal-body">
                    <!-- Tenant user picker -->
                    <div class="mb-3" id="searchWrap">
                        <label class="form-label fw-medium">Benutzer suchen <span class="text-danger">*</span></label>
                        <div class="position-relative">
                            <span class="position-absolute top-50 translate-middle-y ms-3" style="pointer-events:none; color:#9ca3af;">
                                <i class="bi bi-search" id="searchIcon"></i>
                                <span id="searchSpinner" class="spinner-border spinner-border-sm d-none" style="width:.85rem;height:.85rem;"></span>
                            </span>
                            <input type="text" id="userSearchInput" class="form-control ps-5"
                                   placeholder="Name oder E-Mail eingeben…" autocomplete="off">
                            <div id="userSearchDropdown"
                                 class="dropdown-menu w-100 shadow-sm py-1 d-none"
                                 style="max-height:220px; overflow-y:auto; margin-top:2px;">
                            </div>
                        </div>
                        <!-- Selected user chip -->
                        <div id="selectedUserChip" class="d-none mt-2 px-3 py-2 rounded-3 d-flex align-items-center gap-2"
                             style="background:#eff6ff; border:1px solid #bfdbfe;">
                            <i class="bi bi-person-check-fill text-primary"></i>
                            <div class="flex-grow-1 overflow-hidden">
                                <div id="chipName" class="fw-medium small text-truncate"></div>
                                <div id="chipUpn" class="text-muted" style="font-size:11px;" class="text-truncate"></div>
                            </div>
                            <button type="button" class="btn-close btn-sm flex-shrink-0" id="clearSelection" style="font-size:.65rem;"></button>
                        </div>
                        <!-- Fallback: manual entry toggle -->
                        <div class="mt-2">
                            <button type="button" class="btn btn-link btn-sm p-0 text-muted" id="toggleManual"
                                    style="font-size:12px;">
                                <i class="bi bi-keyboard me-1"></i>UPN manuell eingeben
                            </button>
                        </div>
                    </div>

                    <!-- Manual UPN input (hidden by default) -->
                    <div class="mb-3 d-none" id="manualWrap">
                        <label class="form-label fw-medium">UPN / E-Mail <span class="text-danger">*</span></label>
                        <input type="email" id="manualUpnInput" class="form-control"
                               placeholder="max.muster@firma.de">
                        <button type="button" class="btn btn-link btn-sm p-0 text-muted mt-1" id="toggleSearch"
                                style="font-size:12px;">
                            <i class="bi bi-search me-1"></i>Suche verwenden
                        </button>
                    </div>

                    <!-- Hidden field that is actually submitted -->
                    <input type="hidden" name="upn" id="upnHidden">

                    <div class="mb-1">
                        <label class="form-label fw-medium">Rolle</label>
                        <select name="role" class="form-select">
                            <option value="operator" selected>Operator (empfohlen für IT-Mitarbeiter)</option>
                            <option value="admin">Administrator</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Abbrechen</button>
                    <button type="submit" class="btn btn-primary" id="addSubmitBtn" disabled>
                        <i class="bi bi-check2 me-1"></i>Hinzufügen
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
(function () {
    const searchInput    = document.getElementById('userSearchInput');
    const searchIcon     = document.getElementById('searchIcon');
    const searchSpinner  = document.getElementById('searchSpinner');
    const dropdown       = document.getElementById('userSearchDropdown');
    const chip           = document.getElementById('selectedUserChip');
    const chipName       = document.getElementById('chipName');
    const chipUpn        = document.getElementById('chipUpn');
    const clearBtn       = document.getElementById('clearSelection');
    const upnHidden      = document.getElementById('upnHidden');
    const submitBtn      = document.getElementById('addSubmitBtn');
    const toggleManual   = document.getElementById('toggleManual');
    const toggleSearch   = document.getElementById('toggleSearch');
    const manualWrap     = document.getElementById('manualWrap');
    const searchWrap     = document.getElementById('searchWrap');
    const manualInput    = document.getElementById('manualUpnInput');

    // Reset when modal opens
    document.getElementById('addUserModal').addEventListener('show.bs.modal', resetForm);

    function resetForm() {
        searchInput.value  = '';
        manualInput.value  = '';
        upnHidden.value    = '';
        dropdown.classList.add('d-none');
        chip.classList.add('d-none');
        submitBtn.disabled = true;
        manualWrap.classList.add('d-none');
        searchWrap.classList.remove('d-none');
    }

    function setBusy(busy) {
        if (busy) {
            searchIcon.classList.add('d-none');
            searchSpinner.classList.remove('d-none');
        } else {
            searchIcon.classList.remove('d-none');
            searchSpinner.classList.add('d-none');
        }
    }

    // Toggle manual entry
    toggleManual.addEventListener('click', () => {
        searchWrap.classList.add('d-none');
        manualWrap.classList.remove('d-none');
        upnHidden.value    = '';
        submitBtn.disabled = true;
        manualInput.focus();
    });
    toggleSearch.addEventListener('click', () => {
        manualWrap.classList.add('d-none');
        searchWrap.classList.remove('d-none');
        manualInput.value  = '';
        upnHidden.value    = '';
        submitBtn.disabled = true;
        searchInput.focus();
    });

    // Manual input — sync to hidden field
    manualInput.addEventListener('input', () => {
        upnHidden.value    = manualInput.value.trim();
        submitBtn.disabled = !manualInput.value.trim().includes('@');
    });

    // Search input — AJAX against /settings/users/search with debounce.
    let searchTimer = null;
    let activeRequest = 0;
    searchInput.addEventListener('input', function () {
        const q = this.value.trim();
        if (q.length < 2) { dropdown.classList.add('d-none'); setBusy(false); return; }
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => runSearch(q), 200);
    });

    function runSearch(q) {
        const reqId = ++activeRequest;
        setBusy(true);
        fetch('/settings/users/search?q=' + encodeURIComponent(q), { credentials: 'same-origin' })
            .then(r => r.json().then(j => ({ ok: r.ok, body: j })))
            .then(({ ok, body }) => {
                if (reqId !== activeRequest) return; // stale response
                setBusy(false);
                if (!ok || (body && body.error)) {
                    renderError(body && body.error ? body.error : 'Suche fehlgeschlagen');
                    return;
                }
                renderDropdown(Array.isArray(body) ? body : []);
            })
            .catch(err => {
                if (reqId !== activeRequest) return;
                setBusy(false);
                renderError('Netzwerkfehler: ' + err.message);
            });
    }

    searchInput.addEventListener('keydown', e => {
        if (e.key === 'Escape') { dropdown.classList.add('d-none'); searchInput.blur(); }
    });

    document.addEventListener('click', e => {
        if (!e.target.closest('#searchWrap')) dropdown.classList.add('d-none');
    });

    function renderDropdown(users) {
        dropdown.innerHTML = '';
        if (!users.length) {
            const el = document.createElement('div');
            el.className = 'dropdown-item text-muted small pe-none';
            el.innerHTML = '<i class="bi bi-search me-1"></i>Keine Benutzer gefunden';
            dropdown.appendChild(el);
        } else {
            users.forEach(u => {
                const el = document.createElement('div');
                el.className = 'dropdown-item py-2';
                el.style.cursor = 'pointer';
                el.innerHTML =
                    '<div class="fw-medium small">' + esc(u.displayName || u.userPrincipalName) + '</div>' +
                    '<div class="text-muted" style="font-size:11px;">' + esc(u.userPrincipalName) + '</div>';
                el.addEventListener('click', () => selectUser(u));
                dropdown.appendChild(el);
            });
        }
        dropdown.classList.remove('d-none');
    }

    function renderError(msg) {
        dropdown.innerHTML = '';
        const el = document.createElement('div');
        el.className = 'dropdown-item small pe-none';
        el.style.color = '#dc2626';
        el.innerHTML = '<i class="bi bi-exclamation-triangle me-1"></i>' + esc(msg)
            + '<div class="text-muted mt-1" style="font-size:11px;">Tipp: UPN manuell eingeben.</div>';
        dropdown.appendChild(el);
        dropdown.classList.remove('d-none');
    }

    function selectUser(u) {
        upnHidden.value    = u.userPrincipalName;
        chipName.textContent = u.displayName || u.userPrincipalName;
        chipUpn.textContent  = u.userPrincipalName;
        chip.classList.remove('d-none');
        dropdown.classList.add('d-none');
        searchInput.value  = '';
        submitBtn.disabled = false;
    }

    clearBtn.addEventListener('click', () => {
        upnHidden.value    = '';
        chip.classList.add('d-none');
        submitBtn.disabled = true;
        searchInput.focus();
    });

    function esc(str) {
        return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }
})();
</script>

<!-- Edit & delete modals per user -->
<?php foreach ($users as $u): ?>
<div class="modal fade" id="editModal<?= (int)$u['id'] ?>" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Benutzer bearbeiten</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="post" action="/settings/users/<?= (int)$u['id'] ?>/update">
                <?= \App\Core\Csrf::field() ?>
                <div class="modal-body">
                    <p class="fw-medium mb-3"><?= htmlspecialchars($u['upn']) ?></p>
                    <div class="mb-3">
                        <label class="form-label fw-medium">Rolle</label>
                        <select name="role" class="form-select">
                            <option value="operator" <?= $u['role'] === 'operator' ? 'selected' : '' ?>>Operator</option>
                            <option value="admin"    <?= $u['role'] === 'admin'    ? 'selected' : '' ?>>Administrator</option>
                        </select>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_active" id="active<?= (int)$u['id'] ?>"
                               <?= $u['is_active'] ? 'checked' : '' ?>>
                        <label class="form-check-label" for="active<?= (int)$u['id'] ?>">Zugriff aktiv</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Abbrechen</button>
                    <button type="submit" class="btn btn-primary">Speichern</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="delModal<?= (int)$u['id'] ?>" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-danger"><i class="bi bi-trash me-2"></i>Benutzer entfernen</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Soll <strong><?= htmlspecialchars($u['upn']) ?></strong> wirklich entfernt werden?</p>
                <p class="text-muted small mb-0">Der Benutzer verliert sofort den Zugriff.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Abbrechen</button>
                <form method="post" action="/settings/users/<?= (int)$u['id'] ?>/delete">
                    <?= \App\Core\Csrf::field() ?>
                    <button type="submit" class="btn btn-danger">Entfernen</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php endforeach; ?>
