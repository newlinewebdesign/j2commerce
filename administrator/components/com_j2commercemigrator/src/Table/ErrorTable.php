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

class ErrorTable extends Table
{
    public int $j2commerce_migrator_error_id = 0;
    public int $run_id = 0;
    public string $adapter = '';
    public string $source_table = '';
    public ?int $source_id = null;
    public ?string $error_code = null;
    public ?string $error_message = null;
    public ?string $context = null;
    public string $created_on = '';

    public function __construct(DatabaseInterface $db)
    {
        parent::__construct('#__j2commerce_migrator_errors', 'j2commerce_migrator_error_id', $db);
    }

    public function check(): bool
    {
        if ($this->run_id < 1) {
            $this->setError('run_id is required.');
            return false;
        }

        if (empty($this->adapter)) {
            $this->setError('Adapter key is required.');
            return false;
        }

        if (empty($this->source_table)) {
            $this->setError('source_table is required.');
            return false;
        }

        if (empty($this->created_on)) {
            $this->created_on = (new \DateTime('now', new \DateTimeZone('UTC')))->format('Y-m-d H:i:s');
        }

        return true;
    }
}
