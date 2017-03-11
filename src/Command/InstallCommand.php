<?php

namespace Pantheon\TerminusInstaller\Command;

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class InstallCommand
 * @package Pantheon\TerminusInstaller\Command
 */
class InstallCommand extends AbstractCommand
{
    protected function configure()
    {
        $this->setName('install')
            ->setDescription('Installs Terminus via Composer')
            ->setDefinition([
                new InputOption('bin-dir', null, InputOption::VALUE_OPTIONAL, 'Directory in which command-line executable scripts are added', '/usr/local/bin'),
                new InputOption('install-dir', null, InputOption::VALUE_OPTIONAL, 'Directory to which to install Terminus', getcwd()),
                new InputOption('install-version', null, InputOption::VALUE_OPTIONAL, 'Version of Terminus to install'),
            ])
            ->setHelp('Installs the Terminus CLI.');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return integer $status_code The status code returned from Composer
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
        $install_dir = $this->getDir($input->getOption('install-dir'));
        $status_code = $this->installTerminus($install_dir, $input->getOption('install-version'));
        $this->makeSymlink($input->getOption('bin-dir'), $install_dir);
        return $status_code;
    }

    /**
     * Uses Composer to install Terminus
     *
     * @param string $install_dir Directory to which to install Terminus
     * @param string $install_version Version of Terminus to install
     * @return integer $status_code The status code of the installation run
     */
    protected function installTerminus($install_dir, $install_version = null)
    {
        $arguments = [
            'command' => 'require',
            'packages' => [$this->getPackageTitle($install_version),],
            '--working-dir' => $install_dir,
        ];

        $this->output->writeln('Installing Terminus...');
        $status_code = $this->getComposer()->run(new ArrayInput($arguments), $this->output);
        return $status_code;
    }

    /**
     * Writes a symlink for the newly installed Terminus' executable in the bin directory
     *
     * @param string $bin_dir Bin directory
     * @param string $install_dir Dir to which Terminus was installed
     */
    protected function makeSymlink($bin_dir, $install_dir)
    {
        $fs = $this->getFilesystem();
        $exe_dir = $install_dir . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'bin';
        $exe_location = $exe_dir . DIRECTORY_SEPARATOR . 'terminus';

        if ($fs->exists($bin_dir) && is_writable($bin_dir) && is_writable($exe_location)) {
            $fs->symlink($exe_location, $bin_dir . DIRECTORY_SEPARATOR . 'terminus');
        } else {
            $message = <<<EOT
Terminus was installed, but the installer was not able to write to your bin dir. To enable the 
`terminus` command, add this alias to your .bash_profile (Mac) or .bashrc (Linux) file:

alias terminus=$exe_location

Or you can enable it by adding the directory the executable file is in to your path:

PATH="$exe_dir:\$PATH"
EOT;
            $this->output->writeln($message);
        }
    }
}
