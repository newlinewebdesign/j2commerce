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
<?php if ($this->params->get('item_show_sdesc', 1)) : ?>
    <div class="product-sdesc text-body-secondary mb-4">
        <?php echo $this->product->product_short_desc; ?>
    </div>
<?php endif; ?>
