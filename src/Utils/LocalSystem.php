<?php

namespace Pantheon\TerminusInstaller\Utils;

use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Filesystem\Filesystem;

class LocalSystem
{
    /**
     * @param string $location Location to check for existence of
     */
    public static function fileExists($location)
    {
        $sanitized_location = self::sanitizeLocation($location);
        $fs = self::getFilesystem();
        return $fs->exists(self::sanitizeLocation($sanitized_location));
    }

    /**
     * Writes a symlink for the newly installed Terminus' executable in the bin directory
     *
     * @param string $source Location to make a symlink of
     * @param string $target Location to create the new symlink at
     */
    public static function makeSymlink($source, $target)
    {
        $sanitized_source = self::sanitizeLocation($source);
        $sanitized_target = self::sanitizeLocation($target);


        if (!self::fileExists($source)) {
            throw new FileNotFoundException("$source does not exist.");
        }
        if (!is_writable(dirname($sanitized_target))) {
            throw new IOException("$target is not writable.");
        }

        $fs = self::getFilesystem();
        $fs->symlink($sanitized_source, $sanitized_target);
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
        $dir = str_replace(
            ['~', '/',],
            [self::getHomeDir(), DIRECTORY_SEPARATOR,],
            $dir
        );
        if (!file_exists($dir)) {
            return $dir;
        }
        return realpath($dir);
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
}
