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

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

/** @var \J2Commerce\Component\J2commerce\Administrator\View\Order\HtmlView $this */

$wa = $this->getDocument()->getWebAssetManager();
$wa->useScript('keepalive')
    ->useScript('form.validate');

$item = $this->item;
$orderInfo = $item->orderinfo ?? null;
$orderItems = $item->orderitems ?? [];
$orderShipping = $item->ordershipping ?? null;
$orderDiscounts = $item->orderdiscounts ?? [];

?>
<form action="<?php echo Route::_('index.php?option=com_j2commerce&layout=edit&id=' . (int) $item->j2commerce_order_id); ?>"
      method="post" name="adminForm" id="order-form" class="form-validate">

    <div class="main-card">
        <?php echo HTMLHelper::_('uitab.startTabSet', 'orderTab', ['active' => 'basic', 'recall' => true, 'breakpoint' => 768]); ?>

        <?php // Tab 1: Basic ?>
        <?php echo HTMLHelper::_('uitab.addTab', 'orderTab', 'basic', Text::_('COM_J2COMMERCE_TAB_BASIC')); ?>
            <?php echo $this->loadTemplate('basic'); ?>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>

        <?php // Tab 2: Billing Address ?>
        <?php echo HTMLHelper::_('uitab.addTab', 'orderTab', 'billing', Text::_('COM_J2COMMERCE_TAB_BILLING_ADDRESS')); ?>
            <?php echo $this->loadTemplate('billing'); ?>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>

        <?php // Tab 3: Shipping Address ?>
        <?php echo HTMLHelper::_('uitab.addTab', 'orderTab', 'shipping', Text::_('COM_J2COMMERCE_TAB_SHIPPING_ADDRESS')); ?>
            <?php echo $this->loadTemplate('shipping'); ?>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>

        <?php // Tab 4: Items ?>
        <?php echo HTMLHelper::_('uitab.addTab', 'orderTab', 'items', Text::_('COM_J2COMMERCE_TAB_ITEMS')); ?>
            <?php echo $this->loadTemplate('items'); ?>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>

        <?php // Tab 5: Payment & Shipping Methods ?>
        <?php echo HTMLHelper::_('uitab.addTab', 'orderTab', 'payment_shipping', Text::_('COM_J2COMMERCE_TAB_PAYMENT_SHIPPING')); ?>
            <?php echo $this->loadTemplate('payment_shipping'); ?>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>

        <?php // Tab 6: Order Summary ?>
        <?php echo HTMLHelper::_('uitab.addTab', 'orderTab', 'summary', Text::_('COM_J2COMMERCE_TAB_ORDER_SUMMARY')); ?>
            <?php echo $this->loadTemplate('summary'); ?>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>

        <?php echo HTMLHelper::_('uitab.endTabSet'); ?>
    </div>

    <input type="hidden" name="task" value="">
    <input type="hidden" name="id" value="<?php echo (int) $item->j2commerce_order_id; ?>">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
