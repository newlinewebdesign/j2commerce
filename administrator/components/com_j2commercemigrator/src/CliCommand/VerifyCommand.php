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

class VerifyCommand extends AbstractCommand
{
    protected static $defaultName = 'j2commerce:migrator:verify';

    protected function configure(): void
    {
        $this->setDescription('Verify row counts match between source and target after migration');
        $this->addOption('adapter', 'a', InputOption::VALUE_REQUIRED, 'Adapter key (e.g. j2store4)', 'j2store4');
        $this->addOption('mode', 'm', InputOption::VALUE_REQUIRED, 'Source connection mode: A|B|C', 'A');
        $this->addOption('tier', 't', InputOption::VALUE_REQUIRED, 'Verify a specific tier number only');
        $this->addOption('fail-on-partial', null, InputOption::VALUE_NONE, 'Return exit code 1 if any table is not fully migrated');
    }

    protected function doExecute(InputInterface $input, OutputInterface $output): int
    {
        $io           = new SymfonyStyle($input, $output);
        $adapterKey   = (string) ($input->getOption('adapter') ?? 'j2store4');
        $specificTier = $input->getOption('tier') !== null ? (int) $input->getOption('tier') : null;
        $failPartial  = (bool) $input->getOption('fail-on-partial');

        $io->title('J2Commerce Migrator — Verify');
        $io->text("Adapter: {$adapterKey}");

        $registry = new AdapterRegistry();
        $adapter  = $registry->get($adapterKey);

        if (!$adapter) {
            $io->error("Adapter '{$adapterKey}' not found. Run 'j2commerce:migrator:audit --list-adapters' to see available adapters.");
            return 1;
        }

        $db      = Factory::getContainer()->get(DatabaseInterface::class);
        $logger  = new MigrationLogger();
        $app     = Factory::getApplication();
        $connMgr = new ConnectionManager($app, $db);

        if (!$connMgr->isReady()) {
            $io->warning("No source connection found in session. Using Mode A (local Joomla DB).");
            $connMgr->verify(['mode' => 'A']);
        }

        $engine = new MigrationEngine($db, $logger, $connMgr->getReader());
        $result = $engine->audit($adapter);

        if (!($result['success'] ?? false)) {
            $io->error($result['error'] ?? 'Verification failed.');
            return 1;
        }

        $hasIncomplete = false;
        $tiers         = $result['tiers'];

        if ($specificTier !== null) {
            if (!isset($tiers[$specificTier])) {
                $io->error("Tier {$specificTier} not found in adapter '{$adapterKey}'.");
                return 1;
            }
            $tiers = [$specificTier => $tiers[$specificTier]];
        }

        foreach ($tiers as $tierNum => $tier) {
            $io->section("Tier {$tierNum}: {$tier['name']}");

            $rows = [];
            foreach ($tier['tables'] as $sourceTable => $info) {
                if (!empty($info['error'])) {
                    $rows[]        = [$sourceTable, '—', '—', "<error>ERROR: {$info['error']}</error>"];
                    $hasIncomplete = true;
                    continue;
                }

                $sourceCount = $info['source_count'];
                $targetCount = $info['target_count'];
                $status      = $info['status'] ?? 'unknown';

                if ($status !== 'complete' && $status !== 'empty') {
                    $hasIncomplete = true;
                }

                $statusLabel = match ($status) {
                    'complete' => '<info>PASS</info>',
                    'empty'    => '<comment>EMPTY</comment>',
                    'partial'  => '<error>PARTIAL</error>',
                    'excess'   => '<comment>EXCESS</comment>',
                    default    => "<comment>{$status}</comment>",
                };

                $rows[] = [
                    $sourceTable,
                    number_format($sourceCount),
                    number_format($targetCount),
                    $statusLabel,
                ];
            }

            $io->table(['Source Table', 'Source Rows', 'Target Rows', 'Verification'], $rows);
        }

        if ($hasIncomplete) {
            $io->warning('Some tables are not fully migrated. Run the migration again or investigate errors.');
            return $failPartial ? 1 : 0;
        }

        $io->success('All tables verified successfully.');
        return 0;
    }
}
