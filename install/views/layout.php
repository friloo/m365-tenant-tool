<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>M365 Tenant Tool — Setup</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <style>
        body { background: #f0f2f5; }
        .wizard-card { max-width: 640px; margin: 60px auto; }
        .step-indicator .step {
            display: inline-flex; align-items: center; justify-content: center;
            width: 36px; height: 36px; border-radius: 50%;
            background: #dee2e6; color: #6c757d; font-weight: 600; font-size: .9rem;
        }
        .step-indicator .step.active  { background: #0078d4; color: #fff; }
        .step-indicator .step.done    { background: #198754; color: #fff; }
        .step-indicator .line         { flex: 1; height: 2px; background: #dee2e6; margin: 0 6px; }
        .step-indicator .line.done    { background: #198754; }
        .brand-header { text-align: center; margin-bottom: 32px; }
        .brand-header .logo { font-size: 2rem; font-weight: 700; color: #0078d4; }
        .brand-header .sub  { color: #6c757d; font-size: .95rem; }
    </style>
</head>
<body>
<div class="container">
    <div class="wizard-card">
        <div class="brand-header">
            <div class="logo">M365 Tenant Tool</div>
            <div class="sub">Einrichtungsassistent</div>
        </div>

        <!-- Step Indicators -->
        <div class="step-indicator d-flex align-items-center mb-4">
            <?php
            $labels = ['Datenbank', 'Admin', 'Azure AD', 'Einstellungen', 'Fertig'];
            for ($i = 1; $i <= 5; $i++):
                $cls = $i < $step ? 'done' : ($i === $step ? 'active' : '');
            ?>
                <?php if ($i > 1): ?>
                    <div class="line <?= $i <= $step ? 'done' : '' ?>"></div>
                <?php endif; ?>
                <div class="step <?= $cls ?>" title="Schritt <?= $i ?>: <?= $labels[$i-1] ?>">
                    <?php if ($i < $step): ?>✓<?php else: echo $i; endif; ?>
                </div>
            <?php endfor; ?>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible" role="alert">
                <?= htmlspecialchars($error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="card shadow-sm">
            <div class="card-body p-4">
                <?php require __DIR__ . "/../steps/step{$step}.php"; ?>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
