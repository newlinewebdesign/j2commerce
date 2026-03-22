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

/** @var \J2Commerce\Component\J2commerce\Administrator\View\Taxrule\HtmlView $this */

$wa = $this->getDocument()->getWebAssetManager();
$wa->useScript('keepalive')
    ->useScript('form.validate');

?>
<form action="<?php echo Route::_('index.php?option=com_j2commerce&layout=edit&id=' . (int) $this->item->j2commerce_taxrule_id); ?>" method="post" name="adminForm" id="taxrule-form" class="form-validate">

    <div class="main-card">
        <?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', ['active' => 'details', 'recall' => true, 'breakpoint' => 768]); ?>

        <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'details', Text::_('COM_J2COMMERCE_FIELDSET_BASIC')); ?>
        <div class="row">
            <div class="col-lg-9">
                <fieldset id="fieldset-basic" class="options-form">
                    <legend><?php echo Text::_('COM_J2COMMERCE_FIELDSET_BASIC'); ?></legend>
                    <div class="form-grid">
                        <?php echo $this->form->renderField('taxprofile_id'); ?>
                        <?php echo $this->form->renderField('taxrate_id'); ?>
                        <?php echo $this->form->renderField('address'); ?>
                        <?php echo $this->form->renderField('ordering'); ?>
                    </div>
                </fieldset>
            </div>
        </div>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>

        <?php echo HTMLHelper::_('uitab.endTabSet'); ?>
    </div>

    <input type="hidden" name="task" value="">
    <?php echo $this->form->renderField('j2commerce_taxrule_id'); ?>
    <?php echo HTMLHelper::_('form.token'); ?>
</form>