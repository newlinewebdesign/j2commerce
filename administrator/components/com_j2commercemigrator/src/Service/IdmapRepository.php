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

class IdmapRepository
{
    public function __construct(private DatabaseInterface $db) {}

    public function lookupTarget(string $adapter, string $sourceTable, string $sourcePk): ?string
    {
        $query = $this->db->getQuery(true)
            ->select($this->db->quoteName('target_pk'))
            ->from($this->db->quoteName('#__j2commerce_migrator_idmap'))
            ->where($this->db->quoteName('adapter') . ' = :adapter')
            ->where($this->db->quoteName('source_table') . ' = :source_table')
            ->where($this->db->quoteName('source_pk') . ' = :source_pk')
            ->bind(':adapter', $adapter)
            ->bind(':source_table', $sourceTable)
            ->bind(':source_pk', $sourcePk);

        $result = $this->db->setQuery($query)->loadResult();
        return $result ?: null;
    }

    public function lookupSource(string $adapter, string $targetTable, string $targetPk): ?string
    {
        $query = $this->db->getQuery(true)
            ->select($this->db->quoteName('source_pk'))
            ->from($this->db->quoteName('#__j2commerce_migrator_idmap'))
            ->where($this->db->quoteName('adapter') . ' = :adapter')
            ->where($this->db->quoteName('target_table') . ' = :target_table')
            ->where($this->db->quoteName('target_pk') . ' = :target_pk')
            ->bind(':adapter', $adapter)
            ->bind(':target_table', $targetTable)
            ->bind(':target_pk', $targetPk);

        $result = $this->db->setQuery($query)->loadResult();
        return $result ?: null;
    }

    public function record(string $adapter, string $sourceTable, string $sourcePk, string $targetTable, string $targetPk): void
    {
        $now = (new \DateTime('now', new \DateTimeZone('UTC')))->format('Y-m-d H:i:s');

        $query = $this->db->getQuery(true)
            ->insert($this->db->quoteName('#__j2commerce_migrator_idmap'))
            ->columns($this->db->quoteName(['adapter', 'source_table', 'source_pk', 'target_table', 'target_pk', 'created_on']))
            ->values(':adapter, :source_table, :source_pk, :target_table, :target_pk, :created_on')
            ->bind(':adapter', $adapter)
            ->bind(':source_table', $sourceTable)
            ->bind(':source_pk', $sourcePk)
            ->bind(':target_table', $targetTable)
            ->bind(':target_pk', $targetPk)
            ->bind(':created_on', $now);

        try {
            $this->db->setQuery($query)->execute();
        } catch (\Throwable) {
            // Duplicate — ignore
        }
    }

    public function dropForAdapter(string $adapter): void
    {
        $query = $this->db->getQuery(true)
            ->delete($this->db->quoteName('#__j2commerce_migrator_idmap'))
            ->where($this->db->quoteName('adapter') . ' = :adapter')
            ->bind(':adapter', $adapter);

        $this->db->setQuery($query)->execute();
    }

    public function dropAll(): void
    {
        $this->db->truncateTable('#__j2commerce_migrator_idmap');
    }

    public function migrateFromLegacy(): int
    {
        $legacy = '#__j2commerce_migration_idmap';

        // Check if legacy table exists
        try {
            $exists = $this->db->setQuery("SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '" . $this->db->getPrefix() . 'j2commerce_migration_idmap' . "'")->loadResult();

            if (!$exists) {
                return 0;
            }
        } catch (\Throwable) {
            return 0;
        }

        $now = (new \DateTime('now', new \DateTimeZone('UTC')))->format('Y-m-d H:i:s');

        $sql = 'INSERT IGNORE INTO ' . $this->db->quoteName('#__j2commerce_migrator_idmap')
            . ' (adapter, source_table, source_pk, target_table, target_pk, created_on)'
            . ' SELECT \'j2store4\', source_table, source_pk, target_table, target_pk, '
            . $this->db->quote($now)
            . ' FROM ' . $this->db->quoteName($legacy);

        try {
            $this->db->setQuery($sql)->execute();
            return (int) $this->db->getAffectedRows();
        } catch (\Throwable) {
            return 0;
        }
    }
}
