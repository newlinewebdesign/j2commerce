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

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;

/** @var \J2Commerce\Component\J2commerce\Administrator\View\Zones\HtmlView $this */

$app = Factory::getApplication();

$function  = $app->input->getCmd('function', 'jSelectZone');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$onclick   = $this->escape($function);

$wa = $this->getDocument()->getWebAssetManager();
$wa->useScript('table.columns')
    ->useScript('multiselect');

?>
<div class="container-popup">
    <form action="<?php echo Route::_('index.php?option=com_j2commerce&view=zones&layout=modal&tmpl=component&function=' . $function . '&' . Session::getFormToken() . '=1'); ?>" method="post" name="adminForm" id="adminForm">

        <?php echo LayoutHelper::render('joomla.searchtools.default', ['view' => $this, 'options' => ['filterButton' => true]]); ?>

        <?php if (empty($this->items)) : ?>
            <div class="alert alert-info">
                <span class="icon-info-circle" aria-hidden="true"></span><span class="visually-hidden"><?php echo Text::_('INFO'); ?></span>
                <?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
            </div>
        <?php else : ?>
            <table class="table table-sm">
                <caption class="visually-hidden">
                    <?php echo Text::_('COM_J2COMMERCE_ZONES'); ?>,
                    <span id="orderedBy"><?php echo Text::_('JGLOBAL_SORTED_BY'); ?></span>,
                    <span id="filteredBy"><?php echo Text::_('JGLOBAL_FILTERED_BY'); ?></span>
                </caption>
                <thead>
                    <tr>
                        <th scope="col">
                            <?php echo HTMLHelper::_('searchtools.sort', 'COM_J2COMMERCE_HEADING_ZONE_NAME', 'a.zone_name', $listDirn, $listOrder); ?>
                        </th>
                        <th scope="col" class="w-15">
                            <?php echo HTMLHelper::_('searchtools.sort', 'COM_J2COMMERCE_HEADING_ZONE_CODE', 'a.zone_code', $listDirn, $listOrder); ?>
                        </th>
                        <th scope="col" class="w-25">
                            <?php echo HTMLHelper::_('searchtools.sort', 'COM_J2COMMERCE_HEADING_COUNTRY_NAME', 'c.country_name', $listDirn, $listOrder); ?>
                        </th>
                        <th scope="col" class="w-10">
                            <?php echo Text::_('JGRID_HEADING_ID'); ?>
                        </th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($this->items as $i => $item) : ?>
                    <tr class="row<?php echo $i % 2; ?>">
                        <th scope="row">
                            <a class="select-link" href="javascript:void(0)" data-function="<?php echo $onclick; ?>" data-id="<?php echo (int) $item->j2commerce_zone_id; ?>" data-title="<?php echo $this->escape($item->zone_name); ?>" data-country="<?php echo (int) $item->country_id; ?>">
                                <?php echo $this->escape($item->zone_name); ?>
                            </a>
                        </th>
                        <td>
                            <?php echo $this->escape($item->zone_code); ?>
                        </td>
                        <td>
                            <?php echo $this->escape($item->country_name); ?>
                        </td>
                        <td>
                            <?php echo (int) $item->j2commerce_zone_id; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>

            <?php echo $this->pagination->getListFooter(); ?>
        <?php endif; ?>

        <input type="hidden" name="task" value="">
        <input type="hidden" name="boxchecked" value="0">
        <input type="hidden" name="forcedLanguage" value="<?php echo $app->input->get('forcedLanguage', '', 'CMD'); ?>">
        <?php echo HTMLHelper::_('form.token'); ?>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.select-link').forEach(function(element) {
        element.addEventListener('click', function(event) {
            event.preventDefault();
            const functionName = this.getAttribute('data-function');
            const zoneId = this.getAttribute('data-id');
            const zoneTitle = this.getAttribute('data-title');
            const countryId = this.getAttribute('data-country');

            if (window.parent[functionName]) {
                window.parent[functionName](zoneId, zoneTitle, countryId);
            }

            if (window.parent.Joomla && window.parent.Joomla.Modal) {
                window.parent.Joomla.Modal.getCurrent().close();
            }
        });
    });
});
</script>