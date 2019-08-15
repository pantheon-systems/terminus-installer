<?php

namespace Pantheon\TerminusInstaller\Command;

use Pantheon\TerminusInstaller\Utils\LocalSystem;
use Pantheon\TerminusInstaller\Utils\TerminusPackage;
use Symfony\Component\Config\Definition\Exception\ForbiddenOverwriteException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;

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
        // Configure the package
        $package = new TerminusPackage();
        $package->setInstallDir($input->getOption('install-dir'));
        $package->setOutput($output);

        // Execute the installation
        $output->writeln('Installing Terminus...');
        $status_code = $package->runInstall(
            $output,
            $input->getOption('install-version')
        );

        // Ensure the installed package is easy to find
        try {
            $exe_location = $package->getExeName();
            $bin_location = TerminusPackage::getLocation($input->getOption('bin-dir'));
            LocalSystem::makeSymlink($exe_location, $bin_location);
        } catch (ForbiddenOverwriteException $e) {
            // Couldn't write symlink at the location
            $exe_dir = $package->getExeDir();
            $message = <<<EOT
Terminus was installed, but the installer was not able to write to your bin dir. To enable the 
`terminus` command, add this alias to your .bash_profile (Mac) or .bashrc (Linux) file:

alias terminus=$exe_location

Or you can enable it by adding the directory the executable file is in to your path:

PATH="$exe_dir:\$PATH"
EOT;
            $output->writeln($message);
        } catch (FileNotFoundException $e) {
            // Discovered that the executable wasn't present
            $output->writeln('Terminus was not installed.');
            return 1;
        }

        // Return status code of installation
        return $status_code;
    }
}
