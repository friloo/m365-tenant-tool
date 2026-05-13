<?php use App\Core\View; $e = fn($v) => View::escape($v); ?>

<div class="alert alert-info mb-4">
    <i class="bi bi-info-circle me-2"></i>
    <strong>Aufbewahrungsrichtlinien</strong> werden im Microsoft Purview Compliance Portal konfiguriert.
    Hier werden verwandte Compliance-Daten aus Graph angezeigt.
</div>

<div class="mb-4">
    <a href="https://compliance.microsoft.com/informationgovernance" target="_blank" rel="noopener noreferrer"
       class="btn btn-primary btn-lg">
        <i class="bi bi-archive me-2"></i>Microsoft Purview – Information Governance öffnen
        <i class="bi bi-box-arrow-up-right ms-2"></i>
    </a>
</div>

<div class="row g-3 mb-4">
    <div class="col-sm-6">
        <div class="metric-card">
            <div class="metric-label">Offene eDiscovery-Cases</div>
            <div class="metric-value" style="color:<?= $openCount > 0 ? '#dc2626' : '#111827' ?>">
                <?= (int)$openCount ?>
            </div>
        </div>
    </div>
    <div class="col-sm-6">
        <div class="metric-card">
            <div class="metric-label">Geschlossene Cases</div>
            <div class="metric-value"><?= (int)$closedCount ?></div>
        </div>
    </div>
</div>

<div class="content-card mb-4">
    <div class="table-toolbar">
        <span class="fw-semibold"><i class="bi bi-folder2-open me-2"></i>eDiscovery-Cases</span>
    </div>

    <?php if (empty($cases)): ?>
        <div class="alert alert-info m-3">
            <i class="bi bi-info-circle me-2"></i>
            Keine eDiscovery-Cases gefunden oder die Berechtigung
            <code>eDiscovery.Read.All</code> fehlt. Für die Anzeige werden entsprechende Graph-Berechtigungen benötigt.
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="data-table" id="casesTable">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Status</th>
                        <th>Erstellt</th>
                        <th>Geschlossen</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cases as $case):
                        $status = $case['status'] ?? 'unknown';
                        $badgeClass = match ($status) {
                            'active'        => 'badge-warning',
                            'closed'        => 'badge-neutral',
                            'pendingDelete' => 'badge-disabled',
                            default         => 'badge-info',
                        };
                        $statusLabel = match ($status) {
                            'active'        => 'Aktiv',
                            'closed'        => 'Geschlossen',
                            'pendingDelete' => 'Löschung ausstehend',
                            default         => $e($status),
                        };
                    ?>
                        <tr>
                            <td class="fw-medium"><?= $e($case['displayName'] ?? '') ?></td>
                            <td><span class="<?= $badgeClass ?>"><?= $statusLabel ?></span></td>
                            <td style="font-size:12px;">
                                <?= !empty($case['createdDateTime']) ? date('d.m.Y', strtotime($case['createdDateTime'])) : '–' ?>
                            </td>
                            <td style="font-size:12px;">
                                <?= !empty($case['closedDateTime']) ? date('d.m.Y', strtotime($case['closedDateTime'])) : '–' ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<div class="content-card">
    <div class="table-toolbar mb-3">
        <span class="fw-semibold"><i class="bi bi-link-45deg me-2"></i>Direktlinks – Purview Compliance Portal</span>
    </div>
    <div class="row g-3">
        <div class="col-md-3">
            <a href="https://compliance.microsoft.com/informationgovernance/retention"
               target="_blank" rel="noopener noreferrer" class="card text-decoration-none h-100 border">
                <div class="card-body d-flex flex-column align-items-start gap-2">
                    <div class="fs-2 text-primary"><i class="bi bi-clock-history"></i></div>
                    <div>
                        <div class="fw-semibold">Aufbewahrungsrichtlinien</div>
                        <div class="text-muted small">Richtlinien für Datenaufbewahrung verwalten</div>
                    </div>
                    <i class="bi bi-box-arrow-up-right text-muted small mt-auto"></i>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="https://compliance.microsoft.com/informationgovernance/labels"
               target="_blank" rel="noopener noreferrer" class="card text-decoration-none h-100 border">
                <div class="card-body d-flex flex-column align-items-start gap-2">
                    <div class="fs-2 text-success"><i class="bi bi-tag"></i></div>
                    <div>
                        <div class="fw-semibold">Aufbewahrungsbezeichnungen</div>
                        <div class="text-muted small">Labels für Inhalte definieren und verwalten</div>
                    </div>
                    <i class="bi bi-box-arrow-up-right text-muted small mt-auto"></i>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="https://compliance.microsoft.com/ediscovery/home"
               target="_blank" rel="noopener noreferrer" class="card text-decoration-none h-100 border">
                <div class="card-body d-flex flex-column align-items-start gap-2">
                    <div class="fs-2 text-warning"><i class="bi bi-folder2-open"></i></div>
                    <div>
                        <div class="fw-semibold">eDiscovery</div>
                        <div class="text-muted small">Fälle und Inhaltssuche verwalten</div>
                    </div>
                    <i class="bi bi-box-arrow-up-right text-muted small mt-auto"></i>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="https://compliance.microsoft.com/supervisoryreview"
               target="_blank" rel="noopener noreferrer" class="card text-decoration-none h-100 border">
                <div class="card-body d-flex flex-column align-items-start gap-2">
                    <div class="fs-2 text-info"><i class="bi bi-chat-square-text"></i></div>
                    <div>
                        <div class="fw-semibold">Kommunikations-Compliance</div>
                        <div class="text-muted small">Kommunikationsrichtlinien überwachen</div>
                    </div>
                    <i class="bi bi-box-arrow-up-right text-muted small mt-auto"></i>
                </div>
            </a>
        </div>
    </div>
</div>
