<?php
/**
 * @package     J2Commerce
 * @subpackage  plg_j2commerce_app_boxbuilderproduct
 *
 * @copyright   (C)2024-2026 J2Commerce, LLC <https://www.j2commerce.com>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

$boxSize = $this->boxSize ?? 4;

// Determine grid layout based on box_size
// | 2 | 1x2 | | 3 | 1x3 | | 4 | 1x4 | | 5 | 2x3 | | 6 | 2x3 | | 7-8 | 2x4 | | 9 | 3x3 | | 10-12 | 3x4 |
$gridCols = 2;
if ($boxSize <= 4) {
    $gridCols = $boxSize;
} elseif ($boxSize <= 6) {
    $gridCols = 3;
} elseif ($boxSize <= 8) {
    $gridCols = 4;
} elseif ($boxSize === 9) {
    $gridCols = 3;
} else {
    $gridCols = 4;
}
?>

<div class="boxbuilder-sidebar" id="boxbuilder-sidebar-<?php echo $this->product->j2commerce_product_id; ?>">
    <h4 class="boxbuilder-sidebar-title uk-card-title uk-text-center uk-margin-bottom">
        <?php echo Text::_('PLG_J2COMMERCE_APP_BOXBUILDERPRODUCT_BUILD_YOUR_BOX'); ?>
    </h4>

    <div class="boxbuilder-slots-grid uk-margin-bottom"
         id="boxbuilder-slots-<?php echo $this->product->j2commerce_product_id; ?>"
         data-grid-cols="<?php echo $gridCols; ?>"
         style="--boxbuilder-grid-cols: <?php echo $gridCols; ?>;">
        <?php for ($i = 0; $i < $boxSize; $i++): ?>
            <div class="boxbuilder-slot boxbuilder-slot-empty"
                 data-slot-index="<?php echo $i; ?>"
                 data-product-id="">
                <div class="boxbuilder-slot-inner">
                    <span class="boxbuilder-slot-placeholder">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="12" y1="5" x2="12" y2="19"></line>
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                        </svg>
                    </span>
                    <img src="" alt="" class="boxbuilder-slot-image" style="display: none;">

                    <button type="button" class="boxbuilder-slot-remove uk-close" aria-label="<?php echo Text::_('PLG_J2COMMERCE_APP_BOXBUILDERPRODUCT_REMOVE'); ?>" style="display: none;" uk-close>
                    </button>
                </div>
            </div>
        <?php endfor; ?>
    </div>

    <div class="boxbuilder-progress-info uk-text-center uk-text-muted uk-margin-bottom">
        <span class="boxbuilder-selected-count">0</span> / <?php echo $boxSize; ?>
        <?php echo Text::_('PLG_J2COMMERCE_APP_BOXBUILDERPRODUCT_ITEMS_SELECTED'); ?>
    </div>
</div>
