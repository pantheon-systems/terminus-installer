<?php

namespace Pantheon\TerminusInstaller\Command;

use Composer\Composer;
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
 * Class InstallTerminusCommand
 * @package Pantheon\Janus\Command
 */
class InstallCommand extends Command implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    const GLOBAL_SETTINGS = ['time-zone', 'date-format', 'cache-dir',];
    const PACKAGE = 'pantheon-systems/terminus';
    const PREFIX = 'TERMINUS_';
    const TIMEOUT = 3600;

    /**
     * @var Filesystem
     */
    private $filesystem;

    protected function configure()
    {
        $this->setName('install')
            ->setDescription('Installs Terminus via Composer')
            ->setDefinition([
                new InputOption('bin-dir', null, InputOption::VALUE_OPTIONAL, 'Directory in which command-line executable scripts are added', '/usr/local/bin'),
                new InputOption('install-dir', null, InputOption::VALUE_OPTIONAL, 'Directory to which to install Terminus'),
                new InputOption('install-version', null, InputOption::VALUE_OPTIONAL, 'Version of Terminus to install'),
            ])
            ->setHelp('Installs the Terminus CLI.');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $install_dir = $this->getInstallDir($input->getOption('install-dir'));
        $this->installTerminus(new ComposerApp(), $output, [
            'install-dir' => $install_dir,
            'install-version' => $input->getOption('install-version'),
        ]);
        $this->makeSymlink($input->getOption('bin-dir'), $install_dir);
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
        if (!$home) {
            $system = '';
            if (getenv('MSYSTEM') !== null) {
                $system = strtoupper(substr(getenv('MSYSTEM'), 0, 4));
            }
            if ($system != 'MING') {
                $home = getenv('HOMEPATH');
            }
        }
        return $home;
    }

    /**
     * @param string $opt_dir The directory indicated by user options for the install location
     * @return string The install directory
     */
    protected function getInstallDir($opt_dir = null)
    {
        return is_null($dir = $opt_dir) ? getcwd() | str_replace('~', $this->getHomeDir(), $dir);
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
     * @param ComposerApp $composer_app
     * @param OutputInterface $output
     * @param string $install_dir Directory to which to install Terminus
     * @param array $options Elements as follow:
     *     string $install_version Version of Terminus to install
     */
    protected function installTerminus(
        Composer $composer_app,
        OutputInterface $output,
        $install_dir,
        array $options = ['install-version' => null,]
    ) {
        $arguments = [
            'command' => 'require',
            'packages' => [$this->getPackageTitle($options['install-version']),],
            '--working-dir' => $install_dir,
        ];

        #$this->logger->notice('Installing Terminus...');
        echo 'Installing Terminus...' . PHP_EOL;
        $composer_app->run(new ArrayInput($arguments), $output);

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

        if ($fs->exists($bin_dir) && is_writable($bin_dir)) {
            $fs->symlink("$install_dir/vendor/bin/terminus", "$bin_dir/terminus");
        } else {
            $bin_location = "$install_dir/bin/terminus";
            $message =
                'Terminus was installed, but the installer was not able to write to your bin dir. To enable the
                `terminus` command, add this alias to your .bash_profile (Mac) or .bashrc (Linux) file:' . PHP_EOL .
                "alias terminus=$bin_location\n" .
                'Or you can enable it by adding the directory the executable file is in to your path:' . PHP_EOL .
                "\$PATH=\"$bin_location\":\$PATH";
            echo $message;
            #$this->logger->warning($message);
        }

    }
}
