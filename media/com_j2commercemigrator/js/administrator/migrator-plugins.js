/**
 * J2Commerce Migrator — Dashboard plugin grid
 *
 * Renders the adapter card grid on the Dashboard view and wires
 * publish / unpublish toggles through com_plugins AJAX endpoints.
 *
 * Per-adapter debounce timers prevent rapid double-clicks from
 * firing duplicate toggle requests.
 *
 * Exposes window.J2cmPlugins for the core wizard to call.
 *
 * @copyright (C)2024-2026 J2Commerce, LLC
 * @license GNU General Public License version 2 or later
 */

'use strict';

((document, window) => {
    // Per-adapter debounce timers keyed by plugin element_id
    const debounceTimers = {};
    const DEBOUNCE_MS    = 400;

    // ===== Public API =====

    window.J2cmPlugins = {
        /**
         * Initialise the plugin grid — call once on DOMContentLoaded.
         * Reads adapter card data from Joomla.getOptions('com_j2commercemigrator.adapters').
         */
        init() {
            const adapters = Joomla.getOptions('com_j2commercemigrator.adapters', []);
            renderGrid(adapters);
            initSearch();
        },

        /**
         * Refresh the grid state from the server (e.g. after a page event
         * updates plugin enabled states outside this module).
         */
        async refresh() {
            const { apiFetch } = window.J2cmCore;

            try {
                const data = await apiFetch('plugins.list');
                updateGridState(data.adapters ?? []);
            } catch {
                // Refresh failure is non-fatal — grid state may be stale
            }
        },
    };

    // ===== Grid rendering =====

    function renderGrid(adapters) {
        const grid = document.getElementById('j2cm-plugins-grid');
        if (!grid) return;

        if (adapters.length === 0) {
            grid.innerHTML = `
            <div class="col-12 text-center text-muted py-5">
                <span class="fa-solid fa-plug fa-3x mb-3 d-block" aria-hidden="true"></span>
                <p>${esc(Joomla.Text._('COM_J2COMMERCEMIGRATOR_PLUGINS_NONE_INSTALLED'))}</p>
            </div>`;
            return;
        }

        grid.innerHTML = adapters.map((adapter) => renderCard(adapter)).join('');

        // Attach toggle handlers via delegation after render
        grid.querySelectorAll('[data-j2cm-toggle]').forEach((btn) => {
            btn.addEventListener('click', onToggleClick);
        });
    }

    function renderCard(adapter) {
        const enabled    = !!adapter.enabled;
        const elementId  = adapter.element ?? adapter.key ?? '';
        const pluginId   = adapter.id ?? 0;
        const title      = adapter.title ?? adapter.key ?? '';
        const description = adapter.description ?? '';
        const icon       = adapter.icon ?? 'fa-solid fa-plug';
        const author     = adapter.author ?? '';
        const version    = adapter.version ?? '';

        const toggleLabel = enabled
            ? Joomla.Text._('COM_J2COMMERCEMIGRATOR_PLUGINS_DISABLE')
            : Joomla.Text._('COM_J2COMMERCEMIGRATOR_PLUGINS_ENABLE');

        const toggleIcon  = enabled ? 'fa-toggle-on text-success' : 'fa-toggle-off text-muted';
        const badgeCls    = enabled ? 'text-bg-success' : 'text-bg-secondary';
        const badgeLabel  = enabled
            ? Joomla.Text._('COM_J2COMMERCEMIGRATOR_PLUGINS_STATUS_ENABLED')
            : Joomla.Text._('COM_J2COMMERCEMIGRATOR_PLUGINS_STATUS_DISABLED');

        return `
        <div class="col-sm-6 col-lg-4 col-xl-3"
             id="j2cm-plugin-card-${esc(elementId)}"
             data-plugin-element="${esc(elementId)}">
            <div class="card h-100 j2cm-adapter-card ${enabled ? 'border-success' : ''}">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between mb-2">
                        <span class="${esc(icon)} fa-2x text-secondary" aria-hidden="true"></span>
                        <span class="badge ${badgeCls} ms-2">${esc(badgeLabel)}</span>
                    </div>
                    <h5 class="card-title h6 mb-1">${esc(title)}</h5>
                    ${description ? `<p class="card-text text-muted small mb-2">${esc(description)}</p>` : ''}
                    <div class="text-muted small">
                        ${author ? `<div><span class="fa-solid fa-user me-1" aria-hidden="true"></span>${esc(author)}</div>` : ''}
                        ${version ? `<div><span class="fa-solid fa-tag me-1" aria-hidden="true"></span>v${esc(version)}</div>` : ''}
                    </div>
                </div>
                <div class="card-footer bg-transparent d-flex gap-2">
                    <button type="button"
                            class="btn btn-sm ${enabled ? 'btn-outline-warning' : 'btn-outline-success'} flex-grow-1"
                            data-j2cm-toggle="${esc(elementId)}"
                            data-plugin-id="${esc(String(pluginId))}"
                            data-plugin-enabled="${esc(enabled ? '1' : '0')}"
                            aria-label="${esc(toggleLabel)}: ${esc(title)}"
                            aria-pressed="${esc(enabled ? 'true' : 'false')}">
                        <span class="fa-solid ${toggleIcon} me-1" aria-hidden="true"></span>
                        ${esc(toggleLabel)}
                    </button>
                    ${pluginId ? `
                    <a href="${esc(buildEditUrl(pluginId))}"
                       class="btn btn-sm btn-outline-secondary"
                       aria-label="${esc(Joomla.Text._('COM_J2COMMERCEMIGRATOR_PLUGINS_CONFIGURE'))}: ${esc(title)}">
                        <span class="fa-solid fa-gear" aria-hidden="true"></span>
                        <span class="visually-hidden">${esc(Joomla.Text._('COM_J2COMMERCEMIGRATOR_PLUGINS_CONFIGURE'))}</span>
                    </a>` : ''}
                </div>
            </div>
        </div>`;
    }

    // ===== Toggle handler =====

    function onToggleClick(e) {
        const btn       = e.currentTarget;
        const elementId = btn.dataset.j2cmToggle;

        // Debounce per adapter to prevent rapid double-clicks
        clearTimeout(debounceTimers[elementId]);
        debounceTimers[elementId] = setTimeout(() => execToggle(btn, elementId), DEBOUNCE_MS);
    }

    async function execToggle(btn, elementId) {
        const pluginId    = parseInt(btn.dataset.pluginId ?? '0', 10);
        const currentlyOn = btn.dataset.pluginEnabled === '1';
        const newState    = !currentlyOn;

        if (!pluginId) return;

        // Optimistic UI update
        setCardEnabled(elementId, newState);
        btn.disabled = true;

        const { apiFetch } = window.J2cmCore;

        try {
            await apiFetch('plugins.toggle', {
                plugin_id: String(pluginId),
                enabled:   newState ? '1' : '0',
            });

            // Persist the new state on the button
            btn.dataset.pluginEnabled = newState ? '1' : '0';
        } catch (err) {
            // Revert on failure
            setCardEnabled(elementId, currentlyOn);
            btn.dataset.pluginEnabled = currentlyOn ? '1' : '0';

            const statusEl = document.getElementById('j2cm-plugins-status');
            if (statusEl) {
                statusEl.textContent = err.message;
                statusEl.classList.remove('d-none');
            }
        }

        btn.disabled = false;
    }

    // ===== Grid state update =====

    function updateGridState(adapters) {
        adapters.forEach((adapter) => {
            const elementId = adapter.element ?? adapter.key ?? '';
            if (elementId) {
                setCardEnabled(elementId, !!adapter.enabled);
            }
        });
    }

    function setCardEnabled(elementId, enabled) {
        const card   = document.getElementById(`j2cm-plugin-card-${elementId}`);
        const inner  = card?.querySelector('.j2cm-adapter-card');
        const btn    = card?.querySelector('[data-j2cm-toggle]');
        const badge  = card?.querySelector('.badge');
        const icon   = btn?.querySelector('.fa-solid');

        if (inner) {
            inner.classList.toggle('border-success', enabled);
        }

        if (badge) {
            badge.className = `badge ms-2 ${enabled ? 'text-bg-success' : 'text-bg-secondary'}`;
            badge.textContent = enabled
                ? Joomla.Text._('COM_J2COMMERCEMIGRATOR_PLUGINS_STATUS_ENABLED')
                : Joomla.Text._('COM_J2COMMERCEMIGRATOR_PLUGINS_STATUS_DISABLED');
        }

        if (btn) {
            btn.className = `btn btn-sm ${enabled ? 'btn-outline-warning' : 'btn-outline-success'} flex-grow-1`;
            btn.setAttribute('aria-pressed', enabled ? 'true' : 'false');
            btn.dataset.pluginEnabled = enabled ? '1' : '0';

            const label = enabled
                ? Joomla.Text._('COM_J2COMMERCEMIGRATOR_PLUGINS_DISABLE')
                : Joomla.Text._('COM_J2COMMERCEMIGRATOR_PLUGINS_ENABLE');

            if (icon) {
                icon.className = `fa-solid ${enabled ? 'fa-toggle-on text-success' : 'fa-toggle-off text-muted'} me-1`;
            }

            // Update text node (last child of button)
            const textNode = [...btn.childNodes].find((n) => n.nodeType === Node.TEXT_NODE);
            if (textNode) {
                textNode.textContent = ' ' + label;
            }
        }
    }

    // ===== Search/filter =====

    function initSearch() {
        const input = document.getElementById('j2cm-plugins-search');
        if (!input) return;

        let searchTimer;
        input.addEventListener('input', () => {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(() => filterCards(input.value.toLowerCase()), 200);
        });
    }

    function filterCards(query) {
        const cards = document.querySelectorAll('#j2cm-plugins-grid [data-plugin-element]');
        cards.forEach((card) => {
            const title = (card.querySelector('.card-title')?.textContent ?? '').toLowerCase();
            const desc  = (card.querySelector('.card-text')?.textContent ?? '').toLowerCase();
            const match = !query || title.includes(query) || desc.includes(query);
            card.classList.toggle('d-none', !match);
        });
    }

    // ===== Utility =====

    function buildEditUrl(pluginId) {
        return `index.php?option=com_plugins&task=plugin.edit&extension_id=${encodeURIComponent(String(pluginId))}`;
    }

    function esc(str) {
        const d = document.createElement('div');
        d.textContent = String(str ?? '');
        return d.innerHTML;
    }

    // ===== Auto-init =====

    document.addEventListener('DOMContentLoaded', () => {
        if (document.getElementById('j2cm-plugins-grid')) {
            window.J2cmPlugins.init();
        }
    });

})(document, window);
