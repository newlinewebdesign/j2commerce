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

$productfilters = $this->product->productfilters ?? [];
?>
<div class="uk-grid" uk-grid>
    <div class="uk-width-1-1">
        <?php if ($this->params->get('item_show_ldesc')) : ?>
            <div class="product-description uk-margin-large-top uk-padding-small uk-border-top">
                <h3 class="uk-text-center uk-margin-bottom"><?php echo Text::_('COM_J2COMMERCE_PRODUCT_DESCRIPTION'); ?></h3>
                <?php echo $this->loadTemplate('ldesc'); ?>
            </div>
        <?php endif; ?>

        <?php if ($this->params->get('item_show_product_specification')) : ?>
            <div class="product-specs uk-margin-large-top uk-padding-small uk-border-top">
                <h3 class="uk-text-center uk-margin-bottom"><?php echo Text::_('COM_J2COMMERCE_PRODUCT_SPECIFICATIONS'); ?></h3>
                <?php echo $this->loadTemplate('specs'); ?>
            </div>
        <?php endif; ?>

    </div>
</div>
