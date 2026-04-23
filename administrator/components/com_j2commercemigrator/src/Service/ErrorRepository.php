<?php

/**
 * @package     J2Commerce
 * @subpackage  com_j2commercemigrator
 *
 * @copyright   (C)2024-2026 J2Commerce, LLC <https://www.j2commerce.com>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace J2Commerce\Component\J2commercemigrator\Administrator\Service;

defined('_JEXEC') or die;

use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;

class ErrorRepository
{
    public function __construct(private DatabaseInterface $db) {}

    public function record(
        int $runId,
        string $adapter,
        string $sourceTable,
        ?int $sourceId,
        ?string $errorCode,
        string $errorMessage,
        ?string $context = null
    ): int {
        $now = (new \DateTime('now', new \DateTimeZone('UTC')))->format('Y-m-d H:i:s');

        $query = $this->db->getQuery(true)
            ->insert($this->db->quoteName('#__j2commerce_migrator_errors'))
            ->columns($this->db->quoteName([
                'run_id', 'adapter', 'source_table', 'source_id',
                'error_code', 'error_message', 'context', 'created_on',
            ]))
            ->values(':run_id, :adapter, :source_table, :source_id, :error_code, :error_message, :context, :created_on')
            ->bind(':run_id', $runId, ParameterType::INTEGER)
            ->bind(':adapter', $adapter)
            ->bind(':source_table', $sourceTable)
            ->bind(':source_id', $sourceId, ParameterType::INTEGER)
            ->bind(':error_code', $errorCode)
            ->bind(':error_message', $errorMessage)
            ->bind(':context', $context)
            ->bind(':created_on', $now);

        $this->db->setQuery($query)->execute();

        return (int) $this->db->insertid();
    }

    public function getByRun(int $runId, int $limit = 200, int $offset = 0): array
    {
        $query = $this->db->getQuery(true)
            ->select($this->db->quoteName([
                'j2commerce_migrator_error_id', 'run_id', 'adapter', 'source_table',
                'source_id', 'error_code', 'error_message', 'context', 'created_on',
            ]))
            ->from($this->db->quoteName('#__j2commerce_migrator_errors'))
            ->where($this->db->quoteName('run_id') . ' = :run_id')
            ->bind(':run_id', $runId, ParameterType::INTEGER)
            ->order($this->db->quoteName('j2commerce_migrator_error_id') . ' ASC')
            ->setLimit($limit, $offset);

        return $this->db->setQuery($query)->loadObjectList() ?: [];
    }

    public function countByRun(int $runId): int
    {
        $query = $this->db->getQuery(true)
            ->select('COUNT(*)')
            ->from($this->db->quoteName('#__j2commerce_migrator_errors'))
            ->where($this->db->quoteName('run_id') . ' = :run_id')
            ->bind(':run_id', $runId, ParameterType::INTEGER);

        return (int) $this->db->setQuery($query)->loadResult();
    }

    public function deleteByRun(int $runId): void
    {
        $query = $this->db->getQuery(true)
            ->delete($this->db->quoteName('#__j2commerce_migrator_errors'))
            ->where($this->db->quoteName('run_id') . ' = :run_id')
            ->bind(':run_id', $runId, ParameterType::INTEGER);

        $this->db->setQuery($query)->execute();
    }
}
