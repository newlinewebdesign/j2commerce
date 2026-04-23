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

/**
 * Read-only source reader backed by a PDO connection to a remote or alternate database.
 */
class PdoSourceReader implements SourceDatabaseReaderInterface
{
    public function __construct(
        private \PDO   $pdo,
        private string $prefix,
        private string $database
    ) {}

    public function count(string $bareTable, string $whereRawSql = '', array $params = []): int
    {
        $sql = 'SELECT COUNT(*) FROM `' . $this->prefix . $bareTable . '`';

        if ($whereRawSql !== '') {
            $sql .= ' WHERE ' . $whereRawSql;
        }

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return (int) $stmt->fetchColumn();
        } catch (\PDOException) {
            return 0;
        }
    }

    public function describe(string $bareTable): array
    {
        $sql = 'SELECT
                    COLUMN_NAME    AS `Field`,
                    COLUMN_TYPE    AS `Type`,
                    IS_NULLABLE    AS `Null`,
                    COLUMN_KEY     AS `Key`,
                    COLUMN_DEFAULT AS `Default`,
                    EXTRA          AS `Extra`
                FROM INFORMATION_SCHEMA.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME   = ?
                ORDER BY ORDINAL_POSITION';

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$this->prefix . $bareTable]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException) {
            return [];
        }
    }

    public function listTables(string $likePattern): array
    {
        $full = $this->prefix . $likePattern;

        $sql = 'SELECT TABLE_NAME
                FROM INFORMATION_SCHEMA.TABLES
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME LIKE ?';

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$full]);
            $rows = $stmt->fetchAll(\PDO::FETCH_COLUMN);

            return array_map(
                fn(string $t) => str_replace($this->prefix, '', $t),
                $rows
            );
        } catch (\PDOException) {
            return [];
        }
    }

    public function getPrimaryKey(string $bareTable): ?string
    {
        $sql = "SELECT COLUMN_NAME
                FROM INFORMATION_SCHEMA.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME   = ?
                  AND COLUMN_KEY   = 'PRI'
                LIMIT 1";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$this->prefix . $bareTable]);
            $col = $stmt->fetchColumn();
            return $col !== false ? (string) $col : null;
        } catch (\PDOException) {
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
        // $orderBy comes from an internal allowlist (adapter-declared), never user input
        $sql = 'SELECT * FROM `' . $this->prefix . $bareTable . '`';

        if ($whereRawSql !== '') {
            $sql .= ' WHERE ' . $whereRawSql;
        }

        $sql .= ' ORDER BY `' . $orderBy . '` LIMIT ' . $limit . ' OFFSET ' . $offset;

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException) {
            return [];
        }
    }

    public function getPrefix(): string
    {
        return $this->prefix;
    }

    public function getDatabaseName(): string
    {
        return $this->database;
    }
}
