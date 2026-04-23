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

class PreflightTable extends Table
{
    public function __construct(DatabaseInterface $db)
    {
        parent::__construct('#__j2commerce_migrator_preflight', 'j2commerce_migrator_preflight_id', $db);
    }

    public function check(): bool
    {
        if (($this->run_id ?? 0) < 1) {
            $this->setError('run_id is required.');
            return false;
        }

        if (empty($this->check_key)) {
            $this->setError('check_key is required.');
            return false;
        }

        if (empty($this->label)) {
            $this->setError('label is required.');
            return false;
        }

        if (empty($this->checked_on)) {
            $this->checked_on = (new \DateTime('now', new \DateTimeZone('UTC')))->format('Y-m-d H:i:s');
        }

        return true;
    }
}
