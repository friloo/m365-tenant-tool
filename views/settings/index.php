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

<style>
.settings-tabs {
    display: flex; gap: 4px; margin-bottom: 20px;
    border-bottom: 1px solid #e5e7eb;
    overflow-x: auto; -webkit-overflow-scrolling: touch;
    scrollbar-width: none;                   /* Firefox */
}
.settings-tabs::-webkit-scrollbar { display: none; } /* WebKit */
.settings-tabs button {
    background: none; border: none;
    padding: 10px 16px;
    font-size: 14px; font-weight: 500; color: #6b7280;
    cursor: pointer;
    border-bottom: 2px solid transparent;
    transition: color .15s, border-color .15s;
    white-space: nowrap;
    flex-shrink: 0;
}
.settings-tabs button:hover { color: #111827; }
.settings-tabs button.active {
    color: #0078d4;
    border-bottom-color: #0078d4;
}
.settings-tabs button i { margin-right: 6px; }

[data-tab]:not(.tab-active) { display: none; }

.settings-savebar {
    position: sticky; bottom: 0; z-index: 10;
    margin-top: 16px; padding: 14px 0;
    background: linear-gradient(to top, rgba(243,244,246,.95) 60%, transparent);
    backdrop-filter: blur(4px);
    -webkit-backdrop-filter: blur(4px);
}

@media (max-width: 640px) {
    .settings-tabs button { padding: 9px 12px; font-size: 13px; }
    .settings-tabs button i { margin-right: 5px; }
    .settings-savebar { margin: 16px -12px -12px; padding: 12px; }
    .settings-savebar .btn { width: 100%; }
    .settings-savebar .small { display: none; }
}
</style>

<div class="row g-4">
    <div class="col-lg-8">
        <form method="post" action="/settings/save">
            <?= \App\Core\Csrf::field() ?>

        <nav class="settings-tabs" role="tablist">
            <button type="button" data-tab-target="allgemein"        class="active"><i class="bi bi-sliders"></i><?= te('Allgemein') ?></button>
            <button type="button" data-tab-target="benachrichtigungen"><i class="bi bi-bell"></i><?= te('Benachrichtigungen') ?></button>
            <button type="button" data-tab-target="governance"      ><i class="bi bi-shield-check"></i><?= te('Governance') ?></button>
            <button type="button" data-tab-target="ki"              ><i class="bi bi-robot"></i><?= te('KI & Lizenzen') ?></button>
        </nav>

        <!-- KI-Sicherheitsberater -->
        <div class="content-card mb-4" data-tab="ki" id="ai-advisor">
            <div class="card-header-custom">
                <i class="bi bi-robot text-primary"></i>
                <h6><?= te('KI-Sicherheitsberater') ?></h6>
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
                                <?= te('KI-Sicherheitsanalyse aktivieren') ?>
                            </label>
                        </div>
                        <div class="text-muted small mt-1">
                            <i class="bi bi-shield-check me-1 text-success"></i>
                            <?= te('Es werden ausschließlich anonymisierte Metriken (Zahlen, Prozentsätze) übertragen — niemals Benutzernamen, UPNs, Tenant-ID oder Domainnamen.') ?>
                        </div>
                    </div>

                    <div id="aiOptions" <?= ($s['ai_enabled'] ?? '0') !== '1' ? 'style="display:none"' : '' ?>>
                        <div class="row g-3 mt-0">
                            <div class="col-md-3">
                                <label class="form-label fw-medium"><?= te('Anbieter') ?></label>
                                <select name="ai_provider" id="aiProvider" class="form-select" onchange="updateAiDefaults()">
                                    <option value="openai"   <?= ($s['ai_provider'] ?? '') === 'openai'   ? 'selected' : '' ?>>OpenAI</option>
                                    <option value="deepseek" <?= ($s['ai_provider'] ?? '') === 'deepseek' ? 'selected' : '' ?>>DeepSeek</option>
                                    <option value="ollama"   <?= ($s['ai_provider'] ?? '') === 'ollama'   ? 'selected' : '' ?>><?= te('Ollama (lokal)') ?></option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-medium"><?= te('Modell') ?></label>
                                <input type="text" name="ai_model" id="aiModel" class="form-control"
                                       value="<?= htmlspecialchars($s['ai_model'] ?? '') ?>"
                                       placeholder="gpt-4o-mini">
                                <div class="form-text" id="aiModelHint"><?= te('Leer = Standard des Anbieters') ?></div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-medium">API-Key</label>
                                <input type="password" name="ai_api_key" class="form-control"
                                       placeholder="<?= te('Leer = keine Änderung') ?>" autocomplete="new-password">
                                <div class="form-text"><?= te('Wird verschlüsselt gespeichert') ?></div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-medium"><?= te('Cache-Dauer') ?></label>
                                <select name="ai_cache_hours" class="form-select">
                                    <?php foreach ([1 => t('1 Std.'), 4 => t('4 Std.'), 12 => t('12 Std.'), 24 => t('24 Std.'), 48 => t('48 Std.')] as $h => $l): ?>
                                        <option value="<?= $h ?>" <?= (int)($s['ai_cache_hours'] ?? 24) === $h ? 'selected' : '' ?>><?= $l ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6" id="aiBaseUrlRow">
                                <label class="form-label fw-medium"><?= te('Basis-URL') ?> <span class="text-muted">(<?= te('optional') ?>)</span></label>
                                <input type="url" name="ai_base_url" class="form-control"
                                       value="<?= htmlspecialchars($s['ai_base_url'] ?? '') ?>"
                                       placeholder="http://localhost:11434">
                                <div class="form-text" id="aiBaseUrlHint"><?= te('Für Ollama oder eigene OpenAI-kompatible Endpunkte') ?></div>
                            </div>
                            <div class="col-md-6 d-flex align-items-end gap-2">
                                <a href="/ai" class="btn btn-outline-primary btn-sm">
                                    <i class="bi bi-robot me-1"></i> <?= te('Zum KI-Berater') ?>
                                </a>
                                <button type="button" class="btn btn-outline-secondary btn-sm"
                                        data-bs-toggle="modal" data-bs-target="#aiProtocolModal"
                                        onclick="loadAiProtocol()">
                                    <i class="bi bi-file-earmark-text me-1"></i> <?= te('Protokoll anzeigen') ?>
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
                  <?= te('KI-Protokoll — zuletzt gesendete Daten') ?>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
              </div>
              <div class="modal-body">
                <div class="alert alert-success small mb-3">
                  <i class="bi bi-shield-check me-1"></i>
                  <strong><?= te('Datenschutz:') ?></strong> <?= te('Es werden ausschließlich aggregierte, anonymisierte Metriken (Zahlen, Prozentsätze) übertragen. Keine UPNs, keine Namen, keine Tenant-ID, keine Domains, keine SKU-Bezeichnungen, keine Geräte-Namen.') ?>
                </div>
                <div id="aiProtocolBody">
                  <div class="text-center py-4 text-muted">
                    <div class="spinner-border spinner-border-sm me-2"></div><?= te('Lade Protokoll…') ?>
                  </div>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= te('Schließen') ?></button>
              </div>
            </div>
          </div>
        </div>

        <script>
        function loadAiProtocol() {
            const body = document.getElementById('aiProtocolBody');
            body.innerHTML = '<div class="text-center py-4 text-muted"><div class="spinner-border spinner-border-sm me-2"></div>' + <?= json_encode(t('Lade Protokoll…'), JSON_UNESCAPED_UNICODE) ?> + '</div>';
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
                    body.innerHTML = '<div class="alert alert-danger">' + <?= json_encode(t('Protokoll konnte nicht geladen werden:'), JSON_UNESCAPED_UNICODE) ?> + ' ' + escapeHtml(err.message) + '</div>';
                });
        }
        function renderProtocol(d) {
            const esc = escapeHtml;
            const stored = d.stored_at ? new Date(d.stored_at.replace(' ', 'T')).toLocaleString('de-DE') : '–';
            const sent   = d.sent_at   ? new Date(d.sent_at.replace(' ',   'T')).toLocaleString('de-DE') : '–';
            const httpClass = (d.response && d.response.http_code >= 200 && d.response.http_code < 300) ? 'text-success' : 'text-danger';

            let out = '<div class="row g-3 mb-3">';
            out += metaCard(<?= json_encode(t('Gesendet'), JSON_UNESCAPED_UNICODE) ?>, sent);
            out += metaCard(<?= json_encode(t('Anbieter'), JSON_UNESCAPED_UNICODE) ?>, esc(d.provider || ''));
            out += metaCard(<?= json_encode(t('Modell'), JSON_UNESCAPED_UNICODE) ?>, esc(d.model || ''));
            out += metaCard('HTTP-Status', '<span class="' + httpClass + '">' + (d.response && d.response.http_code != null ? d.response.http_code : '–') + '</span>');
            out += '</div>';

            out += '<h6 class="fw-bold mt-3 mb-2"><i class="bi bi-cloud-upload me-1"></i>' + <?= json_encode(t('Endpunkt'), JSON_UNESCAPED_UNICODE) ?> + '</h6>';
            out += '<pre class="bg-light border rounded p-2 small mb-3" style="white-space:pre-wrap;word-break:break-all;">' + esc(d.endpoint || '') + '</pre>';

            if (d.request && d.request.metrics_sent) {
                out += '<h6 class="fw-bold mt-3 mb-2"><i class="bi bi-bar-chart me-1"></i>' + <?= json_encode(t('Übertragene Metriken (anonymisiert)'), JSON_UNESCAPED_UNICODE) ?> + '</h6>';
                out += '<pre class="bg-light border rounded p-2 small mb-3" style="max-height:300px;overflow:auto;">'
                     + esc(JSON.stringify(d.request.metrics_sent, null, 2)) + '</pre>';
            }

            if (d.request && d.request.system_prompt) {
                out += '<h6 class="fw-bold mt-3 mb-2"><i class="bi bi-gear me-1"></i>' + <?= json_encode(t('System-Prompt'), JSON_UNESCAPED_UNICODE) ?> + '</h6>';
                out += '<pre class="bg-light border rounded p-2 small mb-3" style="white-space:pre-wrap;">' + esc(d.request.system_prompt) + '</pre>';
            }
            if (d.request && d.request.user_prompt) {
                out += '<h6 class="fw-bold mt-3 mb-2"><i class="bi bi-chat-left-text me-1"></i>' + <?= json_encode(t('User-Prompt (vollständig)'), JSON_UNESCAPED_UNICODE) ?> + '</h6>';
                out += '<pre class="bg-light border rounded p-2 small mb-3" style="white-space:pre-wrap;max-height:300px;overflow:auto;">' + esc(d.request.user_prompt) + '</pre>';
            }

            out += '<h6 class="fw-bold mt-3 mb-2"><i class="bi bi-cloud-download me-1"></i>' + <?= json_encode(t('Rohantwort des Anbieters'), JSON_UNESCAPED_UNICODE) ?> + '</h6>';
            if (d.response && d.response.curl_err) {
                out += '<div class="alert alert-danger small">' + <?= json_encode(t('cURL-Fehler:'), JSON_UNESCAPED_UNICODE) ?> + ' ' + esc(d.response.curl_err) + '</div>';
            }
            out += '<pre class="bg-light border rounded p-2 small mb-2" style="max-height:300px;overflow:auto;white-space:pre-wrap;">'
                 + esc((d.response && d.response.body) ? d.response.body : <?= json_encode(t('(leer)'), JSON_UNESCAPED_UNICODE) ?>) + '</pre>';

            out += '<div class="text-muted small mt-3"><i class="bi bi-clock me-1"></i>' + <?= json_encode(t('Aufgezeichnet:'), JSON_UNESCAPED_UNICODE) ?> + ' ' + stored + '</div>';
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
        <div class="content-card mb-4" data-tab="allgemein" id="general">
            <div class="card-header-custom">
                <i class="bi bi-gear text-primary"></i>
                <h6><?= te('Allgemein') ?></h6>
            </div>
            <div class="card-body-custom">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-medium"><?= te('App-Name') ?></label>
                        <input type="text" name="app_name" class="form-control" value="<?= $e($s['app_name']) ?>" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-medium"><?= te('Cache-Dauer') ?></label>
                        <select name="cache_ttl" class="form-select">
                            <?php foreach ([5,15,30,60] as $t): ?>
                                <option value="<?= $t ?>" <?= $s['cache_ttl'] == $t ? 'selected' : '' ?>><?= $t ?> <?= te('Min.') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-medium"><?= te('Zeitzone') ?></label>
                        <select name="timezone" class="form-select">
                            <?php foreach (['Europe/Berlin','Europe/Vienna','Europe/Zurich','UTC'] as $tz): ?>
                                <option value="<?= $tz ?>" <?= $s['timezone'] === $tz ? 'selected' : '' ?>><?= $tz ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-medium"><?= te('Sprache') ?></label>
                        <select name="default_language" class="form-select">
                            <?php foreach (\App\Core\I18n::supported() as $code => $name): ?>
                                <option value="<?= $e($code) ?>" <?= ($s['default_language'] ?? \App\Core\I18n::SOURCE) === $code ? 'selected' : '' ?>><?= $e($name) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="text-muted small mt-1"><?= te('Standardsprache der Oberfläche. Jeder Nutzer kann sie über das Sprachmenü oben rechts wechseln.') ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Admin Password -->
        <div class="content-card mb-4" data-tab="allgemein" id="admin-password">
            <div class="card-header-custom">
                <i class="bi bi-person-lock text-primary"></i>
                <h6><?= te('Admin-Passwort ändern') ?></h6>
            </div>
            <div class="card-body-custom">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-medium"><?= te('Neues Passwort') ?></label>
                        <input type="password" name="admin_password" class="form-control" minlength="8" placeholder="<?= te('Leer lassen = keine Änderung') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-medium"><?= te('Bestätigung') ?></label>
                        <input type="password" name="admin_password_confirm" class="form-control">
                    </div>
                </div>
            </div>
        </div>


        <!-- Email Alerts -->
        <div class="content-card mb-4" data-tab="benachrichtigungen" id="email">
            <div class="card-header-custom">
                <i class="bi bi-envelope text-primary"></i>
                <h6><?= te('E-Mail-Benachrichtigungen') ?></h6>
            </div>
            <div class="card-body-custom">
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-medium"><?= te('Alert-Empfänger') ?></label>
                        <input type="email" name="alert_email_to" class="form-control" value="<?= $e($s['alert_email_to']) ?>" placeholder="admin@firma.de">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-medium"><?= te('Absender') ?></label>
                        <input type="email" name="alert_email_from" class="form-control" value="<?= $e($s['alert_email_from']) ?>" placeholder="noreply@firma.de">
                    </div>
                </div>
                <hr>
                <h6 class="small text-muted text-uppercase mb-3"><?= te('SMTP (optional, sonst PHP mail())') ?></h6>
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
                        <label class="form-label fw-medium"><?= te('Benutzer') ?></label>
                        <input type="text" name="smtp_user" class="form-control" value="<?= $e($s['smtp_user']) ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-medium"><?= te('Passwort') ?></label>
                        <input type="password" name="smtp_password" class="form-control" placeholder="<?= te('Leer = keine Änderung') ?>">
                    </div>
                </div>
                <hr>
                <h6 class="small text-muted text-uppercase mb-3">Trigger</h6>
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="alert_risky_users" id="chkRisky" value="1"
                                   <?= $s['alert_risky_users'] ? 'checked' : '' ?>>
                            <label class="form-check-label" for="chkRisky"><?= te('Neue Risikobenutzer') ?></label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="alert_anon_shares" id="chkAnon" value="1"
                                   <?= $s['alert_anon_shares'] ? 'checked' : '' ?>>
                            <label class="form-check-label" for="chkAnon"><?= te('Anonyme Freigaben') ?></label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-medium text-muted"><?= te('MFA-Schwellwert') ?></label>
                        <div class="text-muted small"><?= te('Konfigurierbar unter') ?> <a href="#alert-thresholds"><?= te('Alert-Schwellwerte') ?></a> ↓</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alert Thresholds -->
        <div class="content-card mb-4" data-tab="benachrichtigungen" id="alert-thresholds">
            <div class="card-header-custom">
                <i class="bi bi-sliders text-primary"></i>
                <h6><?= te('Alert-Schwellwerte') ?></h6>
            </div>
            <div class="card-body-custom">
                <p class="text-muted small mb-3">
                    <?= te('Definiert ab wann automatische Benachrichtigungen ausgelöst werden. Alle Schwellwerte werden vom Cron-Job geprüft.') ?>
                </p>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-medium"><?= te('MFA-Quote') ?></label>
                        <div class="input-group input-group-sm">
                            <input type="number" name="alert_mfa_threshold" class="form-control"
                                   value="<?= $e($s['alert_mfa_threshold']) ?>" min="0" max="100">
                            <span class="input-group-text">%</span>
                        </div>
                        <div class="form-text"><?= te('Alert wenn MFA-Registrierungsquote unter diesen Wert fällt.') ?></div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-medium"><?= te('Lizenzauslastung') ?></label>
                        <div class="input-group input-group-sm">
                            <input type="number" name="alert_license_threshold" class="form-control"
                                   value="<?= $e($s['alert_license_threshold'] ?? '90') ?>" min="0" max="100">
                            <span class="input-group-text">%</span>
                        </div>
                        <div class="form-text"><?= te('Alert wenn eine SKU mehr als X% ausgelastet ist.') ?></div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-medium"><?= te('Externe Freigaben') ?></label>
                        <div class="input-group input-group-sm">
                            <input type="number" name="alert_external_shares_max" class="form-control"
                                   value="<?= $e($s['alert_external_shares_max'] ?? '50') ?>" min="0">
                            <span class="input-group-text"><?= te('Stück') ?></span>
                        </div>
                        <div class="form-text"><?= te('Alert wenn aktive externe Freigaben diesen Wert übersteigen.') ?></div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-medium"><?= te('Nicht-konforme Geräte') ?></label>
                        <div class="input-group input-group-sm">
                            <input type="number" name="alert_noncompliant_devices_max" class="form-control"
                                   value="<?= $e($s['alert_noncompliant_devices_max'] ?? '5') ?>" min="0">
                            <span class="input-group-text"><?= te('Geräte') ?></span>
                        </div>
                        <div class="form-text"><?= te('Alert wenn mehr als X Geräte nicht konform sind.') ?></div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-medium"><?= te('Risikobenutzer') ?></label>
                        <div class="input-group input-group-sm">
                            <input type="number" name="alert_risky_users_max" class="form-control"
                                   value="<?= $e($s['alert_risky_users_max'] ?? '0') ?>" min="0">
                            <span class="input-group-text"><?= te('Benutzer') ?></span>
                        </div>
                        <div class="form-text"><?= te('Alert wenn mehr als X Risikobenutzer erkannt werden (0 = bei jedem).') ?></div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-medium"><?= te('Inaktive Konten (Alert)') ?></label>
                        <div class="input-group input-group-sm">
                            <input type="number" name="alert_stale_accounts_max" class="form-control"
                                   value="<?= $e($s['alert_stale_accounts_max'] ?? '10') ?>" min="0">
                            <span class="input-group-text"><?= te('Konten') ?></span>
                        </div>
                        <div class="form-text"><?= te('Alert wenn mehr als X inaktive Konten mit Lizenzen gefunden werden.') ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Share Review / Freigaben-Monitor -->
        <div class="content-card mb-4" data-tab="governance" id="share-review">
            <div class="card-header-custom">
                <i class="bi bi-eye-slash text-primary"></i>
                <h6><?= te('Freigaben-Monitor') ?></h6>
            </div>
            <div class="card-body-custom">
                <p class="text-muted small mb-3">
                    <?= te('Automatisch öffentliche / externe SharePoint-Freigaben überwachen, Besitzer per E-Mail fragen und bei Nicht-Reaktion automatisch widerrufen.') ?>
                    <a href="/sharing/monitor" class="ms-1"><?= te('→ Zum Monitor') ?></a>
                </p>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-medium"><?= te('App-Basis-URL') ?></label>
                        <input type="url" name="app_base_url" class="form-control"
                               value="<?= $e($s['app_base_url'] ?? '') ?>"
                               placeholder="https://m365.firma.de">
                        <div class="form-text"><?= te('Wird für den Bestätigungslink in der E-Mail verwendet.') ?></div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-medium"><?= te('Prüfintervall') ?></label>
                        <div class="input-group">
                            <input type="number" name="share_review_interval_days" class="form-control"
                                   value="<?= (int)($s['share_review_interval_days'] ?? 30) ?>" min="1" max="365">
                            <span class="input-group-text"><?= te('Tage') ?></span>
                        </div>
                        <div class="form-text"><?= te('Wie oft wird eine Bestätigung angefordert?') ?></div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-medium"><?= te('Toleranzzeit') ?></label>
                        <div class="input-group">
                            <input type="number" name="share_review_grace_days" class="form-control"
                                   value="<?= (int)($s['share_review_grace_days'] ?? 7) ?>" min="1" max="60">
                            <span class="input-group-text"><?= te('Tage') ?></span>
                        </div>
                        <div class="form-text"><?= te('Zeit bis zum automatischen Widerruf nach Erinnerung.') ?></div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="share_review_only_anonymous"
                                   id="chkOnlyAnon" value="1"
                                   <?= ($s['share_review_only_anonymous'] ?? '0') === '1' ? 'checked' : '' ?>>
                            <label class="form-check-label" for="chkOnlyAnon">
                                <?= te('Nur öffentliche Links (Anyone-Links) überwachen') ?>
                                <span class="text-muted"><?= te('(deaktiviert = alle externen Freigaben)') ?></span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stale Accounts / Inactive Users -->
        <div class="content-card mb-4" data-tab="governance" id="stale-accounts">
            <div class="card-header-custom">
                <i class="bi bi-person-x text-primary"></i>
                <h6><?= te('Inaktive Konten') ?></h6>
            </div>
            <div class="card-body-custom">
                <p class="text-muted small mb-3">
                    <?= te('Benutzer, die sich länger als der konfigurierte Zeitraum nicht angemeldet haben, werden als inaktiv markiert.') ?>
                    <a href="/staleaccounts" class="ms-1"><?= te('→ Inaktive Konten anzeigen') ?></a>
                </p>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-medium"><?= te('Inaktivitätsschwelle') ?></label>
                        <div class="input-group">
                            <input type="number" name="stale_account_days" class="form-control"
                                   value="<?= (int)($s['stale_account_days'] ?? 90) ?>" min="1" max="730">
                            <span class="input-group-text"><?= te('Tage') ?></span>
                        </div>
                        <div class="form-text"><?= te('Ab wie vielen Tagen gilt ein Konto als inaktiv?') ?></div>
                    </div>
                </div>
                <hr>
                <h6 class="small text-muted text-uppercase mb-3"><?= te('Automatische Lizenzfreigabe') ?> <span class="badge-warning ms-1"><?= te('Optional') ?></span></h6>
                <div class="row g-3">
                    <div class="col-12">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="stale_auto_release_enabled"
                                   id="chkStaleAutoRelease" value="1"
                                   <?= ($s['stale_auto_release_enabled'] ?? '0') === '1' ? 'checked' : '' ?>
                                   onchange="document.getElementById('staleAutoReleaseOptions').style.display = this.checked ? '' : 'none'">
                            <label class="form-check-label fw-medium" for="chkStaleAutoRelease">
                                <?= te('Lizenzen automatisch entziehen bei langer Inaktivität') ?>
                            </label>
                        </div>
                        <div class="form-text">
                            <?= t('Wenn aktiviert, entfernt der Cron-Job (<code>run-stale-cleanup.php</code>) nach dem konfigurierten Zeitraum automatisch alle Lizenzen. <strong>Eine Warnung wird X Tage vorher per E-Mail gesendet.</strong>') ?>
                        </div>
                    </div>
                    <div id="staleAutoReleaseOptions" <?= ($s['stale_auto_release_enabled'] ?? '0') !== '1' ? 'style="display:none"' : '' ?>>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-medium"><?= te('Lizenz-Freigabe nach') ?></label>
                                <div class="input-group">
                                    <input type="number" name="stale_auto_release_days" class="form-control"
                                           value="<?= (int)($s['stale_auto_release_days'] ?? 180) ?>" min="1" max="1095">
                                    <span class="input-group-text"><?= te('Tagen') ?></span>
                                </div>
                                <div class="form-text"><?= te('Tage seit letzter Anmeldung, danach werden Lizenzen entzogen.') ?></div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-medium"><?= te('Vorwarnung') ?></label>
                                <div class="input-group">
                                    <input type="number" name="stale_warn_days_before" class="form-control"
                                           value="<?= (int)($s['stale_warn_days_before'] ?? 14) ?>" min="0" max="90">
                                    <span class="input-group-text"><?= te('Tage vorher') ?></span>
                                </div>
                                <div class="form-text"><?= te('E-Mail-Warnung X Tage vor der automatischen Lizenzfreigabe. 0 = keine Warnung.') ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Password Expiry -->
        <div class="content-card mb-4" data-tab="governance" id="password-expiry">
            <div class="card-header-custom">
                <i class="bi bi-key text-primary"></i>
                <h6><?= te('Passwort-Ablauf') ?></h6>
            </div>
            <div class="card-body-custom">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-medium"><?= te('Passwort-Gültigkeitsdauer') ?></label>
                        <div class="input-group">
                            <input type="number" name="password_expiry_days" class="form-control"
                                   value="<?= (int)($s['password_expiry_days'] ?? 90) ?>" min="1" max="365">
                            <span class="input-group-text"><?= te('Tage') ?></span>
                        </div>
                        <div class="form-text"><?= t('Standard: 90 Tage. Gilt für alle Benutzer ohne <code>DisablePasswordExpiration</code>-Richtlinie.') ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Weekly Report -->
        <div class="content-card mb-4" data-tab="benachrichtigungen" id="weekly-report">
            <div class="card-header-custom">
                <i class="bi bi-envelope-paper text-primary"></i>
                <h6><?= te('Wöchentlicher E-Mail-Report') ?></h6>
            </div>
            <div class="card-body-custom">
                <p class="text-muted small mb-3">
                    <?= te('Sendet jeden Woche einen Zusammenfassungsbericht mit Benutzer-, Lizenz-, Sicherheits- und Freigabe-Kennzahlen. Voraussetzung: Alert-E-Mail muss konfiguriert sein.') ?>
                </p>
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="weekly_report_enabled"
                                   id="weeklyReportEnabled" role="switch"
                                   <?= ($s['weekly_report_enabled'] ?? '0') === '1' ? 'checked' : '' ?>
                                   onchange="document.getElementById('weeklyReportOptions').style.display=this.checked?'block':'none'">
                            <label class="form-check-label fw-medium" for="weeklyReportEnabled"><?= te('Wöchentlichen Report aktivieren') ?></label>
                        </div>
                    </div>
                    <div id="weeklyReportOptions" <?= ($s['weekly_report_enabled'] ?? '0') !== '1' ? 'style="display:none"' : '' ?>>
                        <div class="col-md-4 mt-2">
                            <label class="form-label fw-medium"><?= te('Versandtag') ?></label>
                            <select name="weekly_report_day" class="form-select">
                                <?php
                                $days = ['1'=>t('Montag'),'2'=>t('Dienstag'),'3'=>t('Mittwoch'),'4'=>t('Donnerstag'),'5'=>t('Freitag'),'6'=>t('Samstag'),'7'=>t('Sonntag')];
                                $sel  = (string)($s['weekly_report_day'] ?? '1');
                                foreach ($days as $val => $label): ?>
                                    <option value="<?= $val ?>" <?= $val === $sel ? 'selected' : '' ?>><?= $label ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text"><?= te('Der Cron-Job läuft täglich und prüft, ob heute der konfigurierte Tag ist.') ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- License Criteria -->
        <div class="content-card mb-4" data-tab="ki" id="license-criteria">
            <div class="card-header-custom">
                <i class="bi bi-lightbulb text-primary"></i>
                <h6><?= te('Lizenz-Berater — Kriterien') ?></h6>
            </div>
            <div class="card-body-custom">
                <p class="text-muted small mb-3">
                    <?= t('Diese Kriterien bestimmen, welche Lizenzpläne im <a href="/licenseadvisor">Lizenz-Berater</a> als „passend" eingestuft werden. Aktiviere nur die Dienste, die für euren Tenant relevant sind. Die Einstellungen können auch direkt im Lizenz-Berater geändert werden.') ?>
                </p>
                <div class="row g-3">
                    <?php
                    $licCriteria = [
                        'lic_need_exchange_online' => ['Exchange Online', 'envelope'],
                        'lic_need_office_desktop'  => [t('Office Desktop (Apps)'), 'grid'],
                        'lic_need_teams'           => ['Microsoft Teams', 'chat-dots'],
                        'lic_need_sharepoint'      => ['SharePoint Online', 'share'],
                        'lic_need_onedrive'        => ['OneDrive for Business', 'cloud'],
                        'lic_need_intune'          => [t('Intune (Geräteverwaltung)'), 'phone'],
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
        <div class="content-card mb-4" data-tab="ki" id="license-prices">
            <div class="card-header-custom">
                <i class="bi bi-currency-euro text-primary"></i>
                <h6><?= te('Lizenzpreise konfigurieren') ?></h6>
            </div>
            <div class="card-body-custom">
                <p class="text-muted small mb-3">
                    <?= te('Überschreibe die Katalog-Standardpreise (Listenpreise Mai 2025) mit deinen tatsächlichen Partner- oder CSP-Netto-Preisen. Die angepassten Preise gelten für Lizenz-Berater und Lizenzkosten.') ?>
                </p>
                <a href="/settings/license-prices" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-pencil-square me-1"></i><?= te('Preise bearbeiten') ?>
                </a>
            </div>
        </div>

        <!-- Branding: public review page -->
        <div class="content-card mb-4" data-tab="allgemein" id="branding">
            <div class="card-header-custom">
                <i class="bi bi-palette text-primary"></i>
                <h6><?= te('Branding — Öffentliche Bestätigungsseite') ?></h6>
            </div>
            <div class="card-body-custom">
                <p class="text-muted small mb-3">
                    <?= te('Passt das Erscheinungsbild der öffentlichen Freigabe-Bestätigungsseite an (der Link, den Freigabe-Besitzer per E-Mail erhalten).') ?>
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

        <div class="settings-savebar d-flex gap-2 align-items-center">
            <button type="submit" class="btn btn-primary px-4">
                <i class="bi bi-check2 me-1"></i> Einstellungen speichern
            </button>
            <span class="text-muted small">Speichert alle Tabs gemeinsam.</span>
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

// ── Tab-Navigation ─────────────────────────────────────────────────────
(function () {
    const KEY     = 'm365_settings_tab';
    const tabs    = document.querySelectorAll('.settings-tabs button[data-tab-target]');
    const cards   = document.querySelectorAll('[data-tab]');
    const savebar = document.querySelector('.settings-savebar');

    function activate(name) {
        tabs.forEach(b => b.classList.toggle('active', b.dataset.tabTarget === name));
        cards.forEach(c => c.classList.toggle('tab-active', c.dataset.tab === name));
        try { localStorage.setItem(KEY, name); } catch (_) {}
        // Wenn der User per Hash auf eine konkrete Sektion springt
        // (#admin-password etc.), aktiviere automatisch den richtigen Tab.
    }

    tabs.forEach(b => b.addEventListener('click', () => activate(b.dataset.tabTarget)));

    // Initial: aus Hash → aus localStorage → 'allgemein'
    let initial = 'allgemein';
    if (location.hash) {
        const anchored = document.querySelector(location.hash);
        if (anchored && anchored.dataset.tab) initial = anchored.dataset.tab;
    } else {
        try {
            const saved = localStorage.getItem(KEY);
            if (saved && document.querySelector('button[data-tab-target="' + saved + '"]')) initial = saved;
        } catch (_) {}
    }
    activate(initial);

    // Hash-Change wechselt Tab + scrollt zum Anker
    window.addEventListener('hashchange', () => {
        const target = document.querySelector(location.hash);
        if (target && target.dataset.tab) {
            activate(target.dataset.tab);
            setTimeout(() => target.scrollIntoView({ behavior: 'smooth', block: 'start' }), 50);
        }
    });
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
