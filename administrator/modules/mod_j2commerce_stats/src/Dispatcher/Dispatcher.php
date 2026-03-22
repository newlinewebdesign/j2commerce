<?php

/**
 * @package     J2Commerce
 * @subpackage  mod_j2commerce_stats
 *
 * @copyright   (C) 2024-2026 J2Commerce, LLC <https://www.j2commerce.com>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace J2Commerce\Module\Stats\Administrator\Dispatcher;

use J2Commerce\Module\Stats\Administrator\Helper\StatsHelper;
use Joomla\CMS\Dispatcher\AbstractModuleDispatcher;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\HelperFactoryAwareInterface;
use Joomla\CMS\Helper\HelperFactoryAwareTrait;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Dispatcher class for mod_j2commerce_stats
 *
 * @since  6.0.0
 */
class Dispatcher extends AbstractModuleDispatcher implements HelperFactoryAwareInterface
{
    use HelperFactoryAwareTrait;

    protected function getLayoutData(): array
    {
        $app  = Factory::getApplication();
        $user = $app->getIdentity();

        // Check ACL - user must have permission to manage the component
        if (!$user->authorise('core.manage', 'com_j2commerce')) {
            return [];
        }

        $data = parent::getLayoutData();

        // Load the component language file for shared strings
        $app->getLanguage()->load('com_j2commerce', JPATH_ADMINISTRATOR);

        /** @var StatsHelper $helper */
        $helper = $this->getHelperFactory()->getHelper('StatsHelper');

        // Get order statuses from params (default to all)
        $orderStatuses = $data['params']->get('order_status', ['*']);

        // Ensure it's an array
        if (!\is_array($orderStatuses)) {
            $orderStatuses = [$orderStatuses];
        }

        // Get all statistics
        $data['stats'] = $helper->getAllStats($orderStatuses);

        return $data;
    }
}
