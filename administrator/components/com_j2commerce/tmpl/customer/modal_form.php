<?php

/**
 * @package     J2Commerce
 * @subpackage  com_j2commerce
 *
 * @copyright   (C)2024-2026 J2Commerce, LLC <https://www.j2commerce.com>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 *
 * Address modal form fragment — returned by CustomerController::ajaxGetAddressForm().
 *
 * Expected in scope:
 *   @var \Joomla\CMS\Form\Form $form
 *   @var int                   $addressId
 *   @var int                   $userId
 *   @var array                 $customFields
 *   @var array                 $coreFields
 */

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;

// Resolve the Address Type value so we can render the select manually in a col-12 wrapper
// at the top of the form.
$typeField = $form->getField('type');
$typeValue = $typeField ? $typeField->value : 'billing';
if (empty($typeValue)) {
    $typeValue = 'billing';
}
?>
<form id="j2commerce-address-modal-form" class="form-validate j2commerce-address-modal-form">
    <input type="hidden" name="jform[j2commerce_address_id]" value="<?php echo (int) $addressId; ?>">
    <input type="hidden" name="jform[user_id]" value="<?php echo (int) $userId; ?>">

    <fieldset class="options-form">
        <div class="form-grid">
            <div class="control-group">
                <div class="control-label">
                    <label for="jform_type"><?php echo Text::_('COM_J2COMMERCE_FIELD_ADDRESS_TYPE'); ?></label>
                </div>
                <div class="controls">
                    <select id="jform_type" name="jform[type]" class="form-select">
                        <option value="billing"<?php echo $typeValue === 'billing' ? ' selected="selected"' : ''; ?>>
                            <?php echo Text::_('COM_J2COMMERCE_ADDRESS_TYPE_BILLING'); ?>
                        </option>
                        <option value="shipping"<?php echo $typeValue === 'shipping' ? ' selected="selected"' : ''; ?>>
                            <?php echo Text::_('COM_J2COMMERCE_ADDRESS_TYPE_SHIPPING'); ?>
                        </option>
                    </select>
                </div>
            </div>

            <?php echo $form->renderField('first_name'); ?>
            <?php echo $form->renderField('last_name'); ?>
            <?php echo $form->renderField('email'); ?>
            <?php echo $form->renderField('company'); ?>
            <?php echo $form->renderField('address_1'); ?>
            <?php echo $form->renderField('address_2'); ?>
            <?php echo $form->renderField('city'); ?>
            <?php echo $form->renderField('zip'); ?>
            <?php echo $form->renderField('country_id'); ?>
            <?php echo $form->renderField('zone_id'); ?>
            <?php echo $form->renderField('phone_1'); ?>
            <?php echo $form->renderField('phone_2'); ?>
            <?php echo $form->renderField('tax_number'); ?>

            <?php
            // Render any additional (custom) address fields after tax_number.
            foreach ($customFields as $field) {
                if (\in_array($field->field_namekey, $coreFields, true)) {
                    continue;
                }

                if ($form->getField($field->field_namekey)) {
                    echo $form->renderField($field->field_namekey);
                }
            }
            ?>
        </div>
    </fieldset>

    <input type="hidden" name="<?php echo Session::getFormToken(); ?>" value="1">
</form>
