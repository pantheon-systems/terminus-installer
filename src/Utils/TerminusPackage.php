<?php

namespace Pantheon\TerminusInstaller\Utils;

use Pantheon\TerminusInstaller\Composer\ComposerAwareInterface;
use Pantheon\TerminusInstaller\Composer\ComposerAwareTrait;
use Robo\Common\OutputAwareTrait;
use Robo\Contract\OutputAwareInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;

class TerminusPackage implements ComposerAwareInterface, OutputAwareInterface
{
    use ComposerAwareTrait;
    use OutputAwareTrait;

    const EXE_DIR = '{install_dir}/vendor/bin';
    const EXE_NAME = '{dir}/terminus';
    const PACKAGE_NAME = 'pantheon-systems/terminus';

    /**
     * @var string The desired directory for containing Terminus
     */
    private $install_dir;

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
        return self::getLocation($this->getExeDir());
    }

    /**
     * @param $dir The directory Terminus executable should be located in
     * @return string The location where Terminus should exist
     */
    public static function getLocation($dir)
    {
        return LocalSystem::sanitizeLocation(
            str_replace('{dir}', $dir, self::EXE_NAME)
        );
    }

    /**
     * @return string The desired Terminus installation directory
     */
    public function getInstallDir()
    {
        return $this->install_dir;
    }

    /**
     * @param string $install_version The specific version of Terminus to install
     * @return string The name of the package for Composer install
     */
    protected function getPackageTitle($install_version = null)
    {
        $package = self::PACKAGE_NAME;
        if (!is_null($version = $install_version)) {
            $package .= ":^$version";
        }
        return $package;
    }

    public function runInstall(OutputInterface $output, $version = null)
    {
        $arguments = [
            'command' => 'require',
            'packages' => [$this->getPackageTitle($version),],
            '--working-dir' => $this->getInstallDir(),
        ];

        $status_code = $this->getComposer()->run(new ArrayInput($arguments), $output);
        return $status_code;

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

    public function update()
    {

    }

    /**
     * Gets information for outdated packages
     *
     * @param string $update_dir Directory to which to update Terminus
     * @return object $packageInfo Data returned by composer about outdated packages
     */
    private function getOutdatedPackageData()
    {
        $arguments = [
            'command' => 'outdated',
            '--working-dir' => $update_dir,
            '--format' => 'json',
        ];

        $outdated_output = new BufferedOutput();
        $this->getComposer()->run(new ArrayInput($arguments), $outdated_output);
        $package_data = json_decode($outdated_output->fetch());
        return $package_data;
    }
}
