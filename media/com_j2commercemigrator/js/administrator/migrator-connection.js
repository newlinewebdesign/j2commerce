/**
 * J2Commerce Migrator — Connection step logic
 *
 * Handles mode A/B/C toggle, verify/clear connection, and advancing to Discover.
 *
 * @copyright (C)2024-2026 J2Commerce, LLC
 * @license GNU General Public License version 2 or later
 */

'use strict';

((document) => {
    document.addEventListener('DOMContentLoaded', () => {
        if (!document.getElementById('j2cm-connection-form')) {
            return;
        }

        initModeToggle();
        initSslToggle();
        initVerifyButton();
        initClearButton();
    });

    // ===== Mode toggle: show/hide B/C fields =====

    function initModeToggle() {
        const radios = document.querySelectorAll('input[name="j2cm-mode"]');
        radios.forEach((radio) => {
            radio.addEventListener('change', onModeChange);
        });
        onModeChange(); // apply initial state
    }

    function onModeChange() {
        const selected = document.querySelector('input[name="j2cm-mode"]:checked')?.value ?? 'A';
        const bcFields = document.getElementById('j2cm-conn-fields-bc');
        if (bcFields) {
            bcFields.classList.toggle('d-none', selected === 'A');
        }
    }

    // ===== SSL toggle =====

    function initSslToggle() {
        const sslCheck = document.getElementById('j2cm-ssl');
        const caWrap   = document.getElementById('j2cm-ssl-ca-wrap');
        if (!sslCheck || !caWrap) return;

        sslCheck.addEventListener('change', () => {
            caWrap.classList.toggle('d-none', !sslCheck.checked);
        });
    }

    // ===== Verify connection =====

    function initVerifyButton() {
        const btn = document.getElementById('j2cm-btn-verify');
        if (!btn) return;

        btn.addEventListener('click', async () => {
            const { apiFetch, showBtn, hideBtn, setAlert, state } = window.J2cmCore;

            btn.disabled = true;
            btn.textContent = '';
            const spinner = document.createElement('span');
            spinner.className = 'spinner-border spinner-border-sm me-1';
            spinner.setAttribute('role', 'status');
            spinner.setAttribute('aria-hidden', 'true');
            btn.appendChild(spinner);
            btn.appendChild(document.createTextNode(' ' + Joomla.Text._('COM_J2COMMERCEMIGRATOR_CONNECTION_BTN_VERIFYING')));

            const mode     = document.querySelector('input[name="j2cm-mode"]:checked')?.value ?? 'A';
            const payload  = { adapter: state.adapterKey, mode };

            if (mode !== 'A') {
                payload.host     = document.getElementById('j2cm-host')?.value ?? '';
                payload.port     = document.getElementById('j2cm-port')?.value ?? '3306';
                payload.prefix   = document.getElementById('j2cm-prefix')?.value ?? 'jos_';
                payload.database = document.getElementById('j2cm-database')?.value ?? '';
                payload.username = document.getElementById('j2cm-username')?.value ?? '';
                payload.password = document.getElementById('j2cm-password')?.value ?? '';
                payload.ssl      = document.getElementById('j2cm-ssl')?.checked ? '1' : '0';
                payload.ssl_ca   = document.getElementById('j2cm-ssl-ca')?.value ?? '';
            }

            try {
                const data = await apiFetch('connection.verify', payload);

                // apiFetch throws on !success, so reaching here means ok:true.
                // data contains {mode, meta} from ConnectionManager::verify().
                const modeStr = data.mode ? ' (Mode ' + data.mode + ')' : '';
                const host    = data.meta?.host ?? '';
                const summary = host ? host + modeStr : modeStr.trimStart();

                setAlert(
                    'j2cm-conn-status',
                    'j2cm-conn-alert',
                    'success',
                    Joomla.Text._('COM_J2COMMERCEMIGRATOR_CONNECTION_BTN_VERIFY') + ': ' + (summary || 'OK')
                );
                showBtn('j2cm-btn-next-discover');
                showBtn('j2cm-btn-clear-conn');
            } catch (err) {
                setAlert('j2cm-conn-status', 'j2cm-conn-alert', 'danger', err.message);
                hideBtn('j2cm-btn-next-discover');
            }

            btn.disabled = false;
            btn.textContent = '';
            const icon = document.createElement('span');
            icon.className = 'fa-solid fa-plug me-1';
            icon.setAttribute('aria-hidden', 'true');
            btn.appendChild(icon);
            btn.appendChild(document.createTextNode(' ' + Joomla.Text._('COM_J2COMMERCEMIGRATOR_CONNECTION_BTN_VERIFY')));
        });
    }

    // ===== Clear connection =====

    function initClearButton() {
        const btn = document.getElementById('j2cm-btn-clear-conn');
        if (!btn) return;

        btn.addEventListener('click', async () => {
            const { apiFetch, hideBtn, state } = window.J2cmCore;

            try {
                await apiFetch('connection.clear', { adapter: state.adapterKey });
            } catch {
                // Non-fatal; clear UI regardless
            }

            document.getElementById('j2cm-conn-status')?.classList.add('d-none');
            hideBtn('j2cm-btn-next-discover');
            hideBtn('j2cm-btn-clear-conn');
        });
    }

})(document);
