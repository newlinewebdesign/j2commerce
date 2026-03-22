<?php

/**
 * @package     J2Commerce
 * @subpackage  com_j2commerce
 *
 * @copyright   (C)2024-2026 J2Commerce, LLC <https://www.j2commerce.com>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;

extract($displayData);

/**
 * Layout variables
 * -----------------
 * @var   Form    $form       The form instance for render the section
 * @var   string  $basegroup  The base group name
 * @var   string  $group      Current group name
 * @var   array   $buttons    Array of the buttons that will be rendered
 */

$allFields = $form->getGroup('');
$hidden  = [];
$visible = [];

foreach ($allFields as $field) {
    if ($field->type === 'Hidden' || $field->hidden) {
        $hidden[] = $field;
    } else {
        $visible[] = $field;
    }
}
?>

<tr class="subform-repeatable-group" data-base-name="<?php echo $basegroup; ?>" data-group="<?php echo $group; ?>">
    <?php
    $first = true;
    foreach ($visible as $field) : ?>
        <td data-column="<?php echo strip_tags($field->label); ?>">
            <?php echo $field->renderField(['hiddenLabel' => true, 'hiddenDescription' => true]); ?>
            <?php if ($first) :
                foreach ($hidden as $h) {
                    echo $h->renderField(['hiddenLabel' => true, 'hiddenDescription' => true]);
                }
                $first = false;
            endif; ?>
        </td>
    <?php endforeach; ?>
    <?php if (!empty($buttons)) : ?>
    <td>
        <div class="btn-group">
            <?php if (!empty($buttons['add'])) : ?>
                <button type="button" class="group-add btn btn-sm btn-success" aria-label="<?php echo Text::_('JGLOBAL_FIELD_ADD'); ?>">
                    <span class="icon-plus" aria-hidden="true"></span>
                </button>
            <?php endif; ?>
            <?php if (!empty($buttons['remove'])) : ?>
                <button type="button" class="group-remove btn btn-sm btn-danger" aria-label="<?php echo Text::_('JGLOBAL_FIELD_REMOVE'); ?>">
                    <span class="icon-minus" aria-hidden="true"></span>
                </button>
            <?php endif; ?>
            <?php if (!empty($buttons['move'])) : ?>
                <button type="button" class="group-move btn btn-sm btn-primary" aria-label="<?php echo Text::_('JGLOBAL_FIELD_MOVE'); ?>">
                    <span class="icon-arrows-alt" aria-hidden="true"></span>
                </button>
            <?php endif; ?>
        </div>
    </td>
    <?php endif; ?>
</tr>
