<?php
/**
 * @package     J2Commerce
 * @subpackage  plg_j2commerce_app_boxbuilderproduct
 *
 * @copyright   (C)2024-2026 J2Commerce, LLC <https://www.j2commerce.com>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use J2Commerce\Component\J2commerce\Administrator\Helper\ImageHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

$product      = $this->availableProduct;
$productId    = (int) ($product->product_id ?? 0);
$productName  = $product->product_name ?? '';
$productSku   = $product->product_sku ?? '';
$productImage = $product->product_image ?? '';
$isOutOfStock = $product->is_out_of_stock ?? false;

$imageUrl = '';
if (!empty($productImage)) {
    $imageUrl = ImageHelper::isValidImagePath($productImage)
        ? ImageHelper::getImageUrl($productImage)
        : Uri::root() . $productImage;
}

if (empty($imageUrl)) {
    $imageUrl = Uri::root() . 'media/com_j2commerce/images/placeholder.png';
}
?>

<div class="boxbuilder-product-card boxbuilder-product-list-item d-flex align-items-center p-2 mb-2 border rounded"
     id="boxbuilder-card-<?php echo $productId; ?>"
     data-product-id="<?php echo $productId; ?>"
     data-product-name="<?php echo htmlspecialchars($productName, ENT_QUOTES, 'UTF-8'); ?>"
     data-product-sku="<?php echo htmlspecialchars($productSku, ENT_QUOTES, 'UTF-8'); ?>"
     data-product-image="<?php echo htmlspecialchars($imageUrl, ENT_QUOTES, 'UTF-8'); ?>"
     data-out-of-stock="<?php echo $isOutOfStock ? '1' : '0'; ?>">

    <div class="flex-shrink-0 position-relative">
        <img src="<?php echo htmlspecialchars($imageUrl, ENT_QUOTES, 'UTF-8'); ?>"
             alt="<?php echo htmlspecialchars($productName, ENT_QUOTES, 'UTF-8'); ?>"
             class="boxbuilder-list-image"
             style="width: 80px; height: auto;"
             loading="lazy">
        <?php if ($isOutOfStock): ?>
            <span class="badge bg-secondary position-absolute top-0 start-0" style="font-size: 0.6rem;">
                <?php echo Text::_('COM_J2COMMERCE_STOCK_OUT_OF_STOCK'); ?>
            </span>
        <?php endif; ?>
    </div>

    <div class="flex-grow-1 ms-3">
        <h6 class="boxbuilder-list-title mb-1">
            <?php echo htmlspecialchars($productName, ENT_QUOTES, 'UTF-8'); ?>
        </h6>
        <?php if (!empty($productSku)): ?>
            <div class="fs-xs text-muted">
                <span class="sku-label"><?php echo Text::_('COM_J2COMMERCE_HEADING_SKU'); ?>:</span>
                <span class="sku-value text-dark"><?php echo htmlspecialchars($productSku, ENT_QUOTES, 'UTF-8'); ?></span>
            </div>
        <?php endif; ?>
    </div>

    <div class="flex-shrink-0 ms-2 boxbuilder-actions-container">
        <div class="boxbuilder-add-container text-end">
            <button type="button"
                    class="btn btn-outline-info btn-sm boxbuilder-add-btn"
                    data-product-id="<?php echo $productId; ?>"
                    <?php echo $isOutOfStock ? 'disabled' : ''; ?>>
                <span class="boxbuilder-add-icon">+</span>
                <span class="d-none d-md-inline"><?php echo Text::_('PLG_J2COMMERCE_APP_BOXBUILDERPRODUCT_ADD'); ?></span>
            </button>
        </div>

        <div class="boxbuilder-quantity-container ms-auto" style="display: none;">
            <div class="boxbuilder-quantity-selector d-flex align-items-center justify-content-end">
                <button type="button" class="btn btn-light btn-sm boxbuilder-qty-minus" data-product-id="<?php echo $productId; ?>">
                    <span aria-hidden="true">&minus;</span>
                    <span class="visually-hidden"><?php echo Text::_('PLG_J2COMMERCE_APP_BOXBUILDERPRODUCT_DECREASE'); ?></span>
                </button>
                <span class="boxbuilder-qty-value mx-2" data-product-id="<?php echo $productId; ?>">0</span>
                <button type="button" class="btn btn-light btn-sm boxbuilder-qty-plus" data-product-id="<?php echo $productId; ?>">
                    <span aria-hidden="true">+</span>
                    <span class="visually-hidden"><?php echo Text::_('PLG_J2COMMERCE_APP_BOXBUILDERPRODUCT_INCREASE'); ?></span>
                </button>
            </div>
        </div>
    </div>
</div>
