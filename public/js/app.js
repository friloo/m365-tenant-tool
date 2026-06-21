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

    if (!sidebar) return;

    // Single backdrop element, reused across opens/closes.
    const backdrop = document.createElement('div');
    backdrop.className = 'sidebar-backdrop';
    backdrop.setAttribute('aria-hidden', 'true');
    document.body.appendChild(backdrop);

    const mqPhone  = window.matchMedia('(max-width: 768px)');
    const mqTablet = window.matchMedia('(max-width: 1024px) and (min-width: 769px)');

    function applyDesktopState(collapsed) {
        sidebar.classList.toggle('collapsed', collapsed);
        main?.classList.toggle('collapsed', collapsed);
        try { localStorage.setItem(key, collapsed ? '1' : '0'); } catch (_) {}
    }

    function openMobile() {
        sidebar.classList.add('mobile-open');
        backdrop.classList.add('show');
        document.body.style.overflow = 'hidden';     // prevent body scroll behind drawer
    }
    function closeMobile() {
        sidebar.classList.remove('mobile-open');
        backdrop.classList.remove('show');
        document.body.style.overflow = '';
    }

    function toggle() {
        if (mqPhone.matches) {
            sidebar.classList.contains('mobile-open') ? closeMobile() : openMobile();
        } else if (mqTablet.matches) {
            // On tablet the Burger toggles the inline "expanded" full-width state
            sidebar.classList.toggle('expanded');
        } else {
            applyDesktopState(!sidebar.classList.contains('collapsed'));
        }
    }

    btn?.addEventListener('click', toggle);
    backdrop.addEventListener('click', closeMobile);

    // Close drawer when the user navigates by tapping a sidebar link.
    sidebar.addEventListener('click', (e) => {
        if (mqPhone.matches && e.target.closest('a[href]')) closeMobile();
    });

    // Escape closes the mobile drawer.
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && mqPhone.matches && sidebar.classList.contains('mobile-open')) {
            closeMobile();
        }
    });

    // When crossing breakpoints we don't want a stuck open/closed state.
    function syncOnResize() {
        if (!mqPhone.matches) {
            sidebar.classList.remove('mobile-open');
            backdrop.classList.remove('show');
            document.body.style.overflow = '';
        }
        if (!mqTablet.matches && !mqPhone.matches) {
            sidebar.classList.remove('expanded');
        }
    }
    mqPhone.addEventListener('change', syncOnResize);
    mqTablet.addEventListener('change', syncOnResize);

    // Persisted desktop preference (collapsed sidebar)
    try {
        if (!mqPhone.matches && !mqTablet.matches && localStorage.getItem(key) === '1') {
            applyDesktopState(true);
        }
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
        { label: 'Einstellungen: E-Mail & SMTP',         icon: 'envelope',       url: '/settings#email',            cat: 'Einstellungen' },
        { label: 'Einstellungen: Freigaben-Monitor',     icon: 'eye-slash',      url: '/settings#share-review',     cat: 'Einstellungen' },
        { label: 'Einstellungen: Inaktive Konten',       icon: 'person-x',       url: '/settings#stale-accounts',   cat: 'Einstellungen' },
        { label: 'Einstellungen: Passwort-Ablauf',       icon: 'key',            url: '/settings#password-expiry',  cat: 'Einstellungen' },
        { label: 'Einstellungen: Wöchentlicher Report',  icon: 'envelope-paper', url: '/settings#weekly-report',    cat: 'Einstellungen' },
        { label: 'Einstellungen: Lizenz-Kriterien',      icon: 'lightbulb',      url: '/settings#license-criteria', cat: 'Einstellungen' },
        { label: 'Einstellungen: Branding',              icon: 'palette',        url: '/settings#branding',         cat: 'Einstellungen' },
        { label: 'Einstellungen: Berechtigungen prüfen', icon: 'shield-check',   url: '/settings/permissions',      cat: 'Einstellungen' },
        { label: 'Updates',                              icon: 'cloud-arrow-down', url: '/settings/update',           cat: 'Administration' },
        { label: 'Handbuch',                             icon: 'book',             url: '/manual',                    cat: 'Administration' },
    ];

    const overlay  = document.getElementById('qsOverlay');
    const input    = document.getElementById('qsInput');
    const results  = document.getElementById('qsResults');
    const trigger  = document.getElementById('qsTrigger');
    if (!overlay || !input || !results) return;

    let activeIdx = -1;

    let apiDebounceTimer = null;
    let lastApiResults   = [];

    function open() {
        overlay.classList.add('open');
        overlay.removeAttribute('aria-hidden');
        input.value = '';
        activeIdx = -1;
        lastApiResults = [];
        render('', []);
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

    // Badge colours per API result type
    const TYPE_COLORS = {
        user:   '#0078d4',  // blue
        group:  '#7c3aed',  // purple
        device: '#059669',  // green
    };

    function renderApiItem(item, idx) {
        const isDisabled = item.type === 'user' && item.enabled === false;
        const color      = TYPE_COLORS[item.type] || '#6b7280';
        const labelText  = isDisabled ? item.label + ' <span style="color:#9ca3af;font-weight:400;">(Deaktiviert)</span>' : item.label;
        const itemStyle  = isDisabled ? ' style="opacity:.55;"' : '';
        const iconStyle  = `background:${color}1a;color:${color};`;
        const subtitle   = item.subtitle
            ? `<span class="qs-subtitle" style="font-size:11px;color:#6b7280;display:block;line-height:1.3;">${item.subtitle}</span>`
            : '';
        return `<a href="${item.url}" class="qs-item" data-idx="${idx}" role="option"${itemStyle}>
                <span class="qs-icon" style="${iconStyle}"><i class="bi bi-${item.icon}"></i></span>
                <span class="qs-label" style="display:flex;flex-direction:column;gap:1px;">${labelText}${subtitle}</span>
            </a>`;
    }

    function render(term, apiItems) {
        const trimmed = term.trim();
        const filtered = trimmed
            ? ITEMS.filter(i => score(i, trimmed) > 0)
                   .sort((a, b) => score(b, trimmed) - score(a, trimmed))
            : ITEMS;

        activeIdx = -1;

        const hasApi  = apiItems && apiItems.length > 0;
        const hasNav  = filtered.length > 0;

        if (!hasApi && !hasNav) {
            results.innerHTML = `<div class="qs-empty"><i class="bi bi-search me-2"></i>Kein Ergebnis für „${trimmed}"</div>`;
            return;
        }

        let html  = '';
        let idx   = 0;

        // ── API results (users, groups, devices) ──────────────────
        if (hasApi) {
            html += `<div class="qs-category">Benutzer &amp; Objekte</div>`;
            apiItems.forEach(item => {
                html += renderApiItem(item, idx++);
            });
        }

        // ── Navigation items ──────────────────────────────────────
        if (hasNav) {
            html += `<div class="qs-category">Navigation</div>`;
            filtered.forEach(item => {
                const lbl = highlight(item.label, trimmed);
                html += `<a href="${item.url}" class="qs-item" data-idx="${idx}" role="option">
                    <span class="qs-icon"><i class="bi bi-${item.icon}"></i></span>
                    <span class="qs-label">${lbl}</span>
                </a>`;
                idx++;
            });
        }

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

    input.addEventListener('input', () => {
        const term = input.value;
        clearTimeout(apiDebounceTimer);

        if (term.trim().length < 2) {
            lastApiResults = [];
            render(term, []);
            return;
        }

        // Immediate render with cached API results while waiting for new ones
        render(term, lastApiResults);

        apiDebounceTimer = setTimeout(() => {
            fetch('/api/search?q=' + encodeURIComponent(term.trim()), { headers: { 'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content ?? '' } })
                .then(r => r.ok ? r.json() : { results: [] })
                .then(data => {
                    lastApiResults = data.results || [];
                    // Only update if the term hasn't changed
                    if (input.value === term) {
                        render(term, lastApiResults);
                    }
                })
                .catch(() => {});
        }, 300);
    });

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

// ── Saved Filters (localStorage per page) ─────────────────────────────────
(function () {
    const pageKey = 'filter_' + location.pathname.replace(/\//g, '_');

    function saveFilters() {
        const state = {};
        document.querySelectorAll('[data-save-filter]').forEach(el => {
            state[el.dataset.saveFilter] = el.tagName === 'INPUT' ? el.value : el.value;
        });
        if (Object.keys(state).length) {
            localStorage.setItem(pageKey, JSON.stringify(state));
        }
    }

    function restoreFilters() {
        try {
            const state = JSON.parse(localStorage.getItem(pageKey) || '{}');
            Object.entries(state).forEach(([key, val]) => {
                const el = document.querySelector('[data-save-filter="' + key + '"]');
                if (el && val !== undefined) {
                    el.value = val;
                    el.dispatchEvent(new Event('change', { bubbles: true }));
                }
            });
        } catch {}
    }

    document.addEventListener('DOMContentLoaded', () => {
        restoreFilters();
        document.querySelectorAll('[data-save-filter]').forEach(el => {
            el.addEventListener('change', saveFilters);
        });
    });
})();

// ── PDF / Print export ─────────────────────────────────────────────────────
function printPage() {
    window.print();
}

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-pdf-export]').forEach(btn => {
        btn.addEventListener('click', () => window.print());
    });
});

// ── Help-tooltip initializer ───────────────────────────────────────────────
// Wires Bootstrap tooltips on every .help-tip element. Bootstrap's bundle
// is loaded via CDN above; this guards in case it's blocked offline.
document.addEventListener('DOMContentLoaded', function () {
    if (typeof bootstrap === 'undefined' || !bootstrap.Tooltip) return;
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (el) {
        try { new bootstrap.Tooltip(el, { container: 'body' }); } catch (_) {}
    });
});

// ── Notification bell ──────────────────────────────────────────────────────
(function () {
    document.addEventListener('DOMContentLoaded', function () {
        const trigger = document.getElementById('notifyTrigger');
        const panel   = document.getElementById('notifyPanel');
        if (!trigger || !panel) return;

        trigger.addEventListener('click', function (e) {
            e.stopPropagation();
            const wasOpen = panel.classList.contains('open');
            panel.classList.toggle('open');
            if (!wasOpen) {
                fetch('/notifications/mark-seen', { method: 'POST',
                    headers: { 'X-CSRF': document.querySelector('meta[name="csrf-token"]')?.content || '' }
                }).then(function () {
                    const badge = trigger.querySelector('.notify-badge');
                    if (badge) badge.remove();
                }).catch(function () {});
            }
        });

        document.addEventListener('click', function (e) {
            if (!panel.contains(e.target) && !trigger.contains(e.target)) {
                panel.classList.remove('open');
            }
        });
    });
})();

// ── Sparkline renderer ─────────────────────────────────────────────────────
// Looks for <canvas class="sparkline-canvas" data-values="1,2,3,4,5"></canvas>
// and draws a simple line. No dependencies — keeps Chart.js bundle out of
// the per-cell render path.
(function () {
    function drawSparkline(canvas) {
        const raw = (canvas.dataset.values || '').split(',').map(parseFloat).filter(v => !isNaN(v));
        if (raw.length < 2) return;
        const w = canvas.width = canvas.offsetWidth * 2;
        const h = canvas.height = canvas.offsetHeight * 2;
        const ctx = canvas.getContext('2d');
        const min = Math.min.apply(null, raw);
        const max = Math.max.apply(null, raw);
        const range = (max - min) || 1;
        const dx = w / (raw.length - 1);
        const padY = h * 0.1;
        const usable = h - padY * 2;

        ctx.clearRect(0, 0, w, h);
        ctx.lineWidth = 3;
        const color = canvas.dataset.color || '#0078d4';
        ctx.strokeStyle = color;
        ctx.fillStyle = color + '22';
        ctx.beginPath();
        raw.forEach((v, i) => {
            const x = i * dx;
            const y = h - padY - ((v - min) / range) * usable;
            i === 0 ? ctx.moveTo(x, y) : ctx.lineTo(x, y);
        });
        ctx.stroke();

        ctx.lineTo(w, h);
        ctx.lineTo(0, h);
        ctx.closePath();
        ctx.fill();
    }
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('canvas.sparkline-canvas').forEach(drawSparkline);
    });
})();

// Copy-to-clipboard for code/PowerShell snippets (.ps-snippet > .js-copy).
(function () {
    function fallbackCopy(text, done) {
        const ta = document.createElement('textarea');
        ta.value = text;
        ta.style.position = 'fixed';
        ta.style.opacity = '0';
        document.body.appendChild(ta);
        ta.select();
        try { document.execCommand('copy'); done(); } catch (e) { /* ignore */ }
        document.body.removeChild(ta);
    }
    document.addEventListener('click', function (ev) {
        const btn = ev.target.closest('.js-copy');
        if (!btn) return;
        const snippet = btn.closest('.ps-snippet');
        const code = snippet && snippet.querySelector('code');
        if (!code) return;
        const text = code.textContent;
        const done = function () {
            const orig = btn.innerHTML;
            btn.innerHTML = '<i class="bi bi-check2 me-1"></i>Kopiert!';
            setTimeout(function () { btn.innerHTML = orig; }, 1500);
        };
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text).then(done).catch(function () { fallbackCopy(text, done); });
        } else {
            fallbackCopy(text, done);
        }
    });
})();

// ── Hub tab bar: priority+ overflow with a "Mehr" dropdown ───────────
(function () {
    function layout(nav) {
        const list     = nav.querySelector('.hub-tabs-list');
        const moreWrap = nav.querySelector('.hub-tabs-more');
        const menu     = nav.querySelector('.hub-more-menu');
        const btn      = nav.querySelector('.hub-more-btn');
        if (!list || !moreWrap || !menu) return;

        // Restore every tab into the list in its original order.
        Array.prototype.slice.call(menu.querySelectorAll('.hub-tab')).forEach(function (a) { list.appendChild(a); });
        let tabs = Array.prototype.slice.call(list.querySelectorAll('.hub-tab'));
        tabs.sort(function (a, b) { return (+a.dataset.order) - (+b.dataset.order); });
        tabs.forEach(function (a) { list.appendChild(a); });
        moreWrap.style.display = 'none';
        menu.classList.remove('open');
        if (btn) btn.classList.remove('has-active');

        // Advanced (niche / beta) modules are demoted into the "Mehr" menu by
        // default — regardless of width — to keep the primary tab row focused.
        // The currently active tab is never hidden.
        const advanced = tabs.filter(function (t) {
            return t.dataset.advanced === '1' && !t.classList.contains('active');
        });
        if (advanced.length) {
            moreWrap.style.display = '';
            advanced.forEach(function (t) { menu.appendChild(t); });
            tabs = tabs.filter(function (t) { return advanced.indexOf(t) === -1; });
        }

        let total = 0;
        tabs.forEach(function (t) { total += t.offsetWidth; });
        if (total <= nav.clientWidth + 1) return; // remaining tabs all fit

        moreWrap.style.display = '';
        const budget = nav.clientWidth - moreWrap.offsetWidth;
        let used = 0, visibleCount = 0;
        for (let i = 0; i < tabs.length; i++) {
            const w = tabs[i].offsetWidth;
            if (used + w <= budget) { used += w; visibleCount++; } else break;
        }
        if (visibleCount < 1) visibleCount = 1;

        let visible  = tabs.slice(0, visibleCount);
        let overflow = tabs.slice(visibleCount);

        // Keep the active tab visible even if it would overflow.
        const activeIdx = tabs.findIndex(function (t) { return t.classList.contains('active'); });
        if (activeIdx >= visibleCount) {
            const active = tabs[activeIdx];
            overflow = overflow.filter(function (t) { return t !== active; });
            const dropped = visible.pop();
            if (dropped) overflow.unshift(dropped);
            visible.push(active);
        }

        visible.forEach(function (t) { list.appendChild(t); });
        overflow.forEach(function (t) { menu.appendChild(t); });
        if (btn) btn.classList.toggle('has-active', !!menu.querySelector('.hub-tab.active'));
    }

    function init() {
        const nav = document.querySelector('.hub-tabs');
        if (!nav) return;
        const btn  = nav.querySelector('.hub-more-btn');
        const menu = nav.querySelector('.hub-more-menu');
        if (btn && menu) {
            btn.addEventListener('click', function (e) { e.stopPropagation(); menu.classList.toggle('open'); });
            document.addEventListener('click', function (e) { if (!nav.contains(e.target)) menu.classList.remove('open'); });
            document.addEventListener('keydown', function (e) { if (e.key === 'Escape') menu.classList.remove('open'); });
        }
        let raf;
        const relayout = function () { cancelAnimationFrame(raf); raf = requestAnimationFrame(function () { layout(nav); }); };
        layout(nav);
        window.addEventListener('resize', relayout);
        window.addEventListener('load', relayout); // re-measure once icon font is ready
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init); else init();
})();

// ── Favorites (client-side, localStorage) ────────────────────────────
(function () {
    const KEY = 'm365_favorites';
    function read()  { try { return JSON.parse(localStorage.getItem(KEY) || '[]'); } catch (e) { return []; } }
    function write(list) { try { localStorage.setItem(KEY, JSON.stringify(list)); } catch (e) {} }
    function curPath() { return (location.pathname || '/').replace(/\/+$/, '') || '/'; }
    function curTitle() {
        const el = document.querySelector('.page-title');
        return el ? el.textContent.trim() : (document.title || curPath());
    }

    const toggle = document.getElementById('favToggle');
    function refreshStar() {
        if (!toggle) return;
        const has  = read().some(function (f) { return f.path === curPath(); });
        const icon = toggle.querySelector('i');
        if (icon) icon.className = has ? 'bi bi-star-fill' : 'bi bi-star';
        toggle.classList.toggle('is-fav', has);
        toggle.title = has ? 'Aus Favoriten entfernen' : 'Zu Favoriten hinzufügen';
    }
    if (toggle) {
        toggle.addEventListener('click', function () {
            const p = curPath();
            let list = read();
            if (list.some(function (f) { return f.path === p; })) {
                list = list.filter(function (f) { return f.path !== p; });
            } else {
                list.push({ path: p, title: curTitle() });
            }
            write(list);
            refreshStar();
            renderFavs();
        });
        refreshStar();
    }

    function renderFavs() {
        const grid = document.getElementById('favGrid');
        if (!grid) return;
        const empty = document.getElementById('favEmpty');
        const list  = read();
        grid.innerHTML = '';
        if (!list.length) { if (empty) empty.style.display = ''; return; }
        if (empty) empty.style.display = 'none';
        list.forEach(function (f) {
            const col = document.createElement('div');
            col.className = 'col-sm-6 col-lg-4';
            const a = document.createElement('a');
            a.className = 'fav-card';
            a.href = f.path;
            const ico = document.createElement('span');
            ico.className = 'fav-card-icon';
            ico.innerHTML = '<i class="bi bi-star-fill"></i>';
            const lbl = document.createElement('span');
            lbl.className = 'fav-card-label';
            lbl.textContent = f.title || f.path; // textContent → no XSS
            const rm = document.createElement('button');
            rm.type = 'button';
            rm.className = 'fav-remove';
            rm.title = 'Aus Favoriten entfernen';
            rm.innerHTML = '<i class="bi bi-x-lg"></i>';
            rm.addEventListener('click', function (ev) {
                ev.preventDefault(); ev.stopPropagation();
                write(read().filter(function (x) { return x.path !== f.path; }));
                renderFavs();
                refreshStar();
            });
            a.appendChild(ico); a.appendChild(lbl); a.appendChild(rm);
            col.appendChild(a); grid.appendChild(col);
        });
    }
    renderFavs();
})();
