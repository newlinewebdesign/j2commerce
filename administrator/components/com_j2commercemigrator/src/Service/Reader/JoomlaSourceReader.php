<?php

/**
 * @package     J2Commerce
 * @subpackage  com_j2commercemigrator
 *
 * @copyright   (C)2024-2026 J2Commerce, LLC <https://www.j2commerce.com>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace J2Commerce\Component\J2commercemigrator\Administrator\Service\Reader;

use Joomla\Database\DatabaseInterface;

class JoomlaSourceReader implements SourceDatabaseReaderInterface
{
    public function __construct(private DatabaseInterface $db) {}

    public function count(string $bareTable, string $whereRawSql = '', array $params = []): int
    {
        try {
            $query = $this->db->getQuery(true)
                ->select('COUNT(*)')
                ->from($this->db->quoteName('#__' . $bareTable));

            if ($whereRawSql !== '') {
                $query->where($whereRawSql);
                foreach ($params as $key => $val) {
                    $query->bind($key, $val);
                }
            }

            return (int) $this->db->setQuery($query)->loadResult();
        } catch (\Throwable) {
            return 0;
        }
    }

    public function describe(string $bareTable): array
    {
        try {
            $resolvedTable = $this->db->getPrefix() . $bareTable;

            $query = $this->db->getQuery(true)
                ->select([
                    $this->db->quoteName('COLUMN_NAME', 'Field'),
                    $this->db->quoteName('COLUMN_TYPE', 'Type'),
                    $this->db->quoteName('IS_NULLABLE', 'Null'),
                    $this->db->quoteName('COLUMN_KEY', 'Key'),
                    $this->db->quoteName('COLUMN_DEFAULT', 'Default'),
                    $this->db->quoteName('EXTRA', 'Extra'),
                ])
                ->from($this->db->quoteName('INFORMATION_SCHEMA.COLUMNS'))
                ->where($this->db->quoteName('TABLE_SCHEMA') . ' = DATABASE()')
                ->where($this->db->quoteName('TABLE_NAME') . ' = :tableName')
                ->order($this->db->quoteName('ORDINAL_POSITION'))
                ->bind(':tableName', $resolvedTable);

            return $this->db->setQuery($query)->loadAssocList();
        } catch (\Throwable) {
            return [];
        }
    }

    public function listTables(string $likePattern): array
    {
        try {
            $prefix = $this->db->getPrefix();
            $full   = $prefix . $likePattern;

            $query = $this->db->getQuery(true)
                ->select('TABLE_NAME')
                ->from('INFORMATION_SCHEMA.TABLES')
                ->where('TABLE_SCHEMA = DATABASE()')
                ->where($this->db->quoteName('TABLE_NAME') . ' LIKE :pattern')
                ->bind(':pattern', $full);

            $rows = $this->db->setQuery($query)->loadColumn();

            return array_map(
                static fn(string $t) => str_replace($prefix, '', $t),
                $rows
            );
        } catch (\Throwable) {
            return [];
        }
    }

    public function getPrimaryKey(string $bareTable): ?string
    {
        try {
            $resolvedTable = $this->db->getPrefix() . $bareTable;

            $query = $this->db->getQuery(true)
                ->select($this->db->quoteName('COLUMN_NAME'))
                ->from($this->db->quoteName('INFORMATION_SCHEMA.COLUMNS'))
                ->where($this->db->quoteName('TABLE_SCHEMA') . ' = DATABASE()')
                ->where($this->db->quoteName('TABLE_NAME') . ' = :tableName')
                ->where($this->db->quoteName('COLUMN_KEY') . ' = ' . $this->db->quote('PRI'))
                ->bind(':tableName', $resolvedTable);

            $pk = $this->db->setQuery($query)->loadResult();

            return $pk ?: null;
        } catch (\Throwable) {
            return null;
        }
    }

    public function fetchBatch(
        string $bareTable,
        string $orderBy,
        int $offset,
        int $limit,
        string $whereRawSql = '',
        array $params = []
    ): array {
        try {
            $query = $this->db->getQuery(true)
                ->select('*')
                ->from($this->db->quoteName('#__' . $bareTable))
                ->order($this->db->quoteName($orderBy))
                ->setLimit($limit, $offset);

            if ($whereRawSql !== '') {
                $query->where($whereRawSql);
                foreach ($params as $key => $val) {
                    $query->bind($key, $val);
                }
            }

            return $this->db->setQuery($query)->loadAssocList() ?: [];
        } catch (\Throwable) {
            return [];
        }
    }

    public function getPrefix(): string
    {
        return $this->db->getPrefix();
    }

    public function getDatabaseName(): string
    {
        try {
            return (string) $this->db->setQuery('SELECT DATABASE()')->loadResult();
        } catch (\Throwable) {
            return '';
        }
    }
}
