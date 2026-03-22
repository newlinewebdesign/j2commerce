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

$item = $this->item;
$orderInfo = $item->orderinfo ?? null;

?>
<div class="row">
    <div class="col-lg-8">
        <h4><?php echo Text::_('COM_J2COMMERCE_TAB_SHIPPING_ADDRESS'); ?></h4>

        <?php if ($orderInfo && $item->is_shippable) : ?>
        <div class="card">
            <div class="card-body">
                <address class="mb-0" style="font-style: normal; line-height: 1.8;">
                    <strong><?php echo $this->escape(($orderInfo->shipping_first_name ?? '') . ' ' . ($orderInfo->shipping_last_name ?? '')); ?></strong><br>
                    <?php if (!empty($orderInfo->shipping_company)) : ?>
                        <?php echo $this->escape($orderInfo->shipping_company); ?><br>
                    <?php endif; ?>
                    <?php echo $this->escape($orderInfo->shipping_address_1 ?? ''); ?><br>
                    <?php if (!empty($orderInfo->shipping_address_2)) : ?>
                        <?php echo $this->escape($orderInfo->shipping_address_2); ?><br>
                    <?php endif; ?>
                    <?php echo $this->escape($orderInfo->shipping_city ?? ''); ?>, <?php echo $this->escape($orderInfo->shipping_zone_name ?? ''); ?> <?php echo $this->escape($orderInfo->shipping_zip ?? ''); ?><br>
                    <?php echo $this->escape($orderInfo->shipping_country_name ?? ''); ?><br>
                    <?php if (!empty($orderInfo->shipping_phone_1)) : ?>
                        <?php echo Text::_('COM_J2COMMERCE_PHONE'); ?>: <?php echo $this->escape($orderInfo->shipping_phone_1); ?>
                    <?php endif; ?>
                </address>
            </div>
        </div>

        <div class="mt-3">
            <button type="button" class="btn btn-outline-primary me-2" id="editShippingAddressBtn">
                <span class="icon-pencil-alt" aria-hidden="true"></span> <?php echo Text::_('COM_J2COMMERCE_EDIT_ADDRESS'); ?>
            </button>
            <button type="button" class="btn btn-outline-warning" id="chooseShippingAddressBtn">
                <span class="icon-list" aria-hidden="true"></span> <?php echo Text::_('COM_J2COMMERCE_CHOOSE_ALTERNATE_ADDRESS'); ?>
            </button>
        </div>
        <?php elseif (!$item->is_shippable) : ?>
            <div class="alert alert-secondary"><?php echo Text::_('COM_J2COMMERCE_ORDER_NOT_SHIPPABLE'); ?></div>
        <?php else : ?>
            <div class="alert alert-info"><?php echo Text::_('COM_J2COMMERCE_NO_SHIPPING_ADDRESS'); ?></div>
        <?php endif; ?>
    </div>
</div>
