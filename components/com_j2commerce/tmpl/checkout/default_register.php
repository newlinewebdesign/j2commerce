<?php
/**
 * @package     J2Commerce
 * @subpackage  com_j2commerce
 *
 * @copyright   (C)2024-2026 J2Commerce, LLC <https://www.j2commerce.com>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use J2Commerce\Component\J2commerce\Administrator\Helper\CustomFieldHelper;
use J2Commerce\Component\J2commerce\Administrator\Helper\J2CommerceHelper;
use Joomla\CMS\Language\Text;

/** @var \J2Commerce\Component\J2commerce\Site\View\Checkout\HtmlView $this */

$showShipping = $this->showShipping ?? false;
$fields = $this->fields ?? [];

$config = J2CommerceHelper::config();
$requiredIndicator = $config->get('checkout_required_indicator', 'asterisk');
$fieldStyle = $config->get('checkout_field_style', 'normal');
$isFloating = ($fieldStyle === 'floating');
$asterisk = ($requiredIndicator === 'asterisk') ? ' <span class="text-danger">*</span>' : '';
?>
<div class="j2commerce-register-form">

    <div class="row g-3">
        <?php foreach ($fields as $field) : ?>
            <?php echo CustomFieldHelper::renderField($field); ?>
        <?php endforeach; ?>
    </div>

    <div class="j2commerce-checkout-password-container">
        <h5 class="mt-4 mb-3"><?php echo Text::_('COM_J2COMMERCE_CHECKOUT_SET_PASSWORD'); ?></h5>
        <div class="row g-3">
            <?php if ($isFloating) : ?>
                <div class="col-md-6">
                    <div class="form-floating">
                        <input type="password" name="password" id="password" class="form-control" required autocomplete="new-password" placeholder="<?php echo Text::_('COM_J2COMMERCE_CHECKOUT_ENTER_PASSWORD'); ?>" />
                        <label for="password"><?php echo Text::_('COM_J2COMMERCE_CHECKOUT_ENTER_PASSWORD'); ?><?php echo $asterisk; ?></label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-floating">
                        <input type="password" name="confirm" id="confirm" class="form-control" required autocomplete="new-password" placeholder="<?php echo Text::_('COM_J2COMMERCE_CHECKOUT_CONFIRM_PASSWORD'); ?>" />
                        <label for="confirm"><?php echo Text::_('COM_J2COMMERCE_CHECKOUT_CONFIRM_PASSWORD'); ?><?php echo $asterisk; ?></label>
                    </div>
                </div>
            <?php else : ?>
                <div class="col-md-6">
                    <div class="form-normal">
                        <label for="password" class="form-label"><?php echo Text::_('COM_J2COMMERCE_CHECKOUT_ENTER_PASSWORD'); ?><?php echo $asterisk; ?></label>
                        <input type="password" name="password" id="password" class="form-control" required autocomplete="new-password" />
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-normal">
                        <label for="confirm" class="form-label"><?php echo Text::_('COM_J2COMMERCE_CHECKOUT_CONFIRM_PASSWORD'); ?><?php echo $asterisk; ?></label>
                        <input type="password" name="confirm" id="confirm" class="form-control" required autocomplete="new-password" />
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($showShipping) : ?>
        <div class="mt-3 mb-3">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="shipping_address" value="1" id="shipping-same-as-billing" checked>
                <label class="form-check-label" for="shipping-same-as-billing">
                    <?php echo Text::_('COM_J2COMMERCE_CHECKOUT_SHIPPING_SAME_AS_BILLING'); ?>
                </label>
            </div>
        </div>
    <?php endif; ?>

    <?php echo J2CommerceHelper::plugin()->eventWithHtml('CheckoutRegister', [$this]); ?>

    <div class="mt-3">
        <button type="button" id="button-register" class="btn btn-primary">
            <?php echo Text::_('COM_J2COMMERCE_CHECKOUT_CONTINUE'); ?>
        </button>
    </div>
</div>
