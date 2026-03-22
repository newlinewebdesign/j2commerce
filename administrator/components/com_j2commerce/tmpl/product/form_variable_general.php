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

use J2Commerce\Component\J2commerce\Administrator\Helper\J2CommerceHelper;
use J2Commerce\Component\J2commerce\Administrator\Field\ManufacturersField;
use J2Commerce\Component\J2commerce\Administrator\Field\VendorsField;
use J2Commerce\Component\J2commerce\Administrator\Field\TaxProfileField;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;

$this->item        = $displayData['product'];
$this->form_prefix = $displayData['form_prefix'] ?? 'jform[attribs][j2commerce]';

$manufacturersField = new ManufacturersField();
$manufacturersField->setDatabase(Factory::getContainer()->get('DatabaseDriver'));
$element = new SimpleXMLElement('<field />');
$manufacturersField->setup($element, '');
$manufacturerTypes = $manufacturersField->getOptions();
array_unshift($manufacturerTypes, (object) ['value' => '', 'text' => Text::_('COM_J2COMMERCE_SELECT_MANUFACTURER')]);

$vendorsField = new VendorsField();
$vendorsField->setDatabase(Factory::getContainer()->get('DatabaseDriver'));
$element = new SimpleXMLElement('<field />');
$vendorsField->setup($element, '');
$vendorTypes = $vendorsField->getOptions();
array_unshift($vendorTypes, (object) ['value' => '', 'text' => Text::_('COM_J2COMMERCE_SELECT_VENDOR')]);

$taxprofilesField = new TaxProfileField();
$taxprofilesField->setDatabase(Factory::getContainer()->get('DatabaseDriver'));
$element = new SimpleXMLElement('<field />');
$taxprofilesField->setup($element, '');
$taxprofileTypes = $taxprofilesField->getOptions();
array_unshift($taxprofileTypes, (object) ['value' => '', 'text' => Text::_('COM_J2COMMERCE_SELECT_TAX_PROFILE')]);

$product_params = json_decode($this->item->params ?? '{}');
?>

<div class="j2commerce-product-general">
    <fieldset class="options-form">
        <legend><?php echo Text::_('COM_J2COMMERCE_PRODUCT_TAB_GENERAL'); ?></legend>
        <div class="form-grid">
            <div class="control-group">
                <div class="control-label">
                    <label id="j2commerce-variable-visibility-radio-group-lbl" for="j2commerce-variable-visibility-radio-group"><?php echo Text::_('COM_J2COMMERCE_PRODUCT_VISIBLE_STOREFRONT'); ?></label>
                </div>
                <div class="controls">
                    <?php echo LayoutHelper::render('joomla.form.field.radio.switcher', [
                        'name'    => $this->form_prefix . '[visibility]',
                        'id'      => 'j2commerce-variable-visibility-radio-group',
                        'value'   => $this->item->visibility,
                        'options' => [
                            (object) ['value' => 0, 'text' => Text::_('JNO')],
                            (object) ['value' => 1, 'text' => Text::_('JYES')],
                        ],
                    ]); ?>
                </div>
            </div>

            <div class="control-group align-items-center">
                <div class="control-label">
                    <label id="j2commerce-variable-manufacturer-select-group-lbl" for="j2commerce-variable-manufacturer-select-group"><?php echo Text::_('COM_J2COMMERCE_COUPON_MANUFACTURER'); ?></label>
                </div>
                <div class="controls">
                    <?php echo LayoutHelper::render('joomla.form.field.list-fancy-select', [
                        'name'    => $this->form_prefix . '[manufacturer_id]',
                        'id'      => 'j2commerce-variable-manufacturer-select-group',
                        'value'   => $this->item->manufacturer_id,
                        'options' => $manufacturerTypes,
                    ]); ?>
                </div>
            </div>

            <div class="control-group align-items-center">
                <div class="control-label">
                    <label id="j2commerce-variable-vendor-select-group-lbl" for="j2commerce-variable-vendor-select-group"><?php echo Text::_('COM_J2COMMERCE_PRODUCT_VENDOR'); ?></label>
                </div>
                <div class="controls">
                    <?php echo LayoutHelper::render('joomla.form.field.list-fancy-select', [
                        'name'    => $this->form_prefix . '[vendor_id]',
                        'id'      => 'j2commerce-variable-vendor-select-group',
                        'value'   => $this->item->vendor_id,
                        'options' => $vendorTypes,
                    ]); ?>
                </div>
            </div>

            <div class="control-group align-items-center">
                <div class="control-label">
                    <label id="j2commerce-variable-taxprofile-select-group-lbl" for="j2commerce-variable-taxprofile-select-group"><?php echo Text::_('COM_J2COMMERCE_PRODUCT_TAX_PROFILE'); ?></label>
                </div>
                <div class="controls">
                    <?php echo LayoutHelper::render('joomla.form.field.list-fancy-select', [
                        'name'    => $this->form_prefix . '[taxprofile_id]',
                        'id'      => 'j2commerce-variable-taxprofile-select-group',
                        'value'   => $this->item->taxprofile_id,
                        'options' => $taxprofileTypes,
                    ]); ?>
                </div>
            </div>

            <div class="control-group align-items-center">
                <div class="control-label">
                    <label id="j2commerce-variable-addtocart_text-group-lbl" for="j2commerce-variable-addtocart_text-group"><?php echo Text::_('COM_J2COMMERCE_PRODUCT_CART_TEXT'); ?></label>
                </div>
                <div class="controls">
                    <?php echo LayoutHelper::render('joomla.form.field.text', [
                        'name'  => $this->form_prefix . '[addtocart_text]',
                        'id'    => 'j2commerce-variable-addtocart_text-group',
                        'value' => Text::_($this->item->addtocart_text),
                        'class' => 'form-control',
                    ]); ?>
                </div>
            </div>

            <div class="control-group align-items-center">
                <div class="control-label">
                    <label id="j2commerce-variable-product_css_class-group-lbl" for="j2commerce-variable-product_css_class-group"><?php echo Text::_('COM_J2COMMERCE_PRODUCT_CUSTOM_CSS_CLASS'); ?></label>
                </div>
                <div class="controls">
                    <?php echo LayoutHelper::render('joomla.form.field.text', [
                        'name'  => $this->form_prefix . '[product_css_class]',
                        'id'    => 'j2commerce-variable-product_css_class-group',
                        'value' => $product_params->product_css_class ?? '',
                        'class' => 'form-control',
                    ]); ?>
                </div>
            </div>

            <?php echo J2CommerceHelper::plugin()->eventWithHtml('AfterProductGeneralEdit', [$this, $this->item, $this->form_prefix])->getArgument('html', ''); ?>
        </div>
    </fieldset>
</div>
