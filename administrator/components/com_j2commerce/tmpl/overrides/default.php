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
use Joomla\CMS\Session\Session;

/** @var \J2Commerce\Component\J2commerce\Administrator\View\Overrides\HtmlView $this */

$wa = $this->getDocument()->getWebAssetManager();
$wa->getRegistry()->addRegistryFile('media/com_templates/joomla.asset.json');
$wa->useScript('form.validate')
    ->useScript('keepalive')
    ->useScript('com_templates.admin-templates')
    ->useStyle('com_templates.admin-templates')
    ->useStyle('com_j2commerce.admin.css');

$activeTab = $this->activeTab;
?>

<?php echo $this->navbar; ?>

<div class="main-card mt-3">
    <?php echo HTMLHelper::_('uitab.startTabSet', 'overridesTabs', ['active' => $activeTab, 'breakpoint' => 768]); ?>

    <?php echo HTMLHelper::_('uitab.addTab', 'overridesTabs', 'overrides', Text::_('COM_J2COMMERCE_TAB_CREATE_OVERRIDES')); ?>
    <?php echo $this->loadTemplate('overrides'); ?>
    <?php echo HTMLHelper::_('uitab.endTab'); ?>

    <?php echo HTMLHelper::_('uitab.addTab', 'overridesTabs', 'editor', Text::_('COM_J2COMMERCE_TAB_EDITOR')); ?>
    <?php echo $this->loadTemplate('editor'); ?>
    <?php echo HTMLHelper::_('uitab.endTab'); ?>

    <?php echo HTMLHelper::_('uitab.addTab', 'overridesTabs', 'builder', Text::_('COM_J2COMMERCE_BUILDER_TAB')); ?>
    <?php echo $this->loadTemplate('builder'); ?>
    <?php echo HTMLHelper::_('uitab.endTab'); ?>

    <?php echo HTMLHelper::_('uitab.endTabSet'); ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const btn = document.getElementById('btn-open-visual-builder');
    if (!btn) return;

    btn.addEventListener('click', function() {
        const fileValue = this.dataset.builderFile;

        // Switch to the builder tab using Joomla's web component API
        const tabSet = document.querySelector('joomla-tab#overridesTabs');
        if (tabSet) {
            tabSet.activateTab(document.getElementById('builder'));
        }

        // Pre-select the file in the builder dropdown and trigger load
        const fileSelect = document.getElementById('builder-file-select');
        if (fileSelect && fileValue) {
            // Wait a tick for the tab to render
            setTimeout(function() {
                fileSelect.value = fileValue;
                fileSelect.dispatchEvent(new Event('change'));
            }, 200);
        }
    });
});
</script>

<?php echo $this->footer ?? ''; ?>
