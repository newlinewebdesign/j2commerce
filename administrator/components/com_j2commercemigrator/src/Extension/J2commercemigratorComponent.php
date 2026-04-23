<?php

/**
 * @package     J2Commerce
 * @subpackage  com_j2commercemigrator
 *
 * @copyright   (C)2024-2026 J2Commerce, LLC <https://www.j2commerce.com>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace J2Commerce\Component\J2commercemigrator\Administrator\Extension;

use J2Commerce\Component\J2commercemigrator\Administrator\CliCommand\AuditCommand;
use J2Commerce\Component\J2commercemigrator\Administrator\CliCommand\MigrateCommand;
use J2Commerce\Component\J2commercemigrator\Administrator\CliCommand\ResetCommand;
use J2Commerce\Component\J2commercemigrator\Administrator\CliCommand\VerifyCommand;
use Joomla\Application\ApplicationEvents;
use Joomla\CMS\Application\ConsoleApplication;
use Joomla\CMS\Console\Loader\WritableLoaderInterface;
use Joomla\CMS\Extension\BootableExtensionInterface;
use Joomla\CMS\Extension\MVCComponent;
use Joomla\CMS\Factory;
use Joomla\Event\Event;
use Joomla\Event\SubscriberInterface;
use Psr\Container\ContainerInterface;

final class J2commercemigratorComponent extends MVCComponent implements BootableExtensionInterface, SubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            ApplicationEvents::BEFORE_EXECUTE => 'registerCliCommands',
        ];
    }

    public function boot(ContainerInterface $container): void
    {
        $dispatcher = Factory::getApplication()->getDispatcher();
        $dispatcher->addSubscriber($this);
    }

    public function registerCliCommands(Event $event): void
    {
        if (!($event->getApplication() instanceof ConsoleApplication)) {
            return;
        }

        $loader = Factory::getContainer()->get(WritableLoaderInterface::class);
        $loader->add('j2commerce:migrator:migrate', MigrateCommand::class);
        $loader->add('j2commerce:migrator:audit', AuditCommand::class);
        $loader->add('j2commerce:migrator:verify', VerifyCommand::class);
        $loader->add('j2commerce:migrator:reset', ResetCommand::class);
    }
}
