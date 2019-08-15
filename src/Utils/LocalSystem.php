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
}
