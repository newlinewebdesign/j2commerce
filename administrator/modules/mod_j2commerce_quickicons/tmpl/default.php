<?php

/**
 * @package     J2Commerce
 * @subpackage  mod_j2commerce_quickicons
 *
 * @copyright   (C)2024-2026 J2Commerce, LLC <https://www.j2commerce.com>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $app->getDocument()->getWebAssetManager();
$wa->useScript('core')
    ->useScript('bootstrap.dropdown');
$wa->registerAndUseScript('mod_quickicon', 'mod_quickicon/quickicon.min.js', ['relative' => true, 'version' => 'auto'], ['type' => 'module']);

$html = HTMLHelper::_('icons.buttons', $buttons);
?>
<?php if (!empty($html)) : ?>
    <nav class="quick-icons px-3 pb-3"
         aria-label="<?php echo Text::_('MOD_J2COMMERCE_QUICKICONS') . ' ' . htmlspecialchars($module->title, ENT_QUOTES, 'UTF-8'); ?>">
        <ul class="nav flex-wrap">
            <?php echo $html; ?>
        </ul>
    </nav>
<?php endif; ?>
