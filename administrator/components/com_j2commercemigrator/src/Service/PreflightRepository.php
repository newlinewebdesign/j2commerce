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

class PreflightRepository
{
    public function __construct(private DatabaseInterface $db) {}

    public function upsert(int $runId, string $checkKey, string $label, string $status, ?string $detail = null): int
    {
        $now = (new \DateTime('now', new \DateTimeZone('UTC')))->format('Y-m-d H:i:s');

        $query = $this->db->getQuery(true)
            ->insert($this->db->quoteName('#__j2commerce_migrator_preflight'))
            ->columns($this->db->quoteName(['run_id', 'check_key', 'label', 'status', 'detail', 'checked_on']))
            ->values(':run_id, :check_key, :label, :status, :detail, :checked_on')
            ->bind(':run_id', $runId, ParameterType::INTEGER)
            ->bind(':check_key', $checkKey)
            ->bind(':label', $label)
            ->bind(':status', $status)
            ->bind(':detail', $detail)
            ->bind(':checked_on', $now);

        $this->db->setQuery($query)->execute();

        return (int) $this->db->insertid();
    }

    public function getByRun(int $runId): array
    {
        $query = $this->db->getQuery(true)
            ->select($this->db->quoteName([
                'j2commerce_migrator_preflight_id', 'run_id', 'check_key',
                'label', 'status', 'detail', 'checked_on',
            ]))
            ->from($this->db->quoteName('#__j2commerce_migrator_preflight'))
            ->where($this->db->quoteName('run_id') . ' = :run_id')
            ->bind(':run_id', $runId, ParameterType::INTEGER)
            ->order($this->db->quoteName('j2commerce_migrator_preflight_id') . ' ASC');

        return $this->db->setQuery($query)->loadObjectList() ?: [];
    }

    public function getStatusSummary(int $runId): array
    {
        $query = $this->db->getQuery(true)
            ->select([
                $this->db->quoteName('status'),
                'COUNT(*) AS ' . $this->db->quoteName('total'),
            ])
            ->from($this->db->quoteName('#__j2commerce_migrator_preflight'))
            ->where($this->db->quoteName('run_id') . ' = :run_id')
            ->bind(':run_id', $runId, ParameterType::INTEGER)
            ->group($this->db->quoteName('status'));

        $rows = $this->db->setQuery($query)->loadObjectList() ?: [];

        $summary = ['pass' => 0, 'warn' => 0, 'fail' => 0, 'skip' => 0];

        foreach ($rows as $row) {
            if (isset($summary[$row->status])) {
                $summary[$row->status] = (int) $row->total;
            }
        }

        return $summary;
    }

    public function deleteByRun(int $runId): void
    {
        $query = $this->db->getQuery(true)
            ->delete($this->db->quoteName('#__j2commerce_migrator_preflight'))
            ->where($this->db->quoteName('run_id') . ' = :run_id')
            ->bind(':run_id', $runId, ParameterType::INTEGER);

        $this->db->setQuery($query)->execute();
    }
}
