<?php

namespace Pantheon\TerminusInstaller\Command;

use Pantheon\TerminusInstaller\Utils\LocalSystem;
use Pantheon\TerminusInstaller\Utils\TerminusPackage;
use Symfony\Component\Config\Definition\Exception\ForbiddenOverwriteException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;

/**
 * Class InstallCommand
 * @package Pantheon\TerminusInstaller\Command
 */
class InstallCommand extends Command
{
    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setName('install')
            ->setDescription('Installs Terminus via Composer')
            ->setDefinition([
                new InputOption('bin-dir', null, InputOption::VALUE_OPTIONAL, 'Directory in which command-line executable scripts are added', '/usr/local/bin'),
                new InputOption('install-dir', null, InputOption::VALUE_OPTIONAL, 'Directory to which to install Terminus', getcwd()),
                new InputOption('install-version', null, InputOption::VALUE_OPTIONAL, 'Version of Terminus to install'),
                new InputOption('remove-outdated', null, InputOption::VALUE_OPTIONAL, 'Remove any versions of Terminus in the install directory before installing.', false),
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
        $exe_location = $package->getExeName();

        // Remove the existing version of Terminus
        var_dump($exe_location);
        if (LocalSystem::fileExists($exe_location)) {
            $question = new ConfirmationQuestion(
                'The installer has found another installation of Terminus in this location.' . PHP_EOL . 'Remove the old version? (Y/n) ',
                false
            );
            if ($input->getOption('remove-outdated')
                || $this->getHelper('question')->ask($input, $output, $question)
            ) {
                $output->writeln('Removing the old version of Terminus...');
                $package->runRemove(new NullOutput());
            }
        }

        // Execute the installation
        $output->writeln('Installing Terminus...');
        $status_code_install = $package->runInstall(
            $output,
            $input->getOption('install-version')
        );
        if ($status_code_install > 0) {
            return $status_code_install;
        }

        // Ensure the installed package is easy to find
        try {
            $bin_location = TerminusPackage::getBinLocation($input->getOption('bin-dir'));
            LocalSystem::makeSymlink($exe_location, $bin_location);
        } catch (ForbiddenOverwriteException $e) {
            // Couldn't write symlink at the location
            $output->writeln(self::overwriteErrorMessage($package->getExeDir(), $exe_location));
        } catch (FileNotFoundException $e) {
            // Discovered that the executable wasn't present
            $output->writeln('Terminus was not installed.');
            return 1;
        }

        // Return success
        return 0;
    }

    private static function overwriteErrorMessage($dir, $location)
    {
        return <<<EOT
Terminus was installed, but the installer was not able to write to your bin dir. To enable the 
`terminus` command, add this alias to your .bash_profile (Mac) or .bashrc (Linux) file:

alias terminus=$location

Or you can enable it by adding the directory the executable file is in to your path:

PATH="$dir:\$PATH"
EOT;
    }
}
