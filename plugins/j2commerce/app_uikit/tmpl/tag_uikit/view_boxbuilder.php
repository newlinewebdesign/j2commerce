<?php
/**
 * @package     J2Commerce
 * @subpackage  plg_j2commerce_app_boxbuilderproduct
 *
 * @copyright   (C)2024-2026 J2Commerce, LLC <https://www.j2commerce.com>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

$boxbuilderproducts = $this->product->params->get('boxbuilderproduct', []);
?>
<?php if ($boxbuilderproducts): ?>
    <div class="boxbuilderproducts">
        <table class="uk-table uk-table-bordered uk-table-striped">
            <thead>
                <tr>
                    <th><?php echo Text::_('COM_J2COMMERCE_BUNDLE_PRODUCTS'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($boxbuilderproducts as $boxbuilderproduct): ?>
                    <tr>
                        <td><?php echo isset($boxbuilderproduct->product_name) ? htmlspecialchars($boxbuilderproduct->product_name, ENT_QUOTES, 'UTF-8') : ''; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
