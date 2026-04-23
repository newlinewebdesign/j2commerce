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

interface SourceDatabaseReaderInterface
{
    public function count(string $bareTable, string $whereRawSql = '', array $params = []): int;

    /** Returns array of assoc rows with Field, Type, Null, Key, Default, Extra */
    public function describe(string $bareTable): array;

    /** Returns bare table names (without prefix) matching the LIKE pattern */
    public function listTables(string $likePattern): array;

    public function getPrimaryKey(string $bareTable): ?string;

    public function fetchBatch(
        string $bareTable,
        string $orderBy,
        int $offset,
        int $limit,
        string $whereRawSql = '',
        array $params = []
    ): array;

    public function getPrefix(): string;

    public function getDatabaseName(): string;
}
