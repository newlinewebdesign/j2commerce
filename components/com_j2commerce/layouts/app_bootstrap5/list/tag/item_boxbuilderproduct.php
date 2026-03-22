<?php
/**
 * @package     J2Commerce
 * @subpackage  plg_j2commerce_app_boxbuilderproduct
 *
 * @copyright   (C)2024-2026 J2Commerce, LLC <https://www.j2commerce.com>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
?>
<div class="j2commerce-boxbuilder-list-item">
    <a href="<?php echo $displayData['product']->product_link ?? '#'; ?>" class="btn btn-sm btn-outline-primary mt-2">
        <?php echo Text::_('COM_J2COMMERCE_VIEW_PRODUCT_DETAILS'); ?>
    </a>
</div>
