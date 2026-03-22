<?php
/**
 * @package     J2Commerce
 * @subpackage  com_j2commerce
 *
 * @copyright   (C)2024-2025 J2Commerce, LLC <https://www.j2commerce.com>
 * @license     GNU General Public License version 3 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use J2Commerce\Component\J2commerce\Administrator\Helper\J2CommerceHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

$columns = $this->params->get('item_related_product_columns', 3);
$total = count($this->up_sells);
$counter = 0;
$upsell_image_width = $this->params->get('item_product_upsell_image_width', 100);
?>
<div class="row product-upsells-container">
	<div class="col-sm-12">
		<h3><?php echo Text::_('COM_J2COMMERCE_RELATED_PRODUCTS_UPSELLS'); ?></h3>
        <?php foreach ($this->up_sells as $upsell_product) : ?>
            <?php
                $upsell_product->product_link = J2CommerceHelper::platform()->getProductUrl(['task' => 'view', 'id' => $upsell_product->j2commerce_product_id]);
                if (!empty($upsell_product->addtocart_text)) {
                    $cart_text = Text::_($upsell_product->addtocart_text);
                } else {
                    $cart_text = Text::_('COM_J2COMMERCE_ADD_TO_CART');
                }
                $upsell_product_name = $this->escape($upsell_product->product_name);
            ?>

            <?php $rowcount = ((int) $counter % (int) $columns) + 1; ?>
            <?php if ($rowcount == 1) : ?>
                <?php $row = $counter / $columns; ?>
                <div class="upsell-product-row <?php echo 'row-'.$row; ?> row">
            <?php endif;?>

            <?php $upsell_css = $upsell_product->params->get('product_css_class',''); ?>
            <div class="col-sm-<?php echo round((12 / $columns));?> upsell-product product-<?php echo $upsell_product->j2commerce_product_id;?><?php echo isset($upsell_css) ? ' ' . $upsell_css : ''; ?>">
                <span class="upsell-product-image">
                    <?php
                        $thumb_image = '';
                        if(isset($upsell_product->thumb_image) && $upsell_product->thumb_image){
                            $thumb_image =$platform->getImagePath($upsell_product->thumb_image);
                        }
                    ?>
                    <?php if(isset($thumb_image) &&  !empty($thumb_image)):?>
                        <a href="<?php echo $upsell_product->product_link; ?>">
                            <img title="<?php echo $upsell_product_name ;?>"
                                alt="<?php echo $upsell_product_name ;?>"
                                class="j2commerce-product-thumb-image-<?php echo $upsell_product->j2commerce_product_id; ?>"
                                src="<?php echo $thumb_image;?>"
                                width="<?php echo intval($upsell_image_width);?>"
                            />
                        </a>
                    <?php endif; ?>
                </span>
                <h3 class="upsell-product-title">
                    <a href="<?php echo $upsell_product->product_link; ?>">
                        <?php echo $upsell_product_name; ?>
                    </a>
                </h3>

                <?php if (J2CommerceHelper::product()->canShowprice($this->params)) : ?>
                    <?php
                        $this->singleton_product = $upsell_product;
                        $this->singleton_params = $this->params;
                        echo $this->loadAnyTemplate('site:com_j2commerce/products/price'); // TODO
                    ?>
                <?php endif; ?>

                <?php if (J2CommerceHelper::product()->canShowCart($this->params)) : ?>
                    <?php $upsell_option = isset($upsell_product->options) && is_array($upsell_product->options) ? count($upsell_product->options) : 0; ?>
                    <?php if ($upsell_option || $upsell_product->product_type == 'variable') : ?>
                        <a class="<?php echo $this->params->get('choosebtn_class', 'btn btn-success'); ?>"
                            href="<?php echo $upsell_product->product_link; ?>">
                            <?php echo Text::_('COM_J2COMMERCE_CART_CHOOSE_OPTIONS'); ?>
                        </a>
                    <?php else: ?>
                        <?php
                            $this->singleton_product = $upsell_product;
                            $this->singleton_params = $this->params;
                            $this->singleton_cartext = $this->escape($cart_text);
                            echo $this->loadAnyTemplate('site:com_j2commerce/products/cart'); // TODO
                        ?>
                    <?php endif; ?>
                <?php endif; ?>
                </div>
            <?php $counter++; ?>
            <?php if (($rowcount == $columns) || ($counter == $total)) : ?>
                </div>
            <?php endif; ?>
        <?php endforeach;?>
	</div>
</div>
