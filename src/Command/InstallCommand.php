<?php

namespace Pantheon\TerminusInstaller\Command;

use Composer\Console\Application as ComposerApp;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class InstallCommand
 * @package Pantheon\TerminusInstaller\Command
 */
class InstallCommand extends Command implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    const PACKAGE = 'pantheon-systems/terminus';
    const PREFIX = 'TERMINUS_';
    const TIMEOUT = 3600;

    /**
     * @var Filesystem
     */
    private $filesystem;
    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @return ComposerApp A configured Composer Application object
     */
    protected function getComposer()
    {
        $composer_app = new ComposerApp();
        $composer_app->setAutoExit(false);
        return $composer_app;
    }

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
        $install_dir = $this->getInstallDir($input->getOption('install-dir'));
        $status_code = $this->installTerminus($install_dir, $input->getOption('install-version'));
        $this->makeSymlink($input->getOption('bin-dir'), $install_dir);
        return $status_code;
    }

    /**
     * Returns a Filesystem object.
     *
     * @return Filesystem
     */
    protected function getFilesystem()
    {
        if (empty($this->filesystem)) {
            $this->filesystem = new Filesystem();
        }
        return $this->filesystem;
    }

    /**
     * Returns the appropriate home directory.
     *
     * Adapted from Terminus Package Manager by Ed Reel
     * @author Ed Reel <@uberhacker>
     * @url    https://github.com/uberhacker/tpm
     *
     * @return string
     */
    protected function getHomeDir()
    {
        $home = getenv('HOME');
        if (!$home && !is_null(getenv('MSYSTEM')) && (strtoupper(substr(getenv('MSYSTEM'), 0, 4)) !== 'MING')) {
            $home = getenv('HOMEPATH');
        }
        return $home;
    }

    /**
     * @param string $dir The directory indicated for the install location
     * @return string The install directory
     */
    protected function getInstallDir($dir = null)
    {
        return str_replace('~', $this->getHomeDir(), $dir);
    }

    /**
     * @param string $install_version The specific version of Terminus to install
     * @return string The name of the package for Composer install
     */
    protected function getPackageTitle($install_version = null)
    {
        $package = self::PACKAGE;
        if (!is_null($version = $install_version)) {
            $package .= ":$version";
        }
        return $package;
    }

    /**
     * Uses Composer to install Terminus
     *
     * @param string $install_dir Directory to which to install Terminus
     * @param string $install_version Version of Terminus to install
     * @return integer $status_code The status code of the installation run
     */
    protected function installTerminus($install_dir, $install_version = null) {
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
        $exe_dir = "$install_dir/vendor/bin";
        $exe_location = "$exe_dir/terminus";

        if ($fs->exists($bin_dir) && is_writable($bin_dir) && is_writable($exe_location)) {
            $fs->symlink($exe_location, "$bin_dir/terminus");
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
