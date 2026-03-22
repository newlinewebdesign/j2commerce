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

$boxSize             = $this->boxSize ?? 4;
$availableProducts   = $this->availableProducts ?? [];
$boxPrice            = $this->boxPrice ?? 0;
$productId           = (int) ($this->product->j2commerce_product_id ?? 0);
$productDisplay      = $this->productDisplay ?? 'grid';
?>

<div class="boxbuilder-interactive-container"
     id="boxbuilder-container-<?php echo $productId; ?>"
     data-product-id="<?php echo $productId; ?>"
     data-box-size="<?php echo $boxSize; ?>"
     data-box-price="<?php echo $boxPrice; ?>">
    <div class="row">
        <div class="col-lg-5 order-2 order-lg-1 boxbuilder-products-display">
            <div class="boxbuilder-products-section">
                <h4 class="boxbuilder-section-title d-none d-lg-block mb-4">
                    <?php echo Text::_('PLG_J2COMMERCE_APP_BOXBUILDERPRODUCT_AVAILABLE_PRODUCTS'); ?>
                </h4>

                <?php if ($productDisplay === 'list'): ?>
                    <div class="boxbuilder-products-list">
                        <?php foreach ($availableProducts as $availableProduct): ?>
                            <?php
                            $this->availableProduct = $availableProduct;
                            echo $this->loadTemplate('boxbuilder_product_list_item');
                            ?>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="boxbuilder-products-grid row row-cols-2 row-cols-md-2 row-cols-lg-3">
                        <?php foreach ($availableProducts as $availableProduct): ?>
                            <?php
                            $this->availableProduct = $availableProduct;
                            echo $this->loadTemplate('boxbuilder_product_card');
                            ?>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-lg-6 order-1 order-lg-2 ms-lg-auto boxbuilder-general">
            <?php echo $this->loadTemplate('boxbuilder_general'); ?>

            <div class="sticky-lg-top">
                <?php echo $this->loadTemplate('boxbuilder_sidebar'); ?>

                <form action="<?php echo $this->escape($this->product->cart_form_action); ?>"
                      method="post"
                      class="j2commerce-addtocart-form w-100 d-block mt-5"
                      id="j2commerce-addtocart-form-<?php echo $productId; ?>"
                      name="j2commerce-addtocart-form-<?php echo $productId; ?>"
                      data-product_id="<?php echo $productId; ?>"
                      data-product_type="<?php echo $this->escape($this->product->product_type); ?>"
                      enctype="multipart/form-data">

                    <input type="hidden" name="boxbuilder_selections" id="boxbuilder-selections-<?php echo $productId; ?>" value="[]">
                    <input type="hidden" name="boxbuilder_complete" id="boxbuilder-complete-<?php echo $productId; ?>" value="0">

                    <?php echo $this->loadTemplate('boxbuilder_cart'); ?>
                </form>
            </div>
        </div>
    </div>
</div>
