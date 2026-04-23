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

class AuditCommand extends AbstractCommand
{
    protected static $defaultName = 'j2commerce:migrator:audit';

    protected function configure(): void
    {
        $this->setDescription('Audit source vs target row counts for a registered adapter');
        $this->addOption('adapter', 'a', InputOption::VALUE_REQUIRED, 'Adapter key (e.g. j2store4)', 'j2store4');
        $this->addOption('mode', 'm', InputOption::VALUE_REQUIRED, 'Source connection mode: A|B|C', 'A');
        $this->addOption('list-adapters', 'l', InputOption::VALUE_NONE, 'List all registered adapters and exit');
    }

    protected function doExecute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $registry = new AdapterRegistry();

        if ($input->getOption('list-adapters')) {
            $adapters = $registry->getAll();

            if (empty($adapters)) {
                $io->warning('No adapters registered. Install a j2commercemigrator plugin.');
                return 0;
            }

            $rows = [];
            foreach ($adapters as $adapter) {
                $info   = $adapter->getSourceInfo();
                $rows[] = [$adapter->getKey(), $info->title, $info->version];
            }

            $io->table(['Key', 'Title', 'Version'], $rows);
            return 0;
        }

        $adapterKey = (string) ($input->getOption('adapter') ?? 'j2store4');
        $adapter    = $registry->get($adapterKey);

        if (!$adapter) {
            $io->error("Adapter '{$adapterKey}' not found. Run with --list-adapters to see available adapters.");
            return 1;
        }

        $io->title('J2Commerce Migrator — Audit');
        $io->text("Adapter: {$adapterKey}");

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
            $io->error($result['error'] ?? 'Audit failed.');
            return 1;
        }

        foreach ($result['tiers'] as $tierNum => $tier) {
            $io->section("Tier {$tierNum}: {$tier['name']}");

            $rows = [];
            foreach ($tier['tables'] as $sourceTable => $info) {
                if (!empty($info['error'])) {
                    $rows[] = [$sourceTable, '—', '—', "<error>{$info['error']}</error>"];
                    continue;
                }

                $status = match ($info['status'] ?? '') {
                    'empty'    => '<comment>empty</comment>',
                    'partial'  => '<comment>partial</comment>',
                    'complete' => '<info>complete</info>',
                    'excess'   => '<comment>excess</comment>',
                    default    => $info['status'] ?? '?',
                };

                $rows[] = [
                    $sourceTable,
                    number_format($info['source_count']),
                    number_format($info['target_count']),
                    $status,
                ];
            }

            $io->table(['Source Table', 'Source Rows', 'Target Rows', 'Status'], $rows);
        }

        return 0;
    }
}
