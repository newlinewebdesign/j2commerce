<?php
/**
 * @package     J2Commerce
 * @subpackage  Plugin.J2Commerce.AppUikit
 *
 * @copyright   Copyright (C) 2025-2026 J2Commerce, LLC. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

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
