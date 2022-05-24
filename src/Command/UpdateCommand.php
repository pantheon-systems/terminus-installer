<?php

namespace Pantheon\TerminusInstaller\Command;

use Pantheon\TerminusInstaller\Utils\TerminusPackage;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

/**
 * Class UpdateCommand
 * @package Pantheon\TerminusInstaller\Command
 */
class UpdateCommand extends Command
{
    /**
     * @inheritDoc
     */
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
        $output->writeln('This command is deprecated. Please use `terminus self-update` instead.');
    }
}
