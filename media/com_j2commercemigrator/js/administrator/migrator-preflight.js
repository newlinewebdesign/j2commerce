/**
 * J2Commerce Migrator — Preflight wizard step
 *
 * Fetches prerequisite-check results from the API, renders pass/warn/fail
 * badges for each check, and controls the "Next: Plan" button gate.
 *
 * Exposes window.J2cmPreflight for the core wizard to call.
 *
 * @copyright (C)2024-2026 J2Commerce, LLC
 * @license GNU General Public License version 2 or later
 */

'use strict';

((document, window) => {
    // Status → icon class mapping
    const ICON_MAP = {
        pass: 'fa-circle-check text-success',
        warn: 'fa-triangle-exclamation text-warning',
        fail: 'fa-circle-xmark text-danger',
        skip: 'fa-minus text-muted',
    };

    // Status → badge class mapping
    const BADGE_MAP = {
        pass: 'text-bg-success',
        warn: 'text-bg-warning',
        fail: 'text-bg-danger',
        skip: 'text-bg-secondary',
    };

    // ===== Public API =====

    window.J2cmPreflight = {
        async run() {
            const { apiFetch, hideSkeleton, showBtn, hideBtn, setAlert, state } = window.J2cmCore;

            // Show skeleton, hide content while loading
            document.getElementById('j2cm-preflight-skeleton')?.classList.remove('d-none');
            document.getElementById('j2cm-preflight-checks')?.classList.add('d-none');
            hideBtn('j2cm-btn-next-plan');

            try {
                const data = await apiFetch('migrate.preflight', { adapter: state.adapterKey });
                hideSkeleton('j2cm-preflight-skeleton', 'j2cm-preflight-checks');

                const checks = data.checks ?? [];
                renderChecks(checks);

                const hasError = checks.some((c) => c.status === 'fail');

                if (hasError) {
                    setAlert(
                        'j2cm-preflight-status',
                        'j2cm-preflight-alert',
                        'danger',
                        Joomla.Text._('COM_J2COMMERCEMIGRATOR_PREFLIGHT_BLOCKED'),
                    );
                    hideBtn('j2cm-btn-next-plan');
                } else {
                    const hasWarn = checks.some((c) => c.status === 'warn');
                    if (hasWarn) {
                        setAlert(
                            'j2cm-preflight-status',
                            'j2cm-preflight-alert',
                            'warning',
                            Joomla.Text._('COM_J2COMMERCEMIGRATOR_PREFLIGHT_WARNINGS'),
                        );
                    } else {
                        document.getElementById('j2cm-preflight-status')?.classList.add('d-none');
                    }
                    showBtn('j2cm-btn-next-plan');
                }

                updateSummary(checks);
            } catch (err) {
                hideSkeleton('j2cm-preflight-skeleton', 'j2cm-preflight-checks');
                setAlert('j2cm-preflight-status', 'j2cm-preflight-alert', 'danger', err.message);
                hideBtn('j2cm-btn-next-plan');
            }
        },
    };

    // ===== Rendering =====

    function renderChecks(checks) {
        const container = document.getElementById('j2cm-preflight-checks');
        if (!container) return;

        if (checks.length === 0) {
            container.innerHTML = `<p class="text-muted">${esc(Joomla.Text._('COM_J2COMMERCEMIGRATOR_PREFLIGHT_NO_CHECKS'))}</p>`;
            return;
        }

        container.innerHTML = checks.map((c) => {
            const status  = c.status ?? 'skip';
            const iconCls = ICON_MAP[status] ?? 'fa-circle text-secondary';
            const badge   = BADGE_MAP[status] ?? 'text-bg-secondary';
            const label   = c.label ?? '';
            const detail  = c.detail ?? '';
            const table   = c.table ?? '';

            return `
            <div class="d-flex align-items-start gap-2 mb-2 p-2 border rounded-2 j2cm-check-item j2cm-check-${esc(status)}"
                 role="listitem"
                 aria-label="${esc(label)}: ${esc(status)}">
                <span class="fa-solid ${iconCls} mt-1 flex-shrink-0" aria-hidden="true"></span>
                <div class="flex-grow-1">
                    <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap">
                        <span class="fw-semibold small">${esc(label)}</span>
                        ${table ? `<span class="font-monospace text-muted small">${esc(table)}</span>` : ''}
                        <span class="badge ${badge} ms-auto">${esc(status.toUpperCase())}</span>
                    </div>
                    ${detail ? `<div class="text-muted small mt-1">${esc(detail)}</div>` : ''}
                    ${renderActions(c)}
                </div>
            </div>`;
        }).join('');

        // Attach resolution action handlers after rendering
        container.querySelectorAll('[data-j2cm-action]').forEach((btn) => {
            btn.addEventListener('click', onResolutionClick);
        });
    }

    function renderActions(check) {
        if (check.status !== 'fail' || !Array.isArray(check.actions) || check.actions.length === 0) {
            return '';
        }

        const buttons = check.actions.map((action) => `
            <button type="button"
                    class="btn btn-sm btn-outline-secondary"
                    data-j2cm-action="${esc(action.key)}"
                    data-j2cm-check="${esc(check.id ?? '')}">
                ${esc(action.label)}
            </button>`).join('');

        return `<div class="mt-1 d-flex gap-1 flex-wrap">${buttons}</div>`;
    }

    function updateSummary(checks) {
        const pass  = checks.filter((c) => c.status === 'pass').length;
        const warn  = checks.filter((c) => c.status === 'warn').length;
        const fail  = checks.filter((c) => c.status === 'fail').length;
        const total = checks.length;

        setText('j2cm-preflight-count-total', total.toString());
        setText('j2cm-preflight-count-pass',  pass.toString());
        setText('j2cm-preflight-count-warn',  warn.toString());
        setText('j2cm-preflight-count-fail',  fail.toString());
    }

    // ===== Resolution action handler =====

    async function onResolutionClick(e) {
        const btn       = e.currentTarget;
        const action    = btn.dataset.j2cmAction;
        const checkId   = btn.dataset.j2cmCheck;
        const { apiFetch, state } = window.J2cmCore;

        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>';

        try {
            await apiFetch('migrate.preflightResolve', {
                adapter:  state.adapterKey,
                check_id: checkId,
                action,
            });
            // Re-run preflight to refresh the check list
            window.J2cmPreflight.run();
        } catch (err) {
            btn.disabled = false;
            btn.textContent = action;
            // Restore button text from original action label (best effort)
            const container = btn.closest('[data-j2cm-check]');
            if (container) {
                setAlert('j2cm-preflight-status', 'j2cm-preflight-alert', 'danger', err.message);
            }
        }
    }

    // ===== Utilities =====

    function esc(str) {
        const d = document.createElement('div');
        d.textContent = String(str ?? '');
        return d.innerHTML;
    }

    function setText(id, value) {
        const el = document.getElementById(id);
        if (el) el.textContent = value;
    }

})(document, window);
