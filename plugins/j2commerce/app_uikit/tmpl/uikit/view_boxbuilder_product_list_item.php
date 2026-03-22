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

<div class="boxbuilder-product-card boxbuilder-product-list-item uk-flex uk-flex-middle uk-padding-small uk-margin-small-bottom"
     id="boxbuilder-card-<?php echo $productId; ?>"
     data-product-id="<?php echo $productId; ?>"
     data-product-name="<?php echo htmlspecialchars($productName, ENT_QUOTES, 'UTF-8'); ?>"
     data-product-sku="<?php echo htmlspecialchars($productSku, ENT_QUOTES, 'UTF-8'); ?>"
     data-product-image="<?php echo htmlspecialchars($imageUrl, ENT_QUOTES, 'UTF-8'); ?>"
     data-out-of-stock="<?php echo $isOutOfStock ? '1' : '0'; ?>"
     style="border:1px solid #e5e5e5;border-radius:4px;">

    <div class="uk-flex-none uk-position-relative">
        <img src="<?php echo $imageUrl; ?>"
             alt="<?php echo htmlspecialchars($productName, ENT_QUOTES, 'UTF-8'); ?>"
             class="boxbuilder-list-image"
             style="width: 80px; height: auto;"
             loading="lazy">
        <?php if ($isOutOfStock): ?>
            <span class="uk-badge uk-position-top-left" style="font-size: 0.6rem;">
                <?php echo Text::_('COM_J2COMMERCE_OUT_OF_STOCK'); ?>
            </span>
        <?php endif; ?>
    </div>

    <div class="uk-width-expand uk-margin-small-left">
        <h6 class="boxbuilder-list-title uk-margin-remove-bottom">
            <?php echo htmlspecialchars($productName, ENT_QUOTES, 'UTF-8'); ?>
        </h6>
        <?php if (!empty($productSku)): ?>
            <div class="uk-text-small uk-text-muted">
                <span class="sku-label"><?php echo Text::_('COM_J2COMMERCE_SKU'); ?>:</span>
                <span class="sku-value"><?php echo htmlspecialchars($productSku, ENT_QUOTES, 'UTF-8'); ?></span>
            </div>
        <?php endif; ?>
    </div>

    <div class="uk-flex-none uk-margin-small-left boxbuilder-actions-container">
        <div class="boxbuilder-add-container uk-text-right">
            <button type="button"
                    class="uk-button uk-button-default uk-button-small boxbuilder-add-btn"
                    data-product-id="<?php echo $productId; ?>"
                    <?php echo $isOutOfStock ? 'disabled' : ''; ?>>
                <span class="boxbuilder-add-icon">+</span>
                <span class="uk-visible@m"><?php echo Text::_('PLG_J2COMMERCE_APP_BOXBUILDERPRODUCT_ADD'); ?></span>
            </button>
        </div>

        <div class="boxbuilder-quantity-container" style="display: none;">
            <div class="boxbuilder-quantity-selector uk-flex uk-flex-middle uk-flex-right">
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
