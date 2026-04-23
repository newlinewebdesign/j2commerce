/**
 * J2Commerce Migrator — Core wizard controller
 *
 * Manages step navigation, skeleton loaders, and shared state.
 *
 * @copyright (C)2024-2026 J2Commerce, LLC
 * @license GNU General Public License version 2 or later
 */

'use strict';

((document) => {
    // ===== State =====
    const state = {
        currentStep: 'connect',
        adapterKey:  '',
        token:       '',
        apiUrl:      '',
        conflictMode: 'skip',
        batchSize:    500,
    };

    const STEPS = ['connect', 'discover', 'preflight', 'plan', 'run', 'verify', 'finalize'];

    // ===== Init =====
    document.addEventListener('DOMContentLoaded', () => {
        const config = Joomla.getOptions('com_j2commercemigrator.config', {});
        state.token      = config.token      ?? '';
        state.adapterKey = config.adapterKey ?? '';
        state.apiUrl     = config.apiUrl     ?? '';

        if (!document.querySelector('.j2cm-wizard')) {
            return;
        }

        activateStep('connect');
        initStepClickHandlers();
    });

    // ===== Step Navigation =====

    function activateStep(step) {
        state.currentStep = step;

        // Show / hide sections
        STEPS.forEach((s) => {
            const section = document.getElementById(`j2cm-step-${s}`);
            if (section) {
                section.classList.toggle('d-none', s !== step);
            }
        });

        // Update breadcrumb
        const items = document.querySelectorAll('.j2cm-wizard-steps .breadcrumb-item');
        const stepIdx = STEPS.indexOf(step);
        items.forEach((item, idx) => {
            item.classList.remove('active', 'complete');
            if (idx < stepIdx) {
                item.classList.add('complete');
            } else if (idx === stepIdx) {
                item.classList.add('active');
            }
        });
    }

    function initStepClickHandlers() {
        // Connect → Discover
        on('#j2cm-btn-next-discover', 'click', () => {
            activateStep('discover');
            window.J2cmDiscover?.run();
        });

        // Discover → back to connect
        on('#j2cm-btn-back-connect', 'click', () => activateStep('connect'));

        // Discover → Preflight
        on('#j2cm-btn-next-preflight', 'click', () => {
            activateStep('preflight');
            window.J2cmPreflight?.run();
        });

        // Preflight → back to discover
        on('#j2cm-btn-back-discover', 'click', () => activateStep('discover'));

        // Preflight → Plan
        on('#j2cm-btn-next-plan', 'click', () => {
            activateStep('plan');
            window.J2cmPlan?.render();
        });

        // Plan → back to preflight
        on('#j2cm-btn-back-preflight', 'click', () => activateStep('preflight'));

        // Plan → Run
        on('#j2cm-btn-start-run', 'click', () => {
            state.conflictMode = document.getElementById('j2cm-conflict-mode')?.value ?? 'skip';
            state.batchSize    = parseInt(document.getElementById('j2cm-batch-size')?.value ?? '500', 10);
            activateStep('run');
            window.J2cmRun?.start(state);
        });

        // Run → Verify
        on('#j2cm-btn-next-verify', 'click', () => {
            activateStep('verify');
            window.J2cmVerify?.run();
        });

        // Verify → Finalize
        on('#j2cm-btn-next-finalize', 'click', () => {
            activateStep('finalize');
            window.J2cmFinalize?.render();
        });

        // Discover refresh
        on('#j2cm-btn-refresh-discover', 'click', () => window.J2cmDiscover?.run());
    }

    // ===== API Helper =====

    async function apiFetch(action, payload = {}) {
        const body = new URLSearchParams({ action, ...payload });
        body.append(state.token, '1');

        const response = await fetch(state.apiUrl, {
            method:  'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body,
        });

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }

        const json = await response.json();

        if (!json.success) {
            throw new Error(json.error ?? json.message ?? 'Unknown API error');
        }

        return json.data ?? json;
    }

    // ===== Utility =====

    function on(selector, event, handler) {
        const el = document.querySelector(selector);
        if (el) {
            el.addEventListener(event, handler);
        }
    }

    function showBtn(id) {
        document.getElementById(id)?.classList.remove('d-none');
    }

    function hideBtn(id) {
        document.getElementById(id)?.classList.add('d-none');
    }

    function setAlert(wrapperId, alertId, type, message) {
        const wrap  = document.getElementById(wrapperId);
        const alert = document.getElementById(alertId);
        if (!wrap || !alert) return;
        alert.className = `alert alert-${type}`;
        alert.textContent = message;
        wrap.classList.remove('d-none');
    }

    function hideSkeleton(skeletonId, contentId) {
        document.getElementById(skeletonId)?.classList.add('d-none');
        document.getElementById(contentId)?.classList.remove('d-none');
    }

    // ===== Expose shared helpers =====
    window.J2cmCore = { apiFetch, showBtn, hideBtn, setAlert, hideSkeleton, state, activateStep };

})(document);
