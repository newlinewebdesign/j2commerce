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
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

/** @var \J2Commerce\Component\J2commerce\Administrator\View\Currency\HtmlView $this */

$wa = $this->getDocument()->getWebAssetManager();
$wa->useScript('keepalive')
    ->useScript('form.validate');

?>
<form action="<?php echo Route::_('index.php?option=com_j2commerce&layout=edit&id=' . (int) $this->item->j2commerce_currency_id); ?>" method="post" name="adminForm" id="currency-form" class="form-validate">

    <div class="row title-alias form-vertical mb-3">
        <div class="col-12 col-lg-6">
            <?php echo $this->form->renderField('currency_title'); ?>
        </div>
    </div>

    <div class="main-card">
        <?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', ['active' => 'details', 'recall' => true, 'breakpoint' => 768]); ?>

        <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'details', Text::_('COM_J2COMMERCE_FIELDSET_BASIC')); ?>
        <div class="row">
            <div class="col-lg-9">
                <fieldset id="fieldset-basic" class="options-form">
                    <legend><?php echo Text::_('COM_J2COMMERCE_FIELDSET_BASIC'); ?></legend>
                    <div class="form-grid">
                        <?php echo $this->form->renderField('currency_code'); ?>
                        <?php echo $this->form->renderField('currency_symbol'); ?>
                        <?php echo $this->form->renderField('currency_position'); ?>
                        <?php echo $this->form->renderField('currency_num_decimals'); ?>
                        <?php echo $this->form->renderField('currency_decimal'); ?>
                        <?php echo $this->form->renderField('currency_thousands'); ?>
                        <?php echo $this->form->renderField('currency_value'); ?>
                    </div>
                </fieldset>
            </div>
            <div class="col-lg-3">
                <?php echo LayoutHelper::render('joomla.edit.global', $this); ?>
            </div>
        </div>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>

        <?php echo HTMLHelper::_('uitab.endTabSet'); ?>
    </div>

    <input type="hidden" name="task" value="">
    <?php echo $this->form->renderField('j2commerce_currency_id'); ?>
    <?php echo HTMLHelper::_('form.token'); ?>
</form>