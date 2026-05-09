// Sidebar toggle
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

    if (btn) {
        btn.addEventListener('click', () => {
            const isCollapsed = sidebar.classList.contains('collapsed');
            applyState(!isCollapsed);
        });
    }

    // Restore saved state
    try {
        const saved = localStorage.getItem(key);
        if (saved === '1') applyState(true);
    } catch (_) {}
})();

// Active nav item
(function () {
    const path = window.location.pathname.split('/')[1] || '';
    document.querySelectorAll('.nav-item[data-route]').forEach(el => {
        if (el.dataset.route === path || (path === '' && el.dataset.route === '')) {
            el.classList.add('active');
        }
    });
})();

// Client-side table search
function initTableSearch(inputId, tableId) {
    const input = document.getElementById(inputId);
    const table = document.getElementById(tableId);
    if (!input || !table) return;

    input.addEventListener('input', function () {
        const term = this.value.toLowerCase();
        table.querySelectorAll('tbody tr').forEach(row => {
            row.style.display = row.textContent.toLowerCase().includes(term) ? '' : 'none';
        });
    });
}

// Format bytes
function formatBytes(bytes) {
    if (bytes === 0) return '0 B';
    const k = 1024, sizes = ['B', 'KB', 'MB', 'GB', 'TB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
}

// Toast notification
function showToast(message, type = 'success') {
    const container = document.querySelector('.toast-container') || (() => {
        const c = document.createElement('div');
        c.className = 'toast-container';
        document.body.appendChild(c);
        return c;
    })();

    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-bg-${type} border-0 show`;
    toast.setAttribute('role', 'alert');
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">${message}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" onclick="this.closest('.toast').remove()"></button>
        </div>`;
    container.appendChild(toast);
    setTimeout(() => toast.remove(), 4000);
}
