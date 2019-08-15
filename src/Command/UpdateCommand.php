<?php

namespace Pantheon\TerminusInstaller\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

/**
 * Class UpdateCommand
 * @package Pantheon\TerminusUpdateer\Command
 */
class UpdateCommand extends AbstractCommand
{
    protected function configure()
    {
        $this->setName('update')
            ->setDescription('Updates Terminus via Composer')
            ->setDefinition([
                new InputOption('install-dir', 'dir', InputOption::VALUE_OPTIONAL, 'The directory in which to find Terminus', getcwd()),
            ])
            ->setHelp('Updates the Terminus CLI.');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return integer The status code returned from Composer
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $package = $this->getPackage();
        $package->setInstallDir($input->getOption('install-dir'));

        $output->writeln('Checking package version...');
        if ($package->isUpToDate()) {
            $output->writeln('Terminus does not require updating in this location');
            return 0;
        }

        // If you are behind by a major version get an OK to upgrade it
        if (!$package->onCurrentMajorVersion()) {
            $question = new ConfirmationQuestion(
                'You are behind by at least one major version! Upgrading may break your scripts.' . PHP_EOL . 'Continue? (Y/n)' . PHP_EOL,
                false
            );
            if ($this->getHelper('question')->ask($input, $output, $question)) {
                $output->writeln('Updating Terminus to latest version...');
                $status_code = $package->runInstallLatest($output);

                // Return status code of installation
                return $status_code;
            }
        }

        // Execute the update
        $output->writeln(
            $package->onCurrentMinorVersion()
                ? 'Updating Terminus to latest patch version...'
                : 'Updating Terminus to latest minor version...'
        );
        $status_code = $package->runUpdate($output);

        // Return status code of update
        return $status_code;
    }
}
