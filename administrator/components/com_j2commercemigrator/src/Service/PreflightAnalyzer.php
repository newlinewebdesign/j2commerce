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
use J2Commerce\Component\J2commercemigrator\Administrator\Service\Reader\JoomlaSourceReader;
use J2Commerce\Component\J2commercemigrator\Administrator\Service\Reader\SourceDatabaseReaderInterface;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;

class PreflightAnalyzer
{
    private const BATCH_SIZE          = 1000;
    private const MAX_CONFLICT_SAMPLES = 10;
    private const MAX_ORPHAN_SAMPLES   = 5;

    private DataTransformer $transformer;

    /** @var array<string, list<array{name: string, columns: list<string>}>> */
    private array $constraintCache = [];

    public function __construct(
        private DatabaseInterface $db,
        private ?SourceDatabaseReaderInterface $sourceReader = null,
        private ?PreflightRepository $repository = null
    ) {
        $this->transformer = new DataTransformer();
        $this->sourceReader ??= new JoomlaSourceReader($db);
    }

    /**
     * Analyse every tier exposed by the adapter and optionally persist results.
     *
     * @param  int|null $runId  When supplied, each check is persisted via PreflightRepository.
     */
    public function analyzeAll(MigratorAdapterInterface $adapter, ?int $runId = null): array
    {
        $results = [];

        foreach ($adapter->getTierDefinitions() as $tierDef) {
            $results[$tierDef->tier] = $this->analyzeTier($adapter, $tierDef->tier, $runId);
        }

        return ['success' => true, 'tiers' => $results];
    }

    /**
     * Analyse a single tier and optionally persist results.
     *
     * @param  int|null $runId  When supplied, each check is persisted via PreflightRepository.
     */
    public function analyzeTier(MigratorAdapterInterface $adapter, int $tier, ?int $runId = null): array
    {
        $tiers = [];
        foreach ($adapter->getTierDefinitions() as $tierDef) {
            $tiers[$tierDef->tier] = $tierDef;
        }

        if (!isset($tiers[$tier])) {
            return ['error' => "Invalid tier: {$tier}"];
        }

        $tierDef          = $tiers[$tier];
        $tableMap         = $adapter->getTableMap();
        $tableResults     = [];
        $tierHasConflicts = false;
        $tierTargetAhead  = false;

        foreach ($tierDef->tables as $sourceTable) {
            $targetTable = $tableMap[$sourceTable] ?? null;

            if ($targetTable === null) {
                $tableResults[$sourceTable] = ['error' => 'No mapping found'];
                continue;
            }

            try {
                $analysis                   = $this->analyzeTable($adapter, $sourceTable, $targetTable);
                $tableResults[$sourceTable] = $analysis;

                if ($runId !== null && $this->repository !== null) {
                    $this->persistTableAnalysis($runId, $sourceTable, $targetTable, $analysis);
                }

                if (($analysis['conflicts'] ?? 0) > 0) {
                    $tierHasConflicts = true;
                }

                if (($analysis['target_count'] ?? 0) > ($analysis['source_count'] ?? 0)) {
                    $tierTargetAhead = true;
                }
            } catch (\Throwable $e) {
                $tableResults[$sourceTable] = ['error' => $e->getMessage()];
            }
        }

        return [
            'name'          => $tierDef->name,
            'tables'        => $tableResults,
            'has_conflicts' => $tierHasConflicts,
            'target_ahead'  => $tierTargetAhead,
        ];
    }

    /**
     * Analyse a single source→target table pair for PK collisions, unique-key
     * conflicts, and orphaned target rows.
     *
     * The adapter is used only to resolve the primary-key column name via
     * getConflictKey() so the method stays source-agnostic.
     */
    public function analyzeTable(MigratorAdapterInterface $adapter, string $sourceTable, string $targetTable): array
    {
        $sourceCount = $this->getRowCount($sourceTable, source: true);
        $targetCount = $this->getRowCount($targetTable, source: false);

        if (\is_string($sourceCount) || \is_string($targetCount)) {
            return [
                'source_table'     => $sourceTable,
                'target_table'     => $targetTable,
                'source_count'     => $sourceCount,
                'target_count'     => $targetCount,
                'clean_inserts'    => 0,
                'identical'        => 0,
                'conflicts'        => 0,
                'orphan_targets'   => 0,
                'sample_conflicts' => [],
                'sample_orphans'   => [],
                'error'            => \is_string($targetCount) ? $targetCount : $sourceCount,
            ];
        }

        $sourcePk = $this->sourceReader->getPrimaryKey($sourceTable) ?? 'id';
        $targetPk = $adapter->getConflictKey($targetTable);

        $cleanInserts    = 0;
        $identical       = 0;
        $conflicts       = 0;
        $sampleConflicts = [];
        $offset          = 0;

        while (true) {
            $rows = $this->sourceReader->fetchBatch($sourceTable, $sourcePk, $offset, self::BATCH_SIZE);

            if (empty($rows)) {
                break;
            }

            foreach ($rows as $row) {
                $transformed = $this->transformer->transformRow($row, $sourceTable, $targetTable);
                $targetRow   = $this->findTargetMatch($targetTable, $transformed, $targetPk);

                if ($targetRow === null) {
                    $cleanInserts++;
                    continue;
                }

                $diffs = $this->compareRows($transformed, $targetRow);

                if (empty($diffs)) {
                    $identical++;
                } else {
                    $conflicts++;
                    if (\count($sampleConflicts) < self::MAX_CONFLICT_SAMPLES) {
                        $sampleConflicts[] = [
                            'source_pk'   => $transformed[$targetPk] ?? null,
                            'target_pk'   => $targetRow[$targetPk] ?? null,
                            'label'       => $this->getRowLabel($transformed, $targetPk, $targetTable),
                            'match_type'  => $targetRow['_match_type'] ?? 'primary_key',
                            'differences' => $diffs,
                        ];
                    }
                }
            }

            $offset += self::BATCH_SIZE;

            if (\count($rows) < self::BATCH_SIZE) {
                break;
            }
        }

        $orphanTargets = max(0, $targetCount - $identical - $conflicts);
        $sampleOrphans = $this->getSampleOrphans($sourceTable, $targetTable, $sourcePk, $targetPk);

        return [
            'source_table'     => $sourceTable,
            'target_table'     => $targetTable,
            'source_count'     => $sourceCount,
            'target_count'     => $targetCount,
            'clean_inserts'    => $cleanInserts,
            'identical'        => $identical,
            'conflicts'        => $conflicts,
            'orphan_targets'   => $orphanTargets,
            'sample_conflicts' => $sampleConflicts,
            'sample_orphans'   => $sampleOrphans,
        ];
    }

    /**
     * Look up unique constraints on a target table, cached per table name.
     *
     * @return list<array{name: string, columns: list<string>}>
     */
    public function getUniqueConstraints(string $targetTable): array
    {
        if (isset($this->constraintCache[$targetTable])) {
            return $this->constraintCache[$targetTable];
        }

        $prefixedTable = $this->db->getPrefix() . $targetTable;

        $query = $this->db->getQuery(true)
            ->select([$this->db->quoteName('INDEX_NAME'), $this->db->quoteName('COLUMN_NAME')])
            ->from('INFORMATION_SCHEMA.STATISTICS')
            ->where('TABLE_SCHEMA = DATABASE()')
            ->where($this->db->quoteName('TABLE_NAME') . ' = :table')
            ->where($this->db->quoteName('NON_UNIQUE') . ' = 0')
            ->where($this->db->quoteName('INDEX_NAME') . ' != ' . $this->db->quote('PRIMARY'))
            ->bind(':table', $prefixedTable)
            ->order([$this->db->quoteName('INDEX_NAME'), $this->db->quoteName('SEQ_IN_INDEX')]);

        $rows    = $this->db->setQuery($query)->loadAssocList();
        $grouped = [];

        foreach ($rows as $row) {
            $grouped[$row['INDEX_NAME']][] = $row['COLUMN_NAME'];
        }

        $constraints = [];

        foreach ($grouped as $name => $columns) {
            $constraints[] = ['name' => $name, 'columns' => $columns];
        }

        $this->constraintCache[$targetTable] = $constraints;

        return $constraints;
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    private function findTargetMatch(string $targetTable, array $transformedRow, string $targetPk): ?array
    {
        $pkValue = $transformedRow[$targetPk] ?? null;

        if ($pkValue !== null) {
            $query = $this->db->getQuery(true)
                ->select('*')
                ->from($this->db->quoteName('#__' . $targetTable))
                ->where($this->db->quoteName($targetPk) . ' = :pk')
                ->bind(':pk', $pkValue, ParameterType::INTEGER);

            $row = $this->db->setQuery($query)->loadAssoc();

            if ($row !== null) {
                $row['_match_type'] = 'primary_key';
                return $row;
            }
        }

        foreach ($this->getUniqueConstraints($targetTable) as $constraint) {
            $query   = $this->db->getQuery(true)->select('*')->from($this->db->quoteName('#__' . $targetTable));
            $hasAll  = true;
            $bindIdx = 0;

            foreach ($constraint['columns'] as $col) {
                if (!array_key_exists($col, $transformedRow) || $transformedRow[$col] === null) {
                    $hasAll = false;
                    break;
                }

                $paramName = ':uc' . $bindIdx;
                $val       = $transformedRow[$col];
                $query->where($this->db->quoteName($col) . ' = ' . $paramName);
                $query->bind($paramName, $val);
                $bindIdx++;
            }

            if (!$hasAll) {
                continue;
            }

            $row = $this->db->setQuery($query)->loadAssoc();

            if ($row !== null) {
                $row['_match_type'] = 'unique_key:' . $constraint['name'];
                return $row;
            }
        }

        return null;
    }

    private function compareRows(array $source, array $target): array
    {
        $diffs = [];

        foreach ($source as $col => $sourceVal) {
            if ($col === '_match_type' || !array_key_exists($col, $target)) {
                continue;
            }

            if (!$this->valuesEqual($sourceVal, $target[$col])) {
                $diffs[$col] = ['source' => $sourceVal, 'target' => $target[$col]];
            }
        }

        return $diffs;
    }

    private function valuesEqual(mixed $a, mixed $b): bool
    {
        if ($a === $b) {
            return true;
        }

        if ($a === null || $b === null) {
            return false;
        }

        if (is_numeric($a) && is_numeric($b)) {
            return (float) $a === (float) $b;
        }

        return (string) $a === (string) $b;
    }

    private function getSampleOrphans(
        string $sourceTable,
        string $targetTable,
        string $sourcePk,
        string $targetPk
    ): array {
        try {
            $query = $this->db->getQuery(true)
                ->select('t.*')
                ->from($this->db->quoteName('#__' . $targetTable, 't'))
                ->leftJoin(
                    $this->db->quoteName('#__' . $sourceTable, 's')
                    . ' ON ' . $this->db->quoteName('t.' . $targetPk)
                    . ' = ' . $this->db->quoteName('s.' . $sourcePk)
                )
                ->where($this->db->quoteName('s.' . $sourcePk) . ' IS NULL')
                ->setLimit(self::MAX_ORPHAN_SAMPLES);

            $orphans = $this->db->setQuery($query)->loadAssocList();
            $samples = [];

            foreach ($orphans as $orphan) {
                $samples[] = [
                    'pk'    => $orphan[$targetPk] ?? null,
                    'label' => $this->getRowLabel($orphan, $targetPk, $targetTable),
                ];
            }

            return $samples;
        } catch (\Throwable) {
            return [];
        }
    }

    private function getRowLabel(array $row, string $pk, string $table): string
    {
        $labelColumns = match (true) {
            str_contains($table, 'countries')     => ['country_name', 'country_isocode_2'],
            str_contains($table, 'currencies')    => ['currency_code', 'currency_title'],
            str_contains($table, 'zones')         => ['zone_name', 'zone_code'],
            str_contains($table, 'orderstatuses') => ['orderstatus_name'],
            str_contains($table, 'coupons')       => ['coupon_code', 'coupon_name'],
            str_contains($table, 'vouchers')      => ['voucher_code'],
            str_contains($table, 'customfields')  => ['field_namekey', 'field_name'],
            str_contains($table, 'products')      => ['product_source_id'],
            str_contains($table, 'orders')        => ['order_id', 'invoice_prefix'],
            default                                => [],
        };

        $parts = [];

        foreach ($labelColumns as $col) {
            if (!empty($row[$col])) {
                $parts[] = (string) $row[$col];
            }
        }

        return $parts ? implode(' / ', $parts) : (string) ($row[$pk] ?? '');
    }

    /**
     * @param  bool $source  True = route through sourceReader; false = query target Joomla DB.
     * @return int|string    Row count or an error string when the table is absent/inaccessible.
     */
    private function getRowCount(string $bareTable, bool $source): int|string
    {
        if ($source) {
            try {
                return $this->sourceReader->count($bareTable);
            } catch (\Throwable $e) {
                $msg = $e->getMessage();

                if (stripos($msg, "doesn't exist") !== false || stripos($msg, 'does not exist') !== false) {
                    return 'Table not found';
                }

                return 'ERR: ' . $msg;
            }
        }

        try {
            $query = $this->db->getQuery(true)
                ->select('COUNT(*)')
                ->from($this->db->quoteName('#__' . $bareTable));

            return (int) $this->db->setQuery($query)->loadResult();
        } catch (\Throwable $e) {
            $msg = $e->getMessage();

            if (stripos($msg, "doesn't exist") !== false || stripos($msg, 'does not exist') !== false) {
                return 'Table not found';
            }

            return 'ERR: ' . $msg;
        }
    }

    /**
     * Write one row's analysis summary into the preflight table via PreflightRepository.
     *
     * Status logic:
     *   - 'fail'  → conflicts > 0
     *   - 'warn'  → orphan_targets > 0 (target has rows the source no longer has)
     *   - 'pass'  → clean
     *   - 'skip'  → table lookup returned an error string
     */
    private function persistTableAnalysis(
        int    $runId,
        string $sourceTable,
        string $targetTable,
        array  $analysis
    ): void {
        if (isset($analysis['error'])) {
            $this->repository->upsert(
                runId:    $runId,
                checkKey: 'table:' . $sourceTable,
                label:    $sourceTable . ' → ' . $targetTable,
                status:   'skip',
                detail:   $analysis['error'],
            );
            return;
        }

        $conflicts = $analysis['conflicts'] ?? 0;
        $orphans   = $analysis['orphan_targets'] ?? 0;

        $status = match (true) {
            $conflicts > 0 => 'fail',
            $orphans   > 0 => 'warn',
            default        => 'pass',
        };

        $detail = null;

        if ($conflicts > 0 || $orphans > 0) {
            $detail = json_encode([
                'conflicts'     => $conflicts,
                'orphans'       => $orphans,
                'source_count'  => $analysis['source_count'] ?? 0,
                'target_count'  => $analysis['target_count'] ?? 0,
            ], JSON_THROW_ON_ERROR);
        }

        $this->repository->upsert(
            runId:    $runId,
            checkKey: 'table:' . $sourceTable,
            label:    $sourceTable . ' → ' . $targetTable,
            status:   $status,
            detail:   $detail,
        );
    }
}
