<?php

namespace Pantheon\Janus\Commands;

use Symfony\Component\Yaml\Yaml;

/**
 * Class InstallCommand
 * @package Pantheon\Janus\Commands
 */
class InstallCommand
{
    /**
     * @var string
     */
    private $constant_prefix = 'TERMINUS_';

    /**
     * Installs Terminus
     *
     * @command install
     * @aliases install:terminus
     *
     * @option string $install-dir Directory to which to install Terminus; default is $HOME/.composer/vendor/
     * @option string $cache-dir Directory to which to place Terminus' cache; default is $HOME/.terminus/cache
     * @option string $time-zone Time zone to print out Terminus datetimes in; default is UTC
     * @option string $date-format ; default is Y-m-d H:i:s
     */
    public function install(
        $options = ['install-dir' => null, 'cache-dir' => null, 'time-zone' => null, 'date-format' => null,]
    ) {
        //Install Composer if it is not present
        /**if (!is_executable('composer')) {
            exec("curl -sS https://getcomposer.org/installer | php -- --");
        }**/
        echo "Installin', yo.\n";
        if (isset($options['install-dir']) && !is_null($install_dir = $options['install-dir'])) {
            $install_dir = str_replace('~', $this->getHomeDir(), $install_dir);
            exec("cd $install_dir ; composer require pantheon-systems/terminus");
        } else {
            exec("composer global require pantheon-systems/terminus");
        }
        $this->writeTerminusRC($options);
        echo "You got if fam.\n";
    }

    private function writeTerminusRC($options)
    {
        $home_dir = $this->getHomeDir();
        $config_file = "$home_dir/.terminusrc";
        if (file_exists($config_file)) {
            $contents = Yaml::parse(file_get_contents($config_file));
            $file_config = array_combine(
                array_map(
                    function ($constant_name) {
                        return $this->getKeyFromConstant($constant_name);
                    },
                    array_keys($contents)
                ),
                $contents
            );
            $options = array_merge($file_config, $options);
        }

        $file_text = '';
        foreach ($options as $key => $value) {
            if (!empty($value)) {
                $file_text .= $this->getConstantFromKey($key) . ': ' . str_replace('~', $home_dir, $value) . PHP_EOL;
            }
        }

        file_put_contents($config_file, $file_text);
    }

    /**
     * Reflects a constant name from a given key
     *
     * @param string $key_name The name of a key to get a constant for
     * @return string
     */
    private function getConstantFromKey($key_name)
    {
        return strtoupper(str_replace(['', '-'], [$this->constant_prefix, '_'], $key_name));
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
    private function getHomeDir()
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
     * Reflects a key name given a Terminus constant name
     *
     * @param string $constant_name The name of a constant to get a key for
     * @return string
     */
    private function getKeyFromConstant($constant_name)
    {
        return strtolower(str_replace([$this->constant_prefix, '_'], ['', '-'], $constant_name));
    }
}
