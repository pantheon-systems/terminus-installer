<?php

namespace Pantheon\TerminusInstaller\Utils;

use Symfony\Component\Config\Definition\Exception\ForbiddenOverwriteException;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Filesystem\Filesystem;

class LocalSystem
{
    /**
     * Writes a symlink for the newly installed Terminus' executable in the bin directory
     *
     * @param string $source Location to make a symlink of
     * @param string $target Location to create the new symlink at
     */
    public static function makeSymlink($source, $target)
    {
        $fs = self::getFilesystem();
        if (!$fs->exists($source)) {
            throw new FileNotFoundException("$source does not exist.");
        }
        if (!is_writable($source)) {
            throw new ForbiddenOverwriteException("$source is not writable.");
        }
        if (!is_writable($target)) {
            throw new ForbiddenOverwriteException("$target is not writable.");
        }

        $fs->symlink(
            self::sanitizeLocation($target),
            self::sanitizeLocation($source)
        );
    }

    /**
     * This function ensures that:
     *  - Tildes (~) are interpreted as the home directory.
     *  - The correct directory separator is used.
     *
     * @param string $dir A location
     * @return string The location, sanitized
     */
    public static function sanitizeLocation($dir = null)
    {
        return realpath(
            str_replace(
                ['~', '/',],
                [self::getHomeDir(), DIRECTORY_SEPARATOR,],
                $dir
            )
        );
    }

    /**
     * @return Filesystem A configured Symfony Filesystem object
     */
    protected static function getFilesystem()
    {
        return new Filesystem();
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
    protected static function getHomeDir()
    {
        $home = getenv('HOME');
        if (!$home && !is_null(getenv('MSYSTEM')) && (strtoupper(substr(getenv('MSYSTEM'), 0, 4)) !== 'MING')) {
            $home = getenv('HOMEPATH');
        }
        return $home;
    }

    /**
     * Gets information for outdated packages
     *
     * @param string $update_dir Directory to which to update Terminus
     * @return object $packageInfo Data returned by composer about outdated packages
     */
    private function getOutdatedPackageInfo($update_dir)
    {
        $arguments = [
            'command' => 'outdated',
            '--working-dir' => $update_dir,
            '--format' => 'json',
        ];

        $this->output->writeln('Checking Terminus for updates...');
        $outdatedOutput = new BufferedOutput();
        $this->getComposer()->run(new ArrayInput($arguments), $outdatedOutput);
        $packageInfo = json_decode($outdatedOutput->fetch());
        return $packageInfo;
    }

    /**
     * Standardizes the possible information coming back from the Composer Outdated command
     *
     * @param object|null $packageInfo Data in JSON format returned from composer outdated
     * @return array The list of outdated installed packages
     */
    private function standardizeOutdatedPackageInfo($packageInfo) {
        if (($packageInfo === null) || !isset($packageInfo->installed)) {
            return [];
        }
        return $packageInfo->installed;
    }

    /**
     * Ensures the vital properties for checking Terminus versions are present
     *
     * @param object $terminus_info Should have a latest and a version property
     * @throws \Exception Is thrown if either of those properties are missing
     */
    private function validateTerminusData($terminus_info) {
        if (!property_exists($terminus_info, 'latest')) {
            throw new \Exception(
                'Data returned from composer outdated did not include a latest version.'
            );
        }
        if (!property_exists($terminus_info, 'version')) {
            throw new \Exception(
                'Data returned from composer outdated did not include a currently installed version.'
            );
        }
    }

    /**
     * Checks for Terminus being outdated
     *
     */
    private function isTerminusOutdatedByMajorVersion($terminus_info) {
        if (!empty($terminus_info)) {
            $latest_version = explode('.', $terminus_info->latest);
            $latest_major_version = array_shift($latest_version);

            $installed_version = explode('.', $terminus_info->version);
            $latest_installed_version = array_shift($installed_version);

            return $latest_installed_version < $latest_major_version;
        }
        return false;
    }

    /**
     * @param object $package_info Stores outdated packages in the 'installed' property possibly DNE
     * @return array
     */
    private function getOutdatedTerminusInfo($package_info) {
        $filtered_info = array_filter($package_info, function($package) {
            return $package->name === self::PACKAGE_NAME;
        });
        if (empty($filtered_info)) {
            return (object)[];
        }
        return array_pop($filtered_info);
    }
}
