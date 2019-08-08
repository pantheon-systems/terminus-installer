<?php

namespace Pantheon\TerminusInstaller\Command;

use Pantheon\TerminusInstaller\Composer\ComposerAwareInterface;
use Pantheon\TerminusInstaller\Composer\ComposerAwareTrait;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Filesystem\Filesystem;

abstract class AbstractCommand extends Command implements ComposerAwareInterface
{
    use ComposerAwareTrait;

    const PACKAGE_NAME = 'pantheon-systems/terminus';

    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * @param string $dir The directory indicated for the update location
     * @return string The update directory
     */
    protected function getDir($dir = null)
    {
        return str_replace('~', $this->getHomeDir(), $dir);
    }

    /**
     * @return Filesystem A configured Symfony Filesystem object
     */
    protected function getFilesystem()
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
    protected function getHomeDir()
    {
        $home = getenv('HOME');
        if (!$home && !is_null(getenv('MSYSTEM')) && (strtoupper(substr(getenv('MSYSTEM'), 0, 4)) !== 'MING')) {
            $home = getenv('HOMEPATH');
        }
        return $home;
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
}
