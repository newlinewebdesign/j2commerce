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

class ResetCommand extends AbstractCommand
{
    protected static $defaultName = 'j2commerce:migrator:reset';

    protected function configure(): void
    {
        $this->setDescription('Truncate migrated J2Commerce tables for a given adapter (re-run preparation)');
        $this->addOption('adapter', 'a', InputOption::VALUE_REQUIRED, 'Adapter key (e.g. j2store4)', 'j2store4');
        $this->addOption('tier', 't', InputOption::VALUE_REQUIRED, 'Reset a specific tier number only (omit for all tiers)');
        $this->addOption('yes', 'y', InputOption::VALUE_NONE, 'Skip confirmation prompt');
    }

    protected function doExecute(InputInterface $input, OutputInterface $output): int
    {
        $io           = new SymfonyStyle($input, $output);
        $adapterKey   = (string) ($input->getOption('adapter') ?? 'j2store4');
        $specificTier = $input->getOption('tier') !== null ? (int) $input->getOption('tier') : null;
        $skipConfirm  = (bool) $input->getOption('yes');

        $io->title('J2Commerce Migrator — Reset');
        $io->text("Adapter: {$adapterKey}");

        $registry = new AdapterRegistry();
        $adapter  = $registry->get($adapterKey);

        if (!$adapter) {
            $io->error("Adapter '{$adapterKey}' not found. Run 'j2commerce:migrator:audit --list-adapters' to see available adapters.");
            return 1;
        }

        $tiers = $adapter->getTierDefinitions();

        if ($specificTier !== null) {
            $tiers = array_filter($tiers, static fn($t) => $t->tier === $specificTier);
            if (empty($tiers)) {
                $io->error("Tier {$specificTier} not found in adapter '{$adapterKey}'.");
                return 1;
            }
        }

        $tableCount = array_sum(array_map(static fn($t) => count($t->tables), $tiers));
        $tierLabel  = $specificTier !== null ? "tier {$specificTier}" : 'all tiers';

        $io->warning([
            "This will TRUNCATE {$tableCount} J2Commerce target table(s) for {$tierLabel}.",
            'All migrated data will be permanently deleted from the target tables.',
            'Source data (j2store_* tables) is NOT affected.',
        ]);

        if (!$skipConfirm && !$io->confirm('Are you sure you want to proceed?', false)) {
            $io->text('Reset cancelled.');
            return 0;
        }

        $db      = Factory::getContainer()->get(DatabaseInterface::class);
        $logger  = new MigrationLogger();
        $app     = Factory::getApplication();
        $connMgr = new ConnectionManager($app, $db);

        if (!$connMgr->isReady()) {
            $connMgr->verify(['mode' => 'A']);
        }

        $engine    = new MigrationEngine($db, $logger, $connMgr->getReader());
        $hasErrors = false;

        foreach ($tiers as $tierDef) {
            $io->section("Resetting tier {$tierDef->tier}: {$tierDef->name}");
            $result = $engine->resetTier($adapter, $tierDef->tier);

            if (!empty($result['error'])) {
                $io->error($result['error']);
                $hasErrors = true;
                continue;
            }

            $rows = [];
            foreach ($result['tables'] ?? [] as $table => $tableResult) {
                if (!empty($tableResult['error'])) {
                    $rows[]    = [$table, "<error>ERROR: {$tableResult['error']}</error>"];
                    $hasErrors = true;
                } else {
                    $rows[] = [$table, '<info>truncated</info>'];
                }
            }

            if (!empty($rows)) {
                $io->table(['Table', 'Result'], $rows);
            }
        }

        if ($hasErrors) {
            $io->warning('Reset completed with errors.');
            return 1;
        }

        $io->success('Reset complete. You can now re-run the migration.');
        return 0;
    }
}
