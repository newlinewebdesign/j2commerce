/**
 * @package     J2Commerce
 * @subpackage  com_j2commerce
 *
 * @copyright   (C)2024-2026 J2Commerce, LLC <https://www.j2commerce.com>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
'use strict';

(function() {
    const builderTab = document.querySelector('button[data-bs-target="#overridesTabs-builder"]');
    if (!builderTab) return;

    let builderInitialized = false;

    builderTab.addEventListener('shown.bs.tab', function() {
        if (builderInitialized) return;
        builderInitialized = true;

        const script = document.createElement('script');
        script.src = Joomla.getOptions('system.paths').root + '/media/com_j2commerce/js/administrator/builder-phppagebuilder.js';
        document.head.appendChild(script);
    });
})();
