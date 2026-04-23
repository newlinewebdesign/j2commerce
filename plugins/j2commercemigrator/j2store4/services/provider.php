<?php

/**
 * @package     J2Commerce
 * @subpackage  plg_j2commercemigrator_j2store4
 *
 * @copyright   (C)2024-2026 J2Commerce, LLC <https://www.j2commerce.com>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

use J2Commerce\Plugin\J2commercemigrator\J2store4\Extension\J2store4;
use Joomla\CMS\Extension\PluginInterface;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Event\DispatcherInterface;

return new class implements ServiceProviderInterface
{
    public function register(Container $container): void
    {
        $container->set(
            PluginInterface::class,
            static function (Container $container) {
                return new J2store4(
                    $container->get(DispatcherInterface::class),
                    (array) PluginHelper::getPlugin('j2commercemigrator', 'j2store4')
                );
            }
        );
    }
};
