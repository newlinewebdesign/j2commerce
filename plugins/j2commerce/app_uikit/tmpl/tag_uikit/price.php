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

use J2Commerce\Component\J2commerce\Administrator\Helper\J2CommerceHelper;
use Joomla\CMS\Language\Text;

/** @var \J2Commerce\Component\J2commerce\Site\View\Product\HtmlView $this */

$product = $this->singleton_product;
$params = $this->singleton_params;
?>
<?php echo J2CommerceHelper::plugin()->eventWithHtml('BeforeRenderingProductPrice', [$product])->getArgument('html', ''); ?>

<?php if ($params->get('item_show_product_base_price', 1) || $params->get('item_show_product_special_price', 1)) : ?>
    <div class="j2commerce-product-price-container uk-margin-remove">
        <div class="uk-flex uk-flex-middle">
            <?php if ($params->get('item_show_product_special_price', 1)) : ?>
                <div class="sale-price">
                    <?php if (isset($product->pricing->price)) {
                        echo J2CommerceHelper::product()->displayPrice($product->pricing->price, $product, $params);
                    } ?>
                </div>
            <?php endif; ?>

            <?php if ($params->get('item_show_product_base_price', 1) && isset($product->pricing->base_price) && isset($product->pricing->price)
                && $product->pricing->base_price != $product->pricing->price) : ?>
                <?php $base_price = J2CommerceHelper::product()->displayPrice($product->pricing->base_price, $product, $params); ?>
                <del class="base-price uk-text-muted uk-margin-small-left">
                    <?php echo $base_price; ?>
                </del>
            <?php endif; ?>
        </div>
        <?php if ($params->get('display_price_with_tax_info', 0)) : ?>
            <div class="tax-text uk-display-block uk-text-muted uk-text-small uk-margin-small-top">
                <?php echo J2CommerceHelper::product()->get_tax_text(); ?>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php echo J2CommerceHelper::plugin()->eventWithHtml('AfterRenderingProductPrice', [$product])->getArgument('html', ''); ?>
