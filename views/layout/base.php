<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= \App\Core\View::escape($pageTitle ?? 'Dashboard') ?> — <?= \App\Core\View::escape(\App\Core\Config::getInstance()->get('app_name', 'M365 Tenant Tool')) ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="/public/css/app.css">
</head>
<body>
<div class="app-wrapper">

    <!-- Sidebar -->
    <nav class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <div class="brand-icon">M</div>
            <span class="brand-name"><?= \App\Core\View::escape(\App\Core\Config::getInstance()->get('app_name', 'M365 Tool')) ?></span>
        </div>
        <div class="sidebar-nav">
            <?php require BASE_PATH . '/views/layout/sidebar.php'; ?>
        </div>
        <div style="padding: 12px 16px; border-top: 1px solid rgba(255,255,255,0.06);">
            <a href="/logout" class="nav-item" style="color: #f87171;">
                <span class="nav-icon"><i class="bi bi-box-arrow-right"></i></span>
                <span class="nav-label">Abmelden</span>
            </a>
        </div>
    </nav>

    <!-- Main content -->
    <div class="main-content" id="mainContent">

        <!-- Topbar -->
        <header class="topbar">
            <button class="toggle-btn" id="sidebarToggle" title="Sidebar ein-/ausblenden">
                <i class="bi bi-list" style="font-size: 20px;"></i>
            </button>
            <div class="breadcrumb-area">
                <span class="page-title"><?= \App\Core\View::escape($pageTitle ?? 'Dashboard') ?></span>
                <?php if (!empty($breadcrumb)): ?>
                    <span><?= implode(' / ', array_map('htmlspecialchars', $breadcrumb)) ?></span>
                <?php endif; ?>
            </div>
            <div class="topbar-right">
                <span class="refresh-badge" id="lastRefresh" title="Letzter Cache-Refresh"></span>
                <a href="?refresh=1" class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1" title="Daten aktualisieren">
                    <i class="bi bi-arrow-clockwise"></i>
                </a>
                <div class="d-flex align-items-center gap-2 ms-2 ps-2" style="border-left: 1px solid #e5e7eb;">
                    <i class="bi bi-person-circle text-secondary"></i>
                    <span style="font-size: 13px; font-weight: 500;"><?= \App\Core\View::escape(\App\Auth\LocalAuth::username()) ?></span>
                </div>
            </div>
        </header>

        <!-- Page content -->
        <main class="page-content">
            <?php
            $flash = \App\Core\Session::getFlash('success');
            if ($flash): ?>
                <div class="alert alert-success alert-dismissible mb-4" role="alert">
                    <?= htmlspecialchars($flash) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php echo $content ?? ''; ?>
        </main>
    </div>
</div>

<div class="toast-container"></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="/public/js/app.js"></script>
</body>
</html>
