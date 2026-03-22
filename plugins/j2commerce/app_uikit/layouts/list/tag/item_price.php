<?php
/**
 * @package     J2Commerce
 * @subpackage  com_j2commerce
 *
 * @copyright   (C)2024-2026 J2Commerce, LLC <https://www.j2commerce.com>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use J2Commerce\Component\J2commerce\Administrator\Helper\J2CommerceHelper;

extract($displayData);

$productHelper = J2CommerceHelper::product();

if (!$showPrice || !$productHelper->canShowprice($params)) {
    return;
}

$pricing = $product->pricing ?? null;
if (!$pricing) {
    return;
}

$showBasePrice = (bool) $params->get('list_show_product_base_price', 1);
$showSpecialPrice = (bool) $params->get('list_show_product_special_price', 1);
$showTaxInfo = (bool) $params->get('display_price_with_tax_info', 0);
$showDiscountPercentage = (bool) $params->get('list_show_discount_percentage', 1);

if (!$showBasePrice && !$showSpecialPrice) {
    return;
}

$beforeHtml = J2CommerceHelper::plugin()->eventWithHtml('BeforeRenderingProductPrice', [$product])->getArgument('html', '');
$afterHtml = J2CommerceHelper::plugin()->eventWithHtml('AfterRenderingProductPrice', [$product])->getArgument('html', '');
?>
<?php echo $beforeHtml; ?>

<div class="j2commerce-product-price-container">
    <?php
    $basePrice = $pricing->base_price ?? 0;
    $salePrice = $pricing->price ?? 0;
    $hasDiscount = isset($pricing->is_discount_pricing_available) && $pricing->is_discount_pricing_available;
    ?>

    <?php if ($showBasePrice && $basePrice > 0 && $basePrice != $salePrice): ?>
        <div class="base-price <?php echo $hasDiscount ? 'strike' : ''; ?>">
            <?php echo $productHelper->displayPrice((float) $basePrice, $product, $params); ?>
        </div>
    <?php endif; ?>

    <?php if ($showSpecialPrice && isset($pricing->price)): ?>
        <div class="sale-price">
            <?php echo $productHelper->displayPrice((float) $salePrice, $product, $params); ?>
        </div>
    <?php endif; ?>

    <?php if ($showTaxInfo): ?>
        <div class="tax-text">
            <?php echo $productHelper->get_tax_text(); ?>
        </div>
    <?php endif; ?>
</div>

<?php echo $afterHtml; ?>

<?php if ($showDiscountPercentage && $hasDiscount && $basePrice > 0): ?>
    <?php $discount = (1 - ($salePrice / $basePrice)) * 100; ?>
    <?php if ($discount > 0): ?>
        <div class="discount-percentage">
            <?php echo Text::sprintf('COM_J2COMMERCE_PRODUCT_OFFER', round($discount) . '%'); ?>
        </div>
    <?php endif; ?>
<?php endif; ?>
