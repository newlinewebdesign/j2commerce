/**
 * @package     J2Commerce Library
 * @subpackage  lib_j2commerce
 *
 * @copyright   (C)2024-2026 J2Commerce, LLC <https://www.j2commerce.com>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.select-link').forEach(element => {
        // Listen for click event
        element.addEventListener('click', event => {
            event.preventDefault();
            const {
                target
            } = event;
            if (window.parent.Joomla.Modal && window.parent.Joomla.Modal.getCurrent()) {
                window.parent.Joomla.Modal.getCurrent().close();
            }
        });
    });
});
