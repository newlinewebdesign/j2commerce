<?php
/**
 * @package     J2Commerce
 * @subpackage  Plugin.J2Commerce.AppBootstrap5
 *
 * @copyright   Copyright (C) 2025 J2Commerce, LLC. All rights reserved.
 * @license     GNU General Public License version 2 or later;  see LICENSE.txt
 */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;

extract((array) $displayData);
?>
<form class="form-horizontal form-validate" id="adminForm" name="adminForm" method="post" action="<?php echo Route::_('index.php'); ?>">
    <?php echo HTMLHelper::_('form.token'); ?>
    <input type="hidden" name="option" value="com_j2commerce" />
    <input type="hidden" name="view" value="apps" />
    <input type="hidden" name="task" id="task" value="view" />
</form>
