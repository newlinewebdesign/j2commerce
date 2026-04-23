<?php

/**
 * @package     J2Commerce
 * @subpackage  plg_j2commercemigrator_j2store4
 *
 * @copyright   (C)2024-2026 J2Commerce, LLC <https://www.j2commerce.com>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

use Joomla\CMS\Factory;
use Joomla\CMS\Installer\InstallerAdapter;
use Joomla\CMS\Installer\InstallerScriptInterface;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseInterface;

class Plg_J2commercemigrator_J2store4InstallerScript implements InstallerScriptInterface
{
    public function install(InstallerAdapter $adapter): bool
    {
        return true;
    }

    public function update(InstallerAdapter $adapter): bool
    {
        return true;
    }

    public function uninstall(InstallerAdapter $adapter): bool
    {
        return true;
    }

    public function preflight(string $type, InstallerAdapter $adapter): bool
    {
        if (!$this->isJ2CommerceInstalled()) {
            Factory::getApplication()->enqueueMessage(
                Text::_('PLG_J2COMMERCEMIGRATOR_J2STORE4_ERR_REQUIRES_J2COMMERCE'),
                'error'
            );

            return false;
        }

        if (!$this->isMigratorInstalled()) {
            Factory::getApplication()->enqueueMessage(
                Text::_('PLG_J2COMMERCEMIGRATOR_J2STORE4_ERR_REQUIRES_MIGRATOR'),
                'error'
            );

            return false;
        }

        return true;
    }

    public function postflight(string $type, InstallerAdapter $adapter): bool
    {
        return true;
    }

    private function isJ2CommerceInstalled(): bool
    {
        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true)
            ->select($db->quoteName('extension_id'))
            ->from($db->quoteName('#__extensions'))
            ->where($db->quoteName('element') . ' = ' . $db->quote('com_j2commerce'))
            ->where($db->quoteName('type') . ' = ' . $db->quote('component'));

        $db->setQuery($query);

        return (bool) $db->loadResult();
    }

    private function isMigratorInstalled(): bool
    {
        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true)
            ->select($db->quoteName('extension_id'))
            ->from($db->quoteName('#__extensions'))
            ->where($db->quoteName('element') . ' = ' . $db->quote('com_j2commercemigrator'))
            ->where($db->quoteName('type') . ' = ' . $db->quote('component'));

        $db->setQuery($query);

        return (bool) $db->loadResult();
    }
}
