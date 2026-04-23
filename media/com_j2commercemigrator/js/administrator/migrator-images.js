/**
 * J2Commerce Migrator — Image pipeline step
 *
 * Handles image discovery (source path scan), copy progress, and rebuild
 * pipeline UI with per-batch debounced status updates.
 *
 * Exposes window.J2cmImages for the core wizard to call.
 *
 * @copyright (C)2024-2026 J2Commerce, LLC
 * @license GNU General Public License version 2 or later
 */

'use strict';

((document, window) => {
    // Phase IDs in logical order
    const PHASES = ['discover', 'copy', 'rebuild'];

    // Per-item debounce timers keyed by [phase]-[id]
    const debounceTimers = {};

    let abortController = null;

    // ===== Public API =====

    window.J2cmImages = {
        async run() {
            const { apiFetch, setAlert, state } = window.J2cmCore;

            resetUI();
            abortController = new AbortController();

            try {
                // 1. Discover image layout from adapter
                setPhase('discover', 'running');
                const discoverData = await apiFetch('images.discover', { adapter: state.adapterKey });
                setPhase('discover', 'done');
                renderDiscovery(discoverData);

                if (!discoverData.has_images) {
                    setAlert('j2cm-images-status', 'j2cm-images-alert', 'info',
                        Joomla.Text._('COM_J2COMMERCEMIGRATOR_IMAGES_NONE_FOUND'));
                    return;
                }

                // 2. Copy source images to target directory
                await runCopyPhase(state);

                // 3. Rebuild image manifests (thumbnails, paths)
                await runRebuildPhase(state);

                setAlert('j2cm-images-status', 'j2cm-images-alert', 'success',
                    Joomla.Text._('COM_J2COMMERCEMIGRATOR_IMAGES_COMPLETE'));
            } catch (err) {
                if (err.name === 'AbortError') return;
                setAlert('j2cm-images-status', 'j2cm-images-alert', 'danger', err.message);
            }
        },

        cancel() {
            abortController?.abort();
            PHASES.forEach((p) => setPhase(p, 'cancelled'));
        },
    };

    // ===== Phase runners =====

    async function runCopyPhase(state) {
        const { apiFetch } = window.J2cmCore;

        setPhase('copy', 'running');
        updateProgress('copy', 0, 1);

        let offset = 0;
        let total  = null;

        while (true) {
            if (abortController?.signal.aborted) break;

            const result = await apiFetch('images.copy', {
                adapter: state.adapterKey,
                offset,
                batch_size: 50,
            });

            total  = total ?? (result.total ?? 0);
            offset += result.copied ?? 0;

            updateProgress('copy', offset, total);
            debouncedLog('copy', offset, `Copied ${offset} / ${total} images`);

            if (result.done || offset >= total) break;
        }

        setPhase('copy', abortController?.signal.aborted ? 'cancelled' : 'done');
    }

    async function runRebuildPhase(state) {
        const { apiFetch } = window.J2cmCore;

        setPhase('rebuild', 'running');
        updateProgress('rebuild', 0, 1);

        const categoriesResult = await apiFetch('images.rebuildList', { adapter: state.adapterKey });
        const categories       = categoriesResult.categories ?? [];
        const total            = categories.length;

        for (let i = 0; i < categories.length; i++) {
            if (abortController?.signal.aborted) break;

            const category = categories[i];

            await apiFetch('images.rebuildCategory', {
                adapter:  state.adapterKey,
                category,
            });

            updateProgress('rebuild', i + 1, total);
            debouncedLog('rebuild', i, `Rebuilt category: ${category} (${i + 1}/${total})`);
        }

        setPhase('rebuild', abortController?.signal.aborted ? 'cancelled' : 'done');
    }

    // ===== UI helpers =====

    function resetUI() {
        PHASES.forEach((p) => setPhase(p, 'pending'));
        PHASES.forEach((p) => updateProgress(p, 0, 0));
        document.getElementById('j2cm-images-log')?.replaceChildren();
        document.getElementById('j2cm-images-status')?.classList.add('d-none');
        document.getElementById('j2cm-images-discovery')?.classList.add('d-none');
    }

    function renderDiscovery(data) {
        const section = document.getElementById('j2cm-images-discovery');
        if (!section) return;

        const sourceRoot = data.source_root ?? '';
        const dirs       = data.sub_directories ?? [];
        const pathCols   = data.path_columns ?? {};
        const total      = data.total_estimated ?? 0;

        const colRows = Object.entries(pathCols).map(([table, cols]) =>
            `<tr><td class="font-monospace small">${esc(table)}</td><td class="small text-muted">${esc(cols.join(', '))}</td></tr>`
        ).join('');

        section.innerHTML = `
            <div class="card mb-3">
                <div class="card-header fw-semibold">${esc(Joomla.Text._('COM_J2COMMERCEMIGRATOR_IMAGES_DISCOVERY_TITLE'))}</div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-3">${esc(Joomla.Text._('COM_J2COMMERCEMIGRATOR_IMAGES_SOURCE_ROOT'))}</dt>
                        <dd class="col-sm-9 font-monospace small">${esc(sourceRoot)}</dd>
                        <dt class="col-sm-3">${esc(Joomla.Text._('COM_J2COMMERCEMIGRATOR_IMAGES_SUBDIRS'))}</dt>
                        <dd class="col-sm-9 small">${esc(dirs.join(', '))}</dd>
                        <dt class="col-sm-3">${esc(Joomla.Text._('COM_J2COMMERCEMIGRATOR_IMAGES_ESTIMATED'))}</dt>
                        <dd class="col-sm-9">${Number(total).toLocaleString()}</dd>
                    </dl>
                    ${colRows ? `<table class="table table-sm mt-3 mb-0"><thead><tr><th>Table</th><th>Path Columns</th></tr></thead><tbody>${colRows}</tbody></table>` : ''}
                </div>
            </div>`;

        section.classList.remove('d-none');
    }

    function setPhase(phase, status) {
        const el = document.getElementById(`j2cm-images-phase-${phase}`);
        if (!el) return;

        el.classList.remove('pending', 'running', 'done', 'failed', 'cancelled');
        el.classList.add(status);

        const iconEl = el.querySelector('.j2cm-phase-icon');
        if (iconEl) {
            iconEl.className = 'j2cm-phase-icon fa-solid ' + phaseIcon(status);
        }

        const statusEl = el.querySelector('.j2cm-phase-status');
        if (statusEl) statusEl.textContent = status.charAt(0).toUpperCase() + status.slice(1);
    }

    function phaseIcon(status) {
        return {
            pending:   'fa-clock text-muted',
            running:   'fa-spinner fa-spin text-info',
            done:      'fa-circle-check text-success',
            failed:    'fa-circle-xmark text-danger',
            cancelled: 'fa-ban text-warning',
        }[status] ?? 'fa-clock text-muted';
    }

    function updateProgress(phase, done, total) {
        const bar  = document.getElementById(`j2cm-images-${phase}-bar`);
        const pct  = total > 0 ? Math.min(100, Math.round((done / total) * 100)) : 0;

        if (bar) {
            bar.style.width = `${pct}%`;
            bar.setAttribute('aria-valuenow', String(done));
            bar.setAttribute('aria-valuemax', String(total));
            bar.textContent = `${pct}%`;
        }

        const label = document.getElementById(`j2cm-images-${phase}-label`);
        if (label && total > 0) {
            label.textContent = `${done.toLocaleString()} / ${total.toLocaleString()}`;
        }
    }

    function debouncedLog(phase, id, message) {
        const key = `${phase}-${id}`;
        clearTimeout(debounceTimers[key]);
        debounceTimers[key] = setTimeout(() => appendLog(message), 80);
    }

    function appendLog(message) {
        const log = document.getElementById('j2cm-images-log');
        if (!log) return;

        const li = document.createElement('li');
        li.className = 'list-group-item list-group-item-action py-1 small font-monospace';
        li.textContent = message;
        log.prepend(li);

        // Keep log bounded to 200 entries
        while (log.children.length > 200) {
            log.lastElementChild?.remove();
        }
    }

    // ===== Cancel button =====

    document.addEventListener('DOMContentLoaded', () => {
        document.addEventListener('click', (e) => {
            if (e.target.closest('#j2cm-btn-cancel-images')) {
                window.J2cmImages.cancel();
            }
        });
    });

    // ===== Utility =====

    function esc(str) {
        const d = document.createElement('div');
        d.textContent = String(str ?? '');
        return d.innerHTML;
    }

})(document, window);
