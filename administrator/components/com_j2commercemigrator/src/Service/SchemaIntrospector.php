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

use J2Commerce\Component\J2commercemigrator\Administrator\Adapter\MigratorAdapterInterface;
use J2Commerce\Component\J2commercemigrator\Administrator\Service\Reader\SourceDatabaseReaderInterface;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;

/**
 * Centralises table-existence and row-count probing for the Migrate wizard Discover step.
 *
 * discover()             — checks which source tables the adapter expects vs. what actually
 *                          exists in the connected source DB (via SourceDatabaseReaderInterface).
 * discoverTargetTables() — checks which target tables the adapter plans to write to vs. what
 *                          exists in the local Joomla database.
 *
 * The return shapes are intentionally compatible with MigrationEngine::audit() so that the
 * existing wizard JS (migrator-run.js normalizeTiers / renderTierAudit) can consume either.
 */
class SchemaIntrospector
{
    public function __construct(
        private DatabaseInterface $db
    ) {}

    /**
     * Probe the source database for the tables the adapter expects to read.
     *
     * Return shape (matches old TableMapper::discover() + audit() output):
     * ```
     * [
     *   'success'        => bool,
     *   'source_prefix'  => string,   // e.g. 'jos_'
     *   'tables'         => [
     *     '<source_bare_table>' => [
     *       'source_table' => string,   // bare name without prefix
     *       'target_table' => string,   // mapped bare target name
     *       'exists'       => bool,     // present in source DB?
     *       'source_count' => int|null, // null when table absent
     *       'status'       => 'present'|'missing',
     *     ],
     *     ...
     *   ],
     *   'missing'        => string[],  // bare source table names not found
     *   'present_count'  => int,
     *   'missing_count'  => int,
     * ]
     * ```
     * On failure returns `['success' => false, 'error' => string]`.
     */
    public function discover(
        MigratorAdapterInterface $adapter,
        SourceDatabaseReaderInterface $reader
    ): array {
        $tableMap = $adapter->getTableMap();

        if (empty($tableMap)) {
            return ['success' => false, 'error' => 'Adapter returned an empty table map.'];
        }

        $sourcePrefix = $reader->getPrefix();
        $existingTables = [];

        try {
            $existingTables = $reader->listTables('');
        } catch (\Throwable $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }

        $existing = array_flip($existingTables);
        $tables   = [];
        $missing  = [];

        foreach ($tableMap as $sourceTable => $targetTable) {
            $present    = isset($existing[$sourceTable]);
            $rowCount   = null;

            if ($present) {
                try {
                    $rowCount = $reader->count($sourceTable);
                } catch (\Throwable) {
                    $rowCount = null;
                }
            } else {
                $missing[] = $sourceTable;
            }

            $tables[$sourceTable] = [
                'source_table' => $sourceTable,
                'target_table' => $this->bareTable($targetTable),
                'exists'       => $present,
                'source_count' => $rowCount,
                'status'       => $present ? 'present' : 'missing',
            ];
        }

        return [
            'success'       => true,
            'source_prefix' => $sourcePrefix,
            'tables'        => $tables,
            'missing'       => $missing,
            'present_count' => count($tableMap) - count($missing),
            'missing_count' => count($missing),
        ];
    }

    /**
     * Probe the local Joomla database for the target tables the adapter plans to write to.
     *
     * Return shape:
     * ```
     * [
     *   'success'        => bool,
     *   'tables'         => [
     *     '<target_bare_table>' => [
     *       'target_table' => string,   // bare name without prefix
     *       'exists'       => bool,
     *       'target_count' => int|null, // null when table absent
     *       'status'       => 'present'|'missing',
     *     ],
     *     ...
     *   ],
     *   'missing'        => string[],
     *   'present_count'  => int,
     *   'missing_count'  => int,
     * ]
     * ```
     * On failure returns `['success' => false, 'error' => string]`.
     */
    public function discoverTargetTables(MigratorAdapterInterface $adapter): array
    {
        $tableMap = $adapter->getTableMap();

        if (empty($tableMap)) {
            return ['success' => false, 'error' => 'Adapter returned an empty table map.'];
        }

        $prefix          = $this->db->getPrefix();
        $existingTargets = $this->listLocalTables($prefix);

        if ($existingTargets === null) {
            return ['success' => false, 'error' => 'Could not query local INFORMATION_SCHEMA.'];
        }

        $existing = array_flip($existingTargets);
        $tables   = [];
        $missing  = [];

        foreach ($tableMap as $targetTable) {
            $bare    = $this->bareTable($targetTable);
            $present = isset($existing[$bare]);
            $count   = null;

            if ($present) {
                $count = $this->localRowCount($bare);
            } else {
                $missing[] = $bare;
            }

            $tables[$bare] = [
                'target_table' => $bare,
                'exists'       => $present,
                'target_count' => $count,
                'status'       => $present ? 'present' : 'missing',
            ];
        }

        return [
            'success'       => true,
            'tables'        => $tables,
            'missing'       => $missing,
            'present_count' => count($tables) - count($missing),
            'missing_count' => count($missing),
        ];
    }

    /**
     * Return bare table names (no prefix) present in the local Joomla database.
     * Returns null on DB error.
     *
     * @return string[]|null
     */
    private function listLocalTables(string $prefix): ?array
    {
        try {
            $schema = $this->db->getDatabase();

            $query = $this->db->getQuery(true)
                ->select($this->db->quoteName('TABLE_NAME'))
                ->from($this->db->quoteName('INFORMATION_SCHEMA.TABLES'))
                ->where($this->db->quoteName('TABLE_SCHEMA') . ' = :schema')
                ->bind(':schema', $schema);

            $raw = $this->db->setQuery($query)->loadColumn();

            return array_map(
                static fn(string $t): string => str_replace($prefix, '', $t),
                $raw
            );
        } catch (\Throwable) {
            return null;
        }
    }

    private function localRowCount(string $bareTable): ?int
    {
        try {
            $query = $this->db->getQuery(true)
                ->select('COUNT(*)')
                ->from($this->db->quoteName('#__' . $bareTable));

            return (int) $this->db->setQuery($query)->loadResult();
        } catch (\Throwable) {
            return null;
        }
    }

    private function bareTable(string $table): string
    {
        $prefix = $this->db->getPrefix();
        $table  = str_replace(['#__', $prefix], '', $table);
        return ltrim($table, '_');
    }
}
