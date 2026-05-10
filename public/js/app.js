// ── Page loading bar ─────────────────────────────────────────
(function () {
    const loader = document.createElement('div');
    loader.id = 'page-loader';
    document.body.prepend(loader);

    // Show on navigation
    document.addEventListener('click', function (e) {
        const a = e.target.closest('a[href]');
        if (!a) return;
        const href = a.getAttribute('href');
        if (!href || href.startsWith('#') || href.startsWith('javascript') ||
            a.target === '_blank' || e.ctrlKey || e.metaKey) return;
        loader.classList.add('loading');
    });

    // Show on form submit
    document.addEventListener('submit', () => loader.classList.add('loading'));

    // Hide when page is ready
    window.addEventListener('pageshow', () => loader.classList.remove('loading'));
})();

// ── Sidebar toggle ────────────────────────────────────────────
(function () {
    const sidebar = document.getElementById('sidebar');
    const main    = document.getElementById('mainContent');
    const btn     = document.getElementById('sidebarToggle');
    const key     = 'm365_sidebar_collapsed';

    function applyState(collapsed) {
        sidebar?.classList.toggle('collapsed', collapsed);
        main?.classList.toggle('collapsed', collapsed);
        try { localStorage.setItem(key, collapsed ? '1' : '0'); } catch (_) {}
    }

    btn?.addEventListener('click', () => applyState(!sidebar.classList.contains('collapsed')));

    try {
        if (localStorage.getItem(key) === '1') applyState(true);
    } catch (_) {}
})();

// ── Last refresh badge ────────────────────────────────────────
(function () {
    const badge = document.getElementById('lastRefresh');
    if (!badge) return;
    const ts = badge.dataset.ts ? parseInt(badge.dataset.ts) : Date.now();
    function fmt() {
        const diff = Math.floor((Date.now() - ts) / 60000);
        badge.textContent = diff < 1 ? 'Gerade aktualisiert' :
            diff === 1 ? 'Vor 1 Min.' : `Vor ${diff} Min.`;
    }
    fmt();
    setInterval(fmt, 60000);
})();

// ── Client-side table search ──────────────────────────────────
function initTableSearch(inputId, tableId) {
    const input = document.getElementById(inputId);
    const table = document.getElementById(tableId);
    if (!input || !table) return;

    input.addEventListener('input', function () {
        const term = this.value.toLowerCase();
        let visible = 0;
        table.querySelectorAll('tbody tr[data-searchable!="false"]').forEach(row => {
            const match = row.textContent.toLowerCase().includes(term);
            row.style.display = match ? '' : 'none';
            if (match) visible++;
        });
        // Show/hide empty state
        let empty = table.querySelector('tr.empty-search');
        if (visible === 0 && term) {
            if (!empty) {
                empty = document.createElement('tr');
                empty.className = 'empty-search';
                const cols = table.querySelector('thead tr')?.children.length || 5;
                empty.innerHTML = `<td colspan="${cols}" class="text-center text-muted py-4">
                    <i class="bi bi-search me-2"></i>Keine Ergebnisse für „${term}"</td>`;
                table.querySelector('tbody')?.appendChild(empty);
            }
        } else {
            empty?.remove();
        }
    });
}

// ── Format bytes ──────────────────────────────────────────────
function formatBytes(bytes) {
    if (!bytes || bytes === 0) return '0 B';
    const k = 1024, sizes = ['B', 'KB', 'MB', 'GB', 'TB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
}

// ── Toast notification ────────────────────────────────────────
function showToast(message, type = 'success') {
    const container = document.querySelector('.toast-container');
    if (!container) return;
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-bg-${type} border-0 show mb-2`;
    toast.setAttribute('role', 'alert');
    toast.innerHTML = `<div class="d-flex">
        <div class="toast-body">${message}</div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto"
                onclick="this.closest('.toast').remove()"></button>
    </div>`;
    container.appendChild(toast);
    setTimeout(() => toast.style.opacity = '0', 3500);
    setTimeout(() => toast.remove(), 4000);
}

// ── Confirm-form helper ───────────────────────────────────────
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-confirm]').forEach(el => {
        el.addEventListener('click', function (e) {
            if (!confirm(this.dataset.confirm)) e.preventDefault();
        });
    });
});
