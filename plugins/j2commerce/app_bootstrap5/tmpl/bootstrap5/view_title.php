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

/** @var \J2Commerce\Component\J2commerce\Site\View\Product\HtmlView $this */

?>
<?php if ($this->params->get('item_show_title', 1)) : ?>
    <h<?php echo $this->params->get('item_title_headertag', '2'); ?> class="product-title">
        <?php echo $this->escape($this->product->product_name); ?>
    </h<?php echo $this->params->get('item_title_headertag', '2'); ?>>
<?php endif; ?>
