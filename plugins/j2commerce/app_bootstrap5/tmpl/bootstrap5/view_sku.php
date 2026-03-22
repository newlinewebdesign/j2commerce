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

/** @var \J2Commerce\Component\J2commerce\Site\View\Product\HtmlView $this */
//dump($this->product->variant);

?>
<?php if ($this->params->get('item_show_product_sku', 1) && !empty($this->product->variant->sku)) : ?>
    <div class="product-sku text-end">
        <span class="sku-label text-body-secondary"><?php echo Text::_('COM_J2COMMERCE_SKU'); ?></span>
        <span class="sku-value fw-medium"> <?php echo $this->escape($this->product->variant->sku); ?> </span>
    </div>
<?php endif; ?>
