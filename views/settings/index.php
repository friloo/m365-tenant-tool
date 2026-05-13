<?php use App\Core\View; $e = fn($v) => View::escape($v); $s = $settings; ?>

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

<div class="row g-4">
    <div class="col-lg-8">
        <form method="post" action="/settings/save">
            <?= \App\Core\Csrf::field() ?>

        <!-- KI-Sicherheitsberater -->
        <div class="content-card mb-4" id="ai-advisor">
            <div class="card-header-custom">
                <i class="bi bi-robot text-primary"></i>
                <h6>KI-Sicherheitsberater</h6>
            </div>
            <div class="card-body-custom">
                <div class="row g-3">
                    <!-- Master switch -->
                    <div class="col-12">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="ai_enabled" id="aiEnabled" value="1"
                                   <?= ($s['ai_enabled'] ?? '0') === '1' ? 'checked' : '' ?>
                                   onchange="document.getElementById('aiOptions').style.display=this.checked?'block':'none'">
                            <label class="form-check-label fw-semibold" for="aiEnabled">
                                KI-Sicherheitsanalyse aktivieren
                            </label>
                        </div>
                        <div class="text-muted small mt-1">
                            <i class="bi bi-shield-check me-1 text-success"></i>
                            Es werden ausschließlich anonymisierte Metriken (Zahlen, Prozentsätze) übertragen — niemals Benutzernamen, UPNs, Tenant-ID oder Domainnamen.
                        </div>
                    </div>

                    <div id="aiOptions" <?= ($s['ai_enabled'] ?? '0') !== '1' ? 'style="display:none"' : '' ?>>
                        <div class="row g-3 mt-0">
                            <div class="col-md-3">
                                <label class="form-label fw-medium">Anbieter</label>
                                <select name="ai_provider" id="aiProvider" class="form-select" onchange="updateAiDefaults()">
                                    <option value="openai"   <?= ($s['ai_provider'] ?? '') === 'openai'   ? 'selected' : '' ?>>OpenAI</option>
                                    <option value="deepseek" <?= ($s['ai_provider'] ?? '') === 'deepseek' ? 'selected' : '' ?>>DeepSeek</option>
                                    <option value="ollama"   <?= ($s['ai_provider'] ?? '') === 'ollama'   ? 'selected' : '' ?>>Ollama (lokal)</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-medium">Modell</label>
                                <input type="text" name="ai_model" id="aiModel" class="form-control"
                                       value="<?= htmlspecialchars($s['ai_model'] ?? '') ?>"
                                       placeholder="gpt-4o-mini">
                                <div class="form-text" id="aiModelHint">Leer = Standard des Anbieters</div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-medium">API-Key</label>
                                <input type="password" name="ai_api_key" class="form-control"
                                       placeholder="Leer = keine Änderung" autocomplete="new-password">
                                <div class="form-text">Wird verschlüsselt gespeichert</div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-medium">Cache-Dauer</label>
                                <select name="ai_cache_hours" class="form-select">
                                    <?php foreach ([1 => '1 Std.', 4 => '4 Std.', 12 => '12 Std.', 24 => '24 Std.', 48 => '48 Std.'] as $h => $l): ?>
                                        <option value="<?= $h ?>" <?= (int)($s['ai_cache_hours'] ?? 24) === $h ? 'selected' : '' ?>><?= $l ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6" id="aiBaseUrlRow">
                                <label class="form-label fw-medium">Basis-URL <span class="text-muted">(optional)</span></label>
                                <input type="url" name="ai_base_url" class="form-control"
                                       value="<?= htmlspecialchars($s['ai_base_url'] ?? '') ?>"
                                       placeholder="http://localhost:11434">
                                <div class="form-text" id="aiBaseUrlHint">Für Ollama oder eigene OpenAI-kompatible Endpunkte</div>
                            </div>
                            <div class="col-md-6 d-flex align-items-end gap-2">
                                <a href="/ai" class="btn btn-outline-primary btn-sm">
                                    <i class="bi bi-robot me-1"></i> Zum KI-Berater
                                </a>
                                <button type="button" class="btn btn-outline-secondary btn-sm"
                                        data-bs-toggle="modal" data-bs-target="#aiProtocolModal"
                                        onclick="loadAiProtocol()">
                                    <i class="bi bi-file-earmark-text me-1"></i> Protokoll anzeigen
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- AI Protocol Modal -->
        <div class="modal fade" id="aiProtocolModal" tabindex="-1">
          <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title">
                  <i class="bi bi-file-earmark-text me-2 text-primary"></i>
                  KI-Protokoll &mdash; zuletzt gesendete Daten
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
              </div>
              <div class="modal-body">
                <div class="alert alert-success small mb-3">
                  <i class="bi bi-shield-check me-1"></i>
                  <strong>Datenschutz:</strong> Es werden ausschließlich aggregierte, anonymisierte Metriken
                  (Zahlen, Prozentsätze) übertragen. Keine UPNs, keine Namen, keine Tenant-ID, keine Domains,
                  keine SKU-Bezeichnungen, keine Geräte-Namen.
                </div>
                <div id="aiProtocolBody">
                  <div class="text-center py-4 text-muted">
                    <div class="spinner-border spinner-border-sm me-2"></div>Lade Protokoll&hellip;
                  </div>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Schließen</button>
              </div>
            </div>
          </div>
        </div>

        <script>
        function loadAiProtocol() {
            const body = document.getElementById('aiProtocolBody');
            body.innerHTML = '<div class="text-center py-4 text-muted"><div class="spinner-border spinner-border-sm me-2"></div>Lade Protokoll&hellip;</div>';
            fetch('/ai/last-payload', { credentials: 'same-origin' })
                .then(r => r.json())
                .then(data => {
                    if (data.empty) {
                        body.innerHTML = '<div class="alert alert-info">' + escapeHtml(data.message) + '</div>';
                        return;
                    }
                    body.innerHTML = renderProtocol(data);
                })
                .catch(err => {
                    body.innerHTML = '<div class="alert alert-danger">Protokoll konnte nicht geladen werden: ' + escapeHtml(err.message) + '</div>';
                });
        }
        function renderProtocol(d) {
            const esc = escapeHtml;
            const stored = d.stored_at ? new Date(d.stored_at.replace(' ', 'T')).toLocaleString('de-DE') : '–';
            const sent   = d.sent_at   ? new Date(d.sent_at.replace(' ',   'T')).toLocaleString('de-DE') : '–';
            const httpClass = (d.response && d.response.http_code >= 200 && d.response.http_code < 300) ? 'text-success' : 'text-danger';

            let out = '<div class="row g-3 mb-3">';
            out += metaCard('Gesendet', sent);
            out += metaCard('Anbieter', esc(d.provider || ''));
            out += metaCard('Modell', esc(d.model || ''));
            out += metaCard('HTTP-Status', '<span class="' + httpClass + '">' + (d.response && d.response.http_code != null ? d.response.http_code : '–') + '</span>');
            out += '</div>';

            out += '<h6 class="fw-bold mt-3 mb-2"><i class="bi bi-cloud-upload me-1"></i>Endpunkt</h6>';
            out += '<pre class="bg-light border rounded p-2 small mb-3" style="white-space:pre-wrap;word-break:break-all;">' + esc(d.endpoint || '') + '</pre>';

            if (d.request && d.request.metrics_sent) {
                out += '<h6 class="fw-bold mt-3 mb-2"><i class="bi bi-bar-chart me-1"></i>Übertragene Metriken (anonymisiert)</h6>';
                out += '<pre class="bg-light border rounded p-2 small mb-3" style="max-height:300px;overflow:auto;">'
                     + esc(JSON.stringify(d.request.metrics_sent, null, 2)) + '</pre>';
            }

            if (d.request && d.request.system_prompt) {
                out += '<h6 class="fw-bold mt-3 mb-2"><i class="bi bi-gear me-1"></i>System-Prompt</h6>';
                out += '<pre class="bg-light border rounded p-2 small mb-3" style="white-space:pre-wrap;">' + esc(d.request.system_prompt) + '</pre>';
            }
            if (d.request && d.request.user_prompt) {
                out += '<h6 class="fw-bold mt-3 mb-2"><i class="bi bi-chat-left-text me-1"></i>User-Prompt (vollständig)</h6>';
                out += '<pre class="bg-light border rounded p-2 small mb-3" style="white-space:pre-wrap;max-height:300px;overflow:auto;">' + esc(d.request.user_prompt) + '</pre>';
            }

            out += '<h6 class="fw-bold mt-3 mb-2"><i class="bi bi-cloud-download me-1"></i>Rohantwort des Anbieters</h6>';
            if (d.response && d.response.curl_err) {
                out += '<div class="alert alert-danger small">cURL-Fehler: ' + esc(d.response.curl_err) + '</div>';
            }
            out += '<pre class="bg-light border rounded p-2 small mb-2" style="max-height:300px;overflow:auto;white-space:pre-wrap;">'
                 + esc((d.response && d.response.body) ? d.response.body : '(leer)') + '</pre>';

            out += '<div class="text-muted small mt-3"><i class="bi bi-clock me-1"></i>Aufgezeichnet: ' + stored + '</div>';
            return out;
        }
        function metaCard(label, val) {
            return '<div class="col-md-3"><div class="p-2 border rounded bg-light"><div class="text-muted small">'
                + escapeHtml(label) + '</div><div class="fw-semibold">' + val + '</div></div></div>';
        }
        function escapeHtml(s) {
            return String(s == null ? '' : s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
        }
        </script>

        <!-- General -->
        <div class="content-card mb-4" id="general">
            <div class="card-header-custom">
                <i class="bi bi-gear text-primary"></i>
                <h6>Allgemein</h6>
            </div>
            <div class="card-body-custom">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-medium">App-Name</label>
                        <input type="text" name="app_name" class="form-control" value="<?= $e($s['app_name']) ?>" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-medium">Cache-Dauer</label>
                        <select name="cache_ttl" class="form-select">
                            <?php foreach ([5,15,30,60] as $t): ?>
                                <option value="<?= $t ?>" <?= $s['cache_ttl'] == $t ? 'selected' : '' ?>><?= $t ?> Min.</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-medium">Zeitzone</label>
                        <select name="timezone" class="form-select">
                            <?php foreach (['Europe/Berlin','Europe/Vienna','Europe/Zurich','UTC'] as $tz): ?>
                                <option value="<?= $tz ?>" <?= $s['timezone'] === $tz ? 'selected' : '' ?>><?= $tz ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Admin Password -->
        <div class="content-card mb-4" id="admin-password">
            <div class="card-header-custom">
                <i class="bi bi-person-lock text-primary"></i>
                <h6>Admin-Passwort ändern</h6>
            </div>
            <div class="card-body-custom">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Neues Passwort</label>
                        <input type="password" name="admin_password" class="form-control" minlength="8" placeholder="Leer lassen = keine Änderung">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Bestätigung</label>
                        <input type="password" name="admin_password_confirm" class="form-control">
                    </div>
                </div>
            </div>
        </div>

        <!-- Operator Account -->
        <div class="content-card mb-4" id="operator">
            <div class="card-header-custom">
                <i class="bi bi-person-badge text-warning"></i>
                <h6>Operator-Konto <span class="badge-warning ms-2">Schreibzugriff eingeschränkt</span></h6>
            </div>
            <div class="card-body-custom">
                <p class="text-muted small mb-3">
                    Der Operator kann Benutzer de/aktivieren, Lizenzen zuweisen und Gruppen verwalten —
                    aber keine Einstellungen ändern.
                </p>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-medium">Benutzername</label>
                        <input type="text" name="operator_username" class="form-control" value="<?= $e($s['operator_username']) ?>" placeholder="z.B. operator">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-medium">Passwort</label>
                        <input type="password" name="operator_password" class="form-control" placeholder="Leer lassen = keine Änderung">
                    </div>
                </div>
            </div>
        </div>

        <!-- Email Alerts -->
        <div class="content-card mb-4" id="email">
            <div class="card-header-custom">
                <i class="bi bi-envelope text-primary"></i>
                <h6>E-Mail-Benachrichtigungen</h6>
            </div>
            <div class="card-body-custom">
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Alert-Empfänger</label>
                        <input type="email" name="alert_email_to" class="form-control" value="<?= $e($s['alert_email_to']) ?>" placeholder="admin@firma.de">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Absender</label>
                        <input type="email" name="alert_email_from" class="form-control" value="<?= $e($s['alert_email_from']) ?>" placeholder="noreply@firma.de">
                    </div>
                </div>
                <hr>
                <h6 class="small text-muted text-uppercase mb-3">SMTP (optional, sonst PHP mail())</h6>
                <div class="row g-3">
                    <div class="col-md-5">
                        <label class="form-label fw-medium">SMTP-Host</label>
                        <input type="text" name="smtp_host" class="form-control" value="<?= $e($s['smtp_host']) ?>" placeholder="smtp.firma.de">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-medium">Port</label>
                        <input type="number" name="smtp_port" class="form-control" value="<?= $e($s['smtp_port']) ?>">
                    </div>
                    <div class="col-md-2half">
                        <label class="form-label fw-medium">Benutzer</label>
                        <input type="text" name="smtp_user" class="form-control" value="<?= $e($s['smtp_user']) ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-medium">Passwort</label>
                        <input type="password" name="smtp_password" class="form-control" placeholder="Leer = keine Änderung">
                    </div>
                </div>
                <hr>
                <h6 class="small text-muted text-uppercase mb-3">Trigger</h6>
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="alert_risky_users" id="chkRisky" value="1"
                                   <?= $s['alert_risky_users'] ? 'checked' : '' ?>>
                            <label class="form-check-label" for="chkRisky">Neue Risikobenutzer</label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="alert_anon_shares" id="chkAnon" value="1"
                                   <?= $s['alert_anon_shares'] ? 'checked' : '' ?>>
                            <label class="form-check-label" for="chkAnon">Anonyme Freigaben</label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-medium text-muted">MFA-Schwellwert</label>
                        <div class="text-muted small">Konfigurierbar unter <a href="#alert-thresholds">Alert-Schwellwerte</a> ↓</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alert Thresholds -->
        <div class="content-card mb-4" id="alert-thresholds">
            <div class="card-header-custom">
                <i class="bi bi-sliders text-primary"></i>
                <h6>Alert-Schwellwerte</h6>
            </div>
            <div class="card-body-custom">
                <p class="text-muted small mb-3">
                    Definiert ab wann automatische Benachrichtigungen ausgelöst werden.
                    Alle Schwellwerte werden vom Cron-Job geprüft.
                </p>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-medium">MFA-Quote</label>
                        <div class="input-group input-group-sm">
                            <input type="number" name="alert_mfa_threshold" class="form-control"
                                   value="<?= $e($s['alert_mfa_threshold']) ?>" min="0" max="100">
                            <span class="input-group-text">%</span>
                        </div>
                        <div class="form-text">Alert wenn MFA-Registrierungsquote unter diesen Wert fällt.</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-medium">Lizenzauslastung</label>
                        <div class="input-group input-group-sm">
                            <input type="number" name="alert_license_threshold" class="form-control"
                                   value="<?= $e($s['alert_license_threshold'] ?? '90') ?>" min="0" max="100">
                            <span class="input-group-text">%</span>
                        </div>
                        <div class="form-text">Alert wenn eine SKU mehr als X% ausgelastet ist.</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-medium">Externe Freigaben</label>
                        <div class="input-group input-group-sm">
                            <input type="number" name="alert_external_shares_max" class="form-control"
                                   value="<?= $e($s['alert_external_shares_max'] ?? '50') ?>" min="0">
                            <span class="input-group-text">Stück</span>
                        </div>
                        <div class="form-text">Alert wenn aktive externe Freigaben diesen Wert übersteigen.</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-medium">Nicht-konforme Geräte</label>
                        <div class="input-group input-group-sm">
                            <input type="number" name="alert_noncompliant_devices_max" class="form-control"
                                   value="<?= $e($s['alert_noncompliant_devices_max'] ?? '5') ?>" min="0">
                            <span class="input-group-text">Geräte</span>
                        </div>
                        <div class="form-text">Alert wenn mehr als X Geräte nicht konform sind.</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-medium">Risikobenutzer</label>
                        <div class="input-group input-group-sm">
                            <input type="number" name="alert_risky_users_max" class="form-control"
                                   value="<?= $e($s['alert_risky_users_max'] ?? '0') ?>" min="0">
                            <span class="input-group-text">Benutzer</span>
                        </div>
                        <div class="form-text">Alert wenn mehr als X Risikobenutzer erkannt werden (0 = bei jedem).</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-medium">Inaktive Konten (Alert)</label>
                        <div class="input-group input-group-sm">
                            <input type="number" name="alert_stale_accounts_max" class="form-control"
                                   value="<?= $e($s['alert_stale_accounts_max'] ?? '10') ?>" min="0">
                            <span class="input-group-text">Konten</span>
                        </div>
                        <div class="form-text">Alert wenn mehr als X inaktive Konten mit Lizenzen gefunden werden.</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Share Review / Freigaben-Monitor -->
        <div class="content-card mb-4" id="share-review">
            <div class="card-header-custom">
                <i class="bi bi-eye-slash text-primary"></i>
                <h6>Freigaben-Monitor</h6>
            </div>
            <div class="card-body-custom">
                <p class="text-muted small mb-3">
                    Automatisch öffentliche / externe SharePoint-Freigaben überwachen, Besitzer per E-Mail fragen
                    und bei Nicht-Reaktion automatisch widerrufen.
                    <a href="/sharing/monitor" class="ms-1">→ Zum Monitor</a>
                </p>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-medium">App-Basis-URL</label>
                        <input type="url" name="app_base_url" class="form-control"
                               value="<?= $e($s['app_base_url'] ?? '') ?>"
                               placeholder="https://m365.firma.de">
                        <div class="form-text">Wird für den Bestätigungslink in der E-Mail verwendet.</div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-medium">Prüfintervall</label>
                        <div class="input-group">
                            <input type="number" name="share_review_interval_days" class="form-control"
                                   value="<?= (int)($s['share_review_interval_days'] ?? 30) ?>" min="1" max="365">
                            <span class="input-group-text">Tage</span>
                        </div>
                        <div class="form-text">Wie oft wird eine Bestätigung angefordert?</div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-medium">Toleranzzeit</label>
                        <div class="input-group">
                            <input type="number" name="share_review_grace_days" class="form-control"
                                   value="<?= (int)($s['share_review_grace_days'] ?? 7) ?>" min="1" max="60">
                            <span class="input-group-text">Tage</span>
                        </div>
                        <div class="form-text">Zeit bis zum automatischen Widerruf nach Erinnerung.</div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="share_review_only_anonymous"
                                   id="chkOnlyAnon" value="1"
                                   <?= ($s['share_review_only_anonymous'] ?? '0') === '1' ? 'checked' : '' ?>>
                            <label class="form-check-label" for="chkOnlyAnon">
                                Nur öffentliche Links (Anyone-Links) überwachen
                                <span class="text-muted">(deaktiviert = alle externen Freigaben)</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stale Accounts / Inactive Users -->
        <div class="content-card mb-4" id="stale-accounts">
            <div class="card-header-custom">
                <i class="bi bi-person-x text-primary"></i>
                <h6>Inaktive Konten</h6>
            </div>
            <div class="card-body-custom">
                <p class="text-muted small mb-3">
                    Benutzer, die sich länger als der konfigurierte Zeitraum nicht angemeldet haben,
                    werden als inaktiv markiert.
                    <a href="/staleaccounts" class="ms-1">→ Inaktive Konten anzeigen</a>
                </p>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-medium">Inaktivitätsschwelle</label>
                        <div class="input-group">
                            <input type="number" name="stale_account_days" class="form-control"
                                   value="<?= (int)($s['stale_account_days'] ?? 90) ?>" min="1" max="730">
                            <span class="input-group-text">Tage</span>
                        </div>
                        <div class="form-text">Ab wie vielen Tagen gilt ein Konto als inaktiv?</div>
                    </div>
                </div>
                <hr>
                <h6 class="small text-muted text-uppercase mb-3">Automatische Lizenzfreigabe <span class="badge-warning ms-1">Optional</span></h6>
                <div class="row g-3">
                    <div class="col-12">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="stale_auto_release_enabled"
                                   id="chkStaleAutoRelease" value="1"
                                   <?= ($s['stale_auto_release_enabled'] ?? '0') === '1' ? 'checked' : '' ?>
                                   onchange="document.getElementById('staleAutoReleaseOptions').style.display = this.checked ? '' : 'none'">
                            <label class="form-check-label fw-medium" for="chkStaleAutoRelease">
                                Lizenzen automatisch entziehen bei langer Inaktivität
                            </label>
                        </div>
                        <div class="form-text">
                            Wenn aktiviert, entfernt der Cron-Job (<code>run-stale-cleanup.php</code>) nach dem
                            konfigurierten Zeitraum automatisch alle Lizenzen. <strong>Eine Warnung wird
                            X Tage vorher per E-Mail gesendet.</strong>
                        </div>
                    </div>
                    <div id="staleAutoReleaseOptions" <?= ($s['stale_auto_release_enabled'] ?? '0') !== '1' ? 'style="display:none"' : '' ?>>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-medium">Lizenz-Freigabe nach</label>
                                <div class="input-group">
                                    <input type="number" name="stale_auto_release_days" class="form-control"
                                           value="<?= (int)($s['stale_auto_release_days'] ?? 180) ?>" min="1" max="1095">
                                    <span class="input-group-text">Tagen</span>
                                </div>
                                <div class="form-text">Tage seit letzter Anmeldung, danach werden Lizenzen entzogen.</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-medium">Vorwarnung</label>
                                <div class="input-group">
                                    <input type="number" name="stale_warn_days_before" class="form-control"
                                           value="<?= (int)($s['stale_warn_days_before'] ?? 14) ?>" min="0" max="90">
                                    <span class="input-group-text">Tage vorher</span>
                                </div>
                                <div class="form-text">E-Mail-Warnung X Tage vor der automatischen Lizenzfreigabe. 0 = keine Warnung.</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Password Expiry -->
        <div class="content-card mb-4" id="password-expiry">
            <div class="card-header-custom">
                <i class="bi bi-key text-primary"></i>
                <h6>Passwort-Ablauf</h6>
            </div>
            <div class="card-body-custom">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-medium">Passwort-Gültigkeitsdauer</label>
                        <div class="input-group">
                            <input type="number" name="password_expiry_days" class="form-control"
                                   value="<?= (int)($s['password_expiry_days'] ?? 90) ?>" min="1" max="365">
                            <span class="input-group-text">Tage</span>
                        </div>
                        <div class="form-text">Standard: 90 Tage. Gilt für alle Benutzer ohne <code>DisablePasswordExpiration</code>-Richtlinie.</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Weekly Report -->
        <div class="content-card mb-4" id="weekly-report">
            <div class="card-header-custom">
                <i class="bi bi-envelope-paper text-primary"></i>
                <h6>Wöchentlicher E-Mail-Report</h6>
            </div>
            <div class="card-body-custom">
                <p class="text-muted small mb-3">
                    Sendet jeden Woche einen Zusammenfassungsbericht mit Benutzer-, Lizenz-, Sicherheits- und Freigabe-Kennzahlen.
                    Voraussetzung: Alert-E-Mail muss konfiguriert sein.
                </p>
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="weekly_report_enabled"
                                   id="weeklyReportEnabled" role="switch"
                                   <?= ($s['weekly_report_enabled'] ?? '0') === '1' ? 'checked' : '' ?>
                                   onchange="document.getElementById('weeklyReportOptions').style.display=this.checked?'block':'none'">
                            <label class="form-check-label fw-medium" for="weeklyReportEnabled">Wöchentlichen Report aktivieren</label>
                        </div>
                    </div>
                    <div id="weeklyReportOptions" <?= ($s['weekly_report_enabled'] ?? '0') !== '1' ? 'style="display:none"' : '' ?>>
                        <div class="col-md-4 mt-2">
                            <label class="form-label fw-medium">Versandtag</label>
                            <select name="weekly_report_day" class="form-select">
                                <?php
                                $days = ['1'=>'Montag','2'=>'Dienstag','3'=>'Mittwoch','4'=>'Donnerstag','5'=>'Freitag','6'=>'Samstag','7'=>'Sonntag'];
                                $sel  = (string)($s['weekly_report_day'] ?? '1');
                                foreach ($days as $val => $label): ?>
                                    <option value="<?= $val ?>" <?= $val === $sel ? 'selected' : '' ?>><?= $label ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text">Der Cron-Job läuft täglich und prüft, ob heute der konfigurierte Tag ist.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- License Criteria -->
        <div class="content-card mb-4" id="license-criteria">
            <div class="card-header-custom">
                <i class="bi bi-lightbulb text-primary"></i>
                <h6>Lizenz-Berater — Kriterien</h6>
            </div>
            <div class="card-body-custom">
                <p class="text-muted small mb-3">
                    Diese Kriterien bestimmen, welche Lizenzpläne im <a href="/licenseadvisor">Lizenz-Berater</a> als
                    „passend" eingestuft werden. Aktiviere nur die Dienste, die für euren Tenant relevant sind.
                    Die Einstellungen können auch direkt im Lizenz-Berater geändert werden.
                </p>
                <div class="row g-3">
                    <?php
                    $licCriteria = [
                        'lic_need_exchange_online' => ['Exchange Online', 'envelope'],
                        'lic_need_office_desktop'  => ['Office Desktop (Apps)', 'grid'],
                        'lic_need_teams'           => ['Microsoft Teams', 'chat-dots'],
                        'lic_need_sharepoint'      => ['SharePoint Online', 'share'],
                        'lic_need_onedrive'        => ['OneDrive for Business', 'cloud'],
                        'lic_need_intune'          => ['Intune (Geräteverwaltung)', 'phone'],
                    ];
                    foreach ($licCriteria as $key => [$label, $icon]): ?>
                    <div class="col-md-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="<?= $key ?>"
                                   id="<?= $key ?>" role="switch"
                                   <?= ($s[$key] ?? '0') === '1' ? 'checked' : '' ?>>
                            <label class="form-check-label" for="<?= $key ?>">
                                <i class="bi bi-<?= $icon ?> me-1 text-primary"></i><?= $label ?>
                            </label>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- License prices link -->
        <div class="content-card mb-4" id="license-prices">
            <div class="card-header-custom">
                <i class="bi bi-currency-euro text-primary"></i>
                <h6>Lizenzpreise konfigurieren</h6>
            </div>
            <div class="card-body-custom">
                <p class="text-muted small mb-3">
                    Überschreibe die Katalog-Standardpreise (Listenpreise Mai 2025) mit deinen tatsächlichen
                    Partner- oder CSP-Netto-Preisen. Die angepassten Preise gelten für Lizenz-Berater und Lizenzkosten.
                </p>
                <a href="/settings/license-prices" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-pencil-square me-1"></i>Preise bearbeiten
                </a>
            </div>
        </div>

        <!-- Branding: public review page -->
        <div class="content-card mb-4" id="branding">
            <div class="card-header-custom">
                <i class="bi bi-palette text-primary"></i>
                <h6>Branding — Öffentliche Bestätigungsseite</h6>
            </div>
            <div class="card-body-custom">
                <p class="text-muted small mb-3">
                    Passt das Erscheinungsbild der öffentlichen Freigabe-Bestätigungsseite an
                    (der Link, den Freigabe-Besitzer per E-Mail erhalten).
                </p>

                <!-- Live Preview -->
                <div class="mb-4 p-3 rounded border" id="brandPreview" style="background:#f9fafb;">
                    <div class="d-flex align-items-center gap-3 p-2 rounded mb-2"
                         id="previewBar" style="background:#0078d4;color:#fff;border-radius:8px;">
                        <span id="previewLogo" style="font-size:20px;font-weight:700;">M</span>
                        <span id="previewTitle" style="font-size:15px;font-weight:600;">Freigabe-Überprüfung</span>
                    </div>
                    <div class="text-muted" style="font-size:12px;">
                        <i class="bi bi-eye me-1"></i>Vorschau der Titelleiste
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-medium">Primärfarbe</label>
                        <div class="input-group">
                            <input type="color" name="brand_primary_color" id="brandColor"
                                   class="form-control form-control-color"
                                   value="<?= $e($s['brand_primary_color']) ?>"
                                   title="Farbe wählen">
                            <input type="text" id="brandColorText" class="form-control font-monospace"
                                   value="<?= $e($s['brand_primary_color']) ?>"
                                   placeholder="#0078d4" maxlength="7" readonly>
                        </div>
                        <div class="form-text">Standard: #0078d4 (Microsoft Blau)</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-medium">Logo-URL</label>
                        <input type="url" name="brand_logo_url" id="brandLogoUrl" class="form-control"
                               value="<?= $e($s['brand_logo_url']) ?>"
                               placeholder="https://firma.de/logo.png">
                        <div class="form-text">PNG/SVG, wird in der Titelleiste angezeigt. Leer = Textkürzel.</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-medium">Logo-Text / Kürzel</label>
                        <input type="text" name="brand_logo_text" id="brandLogoText" class="form-control"
                               value="<?= $e($s['brand_logo_text']) ?>"
                               placeholder="M" maxlength="3">
                        <div class="form-text">Kürzel wenn kein Logo gesetzt (max. 3 Zeichen).</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Support-E-Mail (optional)</label>
                        <input type="email" name="brand_review_support_email" class="form-control"
                               value="<?= $e($s['brand_review_support_email']) ?>"
                               placeholder="it@firma.de">
                        <div class="form-text">Wird auf der Bestätigungsseite als Kontakt angezeigt.</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Fußzeilentext (optional)</label>
                        <input type="text" name="brand_review_footer" class="form-control"
                               value="<?= $e($s['brand_review_footer']) ?>"
                               placeholder="© Firma GmbH · IT-Abteilung">
                        <div class="form-text">Erscheint am unteren Rand der öffentlichen Seite.</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary px-4">
                <i class="bi bi-check2 me-1"></i> Einstellungen speichern
            </button>
        </div>
        </form>

<script>
// AI provider defaults
function updateAiDefaults() {
    const p = document.getElementById('aiProvider')?.value;
    const modelEl = document.getElementById('aiModel');
    const hintEl  = document.getElementById('aiModelHint');
    const defaults = { openai: 'gpt-4o-mini', deepseek: 'deepseek-chat', ollama: 'llama3.2' };
    const hints    = { openai: 'z.B. gpt-4o-mini, gpt-4o', deepseek: 'z.B. deepseek-chat, deepseek-reasoner', ollama: 'z.B. llama3.2, mistral, phi3' };
    if (modelEl && !modelEl.value) modelEl.placeholder = defaults[p] || 'Modellname';
    if (hintEl) hintEl.textContent = hints[p] || '';
}
updateAiDefaults();

// Live preview for branding
(function () {
    const colorPicker = document.getElementById('brandColor');
    const colorText   = document.getElementById('brandColorText');
    const bar         = document.getElementById('previewBar');
    const logoText    = document.getElementById('brandLogoText');
    const previewLogo = document.getElementById('previewLogo');

    function updatePreview() {
        const color = colorPicker.value;
        colorText.value = color;
        bar.style.background = color;
        previewLogo.textContent = (logoText?.value || 'M').substring(0, 3) || 'M';
    }

    colorPicker?.addEventListener('input', updatePreview);
    logoText?.addEventListener('input', updatePreview);
    updatePreview();
})();
</script>
    </div>

    <!-- Sidebar actions -->
    <div class="col-lg-4">
        <div class="content-card mb-3">
            <div class="card-header-custom">
                <i class="bi bi-people-fill text-primary"></i>
                <h6>Benutzer-Zugang</h6>
            </div>
            <div class="card-body-custom">
                <p class="small text-muted mb-3">M365-Benutzer berechtigen, sich mit ihrem Microsoft-Konto anzumelden (z.B. IT-Mitarbeiter als Operator).</p>
                <a href="/settings/users" class="btn btn-primary btn-sm w-100">
                    <i class="bi bi-person-plus me-1"></i> Benutzer verwalten
                </a>
            </div>
        </div>

        <div class="content-card mb-3">
            <div class="card-header-custom">
                <i class="bi bi-book text-primary"></i>
                <h6>Handbuch</h6>
            </div>
            <div class="card-body-custom">
                <p class="small text-muted mb-3">Vollständige Dokumentation aller Module, Funktionen und erforderlichen Graph-Berechtigungen.</p>
                <a href="/manual" class="btn btn-primary btn-sm w-100">
                    <i class="bi bi-book me-1"></i> Handbuch öffnen
                </a>
            </div>
        </div>

        <div class="content-card mb-3">
            <div class="card-header-custom">
                <i class="bi bi-eye-slash text-primary"></i>
                <h6>Freigaben-Review — Vorschau</h6>
            </div>
            <div class="card-body-custom">
                <p class="small text-muted mb-3">So sieht die Bestätigungsseite für Benutzer aus, nachdem sie eine Review-E-Mail erhalten haben.</p>
                <a href="/review/demo" target="_blank" class="btn btn-outline-secondary btn-sm w-100">
                    <i class="bi bi-box-arrow-up-right me-1"></i> Vorschau öffnen
                </a>
            </div>
        </div>

        <div class="content-card mb-3">
            <div class="card-header-custom">
                <i class="bi bi-shield-check text-primary"></i>
                <h6>Graph API Berechtigungen</h6>
            </div>
            <div class="card-body-custom">
                <p class="small text-muted mb-3">Prüft welche Berechtigungen dem App-Konto erteilt sind und welche Features dadurch eingeschränkt sind.</p>
                <a href="/settings/permissions" class="btn btn-outline-primary btn-sm w-100">
                    <i class="bi bi-card-checklist me-1"></i> Berechtigungen prüfen
                </a>
            </div>
        </div>

        <div class="content-card mb-3">
            <div class="card-header-custom">
                <i class="bi bi-database text-secondary"></i>
                <h6>Cache</h6>
            </div>
            <div class="card-body-custom">
                <p class="small text-muted mb-3">Graph-API-Daten werden lokal gecacht. Hier komplett leeren für frische Daten.</p>
                <a href="/settings/clear-cache" class="btn btn-outline-secondary btn-sm w-100"
                   onclick="return confirm('Cache wirklich leeren?')">
                    <i class="bi bi-trash me-1"></i> Cache leeren
                </a>
            </div>
        </div>

        <div class="content-card mb-3">
            <div class="card-header-custom">
                <i class="bi bi-envelope text-secondary"></i>
                <h6>E-Mail testen</h6>
            </div>
            <div class="card-body-custom">
                <p class="small text-muted mb-3">Sendet eine Test-E-Mail an den konfigurierten Alert-Empfänger.</p>
                <a href="/settings/test-mail" class="btn btn-outline-primary btn-sm w-100">
                    <i class="bi bi-send me-1"></i> Test-E-Mail senden
                </a>
            </div>
        </div>

        <div class="content-card mb-3">
            <div class="card-header-custom">
                <i class="bi bi-journal-check text-primary"></i>
                <h6>App Audit-Log</h6>
            </div>
            <div class="card-body-custom">
                <p class="small text-muted mb-3">Protokoll aller sicherheitsrelevanten Aktionen (Anmeldungen, Einstellungsänderungen, Benutzeraktionen).</p>
                <a href="/settings/app-audit" class="btn btn-outline-primary btn-sm w-100">
                    <i class="bi bi-journal-check me-1"></i> Audit-Log anzeigen
                </a>
            </div>
        </div>

        <div class="content-card">
            <div class="card-header-custom">
                <i class="bi bi-info-circle text-secondary"></i>
                <h6>Rollen-Übersicht</h6>
            </div>
            <div class="card-body-custom">
                <table class="table table-sm mb-0" style="font-size:12px;">
                    <thead><tr><th>Aktion</th><th>Admin</th><th>Operator</th></tr></thead>
                    <tbody>
                        <tr><td>Einsehen</td><td>✓</td><td>✓</td></tr>
                        <tr><td>CSV-Export</td><td>✓</td><td>✓</td></tr>
                        <tr><td>Benutzer de/aktivieren</td><td>✓</td><td>✓</td></tr>
                        <tr><td>Lizenzen zuweisen</td><td>✓</td><td>✓</td></tr>
                        <tr><td>Gruppen verwalten</td><td>✓</td><td>✓</td></tr>
                        <tr><td>Freigaben widerrufen</td><td>✓</td><td>✓</td></tr>
                        <tr><td>Einstellungen</td><td>✓</td><td>–</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
