<?php

/**
 * @package     J2Commerce
 * @subpackage  com_j2commercemigrator
 *
 * @copyright   (C)2024-2026 J2Commerce, LLC <https://www.j2commerce.com>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace J2Commerce\Component\J2commercemigrator\Administrator\Model;

defined('_JEXEC') or die;

use J2Commerce\Component\J2commercemigrator\Administrator\Helper\MigrationLogger;
use J2Commerce\Component\J2commercemigrator\Administrator\Service\AdapterRegistry;
use J2Commerce\Component\J2commercemigrator\Administrator\Service\ConnectionManager;
use J2Commerce\Component\J2commercemigrator\Administrator\Service\J2CoreMigrator;
use J2Commerce\Component\J2commercemigrator\Administrator\Service\MigrationEngine;
use J2Commerce\Component\J2commercemigrator\Administrator\Service\RunRepository;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

/**
 * Main migration workflow model — orchestrates audit, run, reset, and status
 * checks across both adapter tiers and Joomla core tiers.
 */
class MigrateModel extends BaseDatabaseModel
{
    private function engine(): MigrationEngine
    {
        $db      = $this->getDatabase();
        $app     = Factory::getApplication();
        $logger  = new MigrationLogger();
        $connMgr = new ConnectionManager($app, $db);

        return new MigrationEngine($db, $logger, $connMgr->getReader());
    }

    private function coreEngine(): J2CoreMigrator
    {
        $db     = $this->getDatabase();
        $logger = new MigrationLogger();

        return new J2CoreMigrator($db, $logger);
    }

    private function adapter(string $adapterKey): ?\J2Commerce\Component\J2commercemigrator\Administrator\Adapter\MigratorAdapterInterface
    {
        return (new AdapterRegistry())->get($adapterKey);
    }

    /** Returns per-tier source/target row counts for all adapter tiers. */
    public function audit(string $adapterKey): array
    {
        $adapter = $this->adapter($adapterKey);

        if (!$adapter) {
            return ['error' => "Unknown adapter: {$adapterKey}"];
        }

        return $this->engine()->audit($adapter);
    }

    /** Runs every table in one tier and returns batch statistics. */
    public function runTier(string $adapterKey, int $tier, int $batchSize = 200, string $conflictMode = 'skip'): array
    {
        $adapter = $this->adapter($adapterKey);

        if (!$adapter) {
            return ['error' => "Unknown adapter: {$adapterKey}"];
        }

        return $this->engine()->runTier($adapter, $tier, $batchSize, $conflictMode);
    }

    /** Migrates a single source table (browser-loop, paginated). */
    public function migrateOneTable(string $adapterKey, string $sourceTable, int $batchSize, string $conflictMode, int $offset): array
    {
        $adapter = $this->adapter($adapterKey);

        if (!$adapter) {
            return ['error' => "Unknown adapter: {$adapterKey}"];
        }

        return $this->engine()->migrateOneTable($adapter, $sourceTable, $batchSize, $conflictMode, $offset);
    }

    /** Returns live per-tier progress for the wizard progress bar. */
    public function getProgress(string $adapterKey): array
    {
        $adapter = $this->adapter($adapterKey);

        if (!$adapter) {
            return ['error' => "Unknown adapter: {$adapterKey}"];
        }

        return $this->engine()->getProgress($adapter);
    }

    /** Truncates all target tables in a tier (destructive — requires confirmation in controller). */
    public function resetTier(string $adapterKey, int $tier): array
    {
        $adapter = $this->adapter($adapterKey);

        if (!$adapter) {
            return ['error' => "Unknown adapter: {$adapterKey}"];
        }

        $db     = $this->getDatabase();
        $logger = new MigrationLogger();

        return (new MigrationEngine($db, $logger))->resetTier($adapter, $tier);
    }

    /** Returns the source-table row count for the table-count display in the plan step. */
    public function getSourceTableCount(string $adapterKey, string $sourceTable): array
    {
        $adapter = $this->adapter($adapterKey);

        if (!$adapter) {
            return ['error' => "Unknown adapter: {$adapterKey}"];
        }

        $app      = Factory::getApplication();
        $db       = $this->getDatabase();
        $connMgr  = new ConnectionManager($app, $db);
        $reader   = $connMgr->getReader();
        $tableMap = $adapter->getTableMap();

        if (!isset($tableMap[$sourceTable])) {
            return ['error' => "Unknown source table: {$sourceTable}"];
        }

        return [
            'source_table' => $sourceTable,
            'target_table' => $tableMap[$sourceTable],
            'source_count' => $reader->count($sourceTable),
        ];
    }

    /** Rewrites Bootstrap 2 order-status CSS badge classes to Bootstrap 5 equivalents. */
    public function normalizeOrderStatusCssClasses(): array
    {
        $db     = $this->getDatabase();
        $logger = new MigrationLogger();

        return (new MigrationEngine($db, $logger))->normalizeOrderStatusCssClasses();
    }

    /** Replaces `0000-00-00` date defaults with NULL across migrated tables. */
    public function normalizeDateColumnDefaults(string $adapterKey): array
    {
        $adapter = $this->adapter($adapterKey);

        if (!$adapter) {
            return ['error' => "Unknown adapter: {$adapterKey}"];
        }

        $db     = $this->getDatabase();
        $logger = new MigrationLogger();

        return (new MigrationEngine($db, $logger))->normalizeDateColumnDefaults($adapter);
    }

    // — Joomla core tiers (9–12: ACL, Users, Content, Workflows) —

    /** Audits Joomla core tiers. */
    public function auditCore(): array
    {
        return $this->coreEngine()->audit();
    }

    /** Runs a Joomla core tier. */
    public function runCoreTier(int $tier, string $conflictMode = 'skip'): array
    {
        return $this->coreEngine()->runTier($tier, $conflictMode);
    }

    /** Resets a Joomla core tier. */
    public function resetCoreTier(int $tier): array
    {
        return $this->coreEngine()->resetTier($tier);
    }
}
