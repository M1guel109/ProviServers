(function () {
    const BASE = typeof BASE_URL !== 'undefined' ? BASE_URL : '';

    let savedConfig = [];

    // ── Utilidades ──────────────────────────────────────────────────────
    function debounce(fn, delay) {
        let t;
        return function (...args) {
            clearTimeout(t);
            t = setTimeout(() => fn.apply(this, args), delay);
        };
    }

    function widgetLabel(widgetId) {
        const el = document.querySelector(`[data-widget-id="${widgetId}"]`);
        return el ? (el.dataset.label || widgetId) : widgetId;
    }

    // ── Panel de widgets ocultos ─────────────────────────────────────────
    function addToHiddenPanel(widgetId) {
        const panel = document.getElementById('hidden-widgets-panel');
        const container = document.getElementById('hidden-widgets-container');
        if (!panel || !container) return;
        if (panel.querySelector(`[data-show="${widgetId}"]`)) return;

        const btn = document.createElement('button');
        btn.className = 'btn btn-sm btn-outline-secondary me-1 mb-1';
        btn.dataset.show = widgetId;
        btn.innerHTML = `<i class="bi bi-eye me-1"></i>${widgetLabel(widgetId)}`;
        btn.addEventListener('click', () => showWidget(widgetId));
        panel.appendChild(btn);
        container.classList.remove('d-none');
    }

    function removeFromHiddenPanel(widgetId) {
        const btn = document.querySelector(`#hidden-widgets-panel [data-show="${widgetId}"]`);
        if (btn) btn.remove();
        const panel = document.getElementById('hidden-widgets-panel');
        if (panel && panel.childElementCount === 0) {
            document.getElementById('hidden-widgets-container')?.classList.add('d-none');
        }
    }

    // ── Aplicar configuración guardada ───────────────────────────────────
    function applyConfig(config) {
        const grid = document.getElementById('dashboard-grid');
        if (!grid) return;

        const sorted = [...config].sort((a, b) => a.posicion - b.posicion);
        sorted.forEach(item => {
            const el = grid.querySelector(`[data-widget-id="${item.widget_id}"]`);
            if (!el) return;
            if (!item.visible) {
                el.style.display = 'none';
                addToHiddenPanel(item.widget_id);
            } else {
                el.style.display = '';
                grid.appendChild(el);
            }
        });
    }

    // ── Guardar configuración ────────────────────────────────────────────
    function buildConfig() {
        const grid = document.getElementById('dashboard-grid');
        if (!grid) return savedConfig;

        const visible = [...grid.querySelectorAll('.widget-card')]
            .map((el, i) => ({
                widget_id: el.dataset.widgetId,
                posicion: i,
                visible: el.style.display !== 'none',
            }));

        // Incluir los ocultos que no están en el grid visible
        savedConfig.forEach(item => {
            if (!visible.find(c => c.widget_id === item.widget_id)) {
                visible.push({ widget_id: item.widget_id, posicion: 99, visible: false });
            }
        });

        return visible;
    }

    const saveConfig = debounce(function () {
        const config = buildConfig();
        savedConfig = config;
        fetch(BASE + '/dashboard/config/guardar', {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ config }),
        });
    }, 500);

    // ── Ocultar / mostrar widget ─────────────────────────────────────────
    function hideWidget(widgetId) {
        const el = document.querySelector(`[data-widget-id="${widgetId}"]`);
        if (!el) return;
        el.style.transition = 'opacity 0.25s';
        el.style.opacity = '0';
        setTimeout(() => {
            el.style.display = 'none';
            el.style.opacity = '';
            addToHiddenPanel(widgetId);
            saveConfig();
        }, 260);
    }

    function showWidget(widgetId) {
        const el = document.querySelector(`[data-widget-id="${widgetId}"]`);
        if (!el) return;
        el.style.display = '';
        removeFromHiddenPanel(widgetId);
        saveConfig();
    }

    // ── Inicializar SortableJS ───────────────────────────────────────────
    function initSortable() {
        const grid = document.getElementById('dashboard-grid');
        if (!grid || typeof Sortable === 'undefined') return;
        Sortable.create(grid, {
            handle: '.widget-handle',
            animation: 150,
            onEnd: saveConfig,
        });
    }

    // ── Cargar configuración del backend ─────────────────────────────────
    async function loadConfig() {
        try {
            const res = await fetch(BASE + '/dashboard/config', { credentials: 'same-origin' });
            const data = await res.json();
            if (data.ok && Array.isArray(data.config)) {
                savedConfig = data.config;
                applyConfig(data.config);
            }
        } catch (_) { /* usa el orden HTML por defecto */ }
        initSortable();
    }

    // ── Event listeners globales ─────────────────────────────────────────
    document.addEventListener('click', function (e) {
        const hideBtn = e.target.closest('.btn-ocultar-widget');
        if (hideBtn) {
            hideWidget(hideBtn.dataset.id);
            return;
        }

        const restaurarBtn = e.target.closest('#btn-restaurar-dashboard');
        if (restaurarBtn) {
            if (!confirm('¿Restaurar la configuración por defecto del dashboard?')) return;
            fetch(BASE + '/dashboard/config/restaurar', { method: 'POST', credentials: 'same-origin' })
                .then(() => location.reload());
        }
    });

    document.addEventListener('DOMContentLoaded', loadConfig);
})();
