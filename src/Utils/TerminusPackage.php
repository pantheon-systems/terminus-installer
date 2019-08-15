<?php

namespace Pantheon\TerminusInstaller\Utils;

use Pantheon\TerminusInstaller\Composer\ComposerAwareInterface;
use Pantheon\TerminusInstaller\Composer\ComposerAwareTrait;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;

class TerminusPackage implements ComposerAwareInterface
{
    use ComposerAwareTrait;

    const EXE_DIR = '{install_dir}/vendor/bin';
    const EXE_NAME = '{dir}/terminus';
    const PACKAGE_NAME = 'pantheon-systems/terminus';

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
        return self::getLocation($this->getExeDir());
    }

    /**
     * @return string The desired Terminus installation directory
     */
    public function getInstallDir()
    {
        return $this->install_dir;
    }

    public function getInstalledVersion()
    {
        return str_replace('* ', '', $this->getOutdatedInfo()['versions']);
    }

    /**
     * @return string Latest version of Terminus according to Composer
     */
    public function getLatestVersion()
    {
        return $this->getOutdatedInfo()['latest'];
    }

    /**
     * @param string $dir The directory Terminus executable should be located in
     * @return string The location where Terminus should exist
     */
    public static function getLocation($dir)
    {
        return LocalSystem::sanitizeLocation(
            str_replace('{dir}', $dir, self::EXE_NAME)
        );
    }

    /**
     * @return object Information about how Terminus is outdated
     */
    public function getOutdatedInfo()
    {
        if (!isset($this->outdated_info)) {
            $this->outdated_info = $this->getOutdatedPackageData();
        }
        return $this->outdated_info;
    }

    /**
     * @return boolean
     */
    public function isUpToDate()
    {
        return $this->versionCompare();
    }

    /**
     * Checks for Terminus being outdated by at least one major version
     *
     * @return boolean
     */
    public function onCurrentMajorVersion() {
        return $this->versionCompare(function($version) {
            $version_array = explode('.', $version);
            return array_shift($version_array);
        });
    }

    /**
     * Checks for Terminus being outdated by at least one minor version
     *
     * @return boolean
     */
    public function onCurrentMinorVersion() {
        return $this->versionCompare(function($version) {
            $version_array = explode('.', $version);
            return array_shift($version_array) . '.' . array_shift($version_array);
        });
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
        $arguments = [
            'command' => 'require',
            'packages' => [$this->getPackageTitle($version),],
            '--working-dir' => $this->getInstallDir(),
        ];

        $status_code = $this->getComposer()->run(new ArrayInput($arguments), $output);
        return $status_code;
    }

    /**
     * Runs composer require to install the latest version of Terminus
     *
     * @param OutputInterface $output
     * @return int $status_code The status code returned from composer install
     * @throws \Exception
     */
    public function runInstallLatest(OutputInterface $output)
    {
        return $this->runInstall($output, $this->getLatestVersion());
    }

    /**
     * Runs composer update to update Terminus
     *
     * @param OutputInterface $output
     * @return int $status_code The status code returned from composer update
     * @throws \Exception
     */
    public function runUpdate(OutputInterface $output)
    {
        $arguments = [
            'command' => 'update',
            'packages' => [$this->getPackageTitle(),],
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

    /**
     * Gets information for outdated packages by running composer outdated
     *
     * @return array Data returned by composer outdated about the Terminus package
     */
    private function getOutdatedPackageData()
    {
        $arguments = [
            'command' => 'outdated',
            'package' => $this->getPackageTitle(),
            '--working-dir' => $this->getInstallDir(),
            '--format' => 'json',
        ];

        $outdated_output = new BufferedOutput();
        $this->getComposer()->run(new ArrayInput($arguments), $outdated_output);
        preg_match_all('/(.*) : (.*)/', $outdated_output->fetch(), $info_pieces);
        $trimmer = function ($array) {
            return array_map(
                function ($string) {
                    return strip_tags(trim($string));
                },
                $array
            );
        };
        return array_combine($trimmer($info_pieces[1]), $trimmer($info_pieces[2]));
    }

    /**
     * @param function $transform
     */
    private function versionCompare($transform = null)
    {
        if (is_null($transform)) {
            $transform = function ($version) {
                return $version;
            };
        }
        return version_compare(
            $transform($this->getLatestVersion()),
            $transform($this->getInstalledVersion()),
            '=='
        );
    }

    /**
     * Returns the package title with the version appended, if given
     *
     * @param string $install_version The specific version of Terminus to install
     * @return string The name of the package for Composer install
     */
    public function getPackageTitle($install_version = null)
    {
        $package = self::PACKAGE_NAME;
        if (!is_null($version = $install_version)) {
            $package .= ":^$version";
        }
        return $package;
    }
}
