<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= \App\Core\View::escape($pageTitle ?? 'Dashboard') ?> — <?= \App\Core\View::escape(\App\Core\Config::getInstance()->get('app_name', 'M365 Tenant Tool')) ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="/public/css/app.css">
    <script>
    // Defined in <head> so it's always available before any inline view script runs.
    function initTableSearch(inputId, tableId) {
        function attach() {
            var input = document.getElementById(inputId);
            var table = document.getElementById(tableId);
            if (!input || !table) return;
            input.addEventListener('input', function () {
                var term = this.value.toLowerCase();
                table.querySelectorAll('tbody tr').forEach(function (row) {
                    var match = !term || row.textContent.toLowerCase().includes(term);
                    row.dataset.searchMatch = match ? '1' : '0';
                    // Direct fallback (when no initPagination is active)
                    row.style.display = match && row.dataset.filterMatch !== '0' ? '' : 'none';
                });
                table.dispatchEvent(new CustomEvent('hs:filter'));
            });
        }
        if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', attach);
        else attach();
    }
    </script>
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
        <div style="padding: 10px 16px 14px; text-align:center; border-top: 1px solid rgba(255,255,255,0.04);">
            <a href="https://loheide.eu" target="_blank" rel="noopener"
               style="font-size:10px; color:rgba(255,255,255,0.28); text-decoration:none; line-height:1.6; display:block;"
               title="Entwickelt von Friederich Loheide">
                Entwickelt von<br>
                <span style="color:rgba(255,255,255,0.45); font-weight:500;">Friederich Loheide</span><br>
                <span style="color:rgba(255,255,255,0.28);">loheide.eu</span>
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
            <!-- Quick search trigger -->
            <button class="qs-trigger" id="qsTrigger" title="Schnellsuche (Strg+K)">
                <i class="bi bi-search"></i>
                <span class="qs-trigger-label">Suchen…</span>
                <kbd>Strg K</kbd>
            </button>

            <div class="topbar-right">
                <span class="refresh-badge" id="lastRefresh" data-ts="<?= time() * 1000 ?>" title="Letzter Datenabruf"></span>
                <a href="?refresh=1" class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1" title="Daten aktualisieren">
                    <i class="bi bi-arrow-clockwise"></i>
                </a>
                <div class="d-flex align-items-center gap-2 ms-2 ps-2" style="border-left: 1px solid #e5e7eb;">
                    <?php if (\App\Core\Session::get('auth_type') === 'microsoft'): ?>
                        <i class="bi bi-microsoft" style="color:#0078d4;"></i>
                    <?php else: ?>
                        <i class="bi bi-person-circle text-secondary"></i>
                    <?php endif; ?>
                    <span style="font-size:13px;font-weight:500;"><?= \App\Core\View::escape(\App\Auth\LocalAuth::username()) ?></span>
                    <?php if (\App\Auth\LocalAuth::role() === 'operator'): ?>
                        <span class="badge-warning" style="font-size:10px;">Operator</span>
                    <?php endif; ?>
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

<!-- Command Palette -->
<div class="qs-overlay" id="qsOverlay" aria-hidden="true">
    <div class="qs-palette" role="dialog" aria-label="Schnellsuche">
        <div class="qs-input-wrap">
            <i class="bi bi-search qs-input-icon"></i>
            <input type="text" id="qsInput" class="qs-input" placeholder="Seite oder Einstellung suchen…" autocomplete="off" spellcheck="false">
            <kbd class="qs-esc-hint">Esc</kbd>
        </div>
        <div class="qs-results" id="qsResults" role="listbox"></div>
    </div>
</div>

<div class="toast-container"></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="/public/js/app.js"></script>
</body>
</html>
