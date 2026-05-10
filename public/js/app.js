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

// ── Quick search / Command palette ────────────────────────────
(function () {
    const ITEMS = [
        // Übersicht
        { label: 'Dashboard',               icon: 'speedometer2',        url: '/',                     cat: 'Übersicht' },
        // Verzeichnis
        { label: 'Benutzer',                icon: 'people',              url: '/users',                cat: 'Verzeichnis' },
        { label: 'Gastbenutzer',            icon: 'person-badge',        url: '/guestusers',           cat: 'Verzeichnis' },
        { label: 'Gruppen & Teams',         icon: 'diagram-3',           url: '/groups',               cat: 'Verzeichnis' },
        { label: 'Inaktive Gruppen',        icon: 'diagram-3',           url: '/groups/inactive',      cat: 'Verzeichnis' },
        { label: 'Lizenzen',                icon: 'award',               url: '/licenses',             cat: 'Verzeichnis' },
        { label: 'Lizenz-Ablauf',           icon: 'calendar-x',          url: '/licenses/expiry',      cat: 'Verzeichnis' },
        { label: 'Lizenz-Berater',          icon: 'lightbulb',           url: '/licenseadvisor',       cat: 'Verzeichnis' },
        { label: 'MFA-Methoden',            icon: 'shield-lock',         url: '/mfamethods',           cat: 'Verzeichnis' },
        { label: 'Passwort-Ablauf',         icon: 'key',                 url: '/passwordexpiry',       cat: 'Verzeichnis' },
        // Speicher & Freigaben
        { label: 'OneDrive',                icon: 'cloud',               url: '/onedrive',             cat: 'Speicher & Freigaben' },
        { label: 'SharePoint',              icon: 'share',               url: '/sharepoint',           cat: 'Speicher & Freigaben' },
        { label: 'Freigaben',               icon: 'link-45deg',          url: '/sharing',              cat: 'Speicher & Freigaben' },
        { label: 'Freigaben-Monitor',       icon: 'eye-slash',           url: '/sharing/monitor',      cat: 'Speicher & Freigaben' },
        { label: 'Freigaberichtlinien',     icon: 'sliders',             url: '/sharing/policies',     cat: 'Speicher & Freigaben' },
        // Exchange & Kommunikation
        { label: 'Postfächer',              icon: 'envelope',            url: '/mailboxes',                     cat: 'Exchange & Kommunikation' },
        { label: 'Freigegebene Postfächer', icon: 'envelope-paper',      url: '/mailboxes/shared',              cat: 'Exchange & Kommunikation' },
        { label: 'Externe Weiterleitungen', icon: 'envelope-exclamation', url: '/mailboxes/external-forwards',  cat: 'Exchange & Kommunikation' },
        { label: 'Teams-Nutzung',           icon: 'camera-video',        url: '/teamsusage',                    cat: 'Exchange & Kommunikation' },
        { label: 'Adoptions-Report',        icon: 'graph-up-arrow',      url: '/adoption',                      cat: 'Exchange & Kommunikation' },
        { label: 'Message Center',          icon: 'megaphone',           url: '/msgcenter',                     cat: 'Exchange & Kommunikation' },
        { label: 'Mail Flow & Schutz',      icon: 'arrow-left-right',    url: '/mailflow',                      cat: 'Exchange & Kommunikation' },
        { label: 'Dienststatus',            icon: 'heart-pulse',         url: '/servicehealth',                 cat: 'Exchange & Kommunikation' },
        // Sicherheit
        { label: 'Sicherheit',              icon: 'shield-check',        url: '/security',             cat: 'Sicherheit' },
        { label: 'Security Posture',        icon: 'shield-fill-check',   url: '/securityposture',      cat: 'Sicherheit' },
        { label: 'Secure Score',            icon: 'bar-chart-line',      url: '/securescore',          cat: 'Sicherheit' },
        { label: 'Defender Alerts',         icon: 'bell',                url: '/defenderalerts',       cat: 'Sicherheit' },
        { label: 'Risiko-Anmeldungen',      icon: 'exclamation-triangle', url: '/riskysignins',        cat: 'Sicherheit' },
        { label: 'App-Registrierungen',     icon: 'grid-3x3-gap',        url: '/appregistrations',     cat: 'Sicherheit' },
        { label: 'Admin-Rollen',            icon: 'person-lock',         url: '/adminroles',           cat: 'Sicherheit' },
        // Compliance & Audit
        { label: 'Geräte',                  icon: 'phone',               url: '/devices',              cat: 'Compliance & Audit' },
        { label: 'Inaktive Konten',         icon: 'person-x',            url: '/staleaccounts',        cat: 'Compliance & Audit' },
        { label: 'Audit-Log',               icon: 'clock-history',       url: '/auditlog',             cat: 'Compliance & Audit' },
        { label: 'Sign-in-Log',             icon: 'journal-text',        url: '/signinlog',            cat: 'Compliance & Audit' },
        // Administration
        { label: 'Cron & Automatisierung',  icon: 'clock',               url: '/cron',                 cat: 'Administration' },
        { label: 'Einstellungen',           icon: 'gear',                url: '/settings',             cat: 'Administration' },
        // Settings deep links
        { label: 'Einstellungen: Allgemein',             icon: 'gear',           url: '/settings#general',          cat: 'Einstellungen' },
        { label: 'Einstellungen: Admin-Passwort',        icon: 'person-lock',    url: '/settings#admin-password',   cat: 'Einstellungen' },
        { label: 'Einstellungen: Operator-Konto',        icon: 'person-badge',   url: '/settings#operator',         cat: 'Einstellungen' },
        { label: 'Einstellungen: E-Mail & SMTP',         icon: 'envelope',       url: '/settings#email',            cat: 'Einstellungen' },
        { label: 'Einstellungen: Freigaben-Monitor',     icon: 'eye-slash',      url: '/settings#share-review',     cat: 'Einstellungen' },
        { label: 'Einstellungen: Inaktive Konten',       icon: 'person-x',       url: '/settings#stale-accounts',   cat: 'Einstellungen' },
        { label: 'Einstellungen: Passwort-Ablauf',       icon: 'key',            url: '/settings#password-expiry',  cat: 'Einstellungen' },
        { label: 'Einstellungen: Wöchentlicher Report',  icon: 'envelope-paper', url: '/settings#weekly-report',    cat: 'Einstellungen' },
        { label: 'Einstellungen: Lizenz-Kriterien',      icon: 'lightbulb',      url: '/settings#license-criteria', cat: 'Einstellungen' },
        { label: 'Einstellungen: Branding',              icon: 'palette',        url: '/settings#branding',         cat: 'Einstellungen' },
    ];

    const overlay  = document.getElementById('qsOverlay');
    const input    = document.getElementById('qsInput');
    const results  = document.getElementById('qsResults');
    const trigger  = document.getElementById('qsTrigger');
    if (!overlay || !input || !results) return;

    let activeIdx = -1;

    function open() {
        overlay.classList.add('open');
        overlay.removeAttribute('aria-hidden');
        input.value = '';
        activeIdx = -1;
        render('');
        requestAnimationFrame(() => input.focus());
    }

    function close() {
        overlay.classList.remove('open');
        overlay.setAttribute('aria-hidden', 'true');
    }

    function highlight(text, term) {
        if (!term) return text;
        const idx = text.toLowerCase().indexOf(term.toLowerCase());
        if (idx === -1) return text;
        return text.slice(0, idx) +
               '<mark>' + text.slice(idx, idx + term.length) + '</mark>' +
               text.slice(idx + term.length);
    }

    function score(item, term) {
        const lbl = item.label.toLowerCase();
        const t   = term.toLowerCase();
        if (lbl.startsWith(t)) return 3;
        if (lbl.includes(t))   return 2;
        if (item.cat.toLowerCase().includes(t)) return 1;
        return 0;
    }

    function render(term) {
        const trimmed = term.trim();
        const filtered = trimmed
            ? ITEMS.filter(i => score(i, trimmed) > 0)
                   .sort((a, b) => score(b, trimmed) - score(a, trimmed))
            : ITEMS;

        activeIdx = -1;

        if (filtered.length === 0) {
            results.innerHTML = `<div class="qs-empty"><i class="bi bi-search me-2"></i>Kein Ergebnis für „${trimmed}"</div>`;
            return;
        }

        let html = '';
        let lastCat = null;
        filtered.forEach((item, i) => {
            if (item.cat !== lastCat) {
                html += `<div class="qs-category">${item.cat}</div>`;
                lastCat = item.cat;
            }
            const lbl = highlight(item.label, trimmed);
            html += `<a href="${item.url}" class="qs-item" data-idx="${i}" role="option">
                <span class="qs-icon"><i class="bi bi-${item.icon}"></i></span>
                <span class="qs-label">${lbl}</span>
            </a>`;
        });
        results.innerHTML = html;
    }

    function getItems() {
        return results.querySelectorAll('.qs-item');
    }

    function setActive(idx) {
        const items = getItems();
        items.forEach(el => el.classList.remove('active'));
        if (idx >= 0 && idx < items.length) {
            items[idx].classList.add('active');
            items[idx].scrollIntoView({ block: 'nearest' });
            activeIdx = idx;
        } else {
            activeIdx = -1;
        }
    }

    // Events
    trigger?.addEventListener('click', open);

    document.addEventListener('keydown', e => {
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            overlay.classList.contains('open') ? close() : open();
        }
        if (!overlay.classList.contains('open')) return;

        if (e.key === 'Escape') { e.preventDefault(); close(); }
        if (e.key === 'ArrowDown') { e.preventDefault(); setActive(Math.min(activeIdx + 1, getItems().length - 1)); }
        if (e.key === 'ArrowUp')   { e.preventDefault(); setActive(Math.max(activeIdx - 1, 0)); }
        if (e.key === 'Enter') {
            e.preventDefault();
            const active = results.querySelector('.qs-item.active');
            if (active) { close(); window.location.href = active.href; }
        }
    });

    input.addEventListener('input', () => render(input.value));

    // Click on result
    results.addEventListener('click', e => {
        const item = e.target.closest('.qs-item');
        if (item) close();
    });

    // Click outside palette closes
    overlay.addEventListener('click', e => {
        if (e.target === overlay) close();
    });
})();
