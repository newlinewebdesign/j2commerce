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

$bundleproducts = (array) $this->product->params->get('bundleproduct', []);
?>
<?php if (!empty($bundleproducts)) : ?>
    <div class="bundleproducts">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th><?php echo Text::_('COM_J2COMMERCE_BUNDLE_PRODUCTS'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($bundleproducts as $bundleproduct) : ?>
                    <tr>
                        <td><?php echo $this->escape($bundleproduct->product_name ?? ''); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
