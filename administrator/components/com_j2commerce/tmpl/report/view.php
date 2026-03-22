<?php
/**
 * @package     J2Commerce
 * @subpackage  com_j2commerce
 *
 * @copyright   (C)2024-2026 J2Commerce, LLC <https://www.j2commerce.com>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;

/** @var \J2Commerce\Component\J2commerce\Administrator\View\Report\HtmlView $this */

$app = Factory::getApplication();
$row = $this->item;

// Load the report plugin's language file
$app->getLanguage()->load('plg_j2commerce_' . $row->element, JPATH_PLUGINS . '/j2commerce/' . $row->element, null, true);

echo $this->navbar ?? '';

?>

<h3><?php echo Text::_($row->name); ?></h3>

<?php
// Dispatch onJ2CommerceGetReportView to let the report plugin render its content
PluginHelper::importPlugin('j2commerce');

$event = new \Joomla\Event\Event('onJ2CommerceGetReportView', [$row]);
$app->getDispatcher()->dispatch('onJ2CommerceGetReportView', $event);
$results = $event->getArgument('result', []);

$html = '';
foreach ($results as $result) {
    if (is_string($result)) {
        $html .= $result;
    }
}

if (!empty($html)) {
    echo $html;
} else {
    echo '<div class="alert alert-info">' . Text::_('COM_J2COMMERCE_REPORT_NO_CONTENT') . '</div>';
}

<?php echo $this->footer ?? ''; ?>
