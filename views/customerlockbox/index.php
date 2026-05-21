<?php use App\Core\View; $e = fn($v) => View::escape($v); ?>

<?php if (!empty($flash)): ?>
    <div class="alert alert-success alert-dismissible mb-3"><i class="bi bi-check-circle me-2"></i><?= $e($flash) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<div class="alert alert-info d-flex gap-3 mb-3">
    <i class="bi bi-lock-fill flex-shrink-0 mt-1" style="font-size:1.4rem;color:#0078d4;"></i>
    <div>
        <strong>Customer Lockbox</strong>: ohne Customer Lockbox darf Microsoft Support im Notfall
        auf Ihre Daten zugreifen, ohne dass Sie es erfahren oder zustimmen können. Mit aktiviertem
        Lockbox muss ein Tenant-Admin jeden Microsoft-Support-Zugriff aktiv approven; ohne
        Approval gibt es <em>keinen</em> Zugriff.
        <strong>Voraussetzung:</strong> Microsoft 365 E5 oder als Add-on, plus Bedingung für viele
        DSGVO-Verträge mit Mandanten in regulierten Branchen.
    </div>
</div>

<div class="content-card mb-4">
    <div class="card-header-custom"><i class="bi bi-gear text-primary"></i><h6>Status (manuell gepflegt)</h6></div>
    <div class="card-body-custom">
        <form method="post" action="/customerlockbox/save">
            <?= \App\Core\Csrf::field() ?>
            <div class="row g-3">
                <div class="col-md-12">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="enabled" id="lockboxEnabled" value="1"
                               <?= $data['enabled'] ? 'checked' : '' ?>>
                        <label class="form-check-label fw-medium" for="lockboxEnabled">
                            Customer Lockbox ist im Tenant aktiviert
                        </label>
                    </div>
                </div>
                <div class="col-md-8">
                    <label class="form-label fw-medium">Approver-Liste <span class="text-muted small">(UPNs, kommagetrennt)</span></label>
                    <input type="text" name="approvers" class="form-control" value="<?= $e($data['approvers']) ?>"
                           placeholder="admin1@firma.de, admin2@firma.de">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-medium">Reaktions-SLA (Stunden)</label>
                    <input type="number" name="sla_hours" class="form-control" min="0" max="72"
                           value="<?= (int)$data['sla_hours'] ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-medium">Letzte Review</label>
                    <input type="date" name="last_review" class="form-control" value="<?= $e($data['last_review']) ?>">
                    <div class="form-text">Halbjährlich prüfen, ob noch alles aktuell ist.</div>
                </div>
            </div>
            <button type="submit" class="btn btn-primary mt-3">
                <i class="bi bi-check2 me-1"></i>Speichern
            </button>
        </form>
    </div>
</div>

<div class="content-card mb-4">
    <div class="card-header-custom"><i class="bi bi-link-45deg text-info"></i><h6>Konfiguration im Admin-Center</h6></div>
    <div class="card-body-custom">
        <p class="text-muted small mb-3">
            Customer Lockbox wird im Microsoft 365 Admin Center konfiguriert. Microsoft Graph stellt
            für diese Einstellung keine Schreib-Endpunkt zur Verfügung — daher der manuelle Eintrag
            oben und die direkten Links unten.
        </p>
        <div class="d-flex flex-wrap gap-2">
            <a href="https://admin.microsoft.com/Adminportal/Home#/securityprivacy" target="_blank" rel="noopener" class="btn btn-outline-primary">
                <i class="bi bi-box-arrow-up-right me-1"></i>M365 Admin Center → Security &amp; Privacy
            </a>
            <a href="https://learn.microsoft.com/de-de/purview/customer-lockbox-requests" target="_blank" rel="noopener" class="btn btn-outline-secondary">
                <i class="bi bi-book me-1"></i>Microsoft-Doku
            </a>
        </div>
    </div>
</div>
