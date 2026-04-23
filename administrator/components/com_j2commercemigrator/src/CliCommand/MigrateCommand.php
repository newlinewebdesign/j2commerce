<?php

/**
 * @package     J2Commerce
 * @subpackage  com_j2commercemigrator
 *
 * @copyright   (C)2024-2026 J2Commerce, LLC <https://www.j2commerce.com>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace J2Commerce\Component\J2commercemigrator\Administrator\CliCommand;

use J2Commerce\Component\J2commercemigrator\Administrator\Helper\MigrationLogger;
use J2Commerce\Component\J2commercemigrator\Administrator\Service\AdapterRegistry;
use J2Commerce\Component\J2commercemigrator\Administrator\Service\ConnectionManager;
use J2Commerce\Component\J2commercemigrator\Administrator\Service\MigrationEngine;
use Joomla\CMS\Factory;
use Joomla\Console\Command\AbstractCommand;
use Joomla\Database\DatabaseInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class MigrateCommand extends AbstractCommand
{
    protected static $defaultName = 'j2commerce:migrator:migrate';

    protected function configure(): void
    {
        $this->setDescription('Run a migration via a registered adapter');
        $this->addOption('adapter', 'a', InputOption::VALUE_REQUIRED, 'Adapter key (e.g. j2store4)', 'j2store4');
        $this->addOption('tier', 't', InputOption::VALUE_REQUIRED, 'Run a specific tier number (omit for all tiers)');
        $this->addOption('batch-size', 'b', InputOption::VALUE_REQUIRED, 'Batch size per query', '200');
        $this->addOption('conflict-mode', 'c', InputOption::VALUE_REQUIRED, 'Conflict mode: skip|overwrite|merge', 'skip');
        $this->addOption('mode', 'm', InputOption::VALUE_REQUIRED, 'Source connection mode: A|B|C', 'A');
    }

    protected function doExecute(InputInterface $input, OutputInterface $output): int
    {
        $io           = new SymfonyStyle($input, $output);
        $adapterKey   = (string) ($input->getOption('adapter') ?? 'j2store4');
        $batchSize    = (int) ($input->getOption('batch-size') ?? 200);
        $conflictMode = (string) ($input->getOption('conflict-mode') ?? 'skip');
        $specificTier = $input->getOption('tier') !== null ? (int) $input->getOption('tier') : null;

        $io->title('J2Commerce Migrator — Run');
        $io->text("Adapter: {$adapterKey}");

        $registry = new AdapterRegistry();
        $adapter  = $registry->get($adapterKey);

        if (!$adapter) {
            $io->error("Adapter '{$adapterKey}' not found. Run 'j2commerce:migrator:audit' to list available adapters.");
            return 1;
        }

        $db     = Factory::getContainer()->get(DatabaseInterface::class);
        $logger = new MigrationLogger();
        $app    = Factory::getApplication();
        $connMgr = new ConnectionManager($app, $db);

        if (!$connMgr->isReady()) {
            $io->warning("No source connection found in session. Using Mode A (local Joomla DB).");
            $connMgr->verify(['mode' => 'A']);
        }

        $engine = new MigrationEngine($db, $logger, $connMgr->getReader());

        $tiers = $adapter->getTierDefinitions();

        if ($specificTier !== null) {
            $io->section("Running tier {$specificTier}");
            $result = $engine->runTier($adapter, $specificTier, $batchSize, $conflictMode);
            $this->renderTierResult($io, $result);
            return $result['success'] ? 0 : 1;
        }

        $totalMigrated = 0;
        $hasErrors     = false;

        foreach ($tiers as $tierDef) {
            $io->section("Tier {$tierDef->tier}: {$tierDef->name}");
            $result = $engine->runTier($adapter, $tierDef->tier, $batchSize, $conflictMode);
            $this->renderTierResult($io, $result);

            $totalMigrated += $result['total_migrated'] ?? 0;

            if (!($result['success'] ?? false)) {
                $hasErrors = true;
            }
        }

        if ($hasErrors) {
            $io->warning("Migration completed with errors. Total migrated: {$totalMigrated}");
            return 1;
        }

        $io->success("Migration complete. Total migrated: {$totalMigrated}");
        return 0;
    }

    private function renderTierResult(SymfonyStyle $io, array $result): void
    {
        if (!empty($result['error'])) {
            $io->error($result['error']);
            return;
        }

        $counts = $result['counts'] ?? [];
        $io->definitionList(
            ['Inserted'   => $counts['inserted'] ?? 0],
            ['Skipped'    => $counts['skipped'] ?? 0],
            ['Overwritten' => $counts['overwritten'] ?? 0],
            ['Merged'     => $counts['merged'] ?? 0],
            ['Errors'     => $counts['errors'] ?? 0],
        );

        if (!empty($result['tables'])) {
            foreach ($result['tables'] as $table => $tableResult) {
                if (!empty($tableResult['error'])) {
                    $io->text("  <error>[ERR] {$table}: {$tableResult['error']}</error>");
                }
            }
        }
    }
}
