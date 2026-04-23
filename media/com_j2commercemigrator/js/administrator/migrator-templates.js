/**
 * J2Commerce Migrator — Subtemplate discovery and migration UI
 *
 * Scans source J2Store subtemplate files, shows a diff-style comparison of
 * J2Store vs J2Commerce class/helper references, and fires per-file migration
 * with conflict resolution (auto / manual / skip).
 *
 * Exposes window.J2cmTemplates for the core wizard to call.
 *
 * @copyright (C)2024-2026 J2Commerce, LLC
 * @license GNU General Public License version 2 or later
 */

'use strict';

((document, window) => {
    // Per-file debounce timers keyed by file path hash
    const debounceTimers = {};

    // Resolution choice per file: 'auto' | 'manual' | 'skip'
    const resolutions = {};

    // ===== Public API =====

    window.J2cmTemplates = {
        async run() {
            const { apiFetch, setAlert, state } = window.J2cmCore;

            resetUI();

            try {
                const data = await apiFetch('templates.discover', { adapter: state.adapterKey });
                renderTemplateList(data.templates ?? []);

                if ((data.templates ?? []).length === 0) {
                    setAlert('j2cm-templates-status', 'j2cm-templates-alert', 'info',
                        Joomla.Text._('COM_J2COMMERCEMIGRATOR_TEMPLATES_NONE_FOUND'));
                }
            } catch (err) {
                setAlert('j2cm-templates-status', 'j2cm-templates-alert', 'danger', err.message);
            }
        },

        async migrateAll() {
            const files = [...document.querySelectorAll('.j2cm-template-item')];
            for (const item of files) {
                const filePath = item.dataset.filePath;
                if (!filePath || resolutions[filePath] === 'skip') continue;
                await migrateFile(filePath, resolutions[filePath] ?? 'auto');
            }

            const { setAlert } = window.J2cmCore;
            setAlert('j2cm-templates-status', 'j2cm-templates-alert', 'success',
                Joomla.Text._('COM_J2COMMERCEMIGRATOR_TEMPLATES_COMPLETE'));
        },
    };

    // ===== Rendering =====

    function renderTemplateList(templates) {
        const container = document.getElementById('j2cm-templates-list');
        if (!container) return;

        if (templates.length === 0) {
            container.innerHTML = '';
            return;
        }

        container.innerHTML = templates.map((tpl) => {
            const id      = hashPath(tpl.path);
            const changes = tpl.changes ?? 0;
            const status  = tpl.status ?? 'pending';

            return `
            <div class="j2cm-template-item card mb-2"
                 id="j2cm-tpl-${esc(id)}"
                 data-file-path="${esc(tpl.path)}">
                <div class="card-body py-2 px-3">
                    <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap">
                        <span class="font-monospace small fw-semibold">${esc(tpl.path)}</span>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge text-bg-secondary">${esc(String(changes))} ${esc(Joomla.Text._('COM_J2COMMERCEMIGRATOR_TEMPLATES_CHANGES'))}</span>
                            <span class="j2cm-tpl-status badge ${statusBadge(status)}">${esc(status)}</span>
                        </div>
                    </div>
                    <div class="d-flex gap-2 mt-2 flex-wrap align-items-center">
                        ${renderResolutionSelector(id, tpl.path)}
                        <button type="button"
                                class="btn btn-sm btn-outline-primary"
                                data-j2cm-tpl-preview="${esc(tpl.path)}">
                            <span class="fa-solid fa-eye me-1" aria-hidden="true"></span>
                            ${esc(Joomla.Text._('COM_J2COMMERCEMIGRATOR_TEMPLATES_PREVIEW'))}
                        </button>
                        <button type="button"
                                class="btn btn-sm btn-primary"
                                data-j2cm-tpl-migrate="${esc(tpl.path)}">
                            <span class="fa-solid fa-wand-magic-sparkles me-1" aria-hidden="true"></span>
                            ${esc(Joomla.Text._('COM_J2COMMERCEMIGRATOR_TEMPLATES_MIGRATE_ONE'))}
                        </button>
                    </div>
                </div>
                <div class="j2cm-tpl-preview d-none" id="j2cm-tpl-diff-${esc(id)}"></div>
            </div>`;
        }).join('');

        // Default resolutions
        templates.forEach((tpl) => {
            resolutions[tpl.path] = 'auto';
        });

        // Attach event handlers via delegation
        container.querySelectorAll('select[data-j2cm-resolution]').forEach((sel) => {
            sel.addEventListener('change', onResolutionChange);
        });
    }

    function renderResolutionSelector(id, filePath) {
        return `
        <label class="visually-hidden" for="j2cm-res-${esc(id)}">${esc(Joomla.Text._('COM_J2COMMERCEMIGRATOR_TEMPLATES_RESOLUTION'))}</label>
        <select id="j2cm-res-${esc(id)}"
                class="form-select form-select-sm w-auto"
                data-j2cm-resolution="${esc(filePath)}">
            <option value="auto">${esc(Joomla.Text._('COM_J2COMMERCEMIGRATOR_TEMPLATES_RES_AUTO'))}</option>
            <option value="manual">${esc(Joomla.Text._('COM_J2COMMERCEMIGRATOR_TEMPLATES_RES_MANUAL'))}</option>
            <option value="skip">${esc(Joomla.Text._('COM_J2COMMERCEMIGRATOR_TEMPLATES_RES_SKIP'))}</option>
        </select>`;
    }

    // ===== Event delegation =====

    document.addEventListener('click', async (e) => {
        const previewBtn = e.target.closest('[data-j2cm-tpl-preview]');
        if (previewBtn) {
            await onPreviewClick(previewBtn.dataset.j2cmTplPreview);
            return;
        }

        const migrateBtn = e.target.closest('[data-j2cm-tpl-migrate]');
        if (migrateBtn) {
            const filePath = migrateBtn.dataset.j2cmTplMigrate;
            await migrateFile(filePath, resolutions[filePath] ?? 'auto');
        }

        const migrateAllBtn = e.target.closest('#j2cm-btn-templates-migrate-all');
        if (migrateAllBtn) {
            await window.J2cmTemplates.migrateAll();
        }
    });

    // ===== Per-file actions =====

    async function onPreviewClick(filePath) {
        const { apiFetch, state } = window.J2cmCore;
        const id    = hashPath(filePath);
        const panel = document.getElementById(`j2cm-tpl-diff-${id}`);

        if (!panel) return;

        const isVisible = !panel.classList.contains('d-none');
        if (isVisible) {
            panel.classList.add('d-none');
            panel.innerHTML = '';
            return;
        }

        panel.classList.remove('d-none');
        panel.innerHTML = '<div class="p-3 text-muted small"><span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Loading…</div>';

        try {
            const data = await apiFetch('templates.preview', {
                adapter:   state.adapterKey,
                file_path: filePath,
            });
            renderDiff(panel, data.lines ?? []);
        } catch (err) {
            panel.innerHTML = `<div class="p-3 text-danger small">${esc(err.message)}</div>`;
        }
    }

    function renderDiff(panel, lines) {
        const html = lines.map((line) => {
            const cls = line.type === 'add' ? 'bg-success-subtle' :
                        line.type === 'remove' ? 'bg-danger-subtle' :
                        '';
            return `<div class="j2cm-diff-line ${cls} font-monospace small px-3 py-0">${esc(line.content)}</div>`;
        }).join('');

        panel.innerHTML = `<div class="j2cm-diff-view border-top" role="region" aria-label="Diff preview">${html}</div>`;
    }

    async function migrateFile(filePath, resolution) {
        const { apiFetch, state } = window.J2cmCore;
        const id   = hashPath(filePath);
        const item = document.getElementById(`j2cm-tpl-${id}`);

        setFileStatus(id, 'running');

        // Debounce status updates keyed by file path hash
        clearTimeout(debounceTimers[id]);

        try {
            await apiFetch('templates.migrate', {
                adapter:    state.adapterKey,
                file_path:  filePath,
                resolution,
            });

            debounceTimers[id] = setTimeout(() => setFileStatus(id, 'done'), 100);
        } catch (err) {
            debounceTimers[id] = setTimeout(() => {
                setFileStatus(id, 'failed');
                const panel = document.getElementById(`j2cm-tpl-diff-${id}`);
                if (panel) {
                    panel.classList.remove('d-none');
                    panel.innerHTML = `<div class="p-3 text-danger small">${esc(err.message)}</div>`;
                }
            }, 100);
        }
    }

    function onResolutionChange(e) {
        const sel      = e.currentTarget;
        const filePath = sel.dataset.j2cmResolution;
        if (filePath) {
            resolutions[filePath] = sel.value;
        }
    }

    // ===== UI helpers =====

    function resetUI() {
        const container = document.getElementById('j2cm-templates-list');
        if (container) container.innerHTML = '';
        document.getElementById('j2cm-templates-status')?.classList.add('d-none');
    }

    function setFileStatus(id, status) {
        const badge = document.querySelector(`#j2cm-tpl-${id} .j2cm-tpl-status`);
        if (!badge) return;

        badge.className = `j2cm-tpl-status badge ${statusBadge(status)}`;
        badge.textContent = status;
    }

    function statusBadge(status) {
        return {
            pending:   'text-bg-secondary',
            running:   'text-bg-info',
            done:      'text-bg-success',
            failed:    'text-bg-danger',
            skipped:   'text-bg-warning',
        }[status] ?? 'text-bg-secondary';
    }

    // Simple hash for turning a file path into a safe DOM ID fragment
    function hashPath(path) {
        return [...(path ?? '')].reduce((acc, c) => ((acc << 5) - acc + c.charCodeAt(0)) | 0, 0)
            .toString(36)
            .replace('-', 'n');
    }

    function esc(str) {
        const d = document.createElement('div');
        d.textContent = String(str ?? '');
        return d.innerHTML;
    }

})(document, window);
