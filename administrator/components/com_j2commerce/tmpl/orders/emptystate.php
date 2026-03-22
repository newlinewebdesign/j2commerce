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
use Joomla\CMS\Layout\LayoutHelper;

/** @var \J2Commerce\Component\J2commerce\Administrator\View\Orders\HtmlView $this */

$displayData = [
    'textPrefix' => 'COM_J2COMMERCE_ORDERS',
    'formURL'    => 'index.php?option=com_j2commerce&view=orders',
    'icon'       => 'icon-shopping-cart',
];

?>

<?php echo $this->navbar; ?>

<div class="px-4 py-5 text-center">
    <div class="py-5">
        <span class="fas fa-shopping-cart fa-5x text-muted mb-4" aria-hidden="true"></span>
        <h1 class="display-5 fw-bold text-body-emphasis"><?php echo Text::_('COM_J2COMMERCE_ORDERS_EMPTYSTATE_TITLE'); ?></h1>
        <div class="col-lg-6 mx-auto">
            <p class="lead mb-4"><?php echo Text::_('COM_J2COMMERCE_ORDERS_EMPTYSTATE_CONTENT'); ?></p>
        </div>
    </div>
</div>

<?php echo $this->footer ?? ''; ?>
