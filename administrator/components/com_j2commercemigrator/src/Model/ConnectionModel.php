<?php

/**
 * @package     J2Commerce
 * @subpackage  com_j2commercemigrator
 *
 * @copyright   (C)2024-2026 J2Commerce, LLC <https://www.j2commerce.com>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace J2Commerce\Component\J2commercemigrator\Administrator\Model;

defined('_JEXEC') or die;

use J2Commerce\Component\J2commercemigrator\Administrator\Service\ConnectionManager;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

/**
 * Connection model — wraps ConnectionManager for verify / clear / status operations.
 */
class ConnectionModel extends BaseDatabaseModel
{
    private function manager(): ConnectionManager
    {
        return new ConnectionManager(Factory::getApplication(), $this->getDatabase());
    }

    /** Validates credentials and stores the verified connection in session. */
    public function verify(array $credentials): array
    {
        return $this->manager()->verify($credentials);
    }

    /** Removes connection credentials from the session. */
    public function clear(): void
    {
        $this->manager()->clear();
    }

    /** Returns the current connection status from session. */
    public function getStatus(): array
    {
        $mgr = $this->manager();

        return [
            'ok'           => $mgr->isReady(),
            'status'       => $mgr->getStatus(),
            'pdoAvailable' => extension_loaded('pdo_mysql'),
        ];
    }

    /** Returns true when a verified connection is present in session. */
    public function isReady(): bool
    {
        return $this->manager()->isReady();
    }
}
