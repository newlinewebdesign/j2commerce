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

<div class="col h-100 d-flex pt-3 pt-lg-4">
    <div class="boxbuilder-product-card card h-100 d-flex flex-column w-100 justify-content-between"
         id="boxbuilder-card-<?php echo $productId; ?>"
         data-product-id="<?php echo $productId; ?>"
         data-product-name="<?php echo htmlspecialchars($productName, ENT_QUOTES, 'UTF-8'); ?>"
         data-product-sku="<?php echo htmlspecialchars($productSku, ENT_QUOTES, 'UTF-8'); ?>"
         data-product-image="<?php echo htmlspecialchars($imageUrl, ENT_QUOTES, 'UTF-8'); ?>"
         data-out-of-stock="<?php echo $isOutOfStock ? '1' : '0'; ?>">

        <div class="card-img-top">
            <div class="boxbuilder-product-image p-2">
                <img src="<?php echo htmlspecialchars($imageUrl, ENT_QUOTES, 'UTF-8'); ?>"
                     alt="<?php echo htmlspecialchars($productName, ENT_QUOTES, 'UTF-8'); ?>"
                     class="img-fluid"
                     loading="lazy">
                <?php if ($isOutOfStock): ?>
                    <div class="boxbuilder-out-of-stock-badge">
                        <?php echo Text::_('COM_J2COMMERCE_STOCK_OUT_OF_STOCK'); ?>
                    </div>
                <?php endif; ?>
            </div>
            <h6 class="boxbuilder-product-title card-title mb-2 p-2">
                <?php echo htmlspecialchars($productName, ENT_QUOTES, 'UTF-8'); ?>
            </h6>
        </div>

        <div class="card-body p-2 p-md-3">
            <div class="boxbuilder-add-container">
                <button type="button"
                        class="btn btn-outline-info btn-sm w-100 boxbuilder-add-btn"
                        data-product-id="<?php echo $productId; ?>"
                        <?php echo $isOutOfStock ? 'disabled' : ''; ?>>
                    <span class="boxbuilder-add-icon">+</span>
                    <?php echo Text::_('PLG_J2COMMERCE_APP_BOXBUILDERPRODUCT_ADD'); ?>
                </button>
            </div>

            <div class="boxbuilder-quantity-container" style="display: none;">
                <div class="boxbuilder-quantity-selector d-flex align-items-center justify-content-center">
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
</div>
