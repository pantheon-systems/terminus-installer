<?php

namespace Pantheon\TerminusInstaller\Command;

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

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
                new InputOption('dir', null, InputOption::VALUE_OPTIONAL, 'The directory in which to find Terminus', getcwd()),
            ])
            ->setHelp('Updates the Terminus CLI.');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return integer $status_code The status code returned from Composer
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
        $status_code = $this->updateTerminus($this->getDir($input->getOption('dir')));
        return $status_code;
    }

    /**
     * Uses Composer to update Terminus
     *
     * @param string $update_dir Directory to which to update Terminus
     * @return integer $status_code The status code of the update operation run
     */
    protected function updateTerminus($update_dir) {
        $arguments = [
            'command' => 'update',
            'packages' => [$this->getPackageTitle(),],
            '--working-dir' => $update_dir,
        ];

        $this->output->writeln('Updating Terminus...');
        $status_code = $this->getComposer()->run(new ArrayInput($arguments), $this->output);
        return $status_code;
    }
}
