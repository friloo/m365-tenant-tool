<!DOCTYPE html>
<html lang="<?= \App\Core\View::escape(\App\Core\I18n::locale()) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= \App\Core\View::escape($pageTitle ?? 'Dashboard') ?> — <?= \App\Core\View::escape(\App\Core\Config::getInstance()->get('app_name', 'M365 Tenant Tool')) ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="/public/css/app.css?v=<?= @filemtime(BASE_PATH . '/public/css/app.css') ?: '1' ?>">
    <meta name="csrf-token" content="<?= \App\Core\Csrf::token() ?>">
    <script>
    // Translations needed by the inline JS below (pager, etc.). Filled from the
    // active locale so client-rendered chrome matches the server-rendered page.
    window.I18N = {
        noEntries: <?= json_encode(t('Keine Einträge gefunden'), JSON_UNESCAPED_UNICODE) ?>,
        of:        <?= json_encode(t('von'), JSON_UNESCAPED_UNICODE) ?>
    };

    // Both functions live in <head> so they are always defined before any
    // inline view script runs (app.js loads after the body content).

    function initTableSearch(inputId, tableId) {
        function attach() {
            var input = document.getElementById(inputId);
            var table = document.getElementById(tableId);
            if (!input || !table) return;
            input.addEventListener('input', function () {
                var term = this.value.toLowerCase();
                var hasPager = table.dataset.hasPager === '1';
                table.querySelectorAll('tbody tr').forEach(function (row) {
                    var match = !term || row.textContent.toLowerCase().includes(term);
                    row.dataset.searchMatch = match ? '1' : '0';
                    // Direct visibility fallback for tables without initPagination
                    if (!hasPager) {
                        row.style.display = match && row.dataset.filterMatch !== '0' ? '' : 'none';
                    }
                });
                table.dispatchEvent(new CustomEvent('hs:filter'));
            });
        }
        if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', attach);
        else attach();
    }

    function initPagination(tableId, initialSize) {
        var table = document.getElementById(tableId);
        if (!table) return;
        table.dataset.hasPager = '1'; // signals initTableSearch to skip direct style.display
        var pageSize = initialSize || 25;
        var page = 1;

        function allRows() {
            return Array.from(table.querySelectorAll('tbody tr'));
        }
        function visibleRows() {
            return allRows().filter(function (r) {
                return r.dataset.searchMatch !== '0' && r.dataset.filterMatch !== '0';
            });
        }
        function applyPage() {
            var vr    = visibleRows();
            var total = vr.length;
            var totalPages = Math.max(1, Math.ceil(total / pageSize));
            if (page > totalPages) page = totalPages;
            var start = (page - 1) * pageSize;
            var end   = start + pageSize;
            allRows().forEach(function (r) {
                if (r.dataset.searchMatch === '0' || r.dataset.filterMatch === '0') {
                    r.style.display = 'none';
                } else {
                    r.style.display = (vr.indexOf(r) >= start && vr.indexOf(r) < end) ? '' : 'none';
                }
            });
            renderPager(total, totalPages);
        }
        function renderPager(total, totalPages) {
            var pagerId = tableId + '-pager';
            var pager   = document.getElementById(pagerId);
            if (!pager) {
                pager = document.createElement('div');
                pager.id = pagerId;
                pager.className = 'pager-bar';
                var card = table.closest('.content-card');
                if (card) card.appendChild(pager);
                else table.parentElement.insertAdjacentElement('afterend', pager);
            }
            if (total === 0) {
                pager.innerHTML = '<span class="text-muted small">' + window.I18N.noEntries + '</span>';
                return;
            }
            var from = (page - 1) * pageSize + 1;
            var to   = Math.min(page * pageSize, total);
            var btns = '', lastEllipsis = false;
            for (var i = 1; i <= totalPages; i++) {
                if (i === 1 || i === totalPages || Math.abs(i - page) <= 1) {
                    var cls = i === page ? 'btn-primary' : 'btn-outline-secondary';
                    btns += '<button class="btn btn-sm ' + cls + ' pager-btn px-2 py-1" style="min-width:32px;" data-p="' + i + '">' + i + '</button>';
                    lastEllipsis = false;
                } else if (!lastEllipsis) {
                    btns += '<span class="text-muted small px-1">…</span>';
                    lastEllipsis = true;
                }
            }
            pager.innerHTML =
                '<span class="text-muted small">' + from + '–' + to + ' ' + window.I18N.of + ' ' + total + '</span>' +
                '<div class="d-flex align-items-center gap-1">' +
                '<button class="btn btn-sm btn-outline-secondary pager-btn px-2 py-1" data-p="prev"' + (page <= 1 ? ' disabled' : '') + '>‹</button>' +
                btns +
                '<button class="btn btn-sm btn-outline-secondary pager-btn px-2 py-1" data-p="next"' + (page >= totalPages ? ' disabled' : '') + '>›</button>' +
                '</div>';
            pager.querySelectorAll('.pager-btn').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var v = btn.dataset.p;
                    if      (v === 'prev') { if (page > 1) page--; }
                    else if (v === 'next') { if (page < totalPages) page++; }
                    else    page = parseInt(v, 10);
                    applyPage();
                });
            });
        }
        // Initialise default match state on all rows
        allRows().forEach(function (r) {
            if (!r.dataset.searchMatch) r.dataset.searchMatch = '1';
            if (!r.dataset.filterMatch) r.dataset.filterMatch = '1';
        });
        // Re-page whenever search or filter fires
        table.addEventListener('hs:filter', function () { page = 1; applyPage(); });
        applyPage();
    }

    // Defense-in-depth: every POST form on every authenticated page must carry
    // a CSRF token. Forms in views that forgot to render the hidden _csrf field
    // would otherwise hit the router's 419 page. We auto-inject the token from
    // the <meta name="csrf-token"> tag right before submit.
    document.addEventListener('submit', function (ev) {
        const form = ev.target;
        if (!form || form.tagName !== 'FORM') return;
        const method = (form.method || 'GET').toUpperCase();
        if (method !== 'POST') return;
        if (form.querySelector('input[name="_csrf"]')) return;
        const meta = document.querySelector('meta[name="csrf-token"]');
        if (!meta) return;
        const inp = document.createElement('input');
        inp.type = 'hidden';
        inp.name = '_csrf';
        inp.value = meta.content || '';
        form.appendChild(inp);
    }, true);
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
                <span class="nav-label"><?= te('Abmelden') ?></span>
            </a>
        </div>
        <div style="padding: 10px 16px 14px; text-align:center; border-top: 1px solid rgba(255,255,255,0.04);">
            <a href="https://loheide.eu" target="_blank" rel="noopener"
               style="font-size:10px; color:rgba(255,255,255,0.28); text-decoration:none; line-height:1.6; display:block;"
               title="<?= te('Entwickelt von') ?> Friederich Loheide">
                <?= te('Entwickelt von') ?><br>
                <span style="color:rgba(255,255,255,0.45); font-weight:500;">Friederich Loheide</span><br>
                <span style="color:rgba(255,255,255,0.28);">loheide.eu</span>
            </a>
        </div>
    </nav>

    <!-- Main content -->
    <div class="main-content" id="mainContent">

        <!-- Topbar -->
        <header class="topbar">
            <button class="toggle-btn" id="sidebarToggle" title="<?= te('Sidebar ein-/ausblenden') ?>">
                <i class="bi bi-list" style="font-size: 20px;"></i>
            </button>
            <div class="breadcrumb-area">
                <span class="page-title"><?= \App\Core\View::escape($pageTitle ?? 'Dashboard') ?></span>
                <?php if (!empty($breadcrumb)): ?>
                    <span><?= implode(' / ', array_map('htmlspecialchars', $breadcrumb)) ?></span>
                <?php endif; ?>
            </div>
            <!-- Quick search trigger -->
            <button class="qs-trigger" id="qsTrigger" title="<?= te('Schnellsuche (Strg+K)') ?>">
                <i class="bi bi-search"></i>
                <span class="qs-trigger-label"><?= te('Suchen…') ?></span>
                <kbd>Strg K</kbd>
            </button>

            <div class="topbar-right">
                <span class="refresh-badge" id="lastRefresh" data-ts="<?= time() * 1000 ?>" title="<?= te('Letzter Datenabruf') ?>"></span>
                <?php $__locale = \App\Core\I18n::locale(); ?>
                <div class="dropdown">
                    <button type="button" class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1" data-bs-toggle="dropdown" aria-expanded="false" title="<?= te('Sprache wechseln') ?>">
                        <i class="bi bi-translate"></i>
                        <span style="font-size:12px;font-weight:600;text-transform:uppercase;"><?= \App\Core\View::escape($__locale) ?></span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <?php foreach (\App\Core\I18n::supported() as $__code => $__name): ?>
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-2 <?= $__code === $__locale ? 'active' : '' ?>"
                                   href="<?= \App\Core\View::escape(\App\Core\I18n::switchUrl($__code)) ?>">
                                    <span class="text-uppercase text-muted" style="font-size:11px;width:20px;"><?= \App\Core\View::escape($__code) ?></span>
                                    <span><?= \App\Core\View::escape($__name) ?></span>
                                    <?php if ($__code === $__locale): ?><i class="bi bi-check2 ms-auto"></i><?php endif; ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <button id="favToggle" type="button" class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1" title="<?= te('Zu Favoriten hinzufügen') ?>">
                    <i class="bi bi-star"></i>
                </button>
                <a href="?refresh=1" class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1" title="<?= te('Daten aktualisieren') ?>">
                    <i class="bi bi-arrow-clockwise"></i>
                </a>
                <button onclick="window.print()" class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1" title="<?= te('Seite drucken / als PDF speichern') ?>">
                    <i class="bi bi-printer"></i>
                </button>
                <?php
                $_notifUnread = 0;
                $_notifRecent = [];
                try {
                    $_notifUnread = \App\Modules\Notifications\NotificationService::unreadCount();
                    $_notifRecent = \App\Modules\Notifications\NotificationService::recent(10);
                } catch (\Throwable) {}
                ?>
                <div style="position:relative;">
                    <button id="notifyTrigger" class="notify-trigger" type="button" title="<?= te('Benachrichtigungen') ?>" aria-label="<?= te('Benachrichtigungen') ?>">
                        <i class="bi bi-bell" style="font-size:18px;"></i>
                        <?php if ($_notifUnread > 0): ?>
                            <span class="notify-badge"><?= $_notifUnread > 99 ? '99+' : (int)$_notifUnread ?></span>
                        <?php endif; ?>
                    </button>
                    <div id="notifyPanel" class="notify-panel">
                        <div class="notify-panel-head">
                            <strong><i class="bi bi-bell-fill"></i> <?= te('Benachrichtigungen') ?></strong>
                            <a href="/notifications"><?= te('Alle anzeigen') ?></a>
                        </div>
                        <?php if (empty($_notifRecent)): ?>
                            <div class="notify-empty"><i class="bi bi-inbox" style="font-size:32px;display:block;margin-bottom:8px;"></i><?= te('Keine Ereignisse') ?></div>
                        <?php else: ?>
                            <?php foreach ($_notifRecent as $n): ?>
                                <?php
                                $icon = match ($n['severity']) {
                                    'critical' => 'exclamation-triangle-fill',
                                    'warn'     => 'exclamation-circle',
                                    'success'  => 'check-circle',
                                    default    => 'info-circle',
                                };
                                $ts = strtotime((string)$n['created_at']) ?: time();
                                $age = time() - $ts;
                                if ($age < 60)        $ago = t('gerade eben');
                                elseif ($age < 3600)  $ago = floor($age / 60) . ' Min.';
                                elseif ($age < 86400) $ago = floor($age / 3600) . ' Std.';
                                else                  $ago = floor($age / 86400) . ' Tg.';
                                $clickAttr = !empty($n['link']) ? ('onclick="window.location=\'' . htmlspecialchars($n['link'], ENT_QUOTES) . '\'"') : '';
                                ?>
                                <div class="notify-item severity-<?= htmlspecialchars($n['severity']) ?>" <?= $clickAttr ?>>
                                    <div class="notify-item-icon"><i class="bi bi-<?= $icon ?>"></i></div>
                                    <div class="notify-item-body">
                                        <div class="notify-item-title"><?= htmlspecialchars($n['title']) ?></div>
                                        <?php if (!empty($n['body'])): ?>
                                            <div class="notify-item-text"><?= htmlspecialchars($n['body']) ?></div>
                                        <?php endif; ?>
                                        <div class="notify-item-time"><?= htmlspecialchars($ago) ?> · <?= htmlspecialchars($n['category']) ?></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2 ms-2 ps-2" style="border-left: 1px solid #e5e7eb;">
                    <?php if (\App\Core\Session::get('auth_type') === 'microsoft'): ?>
                        <i class="bi bi-microsoft" style="color:#0078d4;"></i>
                    <?php else: ?>
                        <i class="bi bi-person-circle text-secondary"></i>
                    <?php endif; ?>
                    <span style="font-size:13px;font-weight:500;"><?= \App\Core\View::escape(\App\Auth\LocalAuth::username()) ?></span>
                    <?php if (\App\Auth\LocalAuth::role() === 'operator'): ?>
                        <span class="badge-warning" style="font-size:10px;"><?= te('Operator') ?></span>
                    <?php endif; ?>
                </div>
            </div>
        </header>

        <!-- Hub tab bar (modules of the active hub) -->
        <?php require BASE_PATH . '/views/layout/hub_tabs.php'; ?>

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
    <div class="qs-palette" role="dialog" aria-label="<?= te('Schnellsuche') ?>">
        <div class="qs-input-wrap">
            <i class="bi bi-search qs-input-icon"></i>
            <input type="text" id="qsInput" class="qs-input" placeholder="<?= te('Seite oder Einstellung suchen…') ?>" autocomplete="off" spellcheck="false">
            <kbd class="qs-esc-hint">Esc</kbd>
        </div>
        <div class="qs-results" id="qsResults" role="listbox"></div>
    </div>
</div>

<div class="toast-container"></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="/public/js/app.js?v=<?= @filemtime(BASE_PATH . '/public/js/app.js') ?: '1' ?>"></script>
</body>
</html>
