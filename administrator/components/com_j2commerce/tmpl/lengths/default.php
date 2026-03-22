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

/** @var \J2Commerce\Component\J2commerce\Administrator\View\Lengths\HtmlView $this */

$wa = $this->getDocument()->getWebAssetManager();
$wa->useScript('table.columns')
    ->useScript('multiselect');

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));

?>
<?php echo $this->navbar; ?>

<form action="<?php echo Route::_('index.php?option=com_j2commerce&view=lengths'); ?>" method="post" name="adminForm" id="adminForm">
    <div class="row">
        <div class="col-md-12">
            <div id="j-main-container" class="j-main-container">
                <?php echo LayoutHelper::render('joomla.searchtools.default', ['view' => $this]); ?>

                <?php if (empty($this->items)) : ?>
                    <div class="alert alert-info">
                        <span class="icon-info-circle" aria-hidden="true"></span><span class="visually-hidden"><?php echo Text::_('INFO'); ?></span>
                        <?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
                    </div>
                <?php else : ?>
                    <table class="table itemList" id="lengthsList">
                        <caption class="visually-hidden">
                            <?php echo Text::_('COM_J2COMMERCE_LENGTHS'); ?>,
                            <span id="orderedBy"><?php echo Text::_('JGLOBAL_SORTED_BY'); ?></span>,
                            <span id="filteredBy"><?php echo Text::_('JGLOBAL_FILTERED_BY'); ?></span>
                        </caption>
                        <thead>
                            <tr>
                                <td class="w-1 text-center">
                                    <?php echo HTMLHelper::_('grid.checkall'); ?>
                                </td>
                                <th scope="col" class="w-1 text-center">
                                    <?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'a.enabled', $listDirn, $listOrder); ?>
                                </th>
                                <th scope="col">
                                    <?php echo HTMLHelper::_('searchtools.sort', 'COM_J2COMMERCE_HEADING_LENGTH_TITLE', 'a.length_title', $listDirn, $listOrder); ?>
                                </th>
                                <th scope="col" class="w-10 d-none d-md-table-cell">
                                    <?php echo HTMLHelper::_('searchtools.sort', 'COM_J2COMMERCE_HEADING_UNIT', 'a.length_unit', $listDirn, $listOrder); ?>
                                </th>
                                <th scope="col" class="w-10 d-none d-md-table-cell">
                                    <?php echo HTMLHelper::_('searchtools.sort', 'COM_J2COMMERCE_HEADING_VALUE', 'a.length_value', $listDirn, $listOrder); ?>
                                </th>
                                <th scope="col" class="w-5 d-none d-md-table-cell">
                                    <?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.j2commerce_length_id', $listDirn, $listOrder); ?>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($this->items as $i => $item) : ?>
                            <tr class="row<?php echo $i % 2; ?>">
                                <td class="text-center">
                                    <?php echo HTMLHelper::_('grid.id', $i, $item->j2commerce_length_id, false, 'cid', 'cb', $item->length_title); ?>
                                </td>
                                <td class="text-center">
                                    <?php echo HTMLHelper::_('jgrid.published', $item->enabled, $i, 'lengths.', true, 'cb'); ?>
                                </td>
                                <td>
                                    <a href="<?php echo Route::_('index.php?option=com_j2commerce&task=length.edit&id=' . $item->j2commerce_length_id); ?>">
                                        <?php echo $this->escape($item->length_title); ?>
                                    </a>
                                </td>
                                <td class="d-none d-md-table-cell">
                                    <?php echo $this->escape($item->length_unit); ?>
                                </td>
                                <td class="d-none d-md-table-cell">
                                    <?php echo number_format((float) $item->length_value, 8); ?>
                                </td>
                                <td class="d-none d-md-table-cell">
                                    <?php echo (int) $item->j2commerce_length_id; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>

                    <?php echo $this->pagination->getListFooter(); ?>
                <?php endif; ?>

                <input type="hidden" name="task" value="">
                <input type="hidden" name="boxchecked" value="0">
                <?php echo HTMLHelper::_('form.token'); ?>
            </div>
        </div>
    </div>
</form>

<?php echo $this->footer ?? ''; ?>
