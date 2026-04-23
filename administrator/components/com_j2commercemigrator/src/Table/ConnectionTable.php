<?php

/**
 * @package     J2Commerce
 * @subpackage  com_j2commercemigrator
 *
 * @copyright   (C)2024-2026 J2Commerce, LLC <https://www.j2commerce.com>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace J2Commerce\Component\J2commercemigrator\Administrator\Table;

defined('_JEXEC') or die;

use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseInterface;

class ConnectionTable extends Table
{
    public function __construct(DatabaseInterface $db)
    {
        parent::__construct('#__j2commerce_migrator_connections', 'j2commerce_migrator_connection_id', $db);
    }

    public function check(): bool
    {
        if (empty($this->adapter)) {
            $this->setError('Adapter key is required.');
            return false;
        }

        if (!in_array($this->connection_mode ?? 'A', ['A', 'B', 'C'], true)) {
            $this->connection_mode = 'A';
        }

        $port = (int) ($this->port ?? 3306);
        if ($port < 1 || $port > 65535) {
            $this->port = 3306;
        }

        return true;
    }
}
