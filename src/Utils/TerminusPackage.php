<?php

namespace Pantheon\TerminusInstaller\Utils;

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;

class TerminusPackage
{

    const EXE_DIR = '{install_dir}';
    const EXE_NAME = '{dir}/terminus';
    const DOWNLOAD_URL = 'https://github.com/pantheon-systems/terminus/releases/download/{version}/terminus.phar';

    /**
     * @var string The desired directory for containing Terminus
     */
    private $install_dir;
    /**
     * @var object Data returned by composer outdated command about Terminus
     */
    private $outdated_info;

    /**
     * @return string The directory which should contain the Terminus executable
     */
    public function getExeDir()
    {
        return LocalSystem::sanitizeLocation(
            str_replace('{install_dir}', $this->getInstallDir(), self::EXE_DIR)
        );
    }

    /**
     * @return string The directory which should contain the Terminus executable
     */
    public function getExeName()
    {
        return self::getBinLocation($this->getExeDir());
    }

    /**
     * @return string The desired Terminus installation directory
     */
    public function getInstallDir()
    {
        return $this->install_dir;
    }

    /**
     * @return string Latest version of Terminus according to Composer
     */
    public function getFixedVersion()
    {
        return '3.0.6';
    }

    /**
     * @param string $dir The directory Terminus executable should be located in
     * @return string The location where Terminus should exist
     */
    public static function getBinLocation($dir)
    {
        return LocalSystem::sanitizeLocation(
            str_replace('{dir}', $dir, self::EXE_NAME)
        );
    }

    /**
     * Runs composer require on the set directory returns the status code.
     * Composer's output is fed into the output.
     *
     * @param OutputInterface $output
     * @param string $version
     * @return int $status_code The status code returned from composer install
     * @throws \Exception
     */
    public function runInstall(OutputInterface $output, $version = null)
    {
        $url = str_replace('{version}', $version ?: $this->getFixedVersion(), self::DOWNLOAD_URL);
        $file_name = $this->getInstallDir() . DIRECTORY_SEPARATOR . 'terminus';

        if (file_put_contents($file_name, file_get_contents($url)))
        {
            $output->writeln('File downloaded successfully');
            if (!$version) {
                // @todo Run self-update.
            }
            return 0;
        }

        throw new \Exception('An error ocurred while downloading Terminus');
    }

    /**
     * Remove the existing version of Terminus.
     *
     * @param OutputInterface $output
     * @return int $status_code The status code returned from composer remove
     * @throws \Exception
     */
    public function runRemove(OutputInterface $output)
    {
        $file_name = $this->getInstallDir() . DIRECTORY_SEPARATOR . 'terminus';
        if (file_exists($file_name)) {
            unlink($file_name);
            $output->writeln('File removed successfully');
            return 0;
        }
    }

    /**
     * Sets the desired Terminus installation directory
     *
     * @param $dir
     */
    public function setInstallDir($dir)
    {
        $this->install_dir = LocalSystem::sanitizeLocation($dir);
    }

}
