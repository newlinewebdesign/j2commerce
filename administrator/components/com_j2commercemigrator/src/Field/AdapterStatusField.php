<?php

/**
 * @package     J2Commerce
 * @subpackage  com_j2commercemigrator
 *
 * @copyright   (C)2024-2026 J2Commerce, LLC <https://www.j2commerce.com>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace J2Commerce\Component\J2commercemigrator\Administrator\Field;

defined('_JEXEC') or die;

use Joomla\CMS\Form\FormField;
use Joomla\CMS\Plugin\PluginHelper;

/**
 * Renders a Bootstrap 5 status pill indicating whether a migrator adapter plugin
 * is enabled, disabled, or missing (misconfigured).
 */
class AdapterStatusField extends FormField
{
    protected $type = 'AdapterStatus';

    protected function getInput(): string
    {
        $adapter    = (string) ($this->element['adapter'] ?? $this->value ?? '');
        $pluginName = 'j2commercemigrator_' . $adapter;

        $plugin = PluginHelper::getPlugin('j2commercemigrator', $adapter);

        if ($plugin === false || $plugin === null) {
            return $this->renderPill('danger', 'COM_J2COMMERCEMIGRATOR_ADAPTER_STATUS_MISSING');
        }

        if (!PluginHelper::isEnabled('j2commercemigrator', $adapter)) {
            return $this->renderPill('warning', 'COM_J2COMMERCEMIGRATOR_ADAPTER_STATUS_DISABLED');
        }

        return $this->renderPill('success', 'COM_J2COMMERCEMIGRATOR_ADAPTER_STATUS_ENABLED');
    }

    private function renderPill(string $contextClass, string $langKey): string
    {
        return sprintf(
            '<span class="badge bg-%s">%s</span>',
            htmlspecialchars($contextClass, ENT_COMPAT, 'UTF-8'),
            htmlspecialchars(\Joomla\CMS\Language\Text::_($langKey), ENT_COMPAT, 'UTF-8')
        );
    }

    protected function getLabel(): string
    {
        return '';
    }
}
