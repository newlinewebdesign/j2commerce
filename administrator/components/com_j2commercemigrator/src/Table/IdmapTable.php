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

class IdmapTable extends Table
{
    public int $j2commerce_migrator_idmap_id = 0;
    public string $adapter = '';
    public string $entity = '';
    public int $source_id = 0;
    public int $target_id = 0;

    public function __construct(DatabaseInterface $db)
    {
        parent::__construct('#__j2commerce_migrator_idmap', 'j2commerce_migrator_idmap_id', $db);
    }

    public function check(): bool
    {
        if (empty($this->adapter)) {
            $this->setError('Adapter key is required.');
            return false;
        }

        if (empty($this->entity)) {
            $this->setError('Entity name is required.');
            return false;
        }

        return true;
    }
}
