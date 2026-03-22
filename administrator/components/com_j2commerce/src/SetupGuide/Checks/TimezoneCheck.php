<?php

declare(strict_types=1);

/**
 * @package     J2Commerce
 * @subpackage  com_j2commerce
 *
 * @copyright   (C)2024-2026 J2Commerce, LLC <https://www.j2commerce.com>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace J2Commerce\Component\J2commerce\Administrator\SetupGuide\Checks;

use J2Commerce\Component\J2commerce\Administrator\SetupGuide\AbstractSetupCheck;
use J2Commerce\Component\J2commerce\Administrator\SetupGuide\SetupCheckResult;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

defined('_JEXEC') or die;

class TimezoneCheck extends AbstractSetupCheck
{
    public function getId(): string
    {
        return 'timezone';
    }

    public function getGroup(): string
    {
        return 'store_identity';
    }

    public function getGroupOrder(): int
    {
        return 100;
    }

    public function getLabel(): string
    {
        return Text::_('COM_J2COMMERCE_SETUP_GUIDE_CHECK_TIMEZONE');
    }

    public function getDescription(): string
    {
        return Text::_('COM_J2COMMERCE_SETUP_GUIDE_CHECK_TIMEZONE_DESC');
    }

    public function check(): SetupCheckResult
    {
        $timezone = Factory::getApplication()->get('offset', 'UTC');

        if ($timezone === 'UTC') {
            return new SetupCheckResult(
                'warning',
                Text::_('COM_J2COMMERCE_SETUP_GUIDE_CHECK_TIMEZONE_WARNING'),
                ['timezone' => $timezone]
            );
        }

        return new SetupCheckResult(
            'pass',
            Text::sprintf('COM_J2COMMERCE_SETUP_GUIDE_CHECK_TIMEZONE_PASS', $timezone),
            ['timezone' => $timezone]
        );
    }

    public function getDetailView(): string
    {
        $timezone     = Factory::getApplication()->get('offset', 'UTC');
        $globalConfig = 'index.php?option=com_config';

        try {
            $tz      = new \DateTimeZone($timezone);
            $now     = new \DateTime('now', $tz);
            $timeStr = $now->format('g:i A');
        } catch (\Exception) {
            $timeStr = '';
        }

        $html = '<h5>' . Text::_('COM_J2COMMERCE_SETUP_GUIDE_CHECK_TIMEZONE') . '</h5>'
            . '<p>' . Text::_('COM_J2COMMERCE_SETUP_GUIDE_CHECK_TIMEZONE_DESC') . '</p>'
            . '<p class="text-muted small mb-1">' . Text::_('COM_J2COMMERCE_SETUP_GUIDE_CURRENT_VALUE') . '</p>'
            . '<p class="fw-semibold">' . htmlspecialchars($timezone) . '</p>';

        if ($timeStr !== '') {
            $html .= '<div class="text-center my-3">'
                . '<span class="fa-regular fa-clock" style="font-size:3rem;" aria-hidden="true"></span>'
                . '<p class="fw-bold fs-4 mb-0 mt-2">' . $timeStr . '</p>'
                . '<p class="text-muted small">' . Text::_('COM_J2COMMERCE_SETUP_GUIDE_CHECK_TIMEZONE_CURRENT_TIME') . '</p>'
                . '</div>';
        }

        $html .= '<a href="' . $globalConfig . '" class="btn btn-primary w-100">'
            . Text::_('COM_J2COMMERCE_SETUP_GUIDE_ACTION_GLOBAL_CONFIG')
            . '</a>';

        return $html;
    }
}
