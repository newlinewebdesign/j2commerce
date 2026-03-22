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
use Joomla\Registry\Registry;

$productParams      = $this->product->params instanceof Registry
    ? $this->product->params
    : new Registry($this->product->params ?? '{}');

$boxbuilderproducts = (array) $productParams->get('boxbuilderproduct', []);
?>
<?php if (!empty($boxbuilderproducts)): ?>
    <div class="boxbuilderproducts">
        <table class="table table-bordered table-striped">
            <thead>
            <tr>
                <th><?php echo Text::_('COM_J2COMMERCE_APP_BOX_BUILDER_PRODUCTS'); ?></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($boxbuilderproducts as $boxbuilderproduct): ?>
                <tr>
                    <td><?php echo htmlspecialchars($boxbuilderproduct->product_name ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
