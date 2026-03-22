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
use Joomla\CMS\Uri\Uri;

$product      = $this->availableProduct;
$productId    = $product->product_id ?? 0;
$productName  = $product->product_name ?? '';
$productSku   = $product->product_sku ?? '';
$productImage = $product->product_image ?? '';
$isOutOfStock = $product->is_out_of_stock ?? false;

$imageUrl = !empty($productImage)
    ? Uri::root() . $productImage
    : Uri::root() . 'media/com_j2commerce/images/placeholder.png';
?>

<div class="uk-flex uk-flex-column uk-padding-small">
    <div class="boxbuilder-product-card uk-card uk-card-default uk-flex uk-flex-column uk-height-1-1"
         id="boxbuilder-card-<?php echo $productId; ?>"
         data-product-id="<?php echo $productId; ?>"
         data-product-name="<?php echo htmlspecialchars($productName, ENT_QUOTES, 'UTF-8'); ?>"
         data-product-sku="<?php echo htmlspecialchars($productSku, ENT_QUOTES, 'UTF-8'); ?>"
         data-product-image="<?php echo htmlspecialchars($imageUrl, ENT_QUOTES, 'UTF-8'); ?>"
         data-out-of-stock="<?php echo $isOutOfStock ? '1' : '0'; ?>">

        <div class="uk-card-media-top">
            <div class="boxbuilder-product-image uk-padding-small">
                <img src="<?php echo $imageUrl; ?>"
                     alt="<?php echo htmlspecialchars($productName, ENT_QUOTES, 'UTF-8'); ?>"
                     loading="lazy"
                     style="width:100%;height:auto;">
                <?php if ($isOutOfStock): ?>
                    <div class="boxbuilder-out-of-stock-badge">
                        <?php echo Text::_('COM_J2COMMERCE_OUT_OF_STOCK'); ?>
                    </div>
                <?php endif; ?>
            </div>
            <h6 class="boxbuilder-product-title uk-card-title uk-margin-remove uk-padding-small">
                <?php echo htmlspecialchars($productName, ENT_QUOTES, 'UTF-8'); ?>
            </h6>
        </div>

        <div class="uk-card-body uk-padding-small">
            <div class="boxbuilder-add-container">
                <button type="button"
                        class="uk-button uk-button-default uk-button-small uk-width-1-1 boxbuilder-add-btn"
                        data-product-id="<?php echo $productId; ?>"
                        <?php echo $isOutOfStock ? 'disabled' : ''; ?>>
                    <span class="boxbuilder-add-icon">+</span>
                    <?php echo Text::_('PLG_J2COMMERCE_APP_BOXBUILDERPRODUCT_ADD'); ?>
                </button>
            </div>

            <div class="boxbuilder-quantity-container" style="display: none;">
                <div class="boxbuilder-quantity-selector uk-flex uk-flex-middle uk-flex-center">
                    <button type="button"
                            class="uk-button uk-button-default uk-button-small boxbuilder-qty-minus"
                            data-product-id="<?php echo $productId; ?>">
                        <span aria-hidden="true">&minus;</span>
                        <span class="uk-hidden"><?php echo Text::_('PLG_J2COMMERCE_APP_BOXBUILDERPRODUCT_DECREASE'); ?></span>
                    </button>
                    <span class="boxbuilder-qty-value uk-margin-small-left uk-margin-small-right" data-product-id="<?php echo $productId; ?>">0</span>
                    <button type="button"
                            class="uk-button uk-button-default uk-button-small boxbuilder-qty-plus"
                            data-product-id="<?php echo $productId; ?>">
                        <span aria-hidden="true">+</span>
                        <span class="uk-hidden"><?php echo Text::_('PLG_J2COMMERCE_APP_BOXBUILDERPRODUCT_INCREASE'); ?></span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
