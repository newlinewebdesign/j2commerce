/**
 * @package     J2Commerce
 * @subpackage  com_j2commerce
 *
 * @copyright   (C)2024-2026 J2Commerce, LLC <https://www.j2commerce.com>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

'use strict';

document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('order-form');
    if (!form) return;

    // Tab navigation helpers for prev/next buttons within tab panels
    document.addEventListener('click', (e) => {
        const navBtn = e.target.closest('[data-j2c-tab-target]');
        if (!navBtn) return;

        e.preventDefault();
        const targetId = navBtn.dataset.j2cTabTarget;
        const tabTrigger = document.querySelector(`button[data-bs-target="#${targetId}"], a[data-bs-target="#${targetId}"]`);

        if (tabTrigger && typeof bootstrap !== 'undefined') {
            const tab = bootstrap.Tab.getOrCreateInstance(tabTrigger);
            tab.show();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    });

    // Highlight invalid fields on failed validation
    form.addEventListener('invalid', (e) => {
        const field = e.target;
        if (!field) return;

        field.classList.add('is-invalid');

        // Find and activate the tab containing the invalid field
        const tabPane = field.closest('.tab-pane');
        if (tabPane) {
            const tabTrigger = document.querySelector(`button[data-bs-target="#${tabPane.id}"], a[data-bs-target="#${tabPane.id}"]`);
            if (tabTrigger && typeof bootstrap !== 'undefined') {
                const tab = bootstrap.Tab.getOrCreateInstance(tabTrigger);
                tab.show();
            }
        }
    }, true);

    // Clear invalid styling on input
    form.addEventListener('input', (e) => {
        e.target.classList.remove('is-invalid');
    });
});
