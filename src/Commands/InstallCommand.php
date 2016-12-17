<?php

namespace Pantheon\Janus\Commands;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;
use Symfony\Component\Yaml\Yaml;

/**
 * Class InstallCommand
 * @package Pantheon\Janus\Commands
 */
class InstallCommand implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var string
     */
    const SOURCE = 'pantheon-systems/terminus';
    /**
     * @var string
     */
    const PREFIX = 'TERMINUS_';
    /**
     * @var integer
     */
    const TIMEOUT = 3600;
    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * Installs Terminus
     *
     * @command install
     *
     * @option string $bin-dir Directory in which command-line executable scripts are added. The default is /usr/local/bin.
     * @option string $composer-file A copy of Composer with which to run the installation.
     * @option string $install-dir Directory to which to install Terminus. The default is $HOME/.composer/vendor.
     * @option string $cache-dir Directory to which to place Terminus' cache. The default is $HOME/.terminus/cache.
     * @option string $time-zone Time zone to print out Terminus datetimes in. The default is UTC.
     * @option string $date-format PHP-standard format in which to format timestamps. The default is Y-m-d H:i:s.
     */
    public function install($options = [
        'composer-file' => null,
        'bin-dir' => '/usr/local/bin',
        'install-dir' => null,
        'cache-dir' => null,
        'time-zone' => null,
        'date-format' => null,
    ]) {
        $fs = $this->getFilesystem();
        $bin_dir = $options['bin-dir'];
        $bin_is_writable = $fs->exists($bin_dir) && is_writable($bin_dir);

        // Find or install a copy of Composer
        if (is_null($composer_exe = $options['composer-file'])) {
            $has_composer = (trim(shell_exec('type composer > /dev/null; echo $?')) === '0');
            $composer_exe = 'composer';

            // If Composer is not present, download it
            if (!$has_composer) {
                $this->logger->notice('Installing the Composer package manager...');
                $this->getProcess("curl -sS https://getcomposer.org/installer")->run();

                // If the bin dir is writable, move Composer into that dir
                if ($bin_is_writable) {
                    $fs->rename('composer.phar', "$bin_dir/composer");
                } else { // Else, all moves into the bin dir should be skipped
                    $composer_exe = 'composer.json';
                }
            }
        }

        // Use Composer to install Terminus
        $this->logger->notice('Installing Terminus...');
        $home_dir = $this->getHomeDir();

        $install_command = "$composer_exe require " . self::SOURCE;
        if (isset($options['install-dir']) && !is_null($install_dir = $options['install-dir'])) {
            $install_dir = str_replace('~', $home_dir, $install_dir);
            $install_command = "cd $install_dir ; $install_command";
        } else {
            $install_dir = getcwd();
        }
        $this->getProcess($install_command)->run();
        if ($bin_is_writable) {
            $fs->symlink("$install_dir/vendor/bin/terminus", "$bin_dir/terminus");
        } else {
            $this->logger->warning("Terminus was installed, but the installer was not able to write to your bin dir. To enable the `terminus` command, add this alias to your .bash_profile (Mac) or .bashrc (Linux) file:\nalias terminus=$install_dir/bin/terminus");
            $fs->remove($composer_exe);
        }

        $settings = [];
        foreach ($options as $key => $value) {
            if (!empty($value) && in_array($key, ['cache-dir', 'date-format', 'time-zone',])) {
                $settings[$key] = $value;
            }
        }
        if (!empty($settings)) {
            $this->logger->notice('Writing configuration file...');
            $this->writeTerminusConfig($settings);
        }
    }

    /**
     * Reflects a constant name from a given key.
     *
     * @param string $key_name The name of a key to get a constant for
     * @return string
     */
    protected function getConstantFromKey($key_name)
    {
        return self::PREFIX . strtoupper(str_replace('-', '_', $key_name));
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
     * Returns a set-up process object.
     *
     * @param string $cmd The command to execute
     * @return Process
     */
    protected function getProcess($cmd)
    {
        $process = new Process($cmd);
        $process->setTimeout(self::TIMEOUT);
        return $process;
    }

    /**
     * Combines the settings given with any preexisting settings and writes to the config file.
     *
     * @param string[] $settings An associative array of settings from the command line
     */
    protected function writeTerminusConfig($settings)
    {
        $home_dir = $this->getHomeDir();
        $config_file = "$home_dir/.terminus/config.yml";
        $fs = $this->getFilesystem();

        // Change the keys from option-style to Terminus-settings style.
        $settings = array_combine(
            array_map(
                function ($constant) {return $this->getConstantFromKey($constant);},
                array_keys($settings)
            ),
            array_map(
                function ($value) {return str_replace('~', '[[ TERMINUS_USER_HOME ]]', $value);},
                $settings
            )
        );

        // If there is an existing config file, add it to $settings but do not override $settings.
        if ($fs->exists($config_file)) {
            $settings = array_merge(Yaml::parse(file_get_contents($config_file)), $settings);
        }

        // Write the revised config file
        $fs->dumpFile($config_file, Yaml::dump($settings));
    }
}
