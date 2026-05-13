<?php use App\Core\View; $e = fn($v) => View::escape($v); ?>

<div class="alert alert-info mb-4">
    <i class="bi bi-info-circle me-2"></i>
    <strong>DLP-Richtlinien</strong> (Data Loss Prevention) werden im Microsoft Purview Compliance Portal verwaltet.
    Hier sehen Sie eine Übersicht der verfügbaren Grundlagen.
</div>

<div class="mb-4">
    <a href="https://compliance.microsoft.com/datalossprevention" target="_blank" rel="noopener noreferrer"
       class="btn btn-primary btn-lg">
        <i class="bi bi-shield-lock me-2"></i>Microsoft Purview – DLP-Richtlinien öffnen
        <i class="bi bi-box-arrow-up-right ms-2"></i>
    </a>
</div>

<div class="row g-3 mb-4">
    <div class="col-sm-4">
        <div class="metric-card">
            <div class="metric-label">Sensitivity Labels aktiv</div>
            <div class="metric-value"><?= (int)$activeCount ?></div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="metric-card">
            <div class="metric-label">Sensitivity Labels gesamt</div>
            <div class="metric-value"><?= count($labels) ?></div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="metric-card" style="cursor:pointer;" onclick="window.open('https://compliance.microsoft.com/datalossprevention','_blank')">
            <div class="metric-label">Purview Portal</div>
            <div class="metric-value" style="font-size:1.4rem;">
                <i class="bi bi-box-arrow-up-right text-primary"></i>
            </div>
        </div>
    </div>
</div>

<div class="content-card mb-4">
    <div class="table-toolbar">
        <span class="fw-semibold"><i class="bi bi-tags me-2"></i>Sensitivity Labels</span>
    </div>

    <?php if (empty($labels)): ?>
        <div class="alert alert-warning m-3">
            <i class="bi bi-exclamation-triangle me-2"></i>
            Für die Anzeige von Sensitivity Labels wird die Berechtigung
            <code>InformationProtectionPolicy.Read.All</code> benötigt.
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="data-table" id="labelsTable">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Priorität</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    usort($labels, fn($a, $b) => (int)($a['priority'] ?? 0) - (int)($b['priority'] ?? 0));
                    foreach ($labels as $label):
                        $isActive = $label['isActive'] ?? false;
                    ?>
                        <tr>
                            <td class="fw-medium"><?= $e($label['name'] ?? '') ?></td>
                            <td><?= (int)($label['priority'] ?? 0) ?></td>
                            <td>
                                <?php if ($isActive): ?>
                                    <span class="badge-ok">Aktiv</span>
                                <?php else: ?>
                                    <span class="badge-disabled">Inaktiv</span>
                                <?php endif; ?>
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
        <div class="col-md-4">
            <a href="https://compliance.microsoft.com/datalossprevention/policies"
               target="_blank" rel="noopener noreferrer" class="card text-decoration-none h-100 border">
                <div class="card-body d-flex align-items-start gap-3">
                    <div class="fs-2 text-danger flex-shrink-0"><i class="bi bi-shield-exclamation"></i></div>
                    <div>
                        <div class="fw-semibold">DLP-Richtlinien</div>
                        <div class="text-muted small">Richtlinien erstellen und verwalten</div>
                    </div>
                    <i class="bi bi-box-arrow-up-right text-muted ms-auto align-self-start small"></i>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="https://compliance.microsoft.com/dataclassification/activityexplorer"
               target="_blank" rel="noopener noreferrer" class="card text-decoration-none h-100 border">
                <div class="card-body d-flex align-items-start gap-3">
                    <div class="fs-2 text-warning flex-shrink-0"><i class="bi bi-activity"></i></div>
                    <div>
                        <div class="fw-semibold">Aktivitäts-Explorer</div>
                        <div class="text-muted small">Benutzeraktivitäten analysieren</div>
                    </div>
                    <i class="bi bi-box-arrow-up-right text-muted ms-auto align-self-start small"></i>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="https://compliance.microsoft.com/dataclassification/contentexplorer"
               target="_blank" rel="noopener noreferrer" class="card text-decoration-none h-100 border">
                <div class="card-body d-flex align-items-start gap-3">
                    <div class="fs-2 text-info flex-shrink-0"><i class="bi bi-search"></i></div>
                    <div>
                        <div class="fw-semibold">Inhalts-Explorer</div>
                        <div class="text-muted small">Klassifizierte Inhalte durchsuchen</div>
                    </div>
                    <i class="bi bi-box-arrow-up-right text-muted ms-auto align-self-start small"></i>
                </div>
            </a>
        </div>
    </div>
</div>
