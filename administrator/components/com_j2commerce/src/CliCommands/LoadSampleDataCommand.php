<?php

/**
 * @package     J2Commerce
 * @subpackage  com_j2commerce
 *
 * @copyright   (C)2024-2026 J2Commerce, LLC <https://www.j2commerce.com>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace J2Commerce\Component\J2commerce\Administrator\CliCommands;

defined('_JEXEC') or die;

use J2Commerce\Component\J2commerce\Administrator\Helper\SampleDataHelper;
use Joomla\CMS\Factory;
use Joomla\Console\Command\AbstractCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class LoadSampleDataCommand extends AbstractCommand
{
    protected static $defaultName = 'j2commerce:load:sampledata';

    private const ALLOWED_PROFILES = ['minimal', 'standard', 'full'];

    protected function configure(): void
    {
        $this->setDescription('Load sample data into a J2Commerce store for testing and development');
        $this->addOption('profile', 'p', InputOption::VALUE_OPTIONAL, 'Data volume preset: minimal, standard, or full', 'standard');
        $this->addOption('yes', 'y', InputOption::VALUE_NONE, 'Skip confirmation prompt');
        $this->addOption('clean', null, InputOption::VALUE_NONE, 'Remove existing sample data before loading');
        $this->addOption('remove', null, InputOption::VALUE_NONE, 'Remove all sample data and exit');
    }

    protected function doExecute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $db     = Factory::getContainer()->get(\Joomla\Database\DatabaseInterface::class);
        $helper = new SampleDataHelper($db);

        // Handle --remove flag
        if ($input->getOption('remove')) {
            return $this->doRemove($io, $helper);
        }

        $profile = strtolower((string) ($input->getOption('profile') ?? 'standard'));

        if (!in_array($profile, self::ALLOWED_PROFILES, true)) {
            $io->error(sprintf('Invalid profile "%s". Allowed values: %s', $profile, implode(', ', self::ALLOWED_PROFILES)));
            return 1;
        }

        // Handle --clean flag
        if ($input->getOption('clean') && $helper->isLoaded()) {
            $io->section('Removing existing sample data...');
            $removed = $helper->remove();
            $io->success('Existing sample data removed.');
            $this->printSummary($io, $removed);
        } elseif ($helper->isLoaded() && !$input->getOption('clean')) {
            $io->warning('Sample data is already loaded. Use --clean to remove and reload, or --remove to remove only.');
            return 0;
        }

        // Confirmation prompt
        if (!$input->getOption('yes') && $input->isInteractive()) {
            $io->note([
                'This will insert sample data into your J2Commerce store.',
                'Profile: ' . $profile,
                'Use --yes to skip this prompt.',
            ]);

            if (!$io->confirm('Are you sure you want to load sample data?', false)) {
                $io->text('Aborted.');
                return 0;
            }
        }

        $io->section(sprintf('Loading sample data (profile: %s)...', $profile));

        try {
            $result = $helper->load($profile);
        } catch (\Throwable $e) {
            $io->error('Failed to load sample data: ' . $e->getMessage());
            return 2;
        }

        $io->success(sprintf('Sample data loaded successfully (profile: %s).', $profile));
        $this->printSummary($io, $result);

        return 0;
    }

    private function doRemove(SymfonyStyle $io, SampleDataHelper $helper): int
    {
        if (!$helper->isLoaded()) {
            $io->warning('No sample data found to remove.');
            return 0;
        }

        $io->section('Removing sample data...');

        try {
            $result = $helper->remove();
        } catch (\Throwable $e) {
            $io->error('Failed to remove sample data: ' . $e->getMessage());
            return 2;
        }

        $io->success('Sample data removed successfully.');
        $this->printSummary($io, $result);

        return 0;
    }

    private function printSummary(SymfonyStyle $io, array $result): void
    {
        $skip = ['success', 'profile'];
        $rows = [];

        foreach ($result as $key => $value) {
            if (in_array($key, $skip, true) || !is_numeric($value)) {
                continue;
            }
            $rows[] = [ucwords(str_replace('_', ' ', $key)), (string) $value];
        }

        if (!empty($rows)) {
            $io->table(['Entity', 'Count'], $rows);
        }
    }
}
