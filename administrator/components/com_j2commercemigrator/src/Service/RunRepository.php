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

use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;

class RunRepository
{
    public function __construct(private DatabaseInterface $db) {}

    public function create(string $adapter, string $conflictMode, int $batchSize, int $userId, array $connectionMeta = []): int
    {
        $now    = (new \DateTime('now', new \DateTimeZone('UTC')))->format('Y-m-d H:i:s');
        $counts = json_encode(['inserted' => 0, 'skipped' => 0, 'overwritten' => 0, 'merged' => 0, 'errors' => 0]);
        $meta   = json_encode($connectionMeta);

        $query = $this->db->getQuery(true)
            ->insert($this->db->quoteName('#__j2commerce_migrator_runs'))
            ->columns($this->db->quoteName([
                'adapter', 'status', 'tier', 'conflict_mode', 'batch_size',
                'started_on', 'user_id', 'counts', 'error_count', 'connection_meta',
            ]))
            ->values(':adapter, :status, :tier, :conflict_mode, :batch_size, :started_on, :user_id, :counts, :error_count, :connection_meta')
            ->bind(':adapter', $adapter)
            ->bind(':status', 'running')
            ->bind(':tier', 0, ParameterType::INTEGER)
            ->bind(':conflict_mode', $conflictMode)
            ->bind(':batch_size', $batchSize, ParameterType::INTEGER)
            ->bind(':started_on', $now)
            ->bind(':user_id', $userId, ParameterType::INTEGER)
            ->bind(':counts', $counts)
            ->bind(':error_count', 0, ParameterType::INTEGER)
            ->bind(':connection_meta', $meta);

        $this->db->setQuery($query)->execute();

        return (int) $this->db->insertid();
    }

    public function finish(int $runId, string $status, array $counts, int $errorCount): void
    {
        $now       = (new \DateTime('now', new \DateTimeZone('UTC')))->format('Y-m-d H:i:s');
        $countsJson = json_encode($counts);

        $query = $this->db->getQuery(true)
            ->update($this->db->quoteName('#__j2commerce_migrator_runs'))
            ->set($this->db->quoteName('status') . ' = :status')
            ->set($this->db->quoteName('finished_on') . ' = :finished_on')
            ->set($this->db->quoteName('counts') . ' = :counts')
            ->set($this->db->quoteName('error_count') . ' = :error_count')
            ->where($this->db->quoteName('j2commerce_migrator_run_id') . ' = :id')
            ->bind(':status', $status)
            ->bind(':finished_on', $now)
            ->bind(':counts', $countsJson)
            ->bind(':error_count', $errorCount, ParameterType::INTEGER)
            ->bind(':id', $runId, ParameterType::INTEGER);

        $this->db->setQuery($query)->execute();
    }

    public function getList(int $limit = 50, int $offset = 0): array
    {
        $query = $this->db->getQuery(true)
            ->select([
                $this->db->quoteName('j2commerce_migrator_run_id'),
                $this->db->quoteName('adapter'),
                $this->db->quoteName('status'),
                $this->db->quoteName('conflict_mode'),
                $this->db->quoteName('batch_size'),
                $this->db->quoteName('started_on'),
                $this->db->quoteName('finished_on'),
                $this->db->quoteName('user_id'),
                $this->db->quoteName('counts'),
                $this->db->quoteName('error_count'),
            ])
            ->from($this->db->quoteName('#__j2commerce_migrator_runs'))
            ->order($this->db->quoteName('started_on') . ' DESC')
            ->setLimit($limit, $offset);

        return $this->db->setQuery($query)->loadObjectList() ?: [];
    }

    public function getById(int $runId): ?object
    {
        $query = $this->db->getQuery(true)
            ->select('*')
            ->from($this->db->quoteName('#__j2commerce_migrator_runs'))
            ->where($this->db->quoteName('j2commerce_migrator_run_id') . ' = :id')
            ->bind(':id', $runId, ParameterType::INTEGER);

        return $this->db->setQuery($query)->loadObject() ?: null;
    }

    public function delete(int $runId): void
    {
        $query = $this->db->getQuery(true)
            ->delete($this->db->quoteName('#__j2commerce_migrator_runs'))
            ->where($this->db->quoteName('j2commerce_migrator_run_id') . ' = :id')
            ->bind(':id', $runId, ParameterType::INTEGER);

        $this->db->setQuery($query)->execute();
    }
}
